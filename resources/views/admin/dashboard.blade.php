@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

@php
    $alertStatus   = \App\Models\AlertStatus::query()->first();
    $alertMeta     = $alertStatus?->meta();
    $currentLevel  = $alertStatus->level ?? 5;
    $currentColour = $alertMeta['colour'] ?? '#22c55e';
    $textColour    = in_array($currentLevel, [1,2,4], true) ? '#0b1120' : '#fff';
    $levelConfig   = [];
    foreach (\App\Models\AlertStatus::config() as $l => $meta) {
        $levelConfig[$l] = ['colour'=>$meta['colour'],'title'=>$meta['title'],'description'=>$meta['description'],'textColour'=>in_array($l,[1,2,4])?'#0b1120':'#fff'];
    }
    try { $approvalRequired = \App\Models\Setting::registrationApprovalRequired(); $settingAvailable = true; } catch(\Throwable $e) { $approvalRequired = true; $settingAvailable = false; }
    try { $raynetAutoApproval = filter_var(\App\Models\Setting::get('raynet_email_auto_approval',true),FILTER_VALIDATE_BOOLEAN); } catch(\Throwable $e) { $raynetAutoApproval = true; }
    try { $extraDomainsRaw = \App\Models\Setting::get('auto_approve_domains','[]'); $extraDomains = array_values(array_filter(json_decode($extraDomainsRaw,true)??[],fn($d)=>!empty(trim($d)))); } catch(\Throwable $e) { $extraDomains = []; }
    try { $pendingUsers = \App\Models\User::where('registration_pending',true)->orderBy('created_at')->get(); $totalPending = $pendingUsers->count(); } catch(\Throwable $e) { $pendingUsers = collect(); $totalPending = 0; }
    $totalUsers    = \App\Models\User::count();
    $totalAdmins   = \App\Models\User::where('is_admin',true)->count();
    $totalCallsign = \App\Models\User::whereNotNull('callsign')->count();
    try { $totalLoggedHours = \App\Models\ActivityLog::sum('hours'); $totalLogEntries = \App\Models\ActivityLog::count(); } catch(\Throwable $e) { $totalLoggedHours=0; $totalLogEntries=0; }
    $canAccessLogs = auth()->user()->is_super_admin ?? false;
    $isSuperAdmin  = auth()->user()->is_super_admin ?? false;
    $upcomingEvents = \App\Models\Event::where('starts_at','>=',now())->count();
    try { $modCount = \DB::table('modules')->count(); $modActive = \DB::table('modules')->where('enabled',true)->count(); } catch(\Throwable $e) { $modCount=0; $modActive=0; }
@endphp

<style>
:root {
    --navy:#003366; --navy-mid:#004080; --navy-faint:#e8eef5; --navy-darker:#002244;
    --red:#C8102E; --red-faint:#fdf0f2;
    --grey:#f4f5f7; --grey-mid:#dde2e8; --grey-dark:#9aa3ae;
    --text:#001f40; --text-muted:#6b7f96;
    --green:#1a6b3c; --green-bg:#eef7f2;
    --amber:#92400e; --amber-bg:#fffbeb;
    --purple:#5b21b6; --purple-bg:#f5f3ff;
    --teal:#0e7490; --teal-bg:#ecfeff;
    --shadow-sm:0 1px 3px rgba(0,51,102,.09);
    --shadow-md:0 4px 14px rgba(0,51,102,.11);
    --font:Arial,'Helvetica Neue',Helvetica,sans-serif;
}

/* ── Layout ──────────────────────────────────────────── */
.dash { font-family:var(--font); color:var(--text); font-size:13px; }

