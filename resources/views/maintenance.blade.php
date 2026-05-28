<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — {{ \App\Helpers\RaynetSetting::groupName() }}</title>
    <style>
        :root {
            --navy:  #003366; --navy-2: #002244; --navy-3: #001428; --navy-4: #000d1a;
            --red:   #C8102E; --red-g:  rgba(200,16,46,.18);
            --white: #ffffff;
            --font:  Arial,'Helvetica Neue',Helvetica,sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { font-family: var(--font); background: var(--navy-4); color: var(--white);
               min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }

        /* ── BACKGROUND ── */
        .bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .bg-gradient {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 90% 60% at 65% 15%, rgba(0,51,102,.55) 0%, transparent 55%),
                radial-gradient(ellipse 60% 80% at 5% 90%,  rgba(0,20,50,.7)   0%, transparent 55%),
                linear-gradient(170deg, #000d1a 0%, #001428 45%, #000d1a 100%);
        }
        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.022) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 20%, transparent 75%);
        }
        .radar-wrap { position: absolute; top: 50%; left: 62%; transform: translate(-50%, -50%);
                      width: 800px; height: 800px; opacity: .055; }
        .radar-ring { position: absolute; border-radius: 50%; border: 1px solid var(--red);
                      top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .radar-ring:nth-child(1){width:140px;height:140px;}
        .radar-ring:nth-child(2){width:280px;height:280px;opacity:.85;}
        .radar-ring:nth-child(3){width:420px;height:420px;opacity:.7;}
        .radar-ring:nth-child(4){width:560px;height:560px;opacity:.5;}
        .radar-ring:nth-child(5){width:700px;height:700px;opacity:.3;}
        .radar-ring:nth-child(6){width:800px;height:800px;opacity:.15;}
        .radar-h,.radar-v { position:absolute; background:rgba(200,16,46,.3); top:50%; left:50%; }
        .radar-h { width:800px; height:1px; transform:translate(-50%,-50%); }
        .radar-v { width:1px; height:800px; transform:translate(-50%,-50%); }
        .radar-sweep { position:absolute; top:50%; left:50%; width:400px; height:400px;
            transform-origin:0 0;
            background:conic-gradient(from 0deg,transparent 330deg,rgba(200,16,46,.12) 360deg);
            animation:sweep 5s linear infinite; }
        .radar-arm { position:absolute; top:50%; left:50%; width:400px; height:1px;
            background:linear-gradient(90deg,rgba(200,16,46,.6),transparent);
            transform-origin:0 50%; animation:sweep 5s linear infinite; }
        @keyframes sweep { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }
        .radar-blip { position:absolute; width:6px; height:6px; border-radius:50%;
            background:rgba(200,16,46,.7); box-shadow:0 0 8px rgba(200,16,46,.5);
            top:50%; left:50%; transform:translate(-50%,-50%);
            animation:blipFade 5s ease-in-out infinite; }
        .radar-blip:nth-child(8) {margin-top:-80px;margin-left:60px;animation-delay:0s;}
        .radar-blip:nth-child(9) {margin-top:110px;margin-left:-70px;animation-delay:1.2s;}
        .radar-blip:nth-child(10){margin-top:-40px;margin-left:-130px;animation-delay:2.8s;}
        .radar-blip:nth-child(11){margin-top:55px;margin-left:160px;animation-delay:0.7s;}
        @keyframes blipFade { 0%,80%,100%{opacity:0;} 85%,95%{opacity:1;} }
        .glow-orb { position:absolute; border-radius:50%; filter:blur(70px); }
        .glow-red  { width:550px;height:550px;top:-200px;right:-100px;
            background:radial-gradient(circle,rgba(200,16,46,.1) 0%,transparent 70%);
            animation:orbDrift 13s ease-in-out infinite alternate; }
        .glow-navy { width:450px;height:450px;bottom:-150px;left:-80px;
            background:radial-gradient(circle,rgba(0,51,102,.4) 0%,transparent 70%);
            animation:orbDrift 17s ease-in-out infinite alternate-reverse; }
        @keyframes orbDrift { from{transform:translate(0,0);} to{transform:translate(45px,35px);} }
        .scan { position:absolute; left:0; right:0; height:2px;
            background:linear-gradient(90deg,transparent 0%,rgba(200,16,46,.25) 30%,rgba(200,16,46,.35) 50%,rgba(200,16,46,.25) 70%,transparent 100%);
            animation:scanDown 9s linear infinite; }
        @keyframes scanDown { 0%{top:0;opacity:0;} 3%{opacity:1;} 97%{opacity:1;} 100%{top:100%;opacity:0;} }

        /* ── SHELL ── */
        .shell { position:relative; z-index:1; flex:1; display:flex; flex-direction:column; min-height:100vh; }

        /* ── TOP BAR ── */
        .top-bar {
            display:flex; align-items:center; justify-content:space-between;
            padding:1.1rem 2.5rem; border-bottom:1px solid rgba(255,255,255,.06);
            background:rgba(0,10,24,.5); backdrop-filter:blur(6px);
            gap:1rem; flex-wrap:wrap;
        }
        .logo-img { height:36px; width:auto; display:block; }
        .logo-fallback { display:none; align-items:center; gap:.75rem; }
        .logo-block { background:var(--red); width:36px; height:36px;
            display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .logo-block span { font-size:9px; font-weight:700; color:#fff;
            letter-spacing:.05em; text-align:center; line-height:1.2; text-transform:uppercase; }
        .logo-name { font-size:13px; font-weight:700; color:#fff; letter-spacing:.06em; text-transform:uppercase; }
        .logo-sub  { font-size:9px; color:rgba(255,255,255,.3); letter-spacing:.1em; text-transform:uppercase; margin-top:1px; }
        .top-right { display:flex; align-items:center; gap:.85rem; flex-wrap:wrap; }
        .utc-block { display:flex; flex-direction:column; align-items:flex-end; }
        .utc-label { font-size:8px; font-weight:700; color:rgba(255,255,255,.2); text-transform:uppercase; letter-spacing:.14em; }
        .utc-time  { font-size:15px; font-weight:700; color:rgba(255,255,255,.45); letter-spacing:.12em; font-variant-numeric:tabular-nums; }
        .status-pill {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.3rem .9rem; border:1px solid rgba(200,16,46,.4);
            background:rgba(200,16,46,.08); font-size:10px; font-weight:700;
            color:#fca5a5; text-transform:uppercase; letter-spacing:.1em;
        }
        .pulse-dot { width:6px; height:6px; border-radius:50%; background:#f87171;
            flex-shrink:0; animation:dotPulse 1.6s ease-in-out infinite; }
        @keyframes dotPulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.15;transform:scale(.65);} }

        /* ── MAIN GRID ── */
        .main {
            flex:1; display:grid; grid-template-columns:1fr 380px; gap:2rem;
            align-items:start; padding:2.5rem 2.5rem 2rem;
            max-width:1200px; margin:0 auto; width:100%;
        }
        .col-left { padding-right:1rem; }
        .eyebrow {
            font-size:10px; font-weight:700; color:var(--red);
            text-transform:uppercase; letter-spacing:.22em;
            margin-bottom:1rem; display:flex; align-items:center; gap:.65rem;
        }
        .eyebrow-dash { width:28px; height:2px; background:var(--red); flex-shrink:0; }
        h1 {
            font-size:clamp(46px,6.5vw,82px); font-weight:700; line-height:.98;
            letter-spacing:-.025em; color:#fff; margin-bottom:1.5rem; text-transform:uppercase;
        }
        h1 .fade { color:rgba(255,255,255,.18); }
        h1 .red  { color:var(--red); }
        .headline { font-size:11px; font-weight:700; color:rgba(200,16,46,.65);
            text-transform:uppercase; letter-spacing:.18em; margin-bottom:.7rem; }
        .message-text { font-size:14px; font-weight:400; color:rgba(255,255,255,.38);
            line-height:1.85; max-width:480px; margin-bottom:2rem; }

        .chips { display:flex; gap:.55rem; flex-wrap:wrap; margin-bottom:2rem; }
        .chip { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .8rem;
            font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; border:1px solid; }
        .chip-dot { width:5px; height:5px; border-radius:50%; flex-shrink:0; }
        .c-red  { border-color:rgba(200,16,46,.4);  background:rgba(200,16,46,.07);  color:#fca5a5; }
        .c-red   .chip-dot { background:var(--red); }
        .c-green { border-color:rgba(74,222,128,.3); background:rgba(74,222,128,.06); color:#86efac; }
        .c-green .chip-dot { background:#4ade80; box-shadow:0 0 6px rgba(74,222,128,.4); }
        .c-grey  { border-color:rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:rgba(255,255,255,.35); }
        .c-grey  .chip-dot { background:rgba(255,255,255,.25); }
        .c-amber { border-color:rgba(251,191,36,.3); background:rgba(251,191,36,.06); color:#fde68a; }
        .c-amber .chip-dot { background:#fbbf24; }

        .admin-sep { display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem; }
        .admin-sep-line { flex:1; max-width:36px; height:1px; background:rgba(255,255,255,.07); }
        .admin-sep-text { font-size:9px; font-weight:700; color:rgba(255,255,255,.15); text-transform:uppercase; letter-spacing:.18em; }
        .admin-link {
            display:inline-flex; align-items:center; gap:.6rem;
            padding:.58rem 1.4rem; border:1px solid rgba(255,255,255,.1);
            background:rgba(255,255,255,.03); color:rgba(255,255,255,.3);
            font-family:var(--font); font-size:11px; font-weight:700;
            text-decoration:none; text-transform:uppercase; letter-spacing:.09em; transition:all .2s;
        }
        .admin-link:hover { border-color:rgba(255,255,255,.22); background:rgba(255,255,255,.07); color:rgba(255,255,255,.65); }

        /* ── RIGHT COL ── */
        .col-right { display:flex; flex-direction:column; gap:.85rem; padding-top:.5rem; }

        .icon-panel { position:relative; height:180px; display:flex; align-items:center; justify-content:center; margin-bottom:.25rem; }
        .ring { position:absolute; border-radius:50%; top:50%; left:50%; transform:translate(-50%,-50%); }
        .ring-a { width:165px;height:165px;border:1px solid rgba(200,16,46,.1);animation:ringCW 22s linear infinite; }
        .ring-b { width:128px;height:128px;border:1px dashed rgba(200,16,46,.15);animation:ringCCW 15s linear infinite; }
        .ring-c { width:92px; height:92px; border:1px solid rgba(200,16,46,.22);animation:ringCW 10s linear infinite; }
        .ring-a::after { content:''; position:absolute; top:-4px; left:50%; transform:translateX(-50%);
            width:8px;height:8px;border-radius:50%;background:var(--red);box-shadow:0 0 14px var(--red); }
        @keyframes ringCW  { from{transform:translate(-50%,-50%) rotate(0);}   to{transform:translate(-50%,-50%) rotate(360deg);}  }
        @keyframes ringCCW { from{transform:translate(-50%,-50%) rotate(0);}   to{transform:translate(-50%,-50%) rotate(-360deg);} }
        .icon-core { position:relative;z-index:2; width:66px;height:66px;border-radius:50%;
            background:rgba(200,16,46,.1); border:1px solid rgba(200,16,46,.3);
            display:flex;align-items:center;justify-content:center;font-size:26px;
            animation:iconBob 5s ease-in-out infinite;
            box-shadow:0 0 35px rgba(200,16,46,.15),inset 0 0 20px rgba(200,16,46,.04); }
        @keyframes iconBob { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-7px);} }

        /* Panel cards */
        .panel { border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.03); backdrop-filter:blur(8px); overflow:hidden; }
        .panel-head { padding:.55rem .9rem; background:rgba(255,255,255,.04); border-bottom:1px solid rgba(255,255,255,.06);
            display:flex; align-items:center; justify-content:space-between; }
        .panel-title { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.18em;
            color:rgba(255,255,255,.25); display:flex; align-items:center; gap:.45rem; }
        .panel-title::before { content:'//'; color:rgba(200,16,46,.6); font-size:10px; }
        .panel-badge { font-size:9px; font-weight:700; padding:1px 6px; text-transform:uppercase; letter-spacing:.06em; }
        .panel-body { padding:.6rem .9rem; }

        /* System status */
        .sys-row { display:flex; align-items:center; justify-content:space-between;
            padding:.38rem 0; border-bottom:1px solid rgba(255,255,255,.04); gap:.5rem; }
        .sys-row:last-child { border-bottom:none; }
        .sys-name { font-size:11px; font-weight:700; color:rgba(255,255,255,.4); }
        .sys-right { display:flex; align-items:center; gap:.5rem; }
        .sys-bar-wrap { width:60px; height:3px; background:rgba(255,255,255,.07); }
        .sys-bar { height:100%; transition:width .6s ease; }
        .sys-val { font-size:10px; font-weight:700; min-width:52px; text-align:right; }
        .sv-ok   { color:#86efac; }
        .sv-off  { color:#fca5a5; }
        .sv-part { color:#fde68a; }
        .sb-ok   { background:rgba(74,222,128,.7); }
        .sb-off  { background:rgba(200,16,46,.7); }
        .sb-part { background:rgba(251,191,36,.7); animation:partBlink 1.5s ease-in-out infinite; }
        @keyframes partBlink { 0%,100%{opacity:1;} 50%{opacity:.4;} }
        .maint-timer-row { display:flex; align-items:center; justify-content:space-between;
            padding:.65rem .9rem; border-top:1px solid rgba(255,255,255,.05); background:rgba(255,255,255,.02); }
        .mt-label { font-size:9px; font-weight:700; color:rgba(255,255,255,.2); text-transform:uppercase; letter-spacing:.14em; }
        .mt-value { font-size:13px; font-weight:700; color:rgba(255,255,255,.4); letter-spacing:.1em; font-variant-numeric:tabular-nums; }

        /* Countdown */
        .countdown-panel { border:1px solid rgba(200,16,46,.25); background:rgba(200,16,46,.05); display:none; }
        .countdown-panel.visible { display:block; }
        .cd-body { padding:.75rem .9rem; }
        .cd-digits { display:flex; align-items:flex-end; gap:.3rem; }
        .cd-unit { text-align:center; }
        .cd-num { font-size:36px; font-weight:700; color:#fff; line-height:1; letter-spacing:-.02em; display:block; font-variant-numeric:tabular-nums; }
        .cd-lbl { font-size:8px; font-weight:700; color:rgba(255,255,255,.2); text-transform:uppercase; letter-spacing:.1em; display:block; margin-top:2px; }
        .cd-sep { font-size:30px; font-weight:700; color:rgba(200,16,46,.4); line-height:1; margin-bottom:4px; }
        .cd-return { font-size:9px; color:rgba(255,180,180,.4); letter-spacing:.08em; margin-top:.5rem; }

        /* Auto-disable */
        .auto-panel { border:1px solid rgba(74,222,128,.15); background:rgba(74,222,128,.04); display:none; }
        .auto-panel.visible { display:block; }
        .auto-body { padding:.75rem .9rem; font-size:10px; color:rgba(134,239,172,.5); line-height:1.7; }
        .auto-body strong { color:rgba(134,239,172,.75); font-weight:700; }
        .auto-body code { background:rgba(134,239,172,.08); border:1px solid rgba(134,239,172,.15);
            padding:0 4px; font-family:'Courier New',monospace; font-size:10px; color:rgba(134,239,172,.8); }

        /* Contact */
        .contact-panel { border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02); display:none; }
        .contact-panel.visible { display:flex; align-items:center; gap:.75rem; padding:.7rem .9rem; }
        .contact-icon { font-size:14px; opacity:.5; flex-shrink:0; }
        .contact-label { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:rgba(255,255,255,.2); }
        .contact-val   { font-size:11px; color:rgba(255,255,255,.5); margin-top:2px; }

        /* Activity log */
        .log-entry { display:flex; align-items:flex-start; gap:.55rem;
            padding:.42rem .9rem; border-bottom:1px solid rgba(255,255,255,.04);
            animation:logFade .4s ease both; }
        .log-entry:last-child { border-bottom:none; }
        @keyframes logFade { from{opacity:0;transform:translateX(-4px);} to{opacity:1;transform:none;} }
        .log-time { font-size:9px; color:rgba(255,255,255,.2); font-variant-numeric:tabular-nums; min-width:44px; padding-top:1px; letter-spacing:.04em; }
        .log-dot  { width:5px; height:5px; border-radius:50%; flex-shrink:0; margin-top:4px; }
        .log-text { font-size:10px; color:rgba(255,255,255,.35); line-height:1.5; flex:1; }
        .ld-red   { background:rgba(200,16,46,.8); }
        .ld-amber { background:rgba(251,191,36,.7); }
        .ld-green { background:rgba(74,222,128,.7); }

        /* ── FOOTER ── */
        .footer { display:flex; align-items:center; justify-content:space-between;
            padding:.9rem 2.5rem; border-top:1px solid rgba(255,255,255,.05);
            background:rgba(0,10,24,.5); backdrop-filter:blur(6px);
            gap:1rem; flex-wrap:wrap; }
        .footer-left { font-size:10px; color:rgba(255,255,255,.15); letter-spacing:.04em; }
        .footer-stats { display:flex; align-items:center; gap:1.25rem; }
        .f-stat { text-align:right; }
        .f-val  { font-size:13px; font-weight:700; color:rgba(255,255,255,.18); letter-spacing:.04em; }
        .f-lbl  { font-size:8px; font-weight:700; color:rgba(255,255,255,.1); text-transform:uppercase; letter-spacing:.1em; }
        .footer-vsep { width:1px; height:22px; background:rgba(255,255,255,.06); }

        /* Morse strip */
        .morse-strip { position:fixed; bottom:44px; left:0; right:0;
            overflow:hidden; pointer-events:none; z-index:0; opacity:.055; }
        .morse-inner { display:flex; align-items:center; gap:3px; padding:5px 2.5rem;
            animation:morseMove 38s linear infinite; white-space:nowrap; }
        @keyframes morseMove { from{transform:translateX(0);} to{transform:translateX(-50%);} }
        .md { width:4px;  height:4px;  border-radius:50%; background:var(--red); flex-shrink:0; }
        .ms { width:12px; height:3px;  background:var(--red); flex-shrink:0; }
        .mg { width:7px;  flex-shrink:0; }
        .mw { width:18px; flex-shrink:0; }

        /* ── RESPONSIVE ── */
        @media(max-width:860px) {
            .main { grid-template-columns:1fr; gap:2rem; padding:2rem 1.5rem; }
            .col-right { display:grid; grid-template-columns:1fr 1fr; }
            .icon-panel { display:none; }
            h1 { font-size:50px; }
            .radar-wrap { left:50%; opacity:.04; }
        }
        @media(max-width:560px) {
            .top-bar { padding:1rem 1.25rem; }
            .utc-block { display:none; }
            .col-right { grid-template-columns:1fr; }
            h1 { font-size:40px; }
            .footer { flex-direction:column; text-align:center; gap:.5rem; }
            .footer-stats { justify-content:center; }
        }
    </style>
</head>
<body>

<div class="bg">
    <div class="bg-gradient"></div>
    <div class="bg-grid"></div>
    <div class="radar-wrap">
        <div class="radar-ring"></div><div class="radar-ring"></div>
        <div class="radar-ring"></div><div class="radar-ring"></div>
        <div class="radar-ring"></div><div class="radar-ring"></div>
        <div class="radar-h"></div><div class="radar-v"></div>
        <div class="radar-sweep"></div><div class="radar-arm"></div>
        <div class="radar-blip"></div><div class="radar-blip"></div>
        <div class="radar-blip"></div><div class="radar-blip"></div>
    </div>
    <div class="glow-orb glow-red"></div>
    <div class="glow-orb glow-navy"></div>
    <div class="scan"></div>
</div>

<div class="morse-strip"><div class="morse-inner" id="morseTicker"></div></div>

<div class="shell">

    <div class="top-bar">
        <img class="logo-img" src="/images/raynet-uk-liverpool-banner.png" alt="{{ \App\Helpers\RaynetSetting::groupName() }}"
             onerror="this.style.display='none';document.getElementById('logo-fb').style.display='flex';">
        <div id="logo-fb" class="logo-fallback">
            <div class="logo-block"><span>RAY<br>NET</span></div>
            <div><div class="logo-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div><div class="logo-sub">Members Portal</div></div>
        </div>
        <div class="top-right">
            <div class="utc-block">
                <div class="utc-label">UTC</div>
                <div class="utc-time" id="utcClock">--:--:--</div>
            </div>
            <div class="status-pill"><span class="pulse-dot"></span>Maintenance Active</div>
        </div>
    </div>

    <div class="main">

        <div class="col-left">
            <div class="eyebrow"><span class="eyebrow-dash"></span>System Notice</div>

            <h1>
                @if(!empty($maintTitle))
                    {!! nl2br(e($maintTitle)) !!}
                @else
                    We'll be<br><span class="fade">back</span><br><span class="red">shortly.</span>
                @endif
            </h1>

            @if(!empty($maintHeadline))
            <div class="headline">{{ $maintHeadline }}</div>
            @endif

            <p class="message-text">
                {{ $message ?: 'The ' . \App\Helpers\RaynetSetting::groupName() . ' Members Portal is currently undergoing scheduled maintenance. All services will be restored as soon as possible. We apologise for any inconvenience caused.' }}
            </p>

            <div class="chips">
                <div class="chip c-red"><span class="chip-dot"></span>Portal Offline</div>
                <div class="chip c-green"><span class="chip-dot"></span>Admins Unaffected</div>
                @if(!empty($maintContact))
                <div class="chip c-amber"><span class="chip-dot"></span>Support Available</div>
                @endif
                <div class="chip c-grey"><span class="chip-dot"></span>Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
                <div class="chip c-grey"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupRegion() }}</div>
            </div>

            <div class="admin-sep">
                <span class="admin-sep-line"></span>
                <span class="admin-sep-text">For administrators</span>
            </div>
            <a href="/admin" class="admin-link">⚙ Admin Panel Access</a>
        </div>

        <div class="col-right">

            <div class="icon-panel">
                <div class="ring ring-a"></div>
                <div class="ring ring-b"></div>
                <div class="ring ring-c"></div>
                <div class="icon-core">🔧</div>
            </div>

            {{-- System status --}}
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">System Status</div>
                    <span class="panel-badge" style="background:rgba(200,16,46,.12);border:1px solid rgba(200,16,46,.25);color:#fca5a5;">Maintenance</span>
                </div>
                <div class="panel-body">
                    <div class="sys-row">
                        <span class="sys-name">Member Portal</span>
                        <div class="sys-right">
                            <div class="sys-bar-wrap"><div class="sys-bar sb-off" style="width:0%;"></div></div>
                            <span class="sys-val sv-off">Offline</span>
                        </div>
                    </div>
                    <div class="sys-row">
                        <span class="sys-name">Database</span>
                        <div class="sys-right">
                            <div class="sys-bar-wrap"><div class="sys-bar sb-part" style="width:35%;"></div></div>
                            <span class="sys-val sv-part">Limited</span>
                        </div>
                    </div>
                    <div class="sys-row">
                        <span class="sys-name">Admin Panel</span>
                        <div class="sys-right">
                            <div class="sys-bar-wrap"><div class="sys-bar sb-ok" style="width:100%;"></div></div>
                            <span class="sys-val sv-ok">Online</span>
                        </div>
                    </div>
                    <div class="sys-row">
                        <span class="sys-name">API Routes</span>
                        <div class="sys-right">
                            <div class="sys-bar-wrap"><div class="sys-bar sb-ok" style="width:100%;"></div></div>
                            <span class="sys-val sv-ok">Online</span>
                        </div>
                    </div>
                    <div class="sys-row">
                        <span class="sys-name">Email System</span>
                        <div class="sys-right">
                            <div class="sys-bar-wrap"><div class="sys-bar sb-ok" style="width:100%;"></div></div>
                            <span class="sys-val sv-ok">Online</span>
                        </div>
                    </div>
                </div>
                <div class="maint-timer-row">
                    <span class="mt-label">Duration</span>
                    <span class="mt-value" id="maintDuration">—</span>
                </div>
            </div>

            {{-- Countdown --}}
            <div class="countdown-panel panel" id="countdownPanel">
                <div class="panel-head">
                    <div class="panel-title">Expected Return</div>
                </div>
                <div class="cd-body">
                    <div class="cd-digits">
                        <div class="cd-unit"><span class="cd-num" id="cdH">00</span><span class="cd-lbl">Hours</span></div>
                        <span class="cd-sep">:</span>
                        <div class="cd-unit"><span class="cd-num" id="cdM">00</span><span class="cd-lbl">Mins</span></div>
                        <span class="cd-sep">:</span>
                        <div class="cd-unit"><span class="cd-num" id="cdS">00</span><span class="cd-lbl">Secs</span></div>
                    </div>
                    <div class="cd-return" id="cdReturnLabel"></div>
                </div>
            </div>

            {{-- Auto-disable notice --}}
            <div class="auto-panel" id="autoPanel">
                <div class="panel-head" style="border-color:rgba(74,222,128,.1);background:rgba(74,222,128,.04);">
                    <div class="panel-title" style="color:rgba(134,239,172,.4);">Auto-disable Active</div>
                    <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,.5);display:inline-block;animation:dotPulse 2s ease-in-out infinite;"></span>
                </div>
                <div class="auto-body">
                    <strong>Scheduled disable configured.</strong><br>
                    Maintenance will disengage automatically at the scheduled time.
                    Requires the <code>lms:daily</code> scheduled command to be running,
                    or a cron for the Laravel scheduler:
                    <code>* * * * * php artisan schedule:run</code>
                </div>
            </div>

            {{-- Contact --}}
            <div class="contact-panel" id="contactPanel">
                <div class="contact-icon">✉</div>
                <div>
                    <div class="contact-label">Group Controller Enquiries</div>
                    <div class="contact-val" id="contactVal"></div>
                </div>
            </div>

            {{-- Activity log --}}
            <div class="log-panel panel">
                <div class="panel-head">
                    <div class="panel-title">Activity Log</div>
                </div>
                <div id="logEntries"></div>
            </div>

        </div>
    </div>

    <div class="footer">
        <div class="footer-left">
            {{ \App\Helpers\RaynetSetting::groupName() }} &nbsp;·&nbsp; Volunteer Emergency Communications &nbsp;·&nbsp; Affiliated to RAYNET-UK
        </div>
        <div class="footer-stats">
            <div class="f-stat"><div class="f-val">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div><div class="f-lbl">Group Ref</div></div>
            <div class="footer-vsep"></div>
            <div class="f-stat"><div class="f-val">{{ \App\Helpers\RaynetSetting::groupRegion() }}</div><div class="f-lbl">Area</div></div>
            <div class="footer-vsep"></div>
            <div class="f-stat"><div class="f-val" id="footerTimer">—</div><div class="f-lbl">Offline Duration</div></div>
        </div>
    </div>

</div>

<script>
(function(){

// UTC Clock
function tick(){
    var d=new Date(),h=String(d.getUTCHours()).padStart(2,'0'),
        m=String(d.getUTCMinutes()).padStart(2,'0'),s=String(d.getUTCSeconds()).padStart(2,'0');
    var el=document.getElementById('utcClock');
    if(el) el.textContent=h+':'+m+':'+s;
}
tick(); setInterval(tick,1000);

// Maintenance duration
@if(!empty($maintStarted))
var mStart=new Date('{{ $maintStarted }}').getTime();
function updateDur(){
    var e=Math.max(0,Math.floor((Date.now()-mStart)/1000));
    var h=Math.floor(e/3600),m=Math.floor((e%3600)/60),s=e%60;
    var str=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    ['maintDuration','footerTimer'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=str;});
}
updateDur(); setInterval(updateDur,1000);
@else
function updateDur(){
    var d=new Date(),h=String(d.getUTCHours()).padStart(2,'0'),m=String(d.getUTCMinutes()).padStart(2,'0');
    ['maintDuration','footerTimer'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=h+':'+m+' UTC';});
}
updateDur(); setInterval(updateDur,60000);
@endif

// Countdown
@if(!empty($maintReturnAt))
var retAt=new Date('{{ $maintReturnAt }}').getTime();
var cp=document.getElementById('countdownPanel'); if(cp)cp.classList.add('visible');
var rl=document.getElementById('cdReturnLabel');
if(rl){var rd=new Date(retAt);rl.textContent='Expected: '+rd.toLocaleString('en-GB',{weekday:'short',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});}
function cd(){
    var rem=Math.max(0,Math.floor((retAt-Date.now())/1000));
    document.getElementById('cdH').textContent=String(Math.floor(rem/3600)).padStart(2,'0');
    document.getElementById('cdM').textContent=String(Math.floor((rem%3600)/60)).padStart(2,'0');
    document.getElementById('cdS').textContent=String(rem%60).padStart(2,'0');
    if(rem===0){var rl2=document.getElementById('cdReturnLabel');if(rl2)rl2.textContent='Due online now — please refresh.';}
}
cd(); setInterval(cd,1000);
@endif

// Auto-disable
@if(!empty($maintAutoOff))
var ap=document.getElementById('autoPanel'); if(ap)ap.classList.add('visible');
@endif

// Contact
@if(!empty($maintContact))
var xp=document.getElementById('contactPanel'); if(xp)xp.classList.add('visible');
var xv=document.getElementById('contactVal');   if(xv)xv.textContent='{{ $maintContact }}';
@endif

// Activity log
var LOGS=[
    {dot:'ld-red',  text:'Member portal suspended — maintenance mode engaged'},
    {dot:'ld-amber',text:'Database connections limited to admin processes'},
    {dot:'ld-green',text:'Admin panel access confirmed active'},
    {dot:'ld-green',text:'API routes operational — external integrations unaffected'},
    {dot:'ld-amber',text:'Session store in maintenance state'},
    {dot:'ld-green',text:'Email service online'},
    {dot:'ld-red',  text:'LMS portal access suspended'},
    {dot:'ld-green',text:'Static assets serving normally'},
];
var logContainer=document.getElementById('logEntries');
var li=0;
function addLog(){
    if(!logContainer||li>=LOGS.length) return;
    var msg=LOGS[li++];
    var now=new Date(),ts=String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0');
    var div=document.createElement('div');
    div.className='log-entry';
    div.innerHTML='<span class="log-time">'+ts+'</span><span class="log-dot '+msg.dot+'"></span><span class="log-text">'+msg.text+'</span>';
    logContainer.appendChild(div);
    var entries=logContainer.querySelectorAll('.log-entry');
    if(entries.length>6) entries[0].remove();
}
LOGS.forEach(function(_,i){setTimeout(addLog,i*420);});

// Morse ticker — RAYNET: .-. .- -.-- -. . -
var SEQ=[
    ['dot','gap','dash','gap','dot','word'],    // R
    ['dot','gap','dash','word'],                // A
    ['dash','gap','dot','gap','dash','gap','dash','word'], // Y
    ['dash','gap','dot','word'],               // N
    ['dot','word'],                            // E
    ['dash','word'],                           // T
];
var ticker=document.getElementById('morseTicker');
if(ticker){
    var html='';
    for(var r=0;r<7;r++){
        SEQ.forEach(function(letter){
            letter.forEach(function(sym){
                if(sym==='dot') html+='<div class="md"></div>';
                else if(sym==='dash') html+='<div class="ms"></div>';
                else if(sym==='gap')  html+='<div class="mg"></div>';
                else if(sym==='word') html+='<div class="mw"></div>';
            });
        });
        html+='<div style="width:40px;flex-shrink:0;"></div>';
    }
    ticker.innerHTML=html+html;
}

})();
</script>
</body>
</html>