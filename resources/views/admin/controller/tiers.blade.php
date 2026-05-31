@extends('layouts.admin')
@section('title','Tier Structure')
@section('content')
<style>
.ctrl-wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}
.ctrl-nav{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ctrl-nav-btn{padding:.55rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;border-radius:5px;border:1px solid #dde2e8;background:#fff;color:#003366;text-decoration:none;transition:all .15s;}
.ctrl-nav-btn:hover,.ctrl-nav-btn.active{background:#003366;color:#fff;border-color:#003366;}
.ctrl-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;overflow:hidden;margin-bottom:1rem;}
.ctrl-card-head{padding:.85rem 1.25rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;}
.ctrl-card-body{padding:1.25rem;}
.tier-badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:10px;font-weight:bold;}
.tier-t1{background:#fef3f2;border:1px solid #fca5a5;color:#C8102E;}
.tier-t2{background:#fff8e1;border:1px solid #fde68a;color:#92400e;}
.tier-t3{background:#f0f4f8;border:1px solid #c5d5e8;color:#003366;}
.tier-support{background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;}
.tier-standby{background:#f5f3ff;border:1px solid #c4b5fd;color:#5b21b6;}
.tier-table{width:100%;border-collapse:collapse;font-size:13px;}
.tier-table th{background:#f8f9fb;padding:.6rem .85rem;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;border-bottom:1px solid #dde2e8;}
.tier-table td{padding:.6rem .85rem;border-bottom:1px solid #f0f1f3;vertical-align:middle;}
.tier-table tr:last-child td{border-bottom:none;}
.form-row{display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;}
.form-field-sm{display:flex;flex-direction:column;gap:3px;}
.form-field-sm label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;}
.form-field-sm select,.form-field-sm input{padding:.45rem .7rem;border:1px solid #dde2e8;border-radius:4px;font-size:12px;outline:none;font-family:inherit;}
.btn-add{padding:.45rem 1rem;background:#003366;color:#fff;border:none;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;}
.btn-del{background:none;border:none;color:#C8102E;cursor:pointer;font-size:11px;font-weight:bold;font-family:inherit;padding:.2rem .4rem;}
</style>
<div class="ctrl-wrap">
    <div class="ctrl-nav">
        <a href="{{ route('admin.controller.index') }}" class="ctrl-nav-btn">📊 Overview</a>
        <a href="{{ route('admin.controller.tiers') }}" class="ctrl-nav-btn active">👥 Tier Structure</a>
        <a href="{{ route('admin.controller.alerts') }}" class="ctrl-nav-btn">🚨 Alerts</a>
        <a href="{{ route('admin.controller.annual-return') }}" class="ctrl-nav-btn">📋 Annual Return</a>
    </div>

    @if(session('success'))<div style="background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:13px;font-weight:bold;">✓ {{ session('success') }}</div>@endif

    {{-- Add member form --}}
    <div class="ctrl-card">
        <div class="ctrl-card-head">Add Member to Tier</div>
        <div class="ctrl-card-body">
            <form method="POST" action="{{ route('admin.controller.tiers.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-field-sm">
                        <label>Member</label>
                        <select name="user_id" required style="min-width:180px;">
                            <option value="">— Select —</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}{{ $m->callsign ? ' ('.$m->callsign.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field-sm">
                        <label>Tier</label>
                        <select name="tier" required>
                            <option value="tier1">Tier 1 — Primary</option>
                            <option value="tier2">Tier 2 — Secondary</option>
                            <option value="tier3">Tier 3 — Tertiary</option>
                            <option value="support">Support</option>
                            <option value="standby">Standby</option>
                        </select>
                    </div>
                    <div class="form-field-sm">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-field-sm">
                        <label>End Date (optional)</label>
                        <input type="date" name="end_date">
                    </div>
                    <div class="form-field-sm">
                        <label>Notes</label>
                        <input type="text" name="notes" placeholder="Optional…" style="width:160px;">
                    </div>
                    <button type="submit" class="btn-add">+ Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Current assignments --}}
    <div class="ctrl-card">
        <div class="ctrl-card-head">Current Tier Assignments</div>
        <table class="tier-table">
            <thead><tr><th>Member</th><th>Callsign</th><th>Tier</th><th>From</th><th>Until</th><th>Notes</th><th></th></tr></thead>
            <tbody>
            @forelse($current as $row)
            <tr>
                <td style="font-weight:bold;">{{ $row->name }}</td>
                <td><code>{{ $row->callsign ?: '—' }}</code></td>
                <td>
                    @php $cls = ['tier1'=>'tier-t1','tier2'=>'tier-t2','tier3'=>'tier-t3','support'=>'tier-support','standby'=>'tier-standby'][$row->tier] ?? ''; @endphp
                    <span class="tier-badge {{ $cls }}">{{ str_replace(['tier1','tier2','tier3','support','standby'],['Tier 1','Tier 2','Tier 3','Support','Standby'],$row->tier) }}</span>
                </td>
                <td>{{ \Carbon\Carbon::parse($row->start_date)->format('j M Y') }}</td>
                <td>{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('j M Y') : '<span style="color:#1a6b3c;font-size:11px;">Current</span>' }}</td>
                <td style="color:#6b7f96;font-size:12px;">{{ $row->notes ?: '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.controller.tiers.delete',$row->id) }}" onsubmit="return confirm('Remove this tier assignment?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del">✕</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#9aa3ae;font-style:italic;padding:1.5rem;">No current tier assignments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($history->count())
    <div class="ctrl-card">
        <div class="ctrl-card-head">Historical Assignments</div>
        <table class="tier-table">
            <thead><tr><th>Member</th><th>Callsign</th><th>Tier</th><th>From</th><th>Until</th><th>Notes</th></tr></thead>
            <tbody>
            @foreach($history as $row)
            <tr style="opacity:.6;">
                <td>{{ $row->name }}</td>
                <td><code>{{ $row->callsign ?: '—' }}</code></td>
                <td>{{ str_replace(['tier1','tier2','tier3','support','standby'],['Tier 1','Tier 2','Tier 3','Support','Standby'],$row->tier) }}</td>
                <td>{{ \Carbon\Carbon::parse($row->start_date)->format('j M Y') }}</td>
                <td>{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('j M Y') : '—' }}</td>
                <td style="color:#6b7f96;font-size:12px;">{{ $row->notes ?: '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
