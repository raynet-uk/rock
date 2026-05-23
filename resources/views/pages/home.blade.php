@extends('layouts.app')
@section('title', 'Home')
@section('content')



<style>
:root {
    --navy: #003366;
    --red: #C8102E;
    --white: #FFFFFF;
    --light: #F2F2F2;
    --text: #003366;
    --text-light: #1A1A1A;
    --muted: #4A4A4A;
    --border: #D0D0D0;
    --shadow-sm: 0 2px 8px rgba(0,51,102,0.06);
    --shadow-md: 0 4px 16px rgba(0,51,102,0.1);
    --transition: all 0.2s ease;
}
*, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }
html { scroll-behavior: smooth; }
body {
    background: var(--light);
    color: var(--text);
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    font-size: 15px;
    line-height: 1.55;
    min-height: 100vh;
}
.wrap { max-width: 1200px; margin: 0 auto; padding: 0 1rem 3rem; }

/* TOP BAR */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 2px solid var(--navy);
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}
.brand { display: flex; align-items: center; gap: 0.8rem; }
.brand-badge {
    width: 40px; height: 40px;
    background: var(--navy); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: bold; border-radius: 6px;
}
.brand-name { font-size: 1.25rem; font-weight: bold; color: var(--navy); }
.brand-sub { font-size: 0.8rem; color: var(--muted); }
.status-chip {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.4rem 0.9rem; border-radius: 999px;
    background: white; border: 1px solid var(--border);
    font-size: 0.85rem; color: var(--muted);
}
.online-dot {
    width: 8px; height: 8px; background: #2E7D32;
    border-radius: 50%; box-shadow: 0 0 0 2px rgba(46,125,50,0.25);
}

/* HERO */
.hero {
    background: white; border-radius: 12px; overflow: hidden;
    border: 1px solid var(--border); box-shadow: var(--shadow-md); margin-bottom: 2rem;
}
.hero-banner img { width: 100%; height: auto; display: block; }
.hero-body {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .hero-body {
        grid-template-columns: minmax(220px, 260px) 1fr;
        padding: 2rem;
        gap: 2rem;
        align-items: start;
    }
}
.hero-eyebrow {
    font-size: 0.9rem; font-weight: bold; color: var(--red);
    text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.8rem;
}
.hero-title {
    font-size: 2rem; font-weight: bold; line-height: 1.15;
    color: var(--navy); margin-bottom: 1rem;
}
@media (min-width: 768px) { .hero-title { font-size: 2.4rem; } }
.hero-title span { color: var(--red); }
.hero-desc { font-size: 1rem; color: var(--text-light); margin-bottom: 1.5rem; }
.hero-actions { display: flex; flex-wrap: wrap; gap: 1rem; }

/* BUTTONS */
.btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.75rem 1.4rem; border-radius: 999px;
    font-size: 0.95rem; font-weight: bold;
    text-decoration: none; transition: var(--transition);
}
.btn-primary { background: var(--red); color: white; }
.btn-primary:hover { background: #a00d25; transform: translateY(-2px); }
.btn-outline { border: 2px solid var(--navy); color: var(--navy); }
.btn-outline:hover { background: var(--navy); color: white; }

/* SECTION HEAD */
.section-head {
    display: flex; flex-direction: column; gap: 0.5rem; margin: 2rem 0 1.5rem;
}
@media (min-width: 768px) {
    .section-head { flex-direction: row; justify-content: space-between; align-items: flex-end; }
}
.section-head h2 { font-size: 1.6rem; font-weight: bold; color: var(--navy); margin: 0; }
.section-head p { font-size: 1rem; color: var(--muted); margin: 0; }
.section-head a { color: var(--red); font-weight: bold; font-size: 0.95rem; text-decoration: none; }
.section-head a:hover { text-decoration: underline; }

/* EVENTS */
.events-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media (min-width: 768px) { .events-grid { grid-template-columns: 1fr 1fr; } }
.event-card {
    background: white; border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden; box-shadow: var(--shadow-sm);
}
.event-head {
    padding: 1rem 1.2rem; background: var(--light);
    border-bottom: 1px solid var(--border); font-weight: bold; color: var(--navy); font-size: 1rem;
}
.event-next {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.3rem 0.8rem; border-radius: 999px;
    background: rgba(46,125,50,0.1); color: #2E7D32; font-size: 0.8rem; font-weight: bold;
}
.event-body { padding: 1.2rem; }
.event-type {
    display: inline-block; padding: 0.3rem 0.8rem;
    border-radius: 999px; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.8rem;
}
.event-title { font-size: 1.25rem; font-weight: bold; color: var(--navy); margin-bottom: 0.6rem; }
.event-meta { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.8rem; font-size: 0.9rem; color: var(--muted); }
.event-desc { font-size: 0.95rem; color: var(--text-light); line-height: 1.5; }
.event-list-item { display: flex; gap: 1rem; padding: 1rem 1.2rem; border-radius: 8px; transition: var(--transition); }
.event-list-item:hover { background: var(--light); }
.event-date { min-width: 50px; text-align: center; padding: 0.5rem 0.4rem; background: var(--navy); color: white; border-radius: 6px; }
.event-date-day { font-size: 1.3rem; font-weight: bold; line-height: 1; }
.event-date-month { font-size: 0.75rem; text-transform: uppercase; }
.event-list-title { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.3rem; }
.event-list-sub { font-size: 0.85rem; color: var(--muted); }

/* WHAT WE DO */
.what-grid { display: grid; grid-template-columns: 1fr; gap: 1.2rem; margin-bottom: 2rem; }
@media (min-width: 576px) { .what-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 992px) { .what-grid { grid-template-columns: repeat(4, 1fr); } }
.what-card {
    background: white; border: 1px solid var(--border); border-radius: 10px;
    padding: 1.4rem; text-decoration: none; color: inherit;
    display: flex; flex-direction: column; gap: 0.8rem; transition: var(--transition);
}
.what-card:hover { transform: translateY(-4px); border-color: var(--red); box-shadow: var(--shadow-md); }
.what-card-icon { font-size: 2.2rem; line-height: 1; }
.what-card-title { font-size: 1.15rem; font-weight: bold; color: var(--navy); }
.what-card-desc { font-size: 0.95rem; color: var(--text-light); flex: 1; }
.what-card-arrow { font-size: 1.2rem; color: var(--red); font-weight: bold; align-self: flex-start; }

/* CTA STRIP */
.cta-strip {
    background: var(--navy); color: white; border-radius: 12px;
    padding: 1.8rem 1.5rem; display: flex; flex-direction: column; gap: 1.5rem; box-shadow: var(--shadow-md);
}
@media (min-width: 768px) {
    .cta-strip { flex-direction: row; align-items: center; justify-content: space-between; padding: 2rem 2.5rem; }
}
.cta-strip-title { font-size: 1.4rem; font-weight: bold; margin-bottom: 0.5rem; }
.cta-strip-desc { font-size: 1rem; max-width: 500px; }
.cta-strip .btn-primary { background: white; color: var(--navy); padding: 0.8rem 1.6rem; font-size: 1rem; }
.cta-strip .btn-primary:hover { background: var(--light); }

/* RSGB NEWS */
.rsgb-news-card { border-radius: 14px; border: 1px solid #e5e7eb; box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
.rsgb-news-card ul { list-style: none; padding: 0; margin: 0; }
.rsgb-news-card li {
    background: white; border: 1px solid #eee; border-radius: 10px;
    padding: 14px 16px; margin-bottom: 12px; transition: all .2s ease; position: relative;
}
.rsgb-news-card li:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.rsgb-news-card a { text-decoration: none; font-weight: 600; color: inherit; display: block; }
.rsgb-news-card a:hover { text-decoration: underline; }
.rsgb-news-card li::after { content: "→"; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); opacity: .35; }
.rsgb-news-card li:last-child { margin-bottom: 0; }
</style>

<div class="wrap">
    <nav class="topbar">
        <div class="brand">
            <div class="brand-badge">📡</div>
            <div>
                <div class="brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="brand-sub">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
            </div>
        </div>
        <div class="status-chip">
            <div class="online-dot"></div>
            <span>Ready to Support – Volunteer Emergency Comms</span>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-banner">
            <img src="{{ asset('images/raynet-uk-liverpool-banner.png') }}"
                 alt="RAYNET-UK Liverpool – Resilient Communications">
        </div>
        <div class="hero-body">
            <div>
                @include('partials.alert-status-card', ['alertStatus' => $alertStatus ?? null])
            </div>
            <div>
                <div class="hero-eyebrow">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
                <h1 class="hero-title">
                    Reliable emergency comms<br>when it matters <span>for {{ \App\Helpers\RaynetSetting::groupRegion() }}</span>
                </h1>
                <p class="hero-desc">
                    {{ \App\Helpers\RaynetSetting::groupName() }} provides trained volunteers and resilient radio networks to support user services, local authorities,Local Resilience Forums and events when normal systems fail or are overloaded.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('request-support') }}" class="btn btn-primary">Request Support Now →</a>
                    <a href="{{ route('event-support') }}" class="btn btn-outline">Event Support</a>
                </div>
            </div>
        </div>
    </section>

