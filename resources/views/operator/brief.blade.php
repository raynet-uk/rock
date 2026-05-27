<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#003366">
    <title>Operator Brief — {{ $assignment->user->name }}</title>
    <style>
    /* ── RAYNET brand tokens ──────────────────────────────────────────────── */
    :root {
        --navy:       #003366;
        --navy-mid:   #004080;
        --navy-faint: #e8eef5;
        --navy-deep:  #001f40;
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
        --green-bdr:  #b8ddc9;
        --amber:      #8a5c00;
        --amber-bg:   #fef9ec;
        --amber-bdr:  #e8c96a;
        --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
        --shadow-md: 0 4px 16px rgba(0,51,102,.14);
    }
    *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 15px; }
    body {
        background: var(--grey); color: var(--text);
        font-family: var(--font); min-height: 100vh;
        padding-bottom: 3rem;
    }

    /* ── TOP BAR ─────────────────────────────────────────────────────────── */
    .top-bar {
        background: var(--navy); border-bottom: 3px solid var(--red);
        padding: .65rem 1.1rem;
        display: flex; align-items: center; gap: .75rem;
        position: sticky; top: 0; z-index: 100;
        box-shadow: 0 2px 10px rgba(0,0,0,.3);
    }
    .top-bar-logo {
        background: var(--red); width: 34px; height: 34px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .top-bar-logo span {
        font-size: 8px; font-weight: bold; color: #fff;
        letter-spacing: .04em; text-align: center; line-height: 1.2; text-transform: uppercase;
    }
    .top-bar-title { flex: 1; min-width: 0; }
    .top-bar-org   { font-size: 12px; font-weight: bold; color: #fff; text-transform: uppercase; letter-spacing: .04em; }
    .top-bar-sub   { font-size: 10px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .04em; margin-top: 1px; }
    .top-bar-status { flex-shrink: 0; }

    /* ── HERO CARD ───────────────────────────────────────────────────────── */
    .hero {
        background: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy) 70%, var(--navy-mid) 100%);
        padding: 1.25rem 1.1rem 1rem;
        border-bottom: 3px solid var(--red);
    }
    .hero-name {
        font-size: 22px; font-weight: bold; color: #fff; line-height: 1.1;
        margin-bottom: .2rem;
    }
    .hero-callsign {
        font-size: 13px; color: rgba(255,255,255,.65); font-weight: bold;
        letter-spacing: .08em; margin-bottom: .6rem;
    }
    .hero-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
    .hero-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
        padding: .28rem .7rem; font-size: 11px; font-weight: bold; color: rgba(255,255,255,.9);
    }

    /* ── ATTENDANCE PANEL ────────────────────────────────────────────────── */
    .attend-panel {
        margin: 1rem 1rem 0;
        border: 2px solid var(--grey-mid); background: var(--white);
        box-shadow: var(--shadow-md);
        border-radius: 2px;
    }
    .attend-panel.status-checked_in  { border-color: var(--green); }
    .attend-panel.status-on_break    { border-color: var(--amber-bdr); }
    .attend-panel.status-checked_out { border-color: var(--navy); }

    .attend-header {
        padding: .75rem 1rem; border-bottom: 1px solid var(--grey-mid);
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    }
    .attend-label {
        font-size: 11px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .1em; color: var(--text-muted);
    }
    .attend-status-badge {
        font-size: 11px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .06em; padding: 3px 10px; border: 1px solid;
    }
    .badge-not_arrived  { background: var(--grey);       border-color: var(--grey-mid);  color: var(--grey-dark); }
    .badge-checked_in   { background: var(--green-bg);   border-color: var(--green-bdr); color: var(--green); }
    .badge-on_break     { background: var(--amber-bg);   border-color: var(--amber-bdr); color: var(--amber); }
    .badge-checked_out  { background: var(--navy-faint); border-color: rgba(0,51,102,.2);color: var(--navy); }

    /* Summary row */
    .attend-summary {
        display: flex; gap: 0; border-bottom: 1px solid var(--grey-mid);
    }
    .attend-stat {
        flex: 1; padding: .6rem .5rem; text-align: center;
        border-right: 1px solid var(--grey-mid);
    }
    .attend-stat:last-child { border-right: none; }
    .attend-stat-val {
        font-size: 18px; font-weight: bold; color: var(--navy); line-height: 1;
    }
    .attend-stat-val.green { color: var(--green); }
    .attend-stat-val.amber { color: var(--amber); }
    .attend-stat-lbl {
        font-size: 9px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .1em; color: var(--text-muted); margin-top: 2px;
    }

    /* Action buttons */
    .attend-actions { padding: .85rem 1rem; display: flex; flex-direction: column; gap: .55rem; }
    .attend-btn {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        padding: .85rem 1rem; border: none; font-family: var(--font);
        font-size: 14px; font-weight: bold; cursor: pointer; transition: all .15s;
        text-transform: uppercase; letter-spacing: .06em; width: 100%;
    }
    .attend-btn-checkin  { background: var(--green);   color: #fff; }
    .attend-btn-checkin:hover  { background: #155530; }
    .attend-btn-break    { background: var(--amber-bg); color: var(--amber); border: 2px solid var(--amber-bdr); }
    .attend-btn-break:hover    { background: #fdeec5; }
    .attend-btn-endbreak { background: var(--green-bg); color: var(--green); border: 2px solid var(--green-bdr); }
    .attend-btn-endbreak:hover { background: #d5ece0; }
    .attend-btn-checkout { background: var(--navy);    color: #fff; }
    .attend-btn-checkout:hover { background: var(--navy-mid); }
    .attend-btn-icon { font-size: 18px; }

    /* Geo gate states */
    .geo-checking {
        display: flex; align-items: center; justify-content: center; gap: .6rem;
        padding: .85rem 1rem; background: var(--navy-faint); border: 1px solid rgba(0,51,102,.2);
        font-size: 13px; font-weight: bold; color: var(--navy); text-align: center;
    }
    .geo-spinner {
        width: 18px; height: 18px; border: 2px solid rgba(0,51,102,.2);
        border-top-color: var(--navy); border-radius: 50%;
        animation: spin .7s linear infinite; flex-shrink: 0;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .geo-blocked {
        padding: .85rem 1rem; background: var(--red-faint);
        border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red);
        font-size: 13px; color: var(--red); line-height: 1.5;
    }
    .geo-blocked strong { display: block; margin-bottom: .3rem; font-size: 14px; }
    .geo-blocked-dist { font-size: 12px; color: var(--text-muted); margin-top: .3rem; }
    .geo-denied {
        padding: .75rem 1rem; background: var(--amber-bg);
        border: 1px solid var(--amber-bdr); border-left: 3px solid var(--amber-bdr);
        font-size: 12px; color: var(--amber); line-height: 1.5;
    }
    .geo-override-btn {
        display: flex; align-items: center; justify-content: center; gap: .4rem;
        margin-top: .5rem; padding: .55rem 1rem; width: 100%;
        background: var(--amber-bg); border: 1px solid var(--amber-bdr);
        color: var(--amber); font-size: 12px; font-weight: bold;
        text-transform: uppercase; letter-spacing: .05em;
        font-family: var(--font); cursor: pointer;
    }
    .geo-override-btn:hover { background: #fdeec5; }

    /* ── NEW: Navigate / Compass / Live dot / POI proximity ── */
    .nav-btn {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        width: 100%; padding: .75rem 1rem; border: none; cursor: pointer;
        font-family: var(--font); font-size: 13px; font-weight: bold;
        text-transform: uppercase; letter-spacing: .06em; transition: opacity .15s;
    }
    .nav-btn-primary { background: var(--navy); color: var(--white); }
    .nav-btn-primary:hover { opacity: .88; }
    .nav-btn-secondary { background: var(--navy-faint); color: var(--navy); border: 1px solid rgba(0,51,102,.2); }
    .nav-btn-secondary:hover { background: #d0ddf0; }

    /* Compass */
    .compass-wrap {
        display: flex; flex-direction: column; align-items: center;
        padding: .85rem 1rem; background: var(--navy-faint);
        border: 1px solid rgba(0,51,102,.15);
    }
    .compass-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-bottom: .5rem; }
    .compass-info  { font-size: 12px; color: var(--text-muted); margin-top: .5rem; text-align: center; }

    /* Live dot pulse */
    @keyframes livePulse { 0%,100%{transform:scale(1);opacity:.9} 50%{transform:scale(1.5);opacity:.4} }
    .live-dot-pulse { animation: livePulse 2s ease-in-out infinite; }

    /* Nearest POI cards */
    .poi-prox-card {
        display: flex; align-items: center; gap: .7rem;
        padding: .55rem .85rem; border-bottom: 1px solid var(--grey-mid);
        background: var(--white);
    }
    .poi-prox-card:last-child { border-bottom: none; }
    .poi-prox-card.medical { background: var(--red-faint); border-left: 3px solid var(--red); }
    .poi-prox-icon  { font-size: 20px; flex-shrink: 0; width: 28px; text-align: center; }
    .poi-prox-info  { flex: 1; min-width: 0; }
    .poi-prox-name  { font-size: 13px; font-weight: bold; color: var(--text); }
    .poi-prox-dist  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
    .poi-prox-nav   { font-size: 10px; font-weight: bold; color: var(--navy); text-decoration: none; text-transform: uppercase; letter-spacing: .04em; flex-shrink: 0; }

    /* Note input inside forms */
    .attend-note-row { display: flex; gap: .4rem; padding: 0 1rem .6rem; }
    .attend-note-input {
        flex: 1; border: 1px solid var(--grey-mid); padding: .4rem .65rem;
        font-family: var(--font); font-size: 13px; color: var(--text); outline: none;
        transition: border-color .15s;
    }
    .attend-note-input:focus { border-color: var(--navy); }
    .attend-note-input::placeholder { color: var(--grey-dark); }

    /* Flash messages */
    .flash {
        margin: .75rem 1rem 0; padding: .6rem .9rem; font-size: 13px; font-weight: bold;
        border-left: 3px solid; display: flex; align-items: center; gap: .5rem;
    }
    .flash-success { background: var(--green-bg); border-color: var(--green); color: var(--green); }
    .flash-error   { background: var(--red-faint); border-color: var(--red);   color: var(--red); }

    /* ── ATTENDANCE LOG ───────────────────────────────────────────────────── */
    .log-section { margin: 1rem 1rem 0; }
    .log-title {
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .12em; color: var(--text-muted); margin-bottom: .5rem;
        display: flex; align-items: center; gap: .4rem;
    }
    .log-title::after { content: ''; flex: 1; height: 1px; background: var(--grey-mid); }
    .log-list { display: flex; flex-direction: column; gap: .3rem; }
    .log-item {
        display: flex; align-items: center; gap: .75rem;
        background: var(--white); border: 1px solid var(--grey-mid);
        padding: .5rem .85rem;
    }
    .log-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 2px solid;
    }
    .log-dot.check_in    { background: var(--green);   border-color: var(--green); }
    .log-dot.break_start { background: var(--amber-bg); border-color: var(--amber-bdr); }
    .log-dot.break_end   { background: var(--green-bg); border-color: var(--green-bdr); }
    .log-dot.check_out   { background: var(--navy-faint); border-color: var(--navy); }
    .log-type {
        font-size: 12px; font-weight: bold; color: var(--text);
        text-transform: uppercase; letter-spacing: .04em; flex: 1;
    }
    .log-type.check_in    { color: var(--green); }
    .log-type.break_start { color: var(--amber); }
    .log-type.break_end   { color: var(--green); }
    .log-type.check_out   { color: var(--navy); }
    .log-note  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
    .log-time  { font-size: 11px; font-weight: bold; color: var(--text-muted); white-space: nowrap; flex-shrink: 0; }
    .log-empty { font-size: 12px; color: var(--text-muted); font-style: italic; padding: .5rem 0; }

    /* ── BRIEF SECTIONS ──────────────────────────────────────────────────── */
    .section { margin: 1rem 1rem 0; }
    .section-head {
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .12em; color: var(--navy); margin-bottom: .5rem;
        display: flex; align-items: center; gap: .4rem; padding-bottom: .35rem;
        border-bottom: 2px solid var(--navy);
    }
    .section-card {
        background: var(--white); border: 1px solid var(--grey-mid);
        box-shadow: var(--shadow-sm);
    }

    /* Detail rows */
    .detail-row {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .6rem .85rem; border-bottom: 1px solid var(--grey-mid);
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label {
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .08em; color: var(--text-muted); flex-shrink: 0;
        width: 90px; padding-top: 1px;
    }
    .detail-val { font-size: 13px; color: var(--text); font-weight: bold; flex: 1; min-width: 0; }
    .detail-val.muted { color: var(--grey-dark); font-weight: normal; font-style: italic; }

    /* Shifts */
    .shifts-list { display: flex; flex-direction: column; gap: 0; }
    .shift-item {
        display: flex; align-items: center; gap: .75rem;
        padding: .6rem .85rem; border-bottom: 1px solid var(--grey-mid);
    }
    .shift-item:last-child { border-bottom: none; }
    .shift-badge {
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .04em; padding: 2px 8px; border: 1px solid; flex-shrink: 0;
        white-space: nowrap;
    }
    .shift-badge.work { background: var(--navy-faint); border-color: rgba(0,51,102,.25); color: var(--navy); }
    .shift-badge.brk  { background: var(--amber-bg);   border-color: var(--amber-bdr);  color: var(--amber); }
    .shift-time { font-size: 14px; font-weight: bold; color: var(--text); flex: 1; }
    .shift-label-txt { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

    /* Frequency tiers */
    .freq-tier {
        display: flex; align-items: center; gap: .75rem;
        padding: .6rem .85rem; border-bottom: 1px solid var(--grey-mid);
    }
    .freq-tier:last-child { border-bottom: none; }
    .freq-tier-badge {
        font-size: 10px; font-weight: bold; padding: 2px 7px;
        border: 1px solid; text-transform: uppercase; letter-spacing: .04em;
        flex-shrink: 0; min-width: 68px; text-align: center;
    }
    .tier-pri { background: var(--navy-faint); border-color: rgba(0,51,102,.25); color: var(--navy); }
    .tier-sec { background: var(--green-bg);   border-color: var(--green-bdr);  color: var(--green); }
    .tier-fal { background: var(--amber-bg);   border-color: var(--amber-bdr);  color: var(--amber); }
    .freq-main { font-size: 16px; font-weight: bold; color: var(--text); }
    .freq-sub  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

    /* Equipment checklist */
    .equip-list { display: flex; flex-direction: column; gap: 0; }
    .equip-item {
        display: flex; align-items: center; gap: .75rem;
        padding: .55rem .85rem; border-bottom: 1px solid var(--grey-mid);
        font-size: 13px; color: var(--text);
    }
    .equip-item:last-child { border-bottom: none; }
    .equip-check {
        width: 18px; height: 18px; border: 2px solid var(--grey-mid);
        flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    }

    /* Notes box */
    .notes-box {
        padding: .75rem .85rem; background: var(--amber-bg);
        border-left: 3px solid var(--amber-bdr); font-size: 13px; color: var(--text);
        line-height: 1.5;
    }

    /* Emergency box */
    .emergency-box {
        padding: .75rem .85rem; background: var(--red-faint);
        border-left: 3px solid var(--red); font-size: 13px; color: var(--text);
    }
    .emergency-label {
        font-size: 10px; font-weight: bold; text-transform: uppercase;
        letter-spacing: .08em; color: var(--red); margin-bottom: .35rem;
    }
    .emergency-name  { font-size: 15px; font-weight: bold; color: var(--text); }
    .emergency-phone {
        font-size: 18px; font-weight: bold; color: var(--red);
        text-decoration: none; display: block; margin-top: .25rem;
    }

    /* Footer */
    .page-footer {
        margin: 1.5rem 1rem 0; padding: .75rem;
        text-align: center; font-size: 10px; color: var(--text-muted);
        border-top: 1px solid var(--grey-mid); line-height: 1.6;
    }

    /* Utility */
    @keyframes fadeUp { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }
    @keyframes routeMarch { from { stroke-dashoffset: 24; } to { stroke-dashoffset: 0; } }
    .route-animated { stroke-dasharray: 16 8; animation: routeMarch 0.6s linear infinite; }
    .fade-in { animation: fadeUp .25s ease both; }

    @media (min-width: 600px) {
        body { max-width: 560px; margin: 0 auto; }
    }
    </style>
</head>
<body>

{{-- TOP BAR --}}
<div class="top-bar fade-in">
    <div class="top-bar-logo"><span>RAY<br>NET</span></div>
    <div class="top-bar-title">
        <div class="top-bar-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
        <div class="top-bar-sub">Operator Brief</div>
    </div>
    <div class="top-bar-status">
        <span class="attend-status-badge badge-{{ $assignment->attendance_status }}">
            {{ $assignment->attendanceLabel() }}
        </span>
    </div>
</div>

{{-- HERO --}}
<div class="hero fade-in">
    <div class="hero-name">{{ $assignment->user->name }}</div>
    @if ($assignment->callsign)
        <div class="hero-callsign">{{ $assignment->callsign }}</div>
    @endif
    <div class="hero-chips">
        <span class="hero-chip">
            🏷 {{ $assignment->role ?: 'No role assigned' }}
        </span>
        <span class="hero-chip">
            📅 {{ $assignment->event->starts_at?->format('D j M Y') ?? '—' }}
        </span>
        @if ($assignment->event->location)
            <span class="hero-chip">📍 {{ $assignment->event->location }}</span>
        @endif
        @if ($assignment->event->is_private)
            <span class="hero-chip" style="background:rgba(200,16,46,.2);border-color:rgba(200,16,46,.5);font-weight:700;">🔒 Members Only</span>
        @endif
        @if ($assignment->event->supporting_group)
            <span class="hero-chip" style="background:rgba(200,16,46,.12);border-color:rgba(200,16,46,.35);font-weight:700;">🤝 Supporting: {{ $assignment->event->supporting_group }}</span>
        @endif
        <span class="hero-chip" style="background:{{ match($assignment->status) {
            'confirmed' => 'rgba(26,107,60,.35)',
            'standby'   => 'rgba(138,92,0,.35)',
            'declined'  => 'rgba(200,16,46,.35)',
            default     => 'rgba(255,255,255,.12)',
        } }};border-color:{{ match($assignment->status) {
            'confirmed' => 'rgba(26,107,60,.6)',
            'standby'   => 'rgba(138,92,0,.6)',
            'declined'  => 'rgba(200,16,46,.6)',
            default     => 'rgba(255,255,255,.25)',
        } }};">
            {{ $assignment->statusLabel() }}
        </span>
    </div>
</div>

{{-- FLASH MESSAGES --}}
@if (session('attend_success'))
    <div class="flash flash-success fade-in">✓ {{ session('attend_success') }}</div>
@endif
@if (session('attend_error'))
    <div class="flash flash-error fade-in">⚠ {{ session('attend_error') }}</div>
@endif

{{-- ═══════════════ ATTENDANCE PANEL ═══════════════ --}}
@php
    $status     = $assignment->attendance_status;
    $dutyMins   = $assignment->dutyMinutes();
    $breakMins  = $assignment->totalBreakMinutes();
    $log        = array_reverse($assignment->attendance_log ?? []);

    // Check-in time for display
    $checkInTime = null;
    foreach (array_reverse($log) as $entry) {
        if (($entry['type'] ?? '') === 'check_in') { $checkInTime = $entry['time']; break; }
    }
    $log = array_reverse($log); // restore chronological for display

    $dutyDisplay  = $dutyMins !== null
        ? (floor($dutyMins/60) > 0 ? floor($dutyMins/60).'h ' : '') . ($dutyMins%60).'m'
        : '—';
    $breakDisplay = $breakMins > 0
        ? (floor($breakMins/60) > 0 ? floor($breakMins/60).'h ' : '') . ($breakMins%60).'m'
        : '—';
@endphp

<div class="attend-panel status-{{ $status }} fade-in">

    <div class="attend-header">
        <span class="attend-label">Attendance</span>
        <span class="attend-status-badge badge-{{ $status }}">{{ $assignment->attendanceLabel() }}</span>
    </div>

    @if ($status !== 'not_arrived')
    <div class="attend-summary">
        <div class="attend-stat">
            <div class="attend-stat-val green">
                {{ $checkInTime ? \Carbon\Carbon::parse($checkInTime)->format('H:i') : '—' }}
            </div>
            <div class="attend-stat-lbl">Checked In</div>
        </div>
        <div class="attend-stat">
            <div class="attend-stat-val {{ $dutyMins !== null ? 'green' : '' }}">{{ $dutyDisplay }}</div>
            <div class="attend-stat-lbl">Duty Time</div>
        </div>
        <div class="attend-stat">
            <div class="attend-stat-val {{ $breakMins > 0 ? 'amber' : '' }}">{{ $breakDisplay }}</div>
            <div class="attend-stat-lbl">On Break</div>
        </div>
    </div>
    @endif

    <div class="attend-actions">

        @if ($assignment->canCheckIn())
        {{-- Geolocation-gated check-in --}}
        <div id="geo-checkin-wrap">
            {{-- Initial state: button that triggers geo check --}}
            <button type="button"
                    class="attend-btn attend-btn-checkin"
                    id="geo-checkin-btn"
                    onclick="requestGeoCheckIn()">
                <span class="attend-btn-icon">✓</span> Check In
            </button>
        </div>

        {{-- Hidden real form — only submitted after geo passes --}}
        <form method="POST"
              action="{{ route('operator.brief.check-in', $assignment->briefing_token) }}"
              id="checkin-form"
              style="display:none;">
            @csrf
            <input type="hidden" name="note" id="checkin-note" value="">
        </form>

        @elseif ($assignment->attendance_status === 'not_arrived')
        {{-- Status is not_arrived but canCheckIn() returned false — must be wrong date --}}
        @php
            $eventDate  = $assignment->event->starts_at?->timezone('Europe/London');
            $today      = now()->timezone('Europe/London');
            $isEarly    = $eventDate && $today->lt($eventDate->startOfDay());
            $daysAway   = $eventDate ? (int) $today->startOfDay()->diffInDays($eventDate->startOfDay(), false) : null;
        @endphp
        <div style="padding:.85rem 1rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);border-left:3px solid var(--navy);font-size:13px;color:var(--navy);line-height:1.5;">
            @if ($isEarly && $daysAway !== null)
                <strong style="display:block;margin-bottom:.25rem;">⏳ Check-in not yet available</strong>
                This event is on <strong>{{ $eventDate->format('l j F') }}</strong>
                @if ($daysAway === 1) — tomorrow. @elseif ($daysAway > 1) — in {{ $daysAway }} days. @endif
                <div style="font-size:11px;color:var(--text-muted);margin-top:.4rem;">Check-in will open on the day of the event.</div>
            @else
                <strong style="display:block;margin-bottom:.25rem;">⚠ Check-in unavailable</strong>
                This event's check-in window has passed.
            @endif
        </div>
        @endif

        @if ($assignment->canStartBreak())
        <form method="POST" action="{{ route('operator.brief.break-start', $assignment->briefing_token) }}" id="break-form">
            @csrf
            <input type="text" class="attend-note-input" name="note"
                   placeholder="Break reason (optional, e.g. Lunch)…"
                   style="margin-bottom:.45rem;display:block;width:100%;">
            <button type="submit" class="attend-btn attend-btn-break">
                <span class="attend-btn-icon">⏸</span> Start Break
            </button>
        </form>
        @endif

        @if ($assignment->canEndBreak())
        <form method="POST" action="{{ route('operator.brief.break-end', $assignment->briefing_token) }}">
            @csrf
            <input type="hidden" name="note" value="">
            <button type="submit" class="attend-btn attend-btn-endbreak">
                <span class="attend-btn-icon">▶</span> End Break
            </button>
        </form>
        @endif

        @if ($assignment->canCheckOut())
        <form method="POST" action="{{ route('operator.brief.check-out', $assignment->briefing_token) }}" id="checkout-form">
            @csrf
            <input type="text" class="attend-note-input" name="note"
                   placeholder="Checkout note (optional)…"
                   style="margin-bottom:.45rem;display:block;width:100%;">
            <button type="submit" class="attend-btn attend-btn-checkout"
                    onclick="return confirm('Check out and end your shift?')">
                <span class="attend-btn-icon">⏹</span> Check Out
            </button>
        </form>
        @endif

        @if ($status === 'checked_out')
        <div style="text-align:center;padding:.5rem;font-size:13px;color:var(--text-muted);">
            You have checked out. Thank you for today.
        </div>
        @endif

    </div>
</div>

{{-- ═══════════════ ATTENDANCE LOG ═══════════════ --}}
@if (!empty($assignment->attendance_log))
<div class="log-section fade-in">
    <div class="log-title">Attendance Log</div>
    <div class="log-list">
        @foreach (array_reverse($assignment->attendance_log) as $entry)
        @php
            $typeLabels = [
                'check_in'    => '✓ Checked In',
                'break_start' => '⏸ Break Started',
                'break_end'   => '▶ Break Ended',
                'check_out'   => '⏹ Checked Out',
            ];
            $typeLabel = $typeLabels[$entry['type']] ?? $entry['type'];
            $timeFormatted = \Carbon\Carbon::parse($entry['time'])->format('H:i');
            $dateFormatted = \Carbon\Carbon::parse($entry['time'])->format('D j M');
        @endphp
        <div class="log-item">
            <div class="log-dot {{ $entry['type'] }}"></div>
            <div style="flex:1;min-width:0;">
                <div class="log-type {{ $entry['type'] }}">{{ $typeLabel }}</div>
                @if (!empty($entry['note']))
                    <div class="log-note">{{ $entry['note'] }}</div>
                @endif
            </div>
            <div class="log-time">{{ $timeFormatted }}<br><span style="font-size:10px;font-weight:normal;">{{ $dateFormatted }}</span></div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══════════════ SCHEDULE ═══════════════ --}}
@php
    $shifts = $assignment->shifts ?? [];
    if (empty($shifts) && ($assignment->start_time || $assignment->end_time)) {
        $shifts = [[
            'type'  => 'shift',
            'start' => $assignment->start_time ? substr($assignment->start_time, 0, 5) : null,
            'end'   => $assignment->end_time   ? substr($assignment->end_time,   0, 5) : null,
            'label' => '',
        ]];
    }
@endphp

<div class="section fade-in">
    <div class="section-head">🕐 Your Schedule</div>
    <div class="section-card">

        {{-- Report / depart --}}
        @if ($assignment->report_time || $assignment->depart_time)
        <div class="detail-row" style="background:var(--navy-faint);">
            @if ($assignment->report_time)
            <div style="flex:1;">
                <div class="detail-label">Report</div>
                <div class="detail-val" style="font-size:16px;">{{ substr($assignment->report_time,0,5) }}</div>
            </div>
            @endif
            @if ($assignment->depart_time)
            <div style="flex:1;">
                <div class="detail-label">Depart</div>
                <div class="detail-val" style="font-size:16px;">{{ substr($assignment->depart_time,0,5) }}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Shifts and breaks --}}
        @if (!empty($shifts))
        <div class="shifts-list">
            @foreach ($shifts as $sh)
            @php $isBreak = ($sh['type'] ?? 'shift') === 'break'; @endphp
            <div class="shift-item">
                <span class="shift-badge {{ $isBreak ? 'brk' : 'work' }}">
                    {{ $isBreak ? '⏸ Break' : '▶ Shift' }}
                </span>
                <div>
                    <div class="shift-time">
                        {{ $sh['start'] ?? '?' }} – {{ $sh['end'] ?? '?' }}
                    </div>
                    @if (!empty($sh['label']))
                    <div class="shift-label-txt">{{ $sh['label'] }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:.75rem .85rem;font-size:13px;color:var(--text-muted);font-style:italic;">No shift times set.</div>
        @endif

    </div>
</div>

{{-- ═══════════════ POSITION ═══════════════ --}}
@if ($assignment->location_name || $assignment->grid_ref || $assignment->what3words || ($assignment->lat && $assignment->lng))
<div class="section fade-in">
    <div class="section-head">📍 Your Position</div>
    <div class="section-card">

        @if ($assignment->location_name)
        <div class="detail-row">
            <div class="detail-label">Location</div>
            <div class="detail-val">{{ $assignment->location_name }}</div>
        </div>
        @endif

        @if ($assignment->grid_ref)
        <div class="detail-row">
            <div class="detail-label">OS Grid</div>
            <div class="detail-val">{{ $assignment->grid_ref }}</div>
        </div>
        @endif

        @if ($assignment->what3words)
        <div class="detail-row">
            <div class="detail-label">What3Words</div>
            <div class="detail-val">
                <a href="https://what3words.com/{{ $assignment->what3words }}"
                   target="_blank" style="color:var(--red);text-decoration:none;font-weight:bold;">
                    ///{{ $assignment->what3words }}
                </a>
            </div>
        </div>
        @endif

        @php
            $hasOpPin    = $assignment->lat && $assignment->lng;
            $hasEventMap = !empty($eventPolygon) || !empty($eventRoute) || !empty($eventPois) || !empty($eventPin);
        @endphp
        @if ($hasOpPin || $hasEventMap)
        <div class="detail-row" style="padding:0;">
            <div id="mini-map" style="width:100%;height:260px;"></div>
        </div>
        @endif

        {{-- Navigate to position --}}
        @if ($hasOpPin)
        <div class="detail-row" style="padding:.6rem .85rem;gap:.5rem;flex-direction:column;">
            <button class="nav-btn nav-btn-primary" onclick="navigateToPosition()">
                🗺 Navigate to My Position
            </button>
            <div id="nav-dist-info" style="font-size:11px;color:var(--text-muted);text-align:center;display:none;"></div>
        </div>
        @endif

        {{-- Live compass bearing to position --}}
        @if ($hasOpPin)
        <div class="detail-row" style="padding:0;" id="compass-section" style="display:none;">
            <div class="compass-wrap">
                <div class="compass-label">Compass to your position</div>
                <svg id="compass-svg" width="100" height="100" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="46" fill="rgba(0,51,102,.06)" stroke="rgba(0,51,102,.15)" stroke-width="1"/>
                    <circle cx="50" cy="50" r="2" fill="rgba(0,51,102,.3)"/>
                    <!-- Cardinal labels -->
                    <text x="50" y="12" text-anchor="middle" font-size="9" font-weight="bold" font-family="Arial" fill="rgba(0,51,102,.5)">N</text>
                    <text x="88" y="54" text-anchor="middle" font-size="9" font-family="Arial" fill="rgba(0,51,102,.4)">E</text>
                    <text x="50" y="95" text-anchor="middle" font-size="9" font-family="Arial" fill="rgba(0,51,102,.4)">S</text>
                    <text x="12" y="54" text-anchor="middle" font-size="9" font-family="Arial" fill="rgba(0,51,102,.4)">W</text>
                    <!-- Needle group — rotated by JS -->
                    <g id="compass-needle">
                        <polygon points="50,14 46,50 54,50" fill="#C8102E"/>
                        <polygon points="50,86 46,50 54,50" fill="rgba(0,51,102,.3)"/>
                    </g>
                    <circle cx="50" cy="50" r="4" fill="#003366"/>
                </svg>
                <div class="compass-info" id="compass-info">Getting location…</div>
            </div>
        </div>
        @endif

    </div>
</div>
@endif

{{-- Nearest POIs panel — shown when GPS is available and event has POIs --}}
@if (!empty($eventPois))
<div class="section fade-in" id="nearest-pois-section" style="display:none;">
    <div class="section-head">🚩 Nearest Points of Interest</div>
    <div class="section-card" style="padding:0;">
        <div id="nearest-pois-list"></div>
    </div>
</div>
@endif

{{-- ═══════════════ FREQUENCIES ═══════════════ --}}
@if ($assignment->frequency || $assignment->secondary_frequency || $assignment->fallback_frequency)
<div class="section fade-in">
    <div class="section-head">📻 Frequencies</div>
    <div class="section-card">

        @if ($assignment->frequency)
        <div class="freq-tier">
            <span class="freq-tier-badge tier-pri">★ Primary</span>
            <div>
                <div class="freq-main">{{ $assignment->frequency }}</div>
                <div class="freq-sub">
                    {{ $assignment->mode }}
                    @if ($assignment->ctcss_tone) · CTCSS {{ $assignment->ctcss_tone }} @endif
                    @if ($assignment->channel_label) · {{ $assignment->channel_label }} @endif
                </div>
            </div>
        </div>
        @endif

        @if ($assignment->secondary_frequency)
        <div class="freq-tier">
            <span class="freq-tier-badge tier-sec">Secondary</span>
            <div>
                <div class="freq-main">{{ $assignment->secondary_frequency }}</div>
                <div class="freq-sub">
                    {{ $assignment->secondary_mode ?? 'FM' }}
                    @if ($assignment->secondary_ctcss) · CTCSS {{ $assignment->secondary_ctcss }} @endif
                </div>
            </div>
        </div>
        @endif

        @if ($assignment->fallback_frequency)
        <div class="freq-tier">
            <span class="freq-tier-badge tier-fal">Fallback</span>
            <div>
                <div class="freq-main">{{ $assignment->fallback_frequency }}</div>
                <div class="freq-sub">
                    {{ $assignment->fallback_mode ?? 'FM' }}
                    @if ($assignment->fallback_ctcss) · CTCSS {{ $assignment->fallback_ctcss }} @endif
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endif

{{-- ═══════════════ EQUIPMENT ═══════════════ --}}
@php
    $equipItems = $assignment->equipment_items ?? [];
    if (empty($equipItems) && $assignment->equipment) {
        $equipItems = array_filter(array_map('trim', explode(',', $assignment->equipment)));
    }
@endphp
@if (!empty($equipItems))
<div class="section fade-in">
    <div class="section-head">🎒 Equipment to Bring</div>
    <div class="section-card">
        <div class="equip-list">
            @foreach ($equipItems as $item)
            <div class="equip-item">
                <div class="equip-check"></div>
                {{ $item }}
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ═══════════════ BRIEFING NOTES ═══════════════ --}}
@if ($assignment->briefing_notes)
<div class="section fade-in">
    <div class="section-head">📋 Briefing Notes</div>
    <div class="section-card">
        <div class="notes-box">{{ $assignment->briefing_notes }}</div>
    </div>
