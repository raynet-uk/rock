<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;}
.wrap{max-width:600px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.08);}
.header{background:linear-gradient(135deg,#003366,#001a33);padding:2rem;text-align:center;}
.header h1{color:#fff;margin:0;font-size:1.5rem;letter-spacing:.02em;}
.header p{color:rgba(255,255,255,.6);margin:.5rem 0 0;font-size:.9rem;}
.body{padding:2rem;}
.callsign{display:inline-block;font-family:monospace;font-size:1.4rem;font-weight:900;color:#003366;background:#f0f4ff;padding:.4rem 1rem;border-radius:8px;border:2px solid #003366;margin:.5rem 0;}
.btn{display:inline-block;background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;text-decoration:none;padding:.75rem 2rem;border-radius:999px;font-weight:800;font-size:1rem;margin:1.5rem 0;}
.footer{background:#f8fafc;padding:1rem 2rem;font-size:.75rem;color:#94a3b8;text-align:center;border-top:1px solid #e5e7eb;}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h1>{{ $groupName }}</h1>
    <p>Radio Amateur Emergency Network</p>
  </div>
  <div class="body">
    <p>Hello <strong>{{ $name }}</strong>,</p>
    <p>You were logged on a <strong>{{ $groupName }}</strong> net as:</p>
    <div style="text-align:center;margin:1rem 0;"><span class="callsign">{{ $callsign }}</span></div>
    <p>We'd love for you to join our RAYNET group online. As a member you'll get access to:</p>
    <ul style="color:#334155;line-height:1.8;">
      <li>Net schedules and announcements</li>
      <li>Event callouts and volunteering</li>
      <li>Training resources and exercise logs</li>
      <li>Member directory and contact tools</li>
    </ul>
    <div style="text-align:center;">
      <a href="{{ $inviteUrl }}" class="btn">Join {{ $groupName }} →</a>
    </div>
    <p style="font-size:.85rem;color:#64748b;">
      This invitation was sent by {{ $adminName ?? $groupName }} after your participation in one of our nets.
      If you believe this was sent in error, please ignore this email.
    </p>
  </div>
  <div class="footer">
    {{ $groupName }} · <a href="{{ $inviteUrl }}" style="color:#C8102E;">{{ $inviteUrl }}</a>
  </div>
</div>
</body>
</html>
