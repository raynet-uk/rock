@extends('layouts.app')
@section('title', 'Member Login')
@section('content')

@php
    $intendedUrl      = session('url.intended', '');
    $isVerifyRedirect = str_contains($intendedUrl, '/email/verify/');
    $fromM0kkn        = ($from ?? session('login_from')) === 'm0kkn';
@endphp

<style>
:root {
    --navy:       #003366;
    --navy-mid:   #004080;
    --navy-faint: #e8eef5;
    --red:        #C8102E;
    --green:      #1a6b3c;
    --green-bg:   #eef7f2;
    --amber:      #8a5500;
    --amber-bg:   #fdf8ec;
    --grey:       #f2f5f9;
    --grey-mid:   #dde2e8;
    --grey-dark:  #9aa3ae;
    --white:      #fff;
    --text:       #001f40;
    --muted:      #6b7f96;
    --font:       Arial,"Helvetica Neue",Helvetica,sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body { font-family: var(--font); font-size: 14px; color: var(--text); background: var(--navy); min-height: 100vh; }

.page { display: flex; min-height: 100vh; flex-direction: column; }
@media (min-width: 820px) { .page { flex-direction: row; } }

/* ── LEFT ── */
.left {
    background: var(--navy); padding: 2.5rem 2rem;
    display: flex; flex-direction: column;
    position: relative; overflow: hidden; flex-shrink: 0;
}
.left::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(-45deg,transparent,transparent 20px,rgba(255,255,255,.018) 20px,rgba(255,255,255,.018) 21px);
    pointer-events: none;
}
.left::after {
    content: ''; position: absolute; bottom: -20%; right: -15%;
    width: 60%; padding-top: 60%; border-radius: 50%;
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
.left-status { margin-top: 2rem; padding: .75rem 1rem; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); border-left: 3px solid rgba(255,255,255,.15); display: flex; align-items: center; gap: .65rem; }
.left-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(74,222,128,.2); animation: pulse 2.5s ease infinite; }
@keyframes pulse { 0%,100% { box-shadow: 0 0 0 3px rgba(74,222,128,.2); } 50% { box-shadow: 0 0 0 6px rgba(74,222,128,.05); } }
.left-status-text { font-size: .75rem; color: rgba(255,255,255,.45); }
.left-status-text strong { color: rgba(255,255,255,.7); }
.left-footer { margin-top: auto; padding-top: 2rem; font-size: .7rem; color: rgba(255,255,255,.2); line-height: 1.6; }

/* ── RIGHT — IS the white surface ── */
.right { background: var(--white); flex: 1; display: flex; flex-direction: column; border-left: 4px solid var(--red); }

.right-head { padding: 2.5rem 2.5rem 0; border-bottom: 1px solid var(--grey-mid); background: var(--grey); }
@media (min-width: 820px) { .right-head { padding: 3rem 3.5rem 0; } }

