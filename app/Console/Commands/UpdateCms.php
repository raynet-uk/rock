<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;

class UpdateCms extends Command
{
    protected $signature   = 'cms:update {--force : Skip confirmation}';
    protected $description = 'Pull latest CMS update from GitHub and apply migrations';

    public function handle(): int
    {
        $this->info('RAYNET-OS CMS Updater');
        $this->line('Current version: ' . trim(file_get_contents(base_path('VERSION'))));

        // Fetch latest version from GitHub
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'header' => "User-Agent: RAYNET-CMS\r\n"]]);
        $remoteVersion = @file_get_contents('https://raw.githubusercontent.com/raynet-uk/raynet-cms/main/VERSION', false, $ctx);

        if (!$remoteVersion) {
            $this->error('Could not fetch remote version. Check internet connection.');
            return 1;
        }

        $remoteVersion = trim($remoteVersion);
        $localVersion  = trim(file_get_contents(base_path('VERSION')));

        $this->line('Remote version: ' . $remoteVersion);

        if (version_compare($remoteVersion, $localVersion, '<=')) {
            $this->info('Already up to date.');
            Setting::set('update_available', '0');
            return 0;
        }

        $this->warn("Update available: {$localVersion} → {$remoteVersion}");

        if (!$this->option('force') && !$this->confirm('Apply update now?')) {
            return 0;
        }

        $this->line('Pulling from GitHub...');
        exec('cd ' . base_path() . ' && git fetch origin && git reset --hard origin/main 2>&1', $out, $code);
        $this->line(implode("\n", $out));

        if ($code !== 0) {
            $this->error('Git pull failed.');
            return 1;
        }

        // Fix permissions
        exec('chown -R ' . posix_getpwuid(posix_geteuid())['name'] . ' ' . base_path());

        $this->line('Running composer install...');
        exec('cd ' . base_path() . ' && composer install --no-dev --optimize-autoloader 2>&1', $out2);

        $this->line('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        $newVersion = trim(file_get_contents(base_path('VERSION')));
        Setting::set('last_updated_version', $newVersion);
        Setting::set('last_updated_at', now()->toISOString());
        Setting::set('show_update_interstitial', '1');
        Setting::set('update_available', '0');

        $this->info("✓ Updated to {$newVersion}");
        return 0;
    }
}
