<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;color:#1e293b;}
.wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.08);}
.header{background:linear-gradient(135deg,#003366,#001a33);padding:2rem;text-align:center;}
.header h1{color:#fff;margin:0;font-size:1.4rem;font-weight:900;letter-spacing:.02em;}
.header p{color:rgba(255,255,255,.55);margin:.4rem 0 0;font-size:.85rem;}
.body{padding:2rem;}
.callsign-badge{display:inline-block;font-family:monospace;font-size:1.5rem;font-weight:900;color:#003366;background:#f0f4ff;padding:.5rem 1.2rem;border-radius:10px;border:2px solid #c7d7ff;margin:.5rem 0 1.25rem;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin:1.25rem 0;background:#f8fafc;border-radius:10px;padding:1rem;border:1px solid #e2e8f0;}
.info-item label{display:block;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.2rem;}
.info-item span{font-size:.95rem;font-weight:700;color:#1e293b;}
.slot-box{background:linear-gradient(135deg,#003366,#004080);color:#fff;border-radius:10px;padding:1rem 1.25rem;margin:1.25rem 0;text-align:center;}
.slot-box .slot-label{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);}
.slot-box .slot-time{font-size:1.6rem;font-weight:900;font-family:monospace;margin:.2rem 0;}
.slot-box .slot-sub{font-size:.78rem;color:rgba(255,255,255,.65);}
.announce{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:.85rem 1rem;margin:1rem 0;font-size:.85rem;color:#9a3412;}
.btn{display:inline-block;background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;text-decoration:none;padding:.75rem 2rem;border-radius:999px;font-weight:800;font-size:.95rem;margin:1.25rem 0;}
.footer{background:#f8fafc;padding:.85rem 2rem;font-size:.72rem;color:#94a3b8;text-align:center;border-top:1px solid #e5e7eb;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>{{ $groupName }}</h1>
    <p>Net Controller Scheduling Notification</p>
  </div>
  <div class="body">
    <p>Hello <strong>{{ $controllerName }}</strong>,</p>
    <p>You have been scheduled as <strong>Net Controller</strong> for:</p>

    <div style="text-align:center;">
      <div class="callsign-badge">{{ $netCallsign }}</div>
    </div>

    <div class="info-grid">
      <div class="info-item">
        <label>Net Name</label>
        <span>{{ $netName ?: '—' }}</span>
      </div>
      <div class="info-item">
        <label>Frequency</label>
        <span>{{ $frequency ?: '—' }}</span>
      </div>
      <div class="info-item">
        <label>Your Callsign</label>
        <span style="font-family:monospace;">{{ $controllerCallsign }}</span>
      </div>
      <div class="info-item">
        <label>Role</label>
        <span>Net Controller</span>
      </div>
    </div>

    <div class="slot-box">
      <div class="slot-label">Your Control Slot</div>
      <div class="slot-time">{{ $slotStart }} – {{ $slotEnd }}</div>
      <div class="slot-sub">Please be ready on frequency before your slot starts</div>
    </div>

    @if($description)
    <p style="font-size:.88rem;color:#475569;">{{ $description }}</p>
    @endif

    @if($announcement)
    <div class="announce">
      <strong>📢 Announcement:</strong> {{ $announcement }}
    </div>
    @endif

    @if($netUrl)
    <div style="text-align:center;">
      <div style="background:#f0f4ff;border:1px solid #c7d7ff;border-radius:10px;padding:1rem;margin:1rem 0;text-align:center;">
      <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#6366f1;margin-bottom:.35rem;">Your Net Control Portal</div>
      <a href="{{ $netUrl }}" style="font-family:monospace;font-size:.95rem;font-weight:900;color:#003366;text-decoration:none;">{{ $netUrl }}</a>
      <div style="font-size:.72rem;color:#64748b;margin-top:.35rem;">Log in and visit this URL during your slot</div>
    </div>
    <a href="{{ $netUrl }}" class="btn">Open Net Control Portal →</a>
    </div>
    @endif

    <p style="font-size:.82rem;color:#64748b;margin-top:1rem;">
      This notification was sent automatically by RAYNET-OS when your callsign was assigned as Net Controller.
      If you believe this was sent in error, please contact your group administrator.
    </p>
  </div>
  <div class="footer">{{ $groupName }} · RAYNET-OS · <a href="{{ $netUrl ?? config('app.url') }}" style="color:#C8102E;">{{ config('app.url') }}</a></div>
</div>
</body>
</html>
