@extends('layouts.admin')
@section('title','Annual Return')
@section('content')
<style>
.ar-wrap{max-width:800px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}
.ctrl-nav{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ctrl-nav-btn{padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;border-radius:5px;border:1px solid #dde2e8;background:#fff;color:#003366;text-decoration:none;transition:all .15s;}
.ctrl-nav-btn:hover,.ctrl-nav-btn.active{background:#003366;color:#fff;border-color:#003366;}
.ar-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;margin-bottom:1rem;}
.ar-card-head{padding:.85rem 1.25rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;}
.ar-row{display:grid;grid-template-columns:1fr auto;align-items:center;gap:1rem;padding:.75rem 1.25rem;border-bottom:1px solid #f0f1f3;}
.ar-row:last-child{border-bottom:none;}
.ar-label{font-size:13px;color:#4b5563;}
.ar-label small{display:block;font-size:11px;color:#9aa3ae;margin-top:1px;}
.ar-value{font-size:1.1rem;font-weight:bold;color:#001f40;text-align:right;min-width:60px;}
.ar-input{width:80px;padding:.35rem .5rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;text-align:right;outline:none;font-family:inherit;}
</style>
<div class="ar-wrap">
    <div class="ctrl-nav">
        <a href="{{ route('admin.controller.index') }}" class="ctrl-nav-btn">📊 Overview</a>
        <a href="{{ route('admin.controller.tiers') }}" class="ctrl-nav-btn">👥 Tier Structure</a>
        <a href="{{ route('admin.controller.alerts') }}" class="ctrl-nav-btn">🚨 Alerts</a>
        <a href="{{ route('admin.controller.annual-return') }}" class="ctrl-nav-btn active">📋 Annual Return</a>
    </div>

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <h1 style="font-size:1.3rem;font-weight:bold;color:#001f40;">Annual Return Data</h1>
        <form method="GET" style="display:flex;align-items:center;gap:.4rem;">
            <select name="year" onchange="this.form.submit()" style="padding:.35rem .65rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
        <span style="font-size:12px;color:#9aa3ae;">Auto-populated from ROCK activity data</span>
    </div>

    <div class="ar-card">
        <div class="ar-card-head">{{ $year }} Annual Return — {{ \App\Helpers\RaynetSetting::groupName() }}</div>
        <div class="ar-row"><div class="ar-label">Number of members in the group<small>Active members at time of report</small></div><div class="ar-value">{{ $stats['member_count'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Group geographical area<small>From site settings</small></div><div class="ar-value" style="font-size:13px;">{{ \App\Models\Setting::get('group_area','—') }}</div></div>
        <div class="ar-row"><div class="ar-label">Number of pre-planned user service events attended</div><div class="ar-value">{{ $stats['user_service_events'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Number of pre-planned internal group exercises attended</div><div class="ar-value">{{ $stats['exercises'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Number of live callouts received from the user services</div><div class="ar-value">{{ $stats['live_callouts'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Total events (all types)<small>From ROCK events calendar</small></div><div class="ar-value">{{ $stats['total_events'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Test alerts raised</div><div class="ar-value">{{ $stats['test_alerts'] }}</div></div>
        <div class="ar-row"><div class="ar-label">Standby alerts raised</div><div class="ar-value">{{ $stats['standby_alerts'] }}</div></div>
    </div>

    <div class="ar-card">
        <div class="ar-card-head">Additional Fields (manual entry)</div>
        <div class="ar-row">
            <div class="ar-label">Total number of member hours worked</div>
            <input type="number" class="ar-input" placeholder="0" id="member-hours">
        </div>
        <div class="ar-row">
            <div class="ar-label">Number of events where talk-through was used</div>
            <input type="number" class="ar-input" placeholder="0" id="talk-through">
        </div>
        <div class="ar-row" style="display:block;padding:.75rem 1.25rem;">
            <div class="ar-label" style="margin-bottom:.4rem;">User services involved</div>
            <textarea style="width:100%;padding:.5rem .75rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;font-family:inherit;resize:vertical;" rows="2" placeholder="e.g. Merseyside Police, North West Ambulance…" id="user-services"></textarea>
        </div>
        <div class="ar-row">
            <div class="ar-label">Most common user service</div>
            <input type="text" style="padding:.35rem .65rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;font-family:inherit;width:200px;" placeholder="e.g. Merseyside Police" id="most-common">
        </div>
        <div class="ar-row" style="display:block;padding:.75rem 1.25rem;">
            <div class="ar-label" style="margin-bottom:.4rem;">Additional information and comments</div>
            <textarea style="width:100%;padding:.5rem .75rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;font-family:inherit;resize:vertical;" rows="3" id="additional-comments"></textarea>
        </div>
    </div>

    {{-- Copy-paste output --}}
    <div style="background:#001f40;border-radius:8px;padding:1.25rem;margin-top:1rem;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-bottom:.75rem;">Copy-paste for Annual Return Form</div>
        <pre id="ar-output" style="font-family:monospace;font-size:12px;color:#7effa0;white-space:pre-wrap;margin:0;">RAYNET Annual Return — {{ \App\Helpers\RaynetSetting::groupName() }} — {{ $year }}

Members: {{ $stats['member_count'] }}
User service events: {{ $stats['user_service_events'] }}
Exercises: {{ $stats['exercises'] }}
Live callouts: {{ $stats['live_callouts'] }}
Total events: {{ $stats['total_events'] }}
Member hours: [fill in]
Talk-through events: [fill in]
User services involved: [fill in]
Most common user service: [fill in]
</pre>
        <button onclick="navigator.clipboard.writeText(document.getElementById('ar-output').textContent).then(()=>{this.textContent='✓ Copied!';setTimeout(()=>this.textContent='📋 Copy to Clipboard',1500)})" style="margin-top:.75rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;padding:.4rem .9rem;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;">📋 Copy to Clipboard</button>
    </div>
</div>
@endsection
