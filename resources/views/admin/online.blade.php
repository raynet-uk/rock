@extends('layouts.admin')

@section('content')
<style>
    .online-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .online-header h1 {
        font-size: 1.5rem;
        color: #003366;
    }
    .online-count {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .35rem .9rem;
        background: #003366;
        color: white;
        font-size: 0.9rem;
        font-weight: bold;
        border-radius: 999px;
    }
    .online-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        animation: pulse-green 1.8s ease-in-out infinite;
        flex-shrink: 0;
    }
    @keyframes pulse-green {
        0%, 100% { opacity: 1; }
        50%       { opacity: .3; }
    }
    .online-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 8px rgba(0,51,102,0.06);
    }
    .online-table th {
        background: #003366;
        color: white;
        padding: .7rem 1rem;
        text-align: left;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .online-table td {
        padding: .75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.9rem;
        color: #1a1a1a;
        vertical-align: middle;
    }
    .online-table tr:last-child td { border-bottom: none; }
    .online-table tr:hover td { background: #f8f9fc; }
    .badge-admin {
        display: inline-block;
        padding: .2rem .6rem;
        background: #C8102E;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        border-radius: 999px;
        margin-left: .4rem;
    }
    .last-seen {
        font-size: 0.82rem;
        color: #666;
    }
    .ua-cell {
        font-size: 0.8rem;
        color: #666;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .btn-action {
        display: inline-block;
        padding: .3rem .8rem;
        font-size: 0.8rem;
        font-weight: bold;
        cursor: pointer;
        border: none;
        font-family: inherit;
        text-decoration: none;
    }
    .btn-kick {
        background: #C8102E;
        color: white;
    }
    .btn-kick:hover { background: #a00d25; }
    .empty-state {
        background: white;
        padding: 3rem 2rem;
        text-align: center;
        color: #666;
        box-shadow: 0 2px 8px rgba(0,51,102,0.06);
    }
    .empty-state .icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .refresh-note {
        font-size: 0.82rem;
        color: #888;
        margin-top: 1rem;
        text-align: right;
    }
    @media (max-width: 640px) {
        .online-table th:nth-child(3),
        .online-table td:nth-child(3),
        .online-table th:nth-child(4),
        .online-table td:nth-child(4) { display: none; }
    }
</style>

<div class="online-header">
    <h1>Who's Online</h1>
    <span class="online-count">
        <span class="online-dot"></span>
        {{ $online->count() }} active {{ Str::plural('session', $online->count()) }}
    </span>
</div>

@if ($online->isEmpty())
    <div class="empty-state">
        <div class="icon">💤</div>
        <div>No members have been active in the last 15 minutes.</div>
    </div>
@else
    <table class="online-table">
        <thead>
            <tr>
                <th>Member</th>
                <th>Last active</th>
                <th>IP address</th>
                <th>Browser</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($online as $row)
            <tr>
                <td>
                    <strong>{{ $_isTempAdmin && isset($row) && method_exists($row, 'piiVisible') && !$row->piiVisible() ? '●●●●●●●●●' : $row->name }}</strong>
                    @if ($row->is_admin)
                        <span class="badge-admin">Admin</span>
                    @endif
                    <div style="font-size:0.8rem;color:#666;">{{ $_isTempAdmin && isset($row) && method_exists($row, 'piiVisible') && !$row->piiVisible() ? '●●●●●●●' : $row->email }}</div>
                </td>
                <td>
                    <span class="last-seen">
                        {{ \Carbon\Carbon::createFromTimestamp($row->last_activity)->diffForHumans() }}
                    </span>
                </td>
                <td style="font-size:0.85rem;">{{ $row->ip_address ?? '—' }}</td>
                <td class="ua-cell" title="{{ $row->user_agent }}">
                    {{ $row->user_agent ? \Illuminate\Support\Str::limit($row->user_agent, 50) : '—' }}
                </td>
                <td>
                    <form method="POST"
                          action="{{ route('admin.users.force-logout', $row->id) }}"
                          onsubmit="return confirm('Force logout {{ addslashes($row->name) }}?');"
                          style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-action btn-kick">⏏ Kick</button>
                    </form>
                    <a href="{{ route('admin.users.edit', $row->id) }}"
                       class="btn-action"
                       style="background:#003366;color:white;margin-left:.3rem;">
                        Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="refresh-note">
        Showing sessions active in the last 15 minutes.
        <a href="{{ route('admin.online') }}" style="color:#003366;">↻ Refresh</a>
    </div>
@endif
@endsection
