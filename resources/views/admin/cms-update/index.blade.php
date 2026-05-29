@extends('layouts.admin')
@section('title', 'CMS Update — Admin')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--green:#1a6b3c;--green-bg:#eef7f2;--grey:#f2f2f2;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
.wrap{max-width:700px;margin:2rem auto;padding:0 1.5rem 4rem;}
.card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1.5rem;margin-bottom:1.5rem;}
.card-title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);margin-bottom:1rem;}
.version-badge{display:inline-block;padding:.3rem .9rem;font-family:monospace;font-weight:bold;font-size:1.1rem;border-radius:4px;}
.badge-current{background:#e8eef5;color:var(--navy);}
.badge-new{background:#eef7f2;color:var(--green);}
.badge-uptodate{background:#eef7f2;color:var(--green);}
.row{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.meta{font-size:12px;color:var(--muted);margin-top:.5rem;}
.btn{padding:.55rem 1.2rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;border:1px solid;display:inline-block;text-decoration:none;}
.btn-navy{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-green{background:var(--green);border-color:var(--green);color:#fff;}
.btn-outline{background:transparent;border-color:var(--navy);color:var(--navy);}
.alert{padding:.75rem 1rem;font-size:13px;font-weight:bold;margin-bottom:1rem;}
.alert-green{background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);color:var(--green);}
.alert-amber{background:#fdf8ec;border:1px solid #e8c96a;border-left:3px solid #c8a030;color:#7a5800;}
.warning-box{background:#fff8e1;border:1px solid #ffe082;border-left:3px solid #f9a825;padding:1rem;font-size:12px;color:#7a5800;margin-top:1rem;}
</style>
<div class="wrap">
    <h1 style="font-size:22px;font-weight:bold;color:var(--navy);margin-bottom:.25rem;">🔄 CMS Update</h1>
    <p style="font-size:13px;color:var(--muted);margin-bottom:1.5rem;">Manage RAYNET-OS CMS version and updates.</p>

    @if(session('status'))
    <div class="alert alert-green">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-title">Version Status</div>
        <div class="row">
            <div>
                <div style="font-size:12px;color:var(--muted);margin-bottom:.25rem;">Installed Version</div>
                <span class="version-badge badge-current">v{{ $localVersion }}</span>
            </div>
            <div>
                <div style="font-size:12px;color:var(--muted);margin-bottom:.25rem;">Latest Version</div>
                <span class="version-badge {{ $updateAvailable ? 'badge-new' : 'badge-uptodate' }}">v{{ $remoteVersion }}</span>
            </div>
            <div>
                @if($updateAvailable)
                    <span style="background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:.3rem .8rem;font-size:12px;font-weight:bold;">⚠ Update Available</span>
                @else
                    <span style="background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);padding:.3rem .8rem;font-size:12px;font-weight:bold;">✓ Up to Date</span>
                @endif
            </div>
        </div>
        <div class="meta">
            @if($checkedAt) Last checked: {{ \Carbon\Carbon::parse($checkedAt)->diffForHumans() }} @endif
            @if($lastUpdated) · Last updated: {{ \Carbon\Carbon::parse($lastUpdated)->format('j M Y H:i') }} @endif
        </div>
        <div style="display:flex;gap:.5rem;margin-top:1rem;">
            <form method="POST" action="{{ route('admin.cms-update.check') }}">@csrf
                <button type="submit" class="btn btn-outline">🔍 Check Now</button>
            </form>
            @if($updateAvailable)
            <form method="POST" action="{{ route('admin.cms-update.apply') }}"
                  onsubmit="return confirm('This will pull the latest code from GitHub, run migrations and clear caches. Your data will not be affected. Continue?')">@csrf
                <button type="submit" class="btn btn-green">⬆ Apply Update v{{ $remoteVersion }}</button>
            </form>
            @endif
        </div>
        @if($updateAvailable)
        <div class="warning-box">
            ⚠ <strong>Before updating:</strong> The update pulls the latest code from GitHub, runs database migrations, and clears all caches. Your group data, settings, members, events, and uploaded files are preserved. The site will be briefly unavailable during the update.
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title">What Gets Updated</div>
        <ul style="font-size:13px;color:var(--text);line-height:2;padding-left:1.25rem;">
            <li>Application code (controllers, models, views)</li>
            <li>Database schema (new migrations only)</li>
            <li>Routes and configuration defaults</li>
        </ul>
        <p style="font-size:12px;color:var(--muted);margin-top:.75rem;">
            <strong>Never touched:</strong> Your .env file, uploaded files, group settings, member data, events, and activity logs.
        </p>
    </div>
</div>
@endsection
