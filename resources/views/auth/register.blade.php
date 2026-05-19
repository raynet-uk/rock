@extends('layouts.app')
@section('title', 'Create Account')
@section('content')

<style>
:root {
    --navy:       #003366;
    --navy-mid:   #004080;
    --navy-faint: #e8eef5;
    --red:        #C8102E;
    --red-faint:  #fdf0f2;
    --white:      #FFFFFF;
    --grey:       #f2f5f9;
    --grey-mid:   #dde2e8;
    --grey-dark:  #9aa3ae;
    --text:       #001f40;
    --text-mid:   #2d4a6b;
    --muted:      #6b7f96;
    --green:      #1a6b3c;
    --green-bg:   #eef7f2;
    --teal:       #0288d1;
    --font:       Arial,'Helvetica Neue',Helvetica,sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body { font-family: var(--font); font-size: 14px; color: var(--text); background: var(--navy); min-height: 100vh; }

/* ── Full-screen split ── */
.page { display: flex; min-height: 100vh; flex-direction: column; }
@media (min-width: 820px) { .page { flex-direction: row; } }

/* ── LEFT PANEL ── */
.left {
    background: var(--navy);
    padding: 2.5rem 2rem;
    display: flex; flex-direction: column;
    position: relative; overflow: hidden; flex-shrink: 0;
}
.left::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(-45deg,transparent,transparent 20px,rgba(255,255,255,.018) 20px,rgba(255,255,255,.018) 21px);
    pointer-events: none;
}
.left::after {
    content: ''; position: absolute;
    bottom: -20%; right: -15%;
    width: 60%; padding-top: 60%;
    border-radius: 50%;
    background: radial-gradient(circle,rgba(200,16,46,.14) 0%,transparent 65%);
    pointer-events: none;
}
@media (min-width: 820px) {
    .left { width: 42%; min-height: 100vh; padding: 3rem 3rem 3rem 2.5rem; position: sticky; top: 0; height: 100vh; }
}
.left-inner { position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%; }

