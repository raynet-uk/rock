<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Handover Accepted</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.card{background:#fff;border-radius:16px;padding:2.5rem;text-align:center;max-width:440px;box-shadow:0 4px 24px rgba(0,0,0,.1);}
.icon{font-size:3rem;margin-bottom:1rem;}
h1{color:#166534;font-size:1.4rem;margin:0 0 .5rem;}
p{color:#475569;font-size:.9rem;}
.cs{font-family:monospace;font-weight:900;font-size:1.2rem;color:#003366;}
</style>
</head>
<body>
<div class="card">
  <div class="icon">✅</div>
  <h1>Handover Accepted</h1>
  <p>You have accepted the early handover from <span class="cs">{{ $requesterCallsign }}</span>.</p>
  <p>Their slot has been ended at <strong>{{ $nowTime }}</strong>. Please QSY to frequency and take over net control.</p>
  <p style="font-size:.78rem;color:#94a3b8;margin-top:1.5rem;">{{ $groupName }} · RAYNET-OS</p>
</div>
</body>
</html>
