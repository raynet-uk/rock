<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use SimpleXMLElement;
use Throwable;

class RefreshRsgbRegionNews extends Command
{
    protected $signature = 'rsgb:refresh-region';
    protected $description = 'Fetch latest RSGB regional news based on configured zone';

    // Maps RAYNET zone => one or more RSGB RSS feed URLs
    // RSGB regions: 1=Scotland West, 2=Scotland North, 3=England NW, 4=England NE,
    //               5=England W Midlands, 6=North Wales, 7=South Wales, 8=NI,
    //               9=London/Thames, 10=England S/SE, 11=England SW/CI,
    //               12=England E/East Anglia, 13=England E Midlands
    protected array $zoneMap = [
        'Zone 1'  => [
            'label' => 'England North East',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-4/feed/'],
        ],
        'Zone 2'  => [
            'label' => 'Yorkshire & Humberside',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-4/feed/'],
        ],
        'Zone 3'  => [
            'label' => 'England East Midlands',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-13/feed/'],
        ],
        'Zone 4'  => [
            'label' => 'England East & East Anglia',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-12/feed/'],
        ],
        'Zone 5'  => [
            'label' => 'London & Thames Valley',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-9/feed/'],
        ],
        'Zone 6'  => [
            'label' => 'England South & South East',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-10/feed/'],
        ],
        'Zone 7'  => [
            'label' => 'England South West & Channel Islands',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-11/feed/'],
        ],
        'Zone 8'  => [
            'label' => 'South Wales',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-7/feed/'],
        ],
        'Zone 9'  => [
            'label' => 'England West Midlands',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-5/feed/'],
        ],
        'Zone 10' => [
            'label' => 'England North West',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-3/feed/'],
        ],
        'Zone 11' => [
            'label' => 'Northern Ireland',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-8/feed/'],
        ],
        'Zone 18' => [
            'label' => 'North Wales',
            'feeds' => ['https://rsgb.org/main/blog/category/all-regions/region-6/feed/'],
        ],
        'Zone 20' => [
            'label' => 'Scotland',
            'feeds' => [
                'https://rsgb.org/main/blog/category/all-regions/region-1/feed/',
                'https://rsgb.org/main/blog/category/all-regions/region-2/feed/',
            ],
        ],
    ];

    public function handle()
    {
        $zone    = Setting::get('group_region', 'Zone 10');
        $config  = $this->zoneMap[$zone] ?? $this->zoneMap['Zone 10'];
        $label   = $config['label'];
        $feeds   = $config['feeds'];
        $allUrls = implode(', ', $feeds);

        $this->info("Zone: $zone ($label) => $allUrls");

        $headlines = [];

        foreach ($feeds as $rssUrl) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'RAYNET-Liverpool-Dashboard/1.0 (+https://raynet-liverpool.uk)',
                ])->timeout(12)->get($rssUrl);

                if ($response->failed()) {
                    $this->warn("HTTP failed for $rssUrl: " . $response->status());
                    continue;
                }

                libxml_use_internal_errors(true);
                $rss   = new SimpleXMLElement($response->body());
                $items = $rss->xpath('//item');

                foreach ($items as $item) {
                    $title   = trim((string) $item->title);
                    $link    = trim((string) $item->link);
                    $pubDate = (string) $item->pubDate;
                    if (!$title || !$link) continue;
                    $headlines[] = [
                        'title' => $title,
                        'link'  => $link,
                        'date'  => date('d M Y', strtotime($pubDate)) ?: '—',
                        'ts'    => strtotime($pubDate),
                    ];
                }

            } catch (Throwable $e) {
                $this->warn("Error fetching $rssUrl: " . $e->getMessage());
                Log::warning("RSGB regional feed error ($rssUrl): " . $e->getMessage());
            }
        }

        // Sort merged results by date descending
        usort($headlines, fn($a, $b) => $b['ts'] <=> $a['ts']);

        // Remove internal ts field
        $headlines = array_map(fn($h) => array_diff_key($h, ['ts' => 0]), $headlines);

        Cache::forever('rsgb_region3_news', [
            'headlines'  => $headlines,
            'updated_at' => now()->toDateTimeString(),
            'zone'       => $zone,
            'label'      => $label,
            'url'        => $feeds[0],
        ]);

        $this->info("Cached " . count($headlines) . " headlines for $zone ($label)");
        Log::info("Cached " . count($headlines) . " RSGB regional headlines for $zone ($label)");
    }
}
