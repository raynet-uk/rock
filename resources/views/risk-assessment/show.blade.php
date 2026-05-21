@extends('layouts.app')
@section('title', 'Risk Assessment — {{ $riskAssessment->event_name }}')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*{box-sizing:border-box;}
.wrap{max-width:1000px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.btn{padding:.5rem 1.25rem;border-radius:999px;font-size:.88rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;}
.btn-primary{background:var(--red);color:#fff;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.risk-table{width:100%;border-collapse:collapse;font-size:.88rem;}
.risk-table th{background:var(--navy);color:#fff;padding:.5rem .75rem;text-align:left;}
.risk-table td{padding:.5rem .75rem;border-bottom:1px solid var(--grey-mid);}
.residual-Low{background:#d1fae5;color:#065f46;padding:.2rem .5rem;border-radius:4px;font-weight:bold;}
.residual-Medium{background:#fef3c7;color:#92400e;padding:.2rem .5rem;border-radius:4px;font-weight:bold;}
.residual-High{background:#fee2e2;color:#991b1b;padding:.2rem .5rem;border-radius:4px;font-weight:bold;}
</style>
<div class="wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <div style="font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;">Risk Assessment</div>
            <h1 style="font-size:1.8rem;font-weight:bold;color:var(--navy);">{{ $riskAssessment->event_name }}</h1>
        </div>
        <div style="display:flex;gap:.5rem;">
            <a href="{{ route('risk-assessment.pdf', $riskAssessment) }}" class="btn btn-primary">📥 Download PDF</a>
            <a href="{{ route('risk-assessment.index') }}" class="btn btn-navy">← Back</a>
        </div>
    </div>

    {{-- RAG Banner --}}
    <div style="background:{{ $riskAssessment->ragColour() }};color:#fff;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
        <div style="font-size:.85rem;text-transform:uppercase;letter-spacing:.1em;font-weight:700;opacity:.8;">Overall Event Risk Status</div>
        <div style="font-size:1.5rem;font-weight:900;margin-top:.2rem;">{{ $riskAssessment->ragLabel() }}</div>
    </div>

    @if($riskAssessment->rag_status === 'red')
    <div style="background:#fee2e2;border-left:4px solid #dc2626;padding:1rem 1.25rem;border-radius:0 6px 6px 0;margin-bottom:1.5rem;font-size:.88rem;color:#991b1b;">
        <strong>⚠ This assessment contains one or more high residual risks.</strong> It requires Group Controller review before approval.
    </div>
    @endif

    {{-- Details --}}
    <div style="background:#fff;border:1px solid var(--grey-mid);border-radius:8px;padding:1.25rem;margin-bottom:1.5rem;">
        <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:.75rem;">Event Details</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.9rem;">
            <div><span style="color:var(--muted);">Location:</span> {{ $riskAssessment->location ?? '—' }}</div>
            <div><span style="color:var(--muted);">Date:</span> {{ $riskAssessment->event_date?->format('d M Y') ?? '—' }}</div>
            <div><span style="color:var(--muted);">Time:</span> {{ $riskAssessment->start_time ?? '—' }} – {{ $riskAssessment->finish_time ?? '—' }}</div>
            <div><span style="color:var(--muted);">Attendance:</span> {{ $riskAssessment->attendance ?? '—' }}</div>
        </div>
    </div>

    {{-- Risk Register --}}
    <div style="font-size:.95rem;font-weight:700;color:var(--navy);margin-bottom:.75rem;">Risk Register</div>
    <table class="risk-table">
        <thead><tr>
            <th>Hazard</th>
            <th>Control Measures</th>
            <th>Likelihood</th>
            <th>Severity</th>
            <th>Residual</th>
        </tr></thead>
        <tbody>
            @foreach($result['risks'] as $risk)
            <tr>
                <td><strong>{{ $risk['hazard'] }}</strong><br><small style="color:var(--muted);">{{ $risk['cause'] }}</small></td>
                <td>{{ $risk['controls'] }}</td>
                <td>{{ $risk['likelihood'] }}</td>
                <td>{{ $risk['severity'] }}</td>
                <td><span class="residual-{{ $risk['residual'] }}">{{ $risk['residual'] }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($riskAssessment->notes)
    <div style="background:#fff;border:1px solid var(--grey-mid);border-radius:8px;padding:1.25rem;margin-top:1.5rem;">
        <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:.5rem;">Additional Notes</div>
        <p style="font-size:.9rem;color:var(--text);">{{ $riskAssessment->notes }}</p>
    </div>
    @endif
</div>
@endsection
