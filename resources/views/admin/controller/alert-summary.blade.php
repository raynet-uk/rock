@extends('layouts.admin')
@section('title','Alert Summary')
@section('content')
<div style="max-width:700px;margin:2rem auto;padding:0 1.5rem 5rem;">
    <a href="{{ route('admin.controller.alerts') }}" style="font-size:12px;color:#003366;text-decoration:none;font-weight:bold;">← Back to Alerts</a>
    <h1 style="font-size:1.4rem;font-weight:bold;color:#001f40;margin:1rem 0 .25rem;">{{ $alert->title }}</h1>
    <div style="font-size:12px;color:#6b7f96;margin-bottom:1.5rem;">{{ ucfirst($alert->type) }} · Raised {{ \Carbon\Carbon::parse($alert->raised_at)->format('j M Y H:i') }}{{ $alert->closed_at ? ' · Closed '.\Carbon\Carbon::parse($alert->closed_at)->format('H:i') : '' }}</div>

    @foreach($summary as $key => $s)
    <div style="background:#fff;border:1px solid #dde2e8;border-radius:8px;padding:1rem 1.25rem;margin-bottom:.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-weight:bold;font-size:14px;color:#001f40;">{{ $s['label'] }}</span>
            <span style="font-size:1.25rem;font-weight:bold;color:{{ $s['pct']>=80?'#1a6b3c':($s['pct']>=50?'#92400e':'#C8102E') }};">{{ $s['pct'] }}%</span>
        </div>
        <div style="background:#f0f4f8;border-radius:4px;height:6px;margin-bottom:.5rem;">
            <div style="background:{{ $s['pct']>=80?'#1a6b3c':($s['pct']>=50?'#f59e0b':'#C8102E') }};height:6px;border-radius:4px;width:{{ $s['pct'] }}%;"></div>
        </div>
        <div style="font-size:12px;color:#6b7f96;">{{ $s['available'] }}/{{ $s['total'] }} responded{{ $s['avg_min'] ? ' · avg '.($s['avg_min']).' min' : '' }}</div>
        @if($s['callsigns']->count())
        <div style="margin-top:.4rem;font-family:monospace;font-size:12px;color:#003366;">{{ $s['callsigns']->join(', ') }}</div>
        @endif
    </div>
    @endforeach

    {{-- Copy-paste summary --}}
    <div style="background:#001f40;border-radius:8px;padding:1.25rem;margin-top:1.5rem;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-bottom:.75rem;">Copy-paste Summary</div>
        <pre id="summary-text" style="font-family:monospace;font-size:12px;color:#7effa0;white-space:pre-wrap;margin:0;">{{ \App\Helpers\RaynetSetting::groupName() }} — Alert Response
{{ ucfirst($alert->type) }} · {{ \Carbon\Carbon::parse($alert->raised_at)->format('j M Y H:i') }}

@foreach($summary as $s){{ $s['label'] }}: {{ $s['pct'] }}% ({{ $s['available'] }}/{{ $s['total'] }})
@endforeach
Callsigns responded:
@foreach($summary as $s)@if($s['callsigns']->count()){{ $s['callsigns']->join(', ') }}
@endif@endforeach</pre>
        <button onclick="navigator.clipboard.writeText(document.getElementById('summary-text').textContent).then(()=>{this.textContent='✓ Copied!';setTimeout(()=>this.textContent='📋 Copy',1500)})" style="margin-top:.75rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;padding:.4rem .9rem;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;">📋 Copy</button>
    </div>
</div>
@endsection
