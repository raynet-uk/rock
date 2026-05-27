@extends('layouts.app')
@section('title', 'Request Support')
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
.page-header { margin-bottom: 2rem; text-align: center; }
.page-header-eyebrow {
    font-size: 0.85rem;
    font-weight: bold;
    color: var(--red);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.6rem;
}
.page-header h1 {
    font-size: 1.7rem;
    font-weight: bold;
    line-height: 1.15;
    color: var(--navy);
    margin-bottom: 0.8rem;
}
@media (min-width: 576px) { .page-header h1 { font-size: 2rem; } }
.page-header h1 span { color: var(--red); }
.page-header p {
    font-size: 0.95rem;
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

/* FLASH MESSAGES */
.flash {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}
.flash-success {
    background: rgba(46,125,50,0.12);
    border: 1px solid rgba(46,125,50,0.3);
    color: #2E7D32;
}
.flash-error {
    background: rgba(200,16,46,0.12);
    border: 1px solid rgba(200,16,46,0.3);
    color: var(--red);
}
.flash-icon { font-size: 1.4rem; flex-shrink: 0; margin-top: 0.2rem; }
.flash ul { margin: 0.5rem 0 0 1.2rem; padding: 0; list-style: disc; }
.flash li { margin: 0.2rem 0; }

/* MAIN GRID */
.request-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) { .request-grid { grid-template-columns: 1fr 300px; } }

/* FORM CARD */
.form-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.form-card-head {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    background: var(--light);
    border-bottom: 1px solid var(--border);
}
.form-card-head-icon { font-size: 1.6rem; line-height: 1; }
.form-card-head-title {
    font-size: 1.15rem;
    font-weight: bold;
    color: var(--navy);
}
.form-card-head-sub {
    font-size: 0.85rem;
    color: var(--muted);
}
.form-body {
    padding: 1.2rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}
.field-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 576px) { .field-row { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); } }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field.full { grid-column: 1 / -1; }
.field label {
    font-size: 0.9rem;
    font-weight: bold;
    color: var(--navy);
}
.field label .req { color: var(--red); font-weight: bold; }
.field input,
.field select,
.field textarea {
    padding: 0.8rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.95rem;
    background: white;
    color: var(--text-light);
    transition: var(--transition);
}
.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(200,16,46,0.15);
    outline: none;
}
.field textarea {
    min-height: 140px;
    resize: vertical;
}
.field input::placeholder,
.field textarea::placeholder { color: var(--muted); }

/* FORM FOOTER */
.form-footer {
    padding: 1rem 1.2rem;
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: var(--light);
}
@media (min-width: 576px) {
    .form-footer { flex-direction: row; align-items: center; justify-content: space-between; }
}
.form-footer-note {
    font-size: 0.85rem;
    color: var(--muted);
}
.form-footer-note span { color: var(--red); font-weight: bold; }
.btn-submit {
    padding: 0.8rem 1.6rem;
    border-radius: 999px;
    background: var(--red);
    color: white;
    font-size: 0.95rem;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    align-self: flex-start;
}
.btn-submit:hover {
    background: #a00d25;
    transform: translateY(-2px);
}

/* SIDE COLUMN */
.side-col { display: flex; flex-direction: column; gap: 1.5rem; }

/* INFO PANEL */
.info-panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.info-panel-body { padding: 1.2rem; }
.info-panel-title {
    font-size: 1.15rem;
    font-weight: bold;
    color: var(--navy);
    margin-bottom: 1rem;
}
.info-row {
    display: flex;
    gap: 0.8rem;
    padding: 0.8rem 0;
    border-bottom: 1px solid var(--border);
}
.info-row:last-child { border-bottom: none; padding-bottom: 0; }
.info-row-icon { font-size: 1.6rem; flex-shrink: 0; margin-top: 0.2rem; }
.info-row-label {
    font-size: 1rem;
    font-weight: bold;
    color: var(--navy);
    margin-bottom: 0.2rem;
}
.info-row-val { font-size: 0.9rem; color: var(--text-light); }

/* LINK CARD */
.link-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
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

/* LINK CARD HEADER (was missing entirely) */
.card-head {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    background: var(--light);
    border-bottom: 1px solid var(--border);
}
.card-head-icon { font-size: 1.4rem; line-height: 1; flex-shrink: 0; }
.card-head-title { font-size: 1.05rem; font-weight: bold; color: var(--navy); }

