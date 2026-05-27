<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    /**
     * Month view calendar.
     */
    public function index(?int $year = null, ?int $month = null)
    {
        $today = Carbon::today();

        // Determine which month to show
        if ($year === null || $month === null) {
            $currentMonth = $today->copy()->startOfMonth();
        } else {
            $currentMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        }

        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd   = $currentMonth->copy()->endOfMonth();

        // Calendar grid runs from Monday at/before month start to Sunday at/after month end
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch events that overlap this month (single or multi-day)
        $events = Event::with('type')
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('starts_at', [$monthStart, $monthEnd])
                  ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                      $q2->whereNotNull('ends_at')
                         ->where('starts_at', '<=', $monthEnd)
                         ->where('ends_at', '>=', $monthStart);
                  });
            })
            ->when(!auth()->check(), fn($q) => $q->where('is_private', false))
            ->orderBy('starts_at')
            ->get();

        $weeks  = [];
        $cursor = $gridStart->copy();

        while ($cursor <= $gridEnd) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $date    = $cursor->copy();
                $inMonth = $date->month === $currentMonth->month;
                $isToday = $date->isSameDay($today);

                // Events that cover this day (start on, or span across)
                $dayEvents = $events->filter(function ($event) use ($date) {
                    $startDate = $event->starts_at->copy()->startOfDay();
                    $endDate   = $event->ends_at
                        ? $event->ends_at->copy()->startOfDay()
                        : $startDate;

                    return $date->greaterThanOrEqualTo($startDate)
                        && $date->lessThanOrEqualTo($endDate);
                });

                $week[] = [
                    'date'      => $date,
                    'in_month'  => $inMonth,
                    'is_today'  => $isToday,
                    'events'    => $dayEvents,
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $icsUrl = route('calendar.ics', [
            'year'  => $currentMonth->format('Y'),
            'month' => $currentMonth->format('m'),
        ]);

        return view('calendar', [
            'currentMonth' => $currentMonth,
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
            'weeks'        => $weeks,
            'icsUrl'       => $icsUrl,
        ]);
    }

    /**
     * Export the month as a single ICS file containing all events.
     */
    public function ics(int $year, int $month)
    {
        $currentMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthStart   = $currentMonth->copy()->startOfMonth();
        $monthEnd     = $currentMonth->copy()->endOfMonth();

        $events = Event::with('type')
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('starts_at', [$monthStart, $monthEnd])
                  ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                      $q2->whereNotNull('ends_at')
                         ->where('starts_at', '<=', $monthEnd)
                         ->where('ends_at', '>=', $monthStart);
                  });
            })
            ->when(!auth()->check(), fn($q) => $q->where('is_private', false))
            ->orderBy('starts_at')
            ->get();

        $siteName = config('app.name', 'Liverpool RAYNET');
        $domain   = parse_url(config('app.url', 'https://example.com'), PHP_URL_HOST) ?? 'example.com';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' . $this->escapeIcsText($siteName) . '//EN',
            'CALSCALE:GREGORIAN',
        ];

        foreach ($events as $event) {
            $uid     = 'calendar-' . $currentMonth->format('Ym') . '-event-' . $event->id . '@' . $domain;
            $dtStart = $event->starts_at->copy()->utc()->format('Ymd\THis\Z');
            $dtEnd   = $event->ends_at
                ? $event->ends_at->copy()->utc()->format('Ymd\THis\Z')
                : $event->starts_at->copy()->addHours(2)->utc()->format('Ymd\THis\Z');
            $dtStamp = now()->utc()->format('Ymd\THis\Z');

            $summary = $event->title;

            $descriptionPieces = [];
            if ($event->description) {
                $descriptionPieces[] = $event->description;
            }
            if ($event->type) {
                $descriptionPieces[] = 'Type: ' . $event->type->name;
            }
            $description = implode('\n', $descriptionPieces);

            $location = $event->location ?? '';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $this->escapeIcsText($uid);
            $lines[] = 'DTSTAMP:' . $dtStamp;
            $lines[] = 'DTSTART:' . $dtStart;
            $lines[] = 'DTEND:' . $dtEnd;
            $lines[] = 'SUMMARY:' . $this->escapeIcsText($summary);

            if ($location !== '') {
                $lines[] = 'LOCATION:' . $this->escapeIcsText($location);
            }
            if ($description !== '') {
                $lines[] = 'DESCRIPTION:' . $this->escapeIcsText($description);
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        $body = implode("\r\n", $lines) . "\r\n";

        return response($body, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar-' . $currentMonth->format('Y-m') . '.ics"',
        ]);
    }

    private function escapeIcsText(string $text): string
    {
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace(";", "\;", $text);
        $text = str_replace(",", "\,", $text);
        $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);

        return $text;
    }
}