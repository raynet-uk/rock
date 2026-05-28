@extends('layouts.app')

@section('title', 'Calendar')

@section('content')

<style>
/* ─── RAYNET BRAND TOKENS (Brand Book v2) ────────────────────────────────────
   Navy Blue     Pantone 295C  #003366
   White                       #FFFFFF
   Emergency Red Pantone 186C  #C8102E
   Light Grey                  #F2F2F2
   Headings: Arial Bold / Helvetica Neue Bold
   Body:     Arial Regular / Helvetica Neue Regular
─────────────────────────────────────────────────────────────────────────── */
:root {
    --navy:       #003366;
    --navy-mid:   #004080;
    --navy-faint: #e8eef5;
    --navy-deep:  #00234a;
    --red:        #C8102E;
    --red-faint:  #fdf0f2;
    --white:      #FFFFFF;
    --grey:       #F2F2F2;
    --grey-mid:   #dde2e8;
    --grey-dark:  #9aa3ae;
    --text:       #001f40;
    --text-mid:   #2d4a6b;
    --text-muted: #6b7f96;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.11);
    --shadow-lg: 0 12px 40px rgba(0,51,102,.16);
    --cell-h: 130px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--grey); color: var(--text); font-family: var(--font); font-size: 14px; min-height: 100vh; }

