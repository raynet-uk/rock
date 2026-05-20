@php
    $news       = cache('rsgb_news', ['headlines' => [], 'updated_at' => null]);
    $region3    = cache('rsgb_region3_news', ['headlines' => [], 'updated_at' => null]);
    $groupZone  = \App\Helpers\RaynetSetting::groupZone();
    $zoneMap = [
        'Zone 1'  => 'England North East',
        'Zone 2'  => 'England North East',
        'Zone 3'  => 'England East Midlands',
        'Zone 4'  => 'England East and East Anglia',
        'Zone 5'  => 'London and Thames Valley',
        'Zone 6'  => 'England South and South East',
        'Zone 7'  => 'England South West and Channel Islands',
        'Zone 8'  => 'South Wales',
        'Zone 9'  => 'England West Midlands',
        'Zone 10' => 'England North West',
        'Zone 11' => 'Northern Ireland',
        'Zone 18' => 'North Wales',
        'Zone 20' => 'Scotland',
    ];
    $zoneLabel  = $zoneMap[$groupZone] ?? ($groupZone ?: 'England North West');
    $zoneCache  = cache('rsgb_region3_news', ['headlines' => [], 'updated_at' => null, 'url' => null]);
    $region3    = $zoneCache;
    $zoneLink   = !empty($zoneCache['url']) ? str_replace('/feed/', '/', $zoneCache['url']) : 'https://rsgb.org/main/blog/category/all-regions/';
@endphp

<style>
.rsgb-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .rsgb-grid { grid-template-columns: 1fr 1fr; }
}
.rsgb-col-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.9rem;
    padding-bottom: 0.6rem;
    border-bottom: 2px solid #003366;
}
.rsgb-col-title {
    font-size: 1rem;
    font-weight: 700;
    color: #003366;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.rsgb-badge {
    font-size: 0.65rem;
    font-weight: 700;
    background: #003366;
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.rsgb-more-link {
    font-size: 0.8rem;
    color: #C8102E;
    font-weight: 600;
    text-decoration: none;
}
.rsgb-more-link:hover { text-decoration: underline; }

/* National news — red left border style */
.rsgb-news-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}
.rsgb-news-item {
    border-left: 3px solid #C8102E;
    padding: 0.45rem 0.75rem;
    background: #fafafa;
    border-radius: 0 6px 6px 0;
}
.rsgb-news-item a {
    font-size: 0.88rem;
    font-weight: 600;
    color: #003366;
    text-decoration: none;
    display: block;
    line-height: 1.35;
    margin-bottom: 0.2rem;
}
.rsgb-news-item a:hover { color: #C8102E; }
.rsgb-news-date { font-size: 0.73rem; color: #999; }

/* Region 3 — compact date-badge style */
.rsgb-r3-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}
.rsgb-r3-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.rsgb-r3-item:last-child { border-bottom: none; }
.rsgb-r3-date {
    font-size: 0.7rem;
    color: white;
    background: #003366;
    padding: 0.2rem 0.45rem;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 0.1rem;
    line-height: 1.4;
}
.rsgb-r3-item a {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1a1a1a;
    text-decoration: none;
    line-height: 1.35;
}
.rsgb-r3-item a:hover { color: #C8102E; }

.rsgb-updated {
    font-size: 0.7rem;
    color: #bbb;
    margin-top: 0.75rem;
}
.rsgb-empty {
    color: #999;
    font-size: 0.88rem;
    padding: 0.5rem 0;
}
</style>

<div class="rsgb-grid">

    {{-- National News --}}
    <div>
        <div class="rsgb-col-head">
            <span class="rsgb-col-title">📰 National News</span>
            <a href="https://rsgb.org/main/news/" target="_blank" rel="noopener noreferrer" class="rsgb-more-link">rsgb.org →</a>
        </div>

        @if(empty($news['headlines']))
            <p class="rsgb-empty">No news available.</p>
        @else
            <ul class="rsgb-news-list">
                @foreach(array_slice($news['headlines'], 0, 5) as $item)
                    <li class="rsgb-news-item">
                        <a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer">{{ $item['title'] }}</a>
                        <span class="rsgb-news-date">{{ $item['date'] }}</span>
                    </li>
                @endforeach
            </ul>
            @if($news['updated_at'])
                <p class="rsgb-updated">Refreshed {{ \Carbon\Carbon::parse($news['updated_at'])->diffForHumans() }}</p>
            @endif
        @endif
    </div>

    {{-- Region 3 --}}
    <div>
        <div class="rsgb-col-head">
            <span class="rsgb-col-title">
                📍 {{ $zoneLabel }}
                <span class="rsgb-badge">Weekly</span>
            </span>
            <a href="{{ $zoneLink }}" target="_blank" rel="noopener noreferrer" class="rsgb-more-link">More →</a>
        </div>

        @if(empty($region3['headlines']))
            <p class="rsgb-empty">No updates available.</p>
        @else
            <ul class="rsgb-r3-list">
                @foreach(array_slice($region3['headlines'], 0, 5) as $item)
                    <li class="rsgb-r3-item">
                        <span class="rsgb-r3-date">{{ $item['date'] }}</span>
                        <a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer">{{ $item['title'] }}</a>
                    </li>
                @endforeach
            </ul>
            @if($region3['updated_at'])
                <p class="rsgb-updated">Refreshed {{ \Carbon\Carbon::parse($region3['updated_at'])->diffForHumans() }}</p>
            @endif
        @endif
    </div>

</div>
