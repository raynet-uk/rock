<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Remote Access Review</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0a1628;color:#c8d8e8;font-family:monospace;font-size:13px;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;}
.wrap{max-width:720px;width:100%;}
.header{background:#001f40;border:1px solid #1a3a5c;border-top:3px solid #C8102E;padding:1rem 1.5rem;margin-bottom:1rem;}
.header-title{color:#7effa0;font-size:15px;font-weight:bold;margin-bottom:.25rem;}
.header-sub{color:#6b8fa8;font-size:11px;}
.card{background:#001f40;border:1px solid #1a3a5c;margin-bottom:.75rem;overflow:hidden;}
.card-head{background:#0d2540;padding:.5rem 1rem;color:#ffd700;font-size:10px;text-transform:uppercase;letter-spacing:.1em;border-bottom:1px solid #1a3a5c;}
.card-body{padding:.75rem 1rem;}
.row{display:flex;gap:1rem;padding:.2rem 0;border-bottom:1px solid rgba(255,255,255,.04);}
.row:last-child{border-bottom:none;}
.key{color:#6b8fa8;min-width:180px;flex-shrink:0;}
.val{color:#7effa0;word-break:break-all;}
.val.sensitive{color:#ffaa44;}
.actions{display:flex;gap:.75rem;margin-top:1rem;}
.btn{padding:.65rem 1.5rem;font-family:monospace;font-size:13px;font-weight:bold;cursor:pointer;border:none;border-radius:3px;text-decoration:none;display:inline-block;}
.btn-go{background:#1a6b3c;color:#7effa0;}
.btn-go:hover{background:#1f8049;}
.btn-cancel{background:#1a2a3a;color:#6b8fa8;border:1px solid #2a4a6a;}
.btn-copy{background:#003366;color:#7effa0;border:1px solid #0066cc;padding:.4rem 1rem;font-size:11px;}
.warning{background:#2a1a0a;border:1px solid #8a5500;border-left:3px solid #ffaa44;padding:.65rem 1rem;color:#ffaa44;font-size:12px;margin-bottom:1rem;}
.expiry{color:#6b8fa8;font-size:11px;margin-top:.5rem;}
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="header-title">🔐 Remote Access Review</div>
        <div class="header-sub">
            Code: <strong style="color:#ffd700;">{{ $token->code }}</strong> &nbsp;·&nbsp;
            From: {{ $from ?: 'RAYNET Support' }} &nbsp;·&nbsp;
            Expires: {{ $token->expires_at->format('j M Y H:i') }}
        </div>
    </div>

    <div class="warning">
        ⚠ Review the system information below. The support technician will have full admin access to this site until the code expires or is revoked.
    </div>

    @foreach($sysInfo as $section => $rows)
    <div class="card">
        <div class="card-head">{{ $section }}</div>
        <div class="card-body">
            @foreach($rows as $key => $value)
            @php $sensitive = collect(['password','secret','key','token','pass'])->contains(fn($s) => str_contains(strtolower($key), $s)); @endphp
            <div class="row">
                <span class="key">{{ $key }}</span>
                <span class="val {{ $sensitive ? 'sensitive' : '' }}">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="actions">
        <a href="{{ $confirmUrl }}" class="btn btn-go">✓ Confirm & Grant Access</a>
        <a href="/" class="btn btn-cancel">✕ Cancel</a>
        <button onclick="copyAll()" class="btn btn-copy">📋 Copy All Info</button>
    </div>
    <div class="expiry">Access will automatically expire at {{ $token->expires_at->format('H:i') }} on {{ $token->expires_at->format('j M Y') }}. You can revoke it early from Admin → Remote Help.</div>
</div>
<script>
function copyAll() {
    const rows = document.querySelectorAll('.row');
    let text = '';
    document.querySelectorAll('.card').forEach(card => {
        text += '\n=== ' + card.querySelector('.card-head').textContent + ' ===\n';
        card.querySelectorAll('.row').forEach(row => {
            text += row.querySelector('.key').textContent.trim() + ': ' + row.querySelector('.val').textContent.trim() + '\n';
        });
    });
    navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard'));
}
</script>
</body>
</html>