.brand { display: flex; align-items: center; gap: .85rem; }
.rn-logo { background: var(--red); width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo span { font-size: 11px; font-weight: bold; color: var(--white); letter-spacing: .06em; text-align: center; line-height: 1.15; text-transform: uppercase; }
.brand-name { font-size: 15px; font-weight: bold; color: var(--white); letter-spacing: .04em; text-transform: uppercase; }
.brand-sub  { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; text-transform: uppercase; letter-spacing: .06em; }

.left-hero { margin-top: 3.5rem; flex: 1; }
.left-eyebrow { font-size: .68rem; font-weight: bold; text-transform: uppercase; letter-spacing: .16em; color: rgba(255,255,255,.35); margin-bottom: .6rem; }
.left-title { font-size: clamp(1.8rem,4vw,2.6rem); font-weight: bold; color: #fff; line-height: 1.12; margin-bottom: .85rem; }
.left-title span { color: #90caf9; }
.left-desc { font-size: .87rem; color: rgba(255,255,255,.5); line-height: 1.7; max-width: 340px; }

.left-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 2rem; }
.chip { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .75rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); font-size: .72rem; color: rgba(255,255,255,.5); background: rgba(255,255,255,.06); }
.chip-dot { width: 5px; height: 5px; border-radius: 50%; background: #90caf9; flex-shrink: 0; }

.left-steps { margin-top: 2.5rem; display: flex; flex-direction: column; gap: 0; }
.left-step { display: flex; gap: .85rem; position: relative; }
.left-step:not(:last-child)::before {
    content: ''; position: absolute;
    left: 13px; top: 28px;
    width: 2px; height: calc(100% - 8px);
    background: rgba(255,255,255,.1);
}
.step-num {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: bold; color: rgba(255,255,255,.5);
    position: relative; z-index: 1; background: var(--navy);
}
.left-step.done .step-num { border-color: #4ade80; color: #4ade80; background: rgba(74,222,128,.08); }
.step-body { padding: .05rem 0 1.5rem; }
.step-title { font-size: .78rem; font-weight: bold; color: rgba(255,255,255,.65); margin-bottom: .15rem; }
.step-desc  { font-size: .72rem; color: rgba(255,255,255,.35); line-height: 1.55; }

.left-footer { margin-top: auto; padding-top: 2rem; font-size: .7rem; color: rgba(255,255,255,.2); line-height: 1.6; }

/* ── RIGHT PANEL ── */
.right {
    background: var(--white);
    flex: 1; display: flex; flex-direction: column;
    border-left: 4px solid var(--red);
}

.right-head { padding: 2.5rem 2.5rem 0; border-bottom: 1px solid var(--grey-mid); background: var(--grey); }
@media (min-width: 820px) { .right-head { padding: 3rem 3.5rem 0; } }

.right-head-title {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding-bottom: 1.25rem; gap: 1rem;
}
.right-eyebrow { font-size: .68rem; font-weight: bold; text-transform: uppercase; letter-spacing: .16em; color: var(--red); margin-bottom: .3rem; display: flex; align-items: center; gap: .35rem; }
.right-eyebrow::before { content: ''; width: 12px; height: 2px; background: var(--red); display: inline-block; }
.right-title { font-size: 1.2rem; font-weight: bold; color: var(--navy); }
.right-sub   { font-size: .78rem; color: var(--muted); margin-top: .2rem; line-height: 1.55; max-width: 380px; }
.approval-badge {
    display: inline-flex; align-items: center; gap: .35rem; padding: 3px 10px;
    font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    background: var(--white); border: 1px solid var(--grey-mid); color: var(--muted); flex-shrink: 0;
}

.section-divider { display: flex; align-items: center; gap: .6rem; padding: 0 0 .85rem; }
.section-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .14em; color: var(--muted); white-space: nowrap; }
.section-rule  { flex: 1; height: 1px; background: var(--grey-mid); }

.right-body { flex: 1; padding: 0 2.5rem; overflow-y: auto; }
@media (min-width: 820px) { .right-body { padding: 0 3.5rem; } }

.reg-form { padding: 1.75rem 0 1rem; max-width: 480px; display: flex; flex-direction: column; gap: .95rem; }

/* ── Fields ── */
.field { display: flex; flex-direction: column; gap: .3rem; }
.field label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
.field label .req { color: var(--red); margin-left: 2px; }
.field label small { text-transform: none; letter-spacing: 0; color: var(--grey-dark); font-weight: normal; font-size: 10px; }

.input-wrap { position: relative; }
.input-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); font-size: .85rem; color: var(--grey-dark); pointer-events: none; transition: color .15s; }
.field:focus-within .input-icon { color: var(--navy); }

