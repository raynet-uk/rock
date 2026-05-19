@extends('layouts.admin')
@section('title', 'Temporary Guests')
@section('content')
<style>
:root{--navy:#003366;--navy2:#00234a;--red:#C8102E;--white:#ffffff;--grey:#f4f5f7;--border:#e1e5ec;--text:#1a2332;--muted:#6b7a90;--green:#1a6b3c;--amber:#b45309;}
.tg-page{max-width:1100px;margin:0 auto;padding:28px clamp(16px,3vw,32px) 64px;}
.tg-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.tg-title{font-size:22px;font-weight:bold;color:var(--navy);}
.tg-subtitle{font-size:13px;color:var(--muted);margin-top:2px;}
.tg-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;text-decoration:none;border:none;cursor:pointer;transition:all .15s;font-family:inherit;}
.tg-btn-primary{background:var(--red);color:#fff;}
.tg-btn-primary:hover{background:#a50f26;color:#fff;}
.tg-btn-ghost{background:var(--grey);color:var(--text);border:1px solid var(--border);}
.tg-btn-ghost:hover{border-color:var(--navy);color:var(--navy);}
.tg-btn-danger{background:none;border:1px solid rgba(200,16,46,.4);color:var(--red);}
.tg-btn-danger:hover{background:var(--red);color:#fff;}
.tg-btn-sm{padding:5px 11px;font-size:11px;}
.tg-alert{padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;border-left:3px solid;}
.tg-alert-success{background:#eef7f2;border-color:var(--green);color:var(--green);}
.tg-alert-error{background:#fdf0f2;border-color:var(--red);color:var(--red);}
.tg-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;}
@media(max-width:640px){.tg-stats{grid-template-columns:repeat(2,1fr);}}
.tg-stat{background:var(--white);border:1px solid var(--border);padding:14px 16px;border-top:3px solid var(--navy);}
.tg-stat.green{border-top-color:var(--green);}
.tg-stat.amber{border-top-color:var(--amber);}
.tg-stat.red{border-top-color:var(--red);}
.tg-stat-num{font-size:26px;font-weight:bold;color:var(--navy);line-height:1;font-family:monospace;}
.tg-stat.green .tg-stat-num{color:var(--green);}
.tg-stat.amber .tg-stat-num{color:var(--amber);}
.tg-stat.red .tg-stat-num{color:var(--red);}
.tg-stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-top:4px;}
.tg-table-wrap{background:var(--white);border:1px solid var(--border);overflow:hidden;}
.tg-table-head{background:var(--navy2);padding:10px 16px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.6);}
.tg-empty{padding:40px 24px;text-align:center;color:var(--muted);font-size:14px;}
.tg-empty-icon{font-size:2rem;opacity:.2;margin-bottom:8px;}
table{width:100%;border-collapse:collapse;}
thead th{padding:10px 14px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);background:var(--grey);border-bottom:1px solid var(--border);text-align:left;white-space:nowrap;}
tbody td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:#f8f9fb;}
.tg-name{font-weight:600;color:var(--navy);}
.tg-callsign{font-family:monospace;font-size:12px;font-weight:bold;color:var(--navy);}
.tg-email{font-size:12px;color:var(--muted);}
.tg-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:bold;padding:2px 8px;border:1px solid;text-transform:uppercase;letter-spacing:.05em;}
.tg-badge-active{background:rgba(26,107,60,.08);border-color:rgba(26,107,60,.3);color:var(--green);}
.tg-badge-expired{background:rgba(200,16,46,.08);border-color:rgba(200,16,46,.3);color:var(--red);}
.tg-badge-noexpiry{background:rgba(0,51,102,.06);border-color:rgba(0,51,102,.2);color:var(--navy);}
.tg-expiry{font-size:12px;}
.tg-expiry-date{font-weight:600;font-family:monospace;}
.tg-expiry-rel{font-size:11px;color:var(--muted);}
.tg-expiry-soon{color:var(--amber);}
.tg-expiry-past{color:var(--red);}
.tg-actions{display:flex;align-items:center;gap:5px;}
.tg-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;}
.tg-modal-bg.open{display:flex;}
.tg-modal{background:#fff;border:1px solid var(--border);padding:28px;max-width:420px;width:100%;margin:16px;}
.tg-modal h3{font-size:16px;font-weight:bold;color:var(--navy);margin-bottom:16px;}
.tg-modal-foot{display:flex;gap:8px;margin-top:20px;justify-content:flex-end;}
.tg-input{width:100%;border:1px solid var(--border);padding:8px 10px;font-size:13px;font-family:inherit;color:var(--text);outline:none;transition:border-color .15s;}
.tg-input:focus{border-color:var(--navy);}
.tg-label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:5px;display:block;}
</style>

