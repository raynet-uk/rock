@extends('layouts.app')
@section('title', 'Members Hub')
@section('content')

@php
    $roleColour = $operator['role_colour'] ?? null;
    $alertStatus = $alertStatus ?? null;
    $meta = $alertStatus?->meta();
    $level = $alertStatus->level ?? 5;
    $colour = $meta['colour'] ?? '#22c55e';
    $textColour = '#fff';
    if (in_array($level, [1, 2, 4], true)) { $textColour = '#0b1120'; }

    use Carbon\Carbon;
    $now = Carbon::now();
    $yearStart = $now->month >= 9
        ? Carbon::create($now->year, 9, 1)
        : Carbon::create($now->year - 1, 9, 1);
    $yearEnd   = $yearStart->copy()->addYear()->subDay();
    $yearLabel = $yearStart->format('M Y') . ' – ' . $yearEnd->format('M Y');
    $me         = auth()->user();
    $attended   = (bool)($me->attended_event_this_year ?? false);
    $eventsCount= (int)($me->events_attended_this_year ?? 0);
    $volHours   = (float)($me->volunteering_hours_this_year ?? 0);
    $barPct     = $eventsCount > 0 ? min(100, round(($eventsCount / max($eventsCount, 10)) * 100)) : 0;

    // ── DMR Network permissions ────────────────────────────────────────────
    // Uses hasDirectPermission() to bypass Gate::before super-admin rule
    $hasDmrDashboard = auth()->user()->hasDirectPermission('view dmr dashboard')
        || auth()->user()->roles->flatMap->permissions->contains('name', 'view dmr dashboard');
    $hasDmrMasters = auth()->user()->hasDirectPermission('view dmr masters')
        || auth()->user()->roles->flatMap->permissions->contains('name', 'view dmr masters');
    $hasDmrAny = $hasDmrDashboard || $hasDmrMasters;
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
:root {
    --navy:       #003366;
    --navy-deep:  #001f40;
    --navy-mid:   #004080;
    --navy-faint: #e8eef5;
    --navy-soft:  #d0dcea;
    --red:        #C8102E;
    --red-faint:  rgba(200,16,46,.08);
    --teal:       #0288d1;
    --teal-faint: rgba(2,136,209,.08);
    --green:      #1a7a3c;
    --green-faint:rgba(26,122,60,.08);
    --amber:      #b45309;
    --white:      #ffffff;
    --bg:         #f0f4f8;
    --border:     #dce4ee;
    --border-soft:#e8eef5;
    --text:       #001f40;
    --text-mid:   #2d4a6b;
    --muted:      #6b7f96;
    --font-sans:  Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --font-mono:  Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-xs:  0 1px 2px rgba(0,31,64,.06);
    --shadow-sm:  0 2px 8px rgba(0,31,64,.08);
    --shadow-md:  0 4px 20px rgba(0,31,64,.10);
    --shadow-lg:  0 8px 32px rgba(0,31,64,.12);
    --radius:     10px;
    --radius-sm:  6px;
    --transition: all .18s ease;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-sans);
    font-size: 14px;
    line-height: 1.6;
    min-height: 100vh;
}

/* ── PAGE SHELL ── */
.hub-shell {
    display: grid;
    grid-template-columns: 260px 1fr;
    grid-template-rows: auto 1fr;
    min-height: 100vh;
}
@media(max-width: 900px) { .hub-shell { grid-template-columns: 1fr; } }

/* ── TOP BAR ── */
.hub-topbar {
    grid-column: 1 / -1;
    background: var(--navy-deep);
    border-bottom: 3px solid var(--red);
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 58px;
    gap: 1rem;
    position: sticky;
    top: 60px;
    z-index: 200;
    box-shadow: 0 2px 16px rgba(0,0,0,.25);
}
.topbar-brand {
    display: flex;
    align-items: center;
    gap: .9rem;
    flex-shrink: 0;
}
.topbar-logo {
    width: 36px; height: 36px;
    background: var(--red);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-mono);
    font-size: 9px; font-weight: 600;
    color: #fff; letter-spacing: .06em;
    text-align: center; line-height: 1.2;
    text-transform: uppercase; flex-shrink: 0;
}
.topbar-name { font-size: 14px; font-weight: 600; color: #fff; letter-spacing: .04em; text-transform: uppercase; }
.topbar-sub { font-size: 10px; color: rgba(255,255,255,.45); letter-spacing: .08em; text-transform: uppercase; margin-top: 1px; }

.topbar-nav {
    display: flex;
    align-items: center;
    gap: .25rem;
}
.topbar-nav a {
    color: rgba(255,255,255,.6);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    padding: .35rem .75rem;
    letter-spacing: .04em;
    transition: var(--transition);
    border-bottom: 2px solid transparent;
}
.topbar-nav a:hover, .topbar-nav a.active { color: #fff; border-bottom-color: var(--teal); }
@media(max-width: 700px) { .topbar-nav { display: none; } }

.topbar-user {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .4rem .75rem .4rem .4rem;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    text-decoration: none;
    transition: var(--transition);
    flex-shrink: 0;
}
.topbar-user:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.2); }
.topbar-av {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: var(--navy-mid);
    border: 2px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; text-transform: uppercase; flex-shrink: 0;
}
.topbar-user-name { font-size: 12px; font-weight: 600; color: #fff; }
.topbar-user-role { font-size: 10px; color: rgba(255,255,255,.45); font-family: var(--font-mono); }

/* ── SIDEBAR ── */
.hub-sidebar {
    background: var(--navy-deep);
    border-right: 1px solid rgba(255,255,255,.08);
    padding: 1.5rem 0 2rem;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: sticky;
    top: 118px;
    height: calc(100vh - 58px);
    overflow-y: auto;
}
@media(max-width: 900px) { .hub-sidebar { display: none; } }

.sidebar-section-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .15em;
    text-transform: uppercase;
    color: rgba(255,255,255,.25);
    padding: 1rem 1.25rem .4rem;
}
.sidebar-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem 1.25rem;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,.65);
    text-decoration: none;
    transition: var(--transition);
    border-left: 3px solid transparent;
    margin: 0 0;
}
.sidebar-link:hover { color: #fff; background: rgba(255,255,255,.05); border-left-color: rgba(255,255,255,.2); }
.sidebar-link.active { color: #fff; background: rgba(2,136,209,.12); border-left-color: var(--teal); }
.sidebar-link-icon { font-size: 14px; width: 18px; text-align: center; flex-shrink: 0; opacity: .8; }
.sidebar-badge {
    margin-left: auto;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    background: var(--red);
    color: #fff;
    border-radius: 999px;
    font-family: var(--font-mono);
}

/* Alert level in sidebar */
.sidebar-alert {
    margin: 1rem 1rem;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
}
.sidebar-alert-head {
    padding: .5rem .85rem;
    background: rgba(255,255,255,.07);
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sidebar-alert-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: rgba(255,255,255,.4); }
.sidebar-alert-level-num { font-size: 18px; font-weight: 700; font-family: var(--font-mono); }
.sidebar-alert-body { padding: .75rem .85rem; }
.sidebar-alert-title { font-size: 12px; font-weight: 700; margin-bottom: .2rem; }
.sidebar-alert-desc { font-size: 11px; line-height: 1.5; opacity: .8; }

/* ── MAIN CONTENT ── */
.hub-main { padding: 1.75rem 1.5rem 3rem; overflow-x: hidden; }
@media(max-width: 600px) { .hub-main { padding: 1.25rem 1rem 3rem; } }

/* ── SECTION HEADING ── */
.sec-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    gap: 1rem;
}
.sec-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .14em;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: .5rem;
}
.sec-title::before { content: ''; width: 3px; height: 14px; background: var(--red); display: inline-block; }