</div>
@endif

{{-- ═══════════════ EVENT DETAILS ═══════════════ --}}
<div class="section fade-in">
    <div class="section-head">📅 Event Details</div>
    <div class="section-card">
        <div class="detail-row">
            <div class="detail-label">Event</div>
            <div class="detail-val">{{ $assignment->event->title }}</div>
        </div>
        @if ($assignment->event->starts_at)
        <div class="detail-row">
            <div class="detail-label">Date</div>
            <div class="detail-val">{{ $assignment->event->starts_at->format('l j F Y') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Time</div>
            <div class="detail-val">
                {{ $assignment->event->starts_at->format('H:i') }}
                @if ($assignment->event->ends_at)
                    – {{ $assignment->event->ends_at->format('H:i') }}
                @endif
            </div>
        </div>
        @endif
        @if ($assignment->event->location)
        <div class="detail-row">
            <div class="detail-label">Location</div>
            <div class="detail-val">{{ $assignment->event->location }}</div>
        </div>
        @endif
        @if ($assignment->event->description)
        <div class="detail-row">
            <div class="detail-label">Info</div>
            <div class="detail-val" style="font-weight:normal;">{{ $assignment->event->description }}</div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════ EMERGENCY CONTACT ═══════════════ --}}
@if ($assignment->emergency_contact_name)
<div class="section fade-in">
    <div class="section-head" style="color:var(--red);">🆘 Emergency Contact</div>
    <div class="section-card">
        <div class="emergency-box">
            <div class="emergency-label">In case of emergency contact</div>
            <div class="emergency-name">{{ $assignment->emergency_contact_name }}</div>
            @if ($assignment->emergency_contact_phone)
            <a href="tel:{{ $assignment->emergency_contact_phone }}" class="emergency-phone">
                {{ $assignment->emergency_contact_phone }}
            </a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- FOOTER --}}
