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

        return Command::SUCCESS;
    }
}
