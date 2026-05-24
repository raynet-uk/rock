@extends('layouts.app')
@section('title','Net Control — ' . $net['callsign'])
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
:root{--navy:#003366;--red:#C8102E;--border:#dde2e8;--muted:#6b7f96;}
*{box-sizing:border-box;}

/* ── Mobile (default) ── */
.nc-wrap{max-width:900px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.nc-header{background:linear-gradient(135deg,#001a33 0%,#003366 50%,#001a33 100%);color:#fff;border-radius:16px;margin-bottom:1.5rem;}
.status-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:999px;font-size:.78rem;font-weight:800;}
.pill-pre{background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3);}
.pill-live{background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3);}
.pill-post{background:rgba(148,163,184,.15);color:#94a3b8;border:1px solid rgba(148,163,184,.3);}
.nc-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:1.25rem;}
.nc-card-title{font-size:.95rem;font-weight:800;color:var(--navy);margin-bottom:1rem;}
.slot-row{display:flex;align-items:center;gap:1rem;padding:.75rem 0;border-bottom:1px solid #f1f5f9;}
.slot-row:last-child{border-bottom:none;}
.slot-cs{font-family:monospace;font-weight:900;color:var(--navy);font-size:1rem;min-width:80px;}
.slot-time{font-family:monospace;font-size:.85rem;color:var(--muted);}
.slot-you{font-size:.7rem;font-weight:800;padding:.15rem .45rem;border-radius:999px;background:#f0f4ff;color:#4338ca;}
.countdown-box{text-align:center;padding:1.25rem;background:linear-gradient(135deg,#003366,#004080);border-radius:12px;color:#fff;margin-bottom:1.25rem;}
.countdown-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.55);}
.countdown-time{font-size:2.5rem;font-weight:900;font-family:monospace;letter-spacing:.05em;margin:.25rem 0;}
.countdown-sub{font-size:.78rem;color:rgba(255,255,255,.55);}
.input{width:100%;border:1px solid var(--border);border-radius:8px;padding:.55rem .75rem;font-size:.9rem;outline:none;transition:border .2s;}
.input:focus{border-color:var(--navy);}
.btn-primary{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.6rem 1.25rem;font-size:.88rem;font-weight:700;cursor:pointer;}
.btn-primary:disabled{opacity:.5;cursor:default;}
.label{font-size:.75rem;font-weight:700;color:var(--muted);margin-bottom:.3rem;display:block;}
.script-box{background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:1.25rem;font-size:.85rem;line-height:1.7;color:#334155;}
.script-box strong{color:var(--navy);}
.script-box .cs-inline{font-family:monospace;font-weight:900;color:var(--red);font-size:.95rem;}
.handover-arrow{display:flex;align-items:center;gap:.75rem;padding:.75rem;background:#f8fafc;border-radius:10px;border:1px solid var(--border);margin-bottom:.75rem;}
.log-row{display:grid;grid-template-columns:2rem 3.5rem 5.5rem 1fr 4rem 4rem 2rem;gap:.4rem;align-items:center;padding:.5rem .75rem;border-bottom:1px solid #f1f5f9;}
.log-row.hdr{background:#f8fafc;font-size:.62rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(1.4);}}

/* ── Desktop (≥1024px) ── */
@media (min-width:1024px) {
  body{background:#f0f4f8;}
  .nc-wrap{max-width:1400px;padding:1.5rem 2rem 3rem;}

  /* Full-width header */
  .nc-header{border-radius:16px;padding:1.75rem 2rem;margin-bottom:1.75rem;}
  .nc-header .cs{font-size:2.5rem;}

  /* Two-column grid */
  .nc-desktop-grid{display:grid;grid-template-columns:340px 1fr;gap:1.5rem;align-items:start;}

  /* Left column: countdown + handover + script */
  .nc-left{display:flex;flex-direction:column;gap:0;}

  /* Right column: log form + live log */
  .nc-right{display:flex;flex-direction:column;gap:0;}

  /* Countdown bigger on desktop */
  .countdown-box{padding:2rem;margin-bottom:0;border-radius:12px 12px 0 0;}
  .countdown-time{font-size:3.5rem;}
  .countdown-label{font-size:.75rem;}

  /* Handover joins countdown */
  .nc-handover-card{border-radius:0 0 12px 12px;margin-top:0;border-top:none;}

  /* Script card */
  .nc-script-card{margin-top:1.5rem;}

  /* Right column sticky log */
  .nc-log-card{flex:1;}

  /* Wider log table on desktop */
  .log-row{grid-template-columns:2rem 3.5rem 7rem 1fr 5rem 5rem 2rem;}
  .log-row.hdr{font-size:.65rem;}

  /* Sticky countdown banner */
  .nc-sticky-banner{display:flex;}
  .nc-wrap{padding-top:.5rem;}
}

/* Hidden on mobile */
.nc-sticky-banner{display:none;position:sticky;top:0;z-index:100;
  background:linear-gradient(135deg,#001a33,#003366);
  color:#fff;padding:.6rem 2rem;
  align-items:center;justify-content:space-between;gap:1rem;
  border-bottom:1px solid rgba(255,255,255,.08);
  box-shadow:0 2px 12px rgba(0,0,0,.3);}
</style>

<div class="nc-wrap">

  {{-- Header --}}
  <div class="nc-header" style="position:relative;overflow:hidden;padding:0;">
    {{-- Animated background canvas --}}
    <canvas id="ncHeaderCanvas" style="position:absolute;inset:0;width:100%;height:100%;opacity:.18;pointer-events:none;"></canvas>

    <div style="position:relative;z-index:1;padding:1.5rem;">

      {{-- Top row: group name + status pill --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div style="display:flex;align-items:center;gap:.6rem;">
          <div style="width:8px;height:8px;border-radius:50%;background:#C8102E;animation:pulse 1.5s infinite;flex-shrink:0;"></div>
          <span style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.5);">{{ $groupName }}</span>
        </div>
        <div id="statusPill" class="status-pill pill-pre">⏳ Pre-slot</div>
      </div>

      {{-- Main callsign display --}}
      <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
          <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.35);margin-bottom:.2rem;">Net Control Portal</div>
          <div style="font-family:monospace;font-size:3rem;font-weight:900;letter-spacing:.06em;line-height:1;color:#fff;text-shadow:0 0 30px rgba(200,16,46,.4);">{{ $net['callsign'] ?: 'NET' }}</div>
          <div style="display:flex;align-items:center;gap:.75rem;margin-top:.5rem;flex-wrap:wrap;">
            @if($net['frequency'])
            <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.7);background:rgba(255,255,255,.08);padding:.2rem .6rem;border-radius:999px;border:1px solid rgba(255,255,255,.12);">
              📡 {{ $net['frequency'] }} MHz
            </span>
            @endif
            @if($net['name'])
            <span style="font-size:.78rem;color:rgba(255,255,255,.45);font-weight:600;">{{ $net['name'] }}</span>
            @endif
          </div>
        </div>

        {{-- Controller info box --}}
        <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:.85rem 1.1rem;text-align:center;backdrop-filter:blur(8px);min-width:140px;">
          <div style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35);margin-bottom:.35rem;">Your Callsign</div>
          <div style="font-family:monospace;font-size:1.5rem;font-weight:900;color:#fff;letter-spacing:.05em;">{{ $user->callsign }}</div>
          <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-top:.2rem;">{{ $user->name }}</div>
        </div>
      </div>

      {{-- Slot info strip --}}
      <div style="display:flex;align-items:center;gap:.5rem;margin-top:1.1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.08);flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.06);border-radius:8px;padding:.3rem .75rem;border:1px solid rgba(255,255,255,.1);">
          <span style="font-size:.65rem;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Your slot</span>
          <span style="font-family:monospace;font-weight:900;color:#fff;font-size:.88rem;">{{ $slot['from'] }} – {{ $slot['to'] }}</span>
        </div>
        @if($prevSlot)
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:rgba(255,255,255,.4);">
          <span>← Takeover from</span>
          <span style="font-family:monospace;font-weight:800;color:rgba(255,255,255,.65);">{{ $prevSlot['callsign'] }}</span>
        </div>
        @endif
        @if($nextSlot)
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:rgba(255,255,255,.4);">
          <span>Handover to</span>
          <span style="font-family:monospace;font-weight:800;color:rgba(255,255,255,.65);">{{ $nextSlot['callsign'] }}</span>
          <span>→</span>
        </div>
        @endif
      </div>
    </div>
  </div>

  <script>
  // Animated sine wave header background
  (function(){
    var c = document.getElementById('ncHeaderCanvas');
    if (!c) return;
    var ctx = c.getContext('2d');
    var t = 0;
    function resize() { c.width = c.offsetWidth; c.height = c.offsetHeight; }
    resize();
    window.addEventListener('resize', resize);
    function draw() {
      ctx.clearRect(0,0,c.width,c.height);
      var lines = 4;
      for (var l=0; l<lines; l++) {
        ctx.beginPath();
        ctx.strokeStyle = l===0 ? '#C8102E' : 'rgba(255,255,255,0.4)';
        ctx.lineWidth   = l===0 ? 1.5 : .8;
        var amp   = 8 + l*6;
        var freq  = .012 + l*.004;
        var speed = .03 + l*.01;
        var yBase = c.height * (0.3 + l*0.15);
        ctx.moveTo(0, yBase);
        for (var x=0; x<c.width; x++) {
          var y = yBase + Math.sin(x*freq + t*(speed*(l%2===0?1:-1))) * amp
                       + Math.sin(x*.02 + t*.02) * (amp*.4);
          ctx.lineTo(x, y);
        }
        ctx.stroke();
      }
      t += .5;
      requestAnimationFrame(draw);
    }
    draw();
  })();
  </script>

  {{-- Sticky countdown banner (desktop only) --}}
  <div class="nc-sticky-banner">
    <div style="display:flex;align-items:center;gap:1.5rem;">
      <div id="statusPillSticky" class="status-pill pill-pre" style="font-size:.72rem;">⏳ Pre-slot</div>
      <div>
        <div style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);" id="stickyLabel">Time until slot starts</div>
        <div style="font-family:monospace;font-weight:900;font-size:1.2rem;letter-spacing:.06em;" id="stickyTime">--:--:--</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:1.5rem;">
      <div style="text-align:center;">
        <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35);">Your slot</div>
        <div style="font-family:monospace;font-weight:800;font-size:.85rem;">{{ $slot['from'] }} – {{ $slot['to'] }}</div>
      </div>
      @if($prevSlot)
      <div style="font-size:.75rem;color:rgba(255,255,255,.45);">← <span style="font-family:monospace;font-weight:800;color:rgba(255,255,255,.7);">{{ $prevSlot['callsign'] }}</span></div>
      @endif
      <div style="font-family:monospace;font-size:1.1rem;font-weight:900;background:rgba(255,255,255,.1);padding:.25rem .85rem;border-radius:8px;letter-spacing:.05em;">{{ $net['callsign'] }}</div>
      @if($nextSlot)
      <div style="font-size:.75rem;color:rgba(255,255,255,.45);"><span style="font-family:monospace;font-weight:800;color:rgba(255,255,255,.7);">{{ $nextSlot['callsign'] }}</span> →</div>
      @endif
      <div id="stickyLogBtn" style="display:none;">
        <span style="font-size:.72rem;font-weight:800;color:#22c55e;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);padding:.25rem .65rem;border-radius:999px;">🔴 ON AIR</span>
      </div>
    </div>
  </div>

  {{-- Desktop two-column grid --}}
  <div class="nc-desktop-grid">
  <div class="nc-left">

  {{-- Countdown --}}
  <div class="countdown-box" id="countdownBox">
    <div class="countdown-label" id="countdownLabel">Time until your slot starts</div>
    <div class="countdown-time" id="countdownTime">--:--:--</div>
    <div class="countdown-sub" id="countdownSub">Your slot: {{ $slot['from'] }} – {{ $slot['to'] }}</div>
  </div>

  {{-- Handover info --}}
  <div class="nc-card nc-handover-card">
    <div class="nc-card-title">🔄 Handover</div>
    <div class="handover-arrow">
      <div style="flex:1;text-align:center;">
        @if($prevSlot)
          <div style="font-size:.7rem;color:var(--muted);font-weight:700;">BEFORE YOU</div>
          <div class="slot-cs">{{ $prevSlot['callsign'] }}</div>
          <div class="slot-time">{{ $prevSlot['from'] }} – {{ $prevSlot['to'] }}</div>
        @else
          <div style="font-size:.82rem;color:var(--muted);">Net opens</div>
        @endif
      </div>
      <div style="font-size:1.5rem;">→</div>
      <div style="flex:1;text-align:center;background:#f0f4ff;border-radius:8px;padding:.5rem;">
        <div style="font-size:.7rem;color:#4338ca;font-weight:800;">YOUR SLOT</div>
        <div class="slot-cs" style="color:#003366;">{{ $user->callsign }}</div>
        <div class="slot-time">{{ $slot['from'] }} – {{ $slot['to'] }}</div>
      </div>
      <div style="font-size:1.5rem;">→</div>
      <div style="flex:1;text-align:center;">
        @if($nextSlot)
          <div style="font-size:.7rem;color:var(--muted);font-weight:700;">AFTER YOU</div>
          <div class="slot-cs">{{ $nextSlot['callsign'] }}</div>
          <div class="slot-time">{{ $nextSlot['from'] }} – {{ $nextSlot['to'] }}</div>
        @else
          <div style="font-size:.82rem;color:var(--muted);">Net closes</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Net Controller Script --}}
  <div class="nc-card nc-script-card">
    <div class="nc-card-title">📋 Net Controller Script</div>
    <div class="script-box">
      @if($prevSlot)
      <p><strong>Takeover from {{ $prevSlot['callsign'] }}:</strong><br>
      "This is <span class="cs-inline">{{ $user->callsign }}</span> taking over net control from <span class="cs-inline">{{ $prevSlot['callsign'] }}</span>. The time is <span id="scriptTime">--:--</span>.
      This is the <strong>{{ $net['callsign'] }}</strong> net on <strong>{{ $net['frequency'] }}</strong>. All stations please pass your callsign and signal report."</p>
      @else
      <p><strong>Opening the net:</strong><br>
      "Good [morning/afternoon/evening], this is <span class="cs-inline">{{ $user->callsign }}</span>, net control for the <strong>{{ $net['callsign'] }}</strong> net on <strong>{{ $net['frequency'] }}</strong>.
      The time is <span id="scriptTime">--:--</span>. All stations wishing to check in please pass your callsign now."</p>
      @endif

      <p style="margin-top:1rem;"><strong>Logging a station:</strong><br>
      "Received [callsign], signal report [59], you are logged. Next station please."</p>

      @if($nextSlot)
      <p style="margin-top:1rem;"><strong>Handover to {{ $nextSlot['callsign'] }}:</strong><br>
      "The time is now <span id="scriptTime2">--:--</span>. I am handing over net control to <span class="cs-inline">{{ $nextSlot['callsign'] }}</span>.
      This has been <span class="cs-inline">{{ $user->callsign }}</span> for net control. Over to <span class="cs-inline">{{ $nextSlot['callsign'] }}</span>."</p>
      @else
      <p style="margin-top:1rem;"><strong>Closing the net:</strong><br>
      "The time is now <span id="scriptTime2">--:--</span>. That concludes the <strong>{{ $net['callsign'] }}</strong> net. Thank you all for checking in. This is <span class="cs-inline">{{ $user->callsign }}</span> closing the net. 73."</p>
      @endif
    </div>
  </div>

  </div>{{-- /nc-left --}}
  <div class="nc-right">

  {{-- Log a station --}}
  <div class="nc-card" id="logCard">
    <div class="nc-card-title">📻 Log a Station</div>
    <div id="logBanner" style="border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;"></div>

    {{-- QRZ preview card --}}
    <div id="ncQrzCard" style="display:none;margin-bottom:.85rem;padding:.75rem 1rem;background:linear-gradient(135deg,#f0f4ff,#f8fafc);border:1px solid #c7d7ff;border-radius:10px;">
      <div style="display:flex;align-items:center;gap:.85rem;">
        <img id="ncQrzPhoto" src="" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #c7d7ff;display:none;flex-shrink:0;">
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
            <span id="ncQrzCallsign" style="font-family:monospace;font-weight:900;font-size:1rem;color:#003366;"></span>
            <span id="ncQrzLicence" style="display:none;font-size:.68rem;font-weight:800;padding:.12rem .4rem;border-radius:999px;background:#dcfce7;color:#15803d;"></span>
            <span id="ncQrzRegistered" style="display:none;font-size:.68rem;font-weight:800;padding:.12rem .4rem;border-radius:999px;background:#fef9c3;color:#a16207;">✓ On RAYNET</span>
            <a id="ncQrzLink" href="#" target="_blank" style="font-size:.68rem;color:#6366f1;font-weight:700;text-decoration:none;">QRZ ↗</a>
          </div>
          <div id="ncQrzName" style="font-weight:700;color:#334155;font-size:.88rem;margin-top:.12rem;"></div>
          <div style="display:flex;gap:.75rem;margin-top:.15rem;flex-wrap:wrap;">
            <span id="ncQrzLocation" style="font-size:.72rem;color:#64748b;display:none;">📍 <span></span></span>
            <span id="ncQrzGrid" style="font-size:.72rem;color:#64748b;font-family:monospace;display:none;">Grid: <span></span></span>
          </div>
        </div>
        <div id="ncInviteBtn" style="display:none;">
          <button onclick="ncOpenInvite()" style="font-size:.72rem;font-weight:700;background:linear-gradient(135deg,#003366,#001a33);color:#fff;border:none;border-radius:7px;padding:.35rem .75rem;cursor:pointer;white-space:nowrap;">✉ Invite</button>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 2fr auto;gap:.65rem;align-items:start;">
      <div>
        <label class="label">Callsign *</label>
        <input type="text" id="ncCallsign" class="input" placeholder="G4BDS"
               style="text-transform:uppercase;font-family:monospace;font-weight:800;font-size:.95rem;"
               maxlength="20" autocomplete="off">
      </div>
      <div>
        <label class="label">Signal Report</label>
        <input type="text" id="ncReport" class="input" placeholder="59" maxlength="10" style="font-family:monospace;font-weight:700;">
      </div>
      <div>
        <label class="label">Notes</label>
        <input type="text" id="ncNotes" class="input" placeholder="Optional">
      </div>
      <div style="padding-top:1.6rem;">
        <button onclick="ncLog()" id="ncSubmitBtn" class="btn-primary" style="width:100%;white-space:nowrap;" disabled>+ Log</button>
      </div>
    </div>
    <div id="ncError" style="color:#C8102E;font-size:.78rem;margin-top:.5rem;display:none;padding:.35rem .6rem;background:#fff1f2;border-radius:6px;border:1px solid #fecdd3;"></div>
  </div>


  {{-- Live station log --}}
  <div class="nc-card" style="padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);">
      <div style="font-weight:800;color:var(--navy);">Live Station Log</div>
      <div id="ncLogCount" style="font-size:.8rem;color:var(--muted);font-weight:700;">0 stations</div>
    </div>
    <div class="log-row hdr">
      <div>#</div><div>Time</div><div>Callsign</div><div>Name</div><div>Licence</div><div>Signal</div><div></div>
    </div>
    <div id="ncLog" style="min-height:60px;"></div>
    <div id="ncLogEmpty" style="text-align:center;padding:2rem;color:var(--muted);font-size:.85rem;">No stations logged yet</div>
  </div>

</div>

  </div>{{-- /nc-right --}}
  </div>{{-- /nc-desktop-grid --}}

  {{-- Offline sync popup --}}
  <div id="ncOfflineModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1002;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
        <div style="font-size:1.5rem;">📶</div>
        <div>
          <div style="font-size:1rem;font-weight:900;color:#003366;">Connection Restored</div>
          <div style="font-size:.78rem;color:#6b7f96;margin-top:.1rem;">Offline-logged stations ready to sync</div>
        </div>
      </div>
      <div id="ncOfflineList" style="background:#f8fafc;border-radius:8px;border:1px solid #dde2e8;margin-bottom:1rem;overflow:hidden;max-height:250px;overflow-y:auto;"></div>
      <div id="ncOfflineErr" style="display:none;color:#C8102E;font-size:.78rem;margin-bottom:.75rem;"></div>
      <div style="display:flex;gap:.75rem;">
        <button id="ncOfflineImportBtn" onclick="ncImportQueue()" class="btn-primary" style="flex:1;">📥 Import All</button>
        <button onclick="ncDiscardQueue()" style="font-size:.82rem;font-weight:700;color:#C8102E;background:none;border:1px solid #fecdd3;border-radius:8px;padding:.5rem .85rem;cursor:pointer;">Discard</button>
        <button onclick="document.getElementById('ncOfflineModal').style.display='none'" style="font-size:.82rem;font-weight:700;color:#6b7f96;background:none;border:1px solid #dde2e8;border-radius:8px;padding:.5rem .85rem;cursor:pointer;">Later</button>
      </div>
    </div>
  </div>

  {{-- Invite modal --}}
  <div id="ncInviteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1001;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <div style="font-size:1rem;font-weight:900;color:#003366;">✉ Invite to RAYNET</div>
        <button onclick="document.getElementById('ncInviteModal').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#6b7f96;">✕</button>
      </div>
      <div id="ncInviteCallsign" style="font-family:monospace;font-size:1.2rem;font-weight:900;color:#003366;background:#f0f4ff;padding:.4rem .85rem;border-radius:8px;display:inline-block;margin-bottom:1rem;"></div>
      <div id="ncInviteQrzRow" style="display:none;margin-bottom:.75rem;padding:.55rem .85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:.82rem;">
        <div style="font-weight:700;color:#15803d;margin-bottom:.25rem;">📧 Email found on QRZ:</div>
        <div style="display:flex;align-items:center;gap:.5rem;">
          <span id="ncInviteQrzEmail" style="font-family:monospace;font-weight:700;color:#334155;"></span>
          <button onclick="document.getElementById('ncInviteEmail').value=document.getElementById('ncInviteQrzEmail').textContent" style="font-size:.7rem;font-weight:700;background:#15803d;color:#fff;border:none;border-radius:5px;padding:.18rem .5rem;cursor:pointer;">Use</button>
        </div>
      </div>
      <label class="label">Email Address *</label>
      <input type="email" id="ncInviteEmail" class="input" placeholder="operator@example.com" style="margin-bottom:.5rem;">
      <div id="ncInviteErr" style="color:#C8102E;font-size:.78rem;margin-bottom:.5rem;display:none;"></div>
      <div style="display:flex;gap:.75rem;margin-top:.75rem;">
        <button onclick="ncSendInvite()" id="ncInviteSendBtn" class="btn-primary" style="flex:1;">Send Invitation</button>
        <button onclick="document.getElementById('ncInviteModal').style.display='none'" style="font-size:.82rem;font-weight:700;color:#6b7f96;background:none;border:1px solid #dde2e8;border-radius:8px;padding:.5rem .85rem;cursor:pointer;">Cancel</button>
      </div>
      <div id="ncInviteOk" style="display:none;text-align:center;padding:.65rem;background:#f0fdf4;border-radius:8px;color:#15803d;font-weight:700;margin-top:.75rem;">✓ Invitation sent!</div>
    </div>
  </div>


<script>
var SLOT_FROM      = '{{ $slot['from'] }}';
var SLOT_TO        = '{{ $slot['to'] }}';
var WINDOW_MINS    = {{ $windowMins }};
var LOG_HASH       = '';
var CAN_LOG        = false;
var NC_QRZ         = {};
var NC_INVITE_CS   = '';
var NC_OFFLINE_KEY = 'raynet_nc_offline_log';
var ncQrzTimer;

// ── Helpers ───────────────────────────────────────────────────────────────
function parseTime(t) {
    var d = new Date(); var p = t.split(':');
    d.setHours(parseInt(p[0]), parseInt(p[1]), 0, 0); return d;
}
function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function isOffline() { return !navigator.onLine; }
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

// ── Slot times ────────────────────────────────────────────────────────────
var slotFrom = parseTime(SLOT_FROM);
var slotTo   = parseTime(SLOT_TO);
if (slotTo <= slotFrom) { slotTo = new Date(slotTo.getTime() + 86400000); }
var windowStart = new Date(slotFrom.getTime() - WINDOW_MINS*60000);
var windowEnd   = new Date(slotTo.getTime()   + WINDOW_MINS*60000);

// ── Offline queue ─────────────────────────────────────────────────────────
function getQueue() {
    try {
        var q = JSON.parse(localStorage.getItem(NC_OFFLINE_KEY)||'[]');
        return q.filter(function(e){ return e&&e.callsign&&e.logged_at&&!e.id; });
    } catch(e){ return []; }
}
function saveQueue(q) { localStorage.setItem(NC_OFFLINE_KEY, JSON.stringify(q)); }
function addToQueue(cs, rep, notes) {
    var q = getQueue();
    q.push({callsign:cs, signal_report:rep, notes:notes, logged_at:new Date().toISOString()});
    saveQueue(q);
}
function clearQueue() { localStorage.removeItem(NC_OFFLINE_KEY); }

// ── Countdown tick ────────────────────────────────────────────────────────
function tick() {
    var now  = new Date();
    var pill = document.getElementById('statusPill');
    var box  = document.getElementById('countdownBox');
    var ctL  = document.getElementById('countdownLabel');
    var ctT  = document.getElementById('countdownTime');
    var ctS  = document.getElementById('countdownSub');
    var btn  = document.getElementById('ncSubmitBtn');
    var bnr  = document.getElementById('logBanner');
    var hhmm = now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
    ['scriptTime','scriptTime2'].forEach(function(id){
        var el=document.getElementById(id); if(el) el.textContent=hhmm;
    });

    function fmt(sec) {
        var h=Math.floor(sec/3600),m=Math.floor((sec%3600)/60),s=sec%60;
        return (h?String(h).padStart(2,'0')+':':'')+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    }

    if (now < windowStart) {
        location.reload(); return;
    } else if (now >= windowStart && now < slotFrom) {
        var diff=Math.floor((slotFrom-now)/1000);
        ctL.textContent='Time until your slot starts'; ctT.textContent=fmt(diff);
        if(pill){pill.className='status-pill pill-pre';pill.textContent='⏳ Slot starts '+SLOT_FROM;}
        box.style.background='linear-gradient(135deg,#1e293b,#334155)';
        CAN_LOG=false;
        bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#fff7ed;color:#c2410c;';
        bnr.innerHTML='⏳ Logging opens at <strong>'+SLOT_FROM+'</strong>';
    } else if (now>=slotFrom && now<=slotTo) {
        var diff=Math.floor((slotTo-now)/1000);
        ctL.textContent='Time remaining in your slot'; ctT.textContent=fmt(diff);
        if(pill){pill.className='status-pill pill-live';pill.textContent='🔴 ON AIR';}
        box.style.background='linear-gradient(135deg,#C8102E,#8b0000)';
        if(isOffline()){
            CAN_LOG=true;
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#1e293b;color:#fbbf24;';
            bnr.innerHTML='📴 <strong>Offline mode</strong> — entries will sync when reconnected';
        } else if (window._ncLoggingEnabled === false) {
            CAN_LOG=false;
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#fff7ed;color:#c2410c;';
            bnr.innerHTML='⚠️ Station logging is <strong>disabled</strong> by the net controller — contact them to enable it';
        } else {
            CAN_LOG=true;
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#f0fdf4;color:#15803d;';
            bnr.innerHTML='🟢 You are <strong>ON AIR</strong> — log stations below';
        }
    } else if (now>slotTo && now<=windowEnd) {
        ctL.textContent='Your slot has ended'; ctT.textContent='ENDED';
        ctS.textContent='Access expires in '+Math.ceil((windowEnd-now)/60000)+' min(s)';
        if(pill){pill.className='status-pill pill-post';pill.textContent='✓ Slot ended';}
        box.style.background='linear-gradient(135deg,#334155,#1e293b)';
        CAN_LOG=false;
        bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#f1f5f9;color:#64748b;';
        bnr.innerHTML='✓ Slot ended — logging disabled';
    } else {
        location.reload(); return;
    }

    if(btn) btn.disabled=!CAN_LOG;

    // Sync sticky banner
    var stickyTime  = document.getElementById('stickyTime');
    var stickyLabel = document.getElementById('stickyLabel');
    var stickyPill  = document.getElementById('statusPillSticky');
    var stickyBtn   = document.getElementById('stickyLogBtn');
    if (stickyTime)  stickyTime.textContent  = ctT.textContent;
    if (stickyLabel) stickyLabel.textContent = ctL.textContent;
    if (stickyPill && pill) { stickyPill.className=pill.className; stickyPill.textContent=pill.textContent; }
    if (stickyBtn)   stickyBtn.style.display = CAN_LOG ? '' : 'none';

    setTimeout(tick,1000);
}

// ── QRZ lookup ────────────────────────────────────────────────────────────
function ncQrzLookup(cs) {
    if (!cs || cs.length < 3) { ncHideQrz(); return; }
    if (isOffline()) {
        ncHideQrz();
        var nm = document.getElementById('ncQrzName');
        if (nm) { nm.textContent='📴 Offline — QRZ unavailable'; nm.style.color='#fbbf24'; }
        return;
    }
    fetch('/admin/events/station-log/qrz?callsign='+encodeURIComponent(cs), {
        headers: {'X-CSRF-TOKEN': csrf(), 'Accept':'application/json'}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        NC_QRZ[cs] = d;
        if (!d.found) { ncHideQrz(); return; }
        ncShowQrz(cs, d);
    })
    .catch(function(){ ncHideQrz(); });
}

function ncShowQrz(cs, d) {
    var card = document.getElementById('ncQrzCard');
    if (!card) return;
    document.getElementById('ncQrzCallsign').textContent = cs;
    document.getElementById('ncQrzName').textContent     = d.name || '';
    document.getElementById('ncQrzName').style.color     = '';
    var ph = document.getElementById('ncQrzPhoto');
    if (d.photo) { ph.src='/admin/events/station-log/qrz-photo?callsign='+encodeURIComponent(cs); ph.style.display=''; }
    else { ph.style.display='none'; }
    var lic = document.getElementById('ncQrzLicence');
    if (d.licence_class) { lic.textContent=d.licence_class; lic.style.display=''; }
    else { lic.style.display='none'; }
    document.getElementById('ncQrzRegistered').style.display = d.is_registered ? '' : 'none';
    document.getElementById('ncQrzLink').href = d.qrz_url || ('https://www.qrz.com/db/'+cs);
    var loc = document.getElementById('ncQrzLocation');
    if (d.location) { loc.querySelector('span').textContent=d.location; loc.style.display=''; }
    else { loc.style.display='none'; }
    var grid = document.getElementById('ncQrzGrid');
    if (d.grid) { grid.querySelector('span').textContent=d.grid; grid.style.display=''; }
    else { grid.style.display='none'; }
    document.getElementById('ncInviteBtn').style.display = d.is_registered ? 'none' : '';
    NC_INVITE_CS = cs;
    card.style.display = '';
}

function ncHideQrz() {
    var c = document.getElementById('ncQrzCard');
    if (c) c.style.display = 'none';
}

// ── Invite ────────────────────────────────────────────────────────────────
function ncOpenInvite() {
    var cs = NC_INVITE_CS; var d = NC_QRZ[cs] || {};
    document.getElementById('ncInviteCallsign').textContent = cs;
    document.getElementById('ncInviteEmail').value = '';
    document.getElementById('ncInviteErr').style.display = 'none';
    document.getElementById('ncInviteOk').style.display  = 'none';
    var btn = document.getElementById('ncInviteSendBtn');
    btn.disabled = false; btn.textContent = 'Send Invitation';
    var row = document.getElementById('ncInviteQrzRow');
    if (d.email) { document.getElementById('ncInviteQrzEmail').textContent=d.email; row.style.display=''; }
    else { row.style.display='none'; }
    document.getElementById('ncInviteModal').style.display = 'flex';
}

function ncSendInvite() {
    var cs  = NC_INVITE_CS; var d = NC_QRZ[cs] || {};
    var email = document.getElementById('ncInviteEmail').value.trim();
    var err   = document.getElementById('ncInviteErr');
    var btn   = document.getElementById('ncInviteSendBtn');
    if (!email) { err.textContent='Email required'; err.style.display=''; return; }
    err.style.display='none'; btn.disabled=true; btn.textContent='Sending...';
    fetch('/admin/events/station-log/invite', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},
        body: JSON.stringify({callsign:cs, email:email, name:d.name||cs})
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            document.getElementById('ncInviteOk').style.display = '';
            btn.textContent = '✓ Sent';
            setTimeout(function(){ document.getElementById('ncInviteModal').style.display='none'; }, 2000);
        } else {
            err.textContent = res.error||'Failed'; err.style.display='';
            btn.disabled=false; btn.textContent='Send Invitation';
        }
    });
}