.field input {
    width: 100%; padding: .52rem .75rem .52rem 2.1rem;
    border: 1px solid var(--grey-mid); background: var(--white); color: var(--text);
    font-family: var(--font); font-size: 13px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.field input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.field input::placeholder { color: var(--grey-dark); }
.field input.input-error { border-color: var(--red); }
.field input.input-autofilled { border-color: var(--teal); background: #f0f8ff; }
.field input:disabled { background: var(--grey); color: var(--muted); cursor: not-allowed; }
#callsign { text-transform: uppercase; font-weight: bold; letter-spacing: .08em; }

/* Autofill indicator */
.autofill-badge {
    display: none; align-items: center; gap: .3rem;
    font-size: 10px; font-weight: bold; color: var(--teal);
    margin-top: .2rem;
}
.autofill-badge.show { display: flex; }
.autofill-badge-clear {
    background: none; border: none; color: var(--muted); font-size: 10px;
    font-family: var(--font); cursor: pointer; text-decoration: underline;
    padding: 0; font-weight: bold;
}
.autofill-badge-clear:hover { color: var(--red); }

/* Password reveal */
.pwd-wrap input { padding-right: 3rem; }
.pwd-reveal {
    position: absolute; right: .7rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--grey-dark);
    font-family: var(--font); font-size: 10px; font-weight: bold;
    text-transform: uppercase; letter-spacing: .08em; cursor: pointer; padding: 0;
    transition: color .12s;
}
.pwd-reveal:hover { color: var(--navy); }

.pwd-strength-bar   { height: 3px; background: var(--grey-mid); margin-top: .35rem; transition: all .2s; width: 0; }
.pwd-strength-label { font-size: 11px; font-weight: bold; margin-top: .15rem; min-height: 1rem; color: var(--muted); }
.match-hint { font-size: 11px; font-weight: bold; margin-top: .2rem; min-height: 1rem; }
.field-note { font-size: 10px; color: var(--grey-dark); line-height: 1.5; }

/* Error */
.error-alert {
    display: flex; align-items: flex-start; gap: .5rem;
    padding: .65rem .9rem; margin-bottom: .5rem;
    background: var(--red-faint); border: 1px solid rgba(200,16,46,.3);
    border-left: 3px solid var(--red); font-size: 12px; color: var(--red); font-weight: bold;
}
.field-error { font-size: 11px; color: var(--red); font-weight: bold; }

/* Callsign feedback */
.cs-feedback { display: none; align-items: center; gap: .38rem; font-size: .68rem; font-weight: bold; margin-top: .18rem; padding: .26rem .52rem; border: 1px solid; }
.cs-feedback.show { display: flex; }
.cs-feedback.ok  { background: var(--green-bg); border-color: #b8ddc9; color: var(--green); }
.cs-feedback.err { background: var(--red-faint); border-color: rgba(200,16,46,.2); color: var(--red); }
#callsign.cs-valid   { border-color: #16a34a !important; box-shadow: 0 0 0 3px rgba(22,163,74,.1) !important; }
#callsign.cs-invalid { border-color: var(--red) !important; box-shadow: 0 0 0 3px rgba(200,16,46,.08) !important; }
.cs-help { font-size: .67rem; color: var(--muted); margin-top: .18rem; line-height: 1.5; }
.cs-help a { color: var(--navy); font-weight: bold; text-decoration: none; }
.cs-help a:hover { text-decoration: underline; }

/* QRZ card */
@keyframes qrzSpin { to { transform: rotate(360deg); } }

/* Actions */
.reg-actions {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; padding-top: .5rem; flex-wrap: wrap;
}
.login-link {
    font-size: 11px; font-weight: bold; color: var(--navy); text-decoration: none;
    text-transform: uppercase; letter-spacing: .05em;
}
.login-link:hover { text-decoration: underline; }
.btn-register {
    padding: .52rem 1.4rem; border: 1px solid var(--navy); background: var(--navy); color: var(--white);
    font-family: var(--font); font-size: 12px; font-weight: bold; cursor: pointer;
    transition: background .12s, box-shadow .12s; text-transform: uppercase; letter-spacing: .05em;
    display: inline-flex; align-items: center; gap: .4rem;
}
.btn-register:hover { background: var(--navy-mid); box-shadow: 0 4px 12px rgba(0,51,102,.18); }
.btn-register:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

.right-footer {
    padding: 1rem 2.5rem; border-top: 1px solid var(--grey-mid); background: var(--grey);
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; font-size: 11px; color: var(--muted); font-weight: bold;
}
@media (min-width: 820px) { .right-footer { padding: 1rem 3.5rem; } }
.right-footer strong { color: var(--navy); }

@media (max-width: 480px) {
    .right-head { padding: 2rem 1.5rem 0; }
    .right-body { padding: 0 1.5rem; }
    .right-footer { padding: 1rem 1.5rem; }
}
</style>

<div class="page">

    {{-- ── LEFT PANEL ── --}}
    <div class="left">
        <div class="left-inner">
            <div class="brand">
                <div class="rn-logo"><span>RAY<br>NET</span></div>
                <div>
                    <div class="brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                    <div class="brand-sub">Members' Portal</div>
                </div>
            </div>
            <div class="left-hero">
                <div class="left-eyebrow">New member registration</div>
                <div class="left-title">Join the <span>group</span></div>
                <div class="left-desc">
                    Enter your callsign first — we'll look it up on QRZ.com and pre-fill your details automatically.
                </div>
                <div class="left-chips">
                    <span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupName() }}</span>
                    <span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupRegion() }}</span>
                    @if(\App\Helpers\RaynetSetting::groupNumber())<span class="chip"><span class="chip-dot"></span>Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</span>@endif
                </div>
                <div class="left-steps" style="margin-top:2rem;">
                    <div class="left-step done">
                        <div class="step-num">✓</div>
                        <div class="step-body">
                            <div class="step-title">Create account</div>
                            <div class="step-desc">Enter your callsign — QRZ fills in the rest.</div>
                        </div>
                    </div>
                    <div class="left-step">
                        <div class="step-num">2</div>
                        <div class="step-body">
                            <div class="step-title">Verify email</div>
                            <div class="step-desc">Click the link we send to your inbox.</div>
                        </div>
                    </div>
                    <div class="left-step">
                        <div class="step-num">3</div>
                        <div class="step-body">
                            <div class="step-title">Controller approval</div>
                            <div class="step-desc">A Group Controller verifies your callsign via Ofcom records.</div>
                        </div>
                    </div>
                    <div class="left-step">
                        <div class="step-num">4</div>
                        <div class="step-body">
                            <div class="step-title">Access granted</div>
                            <div class="step-desc">Sign in and access the members' hub.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="left-footer">
                Radio Amateurs' Emergency Network<br>
                Access is restricted to registered members only.
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="right">
        <div class="right-head">
            <div class="right-head-title">
                <div>
                    <div class="right-eyebrow">New account</div>
                    <div class="right-title">Create your account</div>
                    <div class="right-sub">Enter your callsign — we'll look it up on QRZ.com and fill in your name and email automatically.</div>
                </div>
                <div class="approval-badge">⏳ Approval required</div>
            </div>
        </div>

        <div class="right-body">
            <form method="POST" action="{{ route('register') }}" class="reg-form" id="regForm">
                @csrf

                @if ($errors->any())
                    <div class="error-alert">
                        <span>⚠</span>
                        <div>@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                    </div>
                @endif

                {{-- ── OPERATOR DETAILS (first) ── --}}
                <div class="section-divider">
                    <span class="section-label">Start Here — Your Callsign</span>
                    <span class="section-rule"></span>
                </div>

                <div class="field">
                    <label for="callsign">Callsign <span class="req">*</span> <small>(becomes your login username once approved)</small></label>
                    <div class="input-wrap">
                        <span class="input-icon">📡</span>
                        <input id="callsign" name="callsign" type="text"
                               value="{{ old('callsign') }}"
                               required autocomplete="off"
                               placeholder="e.g. M0XYZ"
                               maxlength="12"
                               oninput="validateCallsign(this)"
                               class="{{ $errors->has('callsign') ? 'input-error' : '' }}">
                    </div>
                    <div class="cs-feedback ok" id="cs-ok">✓ Valid callsign format</div>
                    <div class="cs-feedback err" id="cs-err">✕ <span id="cs-err-msg">Not a recognised amateur radio callsign</span></div>
                    @error('callsign')<div class="field-error">{{ $message }}</div>@enderror
                    <div class="cs-help">Format: prefix + number + suffix — e.g. G4BDS, M0ABC, 2E0XYZ. <a href="https://www.ofcom.org.uk/manage-your-licence/radiocommunication-licences/amateur-radio" target="_blank" rel="noopener">Ofcom ↗</a></div>

                    {{-- QRZ lookup result (same structure as profile page) --}}
                    <div id="qrzCard" style="display:none; margin-top:.5rem;">

                        <div id="qrzLoading" style="display:none;align-items:center;gap:.5rem;padding:.6rem .85rem;background:var(--grey);border:1px solid var(--grey-mid);font-size:.74rem;color:var(--muted);">
                            <span style="display:inline-block;width:13px;height:13px;border:2px solid var(--grey-mid);border-top-color:var(--navy);border-radius:50%;animation:qrzSpin .7s linear infinite;flex-shrink:0;"></span>
                            Looking up on QRZ.com…
                        </div>

                        <div id="qrzNotFound" style="display:none;border:1.5px solid #fca5a5;">
                            <div style="background:#fef2f2;padding:.7rem .95rem;display:flex;align-items:flex-start;gap:.55rem;">
                                <span style="font-size:1.1rem;flex-shrink:0;">⚠</span>
                                <div>
                                    <div style="font-size:.84rem;font-weight:bold;color:#b91c1c;margin-bottom:.22rem;">Callsign not found on QRZ.com</div>
                                    <div style="font-size:.76rem;color:#7f1d1d;line-height:1.55;">
                                        We couldn't find <strong id="qrzMissingCallsign" style="font-family:monospace;letter-spacing:.05em;"></strong> on QRZ.
                                        You can still register — just fill in your name and email manually.
                                        A Group Controller will verify your callsign via Ofcom records.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="qrzResult" style="display:none;border:1.5px solid #b8ddc9;box-shadow:0 2px 10px rgba(0,51,102,.08);">
                            <div style="background:var(--navy);padding:.65rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                <div style="display:flex;align-items:center;gap:.55rem;">
                                    <span style="font-size:.58rem;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.45);">✓ QRZ Verified</span>
                                    <span id="qrzCallsignBadge" style="font-family:monospace;font-size:.9rem;font-weight:bold;color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);padding:.1rem .55rem;letter-spacing:.1em;"></span>
                                </div>
                                <a id="qrzLink" href="#" target="_blank" rel="noopener"
                                   style="font-size:.64rem;font-weight:bold;color:rgba(255,255,255,.5);text-decoration:none;border-bottom:1px solid rgba(255,255,255,.2);">qrz.com ↗</a>
                            </div>
                            <div style="background:white;padding:.85rem 1rem;display:flex;align-items:flex-start;gap:.85rem;">
                                <div id="qrzAvatar" style="width:52px;height:52px;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:1.15rem;font-weight:bold;color:#fff;flex-shrink:0;overflow:hidden;border:2px solid var(--grey-mid);"></div>
                                <div style="flex:1;min-width:0;">
                                    <div id="qrzName" style="font-size:.95rem;font-weight:bold;color:var(--text);line-height:1.2;margin-bottom:.22rem;"></div>
                                    <div id="qrzLocation" style="font-size:.75rem;color:var(--muted);margin-bottom:.38rem;"></div>
                                    <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;">
                                        <span id="qrzLicenceBadge" style="display:none;font-size:.63rem;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;padding:.14rem .5rem;background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);"></span>
                                        <span id="qrzGridBadge"    style="display:none;font-size:.63rem;font-weight:bold;font-family:monospace;padding:.14rem .5rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);color:var(--navy);"></span>
                                    </div>
                                    <div style="margin-top:.55rem;font-size:.72rem;color:var(--green);font-weight:bold;">✓ Name and email pre-filled from QRZ — check and confirm below.</div>
                                </div>
                            </div>
                        </div>

                        <div id="qrzError" style="display:none;padding:.5rem .85rem;background:var(--grey);border:1px solid var(--grey-mid);font-size:.71rem;color:var(--muted);">
                            ⚠ QRZ lookup unavailable — <span id="qrzErrorDetail">could not reach the lookup service</span>.
                        </div>
                    </div>
                </div>

                {{-- ── PERSONAL DETAILS (auto-filled from QRZ) ── --}}
                <div class="section-divider" style="margin-top:.25rem;">
                    <span class="section-label">Personal Details</span>
                    <span class="section-rule"></span>
                </div>

                <div class="field">
                    <label for="name">Full name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input id="name" name="name" type="text"
                               value="{{ old('name') }}"
                               required autofocus autocomplete="name"
                               placeholder="Waiting for callsign lookup…"
                               class="{{ $errors->has('name') ? 'input-error' : '' }}">
                    </div>
                    <div class="autofill-badge" id="nameBadge">
                        📡 Pre-filled from QRZ.com &nbsp;
                        <button type="button" class="autofill-badge-clear" onclick="clearAutofill('name')">Clear</button>
                    </div>
                    @error('name')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="email">Email address <span class="req">*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">✉</span>
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               required autocomplete="username"
                               placeholder="Waiting for callsign lookup…"
                               class="{{ $errors->has('email') ? 'input-error' : '' }}">
                    </div>
                    <div class="autofill-badge" id="emailBadge">
                        📡 Pre-filled from QRZ.com &nbsp;
                        <button type="button" class="autofill-badge-clear" onclick="clearAutofill('email')">Clear</button>
                    </div>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                {{-- ── SECURITY ── --}}
                <div class="section-divider" style="margin-top:.25rem;">
                    <span class="section-label">Security</span>
                    <span class="section-rule"></span>
                </div>

                <div class="field">
                    <label for="password">Password <span class="req">*</span></label>
                    <div class="input-wrap pwd-wrap">
                        <span class="input-icon">🔒</span>
                        <input id="password" name="password" type="password"
                               required autocomplete="new-password"
                               placeholder="Minimum 8 characters"
                               oninput="checkStrength(this.value)"
                               class="{{ $errors->has('password') ? 'input-error' : '' }}">
                        <button type="button" class="pwd-reveal" id="pwdToggle"
                                onclick="togglePwd('password','pwdToggle')">Show</button>
                    </div>
                    <div class="pwd-strength-bar" id="strengthBar"></div>
                    <div class="pwd-strength-label" id="strengthLabel"></div>
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm password <span class="req">*</span></label>
                    <div class="input-wrap pwd-wrap">
                        <span class="input-icon">🔒</span>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               required autocomplete="new-password"
                               placeholder="Repeat your password"
                               oninput="checkMatch()">
                        <button type="button" class="pwd-reveal"
                                onclick="togglePwd('password_confirmation', this)">Show</button>
                    </div>
                    <div class="match-hint" id="matchHint"></div>
                </div>

                <div class="reg-actions">
                    <a href="{{ route('login') }}" class="login-link">Already registered? Sign in →</a>
                    <button type="submit" class="btn-register" id="submitBtn">Create Account →</button>
                </div>

            </form>
        </div>

        <div class="right-footer">
            <span>🔒 Account access is approved by a <strong>Group Controller</strong></span>
            <span>Contact your controller if you need assistance</span>
        </div>
    </div>

