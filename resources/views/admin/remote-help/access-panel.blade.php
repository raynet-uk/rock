@extends('layouts.admin')
@section('title', 'Remote Access Panel — Admin')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f2f2;--grey-mid:#dde2e8;--text:#001f40;--text-muted:#6b7f96;}
*{box-sizing:border-box;}
body{background:var(--grey);font-family:Arial,sans-serif;font-size:14px;color:var(--text);}
.rn-header{background:var(--navy);border-bottom:4px solid var(--red);padding:0 1.5rem;}
.rn-header-inner{max-width:700px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;}
.rn-logo{background:var(--red);padding:4px 10px;font-size:11px;font-weight:bold;color:#fff;letter-spacing:.1em;}
.rn-back{color:rgba(255,255,255,.8);text-decoration:none;font-size:12px;border:1px solid rgba(255,255,255,.25);padding:.3rem .8rem;}
.wrap{max-width:700px;margin:2rem auto;padding:0 1.5rem 4rem;}
.card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--red);padding:1.5rem;}
.page-title{font-size:22px;font-weight:bold;color:var(--navy);margin-bottom:.25rem;}
.page-sub{font-size:13px;color:var(--text-muted);margin-bottom:1.5rem;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.35rem;}
.field input{width:100%;padding:.5rem .75rem;border:1px solid var(--grey-mid);font-size:13px;outline:none;}
.btn{padding:.65rem 1.5rem;font-size:13px;font-weight:bold;cursor:pointer;border:none;background:var(--red);color:#fff;width:100%;letter-spacing:.05em;}
.warning{background:#fff8e1;border:1px solid #ffe082;border-left:3px solid #f9a825;padding:.75rem 1rem;font-size:12px;color:#7a5800;margin-bottom:1rem;}
</style>
<div class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-logo">RAYNET — Liverpool Support</div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Admin</a>
    </div>
</div>
<div class="wrap">
    <div class="page-title">🔐 Remote Site Access</div>
    <div class="page-sub">Enter the site URL and support code provided by the group requesting help.</div>
    <div class="card">
        <div class="warning">⚠ This will log you in as the super admin on the remote site. Only use codes shared directly by the group controller.</div>
        <form method="POST" action="{{ route('admin.remote-help.access') }}">
            @csrf
            <div class="field">
                <label>Site URL</label>
                <input type="url" name="site_url" value="https://" placeholder="https://raynet-grampian.net" required>
            </div>
            <div class="field">
                <label>Support Code</label>
                <input type="text" name="code" placeholder="XXXX-XXXX" required
                       style="font-family:monospace;font-size:1.2rem;letter-spacing:.2em;text-transform:uppercase"
                       oninput="this.value=this.value.toUpperCase()">
            </div>
            <button type="submit" class="btn">Connect to Remote Site →</button>
        </form>
    </div>

    {{-- Pending Sessions --}}
    <div style="margin-top:1.5rem;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:#6b7f96;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;">
            <span style="width:6px;height:6px;border-radius:50%;background:#7effa0;display:inline-block;animation:pulse 2s infinite;"></span>
            Sites Requesting Support
            <span id="pending-count" style="background:rgba(126,255,160,.15);border:1px solid rgba(126,255,160,.25);color:#7effa0;font-size:9px;padding:.1rem .4rem;border-radius:999px;">0</span>
        </div>
        <div id="pending-sessions" style="display:flex;flex-direction:column;gap:.5rem;">
            <div id="pending-empty" style="font-size:13px;color:rgba(255,255,255,.35);font-style:italic;padding:.75rem 1rem;background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.1);border-radius:6px;text-align:center;">
                📡 No sites are currently requesting support — when a ROCK site generates a code it will appear here automatically.
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.3;}}
.session-card{background:#fff;border:1px solid #dde2e8;border-radius:6px;padding:.85rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;transition:all .15s;}
.session-card:hover{background:#f8f9fb;border-color:#003366;}
.session-card-left{display:flex;flex-direction:column;gap:.2rem;}
.session-card-name{font-size:13px;font-weight:bold;color:#001f40;}
.session-card-url{font-size:11px;color:#6b7f96;font-family:monospace;}
.session-card-meta{font-size:10px;color:#9aa3ae;}
.session-card-right{display:flex;align-items:center;gap:.5rem;}
.session-card-code{font-family:monospace;font-size:13px;font-weight:bold;color:#ffd700;background:rgba(255,215,0,.1);border:1px solid rgba(255,215,0,.2);padding:.25rem .6rem;border-radius:4px;}
.session-dismiss{background:none;border:none;color:#9aa3ae;cursor:pointer;font-size:1rem;padding:.2rem;line-height:1;}
.session-dismiss:hover{color:#C8102E;}
.session-connect-btn{background:#1a6b3c;border:1px solid rgba(126,255,160,.3);color:#7effa0;font-size:11px;font-weight:bold;padding:.3rem .7rem;border-radius:4px;cursor:pointer;font-family:inherit;transition:all .15s;}
.session-connect-btn:hover{background:#1f8049;}
</style>

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

        // Remove old cards
        container.querySelectorAll('.session-card').forEach(c => c.remove());

        if (sessions.length === 0) {
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
            sessions.forEach(s => {
                const card = document.createElement('div');
                card.className = 'session-card';
                card.id = 'session-' + s.id;
                const expiresIn = Math.max(0, Math.round((new Date(s.expires_at) - Date.now()) / 60000));
                card.innerHTML = `
                    <div class="session-card-left">
                        <div class="session-card-name">${s.group_name || s.site_name || 'Unknown Group'}</div>
                        <div class="session-card-url">${s.site_url}</div>
                        <div class="session-card-meta">⏱ Code expires in ~${expiresIn} min</div>
                    </div>
                    <div class="session-card-right">
                        <button class="session-connect-btn" onclick="prefillSession('${s.site_url}','${s.code}',${s.id})">🔗 Connect →</button>
                        <button class="session-dismiss" onclick="dismissSession(${s.id})" title="Dismiss">✕</button>
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
    document.querySelector('input[name=site_url]').scrollIntoView({behavior:'smooth'});
    document.querySelector('.card').style.boxShadow = '0 0 0 3px rgba(126,255,160,.4)';
    setTimeout(() => document.querySelector('.card').style.boxShadow = '', 1500);
}

function dismissSession(id) {
    fetch('{{ route("admin.remote-help.dismiss-session") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify({id})
    }).then(() => {
        const card = document.getElementById('session-' + id);
        if (card) card.remove();
        loadPendingSessions();
    });
}

// Poll every 10 seconds
loadPendingSessions();
setInterval(loadPendingSessions, 10000);
</script>
@endsection