.notice { display: flex; align-items: flex-start; gap: .75rem; padding: .85rem 1rem; margin-bottom: 1.25rem; animation: slideDown .3s ease; }
.notice-amber { background: var(--amber-bg); border: 1px solid #f5d87a; border-left: 3px solid #c49a00; }
.notice-green { background: var(--green-bg); border: 1px solid #b8ddc9; border-left: 3px solid var(--green); }
.notice-icon  { font-size: 1rem; flex-shrink: 0; margin-top: .1rem; }
.notice-title { font-size: .72rem; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; margin-bottom: .2rem; }
.notice-amber .notice-title { color: var(--amber); }
.notice-green .notice-title { color: var(--green); }
.notice-body  { font-size: .78rem; color: var(--muted); line-height: 1.6; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

.right-head-title { display: flex; align-items: center; justify-content: space-between; padding-bottom: 1.25rem; gap: 1rem; }
.right-eyebrow { font-size: .68rem; font-weight: bold; text-transform: uppercase; letter-spacing: .16em; color: var(--red); margin-bottom: .3rem; display: flex; align-items: center; gap: .35rem; }
.right-eyebrow::before { content: ''; width: 12px; height: 2px; background: var(--red); display: inline-block; }
.right-title { font-size: 1.2rem; font-weight: bold; color: var(--navy); }
.right-sub   { font-size: .78rem; color: var(--muted); margin-top: .15rem; }
.secure-badge { display: inline-flex; align-items: center; gap: .3rem; padding: 3px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em; background: var(--white); border: 1px solid var(--grey-mid); color: var(--muted); flex-shrink: 0; }

.method-bar { display: flex; border-bottom: 2px solid var(--grey-mid); overflow-x: auto; background: var(--grey); padding: 0 2.5rem; }
@media (min-width: 820px) { .method-bar { padding: 0 3.5rem; } }
.method-btn {
    padding: .75rem 1.1rem; background: transparent; border: none;
    border-bottom: 3px solid transparent; color: var(--muted);
    font-family: var(--font); font-size: 11px; font-weight: bold;
    text-transform: uppercase; letter-spacing: .06em;
    cursor: pointer; transition: all .15s;
    display: flex; align-items: center; gap: .4rem;
    margin-bottom: -2px; white-space: nowrap;
}
.method-btn:hover { color: var(--navy); }
.method-btn.active { color: var(--navy); border-bottom-color: var(--red); }
.method-soon { font-size: 9px; padding: 1px 5px; background: var(--grey-mid); border: 1px solid var(--grey-mid); color: var(--grey-dark); font-weight: bold; text-transform: uppercase; letter-spacing: .05em; }

.right-body { flex: 1; padding: 0 2.5rem; }
@media (min-width: 820px) { .right-body { padding: 0 3.5rem; } }

.method-pane { display: none; padding: 2rem 0 1rem; max-width: 480px; }
.method-pane.active { display: block; animation: fadeUp .2s ease; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

/* ── Shared fields ── */
.field { margin-bottom: 1rem; display: flex; flex-direction: column; gap: .3rem; }
.field label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
.field label small { text-transform: none; letter-spacing: 0; color: var(--grey-dark); font-weight: normal; font-size: 10px; }
.input-wrap { position: relative; }
.input-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); font-size: .85rem; color: var(--muted); pointer-events: none; }
.field input[type="text"],
.field input[type="email"],
.field input[type="password"] {
    width: 100%; padding: .52rem .75rem .52rem 2.1rem;
    border: 1px solid var(--grey-mid); background: var(--white); color: var(--text);
    font-family: var(--font); font-size: 13px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.field input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.pwd-toggle { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); background: none; border: none; font-family: var(--font); font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); cursor: pointer; padding: 0; }
.pwd-toggle:hover { color: var(--navy); }
.remember-row {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .65rem; background: var(--grey); border: 1px solid var(--grey-mid);
    font-size: 12px; font-weight: bold; color: var(--muted);
    cursor: pointer; margin-bottom: 1.1rem; user-select: none; transition: border-color .15s;
}
.remember-row:hover { border-color: var(--navy); }
.remember-row input[type="checkbox"] { width: 14px; height: 14px; accent-color: var(--navy); cursor: pointer; flex-shrink: 0; }
.error-alert { display: flex; align-items: center; gap: .6rem; padding: .65rem 1rem; margin-bottom: 1rem; background: #fdf0f2; border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red); color: var(--red); font-size: 12px; font-weight: bold; }
.btn-submit {
    padding: .52rem 1.1rem; background: var(--navy); color: var(--white);
    border: 1px solid var(--navy); font-family: var(--font);
    font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em;
    cursor: pointer; transition: background .12s, box-shadow .12s;
    display: inline-flex; align-items: center; gap: .4rem;
}
.btn-submit:hover { background: var(--navy-mid); box-shadow: 0 4px 12px rgba(0,51,102,.18); }
.btn-submit:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }
.btn-full { width: 100%; justify-content: center; }

/* ── MAGIC CODE — PIN input (Uber design principle) ── */

/* Step wrapper with slide transition */
.code-steps { position: relative; overflow: hidden; }
.code-step { display: none; }
.code-step.active { display: block; animation: fadeUp .25s ease; }

/* Step 1 — request form */
.code-destination-desc {
    font-size: 13px; color: var(--muted); line-height: 1.65;
    margin-bottom: 1.25rem;
    padding: .75rem 1rem;
    background: var(--navy-faint);
    border-left: 3px solid var(--navy);
}
.code-destination-desc strong { color: var(--navy); }

/* Step 2 — PIN entry */
.pin-header { margin-bottom: 1.75rem; }
.pin-sent-to {
    display: flex; align-items: center; gap: .6rem;
    padding: .65rem .9rem;
    background: var(--green-bg); border: 1px solid #b8ddc9; border-left: 3px solid var(--green);
    margin-bottom: 1.5rem;
}
.pin-sent-icon { font-size: 1rem; flex-shrink: 0; }
.pin-sent-label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em; color: var(--green); }
.pin-sent-email { font-size: 12px; color: var(--muted); margin-top: 1px; font-weight: bold; }

