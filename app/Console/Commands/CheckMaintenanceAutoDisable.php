<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class CheckMaintenanceAutoDisable extends Command
{
    protected $signature   = 'maintenance:auto-disable';
    protected $description = 'Automatically disable maintenance mode if the auto-disable time has passed';

    public function handle(): int
    {
        $autoOff = Setting::get('maintenance_auto_disable_at', '');
        if (!$autoOff) return 0;

        if (now()->gte(\Carbon\Carbon::parse($autoOff))) {
            Setting::set('maintenance_mode', '0');
            Setting::set('maintenance_auto_disable_at', '');
            $this->info('Maintenance mode automatically disabled at ' . now()->format('j M Y H:i'));
        } else {
            $this->info('Maintenance auto-disable scheduled for ' . $autoOff . ' — not yet.');
        }

        return 0;
    }
}
