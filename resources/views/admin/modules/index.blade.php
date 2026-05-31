@extends('layouts.admin')
@section('title', 'Module Manager')
@section('content')

@php
    $activeCount = collect($modules)->where('enabled', true)->count();
    $total       = count($modules);
@endphp

<style>
*{box-sizing:border-box;}
.mm{max-width:1100px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}

/* Hero */
.mm-hero{background:linear-gradient(135deg,#001f40 0%,#003366 60%,#0a1f3a 100%);border-radius:12px;padding:2rem 2.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;}
.mm-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");pointer-events:none;}
.mm-hero-left{position:relative;z-index:1;}
.mm-hero-eyebrow{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.4rem;}
.mm-hero-title{font-size:1.6rem;font-weight:bold;color:#fff;margin-bottom:.3rem;display:flex;align-items:center;gap:.6rem;}
.mm-hero-sub{font-size:.875rem;color:rgba(255,255,255,.5);}
.mm-hero-stats{position:relative;z-index:1;display:flex;gap:1.5rem;}
.mm-stat{text-align:center;}
.mm-stat-num{font-size:1.75rem;font-weight:bold;color:#fff;line-height:1;}
.mm-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.4);margin-top:.2rem;}

/* Actions bar */
.mm-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem;}
.mm-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;border-radius:5px;border:1px solid;transition:all .15s;font-family:inherit;}
.mm-btn-primary{background:#003366;border-color:#003366;color:#fff;}
.mm-btn-primary:hover{background:#002244;}
.mm-btn-secondary{background:#e8eef5;border-color:#c5d5e8;color:#003366;}
.mm-btn-secondary:hover{background:#d5e3f0;}
.mm-btn-ghost{background:transparent;border-color:#dde2e8;color:#6b7f96;}
.mm-btn-ghost:hover{background:#f4f5f7;color:#003366;}

/* Tabs */
.mm-tabs{display:flex;gap:0;border-bottom:2px solid #dde2e8;margin-bottom:1.25rem;}
.mm-tab{padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;color:#6b7f96;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;text-decoration:none;display:flex;align-items:center;gap:.4rem;}
.mm-tab:hover{color:#003366;}
.mm-tab--active{color:#003366;border-bottom-color:#003366;}
.mm-tab__count{background:#e8eef5;color:#6b7f96;font-size:10px;padding:.1rem .4rem;border-radius:999px;font-weight:bold;}
.mm-tab__count--warn{background:#fff3cd;color:#856404;}

/* Notices */
.mm-notice{display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-radius:6px;font-size:13px;font-weight:bold;margin-bottom:1rem;}
.mm-notice--ok{background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;}
.mm-notice--err{background:#fef2f2;border:1px solid #fca5a5;color:#C8102E;}
.mm-notice--warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}

/* Upload panel */
.mm-upload-panel{background:#fff;border:1px solid #dde2e8;border-radius:8px;margin-bottom:1.25rem;overflow:hidden;}
.mm-upload-panel__head{padding:.75rem 1.25rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#003366;display:flex;align-items:center;gap:.5rem;}
.mm-upload-form{padding:1.25rem;}
.mm-upload-drop{border:2px dashed #dde2e8;border-radius:6px;padding:2rem;text-align:center;transition:border-color .15s;cursor:pointer;}
.mm-upload-drop:hover{border-color:#003366;background:#f8f9fb;}
.mm-upload-drop__text{font-size:13px;color:#4b5563;margin:.5rem 0 .25rem;}
.mm-upload-drop__hint{font-size:11px;color:#9aa3ae;}
.mm-upload-browse{color:#003366;font-weight:bold;cursor:pointer;text-decoration:underline;}
.mm-upload-actions{display:flex;gap:.5rem;margin-top:1rem;}
.mm-upload-err{color:#C8102E;font-size:12px;margin-top:.5rem;}
.mm-hidden{display:none!important;}

/* Module cards */
.mm-grid{display:flex;flex-direction:column;gap:.75rem;}
.mm-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;transition:box-shadow .15s;}
.mm-card:hover{box-shadow:0 4px 16px rgba(0,51,102,.08);}
.mm-card--core{border-left:4px solid #003366;}
.mm-card--active{border-left:4px solid #1a6b3c;}
.mm-card--inactive{border-left:4px solid #dde2e8;opacity:.85;}
.mm-card--update{border-left:4px solid #f59e0b;}
.mm-card-inner{padding:1.25rem 1.5rem;display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:start;}
@media(max-width:640px){.mm-card-inner{grid-template-columns:1fr;}}
.mm-card-left{display:flex;flex-direction:column;gap:.5rem;}
.mm-card-head{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.mm-code-badge{background:#003366;color:#fff;font-size:10px;font-weight:bold;padding:.2rem .55rem;border-radius:3px;letter-spacing:.1em;font-family:monospace;}
.mm-code-badge--core{background:#6b7f96;}
.mm-mod-name{font-size:15px;font-weight:bold;color:#001f40;}
.mm-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:10px;font-weight:bold;padding:.2rem .6rem;border-radius:999px;text-transform:uppercase;letter-spacing:.08em;}
.mm-pill--active{background:rgba(26,107,60,.1);border:1px solid rgba(26,107,60,.25);color:#1a6b3c;}
.mm-pill--inactive{background:#f4f5f7;border:1px solid #dde2e8;color:#9aa3ae;}
.mm-pill--core{background:rgba(0,51,102,.1);border:1px solid rgba(0,51,102,.2);color:#003366;}
.mm-pill--update{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);color:#92400e;}
.mm-pill-dot{width:5px;height:5px;border-radius:50%;background:currentColor;}
.mm-mod-desc{font-size:13px;color:#4b5563;line-height:1.5;}
.mm-mod-meta{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;font-size:11px;color:#9aa3ae;}
.mm-mod-meta a{color:#6b7f96;text-decoration:none;}
.mm-mod-meta a:hover{color:#003366;}
.mm-tags{display:flex;gap:.3rem;flex-wrap:wrap;}
.mm-tag{background:#f0f4f8;color:#6b7f96;font-size:10px;padding:.15rem .45rem;border-radius:3px;}
.mm-actions-row{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-top:.25rem;}
.mm-act{font-size:11px;font-weight:bold;color:#003366;background:none;border:none;cursor:pointer;padding:0;font-family:inherit;text-decoration:underline;}
.mm-act:hover{color:#001f40;}
.mm-act-sep{color:#dde2e8;font-size:11px;}
.mm-act--locked{color:#9aa3ae;text-decoration:none;cursor:default;}
.mm-card-right{display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;}
.mm-ver{font-family:monospace;font-size:11px;background:#f0f4f8;color:#6b7f96;padding:.2rem .5rem;border-radius:3px;}
.mm-toggle-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;font-size:12px;font-weight:bold;border-radius:5px;cursor:pointer;border:1px solid;font-family:inherit;transition:all .15s;white-space:nowrap;}
.mm-toggle-btn--enable{background:#1a6b3c;border-color:#1a6b3c;color:#fff;}
.mm-toggle-btn--enable:hover{background:#155730;}
.mm-toggle-btn--disable{background:#fff;border-color:#dde2e8;color:#6b7f96;}
.mm-toggle-btn--disable:hover{background:#fef2f2;border-color:#fca5a5;color:#C8102E;}
.mm-toggle-btn--update{background:#f59e0b;border-color:#f59e0b;color:#fff;}
.mm-toggle-btn--update:hover{background:#d97706;}
.mm-delete-btn{background:none;border:none;color:#9aa3ae;cursor:pointer;font-size:11px;font-weight:bold;font-family:inherit;padding:.2rem .4rem;border-radius:3px;transition:all .15s;}
.mm-delete-btn:hover{background:#fef2f2;color:#C8102E;}

/* Changelog */
.mm-cl{background:#f8f9fb;border-top:1px solid #f0f1f3;padding:.75rem 1.5rem;}
.mm-cl__row{display:flex;gap:.75rem;padding:.2rem 0;font-size:12px;}
.mm-cl__ver{background:#e8eef5;color:#003366;padding:.1rem .4rem;border-radius:3px;font-family:monospace;flex-shrink:0;}
.mm-cl__note{color:#4b5563;}

/* Components */
.mm-components{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.25rem;}
.mm-component{font-size:10px;background:#eef7f2;color:#1a6b3c;border:1px solid rgba(26,107,60,.2);padding:.15rem .5rem;border-radius:3px;}

/* Empty */
.mm-empty{text-align:center;padding:4rem 2rem;color:#9aa3ae;}
.mm-empty svg{display:block;margin:0 auto 1rem;}
</style>

<div class="mm">

    {{-- Hero --}}
    <div class="mm-hero">
        <div class="mm-hero-left">
            <div class="mm-hero-eyebrow">ROCK · System</div>
            <div class="mm-hero-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                Module Manager
            </div>
            <div class="mm-hero-sub">Install, manage and update ROCK modules from the official registry.</div>
        </div>
        <div class="mm-hero-stats">
            <div class="mm-stat">
                <div class="mm-stat-num">{{ $total }}</div>
                <div class="mm-stat-label">Installed</div>
            </div>
            <div class="mm-stat">
                <div class="mm-stat-num" style="color:#7effa0;">{{ $activeCount }}</div>
                <div class="mm-stat-label">Active</div>
            </div>
            <div class="mm-stat">
                <div class="mm-stat-num" style="color:{{ $updateCount > 0 ? '#fde047' : 'rgba(255,255,255,.4)' }};">{{ $updateCount }}</div>
                <div class="mm-stat-label">Updates</div>
            </div>
        </div>
    </div>

    {{-- Actions bar --}}
    <div class="mm-bar">
        <button class="mm-btn mm-btn-primary" onclick="openRegistry()">
            🔌 Browse Modules
        </button>
        <button class="mm-btn mm-btn-secondary" onclick="document.getElementById('mm-upload-panel').classList.toggle('mm-hidden')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload ZIP
        </button>
        <form action="{{ route('admin.modules.refresh-updates') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="mm-btn mm-btn-ghost">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Check Updates
            </button>
        </form>
    </div>

    {{-- Upload panel --}}
    <div id="mm-upload-panel" class="mm-upload-panel mm-hidden">
        <div class="mm-upload-panel__head">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Install Module from ZIP
        </div>
        <form action="{{ route('admin.modules.upload') }}" method="POST" enctype="multipart/form-data" class="mm-upload-form">
            @csrf
            <div class="mm-upload-drop" onclick="document.getElementById('mm-file-input').click()">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p class="mm-upload-drop__text">Drag & drop a <strong>.zip</strong> here, or <span class="mm-upload-browse">browse</span></p>
                <p class="mm-upload-drop__hint" id="mm-file-name">Max 20 MB · Must contain a valid module.json</p>
                <input type="file" name="module_zip" id="mm-file-input" accept=".zip" style="display:none" onchange="document.getElementById('mm-file-name').textContent = this.files[0]?.name ?? 'No file chosen'">
            </div>
            @error('module_zip')<div class="mm-upload-err">{{ $message }}</div>@enderror
            <div class="mm-upload-actions">
                <button type="submit" class="mm-btn mm-btn-primary">Install Now</button>
                <button type="button" class="mm-btn mm-btn-ghost" onclick="document.getElementById('mm-upload-panel').classList.add('mm-hidden')">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Notices --}}
    @if(session('success'))
    <div class="mm-notice mm-notice--ok">✓ {!! session('success') !!}</div>
    @endif
    @if(session('error'))
    <div class="mm-notice mm-notice--err">⚠ {!! session('error') !!}</div>
    @endif
    @if($updateCount > 0)
    <div class="mm-notice mm-notice--warn">⬆ <strong>{{ $updateCount }} update{{ $updateCount > 1 ? 's' : '' }} available</strong> — apply from the cards below.</div>
    @endif

    {{-- Filter tabs --}}
    <div class="mm-tabs">
        <a href="#" class="mm-tab mm-tab--active" data-filter="all">All <span class="mm-tab__count">{{ $total }}</span></a>
        <a href="#" class="mm-tab" data-filter="active">Active <span class="mm-tab__count">{{ $activeCount }}</span></a>
        <a href="#" class="mm-tab" data-filter="inactive">Inactive <span class="mm-tab__count">{{ $total - $activeCount }}</span></a>
        @if($updateCount > 0)
        <a href="#" class="mm-tab" data-filter="update">Updates <span class="mm-tab__count mm-tab__count--warn">{{ $updateCount }}</span></a>
        @endif
    </div>

    {{-- Module cards --}}
    @if($total === 0)
    <div class="mm-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        <p style="font-weight:bold;color:#4b5563;margin-bottom:.25rem;">No modules installed</p>
        <p style="font-size:13px;">Click Browse Modules to install from the ROCK registry.</p>
    </div>
    @else
    <div class="mm-grid" id="mm-grid">
    @foreach($modules as $alias => $mod)
    @php
        $isCore   = $mod['is_core'] ?? false;
        $enabled  = $mod['enabled'] ?? false;
        $hasUpdate = !empty($mod['update']);
        $cardClass = $isCore ? 'mm-card--core' : ($hasUpdate ? 'mm-card--update' : ($enabled ? 'mm-card--active' : 'mm-card--inactive'));
        $state    = $enabled ? 'active' : 'inactive';
    @endphp
    <div class="mm-card {{ $cardClass }}" data-state="{{ $state }}" data-update="{{ $hasUpdate ? 'true' : 'false' }}">
        <div class="mm-card-inner">
            <div class="mm-card-left">
                <div class="mm-card-head">
                    <span class="mm-code-badge {{ $isCore ? 'mm-code-badge--core' : '' }}">{{ $isCore ? 'CORE' : strtoupper($alias) }}</span>
                    <span class="mm-mod-name">{{ $mod['name'] }}</span>
                    @if($isCore)
                        <span class="mm-pill mm-pill--core"><span class="mm-pill-dot"></span> Always Active</span>
                    @elseif($hasUpdate)
                        <span class="mm-pill mm-pill--update">⬆ Update Available</span>
                    @elseif($enabled)
                        <span class="mm-pill mm-pill--active"><span class="mm-pill-dot"></span> Active</span>
                    @else
                        <span class="mm-pill mm-pill--inactive">Inactive</span>
                    @endif
                </div>

                <div class="mm-mod-desc">{{ $mod['description'] }}</div>

                @if(!empty($mod['components']))
                <div class="mm-components">
                    @foreach($mod['components'] as $comp)
                    <span class="mm-component">✓ {{ $comp }}</span>
                    @endforeach
                </div>
                @endif

                @if(!empty($mod['tags']))
                <div class="mm-tags">
                    @foreach($mod['tags'] as $tag)
                    <span class="mm-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="mm-actions-row">
                    @if($isCore)
                        @if(\Illuminate\Support\Facades\Route::has('admin.core.health'))
                        <a href="{{ route('admin.core.health') }}" class="mm-act">System Health</a>
                        <span class="mm-act-sep">·</span>
                        @endif
                        <span class="mm-act mm-act--locked">🔒 Core — cannot be modified</span>
                    @else
                        @if($hasUpdate)
                        <form method="POST" action="{{ route('admin.modules.update', $alias) }}" style="display:inline">@csrf
                            <button class="mm-act" style="color:#f59e0b;">⬆ View update</button>
                        </form>
                        <span class="mm-act-sep">·</span>
                        @endif
                        @if(!empty($mod['docs_uri']))
                        <a href="{{ $mod['docs_uri'] }}" target="_blank" class="mm-act">Docs</a>
                        <span class="mm-act-sep">·</span>
                        @endif
                        @if(!empty($mod['changelog']))
                        <button class="mm-act" onclick="this.closest('.mm-card').querySelector('.mm-cl').classList.toggle('mm-hidden')">Changelog</button>
                        <span class="mm-act-sep">·</span>
                        @endif
                        @if($mod['can_delete'] ?? true)
                        <form method="POST" action="{{ route('admin.modules.delete', $alias) }}" style="display:inline"
                              onsubmit="return confirm('Delete {{ $mod['name'] }}? This cannot be undone.')">@csrf
                            <button class="mm-act" style="color:#9aa3ae;">Delete</button>
                        </form>
                        @endif
                    @endif
                    <div style="margin-left:auto;font-size:11px;color:#9aa3ae;">
                        @if($mod['author'])
                            by @if($mod['author_uri'])<a href="{{ $mod['author_uri'] }}" target="_blank" class="mm-act">{{ $mod['author'] }}</a>@else{{ $mod['author'] }}@endif
                        @endif
                        @if($mod['license']) · {{ $mod['license'] }} @endif
                    </div>
                </div>
            </div>

            <div class="mm-card-right">
                <code class="mm-ver">v{{ $mod['version'] }}</code>

                @if(!$isCore)
                @if($hasUpdate)
                <form method="POST" action="{{ route('admin.modules.update', $alias) }}">@csrf
                    <button class="mm-toggle-btn mm-toggle-btn--update">⬆ Update Now</button>
                </form>
                @elseif($enabled)
                <form method="POST" action="{{ route('admin.modules.disable', $alias) }}">@csrf
                    <button class="mm-toggle-btn mm-toggle-btn--disable">⏸ Deactivate</button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.modules.enable', $alias) }}">@csrf
                    <button class="mm-toggle-btn mm-toggle-btn--enable">▶ Activate</button>
                </form>
                @endif
                @endif
            </div>
        </div>

        @if(!empty($mod['changelog']))
        <div class="mm-cl mm-hidden">
            @foreach($mod['changelog'] as $ver => $note)
            <div class="mm-cl__row">
                <code class="mm-cl__ver">{{ $ver }}</code>
                <span class="mm-cl__note">{{ $note }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
    </div>
    @endif

</div>

{{-- Registry Modal --}}
<div id="mm-registry-modal" style="display:none;position:fixed;inset:0;background:rgba(0,15,30,.8);z-index:9999;overflow-y:auto;padding:2rem 1rem;">
    <div style="max-width:1000px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4);">
        <div style="background:linear-gradient(135deg,#001f40,#003366);padding:1.5rem 1.75rem;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.25rem;">ROCK Module Registry</div>
                <div style="font-size:1.25rem;font-weight:bold;color:#fff;">Browse & Install Modules</div>
            </div>
            <button onclick="closeRegistry()" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;">✕</button>
        </div>
        <div style="padding:1rem 1.5rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <input type="text" id="mm-reg-search" placeholder="Search modules..." oninput="filterRegistry()"
                   style="padding:.5rem .85rem;border:1px solid #dde2e8;border-radius:5px;font-size:13px;width:250px;outline:none;">
            <div id="mm-reg-count" style="font-size:12px;color:#6b7f96;"></div>
            <div style="margin-left:auto;font-size:11px;color:#6b7f96;">
                Source: <a href="https://github.com/raynet-uk/rock-modules" target="_blank" style="color:#003366;">raynet-uk/rock-modules</a>
            </div>
        </div>
        <div id="mm-reg-loading" style="padding:3rem;text-align:center;color:#6b7f96;">
            <div style="font-size:2rem;margin-bottom:.75rem;animation:rSpin 1s linear infinite;display:inline-block;">⟳</div>
            <div>Loading registry...</div>
        </div>
        <div id="mm-reg-error" style="display:none;padding:2rem;text-align:center;color:#C8102E;"></div>
        <div id="mm-reg-grid" style="display:none;padding:1.5rem;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;"></div>
        <div style="padding:1rem 1.5rem;background:#f8f9fb;border-top:1px solid #dde2e8;font-size:11px;color:#9aa3ae;text-align:center;">
            Always review module code before installing on production sites.
        </div>
    </div>
</div>

<style>
@keyframes rSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.reg-card{background:#fff;border:1px solid #dde2e8;border-radius:8px;overflow:hidden;transition:box-shadow .15s;}
.reg-card:hover{box-shadow:0 4px 16px rgba(0,51,102,.12);}
.reg-card-head{padding:1rem;border-bottom:1px solid #f0f1f3;}
.reg-card-code{background:#003366;color:#fff;font-size:10px;font-weight:bold;padding:.2rem .55rem;border-radius:3px;letter-spacing:.1em;font-family:monospace;margin-bottom:.5rem;display:inline-block;}
.reg-card-name{font-size:14px;font-weight:bold;color:#001f40;margin-bottom:.15rem;}
.reg-card-author{font-size:11px;color:#6b7f96;}
.reg-card-body{padding:1rem;}
.reg-card-desc{font-size:12px;color:#4b5563;line-height:1.5;margin-bottom:.75rem;}
.reg-card-tags{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.75rem;}
.reg-card-tag{background:#f0f4f8;color:#6b7f96;font-size:10px;padding:.15rem .45rem;border-radius:3px;}
.reg-card-foot{padding:.75rem 1rem;background:#f8f9fb;border-top:1px solid #f0f1f3;display:flex;align-items:center;justify-content:space-between;}
.reg-install-btn{padding:.4rem .9rem;font-size:12px;font-weight:bold;border:none;border-radius:4px;cursor:pointer;font-family:inherit;transition:all .15s;}
.reg-install-btn.install{background:#003366;color:#fff;}
.reg-install-btn.install:hover{background:#002244;}
.reg-install-btn.installed{background:#eef7f2;color:#1a6b3c;border:1px solid #b8ddc9;cursor:default;}
.reg-install-btn.installing{background:#f0f4f8;color:#6b7f96;cursor:wait;}
</style>

<script>
// Filter tabs
document.querySelectorAll('.mm-tab').forEach(tab => {
    tab.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.mm-tab').forEach(t => t.classList.remove('mm-tab--active'));
        tab.classList.add('mm-tab--active');
        const filter = tab.dataset.filter;
        document.querySelectorAll('.mm-card').forEach(card => {
            if (filter === 'all') { card.style.display = ''; return; }
            if (filter === 'update') { card.style.display = card.dataset.update === 'true' ? '' : 'none'; return; }
            card.style.display = card.dataset.state === filter ? '' : 'none';
        });
    });
});

// Registry
let registryData = [];
function openRegistry() {
    document.getElementById('mm-registry-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (!registryData.length) loadRegistry();
}
function closeRegistry() {
    document.getElementById('mm-registry-modal').style.display = 'none';
    document.body.style.overflow = '';
}
function loadRegistry() {
    document.getElementById('mm-reg-loading').style.display = 'block';
    document.getElementById('mm-reg-error').style.display = 'none';
    document.getElementById('mm-reg-grid').style.display = 'none';
    fetch('{{ route("admin.modules.browse") }}', { headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} })
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            registryData = data.modules || [];
            document.getElementById('mm-reg-loading').style.display = 'none';
            renderRegistry(registryData);
        })
        .catch(e => {
            document.getElementById('mm-reg-loading').style.display = 'none';
            document.getElementById('mm-reg-error').style.display = 'block';
            document.getElementById('mm-reg-error').textContent = '⚠ ' + e.message;
        });
}
function renderRegistry(modules) {
    const grid = document.getElementById('mm-reg-grid');
    document.getElementById('mm-reg-count').textContent = modules.length + ' module' + (modules.length !== 1 ? 's' : '');
    grid.style.display = 'grid';
    grid.innerHTML = modules.map(m => `
        <div class="reg-card">
            <div class="reg-card-head">
                <div class="reg-card-code">${m.system_code || m.alias.toUpperCase()}</div>
                <div class="reg-card-name">${m.name}</div>
                <div class="reg-card-author">by ${m.author || 'RAYNET Liverpool'} ${m.installed ? '· <span style="color:#1a6b3c;font-weight:bold;">✓ Installed</span>' : ''}</div>
            </div>
            <div class="reg-card-body">
                <div class="reg-card-desc">${m.description}</div>
                <div class="reg-card-tags">${(m.tags||[]).map(t=>`<span class="reg-card-tag">${t}</span>`).join('')}</div>
            </div>
            <div class="reg-card-foot">
                <span style="font-family:monospace;font-size:11px;color:#9aa3ae;">v${m.version}</span>
                <div style="display:flex;gap:.4rem;">
                    ${m.docs_url ? `<a href="${m.docs_url}" target="_blank" style="padding:.4rem .7rem;font-size:11px;font-weight:bold;border:1px solid #dde2e8;border-radius:4px;color:#6b7f96;text-decoration:none;">Docs</a>` : ''}
                    <button class="reg-install-btn ${m.installed ? 'installed' : 'install'}" id="reg-btn-${m.alias}"
                        ${m.installed ? 'disabled' : `onclick="installModule('${m.alias}','${m.download_url}',this)"`}>
                        ${m.installed ? '✓ Installed' : '⬇ Install'}
                    </button>
                </div>
            </div>
        </div>`).join('');
}
function filterRegistry() {
    const q = document.getElementById('mm-reg-search').value.toLowerCase();
    renderRegistry(registryData.filter(m => m.name.toLowerCase().includes(q) || m.alias.toLowerCase().includes(q) || m.description.toLowerCase().includes(q) || (m.tags||[]).some(t=>t.includes(q))));
}
function installModule(alias, url, btn) {
    if (!confirm('Install ' + alias + ' from the ROCK registry?')) return;
    btn.textContent = '⟳ Installing...'; btn.className = 'reg-install-btn installing'; btn.disabled = true;
    const token = document.querySelector('meta[name=csrf-token]')?.content || '';
    fetch('{{ route("admin.modules.install-from-registry") }}', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({download_url:url, alias:alias})
    }).then(r=>r.json()).then(data => {
        if (data.error) throw new Error(data.error);
        btn.textContent = '✓ Installed'; btn.className = 'reg-install-btn installed'; btn.disabled = true;
        setTimeout(() => location.reload(), 1200);
    }).catch(e => {
        btn.textContent = '⬇ Install'; btn.className = 'reg-install-btn install'; btn.disabled = false;
        alert('Install failed: ' + e.message);
    });
}
document.getElementById('mm-registry-modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeRegistry(); });
</script>
@endsection
