@extends('layouts.app')
@section('title', 'Event Support Packs')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*{box-sizing:border-box;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;}
.btn{padding:.5rem 1.25rem;border-radius:999px;font-size:.88rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;}
.btn-primary{background:var(--red);color:#fff;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.stat-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;}
@media(max-width:700px){.stat-cards{grid-template-columns:1fr 1fr;}}
.stat-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:1.25rem;text-align:center;}
.stat-num{font-size:2rem;font-weight:800;color:var(--navy);}
.stat-label{font-size:.78rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;}
.esp-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;}
.esp-table th{background:var(--navy);color:#fff;padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;}
.esp-table td{padding:.7rem 1rem;border-bottom:1px solid var(--grey-mid);font-size:.88rem;}
.esp-table tr:last-child td{border-bottom:none;}
.esp-table tr:hover td{background:#f8f9fb;}
.rag-pill{padding:.2rem .65rem;border-radius:999px;font-size:.75rem;font-weight:bold;display:inline-block;}
.rag-green{background:#d1fae5;color:#065f46;}
.rag-amber{background:#fef3c7;color:#92400e;}
.rag-red{background:#fee2e2;color:#991b1b;}
.status-pill{padding:.2rem .6rem;border-radius:4px;font-size:.72rem;font-weight:bold;display:inline-block;}
.status-draft{background:#f3f4f6;color:#6b7280;}
.status-awaiting_review{background:#fef3c7;color:#92400e;}
.status-approved{background:#d1fae5;color:#065f46;}
.status-approved_with_controls{background:#d1fae5;color:#065f46;}
.status-escalated{background:#fee2e2;color:#991b1b;}
.status-returned{background:#fef3c7;color:#92400e;}
.status-closed{background:#f3f4f6;color:#6b7280;}
.status-cancelled{background:#f3f4f6;color:#6b7280;}
.template-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-top:1rem;}
.template-card{background:#fff;border:1px solid var(--grey-mid);border-radius:8px;padding:1rem;cursor:pointer;text-decoration:none;transition:all .15s;display:block;}
.template-card:hover{border-color:var(--navy);transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,51,102,.1);}
.template-name{font-size:.9rem;font-weight:700;color:var(--navy);margin-bottom:.25rem;}
.template-desc{font-size:.75rem;color:var(--muted);}
</style>

<div class="wrap">
    <div class="page-head">
        <div>
            <div class="eyebrow">Members Area</div>
            <h1 class="page-title">📋 Event Support Packs</h1>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="{{ route('event-pack.create') }}" class="btn btn-primary">+ New Event Pack</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;border-left:3px solid #059669;padding:.75rem 1rem;border-radius:4px;margin-bottom:1rem;font-size:.88rem;color:#065f46;font-weight:bold;">✓ {{ session('success') }}</div>
    @endif

    {{-- Stat cards --}}
    <div class="stat-cards">
        <div class="stat-card" style="border-top:3px solid var(--navy);">
            <div class="stat-num">{{ $stats['upcoming'] }}</div>
            <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #f59e0b;">
            <div class="stat-num" style="color:#f59e0b;">{{ $stats['amber'] }}</div>
            <div class="stat-label">Amber Events</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #dc2626;">
            <div class="stat-num" style="color:#dc2626;">{{ $stats['red'] }}</div>
            <div class="stat-label">Red / Escalated</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #059669;">
            <div class="stat-num" style="color:#059669;">{{ $stats['help'] }}</div>
            <div class="stat-label">Help Requested</div>
        </div>
    </div>

    @if($packs->isEmpty())
    <div style="background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:2.5rem;margin-bottom:2rem;">
        <div style="text-align:center;padding:1rem 0 2rem;color:var(--muted);">
            <div style="font-size:3rem;opacity:.2;margin-bottom:.75rem;">📋</div>
            <p style="font-size:.95rem;">No event support packs yet.</p>
        </div>
        <div style="font-size:.9rem;font-weight:700;color:var(--navy);margin-bottom:.75rem;">Start from a template:</div>
        <div class="template-grid">
            @foreach([
                ['walking','🚶','Walking Event','Outdoor route, checkpoints, welfare, road exposure'],
                ['static','🎪','Static Public Event','Control point, public interaction, welfare'],
                ['rural','🌿','Rural Checkpoint','Remote posts, access, weather, lone working'],
                ['urban_parade','🏙️','Urban Parade','Road exposure, public order, moving event'],
                ['training','📚','Training Exercise','Internal, objectives, lower public exposure'],
                ['emergency','🚨','Emergency Exercise','Multi-agency, formal comms, high documentation'],
                ['radio_only','📻','Radio Support Only','Comms plan, operator schedule'],
                ['multi_site','🗺️','Multi-Site Event','Talk-through, outstations, coordination'],
            ] as [$key,$icon,$name,$desc])
            <a href="{{ route('event-pack.create', ['template'=>$key]) }}" class="template-card">
                <div style="font-size:1.5rem;margin-bottom:.4rem;">{{ $icon }}</div>
                <div class="template-name">{{ $name }}</div>
                <div class="template-desc">{{ $desc }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <table class="esp-table">
        <thead><tr>
            <th>Event Name</th>
            <th>Date</th>
            <th>Group</th>
            <th>Controller</th>
            <th>User Service</th>
            <th>RAG</th>
            <th>Status</th>
            <th>Help?</th>
            <th>Documents</th>
            <th>Updated</th>
            <th></th>
        </tr></thead>
        <tbody>
            @foreach($packs as $pack)
            <tr>
                <td><strong><a href="{{ route('event-pack.show', $pack) }}" style="color:var(--navy);text-decoration:none;">{{ $pack->event_name }}</a></strong>
                    @if($pack->cloned_from)<div style="font-size:.7rem;color:var(--muted);">(cloned)</div>@endif
                </td>
                <td style="white-space:nowrap;">{{ $pack->event_date->format('d M Y') }}</td>
                <td style="font-size:.78rem;">{{ $pack->group_ref }}</td>
                <td style="font-family:monospace;font-size:.85rem;">{{ $pack->controller_callsign ? strtoupper($pack->controller_callsign) : '—' }}</td>
                <td style="font-size:.82rem;">{{ $pack->organiser_name ?? '—' }}</td>
                <td>
                    @if($pack->rag_status)
                        <span class="rag-pill rag-{{ $pack->rag_status }}">{{ strtoupper($pack->rag_status) }}</span>
                    @else<span style="color:var(--muted);font-size:.8rem;">—</span>@endif
                </td>
                <td><span class="status-pill status-{{ $pack->status }}">{{ $pack->statusLabel() }}</span></td>
                <td style="text-align:center;">
                    @if($pack->assistance_visible)
                        <span style="color:#059669;font-weight:bold;font-size:.8rem;">✓ Public</span>
                    @elseif($pack->assistance_contact)
                        <span style="color:#92400e;font-size:.8rem;">Private</span>
                    @else
                        <span style="color:var(--muted);font-size:.8rem;">—</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    @php $docCount = $pack->documents_count ?? 0; @endphp
                    @if($docCount > 0)
                        <span style="color:#059669;font-size:.8rem;font-weight:bold;">{{ $docCount }} ✓</span>
                    @else
                        <span style="color:#dc2626;font-size:.78rem;">None</span>
                    @endif
                </td>
                <td style="font-size:.78rem;color:var(--muted);white-space:nowrap;">{{ $pack->updated_at->format('d M H:i') }}</td>
                <td>
                    <div style="display:flex;gap:.3rem;">
                        <a href="{{ route('event-pack.show', $pack) }}" style="background:#e8eef5;color:var(--navy);padding:.3rem .65rem;border-radius:4px;font-size:.75rem;font-weight:bold;text-decoration:none;">View</a>
                        <form method="POST" action="{{ route('event-pack.clone', $pack) }}" style="display:inline;">
                            @csrf <button type="submit" style="background:#f3f4f6;color:#6b7280;border:none;padding:.3rem .65rem;border-radius:4px;font-size:.75rem;font-weight:bold;cursor:pointer;">Clone</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:1rem;">{{ $packs->links() }}</div>
    @endif
</div>
@endsection