</div>

<script>
/* ── Password strength & match ── */
function checkStrength(val) {
    const bar = document.getElementById('strengthBar'), label = document.getElementById('strengthLabel');
    if (!val) { bar.style.width = '0'; label.textContent = ''; return; }
    let s = 0;
    if (val.length >= 8)          s++;
    if (val.length >= 12)         s++;
    if (/[A-Z]/.test(val))        s++;
    if (/[0-9]/.test(val))        s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const lvls = [
        { w:'20%', c:'#C8102E', t:'Very weak' },
        { w:'40%', c:'#f97316', t:'Weak' },
        { w:'60%', c:'#f59e0b', t:'Fair' },
        { w:'80%', c:'#84cc16', t:'Good' },
        { w:'100%',c:'#1a6b3c', t:'Strong' },
    ];
    const l = lvls[Math.min(s - 1, 4)] || lvls[0];
    bar.style.width = l.w; bar.style.background = l.c;
    label.textContent = l.t; label.style.color = l.c;
    checkMatch();
}
function checkMatch() {
    const p = document.getElementById('password').value;
    const c = document.getElementById('password_confirmation').value;
    const h = document.getElementById('matchHint');
    if (!c) { h.textContent = ''; return; }
    if (p === c) { h.textContent = '✓ Passwords match'; h.style.color = 'var(--green)'; }
    else         { h.textContent = '✗ Passwords do not match'; h.style.color = 'var(--red)'; }
}
function togglePwd(inputId, btn) {
    const inp = document.getElementById(inputId);
    const el  = typeof btn === 'string' ? document.getElementById(btn) : btn;
    if (!inp || !el) return;
    if (inp.type === 'password') { inp.type = 'text';     el.textContent = 'Hide'; }
    else                         { inp.type = 'password'; el.textContent = 'Show'; }
}