/* ── WELCOME BANNER ── */
.welcome-banner {
    background: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy-mid) 100%);
    border: 1px solid rgba(255,255,255,.08);
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.welcome-banner::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(200,16,46,.08);
    pointer-events: none;
}
.welcome-banner::after {
    content: '';
    position: absolute;
    bottom: -30px; left: 20%;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(2,136,209,.06);
    pointer-events: none;
}
.welcome-text { position: relative; z-index: 1; }
.welcome-greeting { font-size: 11px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; color: var(--teal); margin-bottom: .3rem; font-family: var(--font-mono); }
.welcome-name { font-size: 1.6rem; font-weight: 700; color: #fff; line-height: 1.2; margin-bottom: .35rem; }
.welcome-meta { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.welcome-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: 11px; font-weight: 600;
    padding: .25rem .75rem;
    border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.75);
    font-family: var(--font-mono);
    letter-spacing: .04em;
}
.welcome-callsign { color: var(--teal); border-color: rgba(2,136,209,.4); background: rgba(2,136,209,.08); }
.welcome-actions { display: flex; align-items: center; gap: .6rem; flex-shrink: 0; position: relative; z-index: 1; }
.wb-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.1rem;
    font-family: var(--font-sans); font-size: 12px; font-weight: 600;
    text-decoration: none; transition: var(--transition);
    letter-spacing: .04em; text-transform: uppercase;
    border: 1px solid;
    cursor: pointer;
}
.wb-btn-primary { background: var(--teal); border-color: var(--teal); color: #fff; }
.wb-btn-primary:hover { background: #0277bd; }
.wb-btn-ghost { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.2); color: rgba(255,255,255,.8); }
.wb-btn-ghost:hover { background: rgba(255,255,255,.12); color: #fff; }

/* ── STAT STRIP ── */
.stat-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    margin-bottom: 1.75rem;
}
@media(max-width:1100px){ .stat-strip { grid-template-columns: repeat(3,1fr); } }
@media(max-width:900px){ .stat-strip { grid-template-columns: repeat(3,1fr); gap:.65rem; } }
@media(max-width:480px){ .stat-strip { grid-template-columns: repeat(2,1fr); gap:.5rem; } }

.stat-tile {
    background: var(--white);
    border: 1px solid var(--border);
    padding: 1rem 1.15rem .9rem;
    position: relative;
    box-shadow: var(--shadow-xs);
    transition: var(--transition);
    border-top: 3px solid var(--navy);
}
.stat-tile:hover { box-shadow: var(--shadow-sm); transform: translateY(-1px); }
.stat-tile.t-green  { border-top-color: var(--green); }
.stat-tile.t-teal   { border-top-color: var(--teal); }
.stat-tile.t-red    { border-top-color: var(--red); }
.stat-tile.t-amber  { border-top-color: var(--amber); }
.stat-tile-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .13em; color: var(--muted); margin-bottom: .35rem; }
.stat-tile-value { font-size: 28px; font-weight: 700; line-height: 1; color: var(--navy); font-family: var(--font-mono); }
.stat-tile.t-green  .stat-tile-value { color: var(--green); }
.stat-tile.t-teal   .stat-tile-value { color: var(--teal); }
.stat-tile.t-red    .stat-tile-value { color: var(--red); }
.stat-tile.t-amber  .stat-tile-value { color: var(--amber); }
.stat-tile-sub { font-size: 11px; color: var(--muted); margin-top: .3rem; }
/* ── NOTIFICATIONS CARD ── */
.notif-hub-list { display: flex; flex-direction: column; }
.notif-hub-item {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: .85rem 1.15rem;
    border-bottom: 1px solid var(--border-soft);
    transition: background .1s;
    position: relative;
}
.notif-hub-item:last-child { border-bottom: none; }
.notif-hub-item:hover { background: var(--navy-faint); }
.notif-hub-item.unread { background: #f5f8ff; }
.notif-hub-item.unread:hover { background: #edf2ff; }
.notif-hub-item.unread::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px;
}

.nhi-priority {
    width: 36px; height: 36px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; border-radius: 4px;
}
.nhi-body { flex: 1; min-width: 0; }
.nhi-title {
    font-size: 13px; font-weight: 700; color: var(--text);
    margin-bottom: .2rem; line-height: 1.3;
}
.nhi-text {
    font-size: 12px; color: var(--text-mid); line-height: 1.5;
    overflow: hidden; text-overflow: ellipsis;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.nhi-meta {
    display: flex; align-items: center; gap: .5rem;
    margin-top: .35rem; flex-wrap: wrap;
}
.nhi-time { font-size: 10px; color: var(--muted); font-family: var(--font-mono); }
.nhi-unread-dot {
    width: 7px; height: 7px; border-radius: 50%;
    flex-shrink: 0; margin-top: 5px;
}
.nhi-priority-chip {
    font-size: 9px; font-weight: 700; padding: 1px 6px; border: 1px solid;
    text-transform: uppercase; letter-spacing: .05em;
}

.notif-hub-empty {
    padding: 2.5rem 1rem; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: .65rem;
}
.notif-hub-empty-icon { font-size: 2rem; opacity: .2; }
.notif-hub-empty-text { font-size: 13px; color: var(--muted); }

.notif-hub-loading {
    padding: 2rem 1rem; text-align: center;
}
.notif-loading-bar {
    width: 100%; height: 4px; background: var(--border);
    overflow: hidden; margin: 0 auto .75rem; max-width: 200px;
}
.notif-loading-fill {
    height: 100%; background: var(--navy);
    width: 40%; animation: loadSlide 1.2s ease-in-out infinite;
}
@keyframes loadSlide {
    0% { transform: translateX(-150%); }
    100% { transform: translateX(400%); }
}
.notif-loading-text { font-size: 12px; color: var(--muted); }

.notif-hub-actions {
    display: flex; align-items: center; justify-content: space-between;
    padding: .5rem 1.15rem;
    border-top: 1px solid var(--border-soft);
    background: #fafbfd;
    gap: .5rem; flex-wrap: wrap;
}
.notif-mark-all-btn {
    font-size: 11px; font-weight: 700; color: var(--navy);
    background: none; border: none; cursor: pointer;
    font-family: var(--font-sans); text-transform: uppercase;
    letter-spacing: .07em; padding: 0; transition: color .15s;
}
.notif-mark-all-btn:hover { color: var(--teal); }
.notif-unread-badge {
    display: inline-flex; align-items: center;
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; background: var(--red); color: #fff; border-radius: 999px;
    font-family: var(--font-mono);
}

/* Priority colours for hub notifications */
.p-colour-1 { background: rgba(34,197,94,.1); }
.p-colour-2 { background: rgba(2,136,209,.1); }
.p-colour-3 { background: rgba(245,158,11,.1); }
.p-colour-4 { background: rgba(234,88,12,.1); }
.p-colour-5 { background: rgba(200,16,46,.1); }
.p-border-1 { border-left-color: #22c55e !important; }
.p-border-2 { border-left-color: #0288d1 !important; }
.p-border-3 { border-left-color: #f59e0b !important; }
.p-border-4 { border-left-color: #ea580c !important; }
.p-border-5 { border-left-color: #C8102E !important; }

/* ── MAIN GRID ── */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}
@media(max-width:820px){ .main-grid { grid-template-columns: 1fr; } }
.main-grid .span-2 { grid-column: span 2; }
@media(max-width:820px){ .main-grid .span-2 { grid-column: span 1; } }

/* ── CARD ── */
.card {
    background: var(--white);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-xs);
    overflow: hidden;
    transition: var(--transition);
}
.card:hover { box-shadow: var(--shadow-sm); }
.card-head {
    padding: .85rem 1.15rem;
    border-bottom: 1px solid var(--border-soft);
    background: #fafbfd;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.card-head-left { display: flex; align-items: center; gap: .6rem; }
.card-head-icon {
    width: 28px; height: 28px;
    background: var(--navy-faint);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.card-head h2 { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .07em; }
.card-body { padding: 1.15rem; }

/* ── ALERT CARD ── */
.alert-card { border-top: 3px solid var(--red); }
.alert-card-level-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .85rem 1.15rem;
    border-bottom: 1px solid var(--border-soft);
}
.alert-level-ring {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700;
    border: 3px solid;
    font-family: var(--font-mono);
    flex-shrink: 0;
}
.alert-level-info .level-num { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--muted); }
.alert-level-info .level-title { font-size: 15px; font-weight: 700; color: var(--navy); }
.alert-body-box { padding: 1rem 1.15rem; }
.alert-inner-box { padding: .9rem 1rem; border-left: 3px solid; }
.alert-inner-desc { font-size: 13px; line-height: 1.6; }
.alert-msg-box { margin-top: .65rem; padding: .6rem .85rem; background: rgba(0,0,0,.04); border: 1px dashed rgba(0,0,0,.12); font-size: 12px; font-weight: 600; }

/* ── EVENTS LIST ── */
.event-item {
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    padding: .85rem 0;
    border-bottom: 1px solid var(--border-soft);
}
.event-item:last-child { border-bottom: none; }
.event-date-block {
    width: 42px;
    flex-shrink: 0;
    text-align: center;
    border: 1px solid var(--border);
    overflow: hidden;
}
.edb-month {
    font-size: 9px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    background: var(--navy); color: #fff;
    padding: .2rem 0;
    font-family: var(--font-mono);
}
.edb-day { font-size: 18px; font-weight: 700; color: var(--navy); padding: .2rem 0 .15rem; line-height: 1; font-family: var(--font-mono); }
.event-content { flex: 1; min-width: 0; }
.event-type-pill {
    display: inline-flex; align-items: center;
    font-size: 10px; font-weight: 700;
    padding: 1px 7px; border: 1px solid;
    border-radius: 3px; margin-bottom: .25rem;
    text-transform: uppercase; letter-spacing: .05em;
}
.event-title-link {
    display: block;
    font-size: 13px; font-weight: 600;
    color: var(--navy); text-decoration: none;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: .15rem;
    transition: var(--transition);
}
.event-title-link:hover { color: var(--teal); }
.event-meta-row { display: flex; align-items: center; gap: .6rem; font-size: 11px; color: var(--muted); flex-wrap: wrap; }

/* ── ACTIVITY CARD ── */
.activity-band {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .75rem 1.15rem;
    font-size: 12px;
    font-weight: 700;
    border-bottom: 1px solid var(--border-soft);
}
.activity-band.yes { background: var(--green-faint); color: var(--green); border-left: 3px solid var(--green); }
.activity-band.no  { background: var(--red-faint); color: var(--red); border-left: 3px solid var(--red); }

.act-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; margin-bottom: 1rem; }
.act-stat { text-align: center; padding: .75rem .5rem; background: var(--bg); border: 1px solid var(--border); }
.act-stat-num { font-size: 22px; font-weight: 700; color: var(--navy); font-family: var(--font-mono); line-height: 1; }
.act-stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-top: .2rem; }

/* ── PROGRESS ── */
.progress-wrap { margin-top: .75rem; }
.progress-head { display: flex; justify-content: space-between; font-size: 11px; color: var(--muted); margin-bottom: .4rem; font-family: var(--font-mono); }
.progress-track { height: 6px; background: var(--border); overflow: hidden; }
.progress-fill { height: 100%; background: var(--green); transition: width .6s ease; }

/* ── LINK LIST ── */
.link-list { display: flex; flex-direction: column; gap: .5rem; }
.link-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: .65rem .9rem;
    background: var(--bg);
    border: 1px solid var(--border-soft);
    text-decoration: none;
    color: var(--text);
    font-size: 13px; font-weight: 500;
    transition: var(--transition);
}
.link-item:hover { background: var(--white); border-color: var(--teal); color: var(--teal); }
.link-item-arrow { color: var(--muted); font-size: 12px; transition: var(--transition); }
.link-item:hover .link-item-arrow { color: var(--teal); transform: translateX(3px); }

/* ── CONDX CARD ── */
.condx-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
@media(max-width:480px){ .condx-grid { grid-template-columns: 1fr; } }
.condx-tile { padding: .75rem .9rem; border: 1px solid var(--border-soft); background: var(--bg); }
.condx-tile-icon { font-size: 16px; margin-bottom: .3rem; }
.condx-tile-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: .2rem; }
.condx-tile-value { font-size: 12px; font-weight: 600; color: var(--text); font-family: var(--font-mono); }
.condx-badge { display: inline-flex; align-items: center; gap: .25rem; font-size: 10px; font-weight: 700; padding: 2px 7px; border: 1px solid; font-family: var(--font-mono); letter-spacing: .04em; }

/* ── LIVE INDICATOR ── */
.live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); animation: blink 1.8s ease-in-out infinite; flex-shrink: 0; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.3;} }