/* ── Welcome panel ───────────────────────────────────── */
.welcome-panel {
    background:#fff;
    border:1px solid var(--grey-mid);
    border-left:4px solid var(--navy);
    margin-bottom:1.25rem;
    box-shadow:var(--shadow-sm);
    overflow:hidden;
}
.welcome-panel__head {
    background:var(--navy);
    padding:.85rem 1.25rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.welcome-panel__title {
    display:flex; align-items:center; gap:.65rem;
    font-size:1rem; font-weight:bold; color:#fff;
    letter-spacing:-.01em;
}
.welcome-panel__logo {
    width:28px; height:28px; background:var(--red);
    display:flex; align-items:center; justify-content:center;
    font-size:7px; font-weight:bold; color:#fff; text-align:center;
    line-height:1.2; text-transform:uppercase; letter-spacing:.04em; flex-shrink:0;
}
.welcome-panel__date { font-size:11px; color:rgba(255,255,255,.5); }
.welcome-panel__body { padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.welcome-panel__greeting { font-size:14px; color:var(--text-muted); }
.welcome-panel__greeting strong { color:var(--navy); font-size:16px; display:block; margin-bottom:.2rem; }
.welcome-panel__actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.wp-action {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.4rem .85rem; font-size:12px; font-weight:bold;
    font-family:var(--font); cursor:pointer; text-decoration:none;
    transition:all .12s; white-space:nowrap; border:1px solid;
}
.wp-action--primary { background:var(--navy); border-color:var(--navy); color:#fff; }
.wp-action--primary:hover { background:var(--navy-mid); }
.wp-action--outline { background:#fff; border-color:var(--grey-mid); color:var(--navy); }
.wp-action--outline:hover { background:var(--navy-faint); border-color:var(--navy); }
.wp-action--red { background:var(--red); border-color:var(--red); color:#fff; }
.wp-action--red:hover { background:#a00d25; }

/* ── Pending banner ──────────────────────────────────── */
.pending-banner {
    background:var(--red-faint); border:1px solid rgba(200,16,46,.3);
    border-left:4px solid var(--red);
    padding:.75rem 1.1rem; margin-bottom:1.25rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    box-shadow:var(--shadow-sm);
}
.pending-banner__text { font-size:13px; font-weight:bold; color:var(--red); display:flex; align-items:center; gap:.5rem; }
.pending-banner__sub  { font-size:11px; color:rgba(200,16,46,.7); font-weight:normal; margin-top:.1rem; }

/* ── Dashboard grid ──────────────────────────────────── */
.dash-grid { display:grid; grid-template-columns:1fr 320px; gap:1.1rem; align-items:start; }
@media(max-width:1000px){ .dash-grid { grid-template-columns:1fr; } }
.dash-col-left  { display:flex; flex-direction:column; gap:1.1rem; }
.dash-col-right { display:flex; flex-direction:column; gap:1.1rem; }

/* ── Widget box (WordPress style) ────────────────────── */
.widget {
    background:#fff;
    border:1px solid var(--grey-mid);
    box-shadow:var(--shadow-sm);
}
.widget__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:.6rem 1rem;
    background:var(--grey);
    border-bottom:1px solid var(--grey-mid);
    cursor:default;
}
.widget__title {
    font-size:11px; font-weight:bold; text-transform:uppercase;
    letter-spacing:.08em; color:var(--navy);
    display:flex; align-items:center; gap:.45rem;
}
.widget__link {
    font-size:11px; color:var(--navy); text-decoration:none; font-weight:bold;
    letter-spacing:.02em; opacity:.6; transition:opacity .12s;
}
.widget__link:hover { opacity:1; }
.widget__body { padding:1rem; }
.widget--alert { border-top:3px solid var(--red); }
.widget--pending { border-top:3px solid var(--red); }
.widget--green  { border-top:3px solid var(--green); }
.widget--amber  { border-top:3px solid #c49a00; }
.widget--purple { border-top:3px solid var(--purple); }
.widget--teal   { border-top:3px solid var(--teal); }
.widget--navy   { border-top:3px solid var(--navy); }

/* ── At a glance ─────────────────────────────────────── */
.glance-grid { display:grid; grid-template-columns:1fr 1fr; gap:1px; background:var(--grey-mid); border:1px solid var(--grey-mid); margin-bottom:.85rem; }
.glance-item { background:#fff; padding:.75rem .9rem; display:flex; align-items:center; gap:.65rem; }
.glance-num  { font-size:1.5rem; font-weight:bold; line-height:1; color:var(--navy); font-variant-numeric:tabular-nums; flex-shrink:0; min-width:36px; }
.glance-label{ font-size:11px; color:var(--text-muted); font-weight:bold; text-transform:uppercase; letter-spacing:.06em; line-height:1.3; }
.glance-num.red    { color:var(--red); }
.glance-num.green  { color:var(--green); }
.glance-num.amber  { color:#d97706; }
.glance-num.purple { color:var(--purple); }
.glance-num.teal   { color:var(--teal); }

/* Alert level indicator */
.alert-indicator { display:flex; align-items:center; gap:.75rem; padding:.75rem .9rem; background:var(--grey); border:1px solid var(--grey-mid); margin-bottom:.75rem; }
.alert-swatch { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:bold; border:3px solid; flex-shrink:0; }
.alert-info__level { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); }
.alert-info__title { font-size:14px; font-weight:bold; color:var(--navy); margin-top:.1rem; }
.alert-info__desc  { font-size:11px; color:var(--text-muted); margin-top:.15rem; }

/* Alert form */
.af-label { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.09em; color:var(--text-muted); display:block; margin-bottom:.3rem; }
.af-input  { width:100%; border:1px solid var(--grey-mid); padding:.4rem .65rem; font-size:12px; font-family:var(--font); color:var(--text); outline:none; transition:border-color .15s; box-sizing:border-box; }
.af-input:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(0,51,102,.07); }
.af-row { display:flex; gap:.6rem; flex-wrap:wrap; margin-bottom:.6rem; }
.af-field { flex:1; min-width:120px; }
.af-dots { display:flex; gap:.3rem; flex-wrap:wrap; margin-bottom:.75rem; }
.level-dot { width:36px; height:36px; border:2px solid transparent; cursor:pointer; font-size:13px; font-weight:bold; transition:all .15s; flex-shrink:0; }
.level-dot.active { border-color:var(--navy); box-shadow:0 0 0 2px var(--navy); transform:scale(1.1); }

/* Module cards (compact) */
.mod-list { display:flex; flex-direction:column; gap:0; }
.mod-item { display:flex; align-items:center; gap:.75rem; padding:.65rem .9rem; border-bottom:1px solid var(--grey-mid); text-decoration:none; color:var(--text); transition:background .1s; }
.mod-item:last-child { border-bottom:none; }
.mod-item:hover { background:var(--navy-faint); }
.mod-icon { width:32px; height:32px; background:var(--navy-faint); border:1px solid rgba(0,51,102,.15); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.mod-text  { flex:1; }
.mod-title { font-size:12px; font-weight:bold; color:var(--navy); }
.mod-desc  { font-size:11px; color:var(--text-muted); margin-top:1px; }
.mod-pills { display:flex; gap:.3rem; flex-wrap:wrap; margin-left:auto; }
.mod-arrow { font-size:12px; color:var(--grey-dark); transition:all .12s; margin-left:.5rem; }
.mod-item:hover .mod-arrow { color:var(--red); transform:translateX(2px); }

/* Stat pills */
.sp { font-size:10px; font-weight:bold; padding:1px 6px; border:1px solid; letter-spacing:.03em; white-space:nowrap; }
.sp-blue   { background:var(--navy-faint); border-color:rgba(0,51,102,.25); color:var(--navy); }
.sp-green  { background:var(--green-bg);   border-color:#b8ddc9;           color:var(--green); }
.sp-amber  { background:var(--amber-bg);   border-color:rgba(146,64,14,.25);color:var(--amber); }
.sp-red    { background:var(--red-faint);  border-color:rgba(200,16,46,.25);color:var(--red); }
.sp-purple { background:var(--purple-bg);  border-color:rgba(91,33,182,.25);color:var(--purple); }
.sp-teal   { background:var(--teal-bg);    border-color:rgba(14,116,144,.25);color:var(--teal); }
.sp-grey   { background:var(--grey); border-color:var(--grey-mid); color:var(--text-muted); }

/* Quick links */
.quick-links { display:flex; flex-direction:column; gap:0; }
.quick-link-item { display:flex; align-items:center; gap:.65rem; padding:.6rem .9rem; border-bottom:1px solid var(--grey-mid); text-decoration:none; color:var(--navy); font-size:12px; font-weight:bold; transition:background .1s; border-left:3px solid transparent; }
.quick-link-item:last-child { border-bottom:none; }
.quick-link-item:hover { background:var(--navy-faint); border-left-color:var(--red); color:var(--red); }
.quick-link-item.ql-red { border-left-color:var(--red); color:var(--red); background:var(--red-faint); }
.quick-link-item.ql-purple { border-left-color:var(--purple); color:var(--purple); }

/* Toggle buttons */
.toggle-btn { display:inline-flex; align-items:center; gap:.5rem; padding:.38rem .85rem; border:1px solid; font-family:var(--font); font-size:11px; font-weight:bold; cursor:pointer; text-transform:uppercase; letter-spacing:.05em; transition:all .12s; }
.toggle-btn.is-on  { background:var(--navy); border-color:var(--navy); color:#fff; }
.toggle-btn.is-off { background:var(--grey); border-color:var(--grey-mid); color:var(--text-muted); }
.toggle-btn.is-on:hover  { background:var(--navy-mid); }
.toggle-btn.is-off:hover { border-color:var(--navy); color:var(--navy); }
.toggle-track { display:inline-flex; align-items:center; width:30px; height:16px; border-radius:8px; padding:2px; flex-shrink:0; transition:background .2s; }
.toggle-track.on  { background:#22c55e; }
.toggle-track.off { background:var(--grey-mid); }
.toggle-knob { width:12px; height:12px; border-radius:50%; background:#fff; display:block; flex-shrink:0; transition:transform .2s; }
.toggle-knob.on { transform:translateX(14px); }
.toggle-knob.off { transform:translateX(0); }

/* Pending table */
.pending-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.pending-tbl thead { background:var(--navy); }
.pending-tbl th { padding:.45rem .75rem; text-align:left; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.75); }
.pending-tbl th:last-child { text-align:right; }
.pending-tbl tr { border-top:1px solid var(--grey-mid); transition:background .1s; }
.pending-tbl tr:hover { background:var(--navy-faint); }
.pending-tbl td { padding:.55rem .75rem; }
.pending-tbl td:last-child { text-align:right; white-space:nowrap; }
.cs-pill { display:inline-flex; align-items:center; gap:.3rem; padding:1px 7px; border:1px solid rgba(200,16,46,.3); background:var(--red-faint); font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; color:var(--red); }

/* Branding grid */
.brand-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
@media(max-width:600px){ .brand-grid { grid-template-columns:1fr; } }
.brand-field label { display:block; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); margin-bottom:.25rem; }
.brand-field input { width:100%; border:1px solid var(--grey-mid); padding:.38rem .65rem; font-size:12px; font-family:var(--font); color:var(--text); outline:none; transition:border-color .15s; box-sizing:border-box; }
.brand-field input:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(0,51,102,.07); }
.brand-field .hint { font-size:10px; color:var(--grey-dark); margin-top:.2rem; }

/* Domain */
.domain-acc-body { display:none; border-top:1px solid var(--grey-mid); background:#fff; padding:.85rem .9rem; }
.domain-acc-body.open { display:block; }
.domain-builtin { display:flex; align-items:center; justify-content:space-between; padding:.45rem .7rem; margin-bottom:.55rem; background:var(--green-bg); border:1px solid #b8ddc9; border-left:3px solid var(--green); font-size:12px; }
.domain-tag { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .55rem; background:var(--navy-faint); border:1px solid rgba(0,51,102,.2); color:var(--navy); font-size:12px; font-weight:bold; font-family:monospace; }
.domain-tag-remove { background:none; border:none; color:var(--grey-dark); cursor:pointer; font-size:14px; line-height:1; padding:0; margin-left:2px; transition:color .12s; }
.domain-tag-remove:hover { color:var(--red); }
.domain-add-row { display:flex; gap:.4rem; margin-top:.55rem; }
.domain-add-input { flex:1; padding:.38rem .6rem; border:1px solid var(--grey-mid); font-family:monospace; font-size:12px; outline:none; }
.domain-add-input:focus { border-color:var(--navy); }
.domain-add-btn { padding:.38rem .8rem; background:var(--navy); color:#fff; border:none; font-size:11px; font-weight:bold; cursor:pointer; font-family:var(--font); text-transform:uppercase; }
.domain-save-btn { padding:.35rem .85rem; background:var(--green-bg); color:var(--green); border:1px solid #b8ddc9; font-size:11px; font-weight:bold; cursor:pointer; font-family:var(--font); text-transform:uppercase; }

/* Buttons */
.btn { display:inline-flex; align-items:center; gap:.35rem; padding:.38rem 1rem; border:1px solid; font-family:var(--font); font-size:11px; font-weight:bold; cursor:pointer; transition:all .12s; white-space:nowrap; text-transform:uppercase; letter-spacing:.05em; text-decoration:none; }
.btn-primary { background:var(--navy); border-color:var(--navy); color:#fff; }
.btn-primary:hover { background:var(--navy-mid); }
.btn-danger  { background:transparent; border-color:var(--red); color:var(--red); }
.btn-danger:hover { background:var(--red-faint); }
.btn-sm { padding:.28rem .75rem; font-size:10px; }

@keyframes fadeUp { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }
.fade-in { animation:fadeUp .25s ease both; }
</style>

<div class="dash">

{{-- ══════════════════════════════════
     WELCOME PANEL
══════════════════════════════════ --}}
<div class="welcome-panel fade-in">
    <div class="welcome-panel__head">
        <div class="welcome-panel__title">
            <div class="welcome-panel__logo"><span>RAY<br>NET</span></div>
            Welcome to {{ \App\Helpers\RaynetSetting::groupName() }} Admin
        </div>
        <div class="welcome-panel__date">{{ now()->format('l, d F Y · H:i') }}</div>
    </div>
    <div class="welcome-panel__body">
        <div>
            <div class="welcome-panel__greeting">
                <strong>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ session('admin_name', auth()->user()->name ?? 'Admin') }}</strong>
                Use the panel below to manage your group — or jump straight to a section with the quick actions.
            </div>
        </div>
        <div class="welcome-panel__actions">
            <a href="{{ route('admin.users.index') }}"        class="wp-action wp-action--primary">👥 Members</a>
            <a href="{{ route('admin.events') }}"             class="wp-action wp-action--outline">📅 Events</a>
            <a href="{{ route('admin.modules.index') }}"      class="wp-action wp-action--outline">⊞ Modules</a>
            <a href="{{ route('admin.notifications.index') }}" class="wp-action wp-action--outline">🔔 Notify</a>
            @if($isSuperAdmin)
            <a href="{{ route('admin.super.index') }}"        class="wp-action wp-action--outline" style="border-color:var(--purple);color:var(--purple);">★ Super</a>
            @endif
        </div>
    </div>
</div>

{{-- Pending approvals banner --}}
@if($totalPending > 0)
<div class="pending-banner fade-in">
    <div>
        <div class="pending-banner__text">⏳ {{ $totalPending }} registration {{ Str::plural('approval', $totalPending) }} pending</div>
        <div class="pending-banner__sub">New member {{ Str::plural('account', $totalPending) }} waiting for Group Controller review — scroll down to approve or reject.</div>
    </div>
    <a href="#widget-pending" class="wp-action wp-action--red">View Pending →</a>
</div>
@endif

{{-- ══════════════════════════════════
     MAIN DASHBOARD GRID
══════════════════════════════════ --}}
<div class="dash-grid">

    {{-- ── LEFT COLUMN ── --}}
    <div class="dash-col-left">

        {{-- ALERT STATUS WIDGET --}}
        <div class="widget widget--alert fade-in" id="alert-status">
            <div class="widget__head">
                <span class="widget__title">⚠ Alert Status</span>
                @if(!empty($alertStatus?->message)||!empty($alertStatus?->headline))
                    <span class="sp sp-red">Active message</span>
                @endif
            </div>
            <div class="widget__body">
                {{-- Current level indicator --}}
                <div class="alert-indicator">
                    <div class="alert-swatch" id="alertSwatch"
                         style="background:{{ $currentColour }};color:{{ $textColour }};border-color:{{ $currentColour }};">
                        <span id="swatchNum">{{ $currentLevel }}</span>
                    </div>
                    <div>
                        <div class="alert-info__level">Current Alert Level</div>
                        <div class="alert-info__title" id="previewTitle">{{ $alertMeta['title'] ?? 'Level '.$currentLevel }}</div>
                        <div class="alert-info__desc"  id="previewDesc">{{ $alertMeta['description'] ?? '' }}</div>
                    </div>
                </div>

                {{-- Quick level dots --}}
                <div class="af-dots">
                    @foreach(\App\Models\AlertStatus::config() as $l => $meta)
                    <form method="POST" action="{{ route('admin.alert-status.update') }}" style="display:contents">
                        @csrf
                        <input type="hidden" name="level" value="{{ $l }}">
                        <button type="submit" class="level-dot {{ $currentLevel==$l?'active':'' }}"
                                data-level="{{ $l }}"
                                style="background:{{ $meta['colour'] }};color:{{ $levelConfig[$l]['textColour'] }};"
                                title="{{ $meta['title'] }}"
                                onclick="applyPreview({{ $l }})">{{ $l }}</button>
                    </form>
                    @endforeach
                    <span style="font-size:10px;color:var(--text-muted);align-self:center;margin-left:.25rem;">Click to save immediately</span>
                </div>

                {{-- Edit form --}}
                <form method="POST" action="{{ route('admin.alert-status.update') }}">
                    @csrf
                    <div class="af-row">
                        <div class="af-field">
                            <label class="af-label">Level</label>
                            <select name="level" id="levelSelect" class="af-input" onchange="applyPreview(parseInt(this.value))">
                                @foreach(\App\Models\AlertStatus::config() as $level => $meta)
                                <option value="{{ $level }}" {{ $currentLevel==$level?'selected':'' }}>{{ $meta['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="af-field">
                            <label class="af-label">Headline <span style="font-weight:normal;text-transform:none;letter-spacing:0">(optional)</span></label>
                            <input type="text" name="headline" id="headlineInput" class="af-input"
                                   value="{{ old('headline',$alertStatus?->headline) }}"
                                   placeholder="e.g. Storm standby issued"
                                   oninput="applyPreview(parseInt(document.getElementById('levelSelect').value))">
                        </div>
                    </div>
                    <div style="margin-bottom:.65rem">
                        <label class="af-label">Message <span style="font-weight:normal;text-transform:none;letter-spacing:0">(optional — overrides default description)</span></label>
                        <textarea name="message" id="msgInput" class="af-input" rows="2"
                                  placeholder="Leave blank to use the default description."
                                  oninput="applyPreview(parseInt(document.getElementById('levelSelect').value))">{{ old('message',$alertStatus?->message) }}</textarea>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap">
                        <span style="font-size:11px;color:var(--text-muted);font-weight:bold">
                            Current: <strong style="color:var(--green)">{{ $alertMeta['title'] ?? 'Unknown' }}</strong>
                            @if(!empty($alertStatus?->headline)) — {{ $alertStatus->headline }}@endif
                        </span>
                        <button type="submit" class="btn btn-primary btn-sm">✓ Update Alert</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MEMBERS WIDGET --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">👥 Members &amp; People</span>
                <a href="{{ route('admin.users.index') }}" class="widget__link">View all →</a>
            </div>
            <div class="mod-list">
                <a href="{{ route('admin.users.index') }}" class="mod-item">
                    <div class="mod-icon">👥</div>
                    <div class="mod-text">
                        <div class="mod-title">Manage Members</div>
                        <div class="mod-desc">Accounts, callsigns, roles, admin rights</div>
                    </div>
                    <div class="mod-pills">
                        <span class="sp sp-blue">{{ $totalUsers }} members</span>
                        @if($totalPending > 0)<span class="sp sp-amber">{{ $totalPending }} pending</span>@endif
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.roles') }}" class="mod-item">
                    <div class="mod-icon">🗂️</div>
                    <div class="mod-text">
                        <div class="mod-title">Roles</div>
                        <div class="mod-desc">Define and colour-code member roles</div>
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.availability.index') }}" class="mod-item">
                    <div class="mod-icon">📅</div>
                    <div class="mod-text">
                        <div class="mod-title">Member Availability</div>
                        <div class="mod-desc">Holidays, unavailability &amp; leave periods</div>
                    </div>
                    @php $unavailNow=\App\Models\MemberAvailability::where('from_date','<=',now())->where('to_date','>=',now())->count(); @endphp
                    @if($unavailNow > 0)<div class="mod-pills"><span class="sp sp-red">{{ $unavailNow }} unavailable</span></div>@endif
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.online') }}" class="mod-item">
                    <div class="mod-icon">🟢</div>
                    <div class="mod-text">
                        <div class="mod-title">Who's Online</div>
                        <div class="mod-desc">Currently active sessions</div>
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
            </div>
        </div>

        {{-- EVENTS WIDGET --}}
        <div class="widget widget--teal fade-in">
            <div class="widget__head">
                <span class="widget__title">📅 Events &amp; Calendar</span>
                <a href="{{ route('admin.events') }}" class="widget__link">View all →</a>
            </div>
            <div class="mod-list">
                <a href="{{ route('admin.events') }}" class="mod-item">
                    <div class="mod-icon">📅</div>
                    <div class="mod-text">
                        <div class="mod-title">Manage Events</div>
                        <div class="mod-desc">Create, edit &amp; publish events and .ics feeds</div>
                    </div>
                    <div class="mod-pills"><span class="sp sp-teal">{{ $upcomingEvents }} upcoming</span></div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.event-types') }}" class="mod-item">
                    <div class="mod-icon">🟦</div>
                    <div class="mod-text">
                        <div class="mod-title">Event Types</div>
                        <div class="mod-desc">Labels and colours for event categories</div>
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('calendar') }}" class="mod-item" target="_blank">
                    <div class="mod-icon">📆</div>
                    <div class="mod-text">
                        <div class="mod-title">Public Calendar ↗</div>
                        <div class="mod-desc">View the public-facing calendar page</div>
                    </div>
                    <span class="mod-arrow">↗</span>
                </a>
            </div>
        </div>

        {{-- TRAINING WIDGET --}}
        <div class="widget widget--purple fade-in">
            <div class="widget__head">
                <span class="widget__title">🎓 Training &amp; LMS</span>
                <a href="{{ route('admin.lms.index') }}" class="widget__link">Manage →</a>
            </div>
            <div class="mod-list">
                <a href="{{ route('admin.lms.index') }}" class="mod-item">
                    <div class="mod-icon">🏫</div>
                    <div class="mod-text">
                        <div class="mod-title">Course Builder</div>
                        <div class="mod-desc">Courses, quizzes, drip content &amp; certificates</div>
                    </div>
                    @php $lmsCourseCount=\App\Models\Course::count(); $lmsPublished=\App\Models\Course::where('is_published',true)->count(); $lmsEnrolled=\App\Models\CourseEnrollment::count(); @endphp
                    <div class="mod-pills">
                        <span class="sp sp-purple">{{ $lmsCourseCount }} courses</span>
                        <span class="sp sp-green">{{ $lmsPublished }} live</span>
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.lms.scorm-builder') }}" class="mod-item">
                    <div class="mod-icon">📦</div>
                    <div class="mod-text">
                        <div class="mod-title">SCORM Builder</div>
                        <div class="mod-desc">Create &amp; manage SCORM packages</div>
                    </div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('lms.index') }}" class="mod-item" target="_blank">
                    <div class="mod-icon">🎓</div>
                    <div class="mod-text">
                        <div class="mod-title">Training Portal ↗</div>
                        <div class="mod-desc">View the member-facing portal</div>
                    </div>
                    <div class="mod-pills"><span class="sp sp-blue">{{ $lmsEnrolled }} enrolled</span></div>
                    <span class="mod-arrow">↗</span>
                </a>
            </div>
        </div>

        {{-- SYSTEM WIDGET --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">⚙️ System &amp; Configuration</span>
            </div>
            <div class="mod-list">
                <a href="{{ route('admin.settings') }}" class="mod-item">
                    <div class="mod-icon">⚙️</div>
                    <div class="mod-text"><div class="mod-title">Site Settings</div><div class="mod-desc">Email, broadcasts, header code, global config</div></div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.modules.index') }}" class="mod-item">
                    <div class="mod-icon">⊞</div>
                    <div class="mod-text"><div class="mod-title">Module Manager</div><div class="mod-desc">Install, enable and update feature modules</div></div>
                    <div class="mod-pills"><span class="sp sp-purple">{{ $modCount }} installed</span><span class="sp sp-green">{{ $modActive }} active</span></div>
                    <span class="mod-arrow">→</span>
                </a>
                <a href="{{ route('admin.notifications.index') }}" class="mod-item">
                    <div class="mod-icon">🔔</div>
                    <div class="mod-text"><div class="mod-title">Notifications</div><div class="mod-desc">Send and track member notifications</div></div>
                    @php $unread=\App\Models\AdminNotificationRecipient::whereNull('read_at')->whereNull('removed_at')->count(); @endphp
                    @if($unread > 0)<div class="mod-pills"><span class="sp sp-red">{{ $unread }} unread</span></div>@endif
                    <span class="mod-arrow">→</span>
                </a>
                @if($canAccessLogs)
                <a href="{{ route('admin.activity-logs.index') }}" class="mod-item">
                    <div class="mod-icon">📊</div>
                    <div class="mod-text"><div class="mod-title">Activity Logs</div><div class="mod-desc">Hours, attendance, charts &amp; PDF export</div></div>
                    <div class="mod-pills"><span class="sp sp-blue">{{ number_format($totalLoggedHours,1) }}h</span></div>
                    <span class="mod-arrow">→</span>
                </a>
                @endif
                @if($isSuperAdmin)
                <a href="{{ route('admin.super.permissions.index') }}" class="mod-item">
                    <div class="mod-icon">★</div>
                    <div class="mod-text"><div class="mod-title">Permission Management</div><div class="mod-desc">Roles, permissions &amp; access control</div></div>
                    <div class="mod-pills"><span class="sp sp-purple">{{ \Spatie\Permission\Models\Permission::count() }} permissions</span></div>
                    <span class="mod-arrow">→</span>
                </a>
                @endif
                <a href="{{ route('admin.aprs.index') }}" class="mod-item">
                    <div class="mod-icon">📡</div>
                    <div class="mod-text"><div class="mod-title">APRS Locations</div><div class="mod-desc">Operator location tracking</div></div>
                    <span class="mod-arrow">→</span>
                </a>
            </div>
        </div>

        {{-- SITE BRANDING WIDGET --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">🎨 Site Identity &amp; Branding</span>
                <span style="font-size:10px;color:var(--text-muted)">Used as defaults for other RAYNET groups</span>
            </div>
            <div class="widget__body">
                <form id="brandingForm">
                    @csrf
                    <div class="brand-grid" style="margin-bottom:.85rem">
                        <div class="brand-field">
                            <label>Group Name</label>
                            <input type="text" name="branding[group_name]" value="{{ \App\Models\Setting::get('branding_group_name', \App\Helpers\RaynetSetting::groupName()) }}" placeholder="e.g. {{ \App\Helpers\RaynetSetting::groupName() }}">
                            <div class="hint">Displayed in the site header and emails</div>
                        </div>
                        <div class="brand-field">
                            <label>Group Callsign</label>
                            <input type="text" name="branding[callsign]" value="{{ \App\Models\Setting::get('branding_callsign', '') }}" placeholder="e.g. GB3RL" style="font-family:monospace;text-transform:uppercase">
                            <div class="hint">Net control callsign or group designator</div>
                        </div>
                        <div class="brand-field">
                            <label>Region</label>
                            <input type="text" name="branding[region]" value="{{ \App\Models\Setting::get('branding_region', '') }}" placeholder="e.g. North West">
                            <div class="hint">RAYNET region or county</div>
                        </div>
                        <div class="brand-field">
                            <label>Contact Email</label>
                            <input type="email" name="branding[contact_email]" value="{{ \App\Models\Setting::get('branding_contact_email', '') }}" placeholder="e.g. info@{{ \App\Helpers\RaynetSetting::siteUrl() }}">
                            <div class="hint">Shown on public-facing pages</div>
                        </div>
                        <div class="brand-field">
                            <label>Net Frequency</label>
                            <input type="text" name="branding[frequency]" value="{{ \App\Models\Setting::get('branding_frequency', '') }}" placeholder="e.g. 145.500 MHz" style="font-family:monospace">
                            <div class="hint">Primary calling / net frequency</div>
                        </div>
                        <div class="brand-field">
                            <label>Site URL</label>
                            <input type="url" name="branding[site_url]" value="{{ \App\Models\Setting::get('branding_site_url', config('app.url')) }}" placeholder="{{ \App\Helpers\RaynetSetting::siteUrl() }}">
                            <div class="hint">Canonical URL used in emails and feeds</div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end">
                        <button type="button" class="btn btn-primary btn-sm" onclick="saveBranding()">✓ Save Branding</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- REGISTRATION SETTINGS WIDGET --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">🔐 Registration Settings</span>
                @if(!$settingAvailable)
                    <span style="font-size:10px;color:var(--red);font-weight:bold;">⚠ Run php artisan migrate</span>
                @endif
            </div>
            <div style="border-bottom:1px solid var(--grey-mid)">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.85rem 1rem">
                    <div>
                        <div style="font-size:12px;font-weight:bold;color:var(--navy)">Require approval for new registrations</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:.15rem;max-width:440px">When ON, accounts are held pending and must be approved. When OFF, accounts activate immediately.</div>
                    </div>
                    @if($settingAvailable)
                    <form method="POST" action="{{ route('admin.settings.toggle') }}" style="flex-shrink:0">
                        @csrf
                        <input type="hidden" name="key" value="registration_approval_required">
                        <input type="hidden" name="value" value="{{ $approvalRequired ? '0' : '1' }}">
                        <button type="submit" class="toggle-btn {{ $approvalRequired ? 'is-on' : 'is-off' }}">
                            <span class="toggle-track {{ $approvalRequired ? 'on' : 'off' }}"><span class="toggle-knob {{ $approvalRequired ? 'on' : 'off' }}"></span></span>
                            {{ $approvalRequired ? 'Approval ON' : 'Approval OFF' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div style="border-bottom:1px solid var(--grey-mid)">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.85rem 1rem">
                    <div>
                        <div style="font-size:12px;font-weight:bold;color:var(--navy)">Auto-approve trusted domain registrations</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:.15rem;max-width:440px">Accounts with a trusted email domain skip the manual queue.</div>
                    </div>
                    @if($settingAvailable)
                    <form method="POST" action="{{ route('admin.settings.toggle') }}" style="flex-shrink:0">
                        @csrf
                        <input type="hidden" name="key" value="raynet_email_auto_approval">
                        <input type="hidden" name="value" value="{{ $raynetAutoApproval ? '0' : '1' }}">
                        <button type="submit" class="toggle-btn {{ $raynetAutoApproval ? 'is-on' : 'is-off' }}">
                            <span class="toggle-track {{ $raynetAutoApproval ? 'on' : 'off' }}"><span class="toggle-knob {{ $raynetAutoApproval ? 'on' : 'off' }}"></span></span>
                            {{ $raynetAutoApproval ? 'Auto-approve ON' : 'Auto-approve OFF' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @if($settingAvailable)
            <div style="background:var(--grey);border-bottom:1px solid var(--grey-mid)">
                <button type="button" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;background:none;border:none;font-family:var(--font);font-size:11px;font-weight:bold;color:var(--navy);cursor:pointer;text-transform:uppercase;letter-spacing:.05em"
                        onclick="document.getElementById('domainAccBody').classList.toggle('open');this.querySelector('.dchev').style.transform=document.getElementById('domainAccBody').classList.contains('open')?'rotate(180deg)':''">
                    <span>🌐 Manage trusted domains <span id="domainCountBadge" style="background:var(--navy);color:#fff;font-size:9px;padding:1px 6px;border-radius:8px;margin-left:.35rem">{{ count($extraDomains)+1 }}</span></span>
                    <span class="dchev" style="font-size:9px;transition:transform .2s;color:var(--grey-dark)">▼</span>
                </button>
                <div class="domain-acc-body" id="domainAccBody">
                    <div class="domain-builtin">
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <span style="font-size:9px;font-weight:bold;background:var(--green);color:#fff;padding:1px 5px;text-transform:uppercase;letter-spacing:.07em">Built-in</span>
                            <span style="font-size:12px;font-weight:bold;color:var(--green);font-family:monospace">@raynet-uk.net</span>
                        </div>
                        <span style="font-size:10px;color:var(--green);opacity:.7">Cannot be removed</span>
                    </div>
                    <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);margin-bottom:.4rem">Additional trusted domains</div>
                    <div id="domainList" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.6rem;min-height:24px"></div>
                    <div class="domain-add-row">
                        <input type="text" class="domain-add-input" id="domainInput" placeholder="e.g. example.org (without the @)" onkeydown="if(event.key==='Enter'){event.preventDefault();addDomain();}">
                        <button type="button" class="domain-add-btn" onclick="addDomain()">+ Add</button>
                    </div>
                    <div id="domainErr" style="font-size:10px;color:var(--red);font-weight:bold;margin-top:.3rem;min-height:.9rem"></div>
                    <form method="POST" action="{{ route('admin.settings.toggle') }}" id="domainSaveForm">
                        @csrf
                        <input type="hidden" name="key" value="auto_approve_domains">
                        <input type="hidden" name="value" id="domainSaveValue" value="{{ json_encode($extraDomains) }}">
                        <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
                            <span style="font-size:10px;color:var(--text-muted);font-weight:bold">Changes are not saved until you click Save.</span>
                            <button type="submit" class="domain-save-btn">✓ Save domains</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            <div style="padding:.55rem 1rem;background:var(--grey);font-size:10px;font-weight:bold;color:var(--text-muted);display:flex;gap:1.5rem;flex-wrap:wrap">
                <span>Approval: @if($approvalRequired)<span style="color:var(--green)">● Required</span>@else<span style="color:var(--amber)">● Disabled</span>@endif</span>
                <span>Auto-approve: @if($raynetAutoApproval&&$approvalRequired)<span style="color:var(--green)">● Active</span>@else<span style="color:var(--text-muted)">● Inactive</span>@endif</span>
            </div>
        </div>

        {{-- PENDING APPROVALS WIDGET --}}
        @if($pendingUsers->isNotEmpty())
        <div class="widget widget--pending fade-in" id="widget-pending">
            <div class="widget__head" style="background:#fef2f2;border-bottom-color:rgba(200,16,46,.2)">
                <span class="widget__title" style="color:var(--red)">⏳ Pending Account Approvals</span>
                <span class="sp sp-red">{{ $pendingUsers->count() }} awaiting review</span>
            </div>
            <div style="padding:.5rem 1rem;background:var(--red-faint);border-bottom:1px solid rgba(200,16,46,.15);font-size:11px;color:var(--red);font-weight:bold">
                New registrations waiting for a Group Controller to approve or reject.
            </div>
            <table class="pending-tbl">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Callsign</th><th>Registered</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($pendingUsers as $pending)
                @php
                    $allTrusted = array_merge(['raynet-uk.net'],$extraDomains);
                    $emailDomain = strtolower(substr(strrchr($pending->email,'@'),1));
                    $isTrusted = in_array($emailDomain,$allTrusted);
                @endphp
                <tr>
                    <td style="font-weight:bold">{{ $_isTempAdmin && isset($pending) && method_exists($pending, 'piiVisible') && !$pending->piiVisible() ? '●●●●●●●●●' : $pending->name }}</td>
                    <td style="color:var(--text-muted)">
                        {{ $_isTempAdmin && isset($pending) && method_exists($pending, 'piiVisible') && !$pending->piiVisible() ? '●●●●●●●' : $pending->email }}
                        @if($isTrusted)<span style="display:inline-block;margin-left:.3rem;padding:1px 5px;font-size:9px;font-weight:bold;background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);text-transform:uppercase;letter-spacing:.05em">Trusted</span>@endif
                    </td>
                    <td><span class="cs-pill">📡 {{ $pending->callsign ?? '—' }}</span></td>
                    <td style="color:var(--text-muted);font-size:11px">{{ $pending->created_at->diffForHumans() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.registration.approve',$pending->id) }}" style="display:inline">
                            @csrf<button type="submit" class="btn btn-primary btn-sm">✓ Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.registration.reject',$pending->id) }}" style="display:inline;margin-left:.4rem" onsubmit="return confirm('Delete registration for {{ addslashes($pending->name) }}?')">
                            @csrf<button type="submit" class="btn btn-danger btn-sm">✗ Reject</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>{{-- end left col --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="dash-col-right">

        {{-- AT A GLANCE --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">📊 At a Glance</span>
            </div>
            <div class="widget__body" style="padding:.75rem">
                <div class="glance-grid">
                    <div class="glance-item">
                        <div class="glance-num">{{ $totalUsers }}</div>
                        <div class="glance-label">Members</div>
                    </div>
                    <div class="glance-item">
                        <div class="glance-num green">{{ $totalCallsign }}</div>
                        <div class="glance-label">Callsigns</div>
                    </div>
                    <div class="glance-item">
                        <div class="glance-num amber">{{ $upcomingEvents }}</div>
                        <div class="glance-label">Upcoming Events</div>
                    </div>
                    <div class="glance-item">
                        <div class="glance-num purple">{{ number_format($totalLoggedHours,1) }}h</div>
                        <div class="glance-label">Hours Logged</div>
                    </div>
                    <div class="glance-item">
                        <div class="glance-num teal">{{ $modActive }}</div>
                        <div class="glance-label">Active Modules</div>
                    </div>
                    <div class="glance-item">
                        @if($totalPending > 0)
                        <div class="glance-num red">{{ $totalPending }}</div>
                        <div class="glance-label">Pending Approval</div>
                        @else
                        <div class="glance-num green">0</div>
                        <div class="glance-label">Pending Approval</div>
                        @endif
                    </div>
                </div>
                <div style="font-size:10px;color:var(--text-muted);text-align:right;margin-top:.35rem">
                    Core v{{ config('app.raynet.core_version', '1.0.0') }}
                </div>
            </div>
        </div>

        {{-- QUICK LINKS --}}
        <div class="widget widget--navy fade-in">
            <div class="widget__head">
                <span class="widget__title">⚡ Quick Links</span>
            </div>
            <div class="quick-links">
                <a href="{{ route('admin.dashboard') }}"            class="quick-link-item">⊞ Dashboard</a>
                <a href="{{ route('admin.users.index') }}"          class="quick-link-item">👥 All Members</a>
                <a href="{{ route('admin.events') }}"               class="quick-link-item">📅 Events</a>
                <a href="{{ route('admin.notifications.index') }}"  class="quick-link-item">🔔 Notifications</a>
                <a href="{{ route('admin.lms.index') }}"            class="quick-link-item">🎓 Course Builder</a>
                <a href="{{ route('admin.activity-logs.index') }}"  class="quick-link-item">📊 Activity Logs</a>
                <a href="{{ route('admin.modules.index') }}"        class="quick-link-item ql-purple">⊞ Module Manager</a>
                <a href="{{ route('admin.settings') }}"             class="quick-link-item">⚙️ Site Settings</a>
                @if(\Illuminate\Support\Facades\Route::has('admin.netlog.index'))
                <a href="{{ route('admin.netlog.index') }}"         class="quick-link-item">📻 Net Log</a>
                @endif
                @if(\Illuminate\Support\Facades\Route::has('admin.announcements.index'))
                <a href="{{ route('admin.announcements.index') }}"  class="quick-link-item">📢 Announcements</a>
                @endif
                @if($isSuperAdmin)
                <a href="{{ route('admin.super.index') }}"          class="quick-link-item ql-purple">★ Super Admin</a>
                @endif
                @if($totalPending > 0)
                <a href="#widget-pending"                            class="quick-link-item ql-red">⏳ {{ $totalPending }} Pending</a>
                @endif
                <a href="{{ route('home') }}" target="_blank"        class="quick-link-item">🌐 View Public Site ↗</a>
            </div>
        </div>

        {{-- ANALYTICS WIDGET --}}
        <div class="widget widget--amber fade-in">
            <div class="widget__head">
                <span class="widget__title">📈 Analytics</span>
            </div>
            <div class="mod-list">
                @if($canAccessLogs)
                <a href="{{ route('admin.activity-logs.index') }}" class="mod-item">
                    <div class="mod-icon">📋</div>
                    <div class="mod-text"><div class="mod-title">Activity &amp; Hours Log</div><div class="mod-desc">{{ number_format($totalLogEntries) }} entries · {{ number_format($totalLoggedHours,1) }}h logged</div></div>
                    <span class="mod-arrow">→</span>
                </a>
                @else
                <div class="mod-item" style="opacity:.6;cursor:not-allowed">
                    <div class="mod-icon">📋</div>
                    <div class="mod-text"><div class="mod-title">Activity Log</div><div class="mod-desc">Super admin access required</div></div>
                    <span class="sp sp-grey">🔒</span>
                </div>
                @endif
                <a href="{{ route('data-dashboard') }}" class="mod-item" target="_blank">
                    <div class="mod-icon">📡</div>
                    <div class="mod-text"><div class="mod-title">Data Dashboard ↗</div><div class="mod-desc">Live propagation &amp; HF conditions</div></div>
                    <span class="mod-arrow">↗</span>
                </a>
                <a href="{{ route('admin.aprs.index') }}" class="mod-item">
                    <div class="mod-icon">🗺️</div>
                    <div class="mod-text"><div class="mod-title">APRS Locations</div><div class="mod-desc">Operator location tracking</div></div>
                    <span class="mod-arrow">→</span>
                </a>
            </div>
        </div>

        {{-- ACTIVE ALERT SUMMARY --}}
        <div class="widget fade-in" style="border-top:3px solid {{ $currentColour }}">
            <div class="widget__head">
                <span class="widget__title">⚠ Current Alert</span>
                <a href="#alert-status" class="widget__link">Edit →</a>
            </div>
            <div class="widget__body">
                <div style="display:flex;align-items:center;gap:.75rem">
                    <div style="width:48px;height:48px;border-radius:50%;background:{{ $currentColour }};color:{{ $textColour }};display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:bold;border:3px solid {{ $currentColour }};flex-shrink:0">
                        {{ $currentLevel }}
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:bold;color:var(--navy)">{{ $alertMeta['title'] ?? 'Level '.$currentLevel }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:.15rem">{{ $alertMeta['description'] ?? '' }}</div>
                        @if(!empty($alertStatus?->headline))
                        <div style="font-size:11px;font-weight:bold;color:var(--navy);margin-top:.25rem;padding:.25rem .5rem;background:var(--navy-faint);border-left:2px solid var(--navy)">{{ $alertStatus->headline }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end right col --}}
</div>{{-- end dash grid --}}

</div>{{-- end .dash --}}

@push('scripts')
<script>
const LEVEL_CONFIG = @json($levelConfig);
function applyPreview(level){
    const cfg=LEVEL_CONFIG[level]; if(!cfg)return;
    const msg=document.getElementById('msgInput')?.value.trim()||'';
    const hl=document.getElementById('headlineInput')?.value.trim()||'';
    const sw=document.getElementById('alertSwatch');
    if(sw){sw.style.background=cfg.colour;sw.style.color=cfg.textColour;sw.style.borderColor=cfg.colour;}
    const sn=document.getElementById('swatchNum'); if(sn)sn.textContent=level;
    const pt=document.getElementById('previewTitle'); if(pt)pt.textContent=hl||cfg.title;
    const pd=document.getElementById('previewDesc'); if(pd)pd.textContent=msg||cfg.description;
    const mb=document.getElementById('previewMsg'); if(mb){if(msg){mb.style.display='block';mb.textContent=msg;}else{mb.style.display='none';}}
    const ls=document.getElementById('levelSelect'); if(ls)ls.value=level;
    document.querySelectorAll('.level-dot').forEach(d=>d.classList.toggle('active',parseInt(d.dataset.level)===level));
}
applyPreview({{ $currentLevel }});

// Domain management
let domains=@json($extraDomains);
function renderDomains(){
    const list=document.getElementById('domainList');
    if(!list)return;
    list.innerHTML='';
    if(domains.length===0){list.innerHTML='<span style="font-size:11px;color:var(--text-muted);font-style:italic">No additional domains yet.</span>';}
    else{domains.forEach((d,i)=>{const t=document.createElement('div');t.className='domain-tag';t.innerHTML='<span>@'+d+'</span><button type="button" class="domain-tag-remove" onclick="removeDomain('+i+')">×</button>';list.appendChild(t);});}
    const cb=document.getElementById('domainCountBadge'); if(cb)cb.textContent=domains.length+1;
    const sv=document.getElementById('domainSaveValue'); if(sv)sv.value=JSON.stringify(domains);
}
function addDomain(){
    const input=document.getElementById('domainInput'),err=document.getElementById('domainErr');
    let val=input.value.trim().toLowerCase().replace(/^@/,'').replace(/^https?:\/\//,'').split('/')[0];
    err.textContent='';
    if(!val){err.textContent='Please enter a domain name.';return;}
    if(!/^[a-z0-9][a-z0-9\-\.]+\.[a-z]{2,}$/.test(val)){err.textContent='Invalid format — e.g. example.org';return;}
    if(val==='raynet-uk.net'){err.textContent='raynet-uk.net is already built-in.';return;}
    if(domains.includes(val)){err.textContent=val+' is already listed.';return;}
    domains.push(val);renderDomains();input.value='';input.focus();
}
function removeDomain(i){domains.splice(i,1);renderDomains();}
renderDomains();

// Branding save
function saveBranding(){
    const fields=document.querySelectorAll('#brandingForm input[name^="branding["]');
    const saves=[];
    fields.forEach(input=>{
        const key='branding_'+input.name.match(/\[(.+)\]/)[1];
        saves.push(fetch('{{ route("admin.settings.toggle") }}',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:'key='+encodeURIComponent(key)+'&value='+encodeURIComponent(input.value)
        }));
    });
    Promise.all(saves).then(()=>{
        const btn=document.querySelector('#brandingForm button[type="button"]');
        if(btn){btn.textContent='✓ Saved!';btn.style.background='var(--green)';btn.style.borderColor='var(--green)';setTimeout(()=>{btn.textContent='✓ Save Branding';btn.style.background='';btn.style.borderColor='';},2000);}
    });
}
</script>
@endpush

@endsection