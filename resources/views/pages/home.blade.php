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