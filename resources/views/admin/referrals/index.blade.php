@extends('layouts.admin')
@section('title', 'Member Referrals')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}
.page-head{margin-bottom:1.5rem;}
.page-eyebrow{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;}
.stat-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1rem 1.2rem;box-shadow:0 1px 3px rgba(0,51,102,.08);}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);margin-bottom:.3rem;}
.stat-value{font-size:2rem;font-weight:bold;color:var(--navy);}
.stat-sub{font-size:.75rem;color:var(--muted);margin-top:.2rem;}
.ref-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--grey-mid);}
.ref-table th{background:var(--navy);color:#fff;padding:.6rem 1rem;font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;text-align:left;}
.ref-table td{padding:.75rem 1rem;border-bottom:1px solid var(--grey-mid);font-size:.88rem;color:var(--text);vertical-align:middle;}
.ref-table tr:last-child td{border-bottom:none;}
.ref-table tr:hover td{background:var(--grey);}
.referrer-group{margin-bottom:2rem;}
.referrer-head{background:var(--navy);color:#fff;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;border-radius:6px 6px 0 0;}
.referrer-name{font-weight:bold;font-size:.95rem;}
.referrer-count{font-size:.8rem;opacity:.7;}
</style>
<div class="wrap">
    <div class="page-head">
        <div class="page-eyebrow">Admin Panel</div>
        <h1 class="page-title">📡 Member Referrals</h1>
    </div>
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-label">Total Invites Sent</div>
            <div class="stat-value">{{ $totalReferrals }}</div>
            <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Referrers</div>
            <div class="stat-value">{{ $referrerCount }}</div>
            <div class="stat-sub">Unique members who referred</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--red);">
            <div class="stat-label">This Month</div>
            <div class="stat-value" style="color:var(--red);">{{ $thisMonth }}</div>
            <div class="stat-sub">Invites sent in {{ now()->format('F Y') }}</div>
        </div>
    </div>
    @if($referrals->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);background:#fff;border:1px solid var(--grey-mid);border-radius:8px;">
            No referrals yet. Members can send invites from the <a href="{{ route('members.refer') }}" style="color:var(--red);">Members Area</a>.
        </div>
    @else
        @foreach($byReferrer as $referrerId => $group)
            @php $referrer = $group->first()->referrer; @endphp
            <div class="referrer-group">
                <div class="referrer-head">
                    <div>
                        <div class="referrer-name">
                            {{ $referrer?->name ?? 'Unknown' }}
                            @if($referrer?->callsign)
                                <span style="font-family:monospace;font-size:.85rem;opacity:.8;">({{ strtoupper($referrer->callsign) }})</span>
                            @endif
                        </div>
                    </div>
                    <div class="referrer-count">{{ $group->count() }} {{ Str::plural('invite', $group->count()) }} sent</div>
                </div>
                <table class="ref-table" style="border-radius:0 0 6px 6px;overflow:hidden;">
                    <thead>
                        <tr>
                            <th>Callsign</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Sent</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->sortByDesc('sent_at') as $ref)
                        <tr>
                            <td><span style="font-family:monospace;font-weight:bold;">{{ strtoupper($ref->callsign) }}</span></td>
                            <td>{{ $ref->name ?: '—' }}</td>
                            <td style="color:var(--muted);">{{ $ref->email }}</td>
                            <td style="color:var(--muted);font-size:.82rem;">{{ $ref->sent_at->format('d M Y H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.super.admin.referrals.destroy', $ref) }}" onsubmit="return confirm('Delete this referral record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:none;border:1px solid #fca5a5;color:#dc2626;padding:.3rem .7rem;border-radius:4px;font-size:.75rem;cursor:pointer;">✕ Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
@endsection
