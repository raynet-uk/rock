@extends('layouts.app')
@section('title', $item->exercise_name . ' — PXR')
@section('content')
<div style="max-width:950px;margin:0 auto;padding:2rem 1rem;font-family:inherit">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <a href="{{ route('admin.afteractionreport.index') }}"
            style="color:#6b7280;font-size:.85rem;text-decoration:none">← All Reports</a>
        <div style="display:flex;gap:.6rem">
            <a href="{{ route('admin.afteractionreport.edit', $item->id) }}"
               style="background:#1d4ed8;color:#fff;padding:.4rem .9rem;border-radius:7px;font-size:.82rem;font-weight:500;text-decoration:none">Edit</a>
            <button onclick="window.print()"
               style="background:#fff;border:1px solid #d1d5db;color:#374151;padding:.4rem .9rem;border-radius:7px;font-size:.82rem;cursor:pointer;font-family:inherit">Print / PDF</button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.7rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.875rem">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:#1e3a8a;color:#fff;border-radius:10px 10px 0 0;padding:1.5rem 2rem">
        <div style="font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;opacity:.7;margin-bottom:.3rem">POST-EXERCISE REPORT (PXR)</div>
        <h1 style="font-size:1.5rem;font-weight:700;margin:0 0 .5rem">{{ $item->exercise_name }}</h1>
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem;font-size:.83rem;opacity:.85">
            <span>Date: {{ \Carbon\Carbon::parse($item->exercise_date)->format('d/m/Y') }}</span>
            <span>Type: {{ $item->exercise_type }}</span>
            @if($item->author_callsign || $item->author_name)
                <span>Author: {{ implode(', ', array_filter([$item->author_callsign, $item->author_name])) }}</span>
            @endif
            @if($item->group_name)
                <span>Group: RAYNET {{ $item->group_name }}</span>
            @endif
        </div>
    </div>

    @php
        $gradeColour = match($item->overall_grade ?? '') { 'Objective Met' => '#15803d', 'Partially Met' => '#b45309', 'Not Met' => '#dc2626', default => '#6b7280' };
        $gradeBg     = match($item->overall_grade ?? '') { 'Objective Met' => '#f0fdf4', 'Partially Met' => '#fffbeb', 'Not Met' => '#fef2f2', default => '#f9fafb' };
    @endphp

    <div style="background:#fff;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px;padding:2rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">

        @if($item->distribution)
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.82rem;color:#475569">
            <strong>Distribution:</strong> {{ $item->distribution }}
        </div>
        @endif

        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">1. Administrative Overview</h2>
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;margin-bottom:1.5rem">
            @if($item->participants)
            <tr><td style="width:160px;padding:.4rem .6rem;font-weight:600;color:#374151;vertical-align:top">Participants</td><td style="padding:.4rem .6rem;color:#111827">{{ $item->participants }}</td></tr>
            @endif
            @if($item->op_start || $item->op_end)
            <tr style="background:#f9fafb"><td style="padding:.4rem .6rem;font-weight:600;color:#374151">Operational Period</td><td style="padding:.4rem .6rem;color:#111827">{{ $item->op_start ? \Carbon\Carbon::parse($item->op_start)->format('d/m/Y H:i') : '—' }} → {{ $item->op_end ? \Carbon\Carbon::parse($item->op_end)->format('d/m/Y H:i') : '—' }}</td></tr>
            @endif
            @if($item->location)
            <tr><td style="padding:.4rem .6rem;font-weight:600;color:#374151">Location / NGR</td><td style="padding:.4rem .6rem;color:#111827">{{ $item->location }}</td></tr>
            @endif
        </table>

        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">2. Objectives &amp; Scope</h2>
        @if($item->primary_objective)<p style="font-size:.875rem;color:#111827;margin:0 0 .75rem"><strong>Primary Objective:</strong> {{ $item->primary_objective }}</p>@endif
        @if($item->jesip_principles_tested)<p style="font-size:.875rem;color:#111827;margin:0 0 1.5rem"><strong>JESIP Principles Tested:</strong> {{ $item->jesip_principles_tested }}</p>@endif

        @if(count($item->chronology ?? []) > 0)
        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">3. Chronology of Events</h2>
        <div style="margin-bottom:1.5rem">
            @foreach($item->chronology as $entry)
            <div style="display:flex;gap:1rem;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.875rem">
                <span style="min-width:60px;font-weight:600;color:#1d4ed8;font-family:monospace">{{ $entry['offset'] ?? '' }}</span>
                <span style="color:#111827">{{ $entry['event'] ?? '' }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">4. Analysis of JESIP Principles</h2>
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;margin-bottom:1.5rem">
            <thead><tr style="background:#f0f4ff"><th style="padding:.55rem .75rem;text-align:left;font-weight:600;color:#3730a3;width:30%">Principle</th><th style="padding:.55rem .75rem;text-align:left;font-weight:600;color:#3730a3">Observation</th></tr></thead>
            <tbody>
                @foreach([['Co-location',$item->jesip_colocation],['Communication',$item->jesip_communication],['Coordination',$item->jesip_coordination],['Joint Understanding of Risk',$item->jesip_joint_risk],['Shared Situational Awareness',$item->jesip_shared_awareness]] as $row)
                <tr style="border-top:1px solid #e5e7eb">
                    <td style="padding:.55rem .75rem;font-weight:600;color:#374151;vertical-align:top">{{ $row[0] }}</td>
                    <td style="padding:.55rem .75rem;color:{{ $row[1] ? '#111827' : '#9ca3af' }}">{{ $row[1] ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">5. Technical &amp; Operational Performance</h2>
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;margin-bottom:1.5rem">
            @foreach([['RF Coverage',$item->rf_coverage],['Equipment Reliability',$item->equipment_reliability],['Logistics',$item->logistics],['Data / Digital',$item->digital_performance]] as $idx => $row)
            <tr style="{{ $idx%2===0?'':'background:#f9fafb' }};border-top:1px solid #e5e7eb">
                <td style="width:175px;padding:.5rem .75rem;font-weight:600;color:#374151;vertical-align:top">{{ $row[0] }}</td>
                <td style="padding:.5rem .75rem;color:{{ $row[1] ? '#111827' : '#9ca3af' }}">{{ $row[1] ?: '—' }}</td>
            </tr>
            @endforeach
        </table>

        @if(count($item->lessons ?? []) > 0)
        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">6. Summary of Lessons Identified</h2>
        @foreach($item->lessons as $i => $lesson)
        <div style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:1rem 1.25rem;margin-bottom:1rem">
            <p style="font-size:.78rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .6rem">Lesson {{ $i+1 }}</p>
            @if(!empty($lesson['observation']))<p style="font-size:.875rem;margin:0 0 .4rem;color:#111827"><strong>Observation:</strong> {{ $lesson['observation'] }}</p>@endif
            @if(!empty($lesson['discussion']))<p style="font-size:.875rem;margin:0 0 .4rem;color:#374151"><strong>Discussion:</strong> {{ $lesson['discussion'] }}</p>@endif
            @if(!empty($lesson['recommendation']))<p style="font-size:.875rem;margin:0;color:#374151"><strong>Recommendation:</strong> {{ $lesson['recommendation'] }}</p>@endif
        </div>
        @endforeach
        <div style="margin-bottom:1.5rem"></div>
        @endif

        <h2 style="font-size:1rem;font-weight:700;color:#1e3a8a;border-bottom:2px solid #c7d2fe;padding-bottom:.4rem;margin:0 0 1rem">7. Conclusion &amp; Sign-off</h2>
        @if($item->overall_grade)
        <div style="display:inline-block;background:{{ $gradeBg }};color:{{ $gradeColour }};border:1px solid {{ $gradeColour }};padding:.35rem 1rem;border-radius:6px;font-weight:700;font-size:.9rem;margin-bottom:.75rem">
            Overall Grade: {{ $item->overall_grade }}
        </div>
        @endif
        @if($item->closing_statement)<p style="font-size:.875rem;color:#111827;margin:.5rem 0 1rem">{{ $item->closing_statement }}</p>@endif
        <div style="display:flex;gap:3rem;margin-top:1rem">
            <div>
                <div style="font-size:.78rem;color:#6b7280;margin-bottom:.25rem">Signed</div>
                <div style="font-size:.9rem;font-weight:600;color:#111827">{{ $item->signed_by ?: '______________________' }}</div>
            </div>
            <div>
                <div style="font-size:.78rem;color:#6b7280;margin-bottom:.25rem">Date</div>
                <div style="font-size:.9rem;font-weight:600;color:#111827">{{ $item->signed_date ? \Carbon\Carbon::parse($item->signed_date)->format('d/m/Y') : '______________________' }}</div>
            </div>
        </div>

        <div style="background:#fef9c3;border:1px solid #fde047;border-radius:7px;padding:.75rem 1rem;margin-top:2rem;font-size:.78rem;color:#713f12">
            <strong>GDPR / Data Protection Reminder:</strong>
            Ensure no personal details (phone numbers or home addresses of volunteers) are included if this report is to be shared with LRF partners.
            If the exercise involved a simulated Major Incident, attach a copy of formal M/ETHANE messages as an appendix for audit purposes.
        </div>

    </div>
</div>
@endsection
