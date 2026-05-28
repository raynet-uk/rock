{{-- resources/views/admin/super/operations.blade.php --}}
{{-- Route: GET /admin/super/operations  name: admin.super.operations --}}
{{-- Middleware: auth, verified, role:super-admin --}}
@extends('layouts.admin')

@section('title', 'Operations Centre — Super Admin')

@section('content')
@php
use Carbon\Carbon;

// ── Live data ─────────────────────────────────────────────────────────────
$totalMembers   = \App\Models\User::whereDoesntHave('roles', fn($q) => $q->where('name','super-admin'))
                    ->where('registration_pending', false)
                    ->count();

$activeMembers  = \App\Models\User::where('status', 'Active')
                    ->where('registration_pending', false)->count();

$suspendedCount = \App\Models\User::whereNotNull('suspended_at')->count();
$pendingCount   = \App\Models\User::where('registration_pending', true)->count();

// On-call = Active + available_for_callout
try {
    $onCallCount = \App\Models\User::where('status', 'Active')
                      ->where('available_for_callout', true)->count();
} catch (\Throwable $e) { $onCallCount = 0; }
$offCallCount = $activeMembers - $onCallCount;

// Roles breakdown
$roleBreakdown = \Spatie\Permission\Models\Role::withCount('users')
                    ->orderBy('name')->get();

// Recent logins (count active sessions as proxy — last_login_at may not exist)
try {
    $recentActivity = \DB::table('sessions')
                        ->whereNotNull('user_id')
                        ->where('last_activity', '>=', now()->subDays(7)->timestamp)
                        ->distinct('user_id')
                        ->count('user_id');
} catch (\Throwable $e) {
    $recentActivity = 0;
}

// Events upcoming
try {
    $upcomingEvents = \App\Models\Event::where('starts_at', '>=', now())
                        ->orderBy('starts_at')->limit(5)->get();
    $upcomingCount  = \App\Models\Event::where('starts_at', '>=', now())->count();
} catch (\Throwable $e) {
    $upcomingEvents = collect();
    $upcomingCount  = 0;
}

// LMS stats
try {
    $courseCount    = \App\Models\Course::where('is_published', true)->count();
    $completedCount = \App\Models\CourseEnrollment::whereNotNull('completed_at')->count();
} catch (\Throwable $e) {
    $courseCount = $completedCount = 0;
}

// Members with callsigns
try {
    $callsignCount = \App\Models\User::whereNotNull('callsign')
                        ->where('registration_pending', false)->count();
} catch (\Throwable $e) { $callsignCount = 0; }

// Vehicle/callout capable
try {
    $vehicleCount = \App\Models\User::where('has_vehicle', true)->count();
} catch (\Throwable $e) { $vehicleCount = 0; }

// Members with DMR IDs
try {
    $dmrCount = \App\Models\User::whereNotNull('dmr_id')->count();
} catch (\Throwable $e) { $dmrCount = 0; }

// Volunteer hours YTD
try {
    $totalHours = \App\Models\User::sum('volunteering_hours_this_year');
} catch (\Throwable $e) { $totalHours = 0; }

// Broadcast message
$broadcastMsg = \App\Models\Setting::get('broadcast_message', '');

// Alert status
$alertStatus = \App\Models\AlertStatus::query()->first();
$alertMeta   = $alertStatus?->meta();
@endphp

