<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Remote Access Review</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;background:#0d1b2e;}
body{display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;padding:1rem;}
.bg{position:fixed;inset:0;background:radial-gradient(ellipse at 30% 20%,rgba(0,51,102,.4) 0%,transparent 60%),radial-gradient(ellipse at 70% 80%,rgba(200,16,46,.15) 0%,transparent 60%);pointer-events:none;}
.wrap{position:relative;z-index:1;width:100%;max-width:560px;}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;backdrop-filter:blur(20px);box-shadow:0 24px 64px rgba(0,0,0,.5);}
.top-bar{height:3px;background:linear-gradient(90deg,#003366,#C8102E,#003366);}
.card-head{padding:1.75rem 1.75rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.06);}
.head-row{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.head-icon{width:48px;height:48px;background:rgba(200,16,46,.15);border:1.5px solid rgba(200,16,46,.3);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.head-title{font-size:1.15rem;font-weight:bold;color:#fff;margin-bottom:.2rem;}
.head-sub{font-size:12px;color:rgba(255,255,255,.4);}
.target{display:flex;align-items:center;gap:.75rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.85rem 1rem;}
.target-icon{width:36px;height:36px;background:rgba(0,51,102,.4);border:1px solid rgba(0,102,204,.3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.target-name{font-size:14px;font-weight:bold;color:#fff;}
.target-url{font-size:11px;color:rgba(255,255,255,.4);font-family:monospace;margin-top:.1rem;}
.chips{display:flex;gap:.6rem;flex-wrap:wrap;padding:1rem 1.75rem;border-bottom:1px solid rgba(255,255,255,.06);}
.chip{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:.5rem .85rem;}
.chip-label{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3);margin-bottom:.15rem;}
.chip-val{font-size:13px;font-weight:bold;color:#fff;font-family:monospace;}
.chip-val.green{color:#7effa0;}
.chip-val.amber{color:#fde047;}
.info-wrap{padding:1rem 1.75rem;border-bottom:1px solid rgba(255,255,255,.06);max-height:220px;overflow-y:auto;}
.info-wrap::-webkit-scrollbar{width:3px;}
.info-wrap::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:999px;}
.info-section{margin-bottom:.85rem;}
.info-section:last-child{margin-bottom:0;}
.info-title{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.25);margin-bottom:.4rem;}
.info-grid{display:grid;grid-template-columns:140px 1fr;gap:2px;}
.info-key{font-size:11px;color:rgba(255,255,255,.35);padding:.25rem .5rem .25rem 0;}
.info-val{font-size:11px;color:rgba(255,255,255,.7);font-family:monospace;padding:.25rem 0;word-break:break-all;}
.info-val.sensitive{color:#ffaa44;}
.warning-bar{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1.75rem;background:rgba(245,158,11,.06);border-bottom:1px solid rgba(245,158,11,.15);}
.warning-text{font-size:11px;color:rgba(245,158,11,.85);line-height:1.5;}
.actions{display:grid;grid-template-columns:1fr auto auto;gap:.6rem;padding:1.25rem 1.75rem;}
.btn-confirm{padding:.75rem 1rem;background:linear-gradient(135deg,#1a6b3c,#155730);color:#7effa0;font-size:13px;font-weight:bold;border:1px solid rgba(126,255,160,.2);border-radius:8px;cursor:pointer;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:all .15s;box-shadow:0 2px 12px rgba(26,107,60,.3);}
.btn-confirm:hover{background:linear-gradient(135deg,#1f8049,#1a6b3c);}
.btn-copy{padding:.75rem 1rem;background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);font-size:12px;font-weight:bold;border:1px solid rgba(255,255,255,.08);border-radius:8px;cursor:pointer;transition:all .15s;}
.btn-copy:hover{background:rgba(255,255,255,.1);color:#fff;}
.btn-cancel{padding:.75rem 1rem;background:rgba(200,16,46,.08);color:rgba(200,16,46,.7);font-size:12px;font-weight:bold;border:1px solid rgba(200,16,46,.15);border-radius:8px;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:all .15s;}
.btn-cancel:hover{background:rgba(200,16,46,.15);color:#C8102E;}
.card-foot{padding:.75rem 1.75rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,.04);}
.foot-text{font-size:10px;color:rgba(255,255,255,.2);}
.foot-badge{font-size:9px;font-weight:bold;letter-spacing:.1em;color:rgba(255,255,255,.2);text-transform:uppercase;}
</style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
<div class="card">
    <div class="top-bar"></div>
    <div class="card-head">
        <div class="head-row">
            <div class="head-icon">🔐</div>
            <div>
                <div class="head-title">Remote Access Review</div>
                <div class="head-sub">Confirm details before connecting to the remote site as super admin.</div>
            </div>
        </div>
        <div class="target">
            <div class="target-icon">🌐</div>
            <div>
                <div class="target-name">{{ \App\Models\Setting::get('group_name', config('app.name')) }}</div>
                <div class="target-url">{{ config('app.url') }}</div>
            </div>
        </div>
    </div>
    <div class="chips">
        <div class="chip">
            <div class="chip-label">Access Code</div>
            <div class="chip-val amber">{{ $token->code }}</div>
        </div>
        <div class="chip">
            <div class="chip-label">Expires</div>
            <div class="chip-val">{{ $token->expires_at->format('j M H:i') }}</div>
        </div>
        <div class="chip">
            <div class="chip-label">Time Remaining</div>
            <div class="chip-val green">{{ floor(now()->diffInMinutes($token->expires_at) / 60) }}h {{ now()->diffInMinutes($token->expires_at) % 60 }}m</div>
        </div>
        <div class="chip">
            <div class="chip-label">Requested By</div>
            <div class="chip-val" style="font-size:11px;">{{ $from ?: 'RAYNET Support' }}</div>
        </div>
    </div>
    <div class="info-wrap">
        @foreach($sysInfo as $section => $rows)
        <div class="info-section">
            <div class="info-title">{{ $section }}</div>
            <div class="info-grid">
                @foreach($rows as $key => $value)
                @php $sensitive = collect(['password','secret','key','token','pass'])->contains(fn($s) => str_contains(strtolower($key), $s)); @endphp
                <div class="info-key">{{ $key }}</div>
                <div class="info-val {{ $sensitive ? 'sensitive' : '' }}">{{ $value }}</div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    <div class="warning-bar">
        <div>⚠</div>
        <div class="warning-text">You will be logged in as super admin on this site. The session is time-limited and the remote group can revoke it at any time via Admin → Remote Help.</div>
    </div>
    <div class="actions">
        <a href="{{ $confirmUrl }}" class="btn-confirm">✓ Confirm & Connect</a>
        <button onclick="copyAll()" class="btn-copy">📋</button>
        <a href="/" class="btn-cancel">✕</a>
    </div>
    <div class="card-foot">
        <span class="foot-text">Session expires {{ $token->expires_at->format('j M Y \a\t H:i') }}</span>
        <span class="foot-badge">ROCK · Remote Support</span>
    </div>
</div>
</div>
<script>
function copyAll() {
    let text = 'ROCK Remote Access\n\n';
    document.querySelectorAll('.info-section').forEach(s => {
        text += s.querySelector('.info-title').textContent + '\n';
        const keys = s.querySelectorAll('.info-key');
        const vals = s.querySelectorAll('.info-val');
        keys.forEach((k,i) => text += '  ' + k.textContent.trim() + ': ' + vals[i].textContent.trim() + '\n');
        text += '\n';
    });
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('.btn-copy');
        btn.textContent = '✓';
        setTimeout(() => btn.textContent = '📋', 1500);
    });
}
</script>
</body>
</html>
