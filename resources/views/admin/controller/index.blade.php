@extends('layouts.admin')
@section('title','Controller Dashboard')
@section('content')
<style>
.ctrl-wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}
.ctrl-hero{background:linear-gradient(135deg,#001f40,#003366);border-radius:12px;padding:2rem 2.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;position:relative;overflow:hidden;}
.ctrl-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");pointer-events:none;}
.ctrl-hero-text{position:relative;z-index:1;}
.ctrl-hero-eyebrow{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.3rem;}
.ctrl-hero-title{font-size:1.5rem;font-weight:bold;color:#fff;margin-bottom:.25rem;}
.ctrl-hero-sub{font-size:.875rem;color:rgba(255,255,255,.5);}
.ctrl-stats{position:relative;z-index:1;display:flex;gap:1.5rem;flex-wrap:wrap;}
.ctrl-stat{text-align:center;}
.ctrl-stat-num{font-size:1.75rem;font-weight:bold;color:#fff;line-height:1;}
.ctrl-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.4);margin-top:.2rem;}
.ctrl-nav{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ctrl-nav-btn{padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;border-radius:5px;border:1px solid #dde2e8;background:#fff;color:#003366;text-decoration:none;transition:all .15s;}
.ctrl-nav-btn:hover,.ctrl-nav-btn.active{background:#003366;color:#fff;border-color:#003366;}
.ctrl-nav-btn.danger{border-color:#C8102E;color:#C8102E;}
.ctrl-nav-btn.danger:hover{background:#C8102E;color:#fff;}
.ctrl-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:700px){.ctrl-grid{grid-template-columns:1fr;}}
.ctrl-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;}
.ctrl-card-head{padding:.85rem 1.25rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;display:flex;align-items:center;justify-content:space-between;}
.ctrl-card-body{padding:1.25rem;}
.ctrl-alert-active{background:linear-gradient(135deg,#C8102E,#a00d25);color:#fff;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.ctrl-alert-badge{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:.25rem .75rem;font-size:11px;font-weight:bold;text-transform:uppercase;}
.ctrl-tier-pill{display:inline-flex;align-items:center;gap:.3rem;background:#f0f4f8;border:1px solid #dde2e8;border-radius:999px;padding:.2rem .6rem;font-size:11px;font-weight:bold;color:#003366;margin:.15rem;}
.ctrl-tier-t1{background:#fef3f2;border-color:#fca5a5;color:#C8102E;}
.ctrl-tier-t2{background:#fff8e1;border-color:#fde68a;color:#92400e;}
.ctrl-tier-t3{background:#f0f4f8;border-color:#c5d5e8;color:#003366;}
.ctrl-tier-support{background:#eef7f2;border-color:#b8ddc9;color:#1a6b3c;}
.ctrl-tier-standby{background:#f5f3ff;border-color:#c4b5fd;color:#5b21b6;}
</style>
<div class="ctrl-wrap">
    <div class="ctrl-hero">
        <div class="ctrl-hero-text">
            <div class="ctrl-hero-eyebrow">ROCK · Controller</div>
            <div class="ctrl-hero-title">🎖 Controller Dashboard</div>
            <div class="ctrl-hero-sub">Group Controller and Deputy Controller section — alert management, tier structure and annual return.</div>
        </div>
        <div class="ctrl-stats">
            @php $allMembers = collect($tiers)->flatten(1); @endphp
            <div class="ctrl-stat"><div class="ctrl-stat-num">{{ $allMembers->count() }}</div><div class="ctrl-stat-label">On Duty</div></div>
            <div class="ctrl-stat"><div class="ctrl-stat-num" style="color:#7effa0;">{{ $yearStats['member_count'] }}</div><div class="ctrl-stat-label">Members</div></div>
            <div class="ctrl-stat"><div class="ctrl-stat-num" style="color:{{ $activeAlert ? '#fde047' : 'rgba(255,255,255,.4)' }};">{{ $activeAlert ? '!' : '—' }}</div><div class="ctrl-stat-label">Alert</div></div>
        </div>
    </div>

    <div class="ctrl-nav">
        <a href="{{ route('admin.controller.index') }}" class="ctrl-nav-btn active">📊 Overview</a>
        <a href="{{ route('admin.controller.tiers') }}" class="ctrl-nav-btn">👥 Tier Structure</a>
        <a href="{{ route('admin.controller.alerts') }}" class="ctrl-nav-btn {{ $activeAlert ? 'danger' : '' }}">🚨 Alerts {{ $activeAlert ? '(ACTIVE)' : '' }}</a>
        <a href="{{ route('admin.controller.annual-return') }}" class="ctrl-nav-btn">📋 Annual Return</a>
    </div>

    @if($activeAlert)
    <div class="ctrl-alert-active">
        <div>
            <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;opacity:.7;margin-bottom:.3rem;">⚡ Active Alert</div>
            <div style="font-size:1.1rem;font-weight:bold;">{{ $activeAlert->title }}</div>
            <div style="font-size:12px;opacity:.8;margin-top:.2rem;">Raised {{ \Carbon\Carbon::parse($activeAlert->raised_at)->diffForHumans() }}</div>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <span class="ctrl-alert-badge">{{ strtoupper($activeAlert->type) }}</span>
            <a href="{{ route('admin.controller.alerts') }}" style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;padding:.4rem .9rem;border-radius:5px;font-size:12px;font-weight:bold;text-decoration:none;">View Responses →</a>
        </div>
    </div>
    @endif

    <div class="ctrl-grid">
        {{-- Current Tier Structure --}}
        <div class="ctrl-card">
            <div class="ctrl-card-head">
                Current Tier Structure
                <a href="{{ route('admin.controller.tiers') }}" style="font-size:10px;color:#003366;text-decoration:none;font-weight:bold;">Manage →</a>
            </div>
            <div class="ctrl-card-body">
                @php $tierLabels = ['tier1'=>'Tier 1','tier2'=>'Tier 2','tier3'=>'Tier 3','support'=>'Support','standby'=>'Standby'];
                $tierClasses = ['tier1'=>'ctrl-tier-t1','tier2'=>'ctrl-tier-t2','tier3'=>'ctrl-tier-t3','support'=>'ctrl-tier-support','standby'=>'ctrl-tier-standby']; @endphp
                @forelse($tiers as $key => $members)
                    @if(count($members))
                    <div style="margin-bottom:.75rem;">
                        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;margin-bottom:.3rem;">{{ $tierLabels[$key] }}</div>
                        <div>
                            @foreach($members as $m)
                            <span class="ctrl-tier-pill {{ $tierClasses[$key] }}">{{ $m->callsign ?: $m->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @empty
                    <p style="font-size:13px;color:#9aa3ae;font-style:italic;">No tier assignments yet. <a href="{{ route('admin.controller.tiers') }}">Add members →</a></p>
                @endforelse
            </div>
        </div>

        {{-- Year Stats --}}
        <div class="ctrl-card">
            <div class="ctrl-card-head">
                {{ $yearStats['year'] }} Statistics
                <a href="{{ route('admin.controller.annual-return') }}" style="font-size:10px;color:#003366;text-decoration:none;font-weight:bold;">Full Return →</a>
            </div>
            <div class="ctrl-card-body">
                @foreach([
                    ['Active Members', $yearStats['member_count']],
                    ['Total Events', $yearStats['total_events']],
                    ['Live Callouts', $yearStats['live_callouts']],
                    ['Standby Alerts', $yearStats['standby_alerts']],
                    ['Test Alerts', $yearStats['test_alerts']],
                ] as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f0f1f3;font-size:13px;">
                    <span style="color:#4b5563;">{{ $label }}</span>
                    <span style="font-weight:bold;color:#001f40;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
