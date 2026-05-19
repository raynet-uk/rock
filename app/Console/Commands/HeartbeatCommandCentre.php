<?php
namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HeartbeatCommandCentre extends Command
{
    protected $signature   = 'raynet:heartbeat';
    protected $description = 'Send a lightweight heartbeat ping to RAYNET Command Centre';

    public function handle(): int
    {
        $licenceKey = Setting::get('cms_licence_key') ?? env('CMS_LICENCE_KEY', '');
        $endpoint   = 'https://command.nathandillon.co.uk/api/cms/heartbeat';

        if (!$licenceKey) {
            $this->warn('No CMS licence key configured.');
            return 1;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-CMS-Licence' => $licenceKey,
                    'X-Site-Url'    => config('app.url'),
                ])
                ->post($endpoint, [
                    'licence_key' => $licenceKey,
                    'site_url'    => config('app.url'),
                    'cms_version' => '1.0.0',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['activated'])) {
                    $this->info('✓ CMS registered with Command Centre for the first time.');
                } else {
                    $this->info('✓ Heartbeat sent. Group: ' . ($data['group'] ?? 'unknown'));
                }
                return 0;
            }

            $this->warn('Heartbeat failed: ' . $response->status() . ' — ' . $response->json('error', 'Unknown error'));
            return 1;

        } catch (\Throwable $e) {
            $this->warn('Heartbeat exception: ' . $e->getMessage());
            return 1;
        }
    }
}
