<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Response Recorded</title>
<style>*{box-sizing:border-box;margin:0;padding:0;}body{background:#0d1b2e;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Arial,sans-serif;}
.card{background:#fff;border-radius:12px;padding:2.5rem;text-align:center;max-width:360px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.4);}
.icon{font-size:3rem;margin-bottom:1rem;}
.title{font-size:1.3rem;font-weight:bold;color:#001f40;margin-bottom:.5rem;}
.sub{font-size:13px;color:#6b7f96;}</style></head>
<body><div class="card">
<div class="icon">{{ $response === 'available' ? '✅' : '❌' }}</div>
<div class="title">{{ $response === 'available' ? 'Response Recorded' : 'Unavailability Noted' }}</div>
<div class="sub">{{ $response === 'available' ? 'Thank you — your availability has been recorded. The Group Controller has been notified.' : 'Your unavailability has been recorded. Thank you for letting us know.' }}</div>
</div></body></html>