/* The 6 PIN boxes — core Uber pattern */
.pin-label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: .65rem; }
.pin-inputs {
    display: flex; gap: .5rem; align-items: center;
}
.pin-sep {
    width: 12px; height: 2px; background: var(--grey-mid); flex-shrink: 0;
    margin: 0 .1rem;
}
.pin-box {
    width: 52px; height: 60px;
    border: 2px solid var(--grey-mid);
    background: var(--white);
    font-family: var(--font);
    font-size: 1.6rem; font-weight: bold;
    color: var(--navy);
    text-align: center;
    outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
    caret-color: transparent;
    -moz-appearance: textfield;
    cursor: text;
}
.pin-box::-webkit-outer-spin-button,
.pin-box::-webkit-inner-spin-button { -webkit-appearance: none; }
.pin-box::placeholder { color: var(--grey-mid); font-size: 1.2rem; font-weight: normal; }
.pin-box:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(0,51,102,.1);
    background: var(--navy-faint);
}
.pin-box.filled { border-color: var(--navy); background: var(--white); }
.pin-box.error  { border-color: var(--red) !important; box-shadow: 0 0 0 3px rgba(200,16,46,.1) !important; background: #fdf0f2 !important; }
.pin-box.success { border-color: var(--green) !important; background: var(--green-bg) !important; }

/* PIN status messages */
.pin-status { min-height: 1.4rem; margin-top: .75rem; font-size: 12px; font-weight: bold; display: flex; align-items: center; gap: .4rem; }
.pin-status.error   { color: var(--red); }
.pin-status.success { color: var(--green); }
.pin-status.info    { color: var(--muted); }

/* Countdown / resend row */
.pin-resend-row {
    margin-top: 1.5rem; padding-top: 1.25rem;
    border-top: 1px solid var(--grey-mid);
    display: flex; align-items: center; justify-content: space-between;
    gap: .75rem; flex-wrap: wrap;
}
.pin-countdown { font-size: 11px; color: var(--muted); font-weight: bold; }
.pin-countdown span { color: var(--navy); font-variant-numeric: tabular-nums; }
.btn-resend {
    background: none; border: none; font-family: var(--font);
    font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    color: var(--navy); cursor: pointer; padding: 0;
    border-bottom: 1px solid rgba(0,51,102,.25); padding-bottom: 1px;
    transition: opacity .12s;
}
.btn-resend:hover { opacity: .65; }
.btn-resend:disabled { opacity: .35; cursor: not-allowed; }

/* Change address link */
.btn-change-address {
    background: none; border: none; font-family: var(--font);
    font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    color: var(--muted); cursor: pointer; padding: 0; margin-top: 1rem;
    display: flex; align-items: center; gap: .3rem;
    transition: color .12s;
}
.btn-change-address:hover { color: var(--navy); }

/* Verify button with loading state */
.btn-verify-wrap { margin-top: 1.25rem; }

/* SSO coming soon */
.coming-soon { padding: 3rem 0; display: flex; flex-direction: column; gap: .75rem; }
.coming-soon-icon { width: 44px; height: 44px; background: var(--grey); border: 1px solid var(--grey-mid); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.coming-soon-title { font-size: 13px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .05em; }
.coming-soon-sub { font-size: 12px; color: var(--muted); line-height: 1.65; max-width: 300px; }

.right-footer {
    padding: 1rem 2.5rem; border-top: 1px solid var(--grey-mid); background: var(--grey);
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; font-size: 11px; color: var(--muted); font-weight: bold;
}
@media (min-width: 820px) { .right-footer { padding: 1rem 3.5rem; } }
.right-footer a { color: var(--navy); text-decoration: none; }
.right-footer a:hover { text-decoration: underline; }

@media (max-width: 500px) {
    .pin-box { width: 42px; height: 52px; font-size: 1.35rem; }
    .pin-inputs { gap: .35rem; }
    .pin-sep { width: 8px; }
}
</style>

<div class="page">

    {{-- ── LEFT ── --}}
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
                @if($fromM0kkn)
                <div class="left-eyebrow" style="color:#90caf9;">📡 M0KKN Dashboard</div>
                <div class="left-title">Sign in to<br><span>M0KKN</span></div>
                <div class="left-desc">You came from the M0KKN repeater dashboard. Sign in below and you'll be returned there automatically.</div>
            @else
                <div class="left-eyebrow">Secure member access</div>
                <div class="left-title">Welcome <span>back</span></div>
                <div class="left-desc">
                    Access your operator profile, activity log, upcoming events, and group communications — all in one place.
                </div>
            @endif
                <div class="left-chips">
                    <span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupName() }}</span>
                    <span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupRegion() }}</span>
                    @if(\App\Helpers\RaynetSetting::groupNumber())<span class="chip"><span class="chip-dot"></span>Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</span>@endif
                </div>
                <div class="left-status">
                    <div class="left-status-dot"></div>
                    <div class="left-status-text">Portal is <strong>online</strong> — all systems operational</div>
                </div>
            </div>
            <div class="left-footer">
                Radio Amateurs' Emergency Network<br>
                Access is restricted to registered members only.<br>
                All sessions are logged for security purposes.
            </div>
        </div>
    </div>

    {{-- ── RIGHT — is the white surface ── --}}
    <div class="right">

        <div class="right-head">
            @if(session('suspended_login'))
                @php $suspendReason = session('suspended_login')['reason'] ?? null; @endphp
                <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.9rem 1rem;margin-bottom:1.25rem;
                            background:#fff5f7;border:1px solid rgba(200,16,46,.25);border-left:3px solid var(--red);
                            animation:slideDown .3s ease;">
                    <div style="width:32px;height:32px;background:var(--red);display:flex;align-items:center;
                                justify-content:center;font-size:14px;flex-shrink:0;margin-top:1px;">🚫</div>
                    <div>
                        <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;
                                    color:var(--red);margin-bottom:.25rem;">Account Suspended</div>
                        @if($suspendReason)
                            <div style="font-size:13px;font-weight:bold;color:var(--navy);margin-bottom:.25rem;line-height:1.4;">
                                {{ $suspendReason }}
                            </div>
                        @endif
                        <div style="font-size:12px;color:var(--muted);line-height:1.6;">
                            Sign-in is not permitted. Contact the Group Controller if you believe this is an error.
                        </div>
                    </div>
                </div>
            @endif

            @if ($isVerifyRedirect)
                <div class="notice notice-amber">
                    <div class="notice-icon">✉️</div>
                    <div>
                        <div class="notice-title">Log in to verify your email</div>
                        <div class="notice-body">You clicked a verification link. Sign in below and your email will be confirmed automatically.</div>
                    </div>
                </div>
            @endif

            @if (session('verified_notice'))
                <div class="notice notice-green">
                    <div class="notice-icon">✓</div>
                    <div>
                        <div class="notice-title">Email verified</div>
                        <div class="notice-body">{{ session('verified_notice') }}</div>
                    </div>
                </div>
            @endif

            <div class="right-head-title">
                <div>
                    <div class="right-eyebrow">Member sign-in</div>
                    <div class="right-title">{{ \App\Helpers\RaynetSetting::groupName() }} Portal</div>
                    <div class="right-sub">Authorised members only — all activity logged</div>
                </div>
                <div class="secure-badge">🔒 Secure</div>
            </div>
        </div>

        <div class="method-bar">
            <button class="method-btn active" data-method="password" type="button">🔑 Password</button>
            <button class="method-btn" data-method="email_code" type="button">✉ Magic code</button>
            <button class="method-btn" data-method="sso" type="button">🔗 SSO <span class="method-soon">Soon</span></button>
        </div>

        <div class="right-body">

            {{-- ── METHOD: Password ── --}}
            <div class="method-pane active" id="method-password">

                @if ($errors->any() && old('_method_used') === 'password')
                    <div class="error-alert">✕ {{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="_method_used" value="password">

                    <div class="field">
                        <label for="login">Email or callsign <small>(not case-sensitive)</small></label>
                        <div class="input-wrap">
                            <span class="input-icon">✉</span>
                            <input id="login" name="login" type="text"
                                   autocomplete="username"
                                   value="{{ old('login') }}"
                                   required autofocus
                                   placeholder="e.g. M0XYZ or name@example.com">
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input id="password" name="password" type="password"
                                   autocomplete="current-password"
                                   required placeholder="••••••••••">
                            <button type="button" class="pwd-toggle" id="pwdToggle"
                                    onclick="togglePwd()">show</button>
                        </div>
                    </div>

                    <label class="remember-row">
                        <input type="checkbox" name="remember">
                        Remember me on this device
                    </label>

                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <a href="{{ route('password.request') }}"
                           style="font-size:11px;font-weight:bold;color:var(--muted);text-decoration:none;text-transform:uppercase;letter-spacing:.05em;transition:color .12s;"
                           onmouseover="this.style.color='var(--navy)'"
                           onmouseout="this.style.color='var(--muted)'">
                            Forgot password?
                        </a>
                        <button type="submit" class="btn-submit">Sign in →</button>
                    </div>
                </form>
            </div>{{-- /method-password --}}


            {{-- ── METHOD: Magic code ── --}}
            <div class="method-pane" id="method-email_code">

                @if ($errors->any() && old('_method_used') === 'email_code')
                    <div class="error-alert">✕ {{ $errors->first() }}</div>
                @endif

                <div class="code-steps">

                    {{-- Step 1 — enter email / callsign --}}
                    <div class="code-step active" id="code-step-1">

                        <div class="code-destination-desc">
                            Enter your <strong>email address or callsign</strong> and we'll send a 6-digit code to your registered email. No password needed.
                        </div>

                        <form id="codeRequestForm">
                            @csrf

                            <div class="field">
                                <label for="code_login">Email or callsign</label>
                                <div class="input-wrap">
                                    <span class="input-icon">✉</span>
                                    <input id="code_login" name="code_login" type="text"
                                           autocomplete="username"
                                           placeholder="e.g. M0XYZ or name@example.com"
                                           required>
                                </div>
                            </div>

                            <div id="codeRequestError" class="error-alert" style="display:none;"></div>

                            <button type="submit" class="btn-submit btn-full" id="btnSendCode">
                                Send code →
                            </button>
                        </form>
                    </div>

                    {{-- Step 2 — enter PIN --}}
                    <div class="code-step" id="code-step-2">

                        <div class="pin-sent-to">
                            <div class="pin-sent-icon">✉️</div>
                            <div>
                                <div class="pin-sent-label">Code sent</div>
                                <div class="pin-sent-email" id="pinSentEmail">—</div>
                            </div>
                        </div>

                        <form id="codeVerifyForm" method="POST" action="{{ route('login.code.verify') }}">
                            @csrf
                            <input type="hidden" name="_method_used" value="email_code">
                            <input type="hidden" name="code_login" id="hiddenCodeLogin">
                            <input type="hidden" name="code" id="hiddenCode">

                            <div class="pin-label">Enter your 6-digit code <span style="font-size:10px;color:var(--grey-dark);font-weight:normal;text-transform:none;letter-spacing:0;">(you can paste the whole code)</span></div>

                            <div class="pin-inputs" id="pinInputs" role="group" aria-label="6-digit verification code">
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="one-time-code" data-index="0">
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="off" data-index="1">
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="off" data-index="2">
                                <div class="pin-sep"></div>
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="off" data-index="3">
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="off" data-index="4">
                                <input class="pin-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                                       placeholder="·" autocomplete="off" data-index="5">
                            </div>

                            <div class="pin-status info" id="pinStatus"></div>

                            <div class="pin-resend-row">
                                <div class="pin-countdown">
                                    Resend in <span id="countdownTimer">10:00</span>
                                </div>
                                <button type="button" class="btn-resend" id="btnResend" disabled
                                        onclick="resendCode()">Resend code</button>
                            </div>

                            <div class="btn-verify-wrap">
                                <button type="submit" class="btn-submit btn-full" id="btnVerify" disabled>
                                    Verify code →
                                </button>
                            </div>

                        </form>

                        <button type="button" class="btn-change-address" onclick="backToStep1()">
                            ← Use a different address
                        </button>

                    </div>{{-- /code-step-2 --}}
                </div>
            </div>{{-- /method-email_code --}}


            {{-- ── METHOD: SSO ── --}}
            <div class="method-pane" id="method-sso">
                <div class="coming-soon">
                    <div class="coming-soon-icon">🔗</div>
                    <div class="coming-soon-title">Single sign-on</div>
                    <div class="coming-soon-sub">Sign in with Google, Microsoft, or another identity provider. Coming soon.</div>
                </div>
            </div>

        </div>{{-- /right-body --}}

        <div class="right-footer">
            <span>🔐 Members only · All activity is logged</span>
            <a href="{{ route('register') }}">Not a member? Request access →</a>
        </div>

    </div>{{-- /right --}}

</div>{{-- /page --}}

<script>
/* ── Method switcher ────────────────────────────────────────────────────── */
(function () {
    const btns  = document.querySelectorAll('.method-btn');
    const panes = document.querySelectorAll('.method-pane');
    function activate(method) {
        btns.forEach(b  => b.classList.toggle('active', b.dataset.method === method));
        panes.forEach(p => p.classList.toggle('active', p.id === 'method-' + method));
    }
    btns.forEach(b => b.addEventListener('click', () => activate(b.dataset.method)));
    const params = new URLSearchParams(window.location.search);
    const requested = params.get('method');
    if (requested && document.getElementById('method-' + requested)) activate(requested);
})();

/* ── Password reveal ────────────────────────────────────────────────────── */
function togglePwd() {
    const input  = document.getElementById('password');
    const toggle = document.getElementById('pwdToggle');
    input.type   = input.type === 'password' ? 'text' : 'password';
    toggle.textContent = input.type === 'password' ? 'show' : 'hide';
}

/* ── Magic code — PIN input controller ─────────────────────────────────── */
const PIN = {
    boxes:       null,
    countdown:   null,
    secondsLeft: 600,

    init() {
        this.boxes = Array.from(document.querySelectorAll('.pin-box'));
        this.boxes.forEach((box, i) => {
            box.addEventListener('keydown', e => this.onKeyDown(e, i));
            box.addEventListener('input',   e => this.onInput(e, i));
            box.addEventListener('paste',   e => this.onPaste(e));
            box.addEventListener('focus',   ()  => box.select());
            box.addEventListener('click',   ()  => box.select());
        });

        document.getElementById('pinInputs').addEventListener('paste', e => this.onPaste(e));

        document.addEventListener('paste', e => {
            const step2 = document.getElementById('code-step-2');
            if (step2 && step2.classList.contains('active')) this.onPaste(e);
        });
    },

    onKeyDown(e, i) {
        if (e.key === 'Backspace') {
            e.preventDefault();
            if (this.boxes[i].value) {
                this.boxes[i].value = '';
                this.boxes[i].classList.remove('filled');
            } else if (i > 0) {
                this.boxes[i - 1].value = '';
                this.boxes[i - 1].classList.remove('filled');
                this.boxes[i - 1].focus();
            }
            this.syncAndCheck();
        }
        if (e.key === 'ArrowLeft'  && i > 0) { e.preventDefault(); this.boxes[i - 1].focus(); }
        if (e.key === 'ArrowRight' && i < 5) { e.preventDefault(); this.boxes[i + 1].focus(); }
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') e.preventDefault();
        if (!/^[0-9]$/.test(e.key) && !['Backspace','Tab','ArrowLeft','ArrowRight','Delete'].includes(e.key)) {
            e.preventDefault();
        }
    },

    onInput(e, i) {
        const val = e.target.value.replace(/\D/g, '');
        e.target.value = val ? val[val.length - 1] : '';
        if (e.target.value) {
            e.target.classList.add('filled');
            if (i < 5) this.boxes[i + 1].focus();
        } else {
            e.target.classList.remove('filled');
        }
        this.syncAndCheck();
    },

    onPaste(e) {
        e.preventDefault();
        e.stopPropagation();
        const digits = (e.clipboardData || window.clipboardData)
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);
        if (!digits) return;
        this.boxes.forEach((box, i) => {
            box.value = digits[i] || '';
            box.classList.toggle('filled', !!digits[i]);
            box.classList.remove('error');
        });
        const nextEmpty = this.boxes.findIndex(b => !b.value);
        this.boxes[nextEmpty === -1 ? 5 : nextEmpty].focus();
        this.syncAndCheck();
    },

    getCode() {
        return this.boxes.map(b => b.value).join('');
    },

    syncAndCheck() {
        const code = this.getCode();
        document.getElementById('hiddenCode').value = code;
        const complete = code.length === 6;
        document.getElementById('btnVerify').disabled = !complete;
        this.boxes.forEach(b => b.classList.remove('error'));
        const status = document.getElementById('pinStatus');
        if (complete) {
            status.textContent = '✓ Code entered — press verify to continue';
            status.className   = 'pin-status success';
        } else {
            status.textContent = '';
            status.className   = 'pin-status';
        }
    },

    setError(msg) {
        this.boxes.forEach(b => { b.classList.add('error'); b.value = ''; b.classList.remove('filled'); });
        document.getElementById('hiddenCode').value = '';
        document.getElementById('btnVerify').disabled = true;
        const status = document.getElementById('pinStatus');
        status.textContent = '✕ ' + msg;
        status.className   = 'pin-status error';
        this.boxes[0].focus();
    },

    startCountdown() {
        this.secondsLeft = 600;
        clearInterval(this.countdown);
        const resend  = document.getElementById('btnResend');
        const display = document.getElementById('countdownTimer');
        resend.disabled = true;
        this.countdown = setInterval(() => {
            this.secondsLeft--;
            const m = Math.floor(this.secondsLeft / 60);
            const s = this.secondsLeft % 60;
            display.textContent = m + ':' + String(s).padStart(2, '0');
            if (this.secondsLeft <= 0) {
                clearInterval(this.countdown);
                display.textContent = '0:00';
                resend.disabled = false;
            }
        }, 1000);
    }
};

/* ── Code request form ──────────────────────────────────────────────────── */
document.getElementById('codeRequestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const login  = document.getElementById('code_login').value.trim();
    const btn    = document.getElementById('btnSendCode');
    const errBox = document.getElementById('codeRequestError');

    if (!login) return;

    btn.disabled    = true;
    btn.textContent = 'Sending…';
    errBox.style.display = 'none';

    try {
        const token = document.querySelector('#codeRequestForm [name="_token"]').value;
        const res = await fetch('{{ route("login.code.request") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body:    JSON.stringify({ login })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            document.getElementById('pinSentEmail').textContent  = data.sent_to || login;
            document.getElementById('hiddenCodeLogin').value     = login;
            goToStep2();
        } else {
            errBox.textContent   = '✕ ' + (data.message || 'Could not find that account. Please check your email or callsign.');
            errBox.style.display = 'flex';
            btn.disabled         = false;
            btn.textContent      = 'Send code →';
        }
    } catch {
        errBox.textContent   = '✕ Something went wrong. Please try again.';
        errBox.style.display = 'flex';
        btn.disabled         = false;
        btn.textContent      = 'Send code →';
    }
});

