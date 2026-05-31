@extends('layouts.admin')
@section('title','Alert Management')
@section('content')
<style>
.ctrl-wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}
.ctrl-nav{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ctrl-nav-btn{padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;border-radius:5px;border:1px solid #dde2e8;background:#fff;color:#003366;text-decoration:none;transition:all .15s;}
.ctrl-nav-btn:hover,.ctrl-nav-btn.active{background:#003366;color:#fff;border-color:#003366;}
.ctrl-nav-btn.danger{border-color:#C8102E;color:#C8102E;}
.ctrl-nav-btn.danger.active,.ctrl-nav-btn.danger:hover{background:#C8102E;color:#fff;border-color:#C8102E;}
.ctrl-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;margin-bottom:1rem;}
.ctrl-card-head{padding:.85rem 1.25rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;display:flex;align-items:center;justify-content:space-between;}
.ctrl-card-body{padding:1.25rem;}
.alert-type-btn{padding:1rem 1.5rem;border-radius:8px;border:2px solid;cursor:pointer;font-family:inherit;font-weight:bold;font-size:14px;transition:all .15s;text-align:center;flex:1;}
.alert-type-btn.test{background:#f0f4f8;border-color:#c5d5e8;color:#003366;}
.alert-type-btn.test.sel{background:#003366;color:#fff;border-color:#003366;}
.alert-type-btn.standby{background:#fff8e1;border-color:#fde68a;color:#92400e;}
.alert-type-btn.standby.sel{background:#f59e0b;color:#fff;border-color:#f59e0b;}
.alert-type-btn.callout{background:#fef3f2;border-color:#fca5a5;color:#C8102E;}
.alert-type-btn.callout.sel{background:#C8102E;color:#fff;border-color:#C8102E;}
.resp-row{display:flex;align-items:center;gap:.75rem;padding:.6rem .85rem;border-bottom:1px solid #f0f1f3;}
.resp-row:last-child{border-bottom:none;}
.resp-tier-badge{font-size:10px;font-weight:bold;padding:.15rem .5rem;border-radius:3px;width:60px;text-align:center;}
.resp-available{background:#eef7f2;color:#1a6b3c;}
.resp-unavailable{background:#fef3f2;color:#C8102E;}
.resp-no-response{background:#f0f4f8;color:#6b7f96;}
.resp-time{font-size:11px;color:#9aa3ae;font-family:monospace;}
.resp-name{flex:1;font-size:13px;font-weight:bold;color:#001f40;}
.resp-callsign{font-family:monospace;font-size:12px;color:#003366;}
.resp-actions{display:flex;gap:.3rem;}
.resp-btn{padding:.25rem .6rem;border-radius:4px;border:1px solid;font-size:11px;font-weight:bold;cursor:pointer;font-family:inherit;}
.resp-btn.avail{background:#eef7f2;border-color:#b8ddc9;color:#1a6b3c;}
.resp-btn.unavail{background:#fef3f2;border-color:#fca5a5;color:#C8102E;}
</style>
<div class="ctrl-wrap">
    <div class="ctrl-nav">
        <a href="{{ route('admin.controller.index') }}" class="ctrl-nav-btn">📊 Overview</a>
        <a href="{{ route('admin.controller.tiers') }}" class="ctrl-nav-btn">👥 Tier Structure</a>
        <a href="{{ route('admin.controller.alerts') }}" class="ctrl-nav-btn {{ $activeAlert ? 'danger' : '' }} active">🚨 Alerts</a>
        <a href="{{ route('admin.controller.annual-return') }}" class="ctrl-nav-btn">📋 Annual Return</a>
    </div>

    @if(session('success'))<div style="background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:13px;font-weight:bold;">✓ {{ session('success') }}</div>@endif

    @if($activeAlert)
    {{-- Active alert response tracking --}}
    <div style="background:linear-gradient(135deg,#C8102E,#a00d25);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1rem;color:#fff;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
            <div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;opacity:.7;">⚡ Active Alert</div>
                <div style="font-size:1.2rem;font-weight:bold;margin:.2rem 0;">{{ $activeAlert->title }}</div>
                <div style="font-size:12px;opacity:.8;">{{ $activeAlert->message }}</div>
                <div style="font-size:11px;opacity:.6;margin-top:.3rem;">Raised {{ \Carbon\Carbon::parse($activeAlert->raised_at)->format('H:i d M Y') }} · {{ \Carbon\Carbon::parse($activeAlert->raised_at)->diffForHumans() }}</div>
            </div>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <a href="{{ route('admin.controller.alerts.summary',$activeAlert->id) }}" style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;padding:.45rem .9rem;border-radius:5px;font-size:12px;font-weight:bold;text-decoration:none;">📋 Summary</a>
                <form method="POST" action="{{ route('admin.controller.alerts.close',$activeAlert->id) }}" onsubmit="return confirm('Close this alert?')">
                    @csrf
                    <button style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:.45rem .9rem;border-radius:5px;font-size:12px;font-weight:bold;cursor:pointer;">✓ Close Alert</button>
                </form>
            </div>
        </div>
    </div>

    <div class="ctrl-card">
        <div class="ctrl-card-head">
            Response Tracker
            <span style="font-size:11px;font-weight:normal;color:#6b7f96;">{{ $responses->where('response','available')->count() }}/{{ $responses->count() }} responded</span>
        </div>
        @php
        $tiers = ['tier1'=>'Tier 1','tier2'=>'Tier 2','tier3'=>'Tier 3','support'=>'Support','standby'=>'Standby'];
        @endphp
        @foreach($tiers as $key => $label)
        @php $tierResponses = $responses->where('tier',$key); @endphp
        @if($tierResponses->count())
        <div style="padding:.5rem .85rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;display:flex;justify-content:space-between;">
            <span>{{ $label }}</span>
            <span>{{ $tierResponses->where('response','available')->count() }}/{{ $tierResponses->count() }}</span>
        </div>
        @foreach($tierResponses as $r)
        <div class="resp-row" id="resp-{{ $r->id }}">
            <span class="resp-callsign">{{ $r->callsign ?: '—' }}</span>
            <span class="resp-name">{{ $r->name }}</span>
            <span class="resp-time">{{ $r->responded_at ? \Carbon\Carbon::parse($r->responded_at)->format('H:i') : '—' }}</span>
            <span class="resp-tier-badge resp-{{ $r->response }}">{{ ['available'=>'✓ Yes','unavailable'=>'✕ No','no_response'=>'…'][$r->response] }}</span>
            <div class="resp-actions">
                <button class="resp-btn avail" onclick="markResp({{ $r->id }},'available')">✓</button>
                <button class="resp-btn unavail" onclick="markResp({{ $r->id }},'unavailable')">✕</button>
            </div>
        </div>
        @endforeach
        @endif
        @endforeach
    </div>

    @else
    {{-- Raise alert form --}}
    <div class="ctrl-card">
        <div class="ctrl-card-head">🚨 Raise Alert</div>
        <div class="ctrl-card-body">
            <form method="POST" action="{{ route('admin.controller.alerts.raise') }}" id="alert-form">
                @csrf
                <input type="hidden" name="type" id="alert-type" value="test">
                <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;">
                    <button type="button" class="alert-type-btn test sel" id="btn-test" onclick="setAlertType('test')">🔵 Test Alert<br><small style="font-weight:normal;font-size:11px;">Status → Level 4</small></button>
                    <button type="button" class="alert-type-btn standby" id="btn-standby" onclick="setAlertType('standby')">🟡 Standby<br><small style="font-weight:normal;font-size:11px;">Status → Level 2</small></button>
                    <button type="button" class="alert-type-btn callout" id="btn-callout" onclick="setAlertType('callout')">🔴 Live Callout<br><small style="font-weight:normal;font-size:11px;">Status → Level 1</small></button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                    <div>
                        <label style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;display:block;margin-bottom:3px;">Title</label>
                        <input type="text" name="title" placeholder="e.g. Test Alert — Sunday Exercise" style="width:100%;padding:.5rem .75rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;font-family:inherit;">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;display:block;margin-bottom:3px;">Tier Scope</label>
                        <div style="display:flex;gap:.3rem;flex-wrap:wrap;padding-top:4px;">
                            @foreach(['tier1'=>'T1','tier2'=>'T2','tier3'=>'T3','support'=>'Sup','standby'=>'Stby'] as $v => $l)
                            <label style="display:flex;align-items:center;gap:3px;font-size:11px;cursor:pointer;">
                                <input type="checkbox" name="tier_scope[]" value="{{ $v }}" checked> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="margin-bottom:.75rem;">
                    <label style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;display:block;margin-bottom:3px;">Message</label>
                    <textarea name="message" rows="2" placeholder="Additional details for members…" style="width:100%;padding:.5rem .75rem;border:1px solid #dde2e8;border-radius:4px;font-size:13px;outline:none;font-family:inherit;resize:vertical;"></textarea>
                </div>
                <button type="submit" id="raise-btn" style="padding:.7rem 1.5rem;background:#C8102E;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:bold;cursor:pointer;">🚨 Raise Alert & Notify Members</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Alert History --}}
    @if($history->count())
    <div class="ctrl-card">
        <div class="ctrl-card-head">Alert History</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead><tr style="background:#f8f9fb;">
                <th style="padding:.6rem .85rem;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;border-bottom:1px solid #dde2e8;">Title</th>
                <th style="padding:.6rem .85rem;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;border-bottom:1px solid #dde2e8;">Type</th>
                <th style="padding:.6rem .85rem;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;border-bottom:1px solid #dde2e8;">Raised</th>
                <th style="padding:.6rem .85rem;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;border-bottom:1px solid #dde2e8;">Closed</th>
                <th style="padding:.6rem .85rem;border-bottom:1px solid #dde2e8;"></th>
            </tr></thead>
            <tbody>
            @foreach($history as $a)
            <tr style="border-bottom:1px solid #f0f1f3;">
                <td style="padding:.6rem .85rem;font-weight:bold;">{{ $a->title }}</td>
                <td style="padding:.6rem .85rem;"><span style="font-size:10px;font-weight:bold;padding:.15rem .5rem;border-radius:3px;background:#f0f4f8;color:#003366;">{{ strtoupper($a->type) }}</span></td>
                <td style="padding:.6rem .85rem;color:#6b7f96;font-size:12px;">{{ \Carbon\Carbon::parse($a->raised_at)->format('j M Y H:i') }}</td>
                <td style="padding:.6rem .85rem;color:#6b7f96;font-size:12px;">{{ $a->closed_at ? \Carbon\Carbon::parse($a->closed_at)->format('j M Y H:i') : '—' }}</td>
                <td style="padding:.6rem .85rem;"><a href="{{ route('admin.controller.alerts.summary',$a->id) }}" style="font-size:11px;color:#003366;font-weight:bold;text-decoration:none;">Summary →</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
<script>
function setAlertType(type) {
    document.getElementById('alert-type').value = type;
    ['test','standby','callout'].forEach(t => {
        document.getElementById('btn-'+t).className = 'alert-type-btn '+t+(t===type?' sel':'');
    });
    const btn = document.getElementById('raise-btn');
    const colors = {test:'#003366',standby:'#f59e0b',callout:'#C8102E'};
    btn.style.background = colors[type];
}
const CSRF = document.querySelector('meta[name=csrf-token]')?.content||'';
function markResp(id, response) {
    fetch('{{ route("admin.controller.alerts.respond",":id") }}'.replace(':id',id), {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body:JSON.stringify({response})
    }).then(()=>{
        const row = document.getElementById('resp-'+id);
        const badge = row.querySelector('.resp-tier-badge');
        const time = row.querySelector('.resp-time');
        badge.className = 'resp-tier-badge resp-'+response;
        badge.textContent = {available:'✓ Yes',unavailable:'✕ No',no_response:'…'}[response];
        if (response!=='no_response') time.textContent = new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
    });
}
</script>
@endsection