/* ── VERIFIED TOAST ── */
.toast-verified {
    display: flex; align-items: flex-start; gap: .65rem;
    padding: .75rem 1rem; margin-bottom: 1.25rem;
    background: var(--green-faint); border: 1px solid rgba(26,122,60,.25);
    border-left: 3px solid var(--green); font-size: 12px;
    animation: fadeUp .4s ease;
}
@keyframes fadeUp { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }
.toast-icon { font-size: 14px; flex-shrink: 0; margin-top: 1px; }
.toast-title { font-size: 12px; font-weight: 700; color: var(--green); }
.toast-body { font-size: 12px; color: var(--text-mid); margin-top: 1px; }

/* ── YEAR BADGE ── */
.year-badge {
    display: inline-flex; align-items: center;
    font-size: 10px; font-weight: 600;
    padding: 2px 8px; border: 1px solid var(--border);
    color: var(--muted); font-family: var(--font-mono);
    background: var(--bg);
}

/* ── FOOTER NOTE ── */
.card-foot {
    padding: .65rem 1.15rem;
    border-top: 1px solid var(--border-soft);
    background: #fafbfd;
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; flex-wrap: wrap;
}
.card-foot-text { font-size: 11px; color: var(--muted); }
.card-foot a { font-size: 11px; font-weight: 700; color: var(--navy); text-decoration: none; white-space: nowrap; }
.card-foot a:hover { color: var(--teal); }

/* ── PROPAGATION ── */
.condx-indices { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .75rem; }

/* ── EMPTY STATE ── */
.empty { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: 13px; }
.empty-icon { font-size: 1.8rem; opacity: .25; margin-bottom: .5rem; }


/* ── MOBILE IMPROVEMENTS ── */
@media(max-width:900px) {
    .hub-main { padding: 1rem .85rem 5rem; }
    .welcome-banner { flex-direction: column; align-items: flex-start; gap: .75rem; padding: 1.2rem 1rem; }
    .welcome-name { font-size: 1.3rem; }
    .welcome-actions { width: 100%; }
    .wb-btn { font-size: 11px; padding: .45rem .9rem; flex: 1; justify-content: center; }
    .stat-tile { padding: .7rem .75rem; }
    .stat-tile-label { font-size: 9px; letter-spacing: .08em; }
    .stat-tile-value { font-size: 22px; }
    .stat-tile-sub { font-size: 10px; line-height: 1.3; }
    .hub-topbar { padding: 0 .85rem; }
    .topbar-user-name { display: none; }
    .topbar-user-role { display: none; }
}
@media(max-width:480px) {
    .stat-tile-sub { display: none; }
    .hub-main { padding: .85rem .65rem 5rem; }
    .welcome-name { font-size: 1.15rem; }
    .welcome-greeting { font-size: .8rem; }
}

