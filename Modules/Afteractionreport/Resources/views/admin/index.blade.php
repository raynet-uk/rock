@extends('layouts.app')
@section('title', 'Post-Exercise Reports (PXR)')
@section('content')
<div style="max-width:1100px;margin:0 auto;padding:2rem 1rem;font-family:inherit">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;margin:0">Post-Exercise Reports</h1>
            <p style="color:#6b7280;font-size:.85rem;margin:.2rem 0 0">JESIP-aligned After Action Reports (PXR)</p>
        </div>
        <a href="{{ route('admin.afteractionreport.create') }}"
            style="background:#1d4ed8;color:#fff;padding:.45rem 1rem;border-radius:7px;font-size:.85rem;font-weight:500;text-decoration:none">
            + New Report
        </a>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.7rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.875rem">
            {{ session('success') }}
        </div>
    @endif

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)">
        @if($items->count())
        <table style="width:100%;border-collapse:collapse;font-size:.875rem">
            <thead>
                <tr style="background:#f0f4ff;border-bottom:2px solid #c7d2fe">
                    <th style="padding:.65rem .9rem;text-align:left;font-weight:600;color:#3730a3">Exercise</th>
                    <th style="padding:.65rem .9rem;text-align:left;font-weight:600;color:#3730a3">Date</th>
                    <th style="padding:.65rem .9rem;text-align:left;font-weight:600;color:#3730a3">Type</th>
                    <th style="padding:.65rem .9rem;text-align:left;font-weight:600;color:#3730a3">Grade</th>
                    <th style="padding:.65rem .9rem;text-align:left;font-weight:600;color:#3730a3">Author</th>
                    <th style="padding:.65rem .9rem"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                @php
                    $gradeColour = match($item->overall_grade ?? '') {
                        'Objective Met'  => '#15803d',
                        'Partially Met'  => '#b45309',
                        'Not Met'        => '#dc2626',
                        default          => '#6b7280',
                    };
                    $gradeBg = match($item->overall_grade ?? '') {
                        'Objective Met'  => '#f0fdf4',
                        'Partially Met'  => '#fffbeb',
                        'Not Met'        => '#fef2f2',
                        default          => '#f9fafb',
                    };
                @endphp
                <tr style="border-top:1px solid #f3f4f6">
                    <td style="padding:.65rem .9rem;font-weight:600;color:#111827">
                        <a href="{{ route('admin.afteractionreport.show', $item->id) }}"
                           style="color:#1d4ed8;text-decoration:none">{{ $item->exercise_name }}</a>
                    </td>
                    <td style="padding:.65rem .9rem;color:#374151">
                        {{ \Carbon\Carbon::parse($item->exercise_date)->format('d/m/Y') }}
                    </td>
                    <td style="padding:.65rem .9rem;color:#6b7280;font-size:.82rem">{{ $item->exercise_type }}</td>
                    <td style="padding:.65rem .9rem">
                        @if($item->overall_grade)
                            <span style="background:{{ $gradeBg }};color:{{ $gradeColour }};padding:.2rem .6rem;border-radius:5px;font-size:.78rem;font-weight:600">
                                {{ $item->overall_grade }}
                            </span>
                        @else
                            <span style="color:#9ca3af;font-size:.82rem">—</span>
                        @endif
                    </td>
                    <td style="padding:.65rem .9rem;color:#6b7280;font-size:.82rem">
                        {{ $item->author_callsign ? $item->author_callsign . ' — ' : '' }}{{ $item->author_name }}
                    </td>
                    <td style="padding:.65rem .9rem;text-align:right;white-space:nowrap">
                        <a href="{{ route('admin.afteractionreport.edit', $item->id) }}"
                            style="color:#2563eb;font-size:.8rem;text-decoration:none;margin-right:.75rem">Edit</a>
                        <form action="{{ route('admin.afteractionreport.destroy', $item->id) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.8rem;padding:0;font-family:inherit"
                                onclick="return confirm('Permanently delete this PXR report?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div style="padding:.75rem 1rem;border-top:1px solid #f3f4f6">
            {{ $items->links() }}
        </div>
        @else
            <p style="text-align:center;color:#9ca3af;padding:2.5rem;font-size:.875rem">
                No reports yet. <a href="{{ route('admin.afteractionreport.create') }}" style="color:#2563eb">Create the first PXR.</a>
            </p>
        @endif
    </div>

</div>
@endsection