<div class="page-footer fade-in">
    {{ \App\Helpers\RaynetSetting::groupName() }} Group ({{ \App\Helpers\RaynetSetting::groupNumber() }}) · Member of RAYNET-UK<br>
    This page is for authorised personnel only · {{ now()->format('j M Y') }}
</div>

{{-- Mini map — shows operator position, event route, site polygon and POIs --}}
@php
    $hasOpPin    = $assignment->lat && $assignment->lng;
    $hasEventMap = !empty($eventPolygon) || !empty($eventRoute) || !empty($eventPois) || !empty($eventPin);
@endphp
@if ($hasOpPin || $hasEventMap)
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data passed from controller ──────────────────────────────────────────
    const OP_LAT       = {{ $hasOpPin ? (float)$assignment->lat : 'null' }};
    const OP_LNG       = {{ $hasOpPin ? (float)$assignment->lng : 'null' }};
    const OP_LABEL     = '{{ addslashes($assignment->location_name ?: $assignment->user->name) }}';
    const EVENT_PIN    = {!! isset($eventPin)     && $eventPin     ? json_encode($eventPin)     : 'null' !!};
    const EVENT_POLY   = {!! isset($eventPolygon) && $eventPolygon ? json_encode($eventPolygon) : 'null' !!};
    @php
        // Normalise event_route to array format for brief page
        $briefRoutes = [];
        if (!empty($eventRoute)) {
            if (isset($eventRoute['type'])) {
                // Legacy single geometry
                $briefRoutes = [['id' => 'r-legacy', 'name' => 'Event Route', 'geometry' => $eventRoute]];
            } elseif (is_array($eventRoute) && isset($eventRoute[0])) {
                $briefRoutes = $eventRoute;
            }
        }
    @endphp
    const EVENT_ROUTES = {!! json_encode($briefRoutes) !!};
    const EVENT_POIS   = {!! isset($eventPois)    && $eventPois    ? json_encode($eventPois)    : '[]'   !!};

    // ── Determine initial centre ─────────────────────────────────────────────
    let initLat = OP_LAT || (EVENT_PIN ? EVENT_PIN.lat : 53.4084);
    let initLng = OP_LNG || (EVENT_PIN ? EVENT_PIN.lng : -2.9916);
    let initZoom = 15;

    const map = L.map('mini-map', {
        center: [initLat, initLng],
        zoom:   initZoom,
        zoomControl:      true,
        scrollWheelZoom:  false,
        dragging:         true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);

    const allRouteLayers = [];

    // ── Site boundary polygon + inverse mask ─────────────────────────────────
    if (EVENT_POLY && EVENT_POLY.coordinates) {
        const polyLayer = L.geoJSON(
            { type: 'Feature', geometry: EVENT_POLY },
            { style: { color: '#003366', weight: 2, opacity: 0.8, fillColor: '#003366', fillOpacity: 0.06, dashArray: '6 3' } }
        ).addTo(map);

        polyLayer.bindTooltip('<span style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;">Site Boundary</span>', { sticky: true });

        const worldRing = [[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];
        const isMulti   = EVENT_POLY.type === 'MultiPolygon';
        const polys     = isMulti ? EVENT_POLY.coordinates : [EVENT_POLY.coordinates];
        polys.forEach(function(rings) {
            L.geoJSON(
                { type:'Feature', geometry:{ type:'Polygon', coordinates:[worldRing, rings[0]] } },
                { style:{ color:'transparent', weight:0, fillColor:'#001f40', fillOpacity:0.35 }, interactive:false }
            ).addTo(map);
        });

        try { map.fitBounds(polyLayer.getBounds(), { padding: [16, 16] }); } catch(e) {}
    }

    // ── Event routes with marching-ants animation ─────────────────────────────
    EVENT_ROUTES.forEach(function(routeObj) {
        const geometry  = routeObj.geometry || routeObj;
        const routeName = routeObj.name || 'Route';
        if (!geometry || !geometry.coordinates) return;

        // White glow
        L.geoJSON({ type:'Feature', geometry },
            { style: { color:'#fff', weight:7, opacity:0.28, lineCap:'round', lineJoin:'round' }, interactive:false }
        ).addTo(map);

        // Solid base
        L.geoJSON({ type:'Feature', geometry },
            { style: { color:'#7c3aed', weight:4, opacity:0.3, lineCap:'round', lineJoin:'round' }, interactive:false }
        ).addTo(map);

        // Animated overlay
        const routeLayer = L.geoJSON({ type:'Feature', geometry },
            { style: { color:'#7c3aed', weight:4, opacity:1, lineCap:'butt', lineJoin:'round' } }
        ).addTo(map);
        allRouteLayers.push(routeLayer);

        setTimeout(function() {
            routeLayer.eachLayer(function(l) {
                const el = l.getElement ? l.getElement() : null;
                if (el) el.classList.add('route-animated');
            });
        }, 120);

        // Distance
        let totalKm = 0;
        try {
            const rc = geometry.type === 'LineString' ? geometry.coordinates : geometry.coordinates.flat();
            for (let i = 1; i < rc.length; i++) {
                totalKm += L.latLng(rc[i-1][1], rc[i-1][0]).distanceTo(L.latLng(rc[i][1], rc[i][0])) / 1000;
            }
        } catch(e) {}
        const dist = totalKm > 0 ? ` · ${totalKm < 1 ? Math.round(totalKm*1000)+'m' : totalKm.toFixed(1)+'km'}` : '';
        routeLayer.bindTooltip(`<strong style="font-size:10px;text-transform:uppercase;letter-spacing:.04em;">${routeName}${dist}</strong>`, { sticky:true });

        // START / FINISH labels + midpoint arrow
        const rc = geometry.type === 'LineString' ? geometry.coordinates : geometry.coordinates[0];
        if (rc && rc.length >= 2) {
            const mkPill = (text, bg) => L.divIcon({
                className: '',
                html: `<div style="background:${bg};color:#fff;font-size:9px;font-weight:bold;padding:2px 6px;white-space:nowrap;border:1.5px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);font-family:Arial,sans-serif;">${text}</div>`,
                iconAnchor: [24, 9],
            });
            try {
                const mid = Math.floor(rc.length / 2);
                const angle = Math.atan2(rc[mid][1] - rc[mid-1][1], rc[mid][0] - rc[mid-1][0]) * 180 / Math.PI;
                L.marker([rc[mid][1], rc[mid][0]], {
                    icon: L.divIcon({
                        className:'',
                        html:`<div style="width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-bottom:14px solid #7c3aed;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4));transform:rotate(${angle-90}deg);transform-origin:center;"></div>`,
                        iconSize:[14,14], iconAnchor:[7,7],
                    }), interactive:false
                }).addTo(map);
            } catch(e) {}

            const multiRoute = EVENT_ROUTES.length > 1;
            L.marker([rc[0][1], rc[0][0]], { icon: mkPill(multiRoute ? `▶ ${routeName}` : '▶ START', '#1a6b3c'), interactive:false }).addTo(map);
            L.marker([rc[rc.length-1][1], rc[rc.length-1][0]], { icon: mkPill(multiRoute ? `⬛ ${routeName} End` : '⬛ FINISH', '#C8102E'), interactive:false }).addTo(map);
        }
    });

    // Fit to routes if no polygon
    if (!EVENT_POLY && allRouteLayers.length > 0) {
        try { map.fitBounds(L.featureGroup(allRouteLayers).getBounds(), { padding: [20, 20] }); } catch(e) {}
    }

    // ── POI markers ──────────────────────────────────────────────────────────
    const POI_META = {
        entrance: { emoji:'🚪', label:'Entrance'  },
        exit:     { emoji:'🚪', label:'Exit'       },
        car_park: { emoji:'🅿',  label:'Car Park'  },
        medical:  { emoji:'🩺', label:'Medical'    },
        control:  { emoji:'📡', label:'Control'    },
        hazard:   { emoji:'⚠',  label:'Hazard'     },
        info:     { emoji:'ℹ',  label:'Info Point' },
        custom:   { emoji:'🚩', label:'POI'        },
    };

    (EVENT_POIS || []).forEach(function (poi) {
        const meta   = POI_META[poi.type] || POI_META.custom;
        const colour = poi.colour || '#C8102E';
        const icon   = L.divIcon({
            className: '',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:${colour};border:2px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.4);font-size:13px;line-height:1;">${meta.emoji}</div>`,
            iconSize: [26,26], iconAnchor: [13,26], popupAnchor: [0,-28],
        });
        L.marker([poi.lat, poi.lng], { icon, title: poi.name || meta.label })
            .bindPopup(`<div style="font-family:Arial,sans-serif;min-width:130px;">
                <strong style="color:#003366;display:block;margin-bottom:3px;">${poi.name || meta.label}</strong>
                ${poi.description ? `<div style="font-size:11px;color:#6b7f96;margin-bottom:3px;">${poi.description}</div>` : ''}
                ${poi.grid_ref ? `<div style="font-size:11px;font-weight:bold;letter-spacing:.04em;color:#003366;margin-bottom:2px;">📍 ${poi.grid_ref}</div>` : ''}
                ${poi.w3w ? `<div style="font-size:11px;color:#e65c00;font-weight:bold;">/// ${poi.w3w}</div>` : ''}
            </div>`)
            .addTo(map);
    });

    // ── Operator position marker ─────────────────────────────────────────────
    if (OP_LAT && OP_LNG) {
        const opIcon = L.divIcon({
            className: '',
            html: '<div style="width:28px;height:28px;background:#003366;border:3px solid #C8102E;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 6px rgba(0,0,0,.4);"></div>',
            iconSize: [28,28], iconAnchor: [14,28], popupAnchor: [0,-30],
        });
        L.marker([OP_LAT, OP_LNG], { icon: opIcon })
            .addTo(map)
            .bindPopup(`<strong style="font-family:Arial,sans-serif;color:#003366;">${OP_LABEL}</strong><div style="font-size:10px;color:#6b7f96;font-family:Arial,sans-serif;">Your assigned position</div>`)
            .openPopup();
    }

    // ── Live "You are here" dot — tracks GPS position after check-in ──────────
    let liveMarker = null, liveLine = null;
    function startLiveTracking() {
        if (!navigator.geolocation) return;
        navigator.geolocation.watchPosition(function(pos) {
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            const liveIcon = L.divIcon({
                className:'',
                html:`<div style="position:relative;width:16px;height:16px;">
                    <div class="live-dot-pulse" style="position:absolute;top:0;left:0;width:16px;height:16px;border-radius:50%;background:rgba(0,122,255,.25);"></div>
                    <div style="position:absolute;top:3px;left:3px;width:10px;height:10px;border-radius:50%;background:#007AFF;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>
                </div>`,
                iconSize:[16,16], iconAnchor:[8,8],
            });
            if (liveMarker) liveMarker.setLatLng([lat,lng]);
            else liveMarker = L.marker([lat,lng], {icon:liveIcon, interactive:false}).addTo(map);
            if (OP_LAT && OP_LNG) {
                const distM = L.latLng(lat,lng).distanceTo(L.latLng(OP_LAT,OP_LNG));
                const distStr = distM < 1000 ? Math.round(distM)+'m' : (distM/1000).toFixed(2)+'km';
                if (liveLine) liveMarker._map && map.removeLayer(liveLine);
                liveLine = L.polyline([[lat,lng],[OP_LAT,OP_LNG]], {
                    color:'#007AFF', weight:1.5, dashArray:'4 4', opacity:.6, interactive:false
                }).addTo(map);
                const info = document.getElementById('nav-dist-info');
                if (info) { info.style.display='block'; info.textContent=`You are ${distStr} from your position`; }
                // Show nearest POIs using live location
                showNearestPois(lat, lng);
            }
        }, null, { enableHighAccuracy:true, maximumAge:5000 });
    }
    // Start tracking if already checked in
    const opStatus = '{{ $assignment->attendance_status }}';
    if (opStatus === 'checked_in' || opStatus === 'on_break') {
        startLiveTracking();
    }

});
</script>
@endif

{{-- Live clock for the check-in panel --}}
<script>
(function () {
    const status = '{{ $assignment->attendance_status }}';
    if (status === 'checked_in' || status === 'on_break') {
        setTimeout(() => location.reload(), 60000);
    }
})();
</script>

{{-- Geolocation gate for check-in --}}
@if ($assignment->canCheckIn())
<script>
// ── Event geo data from server ─────────────────────────────────────────────
const TEST_MODE    = {{ request()->routeIs('operator.brief.test') ? 'true' : 'false' }};
const GEO_POLYGON  = {!! isset($eventPolygon) && $eventPolygon ? json_encode($eventPolygon) : 'null' !!};
const GEO_PIN      = {!! isset($eventPin)     && $eventPin     ? json_encode($eventPin)     : 'null' !!};
const MAX_MILES    = 1;          // allowed radius if no polygon set
const MAX_METRES   = MAX_MILES * 1609.344;

// ── Haversine distance (metres) ────────────────────────────────────────────
function haversine(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const toRad = x => x * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    const a = Math.sin(dLat/2)**2 +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ── Ray-casting point-in-polygon ───────────────────────────────────────────
// Works on GeoJSON coordinate arrays [lng, lat]
function pointInPolygon(lat, lng, polyCoords) {
    // polyCoords is the first ring of a GeoJSON Polygon: [[lng,lat], ...]
    let inside = false;
    const x = lng, y = lat;
    for (let i = 0, j = polyCoords.length - 1; i < polyCoords.length; j = i++) {
        const xi = polyCoords[i][0], yi = polyCoords[i][1];
        const xj = polyCoords[j][0], yj = polyCoords[j][1];
        const intersect = ((yi > y) !== (yj > y)) &&
                          (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}

// Check if point is inside any ring of a Polygon or MultiPolygon geometry
function isInsideEventPolygon(lat, lng, geo) {
    if (!geo || !geo.coordinates) return false;
    if (geo.type === 'Polygon') {
        return pointInPolygon(lat, lng, geo.coordinates[0]);
    }
    if (geo.type === 'MultiPolygon') {
        return geo.coordinates.some(poly => pointInPolygon(lat, lng, poly[0]));
    }
    return false;
}

// ── Distance to polygon boundary (rough: distance to centroid) ─────────────
function distToPolygonCentroid(lat, lng, geo) {
    if (!geo || !geo.coordinates) return Infinity;
    const ring = geo.type === 'MultiPolygon' ? geo.coordinates[0][0] : geo.coordinates[0];
    const sumLat = ring.reduce((s,c) => s + c[1], 0) / ring.length;
    const sumLng = ring.reduce((s,c) => s + c[0], 0) / ring.length;
    return haversine(lat, lng, sumLat, sumLng);
}

// ── Main gate logic ─────────────────────────────────────────────────────────
function requestGeoCheckIn() {
    if (TEST_MODE) { submitCheckIn('Test mode — geo bypassed'); return; }
    const wrap = document.getElementById('geo-checkin-wrap');

    // No geo data at all — allow straight through
    if (!GEO_POLYGON && !GEO_PIN) {
        document.getElementById('checkin-form').submit();
        return;
    }

    // Geolocation not supported
    if (!navigator.geolocation) {
        showGeoUnavailable(wrap);
        return;
    }

    // Show spinner while waiting
    wrap.innerHTML = `
        <div class="geo-checking">
            <div class="geo-spinner"></div>
            Getting your location…
        </div>`;

    navigator.geolocation.getCurrentPosition(
        function(pos) { onGeoSuccess(pos, wrap); },
        function(err)  { onGeoError(err, wrap);  },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

function onGeoSuccess(pos, wrap) {
    const userLat = pos.coords.latitude;
    const userLng = pos.coords.longitude;
    const accuracy = pos.coords.accuracy; // metres

    let allowed  = false;
    let distInfo = '';

    if (GEO_POLYGON) {
        // Primary check: inside the drawn site polygon
        const inside = isInsideEventPolygon(userLat, userLng, GEO_POLYGON);
        if (inside) {
            allowed  = true;
            distInfo = 'You are inside the event site.';
        } else {
            // Fallback: within 1 mile of polygon centroid
            const distM = distToPolygonCentroid(userLat, userLng, GEO_POLYGON);
            const distMiles = (distM / 1609.344).toFixed(2);
            if (distM <= MAX_METRES) {
                allowed  = true;
                distInfo = `You are ${distMiles} miles from the event site.`;
            } else {
                distInfo = `You are ${distMiles} miles from the event site (limit: ${MAX_MILES} mile).`;
            }
        }
    } else if (GEO_PIN) {
        // No polygon — check against event pin
        const distM = haversine(userLat, userLng, GEO_PIN.lat, GEO_PIN.lng);
        const distMiles = (distM / 1609.344).toFixed(2);
        if (distM <= MAX_METRES) {
            allowed  = true;
            distInfo = `You are ${distMiles} miles from the event location.`;
        } else {
            distInfo = `You are ${distMiles} miles from the event location (limit: ${MAX_MILES} mile).`;
        }
    }

    if (allowed) {
        // Pass location accuracy as a note for the log
        document.getElementById('checkin-note').value =
            `GPS: ${userLat.toFixed(5)},${userLng.toFixed(5)} ±${Math.round(accuracy)}m`;
        document.getElementById('checkin-form').submit();
    } else {
        showGeoBlocked(wrap, distInfo, userLat, userLng);
    }
}

function onGeoError(err, wrap) {
    const msgs = {
        1: 'Location permission was denied. Please allow location access in your browser settings and try again.',
        2: 'Your location could not be determined. Please check that GPS is enabled.',
        3: 'Location request timed out. Please try again.',
    };
    const msg = msgs[err.code] || 'An unknown location error occurred.';

    wrap.innerHTML = `
        <div class="geo-denied">
            <strong>📍 Location required</strong>
            ${msg}
        </div>
        <button type="button" class="geo-override-btn" onclick="requestGeoCheckIn()">
            ↻ Try Again
        </button>
        <button type="button" class="geo-override-btn"
                style="margin-top:.3rem;color:var(--text-muted);border-color:var(--grey-mid);"
                onclick="showManualOverride()">
            ⚠ Check In Without Location
        </button>`;
}

function showGeoBlocked(wrap, distInfo, userLat, userLng) {
    wrap.innerHTML = `
        <div class="geo-blocked">
            <strong>⛔ Too Far from Event Site</strong>
            You must be on-site or within ${MAX_MILES} mile to check in.
            <div class="geo-blocked-dist">${distInfo}</div>
            ${userLat ? `<div class="geo-blocked-dist" style="margin-top:4px;font-size:10px;color:#9aa3ae;">Your location: ${userLat.toFixed(5)}, ${userLng.toFixed(5)}</div>` : ''}
        </div>
        <button type="button" class="geo-override-btn" onclick="requestGeoCheckIn()" style="margin-top:.5rem;">
            ↻ Try Again
        </button>
        <button type="button" class="geo-override-btn"
                style="margin-top:.3rem;color:var(--text-muted);border-color:var(--grey-mid);"
                onclick="showManualOverride()">
            ⚠ Check In Anyway (manual override)
        </button>`;
}

function showGeoUnavailable(wrap) {
    // Browser has no geolocation at all — just let them check in
    wrap.innerHTML = `
        <div class="geo-denied">
            📍 Location services are not available on this device. Checking in without location verification.
        </div>`;
    setTimeout(() => document.getElementById('checkin-form').submit(), 1500);
}

function showManualOverride() {
    if (!confirm('Check in without location verification?\n\nThis will be logged. Only use this if you are genuinely on-site and GPS is unavailable.')) {
        return;
    }
    document.getElementById('checkin-note').value = 'Manual override — location not verified';
    document.getElementById('checkin-form').submit();
}
</script>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FEATURE 1 — Navigate to My Position
     Detects iOS vs Android and opens the correct maps app.
     Also shows estimated walking distance from current GPS.
══════════════════════════════════════════════════════════════ --}}
@if ($assignment->lat && $assignment->lng)
<script>
const NAV_LAT = {{ (float)$assignment->lat }};
const NAV_LNG = {{ (float)$assignment->lng }};
const NAV_LABEL = '{{ addslashes($assignment->location_name ?: $assignment->user->name) }}';

function navigateToPosition() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const encodedLabel = encodeURIComponent(NAV_LABEL);
    const url = isIOS
        ? `maps://maps.apple.com/?daddr=${NAV_LAT},${NAV_LNG}&dirflg=w`
        : `https://maps.google.com/?daddr=${NAV_LAT},${NAV_LNG}&travelmode=walking`;
    // Try to show distance first
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const distM = haversineMetres(pos.coords.latitude, pos.coords.longitude, NAV_LAT, NAV_LNG);
            const distStr = distM < 1000 ? Math.round(distM)+'m' : (distM/1000).toFixed(2)+'km';
            const walkMins = Math.round(distM / 80); // ~80m per minute walking
            const info = document.getElementById('nav-dist-info');
            if (info) { info.style.display='block'; info.textContent=`${distStr} away · ~${walkMins} min walk`; }
            window.open(url, '_blank');
        }, function() { window.open(url, '_blank'); }, { timeout:5000 });
    } else {
        window.open(url, '_blank');
    }
}