/* ── MOBILE QUICK NAV (replaces broken bottom bar) ── */
.mobile-quick-nav {
    display: none;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1.25rem;
    padding: .75rem;
    background: var(--navy-deep);
    border-radius: 8px;
}
@media(max-width:900px) { .mobile-quick-nav { display: flex; } }
.mqn-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem .9rem;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 999px;
    color: rgba(255,255,255,.8);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all .15s;
}
.mqn-link:hover, .mqn-link.active { background: rgba(255,255,255,.15); color: #fff; border-color: rgba(255,255,255,.3); }
.mqn-link.active { background: var(--teal); border-color: var(--teal); color: #fff; }
@media print {
    .hub-sidebar, .hub-topbar { display: none !important; }
    .hub-shell { grid-template-columns: 1fr; }
}
</style>

@php
    $initials = strtoupper(substr($operator['name'] ?? 'M', 0, 1));
@endphp

<div class="hub-shell">

    {{-- ── TOP BAR ── --}}
    <header class="hub-topbar">
        <div class="topbar-brand">
            <div class="topbar-logo">RAY<br>NET</div>
            <div>
                <div class="topbar-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="topbar-sub">Members' Hub</div>
            </div>
        </div>

        <nav class="topbar-nav">
            <a href="{{ route('members') }}" class="active">Hub</a>
            <a href="{{ route('profile.edit') }}">Profile</a>
            <a href="{{ route('members.activity') }}">Activity</a>
            <a href="{{ route('calendar') }}">Calendar</a>
            <a href="{{ route('data-dashboard') }}">Data</a>
            <a href="{{ route('lms.index') }}">Training</a>
            @if($hasDmrAny)
                <a href="{{ route('dmr.index') }}" class="{{ request()->routeIs('dmr.*') ? 'active' : '' }}">DMR</a>
            @endif
        </nav>

        <a href="{{ route('profile.edit') }}" class="topbar-user">
            @if(auth()->user()->hasRole("temporary_guest") || auth()->user()->guest_expires_at)
                <img src="{{ Storage::url('avatars/TempAvatar.png') }}"
                     style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid rgba(180,83,9,.4);flex-shrink:0;" alt="Temporary Guest">
            @elseif(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}"
                     style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.2);flex-shrink:0;" alt="">
            @else
                <div class="topbar-av">{{ $initials }}</div>
            @endif
            <div>
                <div class="topbar-user-name">{{ $operator['name'] ?? 'Operator' }}</div>
                <div class="topbar-user-role">
                    @if(!empty($operator['callsign'])) {{ $operator['callsign'] }} · @endif
                    {{ $operator['role'] ?? 'Member' }}
                </div>
            </div>
        </a>
    </header>

    {{-- ── SIDEBAR ── --}}
    <aside class="hub-sidebar">

        {{-- Alert level --}}
        <div class="sidebar-alert">
            <div class="sidebar-alert-head">
                <span class="sidebar-alert-label">Alert Status</span>
                <span class="sidebar-alert-level-num" style="color:{{ $colour }};">{{ $level }}</span>
            </div>
            <div class="sidebar-alert-body" style="background:{{ $colour }}20; border:1px solid {{ $colour }}40;">
                <div class="sidebar-alert-title" style="color:{{ $colour }};">{{ $meta['title'] ?? 'Level ' . $level }}</div>
                <div class="sidebar-alert-desc" style="color:{{ $colour }};">{{ Str::limit($meta['description'] ?? '', 80) }}</div>
            </div>
        </div>

        <div class="sidebar-section-label">Navigation</div>
        <a href="{{ route('members') }}" class="sidebar-link active">
            <span class="sidebar-link-icon">🏠</span> Hub
        </a>
        <a href="{{ route('profile.edit') }}" class="sidebar-link">
            <span class="sidebar-link-icon">👤</span> My Profile
        </a>
        <a href="{{ route('lms.index') }}" class="sidebar-link">
            <span class="sidebar-link-icon">🎓</span> Training Portal
        </a>
        <a href="{{ route('members.activity') }}" class="sidebar-link">
            <span class="sidebar-link-icon">📅</span> Activity Log
        </a>

        <div class="sidebar-section-label" style="margin-top:.5rem;">Events</div>
        <a href="{{ route('calendar') }}" class="sidebar-link">
            <span class="sidebar-link-icon">📆</span> Calendar
        </a>
        <a href="{{ route('events.index') }}" class="sidebar-link">
            <span class="sidebar-link-icon">📋</span> Event List
        </a>
        <a href="{{ route('member.availability') }}" class="sidebar-link">
            <span class="sidebar-link-icon">🗓️</span> My Availability
        </a>

        <div class="sidebar-section-label" style="margin-top:.5rem;">Resources</div>
        <a href="{{ route('gallery') }}" class="sidebar-link {{ request()->routeIs('gallery') ? 'active' : '' }}">
            <span class="sidebar-icon">📸</span> Gallery
        </a>
        <a href="{{ route('members.my-photos') }}" class="sidebar-link {{ request()->routeIs('members.my-photos') ? 'active' : '' }}">
            <span class="sidebar-icon">🖼️</span> My Photos
            <span style="margin-left:auto;background:#C8102E;color:#fff;font-size:8px;font-weight:800;padding:2px 6px;border-radius:999px;letter-spacing:.04em;">NEW</span>
        </a>
        <a href="{{ route('members.albums.index') }}" class="sidebar-link {{ request()->routeIs('members.albums*') ? 'active' : '' }}">
            <span class="sidebar-icon">📚</span> My Albums
        </a>

        @if(auth()->user()->hasPermissionTo('approve photos') || auth()->user()->isAdmin())
        @php $pendingCount = \App\Models\Photo::where('status','pending')->count(); @endphp
        <a href="{{ route('members.photo-approval.index') }}" class="sidebar-link {{ request()->routeIs('members.photo-approval*') ? 'active' : '' }}">
            <span class="sidebar-icon">✅</span> Photo Approval
            @if($pendingCount > 0)
                <span style="margin-left:auto;background:#f59e0b;color:#fff;font-size:9px;font-weight:bold;padding:1px 6px;border-radius:999px;">{{ $pendingCount }}</span>
            @endif
        </a>
        @endif
        <a href="{{ route('data-dashboard') }}" class="sidebar-link">
            <span class="sidebar-link-icon">📡</span> Data Dashboard
        </a>
        @if($hasDmrAny)
            <a href="{{ route('dmr.index') }}"
               class="sidebar-link {{ request()->routeIs('dmr.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon">📻</span>
                DMR Network
                @if($hasDmrDashboard)
                    <span class="sidebar-badge" style="background:var(--teal);font-size:8px;">FULL</span>
                @endif
            </a>
        @endif
        @foreach ($trainingLinks as $link)
        <a href="{{ $link['url'] }}" class="sidebar-link">
            <span class="sidebar-link-icon">🎓</span> {{ $link['label'] }}
        </a>
        @endforeach

        <div class="sidebar-section-label" style="margin-top:.5rem;">Recruit</div>
        <a href="{{ route('members.refer') }}" class="sidebar-link {{ request()->routeIs('members.refer') ? 'active' : '' }}">
            <span class="sidebar-icon">📡</span> Invite Someone to Join
        </a>

        <div class="sidebar-section-label" style="margin-top:.5rem;">Account</div>
        <a href="{{ route('password.change') }}" class="sidebar-link">
            <span class="sidebar-link-icon">🔑</span> Change Password
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display:contents;">
            @csrf
            <button type="submit" class="sidebar-link" style="border:none;cursor:pointer;background:none;width:100%;text-align:left;">
                <span class="sidebar-link-icon">⏻</span> Sign Out
            </button>
        </form>
    </aside>

    {{-- ── MAIN ── --}}
    <main class="hub-main">

        @if (session('verified_notice'))
        <div class="toast-verified">
            <span class="toast-icon">✓</span>
            <div>
                <div class="toast-title">Email verified</div>
                <div class="toast-body">{{ session('verified_notice') }}</div>
            </div>
        </div>
        @endif

        {{-- Welcome banner --}}
        <div class="welcome-banner" style="margin-bottom:1.75rem;">
            <div class="welcome-text">
                <div class="welcome-greeting">Welcome back</div>
                <div class="welcome-name">{{ $operator['name'] ?? 'Operator' }}</div>
                <div class="welcome-meta">
                    @if (!empty($operator['callsign']))
                        <span class="welcome-chip welcome-callsign">{{ $operator['callsign'] }}</span>
                    @endif
                    @if (!empty($operator['role']))
                        <span class="welcome-chip">{{ $operator['role'] }}</span>
                    @endif
                    @if (!empty($operator['level']))
                        <span class="welcome-chip">Level {{ $operator['level'] }}</span>
                    @endif
                </div>
            </div>
            <div class="welcome-actions">
                <a href="{{ route('members.activity') }}" class="wb-btn wb-btn-ghost">📅 Activity</a>
                <a href="{{ route('profile.edit') }}" class="wb-btn wb-btn-primary">Edit Profile →</a>
            </div>
        </div>

        {{-- Mobile quick nav --}}
        <div class="mobile-quick-nav">
            <a href="{{ route('profile.edit') }}" class="mqn-link">👤 Profile</a>
            <a href="{{ route('calendar') }}" class="mqn-link">📅 Calendar</a>
            <a href="{{ route('lms.index') }}" class="mqn-link">🎓 Training</a>
            <a href="{{ route('gallery') }}" class="mqn-link {{ request()->routeIs('gallery') ? 'active' : '' }}">📸 Gallery</a>
            <a href="{{ route('members.my-photos') }}" class="mqn-link {{ request()->routeIs('members.my-photos') ? 'active' : '' }}">🖼️ My Photos <span style="background:#C8102E;color:#fff;font-size:7px;font-weight:800;padding:1px 4px;border-radius:999px;vertical-align:middle;">NEW</span></a>

            @if(auth()->user()->hasPermissionTo('approve photos') || auth()->user()->isAdmin())
            <a href="{{ route('members.photo-approval.index') }}" class="mqn-link {{ request()->routeIs('members.photo-approval*') ? 'active' : '' }}">✅ Approve</a>
            @endif
            <a href="{{ route('members.refer') }}" class="mqn-link {{ request()->routeIs('members.refer') ? 'active' : '' }}">📡 Invite</a>
            <a href="{{ route('members.activity') }}" class="mqn-link">📊 Activity</a>
            <a href="{{ route('member.availability') }}" class="mqn-link">🕐 Availability</a>
            <a href="{{ route('resources.index') }}" class="mqn-link">📚 Library</a>
        </div>

        {{-- Stat strip --}}
        <div class="stat-strip">
            <div class="stat-tile t-green">
                <div class="stat-tile-label">Events Attended</div>
                <div class="stat-tile-value">{{ $eventsCount }}</div>
                <div class="stat-tile-sub">This year</div>
            </div>
            <div class="stat-tile t-teal">
                <div class="stat-tile-label">Volunteer Hours</div>
                <div class="stat-tile-value">{{ number_format($volHours, 1) }}</div>
                <div class="stat-tile-sub">{{ $yearLabel }}</div>
            </div>
            <div class="stat-tile">
                <div class="stat-tile-label">Alert Level</div>
                <div class="stat-tile-value" style="color:{{ $colour }};">{{ $level }}</div>
                <div class="stat-tile-sub">{{ $meta['title'] ?? 'Current' }}</div>
            </div>
            <div class="stat-tile t-amber">
                <div class="stat-tile-label">Avg Hrs / Event</div>
                <div class="stat-tile-value">{{ $eventsCount > 0 ? number_format($volHours / $eventsCount, 1) : '—' }}</div>
                <div class="stat-tile-sub">Per session</div>
            </div>
            <div class="stat-tile t-red" id="notifStatTile" style="cursor:pointer;" onclick="document.getElementById('notifCard').scrollIntoView({behavior:'smooth'})">
                <div class="stat-tile-label">Notifications</div>
                <div class="stat-tile-value" id="notifStatCount">—</div>
                <div class="stat-tile-sub" id="notifStatSub">Loading…</div>
            </div>
        </div>

        {{-- Main grid --}}
        <div class="main-grid">

            {{-- Recruit card --}}
            <div class="card" style="border-top:3px solid var(--red);">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📡</div>
                        <h2>Invite Someone to Join</h2>
                    </div>
                </div>
                <div class="card-body">
                    <p style="font-size:.88rem;color:var(--text-mid);line-height:1.6;margin-bottom:1rem;">
                        Know someone who would make a great RAYNET member? Whether they hold an amateur radio licence or just want to get involved and support us — send them a personalised invitation.
                    </p>
                    <a href="{{ route('members.refer') }}" style="display:inline-flex;align-items:center;gap:.5rem;background:var(--red);color:#fff;padding:.6rem 1.2rem;border-radius:999px;font-size:.85rem;font-weight:bold;text-decoration:none;">
                        📨 Send an Invite →
                    </a>
                </div>
            </div>

            {{-- Alert card --}}
            <div class="card alert-card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">⚠</div>
                        <h2>Alert Status</h2>
                    </div>
                    <div class="live-dot"></div>
                </div>
                <div class="alert-card-level-bar">
                    <div class="alert-level-ring" style="color:{{ $colour }};border-color:{{ $colour }};">{{ $level }}</div>
                    <div class="alert-level-info">
                        <div class="level-num">Alert Level {{ $level }} / 5</div>
                        <div class="level-title">{{ $meta['title'] ?? 'Level ' . $level }}</div>
                    </div>
                </div>
                <div class="alert-body-box">
                    <div class="alert-inner-box" style="background:{{ $colour }}10;border-color:{{ $colour }}50;">
                        <div class="alert-inner-desc" style="color:{{ $colour }}; font-weight:600; font-size:12px;">
                            {{ $meta['description'] ?? '' }}
                        </div>
                        @if (!empty($alertStatus?->message))
                            <div class="alert-msg-box">{{ $alertStatus->message }}</div>
                        @endif
                    </div>
                </div>
                @if (!empty($alertStatus?->headline))
                <div class="card-foot">
                    <span class="card-foot-text">📢 {{ $alertStatus->headline }}</span>
                </div>
                @endif
            </div>

            {{-- Annual activity --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📊</div>
                        <h2>Annual Activity</h2>
                    </div>
                    <span class="year-badge">{{ $yearLabel }}</span>
                </div>
                <div class="activity-band {{ $attended ? 'yes' : 'no' }}">
                    {{ $attended ? '✓ Attended at least one event this year' : '○ No events recorded yet this year' }}
                </div>
                <div class="card-body">
                    <div class="act-stats">
                        <div class="act-stat">
                            <div class="act-stat-num">{{ $eventsCount }}</div>
                            <div class="act-stat-label">Events</div>
                        </div>
                        <div class="act-stat">
                            <div class="act-stat-num">{{ number_format($volHours, 1) }}</div>
                            <div class="act-stat-label">Hours</div>
                        </div>
                        <div class="act-stat">
                            <div class="act-stat-num">{{ $eventsCount > 0 ? number_format($volHours / $eventsCount, 1) : '—' }}</div>
                            <div class="act-stat-label">Avg hrs</div>
                        </div>
                    </div>
                    @if ($eventsCount > 0)
                    <div class="progress-wrap">
                        <div class="progress-head">
                            <span>Participation</span>
                            <span>{{ $eventsCount }} logged</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $barPct }}%;"></div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-foot">
                    <span class="card-foot-text">Resets 1 Sep each year</span>
                    <a href="{{ route('members.activity') }}">Full activity calendar →</a>
                </div>
            </div>

            {{-- ── DMR Network card (shown only if user has any DMR permission) ── --}}
            @if($hasDmrAny)
            <div class="card span-2" style="border-top:3px solid var(--navy);">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📻</div>
                        <h2>DMR Network</h2>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        @if($hasDmrDashboard)
                            <span style="font-size:10px;font-weight:700;padding:2px 9px;background:rgba(2,136,209,.1);border:1px solid rgba(2,136,209,.3);color:var(--teal);text-transform:uppercase;letter-spacing:.06em;">
                                Full Access
                            </span>
                        @else
                            <span style="font-size:10px;font-weight:700;padding:2px 9px;background:var(--navy-faint);border:1px solid var(--border);color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">
                                Masters View
                            </span>
                        @endif
                        <div class="live-dot"></div>
                    </div>
                </div>
                <div class="card-body" style="padding:.85rem 1.15rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:.3rem;">
                                {{ \App\Helpers\RaynetSetting::groupName() }} DMR Network &middot; m0kkn.dragon-net.pl
                            </div>
                            <div style="font-size:12px;color:var(--muted);">
                                @if($hasDmrDashboard)
                                    Live QSOs, master systems, peers, bridges &amp; call log
                                @else
                                    Network master systems status
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;flex-shrink:0;">
                            <a href="{{ route('dmr.index') }}"
                               style="display:inline-flex;align-items:center;gap:.4rem;padding:.48rem 1.1rem;background:var(--navy);color:#fff;font-size:12px;font-weight:700;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;border:1px solid var(--navy);transition:background .15s;"
                               onmouseover="this.style.background='var(--navy-mid)'"
                               onmouseout="this.style.background='var(--navy)'">
                                📻 Network Page
                            </a>
                            @if($hasDmrDashboard)
                                <a href="{{ env('DMR_DASHBOARD_URL', 'http://m0kkn.dragon-net.pl:8010') }}"
                                   target="_blank"
                                   style="display:inline-flex;align-items:center;gap:.4rem;padding:.48rem 1.1rem;background:transparent;color:var(--teal);font-size:12px;font-weight:700;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;border:1px solid rgba(2,136,209,.4);transition:all .15s;"
                                   onmouseover="this.style.background='rgba(2,136,209,.08)'"
                                   onmouseout="this.style.background='transparent'">
                                    Full Dashboard ↗
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notifications --}}
            <div class="card span-2" id="notifCard" style="border-top: 3px solid var(--navy);">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">🔔</div>
                        <h2>My Notifications</h2>
                    </div>
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <span class="notif-unread-badge" id="notifHubBadge" style="display:none;">0</span>
                        <div class="live-dot"></div>
                    </div>
                </div>

                <div id="notifHubContainer">
                    <div class="notif-hub-loading">
                        <div class="notif-loading-bar"><div class="notif-loading-fill"></div></div>
                        <div class="notif-loading-text">Loading notifications…</div>
                    </div>
                </div>

                <div class="notif-hub-actions" id="notifHubActions" style="display:none;">
                    <button class="notif-mark-all-btn" onclick="hubMarkAllRead()">✓ Mark all read</button>
                    <span class="card-foot-text" id="notifHubFooterText"></span>
                </div>
            </div>

            {{-- Training portal --}}
            <div class="card span-2" style="border-top:3px solid var(--teal);">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">🎓</div>
                        <h2>RAYNET Training Portal</h2>
                    </div>
                    <a href="{{ route('lms.index') }}" style="font-size:11px;font-weight:700;color:var(--navy);text-decoration:none;">Go to training →</a>
                </div>
                <div class="card-body" style="padding:.75rem 1.15rem;">
                    @php
                        $myEnrolled = \App\Models\CourseEnrollment::where('user_id', auth()->id())
                            ->with('course')
                            ->get()
                            ->filter(fn($e) => $e->course !== null);
                    @endphp
                    @if($myEnrolled->isEmpty())
                        <div class="empty">
                            <div class="empty-icon">🎓</div>
                            You have not been enrolled on any training courses yet.
                            Your Group Controller will assign courses when available.
                        </div>
                    @else
                        <div class="link-list">
                            @foreach($myEnrolled as $enrolment)
                            @php
                                $pct = $enrolment->course->getProgressFor(auth()->id());
                            @endphp
                            <a href="{{ route('lms.course', $enrolment->course->slug) }}" class="link-item">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:.3rem;">
                                        {{ $enrolment->course->title }}
                                    </div>
                                    <div style="height:4px;background:var(--border);overflow:hidden;max-width:200px;">
                                        <div style="height:100%;background:{{ $pct==100 ? 'var(--green)' : 'var(--teal)' }};width:{{ $pct }}%;transition:width .4s ease;"></div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:.65rem;flex-shrink:0;">
                                    @if($enrolment->completed_at)
                                        <span style="font-size:10px;font-weight:700;padding:2px 8px;background:var(--green-faint);border:1px solid rgba(26,122,60,.25);color:var(--green);">✓ Complete</span>
                                    @else
                                        <span style="font-size:11px;font-weight:700;color:var(--teal);">{{ $pct }}%</span>
                                    @endif
                                    <span class="link-item-arrow">→</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Upcoming events --}}
            <div class="card span-2">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📅</div>
                        <h2>Upcoming Events &amp; Training</h2>
                    </div>
                    <a href="{{ route('calendar') }}" style="font-size:11px;font-weight:700;color:var(--navy);text-decoration:none;">Full calendar →</a>
                </div>
                <div class="card-body" style="padding:.5rem 1.15rem;">
                    @forelse ($upcoming as $event)
                    @php
                        $type     = $event->type;
                        $evColour = $type && $type->colour ? $type->colour : '#22c55e';
                        $eDate    = $event->starts_at ?? null;
                    @endphp
                    <div class="event-item">
                        <div class="event-date-block">
                            <div class="edb-month">{{ $eDate ? \Carbon\Carbon::parse($eDate)->format('M') : '—' }}</div>
                            <div class="edb-day">{{ $eDate ? \Carbon\Carbon::parse($eDate)->format('j') : '?' }}</div>
                        </div>
                        <div class="event-content">
                            <div style="margin-bottom:.25rem;">
                                <span class="event-type-pill" style="color:{{ $evColour }};border-color:{{ $evColour }}30;background:{{ $evColour }}0d;">
                                    {{ $type?->name ?? 'Event' }}
                                </span>
                            </div>
                            <a href="{{ $event->url() }}" class="event-title-link">{{ $event->title }}</a>
                            <div class="event-meta-row">
                                <span>🕐 {{ $event->displayDate() }}</span>
                                @if ($event->location)
                                    <span>📍 {{ $event->location }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty">
                        <div class="empty-icon">📅</div>
                        No upcoming events scheduled yet.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Resources --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📚</div>
                        <h2>Resources &amp; Systems</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="link-list">
                        @foreach ($resources as $resource)
                        <a href="{{ $resource['url'] }}" class="link-item">
                            <span>{{ $resource['label'] }}</span>
                            <span class="link-item-arrow">→</span>
                        </a>
                        @endforeach
                        <a href="{{ route('password.change') }}" class="link-item" style="color:var(--red);">
                            <span>🔑 Change my password</span>
                            <span class="link-item-arrow" style="color:var(--red);">→</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Propagation brief --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">📡</div>
                        <h2>HF / VHF Conditions</h2>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div class="live-dot"></div>
                        <span style="font-size:10px;font-weight:700;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;font-family:var(--font-mono);">Live</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="condx-indices" id="indices-section"></div>
                    <div class="condx-grid">
                        <div class="condx-tile">
                            <div class="condx-tile-icon">☀️</div>
                            <div class="condx-tile-label">Solar / Geo</div>
                            <div class="condx-tile-value" id="solar-geo-values">Loading…</div>
                        </div>
                        <div class="condx-tile">
                            <div class="condx-tile-icon">📈</div>
                            <div class="condx-tile-label">HF Propagation</div>
                            <div class="condx-tile-value" id="hf-values">Loading…</div>
                        </div>
                        <div class="condx-tile" style="grid-column:span 2;">
                            <div class="condx-tile-icon">📍</div>
                            <div class="condx-tile-label">Local ({{ \App\Helpers\RaynetSetting::groupRegion() }})</div>
                            <div class="condx-tile-value" id="local-values">Loading…</div>
                        </div>
                    </div>
                </div>
                <div class="card-foot">
                    <span class="card-foot-text">Updated <span id="updated-time" style="font-family:var(--font-mono);">--</span></span>
                    <a href="{{ route('data-dashboard') }}">Full dashboard →</a>
                </div>
            </div>

            {{-- Ops board --}}
            @if (!empty($opsSystems['ops_board_url']) || !empty($opsSystems['backend_url']))
            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon">🖥</div>
                        <h2>Ops Board</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="link-list">
                        @if (!empty($opsSystems['ops_board_url']))
                        <a href="{{ $opsSystems['ops_board_url'] }}" class="link-item">
                            <span>Status board / Ops dashboard</span>
                            <span class="link-item-arrow">↗</span>
                        </a>
                        @endif
                        @if (!empty($opsSystems['backend_url']))
                        <a href="{{ $opsSystems['backend_url'] }}" class="link-item">
                            <span>Self-hosted back-end</span>
                            <span class="link-item-arrow">↗</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif


        </div>{{-- /main-grid --}}

    </main>{{-- /hub-main --}}

</div>{{-- /hub-shell --}}

<script>
async function loadPropagationBrief() {
    const tSolarGeo = document.getElementById('solar-geo-values');
    const tIndices  = document.getElementById('indices-section');
    const tHF       = document.getElementById('hf-values');
    const tLocal    = document.getElementById('local-values');
    const timeSpan  = document.getElementById('updated-time');

    try {
        const script = document.createElement('script');
        script.src = 'https://signalsafe.uk/feeds/condx.js?v=' + Date.now();
        script.async = true;
        document.head.appendChild(script);
        await new Promise(resolve => setTimeout(resolve, 1800));

        if (window.SIGNALSAFE_CONDX && window.SIGNALSAFE_CONDX.brief_html) {
            const tmp  = document.createElement('div');
            tmp.innerHTML = window.SIGNALSAFE_CONDX.brief_html;
            const text = tmp.textContent || '';

            timeSpan.textContent = window.SIGNALSAFE_CONDX.updated_at || '--';

            const solarGeo = text.match(/Solar\/Geo:([^H]*)HF:/);
            const hfMatch  = text.match(/MUF ([\d.]+).*; LUF ([\d.]+)/);
            const localMatch = text.match(/{{ \App\Helpers\RaynetSetting::groupRegion() }}:([\s\S]*)$/);
            const idxMatch = text.match(/SFI (\d+).*?A (\d+).*?K (\d+).*?X-ray ([A-Z])/);

            tSolarGeo.textContent = solarGeo ? solarGeo[1].trim() : 'N/A';

            if (hfMatch) {
                tHF.innerHTML = `MUF <strong>${hfMatch[1]} MHz</strong> · LUF <strong>${hfMatch[2]} MHz</strong>`;
            } else {
                tHF.textContent = 'No HF data';
            }

            tLocal.textContent = localMatch ? localMatch[1].trim() : 'No local info';

            if (idxMatch) {
                const [_, sfi, a, k, x] = idxMatch;
                const colours = { SFI:'#0077cc', A:'#0066aa', K:'#cc3333', 'X-ray':'#333399' };
                tIndices.innerHTML = [['SFI',sfi],['A',a],['K',k],['X-ray',x]].map(([l,v]) =>
                    `<span class="condx-badge" style="background:${colours[l]}20;border-color:${colours[l]}50;color:${colours[l]}">${l} ${v}</span>`
                ).join('');
            }
        } else {
            [tSolarGeo, tHF, tLocal].forEach(el => { if(el) el.textContent = 'Data unavailable'; });
        }
    } catch(err) {
        [tSolarGeo, tHF, tLocal].forEach(el => { if(el) el.textContent = 'Error loading data'; });
        console.error(err);
    }
}

/* ── Members hub notifications ── */
const PRIORITY_META = {
    1: { colour: '#22c55e', bg: 'rgba(34,197,94,.1)',   icon: '📋', label: 'Routine'     },
    2: { colour: '#0288d1', bg: 'rgba(2,136,209,.1)',   icon: '💬', label: 'Advisory'    },
    3: { colour: '#f59e0b', bg: 'rgba(245,158,11,.1)',  icon: '⚡', label: 'Operational' },
    4: { colour: '#ea580c', bg: 'rgba(234,88,12,.1)',   icon: '🚨', label: 'Urgent'      },
    5: { colour: '#C8102E', bg: 'rgba(200,16,46,.1)',   icon: '🆘', label: 'Emergency'   },
};

let hubNotifications = [];

async function loadHubNotifications() {
    try {
        const resp = await fetch('/members/notifications/recent', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error('failed');
        const data = await resp.json();
        hubNotifications = data.notifications || [];
    } catch {
        hubNotifications = [];
    }
    renderHubNotifications();
}

function renderHubNotifications() {
    const container   = document.getElementById('notifHubContainer');
    const badge       = document.getElementById('notifHubBadge');
    const actions     = document.getElementById('notifHubActions');
    const footerText  = document.getElementById('notifHubFooterText');
    const statCount   = document.getElementById('notifStatCount');
    const statSub     = document.getElementById('notifStatSub');

    const unread = hubNotifications.filter(n => !n.read_at).length;

    if (statCount) {
        statCount.textContent = unread > 0 ? unread : hubNotifications.length;
        if (statSub) statSub.textContent = unread > 0 ? `${unread} unread` : 'All caught up';
    }

    if (badge) {
        badge.style.display = unread > 0 ? 'inline-flex' : 'none';
        badge.textContent   = unread > 9 ? '9+' : String(unread);
    }

    if (hubNotifications.length === 0) {
        container.innerHTML = `
            <div class="notif-hub-empty">
                <div class="notif-hub-empty-icon">🔔</div>
                <div class="notif-hub-empty-text">You're all caught up — no notifications yet.</div>
            </div>`;
        if (actions) actions.style.display = 'none';
        return;
    }

    container.innerHTML = `<div class="notif-hub-list">${
        hubNotifications.map(n => {
            const p   = n.priority || 1;
            const pm  = PRIORITY_META[p] || PRIORITY_META[1];
            const isUnread = !n.read_at;
            return `
            <div class="notif-hub-item ${isUnread ? 'unread p-border-' + p : ''}"
                 style="${isUnread ? 'border-left:3px solid ' + pm.colour + ';' : ''}">
                <div class="nhi-priority" style="background:${pm.bg};">
                    <span>${pm.icon}</span>
                </div>
                <div class="nhi-body">
                    ${n.from_hq ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:1px 6px;background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);color:#C8102E;margin-bottom:4px;">📡 HQ Broadcast</span>` : ''}
                    <div class="nhi-title">${escHtmlHub(n.title || 'Notification')}</div>
                    ${n.body ? `<div class="nhi-text">${escHtmlHub(n.body)}</div>` : ''}
                    <div class="nhi-meta">
                        <span class="nhi-priority-chip" style="background:${pm.bg};color:${pm.colour};border-color:${pm.colour}40;">
                            ${pm.icon} ${pm.label}
                        </span>
                        <span class="nhi-time">${escHtmlHub(n.ago || '')}</span>
                        ${isUnread ? `<span style="width:6px;height:6px;border-radius:50%;background:${pm.colour};display:inline-block;flex-shrink:0;"></span>` : ''}
                    </div>
                </div>
            </div>`;
        }).join('')
    }</div>`;

    if (actions) {
        actions.style.display = 'flex';
        if (footerText) footerText.textContent = `${hubNotifications.length} notification${hubNotifications.length !== 1 ? 's' : ''} · ${unread} unread`;
    }
}

async function hubMarkAllRead() {
    try {
        await fetch('/members/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        });
        hubNotifications = hubNotifications.map(n => ({ ...n, read_at: true }));
        renderHubNotifications();
        if (typeof renderNotifications === 'function') {
            notifications = hubNotifications;
            renderNotifications();
        }
    } catch {}
}

function escHtmlHub(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadHubNotifications();
loadPropagationBrief();
setInterval(loadPropagationBrief, 1200000);
</script>


{{-- EXIF Modal --}}
<div id="exifModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeExif()">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;max-width:480px;width:100%;max-height:80vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.3);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div style="font-size:1rem;font-weight:700;color:#003366;">📷 EXIF Data</div>
            <button onclick="closeExif()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#6b7f96;">✕</button>
        </div>
        <div id="exifContent" style="font-size:.82rem;"></div>
    </div>
</div>

<script>
// Upload location map
var uploadMapInstance = null;
var uploadMarker = null;
function toggleUploadMap() {
    var wrap = document.getElementById('uploadMapWrap');
    if (wrap.style.display === 'none') {
        wrap.style.display = 'block';
        if (!uploadMapInstance) {
            uploadMapInstance = L.map('uploadMap').setView([53.4, -2.99], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(uploadMapInstance);
            uploadMapInstance.on('click', function(e) {
                if (uploadMarker) uploadMarker.remove();
                uploadMarker = L.marker(e.latlng).addTo(uploadMapInstance);
                document.getElementById('uploadLat').value = e.latlng.lat.toFixed(6);
                document.getElementById('uploadLng').value = e.latlng.lng.toFixed(6);
                // Reverse geocode
                fetch('https://nominatim.openstreetmap.org/reverse?lat=' + e.latlng.lat + '&lon=' + e.latlng.lng + '&format=json')
                .then(function(r){return r.json();})
                .then(function(d){
                    if (d && d.address) {
                        var a = d.address;
                        var parts = [];
                        if (a.road) parts.push(a.road);
                        if (a.suburb) parts.push(a.suburb);
                        if (a.city || a.town || a.village) parts.push(a.city || a.town || a.village);
                        var loc = parts.length ? parts.join(', ') : d.display_name;
                        document.getElementById('uploadLocationText').value = loc;
                    }
                }).catch(function(){});
            });
        }
        setTimeout(function(){ uploadMapInstance.invalidateSize(); }, 100);
    } else {
        wrap.style.display = 'none';
    }
}

// Upload progress
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action*="members/photos"]:not([action*="tags"]):not([action*="DELETE"])');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var fileInput = form.querySelector('input[type="file"]');
        if (!fileInput || !fileInput.files.length) {
            alert('Please select at least one photo to upload.');
            return;
        }

        var consent = form.querySelector('input[name="consent"]');
        if (!consent || !consent.checked) {
            alert('Please tick the consent checkbox before uploading.');
            return;
        }

        var btn = document.getElementById('photoUploadBtn');
        var wrap = document.getElementById('uploadProgressWrap');
        var bar = document.getElementById('uploadProgressBar');
        var label = document.getElementById('uploadProgressLabel');
        var successBanner = document.getElementById('photoSuccessBanner');

        btn.disabled = true;
        btn.textContent = '⏳ Uploading…';
        wrap.style.display = 'block';
        bar.style.width = '0%';
        bar.style.background = 'var(--red)';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

        xhr.upload.addEventListener('progress', function(ev) {
            if (ev.lengthComputable) {
                var pct = Math.round(ev.loaded / ev.total * 100);
                bar.style.width = pct + '%';
                label.textContent = pct < 100 ? 'Uploading… ' + pct + '%' : 'Processing…';
            }
        });

        xhr.addEventListener('load', function() {
            bar.style.width = '100%';
            try {
                
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    label.textContent = '✓ ' + resp.message;
                    bar.style.background = '#059669';
                    // Show success banner
                    if (successBanner) {
                        successBanner.textContent = '✓ ' + resp.message;
                        successBanner.style.display = 'block';
                    }
                    // Reset form
                    form.reset();
                    // Reload just the photos grid after short delay
                    setTimeout(function() {
                        wrap.style.display = 'none';
                        btn.disabled = false;
                        btn.textContent = '📤 Upload Photo';
                        // Reload page to show new photo in list
                        window.location.hash = 'myPhotosSection';
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(resp.message || 'Upload failed');
                }
            } catch(err) {
                label.textContent = 'Upload failed. Please try again.';
                bar.style.background = '#dc2626';
                btn.disabled = false;
                btn.textContent = '📤 Upload Photo';
            }
        });

        xhr.addEventListener('error', function() {
            label.textContent = 'Network error. Please try again.';
            bar.style.background = '#dc2626';
            btn.disabled = false;
            btn.textContent = '📤 Upload Photo';
        });

        // Sequential upload - send files one at a time
        var fileInput = form.querySelector('input[type="file"]');
        var files = Array.from(fileInput.files);
        var total = files.length;
        var done = 0; var failed = 0;
        var caption  = form.querySelector('[name="caption"]') ? form.querySelector('[name="caption"]').value : '';
        var location = form.querySelector('[name="location"]') ? form.querySelector('[name="location"]').value : '';
        var taken_at = form.querySelector('[name="taken_at"]') ? form.querySelector('[name="taken_at"]').value : '';
        var latVal = document.getElementById('uploadLat') ? document.getElementById('uploadLat').value : '';
        var lngVal = document.getElementById('uploadLng') ? document.getElementById('uploadLng').value : '';

        function uploadNextMember(index) {
            if (index >= total) {
                bar.style.width='100%'; bar.style.background='#059669';
                label.textContent='✓ '+done+' photo'+(total>1?'s':'')+' uploaded!';
                form.reset(); btn.disabled=false; btn.textContent='📤 Upload Photo';
                // Photos saved as drafts - go to My Photos to review and submit
                setTimeout(function(){ window.location.href='/members/my-photos'; }, 1500);
                return;
            }
            var fd = new FormData();
            fd.append('photos[]', files[index]);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if(caption) fd.append('caption', caption);
            if(location) fd.append('location', location);
            if(taken_at) fd.append('taken_at', taken_at);
            if(latVal) fd.append('lat', latVal);
            if(lngVal) fd.append('lng', lngVal);
            fd.append('consent','1');
            label.textContent='Uploading '+(index+1)+' of '+total+'…';
            bar.style.width=Math.round(index/total*100)+'%';
            xhr = new XMLHttpRequest();
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
            xhr.setRequestHeader('Accept','application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.upload.addEventListener('progress', function(ev){
                if(ev.lengthComputable){var o=(index+ev.loaded/ev.total)/total*100;bar.style.width=Math.round(o)+'%';}
            });
            xhr.addEventListener('load', function(){ try{var r=JSON.parse(xhr.responseText);if(r.success)done++;}catch(e){failed++;} uploadNextMember(index+1); });
            xhr.addEventListener('error', function(){ failed++; uploadNextMember(index+1); });
            xhr.send(fd);
        }
        uploadNextMember(0);
        return; // prevent default XHR send below
        // xhr.send handled by sequential uploader above
    });
});

