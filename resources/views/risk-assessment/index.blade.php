@extends('layouts.app')
@section('title', 'Risk Assessments')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*{box-sizing:border-box;}
.wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.btn{padding:.5rem 1.25rem;border-radius:999px;font-size:.88rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;}
.btn-primary{background:var(--red);color:#fff;}
.ra-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;}
.ra-table th{background:var(--navy);color:#fff;padding:.65rem 1rem;text-align:left;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;}
.ra-table td{padding:.7rem 1rem;border-bottom:1px solid var(--grey-mid);font-size:.88rem;}
.ra-table tr:last-child td{border-bottom:none;}
.rag-pill{padding:.2rem .65rem;border-radius:999px;font-size:.75rem;font-weight:bold;}
.rag-green{background:#d1fae5;color:#065f46;}
.rag-amber{background:#fef3c7;color:#92400e;}
.rag-red{background:#fee2e2;color:#991b1b;}
.status-pill{padding:.2rem .6rem;border-radius:4px;font-size:.72rem;font-weight:bold;}
.status-draft{background:#f3f4f6;color:#6b7280;}
.status-approved{background:#d1fae5;color:#065f46;}
.status-pending{background:#fef3c7;color:#92400e;}
</style>
<div class="wrap">
    <div class="page-head">
        <div>
            <div style="font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;">Members Area</div>
            <h1 class="page-title">📋 Risk Assessments</h1>
        </div>
        <a href="{{ route('risk-assessment.create') }}" class="btn btn-primary">+ New Assessment</a>
    </div>

    @if($assessments->isEmpty())
        <div style="text-align:center;padding:4rem;color:var(--muted);background:#fff;border:1px solid var(--grey-mid);border-radius:8px;">
            <div style="font-size:3rem;margin-bottom:.75rem;opacity:.3;">📋</div>
            <p>No risk assessments yet. <a href="{{ route('risk-assessment.create') }}" style="color:var(--red);font-weight:bold;">Create your first one →</a></p>
        </div>
    @else
        <table class="ra-table">
            <thead><tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>RAG Status</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr></thead>
            <tbody>
                @foreach($assessments as $ra)
                <tr>
                    <td><strong>{{ $ra->event_name }}</strong></td>
                    <td>{{ $ra->event_date?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $ra->location ?? '—' }}</td>
                    <td>
                        @if($ra->rag_status)
                            <span class="rag-pill rag-{{ $ra->rag_status }}">{{ strtoupper($ra->rag_status) }}</span>
                        @else—@endif
                    </td>
                    <td><span class="status-pill status-{{ $ra->status }}">{{ ucfirst($ra->status) }}</span></td>
                    <td>{{ $ra->created_at->format('d M Y') }}</td>
                    <td style="display:flex;gap:.35rem;">
                        <a href="{{ route('risk-assessment.show', $ra) }}" style="background:#e8eef5;color:var(--navy);padding:.3rem .65rem;border-radius:4px;font-size:.75rem;font-weight:bold;text-decoration:none;">View</a>
                        <a href="{{ route('risk-assessment.pdf', $ra) }}" style="background:#d1fae5;color:#065f46;padding:.3rem .65rem;border-radius:4px;font-size:.75rem;font-weight:bold;text-decoration:none;">PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $assessments->links() }}</div>
    @endif
</div>
@endsection
