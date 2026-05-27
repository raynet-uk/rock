@extends('layouts.app')
@section('title', 'Live Propagation Brief')
@section('content')

@php
    $alertStatus  = \App\Models\AlertStatus::query()->first();
    $alertMeta    = $alertStatus?->meta();
    $currentLevel = $alertStatus->level ?? 5;
    $currentColour = $alertMeta['colour'] ?? '#22c55e';
@endphp

<style>
/* ─── ROOT ──────────────────────────────────────────────────── */
:root {
    --navy:      #003366;
    --navy-d:    #001f40;
    --red:       #C8102E;
    --white:     #ffffff;
    --bg:        #f0f3f8;
    --surface:   #ffffff;
    --border:    #d6dce8;
    --text:      #001f40;
    --muted:     #6b7f96;

    --cond-poor:      #ef4444;
    --cond-poor-bg:   rgba(239,68,68,.1);
    --cond-fair:      #f59e0b;
    --cond-fair-bg:   rgba(245,158,11,.1);
    --cond-good:      #22c55e;
    --cond-good-bg:   rgba(34,197,94,.1);
    --cond-excel:     #06b6d4;
    --cond-excel-bg:  rgba(6,182,212,.1);
    --cond-closed:    #64748b;
    --cond-closed-bg: rgba(100,116,139,.1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color: var(--text); font-size: 14px; -webkit-font-smoothing: antialiased; }

/* ─── WRAP ───────────────────────────────────────────────────── */
.wrap { max-width: 1340px; margin: 0 auto; padding: 0 0 60px; }

/* ─── PAGE HEADER ────────────────────────────────────────────── */
.page-header { margin-bottom: 28px; }
.page-eyebrow {
    font-size: 10px; font-weight: bold; color: var(--red);
    letter-spacing: .18em; text-transform: uppercase;
    display: flex; align-items: center; gap: 8px; margin-bottom: 5px;
}
.page-eyebrow::before { content: ''; width: 14px; height: 2px; background: var(--red); }
.page-header h1 {
    font-size: clamp(22px, 4vw, 32px);
    font-weight: bold; color: var(--navy);
    letter-spacing: -.01em; line-height: 1.1; margin-bottom: 6px;
}
.page-header h1 span { color: #06b6d4; }
.page-header p { font-size: 13px; color: var(--muted); max-width: 680px; line-height: 1.6; }

/* ─── STAT STRIP ─────────────────────────────────────────────── */
.stat-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-top: 3px solid var(--navy);
    border-radius: 6px;
    padding: 14px 14px 12px;
    text-align: center;
    box-shadow: 0 1px 6px rgba(0,33,71,.07);
    transition: transform .18s, box-shadow .18s, border-top-color .18s;
    display: block;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0,33,71,.16);
    border-top-color: var(--red);
}
.stat-card:hover .stat-label { color: var(--navy); }

.stat-label {
    font-size: 9px; font-weight: bold; letter-spacing: .14em;
    text-transform: uppercase; color: var(--muted); margin-bottom: 5px;
}
.stat-value {
    font-size: 24px; font-weight: bold; color: var(--navy); line-height: 1;
    font-variant-numeric: tabular-nums;
}
.stat-value.loading { color: var(--border); }
.stat-sub { font-size: 10px; color: var(--muted); margin-top: 4px; }

/* ─── MAIN GRID ──────────────────────────────────────────────── */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
}

@media (max-width: 1000px) {
    .main-grid { grid-template-columns: 1fr; }
}

/* ─── CARDS ──────────────────────────────────────────────────── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    box-shadow: 0 1px 6px rgba(0,33,71,.07);
    margin-bottom: 20px;
    overflow: hidden;
}
.card:last-child { margin-bottom: 0; }

.card-head {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
}
.card-head-icon {
    width: 30px; height: 30px; border-radius: 5px;
    background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.card-head-title { font-size: 12px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; }
.card-head-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
.card-head-right { margin-left: auto; }
.card-body { padding: 16px; }

/* ─── BADGES ─────────────────────────────────────────────────── */
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: bold; letter-spacing: .06em;
    text-transform: uppercase; padding: 2px 8px; border-radius: 3px;
}
.badge-live  { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); color: #16a34a; }
.badge-live::before { content: '●'; font-size: 8px; animation: blink 1.6s ease infinite; }