/* MOBILE FIXES */
@media (max-width: 600px) {
    .wrap { padding: 0 .75rem 2rem; }
    .topbar { padding: .75rem 0; margin-bottom: 1rem; }
    .status-chip span { font-size: .78rem; }
    .page-header h1 { font-size: 1.5rem; }
    .page-header { margin-bottom: 1.2rem; }
    .btn-submit { width: 100%; text-align: center; justify-content: center; align-self: stretch; }
    .form-footer { gap: .75rem; }
    .link-item { padding: .85rem .9rem; gap: .6rem; }
    .link-item-icon { font-size: 1.3rem; }
    .info-row { gap: .6rem; }
    .info-row-icon { font-size: 1.3rem; }
}

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
            <span>Accepting Requests – Free Support</span>
        </div>
    </nav>

    <header class="page-header">
        <div class="page-header-eyebrow">// Request Support</div>
        <h1>Request <span>RAYNET</span><br>for Your Event</h1>
        <p>Fill in the form with as much detail as possible. We'll review your request and contact you promptly to discuss resilient radio support. Fields marked * are required.</p>
    </header>

    @if (session('status'))
        <div class="flash flash-success">
            <div class="flash-icon">✓</div>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="flash flash-error">
            <div class="flash-icon">⚠</div>
            <div>
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="request-grid">
        <div class="form-card">
            <div class="form-card-head">
                <div class="form-card-head-icon">📋</div>
                <div>
                    <div class="form-card-head-title">Event Support Request</div>
                    <div class="form-card-head-sub">Help us plan effectively</div>
                </div>
            </div>

            <form method="POST" action="{{ route('request-support.submit') }}">
                @csrf

                <div class="form-body">
                    <div class="field-row">
                        <div class="field">
                            <label for="event_name">Event Name <span class="req">*</span></label>
                            <input id="event_name" name="event_name" type="text" required
                                   value="{{ old('event_name') }}"
                                   placeholder="e.g. Liverpool Half Marathon 2026">
                        </div>
                        <div class="field">
                            <label for="event_date">Event Date</label>
                            <input id="event_date" name="event_date" type="date"
                                   value="{{ old('event_date') }}">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="location">Location <span class="req">*</span></label>
                            <input id="location" name="location" type="text" required
                                   value="{{ old('location') }}"
                                   placeholder="e.g. City centre, start/finish, key areas">
                        </div>
                        <div class="field">
                            <label for="org">Organising Body</label>
                            <input id="org" name="org" type="text"
                                   value="{{ old('org') }}"
                                   placeholder="e.g. Charity, council, club">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="contact_name">Primary Contact Name <span class="req">*</span></label>
                            <input id="contact_name" name="contact_name" type="text" required
                                   value="{{ old('contact_name') }}"
                                   placeholder="Full name">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="contact_email">Contact Email <span class="req">*</span></label>
                            <input id="contact_email" name="contact_email" type="email" required
                                   value="{{ old('contact_email') }}"
                                   placeholder="you@example.com">
                        </div>
                        <div class="field">
                            <label for="contact_phone">Contact Phone</label>
                            <input id="contact_phone" name="contact_phone" type="tel"
                                   value="{{ old('contact_phone') }}"
                                   placeholder="07700 900123">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field full">
                            <label for="details">Event Outline & RAYNET Help Needed <span class="req">*</span></label>
                            <textarea id="details" name="details" required
                                      placeholder="Route/type, participants, marshal/medical points, safety concerns, why radio needed (e.g. network risk), website links...">{{ old('details') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-footer">
                    <div class="form-footer-note">
                        Submit <span>4+ weeks</span> ahead for best coordination.<br>
                        We'll reply quickly — service is completely free.
                    </div>
                    <button type="submit" class="btn-submit">Submit Request →</button>
                </div>
            </form>
        </div>

        <div class="side-col">
            <div class="info-panel">
                <div class="info-panel-body">
                    <div class="info-panel-title">What Happens Next</div>
                    <div class="info-row">
                        <div class="info-row-icon">📬</div>
                        <div>
                            <div class="info-row-label">Review & Contact</div>
                            <div class="info-row-val">Controller reviews; reply usually within days.</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-row-icon">🗓️</div>
                        <div>
                            <div class="info-row-label">Planning Discussion</div>
                            <div class="info-row-val">Layout, timings, operator needs, safety integration.</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-row-icon">📻</div>
                        <div>
                            <div class="info-row-label">Comms Plan</div>
                            <div class="info-row-val">Frequencies, callsigns, positions confirmed & shared.</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-row-icon">💷</div>
                        <div>
                            <div class="info-row-label">Free Service</div>
                            <div class="info-row-val">Voluntary — no cost to you or participants.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="link-card">
                <div class="card-head">
                    <div class="card-head-icon">🔗</div>
                    <div>
                        <div class="card-head-title">Related Pages</div>
                    </div>
                </div>
                <div class="link-list">
                    <a href="{{ route('event-support') }}" class="link-item">
                        <div class="link-item-icon">🏁</div>
                        <div>
                            <div class="link-item-text">Event Support</div>
                            <div class="link-item-sub">What we offer & process</div>
                        </div>
                        <div class="link-item-arrow">→</div>
                    </a>
                    <a href="{{ route('about') }}" class="link-item">
                        <div class="link-item-icon">📡</div>
                        <div>
                            <div class="link-item-text">About RAYNET</div>
                            <div class="link-item-sub">Who we are & capabilities</div>
                        </div>
                        <div class="link-item-arrow">→</div>
                    </a>
                    <a href="{{ route('training') }}" class="link-item">
                        <div class="link-item-icon">🎓</div>
                        <div>
                            <div class="link-item-text">Training & Nets</div>
                            <div class="link-item-sub">Exercises & schedule</div>
                        </div>
                        <div class="link-item-arrow">→</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection