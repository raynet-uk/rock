@extends('layouts.admin')
@section('title', 'CMS Update — Admin')
@section('content')
<style>
*{box-sizing:border-box;}
.cu-wrap{max-width:860px;margin:0 auto;padding:2rem 1.5rem 5rem;}

/* Hero */
.cu-hero{background:linear-gradient(135deg,#001f40 0%,#003366 60%,#0a1f3a 100%);border-radius:12px;padding:2.5rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;}
.cu-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");}
.cu-hero-left{position:relative;z-index:1;}
.cu-hero-eyebrow{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.5rem;}
.cu-hero-title{font-size:1.75rem;font-weight:bold;color:#fff;margin-bottom:.35rem;display:flex;align-items:center;gap:.6rem;}
.cu-hero-sub{font-size:.875rem;color:rgba(255,255,255,.5);}
.cu-hero-right{position:relative;z-index:1;text-align:right;}
.cu-version-display{display:flex;align-items:center;gap:.75rem;}
.cu-v-box{text-align:center;}
.cu-v-label{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.35);margin-bottom:.25rem;}
.cu-v-number{font-family:monospace;font-size:1.4rem;font-weight:bold;padding:.4rem 1rem;border-radius:6px;}
.cu-v-current{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.8);}
.cu-v-latest{background:rgba(26,107,60,.3);border:1px solid rgba(126,255,160,.3);color:#7effa0;}
.cu-v-latest.no-update{background:rgba(26,107,60,.2);border-color:rgba(26,107,60,.4);color:#4ade80;}
.cu-v-arrow{color:rgba(255,255,255,.2);font-size:1.2rem;}
.cu-status-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:999px;font-size:11px;font-weight:bold;margin-top:.75rem;}
.cu-status-ok{background:rgba(26,107,60,.25);border:1px solid rgba(126,255,160,.25);color:#7effa0;}
.cu-status-warn{background:rgba(250,204,21,.15);border:1px solid rgba(250,204,21,.3);color:#fde047;}
.cu-status-dot{width:6px;height:6px;border-radius:50%;background:currentColor;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}

/* Cards */
.cu-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;}
@media(max-width:640px){.cu-grid{grid-template-columns:1fr;}}
.cu-card{background:#fff;border:1px solid #dde2e8;border-radius:8px;overflow:hidden;}
.cu-card-head{padding:.75rem 1.1rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;display:flex;align-items:center;gap:.5rem;}
.cu-card-head-icon{font-size:1rem;}
.cu-card-head-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;}
.cu-card-body{padding:1.1rem;}

/* Stat row */
.cu-stat-row{display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f0f1f3;font-size:13px;}
.cu-stat-row:last-child{border-bottom:none;}
.cu-stat-key{color:#6b7f96;}
.cu-stat-val{font-weight:bold;color:#001f40;font-family:monospace;font-size:12px;}

/* Actions */
.cu-actions{background:#fff;border:1px solid #dde2e8;border-radius:8px;padding:1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.cu-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.25rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;border-radius:5px;border:1px solid;transition:all .15s;text-decoration:none;font-family:inherit;}
.cu-btn-navy{background:#003366;border-color:#003366;color:#fff;}
.cu-btn-navy:hover{background:#002244;}
.cu-btn-green{background:#1a6b3c;border-color:#1a6b3c;color:#fff;box-shadow:0 2px 8px rgba(26,107,60,.3);}
.cu-btn-green:hover{background:#155730;}
.cu-btn-outline{background:transparent;border-color:#003366;color:#003366;}
.cu-btn-outline:hover{background:#e8eef5;}
.cu-btn-spin{display:none;}

/* Warning */
.cu-warning{background:linear-gradient(135deg,#fdf8ec,#fff8e1);border:1px solid #ffe082;border-left:4px solid #f9a825;border-radius:0 8px 8px 0;padding:1rem 1.25rem;margin-bottom:1rem;font-size:12px;color:#7a5800;}
.cu-warning strong{color:#5a4200;}

/* What's protected */
.cu-protected{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
@media(max-width:500px){.cu-protected{grid-template-columns:1fr;}}
.cu-protected-item{display:flex;align-items:center;gap:.5rem;font-size:12px;color:#1a6b3c;padding:.3rem .5rem;background:#eef7f2;border-radius:4px;}
.cu-protected-item::before{content:'🛡';font-size:.8rem;}

/* Alert */
.cu-alert{padding:.75rem 1rem;border-radius:6px;font-size:13px;font-weight:bold;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.cu-alert-green{background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;}

/* Progress overlay */
.cu-progress{display:none;position:fixed;inset:0;background:rgba(0,20,40,.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;}
.cu-progress.show{display:flex;}
.cu-progress-box{background:#001f40;border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:2.5rem 3rem;text-align:center;max-width:420px;}
.cu-progress-title{font-size:1.2rem;font-weight:bold;color:#fff;margin-bottom:.5rem;}
.cu-progress-sub{font-size:.875rem;color:rgba(255,255,255,.5);margin-bottom:1.5rem;}
.cu-bar-wrap{background:rgba(255,255,255,.08);border-radius:999px;height:6px;overflow:hidden;}
.cu-bar{height:100%;background:linear-gradient(90deg,#003366,#C8102E,#7effa0);background-size:200% 100%;animation:barSlide 1.5s linear infinite;border-radius:999px;}
@keyframes barSlide{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
.cu-progress-steps{margin-top:1.25rem;text-align:left;}
.cu-step{display:flex;align-items:center;gap:.6rem;padding:.3rem 0;font-size:.8rem;color:rgba(255,255,255,.4);transition:color .3s;}
.cu-step.active{color:#7effa0;}
.cu-step.done{color:rgba(255,255,255,.6);}
.cu-step-icon{font-size:.9rem;}
</style>

<div class="cu-wrap">

    @if(session('status'))
    <div class="cu-alert cu-alert-green">✓ {{ session('status') }}</div>
    @endif

    {{-- Hero --}}
    <div class="cu-hero">
        <div class="cu-hero-left">
            <div class="cu-hero-eyebrow">RAYNET-OS · System</div>
            <div class="cu-hero-title">🔄 CMS Update</div>
            <div class="cu-hero-sub">Keep your installation up to date with the latest features and fixes.</div>
            <div class="cu-status-pill {{ $updateAvailable ? 'cu-status-warn' : 'cu-status-ok' }}">
                <span class="cu-status-dot"></span>
                {{ $updateAvailable ? 'Update Available' : 'System Up to Date' }}
            </div>
        </div>
        <div class="cu-hero-right">
            <div class="cu-version-display">
                <div class="cu-v-box">
                    <div class="cu-v-label">Installed</div>
                    <div class="cu-v-number cu-v-current">v{{ $localVersion }}</div>
                </div>
                @if($updateAvailable)
                <div class="cu-v-arrow">→</div>
                <div class="cu-v-box">
                    <div class="cu-v-label">Available</div>
                    <div class="cu-v-number cu-v-latest">v{{ $remoteVersion }}</div>
                </div>
                @else
                <div class="cu-v-box">
                    <div class="cu-v-label">Latest</div>
                    <div class="cu-v-number cu-v-latest no-update">v{{ $remoteVersion }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="cu-actions">
        <form method="POST" action="{{ route('admin.cms-update.check') }}">@csrf
            <button type="submit" class="cu-btn cu-btn-outline">🔍 Check for Updates</button>
        </form>
        @if($updateAvailable)
        <form method="POST" action="{{ route('admin.cms-update.apply') }}" id="updateForm">@csrf
            <button type="submit" class="cu-btn cu-btn-green" onclick="startUpdate(event)">
                ⬆ Apply Update v{{ $remoteVersion }}
            </button>
        </form>
        @endif
        <div style="margin-left:auto;font-size:11px;color:#6b7f96;">
            @if($checkedAt)Last checked {{ \Carbon\Carbon::parse($checkedAt)->diffForHumans() }}@endif
        </div>
    </div>

    @if($updateAvailable)
    <div class="cu-warning">
        <strong>⚠ Before you update:</strong> The updater will pull the latest code from GitHub, run any new database migrations, and clear all caches. The site may be briefly unavailable. Your data is never touched.
    </div>
    @endif

    {{-- Info grid --}}
    <div class="cu-grid">
        <div class="cu-card">
            <div class="cu-card-head">
                <span class="cu-card-head-icon">📊</span>
                <span class="cu-card-head-title">Version Info</span>
            </div>
            <div class="cu-card-body">
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Installed</span>
                    <span class="cu-stat-val">v{{ $localVersion }}</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Latest on GitHub</span>
                    <span class="cu-stat-val">v{{ $remoteVersion }}</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Status</span>
                    <span class="cu-stat-val" style="color:{{ $updateAvailable ? '#f59e0b' : '#1a6b3c' }}">
                        {{ $updateAvailable ? '⚠ Update Available' : '✓ Up to Date' }}
                    </span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Last Checked</span>
                    <span class="cu-stat-val">{{ $checkedAt ? \Carbon\Carbon::parse($checkedAt)->format('j M H:i') : '—' }}</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Last Updated</span>
                    <span class="cu-stat-val">{{ $lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->format('j M Y H:i') : 'Never' }}</span>
                </div>
            </div>
        </div>

        <div class="cu-card">
            <div class="cu-card-head">
                <span class="cu-card-head-icon">⚙️</span>
                <span class="cu-card-head-title">What Gets Updated</span>
            </div>
            <div class="cu-card-body">
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Application code</span>
                    <span class="cu-stat-val" style="color:#1a6b3c;">✓</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Database migrations</span>
                    <span class="cu-stat-val" style="color:#1a6b3c;">✓ New only</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Routes & config</span>
                    <span class="cu-stat-val" style="color:#1a6b3c;">✓</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Your .env file</span>
                    <span class="cu-stat-val" style="color:#C8102E;">✕ Never touched</span>
                </div>
                <div class="cu-stat-row">
                    <span class="cu-stat-key">Uploaded files</span>
                    <span class="cu-stat-val" style="color:#C8102E;">✕ Never touched</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Protected data --}}
    <div class="cu-card" style="margin-bottom:1rem;">
        <div class="cu-card-head">
            <span class="cu-card-head-icon">🛡</span>
            <span class="cu-card-head-title">Your Data is Always Protected</span>
        </div>
        <div class="cu-card-body">
            <div class="cu-protected">
                <div class="cu-protected-item">Members & profiles</div>
                <div class="cu-protected-item">Events & logs</div>
                <div class="cu-protected-item">Settings & branding</div>
                <div class="cu-protected-item">Uploaded files</div>
                <div class="cu-protected-item">Activity logs</div>
                <div class="cu-protected-item">Licence key</div>
            </div>
        </div>
    </div>

</div>

{{-- Update progress overlay --}}
<div class="cu-progress" id="updateProgress">
    <div class="cu-progress-box">
        <div style="font-size:2rem;margin-bottom:1rem;">🔄</div>
        <div class="cu-progress-title">Updating RAYNET-OS</div>
        <div class="cu-progress-sub">Please wait — do not close this page.</div>
        <div class="cu-bar-wrap"><div class="cu-bar"></div></div>
        <div class="cu-progress-steps">
            <div class="cu-step active" id="step1"><span class="cu-step-icon">⬇</span> Pulling latest code from GitHub</div>
            <div class="cu-step" id="step2"><span class="cu-step-icon">📦</span> Installing dependencies</div>
            <div class="cu-step" id="step3"><span class="cu-step-icon">🗄</span> Running database migrations</div>
            <div class="cu-step" id="step4"><span class="cu-step-icon">🧹</span> Clearing caches</div>
            <div class="cu-step" id="step5"><span class="cu-step-icon">✓</span> Finalising update</div>
        </div>
    </div>
</div>

<script>
function startUpdate(e) {
    if (!confirm('Apply update v{{ $remoteVersion ?? "" }}? The site may be briefly unavailable. Your data is never affected.')) {
        e.preventDefault();
        return;
    }
    document.getElementById('updateProgress').classList.add('show');
    // Animate steps
    let steps = [1,2,3,4,5];
    let delays = [0, 4000, 9000, 15000, 19000];
    steps.forEach((s, i) => {
        setTimeout(() => {
            if (i > 0) document.getElementById('step' + steps[i-1]).classList.replace('active','done');
            document.getElementById('step' + s).classList.add('active');
        }, delays[i]);
    });
}
</script>
@endsection
