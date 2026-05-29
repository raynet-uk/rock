@extends('layouts.admin')
@section('title', 'Remote Help — Admin')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--green:#1a6b3c;--green-bg:#eef7f2;--grey:#f2f2f2;--grey-mid:#dde2e8;--text:#001f40;--text-muted:#6b7f96;}
*{box-sizing:border-box;}
body{background:var(--grey);font-family:Arial,sans-serif;font-size:14px;color:var(--text);}
.rn-header{background:var(--navy);border-bottom:4px solid var(--red);padding:0 1.5rem;}
.rn-header-inner{max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;}
.rn-logo{background:var(--red);padding:4px 10px;font-size:11px;font-weight:bold;color:#fff;letter-spacing:.1em;}
.rn-back{color:rgba(255,255,255,.8);text-decoration:none;font-size:12px;border:1px solid rgba(255,255,255,.25);padding:.3rem .8rem;}
.wrap{max-width:900px;margin:2rem auto;padding:0 1.5rem 4rem;}
.page-title{font-size:22px;font-weight:bold;color:var(--navy);margin-bottom:.25rem;}
.page-sub{font-size:13px;color:var(--text-muted);margin-bottom:1.5rem;}
.card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1.5rem;margin-bottom:1.5rem;}
.card-title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);margin-bottom:1rem;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.35rem;}
.field select,.field input{width:100%;padding:.5rem .75rem;border:1px solid var(--grey-mid);font-size:13px;outline:none;}
.btn{padding:.55rem 1.2rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;border:1px solid;display:inline-block;}
.btn-navy{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-red{background:var(--red);border-color:var(--red);color:#fff;}
.code-box{background:#001f40;color:#7effa0;font-family:monospace;font-size:2rem;font-weight:bold;letter-spacing:.25em;padding:1.5rem;text-align:center;border-radius:4px;margin:1rem 0;}
.alert{padding:.75rem 1rem;margin-bottom:1rem;font-size:13px;font-weight:bold;}
.alert-green{background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);color:var(--green);}
.alert-red{background:#fdf0f2;border:1px solid #f5c6cc;border-left:3px solid var(--red);color:var(--red);}
.token-row{display:flex;align-items:center;justify-content:space-between;padding:.65rem .85rem;border:1px solid var(--grey-mid);margin-bottom:.5rem;background:#f9fafc;}
.token-code{font-family:monospace;font-size:1.1rem;font-weight:bold;color:var(--navy);letter-spacing:.15em;}
.token-meta{font-size:11px;color:var(--text-muted);}
.badge{font-size:10px;font-weight:bold;padding:2px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:.05em;}
.badge-green{background:#d4edda;color:#1a6b3c;}
.badge-red{background:#fdf0f2;color:var(--red);}
</style>
<div class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-logo">RAYNET</div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Admin</a>
    </div>
</div>
<div class="wrap">
    <div class="page-title">🛠 Remote Help</div>
    <div class="page-sub">Generate a temporary access code to allow technical support to log in and assist with technical issues.</div>

    @if(session('generated_code'))
    <div class="alert alert-green">
        ✓ Access code generated — valid until {{ session('generated_expires') }}
    </div>
    <div class="code-box">{{ session('generated_code') }}</div>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:1.5rem;">
        Share this code with RAYNET Liverpool support. It expires automatically and can be revoked below at any time.
    </p>
    @endif

    @if(session('status'))
    <div class="alert alert-red">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-title">Generate Access Code</div>
        <form method="POST" action="{{ route('admin.remote-help.generate') }}">
            @csrf
            <div class="field">
                <label>Access Duration</label>
                <select name="hours">
                    <option value="2">2 hours</option>
                    <option value="4" selected>4 hours</option>
                    <option value="8">8 hours</option>
                    <option value="24">24 hours</option>
                </select>
            </div>
            <button type="submit" class="btn btn-navy">Generate Code</button>
        </form>
    </div>

    @if($active->count())
    <div class="card">
        <div class="card-title">Active Codes</div>
        @foreach($active as $t)
        <div class="token-row">
            <div>
                <div class="token-code">{{ $t->code }}</div>
                <div class="token-meta">
                    Expires {{ $t->expires_at->format('j M Y H:i') }}
                    @if($t->accessed_at) · Last accessed {{ $t->accessed_at->diffForHumans() }} @endif
                </div>
            </div>
            <form method="POST" action="{{ route('admin.remote-help.revoke', $t) }}">
                @csrf
                <button type="submit" class="btn btn-red" onclick="return confirm('Revoke this code?')">✕ Revoke</button>
            </form>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- System Info Modal --}}
<div id="sysinfo-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#001f40;color:#c8d8e8;font-family:monospace;font-size:13px;border-radius:6px;max-width:680px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.5);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.1);">
            <div style="font-weight:bold;color:#7effa0;font-size:14px;">🖥 System Information</div>
            <button onclick="document.getElementById('sysinfo-modal').style.display='none'"
                    style="background:none;border:none;color:#6b8fa8;font-size:1.3rem;cursor:pointer;padding:0;">✕</button>
        </div>
        <div style="padding:1.25rem;" id="sysinfo-content">
            <div style="color:#6b8fa8;">Loading...</div>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid rgba(255,255,255,.1);display:flex;gap:.5rem;">
            <button onclick="copySystemInfo()" style="background:#003366;border:1px solid #0066cc;color:#7effa0;padding:.4rem 1rem;font-family:monospace;font-size:12px;cursor:pointer;border-radius:3px;">📋 Copy All</button>
            <button onclick="document.getElementById('sysinfo-modal').style.display='none'" style="background:none;border:1px solid rgba(255,255,255,.2);color:#6b8fa8;padding:.4rem 1rem;font-family:monospace;font-size:12px;cursor:pointer;border-radius:3px;">Close</button>
        </div>
    </div>
</div>

<div style="margin-top:1rem;text-align:right;">
    <button onclick="loadSystemInfo()" class="btn" style="background:#1a2a3a;border-color:#2a4a6a;color:#7effa0;font-family:monospace;">🖥 View System Info for Support</button>
</div>

<script>
function loadSystemInfo() {
    document.getElementById('sysinfo-modal').style.display = 'flex';
    fetch('/admin/remote-help/system-info', {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''}
    })
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('sysinfo-content');
        let html = '';
        for (const [section, rows] of Object.entries(data)) {
            html += '<div style="margin-bottom:1.25rem;">';
            html += '<div style="color:#ffd700;font-size:11px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.5rem;border-bottom:1px solid rgba(255,215,0,.2);padding-bottom:.25rem;">' + section + '</div>';
            for (const [k, v] of Object.entries(rows)) {
                const sensitive = ['password','secret','key','token','pass'].some(s => k.toLowerCase().includes(s));
                html += '<div style="display:flex;gap:1rem;padding:.2rem 0;border-bottom:1px solid rgba(255,255,255,.04);">';
                html += '<span style="color:#6b8fa8;min-width:180px;flex-shrink:0;">' + k + '</span>';
                html += '<span style="color:' + (sensitive ? '#ffaa44' : '#7effa0') + ';word-break:break-all;">' + v + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }
        el.innerHTML = html;
    })
    .catch(e => {
        document.getElementById('sysinfo-content').innerHTML = '<div style="color:#ff6b6b;">Error loading system info: ' + e.message + '</div>';
    });
}

function copySystemInfo() {
    const el = document.getElementById('sysinfo-content');
    const text = el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('System info copied to clipboard');
    });
}
</script>
@endsection
