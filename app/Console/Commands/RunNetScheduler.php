<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\NetSchedule;
use App\Models\Setting;
use Carbon\Carbon;

class RunNetScheduler extends Command {
    protected $signature   = 'net:run-scheduler';
    protected $description = 'Auto-activate/deactivate nets based on their schedule';

    public function handle(): int {
        $now    = Carbon::now()->timezone('Europe/London');
        $dayMap = [0=>'sun',1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat'];

        $schedules = NetSchedule::where('is_active', true)
                                ->where('auto_activate', true)
                                ->get();

        $activeSchedule = null;
        $activeDateBase = null;

        foreach ($schedules as $s) {
            // Check today
            if ($s->isScheduledToday($now)) {
                $start  = Carbon::createFromFormat('H:i:s', $s->start_time, 'Europe/London')->setDate($now->year,$now->month,$now->day);
                $end    = Carbon::createFromFormat('H:i:s', $s->end_time,   'Europe/London')->setDate($now->year,$now->month,$now->day);
                if ($end->lte($start)) $end->addDay();
                $window = $start->copy()->subMinutes(90);
                if ($now->between($window, $end)) { $activeSchedule = $s; $activeDateBase = $now->copy(); break; }
            }
            // Check tomorrow (pre-midnight 90-min window)
            $tomorrowDate = $now->copy()->addDay();
            if ($s->isScheduledToday($tomorrowDate)) {
                $start  = Carbon::createFromFormat('H:i:s', $s->start_time, 'Europe/London')->setDate($tomorrowDate->year,$tomorrowDate->month,$tomorrowDate->day);
                $end    = Carbon::createFromFormat('H:i:s', $s->end_time,   'Europe/London')->setDate($tomorrowDate->year,$tomorrowDate->month,$tomorrowDate->day);
                if ($end->lte($start)) $end->addDay();
                $window = $start->copy()->subMinutes(90);
                if ($now->between($window, $end)) { $activeSchedule = $s; $activeDateBase = $tomorrowDate; break; }
            }
            // Check yesterday (still running past midnight)
            $yesterdayDate = $now->copy()->subDay();
            if ($s->isScheduledToday($yesterdayDate)) {
                $start  = Carbon::createFromFormat('H:i:s', $s->start_time, 'Europe/London')->setDate($yesterdayDate->year,$yesterdayDate->month,$yesterdayDate->day);
                $end    = Carbon::createFromFormat('H:i:s', $s->end_time,   'Europe/London')->setDate($yesterdayDate->year,$yesterdayDate->month,$yesterdayDate->day);
                if ($end->lte($start)) $end->addDay();
                $window = $start->copy()->subMinutes(90);
                if ($now->between($window, $end)) { $activeSchedule = $s; $activeDateBase = $yesterdayDate; break; }
            }
        }

        $currentlyActive = Setting::get('net_active', '0') === '1';

        if ($activeSchedule) {
            $controller = $activeSchedule->activeController($now);
            Setting::set('net_active',       '1');
            Setting::set('net_callsign',     $activeSchedule->callsign);
            Setting::set('net_frequency',    $activeSchedule->frequency ?? '');
            Setting::set('net_band',         $activeSchedule->band ?? '');
            Setting::set('net_controller',   $controller);
            Setting::set('net_description',  $activeSchedule->description ?? '');
            Setting::set('net_announcement', $activeSchedule->announcement ?? '');
            Setting::set('net_priority',     $activeSchedule->priority ?? 'routine');
            Setting::set('net_start_time',   substr($activeSchedule->start_time, 0, 5));
            Setting::set('net_end_time',     substr($activeSchedule->end_time, 0, 5));
            $this->info("Net activated: {$activeSchedule->callsign} | Controller: {$controller} | Priority: {$activeSchedule->priority}");
        } elseif ($currentlyActive && NetSchedule::where('auto_activate', true)->where('is_active', true)->exists()) {
            Setting::set('net_active',       '0');
            Setting::set('net_band',         '');
            Setting::set('net_announcement', '');
            Setting::set('net_priority',     'routine');
            $this->info('Net deactivated by scheduler.');
        }

        // ── 15-minute standby alert for upcoming controllers ─────────────────
        if (Setting::get('net_active', '0') === '1') {
            $slots    = json_decode(Setting::get('net_controller_slots', '[]'), true) ?? [];
            $netCs    = Setting::get('net_callsign', '');
            $freq     = Setting::get('net_frequency', '');
            $group    = \App\Helpers\RaynetSetting::groupName();
            $portal   = config('app.url') . '/net-control';

            foreach ($slots as $i => $slot) {
                $cs      = strtoupper($slot['callsign'] ?? '');
                $fromStr = $slot['from'] ?? '';
                if (!$cs || !$fromStr) continue;

                $fromDt = $now->copy()->setTimeFromTimeString($fromStr . ':00');
                // Handle midnight crossover
                if ($fromDt->lt($now->copy()->subHours(12))) $fromDt->addDay();

                $minsUntil = $now->diffInMinutes($fromDt, false);

                // Fire between 14:30 and 15:30 before the slot (catches every cron run)
                if ($minsUntil < 15.5 && $minsUntil >= 14.5) {
                    $cacheKey = 'standby_alert_sent_' . $cs . '_' . $fromStr;
                    if (\Illuminate\Support\Facades\Cache::has($cacheKey)) continue;

                    $user = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();
                    if (!$user || !$user->email) continue;

                    // Find the previous slot's callsign
                    $prevCs = $i > 0 ? strtoupper($slots[$i - 1]['callsign'] ?? '') : null;

                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\NetControllerStandbyAlert(
                            controllerName:          $user->name ?? $cs,
                            controllerCallsign:      $cs,
                            netCallsign:             $netCs,
                            frequency:               $freq,
                            slotFrom:                $fromStr,
                            slotTo:                  $slot['to'] ?? '',
                            groupName:               $group,
                            portalUrl:               $portal,
                            prevControllerCallsign:  $prevCs,
                        )
                    );

                    // Mark as sent for 2 hours so it doesn't fire again
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(2));
                    \Illuminate\Support\Facades\Cache::put('standby_notified_' . $cs, true, now()->addHours(2));
                    $this->info("Standby alert sent to {$cs} for slot {$fromStr}");
                }
            }
        }

        return Command::SUCCESS;
    }
}
