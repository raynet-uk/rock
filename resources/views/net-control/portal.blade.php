@extends('layouts.app')
@section('title','Net Control — ' . $net['callsign'])
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
:root{--navy:#003366;--red:#C8102E;--border:#dde2e8;--muted:#6b7f96;}
*{box-sizing:border-box;}
.nc-wrap{max-width:900px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.nc-header{background:linear-gradient(135deg,#003366,#001a33);color:#fff;border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.nc-header h1{font-size:1.3rem;font-weight:900;margin:0 0 .25rem;}
.nc-header .cs{font-family:monospace;font-size:2rem;font-weight:900;letter-spacing:.05em;}
.nc-header .freq{font-size:.82rem;color:rgba(255,255,255,.6);margin-top:.2rem;}
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
</style>

<div class="nc-wrap">

  {{-- Header --}}
  <div class="nc-header">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <div class="nc-header h1">Net Control Portal</div>
        <div class="cs">{{ $net['callsign'] ?: 'NET' }}</div>
        <div class="freq">{{ $net['frequency'] ? $net['frequency'] . ' MHz' : '' }} &nbsp;·&nbsp; {{ $groupName }}</div>
      </div>
      <div style="text-align:right;">
        <div id="statusPill" class="status-pill pill-pre">⏳ Pre-slot</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-top:.4rem;">
          Logged in as <strong style="color:#fff;">{{ $user->callsign }}</strong>
        </div>
      </div>
    </div>
  </div>

  {{-- Countdown --}}
  <div class="countdown-box" id="countdownBox">
    <div class="countdown-label" id="countdownLabel">Time until your slot starts</div>
    <div class="countdown-time" id="countdownTime">--:--:--</div>
    <div class="countdown-sub" id="countdownSub">Your slot: {{ $slot['from'] }} – {{ $slot['to'] }}</div>
  </div>

  {{-- Handover info --}}
  <div class="nc-card">
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
  <div class="nc-card">
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

<script>
var SLOT_FROM    = '{{ $slot['from'] }}';
var SLOT_TO      = '{{ $slot['to'] }}';
var WINDOW_MINS  = {{ $windowMins }};
var LOG_HASH     = '';
var CAN_LOG      = false;

function parseTime(t) {
    var now = new Date();
    var p   = t.split(':');
    now.setHours(parseInt(p[0]), parseInt(p[1]), 0, 0);
    return now;
}

var slotFrom = parseTime(SLOT_FROM);
var slotTo   = parseTime(SLOT_TO);
// Handle midnight crossover
if (slotTo <= slotFrom) { slotTo = new Date(slotTo.getTime() + 86400000); }
var windowStart = new Date(slotFrom.getTime() - WINDOW_MINS * 60000);
var windowEnd   = new Date(slotTo.getTime()   + WINDOW_MINS * 60000);

function tick() {
    var now     = new Date();
    var diff    = 0;
    var label   = '';
    var pill    = document.getElementById('statusPill');
    var box     = document.getElementById('countdownBox');
    var ctLabel = document.getElementById('countdownLabel');
    var ctTime  = document.getElementById('countdownTime');
    var ctSub   = document.getElementById('countdownSub');
    var btn     = document.getElementById('ncSubmitBtn');
    var banner  = document.getElementById('logBanner');

    // Update script times
    var hhmm = now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
    ['scriptTime','scriptTime2'].forEach(function(id){
        var el = document.getElementById(id); if(el) el.textContent = hhmm;
    });

    if (now < slotFrom) {
        // Pre-slot
        diff = Math.floor((slotFrom - now) / 1000);
        var h = Math.floor(diff/3600), m = Math.floor((diff%3600)/60), s = diff%60;
        ctLabel.textContent = 'Time until your slot starts';
        ctTime.textContent  = (h?String(h).padStart(2,'0')+':':'') + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        if (pill) { pill.className='status-pill pill-pre'; pill.textContent='⏳ Slot starts '+SLOT_FROM; }
        box.style.background = 'linear-gradient(135deg,#1e293b,#334155)';
        CAN_LOG = false;
        banner.style.cssText = 'border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#fff7ed;color:#c2410c;';
        banner.innerHTML     = '⏳ Logging opens at <strong>' + SLOT_FROM + '</strong> — monitor the frequency until then';
    } else if (now >= slotFrom && now <= slotTo) {
        // Live slot
        diff = Math.floor((slotTo - now) / 1000);
        var h = Math.floor(diff/3600), m = Math.floor((diff%3600)/60), s = diff%60;
        ctLabel.textContent = 'Time remaining in your slot';
        ctTime.textContent  = (h?String(h).padStart(2,'0')+':':'') + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        if (pill) { pill.className='status-pill pill-live'; pill.textContent='🔴 ON AIR'; }
        box.style.background = 'linear-gradient(135deg,#C8102E,#8b0000)';
        CAN_LOG = true;
        banner.style.cssText = 'border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#f0fdf4;color:#15803d;';
        banner.innerHTML     = '🟢 You are <strong>ON AIR</strong> — log stations below';
    } else if (now > slotTo && now <= windowEnd) {
        // Post-slot
        ctLabel.textContent = 'Your slot has ended';
        ctTime.textContent  = 'ENDED';
        ctSub.textContent   = 'Monitoring period — ' + WINDOW_MINS + ' min(s) remaining';
        if (pill) { pill.className='status-pill pill-post'; pill.textContent='✓ Slot ended'; }
        box.style.background = 'linear-gradient(135deg,#334155,#1e293b)';
        CAN_LOG = false;
        banner.style.cssText = 'border-radius:8px;padding:.65rem .85rem;margin-bottom:.85rem;font-size:.8rem;font-weight:700;background:#f1f5f9;color:#64748b;';
        banner.innerHTML     = '✓ Your slot has ended — logging disabled. Page closes access in ' + Math.ceil((windowEnd-now)/60000) + ' min(s)';
    } else {
        // Window expired — reload to show no-access page
        location.reload();
        return;
    }

    if (btn) btn.disabled = !CAN_LOG;
    setTimeout(tick, 1000);
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function loadLog() {
    fetch('{{ route("net-control.stations") }}', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(data){
        var hash = data.map(function(e){ return e.id+':'+e.callsign; }).join(',');
        if (hash === LOG_HASH) return;
        LOG_HASH = hash;
        var log   = document.getElementById('ncLog');
        var empty = document.getElementById('ncLogEmpty');
        var cnt   = document.getElementById('ncLogCount');
        if (cnt) cnt.textContent = data.length + ' station' + (data.length!==1?'s':'');
        if (!data.length) {
            log.innerHTML = '';
            empty.style.display = '';
            return;
        }
        empty.style.display = 'none';
        log.innerHTML = data.map(function(e, i) {
            var qrz  = e.qrz_data || {};
            if (typeof qrz === 'string') { try { qrz = JSON.parse(qrz); } catch(ex){ qrz={}; } }
            var time = new Date(e.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
            var bg   = i%2===0?'#fff':'#f9fafb';
            return '<div class="log-row" style="background:'+bg+'">'
                + '<div style="font-size:.65rem;color:#cbd5e1;text-align:center;">'+(i+1)+'</div>'
                + '<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">'+time+'</div>'
                + '<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">'+escHtml(e.callsign)+'</div>'
                + '<div style="font-weight:600;color:#334155;font-size:.8rem;">'+escHtml(e.name||'—')+'</div>'
                + '<div style="font-size:.68rem;">' + (qrz.licence_class ? '<span style="padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#15803d;font-weight:800;font-size:.65rem;">'+escHtml(qrz.licence_class)+'</span>' : '') + '</div>'
                + '<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">'+escHtml(e.signal_report||'—')+'</div>'
                + '<div></div>'
                + '</div>';
        }).join('');
    }).catch(function(){});
}

function ncLog() {
    if (!CAN_LOG) return;
    var cs  = document.getElementById('ncCallsign').value.trim().toUpperCase();
    var rep = document.getElementById('ncReport').value.trim();
    var notes = document.getElementById('ncNotes').value.trim();
    var err = document.getElementById('ncError');
    if (!cs) { err.textContent='Callsign required'; err.style.display=''; return; }
    err.style.display = 'none';

    fetch('{{ route("net-control.log") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({callsign:cs, signal_report:rep, notes:notes})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            document.getElementById('ncCallsign').value = '';
            document.getElementById('ncReport').value   = '';
            document.getElementById('ncNotes').value    = '';
            loadLog();
        } else {
            err.textContent = d.error || 'Failed to log';
            err.style.display = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    tick();
    loadLog();
    setInterval(loadLog, 3000);

    var ci = document.getElementById('ncCallsign');
    if (ci) ci.addEventListener('keydown', function(e){ if(e.key==='Enter') ncLog(); });
});
</script>
@endsection
