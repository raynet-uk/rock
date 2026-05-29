@extends('layouts.app')
@section('title', 'About')
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
.wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem 3rem;
}

/* TOP BAR */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 2px solid var(--navy);
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}
.brand { display: flex; align-items: center; gap: 0.8rem; }
.brand-badge {
    width: 40px; height: 40px;
    background: var(--navy);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: bold;
    border-radius: 6px;
}
.brand-name { font-size: 1.25rem; font-weight: bold; color: var(--navy); }
.brand-sub { font-size: 0.8rem; color: var(--muted); }
.status-chip {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    background: white;
    border: 1px solid var(--border);
    font-size: 0.85rem;
    color: var(--muted);
}
.online-dot {
    width: 8px; height: 8px;
    background: #2E7D32;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(46,125,50,0.25);
}

/* PAGE HEADER */
.page-header { margin-bottom: 2.5rem; text-align: center; }
.page-header-eyebrow {
    font-size: 0.9rem;
    font-weight: bold;
    color: var(--red);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.8rem;
}
.page-header h1 {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1.15;
    color: var(--navy);
    margin-bottom: 1rem;
}
.page-header h1 span { color: var(--red); }
.page-header p {
    font-size: 1rem;
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

/* STAT STRIP */
.stat-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2.5rem;
}
.stat-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.2rem 1rem;
    text-align: center;
    box-shadow: var(--shadow-sm);
}
.stat-label {
    font-size: 0.85rem;
    font-weight: bold;
    color: var(--muted);
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}
.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 0.3rem;
}
.stat-sub { font-size: 0.9rem; color: var(--muted); }

/* CONTENT GRID */
.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) { .content-grid { grid-template-columns: 1fr 300px; } }

/* INFO CARD */
.info-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}
.info-card:last-child { margin-bottom: 0; }
.card-head {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    background: var(--light);
    border-bottom: 1px solid var(--border);
}
.card-head-icon { font-size: 1.6rem; line-height: 1; }
.card-head-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--navy);
}
.card-head-sub {
    font-size: 0.9rem;
    color: var(--muted);
}
.card-body { padding: 1.2rem; }
.card-body p {
    font-size: 0.95rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}
.card-body p:last-child { margin-bottom: 0; }
.card-body strong { color: var(--navy); }

/* CAPABILITIES LIST */
.cap-list { display: flex; flex-direction: column; gap: 0.8rem; }
.cap-item {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 1rem;
    background: var(--light);
    border-radius: 8px;
    border: 1px solid var(--border);
}
.cap-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.3rem;
}
.cap-item-title {
    font-size: 1.05rem;
    font-weight: bold;
    color: var(--navy);
    margin-bottom: 0.3rem;
}
.cap-item-desc { font-size: 0.9rem; color: var(--text-light); }

/* SIDE COLUMN */
.side-col { display: flex; flex-direction: column; gap: 1.5rem; }

/* LINK CARD */
.link-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.link-list { padding: 0.6rem; }
.link-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
}
.link-item:hover { background: var(--light); }
.link-item-icon { font-size: 1.6rem; line-height: 1; flex-shrink: 0; }
.link-item-text {
    flex: 1;
    font-size: 1rem;
    font-weight: bold;
}
.link-item-sub { font-size: 0.85rem; color: var(--muted); }
.link-item-arrow {
    font-size: 1.2rem;
    color: var(--red);
    font-weight: bold;
}

/* STEPS */
.full-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}
.steps {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
}
@media (min-width: 768px) { .steps { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); } }
.step {
    padding: 1.5rem 1.2rem;
    border-bottom: 1px solid var(--border);
    text-align: center;
}
.step:last-child { border-bottom: none; }
@media (min-width: 768px) {
    .step { border-bottom: none; border-right: 1px solid var(--border); }
    .step:last-child { border-right: none; }
}
.step-num {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--red);
    opacity: 0.2;
    margin-bottom: 0.8rem;
    line-height: 1;
}
.step-title {
    font-size: 1.15rem;
    font-weight: bold;
    color: var(--navy);
    margin-bottom: 0.6rem;
}
.step-desc {
    font-size: 0.95rem;
    color: var(--text-light);
}
</style>

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
            <span>Active Volunteer Group – Ready When Needed</span>
        </div>
    </nav>

<header class="page-header">
        <div class="page-header-eyebrow">// About Us</div>
        <h1>Emergency Communications<br>for <span>{{ \App\Helpers\RaynetSetting::groupRegion() }}</span></h1>
        <p>{{ \App\Helpers\RaynetSetting::groupName() }} is a dedicated team of licensed amateur radio operators providing voluntary, resilient communications support to emergency services, local authorities, and community events across {{ \App\Helpers\RaynetSetting::groupRegion() }}.</p>
    </header>