function haversineMetres(lat1, lng1, lat2, lng2) {
    const R=6371000, toRad=d=>d*Math.PI/180;
    const dLat=toRad(lat2-lat1), dLng=toRad(lng2-lng1);
    const a=Math.sin(dLat/2)**2+Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLng/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}
</script>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FEATURE 2 — Live compass bearing to assigned position
     Uses DeviceOrientationEvent. Updates needle as phone rotates.
     Requests permission on iOS 13+ with a user gesture.
══════════════════════════════════════════════════════════════ --}}
@if ($assignment->lat && $assignment->lng)
<script>
(function() {
    const DEST_LAT = {{ (float)$assignment->lat }};
    const DEST_LNG = {{ (float)$assignment->lng }};
    const section  = document.getElementById('compass-section');
    const needle   = document.getElementById('compass-needle');
    const info     = document.getElementById('compass-info');
    if (!section) return;

    let userLat = null, userLng = null;

    // Compute bearing from user to destination
    function bearingToDest(lat1, lng1, lat2, lng2) {
        const toRad = d => d * Math.PI / 180;
        const dLng = toRad(lng2 - lng1);
        const y = Math.sin(dLng) * Math.cos(toRad(lat2));
        const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) - Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLng);
        return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
    }

    function startCompass() {
        section.style.display = 'block';

        if (!navigator.geolocation) {
            if (info) info.textContent = 'Location unavailable on this device.';
            return;
        }

        if (info) info.textContent = 'Getting your location…';

        navigator.geolocation.getCurrentPosition(function(pos) {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;
            const distM      = haversineMetres(userLat, userLng, DEST_LAT, DEST_LNG);
            const destBearing = bearingToDest(userLat, userLng, DEST_LAT, DEST_LNG);
            const distStr    = distM < 1000 ? Math.round(distM)+'m' : (distM/1000).toFixed(2)+'km';

            // Rotate needle to destination bearing from north (best we can without device heading)
            if (needle) needle.setAttribute('transform', `rotate(${destBearing} 50 50)`);

            // Show useful info — orientation message only if the API exists
            const hasOrientation = typeof DeviceOrientationEvent !== 'undefined';
            if (info) {
                info.textContent = `${distStr} away · ${Math.round(destBearing)}° from north`;
                if (hasOrientation) {
                    info.textContent += ' · Enable compass for live heading';
                }
            }
        }, function() {
            if (info) info.textContent = 'Location permission denied.';
        }, { timeout: 10000, enableHighAccuracy: true });

        // Device heading handler — updates needle relative to which way phone is pointing
        function handleOrientation(e) {
            if (!userLat) return;
            const heading    = e.webkitCompassHeading ?? (360 - (e.alpha ?? 0));
            const destBearing = bearingToDest(userLat, userLng, DEST_LAT, DEST_LNG);
            const needleAngle = destBearing - heading;
            if (needle) needle.setAttribute('transform', `rotate(${needleAngle} 50 50)`);
            const distM   = haversineMetres(userLat, userLng, DEST_LAT, DEST_LNG);
            const distStr = distM < 1000 ? Math.round(distM)+'m' : (distM/1000).toFixed(2)+'km';
            if (info) info.textContent = `${distStr} · ${Math.round(destBearing)}° · Heading ${Math.round(heading)}°`;
        }

        if (typeof DeviceOrientationEvent !== 'undefined') {
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                // iOS 13+ — needs a user gesture; add a button
                const existingBtn = section.querySelector('.compass-perm-btn');
                if (!existingBtn) {
                    const permBtn = document.createElement('button');
                    permBtn.className = 'nav-btn nav-btn-secondary compass-perm-btn';
                    permBtn.style.marginTop = '.5rem';
                    permBtn.textContent = '🧭 Enable Live Compass';
                    permBtn.onclick = function() {
                        DeviceOrientationEvent.requestPermission().then(state => {
                            if (state === 'granted') {
                                window.addEventListener('deviceorientation', handleOrientation, true);
                                permBtn.remove();
                                if (info) info.textContent = 'Compass active — point phone toward destination';
                            }
                        }).catch(() => {});
                    };
                    section.querySelector('.compass-wrap')?.appendChild(permBtn);
                }
            } else {
                // Android / desktop — permission not required, just attach
                window.addEventListener('deviceorientation', handleOrientation, true);
            }
        }
    }

    // Auto-start compass after check-in
    const opStatus = '{{ $assignment->attendance_status }}';
    if (opStatus === 'checked_in' || opStatus === 'on_break') {
        startCompass();
    }
    // Also expose for manual trigger
    window.startCompassManual = startCompass;
})();
</script>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FEATURE 3 — Start live tracking when operator checks in
     (live dot is already wired inside the mini-map DOMContentLoaded block)
     This block also starts tracking immediately after page load
     if status is checked_in (handles refresh case).