<div class="tg-page">
    @if(session('success'))<div class="tg-alert tg-alert-success">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="tg-alert tg-alert-error">✕ {{ session('error') }}</div>@endif

    <div class="tg-header">
        <div>
            <div class="tg-title">Temporary Guests</div>
            <div class="tg-subtitle">Time-limited read-only member access — cannot see personal details of other members</div>
        </div>
        <a href="{{ route('admin.temporary-guests.create') }}" class="tg-btn tg-btn-primary">+ New Guest</a>
    </div>

    @php
        $total    = $guests->count();
        $active   = $guests->filter(fn($u) => ($u->hasRole('temporary_guest') || $u->hasRole('temporary_admin')) && (!$u->guest_expires_at || $u->guest_expires_at->isFuture()))->count();
        $expired  = $guests->filter(fn($u) => $u->guest_expires_at && $u->guest_expires_at->isPast())->count();
        $noExpiry = $guests->filter(fn($u) => !$u->guest_expires_at)->count();
    @endphp
    <div class="tg-stats">
        <div class="tg-stat"><div class="tg-stat-num">{{ $total }}</div><div class="tg-stat-label">Total guests</div></div>
        <div class="tg-stat green"><div class="tg-stat-num">{{ $active }}</div><div class="tg-stat-label">Active</div></div>
        <div class="tg-stat red"><div class="tg-stat-num">{{ $expired }}</div><div class="tg-stat-label">Expired</div></div>
        <div class="tg-stat amber"><div class="tg-stat-num">{{ $noExpiry }}</div><div class="tg-stat-label">No expiry set</div></div>
    </div>

    <div class="tg-table-wrap">
        <div class="tg-table-head">Guest Accounts</div>
        @if($guests->isEmpty())
            <div class="tg-empty"><div class="tg-empty-icon">👤</div>No temporary guest accounts yet. Create one above.</div>
        @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr>
                <th>Name / Callsign</th><th>Email</th><th>Access</th><th>Status</th><th>Expiry</th><th>Created</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @foreach($guests as $guest)
            @php
                $isExpired = $guest->guest_expires_at && $guest->guest_expires_at->isPast();
                $hasRole   = $guest->hasRole('temporary_guest');
                $isSoon    = $guest->guest_expires_at && !$isExpired && $guest->guest_expires_at->diffInHours(now()) < 48;
            @endphp
            <tr>
                <td>
                    <div class="tg-name">{{ $guest->name }}</div>
                    @if($guest->callsign)<div class="tg-callsign">{{ strtoupper($guest->callsign) }}</div>@endif
                </td>
                <td class="tg-email">{{ $guest->email }}</td>
                <td>
                    @if($guest->hasRole("temporary_admin"))
                        <span class="tg-badge" style="background:rgba(0,51,102,.08);border-color:rgba(0,51,102,.3);color:var(--navy);">🔑 Admin</span>
                    @else
                        <span class="tg-badge" style="background:rgba(26,107,60,.08);border-color:rgba(26,107,60,.3);color:var(--green);">👤 Member</span>
                    @endif
                </td>
                <td>
                    @if($isExpired)
                        <span class="tg-badge tg-badge-expired">✕ Expired</span>
                    @elseif(!$hasRole && !$guest->guest_expires_at)
                        <span class="tg-badge tg-badge-expired">✕ Disabled</span>
                    @elseif(!$guest->guest_expires_at)
                        <span class="tg-badge tg-badge-noexpiry">∞ No expiry</span>
                    @else
                        <span class="tg-badge tg-badge-active">✓ Active</span>
                    @endif
                </td>
                <td>
                    @if($guest->guest_expires_at)
                        <div class="tg-expiry">
                            <div class="tg-expiry-date {{ $isExpired ? 'tg-expiry-past' : ($isSoon ? 'tg-expiry-soon' : '') }}">
                                {{ $guest->guest_expires_at->format('d M Y H:i') }}
                            </div>
                            <div class="tg-expiry-rel">
                                {{ $isExpired ? 'Expired ' : 'Expires ' }}{{ $guest->guest_expires_at->diffForHumans() }}
                            </div>
                        </div>
                    @else
                        <span style="color:var(--muted);font-size:12px;">—</span>
                    @endif
                </td>
                <td style="font-size:12px;color:var(--muted);">{{ $guest->created_at->format('d M Y') }}</td>
                <td>
                    <div class="tg-actions">
                        <a href="{{ route('admin.temporary-guests.edit', $guest) }}" class="tg-btn tg-btn-ghost tg-btn-sm">Edit</a>
                        @if(!$isExpired && $hasRole && ($guest->guest_expires_at || $hasRole))
                            <form method="POST" action="{{ route('admin.temporary-guests.disable', $guest) }}"
                                  onsubmit="return confirm('Revoke {{ addslashes($guest->name) }}\'s access immediately?')">
                                @csrf
                                <button type="submit" class="tg-btn tg-btn-danger tg-btn-sm">Disable</button>
                            </form>
                        @else
                            <button type="button" class="tg-btn tg-btn-ghost tg-btn-sm"
                                    onclick="openReinstate({{ $guest->id }}, '{{ addslashes($guest->name) }}')">Reinstate</button>
                        @endif
                        <form method="POST" action="{{ route('admin.temporary-guests.destroy', $guest) }}"
                              onsubmit="return confirm('Permanently delete {{ addslashes($guest->name) }}\'s account?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="tg-btn tg-btn-danger tg-btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

<div class="tg-modal-bg" id="reinstateModal">
    <div class="tg-modal">
        <h3>Reinstate Guest</h3>
        <p style="font-size:13px;color:var(--muted);margin-bottom:18px;">
            Re-enable access for <strong id="reinstateName"></strong>.
            Leave expiry blank for no time limit.
        </p>
        <form method="POST" id="reinstateForm">
            @csrf
            <label class="tg-label">New Expiry Date &amp; Time (optional)</label>
            <input type="datetime-local" name="expires_at" class="tg-input" id="reinstateExpiry">
            <div class="tg-modal-foot">
                <button type="button" class="tg-btn tg-btn-ghost" onclick="closeReinstate()">Cancel</button>
                <button type="submit" class="tg-btn tg-btn-primary">Reinstate Access</button>
            </div>
        </form>
    </div>
</div>
<script>
function openReinstate(id,name){
    document.getElementById('reinstateForm').action='/admin/temporary-guests/'+id+'/reinstate';
    document.getElementById('reinstateName').textContent=name;
    document.getElementById('reinstateModal').classList.add('open');
}
function closeReinstate(){document.getElementById('reinstateModal').classList.remove('open');}
document.getElementById('reinstateModal').addEventListener('click',function(e){if(e.target===this)closeReinstate();});
</script>
@endsection