// Scroll to photos section if returning from upload
if (window.location.hash === "#myPhotosSection") {
    window.history.replaceState(null, null, window.location.pathname);
    setTimeout(function() {
        var el = document.getElementById("myPhotosSection");
        if (el) el.scrollIntoView({behavior: "smooth"});
    }, 200);
}

// EXIF popup
function showExif(id, data) {
    var modal = document.getElementById('exifModal');
    var content = document.getElementById('exifContent');
    var labels = {
        Make: 'Camera Make', Model: 'Camera Model', DateTime: 'Date/Time',
        DateTimeOriginal: 'Date Taken', ExposureTime: 'Exposure Time',
        FNumber: 'F-Number', ISOSpeedRatings: 'ISO', FocalLength: 'Focal Length',
        Flash: 'Flash', Software: 'Software', Orientation: 'Orientation',
        ImageWidth: 'Width', ImageLength: 'Height',
        GPSLatitude: 'GPS Latitude', GPSLongitude: 'GPS Longitude'
    };
    var html = '<table style="width:100%;border-collapse:collapse;">';
    for (var k in data) {
        var label = labels[k] || k;
        html += '<tr style="border-bottom:1px solid #f0f0f0;">';
        html += '<td style="padding:.4rem .5rem;font-weight:600;color:#003366;white-space:nowrap;width:45%;">' + label + '</td>';
        html += '<td style="padding:.4rem .5rem;color:#2d4a6b;">' + data[k] + '</td>';
        html += '</tr>';
    }
    html += '</table>';
    content.innerHTML = html;
    modal.style.display = 'flex';
}
function closeExif() {
    document.getElementById('exifModal').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeExif();
});
</script>