/* ─── HEADER ─── */
.rn-header {
    background: var(--navy); border-bottom: 4px solid var(--red);
    position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 10px rgba(0,0,0,.3);
}
.rn-header-inner {
    max-width: 1440px; margin: 0 auto; padding: 0 1.75rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.rn-brand { display: flex; align-items: center; gap: .85rem; padding: .75rem 0; }
.rn-logo { background: var(--red); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo span { font-size: 10px; font-weight: bold; color: var(--white); letter-spacing: .06em; text-align: center; line-height: 1.15; text-transform: uppercase; }
.rn-org { font-size: 14px; font-weight: bold; color: var(--white); letter-spacing: .04em; text-transform: uppercase; }
.rn-sub { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ─── PAGE BAND ─── */
.page-band { background: var(--white); border-bottom: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm); }
.page-band-inner {
    max-width: 1440px; margin: 0 auto; padding: 1rem 1.75rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.page-band-left {}
.page-eyebrow { font-size: 10px; font-weight: bold; color: var(--red); text-transform: uppercase; letter-spacing: .18em; margin-bottom: .25rem; display: flex; align-items: center; gap: .45rem; }
.page-eyebrow::before { content: ''; width: 14px; height: 2px; background: var(--red); display: inline-block; }
.cal-month-name { font-size: 20px; font-weight: bold; color: var(--navy); line-height: 1; }
.cal-month-sub  { font-size: 12px; color: var(--text-muted); margin-top: .25rem; }

/* Controls */
.cal-controls { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.nav-grp { display: inline-flex; align-items: stretch; border: 1px solid var(--grey-mid); overflow: hidden; background: var(--white); }
.nav-grp a {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .38rem 1rem; color: var(--text-mid); font-size: 12px; font-weight: bold;
    text-decoration: none; transition: all .12s; white-space: nowrap;
    border-right: 1px solid var(--grey-mid); text-transform: uppercase; letter-spacing: .03em;
}
.nav-grp a:last-child { border-right: none; }
.nav-grp a:hover { background: var(--navy-faint); color: var(--navy); }

.today-btn {
    display: inline-flex; align-items: center; padding: .38rem 1rem;
    border: 1px solid var(--grey-mid); background: var(--white); color: var(--text-mid);
    font-size: 12px; font-weight: bold; text-decoration: none; transition: all .12s;
    text-transform: uppercase; letter-spacing: .03em;
}
.today-btn:hover { border-color: var(--navy); color: var(--navy); background: var(--navy-faint); }

.export-btn {
    display: inline-flex; align-items: center; gap: .35rem; padding: .38rem 1rem;
    border: 1px solid rgba(0,51,102,.35); background: var(--navy-faint); color: var(--navy);
    font-size: 12px; font-weight: bold; text-decoration: none; transition: all .12s;
    text-transform: uppercase; letter-spacing: .03em;
}
.export-btn:hover { background: #d0ddf0; border-color: var(--navy); }

/* ─── WRAP ─── */
.wrap { max-width: 1440px; margin: 0 auto; padding: 1.25rem 1.75rem 4rem; }

/* ─── CALENDAR SHELL ─── */
.cal-shell {
    background: var(--white); border: 1px solid var(--grey-mid);
    border-top: 3px solid var(--navy); box-shadow: var(--shadow-md); overflow: hidden;
}

/* DOW header */
.cal-dow { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-bottom: 2px solid var(--red); }
.cal-dow-cell {
    padding: .6rem .75rem .5rem; font-size: 11px; font-weight: bold;
    text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.8);
    border-right: 1px solid rgba(255,255,255,.1); background: var(--navy);
}
.cal-dow-cell:last-child { border-right: none; }
.cal-dow-cell .ds { display: block; }
.cal-dow-cell .dr { font-size: 9px; opacity: .45; letter-spacing: .04em; margin-top: 2px; display: block; }
.cal-dow-cell.weekend { background: var(--navy-deep); color: rgba(255,255,255,.6); }

/* Week row */
.cal-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-bottom: 1px solid var(--grey-mid); }
.cal-week:last-child { border-bottom: none; }

/* Day cell */
.cal-day {
    position: relative; min-height: var(--cell-h);
    padding: .55rem .65rem .5rem; border-right: 1px solid var(--grey-mid);
    background: var(--white); transition: background .1s; overflow: visible;
}
.cal-day:last-child { border-right: none; }
.cal-day:hover { background: var(--navy-faint); }
.cal-day.weekend { background: var(--grey); }
.cal-day.weekend:hover { background: #e4eaf2; }
.cal-day.outside { opacity: .3; pointer-events: none; }
.cal-day.today { background: #eef4ff; }
.cal-day.today::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--red);
}

/* Date number */
.day-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .45rem; }
.day-num { font-size: 13px; font-weight: bold; color: var(--text-muted); line-height: 1; }
.cal-day.today .day-num {
    background: var(--red); color: var(--white);
    width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: bold; border-radius: 50%;
}
.today-pulse { width: 5px; height: 5px; border-radius: 50%; background: var(--red); animation: tp 2s ease-in-out infinite; }
@keyframes tp { 0%,100% { box-shadow: 0 0 0 0 rgba(200,16,46,.5); } 50% { box-shadow: 0 0 0 5px rgba(200,16,46,0); } }

/* Event bars */
.cal-evt {
    position: relative; display: block; margin-bottom: 3px;
    padding: 3px 7px; font-size: 11px; font-weight: bold; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    cursor: pointer; border-left: 3px solid currentColor; transition: filter .1s, transform .1s;
}
.cal-evt:hover { filter: brightness(.88); transform: translateX(2px); }
.span-start  { }
.span-middle { border-left-width: 0; padding-left: 10px; }
.span-end    { border-left-width: 0; padding-left: 10px; }
.evt-more    { display: inline-block; font-size: 10px; color: var(--text-muted); padding: 1px 4px; margin-top: 1px; font-weight: bold; }

/* Popover */
.evt-pop {
    display: none; position: absolute; z-index: 300;
    top: calc(100% + 8px); left: -2px; width: 260px;
    background: var(--white); border: 1px solid var(--grey-mid);
    box-shadow: var(--shadow-lg); pointer-events: none;
}
.cal-evt.flip .evt-pop { left: auto; right: -2px; }
.cal-evt:hover .evt-pop { display: block; pointer-events: auto; }
.pop-top  { height: 4px; }
.pop-inner { padding: .85rem 1rem; }
.pop-type-tag {
    display: inline-block; font-size: 10px; font-weight: bold; padding: 2px 8px;
    margin-bottom: .5rem; letter-spacing: .06em; text-transform: uppercase;
    border: 1px solid;
}
.pop-title  { font-size: 14px; font-weight: bold; color: var(--navy); margin-bottom: .5rem; line-height: 1.3; }
.pop-row    { display: flex; align-items: flex-start; gap: .4rem; font-size: 11px; color: var(--text-muted); margin-bottom: .25rem; line-height: 1.45; }
.pop-row-icon { flex-shrink: 0; }
.pop-foot   { padding: .55rem 1rem; border-top: 1px solid var(--grey-mid); background: var(--grey); }
.pop-link   { display: inline-flex; align-items: center; gap: .3rem; font-size: 11px; font-weight: bold; color: var(--navy); text-decoration: none; text-transform: uppercase; letter-spacing: .04em; }
.pop-link:hover { text-decoration: underline; }

/* ─── LEGEND ─── */
.cal-legend {
    display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1.1rem;
    margin-top: 1rem; padding: .75rem 1.1rem;
    background: var(--white); border: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm);
}
.legend-hd {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .14em; color: var(--text-muted); flex-shrink: 0;
}
.legend-item { display: inline-flex; align-items: center; gap: .4rem; font-size: 11px; font-weight: bold; color: var(--text-mid); }
.legend-swatch { width: 10px; height: 10px; flex-shrink: 0; }

/* ─── FOOTER ─── */
.cal-footer { display: flex; gap: 1.2rem; margin-top: 1rem; }
.cal-footer a { font-size: 12px; font-weight: bold; color: var(--navy); text-decoration: none; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid rgba(0,51,102,.25); padding-bottom: 1px; }
.cal-footer a:hover { opacity: .7; }

/* ─── ANIMATIONS ─── */
@keyframes fadeUp { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
.fade-in { animation: fadeUp .3s ease both; }

/* ─── RESPONSIVE ─── */
@media(max-width:900px) {
    :root { --cell-h: 90px; }
    .cal-evt { font-size: 9px; }
    .cal-dow-cell .dr { display: none; }
}
@media(max-width:600px) {
    :root { --cell-h: 64px; }
    .cal-evt { display: none; }
    .evt-more { display: block; font-size: 9px; }
    .page-band-inner { flex-direction: column; align-items: flex-start; }
}
</style>

{{-- ─── HEADER ─── --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Events Calendar</div>
            </div>
        </div>
    </div>
</header>

{{-- ─── PAGE BAND ── month + controls ─── --}}
<div class="page-band fade-in">
    <div class="page-band-inner">
        <div class="page-band-left">
            <div class="page-eyebrow">Viewing</div>
            <div class="cal-month-name">{{ $monthName }} {{ $year }}</div>
            <div class="cal-month-sub">Group training, exercises &amp; event support — {{ \App\Helpers\RaynetSetting::groupName() }}</div>
        </div>
        <div class="cal-controls">
            <div class="nav-grp">
                <a href="{{ route('calendar', ['year' => $prevYear, 'month' => sprintf('%02d', $prevMonth)]) }}">
                    ‹ {{ \Illuminate\Support\Carbon::createFromDate($prevYear, $prevMonth, 1)->format('M Y') }}
                </a>
                <a href="{{ route('calendar', ['year' => $nextYear, 'month' => sprintf('%02d', $nextMonth)]) }}">
                    {{ \Illuminate\Support\Carbon::createFromDate($nextYear, $nextMonth, 1)->format('M Y') }} ›
                </a>
            </div>
            <a href="{{ route('calendar') }}" class="today-btn">Today</a>
            <a href="{{ route('calendar.ics', ['year' => $year, 'month' => sprintf('%02d', $currentMonth->month)]) }}"
               class="export-btn">↓ Export .ics</a>
        </div>
    </div>
</div>

<div class="wrap">

    {{-- ─── CALENDAR SHELL ─── --}}
    <div class="cal-shell fade-in">

        {{-- Day-of-week headings --}}
        <div class="cal-dow">
            @foreach ([['Mon','day'],['Tue','sday'],['Wed','nesday'],['Thu','rsday'],['Fri','day'],['Sat','urday'],['Sun','day']] as $i => $d)
                <div class="cal-dow-cell {{ $i >= 5 ? 'weekend' : '' }}">
                    <span class="ds">{{ $d[0] }}</span>
                    <span class="dr">{{ $d[1] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Weeks --}}
        @foreach ($weeks as $week)
            <div class="cal-week">
                @foreach ($week as $day)
                    @php
                        /** @var \Illuminate\Support\Carbon $date */
                        $date      = $day['date'];
                        $isCurrent = $day['isCurrentMonth'];
                        $isToday   = $day['isToday'];
                        $dayEvts   = $day['events'];
                        $col       = $date->dayOfWeekIso;
                        $isWeekend = $col >= 6;
                        $maxShow   = 3;
                    @endphp

                    <div class="cal-day
                        {{ $isCurrent ? '' : 'outside' }}
                        {{ $isToday   ? 'today'   : '' }}
                        {{ $isWeekend ? 'weekend' : '' }}">

                        <div class="day-top">
                            <div class="day-num">{{ $date->day }}</div>
                            @if ($isToday)
                                <div class="today-pulse"></div>
                            @endif
                        </div>

                        @php $shown = 0; @endphp
                        @foreach ($dayEvts as $item)
                            @php
                                /** @var \App\Models\Event $ev */
                                $ev        = $item['event'];
                                $spanPart  = $item['span'];
                                $type      = $ev->type;
                                $colour    = $type && $type->colour ? $type->colour : '#003366';
                                $spanClass = match($spanPart) {
                                    'start'  => 'span-start',
                                    'middle' => 'span-middle',
                                    'end'    => 'span-end',
                                    default  => '',
                                };
                                $shown++;
                            @endphp

                            @if ($shown <= $maxShow)
                                <div class="cal-evt {{ $spanClass }} {{ $col >= 6 ? 'flip' : '' }}"
                                     style="background:{{ $colour }}18; color:{{ $colour }};">

                                    @if ($spanPart !== 'middle' && $spanPart !== 'end')
                                        {{ $ev->title }}
                                    @endif

                                    <div class="evt-pop">
                                        <div class="pop-top" style="background:{{ $colour }};"></div>
                                        <div class="pop-inner">
                                            @if ($type)
                                                <span class="pop-type-tag"
                                                      style="background:{{ $colour }}15; border-color:{{ $colour }}55; color:{{ $colour }};">
                                                    {{ $type->name }}
                                                </span>
                                            @endif
                                            <div class="pop-title">{{ $ev->title }}</div>
                                            <div class="pop-row">
                                                <span class="pop-row-icon">📅</span>
                                                <span>{{ $ev->displayDate() }}@if($ev->ends_at)<br>→ {{ $ev->ends_at->format('D j M, H:i') }}@endif</span>
                                            </div>
                                            @if ($ev->location)
                                                <div class="pop-row">
                                                    <span class="pop-row-icon">📍</span>
                                                    <span>{{ $ev->location }}</span>
                                                </div>
                                            @endif
                                            @if ($ev->description)
                                                <div class="pop-row">
                                                    <span class="pop-row-icon">📋</span>
                                                    <span>{{ \Illuminate\Support\Str::limit($ev->description, 90) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="pop-foot">
                                            <a href="{{ $ev->url() }}" class="pop-link">View full details →</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (count($dayEvts) > $maxShow)
                            <span class="evt-more">+{{ count($dayEvts) - $maxShow }} more</span>
                        @endif

                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- ─── LEGEND ─── --}}
    @php
        $legendTypes = collect($weeks)
            ->flatten(1)
            ->pluck('events')->flatten(1)
            ->pluck('event')
            ->filter(fn($e) => $e->type && $e->type->colour)
            ->map(fn($e) => $e->type)
            ->unique('id')
            ->values();
    @endphp
    @if ($legendTypes->isNotEmpty())
        <div class="cal-legend fade-in">
            <span class="legend-hd">Event types:</span>
            @foreach ($legendTypes as $lt)
                <span class="legend-item">
                    <span class="legend-swatch" style="background:{{ $lt->colour }};"></span>
                    {{ $lt->name }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="cal-footer fade-in">
        <a href="{{ route('events.index') }}">View full event list →</a>
    </div>

</div>

@endsection