/* ── Callsign validation ── */
const PATTERNS = [
    /^G[MWIDGJUC]?[0-9][A-Z]{2,3}$/,
    /^M[MWIDGJUC]?[0-9][A-Z]{2,3}$/,
    /^2[EWMID][0-9][A-Z]{2,3}$/,
    /^[0-9]?[A-Z]{1,2}[0-9]{1,2}[A-Z]{1,4}$/
];

window.validateCallsign = function(input) {
    const raw = input.value.trim(), upper = raw.toUpperCase();
    if (raw !== upper) { const p = input.selectionStart; input.value = upper; try { input.setSelectionRange(p,p); } catch(e){} }
    const okEl = document.getElementById('cs-ok'), errEl = document.getElementById('cs-err'), msgEl = document.getElementById('cs-err-msg');
    if (!upper) {
        input.classList.remove('cs-valid','cs-invalid');
        okEl.classList.remove('show'); errEl.classList.remove('show');
        qrzHide(); updatePlaceholders(false); return;
    }
    function fail(msg) {
        input.classList.remove('cs-valid'); input.classList.add('cs-invalid');
        okEl.classList.remove('show'); msgEl.textContent = msg; errEl.classList.add('show');
        qrzHide(); updatePlaceholders(false);
    }
    function pass() {
        input.classList.remove('cs-invalid'); input.classList.add('cs-valid');
        okEl.classList.add('show'); errEl.classList.remove('show');
        clearTimeout(qrzTimer);
        qrzTimer = setTimeout(() => qrzFetch(upper), 600);
    }
    if (upper.length < 3)              return fail('Too short — callsigns are at least 3 characters');
    if (/[^A-Z0-9]/.test(upper))       return fail('Letters and numbers only — no spaces or symbols');
    if (!/[A-Z]/.test(upper))          return fail('Callsigns must contain letters');
    if (!/[0-9]/.test(upper))          return fail('Callsigns must contain a district number');
    if (!PATTERNS.some(re=>re.test(upper))) return fail('Not a recognised format — e.g. G4BDS, M0ABC, 2E0XYZ');
    pass();
};

