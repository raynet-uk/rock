@extends('layouts.app')

@section('title', $event->title)

@section('content')


<style>
:root {
    --navy:   #003366;
    --navy2:  #00234a;
    --red:    #C8102E;
    --white:  #ffffff;
    --grey:   #f4f5f7;
    --border: #e1e5ec;
    --text:   #1a2332;
    --muted:  #6b7a90;
    --green:  #1a6b3c;
    --blue:   #0284c7;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: var(--text); background: var(--grey); -webkit-font-smoothing: antialiased; }
a { color: var(--navy); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ── MASTHEAD ── */
.rn-masthead {
    background: var(--navy); border-bottom: 3px solid var(--red);
    padding: 0 clamp(16px,4vw,48px); height: 56px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
}
.rn-masthead-brand { display: flex; align-items: center; gap: 10px; }
.rn-logo-mark {
    width: 34px; height: 34px; background: var(--red);
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: bold; color: #fff;
    letter-spacing: .05em; text-align: center; line-height: 1.2; text-transform: uppercase; flex-shrink: 0;
}
.rn-masthead-title { font-size: 14px; font-weight: bold; color: #fff; letter-spacing: .01em; }
.rn-masthead-sub   { font-size: 10px; color: rgba(255,255,255,.45); letter-spacing: .07em; text-transform: uppercase; }
.rn-back-link { font-size: 11px; color: rgba(255,255,255,.65); border: 1px solid rgba(255,255,255,.2); padding: 4px 11px; transition: all .15s; }
.rn-back-link:hover { background: rgba(255,255,255,.1); color: #fff; text-decoration: none; }

/* ── LAYOUT ── */
.rn-page { max-width: 1100px; margin: 0 auto; padding: 28px clamp(16px,3vw,32px) 64px; }
.rn-layout { display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
@media (max-width: 820px) { .rn-layout { grid-template-columns: 1fr; } .rn-sidebar { order: -1; } }

/* ── HERO ── */
.rn-hero { background: var(--navy2); padding: 28px clamp(16px,3vw,32px) 24px; border-bottom: 3px solid var(--red); }
.rn-type-chip { display: inline-block; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; padding: 3px 10px; border: 1px solid; margin-bottom: 12px; }
.rn-event-title { font-size: clamp(22px,3.5vw,34px); font-weight: bold; color: #fff; line-height: 1.15; }
.rn-private-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: bold; color: var(--red); border: 1px solid rgba(200,16,46,.4); background: rgba(200,16,46,.12); padding: 2px 8px; vertical-align: middle; text-transform: uppercase; letter-spacing: .06em; margin-left: 10px; }

/* ── STATUS BAR ── */
.rn-status { display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-size: 12px; font-weight: bold; border-bottom: 1px solid var(--border); }
.rn-status.live     { background: #eef7f2; border-left: 4px solid var(--green); }
.rn-status.ended    { background: #f7f8fa; border-left: 4px solid #c0c8d4; color: var(--muted); }
.rn-status.upcoming { background: #e8eef5; border-left: 4px solid var(--navy); }
.rn-pulse { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.rn-pulse.green { background: var(--green); animation: pg 1.8s ease-in-out infinite; }
.rn-pulse.red   { background: var(--red);   animation: pr 1.8s ease-in-out infinite; }
@keyframes pg { 0%,100%{box-shadow:0 0 0 0 rgba(26,107,60,.5)} 50%{box-shadow:0 0 0 6px rgba(26,107,60,0)} }
@keyframes pr { 0%,100%{box-shadow:0 0 0 0 rgba(200,16,46,.5)} 50%{box-shadow:0 0 0 6px rgba(200,16,46,0)} }

/* ── MAIN ── */
.rn-main { background: var(--white); border: 1px solid var(--border); overflow: hidden; }

/* ── COUNTDOWN ── */
.rn-countdown {
    display: grid; grid-template-columns: repeat(4,1fr);
    border-bottom: 1px solid var(--border); background: var(--navy);
}
.rn-cd-unit {
    padding: 18px 8px; text-align: center;
    border-right: 1px solid rgba(255,255,255,.08);
}
.rn-cd-unit:last-child { border-right: none; }
.rn-cd-num {
    font-size: 30px; font-weight: bold; color: #fff;
    line-height: 1; display: block;
}
.rn-cd-lbl {
    font-size: 9px; text-transform: uppercase; letter-spacing: .15em;
    color: rgba(255,255,255,.35); margin-top: 5px; display: block;
}
/* ── CONTENT ── */
.rn-content { padding: 24px; }
.rn-body { font-size: 15px; line-height: 1.75; color: var(--text); }
.rn-body p { margin-bottom: 14px; }
.rn-body p:last-child { margin-bottom: 0; }
.rn-body-empty { color: var(--muted); font-style: italic; font-size: 14px; }

/* ── MAP ── */
.rn-map-wrap { border-top: 1px solid var(--border); }
.rn-map-toolbar { background: var(--navy2); padding: 6px 12px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.rn-map-tool-btn { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; padding: 3px 9px; border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.7); background: none; cursor: pointer; font-family: Arial, sans-serif; transition: all .12s; }
.rn-map-tool-btn:hover { background: rgba(255,255,255,.12); color: #fff; border-color: rgba(255,255,255,.4); }
.rn-map-badges { display: flex; gap: 5px; margin-left: auto; }
.rn-map-badge { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; padding: 2px 7px; border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.6); }
#rn-event-map { width: 100%; height: 340px; }
.rn-map-foot { background: var(--grey); border-top: 1px solid var(--border); padding: 8px 14px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.rn-legend-item { display: flex; align-items: center; gap: 5px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
.rn-legend-pin { width: 10px; height: 10px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); flex-shrink: 0; }
.rn-legend-swatch { width: 10px; height: 10px; flex-shrink: 0; border: 1px solid rgba(0,0,0,.15); }

/* Grid strip */
.rn-grid-strip { border-top: 1px solid var(--border); background: var(--navy2); padding: 10px 14px; display: flex; align-items: center; gap: 14px; }
.rn-grid-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.35); flex-shrink: 0; line-height: 1.5; }
.rn-grid-val { font-family: Arial, Helvetica, sans-serif; font-size: 20px; font-weight: bold; color: #fff; letter-spacing: .1em; }
.rn-grid-sub { font-size: 10px; color: rgba(255,255,255,.35); font-family: Arial, Helvetica, sans-serif; margin-top: 1px; }
.rn-grid-copy { margin-left: auto; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em; padding: 4px 10px; border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.6); background: none; cursor: pointer; font-family: Arial, sans-serif; transition: all .12s; flex-shrink: 0; }
.rn-grid-copy:hover { background: rgba(255,255,255,.1); color: #fff; }
.rn-grid-mode-hint { font-size: 9px; color: rgba(125,211,252,.6); font-style: italic; }

/* POI strip */
.rn-poi-strip { border-top: 1px solid var(--border); padding: 10px 14px; background: var(--grey); }
.rn-poi-strip-hd { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; color: var(--muted); margin-bottom: 7px; }
.rn-poi-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.rn-poi-chip { display: inline-flex; align-items: center; gap: 4px; background: var(--white); border: 1px solid var(--border); padding: 3px 9px; font-size: 11px; color: var(--text); cursor: pointer; transition: border-color .12s, background .12s; }
.rn-poi-chip:hover { border-color: var(--navy); background: #e8eef5; }
.rn-poi-chip .gridref { font-size: 9px; color: var(--muted); font-family: Arial, Helvetica, sans-serif; }

/* Private wall */
.rn-private-wall { margin: 24px; padding: 20px 24px; background: #fdf0f2; border: 1px solid rgba(200,16,46,.2); border-left: 3px solid var(--red); display: flex; align-items: flex-start; gap: 14px; }
.rn-private-wall-icon  { font-size: 28px; line-height: 1; flex-shrink: 0; }
.rn-private-wall-title { font-size: 14px; font-weight: bold; color: var(--red); margin-bottom: 5px; }
.rn-private-wall-text  { font-size: 13px; color: #555; line-height: 1.65; }
.rn-private-wall-text a { color: var(--navy); font-weight: bold; }

/* ── SIDEBAR ── */
.rn-sidebar { display: flex; flex-direction: column; gap: 12px; }
.rn-card { background: var(--white); border: 1px solid var(--border); overflow: hidden; }
.rn-card-head { padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); display: flex; align-items: center; gap: 6px; }
.rn-card-head-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--red); flex-shrink: 0; }
.rn-card-body { padding: 14px; }
.rn-meta-row { display: flex; flex-direction: column; gap: 2px; margin-bottom: 12px; }
.rn-meta-row:last-child { margin-bottom: 0; }
.rn-meta-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
.rn-meta-val { font-size: 13px; font-weight: bold; color: var(--text); }

/* Buttons */
.rn-btn { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 10px 16px; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; transition: all .15s; text-decoration: none; border: none; margin-bottom: 6px; }
.rn-btn:last-child { margin-bottom: 0; }
.rn-btn-primary   { background: var(--red); color: #fff; }
.rn-btn-primary:hover { background: #a50f26; color: #fff; text-decoration: none; }
.rn-btn-ghost { background: var(--grey); color: var(--text); border: 1px solid var(--border); }
.rn-btn-ghost:hover { border-color: var(--navy); color: var(--navy); text-decoration: none; }

/* Share panel */
.rn-share-panel { display: none; border-top: 1px solid var(--border); padding: 12px 14px; background: var(--grey); }
.rn-share-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: 7px; }
.rn-cal-btns { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
.rn-cal-btn { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border: 1px solid var(--border); background: var(--white); font-size: 12px; font-weight: bold; color: var(--text); text-decoration: none; transition: border-color .12s; }
.rn-cal-btn:hover { border-color: var(--navy); text-decoration: none; }
.rn-share-url-row { display: flex; gap: 5px; }
.rn-share-url-row input { flex: 1; border: 1px solid var(--border); padding: 6px 8px; font-size: 11px; color: var(--text); background: var(--white); font-family: Arial, sans-serif; min-width: 0; }
.rn-copy-btn { padding: 6px 10px; background: var(--navy); border: none; color: #fff; font-size: 11px; font-weight: bold; cursor: pointer; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; transition: background .15s; }
.rn-copy-btn:hover { background: var(--navy2); }

/* Weather */
.rn-weather-loading { padding: 14px; font-size: 12px; color: var(--muted); font-style: italic; }
.rn-weather-body { padding: 14px; }
.rn-weather-top { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.rn-weather-icon { font-size: 36px; line-height: 1; }
.rn-weather-temp { font-size: 26px; font-weight: bold; color: var(--navy); line-height: 1; }
.rn-weather-feels { font-size: 11px; color: var(--muted); margin-top: 2px; }
.rn-weather-desc  { font-size: 12px; color: var(--muted); margin-top: 1px; }
.rn-weather-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.rn-weather-stat-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
.rn-weather-stat-val { font-size: 13px; font-weight: bold; color: var(--text); }
.rn-weather-compass  { display: flex; align-items: center; gap: 6px; grid-column: 1/-1; margin-top: 2px; }
.rn-weather-not-yet  { padding: 12px 14px; font-size: 12px; color: var(--muted); }

/* Docs */
.rn-doc-list { display: flex; flex-direction: column; }
.rn-doc-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid var(--border); transition: background .12s; }
.rn-doc-item:last-child { border-bottom: none; }
.rn-doc-item:hover { background: var(--grey); }
.rn-doc-icon { font-size: 18px; flex-shrink: 0; width: 22px; text-align: center; }
.rn-doc-info { flex: 1; min-width: 0; }
.rn-doc-name { font-size: 13px; font-weight: bold; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
.rn-doc-name:hover { text-decoration: underline; }
.rn-doc-meta { font-size: 10px; color: var(--muted); margin-top: 1px; }
.rn-doc-dl { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; color: var(--navy); text-decoration: none; border: 1px solid var(--border); padding: 3px 8px; flex-shrink: 0; transition: all .12s; }
.rn-doc-dl:hover { border-color: var(--navy); background: var(--grey); text-decoration: none; }
.rn-doc-empty { padding: 14px; font-size: 12px; color: var(--muted); font-style: italic; }
.rn-doc-gate  { padding: 14px; font-size: 12px; color: var(--muted); line-height: 1.6; }
.rn-doc-gate a { color: var(--navy); font-weight: bold; }

/* Footer */
.rn-footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 7px; }
.rn-footer-dot { width: 8px; height: 2px; background: var(--red); flex-shrink: 0; }
.rn-admin-hint { padding: 8px 14px; font-size: 11px; color: var(--muted); border-top: 1px solid var(--border); background: var(--grey); }
.rn-admin-hint a { color: var(--navy); font-weight: bold; }

@keyframes dashMove { to { stroke-dashoffset: 0; } }
.leaflet-popup-content-wrapper { border-radius: 2px !important; font-family: Arial, sans-serif !important; box-shadow: 0 4px 16px rgba(0,51,102,.2) !important; }
.leaflet-popup-content { margin: 10px 14px !important; font-size: 13px !important; }
.leaflet-control-attribution { font-size: 10px !important; }

/* ── RSVP ── */
.rn-rsvp-counts { display: grid; grid-template-columns: repeat(3,1fr); gap: 1px; background: var(--border); border-bottom: 1px solid var(--border); }
.rn-rsvp-count { background: var(--white); padding: 10px 8px; text-align: center; }
.rn-rsvp-count-num { font-size: 20px; font-weight: bold; line-height: 1; }
.rn-rsvp-count-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-top: 3px; }
.rn-rsvp-btn-group { display: grid; grid-template-columns: repeat(3,1fr); gap: 6px; margin-bottom: 10px; }
.rn-rsvp-btn { padding: 8px 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; border: 2px solid; cursor: pointer; transition: all .15s; font-family: Arial, sans-serif; background: var(--white); text-align: center; }
.rn-rsvp-btn.attending        { color: var(--green); border-color: var(--green); }
.rn-rsvp-btn.attending:hover,
.rn-rsvp-btn.attending.active  { background: var(--green); color: #fff; }
.rn-rsvp-btn.maybe             { color: #8a5c00; border-color: #8a5c00; }
.rn-rsvp-btn.maybe:hover,
.rn-rsvp-btn.maybe.active      { background: #8a5c00; color: #fff; }
.rn-rsvp-btn.declined          { color: var(--red); border-color: var(--red); }
.rn-rsvp-btn.declined:hover,
.rn-rsvp-btn.declined.active   { background: var(--red); color: #fff; }
.rn-rsvp-note { width: 100%; border: 1px solid var(--border); padding: 7px 10px; font-size: 12px; font-family: Arial, sans-serif; color: var(--text); resize: none; min-height: 60px; outline: none; transition: border-color .15s; }
.rn-rsvp-note:focus { border-color: var(--navy); }
.rn-rsvp-save { width: 100%; padding: 9px; background: var(--navy); border: none; color: #fff; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; font-family: Arial, sans-serif; transition: background .15s; margin-top: 6px; }
.rn-rsvp-save:hover { background: var(--navy2); }
.rn-rsvp-remove { width: 100%; padding: 6px; background: none; border: 1px solid var(--border); color: var(--muted); font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; font-family: Arial, sans-serif; transition: all .15s; margin-top: 4px; }
.rn-rsvp-remove:hover { border-color: var(--red); color: var(--red); }

</style>

{{-- MASTHEAD --}}
<header class="rn-masthead">
    <div class="rn-masthead-brand">
        <div class="rn-logo-mark">RAY<br>NET</div>
        <div>
            <div class="rn-masthead-title">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
            <div class="rn-masthead-sub">Events Calendar</div>
        </div>
    </div>
    <a href="{{ route('calendar') }}" class="rn-back-link">← Calendar</a>
</header>

{{-- HERO --}}
@php $chipColour = $event->type?->colour ?: '#003366'; @endphp
<div class="rn-hero">
    <div style="max-width:1100px;margin:0 auto;padding:0 clamp(16px,3vw,32px);">
        @if($event->type)
            <div class="rn-type-chip" style="color:{{ $chipColour }};border-color:{{ $chipColour }}40;background:{{ $chipColour }}18;">
                {{ $event->type->name }}
            </div>
        @endif
        <h1 class="rn-event-title">
            {{ $event->title }}
            @if($event->is_private)<span class="rn-private-badge">🔒 Members Only</span>@endif
        </h1>
        @if($event->supporting_group === '__OWN__')
            <div style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:4px 12px;background:rgba(0,51,102,.15);border:1px solid rgba(0,51,102,.4);border-radius:999px;font-size:12px;font-weight:700;color:#fff;">
                📡 {{ \App\Helpers\RaynetSetting::groupName() }}
            </div>
        @elseif($event->supporting_group)
            <div style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:4px 12px;background:rgba(200,16,46,.12);border:1px solid rgba(200,16,46,.4);border-radius:999px;font-size:12px;font-weight:700;color:#fff;">
                🤝 Supporting: {{ $event->supporting_group }}
            </div>
        @endif
    </div>
</div>

{{-- STATE --}}
@php
    $now        = now();
    $isLive     = $event->starts_at->lte($now) && ($event->ends_at ? $event->ends_at->gte($now) : $event->starts_at->addHours(4)->gte($now));
    $isEnded    = $event->ends_at ? $event->ends_at->lt($now) : $event->starts_at->addHours(4)->lt($now);
    $isUpcoming = $event->starts_at->gt($now);
    $daysAway   = (int) $event->starts_at->diffInDays($now, false);
    $showWeather= $event->hasLocation() && !$isEnded && abs($daysAway) <= 14;
@endphp

{{-- STATUS --}}
@if($isLive)
<div class="rn-status live">
    <div class="rn-pulse green"></div>
    <span style="color:var(--green);text-transform:uppercase;letter-spacing:.06em;">Exercise Active</span>
    <span style="font-weight:normal;color:var(--muted);margin-left:auto;font-size:11px;">
        Started {{ $event->starts_at->format('H:i') }}@if($event->ends_at) · Ends {{ $event->ends_at->format('H:i') }}@endif
    </span>
</div>
@elseif($isEnded)
<div class="rn-status ended">
    <span style="text-transform:uppercase;letter-spacing:.06em;">✓ Event Completed</span>
    <span style="margin-left:auto;font-size:11px;">{{ $event->starts_at->format('D j M Y') }}</span>
</div>
@else
<div class="rn-status upcoming">
    <div class="rn-pulse red"></div>
    <span style="color:var(--navy);text-transform:uppercase;letter-spacing:.06em;">Upcoming</span>
    <span style="font-weight:normal;color:var(--muted);margin-left:auto;font-size:11px;">{{ $event->starts_at->diffForHumans() }}</span>
</div>
@endif

<div class="rn-page">
<div class="rn-layout">

{{-- MAIN --}}
<div class="rn-main">

    {{-- Countdown --}}
@if($isUpcoming)
<div class="rn-countdown" id="rn-countdown">
    <div class="rn-cd-unit"><span class="rn-cd-num" id="cd-d">00</span><span class="rn-cd-lbl">Days</span></div>
    <div class="rn-cd-unit"><span class="rn-cd-num" id="cd-h">00</span><span class="rn-cd-lbl">Hours</span></div>
    <div class="rn-cd-unit"><span class="rn-cd-num" id="cd-m">00</span><span class="rn-cd-lbl">Mins</span></div>
    <div class="rn-cd-unit"><span class="rn-cd-num" id="cd-s">00</span><span class="rn-cd-lbl">Secs</span></div>
</div>
@endif

    {{-- Private wall --}}
    @if($event->is_private && !session('is_admin') && !auth()->check())
        <div class="rn-private-wall">
            <div class="rn-private-wall-icon">🔒</div>
            <div>
                <div class="rn-private-wall-title">Members Only Event</div>
                <div class="rn-private-wall-text">
                    Full details are only available to logged-in members.<br>
                    Please <a href="{{ route('login') }}">sign in</a> to view the description, map and documents.
                </div>
            </div>
        </div>
    @else

        {{-- Description --}}
        <div class="rn-content">
            @auth
            @if($event->members_description)
                <div class="rn-body">{!! nl2br(e($event->members_description)) !!}</div>
            @elseif($event->description)
                <div class="rn-body">{!! nl2br(e($event->description)) !!}</div>
            @else
                <p class="rn-body-empty">No additional details have been added for this event.</p>
            @endif
            @else
            @if($event->description)
                <div class="rn-body">{!! nl2br(e($event->description)) !!}</div>
            @else
                <p class="rn-body-empty">No additional details have been added for this event.</p>
            @endif
            @endauth
        </div>

        {{-- Map --}}
        @php
            $hasPin     = $event->event_lat && $event->event_lng;
            $hasPolygon = !empty($event->event_polygon);
            $hasRoute   = !empty($event->event_route);
            $hasPois    = !empty($event->event_pois);
            $showMap    = $hasPin || $hasPolygon || $hasRoute || $hasPois;
            $jsLat      = $hasPin ? (float)$event->event_lat : 53.4084;
            $jsLng      = $hasPin ? (float)$event->event_lng : -2.9916;
            $jsPolygon  = $hasPolygon ? json_encode(is_array($event->event_polygon) ? $event->event_polygon : json_decode($event->event_polygon,true)) : 'null';
            $jsPolyName = e($event->event_polygon_name ?? 'Site Boundary');
            $jsRoute    = $hasRoute ? json_encode(is_array($event->event_route) ? $event->event_route : json_decode($event->event_route,true)) : 'null';
            $jsPois     = $hasPois ? json_encode(is_array($event->event_pois) ? $event->event_pois : json_decode($event->event_pois,true)) : 'null';
            $mapBadges  = array_keys(array_filter(['📍 Pin'=>$hasPin,'⬡ Boundary'=>$hasPolygon,'〰 Route'=>$hasRoute,'🚩 POIs'=>$hasPois]));
            $poisDecoded= $hasPois ? (is_array($event->event_pois) ? $event->event_pois : json_decode($event->event_pois,true)) : [];
            $poiEmojis  = ['entrance'=>'🚪','exit'=>'🚪','car_park'=>'🅿','medical'=>'🩺','control'=>'📡','hazard'=>'⚠','info'=>'ℹ','custom'=>'🚩'];
        @endphp

        <div class="rn-map-wrap">
            <div class="rn-map-toolbar">
                <button class="rn-map-tool-btn" id="rn-sat-btn" onclick="toggleSat()">🛰 Satellite</button>
                <button class="rn-map-tool-btn" id="rn-grid-btn" onclick="toggleGridMode()" style="color:#7dd3fc;border-color:rgba(125,211,252,.3);">⊞ Grid Locator</button>
                <button class="rn-map-tool-btn" onclick="toggleFull()">⛶ Fullscreen</button>
                @if(count($mapBadges))
                <div class="rn-map-badges">
                    @foreach($mapBadges as $b)<span class="rn-map-badge">{{ $b }}</span>@endforeach
                </div>
                @endif
            </div>
            <div id="rn-event-map"></div>

            @if($hasPin)
            <div class="rn-grid-strip">
                <div class="rn-grid-lbl">Maidenhead<br>Locator</div>
                <div>
                    <div class="rn-grid-val" id="rn-grid-val">—</div>
                    <div class="rn-grid-sub">
                        {{ number_format(abs($jsLat),4) }}{{ $jsLat >= 0 ? 'N' : 'S' }}
                        &nbsp;
                        {{ number_format(abs($jsLng),4) }}{{ $jsLng < 0 ? 'W' : 'E' }}
                    </div>
                </div>
                <span class="rn-grid-mode-hint" id="rn-grid-hint" style="display:none;">Click map for any point</span>
                <button class="rn-grid-copy" onclick="copyGrid()" id="rn-grid-copy-btn">Copy</button>
            </div>
            @endif

            @if($showMap)
            <div class="rn-map-foot">
                @if($hasPin)<div class="rn-legend-item"><div class="rn-legend-pin" style="background:#C8102E;border:1.5px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>Location</div>@endif
                @if($hasPolygon)<div class="rn-legend-item"><div class="rn-legend-swatch" style="background:rgba(26,107,60,.12);border-color:#1a6b3c;"></div>{{ $jsPolyName }}</div>@endif
                @if($hasRoute)<div class="rn-legend-item"><svg width="22" height="5" style="overflow:visible;flex-shrink:0;"><line x1="0" y1="2.5" x2="22" y2="2.5" stroke="#7c3aed" stroke-width="2.5" stroke-dasharray="6 4" stroke-dashoffset="22" style="animation:dashMove 1.8s linear infinite;"/></svg>Route</div>@endif
                @if($hasPois)<div class="rn-legend-item">🚩 POIs</div>@endif
            </div>
            @endif

            @auth
            @if($hasPois && count($poisDecoded))
            <div class="rn-poi-strip">
                <div class="rn-poi-strip-hd">Points of Interest</div>
                <div class="rn-poi-chips">
                    @foreach($poisDecoded as $poi)
                    @php $em = $poiEmojis[$poi['type']??'custom']??'🚩'; @endphp
                    <button type="button" class="rn-poi-chip" onclick="flyToPoi('{{ $poi['id']??'' }}',{{ $poi['lat']??0 }},{{ $poi['lng']??0 }})">
                        {{ $em }} <strong>{{ $poi['name'] ?? ($poi['type'] ?? 'POI') }}</strong>
                        @if(!empty($poi['grid_ref']))<span class="gridref">{{ $poi['grid_ref'] }}</span>@endif
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
            @else
            @if($hasPois && count($poisDecoded))
            <div class="rn-poi-strip" style="background:#f8f9fb;">
                <div class="rn-poi-strip-hd" style="color:#9aa3ae;">🔒 Points of Interest — visible to logged-in members only</div>
            </div>
            @endif
            @endauth
        </div>

        {{-- Elevation Profile --}}
        @if($hasRoute)
        <div id="pub-elev-panel" style="border:1px solid var(--border);border-top:none;background:#fff;margin-bottom:1rem;">
            <div style="padding:.6rem .85rem;background:#f8f9fb;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;cursor:pointer;" onclick="togglePubElev()">
                <span style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:#4b5563;">⛰ Elevation Profile</span>
                <span id="pub-elev-chevron" style="font-size:10px;color:#9aa3ae;">▼</span>
            </div>
            <div id="pub-elev-body" style="padding:.75rem .85rem;">
                <div style="position:relative;">
                    <canvas id="pub-elev-chart" style="width:100%;height:120px;display:block;"></canvas>
                    <div id="pub-elev-loading" style="text-align:center;font-size:12px;color:#9aa3ae;padding:.5rem;">Loading elevation data…</div>
                </div>
                <div id="pub-elev-stats" style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:.5rem;font-size:11px;color:#6b7f96;"></div>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        @auth
        @php $eventDocs = $event->relationLoaded('documents') ? $event->documents : $event->documents()->orderBy('sort_order')->get(); @endphp
        <div style="border-top:1px solid var(--border);">
            <div class="rn-card-head" style="background:var(--navy);color:rgba(255,255,255,.7);border-bottom-color:rgba(255,255,255,.1);">
                <div class="rn-card-head-dot"></div>
                Event Documentation
                @if($eventDocs->isNotEmpty())<span style="margin-left:auto;font-weight:normal;">{{ $eventDocs->count() }} {{ Str::plural('file',$eventDocs->count()) }}</span>@endif
            </div>
            @if($eventDocs->isEmpty())
                <div class="rn-doc-empty">Briefing notes and documents will appear here when available.</div>
            @else
                <div class="rn-doc-list">
                    @foreach($eventDocs as $doc)
                    @php
                        $ext  = strtolower(pathinfo($doc->filename, PATHINFO_EXTENSION));
                        $icon = match($ext) { 'pdf'=>'📄','doc','docx'=>'📝','xls','xlsx'=>'📊','ppt','pptx'=>'📋','jpg','jpeg','png'=>'🖼','zip'=>'🗜',default=>'📎' };
                        $sz   = $doc->size_bytes ? ($doc->size_bytes < 1048576 ? round($doc->size_bytes/1024).' KB' : round($doc->size_bytes/1048576,1).' MB') : null;
                    @endphp
                    <div class="rn-doc-item">
                        <div class="rn-doc-icon">{{ $icon }}</div>
                        <div class="rn-doc-info">
                            <a href="{{ route('events.documents.download',$doc->id) }}" class="rn-doc-name" target="_blank">{{ $doc->label ?: $doc->filename }}</a>
                            <div class="rn-doc-meta">{{ strtoupper($ext) }}@if($sz) · {{ $sz }}@endif</div>
                        </div>
                        <a href="{{ route('events.documents.download',$doc->id) }}" class="rn-doc-dl" target="_blank">↓</a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @else
        <div style="border-top:1px solid var(--border);">
            <div class="rn-card-head" style="background:var(--navy);color:rgba(255,255,255,.7);border-bottom-color:rgba(255,255,255,.1);">
                <div class="rn-card-head-dot"></div>
                Event Documentation
            </div>
            <div class="rn-doc-gate">
                🔒 Documents are available to logged-in members only.<br>
                <a href="{{ route('login') }}">Sign in to access them →</a>
            </div>
        </div>
        @endauth

        @if(session('is_admin'))
        <div class="rn-admin-hint">Admin · <a href="{{ route('admin.events',['docs'=>$event->id]) }}">Manage documents →</a></div>
        @endif

    @endif
</div>

{{-- SIDEBAR --}}
<aside class="rn-sidebar">

    <div class="rn-card">
        <div class="rn-card-head"><div class="rn-card-head-dot"></div>Event Details</div>
        <div class="rn-card-body">
            <div class="rn-meta-row">
                <div class="rn-meta-lbl">Date</div>
                <div class="rn-meta-val">{{ $event->starts_at->format('D j M Y') }}</div>
            </div>
            <div class="rn-meta-row">
                <div class="rn-meta-lbl">Time</div>
                <div class="rn-meta-val">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) – {{ $event->ends_at->format('H:i') }}@endif</div>
            </div>
            @if($event->location)
            <div class="rn-meta-row">
                <div class="rn-meta-lbl">Location</div>
                <div class="rn-meta-val">
                    @if($event->is_private && !auth()->check())
                        <span style="filter:blur(4px);user-select:none;pointer-events:none;">{{ $event->location }}</span>
                        <span style="font-size:11px;color:var(--red);margin-left:6px;">🔒 <a href="{{ route('login') }}" style="color:var(--red);font-weight:bold;">Sign in</a> to view</span>
                    @else
                        {{ $event->location }}
                    @endif
                </div>
            </div>
            @endif
            @if($event->type)
            <div class="rn-meta-row">
                <div class="rn-meta-lbl">Type</div>
                <div class="rn-meta-val">
                    <span style="display:inline-block;padding:2px 8px;font-size:11px;font-weight:bold;background:{{ $chipColour }}18;border:1px solid {{ $chipColour }}55;color:{{ $chipColour }};">{{ $event->type->name }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="rn-card">
        <div class="rn-card-head"><div class="rn-card-head-dot"></div>Actions</div>
        <div class="rn-card-body" style="padding:10px;">
            <a href="{{ route('request-support') }}" class="rn-btn rn-btn-primary">Request RAYNET Support</a>
            @if(!$event->is_private || auth()->check())
            <button type="button" class="rn-btn rn-btn-ghost" onclick="toggleShare()" id="rn-share-toggle">Share / Add to Calendar</button>
            @endif
        </div>
        <div class="rn-share-panel" id="rn-share-panel">
            @php
                $gcStart = $event->starts_at->format('Ymd\THis');
                $gcEnd   = ($event->ends_at ?? $event->starts_at->addHours(2))->format('Ymd\THis');
                $gcTitle = urlencode($event->title);
                $gcLoc   = urlencode($event->location ?? '');
                $gcDesc  = urlencode(Str::limit($event->description ?? '',200));
                $gcUrl   = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcTitle}&dates={$gcStart}/{$gcEnd}&location={$gcLoc}&details={$gcDesc}";
                $olStart = urlencode($event->starts_at->toIso8601String());
                $olEnd   = urlencode(($event->ends_at ?? $event->starts_at->addHours(2))->toIso8601String());
                $olUrl   = "https://outlook.live.com/calendar/0/deeplink/compose?subject={$gcTitle}&startdt={$olStart}&enddt={$olEnd}&location={$gcLoc}&body={$gcDesc}&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent";
            @endphp
            <div class="rn-share-lbl">Add to Calendar</div>
            <div class="rn-cal-btns">
                <a href="{{ $gcUrl }}" target="_blank" rel="noopener" class="rn-cal-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="17" rx="2" stroke="#4285F4" stroke-width="2"/><path d="M16 2v4M8 2v4M3 9h18" stroke="#4285F4" stroke-width="2" stroke-linecap="round"/></svg>
                    Google Calendar
                </a>
                <a href="{{ route('events.ics',['year'=>$event->starts_at->format('Y'),'month'=>$event->starts_at->format('m'),'slug'=>$event->slug]) }}" class="rn-cal-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="17" rx="2" stroke="#555" stroke-width="2"/><path d="M16 2v4M8 2v4M3 9h18" stroke="#555" stroke-width="2" stroke-linecap="round"/></svg>
                    Apple / ICS File
                </a>
                <a href="{{ $olUrl }}" target="_blank" rel="noopener" class="rn-cal-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="2" stroke="#0078D4" stroke-width="2"/><path d="M2 9l10 6 10-6" stroke="#0078D4" stroke-width="2" stroke-linecap="round"/></svg>
                    Outlook
                </a>
            </div>
            <div class="rn-share-lbl">Share Link</div>
            <div class="rn-share-url-row">
                <input type="text" id="rn-share-url" value="{{ $event->url() }}" readonly>
                <button class="rn-copy-btn" id="rn-copy-btn" onclick="copyUrl()">Copy</button>
            </div>
        </div>
    </div>
</div>{{-- /actions card --}}

 @auth
@if(!$isEnded)
@php
    $myRsvp     = $event->rsvps()->where('user_id', auth()->id())->first();
    $rsvpCounts = $event->rsvps()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status');
    $attending  = $rsvpCounts['attending'] ?? 0;
    $maybe      = $rsvpCounts['maybe']     ?? 0;
    $declined   = $rsvpCounts['declined']  ?? 0;
@endphp
<div class="rn-card">
    <div class="rn-card-head">
        <div class="rn-card-head-dot"></div>
        RSVP
        @if($myRsvp)
            @php $badge = ['attending'=>'✓ Attending','maybe'=>'? Maybe','declined'=>'✕ Declined'][$myRsvp->status]; @endphp
            <span style="margin-left:auto;font-size:9px;font-weight:bold;color:var(--muted);">{{ $badge }}</span>
        @endif
    </div>
    <div class="rn-rsvp-counts">
        <div class="rn-rsvp-count"><div class="rn-rsvp-count-num" style="color:var(--green);">{{ $attending }}</div><div class="rn-rsvp-count-lbl">Attending</div></div>
        <div class="rn-rsvp-count"><div class="rn-rsvp-count-num" style="color:#8a5c00;">{{ $maybe }}</div><div class="rn-rsvp-count-lbl">Maybe</div></div>
        <div class="rn-rsvp-count"><div class="rn-rsvp-count-num" style="color:var(--red);">{{ $declined }}</div><div class="rn-rsvp-count-lbl">Declined</div></div>
    </div>
    <div class="rn-card-body" style="padding:12px;">
        @if(session('rsvp_saved'))
            <div style="font-size:12px;font-weight:bold;color:var(--green);margin-bottom:8px;padding:6px 10px;background:#eef7f2;border:1px solid #b8ddc9;">
                ✓ Response saved.
            </div>
        @endif

        {{-- One-click status buttons — each is its own form --}}
        <div class="rn-rsvp-btn-group">
            <form method="POST" action="{{ route('events.rsvp.store', $event) }}" style="display:contents;">
                @csrf
                <input type="hidden" name="status" value="attending">
                <input type="hidden" name="note" value="{{ $myRsvp?->note }}">
                <button type="submit" class="rn-rsvp-btn attending {{ ($myRsvp?->status === 'attending') ? 'active' : '' }}">✓ Going</button>
            </form>
            <form method="POST" action="{{ route('events.rsvp.store', $event) }}" style="display:contents;">
                @csrf
                <input type="hidden" name="status" value="maybe">
                <input type="hidden" name="note" value="{{ $myRsvp?->note }}">
                <button type="submit" class="rn-rsvp-btn maybe {{ ($myRsvp?->status === 'maybe') ? 'active' : '' }}">? Maybe</button>
            </form>
            <form method="POST" action="{{ route('events.rsvp.store', $event) }}" style="display:contents;">
                @csrf
                <input type="hidden" name="status" value="declined">
                <input type="hidden" name="note" value="{{ $myRsvp?->note }}">
                <button type="submit" class="rn-rsvp-btn declined {{ ($myRsvp?->status === 'declined') ? 'active' : '' }}">✕ Can't</button>
            </form>
        </div>

        {{-- Note form — updates note without changing status --}}
        <form method="POST" action="{{ route('events.rsvp.store', $event) }}" style="margin-top:8px;">
            @csrf
            <input type="hidden" name="status" value="{{ $myRsvp?->status ?? 'attending' }}">
            <textarea name="note" class="rn-rsvp-note" placeholder="Optional note — kit bringing, late arrival…">{{ $myRsvp?->note }}</textarea>
            <button type="submit" class="rn-rsvp-save">Save Note</button>
        </form>

        @if($myRsvp)
        <form method="POST" action="{{ route('events.rsvp.destroy', $event) }}">
            @csrf @method('DELETE')
            <button type="submit" class="rn-rsvp-remove" onclick="return confirm('Remove your RSVP?')">Remove my response</button>
        </form>
        @endif
    </div>
</div>
@endif
@endauth

@if($event->hasLocation() && !$isEnded)
<div class="rn-card" id="rn-weather-card">
    <div class="rn-card-head" style="background:#0284c7;color:rgba(255,255,255,.8);border-bottom-color:rgba(255,255,255,.15);">
        <div class="rn-card-head-dot" style="background:#fff;"></div>
        Weather · {{ $event->starts_at->format('j M') }}
        <span id="rn-weather-src" style="margin-left:auto;font-weight:normal;font-size:9px;opacity:.6;"></span>
    </div>
    @if($showWeather)
        <div id="rn-weather-inner" class="rn-weather-loading">Fetching forecast…</div>
    @else
        <div class="rn-weather-not-yet">🌤 Forecast available from <strong>{{ $event->starts_at->subDays(14)->format('j M') }}</strong>.</div>
    @endif
</div>
@endif

</aside>
</div>
<div class="rn-footer">
    <span class="rn-footer-dot"></span>
    RAYNET — Radio Amateurs' Emergency Network. Voluntary communications support across the UK.
</div>
</div>

{{-- LEAFLET --}}
@if(!$event->is_private || session('is_admin') || auth()->check())
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(function(){
    var HAS_PIN={{ $hasPin?'true':'false' }},HAS_POLY={{ $hasPolygon?'true':'false' }},HAS_ROUTE={{ $hasRoute?'true':'false' }},HAS_POIS={{ $hasPois?'true':'false' }};
    var PIN_LAT={{ $jsLat }},PIN_LNG={{ $jsLng }};
    var POLYGON_GEO={!! $jsPolygon !!},POLY_NAME="{{ $jsPolyName }}",ROUTE_DATA={!! $jsRoute !!},POIS_DATA={!! $jsPois !!},USER_AUTHED={{ auth()->check() ? 'true' : 'false' }};
    var POI_TYPES={entrance:{label:'Entrance',emoji:'🚪',colour:'#1a6b3c'},exit:{label:'Exit',emoji:'🚪',colour:'#C8102E'},car_park:{label:'Car Park',emoji:'🅿',colour:'#003366'},medical:{label:'Medical',emoji:'🩺',colour:'#dc2626'},control:{label:'Control',emoji:'📡',colour:'#7c3aed'},hazard:{label:'Hazard',emoji:'⚠',colour:'#d97706'},info:{label:'Info',emoji:'ℹ',colour:'#0284c7'},custom:{label:'Custom',emoji:'🚩',colour:'#C8102E'}};
    var poiMap={},animPaths=[],streetLayer,satLayer,satOn=false,gridModeActive=false,maidenheadLayer=null;

    /* ── Maidenhead locator — up to 10 characters (precision 1–5) ── */
    function latLngToMaidenhead(lat,lng,precision){
        precision = precision || 5;
        var L = lng + 180, la = lat + 90;
        var r = '';
        /* Pair 1 — Field: IO */
        r += String.fromCharCode(65 + Math.floor(L / 20));
        r += String.fromCharCode(65 + Math.floor(la / 10));
        if(precision === 1) return r;
        /* Pair 2 — Square: IO83 */
        r += Math.floor((L % 20) / 2);
        r += Math.floor(la % 10);
        if(precision === 2) return r;
        /* Pair 3 — Subsquare: IO83ml */
        r += String.fromCharCode(97 + Math.floor((L  % 2)       / (2 / 24)));
        r += String.fromCharCode(97 + Math.floor((la % 1)       / (1 / 24)));
        if(precision === 3) return r;
        /* Pair 4 — Extended square: IO83ml74 */
        r += Math.floor((L  % (2 / 24))  / (2 / 240));
        r += Math.floor((la % (1 / 24))  / (1 / 240));
        if(precision === 4) return r;
        /* Pair 5 — Extended subsquare: IO83ml74JA */
        r += String.fromCharCode(65 + Math.floor((L  % (2 / 240)) / (2 / 5760)));
        r += String.fromCharCode(65 + Math.floor((la % (1 / 240)) / (1 / 5760)));
        return r;
    }

    function esc(s){return(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    var map=L.map('rn-event-map',{center:[HAS_PIN?PIN_LAT:53.4084,HAS_PIN?PIN_LNG:-2.9916],zoom:HAS_PIN?15:12,scrollWheelZoom:false});
    window._rnEventMap = map;
    streetLayer=L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors',maxZoom:19}).addTo(map);
    satLayer=L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{attribution:'© Esri',maxZoom:19});
    var bounds=L.featureGroup().addTo(map);

    if(HAS_POLY&&POLYGON_GEO&&POLYGON_GEO.coordinates){
        var wr=[[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];
        (POLYGON_GEO.type==='MultiPolygon'?POLYGON_GEO.coordinates:[POLYGON_GEO.coordinates]).forEach(function(r){
            var m=L.geoJSON({type:'Feature',geometry:{type:'Polygon',coordinates:[wr,r[0]]}},{style:{color:'transparent',weight:0,fillColor:'#001f40',fillOpacity:.3},interactive:false}).addTo(map);
            m.eachLayer(function(l){if(l._path){l._path.style.pointerEvents='none';l._path.setAttribute('pointer-events','none');}});
        });
        var ll=POLYGON_GEO.type==='MultiPolygon'
            ?POLYGON_GEO.coordinates.map(function(r){return r.map(function(ring){return ring.map(function(c){return[c[1],c[0]];});});})
            :POLYGON_GEO.coordinates.map(function(ring){return ring.map(function(c){return[c[1],c[0]];});});
        var poly=L.polygon(ll,{color:'#1a6b3c',weight:2,fillColor:'#1a6b3c',fillOpacity:.12}).addTo(map);
        poly.bindPopup('<strong style="color:#1a6b3c;font-size:12px;">⬡ '+esc(POLY_NAME)+'</strong>',{maxWidth:200});
        bounds.addLayer(poly);
    }

    if(HAS_ROUTE&&ROUTE_DATA){
        (Array.isArray(ROUTE_DATA)?ROUTE_DATA:[{id:'r0',name:'Route',geometry:ROUTE_DATA}]).forEach(function(r){
            if(!r.geometry||!r.geometry.coordinates)return;
            var lls=(r.geometry.type==='LineString'?r.geometry.coordinates:r.geometry.coordinates[0]).map(function(c){return[c[1],c[0]];});
            L.polyline(lls,{color:'#7c3aed',weight:5,opacity:.15,interactive:false}).addTo(map);
            var dl=L.polyline(lls,{color:'#7c3aed',weight:4,opacity:1,dashArray:'12 8',lineCap:'round'}).addTo(map);
            function anim(layer){var p=layer._path;if(!p)return;p.style.strokeDasharray='12 8';p.style.strokeDashoffset='400';p.style.animation='dashMove 6s linear infinite';animPaths.push(p);}
            if(dl._path)anim(dl);else dl.on('add',function(){anim(dl);});
            dl.bindPopup('<strong style="color:#7c3aed;font-size:12px;">〰 '+esc(r.name||'Route')+'</strong>',{maxWidth:200});
            bounds.addLayer(dl);
            function epIcon(lbl){return L.divIcon({className:'',html:'<div style="width:18px;height:18px;background:#7c3aed;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 5px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:bold;color:#fff;">'+lbl+'</div>',iconSize:[18,18],iconAnchor:[9,9]});}
            L.marker(lls[0],{icon:epIcon('S'),interactive:false,keyboard:false}).addTo(map);
            L.marker(lls[lls.length-1],{icon:epIcon('E'),interactive:false,keyboard:false}).addTo(map);
        });
        map.on('zoomend',function(){setTimeout(function(){animPaths.forEach(function(p){if(p&&p.parentNode){p.style.animation='none';void p.offsetWidth;p.style.animation='dashMove 6s linear infinite';}});},50);});
    }

    if(HAS_PIN){
        var pi=L.divIcon({className:'',html:'<div style="width:24px;height:24px;background:#C8102E;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,.45);"></div>',iconSize:[24,24],iconAnchor:[12,24],popupAnchor:[0,-26]});
        var pm=L.marker([PIN_LAT,PIN_LNG],{icon:pi}).addTo(map);
        var locName="{{ e($event->location ?? '') }}";
        var grid=latLngToMaidenhead(PIN_LAT,PIN_LNG,5);
        pm.bindPopup('<div style="font-family:Arial,sans-serif;"><strong style="color:#C8102E;font-size:12px;">📍 '+(locName?esc(locName):'Event Location')+'</strong><div style="font-size:11px;color:#1565c0;margin-top:4px;font-weight:bold;letter-spacing:.06em;">⊞ '+grid+'</div></div>',{maxWidth:220});
        bounds.addLayer(pm);
        var gEl=document.getElementById('rn-grid-val');
        if(gEl)gEl.textContent=grid;
        window._rnGrid=grid;
    }

    if(HAS_POIS&&Array.isArray(POIS_DATA)&&USER_AUTHED){
        POIS_DATA.forEach(function(poi){
            if(!poi.lat||!poi.lng)return;
            var pt=POI_TYPES[poi.type]||POI_TYPES.custom,col=poi.colour||pt.colour;
            var ic=L.divIcon({className:'',html:'<div style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:'+col+';border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4);font-size:13px;line-height:1;">'+pt.emoji+'</div>',iconSize:[26,26],iconAnchor:[13,26],popupAnchor:[0,-28]});
            var mk=L.marker([poi.lat,poi.lng],{icon:ic}).addTo(map);
            var poiGrid=latLngToMaidenhead(poi.lat,poi.lng,5);
            var html='<div style="font-family:Arial,sans-serif;min-width:130px;"><div style="font-size:10px;font-weight:bold;text-transform:uppercase;color:'+col+';margin-bottom:3px;">'+pt.emoji+' '+pt.label+'</div><strong style="font-size:13px;color:#003366;">'+esc(poi.name||pt.label)+'</strong>';
            if(poi.description)html+='<div style="font-size:11px;color:#6b7f96;margin-top:2px;">'+esc(poi.description)+'</div>';
            if(poi.grid_ref)html+='<div style="font-size:10px;color:#9aa3ae;margin-top:3px;font-weight:bold;">'+esc(poi.grid_ref)+'</div>';
            html+='<div style="font-size:10px;color:#1565c0;margin-top:2px;font-weight:bold;">⊞ '+poiGrid+'</div></div>';
            mk.bindPopup(html,{maxWidth:220});
            poiMap[poi.id]=mk;
            bounds.addLayer(mk);
        });
    }

    if(HAS_POLY||HAS_ROUTE||(HAS_POIS&&POIS_DATA&&POIS_DATA.length&&USER_AUTHED)){
        try{var b=bounds.getBounds();if(b.isValid())map.fitBounds(b,{padding:[24,24],maxZoom:17});}catch(e){}
    }
    map.once('focus click',function(){map.scrollWheelZoom.enable();});

    /* ── Grid mode click — show locator popup ── */
    map.on('click',function(e){
        if(!gridModeActive)return;
        var grid=latLngToMaidenhead(e.latlng.lat,e.latlng.lng,5);
        L.popup().setLatLng(e.latlng).setContent(
            '<div style="font-family:Arial,sans-serif;text-align:center;padding:4px 2px;min-width:150px;">'
            +'<div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#1565c0;margin-bottom:5px;">⊞ Maidenhead Locator</div>'
            +'<div style="font-size:18px;font-weight:bold;color:#003366;letter-spacing:.08em;margin-bottom:4px;">'+grid+'</div>'
            +'<div style="font-size:10px;color:#9aa3ae;margin-bottom:6px;">'+e.latlng.lat.toFixed(5)+', '+e.latlng.lng.toFixed(5)+'</div>'
            +'<button onclick="navigator.clipboard.writeText(\''+grid+'\')" style="width:100%;padding:5px;background:#003366;border:none;color:#fff;font-size:10px;font-weight:bold;cursor:pointer;font-family:Arial;letter-spacing:.04em;">Copy '+grid+'</button>'
            +'</div>'
        ).openOn(map);
    });

    window.flyToPoi=function(id,lat,lng){map.flyTo([lat,lng],18,{animate:true,duration:.8});if(poiMap[id])setTimeout(function(){poiMap[id].openPopup();},850);};

    window.toggleSat=function(){
        satOn=!satOn;
        if(satOn){streetLayer.remove();satLayer.addTo(map);}else{satLayer.remove();streetLayer.addTo(map);}
        document.getElementById('rn-sat-btn').textContent=satOn?'🗺 Street':'🛰 Satellite';
    };
    window.toggleFull=function(){
        var el=document.getElementById('rn-event-map');
        if(!document.fullscreenElement)el.requestFullscreen().then(function(){map.invalidateSize();});
        else document.exitFullscreen();
    };

    /* ── Maidenhead overlay ── */
    window.toggleGridMode=function(){
        gridModeActive=!gridModeActive;
        var btn=document.getElementById('rn-grid-btn');
        var hint=document.getElementById('rn-grid-hint');
        btn.style.opacity=gridModeActive?'1':'.7';
        btn.style.background=gridModeActive?'rgba(125,211,252,.15)':'';
        if(hint)hint.style.display=gridModeActive?'block':'none';
        if(gridModeActive){
            maidenheadLayer=L.layerGroup().addTo(map);
            drawMaidenhead();
            map.on('moveend zoomend',drawMaidenhead);
        } else {
            map.off('moveend zoomend',drawMaidenhead);
            if(maidenheadLayer){map.removeLayer(maidenheadLayer);maidenheadLayer=null;}
            map.closePopup();
            map.getContainer().style.cursor='';
        }
    };

    function drawMaidenhead(){
        if(!maidenheadLayer)return;
        maidenheadLayer.clearLayers();
        var zoom=map.getZoom();
        var b=map.getBounds().pad(0.1);
        var S=b.getSouth(),N=b.getNorth(),W=b.getWest(),E=b.getEast();

        /* Always — Fields 20°×10° (IO) — label when zoom < 6 */
        drawGrid(S,N,W,E, 20,10, 2.5, 1, 15, zoom<6);

        /* zoom 6+ — Squares 2°×1° (IO83) — label when zoom 6–9 */
        if(zoom>=6)  drawGrid(S,N,W,E, 2,1,       2,   2, 12, zoom<10);

        /* zoom 10+ — Subsquares 5'×2.5' (IO83ml) — label when zoom 10–13 */
        if(zoom>=10) drawGrid(S,N,W,E, 2/24,1/24,  1.5, 3, 10, zoom<14);

        /* zoom 14+ — Extended squares (IO83ml74) — label when zoom 14–16 */
        if(zoom>=14) drawGrid(S,N,W,E, 2/240,1/240, 1,  4,  9, zoom<17);

        /* zoom 17+ — Extended subsquares (IO83ml74JA) — always label */
        if(zoom>=17) drawGrid(S,N,W,E, 2/5760,1/5760, 0.75, 5, 8, true);
    }

    function drawGrid(S,N,W,E, lngStep,latStep, weight, precision, labelSize, showLabels){
        var startLng=Math.floor(W/lngStep)*lngStep;
        var startLat=Math.floor(S/latStep)*latStep;
        var lineStyle={color:'#C8102E',weight:weight,opacity:0.8,interactive:false};

        /* Lines */
        for(var lng=startLng;lng<=E+lngStep;lng+=lngStep){
            L.polyline([[Math.max(S-1,-90),lng],[Math.min(N+1,90),lng]],lineStyle).addTo(maidenheadLayer);
        }
        for(var lat=startLat;lat<=N+latStep;lat+=latStep){
            L.polyline([[lat,Math.max(W-1,-180)],[lat,Math.min(E+1,180)]],lineStyle).addTo(maidenheadLayer);
        }

        if(!showLabels)return;

        /* Labels — one per grid cell, centred, unconstrained width */
        for(var lat=startLat;lat<=N;lat+=latStep){
            for(var lng=startLng;lng<=E;lng+=lngStep){
                var clat=lat+latStep/2;
                var clng=lng+lngStep/2;
                if(clat<S||clat>N||clng<W||clng>E)continue;
                var label=latLngToMaidenhead(clat,clng,precision);
                L.marker([clat,clng],{
                    icon:L.divIcon({
                        className:'',
                        html:'<div style="'
                            +'position:absolute;'
                            +'transform:translate(-50%,-50%);'
                            +'white-space:nowrap;'
                            +'font-size:'+labelSize+'px;'
                            +'font-weight:bold;'
                            +'font-family:Arial,Helvetica,sans-serif;'
                            +'color:#fff;'
                            +'background:#C8102E;'
                            +'padding:2px 5px;'
                            +'letter-spacing:.06em;'
                            +'pointer-events:none;'
                            +'line-height:1.4;'
                            +'">'+label+'</div>',
                        iconSize:[0,0],
                        iconAnchor:[0,0],
                    }),
                    interactive:false,
                    keyboard:false,
                }).addTo(maidenheadLayer);
            }
        }
    }

    window.copyGrid=function(){
        var g=window._rnGrid;if(!g)return;
        var btn=document.getElementById('rn-grid-copy-btn');
        navigator.clipboard.writeText(g).then(function(){
            btn.textContent='Copied!';btn.style.color='#22d47d';btn.style.borderColor='rgba(34,212,125,.4)';
            setTimeout(function(){btn.textContent='Copy';btn.style.color='';btn.style.borderColor='';},2000);
        });
    };

})();
</script>
@endif
@if($isUpcoming)
<script>
(function(){
    var target=new Date({{ Js::from($event->starts_at->toIso8601String()) }}).getTime()/1000;
    function tick(){
        var diff=target-Math.floor(Date.now()/1000);
        if(diff<=0){var el=document.getElementById('rn-countdown');if(el)el.remove();return;}
        var d=Math.floor(diff/86400),h=Math.floor((diff%86400)/3600),m=Math.floor((diff%3600)/60),s=diff%60;
        document.getElementById('cd-d').textContent=String(d).padStart(2,'0');
        document.getElementById('cd-h').textContent=String(h).padStart(2,'0');
        document.getElementById('cd-m').textContent=String(m).padStart(2,'0');
        document.getElementById('cd-s').textContent=String(s).padStart(2,'0');
    }
    tick();setInterval(tick,1000);
})();
</script>
@endif

@if($showWeather)
<script>
(function(){
    var lat={{ $event->event_lat }},lng={{ $event->event_lng }};
    var date='{{ $event->starts_at->format("Y-m-d") }}',hour={{ $event->starts_at->format("G") }};
    var WI=['☀️','🌤','⛅','🌥','☁️','','','','','','🌦','','','','','🌧','','','','','🌧','','','','','🌧','','','','','❄️','','','','','🌦','','','','','🌦','','','','','🌦','','','','','🌦','','⛈'];
    var WL=['Clear','Mainly clear','Partly cloudy','Overcast','Overcast','','','','','','Drizzle','','','','','Drizzle','','','','','Rain','','','','','Heavy rain','','','','','Snow','','','','','Showers','','','','','Showers','','','','','Heavy showers','','','','','Thunderstorm'];
    function cmp(d){return['N','NE','E','SE','S','SW','W','NW'][Math.round(d/45)%8];}
    fetch('https://api.open-meteo.com/v1/forecast?latitude='+lat+'&longitude='+lng+'&hourly=temperature_2m,apparent_temperature,precipitation_probability,windspeed_10m,winddirection_10m,weathercode,visibility&timezone=Europe%2FLondon&start_date='+date+'&end_date='+date)
    .then(function(r){return r.json();}).then(function(d){
        var h=d.hourly,idx=Math.min(Math.max(hour,0),(h.time||[]).length-1);
        var icon=WI[h.weathercode[idx]]||'🌡',label=WL[h.weathercode[idx]]||'Variable';
        var temp=Math.round(h.temperature_2m[idx]),feels=Math.round(h.apparent_temperature[idx]);
        var wind=Math.round(h.windspeed_10m[idx]),dir=Math.round(h.winddirection_10m[idx]);
        var rain=Math.round(h.precipitation_probability[idx]);
        var vis=h.visibility?(h.visibility[idx]/1000).toFixed(1)+'km':'—';
        var needle='<svg viewBox="0 0 36 36" fill="none" style="width:36px;height:36px;flex-shrink:0;"><circle cx="18" cy="18" r="16" stroke="#e1e5ec" stroke-width="1.5" fill="white"/><g transform="rotate('+dir+' 18 18)"><polygon points="18,4 21,18 15,18" fill="#C8102E"/><polygon points="18,32 21,18 15,18" fill="#c0c8d4"/></g><circle cx="18" cy="18" r="2.5" fill="#003366"/></svg>';
        document.getElementById('rn-weather-inner').innerHTML=
            '<div class="rn-weather-body">'
            +'<div class="rn-weather-top"><div class="rn-weather-icon">'+icon+'</div><div><div class="rn-weather-temp">'+temp+'°C</div><div class="rn-weather-feels">Feels '+feels+'°</div><div class="rn-weather-desc">'+label+'</div></div></div>'
            +'<div class="rn-weather-stats">'
            +'<div><div class="rn-weather-stat-lbl">Wind</div><div class="rn-weather-stat-val">'+wind+' km/h '+cmp(dir)+'</div></div>'
            +'<div><div class="rn-weather-stat-lbl">Rain chance</div><div class="rn-weather-stat-val">'+rain+'%</div></div>'
            +'<div><div class="rn-weather-stat-lbl">Visibility</div><div class="rn-weather-stat-val">'+vis+'</div></div>'
            +'<div class="rn-weather-compass">'+needle+'<span style="font-size:11px;font-weight:bold;color:#003366;">'+wind+' km/h '+cmp(dir)+'</span></div>'
            +'</div></div>';
        document.getElementById('rn-weather-src').textContent='Open-Meteo';
    }).catch(function(){
        document.getElementById('rn-weather-inner').innerHTML='<div style="padding:12px 14px;font-size:12px;color:var(--muted);">Forecast unavailable.</div>';
    });
})();
function toggleMRsvp(id) {
    const panel = document.getElementById('m-rsvp-' + id);
    const card  = document.getElementById('m-card-' + id);
    const wasOpen = panel.classList.contains('is-open');
    document.querySelectorAll('.m-rsvp-panel.is-open').forEach(p => p.classList.remove('is-open'));
    if (!wasOpen) {
        panel.classList.add('is-open');
        setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 40);
    }
}
</script>
@endif

<script>
function toggleShare(){
    var p=document.getElementById('rn-share-panel'),b=document.getElementById('rn-share-toggle');
    var open=p.style.display==='block';
    p.style.display=open?'none':'block';
    b.textContent=open?'Share / Add to Calendar':'✕ Close';
}
function copyUrl(){
    var inp=document.getElementById('rn-share-url'),btn=document.getElementById('rn-copy-btn');
    navigator.clipboard.writeText(inp.value).then(function(){
        btn.textContent='Copied!';btn.style.background='var(--green)';
        setTimeout(function(){btn.textContent='Copy';btn.style.background='';},2000);
    });
}
function setRsvp(status){
    document.getElementById('rsvp-status').value=status;
    document.querySelectorAll('.rn-rsvp-btn').forEach(function(b){
        b.classList.toggle('active',b.classList.contains(status));
    });
}
</script>

@if($hasRoute)
<script>
(function() {
    var routeData = {!! $jsRoute !!};
    if (!routeData) return;
    var allCoords = [];
    if (Array.isArray(routeData)) {
        routeData.forEach(function(seg) {
            var geom = seg.geometry || seg;
            if (geom && geom.coordinates) {
                geom.coordinates.forEach(function(c) { allCoords.push([c[1], c[0]]); });
            }
        });
    }
    if (allCoords.length < 2) {
        var el = document.getElementById('pub-elev-loading');
        if (el) el.textContent = 'No route data.';
        return;
    }
    var step = Math.max(1, Math.floor(allCoords.length / 40));
    var sample = [];
    for (var i = 0; i < allCoords.length; i += step) sample.push(allCoords[i]);
    if (sample[sample.length-1] !== allCoords[allCoords.length-1]) sample.push(allCoords[allCoords.length-1]);

    var lats = sample.map(function(c){ return parseFloat(c[0]).toFixed(5); }).join(',');
    var lngs = sample.map(function(c){ return parseFloat(c[1]).toFixed(5); }).join(',');
    fetch('https://api.open-meteo.com/v1/elevation?latitude=' + lats + '&longitude=' + lngs)
    .then(function(r) { return r.json(); })
    .then(function(d) {
        var el = document.getElementById('pub-elev-loading');
        if (!d || !d.elevation || d.elevation.length < 2) {
            if (el) el.textContent = 'Elevation data unavailable.';
            return;
        }
        if (el) el.style.display = 'none';
        renderPubElevChart(d.elevation, sample);
    })
    .catch(function() {
        var el = document.getElementById('pub-elev-loading');
        if (el) el.textContent = 'Elevation data unavailable.';
    });
})();

function togglePubElev() {
    var body = document.getElementById('pub-elev-body');
    var chev = document.getElementById('pub-elev-chevron');
    if (!body) return;
    var hidden = body.style.display === 'none';
    body.style.display = hidden ? 'block' : 'none';
    if (chev) chev.textContent = hidden ? '▼' : '▶';
}

function renderPubElevChart(elevs, coords) {
    var canvas = document.getElementById('pub-elev-chart');
    if (!canvas) return;
    canvas.width  = canvas.parentElement.offsetWidth || 600;
    canvas.height = 120;
    var ctx = canvas.getContext('2d');
    var W = canvas.width, H = canvas.height;
    var minE = Math.min.apply(null, elevs), maxE = Math.max.apply(null, elevs);
    var range = maxE - minE || 1;
    var pad = {top:12, right:10, bottom:24, left:40};
    var iW = W - pad.left - pad.right, iH = H - pad.top - pad.bottom;

    var grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + iH);
    grad.addColorStop(0, 'rgba(124,58,237,.4)');
    grad.addColorStop(1, 'rgba(124,58,237,.04)');
    ctx.fillStyle = grad;
    ctx.beginPath();
    elevs.forEach(function(e, i) {
        var x = pad.left + (i / (elevs.length-1)) * iW;
        var y = pad.top + iH - ((e - minE) / range) * iH;
        if (i === 0) { ctx.moveTo(x, pad.top + iH); ctx.lineTo(x, y); }
        else ctx.lineTo(x, y);
    });
    ctx.lineTo(pad.left + iW, pad.top + iH);
    ctx.closePath();
    ctx.fill();

    ctx.strokeStyle = '#7c3aed';
    ctx.lineWidth = 2;
    ctx.lineJoin = 'round';
    ctx.beginPath();
    elevs.forEach(function(e, i) {
        var x = pad.left + (i / (elevs.length-1)) * iW;
        var y = pad.top + iH - ((e - minE) / range) * iH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    ctx.fillStyle = '#9aa3ae';
    ctx.font = '9px Arial';
    ctx.textAlign = 'right';
    [0, 0.5, 1].forEach(function(t) {
        var ev = minE + t * range;
        var y = pad.top + iH - t * iH;
        ctx.fillText(Math.round(ev) + 'm', pad.left - 3, y + 3);
        ctx.strokeStyle = 'rgba(0,0,0,.07)';
        ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + iW, y); ctx.stroke();
    });

    var cumDist = [0];
    for (var i = 1; i < coords.length; i++) {
        var dx = (coords[i][1]-coords[i-1][1]) * Math.cos(coords[i][0]*Math.PI/180) * 111320;
        var dy = (coords[i][0]-coords[i-1][0]) * 111320;
        cumDist.push(cumDist[i-1] + Math.sqrt(dx*dx+dy*dy));
    }
    var totalKm = cumDist[cumDist.length-1] / 1000;
    ctx.fillStyle = '#9aa3ae';
    ctx.font = '9px Arial';
    ctx.textAlign = 'center';
    [0, 0.25, 0.5, 0.75, 1].forEach(function(t) {
        ctx.fillText((t*totalKm).toFixed(1)+'km', pad.left + t*iW, H - 6);
    });

    var gain = 0, loss = 0;
    for (var i = 1; i < elevs.length; i++) {
        var d = elevs[i] - elevs[i-1];
        if (d > 0) gain += d; else loss += Math.abs(d);
    }
    var statsEl = document.getElementById('pub-elev-stats');
    if (statsEl) statsEl.innerHTML =
        '<span><strong>📏</strong> ' + totalKm.toFixed(2) + ' km</span>' +
        '<span><strong>⬆</strong> ' + Math.round(gain) + ' m ascent</span>' +
        '<span><strong>⬇</strong> ' + Math.round(loss) + ' m descent</span>' +
        '<span><strong>🏔</strong> ' + Math.round(maxE) + ' m high</span>' +
        '<span><strong>🏞</strong> ' + Math.round(minE) + ' m low</span>';
}
</script>
@endif

@endsection