<div class="section-head">
        <div>
            <h2>Upcoming & Recent Activity</h2>
            <p>Snapshot of {{ \App\Helpers\RaynetSetting::groupName() }} deployments and upcoming support.</p>
        </div>
        <a href="{{ route('calendar') }}">Full Calendar →</a>
    </div>

    <div class="events-grid">
        <div class="event-card">
            <div class="event-head">
                <span class="event-next">
                    <span style="width:8px;height:8px;background:#2E7D32;border-radius:50%;display:inline-block;"></span>
                    Next Deployment
                </span>
            </div>
            @if ($nextEvent)
                <div class="event-body">
                    @if ($nextEvent->eventType)
                        <div class="event-type" style="background:{{ $nextEvent->eventType->colour }}20; color:{{ $nextEvent->eventType->colour }}; border:1px solid {{ $nextEvent->eventType->colour }}60;">
                            {{ $nextEvent->eventType->name }}
                        </div>
                    @endif
                    <h3 class="event-title">{{ $nextEvent->title }}</h3>
                    <div class="event-meta">
                        <span>📅 {{ \Carbon\Carbon::parse($nextEvent->starts_at)->format('l j M Y') }}</span>
                        @if ($nextEvent->location)<span>📍 {{ $nextEvent->location }}</span>@endif
                        @if ($nextEvent->starts_at)<span>🕐 {{ \Carbon\Carbon::parse($nextEvent->starts_at)->format('H:i') }}</span>@endif
                    </div>
                    @if ($nextEvent->description)
                        <p class="event-desc">{{ Str::limit($nextEvent->description, 160) }}</p>
                    @endif
                </div>
            @else
                <div class="event-body" style="padding:2rem; text-align:center; color:var(--muted);">
                    No upcoming events.<br>Check back soon.
                </div>
            @endif
        </div>

        <div class="event-card">
            <div class="event-head">Other Upcoming & Recent</div>
            <div style="padding:0.5rem;">
                @if (!empty($upcomingEvents) && $upcomingEvents->count() > 0)
                    @foreach ($upcomingEvents->take(5) as $event)
                        <div class="event-list-item">
                            <div class="event-date">
                                <div class="event-date-day">{{ \Carbon\Carbon::parse($event->starts_at)->format('d') }}</div>
                                <div class="event-date-month">{{ \Carbon\Carbon::parse($event->starts_at)->format('M') }}</div>
                            </div>
                            <div>
                                <div class="event-list-title">{{ $event->title }}</div>
                                <div class="event-list-sub">
                                    @if ($event->location) 📍 {{ $event->location }} @endif
                                    @if ($event->eventType) · {{ $event->eventType->name }} @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="padding:2rem; text-align:center; color:var(--muted);">
                        No further events scheduled.
                    </div>
                @endif
            </div>
        </div>
    </div>


    {{-- Live Net Strapline --}}
    @if(!empty($netData))
    @php
        $bandMeta = \App\Models\NetSchedule::$bands[$netData['band'] ?? ''] ?? null;
        $priorityMeta = \App\Models\NetSchedule::$priorities[$netData['priority'] ?? 'routine'] ?? \App\Models\NetSchedule::$priorities['routine'];
        $isEmergency = ($netData['priority'] ?? 'routine') === 'emergency';
        $isUrgent    = ($netData['priority'] ?? 'routine') === 'urgent';
        // Resolve active controller from time slots
        // If slots defined, only show a controller when one is currently time-active
        $nowTime = \Carbon\Carbon::now('Europe/London')->format('H:i');
        $ctrlSlots = $netData['controller_slots'] ?? [];
        $activeController = count($ctrlSlots) ? '' : ($netData['controller'] ?? '');
        foreach ($ctrlSlots as $slot) {
            if (!empty($slot['callsign']) && !empty($slot['from']) && !empty($slot['to'])) {
                if ($nowTime >= $slot['from'] && $nowTime < $slot['to']) {
                    $activeController = strtoupper($slot['callsign']);
                    break;
                }
            }
        }
    @endphp
    @if(!empty($netData['announcement']))
    <div style="background:{{ $isEmergency ? '#7f1d1d' : ($isUrgent ? '#78350f' : '#0f172a') }};border-bottom:1px solid {{ $priorityMeta['colour'] }};padding:.5rem 1rem;text-align:center;">
        <span style="font-size:.78rem;font-weight:800;color:{{ $priorityMeta['colour'] }};letter-spacing:.05em;text-transform:uppercase;">📢 {{ $netData['announcement'] }}</span>
    </div>
    @endif
    {{-- Original banner starts --}}
    @php
        $netStart    = $netData['start_time'] ?? '';
        $netEnd      = $netData['end_time']   ?? '';
        $netStartTs  = $netData['start_ts']   ?? 0;
        $netEndTs    = $netData['end_ts']      ?? 0;
        // Hide banner if net end timestamp has already passed
        $hideAfterEnd = $netEndTs && now()->timestamp >= $netEndTs;
        $isAuth = auth()->check();
    @endphp
    @if(!$hideAfterEnd)
    <div id="netBanner" style="position:relative;overflow:hidden;background:#0a0a1a;border-top:1px solid #1a1a3e;border-bottom:1px solid #1a1a3e;">
        <div id="netEmergencyOverlay" style="display:none;position:absolute;inset:0;pointer-events:none;z-index:4;border:2px solid transparent;box-shadow:inset 0 0 0 2px transparent;"></div>
        <div id="netEmergencyFlash" style="display:none;"></div>
        <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(200,16,46,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(200,16,46,.04) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;"></div>
        <div style="position:absolute;top:-40px;left:15%;width:300px;height:120px;background:radial-gradient(ellipse,rgba(200,16,46,.25) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:absolute;top:0;left:-100%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(200,16,46,.05),transparent);animation:nScan 4s ease-in-out infinite;pointer-events:none;"></div>
        <canvas id="netWaterfall" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:.55;"></canvas>
        <canvas id="netOscope"    style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:.18;"></canvas>
        <style>
        .net-inner{max-width:1200px;margin:0 auto;padding:.85rem 1rem;display:grid;grid-template-columns:auto 1px 1fr auto;align-items:center;gap:1rem;}
        .net-divider{width:1px;height:32px;background:linear-gradient(to bottom,transparent,rgba(200,16,46,.5),transparent);}
        .net-right{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;justify-content:flex-end;}
        .net-meta{text-align:center;}
        .net-meta-label{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);margin-bottom:.1rem;}
        .net-meta-value{font-size:.85rem;font-weight:800;color:rgba(255,255,255,.85);font-family:monospace;}
        .net-vdiv{width:1px;height:24px;background:rgba(255,255,255,.1);}
        .net-join{background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;font-size:.75rem;font-weight:800;padding:.4rem .85rem;border-radius:999px;text-decoration:none;letter-spacing:.05em;border:1px solid rgba(200,16,46,.4);white-space:nowrap;}
        @media(max-width:640px){
            .net-inner{grid-template-columns:1fr;gap:.6rem;}
            .net-divider{display:none;}
            .net-right{justify-content:flex-start;gap:.75rem;}
            .net-vdiv{display:none;}
        }
        </style>
        <div class="net-inner">
            {{-- Badge --}}
            <div style="display:flex;align-items:center;gap:.5rem;">
                <div style="position:relative;width:11px;height:11px;flex-shrink:0;">
                    <span style="position:absolute;inset:0;background:#C8102E;border-radius:50%;animation:nPing 1.5s ease-in-out infinite;opacity:.6;"></span>
                    <span style="position:absolute;inset:1px;background:#ff1a3a;border-radius:50%;"></span>
                </div>
                <span id="netStatusLabel" style="font-size:.62rem;font-weight:900;text-transform:uppercase;letter-spacing:.2em;color:#ff4466;white-space:nowrap;">Live Net</span>
            </div>
            {{-- Divider --}}
            <div class="net-divider"></div>
            {{-- Callsign + info --}}
            <div style="min-width:0;">
                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                    <span style="font-size:1.05rem;font-weight:900;color:#fff;font-family:monospace;">{{ strtoupper($netData['callsign']) }}</span>
                    @auth
                    @if(!empty($bandMeta))
                    <span style="font-size:.72rem;font-weight:900;color:{{ $bandMeta['colour'] }};background:{{ $bandMeta['bg'] }};border:1px solid {{ $bandMeta['border'] }};padding:.15rem .5rem;border-radius:4px;font-family:monospace;letter-spacing:.05em;">{{ $bandMeta['label'] }}</span>
                    @endif
                    @if(!empty($netData['frequency']))
                    <span style="font-size:.88rem;font-weight:700;color:#C8102E;font-family:monospace;background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);padding:.1rem .45rem;border-radius:4px;">{{ $netData['frequency'] }}</span>
                    @endif
                    @endauth
                </div>
                @if(!empty($netData['description']))
                <div style="font-size:.79rem;color:rgba(255,255,255,.5);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $netData['description'] }}</div>
                @endif
                <div id="netTimerDisplay" style="margin-top:.25rem;display:none;">
                    <span id="netTimerBadge" style="font-size:.7rem;font-weight:800;padding:.15rem .55rem;border-radius:999px;font-family:monospace;"></span>
                </div>
            </div>
            {{-- Right side --}}
            <div class="net-right">
                @if($netStart)
                <div class="net-meta">
                    <div class="net-meta-label">Net Time</div>
                    <div class="net-meta-value">{{ \Carbon\Carbon::createFromTimeString($netStart)->format('H:i') }}@if($netEnd)&ndash;{{ \Carbon\Carbon::createFromTimeString($netEnd)->format('H:i') }}@endif</div>
                </div>
                <div class="net-vdiv"></div>
                @endif
                <div class="net-meta" id="netCtrlWrap" style="display:none;overflow:visible;position:relative;padding:2px 6px;">
                    <div id="ctrlRing" style="display:none;position:absolute;bottom:0;left:4px;right:4px;height:2px;border-radius:2px;background:rgba(255,255,255,.08);overflow:hidden;">
                        <div id="ctrlRingArc" style="height:100%;width:100%;background:#22c55e;transform-origin:left;transition:background .6s;border-radius:2px;"></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <img id="netCtrlAvatar" src="" alt="" style="display:none;width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid rgba(255,255,255,.2);opacity:0;transition:opacity .5s;flex-shrink:0;">
                        <div style="flex:1;min-width:0;">
                    <div class="net-meta-label">Controller</div>
                    <div style="position:relative;min-height:1.1em;">
                        <div class="net-meta-value" id="netCtrlDisplay" style="position:relative;z-index:1;"></div>
                        <div id="netCtrlGhost" style="position:absolute;top:0;left:0;right:0;font-size:.85rem;font-weight:800;font-family:monospace;color:rgba(255,255,255,.85);pointer-events:none;opacity:0;text-align:center;white-space:nowrap;"></div>
                    </div>
                        </div>
                    </div>
                    <div id="netCtrlName" style="font-size:.6rem;color:rgba(255,255,255,.4);font-weight:600;letter-spacing:.03em;margin-top:.12rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;"></div>
                </div>
                <div class="net-vdiv" id="netCtrlDivider" style="display:none;"></div>
                @if(!empty($activeController))
                <script>
                (function(){
                    var w = document.getElementById('netCtrlWrap');
                    var d = document.getElementById('netCtrlDivider');
                    var el = document.getElementById('netCtrlDisplay');
                    if (w && el) {
                        el.textContent = '{{ strtoupper($activeController) }}';
                        w.style.display = '';
                        if (d) d.style.display = '';
                    }
                })();
                </script>
                @endif
                <div id="netCheckinWrap" style="display:none;flex-direction:column;align-items:center;gap:.1rem;overflow:hidden;transition:max-width .5s cubic-bezier(.22,1,.36,1),opacity .4s ease;max-width:0;opacity:0;">
                    <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);">On Net</div>
                    <div id="netCheckinCount" style="font-size:1.1rem;font-weight:900;color:rgba(255,255,255,.9);font-family:monospace;line-height:1;">0</div>
                    <div style="font-size:.58rem;color:rgba(255,255,255,.3);font-weight:600;letter-spacing:.05em;">stations</div>
                </div>
            </div>
        </div>
        @verbatim
        <style>
        @keyframes nPing{0%,100%{transform:scale(1);opacity:.6;}50%{transform:scale(2.2);opacity:0;}}
        @keyframes nScan{0%{left:-50%;}100%{left:150%;}}
        @keyframes ctrlRedPulse{0%,100%{color:#ff4444;opacity:1;}50%{color:#ff0000;opacity:.4;}}
        @keyframes ctrlGreenBlink{0%,100%{color:#22c55e;opacity:1;}50%{opacity:.15;}}
        @keyframes ctrlSlideIn{0%{max-width:0;opacity:0;transform:translateX(18px);}100%{max-width:200px;opacity:1;transform:translateX(0);}}
        @keyframes badgeFadeOut{0%{opacity:1;transform:translateY(0) scale(1);}100%{opacity:0;transform:translateY(-8px) scale(.92);}}
        @keyframes badgeFadeIn{0%{opacity:0;transform:translateY(8px) scale(.92);}100%{opacity:1;transform:translateY(0) scale(1);}}
        @keyframes lblFlash{0%,100%{opacity:1;}40%{opacity:0;}}
        @keyframes ghostDrift{0%{opacity:1;transform:translateY(0) scale(1);}100%{opacity:0;transform:translateY(-20px) scale(.9);}}
        @keyframes emergencyHeartbeat{0%,100%{opacity:1;box-shadow:0 0 0 1px rgba(200,16,46,.5),0 -4px 32px rgba(200,16,46,.55),0 4px 32px rgba(200,16,46,.55);}14%{opacity:.85;box-shadow:0 0 0 1px rgba(200,16,46,.3),0 -2px 16px rgba(200,16,46,.3),0 2px 16px rgba(200,16,46,.3);}28%{opacity:1;box-shadow:0 0 0 1px rgba(200,16,46,.5),0 -4px 32px rgba(200,16,46,.55),0 4px 32px rgba(200,16,46,.55);}42%{opacity:.9;box-shadow:0 0 0 1px rgba(200,16,46,.35),0 -3px 20px rgba(200,16,46,.4),0 3px 20px rgba(200,16,46,.4);}56%{opacity:1;box-shadow:0 0 0 1px rgba(200,16,46,.5),0 -4px 32px rgba(200,16,46,.55),0 4px 32px rgba(200,16,46,.55);}}
        @keyframes urgentPulse{0%,100%{opacity:1;box-shadow:0 0 0 1px rgba(245,158,11,.4),0 -4px 28px rgba(245,158,11,.4),0 4px 28px rgba(245,158,11,.4);}50%{opacity:.9;box-shadow:0 0 0 1px rgba(245,158,11,.2),0 -2px 14px rgba(245,158,11,.2),0 2px 14px rgba(245,158,11,.2);}}
        @keyframes flashFade{0%{opacity:1;}60%{opacity:.7;}100%{opacity:0;}}
        </style>
        @endverbatim
    </div>
    <script>
    (function(){
        // Unix timestamps injected from PHP (seconds) — no string parsing, no timezone bugs
        var startTs = {{ $netData['start_ts'] ?? 0 }};
        var endTs   = {{ $netData['end_ts']   ?? 0 }};

        function fmt(sec){
            sec = Math.abs(Math.floor(sec));
            var h=Math.floor(sec/3600),m=Math.floor((sec%3600)/60),s=sec%60;
            return h>0
                ? h+'h '+('0'+m).slice(-2)+'m '+('0'+s).slice(-2)+'s'
                : ('0'+m).slice(-2)+'m '+('0'+s).slice(-2)+'s';
        }

        var _netState = 'unknown'; // track state to detect transitions

        function animateBadgeTransition(badge, lbl, newText, newBadgeCss, newLblText, newLblColor) {
            // Phase 1: fade current badge out upward
            badge.style.animation = 'none';
            void badge.offsetWidth;
            badge.style.animation = 'badgeFadeOut 0.35s ease forwards';
            if (lbl) {
                lbl.style.animation = 'none';
                void lbl.offsetWidth;
                lbl.style.animation = 'lblFlash 0.35s ease forwards';
            }
            setTimeout(function() {
                // Phase 2: swap content and fade in downward
                badge.style.cssText = newBadgeCss;
                badge.textContent   = newText;
                badge.style.animation = 'badgeFadeIn 0.4s cubic-bezier(.22,1,.36,1) forwards';
                if (lbl) {
                    lbl.textContent   = newLblText;
                    lbl.style.color   = newLblColor;
                    lbl.style.animation = 'badgeFadeIn 0.4s cubic-bezier(.22,1,.36,1) forwards';
                }
            }, 370);
        }

        function tick(){
            var now     = Math.floor(Date.now()/1000);
            var banner  = document.getElementById('netBanner');
            var disp    = document.getElementById('netTimerDisplay');
            var badge   = document.getElementById('netTimerBadge');
            var lbl     = document.getElementById('netStatusLabel');
            if (!banner) return;

            // Hide banner once net has ended
            if (endTs && now >= endTs) {
                banner.style.animation = 'none';
                void banner.offsetWidth;
                banner.style.animation = 'badgeFadeOut 0.5s ease forwards';
                setTimeout(function(){ banner.style.display = 'none'; }, 520);
                return;
            }

            var secsToStart = startTs - now;
            var secsOnAir   = now - startTs;

            if (secsToStart <= 0) {
                // ── NET IS LIVE ──
                disp.style.display = 'block';
                if (_netState !== 'live') {
                    // Transition animation — was Starting Soon, now Live
                    animateBadgeTransition(
                        badge, lbl,
                        '⏱ On Air ' + (startTs > 0 ? fmt(secsOnAir) : '—'),
                        'font-size:.72rem;font-weight:800;padding:.2rem .6rem;border-radius:999px;font-family:monospace;background:rgba(200,16,46,.2);border:1px solid rgba(200,16,46,.5);color:#ff6688;',
                        'Live Now', '#ff4466'
                    );
                    _netState = 'live';
                } else {
                    // Already live — just update the counter text quietly
                    badge.textContent = '⏱ On Air ' + (startTs > 0 ? fmt(secsOnAir) : '—');
                }

            } else if (secsToStart <= 90*60) {
                // ── STARTING SOON ──
                disp.style.display = 'block';
                if (_netState !== 'soon') {
                    animateBadgeTransition(
                        badge, lbl,
                        '⏳ Starting in ' + fmt(secsToStart),
                        'font-size:.72rem;font-weight:800;padding:.2rem .6rem;border-radius:999px;font-family:monospace;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:#fbbf24;',
                        'Starting Soon', '#fbbf24'
                    );
                    _netState = 'soon';
                } else {
                    badge.textContent = '⏳ Starting in ' + fmt(secsToStart);
                }

            } else {
                disp.style.display = 'none';
                _netState = 'hidden';
            }
        }

        tick();
        setInterval(tick, 1000);

        // ── Oscilloscope ──
        (function(){
            var canvas = document.getElementById('netOscope');
            if (!canvas) return;
            var ctx    = canvas.getContext('2d');
            var freq   = 1.4;   // base wave frequency
            var targetFreq = 1.4;
            var amp    = 0.32;  // amplitude as fraction of height
            var targetAmp  = 0.32;
            var flatline   = false;
            var phase  = 0;
            var raf;

            function resize() {
                canvas.width  = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }
            resize();
            window.addEventListener('resize', resize);

            function draw() {
                freq += (targetFreq - freq) * 0.04;
                amp  += (targetAmp  - amp)  * 0.04;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.beginPath();
                ctx.strokeStyle = '#C8102E';
                ctx.lineWidth   = 1.2;
                var h  = canvas.height;
                var w  = canvas.width;
                var cy = h / 2;
                for (var x = 0; x <= w; x++) {
                    var t = (x / w) * Math.PI * 2 * freq + phase;
                    var y = flatline
                        ? cy
                        : cy + Math.sin(t) * (h * amp)
                            + Math.sin(t * 2.3 + 1) * (h * amp * 0.18)
                            + Math.sin(t * 0.7 + 2) * (h * amp * 0.09);
                    x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                }
                ctx.stroke();
                phase += flatline ? 0 : 0.018;
                raf = requestAnimationFrame(draw);
            }
            draw();

            // Expose controls globally
            window.oscopeHandover = function() {
                targetFreq = 4.5; targetAmp = 0.55;
                setTimeout(function(){ targetFreq = 1.4; targetAmp = 0.32; }, 2000);
            };
            window.oscopeFlatline = function() {
                flatline = true;
                setTimeout(function(){ flatline = false; }, 1800);
            };
        })();

        // ── Controller live-polling + precision slot scheduler ──
        var _lastCtrl     = ((document.getElementById('netCtrlDisplay') || {}).textContent || '').trim();
        var _slotTimers   = [];
        var _ringInterval = null;
        var _lastPriority = null;

        // ── Ghost: old callsign drifts up and fades on handover ──
        function ghostOldCtrl() {
            var ghost = document.getElementById('netCtrlGhost');
            var disp  = document.getElementById('netCtrlDisplay');
            if (!ghost || !disp || !disp.textContent.trim()) return;
            ghost.textContent = disp.textContent;
            ghost.style.cssText = 'position:absolute;top:0;left:0;right:0;font-size:.85rem;font-weight:800;font-family:monospace;color:rgba(255,255,255,.85);pointer-events:none;text-align:center;white-space:nowrap;opacity:1;animation:ghostDrift .85s ease forwards;z-index:2;';
            setTimeout(function(){ ghost.style.opacity='0'; ghost.textContent=''; }, 900);
        }

        // ── Ring: SVG countdown arc, shows last 60s of a slot ──
        function stopRing() {
            if (_ringInterval) { clearInterval(_ringInterval); _ringInterval = null; }
            var ring = document.getElementById('ctrlRing');
            if (ring) ring.style.display = 'none';
        }

        function startRing(toTimeStr) {
            stopRing();
            if (!toTimeStr) return;
            var ring = document.getElementById('ctrlRing');
            var arc  = document.getElementById('ctrlRingArc');
            if (!ring || !arc) return;
            _ringInterval = setInterval(function() {
                var now     = new Date();
                var nowSecs = now.getHours()*3600 + now.getMinutes()*60 + now.getSeconds();
                var p       = toTimeStr.split(':');
                var toSecs  = parseInt(p[0])*3600 + parseInt(p[1])*60;
                var rem     = toSecs - nowSecs;
                if (rem <= 0) { stopRing(); return; }
                if (rem <= 60) {
                    ring.style.display = '';
                    // Width drains from 100% to 0% over 60 seconds
                    arc.style.width  = (rem / 60 * 100) + '%';
                    arc.style.background = rem <= 10 ? '#ff4444' : rem <= 30 ? '#fbbf24' : '#22c55e';
                } else {
                    ring.style.display = 'none';
                }
            }, 1000);
        }

        // ── Name: fade in controller's name + title below callsign ──
        function showCtrlName(info) {
            var el = document.getElementById('netCtrlName');
            if (!el) return;
            if (info && info.name) {
                el.textContent = info.name + (info.title ? ' \u00b7 ' + info.title : '');
                el.style.animation = 'none';
                void el.offsetWidth;
                el.style.animation = 'badgeFadeIn .5s ease forwards';
            } else {
                el.textContent = '';
            }
        }

        // ── Priority: emergency heartbeat / urgent pulse / full-screen flash ──
        function applyPriority(priority) {
            if (priority === _lastPriority) return;
            var prev    = _lastPriority;
            _lastPriority = priority;
            var banner  = document.getElementById('netBanner');
            var overlay = document.getElementById('netEmergencyOverlay');
            var flash   = document.getElementById('netEmergencyFlash');
            if (banner)  { banner.style.animation = ''; banner.style.boxShadow = ''; }
            if (overlay) { overlay.style.animation = ''; overlay.style.display = 'none'; }
            if (window.waterfallSetActive) window.waterfallSetActive(true, '', priority);
            if (priority === 'emergency') {
                if (banner) {
                    banner.style.animation  = 'emergencyHeartbeat 1.8s ease-in-out infinite';
                    banner.style.transition = 'box-shadow .6s ease';
                    banner.style.boxShadow  = '0 0 0 1px rgba(200,16,46,.5), 0 -4px 32px rgba(200,16,46,.55), 0 4px 32px rgba(200,16,46,.55)';
                }
                if (overlay) { overlay.style.display = 'none'; }
            } else if (priority === 'urgent') {
                if (banner) {
                    banner.style.animation  = 'urgentPulse 2.8s ease-in-out infinite';
                    banner.style.transition = 'box-shadow .6s ease';
                    banner.style.boxShadow  = '0 0 0 1px rgba(245,158,11,.4), 0 -4px 28px rgba(245,158,11,.4), 0 4px 28px rgba(245,158,11,.4)';
                }
                if (overlay) { overlay.style.display = 'none'; }
            }
        }

        // ── Slide controller in (first appearance or handover) ──
        function showCtrlAvatar(info) {
            var img = document.getElementById('netCtrlAvatar');
            if (!img) return;
            if (info && info.photo) {
                img.style.display = '';
                img.style.opacity = '0';
                img.onload = function() {
                    img.style.opacity = '1';
                };
                img.onerror = function() {
                    img.style.display = 'none';
                };
                img.src = info.photo;
            } else {
                img.style.display = 'none';
                img.src = '';
            }
        }

        function ctrlSlideIn(callsign, slotTo, info) {
            var el      = document.getElementById('netCtrlDisplay');
            var wrap    = document.getElementById('netCtrlWrap');
            var divider = document.getElementById('netCtrlDivider');
            if (!el) return;
            if (_lastCtrl && _lastCtrl !== callsign) {
                if (window.oscopeHandover) window.oscopeHandover();
                if (window.waterfallHandover) window.waterfallHandover();
                ghostOldCtrl();
                stopRing();
                setTimeout(function() {
                    el.style.animation = 'none';
                    el.textContent = callsign;
                    el.style.color = '#22c55e';
                    void el.offsetWidth;
                    el.style.animation = 'ctrlGreenBlink 300ms ease-in-out 5';
                    setTimeout(function(){ el.style.animation='none'; el.style.color=''; }, 1600);
                    showCtrlName(info);
                    showCtrlAvatar(info);
                    startRing(slotTo);
                }, 460);
            } else if (!_lastCtrl) {
                el.textContent = callsign;
                showCtrlName(info);
                showCtrlAvatar(info);
                if (wrap) { wrap.style.display=''; wrap.style.animation='none'; void wrap.offsetWidth; wrap.style.animation='ctrlSlideIn .5s cubic-bezier(.22,1,.36,1) forwards'; }
                if (divider) divider.style.display = '';
                el.style.color = '#22c55e';
                setTimeout(function(){ el.style.color=''; }, 1800);
                startRing(slotTo);
            }
            _lastCtrl = callsign;
        }

        // ── Slide controller out (last slot ended) ──
        function ctrlSlideOut() {
            var el      = document.getElementById('netCtrlDisplay');
            var wrap    = document.getElementById('netCtrlWrap');
            var divider = document.getElementById('netCtrlDivider');
            var nameEl  = document.getElementById('netCtrlName');
            if (!wrap || wrap.style.display === 'none') return;
            if (window.oscopeFlatline) window.oscopeFlatline();
            if (window.waterfallFlatline) window.waterfallFlatline();
            stopRing();
            ghostOldCtrl();
            setTimeout(function() {
                if (wrap) { wrap.style.animation='none'; void wrap.offsetWidth; wrap.style.animation='ctrlSlideIn .4s cubic-bezier(.22,1,.36,1) reverse forwards'; }
                if (divider) divider.style.display = 'none';
                setTimeout(function(){
                    if (wrap)   wrap.style.display   = 'none';
                    if (el)     el.textContent        = '';
                    if (nameEl) nameEl.textContent    = '';
                    _lastCtrl = '';
                }, 450);
            }, 420);
        }

        // ── Warning text (upcoming / handover) ──
        function ctrlShowWarning(text, color) {
            var el   = document.getElementById('netCtrlDisplay');
            var wrap = document.getElementById('netCtrlWrap');
            var div  = document.getElementById('netCtrlDivider');
            if (!el) return;
            if (!wrap || wrap.style.display === 'none') {
                el.textContent = text; el.style.color = color;
                if (wrap) { wrap.style.display=''; wrap.style.animation='none'; void wrap.offsetWidth; wrap.style.animation='ctrlSlideIn .5s cubic-bezier(.22,1,.36,1) forwards'; }
                if (div) div.style.display = '';
            } else {
                el.style.animation = 'none'; void el.offsetWidth;
                el.style.color = color; el.textContent = text;
                el.style.animation = 'ctrlGreenBlink 500ms ease-in-out 6';
            }
        }

        // ── Schedule all slot boundary timers precisely ──
        function animateCtrlChange(el, newVal) {
            el.style.animation = 'none';
            el.style.color = '#ff4444';
            void el.offsetWidth;
            el.style.animation = 'ctrlRedPulse 350ms ease-in-out 3';
            setTimeout(function () {
                el.style.animation = 'none';
                el.textContent = newVal;
                el.style.color = '#22c55e';
                void el.offsetWidth;
                el.style.animation = 'ctrlGreenBlink 300ms ease-in-out 5';
                setTimeout(function () {
                    el.style.animation = 'none';
                    el.style.color = '';
                }, 1600);
            }, 1100);
        }

        function scheduleSlots(slots) {
            _slotTimers.forEach(function(t){ clearTimeout(t); });
            _slotTimers = [];
            if (!slots || !slots.length) return;
            var now     = new Date();
            var nowSecs = now.getHours()*3600 + now.getMinutes()*60 + now.getSeconds();
            var startsAt   = {};
            var handoverAt = {};
            slots.forEach(function(s) { if (s.from && s.callsign) startsAt[s.from] = s; });
            slots.forEach(function(s) { if (s.to && startsAt[s.to]) handoverAt[s.to] = true; });

            slots.forEach(function(slot) {
                if (!slot.callsign || !slot.from || !slot.to) return;
                var fp    = slot.from.split(':'), tp = slot.to.split(':');
                var fromS = parseInt(fp[0])*3600 + parseInt(fp[1])*60;
                var toS   = parseInt(tp[0])*3600  + parseInt(tp[1])*60;
                var cs    = slot.callsign.toUpperCase();
                var nextSlot = startsAt[slot.to] || null;
                var nextCs   = nextSlot ? nextSlot.callsign.toUpperCase() : null;
                var info  = slot.info || null;
                var isHandoverTarget = !!handoverAt[slot.from];

                // 10s "starting soon" warning (skip if a handover covers this moment)
                var dWarn = fromS - 10 - nowSecs;
                if (dWarn > 0 && !isHandoverTarget) {
                    (function(c){ _slotTimers.push(setTimeout(function(){ ctrlShowWarning('\u23f3 ' + c + ' starting soon', '#fbbf24'); }, dWarn*1000)); })(cs);
                }

                // Slide in at start
                var dFrom = fromS - nowSecs;
                if (dFrom > 0) {
                    (function(c, to, inf){ _slotTimers.push(setTimeout(function(){ ctrlSlideIn(c, to, inf); }, dFrom*1000)); })(cs, slot.to, info);
                } else if (dFrom > -300 && dFrom <= 0) {
                    // Currently active — start ring for remaining time
                    startRing(slot.to);
                }

                // 10s handover warning (A → B)
                if (nextCs) {
                    var dHover = toS - 10 - nowSecs;
                    if (dHover > 0) {
                        (function(cur, nxt){ _slotTimers.push(setTimeout(function(){
                            var el = document.getElementById('netCtrlDisplay');
                            if (!el) return;
                            el.style.animation='none'; void el.offsetWidth;
                            el.style.color='#fbbf24'; el.textContent = cur + ' \u2192 ' + nxt;
                            el.style.animation='ctrlGreenBlink 600ms ease-in-out 8';
                        }, dHover*1000)); })(cs, nextCs);
                    } else if (dHover <= 0 && (toS - nowSecs) > 0) {
                        // Already inside warning window
                        var el = document.getElementById('netCtrlDisplay');
                        if (el && el.textContent === cs) {
                            el.style.color='#fbbf24'; el.textContent=cs+' \u2192 '+nextCs;
                            el.style.animation='ctrlGreenBlink 600ms ease-in-out 8';
                        }
                    }
                }

                // Slide out at end (only if no next slot)
                var dTo = toS - nowSecs;
                if (dTo > 0 && !nextCs) {
                    _slotTimers.push(setTimeout(function(){ ctrlSlideOut(); }, dTo*1000));
                }
            });
        }

        function pollCtrl() {
            fetch('/net-status-json', { cache: 'no-store' })
                .then(function(r){ return r.ok ? r.json() : null; })
                .then(function(d) {
                    if (!d) return;
                    if (!d.active) {
                        var banner = document.getElementById('netBanner');
                        if (banner) banner.style.display = 'none';
                        if (window.waterfallFlatline) window.waterfallFlatline();
                        return;
                    }
                    applyPriority(d.priority || 'routine');
                    if (window.waterfallSetActive) window.waterfallSetActive(true, d.frequency || '', d.priority || 'routine');
                    var fresh = (d.controller || '').trim().toUpperCase();
                    if (fresh && fresh !== _lastCtrl) {
                        var slotTo = null, info = d.controller_info || null;
                        (d.slots || []).forEach(function(s) {
                            if (s.callsign && s.callsign.toUpperCase() === fresh) {
                                slotTo = s.to;
                                if (s.info) info = s.info;
                            }
                        });
                        ctrlSlideIn(fresh, slotTo, info);
                    }
                    // Update check-in counter
                    var wrap    = document.getElementById('netCheckinWrap');
                    var countEl = document.getElementById('netCheckinCount');
                    var logging = !!d.station_logging;

                    if (wrap) {
                        var wasVisible = wrap.dataset.visible === '1';
                        if (logging && !wasVisible) {
                            wrap.dataset.visible = '1';
                            wrap.style.display   = 'flex';
                            wrap.style.maxWidth  = '0';
                            wrap.style.opacity   = '0';
                            void wrap.offsetWidth;
                            wrap.style.maxWidth  = '120px';
                            wrap.style.opacity   = '1';
                        } else if (!logging && wasVisible) {
                            wrap.dataset.visible = '0';
                            wrap.style.maxWidth  = '0';
                            wrap.style.opacity   = '0';
                            if (countEl) { countEl.textContent = '0'; countEl.dataset.prev = '0'; }
                            setTimeout(function(){ wrap.style.display = 'none'; }, 520);
                        }
                    }

                    if (countEl && logging && typeof d.checkins !== 'undefined') {
                        var newCount = parseInt(d.checkins) || 0;
                        var oldCount = countEl.dataset.prev !== undefined ? parseInt(countEl.dataset.prev) : parseInt(countEl.textContent) || -1;
                        if (newCount !== oldCount || countEl.dataset.initialized !== '1') {
                            countEl.dataset.initialized = '1';
                            countEl.dataset.prev = newCount;
                            countEl.textContent  = newCount;
                            countEl.style.transition = 'color .3s, transform .15s';
                            if (newCount > oldCount) {
                                // Added — bounce up + green flash
                                countEl.style.transform = 'scale(1.5)';
                                countEl.style.color = '#22c55e';
                                setTimeout(function(){
                                    countEl.style.transform = 'scale(1)';
                                }, 200);
                                setTimeout(function(){
                                    countEl.style.color = 'rgba(255,255,255,.9)';
                                }, 1000);
                            } else {
                                // Removed — shrink + red flash
                                countEl.style.transform = 'scale(0.7)';
                                countEl.style.color = '#ff4444';
                                setTimeout(function(){
                                    countEl.style.transform = 'scale(1)';
                                }, 200);
                                setTimeout(function(){
                                    countEl.style.color = 'rgba(255,255,255,.9)';
                                }, 1000);
                            }
                        }
                    }
                    scheduleSlots(d.slots || []);
                })
                .catch(function(){});
        }

        pollCtrl();
        setInterval(pollCtrl, 10000);
    })();
    </script>
    @endif
    @endif

    <div class="section-head">
        <div>
            <h2>What We Do</h2>
            <p>Supporting {{ \App\Helpers\RaynetSetting::groupRegion() }} resilience with volunteer radio communications.</p>
        </div>
        <a href="{{ route('about') }}">About RAYNET-UK →</a>
    </div>

    <div class="what-grid">
        <a href="{{ route('about') }}" class="what-card">
            <div class="what-card-icon">🚑</div>
            <h3 class="what-card-title">Blue-Light Support</h3>
            <p class="what-card-desc">Extra resilient radio for emergency services & authorities during high-demand or failures.</p>
            <div class="what-card-arrow">→</div>
        </a>
        <a href="{{ route('event-support') }}" class="what-card">
            <div class="what-card-icon">🎪</div>
            <h3 class="what-card-title">Public & Major Events</h3>
            <p class="what-card-desc">Dedicated nets for marshals, organisers, medical & welfare teams.</p>
            <div class="what-card-arrow">→</div>
        </a>
        <a href="{{ route('training') }}" class="what-card">
            <div class="what-card-icon">📻</div>
            <h3 class="what-card-title">Training & Preparedness</h3>
            <p class="what-card-desc">Regular exercises in ops, JESIP, mapping, messages, power resilience.</p>
            <div class="what-card-arrow">→</div>
        </a>
        <a href="{{ route('about') }}" class="what-card">
            <div class="what-card-icon">🤝</div>
            <h3 class="what-card-title">Multi-Agency Partnership</h3>
            <p class="what-card-desc">Collaboration with resilience forums, emergency services & voluntary sector.</p>
            <div class="what-card-arrow">→</div>
        </a>
    </div>

    <div class="section-head">
        <div>
            <h2>Latest RSGB News</h2>
            <p>Headlines from the Radio Society of Great Britain – band plans, contests, Ofcom updates & more.</p>
        </div>
        <a href="https://rsgb.org/main/news/" target="_blank" rel="noopener noreferrer">Full RSGB News →</a>
    </div>

    <div class="event-card rsgb-news-card" style="margin-bottom:2rem;">
        <div class="event-body">
            <x-dashboard.rsgb-news-widget />
        </div>
    </div>

    @if(isset($featuredPhotos) && $featuredPhotos->isNotEmpty())
    <div class="section-head">
        <div>
            <h2>Featured Photos</h2>
            <p>Snapshots from our operations and events.</p>
        </div>
        <a href="{{ route('gallery') }}">Full Gallery →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-bottom:2rem;">
        @foreach($featuredPhotos as $photo)
        <a href="{{ route('gallery') }}" style="display:block;border-radius:8px;overflow:hidden;aspect-ratio:4/3;background:#f0f0f0;border:1px solid var(--border);">
            <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->caption }}" style="width:100%;height:100%;object-fit:cover;transition:transform .3s;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'" loading="lazy">
        </a>
        @endforeach
    </div>
    @endif

    <div class="cta-strip">
        <div>
            <div class="cta-strip-title">Planning an Event in {{ \App\Helpers\RaynetSetting::groupRegion() }}?</div>
            <div class="cta-strip-desc">Free volunteer radio support from {{ \App\Helpers\RaynetSetting::groupName() }}.<br> Please try contact us 4 weeks ahead of your event.</div>
        </div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('request-support') }}" class="btn btn-primary">Submit Request →</a>
            <a href="{{ route('event-support') }}" class="btn btn-outline" style="border-color:white; color:white;">More Details</a>
        </div>
    </div>
</div>

@endsection