/* ── QRZ lookup & autofill ── */
(function() {
    let qrzTimer  = null;
    let qrzLast   = '';
    let qrzActive = false;

    const qrzCard     = document.getElementById('qrzCard');
    const qrzLoading  = document.getElementById('qrzLoading');
    const qrzNotFound = document.getElementById('qrzNotFound');
    const qrzResult   = document.getElementById('qrzResult');
    const qrzError    = document.getElementById('qrzError');

    /* Track which fields were autofilled so Clear works */
    const autofilled = { name: false, email: false };

    /* Make qrzTimer and qrzFetch accessible to validateCallsign above */
    window.qrzTimer = null;

    function qrzShow(el) {
        [qrzLoading, qrzNotFound, qrzResult, qrzError].forEach(e => { if (e) e.style.display = 'none'; });
        if (!el) return;
        el.style.display = (el === qrzLoading) ? 'flex' : 'block';
    }

    window.qrzHide = function() {
        if (qrzCard) qrzCard.style.display = 'none';
    };

    window.updatePlaceholders = function(looking) {
        const nameEl  = document.getElementById('name');
        const emailEl = document.getElementById('email');
        if (looking) {
            if (nameEl  && !autofilled.name)  nameEl.placeholder  = 'Waiting for callsign lookup…';
            if (emailEl && !autofilled.email) emailEl.placeholder = 'Waiting for callsign lookup…';
        } else {
            if (nameEl  && !autofilled.name)  nameEl.placeholder  = 'e.g. Ian Jones';
            if (emailEl && !autofilled.email) emailEl.placeholder = 'you@example.com';
        }
    };

    function autofill(field, value) {
        if (!value) return;
        const el = document.getElementById(field);
        const badge = document.getElementById(field + 'Badge');
        if (!el) return;
        el.value = value;
        el.classList.add('input-autofilled');
        autofilled[field] = true;
        if (badge) badge.classList.add('show');
    }

    window.clearAutofill = function(field) {
        const el = document.getElementById(field);
        const badge = document.getElementById(field + 'Badge');
        if (el) { el.value = ''; el.classList.remove('input-autofilled'); el.focus(); }
        autofilled[field] = false;
        if (badge) badge.classList.remove('show');
    };

    function clearAllAutofills() {
        ['name', 'email'].forEach(function(f) {
            const el    = document.getElementById(f);
            const badge = document.getElementById(f + 'Badge');
            if (el)    { el.classList.remove('input-autofilled'); }
            if (badge)  badge.classList.remove('show');
            autofilled[f] = false;
        });
    }

    function qrzRender(data) {
        /* ── Callsign badge & link ── */
        const badge = document.getElementById('qrzCallsignBadge');
        if (badge) badge.textContent = data.callsign || '';

        const linkEl = document.getElementById('qrzLink');
        if (linkEl && data.callsign) linkEl.href = 'https://www.qrz.com/db/' + encodeURIComponent(data.callsign);

        /* ── Licence + grid badges ── */
        const lic = document.getElementById('qrzLicenceBadge');
        if (lic) { lic.textContent = data.licence_class || ''; lic.style.display = data.licence_class ? 'inline-block' : 'none'; }

        const grid = document.getElementById('qrzGridBadge');
        if (grid) { grid.textContent = data.grid ? 'Grid ' + data.grid : ''; grid.style.display = data.grid ? 'inline-block' : 'none'; }

        /* ── Name & location ── */
        const nameEl = document.getElementById('qrzName');
        if (nameEl) nameEl.textContent = data.name || data.callsign || '';

        const locEl = document.getElementById('qrzLocation');
        if (locEl) locEl.textContent = data.location || '';

        /* ── Avatar ── */
        const avatarEl = document.getElementById('qrzAvatar');
        if (avatarEl) {
            if (data.image_url) {
                const img = document.createElement('img');
                img.src = data.image_url;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                img.onerror = () => { avatarEl.textContent = '📡'; };
                avatarEl.innerHTML = '';
                avatarEl.appendChild(img);
            } else {
                const ini = (data.name || data.callsign || '?').split(' ').map(w => w[0]||'').join('').slice(0,2).toUpperCase();
                avatarEl.textContent = ini || '📡';
            }
        }

        qrzShow(qrzResult);

        /* ── Autofill name ── */
        const nameInput = document.getElementById('name');
        if (data.name && nameInput && !nameInput.value.trim()) {
            autofill('name', data.name);
        }

        /* ── Autofill email ── */
        const emailInput = document.getElementById('email');
        if (data.email && emailInput && !emailInput.value.trim()) {
            autofill('email', data.email);
        }
    }

    window.qrzFetch = async function(callsign) {
        if (!qrzCard || !callsign) return;
        if (callsign === qrzLast && qrzResult && qrzResult.style.display !== 'none') return;
        qrzLast = callsign; qrzActive = true;
        qrzCard.style.display = 'block';
        clearAllAutofills();
        updatePlaceholders(true);
        qrzShow(qrzLoading);
        const detailEl = document.getElementById('qrzErrorDetail');
        try {
            const res = await fetch('/committee/profile/qrz-lookup/' + encodeURIComponent(callsign), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                if (detailEl) detailEl.textContent = 'server returned HTTP ' + res.status;
                qrzShow(qrzError); qrzActive = false; return;
            }
            const json = await res.json();
            if (!qrzActive || document.getElementById('callsign').value.trim().toUpperCase() !== callsign) return;
            if (res.status === 422) {
                qrzHide();
            } else if (json.found && json.data) {
                qrzRender(json.data);
            } else if (json.service_error || json.error || json.exception) {
                if (detailEl) detailEl.textContent = json.reason || json.message || json.error || 'QRZ service error';
                qrzShow(qrzError);
            } else if (json.found === false || json.found === 0) {
                const missing = document.getElementById('qrzMissingCallsign');
                if (missing) missing.textContent = callsign;
                qrzShow(qrzNotFound);
                updatePlaceholders(false);
            } else {
                if (detailEl) detailEl.textContent = 'unexpected response';
                qrzShow(qrzError);
            }
        } catch(err) {
            if (detailEl) detailEl.textContent = err.message || 'network error';
            qrzShow(qrzError);
        } finally {
            qrzActive = false;
        }
    };

    /* Expose timer for validateCallsign to clear/set */
    Object.defineProperty(window, 'qrzTimer', {
        get: function()  { return qrzTimer; },
        set: function(v) { qrzTimer = v; }
    });

    /* Re-run on load if old() value present */
    const existing = document.getElementById('callsign');
    if (existing && existing.value.trim()) {
        validateCallsign(existing);
    }
})();
</script>

@endsection