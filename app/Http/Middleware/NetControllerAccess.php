<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NetControllerAccess
{
    // Minutes before/after slot to allow access (1 for testing, 30 for production)
    const WINDOW_MINUTES = 1;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        $slot = self::findActiveSlot($user);
        if (!$slot) {
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
            if ($now->between($windowStart, $windowEnd)) {
                return array_merge($slot, [
                    'from_dt'      => $from,
                    'to_dt'        => $to,
                    'window_start' => $windowStart,
                    'window_end'   => $windowEnd,
                    'can_log'      => $now->between($from, $to),
                ]);
            }
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
        $fromDt = self::parseSlotTime($from, $ref);
        $toDt   = self::parseSlotTime($to, $ref);
        if (!$fromDt || !$toDt) return null;
        // Handle midnight crossover — if to < from, to is next day
        if ($toDt->lt($fromDt)) {
            $toDt->addDay();
        }
        return [$fromDt, $toDt];
    }
}
