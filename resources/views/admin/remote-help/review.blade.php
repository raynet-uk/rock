<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Incoming Support Request</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#f2f4f7;font-family:Arial,sans-serif;font-size:13px;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;}
.wrap{max-width:620px;width:100%;}

/* Header card */
.hero{background:linear-gradient(135deg,#001f40,#003366);border-radius:10px 10px 0 0;padding:1.5rem 1.75rem;display:flex;align-items:center;gap:1rem;}
.hero-icon{width:48px;height:48px;background:rgba(200,16,46,.25);border:2px solid rgba(200,16,46,.4);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;}
.hero-title{font-size:1.2rem;font-weight:bold;color:#fff;margin-bottom:.2rem;}
.hero-sub{font-size:12px;color:rgba(255,255,255,.5);}

/* Main card */
.card{background:#fff;border:1px solid #dde2e8;border-top:none;}
.card-body{padding:1.5rem;}

/* Who is connecting */
.who-box{background:#f8f9fb;border:1px solid #dde2e8;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.85rem;}
.who-avatar{width:40px;height:40px;background:#003366;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.who-name{font-size:14px;font-weight:bold;color:#001f40;}
.who-meta{font-size:11px;color:#6b7f96;margin-top:.1rem;}

/* Warning */
.warning{background:#fffbeb;border:1px solid #fde68a;border-left:3px solid #f59e0b;border-radius:0 6px 6px 0;padding:.75rem 1rem;font-size:12px;color:#92400e;margin-bottom:1.25rem;line-height:1.5;}

/* Info rows */
.info-section{margin-bottom:1rem;}
.info-section-title{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:#9aa3ae;margin-bottom:.5rem;padding-bottom:.35rem;border-bottom:1px solid #f0f1f3;}
.info-row{display:flex;gap:.75rem;padding:.3rem 0;font-size:12px;border-bottom:1px solid #f8f9fb;}
.info-row:last-child{border-bottom:none;}
.info-key{color:#6b7f96;min-width:140px;flex-shrink:0;}
.info-val{color:#001f40;font-family:monospace;word-break:break-all;}
.info-val.sensitive{color:#C8102E;}

/* Access details */
.access-meta{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;}
.access-chip{background:#f0f4f8;border:1px solid #dde2e8;border-radius:5px;padding:.4rem .75rem;font-size:11px;}
.access-chip strong{color:#003366;display:block;font-size:10px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.1rem;}

/* Actions */
.actions{display:flex;gap:.6rem;padding:1rem 1.5rem;background:#f8f9fb;border-top:1px solid #dde2e8;border-radius:0 0 10px 10px;}
.btn-confirm{flex:1;padding:.7rem;background:#1a6b3c;color:#fff;font-size:13px;font-weight:bold;border:none;border-radius:6px;cursor:pointer;text-decoration:none;text-align:center;transition:background .15s;}
.btn-confirm:hover{background:#155730;}
.btn-cancel{padding:.7rem 1.25rem;background:#fff;color:#6b7f96;font-size:13px;font-weight:bold;border:1px solid #dde2e8;border-radius:6px;cursor:pointer;text-decoration:none;text-align:center;transition:all .15s;}
.btn-cancel:hover{background:#fef2f2;color:#C8102E;border-color:#fca5a5;}
.btn-copy{padding:.7rem 1rem;background:#fff;color:#003366;font-size:12px;font-weight:bold;border:1px solid #dde2e8;border-radius:6px;cursor:pointer;transition:all .15s;}
.btn-copy:hover{background:#e8eef5;}
</style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="hero-icon">🔐</div>
        <div>
            <div class="hero-title">Remote Site Review</div>
            <div class="hero-sub">Review system information before connecting to the remote site.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Who is connecting --}}
            <div class="who-box">
                <div class="who-avatar">🌐</div>
                <div>
                    <div class="who-name">{{ \App\Models\Setting::get('group_name', config('app.name')) }}</div>
                    <div class="who-meta">You are about to connect to <strong>{{ config('app.url') }}</strong> as super admin.</div>
                </div>
            </div>

            {{-- Access details --}}
            <div class="access-meta">
                <div class="access-chip">
                    <strong>Code</strong>
                    <span style="font-family:monospace;color:#003366;font-weight:bold;">{{ $token->code }}</span>
                </div>
                <div class="access-chip">
                    <strong>Expires</strong>
                    {{ $token->expires_at->format('j M Y \a\t H:i') }}
                </div>
                <div class="access-chip">
                    <strong>Duration</strong>
                    ~{{ now()->diffInMinutes($token->expires_at) }} min remaining
                </div>
            </div>

            <div class="warning">
                ⚠ Once confirmed, you will be logged in as super admin on the remote site. The session is time-limited and can be revoked by the remote group at any time via their Admin → Remote Help page.
            </div>

            {{-- Compact system info --}}
            @foreach($sysInfo as $section => $rows)
            <div class="info-section">
                <div class="info-section-title">{{ $section }}</div>
                @foreach($rows as $key => $value)
                @php $sensitive = collect(['password','secret','key','token','pass'])->contains(fn($s) => str_contains(strtolower($key), $s)); @endphp
                <div class="info-row">
                    <span class="info-key">{{ $key }}</span>
                    <span class="info-val {{ $sensitive ? 'sensitive' : '' }}">{{ $value }}</span>
                </div>
                @endforeach
            </div>
            @endforeach

        </div>
    </div>

    <div class="actions">
        <a href="{{ $confirmUrl }}" class="btn-confirm">✓ Confirm & Connect</a>
        <button onclick="copyAll()" class="btn-copy">📋 Copy Info</button>
        <a href="/" class="btn-cancel">✕ Cancel</a>
    </div>
</div>
<script>
function copyAll() {
    let text = 'ROCK Remote Access Info\n========================\n';
    document.querySelectorAll('.info-section').forEach(section => {
        text += '\n' + section.querySelector('.info-section-title').textContent + '\n';
        section.querySelectorAll('.info-row').forEach(row => {
            text += row.querySelector('.info-key').textContent.trim() + ': ' + row.querySelector('.info-val').textContent.trim() + '\n';
        });
    });
    navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard'));
}
</script>
</body>
</html>
