@extends('layouts.app')
@section('title', 'Audit Log — ' . $resource->title)
@section('content')
<div style="max-width:900px;margin:0 auto;padding:1.5rem 1rem">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <a href="{{ route('resources.index') }}" style="color:var(--navy);text-decoration:none;font-size:.9rem">&#8592; Resources</a>
        <h1 style="font-size:1.3rem;font-weight:700;color:var(--navy);margin:0">&#128202; Audit Log</h1>
        <span style="font-size:.9rem;color:var(--text-muted)">{{ $resource->title }}</span>
    </div>
    <div style="background:white;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);margin-bottom:1.5rem;padding:1rem">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem">
            <div style="text-align:center"><div style="font-size:2rem;font-weight:700;color:var(--navy)">{{ $resource->download_count }}</div><div style="font-size:.8rem;color:var(--text-muted)">Total Downloads</div></div>
            <div style="text-align:center"><div style="font-size:2rem;font-weight:700;color:var(--navy)">{{ $resource->bookmarks->count() }}</div><div style="font-size:.8rem;color:var(--text-muted)">Bookmarks</div></div>
            <div style="text-align:center"><div style="font-size:2rem;font-weight:700;color:var(--navy)">{{ $resource->versions->count() }}</div><div style="font-size:.8rem;color:var(--text-muted)">Versions</div></div>
            <div style="text-align:center"><div style="font-size:1.1rem;font-weight:700;color:var(--navy)">{{ $resource->created_at->format('d M Y') }}</div><div style="font-size:.8rem;color:var(--text-muted)">Uploaded</div></div>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;background:white;border:1px solid var(--grey-mid)">
        <thead><tr style="background:var(--light);border-bottom:2px solid var(--grey-mid)">
            <th style="padding:.5rem .85rem;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);text-align:left">When</th>
            <th style="padding:.5rem .85rem;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);text-align:left">User</th>
            <th style="padding:.5rem .85rem;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);text-align:left">IP Address</th>
        </tr></thead>
        <tbody>
        @forelse($downloads as $dl)
        <tr style="border-bottom:1px solid var(--grey-mid)">
            <td style="padding:.6rem .85rem;font-size:.88rem;color:var(--text-muted)">{{ $dl->created_at->format('d M Y H:i') }}</td>
            <td style="padding:.6rem .85rem;font-size:.88rem">{{ $dl->user ? $dl->user->name.' ('.$dl->user->callsign.')' : 'Guest' }}</td>
            <td style="padding:.6rem .85rem;font-size:.88rem;color:var(--text-muted);font-family:monospace">{{ $dl->ip_address }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="padding:2rem;text-align:center;color:var(--text-muted)">No downloads recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:1rem">{{ $downloads->links() }}</div>
</div>
@endsection
