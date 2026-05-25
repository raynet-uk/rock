@extends('layouts.netcontrol')
@section('title','Net Control — ' . $net['callsign'])
@section('content')
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
.nc-toolbar{display:flex;gap:.6rem;flex-wrap:wrap;padding:.6rem 1rem;background:rgba(0,0,0,.18);border-bottom:1px solid rgba(255,255,255,.08);}
.nc-toolbar-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:999px;font-size:.78rem;font-weight:800;border:none;cursor:pointer;transition:opacity .2s;}
.nc-toolbar-btn.btn-danger{background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;}
.nc-toolbar-btn:hover{opacity:.85;}
.nc-dialog-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;}
.nc-dialog-overlay.active{display:flex;}
.nc-dialog{background:#fff;border-radius:16px;padding:2rem;max-width:420px;width:90%;text-align:center;box-shadow:0 8px 48px rgba(0,0,0,.25);}
.nc-dialog h2{margin:0 0 .5rem;font-size:1.15rem;color:#7f1d1d;}
.nc-dialog p{color:#475569;font-size:.88rem;margin:.5rem 0 1.25rem;}
.nc-dialog-btns{display:flex;gap:.75rem;justify-content:center;}
.nc-dialog-btns button{padding:.6rem 1.5rem;border-radius:999px;font-weight:800;font-size:.88rem;border:none;cursor:pointer;}
.nc-dialog-btns .btn-confirm{background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;}
.nc-dialog-btns .btn-cancel{background:#f1f5f9;color:#475569;}
@keyframes nc-handover-pulse{0%,100%{opacity:1;}50%{opacity:.5;}}

/* Handover chat widget */
@keyframes ncChatSlideUp{from{opacity:0;transform:translateY(32px) scale(.96);}to{opacity:1;transform:translateY(0) scale(1);}}
@keyframes ncChatMsgIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
@keyframes ncChatPulse{0%,100%{box-shadow:0 0 0 0 rgba(200,16,46,.4);}70%{box-shadow:0 0 0 8px rgba(200,16,46,0);}}
@keyframes ncChatTyping{0%,80%,100%{transform:scale(0);opacity:.4;}40%{transform:scale(1);opacity:1;}}
.nc-chat-widget{position:fixed;bottom:1.5rem;right:1.5rem;z-index:8888;display:none;flex-direction:column;width:340px;max-width:calc(100vw - 2rem);border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.3),0 0 0 1px rgba(255,255,255,.08);overflow:hidden;backdrop-filter:blur(2px);}
.nc-chat-widget.nc-chat-animate{animation:ncChatSlideUp .4s cubic-bezier(.22,.68,0,1.2) both;}
.nc-chat-header{background:linear-gradient(135deg,#001a33 0%,#003366 60%,#004d99 100%);color:#fff;padding:.8rem 1.1rem;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;position:relative;overflow:hidden;}
.nc-chat-header::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='20' cy='20' r='10'/%3E%3C/g%3E%3C/svg%3E");pointer-events:none;}
.nc-chat-header-left{display:flex;align-items:center;gap:.6rem;font-weight:900;font-size:.85rem;position:relative;}
.nc-chat-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#C8102E,#8b0000);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;border:2px solid rgba(255,255,255,.2);}
.nc-chat-header-info{display:flex;flex-direction:column;gap:.05rem;}
.nc-chat-header-title{font-size:.82rem;font-weight:900;line-height:1;}
.nc-chat-header-sub{font-size:.62rem;color:rgba(255,255,255,.5);font-weight:600;}
.nc-chat-badge{background:#C8102E;color:#fff;font-size:.6rem;font-weight:900;border-radius:999px;padding:.15rem .5rem;min-width:20px;text-align:center;display:none;animation:ncChatPulse 1.5s infinite;position:absolute;top:-4px;right:-4px;line-height:1.4;}
.nc-chat-header-right{display:flex;align-items:center;gap:.75rem;position:relative;}
.nc-chat-online-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.3);animation:ncChatPulse 2s infinite;}
.nc-chat-chevron{font-size:.7rem;color:rgba(255,255,255,.5);transition:transform .25s;}
.nc-chat-chevron.open{transform:rotate(180deg);}
.nc-chat-body{display:none;flex-direction:column;background:#fff;}
.nc-chat-toolbar{display:flex;align-items:center;gap:.35rem;padding:.5rem .75rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;}
.nc-chat-toolbar-label{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-right:.15rem;}
.nc-chat-quick{background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:.2rem .6rem;font-size:.72rem;cursor:pointer;transition:all .15s;color:#475569;font-weight:700;white-space:nowrap;}
.nc-chat-quick:hover{background:#003366;color:#fff;border-color:#003366;}
.nc-chat-messages{height:260px;overflow-y:auto;padding:.85rem .75rem;display:flex;flex-direction:column;gap:.6rem;background:linear-gradient(180deg,#f0f4f8 0%,#f8fafc 100%);scroll-behavior:smooth;}
.nc-chat-messages::-webkit-scrollbar{width:4px;}
.nc-chat-messages::-webkit-scrollbar-track{background:transparent;}
.nc-chat-messages::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px;}
.nc-chat-msg{max-width:82%;animation:ncChatMsgIn .2s ease both;display:flex;flex-direction:column;}
.nc-chat-msg.mine{align-self:flex-end;align-items:flex-end;}
.nc-chat-msg.theirs{align-self:flex-start;align-items:flex-start;}
.nc-chat-bubble{padding:.55rem .85rem;border-radius:16px;font-size:.82rem;line-height:1.5;word-break:break-word;}
.nc-chat-msg.mine .nc-chat-bubble{background:linear-gradient(135deg,#003366,#004d99);color:#fff;border-bottom-right-radius:4px;box-shadow:0 2px 8px rgba(0,51,102,.25);}
.nc-chat-msg.theirs .nc-chat-bubble{background:#fff;color:#1e293b;border:1px solid #e2e8f0;border-bottom-left-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.nc-chat-msg.system .nc-chat-bubble{background:transparent;color:#94a3b8;font-size:.72rem;font-style:italic;text-align:center;border:none;box-shadow:none;padding:.25rem .5rem;}
.nc-chat-msg.system{align-self:center;max-width:100%;}
.nc-chat-meta{font-size:.6rem;color:#94a3b8;margin-top:.2rem;display:flex;align-items:center;gap:.3rem;}
.nc-chat-msg.mine .nc-chat-meta{flex-direction:row-reverse;}
.nc-chat-cs{font-weight:800;color:#475569;}
.nc-chat-tick{color:#22c55e;font-size:.65rem;}
.nc-chat-empty{text-align:center;color:#94a3b8;font-size:.8rem;padding:2rem .75rem;display:flex;flex-direction:column;align-items:center;gap:.5rem;}
.nc-chat-empty-icon{font-size:2rem;}
.nc-chat-typing{display:none;align-self:flex-start;padding:.5rem .75rem;background:#fff;border-radius:16px;border-bottom-left-radius:4px;border:1px solid #e2e8f0;gap:.3rem;align-items:center;}
.nc-chat-typing span{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:ncChatTyping 1.2s infinite;}
.nc-chat-typing span:nth-child(2){animation-delay:.15s;}
.nc-chat-typing span:nth-child(3){animation-delay:.3s;}
.nc-chat-input-wrap{padding:.65rem .75rem;border-top:1px solid #e2e8f0;background:#fff;display:flex;flex-direction:column;gap:.5rem;}
.nc-chat-input-row{display:flex;gap:.5rem;align-items:flex-end;}
.nc-chat-input{flex:1;border:1.5px solid #e2e8f0;border-radius:12px;padding:.5rem .75rem;font-size:.82rem;outline:none;resize:none;font-family:inherit;line-height:1.4;max-height:80px;transition:border-color .2s;}
.nc-chat-input:focus{border-color:#003366;box-shadow:0 0 0 3px rgba(0,51,102,.08);}
.nc-chat-send{background:linear-gradient(135deg,#003366,#004d99);color:#fff;border:none;border-radius:10px;padding:.5rem .85rem;font-weight:900;font-size:.85rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.3rem;white-space:nowrap;}
.nc-chat-send:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,51,102,.3);}
.nc-chat-send:active{transform:translateY(0);}
.nc-chat-footer{display:flex;align-items:center;justify-content:space-between;font-size:.6rem;color:#94a3b8;}
.nc-chat-emoji-bar{display:flex;gap:.2rem;}
.nc-chat-emoji{cursor:pointer;font-size:.9rem;padding:.1rem;border-radius:4px;transition:transform .15s;line-height:1;}
.nc-chat-emoji:hover{transform:scale(1.3);}
.nc-handover-banner{display:none;position:sticky;top:0;left:0;right:0;z-index:1000;background:#d97706;color:#fff;text-align:center;padding:.55rem 1rem;font-weight:900;font-size:.82rem;animation:nc-handover-pulse 1s ease-in-out infinite;align-items:center;justify-content:center;gap:1rem;}
.nc-handover-banner.active{display:flex;}
.countdown-box{text-align:center;padding:1.25rem;background:linear-gradient(135deg,#003366,#004080);border-radius:12px;color:#fff;margin-bottom:1.25rem;transition:background .8s ease,box-shadow .3s ease;}
@keyframes nc-pulse-glow{0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,.0);}50%{box-shadow:0 0 18px 8px rgba(220,38,38,.55);}}
.countdown-box.nc-warning{background:linear-gradient(135deg,#92400e,#b45309)!important;}
.countdown-box.nc-danger{background:linear-gradient(135deg,#7f1d1d,#991b1b)!important;}
.countdown-box.nc-pulse{animation:nc-pulse-glow 1s ease-in-out infinite;}
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
  .nc-sticky-banner{display:flex;position:sticky;top:60px;z-index:999;}
  .nc-sticky-banner .status-pill{background:rgba(255,255,255,.15)!important;color:#fff!important;border-color:rgba(255,255,255,.3)!important;}
  .nc-wrap{padding-top:.5rem;}
}

.nc-sticky-banner{
  display:none;
  background:linear-gradient(135deg,#003366,#004080);
  color:#fff;padding:.6rem 2rem;
  align-items:center;justify-content:space-between;gap:1rem;
  border-bottom:1px solid rgba(255,255,255,.08);
  box-shadow:0 2px 12px rgba(0,0,0,.3);
  margin-bottom:1.5rem;
}

.nc-offline-bar{display:none;position:fixed;bottom:1.25rem;left:50%;transform:translateX(-50%);
  z-index:2000;padding:.6rem 1.25rem;border-radius:999px;font-size:.82rem;font-weight:800;
  white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.25);}
.nc-offline-bar.offline{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;}
.nc-offline-bar.online{background:#f0fdf4;color:#15803d;border:1px solid #86efac;}
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
        <div id="statusPill" class="status-pill pill-live">🔴 ON AIR</div>
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
          <span id="slotTimeBanner" style="font-family:monospace;font-weight:900;color:#fff;font-size:.88rem;">{{ $slot['from'] }} – {{ $slot['to'] }}</span>
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
        <button onclick="ncOpenHandoverDialog()" style="margin-left:auto;display:inline-flex;align-items:center;gap:.35rem;background:rgba(200,16,46,.18);border:1px solid rgba(200,16,46,.35);color:#fca5a5;padding:.3rem .8rem;border-radius:999px;font-size:.72rem;font-weight:800;cursor:pointer;transition:all .2s;white-space:nowrap;" onmouseover="this.style.background='rgba(200,16,46,.32)'" onmouseout="this.style.background='rgba(200,16,46,.18)'">🔄 Request Handover</button>
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
      <div id="statusPillSticky" class="status-pill pill-live" style="font-size:.72rem;">🔴 ON AIR</div>
      <div>
        <div style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);" id="stickyLabel">Time until slot starts</div>
        <div style="font-family:monospace;font-weight:900;font-size:1.2rem;letter-spacing:.06em;" id="stickyTime">--:--:--</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:1.5rem;">
      <div style="text-align:center;">
        <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35);">Your slot</div>
        <div id="slotTimeInfo" style="font-family:monospace;font-weight:800;font-size:.85rem;">{{ $slot['from'] }} – {{ $slot['to'] }}</div>
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
  {{-- Early handover banner --}}
  <div class="nc-handover-banner" id="ncHandoverBanner">
    <span id="ncHandoverBannerText">🔄 Early Handover Requested — Waiting for next controller to accept…</span>
    <button onclick="document.getElementById('ncHandoverBanner').classList.remove('active')" style="background:rgba(0,0,0,.2);border:none;color:#fff;border-radius:999px;padding:.2rem .65rem;font-size:.75rem;font-weight:800;cursor:pointer;margin-left:.5rem;" id="ncHandoverDismiss">Dismiss</button>
  </div>

  {{-- Handover chat widget --}}
  <div class="nc-chat-widget" id="ncChatWidget">
    <div class="nc-chat-header" onclick="ncChatToggle()">
      <div class="nc-chat-header-left">
        <div class="nc-chat-avatar" style="position:relative;">🎙️<span class="nc-chat-badge" id="ncChatBadge">0</span></div>
        <div class="nc-chat-header-info">
          <div class="nc-chat-header-title">Handover Chat</div>
          <div class="nc-chat-header-sub" id="ncChatHeaderSub">Connecting to other controller…</div>
        </div>
      </div>
      <div class="nc-chat-header-right">
        <div class="nc-chat-online-dot" id="ncChatOnlineDot" style="display:none;"></div>
        <div class="nc-chat-chevron" id="ncChatChevron">▼</div>
      </div>
    </div>
    <div class="nc-chat-body">

      <div class="nc-chat-messages" id="ncChatMessages">
        <div class="nc-chat-empty" id="ncChatEmpty">
          <div class="nc-chat-empty-icon">🎙️</div>
          <div>Handover chat ready</div>
          <div style="font-size:.72rem;">Say hello to the other controller!</div>
        </div>
        <div class="nc-chat-typing" id="ncChatTyping">
          <span></span><span></span><span></span>
        </div>
      </div>
      <div class="nc-chat-input-wrap">
        <div class="nc-chat-input-row">
          <textarea class="nc-chat-input" id="ncChatInput" placeholder="Type a message… (Enter to send)" maxlength="500" rows="1"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();ncChatSend();}"
            oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,80)+'px';"></textarea>
          <button class="nc-chat-send" onclick="ncChatSend()">Send ➤</button>
        </div>
        <div class="nc-chat-footer">
          <div style="font-size:.6rem;color:#94a3b8;">RAYNET · Handover Channel</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Access revoked dialog --}}
  <div class="nc-dialog-overlay" id="ncAccessRevokedOverlay" style="display:none;background:rgba(0,0,0,.75);position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;">
    <div class="nc-dialog" style="max-width:380px;text-align:center;">
      <div style="font-size:2.5rem;margin-bottom:.75rem;">⚠️</div>
      <h2 style="margin-bottom:.5rem;">Slot No Longer Active</h2>
      <p style="color:#64748b;font-size:.88rem;margin-bottom:1.25rem;">Your net control slot has ended or been reassigned. You will be redirected in <strong id="ncAccessRevokedCount">10</strong> seconds.</p>
      <div style="background:#f1f5f9;border-radius:8px;height:6px;overflow:hidden;margin-bottom:1.25rem;">
        <div id="ncAccessRevokedBar" style="height:100%;background:#C8102E;border-radius:8px;width:100%;transition:width 1s linear;"></div>
      </div>
      <button onclick="window.location.reload()" style="background:#003366;color:#fff;border:none;border-radius:8px;padding:.6rem 1.5rem;font-weight:800;cursor:pointer;font-size:.88rem;">Leave now →</button>
    </div>
  </div>

  {{-- Confirm dialog --}}
  <div class="nc-dialog-overlay" id="ncHandoverOverlay">
    <div class="nc-dialog">
      <h2>🚨 Request Early Handover?</h2>
      <p>This will email the next scheduled controller asking them to take over. A notification will remain on your screen until they accept.</p>
      <p style="font-size:.8rem;color:#b91c1c;font-weight:700;">Only use this in an emergency or if you genuinely need to hand over early.</p>
      <div class="nc-dialog-btns">
        <button class="btn-cancel" onclick="ncCloseHandoverDialog()">Cancel</button>
        <button class="btn-confirm" id="ncHandoverConfirmBtn" onclick="ncConfirmHandover()">Yes, Request Handover</button>
      </div>
    </div>
  </div>

  <div class="nc-desktop-grid">
  <div class="nc-left">

  {{-- Countdown --}}
  <div class="countdown-box" id="countdownBox">
    <div class="countdown-label" id="countdownLabel">Time remaining in your slot</div>
    <div class="countdown-time" id="countdownTime">--:--:--</div>
    <div class="countdown-sub" id="countdownSub">Your slot: {{ $slot['from'] }} – {{ $slot['to'] }}</div>
  </div>

  {{-- Handover info --}}
  <div class="nc-card nc-handover-card">
    <div class="nc-card-title">🔄 Controller Schedule</div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">

      @if($prevSlot)
      <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .65rem;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
        <div style="width:3px;align-self:stretch;border-radius:999px;background:#cbd5e1;flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.1rem;">Before you</div>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
            <span style="font-family:monospace;font-weight:900;font-size:.95rem;color:#475569;">{{ $prevSlot['callsign'] }}</span>
            <span style="font-family:monospace;font-size:.78rem;color:#94a3b8;background:#f1f5f9;padding:.1rem .45rem;border-radius:4px;">{{ $prevSlot['from'] }} – {{ $prevSlot['to'] }}</span>
          </div>
        </div>
      </div>
      @else
      <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .65rem;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
        <div style="width:3px;align-self:stretch;border-radius:999px;background:#cbd5e1;flex-shrink:0;"></div>
        <div style="font-size:.82rem;color:#94a3b8;font-style:italic;">Net opens</div>
      </div>
      @endif

      <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem .65rem;border-radius:8px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);border:2px solid #a5b4fc;">
        <div style="width:3px;align-self:stretch;border-radius:999px;background:#6366f1;flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#6366f1;margin-bottom:.1rem;">Your slot</div>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
            <span style="font-family:monospace;font-weight:900;font-size:.95rem;color:#003366;">{{ $user->callsign }}</span>
            <span id="slotTimeHandover" style="font-family:monospace;font-size:.78rem;color:#4338ca;background:#c7d2fe;padding:.1rem .45rem;border-radius:4px;">{{ $slot['from'] }} – {{ $slot['to'] }}</span>
          </div>
        </div>
      </div>

      @if($nextSlot)
      <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .65rem;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
        <div style="width:3px;align-self:stretch;border-radius:999px;background:#cbd5e1;flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.1rem;">After you</div>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
            <span style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
              <span style="font-family:monospace;font-weight:900;font-size:.95rem;color:#475569;">{{ $nextSlot['callsign'] }}</span>
              <span id="nextCtrlPresenceDot" data-cs="{{ strtoupper($nextSlot['callsign']) }}" style="display:none;align-items:center;gap:.25rem;font-size:.62rem;font-weight:800;color:#16a34a;background:#dcfce7;padding:.1rem .4rem;border-radius:999px;border:1px solid #bbf7d0;">
                <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;animation:pulse 1.2s infinite;"></span> Online
              </span>
              <span id="nextCtrlNotifiedBadge" data-cs="{{ strtoupper($nextSlot['callsign']) }}" style="display:none;align-items:center;gap:.25rem;font-size:.62rem;font-weight:800;color:#92400e;background:#fef3c7;padding:.1rem .4rem;border-radius:999px;border:1px solid #fde68a;">
                📧 Notified
              </span>
            </span>
            <span style="font-family:monospace;font-size:.78rem;color:#94a3b8;background:#f1f5f9;padding:.1rem .45rem;border-radius:4px;">{{ $nextSlot['from'] }} – {{ $nextSlot['to'] }}</span>
          </div>
        </div>
      </div>
      @else
      <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .65rem;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
        <div style="width:3px;align-self:stretch;border-radius:999px;background:#cbd5e1;flex-shrink:0;"></div>
        <div style="font-size:.82rem;color:#94a3b8;font-style:italic;">Net closes</div>
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
var NC_START_TIME  = Date.now();
var NC_NET_NAME    = '{{ addslashes($net['callsign'] ?? '') }}';
var NC_FREQUENCY   = '{{ addslashes($net['frequency'] ?? '') }}';
var NC_USER_CS     = '{{ addslashes($user->callsign ?? '') }}';
var NC_USER_NAME   = '{{ addslashes($user->name ?? '') }}';
var NC_SLOT_FROM     = '{{ addslashes($slot['from'] ?? '') }}';
var NC_SLOT_TO       = '{{ addslashes($slot['to'] ?? '') }}';
var CAN_LOG          = {{ $slot['can_log'] ? 'true' : 'false' }};
var SLOT_FROM_MS     = {{ $slot['from_dt'] ? $slot['from_dt']->getTimestampMs() : 0 }};
var SLOT_TO_MS       = {{ $slot['to_dt'] ? $slot['to_dt']->getTimestampMs() : 0 }};
var WINDOW_START_MS  = {{ isset($slot['window_start']) ? $slot['window_start']->getTimestampMs() : 0 }};
var IS_PRE_SLOT      = {{ (!$slot['can_log'] && now('Europe/London')->lt($slot['from_dt'] ?? now())) ? 'true' : 'false' }};
var NEXT_SLOT_CS     = '{{ strtoupper($nextSlot['callsign'] ?? '') }}';
var NEXT_SLOT_FROM   = '{{ $nextSlot['from'] ?? '' }}';
var NEXT_SLOT_FROM_MS = {{ isset($nextSlot['from']) ? (\Carbon\Carbon::now('Europe/London')->setTimeFromTimeString($nextSlot['from'].':00')->getTimestampMs()) : 0 }};
var PREV_SLOT_CS     = '{{ strtoupper($prevSlot['callsign'] ?? '') }}';
// Chat room = two callsigns sorted alphabetically, joined with underscore
var _chatCsA = NC_USER_CS < (IS_PRE_SLOT ? PREV_SLOT_CS : NEXT_SLOT_CS) ? NC_USER_CS : (IS_PRE_SLOT ? PREV_SLOT_CS : NEXT_SLOT_CS);
var _chatCsB = NC_USER_CS < (IS_PRE_SLOT ? PREV_SLOT_CS : NEXT_SLOT_CS) ? (IS_PRE_SLOT ? PREV_SLOT_CS : NEXT_SLOT_CS) : NC_USER_CS;
var CHAT_ROOM        = _chatCsA + '_' + _chatCsB;
var CHAT_OTHER_CS    = IS_PRE_SLOT ? PREV_SLOT_CS : NEXT_SLOT_CS;
var NC_QRZ         = {};
var NC_INVITE_CS   = '';
var NC_OFFLINE_KEY = 'raynet_nc_offline_log';
var ncQrzTimer;

// ── Helpers ───────────────────────────────────────────────────────────────
function parseTime(t) {
    var now = new Date(); var p = t.split(':');
    var d = new Date(); d.setHours(parseInt(p[0]), parseInt(p[1]), 0, 0);
    // If more than 12h in the future, it's probably yesterday
    if (d - now > 12 * 3600000) { d = new Date(d.getTime() - 86400000); }
    return d;
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
function showNetEndedOverlay() {
    if (document.getElementById('ncNetEndedOverlay')) return;
    var o = document.createElement('div');
    o.id = 'ncNetEndedOverlay';
    o.style.cssText = 'position:fixed;inset:0;background:rgba(240,244,248,.97);z-index:9000;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;font-family:sans-serif;';
    o.innerHTML = '<div style="font-size:3rem;">📻</div>'
        + '<div style="font-size:1.4rem;font-weight:900;color:#003366;">Net has ended</div>'
        + '<div style="color:#6b7f96;font-size:.9rem;">This net session has been closed by the administrator.</div>'
        + '<a href="/" style="margin-top:1rem;padding:.6rem 1.5rem;background:#003366;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;">Return home</a>';
    document.body.appendChild(o);
}
function hideNetEndedOverlay() {
    var o = document.getElementById('ncNetEndedOverlay');
    if (o) o.remove();
}
function pollNetActive() {
    fetch('/net-status-json', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.active === false) {
            if (window._ncNetEnded) return;
            window._ncNetEnded = true;
            var mins = Math.round((Date.now() - NC_START_TIME) / 60000);
            var params = new URLSearchParams({
                net:      NC_NET_NAME,
                freq:     NC_FREQUENCY,
                cs:       NC_USER_CS,
                name:     NC_USER_NAME,
                from:     NC_SLOT_FROM,
                to:       NC_SLOT_TO,
                duration: mins
            });
            window.location.href = '/net-control/thankyou?' + params.toString();
        } else {
            window._ncNetEnded = false;
            // Live-update slot times if admin changed them
            if (d.slots && d.slots.length) {
                var myCs = (typeof NC_USER_CS !== 'undefined' ? NC_USER_CS : '').toUpperCase();
                var matched = null;
                for (var si = 0; si < d.slots.length; si++) {
                    if (d.slots[si].callsign && d.slots[si].callsign.toUpperCase() === myCs) {
                        matched = d.slots[si]; break;
                    }
                }
                // If current user no longer has a slot in list, server will confirm
                // Fall back to first slot if no callsign match
                var slot = matched || d.slots[0];
                if (slot && slot.to && slot.to !== SLOT_TO) {
                    SLOT_TO    = slot.to;
                    NC_SLOT_TO = slot.to;
                    slotTo     = parseTime(slot.to);
                    if (slotTo <= slotFrom) { slotTo = new Date(slotTo.getTime() + 86400000); }
                    SLOT_TO_MS = slotTo.getTime();
                    var slotStr = SLOT_FROM + ' – ' + slot.to;
                    var sub = document.getElementById('countdownSub');
                    if (sub) sub.textContent = 'Your slot: ' + slotStr;
                    var banner = document.getElementById('slotTimeBanner');
                    if (banner) banner.textContent = slotStr;
                    var info = document.getElementById('slotTimeInfo');
                    if (info) info.textContent = slotStr;
                    var handover = document.getElementById('slotTimeHandover');
                    if (handover) handover.textContent = slotStr;
                    var newDiff = Math.max(0, Math.floor((slotTo - new Date()) / 1000));
                    scrambleCountdown(newDiff);
                }
                if (slot && slot.from && slot.from !== SLOT_FROM) {
                    SLOT_FROM = slot.from;
                    NC_SLOT_FROM = slot.from;
                    slotFrom  = parseTime(slot.from);
                    windowStart = new Date(slotFrom.getTime() - WINDOW_MINS * 60000);
                    SLOT_FROM_MS = slotFrom.getTime();
                    var slotStr2 = slot.from + ' – ' + SLOT_TO;
                    var sub = document.getElementById('countdownSub');
                    if (sub) sub.textContent = 'Your slot: ' + slotStr2;
                    var banner = document.getElementById('slotTimeBanner');
                    if (banner) banner.textContent = slotStr2;
                    var info = document.getElementById('slotTimeInfo');
                    if (info) info.textContent = slotStr2;
                    var handover = document.getElementById('slotTimeHandover');
                    if (handover) handover.textContent = slotStr2;
                }
            }
        }
    }).catch(function(){});
}

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

    window.scrambleCountdown = function scrambleCountdown(finalSec) {
            var ctT = document.getElementById('countdownTime');
            var ctL = document.getElementById('countdownLabel');
            if (!ctT) return;
            if (window._scrambleTimer) clearInterval(window._scrambleTimer);
            var chars = '0123456789';
            var duration = 3000;
            var interval = 40;
            var totalSteps = Math.floor(duration / interval);
            var target = fmt(finalSec);
            var locked = new Array(target.length).fill(false);
            if (ctL) { ctL.textContent = 'Recalculating…'; ctL.style.color = '#fbbf24'; }
            var step = 0;
            window._scrambleTimer = setInterval(function() {
                step++;
                var progress = step / totalSteps;
                // Lock chars in left-to-right during final 40% of animation
                for (var ci = 0; ci < target.length; ci++) {
                    if (target[ci] === ':') { locked[ci] = true; continue; }
                    var lockThreshold = 0.6 + (ci / target.replace(/:/g,'').length) * 0.38;
                    if (progress >= lockThreshold) locked[ci] = true;
                }
                var scrambled = '';
                for (var ci = 0; ci < target.length; ci++) {
                    if (target[ci] === ':') { scrambled += ':'; continue; }
                    if (locked[ci]) {
                        scrambled += target[ci];
                    } else {
                        scrambled += chars[Math.floor(Math.random() * chars.length)];
                    }
                }
                ctT.textContent = scrambled;
                if (step >= totalSteps) {
                    clearInterval(window._scrambleTimer);
                    window._scrambleTimer = null;
                    ctT.textContent = target;
                    if (ctL) { ctL.textContent = 'Time remaining in your slot'; ctL.style.color = ''; }
                }
            }, interval);
        }

        function fmt(sec) {
        var h=Math.floor(sec/3600),m=Math.floor((sec%3600)/60),s=sec%60;
        return (h?String(h).padStart(2,'0')+':':'')+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    }


    var nowMs     = now.getTime();
    var inSlot    = SLOT_FROM_MS > 0 && nowMs >= SLOT_FROM_MS && nowMs < SLOT_TO_MS;
    var preSlot   = SLOT_FROM_MS > 0 && nowMs < SLOT_FROM_MS;
    var postSlot  = SLOT_TO_MS > 0 && nowMs >= SLOT_TO_MS;

    if (preSlot) {
        // In the 15-min pre-window — slot hasn't started yet
        var diffToStart = Math.max(0, Math.floor((SLOT_FROM_MS - nowMs) / 1000));
        ctL.textContent = 'Your slot starts in';
        ctT.textContent = fmt(diffToStart);
        if(pill){pill.className='status-pill pill-pre';pill.textContent='⏳ STANDING BY';}
        var box=document.getElementById('countdownBox');
        if(box){ box.classList.remove('nc-warning','nc-danger','nc-pulse'); box.classList.add('nc-warning'); }
        CAN_LOG=false;
        bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#eff6ff;color:#1d4ed8;';
        if (window._ncLoggingEnabled === false) {
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#fff7ed;color:#c2410c;';
            bnr.innerHTML='⚠️ <strong>Logging is currently disabled</strong> by the net controller. Your slot starts at <strong>' + NC_SLOT_FROM + '</strong>.';
        } else {
            bnr.innerHTML='⏳ <strong>Standing by</strong> — your slot starts at <strong>' + NC_SLOT_FROM + '</strong>. Logging will enable automatically.';
        }
        if(ctS) ctS.textContent = 'Your slot: ' + NC_SLOT_FROM + ' – ' + NC_SLOT_TO;
    } else if (inSlot || true) {  // server already validated slot — always show live state
        var diff=Math.max(0,Math.floor((SLOT_TO_MS > 0 ? SLOT_TO_MS - nowMs : slotTo - now)/1000));
        ctL.textContent='Time remaining in your slot'; ctT.textContent=fmt(diff);
        if(pill){pill.className='status-pill pill-live';pill.textContent='🔴 ON AIR';}
        var box=document.getElementById('countdownBox');
        if(box){
            box.classList.remove('nc-warning','nc-danger','nc-pulse');
            if(diff<=60){box.classList.add('nc-danger','nc-pulse');}
            else if(diff<=300){box.classList.add('nc-warning');}
        }
        if(isOffline()){
            CAN_LOG=true;
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#1e293b;color:#fbbf24;';
            bnr.innerHTML='📴 <strong>Offline mode</strong> — entries will sync when reconnected';
        } else if (preSlot || IS_PRE_SLOT) {
            CAN_LOG=false;
            bnr.style.cssText='border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#eff6ff;color:#1d4ed8;';
            bnr.innerHTML='⏳ <strong>Standing by</strong> — your slot starts at <strong>' + NC_SLOT_FROM + '</strong>. Logging will enable automatically.';
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
        if (window._ncLoggingEnabled === false) {
            err.textContent='Station logging is disabled by the net controller';
            err.style.display='';
            return;
        }
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
    if (window._ncNetEnded) return;
    fetch('{{ route('net-control.stations') }}', {cache:'no-store'})
    .then(function(r){
        if (!r.ok) { return null; }
        return r.json();
    })
    .then(function(data){
        if (!data) return;
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
    setInterval(pollLoggingStatus, 10000);
    pollNetActive();
    setInterval(pollNetActive, 15000);

    // Every 15s verify the server still considers this user's slot active
    function pollAccessCheck() {
        if (_handoverRequested) return; // already handing over, don't interfere
        fetch('/net-control/access-check', {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.active) {
                // Show the access revoked modal with countdown
                var overlay = document.getElementById('ncAccessRevokedOverlay');
                if (overlay && overlay.style.display !== 'flex') {
                    overlay.style.display = 'flex';
                    var countEl = document.getElementById('ncAccessRevokedCount');
                    var barEl   = document.getElementById('ncAccessRevokedBar');
                    var secs = 10;
                    if (barEl) barEl.style.width = '100%';
                    var revokedTick = setInterval(function() {
                        secs--;
                        if (countEl) countEl.textContent = secs;
                        if (barEl) barEl.style.width = ((secs / 10) * 100) + '%';
                        if (secs <= 0) {
                            clearInterval(revokedTick);
                            window.location.reload();
                        }
                    }, 1000);
                }
            }
        })
        .catch(function(){});
    }
    setTimeout(function(){
        pollAccessCheck();
        setInterval(pollAccessCheck, 15000);
    }, 5000); // slight delay so it doesn't fire on initial load

    // ── Heartbeat: tell server this controller is on the portal ──────────────
    function sendHeartbeat() {
        fetch('/net-control/heartbeat', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({})
        }).catch(function(){});
    }
    sendHeartbeat();
    setInterval(sendHeartbeat, 15000);

    // ── Presence: show green dot if next controller is on the portal ─────────
    function pollPresence() {
        fetch('/net-control/presence', {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            // Schedule card online dot
            var dot = document.getElementById('nextCtrlPresenceDot');
            var notifiedBadge = document.getElementById('nextCtrlNotifiedBadge');
            if (dot) {
                var cs = dot.getAttribute('data-cs');
                var isOnline   = d.online   && d.online.indexOf(cs)   !== -1;
                var isNotified = d.notified && d.notified.indexOf(cs) !== -1;
                dot.style.display           = isOnline ? 'inline-flex' : 'none';
                if (notifiedBadge) {
                    // Show notified badge only if not yet online
                    notifiedBadge.style.display = (!isOnline && isNotified) ? 'inline-flex' : 'none';
                }
            }
            // Chat widget online dot + header sub
            var chatDot = document.getElementById('ncChatOnlineDot');
            var chatSub = document.getElementById('ncChatHeaderSub');
            var otherOnline = d.online && d.online.indexOf(CHAT_OTHER_CS) !== -1;
            if (chatDot) chatDot.style.display = otherOnline ? 'block' : 'none';
            if (chatSub) chatSub.textContent = CHAT_OTHER_CS
                ? (otherOnline ? '🟢 ' + CHAT_OTHER_CS + ' is online' : CHAT_OTHER_CS + ' — not yet connected')
                : 'Handover channel';
        }).catch(function(){});
    }
    pollPresence();
    setInterval(pollPresence, 5000);

    // ── Handover chat ─────────────────────────────────────────────────────────
    var _chatOpen       = false;
    var _chatLastTs     = 0;
    var _chatUnread     = 0;
    var _chatInterval   = null;
    var _chatVisible    = false;
    var CHAT_WINDOW_MS  = 15 * 60 * 1000; // 15 minutes

    function ncChatShouldShow() {
        var nowMs = Date.now();
        // Outgoing controller: show when <= 15 min left in slot
        var outgoing = SLOT_TO_MS > 0 && NEXT_SLOT_CS && (SLOT_TO_MS - nowMs) <= CHAT_WINDOW_MS && (SLOT_TO_MS - nowMs) > 0;
        // Incoming controller: show when in pre-slot window
        var incoming = IS_PRE_SLOT && PREV_SLOT_CS;
        return (outgoing || incoming) && CHAT_ROOM && CHAT_ROOM !== '_';
    }

    window.ncChatToggle = function() {
        _chatOpen = !_chatOpen;
        var body  = document.querySelector('.nc-chat-body');
        var label = document.getElementById('ncChatToggleLabel');
        if (body)  body.style.display  = _chatOpen ? 'flex' : 'none';
        if (label) label.textContent   = _chatOpen ? '▼ Close' : '▲ Open';
        if (_chatOpen) {
            _chatUnread = 0;
            var badge = document.getElementById('ncChatBadge');
            if (badge) badge.style.display = 'none';
            var msgs = document.getElementById('ncChatMessages');
            if (msgs) msgs.scrollTop = msgs.scrollHeight;
        }
    }

    // ── Typing indicator ─────────────────────────────────────────────────────
    var _typingTimer    = null;
    var _isTyping       = false;
    var _typingPollInt  = null;

    function ncChatSetTyping(typing) {
        if (_isTyping === typing) return;
        _isTyping = typing;
        fetch('/net-control/chat/typing', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({room: CHAT_ROOM, typing: typing})
        }).catch(function(){});
    }

    function ncChatPollTyping() {
        if (!CHAT_ROOM || CHAT_ROOM === '_') return;
        fetch('/net-control/chat/typing?room=' + encodeURIComponent(CHAT_ROOM), {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            var indicator = document.getElementById('ncChatTyping');
            if (!indicator) return;
            if (d.typing && d.typing.length > 0) {
                indicator.style.display = 'flex';
                // Scroll to show it
                var container = document.getElementById('ncChatMessages');
                if (container) container.scrollTop = container.scrollHeight;
            } else {
                indicator.style.display = 'none';
            }
        }).catch(function(){});
    }

    // Hook input event on textarea for typing detection
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('ncChatInput');
        if (!input) return;
        input.addEventListener('input', function() {
            if (!CHAT_ROOM || CHAT_ROOM === '_') return;
            ncChatSetTyping(true);
            clearTimeout(_typingTimer);
            _typingTimer = setTimeout(function() {
                ncChatSetTyping(false);
            }, 4000);
        });
    });

    window.ncChatSend = function() {
        var input = document.getElementById('ncChatInput');
        var text  = input ? input.value.trim() : '';
        if (!text) return;
        input.value = '';
        input.style.height = 'auto';

        // Clear typing indicator on send
        clearTimeout(_typingTimer);
        ncChatSetTyping(false);

        // Render immediately — don't wait for poll
        var now = new Date();
        var timeStr = now.toLocaleTimeString('en-GB', {hour:'2-digit', minute:'2-digit'});
        var fakeTs  = now.getTime();
        ncChatRender([{cs: NC_USER_CS, type: 'message', text: text, time: timeStr, ts: fakeTs}]);
        // Advance lastTs so poll doesn't re-render this message
        if (fakeTs > _chatLastTs) _chatLastTs = fakeTs;

        fetch('/net-control/chat/send', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({room: CHAT_ROOM, message: text})
        }).catch(function(){});
    };

    window.ncChatQuick = function(text) {
        var input = document.getElementById('ncChatInput');
        if (input) {
            input.value = text;
            window.ncChatSend();
        }
    };

    window.ncChatEmoji = function(emoji) {
        var input = document.getElementById('ncChatInput');
        if (input) {
            input.value += emoji;
            input.focus();
        }
    };

    // Web Audio beep for incoming messages
    window._audioCtx = null;

    // Initialise AudioContext on first user gesture so browser allows it
    window._ncInitAudio = function() {
        if (window._audioCtx) return;
        try {
            window._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var buf = window._audioCtx.createBuffer(1, 1, 22050);
            var src = window._audioCtx.createBufferSource();
            src.buffer = buf;
            src.connect(window._audioCtx.destination);
            src.start(0);
        } catch(e) {}
    };
    ['click','keydown','touchstart'].forEach(function(evt) {
        document.addEventListener(evt, window._ncInitAudio, {once: true, passive: true});
    });

    window.ncChatBeep = function() {
        try {
            if (!window._audioCtx) {
                window._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            var play = function() {
                var ctx  = window._audioCtx;
                var osc  = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(660, ctx.currentTime + 0.12);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            };
            if (window._audioCtx.state === 'suspended') {
                window._audioCtx.resume().then(play);
            } else {
                play();
            }
        } catch(e) { console.warn('ncChatBeep:', e); }
    };

    function ncChatRender(msgs) {
        var container = document.getElementById('ncChatMessages');
        var empty     = document.getElementById('ncChatEmpty');
        var typing    = document.getElementById('ncChatTyping');
        if (!container || !msgs.length) return;
        if (empty) empty.style.display = 'none';

        var wasAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 60;

        msgs.forEach(function(m) {
            var system = (m.type === 'system' || m.cs === '__system__');
            var mine   = !system && m.cs === NC_USER_CS;
            var text   = String(m.text).replace(/</g,'&lt;').replace(/>/g,'&gt;');
            var div    = document.createElement('div');
            div.className = 'nc-chat-msg ' + (system ? 'system' : mine ? 'mine' : 'theirs');
            if (system) {
                div.innerHTML = '<div class="nc-chat-bubble">' + text + '</div>';
            } else {
                div.innerHTML = '<div class="nc-chat-bubble">' + text + '</div>'
                    + '<div class="nc-chat-meta">'
                    + (!mine ? '<span class="nc-chat-cs">' + m.cs + '</span>' : '')
                    + '<span>' + m.time + '</span>'
                    + (mine ? '<span class="nc-chat-tick">✓✓</span>' : '')
                    + '</div>';
            }
            if (typing && typing.parentNode === container) {
                container.insertBefore(div, typing);
            } else {
                container.appendChild(div);
            }
        });

        if (wasAtBottom) container.scrollTop = container.scrollHeight;

        // Only beep for real incoming messages — not system, not own
        var incoming = msgs.filter(function(m){
            return m.cs !== NC_USER_CS && m.cs !== '__system__' && m.type !== 'system';
        });
        if (incoming.length) {
            window.ncChatBeep();
            if (!_chatOpen) {
                _chatUnread += incoming.length;
                var badge = document.getElementById('ncChatBadge');
                if (badge) { badge.textContent = _chatUnread; badge.style.display = 'inline-block'; }
            }
        }
    }

    function ncChatPoll() {
        if (!CHAT_ROOM || CHAT_ROOM === '_') return;
        fetch('/net-control/chat/messages?room=' + encodeURIComponent(CHAT_ROOM) + '&since=' + _chatLastTs, {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.messages && d.messages.length) {
                ncChatRender(d.messages);
                _chatLastTs = d.messages[d.messages.length - 1].ts;
            }
        }).catch(function(){});
    }

    // Timed system messages + visibility management
    var _chatSent15   = false;
    var _chatSent5    = false;
    var _chatSentHO   = false;
    var _chatDisabled = false;
    var _chatHideAt   = null;

    function ncChatPostSystem(text) {
        fetch('/net-control/chat/send', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({room: CHAT_ROOM, message: text, type: 'system'})
        }).catch(function(){});
        ncChatRender([{cs:'__system__', type:'system', text:text,
            time: new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}), ts: Date.now()}]);
    }

    function ncChatDisableInput() {
        if (_chatDisabled) return;
        _chatDisabled = true;
        var input = document.getElementById('ncChatInput');
        var btn   = document.querySelector('.nc-chat-send');
        if (input) { input.disabled = true; input.placeholder = 'Handover in progress — chat closed'; }
        if (btn)   { btn.disabled = true; btn.style.opacity = '.4'; }
    }

    function ncChatCheckVisibility() {
        var widget = document.getElementById('ncChatWidget');
        if (!widget) return;
        var nowMs  = Date.now();
        var should = ncChatShouldShow();

        // Hide 1 min after handover starts
        if (_chatHideAt && nowMs >= _chatHideAt) {
            widget.style.display = 'none';
            _chatVisible = false;
            if (_chatInterval) { clearInterval(_chatInterval); _chatInterval = null; }
            return;
        }

        if (should && !_chatVisible) {
            widget.style.display = 'flex';
            widget.classList.remove('nc-chat-animate');
            void widget.offsetWidth;
            widget.classList.add('nc-chat-animate');
            _chatVisible = true;
            if (!_chatInterval) _chatInterval = setInterval(ncChatPoll, 3000);
            if (!_typingPollInt) _typingPollInt = setInterval(ncChatPollTyping, 2000);
            ncChatPoll();
        } else if (!should && _chatVisible && !_chatHideAt) {
            widget.style.display = 'none';
            _chatVisible = false;
            if (_chatInterval) { clearInterval(_chatInterval); _chatInterval = null; }
            if (_typingPollInt) { clearInterval(_typingPollInt); _typingPollInt = null; }
        }

        if (!CHAT_ROOM || CHAT_ROOM === '_') return;

        // 15 min warning (outgoing controller only)
        if (!_chatSent15 && SLOT_TO_MS > 0 && !IS_PRE_SLOT) {
            var minsLeft = (SLOT_TO_MS - nowMs) / 60000;
            if (minsLeft <= 15 && minsLeft > 0) {
                _chatSent15 = true;
                ncChatPostSystem('🎙️ Handover channel open — 15 minutes until slot change. Use this chat to coordinate with the next controller.');
            }
        }

        // 5 min warning
        if (!_chatSent5 && SLOT_TO_MS > 0 && !IS_PRE_SLOT) {
            var minsLeft5 = (SLOT_TO_MS - nowMs) / 60000;
            if (minsLeft5 <= 5 && minsLeft5 > 0) {
                _chatSent5 = true;
                ncChatPostSystem('⏳ 5 minutes to handover — please confirm the next controller is ready.');
            }
        }

        // Handover moment
        if (!_chatSentHO && SLOT_TO_MS > 0 && !IS_PRE_SLOT && nowMs >= SLOT_TO_MS) {
            _chatSentHO = true;
            ncChatPostSystem('🔄 Handover time. This chat will close in 1 minute.');
            ncChatDisableInput();
            _chatHideAt = nowMs + 60000;
        }
    }

    ncChatCheckVisibility();
    setInterval(ncChatCheckVisibility, 5000);
    setInterval(ncChatCheckVisibility, 5000);

    tick();
    loadLog();
    setInterval(loadLog, 3000);

    window.addEventListener('offline', function(){ tick(); loadLog(); });

    // ── Early Handover ────────────────────────────────────────────────────────
    var _handoverRequested = false;

    window.ncOpenHandoverDialog = function() {
        document.getElementById('ncHandoverOverlay').classList.add('active');
    };
    window.ncCloseHandoverDialog = function() {
        document.getElementById('ncHandoverOverlay').classList.remove('active');
    };
    window.ncConfirmHandover = function() {
        var btn = document.getElementById('ncHandoverConfirmBtn');
        btn.disabled = true; btn.textContent = 'Sending…';
        fetch('/net-control/early-handover', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({})
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) {
                ncCloseHandoverDialog();
                _handoverRequested = true;
                document.getElementById('ncHandoverBanner').classList.add('active');
                pollHandoverAccepted();
            } else {
                btn.disabled = false; btn.textContent = 'Yes, Request Handover';
                alert('Failed to send handover request. Please try again.');
            }
        })
        .catch(function(){
            btn.disabled = false; btn.textContent = 'Yes, Request Handover';
            alert('Network error. Please try again.');
        });
    };

    function pollHandoverAccepted() {
        if (!_handoverRequested) return;
        fetch('/net-control/handover-poll', {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.handover_accepted && d.redirect_at) {
                // Accepted — count down to the synced redirect_at timestamp
                var _redirectAt = d.redirect_at;
                var _bannerText = document.getElementById('ncHandoverBannerText');
                var _dismissBtn = document.getElementById('ncHandoverDismiss');
                if (_dismissBtn) _dismissBtn.style.display = 'none';

                // Change banner colour to green
                var _banner = document.getElementById('ncHandoverBanner');
                if (_banner) {
                    _banner.style.background = '#15803d';
                    _banner.style.animationPlayState = 'paused';
                }

                var _p = new URLSearchParams({
                    handover: '1',
                    cs:   '{{ strtoupper($user->callsign ?? "") }}',
                    name: '{{ addslashes($user->name ?? $user->callsign ?? "") }}',
                    net:  '{{ addslashes($slot["_net"]["callsign"] ?? "") }}',
                    freq: '{{ $slot["_net"]["frequency"] ?? "" }}',
                    from: '{{ $slot["from"] ?? "" }}',
                    to:   SLOT_TO || '{{ $slot["to"] ?? "" }}',
                    duration: '0',
                });
                var _url = '/net-control/thankyou?' + _p.toString();

                (function tickBanner() {
                    var secsLeft = Math.max(0, Math.ceil((_redirectAt - Date.now()) / 1000));
                    if (_bannerText) {
                        _bannerText.textContent = '✅ Handover accepted — handing over in ' + secsLeft + 's…';
                    }
                    if (secsLeft <= 0) {
                        window.location.href = _url;
                    } else {
                        setTimeout(tickBanner, 500);
                    }
                })();
            } else {
                setTimeout(pollHandoverAccepted, 5000);
            }
        })
        .catch(function(){ setTimeout(pollHandoverAccepted, 5000); });
    }
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