{{-- Photo Tagger Modal --}}
<div id="taggerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeTagger()">
    <div style="background:#fff;border-radius:10px;max-width:700px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.4);">
        <div style="background:#003366;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-radius:10px 10px 0 0;">
            <div style="font-size:.95rem;font-weight:700;color:#fff;">🏷 Tag People in Photo</div>
            <button onclick="closeTagger()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div style="padding:1.25rem;">
            <p style="font-size:.82rem;color:#6b7f96;margin-bottom:1rem;">Click on the photo to place a tag, then enter the callsign to identify the person.</p>
            <div style="position:relative;display:inline-block;width:100%;cursor:crosshair;" id="taggerImgWrap">
                <img id="taggerImg" src="" alt="" style="width:100%;display:block;border-radius:6px;">
                <div id="taggerDots"></div>
                <div id="taggerPendingDot" style="display:none;position:absolute;width:24px;height:24px;border-radius:50%;background:rgba(200,16,46,.7);border:2px dashed #fff;transform:translate(-50%,-50%);pointer-events:none;"></div>
            </div>
            <div id="taggerForm" style="display:none;margin-top:1rem;background:#f2f5f9;padding:1rem;border-radius:6px;">
                <div style="font-size:.8rem;font-weight:700;color:#003366;margin-bottom:.75rem;">Add tag at selected position</div>
                <div style="display:flex;gap:.5rem;align-items:flex-end;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7f96;margin-bottom:.3rem;">Callsign</label>
                        <input type="text" id="taggerCallsign" placeholder="e.g. M0ABC" style="width:100%;padding:.45rem .6rem;border:1px solid #dde2e8;border-radius:4px;font-size:.88rem;text-transform:uppercase;font-family:monospace;">
                    </div>
                    <button onclick="lookupAndTag()" style="background:#003366;color:#fff;border:none;padding:.45rem 1rem;border-radius:4px;font-size:.82rem;font-weight:bold;cursor:pointer;white-space:nowrap;">🔍 Look up & Tag</button>
                    <button onclick="cancelTag()" style="background:#f0f0f0;color:#666;border:none;padding:.45rem .8rem;border-radius:4px;font-size:.82rem;cursor:pointer;">Cancel</button>
                </div>
                <div id="taggerLookupResult" style="margin-top:.5rem;font-size:.82rem;color:#2d4a6b;"></div>
            </div>
        </div>
    </div>