// ── Log a station ─────────────────────────────────────────────────────────
function ncLog() {
    if (!CAN_LOG) return;
    var cs    = document.getElementById('ncCallsign').value.trim().toUpperCase();
    var rep   = document.getElementById('ncReport').value.trim();
    var notes = document.getElementById('ncNotes').value.trim();
    var err   = document.getElementById('ncError');
    if (!cs) { err.textContent='Callsign required'; err.style.display=''; return; }
    err.style.display = 'none';

    if (isOffline()) {
        addToQueue(cs, rep, notes);
        document.getElementById('ncCallsign').value = '';
        document.getElementById('ncReport').value   = '';
        document.getElementById('ncNotes').value    = '';
        ncHideQrz();
        loadLog();
        return;
    }

    fetch('{{ route('net-control.log') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},
        body: JSON.stringify({callsign:cs, signal_report:rep, notes:notes})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            document.getElementById('ncCallsign').value = '';
            document.getElementById('ncReport').value   = '';
            document.getElementById('ncNotes').value    = '';
            ncHideQrz();
            loadLog();
        } else {
            err.textContent = d.error||'Failed to log';
            err.style.display = '';
        }
    });
}

// ── Live log ──────────────────────────────────────────────────────────────
function loadLog() {
    if (isOffline()) { renderOfflineLog(); return; }
    fetch('{{ route('net-control.stations') }}', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(data){
        var hash = data.map(function(e){ return e.id+':'+e.callsign; }).join(',');
        if (hash === LOG_HASH) return;
        LOG_HASH = hash;
        var log   = document.getElementById('ncLog');
        var empty = document.getElementById('ncLogEmpty');
        var cnt   = document.getElementById('ncLogCount');
        if (cnt) cnt.textContent = data.length+' station'+(data.length!==1?'s':'');
        if (!data.length) { log.innerHTML=''; empty.style.display=''; return; }
        empty.style.display = 'none';
        log.innerHTML = data.map(function(e,i){
            var qrz = e.qrz_data || {};
            if (typeof qrz==='string') { try{ qrz=JSON.parse(qrz); }catch(x){ qrz={}; } }
            var time = new Date(e.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
            var bg   = i%2===0 ? '#fff' : '#f9fafb';
            return '<div class="log-row" style="background:'+bg+'">'
                +'<div style="font-size:.65rem;color:#cbd5e1;text-align:center;">'+(i+1)+'</div>'
                +'<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">'+time+'</div>'
                +'<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">'+escHtml(e.callsign)+'</div>'
                +'<div style="font-weight:600;color:#334155;font-size:.8rem;">'+escHtml(e.name||'—')+'</div>'
                +'<div>'+(qrz.licence_class?'<span style="padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#15803d;font-weight:800;font-size:.65rem;">'+escHtml(qrz.licence_class)+'</span>':'')+'</div>'
                +'<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">'+escHtml(e.signal_report||'—')+'</div>'
                +'<div></div>'
                +'</div>';
        }).join('');
    }).catch(function(){});
}

function renderOfflineLog() {
    var q = getQueue();
    var log=document.getElementById('ncLog'), empty=document.getElementById('ncLogEmpty'), cnt=document.getElementById('ncLogCount');
    if (cnt) cnt.textContent = q.length+' station'+(q.length!==1?'s':'')+' (offline)';
    if (!q.length) { log.innerHTML=''; empty.style.display=''; return; }
    empty.style.display = 'none';
    log.innerHTML = q.map(function(e,i){
        var time = new Date(e.logged_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
        return '<div class="log-row" style="background:'+(i%2===0?'#fffbeb':'#fef9c3')+'">'
            +'<div style="font-size:.65rem;color:#92400e;text-align:center;">'+(i+1)+'</div>'
            +'<div style="font-size:.72rem;color:#92400e;font-family:monospace;">'+time+'</div>'
            +'<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">'+escHtml(e.callsign)+'</div>'
            +'<div style="font-size:.75rem;color:#64748b;">'+escHtml(e.notes||'')+'</div>'
            +'<div><span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fbbf24;color:#1e293b;">⏳ Queued</span></div>'
            +'<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">'+escHtml(e.signal_report||'—')+'</div>'
            +'<div></div>'
            +'</div>';
    }).join('');
}

// ── Offline sync popup ────────────────────────────────────────────────────
function ncShowSyncModal(q) {
    var list = document.getElementById('ncOfflineList');
    var btn  = document.getElementById('ncOfflineImportBtn');
    var err  = document.getElementById('ncOfflineErr');
    if (btn) { btn.disabled=false; btn.textContent='📥 Import '+q.length+' station'+(q.length!==1?'s':''); btn.style.background=''; }
    if (err) err.style.display = 'none';
    if (list) list.innerHTML = q.map(function(e,i){
        var time = new Date(e.logged_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
        return '<div style="display:flex;align-items:center;gap:.75rem;padding:.55rem .85rem;'+(i%2===0?'background:#fff':'background:#f9fafb')+';border-bottom:1px solid #f1f5f9;">'
            +'<span style="font-family:monospace;font-weight:900;color:#003366;min-width:75px;">'+escHtml(e.callsign)+'</span>'
            +'<span style="font-size:.75rem;color:#94a3b8;font-family:monospace;">'+time+'</span>'
            +(e.signal_report?'<span style="font-size:.75rem;font-weight:700;color:#059669;">'+escHtml(e.signal_report)+'</span>':'')
            +'</div>';
    }).join('');
    document.getElementById('ncOfflineModal').style.display = 'flex';
}

function ncDiscardQueue() {
    if (!confirm('Discard all offline entries?')) return;
    clearQueue();
    document.getElementById('ncOfflineModal').style.display = 'none';
    loadLog();
}

function ncImportQueue() {
    var q = getQueue();
    if (!q.length) { document.getElementById('ncOfflineModal').style.display='none'; return; }
    var btn = document.getElementById('ncOfflineImportBtn');
    var err = document.getElementById('ncOfflineErr');
    btn.disabled = true; err.style.display = 'none';
    var done=0, failed=0;
    function next(i) {
        if (i >= q.length) {
            loadLog();
            if (failed === 0) {
                btn.textContent='✓ All imported!'; btn.style.background='#15803d';
                clearQueue();
                setTimeout(function(){ document.getElementById('ncOfflineModal').style.display='none'; }, 1800);
            } else {
                saveQueue(q.slice(done));
                err.textContent=failed+' failed — tap Retry'; err.style.display='';
                btn.disabled=false; btn.textContent='Retry'; btn.style.background='';
            }
            return;
        }
        var e = q[i];
        btn.textContent = 'Importing '+(i+1)+' of '+q.length+'...';
        fetch('{{ route('net-control.log') }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'X-Offline-Replay':'1'},
            body: JSON.stringify({callsign:e.callsign, signal_report:e.signal_report, notes:e.notes})
        })
        .then(function(r){ return r.json(); })
        .then(function(d){ if(d.success) done++; else failed++; next(i+1); })
        .catch(function(){ failed++; next(i+1); });
    }
    next(0);
}

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function(){
    // Clean bad queue data
    try {
        var raw = localStorage.getItem(NC_OFFLINE_KEY);
        if (raw) { var q=JSON.parse(raw); var c=q.filter(function(e){return e&&e.callsign&&e.logged_at&&!e.id;}); if(c.length!==q.length) saveQueue(c); }
    } catch(e) { clearQueue(); }

    // Poll logging status from server every 5s
    window._ncLoggingEnabled = true;
    function pollLoggingStatus() {
        fetch('/net-status-json', {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            var prev = window._ncLoggingEnabled;
            window._ncLoggingEnabled = !!d.station_logging;
            // If status changed, update button immediately
            if (prev !== window._ncLoggingEnabled) {
                var btn = document.getElementById('ncSubmitBtn');
                if (btn) btn.disabled = !window._ncLoggingEnabled || !CAN_LOG;
            }
        }).catch(function(){});
    }
    pollLoggingStatus();
    setInterval(pollLoggingStatus, 5000);

    tick();
    loadLog();
    setInterval(loadLog, 3000);

    window.addEventListener('offline', function(){ tick(); loadLog(); });
    window.addEventListener('online',  function(){
        tick();
        setTimeout(function(){
            var q = getQueue();
            if (q.length > 0) ncShowSyncModal(q); else loadLog();
        }, 800);
    });

    // Show sync modal if queue pending on load
    (function(){
        var q = getQueue();
        if (q.length > 0 && navigator.onLine) setTimeout(function(){ ncShowSyncModal(q); }, 1500);
    })();

    // Callsign input
    var ci = document.getElementById('ncCallsign');
    if (ci) {
        ci.addEventListener('keydown', function(e){ if(e.key==='Enter') ncLog(); });
        ci.addEventListener('input', function(){
            ncHideQrz();
            clearTimeout(ncQrzTimer);
            var val = ci.value.trim().toUpperCase();
            if (val.length >= 3) ncQrzTimer = setTimeout(function(){ ncQrzLookup(val); }, 600);
        });
        ci.addEventListener('blur', function(){
            var val = ci.value.trim().toUpperCase();
            if (val.length >= 3) ncQrzLookup(val);
        });
    }

    // Modal backdrop closes
    document.getElementById('ncOfflineModal').addEventListener('click', function(e){ if(e.target===this) this.style.display='none'; });
    document.getElementById('ncInviteModal').addEventListener('click',  function(e){ if(e.target===this) this.style.display='none'; });
});
</script>
@endsection