/* ─── BAND CONDITIONS ────────────────────────────────────────── */
.band-toggle {
    display: flex; gap: 0;
    border: 1px solid var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-left: auto;
}
.band-toggle-btn {
    padding: 4px 12px;
    font-size: 10px; font-weight: bold; letter-spacing: .07em; text-transform: uppercase;
    background: transparent; border: none; cursor: pointer;
    color: var(--muted); font-family: Arial, sans-serif;
    transition: background .15s, color .15s;
}
.band-toggle-btn.active {
    background: var(--navy);
    color: var(--white);
}

.band-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}

.band-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg);
    transition: border-color .15s;
}
.band-item:hover { border-color: var(--navy); }

.band-name {
    font-size: 13px; font-weight: bold; color: var(--navy);
    min-width: 80px; flex-shrink: 0;
    font-variant-numeric: tabular-nums;
}

.band-bar-wrap { flex: 1; }
.band-bar-track {
    height: 6px; background: var(--border); border-radius: 999px; overflow: hidden;
    margin-bottom: 3px;
}
.band-bar-fill { height: 100%; border-radius: 999px; transition: width .5s ease, background .3s; }
.band-cond-label { font-size: 10px; font-weight: bold; letter-spacing: .05em; text-transform: uppercase; }

/* Condition colours */
.cond-poor   { color: var(--cond-poor);   }
.cond-fair   { color: var(--cond-fair);   }
.cond-good   { color: var(--cond-good);   }
.cond-excel  { color: var(--cond-excel);  }
.cond-closed { color: var(--cond-closed); }

.fill-poor   { background: var(--cond-poor);   width: 20%; }
.fill-fair   { background: var(--cond-fair);   width: 50%; }
.fill-good   { background: var(--cond-good);   width: 80%; }
.fill-excel  { background: var(--cond-excel);  width: 100%; }
.fill-closed { background: var(--cond-closed); width: 5%; }

/* ─── VHF CONDITIONS ─────────────────────────────────────────── */
.vhf-grid {
    display: flex; flex-direction: column; gap: 6px;
}

.vhf-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg);
    font-size: 12px;
}

.vhf-name { font-weight: bold; color: var(--text); }
.vhf-loc  { font-size: 10px; color: var(--muted); margin-top: 1px; }
.vhf-status {
    font-size: 10px; font-weight: bold; letter-spacing: .06em;
    text-transform: uppercase; padding: 2px 8px;
    border-radius: 3px; border: 1px solid;
}
.vhf-open   { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.3);  color: #16a34a; }
.vhf-closed { background: rgba(100,116,139,.1); border-color: rgba(100,116,139,.25); color: var(--muted); }
.vhf-active { background: rgba(6,182,212,.1);  border-color: rgba(6,182,212,.3);  color: #0284c7; }

/* ─── PROPAGATION SECTIONS ───────────────────────────────────── */
.prop-section { margin-bottom: 20px; }
.prop-section:last-child { margin-bottom: 0; }
.prop-section-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 6px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border);
}
.prop-section-icon { font-size: 16px; }
.prop-section-title { font-size: 12px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; }
.prop-section-body { font-size: 13px; color: #4b5563; line-height: 1.6; }
.prop-section-body strong { color: var(--navy); }

/* ─── SIDE CARDS ─────────────────────────────────────────────── */
.dash-alert-banner { height: 5px; }
.dash-alert-body { padding: 14px 16px; }
.dash-alert-label { font-size: 9px; font-weight: bold; letter-spacing: .14em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
.dash-alert-level-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.dash-alert-level-num { font-size: 28px; font-weight: bold; line-height: 1; }
.dash-alert-level-title { font-size: 14px; font-weight: bold; }
.dash-alert-level-desc  { font-size: 11px; color: var(--muted); margin-top: 2px; }
.dash-alert-msg { font-size: 12px; color: var(--text); margin-top: 8px; line-height: 1.5; }

.link-list { display: flex; flex-direction: column; gap: 6px; }
.link-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px;
    border: 1px solid var(--border); border-radius: 5px;
    text-decoration: none; color: var(--text);
    font-size: 12px; transition: background .15s, border-color .15s;
    background: var(--bg);
}
.link-item:hover { background: var(--navy); color: var(--white); border-color: var(--navy); }
.link-item:hover .link-sub { color: rgba(255,255,255,.65); }
.link-icon { font-size: 18px; flex-shrink: 0; }
.link-text { font-weight: bold; }
.link-sub  { font-size: 10px; color: var(--muted); margin-top: 1px; }
.link-arrow { margin-left: auto; opacity: .4; }

/* ─── GEOMAG / MISC ──────────────────────────────────────────── */
.geo-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid var(--border);
}
.geo-row:last-child { border-bottom: none; }
.geo-key { font-size: 11px; font-weight: bold; color: var(--muted); text-transform: uppercase; letter-spacing: .1em; }
.geo-val { font-size: 13px; font-weight: bold; color: var(--navy); }

