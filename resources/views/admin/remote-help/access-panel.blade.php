@extends('layouts.admin')
@section('title', 'Remote Access Panel')
@section('content')
<style>
*{box-sizing:border-box;}
.rap-wrap{max-width:780px;margin:0 auto;padding:2rem 1.5rem 5rem;}

/* Hero */
.rap-hero{background:linear-gradient(135deg,#001f40 0%,#003366 60%,#0a1f3a 100%);border-radius:12px;padding:2rem 2.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden;display:flex;align-items:center;gap:1.5rem;}
.rap-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");pointer-events:none;}
.rap-hero-icon{position:relative;z-index:1;width:56px;height:56px;background:rgba(200,16,46,.2);border:2px solid rgba(200,16,46,.4);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;}
.rap-hero-text{position:relative;z-index:1;}
.rap-hero-eyebrow{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.3rem;}
.rap-hero-title{font-size:1.5rem;font-weight:bold;color:#fff;margin-bottom:.25rem;}
.rap-hero-sub{font-size:.875rem;color:rgba(255,255,255,.5);}

/* Connection card */
.rap-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;margin-bottom:1.25rem;box-shadow:0 2px 8px rgba(0,51,102,.06);}
.rap-card-head{padding:.85rem 1.5rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;display:flex;align-items:center;gap:.5rem;}
.rap-card-head-icon{font-size:1rem;}
.rap-card-head-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;}
.rap-card-body{padding:1.5rem;}
.rap-warning{background:linear-gradient(135deg,#fffbeb,#fff8e1);border:1px solid #ffe082;border-left:4px solid #f9a825;border-radius:0 6px 6px 0;padding:.75rem 1rem;font-size:12px;color:#7a5800;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.5rem;}

/* Fields */
.rap-field{margin-bottom:1.1rem;}
.rap-label{display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;margin-bottom:.4rem;}
.rap-input{width:100%;padding:.6rem .85rem;border:1px solid #dde2e8;border-radius:5px;font-size:13px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;}
.rap-input:focus{border-color:#003366;box-shadow:0 0 0 3px rgba(0,51,102,.08);}
.rap-input-code{font-family:monospace;font-size:1.3rem;letter-spacing:.25em;text-transform:uppercase;text-align:center;}

/* Connect button */
.rap-connect-btn{width:100%;padding:.8rem;background:linear-gradient(135deg,#C8102E,#a00d25);color:#fff;font-size:14px;font-weight:bold;border:none;border-radius:6px;cursor:pointer;letter-spacing:.05em;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:.5rem;box-shadow:0 2px 8px rgba(200,16,46,.3);}
.rap-connect-btn:hover{background:linear-gradient(135deg,#a00d25,#800a1e);box-shadow:0 4px 12px rgba(200,16,46,.4);transform:translateY(-1px);}

/* Pending sessions */
.rap-sessions-head{display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;}
.rap-sessions-label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:#6b7f96;}
.rap-live-dot{width:7px;height:7px;border-radius:50%;background:#1a6b3c;display:inline-block;animation:rap-pulse 2s infinite;}
.rap-count-badge{background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;font-size:10px;font-weight:bold;padding:.15rem .5rem;border-radius:999px;}
.rap-empty{font-size:13px;color:#9aa3ae;padding:1.25rem;background:#f8f9fb;border:1px dashed #dde2e8;border-radius:8px;text-align:center;line-height:1.6;}

/* Session cards */
.rap-session{background:#fff;border:1px solid #dde2e8;border-radius:8px;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;transition:all .15s;margin-bottom:.6rem;border-left:3px solid #1a6b3c;}
.rap-session:hover{box-shadow:0 2px 12px rgba(0,51,102,.1);border-color:#003366;border-left-color:#C8102E;}
.rap-session-left{display:flex;flex-direction:column;gap:.2rem;}
.rap-session-name{font-size:14px;font-weight:bold;color:#001f40;}
.rap-session-url{font-size:11px;color:#6b7f96;font-family:monospace;}
.rap-session-meta{font-size:10px;color:#9aa3ae;display:flex;align-items:center;gap:.3rem;}
.rap-session-right{display:flex;align-items:center;gap:.5rem;flex-shrink:0;}
.rap-session-connect{background:#003366;color:#fff;font-size:11px;font-weight:bold;padding:.4rem .9rem;border-radius:5px;cursor:pointer;border:none;font-family:inherit;transition:all .15s;display:flex;align-items:center;gap:.3rem;}
.rap-session-connect:hover{background:#002244;}
.rap-session-dismiss{background:none;border:none;color:#dde2e8;cursor:pointer;font-size:1.1rem;padding:.2rem .4rem;border-radius:4px;transition:all .15s;}
.rap-session-dismiss:hover{background:#fef2f2;color:#C8102E;}

@keyframes rap-pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(.85);}}
</style>

<div class="rap-wrap">

    {{-- Hero --}}
    <div class="rap-hero">
        <div class="rap-hero-icon">🔐</div>
        <div class="rap-hero-text">
            <div class="rap-hero-eyebrow">ROCK · Remote Support</div>
            <div class="rap-hero-title">Remote Site Access Panel</div>
            <div class="rap-hero-sub">Connect to a ROCK site requesting technical support. Sites appear below automatically when a code is generated.</div>
        </div>
    </div>

    {{-- Connection form --}}
    <div class="rap-card">
        <div class="rap-card-head">
            <span class="rap-card-head-icon">🌐</span>
            <span class="rap-card-head-title">Connect to Remote Site</span>
        </div>
        <div class="rap-card-body">
            <div class="rap-warning">
                ⚠ <span>This grants super admin access to the remote site. Only use codes shared directly by the group controller — never connect to sites you don't recognise.</span>
            </div>
            <form method="POST" action="{{ route('admin.remote-help.access') }}">
                @csrf
                <div class="rap-field">
                    <label class="rap-label">Site URL</label>
                    <input type="url" name="site_url" class="rap-input" value="https://"
                           placeholder="https://raynet-grampian.net" required>
                </div>
                <div class="rap-field">
                    <label class="rap-label">Support Code</label>
                    <input type="text" name="code" class="rap-input rap-input-code"
                           placeholder="XXXX-XXXX" required
                           oninput="this.value=this.value.toUpperCase()">
                    <div style="font-size:11px;color:#9aa3ae;margin-top:.4rem;">Provided by the group requesting support. Click a pending session below to auto-fill the URL.</div>
                </div>
                <button type="submit" class="rap-connect-btn">
                    🔗 Connect to Remote Site
                </button>
            </form>
        </div>
    </div>

    {{-- Pending sessions --}}
    <div class="rap-sessions-head">
        <span class="rap-live-dot"></span>
        <span class="rap-sessions-label">Sites Requesting Support</span>
        <span class="rap-count-badge" id="pending-count">0</span>
        <span style="margin-left:auto;font-size:11px;color:#9aa3ae;">Updates every 10s</span>
    </div>

    <div id="pending-sessions">
        <div id="pending-empty" class="rap-empty">
            📡 No sites are currently requesting support.<br>
            <span style="font-size:12px;">When a ROCK site generates a support code it will appear here automatically.</span>
        </div>
    </div>

</div>

<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content || '';

function loadPendingSessions() {
    fetch('{{ route("admin.remote-help.pending-sessions") }}', {
        headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        const sessions = data.sessions || [];
        const container = document.getElementById('pending-sessions');
        const empty = document.getElementById('pending-empty');
        const count = document.getElementById('pending-count');

        count.textContent = sessions.length;
        count.style.background = sessions.length > 0 ? '#fff3cd' : '#eef7f2';
        count.style.borderColor = sessions.length > 0 ? '#fde68a' : '#b8ddc9';
        count.style.color = sessions.length > 0 ? '#92400e' : '#1a6b3c';

        container.querySelectorAll('.rap-session').forEach(c => c.remove());

        if (sessions.length === 0) {
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
            sessions.forEach(s => {
                const expiresIn = Math.max(0, Math.round((new Date(s.expires_at) - Date.now()) / 60000));
                const card = document.createElement('div');
                card.className = 'rap-session';
                card.id = 'session-' + s.id;
                card.innerHTML = `
                    <div class="rap-session-left">
                        <div class="rap-session-name">${s.group_name || s.site_name || 'Unknown Group'}</div>
                        <div class="rap-session-url">${s.site_url}</div>
                        <div class="rap-session-meta">
                            <span style="width:5px;height:5px;border-radius:50%;background:#1a6b3c;display:inline-block;"></span>
                            Code active · expires in ~${expiresIn} min
                        </div>
                    </div>
                    <div class="rap-session-right">
                        <button class="rap-session-connect" onclick="prefillSession('${s.site_url}','${s.code}',${s.id})">
                            🔗 Connect
                        </button>
                        <button class="rap-session-dismiss" onclick="dismissSession(${s.id})" title="Dismiss">✕</button>
                    </div>
                `;
                container.appendChild(card);
            });
        }
    })
    .catch(() => {});
}

function prefillSession(url, code, id) {
    document.querySelector('input[name=site_url]').value = url;
    document.querySelector('input[name=code]').value = '';
    document.querySelector('input[name=code]').focus();
    const card = document.querySelector('.rap-card');
    card.style.boxShadow = '0 0 0 3px rgba(26,107,60,.3)';
    card.style.borderColor = '#1a6b3c';
    setTimeout(() => { card.style.boxShadow = ''; card.style.borderColor = ''; }, 1500);
    card.scrollIntoView({behavior:'smooth'});
}

function dismissSession(id) {
    fetch('{{ route("admin.remote-help.dismiss-session") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify({id})
    }).then(() => {
        document.getElementById('session-' + id)?.remove();
        loadPendingSessions();
    });
}

loadPendingSessions();
setInterval(loadPendingSessions, 10000);
</script>
@endsection
