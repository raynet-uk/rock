{{-- resources/views/admin/super/permissions.blade.php --}}
@extends('layouts.admin')

@section('title', 'Permission Management — Super Admin')

@section('content')
<style>
.pm-head {
    background: #1e0040;
    border-bottom: 4px solid #7c3aed;
    padding: 1.25rem 0;
    margin: -1.5rem -1rem 1.5rem;
}
.pm-head-inner {
    max-width: 1280px; margin: 0 auto; padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
}
.pm-title   { font-size: 20px; font-weight: 700; color: #e9d5ff; font-family: var(--font); }
.pm-sub     { font-size: 12px; color: rgba(233,213,255,.45); text-transform: uppercase; letter-spacing: .1em; margin-top: 2px; }
.pm-back    { font-size: 12px; font-weight: bold; color: rgba(233,213,255,.6); text-decoration: none;
              border: 1px solid rgba(124,58,237,.4); padding: 4px 10px; transition: all .15s; }
.pm-back:hover { background: rgba(124,58,237,.2); color: #e9d5ff; }

.pm-alert { padding: .65rem 1rem; margin-bottom: 1rem; font-size: 13px; font-family: var(--font); display: flex; align-items: center; gap: .5rem; }
.pm-alert.success { background: var(--green-bg, #eef7f2); border: 1px solid rgba(22,163,74,.3); border-left: 3px solid var(--green, #1a6b3c); color: var(--green, #1a6b3c); }
.pm-alert.error   { background: var(--red-faint, #fdf0f2); border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red, #C8102E); color: var(--red, #C8102E); }

/* Role tabs */
.pm-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--grey-mid); margin-bottom: 1.25rem; overflow-x: auto; background: #fff; }
.pm-tab {
    padding: .75rem 1.35rem; background: none; border: none; border-bottom: 3px solid transparent;
    font-family: var(--font); font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; cursor: pointer; white-space: nowrap; color: var(--grey-dark);
    transition: all .15s; margin-bottom: -2px;
}
.pm-tab:hover { color: var(--navy); }
.pm-tab.active { color: #7c3aed; border-bottom-color: #7c3aed; }
.pm-tab-badge {
    display: inline-block; font-size: 9px; font-weight: 700;
    padding: 1px 5px; margin-left: 5px;
    background: rgba(124,58,237,.1); border: 1px solid rgba(124,58,237,.2); color: #7c3aed;
}

/* Role pane */
.pm-pane { display: none; }
.pm-pane.active { display: block; }

.pm-role-card {
    background: #fff; border: 1px solid var(--grey-mid);
    box-shadow: 0 1px 4px rgba(0,51,102,.06); margin-bottom: 1.25rem;
}
.pm-role-head {
    padding: .75rem 1.1rem; display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    border-bottom: 2px solid var(--grey-mid);
}
.pm-role-name {
    font-size: 16px; font-weight: 700; font-family: var(--font);
    display: flex; align-items: center; gap: .6rem;
}
.pm-role-perm-count {
    font-size: 11px; font-weight: 700; padding: 2px 8px;
    background: rgba(124,58,237,.08); border: 1px solid rgba(124,58,237,.2); color: #7c3aed;
}
.pm-role-note {
    font-size: 11px; color: var(--grey-dark); font-family: var(--font);
}
.pm-save-btn {
    padding: .45rem 1.1rem; background: #7c3aed; border: 1px solid #6d28d9;
    color: #fff; font-family: var(--font); font-size: 12px; font-weight: 700;
    cursor: pointer; text-transform: uppercase; letter-spacing: .06em; transition: all .12s;
}
.pm-save-btn:hover { background: #6d28d9; }

/* Permission groups */
.pm-group { margin-bottom: 1rem; }
.pm-group-head {
    padding: .4rem 1.1rem; background: rgba(0,51,102,.05);
    border-bottom: 1px solid var(--grey-mid);
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: var(--grey-dark); font-family: var(--font);
    display: flex; align-items: center; justify-content: space-between;
}
.pm-group-toggle {
    font-size: 10px; font-weight: 700; font-family: var(--font);
    background: none; border: 1px solid var(--grey-mid); color: var(--grey-dark);
    padding: 1px 7px; cursor: pointer; text-transform: uppercase; letter-spacing: .05em;
    transition: all .12s;
}
.pm-group-toggle:hover { border-color: var(--navy); color: var(--navy); }

.pm-perm-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 0; padding: 0;
}
.pm-perm-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .55rem 1.1rem; border-bottom: 1px solid var(--grey-mid);
    border-right: 1px solid var(--grey-mid);
    cursor: pointer; transition: background .1s; user-select: none;
}
.pm-perm-item:hover { background: var(--navy-faint); }
.pm-perm-item:has(input:checked) { background: #f5f0ff; }
.pm-perm-item input[type="checkbox"] {
    width: 15px; height: 15px; accent-color: #7c3aed;
    flex-shrink: 0; cursor: pointer;
}
.pm-perm-label { font-size: 12px; font-weight: 500; color: var(--text-mid); font-family: var(--font); }
.pm-perm-item:has(input:checked) .pm-perm-label { color: #4c1d95; font-weight: 700; }

/* Super-admin note */
.pm-superadmin-notice {
    background: #1e0040; border: 1px solid rgba(124,58,237,.4);
    padding: 1.25rem 1.5rem;
    display: flex; align-items: flex-start; gap: 1rem;
}
.pm-superadmin-notice-icon { font-size: 24px; flex-shrink: 0; }
.pm-superadmin-notice-title { font-size: 14px; font-weight: 700; color: #e9d5ff; margin-bottom: .3rem; }
.pm-superadmin-notice-text  { font-size: 12px; color: rgba(233,213,255,.6); line-height: 1.6; }
.pm-superadmin-notice-text code { background: rgba(124,58,237,.2); color: #c4b5fd; padding: 1px 5px; }

/* Permissions library */
.pm-library-card {
    background: #fff; border: 1px solid var(--grey-mid);
    box-shadow: 0 1px 4px rgba(0,51,102,.06); overflow: hidden;
}
.pm-library-head {
    background: var(--navy); border-bottom: 2px solid #7c3aed;
    padding: .65rem 1.1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.pm-library-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.9); font-family: var(--font); }
.pm-library-table { width: 100%; border-collapse: collapse; font-family: var(--font); font-size: 13px; }
.pm-library-table th { background: #001f40; color: rgba(255,255,255,.4); text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: .1em; padding: .45rem .85rem; text-align: left; }
.pm-library-table td { padding: .6rem .85rem; border-bottom: 1px solid var(--grey-mid); vertical-align: middle; }
.pm-library-table tr:last-child td { border-bottom: none; }
.pm-library-table tr:hover td { background: var(--navy-faint); }
.pm-perm-chip {
    font-family: 'Courier New', monospace; font-size: 12px; font-weight: 700;
    color: #4c1d95; background: rgba(124,58,237,.08);
    border: 1px solid rgba(124,58,237,.2); padding: 2px 8px;
}

/* Add permission form */
.pm-add-form {
    display: flex; gap: .65rem; align-items: center; flex-wrap: wrap;
    padding: .85rem 1.1rem; background: var(--grey); border-top: 1px solid var(--grey-mid);
}
.pm-add-input {
    flex: 1; min-width: 180px; padding: .45rem .75rem;
    border: 1px solid var(--grey-mid); font-family: 'Courier New', monospace;
    font-size: 12px; color: var(--navy); outline: none;
}
.pm-add-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 2px rgba(124,58,237,.1); }
.pm-add-btn {
    padding: .45rem 1rem; background: #7c3aed; border: 1px solid #6d28d9;
    color: #fff; font-family: var(--font); font-size: 12px; font-weight: 700;
    cursor: pointer; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
}
.pm-add-btn:hover { background: #6d28d9; }
.pm-add-hint { font-size: 11px; color: var(--grey-dark); font-family: var(--font); width: 100%; }

.pm-del-btn {
    padding: 2px 8px; background: var(--red-faint, #fdf0f2);
    border: 1px solid rgba(200,16,46,.25); color: var(--red, #C8102E);
    font-family: var(--font); font-size: 11px; font-weight: 700;
    cursor: pointer; transition: all .12s;
}
.pm-del-btn:hover { background: rgba(200,16,46,.12); }

/* Role badges in library */
.role-used-chip {
    display: inline-block; font-size: 9px; font-weight: 700;
    padding: 1px 5px; margin: 1px; text-transform: uppercase; letter-spacing: .04em;
}
.ruc-super-admin { background: rgba(124,58,237,.1); border: 1px solid rgba(124,58,237,.25); color: #7c3aed; }
.ruc-admin       { background: rgba(200,16,46,.08); border: 1px solid rgba(200,16,46,.2);   color: var(--red); }
.ruc-committee   { background: rgba(217,119,6,.08); border: 1px solid rgba(217,119,6,.2);   color: #d97706; }
.ruc-member      { background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.2);   color: var(--green); }
</style>

{{-- Page header --}}
<div class="pm-head">
    <div class="pm-head-inner">
        <div>
            <div class="pm-title">★ Permission Management</div>
            <div class="pm-sub">Super Admin only · Edit role permissions &amp; permission library</div>
        </div>
        <a href="{{ route('admin.super.index') }}" class="pm-back">← Super Admin</a>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="pm-alert success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="pm-alert error">✕ {{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="pm-alert error">✕ {{ $errors->first() }}</div>
@endif

{{-- Role tabs --}}
<div class="pm-tabs" id="pmTabs">
    @foreach($roles as $role)
        <button class="pm-tab {{ $loop->first ? 'active' : '' }}"
                data-pane="pane-{{ $role->id }}"
                onclick="switchTab(this, 'pane-{{ $role->id }}')">
            {{ $role->name }}
            <span class="pm-tab-badge">{{ $role->permissions->count() }}</span>
        </button>
    @endforeach
    <button class="pm-tab" data-pane="pane-library" onclick="switchTab(this, 'pane-library')"
            style="margin-left:auto;color:var(--navy);border-bottom-color:transparent;">
        📋 Permission Library
    </button>
</div>

{{-- Role panes --}}
@foreach($roles as $role)
<div class="pm-pane {{ $loop->first ? 'active' : '' }}" id="pane-{{ $role->id }}">

    @if($role->name === 'super-admin')
        <div class="pm-superadmin-notice">
            <div class="pm-superadmin-notice-icon">★</div>
            <div>
                <div class="pm-superadmin-notice-title">Super Admin bypasses all permission checks</div>
                <div class="pm-superadmin-notice-text">
                    The <code>super-admin</code> role does not use the permissions table.
                    Instead, <code>Gate::before()</code> in <code>AppServiceProvider</code> returns
                    <code>true</code> for every ability check, granting full access automatically.
                    There is nothing to configure here — super-admin always has access to everything.
                </div>
            </div>
        </div>
    @else
        <div class="pm-role-card">
            <form method="POST" action="{{ route('admin.super.permissions.role', $role) }}">
                @csrf
                @method('PATCH')

                <div class="pm-role-head">
                    <div>
                        <div class="pm-role-name">
                            @switch($role->name)
                                @case('admin')      <span>⚡</span> @break
                                @case('committee')  <span>📊</span> @break
                                @case('member')     <span>👤</span> @break
                            @endswitch
                            {{ ucfirst($role->name) }}
                            <span class="pm-role-perm-count" id="count-{{ $role->id }}">
                                {{ $role->permissions->count() }} permissions
                            </span>
                        </div>
                        <div class="pm-role-note">
                            Tick every permission this role should have. Changes take effect immediately for all users with this role.
                        </div>
                    </div>
                    <button type="submit" class="pm-save-btn">✓ Save Permissions</button>
                </div>

                @foreach($grouped as $category => $perms)
                <div class="pm-group">
                    <div class="pm-group-head">
                        {{ $category }}
                        <button type="button" class="pm-group-toggle"
                                onclick="toggleGroup(this, '{{ $role->id }}-{{ Str::slug($category) }}')">
                            Toggle all
                        </button>
                    </div>
                    <div class="pm-perm-grid" id="group-{{ $role->id }}-{{ Str::slug($category) }}">
                        @foreach($perms as $perm)
                        <label class="pm-perm-item">
                            <input type="checkbox"
                                   name="permissions[]"
                                   value="{{ $perm->id }}"
                                   onchange="updateCount({{ $role->id }})"
                                   {{ $role->permissions->contains($perm) ? 'checked' : '' }}>
                            <span class="pm-perm-label">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div style="padding:.85rem 1.1rem;background:var(--grey);border-top:1px solid var(--grey-mid);
                            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                    <span style="font-size:11px;color:var(--grey-dark);font-family:var(--font);">
                        <span id="count-{{ $role->id }}-num">{{ $role->permissions->count() }}</span>
                        of {{ $permissions->count() }} permissions assigned
                    </span>
                    <button type="submit" class="pm-save-btn">✓ Save Permissions</button>
                </div>

            </form>
        </div>
    @endif

</div>
@endforeach

{{-- Permission library pane --}}
<div class="pm-pane" id="pane-library">
    <div class="pm-library-card">
        <div class="pm-library-head">
            <div class="pm-library-title">📋 Permission Library ({{ $permissions->count() }} total)</div>
        </div>
        <table class="pm-library-table">
            <thead>
                <tr>
                    <th>Permission Name</th>
                    <th>Assigned to Roles</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions->sortBy('name') as $perm)
                <tr>
                    <td><span class="pm-perm-chip">{{ $perm->name }}</span></td>
                    <td>
                        @php
                            $assignedRoles = $roles->filter(fn($r) => $r->permissions->contains($perm));
                        @endphp
                        @forelse($assignedRoles as $r)
                            <span class="role-used-chip ruc-{{ $r->name }}">{{ $r->name }}</span>
                        @empty
                            <span style="font-size:11px;color:var(--grey-dark);">None</span>
                        @endforelse
                    </td>
                    <td style="text-align:right;">
                        <form method="POST"
                              action="{{ route('admin.super.permissions.delete', $perm) }}"
                              onsubmit="return confirm('Delete permission \"{{ $perm->name }}\"?\n\nThis will remove it from all roles immediately.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="pm-del-btn">✕ Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Add new permission --}}
        <form method="POST" action="{{ route('admin.super.permissions.create') }}">
            @csrf
            <div class="pm-add-form">
                <input type="text" name="name" class="pm-add-input"
                       placeholder="e.g. manage events"
                       value="{{ old('name') }}"
                       autocomplete="off">
                <button type="submit" class="pm-add-btn">+ Add Permission</button>
                <div class="pm-add-hint">
                    Use lowercase with spaces: <code style="font-family:monospace;background:var(--grey-mid);padding:1px 4px;">manage something</code> or
                    <code style="font-family:monospace;background:var(--grey-mid);padding:1px 4px;">view something</code>.
                    After adding, go to the role tabs above to assign it.
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(btn, paneId) {
    document.querySelectorAll('.pm-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pm-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(paneId).classList.add('active');
}

function toggleGroup(btn, groupId) {
    var group = document.getElementById('group-' + groupId);
    if (!group) return;
    var boxes  = group.querySelectorAll('input[type="checkbox"]');
    var allOn  = Array.from(boxes).every(cb => cb.checked);
    boxes.forEach(cb => { cb.checked = !allOn; });
    // Trigger count update — find role id from group id
    var roleId = groupId.split('-')[0];
    updateCount(parseInt(roleId));
}

function updateCount(roleId) {
    var form   = document.querySelector('#pane-' + roleId + ' form');
    if (!form) return;
    var checked = form.querySelectorAll('input[type="checkbox"]:checked').length;
    var total   = form.querySelectorAll('input[type="checkbox"]').length;

    var chipEl = document.getElementById('count-' + roleId);
    var numEl  = document.getElementById('count-' + roleId + '-num');
    if (chipEl) chipEl.textContent = checked + ' permissions';
    if (numEl)  numEl.textContent  = checked;
}
</script>

<div style="max-width:900px;margin:2rem auto 0;padding:0 1rem;">
    <div style="font-size:1rem;font-weight:700;color:#003366;margin-bottom:.5rem;padding-bottom:.5rem;border-bottom:2px solid #003366;">📸 Photo Permissions — Per User</div>
    <p style="font-size:.82rem;color:#6b7f96;margin-bottom:1rem;line-height:1.5;">Grant individual members permission to approve or feature photos, regardless of their role. Admins always have both automatically.</p>
    @php $photoUsers = \App\Models\User::role(['member','committee','admin','super-admin'])->orderBy('name')->get(); @endphp
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        <div style="background:#1e0040;padding:.6rem 1rem;display:grid;grid-template-columns:1fr 160px 160px;gap:.5rem;">
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(233,213,255,.5);">Member</div>
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(233,213,255,.5);text-align:center;">Approve Photos</div>
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(233,213,255,.5);text-align:center;">Feature Photos</div>
        </div>
        @foreach($photoUsers as $pu)
        @php $hasApprove=$pu->hasPermissionTo('approve photos'); $hasFeature=$pu->hasPermissionTo('feature photos'); $puIsAdmin=$pu->isAdmin(); @endphp
        <div style="display:grid;grid-template-columns:1fr 160px 160px;gap:.5rem;align-items:center;padding:.65rem 1rem;border-top:1px solid #f3f4f6;{{ $loop->even ? 'background:#fafafa;' : '' }}">
            <div>
                <div style="font-size:.85rem;font-weight:600;color:#003366;">{{ $pu->name }}</div>
                <div style="font-size:.72rem;color:#6b7f96;">@if($pu->callsign)<span style="font-family:monospace;">{{ strtoupper($pu->callsign) }}</span> · @endif{{ $pu->roles->pluck('name')->implode(', ') }}</div>
            </div>
            <div style="text-align:center;">
                @if($puIsAdmin)<span style="font-size:.72rem;color:#059669;font-weight:bold;">✓ Admin</span>
                @else<form method="POST" action="{{ route('admin.super.admin.super.permissions.user-toggle') }}">@csrf<input type="hidden" name="user_id" value="{{ $pu->id }}"><input type="hidden" name="permission" value="approve photos"><button type="submit" style="background:{{ $hasApprove ? '#d1fae5' : '#f3f4f6' }};color:{{ $hasApprove ? '#065f46' : '#6b7f96' }};border:1px solid {{ $hasApprove ? '#6ee7b7' : '#e5e7eb' }};padding:.3rem .9rem;border-radius:999px;font-size:.75rem;font-weight:bold;cursor:pointer;width:100%;">{{ $hasApprove ? '✓ Granted' : '+ Grant' }}</button></form>@endif
            </div>
            <div style="text-align:center;">
                @if($puIsAdmin)<span style="font-size:.72rem;color:#059669;font-weight:bold;">✓ Admin</span>
                @else<form method="POST" action="{{ route('admin.super.admin.super.permissions.user-toggle') }}">@csrf<input type="hidden" name="user_id" value="{{ $pu->id }}"><input type="hidden" name="permission" value="feature photos"><button type="submit" style="background:{{ $hasFeature ? '#fef3c7' : '#f3f4f6' }};color:{{ $hasFeature ? '#92400e' : '#6b7f96' }};border:1px solid {{ $hasFeature ? '#fcd34d' : '#e5e7eb' }};padding:.3rem .9rem;border-radius:999px;font-size:.75rem;font-weight:bold;cursor:pointer;width:100%;">{{ $hasFeature ? '⭐ Granted' : '+ Grant' }}</button></form>@endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