/* ─── DATA TIMESTAMP ─────────────────────────────────────────── */
.data-ts {
    font-size: 10px; color: var(--muted);
    text-align: right; margin-top: 8px;
}

/* ─── ERROR STATE ────────────────────────────────────────────── */
.fetch-error {
    padding: 12px 14px;
    background: #fdf0f2; border: 1px solid rgba(200,16,46,.2); border-left: 3px solid var(--red);
    border-radius: 0 5px 5px 0; font-size: 12px; color: var(--red);
    display: none;
}

/* ─── BAND ACTIVITY (DX SPOTS) ───────────────────────────────── */
.ba-grid {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 10px;
}

.ba-item {
    display: grid;
    grid-template-columns: 36px 1fr 36px 72px;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg);
    transition: border-color .15s;
}
.ba-item:hover { border-color: var(--navy); }

.ba-band {
    font-size: 12px;
    font-weight: bold;
    color: var(--navy);
    font-variant-numeric: tabular-nums;
}

.ba-bar-wrap { flex: 1; }
.ba-bar-track {
    height: 5px;
    background: var(--border);
    border-radius: 999px;
    overflow: hidden;
}
.ba-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width .6s ease, background .3s;
}

.ba-spots {
    font-size: 10px;
    font-weight: bold;
    color: var(--muted);
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.ba-health {
    font-size: 9px;
    font-weight: bold;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 3px;
    text-align: center;
    white-space: nowrap;
}

.ba-hot      { background: #dcfce7; color: #15803d; }
.ba-active   { background: #d1fae5; color: #15803d; }
.ba-moderate { background: #fef9c3; color: #854d0e; }
.ba-quiet    { background: #f1f5f9; color: #64748b; }
.ba-dead     { background: #f1f5f9; color: #94a3b8; }

.ba-bar-hot      { background: #16a34a; }
.ba-bar-active   { background: #22c55e; }
.ba-bar-moderate { background: #f59e0b; }
.ba-bar-quiet    { background: #94a3b8; }
.ba-bar-dead     { background: #cbd5e1; }

/* ─── BAND ACTIVITY (DX SPOTS) ───────────────────────────────── */
@media (max-width: 600px) {
    .stat-strip { grid-template-columns: repeat(2, 1fr); }
    .band-grid  { grid-template-columns: 1fr; }
}
</style>


<div class="wrap">

    <header class="page-header">
        <div class="page-eyebrow">Data Dashboard</div>
        <h1>Live <span>Propagation</span> Brief</h1>
        <p>Real-time UK HF/VHF propagation indicators and solar/geomagnetic conditions sourced from N0NBH &amp; NOAA SWPC — updated automatically every 10 minutes.</p>
    </header>

    {{-- ─── STAT STRIP ─── --}}
    <div class="stat-strip">
        <a class="stat-card" href="https://www.spaceweather.gc.ca/forecast-prevision/solar-solaire/solarflux/sx-5-flux-en.php" target="_blank" rel="noopener">
            <div class="stat-label">Solar Flux</div>
            <div class="stat-value loading" id="val-sfi">–</div>
            <div class="stat-sub">SFI (sfu)</div>
        </a>
        <a class="stat-card" href="https://www.sidc.be/SILSO/home" target="_blank" rel="noopener">
            <div class="stat-label">Sunspots</div>
            <div class="stat-value loading" id="val-sn">–</div>
            <div class="stat-sub">SSN</div>
        </a>
        <a class="stat-card" href="https://www.swpc.noaa.gov/products/real-time-solar-wind" target="_blank" rel="noopener">
            <div class="stat-label">Solar Wind</div>
            <div class="stat-value loading" id="val-sw">–</div>
            <div class="stat-sub">km/s</div>
        </a>
        <a class="stat-card" href="https://www.spaceweatherlive.com/en/solar-activity/kp-index.html" target="_blank" rel="noopener">
            <div class="stat-label">Kp Index</div>
            <div class="stat-value loading" id="val-kp">–</div>
            <div class="stat-sub" id="sub-kp">Planetary</div>
        </a>
        <a class="stat-card" href="https://www.swpc.noaa.gov/products/goes-x-ray-flux" target="_blank" rel="noopener">
            <div class="stat-label">X-Ray</div>
            <div class="stat-value loading" id="val-xray" style="font-size:20px;">–</div>
            <div class="stat-sub">Flare Class</div>
        </a>
        <a class="stat-card" href="https://www.swpc.noaa.gov/products/planetary-k-index" target="_blank" rel="noopener">
            <div class="stat-label">A Index</div>
            <div class="stat-value loading" id="val-ai">–</div>
            <div class="stat-sub">Geomagnetic</div>
        </a>
        <a class="stat-card" href="https://www.swpc.noaa.gov/products/real-time-solar-wind" target="_blank" rel="noopener">
            <div class="stat-label">Mag Field</div>
            <div class="stat-value loading" id="val-mag" style="font-size:18px;">–</div>
            <div class="stat-sub">nT (Bz)</div>
        </a>
        <a class="stat-card" href="https://www.hamqsl.com/solar.html" target="_blank" rel="noopener">
            <div class="stat-label">Signal Noise</div>
            <div class="stat-value loading" id="val-sn2" style="font-size:18px;">–</div>
            <div class="stat-sub">S-Units</div>
        </a>
    </div>

    <div class="fetch-error" id="fetchError">
        ⚠ Unable to load live propagation data from hamqsl.com. Retrying in 60 seconds…
    </div>

    {{-- ─── MAIN GRID ─── --}}
    <div class="main-grid">

        {{-- LEFT COLUMN --}}
        <div>

            {{-- BAND CONDITIONS --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">📡</div>
                    <div>
                        <div class="card-head-title">HF Band Conditions</div>
                        <div class="card-head-sub">Calculated from solar/geomagnetic indices — N0NBH</div>
                    </div>
                    <div class="card-head-right">
                        <div class="band-toggle">
                            <button class="band-toggle-btn active" id="btnDay" onclick="showBands('day')">Day</button>
                            <button class="band-toggle-btn" id="btnNight" onclick="showBands('night')">Night</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="band-grid" id="bandGrid">
                        {{-- Populated by JS --}}
                        <div class="band-item"><span class="band-name">80m–40m</span><div class="band-bar-wrap"><div class="band-bar-track"><div class="band-bar-fill" style="background:var(--border);width:30%;"></div></div><span class="band-cond-label" style="color:var(--border);">Loading…</span></div></div>
                        <div class="band-item"><span class="band-name">30m–20m</span><div class="band-bar-wrap"><div class="band-bar-track"><div class="band-bar-fill" style="background:var(--border);width:30%;"></div></div><span class="band-cond-label" style="color:var(--border);">Loading…</span></div></div>
                        <div class="band-item"><span class="band-name">17m–15m</span><div class="band-bar-wrap"><div class="band-bar-track"><div class="band-bar-fill" style="background:var(--border);width:30%;"></div></div><span class="band-cond-label" style="color:var(--border);">Loading…</span></div></div>
                        <div class="band-item"><span class="band-name">12m–10m</span><div class="band-bar-wrap"><div class="band-bar-track"><div class="band-bar-fill" style="background:var(--border);width:30%;"></div></div><span class="band-cond-label" style="color:var(--border);">Loading…</span></div></div>
                    </div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:var(--cond-excel);border-radius:2px;display:inline-block;"></span>Excellent</span>
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:var(--cond-good);border-radius:2px;display:inline-block;"></span>Good</span>
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:var(--cond-fair);border-radius:2px;display:inline-block;"></span>Fair</span>
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:var(--cond-poor);border-radius:2px;display:inline-block;"></span>Poor</span>
                    </div>
                </div>
            </div>

            {{-- LIVE BAND ACTIVITY (DX SPOTS) --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">📶</div>
                    <div>
                        <div class="card-head-title">Live Band Activity</div>
                        <div class="card-head-sub">Real-time DX cluster spot analysis — updated every 2 min</div>
                    </div>
                    <div class="card-head-right">
                        <span class="badge badge-live">Live</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="ba-grid" id="bandActivityGrid">
                        {{-- Populated by JS from /api/hamdash --}}
                        @foreach (['160m','80m','60m','40m','30m','20m','17m','15m','12m','10m','6m'] as $b)
                        <div class="ba-item ba-loading">
                            <span class="ba-band">{{ $b }}</span>
                            <div class="ba-bar-wrap">
                                <div class="ba-bar-track"><div class="ba-bar-fill" style="width:0%;background:var(--border);"></div></div>
                            </div>
                            <span class="ba-spots">–</span>
                            <span class="ba-health" style="background:var(--border);color:transparent;">···</span>
                        </div>
                        @endforeach
                    </div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;padding-top:8px;border-top:1px solid var(--border);">
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:#16a34a;border-radius:2px;display:inline-block;"></span>Hot / Active</span>
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:#f59e0b;border-radius:2px;display:inline-block;"></span>Moderate</span>
                        <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:4px;"><span style="width:10px;height:6px;background:var(--border);border-radius:2px;display:inline-block;"></span>Quiet / Dead</span>
                        <span style="font-size:10px;color:var(--muted);margin-left:auto;">Via DX cluster spot analysis</span>
                    </div>
                </div>
            </div>

            {{-- PROPAGATION BRIEF --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">🌍</div>
                    <div>
                        <div class="card-head-title">UK Propagation Brief</div>
                        <div class="card-head-sub" id="briefDate">{{ now()->format('l j F Y') }} — Daily Update</div>
                    </div>
                    <div class="card-head-right">
                        <span class="badge badge-live">Live</span>
                    </div>
                </div>
                <div class="card-body">

                    <div class="prop-section">
                        <div class="prop-section-header">
                            <span class="prop-section-icon">☀️</span>
                            <span class="prop-section-title">Solar &amp; Geomagnetic</span>
                        </div>
                        <div class="prop-section-body" id="briefSolar">
                            Fetching live data…
                        </div>
                    </div>

                    <div class="prop-section">
                        <div class="prop-section-header">
                            <span class="prop-section-icon">📡</span>
                            <span class="prop-section-title">HF Bands (1.8–30 MHz)</span>
                        </div>
                        <div class="prop-section-body" id="briefHF">
                            Daytime best: see band conditions above. NVIS on 40 m / 60 m for regional comms.
                            High-band (17 m–10 m) performance varies with solar flux — check band grid above.
                        </div>
                    </div>

                    <div class="prop-section">
                        <div class="prop-section-header">
                            <span class="prop-section-icon">📶</span>
                            <span class="prop-section-title">VHF / UHF (50–432 MHz)</span>
                        </div>
                        <div class="prop-section-body" id="briefVHF">
                            Loading VHF analysis…
                        </div>
                    </div>

                    <div class="prop-section">
                        <div class="prop-section-header">
                            <span class="prop-section-icon">📍</span>
                            <span class="prop-section-title">{{ \App\Helpers\RaynetSetting::groupRegion() }} / {{ \App\Helpers\RaynetSetting::groupRegion() }} Note</span>
                        </div>
                        <div class="prop-section-body">
                            40 m NVIS: Best 08:00–11:00 local; 60 m fallback 14:00–17:00.
                            VHF/UHF: standard repeater and local paths.
                            For RAYNET regional nets, focus on 40 m HF when HF conditions allow.
                        </div>
                    </div>

                    <div class="data-ts" id="dataTimestamp"></div>

                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div>

            {{-- GEOMAGNETIC DETAIL --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">🧲</div>
                    <div>
                        <div class="card-head-title">Space Environment</div>
                        <div class="card-head-sub">Real-time indices</div>
                    </div>
                </div>
                <div class="card-body" style="padding:0 16px;">
                    <div class="geo-row">
                        <span class="geo-key">Geomag Field</span>
                        <span class="geo-val" id="geo-field">–</span>
                    </div>
                    <div class="geo-row">
                        <span class="geo-key">Proton Flux</span>
                        <span class="geo-val" id="geo-proton">–</span>
                    </div>
                    <div class="geo-row">
                        <span class="geo-key">Electron Flux</span>
                        <span class="geo-val" id="geo-electron">–</span>
                    </div>
                    <div class="geo-row">
                        <span class="geo-key">Aurora Prob.</span>
                        <span class="geo-val" id="geo-aurora">–</span>
                    </div>
                    <div class="geo-row">
                        <span class="geo-key">Helium Line</span>
                        <span class="geo-val" id="geo-helium">–</span>
                    </div>
                    <div class="geo-row">
                        <span class="geo-key">MUF</span>
                        <span class="geo-val" id="geo-muf">–</span>
                    </div>
                </div>
            </div>

            {{-- EXTERNAL LINKS --}}
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon">🔗</div>
                    <div>
                        <div class="card-head-title">External Resources</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="link-list">
                        <a href="https://hamdash.app/" target="_blank" class="link-item">
                            <span class="link-icon">📊</span>
                            <div>
                                <div class="link-text">HamDash</div>
                                <div class="link-sub">Live propagation dashboard</div>
                            </div>
                            <span class="link-arrow">↗</span>
                        </a>
                        <a href="https://www.swpc.noaa.gov/" target="_blank" class="link-item">
                            <span class="link-icon">🌤️</span>
                            <div>
                                <div class="link-text">NOAA SWPC</div>
                                <div class="link-sub">Space weather &amp; forecasts</div>
                            </div>
                            <span class="link-arrow">↗</span>
                        </a>
                        <a href="https://www.spaceweatherlive.com/" target="_blank" class="link-item">
                            <span class="link-icon">🔭</span>
                            <div>
                                <div class="link-text">SpaceWeatherLive</div>
                                <div class="link-sub">Kp &amp; aurora real-time</div>
                            </div>
                            <span class="link-arrow">↗</span>
                        </a>
                        <a href="https://www.propquest.co.uk/" target="_blank" class="link-item">
                            <span class="link-icon">📈</span>
                            <div>
                                <div class="link-text">PropQuest</div>
                                <div class="link-sub">MUF &amp; ionosonde data</div>
                            </div>
                            <span class="link-arrow">↗</span>
                        </a>
                        <a href="https://www.hamqsl.com/solar.html" target="_blank" class="link-item">
                            <span class="link-icon">☀️</span>
                            <div>
                                <div class="link-text">N0NBH Solar</div>
                                <div class="link-sub">Solar-terrestrial data feed</div>
                            </div>
                            <span class="link-arrow">↗</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
// ──────────────────────────────────────────────────────────────
// LIVE PROPAGATION DATA — fetched from hamqsl.com XML (N0NBH)
// Same data source as hamdash.app
// ──────────────────────────────────────────────────────────────

let currentBandTime = 'day';
let bandData = {};

// Condition → fill class & label colour
function condClass(cond) {
    if (!cond) return { fill: 'fill-closed', cls: 'cond-closed' };
    const c = cond.toLowerCase();
    if (c === 'excellent') return { fill: 'fill-excel',  cls: 'cond-excel'  };
    if (c === 'good')      return { fill: 'fill-good',   cls: 'cond-good'   };
    if (c === 'fair')      return { fill: 'fill-fair',   cls: 'cond-fair'   };
    if (c === 'poor')      return { fill: 'fill-poor',   cls: 'cond-poor'   };
    return { fill: 'fill-closed', cls: 'cond-closed' };
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) { el.textContent = val; el.classList.remove('loading'); }
}

function renderBandGrid(time) {
    const bands = ['80m-40m', '30m-20m', '17m-15m', '12m-10m'];
    const grid  = document.getElementById('bandGrid');
    if (!grid || !bandData[time]) return;

    grid.innerHTML = bands.map(band => {
        const cond = bandData[time][band] || 'No Data';
        const { fill, cls } = condClass(cond);
        return `
        <div class="band-item">
            <span class="band-name">${band}</span>
            <div class="band-bar-wrap">
                <div class="band-bar-track">
                    <div class="band-bar-fill ${fill}"></div>
                </div>
                <span class="band-cond-label ${cls}">${cond}</span>
            </div>
        </div>`;
    }).join('');
}

// ── Band activity (DX spots) ───────────────────────────────────
const healthEmoji = { hot: '🔥', active: '✅', moderate: '⚡', quiet: '💤', dead: '⬛' };
const healthLabel = { hot: 'Hot', active: 'Active', moderate: 'Moderate', quiet: 'Quiet', dead: 'Dead' };

function renderBandActivity(bands) {
    const grid = document.getElementById('bandActivityGrid');
    if (!grid || !bands || !bands.length) return;

    // Max spots for bar scaling
    const maxSpots = Math.max(...bands.map(b => b.spots || 0), 1);

    grid.innerHTML = bands.map(b => {
        const h       = (b.health || 'quiet').toLowerCase();
        const pct     = Math.round(((b.spots || 0) / maxSpots) * 100);
        const emoji   = healthEmoji[h] || '💤';
        const label   = healthLabel[h] || h;
        return `
        <div class="ba-item">
            <span class="ba-band">${b.band}</span>
            <div class="ba-bar-wrap">
                <div class="ba-bar-track">
                    <div class="ba-bar-fill ba-bar-${h}" style="width:${pct}%;"></div>
                </div>
            </div>
            <span class="ba-spots">${b.spots} spots</span>
            <span class="ba-health ba-${h}">${emoji} ${label}</span>
        </div>`;
    }).join('');
}

// ── Hamdash data fetch ─────────────────────────────────────────
function loadHamDash() {
    fetch('/api/hamdash', { cache: 'no-cache' })
        .then(r => r.json())
        .then(json => {
            const bands = json?.data?.bandHealth?.bands;
            if (bands) renderBandActivity(bands);
        })
        .catch(() => {
            // Silently fail — band activity grid keeps placeholder state
        });
}

function kpDescription(kp) {
    const k = parseFloat(kp);
    if (isNaN(k))  return 'No data';
    if (k <= 1)    return 'Quiet';
    if (k <= 2)    return 'Quiet';
    if (k <= 3)    return 'Unsettled';
    if (k <= 4)    return 'Active';
    if (k <= 5)    return 'Minor Storm';
    if (k <= 6)    return 'Moderate Storm';
    return 'Severe Storm';
}

function updatePropBrief(d) {
    document.getElementById('briefSolar').innerHTML =
        `Solar Flux: <strong>${d.sfi} sfu</strong>, Sunspots: <strong>${d.sn}</strong>.<br>
        Solar wind: <strong>${d.sw} km/s</strong>, Kp Index: <strong>${d.kp}</strong> (${kpDescription(d.kp)}).<br>
        X-ray flux: <strong>${d.xray}</strong>, A Index: <strong>${d.ai}</strong>, Mag field: <strong>${d.mag} nT</strong>.<br>
        <em style="color:#6b7f96;">Geomagnetic field: ${d.geoField}. ${parseFloat(d.kp)>=4 ? 'Aurora activity possible at high latitudes.' : 'Conditions generally stable.'}</em>`;

    const kp = parseFloat(d.kp);
    let vhfSummary = 'Troposcatter/ducting: depends on local pressure systems. ';
    vhfSummary += kp >= 4
        ? `Auroral activity <strong>likely</strong> (Kp ${d.kp}) — VHF aurora may be workable on 2m/6m from {{ \App\Helpers\RaynetSetting::groupRegion() }}. `
        : `Auroral activity <strong>low</strong> (Kp ${d.kp}). `;
    vhfSummary += 'Standard repeater paths reliable for local RAYNET ops.';
    document.getElementById('briefVHF').innerHTML = vhfSummary;
}

function loadPropData() {
    // Proxy via server-side to avoid CORS — see note below
    // Direct fetch from hamqsl.com XML works in many setups
    fetch('/api/propagation', { cache: 'no-cache' })
        .then(r => r.text())
        .then(xml => {
            document.getElementById('fetchError').style.display = 'none';

            const parser = new DOMParser();
            const doc    = parser.parseFromString(xml, 'text/xml');
            const get    = tag => doc.querySelector(tag)?.textContent?.trim() ?? '–';

            // Scalar values
            const d = {
                sfi:      get('solarflux'),
                sn:       get('sunspots'),
                sw:       get('solarwind'),
                kp:       get('kindex'),
                xray:     get('xray'),
                ai:       get('aindex'),
                mag:      get('magneticfield'),
                aurora:   get('aurora'),
                helium:   get('heliumline'),
                proton:   get('protonflux'),
                electron: get('electonflux'),
                muf:      get('muf'),
                geoField: get('geomagfield'),
                sigNoise: get('signalnoise'),
                updated:  get('updated'),
            };

            // Update stats
            setText('val-sfi',  d.sfi);
            setText('val-sn',   d.sn);
            setText('val-sw',   d.sw);
            setText('val-kp',   d.kp);
            setText('val-xray', d.xray);
            setText('val-ai',   d.ai);
            setText('val-mag',  d.mag.replace(/\s+/g, ''));
            setText('val-sn2',  d.sigNoise);
            const subKp = document.getElementById('sub-kp');
            if (subKp) subKp.textContent = kpDescription(d.kp);

            // Sidebar geo values
            setText('geo-field',    d.geoField);
            setText('geo-proton',   d.proton + ' pfu');
            setText('geo-electron', Number(d.electron).toLocaleString() + ' e/cm²');
            setText('geo-aurora',   d.aurora + '°N lat');
            setText('geo-helium',   d.helium + ' sfu');
            setText('geo-muf',      d.muf !== 'NoRpt' ? d.muf + ' MHz' : 'No report');

            // Band conditions
            bandData = { day: {}, night: {} };
            doc.querySelectorAll('calculatedconditions band').forEach(b => {
                const name = b.getAttribute('name');
                const time = b.getAttribute('time');
                bandData[time][name] = b.textContent.trim();
            });
            renderBandGrid(currentBandTime);

            // Propagation brief
            updatePropBrief(d);

            // Timestamp
            const ts = document.getElementById('dataTimestamp');
            if (ts) ts.textContent = 'Data updated: ' + d.updated + ' (N0NBH / hamqsl.com)';
        })
        .catch(err => {
            console.error('Propagation fetch failed:', err);
            document.getElementById('fetchError').style.display = 'block';

            // Fall back to server-rendered PHP values if available
            const phpSfi = '{{ $sfi ?? "--" }}';
            if (phpSfi !== '--') {
                setText('val-sfi',  phpSfi);
                setText('val-sn',   '{{ $sunspots ?? "--" }}');
                setText('val-sw',   '{{ $solarWind ?? "--" }}');
                setText('val-kp',   '{{ $latestKp ?? "--" }}');
                setText('val-xray', '{{ $xray ?? "--" }}');
                setText('val-ai',   '{{ $aIndex ?? "--" }}');
            }
        });
}

function showBands(time) {
    currentBandTime = time;
    document.getElementById('btnDay').classList.toggle('active', time === 'day');
    document.getElementById('btnNight').classList.toggle('active', time === 'night');
    renderBandGrid(time);
}

// Initial load + refresh
loadPropData();
loadHamDash();
setInterval(loadPropData, 10 * 60 * 1000);   // solar data every 10 min
setInterval(loadHamDash,   2 * 60 * 1000);   // DX spots every 2 min
</script>

@endsection