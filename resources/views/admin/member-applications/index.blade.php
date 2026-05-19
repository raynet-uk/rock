@extends('layouts.admin')
@section('title', 'Member Applications')

@section('content')
<style>
.ma-wrap{max-width:1200px;margin:0 auto;padding:0 0 4rem}
.ma-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--grey-mid)}
.ma-table th{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);background:var(--grey);padding:.55rem .9rem;border-bottom:2px solid var(--navy);white-space:nowrap;text-align:left}
.ma-table td{padding:.6rem .9rem;border-bottom:1px solid var(--grey-mid);font-size:12.5px;vertical-align:middle}
.ma-table tr:last-child td{border-bottom:none}
.ma-table tr:hover td{background:var(--navy-faint)}
.ma-tab-row{display:flex;gap:0;margin-bottom:1.25rem;border-bottom:2px solid var(--navy)}
.ma-tab{padding:.5rem 1.1rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);text-decoration:none;border:1px solid transparent;border-bottom:none;margin-bottom:-2px;transition:all .12s}
.ma-tab:hover{color:var(--navy);background:var(--navy-faint)}
.ma-tab.active{color:var(--navy);background:#fff;border-color:var(--navy);border-bottom-color:#fff}
.ma-badge{display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;min-width:17px;height:15px;padding:0 4px;border-radius:8px;margin-left:5px;vertical-align:middle}
.ma-badge--pending{background:#92400e;color:#fffbeb}
.ma-badge--approved{background:var(--green);color:#fff}
.ma-badge--rejected{background:var(--red);color:#fff}
.ma-badge--all{background:var(--navy);color:#fff}
.status-pill{display:inline-flex;align-items:center;padding:2px 8px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;border:1px solid}
.sp-pending{background:#fffbeb;border-color:rgba(146,64,14,.3);color:#92400e}
.sp-approved{background:var(--green-bg);border-color:#b8ddc9;color:var(--green)}
.sp-rejected{background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red)}
.ma-name-link{font-weight:bold;color:var(--navy);text-decoration:none}
.ma-name-link:hover{color:var(--red)}
.ma-empty{padding:3rem;text-align:center;color:var(--text-muted);font-size:13px}
.action-row{display:flex;gap:.4rem;justify-content:flex-end;align-items:center}
</style>

<div class="ma-wrap">

    <div class="rn-page-header">
        <div class="rn-page-eyebrow">People</div>
        <div class="rn-page-header-row">
            <div>
                <div class="rn-page-title">Member Applications</div>
                <div class="rn-page-desc">Review REG-02 applications submitted via the website</div>
            </div>
            <div class="rn-page-actions">
                @if($counts['pending'] > 0)
                <span class="stat-pill sp-amber">{{ $counts['pending'] }} awaiting review</span>
                @endif
            </div>
        </div>
    </div>

    <div class="ma-tab-row">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
        <a class="ma-tab {{ $status === $key ? 'active' : '' }}"
           href="{{ route('admin.member-applications.index', ['status' => $key]) }}">
            {{ $label }}
            <span class="ma-badge ma-badge--{{ $key }}">{{ $counts[$key] }}</span>
        </a>
        @endforeach
    </div>

    <div class="rn-card">
        <table class="ma-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Callsign</th>
                    <th>Email</th>
                    <th>Date of Birth</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td>
                        <a href="{{ route('admin.member-applications.show', $app) }}" class="ma-name-link">
                            {{ strtoupper($app->surname) }}, {{ $app->forenames }}
                        </a>
                    </td>
                    <td>
                        @if($app->callsign)
                            <span style="font-family:monospace;font-weight:bold;font-size:12px;color:var(--navy)">{{ $_isTempAdmin && isset($app) && method_exists($app, 'piiVisible') && !$app->piiVisible() ? '●●●●●' : strtoupper($app->callsign) }}</span>
                        @else
                            <span style="color:var(--grey-dark)">—</span>
                        @endif
                    </td>
                    <td><a href="mailto:{{ $_isTempAdmin && isset($app) && method_exists($app, 'piiVisible') && !$app->piiVisible() ? '●●●●●●●' : $app->email }}" style="color:var(--navy);font-size:12px">{{ $_isTempAdmin && isset($app) && method_exists($app, 'piiVisible') && !$app->piiVisible() ? '●●●●●●●' : $app->email }}</a></td>
                    <td style="font-size:12px">{{ $app->dob->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-size:12px;color:var(--text-muted)" title="{{ $app->created_at->format('d/m/Y H:i') }}">
                            {{ $app->created_at->format('d M Y') }}<br>
                            <span style="font-size:11px">{{ $app->created_at->diffForHumans() }}</span>
                        </span>
                    </td>
                    <td>
                        <span class="status-pill sp-{{ $app->status }}">{{ $app->status }}</span>
                        @if($app->status === 'approved' && $app->invite_sent_at && !$app->converted_user_id)
                            <br><span style="font-size:10px;color:var(--text-muted)">invite sent {{ $app->invite_sent_at->diffForHumans() }}</span>
                        @elseif($app->converted_user_id)
                            <br><span style="font-size:10px;color:var(--green)">✓ account created</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-row">
                            <a href="{{ route('admin.member-applications.show', $app) }}" class="rn-btn rn-btn-ghost rn-btn-sm">View</a>
                            @if($app->status === 'pending')
                            <form method="POST" action="{{ route('admin.member-applications.convert', $app) }}" style="display:inline">
                                @csrf
                                <button class="rn-btn rn-btn-sm" style="background:var(--green);border-color:var(--green);color:#fff"
                                        onclick="return confirm('Approve and send invite to {{ $_isTempAdmin && isset($app) && method_exists($app, 'piiVisible') && !$app->piiVisible() ? '●●●●●●●' : $app->email }}?')">✓ Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.member-applications.reject', $app) }}" style="display:inline">
                                @csrf
                                <button class="rn-btn rn-btn-danger rn-btn-sm"
                                        onclick="return confirm('Reject this application?')">Reject</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="ma-empty">
                    No {{ $status !== 'all' ? $status : '' }} applications found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
    <div style="margin-top:1rem;font-size:12px;color:var(--text-muted)">
        {{ $applications->links() }}
    </div>
    @endif

</div>
@endsection
