<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NetControllerAccess
{
    // Minutes before/after slot to allow access (1 for testing, 30 for production)
    const WINDOW_MINUTES = 2;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        // Hard-block: outgoing controller completed a handover — prevent re-entry for 30 min
        $handoverDone = \Illuminate\Support\Facades\Cache::get('handover_done_' . $user->id);
        if ($handoverDone) {
            return redirect()->route('net-control.thankyou', [
                'cs'       => $handoverDone['cs'] ?? strtoupper($user->callsign ?? ''),
                'name'     => $user->name ?? $user->callsign,
                'net'      => \App\Models\Setting::get('net_callsign', ''),
                'freq'     => \App\Models\Setting::get('net_frequency', ''),
                'from'     => $handoverDone['from'] ?? '',
                'to'       => $handoverDone['to'] ?? '',
                'duration' => 0,
                'handover' => 1,
            ]);
        }

        $slot = self::findActiveSlot($user);
        if (!$slot) {
            // If the net has just ended, redirect to thank-you page
            $recentSlot = self::findRecentlyEndedSlot($user);
            if ($recentSlot) {
                $net  = $recentSlot['_net'] ?? [];
                $from = $recentSlot['from'] ?? '';
                $to   = $recentSlot['to'] ?? '';
                return redirect()->route('net-control.thankyou', [
                    'cs'       => $user->callsign,
                    'name'     => $user->name,
                    'net'      => $net['callsign'] ?? '',
                    'freq'     => $net['frequency'] ?? '',
                    'from'     => $from,
                    'to'       => $to,
                    'duration' => 0,
                ]);
            }
            return response()->view('net-control.no-access', [
                'user'      => $user,
                'nextSlot'  => self::findNextSlot($user),
            ], 403);
        }

        $request->merge(['_controller_slot' => $slot]);
        return $next($request);
    }

    public static function findActiveSlot($user): ?array
    {
        $cs  = strtoupper($user->callsign ?? '');
        if (!$cs) return null;
        $now = Carbon::now('Europe/London');

        foreach (self::allSlotsForCallsign($cs) as $slot) {
            $pair = self::parseSlotPair($slot['from'] ?? '', $slot['to'] ?? '', $now);
            if (!$pair) continue;
            [$from, $to] = $pair;
            $windowStart = $from->copy()->subMinutes(self::WINDOW_MINUTES);
            $windowEnd   = $to->copy()->addMinutes(self::WINDOW_MINUTES);
            // parseSlotPair already confirmed $now is in this window
            return array_merge($slot, [
                'from_dt'      => $from,
                'to_dt'        => $to,
                'window_start' => $windowStart,
                'window_end'   => $windowEnd,
                'can_log'      => $now->between($from, $to),
            ]);
        }
        return null;
    }

    public static function findNextSlot($user): ?array
    {
        $cs  = strtoupper($user->callsign ?? '');
        if (!$cs) return null;
        $now = Carbon::now('Europe/London');
        $next = null;

        foreach (self::allSlotsForCallsign($cs) as $slot) {
            $pair = self::parseSlotPair($slot['from'] ?? '', $slot['to'] ?? '', $now);
            if (!$pair) continue;
            [$from, $to] = $pair;
            $windowStart = $from->copy()->subMinutes(self::WINDOW_MINUTES);
            if ($windowStart->gt($now)) {
                if (!$next || $windowStart->lt(Carbon::parse($next['window_start']))) {
                    $next = array_merge($slot, ['from_dt' => $from, 'window_start' => $windowStart]);
                }
            }
        }
        return $next;
    }

    public static function allSlotsWithMeta(string $cs): array
    {
        $now    = Carbon::now('Europe/London');
        $result = [];

        // Live net slots
        $liveSlots = json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? [];
        $netInfo   = [
            'callsign'  => \App\Models\Setting::get('net_callsign', ''),
            'frequency' => \App\Models\Setting::get('net_frequency', ''),
            'name'      => \App\Models\Setting::get('net_description', ''),
        ];
        foreach ($liveSlots as $slot) {
            $result[] = array_merge($slot, ['_net' => $netInfo, '_source' => 'live']);
        }

        // Scheduled net slots (today's active schedules)
        foreach (\App\Models\NetSchedule::where('is_active', true)->get() as $sched) {
            $slots = is_array($sched->controller_slots) ? $sched->controller_slots : [];
            foreach ($slots as $slot) {
                $result[] = array_merge($slot, [
                    '_net'    => ['callsign' => $sched->callsign, 'frequency' => $sched->frequency, 'name' => $sched->name],
                    '_source' => 'schedule',
                ]);
            }
        }

        return array_filter($result, fn($s) => strtoupper($s['callsign'] ?? '') === $cs);
    }

    private static function allSlotsForCallsign(string $cs): array
    {
        return self::allSlotsWithMeta($cs);
    }

    public static function findRecentlyEndedSlot($user): ?array
    {
        $cs  = strtoupper($user->callsign ?? '');
        if (!$cs) return null;
        $now = Carbon::now('Europe/London');

        // Look for a slot that ended within the last 30 minutes
        foreach (self::allSlotsForCallsign($cs) as $slot) {
            $from = $slot['from'] ?? '';
            $to   = $slot['to'] ?? '';
            if (!$from || !$to) continue;

            foreach ([-1, 0] as $dayOffset) {
                $base   = $now->copy()->addDays($dayOffset);
                $fromDt = $base->copy()->setTimeFromTimeString($from . ':00');
                $toDt   = $now->copy()->setTimeFromTimeString($to . ':00');
                if ($toDt->lte($fromDt)) $toDt->addDay();

                $windowEnd = $toDt->copy()->addMinutes(30);
                if ($now->between($toDt, $windowEnd)) {
                    return $slot;
                }
            }
        }
        return null;
    }

    private static function parseSlotTime(string $time, Carbon $ref): ?Carbon
    {
        if (!$time) return null;
        try {
            $dt = $ref->copy()->setTimeFromTimeString($time . ':00');
            return $dt;
        } catch (\Throwable $e) { return null; }
    }

    private static function parseSlotPair(string $from, string $to, Carbon $ref): ?array
    {
        if (!$from || !$to) return null;

        // Try multiple day offsets so midnight-crossing slots work on both sides
        // e.g. slot 23:00-01:00: at 00:30 we need from=yesterday, to=today
        foreach ([-1, 0, 1] as $dayOffset) {
            $base   = $ref->copy()->addDays($dayOffset);
            $fromDt = self::parseSlotTime($from, $base);
            $toDt   = self::parseSlotTime($to,   $ref->copy()); // always relative to now's date
            if (!$fromDt || !$toDt) continue;

            // Handle midnight crossover — if to <= from, to is next calendar day
            if ($toDt->lte($fromDt)) {
                $toDt->addDay();
            }

            $windowStart = $fromDt->copy()->subMinutes(self::WINDOW_MINUTES);
            $windowEnd   = $toDt->copy()->addMinutes(self::WINDOW_MINUTES);

            // If now falls within this candidate window, this is the right pair
            if ($ref->between($windowStart, $windowEnd)) {
                return [$fromDt, $toDt];
            }
        }

        // No window match — return the straightforward today pair as fallback
        $fromDt = self::parseSlotTime($from, $ref);
        $toDt   = self::parseSlotTime($to,   $ref);
        if (!$fromDt || !$toDt) return null;
        if ($toDt->lte($fromDt)) $toDt->addDay();
        return [$fromDt, $toDt];
    }
}
