@extends('layouts.app')

@section('title', 'Calendar')

@section('content')

@php
    $prevUrl = route('calendar', [
        'year'  => $prevMonth->format('Y'),
        'month' => $prevMonth->format('m'),
    ]);
    $nextUrl = route('calendar', [
        'year'  => $nextMonth->format('Y'),
        'month' => $nextMonth->format('m'),
    ]);
@endphp

<style>
/* ─── RAYNET BRAND TOKENS (Brand Book v2) ─────────────────────────────────
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
    --green:      #1a6b3c;
    --green-bg:   #eef7f2;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.11);
    --shadow-lg: 0 12px 40px rgba(0,51,102,.16);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { background: var(--grey); color: var(--text); font-family: var(--font); font-size: 14px; min-height: 100vh; }

/* ─── HEADER ─── */
.rn-header {
    background: var(--navy); border-bottom: 4px solid var(--red);
    position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 10px rgba(0,0,0,.3);
}
.rn-header-inner {
    max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.rn-brand { display: flex; align-items: center; gap: .85rem; padding: .75rem 0; }
.rn-logo { background: var(--red); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo span { font-size: 10px; font-weight: bold; color: var(--white); letter-spacing: .06em; text-align: center; line-height: 1.15; text-transform: uppercase; }
.rn-org { font-size: 14px; font-weight: bold; color: var(--white); letter-spacing: .04em; text-transform: uppercase; }
.rn-sub { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }
.rn-badge {
    display: flex; align-items: center; gap: .5rem; padding: .3rem .85rem;
    border: 1px solid rgba(255,255,255,.2); font-size: 11px; font-weight: bold;
    color: rgba(255,255,255,.7); text-transform: uppercase; letter-spacing: .1em;
}
.online-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #22d47d;
    box-shadow: 0 0 0 3px rgba(34,212,125,.2); animation: pulse-g 2s infinite;
}
@keyframes pulse-g { 0%,100% { box-shadow: 0 0 0 3px rgba(34,212,125,.2); } 50% { box-shadow: 0 0 0 6px rgba(34,212,125,.04); } }

/* ─── PAGE BAND ─── */
.page-band { background: var(--white); border-bottom: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm); }
.page-band-inner {
    max-width: 1200px; margin: 0 auto; padding: 1.1rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.page-eyebrow { font-size: 10px; font-weight: bold; color: var(--red); text-transform: uppercase; letter-spacing: .18em; margin-bottom: .25rem; display: flex; align-items: center; gap: .45rem; }
.page-eyebrow::before { content: ''; width: 14px; height: 2px; background: var(--red); display: inline-block; }
.cal-month-name { font-size: 20px; font-weight: bold; color: var(--navy); line-height: 1; }
.cal-month-sub  { font-size: 12px; color: var(--text-muted); margin-top: .25rem; }

/* Nav controls */
.cal-controls { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.nav-grp { display: inline-flex; align-items: stretch; border: 1px solid var(--grey-mid); overflow: hidden; }
.nav-grp a {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .38rem 1rem; color: var(--text-mid); font-size: 12px; font-weight: bold;
    text-decoration: none; transition: all .12s; white-space: nowrap;
    border-right: 1px solid var(--grey-mid); text-transform: uppercase; letter-spacing: .03em;
}
.nav-grp a:last-child { border-right: none; }
.nav-grp a:hover { background: var(--navy-faint); color: var(--navy); }
.ics-btn {
    display: inline-flex; align-items: center; gap: .35rem; padding: .38rem 1rem;
    border: 1px solid #b8ddc9; background: var(--green-bg); color: var(--green);
    font-size: 12px; font-weight: bold; text-decoration: none; transition: all .12s;
    text-transform: uppercase; letter-spacing: .03em;
}
.ics-btn:hover { background: #d6ede3; border-color: var(--green); }

/* ─── WRAP ─── */
.wrap { max-width: 1200px; margin: 0 auto; padding: 1.25rem 1.5rem 4rem; }

/* ─── CALENDAR SHELL ─── */
.cal-shell {
    background: var(--white); border: 1px solid var(--grey-mid);
    border-top: 3px solid var(--navy); box-shadow: var(--shadow-md); overflow: hidden;
    margin-bottom: 1.25rem;
}

/* Weekday headers */
.cal-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); border-bottom: 2px solid var(--red); }
.cal-weekday {
    text-align: center; padding: .55rem 0;
    font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em;
    color: rgba(255,255,255,.8); background: var(--navy);
    border-right: 1px solid rgba(255,255,255,.1);
}
.cal-weekday:last-child { border-right: none; }
.cal-weekday.weekend { background: var(--navy-deep); color: rgba(255,255,255,.55); }

/* Grid */
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }

/* Day cell */
.cal-day {
    min-height: 7rem; border-right: 1px solid var(--grey-mid);
    border-bottom: 1px solid var(--grey-mid);
    padding: .5rem .55rem .45rem; position: relative; transition: background .1s;
}
.cal-day:nth-child(7n)  { border-right: none; }
.cal-last-row .cal-day  { border-bottom: none; }
.cal-day:hover          { background: var(--navy-faint); }
.cal-day.out-of-month   { opacity: .3; pointer-events: none; }
.cal-day.is-today       { background: #eef4ff; }
.cal-day.is-today::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--red);
}
.cal-day.weekend        { background: var(--grey); }
.cal-day.weekend:hover  { background: #e4eaf2; }
.cal-day.is-today.weekend { background: #eef4ff; }

/* Date number */
.day-num-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: .4rem; }
.day-num { font-size: 13px; font-weight: bold; color: var(--text-muted); line-height: 1; }
.cal-day.is-today .day-num {
    background: var(--red); color: var(--white);
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: bold;
}
.today-chip {
    font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em;
    color: var(--red); background: var(--red-faint);
    border: 1px solid rgba(200,16,46,.2); padding: 1px 6px;
}

/* Event pill */
.event-pill-wrap { position: relative; margin-bottom: 3px; }
.event-pill {
    display: flex; align-items: center; gap: .25rem;
    padding: 2px 7px; font-size: 11px; font-weight: bold; line-height: 1.35;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 100%; cursor: default; text-decoration: none;
    border-left: 3px solid currentColor; transition: filter .12s, transform .12s;
}
.event-pill:hover { filter: brightness(.88); transform: translateX(2px); }
.event-pill-dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; background: currentColor; opacity: .8; }

/* Tooltip */
.event-tooltip {
    display: none; position: absolute; top: calc(100% + 5px); left: 0; z-index: 100;
    min-width: 220px; max-width: 280px; padding: .75rem .85rem;
    background: var(--white); border: 1px solid var(--grey-mid);
    box-shadow: var(--shadow-lg); pointer-events: none;
}
.cal-day:nth-child(7n-1) .event-tooltip,
.cal-day:nth-child(7n)   .event-tooltip { left: auto; right: 0; }
.event-pill-wrap:hover .event-tooltip { display: block; }

.tooltip-type  { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em; margin-bottom: .35rem; border: 1px solid; display: inline-block; padding: 1px 7px; }
.tooltip-title { font-size: 13px; font-weight: bold; color: var(--navy); margin-bottom: .3rem; line-height: 1.35; }
.tooltip-meta  { font-size: 11px; color: var(--text-muted); margin-bottom: .15rem; font-weight: bold; }
.tooltip-desc  { font-size: 11px; color: var(--text-muted); margin-top: .3rem; line-height: 1.55; }

/* ─── TABLET ─── */
@media(max-width:780px) {
    .page-band-inner { flex-direction: column; align-items: flex-start; gap: .6rem; }
    .cal-controls { width: 100%; }
    .nav-grp { flex: 1; }
    .nav-grp a { flex: 1; justify-content: center; padding: .45rem .5rem; }
    .ics-btn { padding: .45rem .75rem; }
    .cal-day { min-height: 5.5rem; padding: .35rem .4rem; }
    .event-pill { font-size: 10px; padding: 2px 5px; }
}

/* ─── MOBILE — agenda view replaces grid ─── */
@media(max-width:540px) {
    /* Hide the grid calendar entirely on small phones */
    .cal-shell { display: none; }

    /* Show a clean agenda-style list instead */
    .mobile-agenda { display: block !important; }

    .page-band-inner { flex-direction: column; align-items: flex-start; }
    .cal-controls { width: 100%; flex-direction: column; }
    .nav-grp { width: 100%; }
    .nav-grp a { flex: 1; justify-content: center; text-align: center; font-size: 13px; padding: .6rem .5rem; }
    .ics-btn { width: 100%; justify-content: center; padding: .55rem; }

    .upcoming-item { gap: .6rem; }
    .upcoming-date-box { min-width: 40px; }
    .upcoming-date-day { font-size: 16px; }
}

/* ─── UPCOMING CARD ─── */
.upcoming-card {
    background: var(--white); border: 1px solid var(--grey-mid);
    border-top: 3px solid var(--navy); box-shadow: var(--shadow-sm);
}
.upcoming-head {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1.1rem; border-bottom: 1px solid var(--grey-mid); background: var(--grey);
}
.upcoming-head-icon {
    width: 32px; height: 32px; background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0; color: var(--white);
}
.upcoming-head-title { font-size: 13px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .04em; }
.upcoming-head-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; font-weight: bold; }

.upcoming-item {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: .75rem 1.1rem; border-bottom: 1px solid var(--grey-mid);
    text-decoration: none; color: var(--text); transition: background .1s;
}
.upcoming-item:last-child { border-bottom: none; }
.upcoming-item:hover { background: var(--navy-faint); }