/* ── Code verify form ───────────────────────────────────────────────────── */
document.getElementById('codeVerifyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const code  = PIN.getCode();
    const login = document.getElementById('hiddenCodeLogin').value;
    const btn   = document.getElementById('btnVerify');

    if (code.length !== 6) return;

    btn.disabled    = true;
    btn.textContent = 'Verifying…';

    try {
        const token = document.querySelector('#codeVerifyForm [name="_token"]').value;
        const res = await fetch('{{ route("login.code.verify") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body:    JSON.stringify({ login, code })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            PIN.boxes.forEach(b => b.classList.add('success'));
            const status = document.getElementById('pinStatus');
            status.textContent = '✓ Verified — signing you in…';
            status.className   = 'pin-status success';
            btn.textContent    = '✓ Verified';
            // Redirect to role-based destination returned by the server
            setTimeout(() => { window.location.href = data.redirect; }, 600);
        } else {
            PIN.setError(data.message || 'Incorrect code. Please check and try again.');
            btn.disabled    = false;
            btn.textContent = 'Verify code →';
        }
    } catch {
        PIN.setError('Something went wrong. Please try again.');
        btn.disabled    = false;
        btn.textContent = 'Verify code →';
    }
});

/* ── Step transitions ───────────────────────────────────────────────────── */
function goToStep2() {
    document.getElementById('code-step-1').classList.remove('active');
    document.getElementById('code-step-2').classList.add('active');
    PIN.init();
    PIN.startCountdown();
    setTimeout(() => PIN.boxes[0].focus(), 50);
}

function backToStep1() {
    clearInterval(PIN.countdown);
    document.getElementById('code-step-2').classList.remove('active');
    document.getElementById('code-step-1').classList.add('active');
    document.getElementById('btnSendCode').disabled    = false;
    document.getElementById('btnSendCode').textContent = 'Send code →';
    document.getElementById('codeRequestError').style.display = 'none';
}

async function resendCode() {
    const login = document.getElementById('hiddenCodeLogin').value;
    const btn   = document.getElementById('btnResend');
    btn.disabled    = true;
    btn.textContent = 'Sending…';

    try {
        const token = document.querySelector('#codeVerifyForm [name="_token"]').value;
        await fetch('{{ route("login.code.request") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body:    JSON.stringify({ login })
        });
    } catch {}

    btn.textContent = 'Resend code';
    PIN.startCountdown();
    PIN.boxes.forEach(b => { b.value = ''; b.classList.remove('filled','error','success'); });
    document.getElementById('btnVerify').disabled = true;
    document.getElementById('pinStatus').textContent = '';
    PIN.boxes[0].focus();
}
</script>

@endsection