</div>

<script>
var taggerPhotoId = null;
var taggerPendingX = null;
var taggerPendingY = null;

function openTagger(photoId, photoUrl) {
    taggerPhotoId = photoId;
    document.getElementById('taggerImg').src = photoUrl;
    document.getElementById('taggerDots').innerHTML = '';
    document.getElementById('taggerForm').style.display = 'none';
    document.getElementById('taggerPendingDot').style.display = 'none';
    document.getElementById('taggerCallsign').value = '';
    document.getElementById('taggerLookupResult').textContent = '';
    document.getElementById('taggerModal').style.display = 'flex';
}
function closeTagger() {
    document.getElementById('taggerModal').style.display = 'none';
    taggerPhotoId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('taggerImgWrap').addEventListener('click', function(e) {
        if (e.target.closest('#taggerDots')) return;
        var rect = this.getBoundingClientRect();
        taggerPendingX = ((e.clientX - rect.left) / rect.width * 100).toFixed(2);
        taggerPendingY = ((e.clientY - rect.top) / rect.height * 100).toFixed(2);
        var dot = document.getElementById('taggerPendingDot');
        dot.style.left = taggerPendingX + '%';
        dot.style.top  = taggerPendingY + '%';
        dot.style.display = 'block';
        document.getElementById('taggerForm').style.display = 'block';
        document.getElementById('taggerCallsign').focus();
    });

    document.getElementById('taggerCallsign').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') lookupAndTag();
        this.value = this.value.toUpperCase();
    });
});

