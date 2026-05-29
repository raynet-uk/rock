<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RAYNET-OS — Installation Wizard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
body{font-family:'Inter',Arial,sans-serif;background:#05080f;min-height:100vh;color:#111827;overflow-x:hidden}
.iz-scene{position:fixed;inset:0;z-index:0;overflow:hidden}
.iz-scene-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,102,204,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(0,102,204,.08) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,#000 40%,transparent 100%)}
.iz-orb{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none}
.iz-orb-1{width:700px;height:700px;background:radial-gradient(circle,rgba(0,51,153,.35) 0%,transparent 70%);top:-200px;left:-200px;animation:orbDrift1 12s ease-in-out infinite}
.iz-orb-2{width:500px;height:500px;background:radial-gradient(circle,rgba(180,10,40,.18) 0%,transparent 70%);bottom:-100px;right:-100px;animation:orbDrift2 9s ease-in-out infinite}
.iz-orb-3{width:300px;height:300px;background:radial-gradient(circle,rgba(0,180,130,.12) 0%,transparent 70%);top:40%;left:60%;animation:orbDrift3 15s ease-in-out infinite}
@keyframes orbDrift1{0%,100%{transform:translate(0,0)}50%{transform:translate(60px,40px)}}
@keyframes orbDrift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-40px,-60px)}}
@keyframes orbDrift3{0%,100%{transform:translate(0,0)}50%{transform:translate(-80px,50px)}}
.iz-scan{position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,102,204,.015) 3px,rgba(0,102,204,.015) 4px);pointer-events:none}
.iz-wrap{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2.5rem 1rem}
.iz-brand{display:flex;align-items:center;gap:1.1rem;margin-bottom:2rem;animation:fadeDown .5s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:none}}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:none}}
.iz-logo-ring{position:relative;width:64px;height:64px;flex-shrink:0}
.iz-logo-ring svg{width:64px;height:64px}
.iz-logo-inner{position:absolute;inset:8px;background:linear-gradient(145deg,#0a1628,#0d2050);border:1px solid rgba(0,102,204,.4);display:flex;align-items:center;justify-content:center;border-radius:4px}
.iz-logo-text{font-family:'JetBrains Mono',monospace;font-size:8px;font-weight:600;color:#4d9fff;text-align:center;line-height:1.25;letter-spacing:.03em;text-transform:uppercase;transition:all .3s ease}
.iz-brand-info{display:flex;flex-direction:column;gap:.15rem}
.iz-brand-name{font-size:1.2rem;font-weight:700;color:#fff;letter-spacing:-.02em;line-height:1.1;transition:all .3s ease}
.iz-brand-sub{font-size:.7rem;color:rgba(255,255,255,.35);letter-spacing:.1em;text-transform:uppercase;font-weight:500}
.iz-brand-pill{display:inline-flex;align-items:center;gap:.35rem;font-size:.62rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;padding:.18rem .55rem;background:rgba(200,16,46,.12);border:1px solid rgba(200,16,46,.25);color:#ff5f72;border-radius:2px;margin-top:.1rem;width:fit-content}
.iz-brand-pill::before{content:'';width:5px;height:5px;border-radius:50%;background:#ff3355;box-shadow:0 0 6px #ff3355;animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.iz-progress{display:flex;align-items:center;gap:0;margin-bottom:1.75rem;animation:fadeDown .5s .08s ease both;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);padding:.6rem 1.25rem;border-radius:2px}
.iz-step-item{display:flex;align-items:center;gap:.5rem}
.iz-step-num{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;flex-shrink:0;transition:all .3s}
.iz-step-num.done{background:#0d4a26;border:1.5px solid #1a8a4a;color:#4ddc8a;box-shadow:0 0 10px rgba(26,138,74,.3)}
.iz-step-num.active{background:linear-gradient(145deg,#001a4d,#003399);border:1.5px solid #4d7fff;color:#fff;box-shadow:0 0 14px rgba(0,51,153,.5)}
.iz-step-num.pending{background:rgba(255,255,255,.04);border:1.5px solid rgba(255,255,255,.08);color:rgba(255,255,255,.2)}
.iz-step-lbl{font-size:.68rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase}
.iz-step-lbl.done{color:#1a8a4a}
.iz-step-lbl.active{color:rgba(255,255,255,.9)}
.iz-step-lbl.pending{color:rgba(255,255,255,.18)}
.iz-step-line{width:36px;height:1.5px;margin:0 .6rem;flex-shrink:0;border-radius:1px}
.iz-step-line.done{background:linear-gradient(90deg,#1a8a4a,#0d4a26)}
.iz-step-line.pending{background:rgba(255,255,255,.06)}
.iz-card{width:100%;max-width:600px;background:#fff;box-shadow:0 0 0 1px rgba(0,0,0,.08),0 32px 100px rgba(0,0,0,.55),0 0 80px rgba(0,51,153,.12);animation:fadeUp .5s .12s ease both;border-radius:3px;overflow:hidden}
.iz-card-stripe{height:3px;background:linear-gradient(90deg,#001a4d 0%,#0033cc 45%,#C8102E 100%)}
.iz-card-head{padding:1.4rem 1.75rem 1.1rem;border-bottom:1px solid #f0f2f5;display:flex;align-items:flex-start;gap:.85rem}
.iz-head-icon{width:36px;height:36px;background:linear-gradient(145deg,#e8f0ff,#d0e0ff);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;border-radius:3px}
.iz-card-title{font-size:1rem;font-weight:700;color:#0a1a3a;line-height:1.2}
.iz-card-sub{font-size:.76rem;color:#7a8fa8;margin-top:.25rem;line-height:1.55}
.iz-card-body{padding:1.6rem 1.75rem}
.iz-card-foot{padding:1rem 1.75rem;background:#f8fafc;border-top:1px solid #edf0f4;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.iz-preview-panel{margin-bottom:1.4rem;background:linear-gradient(135deg,#0a1628 0%,#0d2050 100%);border:1px solid rgba(0,102,204,.25);padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;border-radius:2px;position:relative;overflow:hidden}
.iz-preview-panel::after{content:'';position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 4px,rgba(0,102,204,.03) 4px,rgba(0,102,204,.03) 5px);pointer-events:none}
.iz-preview-logo{width:48px;height:48px;background:linear-gradient(145deg,#001033,#002266);border:1px solid rgba(0,102,204,.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:3px}
.iz-preview-logo-text{font-family:'JetBrains Mono',monospace;font-size:7.5px;font-weight:600;color:#4d9fff;text-align:center;line-height:1.3;letter-spacing:.02em;text-transform:uppercase}
.iz-preview-info{flex:1;min-width:0}
.iz-preview-name{font-size:.9rem;font-weight:700;color:#fff;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:all .2s}
.iz-preview-call{font-family:'JetBrains Mono',monospace;font-size:.72rem;color:rgba(100,180,255,.7);margin-top:.2rem;transition:all .2s}
.iz-preview-tag{font-size:.6rem;color:rgba(255,255,255,.3);letter-spacing:.1em;text-transform:uppercase;margin-top:.3rem}
.iz-preview-badge{font-size:.58rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.12rem .4rem;background:rgba(0,102,204,.2);border:1px solid rgba(0,102,204,.3);color:#6ab0ff;border-radius:1px}
.iz-field{margin-bottom:1.1rem}
.iz-field:last-child{margin-bottom:0}
.iz-label{display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:#2d3748;margin-bottom:.35rem}
.iz-label-opt{font-weight:400;text-transform:none;letter-spacing:0;color:#a0aec0;font-size:.68rem}
.iz-input{width:100%;border:1.5px solid #e2e8f0;padding:.58rem .85rem;font-size:13.5px;font-family:'Inter',inherit;color:#0a1a3a;outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;border-radius:2px}
.iz-input:focus{border-color:#0033cc;box-shadow:0 0 0 3px rgba(0,51,204,.08)}
.iz-input::placeholder{color:#b0bec5}
.iz-mono{font-family:'JetBrains Mono',monospace;font-size:13px}
.iz-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.iz-divider{height:1px;background:linear-gradient(90deg,transparent,#e8ecf0,transparent);margin:1.1rem 0}
.iz-err{font-size:.68rem;color:#C8102E;font-weight:600;margin-top:.25rem;display:flex;align-items:center;gap:.3rem}
.iz-err::before{content:'⚠';font-size:.7rem}
.iz-hint{font-size:.7rem;color:#94a3b8;margin-top:.28rem;line-height:1.55}
.iz-hint a{color:#0033cc;text-decoration:none}
.iz-hint a:hover{text-decoration:underline}
.iz-alert{padding:.8rem 1rem;font-size:.78rem;line-height:1.6;margin-bottom:1.1rem;display:flex;gap:.7rem;align-items:flex-start;border-radius:2px}
.iz-alert-icon{font-size:1rem;flex-shrink:0;margin-top:.05rem}
.iz-alert-warn{background:#fffbeb;border:1px solid #fde68a;border-left:3px solid #f59e0b;color:#78350f}
.iz-alert-err{background:#fff5f5;border:1px solid rgba(200,16,46,.2);border-left:3px solid #C8102E;color:#C8102E;font-weight:600}
.iz-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.58rem 1.35rem;border:1.5px solid;font-family:'Inter',inherit;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.07em;text-decoration:none;white-space:nowrap;border-radius:2px}
.iz-btn-primary{background:linear-gradient(135deg,#001a4d,#0033cc);border-color:#001a4d;color:#fff;box-shadow:0 4px 14px rgba(0,51,204,.3)}
.iz-btn-primary:hover{background:linear-gradient(135deg,#001133,#002299);transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,51,204,.4)}
.iz-btn-ghost{background:#fff;border-color:#e2e8f0;color:#7a8fa8}
.iz-btn-ghost:hover{border-color:#0033cc;color:#0033cc}
.iz-btn-success{background:linear-gradient(135deg,#0d4a26,#1a8a4a);border-color:#0d4a26;color:#fff;box-shadow:0 4px 14px rgba(13,74,38,.3)}
.iz-btn-success:hover{transform:translateY(-1px)}
.iz-welcome-hero{text-align:center;padding:.5rem 0 1.5rem}
.iz-welcome-badge{display:inline-flex;align-items:center;gap:.4rem;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;padding:.3rem .75rem;background:#f0f4ff;border:1px solid #c8d8ff;color:#003399;border-radius:2px;margin-bottom:1rem}
.iz-welcome-title{font-size:1.6rem;font-weight:800;color:#0a1a3a;margin-bottom:.5rem;letter-spacing:-.03em}
.iz-welcome-sub{font-size:.85rem;color:#64748b;max-width:400px;margin:0 auto 1.5rem;line-height:1.7}
.iz-features{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin:1.25rem 0;text-align:left}
.iz-feat{display:flex;align-items:center;gap:.6rem;padding:.6rem .85rem;background:#f8faff;border:1px solid #e8eeff;font-size:.78rem;color:#374151;font-weight:500;border-radius:2px}
.iz-feat-ic{font-size:.95rem;flex-shrink:0}
.iz-summary{margin:0 0 1.1rem;overflow:hidden;border-radius:2px;border:1px solid #e2e8f0}
.iz-summary-head{padding:.55rem 1rem;background:#0d4a26;color:#fff;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;display:flex;align-items:center;gap:.4rem}
.iz-summary-head::before{content:'✓';font-size:.9rem}
.iz-summary-row{display:flex;gap:1rem;padding:.3rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.78rem}
.iz-summary-row:last-child{border-bottom:none}
.iz-summary-key{font-family:'JetBrains Mono',monospace;font-weight:600;color:#003399;min-width:160px;flex-shrink:0}
.iz-summary-val{color:#374151}
.iz-complete{text-align:center;padding:.75rem 0 1.25rem}
.iz-complete-icon{font-size:3.5rem;margin-bottom:.75rem;display:block}
.iz-complete-title{font-size:1.5rem;font-weight:800;color:#0a1a3a;margin-bottom:.35rem;letter-spacing:-.02em}
.iz-complete-sub{font-size:.83rem;color:#64748b;margin-bottom:1.25rem;line-height:1.6}
.iz-checklist{display:flex;flex-direction:column;gap:.45rem;margin:1.1rem 0}
.iz-check{display:flex;align-items:center;gap:.7rem;padding:.55rem .9rem;background:#f0fdf4;border:1px solid #bbf7d0;font-size:.8rem;font-weight:600;color:#166534;border-radius:2px}
.iz-check::before{content:'✓';font-weight:800;flex-shrink:0}
.iz-next{background:#f8fafc;border:1px solid #e8ecf0;padding:.85rem 1rem;font-size:.78rem;color:#374151;line-height:1.8;margin-top:1rem;border-radius:2px}
.iz-next strong{color:#0033cc}
#licence-status{margin-top:.35rem;font-size:.76rem;font-weight:600;min-height:1.1rem}
.iz-preview-bar{position:fixed;top:0;left:0;right:0;z-index:9999;background:linear-gradient(90deg,#f59e0b,#d97706);color:#fff;text-align:center;padding:.45rem 1rem;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.iz-footer{margin-top:1.5rem;font-size:.67rem;color:rgba(255,255,255,.18);text-align:center;letter-spacing:.04em}
.iz-footer a{color:rgba(255,255,255,.28);text-decoration:none}
.iz-footer a:hover{color:rgba(255,255,255,.5)}
@media(max-width:560px){.iz-row{grid-template-columns:1fr}.iz-features{grid-template-columns:1fr}.iz-step-lbl{display:none}.iz-step-line{width:20px}.iz-progress{padding:.5rem .8rem}}
</style>
</head>
<body>
@if(isset($preview) && $preview)
<div class="iz-preview-bar">⚠ Preview Mode — No data will be saved</div>
<div style="height:34px"></div>
@endif
<div class="iz-scene">
  <div class="iz-scene-grid"></div>
  <div class="iz-orb iz-orb-1"></div>
  <div class="iz-orb iz-orb-2"></div>
  <div class="iz-orb iz-orb-3"></div>
  <div class="iz-scan"></div>
</div>
<div class="iz-wrap">
  <div class="iz-brand">
    <div class="iz-logo-ring">
      <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="32" r="30" stroke="rgba(0,102,204,.4)" stroke-width="1"/>
        <circle cx="32" cy="32" r="24" stroke="rgba(0,102,204,.25)" stroke-width="1" stroke-dasharray="4 3"/>
        <circle cx="32" cy="32" r="18" stroke="rgba(0,102,204,.15)" stroke-width="1"/>
        <circle cx="32" cy="32" r="2" fill="#4d9fff"/>
        <line x1="32" y1="2" x2="32" y2="14" stroke="rgba(0,102,204,.5)" stroke-width="1.5"/>
        <line x1="32" y1="50" x2="32" y2="62" stroke="rgba(0,102,204,.5)" stroke-width="1.5"/>
        <line x1="2" y1="32" x2="14" y2="32" stroke="rgba(0,102,204,.5)" stroke-width="1.5"/>
        <line x1="50" y1="32" x2="62" y2="32" stroke="rgba(0,102,204,.5)" stroke-width="1.5"/>
      </svg>
      <div class="iz-logo-inner">
        <div class="iz-logo-text" id="hdr-logo-text">RAY<br>NET</div>
      </div>
    </div>
    <div class="iz-brand-info">
      <div class="iz-brand-name" id="hdr-name">RAYNET-OS</div>
      <div class="iz-brand-sub">Installation Wizard</div>
      <div class="iz-brand-pill">Setup mode active</div>
    </div>
  </div>
  @if($step !== 'index')
  <div class="iz-progress">
    @php
      $s1 = in_array($step, ['step2','step3']) ? 'done' : ($step === 'step1' ? 'active' : 'pending');
      $s2 = $step === 'step3' ? 'done' : ($step === 'step2' ? 'active' : 'pending');
      $s3 = $step === 'step3' ? 'active' : 'pending';
    @endphp
    <div class="iz-step-item">
      <div class="iz-step-num {{ $s1 }}">{{ $s1 === 'done' ? '✓' : '1' }}</div>
      <span class="iz-step-lbl {{ $s1 }}">Group</span>
    </div>
    <div class="iz-step-line {{ in_array($step,['step2','step3']) ? 'done' : 'pending' }}"></div>
    <div class="iz-step-item">
      <div class="iz-step-num {{ $s2 }}">{{ $s2 === 'done' ? '✓' : '2' }}</div>
      <span class="iz-step-lbl {{ $s2 }}">Admin</span>
    </div>
    <div class="iz-step-line {{ $step === 'step3' ? 'done' : 'pending' }}"></div>
    <div class="iz-step-item">
      <div class="iz-step-num {{ $s3 }}">3</div>
      <span class="iz-step-lbl {{ $s3 }}">Launch</span>
    </div>
  </div>
  @endif
  @php $isPreview = isset($preview) && $preview; @endphp
  @if($step === 'index')
  <div class="iz-card">
    <div class="iz-card-stripe"></div>
    <div class="iz-card-body">
      <div class="iz-welcome-hero">
        <div class="iz-welcome-badge">📡 &nbsp; RAYNET-OS · Open Source Platform</div>
        <h1 class="iz-welcome-title">Welcome to RAYNET-OS</h1>
        <p class="iz-welcome-sub">The complete web platform for RAYNET UK groups. Set up your group portal in about 2 minutes.</p>
      </div>
      <div class="iz-features">
        <div class="iz-feat"><span class="iz-feat-ic">👥</span>Member management &amp; roles</div>
        <div class="iz-feat"><span class="iz-feat-ic">📅</span>Event scheduling &amp; RSVPs</div>
        <div class="iz-feat"><span class="iz-feat-ic">📄</span>Visual page builder</div>
        <div class="iz-feat"><span class="iz-feat-ic">🎓</span>Training portal &amp; LMS</div>
        <div class="iz-feat"><span class="iz-feat-ic">📡</span>Ops map &amp; alert status</div>
        <div class="iz-feat"><span class="iz-feat-ic">🔌</span>Module update system</div>
      </div>
      <div class="iz-alert iz-alert-warn">
        <span class="iz-alert-icon">⚠️</span>
        <div><strong>Before continuing:</strong> ensure <code>php artisan migrate</code> has been run to set up the database tables.</div>
      </div>
    </div>
    <div class="iz-card-foot">
      <span style="font-size:.7rem;color:#94a3b8">github.com/raynet-uk/raynet-cms</span>
      @if($isPreview)
        <a href="{{ route('install.preview.step1') }}" class="iz-btn iz-btn-primary">Get Started →</a>
      @else
        <a href="{{ route('install.step1') }}" class="iz-btn iz-btn-primary">Get Started →</a>
      @endif
    </div>
  </div>
  @elseif($step === 'step1')
  <div class="iz-card">
    <div class="iz-card-stripe"></div>
    <div class="iz-card-head">
      <div class="iz-head-icon">📻</div>
      <div>
        <div class="iz-card-title">Your Group Details</div>
        <div class="iz-card-sub">This information appears throughout your site and can be updated later in Admin → Settings.</div>
      </div>
    </div>
    @if($isPreview)
    <form method="POST" action="{{ route('install.preview.step1.post') }}">
    @else
    <form method="POST" action="{{ route('install.step1.post') }}">
    @endif
      @csrf
      <div class="iz-card-body">
        <div class="iz-preview-panel">
          <div class="iz-preview-logo">
            <div class="iz-preview-logo-text" id="prev-logo">RAY<br>NET</div>
          </div>
          <div class="iz-preview-info">
            <div class="iz-preview-name" id="prev-name">Your Group Name</div>
            <div class="iz-preview-call" id="prev-call">CALLSIGN</div>
            <div class="iz-preview-tag">Live preview — updates as you type</div>
          </div>
          <div class="iz-preview-badge">RAYNET-OS</div>
        </div>
        @if($errors->any())
        <div class="iz-alert iz-alert-err">
          <span class="iz-alert-icon">⚠</span>
          <div>Please fix the errors highlighted below before continuing.</div>
        </div>
        @endif
        <div class="iz-field">
          <label class="iz-label" for="licence_key">Licence Key <span class="iz-label-opt">(required)</span></label>
          <div style="display:flex;gap:.5rem">
            <input type="text" id="licence_key" name="licence_key" class="iz-input iz-mono" value="{{ old('licence_key') }}" placeholder="RAYNET-XXXXXX-XXXXXXXXXXXXXXXX" oninput="this.value=this.value.toUpperCase()" style="flex:1" required>
            <button type="button" onclick="validateLicence()" class="iz-btn iz-btn-primary" id="validate-btn">Validate</button>
          </div>
          <div class="iz-hint">Request a free key from <a href="https://command.nathandillon.co.uk/request-licence" target="_blank">RAYNET Liverpool</a>. Keys are free for all affiliated RAYNET UK groups.</div>
          <div id="licence-status"></div>
          @error('licence_key')<div class="iz-err">{{ $message }}</div>@enderror
        </div>
        <div class="iz-divider"></div>
        <div class="iz-field">
          <label class="iz-label" for="group_name">Group Name <span class="iz-label-opt">(required)</span></label>
          <input type="text" id="group_name" name="group_name" class="iz-input" value="{{ old('group_name') }}" placeholder="e.g. Liverpool RAYNET" oninput="updatePreview()" required autofocus>
          @error('group_name')<div class="iz-err">{{ $message }}</div>@enderror
        </div>
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="group_number">Group Number <span class="iz-label-opt">(optional)</span></label>
            <input type="text" id="group_number" name="group_number" class="iz-input iz-mono" value="{{ old('group_number') }}" placeholder="e.g. 10/ME/179">
          </div>
          <div class="iz-field">
            <label class="iz-label" for="group_callsign">Group Callsign <span class="iz-label-opt">(optional)</span></label>
            <input type="text" id="group_callsign" name="group_callsign" class="iz-input iz-mono" value="{{ old('group_callsign') }}" placeholder="e.g. M0XYZ" oninput="this.value=this.value.toUpperCase();updatePreview()">
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
            <div class="iz-hint">Where event support requests are emailed.</div>
            @error('support_request_email')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
          <div class="iz-field">
            <label class="iz-label" for="site_url">Site URL <span class="iz-label-opt">(required)</span></label>
            <input type="url" id="site_url" name="site_url" class="iz-input" value="{{ old('site_url', config('app.url')) }}" placeholder="https://yourgroup.net" required>
            @error('site_url')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="iz-divider"></div>
        <div class="iz-field">
          <label class="iz-label">📧 Mail Configuration <span class="iz-label-opt">(required for email notifications)</span></label>
          <div class="iz-hint" style="margin-bottom:.75rem;">Used for briefing emails, member notifications, and support requests. Use your hosting provider's SMTP details.</div>
        </div>
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="mail_host">SMTP Host <span class="iz-label-opt">(required)</span></label>
            <input type="text" id="mail_host" name="mail_host" class="iz-input iz-mono" value="{{ old('mail_host', 'mail.' . parse_url(config('app.url'), PHP_URL_HOST)) }}" placeholder="mail.yourgroup.net" required>
            @error('mail_host')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
          <div class="iz-field">
            <label class="iz-label" for="mail_port">SMTP Port</label>
            <select id="mail_port" name="mail_port" class="iz-input">
              <option value="465" {{ old('mail_port','465')==='465'?'selected':'' }}>465 (SSL)</option>
              <option value="587" {{ old('mail_port')==='587'?'selected':'' }}>587 (TLS)</option>
              <option value="25"  {{ old('mail_port')==='25' ?'selected':'' }}>25 (Plain)</option>
            </select>
          </div>
        </div>
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="mail_username">SMTP Username <span class="iz-label-opt">(required)</span></label>
            <input type="text" id="mail_username" name="mail_username" class="iz-input" value="{{ old('mail_username') }}" placeholder="noreply@yourgroup.net" required>
            @error('mail_username')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
          <div class="iz-field">
            <label class="iz-label" for="mail_password">SMTP Password <span class="iz-label-opt">(required)</span></label>
            <input type="password" id="mail_password" name="mail_password" class="iz-input" value="{{ old('mail_password') }}" placeholder="Your email password" required>
            @error('mail_password')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="mail_from_address">From Address <span class="iz-label-opt">(required)</span></label>
            <input type="email" id="mail_from_address" name="mail_from_address" class="iz-input" value="{{ old('mail_from_address') }}" placeholder="noreply@yourgroup.net" required>
          </div>
          <div class="iz-field">
            <label class="iz-label" for="mail_from_name">From Name</label>
            <input type="text" id="mail_from_name" name="mail_from_name" class="iz-input" value="{{ old('mail_from_name') }}" placeholder="Your RAYNET Group" id="mail_from_name">
            <div class="iz-hint">Auto-filled from group name.</div>
          </div>
        </div>
        <div class="iz-divider"></div>
        <div class="iz-field">
          <label class="iz-label">🔍 QRZ XML Lookup <span class="iz-label-opt">(optional but recommended)</span></label>
          <div class="iz-hint" style="margin-bottom:.75rem;">Used to auto-fill operator details (name, location, licence class) during net logging and member management. Requires a QRZ.com XML subscription. <a href="https://www.qrz.com/page/xml_data.html" target="_blank">Learn more</a>.</div>
        </div>
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="qrz_username">QRZ Username <span class="iz-label-opt">(optional)</span></label>
            <input type="text" id="qrz_username" name="qrz_username" class="iz-input iz-mono" value="{{ old('qrz_username') }}" placeholder="Your QRZ callsign">
          </div>
          <div class="iz-field">
            <label class="iz-label" for="qrz_password">QRZ Password <span class="iz-label-opt">(optional)</span></label>
            <input type="password" id="qrz_password" name="qrz_password" class="iz-input" value="{{ old('qrz_password') }}" placeholder="Your QRZ password">
            <div class="iz-hint">Can be added later via Admin → Settings if you don't have it now.</div>
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
  @elseif($step === 'step2')
  <div class="iz-card">
    <div class="iz-card-stripe"></div>
    <div class="iz-card-head">
      <div class="iz-head-icon">🔐</div>
      <div>
        <div class="iz-card-title">Create Your Admin Account</div>
        <div class="iz-card-sub">This will be the first super-administrator. More admins can be added later.</div>
      </div>
    </div>
    @if(isset($dryRun))
    <div class="iz-summary" style="margin:1.25rem 1.75rem 0">
      <div class="iz-summary-head">{{ $dryRun['title'] }}</div>
      <div style="padding:.25rem 0">
        @foreach($dryRun['items'] as $item)
        <div class="iz-summary-row">
          <span class="iz-summary-key">{{ $item['key'] }}</span>
          <span class="iz-summary-val">{{ $item['value'] }}</span>
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
        <div class="iz-alert iz-alert-err">
          <span class="iz-alert-icon">⚠</span>
          <div>Please fix the errors highlighted below.</div>
        </div>
        @endif
        <div class="iz-row">
          <div class="iz-field">
            <label class="iz-label" for="name">Full Name <span class="iz-label-opt">(required)</span></label>
            <input type="text" id="name" name="name" class="iz-input" value="{{ old('name') }}" placeholder="e.g. John Smith" required autofocus>
            @error('name')<div class="iz-err">{{ $message }}</div>@enderror
          </div>
          <div class="iz-field">
            <label class="iz-label" for="callsign">Callsign <span class="iz-label-opt">(required)</span></label>
            <input type="text" id="callsign" name="callsign" class="iz-input iz-mono" value="{{ old('callsign') }}" placeholder="e.g. M0XYZ" required oninput="this.value=this.value.toUpperCase()">
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
  @elseif($step === 'step3')
  <div class="iz-card">
    <div class="iz-card-stripe"></div>
    @if(isset($dryRun))
    <div class="iz-summary" style="margin:1.25rem 1.75rem 0">
      <div class="iz-summary-head">{{ $dryRun['title'] }}</div>
      <div style="padding:.25rem 0">
        @foreach($dryRun['items'] as $item)
        <div class="iz-summary-row">
          <span class="iz-summary-key">{{ $item['key'] }}</span>
          <span class="iz-summary-val">{{ $item['value'] }}</span>
        </div>
        @endforeach
      </div>
    </div>
    @endif
    <div class="iz-card-body">
      <div class="iz-complete">
        <span class="iz-complete-icon">🎉</span>
        <h2 class="iz-complete-title">{{ $groupName ?? 'Your Group' }} is ready!</h2>
        <p class="iz-complete-sub">Your RAYNET-OS portal has been configured. Click <strong>Launch</strong> to log in and get started.</p>
      </div>
      <div class="iz-checklist">
        <div class="iz-check">Group details saved</div>
        <div class="iz-check">Admin account created</div>
        <div class="iz-check">Database configured</div>
        <div class="iz-check">Roles &amp; permissions seeded</div>
      </div>
      <div class="iz-next">
        <strong>After logging in:</strong><br>
        • Go to <strong>Admin → Settings</strong> to upload your group logo and customise colours<br>
        • Go to <strong>Admin → Pages</strong> to edit your Home, About &amp; Training pages<br>
        • Visit <strong>Module Manager</strong> to install additional features
      </div>
    </div>
    <div class="iz-card-foot">
      <span style="font-size:.7rem;color:#94a3b8">RAYNET-OS · Powered by RAYNET Liverpool</span>
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
    RAYNET-OS &middot; Built for <a href="https://www.raynet-uk.net" target="_blank">RAYNET UK</a> &middot;
    <a href="https://github.com/raynet-uk/raynet-cms" target="_blank">GitHub</a> &middot;
    73 de RAYNET Liverpool 📻
  </div>
</div>
<script>
function getInitials(name) {
  if (!name) return 'RAY\nNET';
  const words = name.trim().split(/\s+/).filter(Boolean);
  if (words.length === 1) return words[0].substring(0, 4).toUpperCase();
  return words.slice(0, 2).map(w => w[0]).join('').toUpperCase();
}
function updatePreview() {
  const name = document.getElementById('group_name')?.value || '';
    const mailFromName = document.getElementById('mail_from_name');
    if (mailFromName && !mailFromName.value) mailFromName.value = name;
  const callsign = document.getElementById('group_callsign')?.value || '';
  const prevName = document.getElementById('prev-name');
  const prevCall = document.getElementById('prev-call');
  const prevLogo = document.getElementById('prev-logo');
  const hdrName  = document.getElementById('hdr-name');
  const hdrLogo  = document.getElementById('hdr-logo-text');
  if (prevName) prevName.textContent = name || 'Your Group Name';
  if (prevCall) prevCall.textContent = callsign || 'CALLSIGN';
  const initials = getInitials(name);
  const short = initials.length <= 2 ? initials.split('').join('<br>') : initials.substring(0,4);
  if (prevLogo) prevLogo.innerHTML = short;
  if (hdrLogo)  hdrLogo.innerHTML  = (initials.length <= 3 ? initials : initials.substring(0,3)).split('').join('<br>');
  if (hdrName)  hdrName.textContent = name || 'RAYNET-OS';
}
async function validateLicence() {
  const key    = document.getElementById('licence_key')?.value.trim();
  const status = document.getElementById('licence-status');
  const btn    = document.getElementById('validate-btn');
  if (!key) { status.innerHTML = '<span style="color:#C8102E">⚠ Please enter a licence key first.</span>'; return; }
  btn.textContent = 'Checking…'; btn.disabled = true;
  status.innerHTML = '<span style="color:#64748b">Contacting licence server…</span>';
  try {
    const resp = await fetch('https://command.nathandillon.co.uk/api/cms/validate-key', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ key })
    });
    const data = await resp.json();
    if (data.valid) {
      status.innerHTML = '<span style="color:#166534">✓ Valid licence — details pre-filled below</span>';
      if (data.group_name)   document.getElementById('group_name').value   = data.group_name;
      if (data.group_number) document.getElementById('group_number').value = data.group_number;
      if (data.gc_name)      document.getElementById('gc_name').value      = data.gc_name;
      if (data.gc_email) {
        document.getElementById('gc_email').value              = data.gc_email;
        document.getElementById('support_request_email').value = data.gc_email;
      }
      updatePreview();
    } else {
      status.innerHTML = '<span style="color:#C8102E">✗ ' + (data.message || 'Invalid licence key.') + '</span>';
    }
  } catch(e) {
    status.innerHTML = '<span style="color:#d97706">⚠ Could not reach licence server — continue manually.</span>';
  }
  btn.textContent = 'Validate'; btn.disabled = false;
}
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
</body>
</html>
