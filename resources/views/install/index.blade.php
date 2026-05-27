<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RAYNET-OS — Installation</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{-webkit-font-smoothing:antialiased}
body{font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;background:#0a0f1a;min-height:100vh;color:#111827;overflow-x:hidden}
.iz-bg{position:fixed;inset:0;z-index:0;overflow:hidden}
.iz-bg-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,51,102,.15) 1px,transparent 1px),linear-gradient(90deg,rgba(0,51,102,.15) 1px,transparent 1px);background-size:40px 40px;animation:gridMove 20s linear infinite}
@keyframes gridMove{0%{background-position:0 0}100%{background-position:40px 40px}}
.iz-bg-glow{position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,51,102,.4) 0%,transparent 70%);top:-100px;left:-100px;animation:glowPulse 8s ease-in-out infinite}
.iz-bg-glow2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(200,16,46,.2) 0%,transparent 70%);bottom:-50px;right:-50px;animation:glowPulse 6s ease-in-out infinite reverse}
@keyframes glowPulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}
.iz-wrap{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem}
.iz-brand{display:flex;align-items:center;gap:.85rem;margin-bottom:2.5rem;animation:fadeDown .6s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:none}}
.iz-logo{width:52px;height:52px;background:linear-gradient(135deg,#003366,#004d99);border:2px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 0 1px rgba(0,51,102,.5),0 8px 24px rgba(0,51,102,.4)}
.iz-logo span{font-size:9px;font-weight:bold;color:#fff;text-align:center;line-height:1.3;text-transform:uppercase;letter-spacing:.05em}
.iz-brand-name{font-size:1.25rem;font-weight:bold;color:#fff;letter-spacing:-.01em}
.iz-brand-sub{font-size:.75rem;color:rgba(255,255,255,.45);margin-top:.1rem;letter-spacing:.05em;text-transform:uppercase}
.iz-brand-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;padding:.2rem .6rem;background:rgba(200,16,46,.15);border:1px solid rgba(200,16,46,.3);color:#ff6b7a;margin-top:.25rem}
.iz-brand-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:#C8102E;animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.iz-progress{display:flex;align-items:center;margin-bottom:2rem;animation:fadeDown .6s .1s ease both}
.iz-step-item{display:flex;align-items:center;gap:.5rem}
.iz-step-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:bold;flex-shrink:0}
.iz-step-circle.done{background:#1a6b3c;border:2px solid #2d9a5a;color:#fff;box-shadow:0 0 12px rgba(26,107,60,.4)}
.iz-step-circle.active{background:linear-gradient(135deg,#003366,#004d99);border:2px solid #4d94ff;color:#fff;box-shadow:0 0 16px rgba(0,51,102,.6)}
.iz-step-circle.pending{background:rgba(255,255,255,.05);border:2px solid rgba(255,255,255,.1);color:rgba(255,255,255,.3)}
.iz-step-label{font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.06em}
.iz-step-label.done{color:#2d9a5a}
.iz-step-label.active{color:#fff}
.iz-step-label.pending{color:rgba(255,255,255,.25)}
.iz-step-connector{width:40px;height:2px;margin:0 .5rem;flex-shrink:0}
.iz-step-connector.done{background:linear-gradient(90deg,#1a6b3c,#2d9a5a)}
.iz-step-connector.pending{background:rgba(255,255,255,.08)}
.iz-card{width:100%;max-width:580px;background:rgba(255,255,255,.97);box-shadow:0 0 0 1px rgba(255,255,255,.1),0 24px 80px rgba(0,0,0,.5),0 0 60px rgba(0,51,102,.2);animation:fadeUp .6s .15s ease both;overflow:hidden}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.iz-card-accent{height:4px;background:linear-gradient(90deg,#003366,#004d99 50%,#C8102E)}
.iz-card-head{padding:1.5rem 1.75rem 1.25rem;border-bottom:1px solid #e5e7eb}
.iz-card-title{font-size:1.05rem;font-weight:bold;color:#003366;display:flex;align-items:center;gap:.55rem}
.iz-card-title-icon{width:32px;height:32px;background:#e8eef5;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.iz-card-sub{font-size:.78rem;color:#6b7f96;margin-top:.35rem;line-height:1.55}
.iz-card-body{padding:1.75rem}
.iz-card-foot{padding:1.1rem 1.75rem;background:#f8fafc;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.iz-field{margin-bottom:1.25rem}
.iz-field:last-child{margin-bottom:0}
.iz-label{display:block;font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#374151;margin-bottom:.4rem}
.iz-label-opt{font-weight:normal;text-transform:none;letter-spacing:0;color:#9ca3af;font-size:.72rem}
.iz-input{width:100%;border:1.5px solid #d1d5db;padding:.6rem .9rem;font-size:13.5px;font-family:inherit;color:#111827;outline:none;transition:all .15s;background:#fff}
.iz-input:focus{border-color:#003366;box-shadow:0 0 0 3px rgba(0,51,102,.1)}
.iz-input::placeholder{color:#9ca3af}
.iz-input-mono{font-family:ui-monospace,monospace}
.iz-err{font-size:.72rem;color:#C8102E;font-weight:bold;margin-top:.3rem}
.iz-hint{font-size:.72rem;color:#9aa3ae;margin-top:.3rem;line-height:1.5}
.iz-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.iz-divider{height:1px;background:linear-gradient(90deg,transparent,#e5e7eb,transparent);margin:1.25rem 0}
.iz-info{padding:.85rem 1rem;font-size:.8rem;line-height:1.6;margin-bottom:1.25rem;display:flex;gap:.75rem;align-items:flex-start}
.iz-info-icon{font-size:1.1rem;flex-shrink:0;margin-top:.05rem}
.iz-info-warn{background:#fffbeb;border:1px solid #fde68a;border-left:3px solid #f59e0b;color:#78350f}
.iz-info-ok{background:#eef7f2;border:1px solid #b8ddc9;border-left:3px solid #1a6b3c;color:#1a3a1f}
.iz-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.4rem;border:1.5px solid;font-family:inherit;font-size:.8rem;font-weight:bold;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.06em;text-decoration:none;white-space:nowrap}
.iz-btn-primary{background:linear-gradient(135deg,#003366,#004d99);border-color:#003366;color:#fff;box-shadow:0 4px 12px rgba(0,51,102,.3)}
.iz-btn-primary:hover{background:linear-gradient(135deg,#002244,#003d80);transform:translateY(-1px)}
.iz-btn-ghost{background:#fff;border-color:#d1d5db;color:#6b7f96}
.iz-btn-ghost:hover{border-color:#003366;color:#003366}
.iz-btn-success{background:linear-gradient(135deg,#1a6b3c,#2d9a5a);border-color:#1a6b3c;color:#fff}
.iz-welcome-icon{font-size:3.5rem;text-align:center;margin-bottom:1rem}
.iz-features{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:1.25rem 0;text-align:left}
.iz-feature{display:flex;align-items:flex-start;gap:.55rem;padding:.65rem .85rem;background:#f8fafc;border:1px solid #e5e7eb;font-size:.8rem;color:#374151;line-height:1.4}
.iz-feature-icon{font-size:.95rem;flex-shrink:0}
.iz-complete{text-align:center;padding:1rem 0}
.iz-complete-icon{font-size:4rem;margin-bottom:.75rem}
.iz-complete-title{font-size:1.4rem;font-weight:bold;color:#003366;margin-bottom:.4rem}
.iz-complete-sub{font-size:.85rem;color:#6b7f96;margin-bottom:1.5rem}
.iz-checklist{display:flex;flex-direction:column;gap:.5rem;text-align:left;margin:1.25rem 0}
.iz-check-item{display:flex;align-items:center;gap:.65rem;padding:.6rem .85rem;background:#eef7f2;border:1px solid #b8ddc9;font-size:.82rem;font-weight:bold;color:#1a6b3c}
.iz-check-item::before{content:'✓';font-size:1rem;flex-shrink:0}
.iz-next-steps{background:#f8fafc;border:1px solid #e5e7eb;padding:.85rem 1rem;font-size:.8rem;color:#374151;line-height:1.7;margin-top:1rem}
.iz-next-steps strong{color:#003366}
.iz-err-banner{background:#fdf0f2;border:1px solid rgba(200,16,46,.2);border-left:3px solid #C8102E;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.8rem;color:#C8102E;font-weight:bold}
.preview-bar{position:fixed;top:0;left:0;right:0;z-index:9999;background:linear-gradient(90deg,#f59e0b,#d97706);color:#fff;text-align:center;padding:.5rem 1rem;font-size:.78rem;font-weight:bold;letter-spacing:.06em;text-transform:uppercase}
.iz-footer{margin-top:1.75rem;font-size:.7rem;color:rgba(255,255,255,.25);text-align:center}
.iz-footer a{color:rgba(255,255,255,.4);text-decoration:none}
@media(max-width:560px){.iz-row{grid-template-columns:1fr}.iz-features{grid-template-columns:1fr}.iz-step-label{display:none}.iz-step-connector{width:24px}}
</style>
</head>
<body>

@if(isset($preview) && $preview)
<div class="preview-bar">&#9888; PREVIEW MODE &mdash; No data will be saved.</div>
<div style="height:36px"></div>
@endif

<div class="iz-bg">
    <div class="iz-bg-grid"></div>
    <div class="iz-bg-glow"></div>
    <div class="iz-bg-glow2"></div>
</div>

<div class="iz-wrap">

    <div class="iz-brand">
        <div class="iz-logo"><span>RAY<br>NET</span></div>
        <div>
            <div class="iz-brand-name">RAYNET-OS</div>
            <div class="iz-brand-sub">Installation Wizard</div>
            <div class="iz-brand-badge">Setup mode active</div>
        </div>
    </div>

    @if($step !== 'index')
    <div class="iz-progress">
        <div class="iz-step-item">
            <div class="iz-step-circle {{ in_array($step, ['step2','step3']) ? 'done' : ($step === 'step1' ? 'active' : 'pending') }}">
                {{ in_array($step, ['step2','step3']) ? '✓' : '1' }}
            </div>
            <span class="iz-step-label {{ in_array($step, ['step2','step3']) ? 'done' : ($step === 'step1' ? 'active' : 'pending') }}">Group</span>
        </div>
        <div class="iz-step-connector {{ in_array($step, ['step2','step3']) ? 'done' : 'pending' }}"></div>
        <div class="iz-step-item">
            <div class="iz-step-circle {{ $step === 'step3' ? 'done' : ($step === 'step2' ? 'active' : 'pending') }}">
                {{ $step === 'step3' ? '✓' : '2' }}
            </div>
            <span class="iz-step-label {{ $step === 'step3' ? 'done' : ($step === 'step2' ? 'active' : 'pending') }}">Admin</span>
        </div>
        <div class="iz-step-connector {{ $step === 'step3' ? 'done' : 'pending' }}"></div>
        <div class="iz-step-item">
            <div class="iz-step-circle {{ $step === 'step3' ? 'active' : 'pending' }}">3</div>
            <span class="iz-step-label {{ $step === 'step3' ? 'active' : 'pending' }}">Complete</span>
        </div>
    </div>
    @endif

    @php $isPreview = isset($preview) && $preview; @endphp

    {{-- WELCOME --}}
    @if($step === 'index')
    <div class="iz-card">
        <div class="iz-card-accent"></div>
        <div class="iz-card-body">
            <div style="text-align:center;padding:.5rem 0 1.25rem">
                <div class="iz-welcome-icon">📻</div>
                <h1 style="font-size:1.5rem;font-weight:bold;color:#003366;margin-bottom:.5rem">Welcome to RAYNET-OS</h1>
                <p style="font-size:.88rem;color:#6b7f96;max-width:380px;margin:0 auto 1.5rem;line-height:1.65">The complete web platform for RAYNET UK groups. Takes about 2 minutes to set up.</p>
            </div>
            <div class="iz-features">
                <div class="iz-feature"><span class="iz-feature-icon">👥</span>Member management & roles</div>
                <div class="iz-feature"><span class="iz-feature-icon">📅</span>Event scheduling & RSVPs</div>
                <div class="iz-feature"><span class="iz-feature-icon">📄</span>Visual page builder</div>
                <div class="iz-feature"><span class="iz-feature-icon">🎓</span>Training portal & LMS</div>
                <div class="iz-feature"><span class="iz-feature-icon">📡</span>Ops map & alert status</div>
                <div class="iz-feature"><span class="iz-feature-icon">🔌</span>Module update system</div>
            </div>
            <div class="iz-info iz-info-warn">
                <span class="iz-info-icon">⚠️</span>
                <div><strong>Before you continue:</strong> Make sure you have run <code>php artisan migrate</code> to set up the database.</div>
            </div>
        </div>
        <div class="iz-card-foot">
            <span style="font-size:.72rem;color:#9ca3af">RAYNET-OS · Built for RAYNET UK</span>
            @if($isPreview)
                <a href="{{ route('install.preview.step1') }}" class="iz-btn iz-btn-primary">Get Started →</a>
            @else
                <a href="{{ route('install.step1') }}" class="iz-btn iz-btn-primary">Get Started →</a>
            @endif
        </div>
    </div>

    {{-- STEP 1 --}}
    @elseif($step === 'step1')
    <div class="iz-card">
        <div class="iz-card-accent"></div>
        <div class="iz-card-head">
            <div class="iz-card-title"><div class="iz-card-title-icon">📻</div>Your Group Details</div>
            <div class="iz-card-sub">This information appears throughout your site. You can change it later in Admin → Settings.</div>
        </div>
        @if($isPreview)
        <form method="POST" action="{{ route('install.preview.step1.post') }}">
        @else
        <form method="POST" action="{{ route('install.step1.post') }}">
        @endif
            @csrf
            <div class="iz-card-body">
                @if($errors->any())
                <div class="iz-err-banner">⚠ Please fix the errors below.</div>
                @endif

                {{-- Licence key --}}
                <div class="iz-field" id="licence-field">
                    <label class="iz-label" for="licence_key">RAYNET-OS Licence Key <span class="iz-label-opt">(required)</span></label>
                    <div style="display:flex;gap:.5rem">
                        <input type="text" id="licence_key" name="licence_key" class="iz-input iz-input-mono"
                               value="{{ old('licence_key') }}"
                               placeholder="RAYNET-XXXXXX-XXXXXXXXXXXXXXXX"
                               oninput="this.value=this.value.toUpperCase()"
                               style="flex:1" required>
                        <button type="button" onclick="validateLicence()" class="iz-btn iz-btn-primary" id="validate-btn" style="white-space:nowrap">
                            Validate
                        </button>
                    </div>
                    <div class="iz-hint">Request a licence key from <a href="https://raynet-liverpool.net/request-support" target="_blank" style="color:#003366">RAYNET Liverpool</a>. Keys are free for all RAYNET UK affiliated groups.</div>
                    <div id="licence-status" style="margin-top:.35rem;font-size:.78rem;font-weight:bold"></div>
                    @error('licence_key')<div class="iz-err">{{ $message }}</div>@enderror
                </div>

                <div class="iz-divider"></div>

                <div class="iz-field">
                    <label class="iz-label" for="group_name">Group Name <span class="iz-label-opt">(required)</span></label>
                    <input type="text" id="group_name" name="group_name" class="iz-input" value="{{ old('group_name') }}" placeholder="e.g. Liverpool RAYNET" required autofocus>
                    @error('group_name')<div class="iz-err">{{ $message }}</div>@enderror
                </div>
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="group_number">Group Number <span class="iz-label-opt">(optional)</span></label>
                        <input type="text" id="group_number" name="group_number" class="iz-input iz-input-mono" value="{{ old('group_number') }}" placeholder="e.g. 10/ME/179">
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="group_callsign">Group Callsign <span class="iz-label-opt">(optional)</span></label>
                        <input type="text" id="group_callsign" name="group_callsign" class="iz-input iz-input-mono" value="{{ old('group_callsign') }}" placeholder="e.g. M0XYZ" oninput="this.value=this.value.toUpperCase()">
                    </div>
                </div>
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="group_region">Region / Area <span class="iz-label-opt">(optional)</span></label>
                        <input type="text" id="group_region" name="group_region" class="iz-input" value="{{ old('group_region') }}" placeholder="e.g. Merseyside">
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="raynet_zone">RAYNET Zone <span class="iz-label-opt">(optional)</span></label>
                        <input type="text" id="raynet_zone" name="raynet_zone" class="iz-input" value="{{ old('raynet_zone') }}" placeholder="e.g. Zone 10">
                    </div>
                </div>
                <div class="iz-divider"></div>
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="gc_name">Group Controller <span class="iz-label-opt">(required)</span></label>
                        <input type="text" id="gc_name" name="gc_name" class="iz-input" value="{{ old('gc_name') }}" placeholder="e.g. John Smith" required>
                        @error('gc_name')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="gc_email">GC Email <span class="iz-label-opt">(required)</span></label>
                        <input type="email" id="gc_email" name="gc_email" class="iz-input" value="{{ old('gc_email') }}" placeholder="gc@yourgroup.raynet-uk.net" required>
                        @error('gc_email')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="support_request_email">Support Email <span class="iz-label-opt">(required)</span></label>
                        <input type="email" id="support_request_email" name="support_request_email" class="iz-input" value="{{ old('support_request_email') }}" placeholder="support@yourgroup.com" required>
                        <div class="iz-hint">Where event support requests are sent.</div>
                        @error('support_request_email')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="site_url">Site URL <span class="iz-label-opt">(required)</span></label>
                        <input type="url" id="site_url" name="site_url" class="iz-input" value="{{ old('site_url', config('app.url')) }}" placeholder="https://yourgroup.net" required>
                        @error('site_url')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="iz-card-foot">
                @if($isPreview)
                    <a href="{{ route('install.preview.index') }}" class="iz-btn iz-btn-ghost">← Back</a>
                @else
                    <a href="{{ route('install.index') }}" class="iz-btn iz-btn-ghost">← Back</a>
                @endif
                <button type="submit" class="iz-btn iz-btn-primary">Next: Admin Account →</button>
            </div>
        </form>
    </div>

    {{-- STEP 2 --}}
    @elseif($step === 'step2')
    <div class="iz-card">
        <div class="iz-card-accent"></div>
        <div class="iz-card-head">
            <div class="iz-card-title"><div class="iz-card-title-icon">🔐</div>Create Your Admin Account</div>
            <div class="iz-card-sub">This will be the first administrator. More admins can be added later.</div>
        </div>
        @if(isset($dryRun))
        <div style="margin:0 1.75rem;margin-top:1.25rem;background:#f8fafc;border:1px solid #e5e7eb;overflow:hidden">
            <div style="padding:.6rem 1rem;background:#1a6b3c;color:#fff;font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em">✓ {{ $dryRun['title'] }}</div>
            <div style="padding:.75rem 1rem">
                @foreach($dryRun['items'] as $item)
                <div style="display:flex;gap:1rem;padding:.3rem 0;border-bottom:1px solid #f3f4f6;font-size:.8rem">
                    <span style="font-family:ui-monospace,monospace;font-weight:bold;color:#003366;min-width:180px;flex-shrink:0">{{ $item['key'] }}</span>
                    <span style="color:#374151">{{ $item['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @if($isPreview)
        <form method="POST" action="{{ route('install.preview.step2.post') }}">
        @else
        <form method="POST" action="{{ route('install.step2.post') }}">
        @endif
            @csrf
            <div class="iz-card-body">
                @if($errors->any())
                <div class="iz-err-banner">⚠ Please fix the errors below.</div>
                @endif
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="name">Full Name <span class="iz-label-opt">(required)</span></label>
                        <input type="text" id="name" name="name" class="iz-input" value="{{ old('name') }}" placeholder="e.g. John Smith" required autofocus>
                        @error('name')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="callsign">Callsign <span class="iz-label-opt">(required)</span></label>
                        <input type="text" id="callsign" name="callsign" class="iz-input iz-input-mono" value="{{ old('callsign') }}" placeholder="e.g. M0XYZ" required oninput="this.value=this.value.toUpperCase()">
                        @error('callsign')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="iz-field">
                    <label class="iz-label" for="email">Email Address <span class="iz-label-opt">(required)</span></label>
                    <input type="email" id="email" name="email" class="iz-input" value="{{ old('email') }}" placeholder="you@example.com" required>
                    @error('email')<div class="iz-err">{{ $message }}</div>@enderror
                </div>
                <div class="iz-row">
                    <div class="iz-field">
                        <label class="iz-label" for="password">Password <span class="iz-label-opt">(min 10 chars)</span></label>
                        <input type="password" id="password" name="password" class="iz-input" placeholder="Choose a strong password" required minlength="10">
                        @error('password')<div class="iz-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="iz-field">
                        <label class="iz-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="iz-input" placeholder="Repeat your password" required>
                    </div>
                </div>
            </div>
            <div class="iz-card-foot">
                @if($isPreview)
                    <a href="{{ route('install.preview.step1') }}" class="iz-btn iz-btn-ghost">← Back</a>
                @else
                    <a href="{{ route('install.step1') }}" class="iz-btn iz-btn-ghost">← Back</a>
                @endif
                <button type="submit" class="iz-btn iz-btn-primary">Create Account →</button>
            </div>
        </form>
    </div>

    {{-- STEP 3 --}}
    @elseif($step === 'step3')
    <div class="iz-card">
        <div class="iz-card-accent"></div>
        @if(isset($dryRun))
        <div style="margin:1.25rem 1.75rem 0;background:#f8fafc;border:1px solid #e5e7eb;overflow:hidden">
            <div style="padding:.6rem 1rem;background:#1a6b3c;color:#fff;font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em">✓ {{ $dryRun['title'] }}</div>
            <div style="padding:.75rem 1rem">
                @foreach($dryRun['items'] as $item)
                <div style="display:flex;gap:1rem;padding:.3rem 0;border-bottom:1px solid #f3f4f6;font-size:.8rem">
                    <span style="font-family:ui-monospace,monospace;font-weight:bold;color:#003366;min-width:180px;flex-shrink:0">{{ $item['key'] }}</span>
                    <span style="color:#374151">{{ $item['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        <div class="iz-card-body">
            <div class="iz-complete">
                <div class="iz-complete-icon">🎉</div>
                <h2 class="iz-complete-title">{{ $groupName ?? 'Your Group' }} is ready!</h2>
                <p class="iz-complete-sub">Your RAYNET-OS site has been configured successfully.</p>
            </div>
            <div class="iz-checklist">
                <div class="iz-check-item">Group details saved</div>
                <div class="iz-check-item">Admin account created</div>
                <div class="iz-check-item">Database configured</div>
                <div class="iz-check-item">Update server connected</div>
            </div>
            <div class="iz-next-steps">
                <strong>After logging in:</strong><br>
                • Go to <strong>Admin → Settings</strong> to upload your group logo<br>
                • Go to <strong>Admin → Pages</strong> to customise your About, Home &amp; Training pages<br>
                • Go to <strong>Module Manager</strong> to install additional features
            </div>
        </div>
        <div class="iz-card-foot">
            <span style="font-size:.72rem;color:#9ca3af">RAYNET-OS · Powered by RAYNET Liverpool</span>
            @if($isPreview)
            <form method="POST" action="{{ route('install.preview.complete') }}">
            @else
            <form method="POST" action="{{ route('install.complete') }}">
            @endif
                @csrf
                <button type="submit" class="iz-btn iz-btn-success">✓ Launch My Site</button>
            </form>
        </div>
    </div>
    @endif

    <div class="iz-footer">
        RAYNET-OS &middot; Built for <a href="https://www.raynet-uk.net" target="_blank">RAYNET UK</a> groups &middot;
        <a href="https://github.com/raynet-uk/raynet-cms-modules" target="_blank">GitHub</a>
    </div>

</div>
<script>
async function validateLicence() {
    const key = document.getElementById('licence_key').value.trim();
    const status = document.getElementById('licence-status');
    const btn = document.getElementById('validate-btn');

    if (!key) {
        status.innerHTML = '<span style="color:#C8102E">⚠ Please enter a licence key first.</span>';
        return;
    }

    btn.textContent = 'Checking...';
    btn.disabled = true;
    status.innerHTML = '<span style="color:#6b7f96">Validating...</span>';

    try {
        const resp = await fetch('https://command.nathandillon.co.uk/api/cms/validate-key', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ key: key })
        });
        const data = await resp.json();

        if (data.valid) {
            status.innerHTML = '<span style="color:#1a6b3c">✓ Valid licence — pre-filling your details</span>';
            // Pre-fill form fields from licence data
            if (data.group_name)   document.getElementById('group_name').value   = data.group_name;
            if (data.group_number) document.getElementById('group_number').value = data.group_number;
            if (data.gc_name)      document.getElementById('gc_name').value      = data.gc_name;
            if (data.gc_email) {
                document.getElementById('gc_email').value              = data.gc_email;
                document.getElementById('support_request_email').value = data.gc_email;
            }
        } else {
            status.innerHTML = '<span style="color:#C8102E">✗ ' + (data.message || 'Invalid licence key.') + '</span>';
        }
    } catch(e) {
        status.innerHTML = '<span style="color:#f59e0b">⚠ Could not connect to licence server — you can still continue manually.</span>';
    }

    btn.textContent = 'Validate';
    btn.disabled = false;
}
</script>
</body>
</html>