function cancelTag() {
    document.getElementById('taggerForm').style.display = 'none';
    document.getElementById('taggerPendingDot').style.display = 'none';
    taggerPendingX = null; taggerPendingY = null;
}

function lookupAndTag() {
    var callsign = document.getElementById('taggerCallsign').value.trim().toUpperCase();
    if (!callsign || !taggerPendingX) return;

    var result = document.getElementById('taggerLookupResult');
    result.style.color = '#2d4a6b';
    result.textContent = '⏳ Checking ' + callsign + '…';

    // Directly try to save the tag — controller checks membership
    fetch('/members/photos/' + taggerPhotoId + '/tags', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            callsign: callsign,
            x_pct: parseFloat(taggerPendingX),
            y_pct: parseFloat(taggerPendingY),
        })
    })
    .then(function(r) {
        return r.json().then(function(data) {
            return { status: r.status, data: data };
        });
    })
    .then(function(res) {
        if (res.data.success) {
            var dot = document.createElement('div');
            dot.style.cssText = 'position:absolute;width:24px;height:24px;border-radius:50%;background:rgba(200,16,46,.85);border:2px solid #fff;transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;font-weight:bold;cursor:default;';
            dot.style.left = taggerPendingX + '%';
            dot.style.top  = taggerPendingY + '%';
            dot.title = res.data.name || res.data.callsign;
            document.getElementById('taggerDots').appendChild(dot);

            document.getElementById('taggerPendingDot').style.display = 'none';
            document.getElementById('taggerForm').style.display = 'none';
            document.getElementById('taggerCallsign').value = '';
            taggerPendingX = null; taggerPendingY = null;

            result.style.color = '#059669';
            result.textContent = '✓ Tagged ' + (res.data.name || res.data.callsign) + ' — they have been notified by email.';
        } else {
            result.style.color = '#dc2626';
            result.textContent = '✗ ' + (res.data.message || 'Could not add tag.');
        }
    })
    .catch(function(err) {
        result.style.color = '#dc2626';
        result.textContent = '✗ Failed to save tag. Please try again.';
    });
}
</script>
@endsection