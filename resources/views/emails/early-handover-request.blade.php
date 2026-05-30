<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;color:#1e293b;}
.wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.08);}
.header{background:linear-gradient(135deg,#7f1d1d,#C8102E);padding:2rem;text-align:center;}
.header h1{color:#fff;margin:0;font-size:1.4rem;font-weight:900;letter-spacing:.02em;}
.header p{color:rgba(255,255,255,.7);margin:.4rem 0 0;font-size:.85rem;}
.body{padding:2rem;}
.alert-box{background:#fef2f2;border:2px solid #fca5a5;border-radius:10px;padding:1rem 1.25rem;margin:1rem 0;text-align:center;}
.alert-box .alert-title{font-size:1rem;font-weight:900;color:#991b1b;margin-bottom:.3rem;}
.alert-box .alert-sub{font-size:.82rem;color:#b91c1c;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin:1.25rem 0;background:#f8fafc;border-radius:10px;padding:1rem;border:1px solid #e2e8f0;}
.info-item label{display:block;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.2rem;}
.info-item span{font-size:.95rem;font-weight:700;color:#1e293b;}
.slot-box{background:linear-gradient(135deg,#003366,#004080);color:#fff;border-radius:10px;padding:1rem 1.25rem;margin:1.25rem 0;text-align:center;}
.slot-box .slot-label{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);}
.slot-box .slot-time{font-size:1.6rem;font-weight:900;font-family:monospace;margin:.2rem 0;}
.btn{display:inline-block;background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff !important;text-decoration:none;padding:.85rem 2.5rem;border-radius:999px;font-weight:900;font-size:1rem;margin:1.25rem 0;}
.footer{background:#f8fafc;padding:.85rem 2rem;font-size:.72rem;color:#94a3b8;text-align:center;border-top:1px solid #e5e7eb;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>🚨 Early Handover Requested</h1>
    <p>{{ $groupName }} · {{ $netCallsign }} Net</p>
  </div>
  <div class="body">

    @if($isFallback)
    <p>Hello,</p>
    <p>An early handover has been requested on the <strong>{{ $netCallsign }}</strong> net and there is <strong>no next scheduled controller</strong>. You are receiving this as the group fallback contact.</p>
    @else
    <p>Hello,</p>
    <p>You are the <strong>next scheduled Net Controller</strong> for the <strong>{{ $netCallsign }}</strong> net. The current controller has requested an <strong>early handover</strong>.</p>
    @endif

    <div class="alert-box">
      <div class="alert-title">🚨 Early Handover Requested</div>
      <div class="alert-sub">The current controller needs to hand over earlier than scheduled</div>
    </div>

    <div class="info-grid">
      <div class="info-item">
        <label>Requesting Controller</label>
        <span style="font-family:monospace;">{{ $requesterCallsign }}</span>
      </div>
      <div class="info-item">
        <label>Their Name</label>
        <span>{{ $requesterName }}</span>
      </div>
      <div class="info-item">
        <label>Net Callsign</label>
        <span style="font-family:monospace;">{{ $netCallsign }}</span>
      </div>
      <div class="info-item">
        <label>Frequency</label>
        <span>{{ $frequency }} MHz</span>
      </div>
    </div>

    <div class="slot-box">
      <div class="slot-label">Their Current Slot</div>
      <div class="slot-time">{{ $requesterSlotFrom }} – {{ $requesterSlotTo }}</div>
      <div class="slot-sub">Click accept to take over immediately</div>
    </div>

    <p style="font-size:.88rem;color:#475569;">
      If you are able to take over as Net Controller, click the button below.
      This will end the current controller's session and update the net schedule.
    </p>

    <div style="text-align:center;">
      <a href="{{ $acceptUrl }}" class="btn">✅ Accept Handover</a>
    </div>

    <p style="font-size:.78rem;color:#94a3b8;margin-top:1rem;">
      This link is single-use. If you cannot take over, please ignore this email and contact the group administrator directly.
      This notification was sent automatically by ROCK.
    </p>
  </div>
  <div class="footer">{{ $groupName }} · ROCK · <a href="{{ config('app.url') }}" style="color:#C8102E;">{{ config('app.url') }}</a></div>
</div>
</body>
</html>
