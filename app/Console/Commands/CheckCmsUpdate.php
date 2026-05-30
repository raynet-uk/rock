<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class CheckCmsUpdate extends Command
{
    protected $signature   = 'cms:check-update';
    protected $description = 'Check if a CMS update is available';

    public function handle(): int
    {
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'header' => "User-Agent: RAYNET-CMS\r\n"]]);
        $remote = @file_get_contents('https://raw.githubusercontent.com/raynet-uk/rock/main/VERSION', false, $ctx);
        if (!$remote) return 0;

        $remote = trim($remote);
        $local  = trim(file_get_contents(base_path('VERSION')));

        $available = version_compare($remote, $local, '>') ? '1' : '0';
        Setting::set('update_available', $available);
        Setting::set('update_remote_version', $remote);
        Setting::set('update_checked_at', now()->toISOString());

        $this->info($available === '1' ? "Update available: {$local} → {$remote}" : "Up to date: {$local}");
        return 0;
    }
}