<div class="stat-strip">
        <div class="stat-card">
            <div class="stat-label">Founded</div>
            <div class="stat-value">1953</div>
            <div class="stat-sub">Over 70 years supporting the community</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Group Ref</div>
            <div class="stat-value">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
            <div class="stat-sub">{{ \App\Helpers\RaynetSetting::groupRegion() }} · {{ \App\Helpers\RaynetSetting::groupRegion() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Members</div>
            <div class="stat-value">15+</div>
            <div class="stat-sub">Licensed &amp; trained operators</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Availability</div>
            <div class="stat-value">24/7</div>
            <div class="stat-sub">Activatable on request</div>
        </div>
    </div>

<div class="content-grid">
        <div>
            <div class="info-card">
                <div class="card-head">
                    <div class="card-head-icon">📻</div>
                    <div>
                        <div class="card-head-title">What is RAYNET?</div>
                        <div class="card-head-sub">Radio Amateurs' Emergency Network – UK national organisation</div>
                    </div>
                </div>
                <div class="card-body">
                    <p>RAYNET-UK is the national voluntary communications service provided by licensed radio amateurs, formed in 1953 after severe East Coast flooding to organise amateur radio resources for emergencies.</p>
                    <p>Registered with Ofcom and working closely with police, ambulance, fire &amp; rescue, local authorities, and voluntary agencies, RAYNET supplies resilient communications when normal systems fail or become overwhelmed.</p>
                    <p>{{ \App\Helpers\RaynetSetting::groupName() }} (Group {{ \App\Helpers\RaynetSetting::groupNumber() }}) is one of many RAYNET groups across the UK, providing resilient volunteer radio communications for {{ \App\Helpers\RaynetSetting::groupRegion() }} and surrounding areas.</p>
                </div>
            </div>

            <div class="info-card">
                <div class="card-head">
                    <div class="card-head-icon">⚡</div>
                    <div>
                        <div class="card-head-title">Our Capabilities</div>
                        <div class="card-head-sub">What we deliver on activation</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="cap-list">
                        <div class="cap-item">
                            <div class="cap-dot" style="background:var(--navy);"></div>
                            <div>
                                <div class="cap-item-title">VHF / UHF Voice Communications</div>
                                <div class="cap-item-desc">Portable and mobile FM radios on 2m and 70cm amateur bands, with repeater access across {{ \App\Helpers\RaynetSetting::groupRegion() }} for reliable local coverage.</div>
                            </div>
                        </div>
                        <div class="cap-item">
                            <div class="cap-dot" style="background:var(--red);"></div>
                            <div>
                                <div class="cap-item-title">HF Long-Range Links</div>
                                <div class="cap-item-desc">High-frequency radio for extended regional coverage when local infrastructure is compromised.</div>
                            </div>
                        </div>
                        <div class="cap-item">
                            <div class="cap-dot" style="background:var(--navy);"></div>
                            <div>
                                <div class="cap-item-title">Digital &amp; Data Modes</div>
                                <div class="cap-item-desc">APRS tracking, Winlink email-over-radio, and message handling independent of internet or mobile networks.</div>
                            </div>
                        </div>
                        <div class="cap-item">
                            <div class="cap-dot" style="background:var(--red);"></div>
                            <div>
                                <div class="cap-item-title">Event Support</div>
                                <div class="cap-item-desc">Dedicated nets for marathons, sportives, charity events, and public gatherings to keep teams connected.</div>
                            </div>
                        </div>
                        <div class="cap-item">
                            <div class="cap-dot" style="background:var(--navy);"></div>
                            <div>
                                <div class="cap-item-title">Self-Sufficient Deployment</div>
                                <div class="cap-item-desc">Operators use personal battery-powered equipment, ready to establish communications quickly at any incident.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-col">
            <div class="link-card">
                <div class="card-head">
                    <div class="card-head-icon">🔗</div>
                    <div>
                        <div class="card-head-title">Useful Links</div>
                    </div>
                </div>
                <div class="link-list">
                    <a href="https://www.raynet-uk.net" target="_blank" rel="noopener" class="link-item">
                        <div class="link-item-icon">🌐</div>
                        <div>
                            <div class="link-item-text">RAYNET-UK</div>
                            <div class="link-item-sub">National organisation</div>
                        </div>
                        <div class="link-item-arrow">↗</div>
                    </a>
                    <a href="https://rsgb.org" target="_blank" rel="noopener" class="link-item">
                        <div class="link-item-icon">📖</div>
                        <div>
                            <div class="link-item-text">RSGB</div>
                            <div class="link-item-sub">Amateur radio licensing body</div>
                        </div>
                        <div class="link-item-arrow">↗</div>
                    </a>
                    <a href="{{ route('request-support')  }}" class="link-item">
                        <div class="link-item-icon">📋</div>
                        <div>
                            <div class="link-item-text">Request Support</div>
                            <div class="link-item-sub">For events or emergencies</div>
                        </div>
                        <div class="link-item-arrow">→</div>
                    </a>
                    <a href="{{ route('training')  }}" class="link-item">
                        <div class="link-item-icon">🎓</div>
                        <div>
                            <div class="link-item-text">Training &amp; Nets</div>
                            <div class="link-item-sub">Exercises and schedule</div>
                        </div>
                        <div class="link-item-arrow">→</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

<div class="full-card">
        <div class="card-head">
            <div class="card-head-icon">🔄</div>
            <div>
                <div class="card-head-title">How an Activation Works</div>
                <div class="card-head-sub">From initial request to stand-down</div>
            </div>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-num">01</div>
                <div class="step-title">Request Received</div>
                <div class="step-desc">Emergency service, authority, or event organiser contacts the Group Controller via phone, email, or liaison.</div>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <div class="step-title">Alert Issued</div>
                <div class="step-desc">Controller evaluates need, confirms resources, alerts operators, and updates site status.</div>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <div class="step-title">Operators Deploy</div>
                <div class="step-desc">Volunteers mobilise with equipment to designated points; net established and checked.</div>
            </div>
            <div class="step">
                <div class="step-num">04</div>
                <div class="step-title">Net Operational</div>
                <div class="step-desc">Net controller handles traffic, messages, and reports between field teams and command.</div>
            </div>
            <div class="step">
                <div class="step-num">05</div>
                <div class="step-title">Stand Down</div>
                <div class="step-desc">Released by requester; operators log activities, debrief, and submit report to RAYNET-UK.</div>
            </div>
        </div>
    </div>
@endsection