══════════════════════════════════════════════════════════════ --}}
@if (in_array($assignment->attendance_status, ['checked_in', 'on_break']))
<script>
// Show compass section now
document.addEventListener('DOMContentLoaded', function() {
    const cs = document.getElementById('compass-section');
    if (cs) cs.style.display = 'block';
});
</script>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FEATURE 4 — Nearest POIs panel
     Calculates distance from GPS to each event POI.
     Shows 3 closest; medical POIs always at top regardless of distance.
══════════════════════════════════════════════════════════════ --}}
@if (!empty($eventPois))
<script>
const BRIEF_POIS = {!! json_encode($eventPois) !!};
const POI_META_BRIEF = {
    entrance:{ emoji:'🚪' }, exit:{ emoji:'🚪' }, car_park:{ emoji:'🅿' },
    medical:{ emoji:'🩺' }, control:{ emoji:'📡' }, hazard:{ emoji:'⚠' },
    info:{ emoji:'ℹ' }, custom:{ emoji:'🚩' }
};

function showNearestPois(userLat, userLng) {
    const section = document.getElementById('nearest-pois-section');
    const list    = document.getElementById('nearest-pois-list');
    if (!section || !list) return;

    const withDist = BRIEF_POIS.map(poi => ({
        ...poi,
        distM: haversineMetres(userLat, userLng, poi.lat, poi.lng)
    }));

    // Medical POIs always first regardless of distance
    const medical  = withDist.filter(p => p.type === 'medical').sort((a,b) => a.distM - b.distM);
    const others   = withDist.filter(p => p.type !== 'medical').sort((a,b) => a.distM - b.distM);
    const shown    = [...medical, ...others].slice(0, medical.length + 3);

    section.style.display = 'block';
    list.innerHTML = shown.map(poi => {
        const emoji   = (POI_META_BRIEF[poi.type] || POI_META_BRIEF.custom).emoji;
        const distStr = poi.distM < 1000 ? Math.round(poi.distM)+'m' : (poi.distM/1000).toFixed(2)+'km';
        const isMed   = poi.type === 'medical';
        const navUrl  = /iPad|iPhone|iPod/.test(navigator.userAgent)
            ? `maps://maps.apple.com/?daddr=${poi.lat},${poi.lng}`
            : `https://maps.google.com/?daddr=${poi.lat},${poi.lng}`;
        return `<div class="poi-prox-card${isMed?' medical':''}">
            <div class="poi-prox-icon">${emoji}</div>
            <div class="poi-prox-info">
                <div class="poi-prox-name">${poi.name || poi.type.replace('_',' ')}</div>
                <div class="poi-prox-dist">${distStr} away${poi.description ? ' · '+poi.description : ''}</div>
                ${poi.grid_ref ? `<div style="font-size:10px;font-weight:bold;letter-spacing:.04em;color:#003366;margin-top:2px;">📍 ${poi.grid_ref}</div>` : ''}
                ${poi.w3w ? `<div style="font-size:10px;color:#e65c00;font-weight:bold;margin-top:1px;">/// ${poi.w3w}</div>` : ''}
            </div>
            <a href="${navUrl}" target="_blank" class="poi-prox-nav">Navigate →</a>
        </div>`;
    }).join('');
}

// Auto-show if already have GPS from the live tracking
const opStatusForPoi = '{{ $assignment->attendance_status }}';
if ((opStatusForPoi === 'checked_in' || opStatusForPoi === 'on_break') && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        showNearestPois(pos.coords.latitude, pos.coords.longitude);
    }, null, { timeout:8000 });
}
// Expose globally for live tracking dot callback
window.showNearestPois = showNearestPois;
</script>
@endif

</body>
</html>