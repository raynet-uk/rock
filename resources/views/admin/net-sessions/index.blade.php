@extends('layouts.admin')
@section('title', 'Net Sessions')
@section('content')
<style>
.ns-wrap{max-width:960px;margin:0 auto;padding:2rem 1rem 4rem;}
.ns-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
.ns-title{font-size:1.4rem;font-weight:800;color:#003366;}
.ns-sub{font-size:.85rem;color:#6b7f96;margin-top:.2rem;}
.btn-primary{background:#003366;color:#fff;border:none;padding:.55rem 1.25rem;border-radius:999px;font-size:.88rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;}
.btn-sm{padding:.35rem .8rem;font-size:.78rem;}
.btn-red{background:#dc2626;}
.btn-green{background:#059669;}
.btn-amber{background:#d97706;}
.alert-success{background:#d1fae5;border-left:3px solid #059669;padding:.65rem 1rem;border-radius:4px;font-size:.88rem;color:#065f46;font-weight:bold;margin-bottom:1rem;}
.net-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;padding:1.25rem;margin-bottom:1rem;position:relative;}
.net-card.live{border-color:#C8102E;box-shadow:0 0 0 2px rgba(200,16,46,.15);}
.net-card.upcoming{border-color:#d97706;box-shadow:0 0 0 2px rgba(217,119,6,.1);}
.net-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.net-name{font-size:1rem;font-weight:800;color:#003366;}
.net-callsign{font-family:monospace;font-size:.9rem;color:#C8102E;font-weight:700;}
.net-badges{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;}
.badge{font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:999px;text-transform:uppercase;letter-spacing:.07em;}
.badge-green{background:#d1fae5;color:#065f46;}
.badge-red{background:#fee2e2;color:#991b1b;}
.badge-amber{background:#fef3c7;color:#92400e;}
.badge-blue{background:#dbeafe;color:#1e40af;}
.badge-grey{background:#f1f5f9;color:#64748b;}
.badge-live{background:#C8102E;color:#fff;animation:livePulse 1.5s ease-in-out infinite;}
@keyframes livePulse{0%,100%{opacity:1;}50%{opacity:.7;}}
.net-meta{display:flex;gap:1.5rem;margin-top:.75rem;flex-wrap:wrap;}
.net-meta-item{font-size:.8rem;color:#6b7f96;}
.net-meta-item strong{color:#334155;font-weight:600;}
.net-actions{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.75rem;}
.live-banner{background:#0a0a1a;color:#ff4466;font-size:.72rem;font-weight:800;padding:.3rem .75rem;border-radius:999px;letter-spacing:.1em;text-transform:uppercase;display:inline-flex;align-items:center;gap:.4rem;}
.empty-state{text-align:center;padding:3rem;color:#6b7f96;}
</style>

<div class="ns-wrap">
    <div class="ns-head">
        <div>
            <div class="ns-title">📻 Net Sessions</div>
            <div class="ns-sub">Schedule recurring and one-off nets. The homepage banner activates automatically.</div>
        </div>
        <a href="{{ route('admin.net-sessions.create') }}" class="btn-primary">+ New Net Session</a>
    </div>

    @if(session('success'))
    <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    @php $currentNet = \App\Models\NetSession::getCurrentNet(); @endphp

    @if($currentNet)
    <div style="background:#0a0a1a;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="width:10px;height:10px;background:#C8102E;border-radius:50%;display:inline-block;animation:livePulse 1.5s ease-in-out infinite;"></span>
            <span style="color:#ff4466;font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.15em;">Live Now</span>
        </div>
        <span style="color:#fff;font-weight:800;font-family:monospace;">{{ strtoupper($currentNet->callsign) }}</span>
        @if($currentNet->frequency)<span style="color:#C8102E;font-family:monospace;font-size:.9rem;">{{ $currentNet->frequency }}</span>@endif
        @if($currentNet->manual_active)<span class="badge badge-amber">Manual Override</span>@endif
    </div>
    @endif

    @forelse($nets as $net)
    @php
        $isLive     = $net->isOnAirNow();
        $isUpcoming = $net->isUpcomingToday();
    @endphp
    <div class="net-card {{ $isLive ? 'live' : ($isUpcoming ? 'upcoming' : '') }}">
        <div class="net-card-head">
            <div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                    <span class="net-name">{{ $net->name }}</span>
                    <span class="net-callsign">{{ strtoupper($net->callsign) }}</span>
                    @if($isLive)<span class="badge badge-live">● Live</span>@endif
                    @if($isUpcoming && !$isLive)<span class="badge badge-amber">Today ↑</span>@endif
                </div>
                <div class="net-badges">
                    @if($net->active)<span class="badge badge-green">Active</span>@else<span class="badge badge-grey">Inactive</span>@endif
                    @if($net->manual_active)<span class="badge badge-red">Manual Override</span>@endif
                    @if($net->is_public)<span class="badge badge-blue">Public</span>@else<span class="badge badge-grey">Members Only</span>@endif
                    @if($net->is_recurring)
                        <span class="badge badge-blue">Recurring</span>
                    @else
                        <span class="badge badge-grey">One-off</span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:1.1rem;font-weight:800;font-family:monospace;color:#003366;">
                    {{ \Carbon\Carbon::createFromTimeString($net->start_time)->format('H:i') }}
                    @if($net->end_time)– {{ \Carbon\Carbon::createFromTimeString($net->end_time)->format('H:i') }}@endif
                </div>
                @if($net->is_recurring)
                <div style="font-size:.75rem;color:#6b7f96;margin-top:.2rem;">
                    {{ implode(', ', array_map(fn($d) => substr(\App\Models\NetSession::dayNames()[$d],0,3), $net->days_of_week ?? [])) }}
                </div>
                @elseif($net->specific_date)
                <div style="font-size:.75rem;color:#6b7f96;margin-top:.2rem;">
                    {{ \Carbon\Carbon::parse($net->specific_date)->format('d M Y') }}
                </div>
                @endif
            </div>
        </div>

        <div class="net-meta">
            @if($net->frequency)<div class="net-meta-item"><strong>Freq:</strong> {{ $net->frequency }}</div>@endif
            @if($net->controller)<div class="net-meta-item"><strong>Controller:</strong> {{ $net->controller }}</div>@endif
            @if($net->description)<div class="net-meta-item" style="flex:1;">{{ $net->description }}</div>@endif
        </div>

        <div class="net-actions">
            <a href="{{ route('admin.net-sessions.edit', $net) }}" class="btn-primary btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.net-sessions.toggle-manual', $net) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary btn-sm {{ $net->manual_active ? 'btn-amber' : 'btn-green' }}">
                    {{ $net->manual_active ? '⏹ Remove Override' : '▶ Force Live Now' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.net-sessions.destroy', $net) }}" style="display:inline;" onsubmit="return confirm('Delete this net session?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-primary btn-sm btn-red">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div style="font-size:2rem;margin-bottom:.5rem;">📻</div>
        <div style="font-weight:700;margin-bottom:.25rem;">No net sessions yet</div>
        <div style="font-size:.85rem;">Create your first scheduled net to get started.</div>
    </div>
    @endforelse
</div>
@endsection