<style>
.ops-head {
    background: linear-gradient(135deg, #001428 0%, #002244 60%, #003366 100%);
    border-bottom: 4px solid var(--red);
    padding: 1.5rem 1.5rem 0;
    margin: -1.5rem -1rem 0;
    position: relative; overflow: hidden;
}
.ops-head::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(-45deg,transparent,transparent 28px,rgba(200,16,46,.04) 28px,rgba(200,16,46,.04) 56px);
}
.ops-head-inner {
    max-width: 1340px; margin: 0 auto;
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; position: relative; z-index: 1;
}
.ops-title { font-size: 22px; font-weight: 700; color: #fff; font-family: var(--font); }
.ops-sub { font-size: 11px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .12em; margin-top: 3px; }
.ops-live-time {
    font-family: 'Courier New', monospace; font-size: 20px; font-weight: 700;
    color: #4ade80; letter-spacing: .08em;
    background: rgba(0,0,0,.3); border: 1px solid rgba(74,222,128,.2);
    padding: .3rem .85rem; flex-shrink: 0; margin-top: 4px;
}
.ops-back {
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.4); text-decoration: none;
    border: 1px solid rgba(255,255,255,.15); padding: 4px 10px; font-family: var(--font);
    text-transform: uppercase; letter-spacing: .07em; transition: all .12s;
}
.ops-back:hover { color: #fff; background: rgba(255,255,255,.08); }

/* Status strip under header */
.ops-status-strip {
    display: flex; gap: 0; border-top: 1px solid rgba(255,255,255,.08); margin-top: 1.25rem;
    position: relative; z-index: 1;
}
.ops-status-item {
    padding: .75rem 1.25rem; display: flex; align-items: center; gap: .55rem;
    border-right: 1px solid rgba(255,255,255,.06); flex: 1;
}
.ops-status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ops-status-dot.green  { background: #4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,.15); animation: pulseGreen 2.5s ease infinite; }
.ops-status-dot.amber  { background: #fbbf24; box-shadow: 0 0 0 3px rgba(251,191,36,.15); }
.ops-status-dot.red    { background: var(--red); box-shadow: 0 0 0 3px rgba(200,16,46,.2); }
.ops-status-dot.grey   { background: #6b7f96; }
@keyframes pulseGreen { 0%,100% { box-shadow: 0 0 0 3px rgba(74,222,128,.15); } 50% { box-shadow: 0 0 0 6px rgba(74,222,128,.04); } }
.ops-status-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.35); }
.ops-status-val   { font-size: 13px; font-weight: 700; color: #fff; }

/* Wrap */
.ops-wrap { max-width: 1340px; margin: 0 auto; padding: 1.5rem 1.5rem 4rem; }

/* Grid layouts */
.ops-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
.ops-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
.ops-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
@media(max-width:1100px) { .ops-grid-3 { grid-template-columns: 1fr 1fr; } .ops-grid-4 { grid-template-columns: 1fr 1fr; } }
@media(max-width:700px)  { .ops-grid-3, .ops-grid-2, .ops-grid-4 { grid-template-columns: 1fr; } }

/* Section label */
.ops-section {
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .16em;
    color: var(--text-muted); padding: .3rem 0 .6rem;
    display: flex; align-items: center; gap: .5rem; margin-bottom: .1rem;
}
.ops-section::before { content: ''; width: 18px; height: 2px; background: var(--red); display: inline-block; }

/* Cards */
.ops-card {
    background: #fff; border: 1px solid var(--grey-mid); border-top: 3px solid var(--navy);
    box-shadow: 0 1px 4px rgba(0,51,102,.06); overflow: hidden;
}
.ops-card.accent-red    { border-top-color: var(--red); }
.ops-card.accent-green  { border-top-color: #16a34a; }
.ops-card.accent-amber  { border-top-color: #d97706; }
.ops-card.accent-purple { border-top-color: #7c3aed; }
.ops-card.accent-teal   { border-top-color: #0891b2; }

.ops-card-head {
    padding: .7rem 1rem; background: var(--grey); border-bottom: 1px solid var(--grey-mid);
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
}
.ops-card-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--navy); font-family: var(--font); }
.ops-card-badge {
    font-size: 10px; font-weight: 700; padding: 1px 7px; text-transform: uppercase; letter-spacing: .05em;
}
.ops-card-body { padding: 1rem; }

/* Big stat */
.ops-stat-big { text-align: center; padding: 1.25rem 1rem; }
.ops-stat-num { font-size: 42px; font-weight: 700; line-height: 1; font-family: var(--font); }
.ops-stat-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-top: .4rem; }
.ops-stat-sub { font-size: 11px; color: var(--grey-dark); margin-top: .2rem; }

/* On-call / off-call big tiles */
.ops-callout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.ops-callout-tile {
    padding: 1.5rem 1rem; text-align: center; border-right: 1px solid var(--grey-mid);
    position: relative;
}
.ops-callout-tile:last-child { border-right: none; }
.ops-callout-num { font-size: 48px; font-weight: 700; line-height: 1; }
.ops-callout-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; margin-top: .4rem; }
.ops-callout-tile.on  .ops-callout-num { color: #16a34a; }
.ops-callout-tile.off .ops-callout-num { color: var(--red); }
.ops-callout-tile.on  .ops-callout-lbl { color: #16a34a; }
.ops-callout-tile.off .ops-callout-lbl { color: var(--red); }
.ops-callout-bar { height: 4px; background: var(--grey-mid); margin: 0; }
.ops-callout-bar-fill { height: 100%; background: #16a34a; transition: width .6s ease; }

/* Role breakdown bars */
.ops-role-row { display: flex; align-items: center; gap: .65rem; margin-bottom: .65rem; }
.ops-role-row:last-child { margin-bottom: 0; }
.ops-role-name { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; min-width: 85px; color: var(--text-mid); }
.ops-role-bar-wrap { flex: 1; height: 8px; background: var(--grey); border: 1px solid var(--grey-mid); }
.ops-role-bar { height: 100%; transition: width .6s ease; }
.ops-role-count { font-size: 12px; font-weight: 700; color: var(--navy); min-width: 24px; text-align: right; }
.bar-sa { background: #7c3aed; }
.bar-admin { background: var(--red); }
.bar-committee { background: #d97706; }
.bar-member { background: #16a34a; }

/* Alert level display */
.ops-alert-display {
    display: flex; align-items: center; gap: 1rem; padding: .85rem 1rem;
}
.ops-alert-swatch {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; color: #fff; flex-shrink: 0;
    border: 3px solid rgba(0,0,0,.15);
}
.ops-alert-title { font-size: 15px; font-weight: 700; color: var(--navy); }
.ops-alert-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.ops-alert-msg   { font-size: 12px; font-weight: 700; color: var(--navy); margin-top: 6px; padding: .35rem .65rem; background: var(--navy-faint); border-left: 2px solid var(--navy); }
.ops-alert-change { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--navy); text-decoration: none; padding: .3rem .75rem; border: 1px solid rgba(0,51,102,.25); font-family: var(--font); transition: all .12s; }
.ops-alert-change:hover { background: var(--navy-faint); }

/* Upcoming events list */
.ops-event-row {
    display: flex; align-items: flex-start; gap: .75rem; padding: .65rem 0;
    border-bottom: 1px solid var(--grey-mid);
}
.ops-event-row:last-child { border-bottom: none; }
.ops-event-date {
    min-width: 46px; text-align: center; flex-shrink: 0;
    background: var(--navy); color: #fff; padding: .3rem .4rem;
}
.ops-event-day  { font-size: 18px; font-weight: 700; line-height: 1; }
.ops-event-mon  { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; opacity: .6; }
.ops-event-name { font-size: 13px; font-weight: 700; color: var(--navy); }
.ops-event-time { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.ops-event-empty { padding: 1.25rem; text-align: center; font-size: 12px; color: var(--grey-dark); font-style: italic; }

/* Broadcast editor */
.ops-broadcast-form { padding: .85rem 1rem; }
.ops-input {
    width: 100%; padding: .45rem .75rem; border: 1px solid var(--grey-mid);
    font-family: var(--font); font-size: 13px; color: var(--navy); outline: none;
    transition: border-color .15s;
}
.ops-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.ops-btn {
    padding: .45rem 1rem; font-family: var(--font); font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; cursor: pointer; transition: all .12s; border: 1px solid;
}
.ops-btn.navy { background: var(--navy); border-color: var(--navy); color: #fff; }
.ops-btn.navy:hover { background: #002244; }
.ops-btn.red  { background: var(--red); border-color: var(--red); color: #fff; }
.ops-btn.red:hover { background: #a50e26; }
.ops-btn.ghost { background: var(--grey); border-color: var(--grey-mid); color: var(--text-muted); }
.ops-btn.ghost:hover { border-color: var(--navy); color: var(--navy); }

/* Member callout list */
.ops-member-row {
    display: flex; align-items: center; gap: .65rem; padding: .5rem .85rem;
    border-bottom: 1px solid var(--grey-mid); font-family: var(--font);
}
.ops-member-row:last-child { border-bottom: none; }
.ops-member-av {
    width: 28px; height: 28px; border-radius: 50%; background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.ops-member-name { font-size: 13px; font-weight: 600; color: var(--navy); flex: 1; }
.ops-member-callsign { font-family: monospace; font-size: 11px; font-weight: 700; color: var(--text-muted); }
.ops-on-dot  { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }
.ops-off-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--grey-dark); flex-shrink: 0; }

/* Quick links grid */
.ops-links-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; padding: .85rem 1rem; }
.ops-link {
    display: flex; align-items: center; gap: .5rem; padding: .55rem .75rem;
    background: var(--grey); border: 1px solid var(--grey-mid);
    text-decoration: none; font-size: 12px; font-weight: 700; color: var(--navy);
    transition: all .12s; font-family: var(--font);
}
.ops-link:hover { background: var(--navy-faint); border-color: var(--navy); }
.ops-link-icon { font-size: 14px; flex-shrink: 0; }

/* System health */
.ops-health-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .55rem 1rem; border-bottom: 1px solid var(--grey-mid); font-size: 12px;
}
.ops-health-row:last-child { border-bottom: none; }
.ops-health-label { color: var(--text-muted); font-weight: 700; }
.ops-health-val   { font-weight: 700; color: var(--navy); }
.ops-health-ok    { color: #16a34a; }
.ops-health-warn  { color: #d97706; }
.ops-health-crit  { color: var(--red); }

/* Pending approvals mini */
.ops-pending-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .55rem 1rem; border-bottom: 1px solid var(--grey-mid);
    gap: .5rem; flex-wrap: wrap;
}
.ops-pending-row:last-child { border-bottom: none; }
.ops-pending-name { font-size: 13px; font-weight: 700; color: var(--navy); }
.ops-pending-email { font-size: 11px; color: var(--text-muted); }
.ops-pending-actions { display: flex; gap: .35rem; flex-shrink: 0; }

/* LRF compliance */
.ops-compliance-bar { padding: .85rem 1rem; }
.ops-compliance-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-bottom: .5rem; display: flex; justify-content: space-between; }
.ops-progress-track { height: 12px; background: var(--grey); border: 1px solid var(--grey-mid); margin-bottom: .75rem; }
.ops-progress-fill { height: 100%; transition: width .6s ease; }
</style>

{{-- ── HERO HEADER ── --}}
<div class="ops-head">
    <div class="ops-head-inner">
        <div>
            <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.16em;margin-bottom:.35rem;">
                Super Admin · {{ \App\Helpers\RaynetSetting::groupName() }} · Group {{ \App\Helpers\RaynetSetting::groupNumber() }}
            </div>
            <div class="ops-title">⚡ Operations Centre</div>
            <div class="ops-sub">Live group status · Readiness · Command tools</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;">
            <div class="ops-live-time" id="opsLiveClock">--:--:--</div>
            <a href="{{ route('admin.super.index') }}" class="ops-back">← Super Admin</a>
        </div>
    </div>

    {{-- Status strip --}}
    <div class="ops-status-strip">
        <div class="ops-status-item">
            <div class="ops-status-dot {{ $alertStatus && $alertStatus->level <= 3 ? 'amber' : 'green' }}"></div>
            <div>
                <div class="ops-status-label">Alert Level</div>
                <div class="ops-status-val">{{ $alertMeta['title'] ?? 'Level ' . ($alertStatus->level ?? 5) }}</div>
            </div>
        </div>
        <div class="ops-status-item">
            <div class="ops-status-dot {{ $onCallCount > 0 ? 'green' : 'amber' }}"></div>
            <div>
                <div class="ops-status-label">On Call</div>
                <div class="ops-status-val">{{ $onCallCount }} / {{ $activeMembers }}</div>
            </div>
        </div>
        <div class="ops-status-item">
            <div class="ops-status-dot {{ $pendingCount > 0 ? 'amber' : 'green' }}"></div>
            <div>
                <div class="ops-status-label">Pending Registrations</div>
                <div class="ops-status-val">{{ $pendingCount }}</div>
            </div>
        </div>
        <div class="ops-status-item">
            <div class="ops-status-dot {{ $broadcastMsg ? 'amber' : 'grey' }}"></div>
            <div>
                <div class="ops-status-label">Broadcast Active</div>
                <div class="ops-status-val">{{ $broadcastMsg ? 'Yes' : 'None' }}</div>
            </div>
        </div>
        <div class="ops-status-item">
            <div class="ops-status-dot green"></div>
            <div>
                <div class="ops-status-label">Portal</div>
                <div class="ops-status-val">Online</div>
            </div>
        </div>
    </div>
</div>

<div class="ops-wrap">

    @if(session('status'))
        <div style="padding:.65rem 1rem;margin-bottom:1rem;background:#eef7f2;border:1px solid rgba(22,163,74,.3);border-left:3px solid #16a34a;font-size:13px;font-weight:700;color:#14532d;">
            ✓ {{ session('status') }}
        </div>
    @endif

    {{-- ══════════════════════
         ROW 1: Readiness at a glance
    ══════════════════════ --}}
    <div class="ops-section">Group Readiness</div>
    <div class="ops-grid-4" style="margin-bottom:1.25rem;">

        {{-- On-call / off-call --}}
        <div class="ops-card accent-green" style="grid-column:span 1;">
            <div class="ops-card-head">
                <span class="ops-card-title">🟢 Callout Availability</span>
                <a href="{{ route('admin.users.index') }}" style="font-size:10px;font-weight:700;color:var(--navy);text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">Manage →</a>
            </div>
            <div class="ops-callout-grid">
                <div class="ops-callout-tile on">
                    <div class="ops-callout-num">{{ $onCallCount }}</div>
                    <div class="ops-callout-lbl">On Call</div>
                </div>
                <div class="ops-callout-tile off">
                    <div class="ops-callout-num">{{ $offCallCount }}</div>
                    <div class="ops-callout-lbl">Off Call</div>
                </div>
            </div>
            <div class="ops-callout-bar">
                <div class="ops-callout-bar-fill"
                     style="width:{{ $activeMembers > 0 ? round(($onCallCount/$activeMembers)*100) : 0 }}%;">
                </div>
            </div>
        </div>

        {{-- Total active --}}
        <div class="ops-card">
            <div class="ops-card-head">
                <span class="ops-card-title">👥 Active Members</span>
            </div>
            <div class="ops-stat-big">
                <div class="ops-stat-num" style="color:var(--navy);">{{ $activeMembers }}</div>
                <div class="ops-stat-lbl">of {{ $totalMembers }} total</div>
                <div class="ops-stat-sub">{{ $callsignCount }} licensed · {{ $vehicleCount }} with vehicles</div>
            </div>
        </div>

        {{-- DMR / Radio --}}
        <div class="ops-card accent-teal">
            <div class="ops-card-head">
                <span class="ops-card-title">📡 Radio Capability</span>
            </div>
            <div class="ops-stat-big">
                <div class="ops-stat-num" style="color:#0891b2;">{{ $dmrCount }}</div>
                <div class="ops-stat-lbl">DMR-registered</div>
                <div class="ops-stat-sub">{{ $callsignCount }} licensed operators</div>
            </div>
        </div>

        {{-- YTD hours --}}
        <div class="ops-card accent-purple">
            <div class="ops-card-head">
                <span class="ops-card-title">⏱ YTD Hours</span>
            </div>
            <div class="ops-stat-big">
                <div class="ops-stat-num" style="color:#7c3aed;">{{ number_format($totalHours, 0) }}</div>
                <div class="ops-stat-lbl">Volunteer hours</div>
                <div class="ops-stat-sub">{{ $completedCount }} training completions</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════
         ROW 2: Alert status + Broadcast + Role breakdown
    ══════════════════════ --}}
    <div class="ops-section">Command Controls</div>
    <div class="ops-grid-3" style="margin-bottom:1.25rem;">

        {{-- Alert status --}}
        <div class="ops-card accent-red">
            <div class="ops-card-head">
                <span class="ops-card-title">⚠ Alert Status</span>
                <a href="{{ route('admin.dashboard') }}#alert" style="font-size:10px;font-weight:700;color:var(--red);text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">Change →</a>
            </div>
            @if($alertStatus)
            <div class="ops-alert-display">
                <div class="ops-alert-swatch"
                     style="background:{{ $alertMeta['colour'] ?? '#22c55e' }};border-color:{{ $alertMeta['colour'] ?? '#22c55e' }};">
                    {{ $alertStatus->level }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="ops-alert-title">{{ $alertMeta['title'] ?? 'Level '.$alertStatus->level }}</div>
                    <div class="ops-alert-desc">{{ $alertMeta['description'] ?? '' }}</div>
                    @if($alertStatus->headline)
                        <div class="ops-alert-msg">{{ $alertStatus->headline }}</div>
                    @endif
                </div>
            </div>
            @else
            <div style="padding:1.25rem;text-align:center;font-size:12px;color:var(--grey-dark);">No alert status set.</div>
            @endif
            <div style="padding:.7rem 1rem;border-top:1px solid var(--grey-mid);display:flex;gap:.5rem;flex-wrap:wrap;">
                @foreach(\App\Models\AlertStatus::config() as $level => $meta)
                <form method="POST" action="{{ route('admin.alert-status.update') }}" style="display:contents;">
                    @csrf
                    <input type="hidden" name="level" value="{{ $level }}">
                    <button type="submit"
                            style="width:32px;height:32px;border:2px solid {{ ($alertStatus->level ?? 0) == $level ? '#fff' : 'transparent' }};
                                   background:{{ $meta['colour'] }};cursor:pointer;font-size:11px;font-weight:700;
                                   color:{{ in_array($level,[1,2,4]) ? '#000' : '#fff' }};
                                   box-shadow:{{ ($alertStatus->level ?? 0) == $level ? '0 0 0 2px var(--navy)' : 'none' }};
                                   transition:all .12s;"
                            title="{{ $meta['title'] }}">
                        {{ $level }}
                    </button>
                </form>
                @endforeach
                <span style="font-size:10px;color:var(--grey-dark);align-self:center;margin-left:.25rem;">Quick set</span>
            </div>
        </div>

        {{-- Broadcast message --}}
        <div class="ops-card accent-amber">
            <div class="ops-card-head">
                <span class="ops-card-title">📢 Broadcast Message</span>
                @if($broadcastMsg)
                    <span class="ops-card-badge" style="background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.25);color:#d97706;">LIVE</span>
                @else
                    <span class="ops-card-badge" style="background:var(--grey);border:1px solid var(--grey-mid);color:var(--grey-dark);">NONE</span>
                @endif
            </div>
            <div class="ops-broadcast-form">
                <form method="POST" action="{{ route('admin.settings.toggle') }}">
                    @csrf
                    <input type="hidden" name="key" value="broadcast_message">
                    <textarea name="value" class="ops-input" rows="3"
                              placeholder="Shown as a dismissable banner to all logged-in members…"
                              style="resize:none;margin-bottom:.6rem;">{{ $broadcastMsg }}</textarea>
                    <div style="display:flex;gap:.5rem;">
                        <button type="submit" class="ops-btn navy" style="flex:1;">
                            {{ $broadcastMsg ? '✓ Update' : '📢 Broadcast' }}
                        </button>
                        @if($broadcastMsg)
                        <form method="POST" action="{{ route('admin.settings.toggle') }}" style="display:contents;">
                            @csrf
                            <input type="hidden" name="key" value="broadcast_message">
                            <input type="hidden" name="value" value="">
                            <button type="submit" class="ops-btn ghost">✕ Clear</button>
                        </form>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Role breakdown --}}
        <div class="ops-card">
            <div class="ops-card-head">
                <span class="ops-card-title">🎭 Role Breakdown</span>
                <a href="{{ route('admin.users.roles') }}" style="font-size:10px;font-weight:700;color:var(--navy);text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">Manage →</a>
            </div>
            <div style="padding:.85rem 1rem;">
                @foreach($roleBreakdown as $role)
                @php
                    $barClass = match($role->name) {
                        'super-admin' => 'bar-sa',
                        'admin'       => 'bar-admin',
                        'committee'   => 'bar-committee',
                        default       => 'bar-member',
                    };
                    $pct = $totalMembers > 0 ? round(($role->users_count / $totalMembers) * 100) : 0;
                @endphp
                <div class="ops-role-row">
                    <div class="ops-role-name">{{ ucfirst($role->name) }}</div>
                    <div class="ops-role-bar-wrap">
                        <div class="ops-role-bar {{ $barClass }}" style="width:{{ $pct }}%;"></div>
                    </div>
                    <div class="ops-role-count">{{ $role->users_count }}</div>
                </div>
                @endforeach
                <div style="padding-top:.75rem;border-top:1px solid var(--grey-mid);font-size:11px;color:var(--grey-dark);display:flex;justify-content:space-between;">
                    <span>Total registered</span>
                    <strong style="color:var(--navy);">{{ $totalMembers }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════
         ROW 3: On-call members list + Upcoming events + Pending approvals
    ══════════════════════ --}}
    <div class="ops-section">Members &amp; Events</div>
    <div class="ops-grid-3" style="margin-bottom:1.25rem;">

        {{-- On-call members --}}
        <div class="ops-card accent-green">
            <div class="ops-card-head">
                <span class="ops-card-title">🟢 Members Available for Callout</span>
                <span class="ops-card-badge" style="background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#16a34a;">{{ $onCallCount }}</span>
            </div>
            @php
                try {
                    $calloutMembers = \App\Models\User::where('status','Active')
                        ->where('available_for_callout', true)
                        ->whereNull('suspended_at')
                        ->orderBy('name')
                        ->limit(8)
                        ->get();
                } catch (\Throwable $e) {
                    $calloutMembers = collect();
                }
            @endphp
            @forelse($calloutMembers as $cm)
            <div class="ops-member-row">
                <div class="ops-member-av">{{ strtoupper(substr($cm->name,0,1)) }}</div>
                <div class="ops-member-name">{{ $cm->name }}</div>
                @if($cm->callsign)
                    <div class="ops-member-callsign">{{ $cm->callsign }}</div>
                @endif
                <div class="ops-on-dot" title="Available"></div>
            </div>
            @empty
            <div style="padding:1.25rem;text-align:center;font-size:12px;color:var(--grey-dark);font-style:italic;">
                No members currently marked as available for callout.
            </div>
            @endforelse
            @if($onCallCount > 8)
            <div style="padding:.6rem 1rem;border-top:1px solid var(--grey-mid);font-size:11px;font-weight:700;color:var(--grey-dark);">
                + {{ $onCallCount - 8 }} more
            </div>
            @endif
        </div>

        {{-- Upcoming events --}}
        <div class="ops-card accent-teal">
            <div class="ops-card-head">
                <span class="ops-card-title">📅 Upcoming Events</span>
                <a href="{{ route('admin.events') }}" style="font-size:10px;font-weight:700;color:var(--navy);text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">All {{ $upcomingCount }} →</a>
            </div>
            <div style="padding:.5rem 1rem;">
                @forelse($upcomingEvents as $ev)
                <div class="ops-event-row">
                    <div class="ops-event-date">
                        <div class="ops-event-day">{{ $ev->starts_at->format('d') }}</div>
                        <div class="ops-event-mon">{{ $ev->starts_at->format('M') }}</div>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="ops-event-name">{{ Str::limit($ev->title ?? $ev->name ?? 'Event', 36) }}</div>
                        <div class="ops-event-time">{{ $ev->starts_at->format('D H:i') }}</div>
                    </div>
                </div>
                @empty
                <div class="ops-event-empty">No upcoming events scheduled.</div>
                @endforelse
            </div>
        </div>

        {{-- Pending approvals --}}
        <div class="ops-card {{ $pendingCount > 0 ? 'accent-red' : '' }}">
            <div class="ops-card-head">
                <span class="ops-card-title">⏳ Pending Registrations</span>
                @if($pendingCount > 0)
                    <span class="ops-card-badge" style="background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.25);color:var(--red);">{{ $pendingCount }}</span>
                @endif
            </div>
            @php
                $pendingUsers = \App\Models\User::where('registration_pending', true)
                    ->orderBy('created_at')->limit(5)->get();
            @endphp
            @forelse($pendingUsers as $pu)
            <div class="ops-pending-row">
                <div>
                    <div class="ops-pending-name">{{ $pu->name }}</div>
                    <div class="ops-pending-email">{{ $pu->email }} · {{ $pu->created_at->diffForHumans() }}</div>
                </div>
                <div class="ops-pending-actions">
                    <form method="POST" action="{{ route('admin.users.registration.approve', $pu->id) }}" style="display:contents;">
                        @csrf
                        <button type="submit" class="ops-btn" style="padding:3px 8px;font-size:10px;background:#eef7f2;border-color:#b8ddc9;color:#16a34a;">✓</button>
                    </form>
                    <a href="{{ route('admin.users.edit', $pu->id) }}" class="ops-btn ghost" style="padding:3px 8px;font-size:10px;text-decoration:none;">View</a>
                </div>
            </div>
            @empty
            <div style="padding:1.25rem;text-align:center;font-size:12px;color:var(--grey-dark);font-style:italic;">
                ✓ No pending registrations.
            </div>
            @endforelse
        </div>
    </div>

    {{-- ══════════════════════
         ROW 4: System health + Quick links + LRF report
    ══════════════════════ --}}
    <div class="ops-section">System &amp; Tools</div>
    <div class="ops-grid-3">

        {{-- System health --}}
        <div class="ops-card">
            <div class="ops-card-head">
                <span class="ops-card-title">⚙ System Health</span>
            </div>
            @php
                $phpVer    = PHP_VERSION;
                $dbOk      = true;
                try { \DB::select('SELECT 1'); } catch (\Throwable $e) { $dbOk = false; }
                $cacheOk   = true;
                try { \Cache::put('_ops_ping', 1, 5); $cacheOk = \Cache::get('_ops_ping') === 1; } catch (\Throwable $e) { $cacheOk = false; }
                $storageWritable = is_writable(storage_path());
                $lastClear = \App\Models\Setting::get('last_cache_clear', 'Unknown');
            @endphp
            <div class="ops-health-row">
                <span class="ops-health-label">Portal Status</span>
                <span class="ops-health-val ops-health-ok">● Online</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">Database</span>
                <span class="ops-health-val {{ $dbOk ? 'ops-health-ok' : 'ops-health-crit' }}">{{ $dbOk ? '● Connected' : '● Error' }}</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">Cache</span>
                <span class="ops-health-val {{ $cacheOk ? 'ops-health-ok' : 'ops-health-warn' }}">{{ $cacheOk ? '● Working' : '● Check config' }}</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">Storage</span>
                <span class="ops-health-val {{ $storageWritable ? 'ops-health-ok' : 'ops-health-crit' }}">{{ $storageWritable ? '● Writable' : '● Read-only' }}</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">PHP Version</span>
                <span class="ops-health-val">{{ $phpVer }}</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">Laravel</span>
                <span class="ops-health-val">{{ app()->version() }}</span>
            </div>
            <div class="ops-health-row">
                <span class="ops-health-label">Environment</span>
                <span class="ops-health-val {{ app()->isProduction() ? 'ops-health-ok' : 'ops-health-warn' }}">{{ ucfirst(app()->environment()) }}</span>
            </div>
            <div style="padding:.7rem 1rem;border-top:1px solid var(--grey-mid);display:flex;gap:.5rem;">
                <a href="{{ route('admin.super.permissions.index') }}" class="ops-btn navy" style="font-size:11px;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;">★ Permissions</a>
                <a href="{{ route('admin.settings') }}" class="ops-btn ghost" style="font-size:11px;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;">⚙ Settings</a>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="ops-card">
            <div class="ops-card-head">
                <span class="ops-card-title">🔗 Quick Links</span>
            </div>
            <div class="ops-links-grid">
                <a href="https://www.raynet-uk.net" target="_blank" class="ops-link">
                    <span class="ops-link-icon">📡</span> RAYNET-UK
                </a>
                <a href="https://www.ofcom.org.uk/manage-your-licence/amateur-radio" target="_blank" class="ops-link">
                    <span class="ops-link-icon">📋</span> Ofcom Amateur
                </a>
                <a href="https://www.metoffice.gov.uk/weather/warnings-and-advice/uk-warnings" target="_blank" class="ops-link">
                    <span class="ops-link-icon">⛈</span> Met Office
                </a>
                <a href="https://www.merseysidefire.gov.uk" target="_blank" class="ops-link">
                    <span class="ops-link-icon">🚒</span> {{ \App\Helpers\RaynetSetting::groupRegion() }} Fire
                </a>
                <a href="https://www.qrz.com" target="_blank" class="ops-link">
                    <span class="ops-link-icon">📻</span> QRZ Lookup
                </a>
                @if(config('raynet.dmr_enabled'))
                <a href="{{ route('dmr.index') }}" class="ops-link">
                    <span class="ops-link-icon">📡</span> DMR Network
                </a>
                @endif
                <a href="https://www.radioid.net" target="_blank" class="ops-link">
                    <span class="ops-link-icon">🔢</span> RadioID
                </a>
                <a href="{{ route('admin.lms.index') }}" class="ops-link">
                    <span class="ops-link-icon">🎓</span> Training LMS
                </a>
            </div>
        </div>

        {{-- LRF / Training compliance --}}
        <div class="ops-card accent-purple">
            <div class="ops-card-head">
                <span class="ops-card-title">📊 Training &amp; Compliance</span>
                <a href="{{ route('admin.lms.index') }}" style="font-size:10px;font-weight:700;color:#7c3aed;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">LMS →</a>
            </div>
            <div class="ops-compliance-bar">
                @php
                    $trainedPct = $totalMembers > 0 ? round(($completedCount / $totalMembers) * 100) : 0;
                    $calloutPct = $activeMembers > 0 ? round(($onCallCount / $activeMembers) * 100) : 0;
                    $licensedPct = $totalMembers > 0 ? round(($callsignCount / $totalMembers) * 100) : 0;
                @endphp
                <div class="ops-compliance-label">
                    <span>Training completion</span><span>{{ $trainedPct }}%</span>
                </div>
                <div class="ops-progress-track">
                    <div class="ops-progress-fill" style="width:{{ $trainedPct }}%;background:#7c3aed;"></div>
                </div>
                <div class="ops-compliance-label">
                    <span>Callout availability</span><span>{{ $calloutPct }}%</span>
                </div>
                <div class="ops-progress-track">
                    <div class="ops-progress-fill" style="width:{{ $calloutPct }}%;background:#16a34a;"></div>
                </div>
                <div class="ops-compliance-label">
                    <span>Licensed operators</span><span>{{ $licensedPct }}%</span>
                </div>
                <div class="ops-progress-track">
                    <div class="ops-progress-fill" style="width:{{ $licensedPct }}%;background:#0891b2;"></div>
                </div>
            </div>
            <div style="padding:.5rem 1rem 1rem;display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                <a href="{{ route('admin.users.index') }}" class="ops-btn navy" style="font-size:11px;text-decoration:none;text-align:center;justify-content:center;display:flex;align-items:center;">👥 Members</a>
                <a href="{{ route('admin.activity-logs.index') }}" class="ops-btn ghost" style="font-size:11px;text-decoration:none;text-align:center;justify-content:center;display:flex;align-items:center;">📋 Activity</a>
            </div>
        </div>
    </div>

</div>{{-- /ops-wrap --}}

<script>
// Live clock
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('opsLiveClock');
    if (el) el.textContent = h + ':' + m + ':' + s;
}
updateClock();
setInterval(updateClock, 1000);

// Animate bars on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ops-role-bar, .ops-progress-fill, .ops-callout-bar-fill').forEach(function(el) {
        var w = el.style.width;
        el.style.width = '0';
        setTimeout(function() { el.style.width = w; }, 150);
    });
});
</script>
@endsection