.upcoming-date-box {
    min-width: 44px; text-align: center; padding: .3rem .4rem;
    border: 1px solid var(--grey-mid); flex-shrink: 0;
    display: flex; flex-direction: column; align-items: center; background: var(--grey);
}
.upcoming-date-day   { font-size: 18px; font-weight: bold; color: var(--navy); line-height: 1; }
.upcoming-date-month { font-size: 10px; font-weight: bold; text-transform: uppercase; color: var(--text-muted); margin-top: 2px; letter-spacing: .06em; }

.upcoming-content { flex: 1; min-width: 0; }
.upcoming-title { font-size: 14px; font-weight: bold; color: var(--navy); margin-bottom: .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.upcoming-sub   { font-size: 11px; color: var(--text-muted); font-weight: bold; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }

.type-pill {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: 1px 7px; font-size: 10px; font-weight: bold;
    border: 1px solid; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
}

.upcoming-empty { padding: 2rem 1.1rem; text-align: center; font-size: 13px; color: var(--text-muted); }

/* ─── ANIMATIONS ─── */
@keyframes fadeUp { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
.fade-in { animation: fadeUp .3s ease both; }
</style>

{{-- ─── HEADER ─── --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
            </div>
        </div>
        <div class="rn-badge">
            <div class="online-dot"></div>
            Event Calendar
        </div>
    </div>
</header>

{{-- ─── PAGE BAND ─── --}}
<div class="page-band fade-in">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Calendar</div>
            <div class="cal-month-name">{{ $currentMonth->format('F Y') }}</div>
            <div class="cal-month-sub">Public training, exercises and event support for {{ \App\Helpers\RaynetSetting::groupName() }}. Hover any event for details.</div>
        </div>
        <div class="cal-controls">
            <div class="nav-grp">
                <a href="{{ $prevUrl }}">← {{ $prevMonth->format('M Y') }}</a>
                <a href="{{ $nextUrl }}">{{ $nextMonth->format('M Y') }} →</a>
            </div>
            <a href="{{ $icsUrl }}" class="ics-btn">↓ Export .ics</a>
        </div>
    </div>
</div>

<div class="wrap">

    {{-- ─── CALENDAR ─── --}}
    <div class="cal-shell fade-in">

        <div class="cal-weekdays">
            @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $d)
                <div class="cal-weekday {{ $i >= 5 ? 'weekend' : '' }}">{{ $d }}</div>
            @endforeach
        </div>

        @foreach ($weeks as $weekIdx => $week)
            @php $isLastRow = $weekIdx === count($weeks) - 1; @endphp
            <div class="cal-grid {{ $isLastRow ? 'cal-last-row' : '' }}">
                @foreach ($week as $day)
                    @php
                        /** @var \Illuminate\Support\Carbon $date */
                        $date      = $day['date'];
                        $inMonth   = $day['in_month'];
                        $isToday   = $day['is_today'];
                        $dayEvents = $day['events'];
                        $col       = $date->dayOfWeekIso;
                        $isWeekend = $col >= 6;
                    @endphp

                    <div class="cal-day
                        {{ !$inMonth   ? 'out-of-month' : '' }}
                        {{ $isToday    ? 'is-today'     : '' }}
                        {{ $isWeekend  ? 'weekend'      : '' }}">

                        <div class="day-num-row">
                            <div class="day-num">{{ $date->format('j') }}</div>
                            @if ($isToday) <div class="today-chip">Today</div> @endif
                        </div>

                        <div class="events-row">
                            @foreach ($dayEvents as $event)
                                @php
                                    $type   = $event->eventType ?? $event->type ?? null;
                                    $colour = $type?->colour ?? '#003366';
                                @endphp
                                <div class="event-pill-wrap">
                                    <a href="{{ $event->url() }}" class="event-pill"
                                       style="background:{{ $colour }}18; color:{{ $colour }};">
                                        <span class="event-pill-dot"></span>
                                        <span>{{ $type?->name ?? 'Event' }}</span>
                                    </a>
                                    <div class="event-tooltip">
                                        @if ($type)
                                            <div class="tooltip-type"
                                                 style="background:{{ $colour }}15; border-color:{{ $colour }}55; color:{{ $colour }};">
                                                {{ $type->name }}
                                            </div>
                                        @endif
                                        <div class="tooltip-title">{{ $event->title }}</div>
                                        @if($event->supporting_group === '__OWN__')
                                            <div style="display:inline-flex;align-items:center;gap:.3rem;margin:.2rem 0 .3rem;padding:.15rem .55rem;background:rgba(0,51,102,.08);border:1px solid rgba(0,51,102,.25);border-radius:999px;font-size:.72rem;font-weight:700;color:#003366;">📡 {{ \App\Helpers\RaynetSetting::groupName() }}</div>
                                        @elseif($event->supporting_group)
                                            <div style="display:inline-flex;align-items:center;gap:.3rem;margin:.2rem 0 .3rem;padding:.15rem .55rem;background:rgba(200,16,46,.08);border:1px solid rgba(200,16,46,.25);border-radius:999px;font-size:.72rem;font-weight:700;color:#C8102E;">🤝 {{ $event->supporting_group }}</div>
                                        @endif
                                        <div class="tooltip-meta">
                                            📅 {{ $event->displayDate() }}
                                            @if ($event->ends_at)
                                                → {{ $event->ends_at->format('j M Y, H:i') }}
                                            @endif
                                        </div>
                                        @if ($event->location)
                                            <div class="tooltip-meta">📍 {{ $event->location }}</div>
                                        @endif
                                        @if ($event->description)
                                            <div class="tooltip-desc">{{ Str::limit($event->description, 120) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

    </div>{{-- /cal-shell --}}
{{-- ─── MOBILE AGENDA (replaces grid on small screens) ─── --}}
<div class="mobile-agenda" style="display:none; margin-bottom:1.25rem;">
    <div class="upcoming-card fade-in">
        <div class="upcoming-head">
            <div class="upcoming-head-icon">📅</div>
            <div>
                <div class="upcoming-head-title">{{ $currentMonth->format('F Y') }}</div>
                <div class="upcoming-head-sub">All events this month</div>
            </div>
        </div>
        @php
            $agendaEvents = collect($weeks)->flatten(1)->pluck('events')->flatten()->filter()->unique('id')->sortBy('starts_at');
        @endphp
        @forelse ($agendaEvents as $event)
            @php
                $type   = $event->eventType ?? $event->type ?? null;
                $colour = $type?->colour ?? '#003366';
            @endphp
            <a href="{{ $event->url() }}" class="upcoming-item">
                <div class="upcoming-date-box" style="border-color:{{ $colour }}55;">
                    <div class="upcoming-date-day" style="color:{{ $colour }};">{{ $event->starts_at->format('d') }}</div>
                    <div class="upcoming-date-month">{{ $event->starts_at->format('M') }}</div>
                </div>
                <div class="upcoming-content">
                    <div class="upcoming-title">{{ $event->title }}</div>
                    <div class="upcoming-sub">
                        @if ($event->location) 📍 {{ $event->location }} &nbsp; @endif
                        @if ($type)
                            <span class="type-pill" style="background:{{ $colour }}15; border-color:{{ $colour }}55; color:{{ $colour }};">{{ $type->name }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="upcoming-empty">No events this month.</div>
        @endforelse
    </div>
</div>
    {{-- ─── UPCOMING LIST ─── --}}
@php
    $allEvents    = collect($weeks)->flatten(1)->pluck('events')->flatten()->filter()->unique('id')->sortBy('starts_at');
    $futureEvents = $allEvents->filter(fn($e) => $e->starts_at->gte(now()->startOfDay()));
@endphp

    @if ($futureEvents->isNotEmpty())
        <div class="upcoming-card fade-in">
            <div class="upcoming-head">
                <div class="upcoming-head-icon">📋</div>
                <div>
                    <div class="upcoming-head-title">Events This Month</div>
                    <div class="upcoming-head-sub">
                        {{ $currentMonth->format('F Y') }} · {{ $futureEvents->count() }} {{ Str::plural('event', $futureEvents->count()) }} remaining
                    </div>
                </div>
            </div>

            @foreach ($futureEvents as $event)
                @php
                    $type   = $event->eventType ?? $event->type ?? null;
                    $colour = $type?->colour ?? null;
                @endphp
                <a href="{{ $event->url() }}" class="upcoming-item">
                    <div class="upcoming-date-box"
                         style="{{ $colour ? 'border-color:' . $colour . '55;' : '' }}">
                        <div class="upcoming-date-day"
                             style="{{ $colour ? 'color:' . $colour . ';' : '' }}">
                            {{ $event->starts_at->format('d') }}
                        </div>
                        <div class="upcoming-date-month">
                            {{ $event->starts_at->format('M') }}
                        </div>
                    </div>
                    <div class="upcoming-content">
                        <div class="upcoming-title">{{ $event->title }}</div>
                        <div class="upcoming-sub">
                            @if ($event->location) 📍 {{ $event->location }} @endif
                            @if ($type)
                                @if ($colour)
                                    <span class="type-pill"
                                          style="background:{{ $colour }}15; border-color:{{ $colour }}55; color:{{ $colour }};">
                                        {{ $type->name }}
                                    </span>
                                @else
                                    <span class="type-pill"
                                          style="background:var(--navy-faint); border-color:rgba(0,51,102,.2); color:var(--navy);">
                                        {{ $type->name }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>{{-- /wrap --}}

@endsection