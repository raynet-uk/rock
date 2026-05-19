{{-- resources/views/admin/users/roles.blade.php --}}
@extends('layouts.admin')

@section('title', 'Role Management — Admin')

@section('content')
<style>
/* ═══════════════════════════════════════════════════
   ROLE MANAGEMENT — {{ \App\Helpers\RaynetSetting::groupName() }}
   Brand: Navy #003366 · Red #C8102E · Grey #F2F2F2
═══════════════════════════════════════════════════ */
:root {
    --sa-color: #7c3aed; --sa-bg: rgba(124,58,237,.1); --sa-border: rgba(124,58,237,.3);
    --ad-color: #C8102E; --ad-bg: rgba(200,16,46,.08); --ad-border: rgba(200,16,46,.25);
    --co-color: #d97706; --co-bg: rgba(217,119,6,.08); --co-border: rgba(217,119,6,.25);
    --me-color: #16a34a; --me-bg: rgba(22,163,74,.08); --me-border: rgba(22,163,74,.2);
}

/* ── HERO HEADER ── */
.rm-hero {
    background: linear-gradient(135deg, #001f40 0%, var(--navy) 50%, #004080 100%);
    border-bottom: 4px solid var(--red);
    padding: 0;
    margin: -1.5rem -1rem 0;
    position: relative;
    overflow: hidden;
}
.rm-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: repeating-linear-gradient(-45deg,transparent,transparent 30px,rgba(200,16,46,.03) 30px,rgba(200,16,46,.03) 60px);
}
.rm-hero-inner {
    max-width: 1340px; margin: 0 auto; padding: 1.5rem 1.5rem 0;
    position: relative; z-index: 1;
}
.rm-hero-top {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.rm-hero-title { font-size: 26px; font-weight: 700; color: #fff; font-family: var(--font); line-height: 1.1; }
.rm-hero-sub { font-size: 12px; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .12em; margin-top: 4px; }
.rm-hero-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.rm-hero-btn {
    font-size: 11px; font-weight: 700; font-family: var(--font); text-transform: uppercase;
    letter-spacing: .07em; padding: .4rem .9rem; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem; transition: all .12s;
}
.rm-hero-btn.ghost { border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.6); background: rgba(255,255,255,.06); }
.rm-hero-btn.ghost:hover { background: rgba(255,255,255,.12); color: #fff; }
.rm-hero-btn.primary { border: 1px solid rgba(200,16,46,.5); color: #fff; background: var(--red); }
.rm-hero-btn.primary:hover { background: #a50e26; }

/* ── ROLE CARDS (in hero) ── */
.rm-role-cards {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 0;
    border-top: 1px solid rgba(255,255,255,.1);
}
@media(max-width:900px) { .rm-role-cards { grid-template-columns: 1fr 1fr; } }
.rm-role-card {
    padding: 1rem 1.25rem;
    border-right: 1px solid rgba(255,255,255,.08);
    cursor: pointer; transition: background .15s;
    position: relative; overflow: hidden;
}
.rm-role-card:last-child { border-right: none; }
.rm-role-card:hover { background: rgba(255,255,255,.06); }
.rm-role-card.active { background: rgba(255,255,255,.1); }
.rm-role-card.active::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
}
.rm-role-card[data-role="super-admin"].active::after { background: var(--sa-color); }
.rm-role-card[data-role="admin"].active::after       { background: var(--ad-color); }
.rm-role-card[data-role="committee"].active::after   { background: var(--co-color); }
.rm-role-card[data-role="member"].active::after      { background: var(--me-color); }
.rm-role-card[data-role="all"].active::after         { background: #fff; }
.rmc-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.4); margin-bottom: .35rem; display: flex; align-items: center; gap: .35rem; }
.rmc-count { font-size: 32px; font-weight: 700; line-height: 1; color: #fff; }
.rmc-bar { height: 3px; background: rgba(255,255,255,.1); margin-top: .5rem; border-radius: 1px; overflow: hidden; }
.rmc-bar-fill { height: 100%; transition: width .6s ease; }
.rm-role-card[data-role="super-admin"] .rmc-bar-fill { background: var(--sa-color); }
.rm-role-card[data-role="admin"]       .rmc-bar-fill { background: var(--ad-color); }
.rm-role-card[data-role="committee"]   .rmc-bar-fill { background: var(--co-color); }
.rm-role-card[data-role="member"]      .rmc-bar-fill { background: var(--me-color); }
.rm-role-card[data-role="all"]         .rmc-bar-fill { background: rgba(255,255,255,.5); }

/* ── TOOLBAR ── */
.rm-toolbar {
    background: #fff; border-bottom: 1px solid var(--grey-mid);
    padding: .85rem 1.5rem; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    position: sticky; top: 62px; z-index: 50;
    box-shadow: 0 2px 8px rgba(0,51,102,.06);
}
.rm-search-wrap { position: relative; flex: 1; min-width: 200px; }
.rm-search-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); font-size: 14px; color: var(--grey-dark); pointer-events: none; }
.rm-search {
    width: 100%; padding: .5rem .75rem .5rem 2.2rem;
    border: 1px solid var(--grey-mid); font-family: var(--font); font-size: 13px;
    color: var(--navy); outline: none; transition: border-color .15s, box-shadow .15s;
}
.rm-search:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.rm-sort-select {
    padding: .5rem .75rem; border: 1px solid var(--grey-mid); font-family: var(--font);
    font-size: 12px; color: var(--navy); background: #fff; cursor: pointer; outline: none;
}
.rm-toolbar-sep { width: 1px; height: 24px; background: var(--grey-mid); flex-shrink: 0; }
.rm-result-count { font-size: 11px; color: var(--grey-dark); font-family: var(--font); white-space: nowrap; }

/* ── BULK BAR ── */
.rm-bulk-bar {
    background: #003366; padding: .65rem 1.5rem;
    display: none; align-items: center; gap: .75rem; flex-wrap: wrap;
    border-bottom: 1px solid rgba(255,255,255,.1);
}
.rm-bulk-bar.visible { display: flex; animation: slideDown .2s ease; }
@keyframes slideDown { from { opacity:0; transform:translateY(-4px); } to { opacity:1; } }
.rm-bulk-count { font-size: 13px; font-weight: 700; color: #fff; font-family: var(--font); }
.rm-bulk-select {
    padding: .35rem .65rem; border: 1px solid rgba(255,255,255,.2); font-family: var(--font);
    font-size: 12px; color: #fff; background: rgba(255,255,255,.1);
}
.rm-bulk-select option { background: var(--navy); }
.rm-bulk-apply {
    padding: .35rem .9rem; background: var(--red); border: 1px solid rgba(200,16,46,.5);
    color: #fff; font-family: var(--font); font-size: 11px; font-weight: 700;
    cursor: pointer; text-transform: uppercase; letter-spacing: .06em;
}
.rm-bulk-apply:hover { background: #a50e26; }
.rm-bulk-clear { padding: .35rem .75rem; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: rgba(255,255,255,.6); font-family: var(--font); font-size: 11px; cursor: pointer; }

/* ── TABLE ── */
.rm-wrap { max-width: 1340px; margin: 0 auto; padding: 1.25rem 1.5rem 4rem; }
.rm-table-card { background: #fff; border: 1px solid var(--grey-mid); box-shadow: 0 1px 4px rgba(0,51,102,.06); overflow: hidden; }
.rm-table { width: 100%; border-collapse: collapse; font-family: var(--font); font-size: 13px; }
.rm-table thead tr { background: #001f40; }
.rm-table th {
    color: rgba(255,255,255,.45); text-transform: uppercase; font-size: 10px; font-weight: 700;
    letter-spacing: .12em; padding: .55rem 1rem; text-align: left; white-space: nowrap;
    border-bottom: 2px solid var(--red); cursor: pointer; user-select: none;
    transition: color .12s;
}
.rm-table th:hover { color: rgba(255,255,255,.8); }
.rm-table th.sorted { color: #fff; }
.rm-table th .sort-arrow { font-size: 9px; margin-left: 3px; opacity: .5; }
.rm-table th.sorted .sort-arrow { opacity: 1; }
.rm-table th.cb-col { width: 44px; cursor: default; }
.rm-table td {
    padding: .8rem 1rem; border-bottom: 1px solid var(--grey-mid);
    vertical-align: middle;
}
.rm-table tr:last-child td { border-bottom: none; }
.rm-table tbody tr { transition: background .1s; }
.rm-table tbody tr:hover td { background: var(--navy-faint); }
.rm-table tbody tr.selected td { background: #f0f5ff; }
.rm-table tbody tr.is-self td { background: rgba(0,51,102,.02); }
.rm-table tbody tr.row-hidden { display: none; }

/* ── USER CELL ── */
.rm-user-cell { display: flex; align-items: center; gap: .75rem; }
.rm-av {
    width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 14px; font-weight: 700; color: #fff;
    flex-shrink: 0; text-transform: uppercase; position: relative;
}
.rm-av.role-super-admin { background: linear-gradient(135deg,#4c1d95,#7c3aed); }
.rm-av.role-admin       { background: linear-gradient(135deg,#7f1d1d,var(--red)); }
.rm-av.role-committee   { background: linear-gradient(135deg,#78350f,#d97706); }
.rm-av.role-member      { background: linear-gradient(135deg,#14532d,#16a34a); }
.rm-av-status {
    position: absolute; bottom: 0; right: 0;
    width: 10px; height: 10px; border-radius: 50%; border: 2px solid #fff;
    background: var(--grey-dark);
}
.rm-av-status.online { background: #16a34a; }
.rm-name { font-weight: 700; color: var(--navy); font-size: 13px; line-height: 1.2; }
.rm-email { font-size: 11px; color: var(--grey-dark); margin-top: 1px; }
.rm-meta { display: flex; gap: .35rem; flex-wrap: wrap; margin-top: 4px; align-items: center; }
.rm-callsign {
    font-family: 'Courier New', monospace; font-size: 11px; font-weight: 700;
    color: var(--navy); background: var(--navy-faint);
    border: 1px solid rgba(0,51,102,.2); padding: 1px 6px; letter-spacing: .08em;
}
.rm-you-badge {
    font-size: 9px; font-weight: 700; padding: 1px 5px;
    background: rgba(0,51,102,.08); border: 1px solid rgba(0,51,102,.2);
    color: var(--navy); text-transform: uppercase; letter-spacing: .05em;
}

/* ── ROLE BADGE ── */
.rm-role-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: 3px 9px; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; white-space: nowrap;
}
.rm-role-badge.super-admin { background: var(--sa-bg); border: 1px solid var(--sa-border); color: var(--sa-color); }
.rm-role-badge.admin       { background: var(--ad-bg); border: 1px solid var(--ad-border); color: var(--ad-color); }
.rm-role-badge.committee   { background: var(--co-bg); border: 1px solid var(--co-border); color: var(--co-color); }
.rm-role-badge.member      { background: var(--me-bg); border: 1px solid var(--me-border); color: var(--me-color); }
.rm-role-icon { font-size: 11px; }

/* ── ROLE CHANGE CELL ── */
.rm-change-cell { display: flex; align-items: center; gap: .4rem; }
.rm-role-select {
    padding: .35rem .55rem; border: 1px solid var(--grey-mid); font-family: var(--font);
    font-size: 12px; color: var(--navy); background: #fff; cursor: pointer; outline: none;
    transition: border-color .12s;
}
.rm-role-select:focus { border-color: var(--navy); }
.rm-role-select:disabled { opacity: .4; cursor: not-allowed; }
.rm-save-btn {
    padding: .35rem .75rem; background: var(--navy); border: 1px solid var(--navy);
    color: #fff; font-family: var(--font); font-size: 11px; font-weight: 700;
    cursor: pointer; text-transform: uppercase; letter-spacing: .04em;
    opacity: 0; pointer-events: none; transition: all .15s; white-space: nowrap;
}
.rm-save-btn.show { opacity: 1; pointer-events: all; }
.rm-save-btn:hover { background: #002244; }
.rm-saving-indicator { font-size: 11px; color: var(--grey-dark); font-family: var(--font); display: none; }

/* ── OPERATOR TITLE CELL ── */
.rm-op-title { font-size: 12px; color: var(--text-mid); }
.rm-no-title { font-size: 11px; color: var(--grey-dark); font-style: italic; }

/* ── JOIN DATE ── */
.rm-join-date { font-size: 11px; color: var(--grey-dark); white-space: nowrap; }
.rm-join-new {
    font-size: 9px; font-weight: 700; padding: 1px 5px;
    background: #e0f2fe; border: 1px solid #7dd3fc; color: #0369a1;
    text-transform: uppercase; letter-spacing: .05em; margin-left: 4px;
}

/* ── PROTECTED ROW ── */
.rm-protected-note { font-size: 11px; color: var(--grey-dark); font-family: var(--font); font-style: italic; }

/* ── EMPTY STATE ── */
.rm-empty {
    padding: 3.5rem; text-align: center;
}
.rm-empty-icon { font-size: 2.5rem; opacity: .15; margin-bottom: .75rem; }
.rm-empty-text { font-size: 13px; color: var(--grey-dark); font-family: var(--font); }

/* ── PAGINATION ── */
.rm-pagination {
    padding: .85rem 1.25rem; background: var(--grey);
    border-top: 1px solid var(--grey-mid);
    display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
}
.rm-pagination-info { font-size: 11px; color: var(--grey-dark); font-family: var(--font); }

/* ── TOAST ── */
#rm-toast-wrap {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    display: flex; flex-direction: column; gap: .5rem; pointer-events: none;
}
.rm-toast {
    display: flex; align-items: center; gap: .65rem;
    padding: .7rem 1.1rem;
    background: var(--navy); border-left: 3px solid var(--red);
    color: #fff; font-size: 13px; font-family: var(--font);
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
    animation: toastIn .25s ease;
    pointer-events: all; min-width: 260px; max-width: 380px;
}
.rm-toast.success { border-left-color: #16a34a; }
.rm-toast.error   { border-left-color: var(--red); }
@keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }
@keyframes toastOut { to { opacity:0; transform:translateX(20px); } }
.rm-toast.out { animation: toastOut .2s ease forwards; }

/* ── KEYBOARD SHORTCUT HINT ── */
.rm-kbd-hint {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 700; padding: 1px 5px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.5); font-family: monospace; letter-spacing: .04em;
}

/* ── ALERTS ── */
.rm-alert {
    padding: .65rem 1.25rem; margin-bottom: 1rem; font-size: 13px; font-family: var(--font);
    display: flex; align-items: center; gap: .5rem;
}
.rm-alert.success { background: #eef7f2; border: 1px solid rgba(22,163,74,.3); border-left: 3px solid #16a34a; color: #14532d; }
.rm-alert.error   { background: var(--red-faint); border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red); color: var(--red); }

/* ── ACTIVE FILTER CHIPS ── */
.rm-active-filters { display: flex; gap: .4rem; flex-wrap: wrap; align-items: center; padding: 0 1.5rem .75rem; }
.rm-filter-chip {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: 11px; font-weight: 700; padding: 3px 8px;
    background: var(--navy-faint); border: 1px solid rgba(0,51,102,.2); color: var(--navy);
    text-transform: uppercase; letter-spacing: .05em;
}
.rm-filter-chip-remove { cursor: pointer; opacity: .5; font-size: 13px; line-height: 1; }
.rm-filter-chip-remove:hover { opacity: 1; }
</style>

{{-- Toast container --}}
<div id="rm-toast-wrap"></div>

{{-- ── HERO ── --}}
<div class="rm-hero">
    <div class="rm-hero-inner">
        <div class="rm-hero-top">
            <div>
                <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.14em;margin-bottom:.4rem;">
                    Admin Panel · Access Control
                </div>
                <div class="rm-hero-title">🎭 Role Management</div>
                <div class="rm-hero-sub">Assign Spatie roles · Manage member access levels</div>
            </div>
            <div class="rm-hero-actions">
                <span class="rm-kbd-hint">?</span>
                <span style="font-size:10px;color:rgba(255,255,255,.3);font-family:var(--font);">keyboard shortcuts</span>
                <a href="{{ route('admin.users.index') }}" class="rm-hero-btn ghost">👥 All Members</a>
                <a href="{{ route('admin.dashboard') }}" class="rm-hero-btn ghost">← Admin Panel</a>
                @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.super.permissions.index') }}" class="rm-hero-btn primary">★ Permissions</a>
                @endif
            </div>
        </div>

        {{-- Role cards --}}
        @php
            $totalUsers = $users->total();
            $roleMap = ['all' => $totalUsers, 'super-admin' => 0, 'admin' => 0, 'committee' => 0, 'member' => 0];
            foreach($roleCounts as $rc) {
                $roleMap[$rc->name] = $rc->users_count ?? 0;
            }
            $roleIcons = ['super-admin' => '★', 'admin' => '⚡', 'committee' => '📊', 'member' => '👤', 'all' => '👥'];
        @endphp
        <div class="rm-role-cards">
            @foreach(['all', 'super-admin', 'admin', 'committee', 'member'] as $rk)
            <div class="rm-role-card {{ $roleFilter === $rk || ($rk === 'all' && !$roleFilter) ? 'active' : '' }}"
                 data-role="{{ $rk }}"
                 onclick="filterByRole('{{ $rk }}')">
                <div class="rmc-label">
                    {{ $roleIcons[$rk] }} {{ $rk === 'all' ? 'All Roles' : ucwords(str_replace(['-','_'],' ',$rk)) }}
                </div>
                <div class="rmc-count">{{ $roleMap[$rk] }}</div>
                <div class="rmc-bar">
                    <div class="rmc-bar-fill"
                         style="width:{{ $totalUsers > 0 ? round(($roleMap[$rk] / $totalUsers) * 100) : 0 }}%">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── ALERTS ── --}}
<div class="rm-wrap" style="padding-bottom:0;">
    @if(session('success'))
        <div class="rm-alert success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rm-alert error">✕ {{ session('error') }}</div>
    @endif
</div>

{{-- ── TOOLBAR ── --}}
<form id="filterForm" method="GET" action="{{ route('admin.users.roles') }}">
    <input type="hidden" name="role" id="roleFilterHidden" value="{{ $roleFilter }}">
    <input type="hidden" name="sort" id="sortHidden"       value="{{ request('sort','name') }}">
    <input type="hidden" name="dir"  id="dirHidden"        value="{{ request('dir','asc') }}">

    <div class="rm-toolbar">
        <div class="rm-search-wrap">
            <span class="rm-search-icon">⌕</span>
            <input type="text" name="search" id="rmSearch" class="rm-search"
                   placeholder="Search name, email, callsign…"
                   value="{{ $search }}"
                   autocomplete="off">
        </div>
        <select name="sort" class="rm-sort-select" onchange="this.form.submit()">
            <option value="name"       {{ request('sort','name') === 'name'       ? 'selected' : '' }}>Sort: Name A–Z</option>
            <option value="name_desc"  {{ request('sort') === 'name_desc'         ? 'selected' : '' }}>Sort: Name Z–A</option>
            <option value="role"       {{ request('sort') === 'role'              ? 'selected' : '' }}>Sort: Role</option>
            <option value="joined"     {{ request('sort') === 'joined'            ? 'selected' : '' }}>Sort: Newest first</option>
            <option value="joined_asc" {{ request('sort') === 'joined_asc'        ? 'selected' : '' }}>Sort: Oldest first</option>
        </select>
        <button type="submit" style="padding:.5rem 1rem;background:var(--navy);border:none;color:#fff;font-family:var(--font);font-size:12px;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;">
            Filter
        </button>
        @if($search || $roleFilter)
        <a href="{{ route('admin.users.roles') }}"
           style="padding:.5rem .85rem;background:var(--grey);border:1px solid var(--grey-mid);color:var(--grey-dark);font-family:var(--font);font-size:12px;text-decoration:none;">
            ✕ Clear
        </a>
        @endif
        <div class="rm-toolbar-sep"></div>
        <span class="rm-result-count" id="rmResultCount">
            {{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }}
        </span>
    </div>

    {{-- Active filter chips --}}
    @if($search || $roleFilter)
    <div class="rm-active-filters" style="background:#fff;border-bottom:1px solid var(--grey-mid);padding-top:.5rem;">
        @if($roleFilter)
        <div class="rm-filter-chip">
            Role: {{ ucwords(str_replace(['-','_'],' ',$roleFilter)) }}
            <a href="{{ route('admin.users.roles', array_merge(request()->except('role'), ['search' => $search])) }}"
               class="rm-filter-chip-remove">×</a>
        </div>
        @endif
        @if($search)
        <div class="rm-filter-chip">
            Search: "{{ $search }}"
            <a href="{{ route('admin.users.roles', array_merge(request()->except('search'), ['role' => $roleFilter])) }}"
               class="rm-filter-chip-remove">×</a>
        </div>
        @endif
    </div>
    @endif
</form>

{{-- ── BULK BAR ── --}}
<form method="POST" action="{{ route('admin.users.roles.bulk') }}" id="bulkForm">
    @csrf
    <div class="rm-bulk-bar" id="bulkBar">
        <span class="rm-bulk-count"><span id="bulkCount">0</span> users selected</span>
        <span style="font-size:11px;color:rgba(255,255,255,.4);font-family:var(--font);">→ change to</span>
        <select name="role" class="rm-bulk-select">
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucwords(str_replace(['-','_'],' ',$role->name)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rm-bulk-apply"
                onclick="return confirm('Change role for all ' + document.getElementById('bulkCount').textContent + ' selected users?')">
            Apply
        </button>
        <button type="button" class="rm-bulk-clear" onclick="clearSelection()">
            Deselect all
        </button>
    </div>

{{-- ── TABLE ── --}}
<div class="rm-wrap">
    @if(session('success'))
    @endif

    <div class="rm-table-card">
        <table class="rm-table" id="rmTable">
            <thead>
                <tr>
                    <th class="cb-col">
                        <input type="checkbox" id="selectAll"
                               style="accent-color:var(--red);cursor:pointer;width:15px;height:15px;"
                               onchange="toggleAll(this)">
                    </th>
                    <th onclick="sortBy('name')" class="{{ request('sort','name') === 'name' || request('sort') === 'name_desc' ? 'sorted' : '' }}">
                        Member <span class="sort-arrow">{{ request('sort') === 'name_desc' ? '↓' : '↑' }}</span>
                    </th>
                    <th onclick="sortBy('role')" class="{{ request('sort') === 'role' ? 'sorted' : '' }}">
                        Current Role <span class="sort-arrow">↕</span>
                    </th>
                    <th>Change Role</th>
                    <th>Operator Title</th>
                    <th onclick="sortBy('joined')" class="{{ in_array(request('sort'), ['joined','joined_asc']) ? 'sorted' : '' }}">
                        Joined <span class="sort-arrow">{{ request('sort') === 'joined_asc' ? '↑' : '↓' }}</span>
                    </th>
                </tr>
            </thead>
            <tbody id="rmTableBody">
                @forelse($users as $user)
                    @php
                        $isSelf      = $user->id === auth()->id();
                        $isProtected = $user->isSuperAdmin() && !auth()->user()->isSuperAdmin();
                        $currentRole = $user->getRoleNames()->first() ?? 'member';
                        $initials    = strtoupper(substr($user->name, 0, 1));
                        $isNew       = $user->created_at->gt(now()->subDays(7));
                        $roleIcons2  = ['super-admin'=>'★','admin'=>'⚡','committee'=>'📊','member'=>'👤'];
                    @endphp
                    <tr class="{{ $isSelf ? 'is-self' : '' }}"
                        data-user-id="{{ $user->id }}"
                        data-role="{{ $currentRole }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-search="{{ auth()->user()->isTemporaryAdmin() ? strtolower($user->name) : strtolower($user->name.' '.$user->email.' '.($user->callsign ?? '')) }}">

                        {{-- Checkbox --}}
                        <td style="text-align:center;">
                            @if(!$isSelf && !$isProtected)
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                   class="row-cb"
                                   style="accent-color:var(--red);cursor:pointer;width:15px;height:15px;"
                                   onchange="updateBulkBar()">
                            @endif
                        </td>

                        {{-- User --}}
                        <td>
                            <div class="rm-user-cell">
                                <div class="rm-av role-{{ $currentRole }}">
                                    {{ $initials }}
                                    <div class="rm-av-status"></div>
                                </div>
                                <div>
                                    <div class="rm-name">
                                        {{ $user->name }}
                                        @if($isSelf) <span class="rm-you-badge">You</span> @endif
                                        @if($isNew)  <span class="rm-join-new">New</span> @endif
                                    </div>
                                    <div class="rm-email">{{ $user->email }}</div>
                                    <div class="rm-meta">
                                        @if($user->callsign)
                                            <span @if(auth()->user()->isTemporaryAdmin() && !$user->piiVisible()) style="filter:blur(3px);user-select:none;" @endif class="rm-callsign">{{ $user->callsign }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Current role --}}
                        <td>
                            <span class="rm-role-badge {{ $currentRole }}" id="badge-{{ $user->id }}">
                                <span class="rm-role-icon">{{ $roleIcons2[$currentRole] ?? '👤' }}</span>
                                {{ $currentRole }}
                            </span>
                        </td>

                        {{-- Change role --}}
                        <td>
                            @if($isSelf)
                                <span class="rm-protected-note">Cannot change own role</span>
                            @elseif($isProtected)
                                <span class="rm-protected-note">★ Super-admin protected</span>
                            @else
                                <div class="rm-change-cell">
                                    <select class="rm-role-select"
                                            data-user="{{ $user->id }}"
                                            data-original="{{ $currentRole }}"
                                            onchange="onRoleChange(this)">
                                        @foreach($roles as $role)
                                            @if($role->name === 'super-admin' && !auth()->user()->isSuperAdmin())
                                                @continue
                                            @endif
                                            <option value="{{ $role->name }}"
                                                    @selected($currentRole === $role->name)>
                                                {{ $roleIcons2[$role->name] ?? '' }} {{ ucwords(str_replace(['-','_'],' ',$role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button"
                                            class="rm-save-btn"
                                            id="save-{{ $user->id }}"
                                            onclick="saveRole({{ $user->id }}, this)">
                                        ✓ Save
                                    </button>
                                    <span class="rm-saving-indicator" id="saving-{{ $user->id }}">
                                        ⟳ Saving…
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Operator title --}}
                        <td>
                            @if($user->operator_title)
                                <span class="rm-op-title">{{ $user->operator_title }}</span>
                            @else
                                <span class="rm-no-title">—</span>
                            @endif
                        </td>

                        {{-- Joined --}}
                        <td>
                            <div class="rm-join-date">
                                {{ $user->created_at?->format('d M Y') ?? '—' }}
                            </div>
                            <div style="font-size:10px;color:var(--grey-dark);margin-top:1px;">
                                {{ $user->created_at?->diffForHumans() ?? '' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="rm-empty">
                                <div class="rm-empty-icon">👥</div>
                                <div class="rm-empty-text">No users found matching your filters.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="rm-pagination">
            <span class="rm-pagination-info">
                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
            </span>
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
</form>

{{-- Keyboard shortcut help overlay --}}
<div id="kbOverlay" style="display:none;position:fixed;inset:0;z-index:999;background:rgba(0,10,30,.7);backdrop-filter:blur(3px);align-items:center;justify-content:center;">
    <div style="background:#fff;border-top:3px solid var(--red);max-width:400px;width:100%;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="font-size:14px;font-weight:700;color:var(--navy);margin-bottom:1rem;display:flex;justify-content:space-between;">
            Keyboard Shortcuts
            <button onclick="toggleKb()" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--grey-dark);">×</button>
        </div>
        @foreach([['/', 'Focus search'], ['Esc', 'Clear search / close'], ['A', 'Select all visible'], ['D', 'Deselect all'], ['Ctrl+Enter', 'Submit bulk change']] as [$k, $v])
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--grey-mid);">
            <span style="font-size:13px;color:var(--text-mid);">{{ $v }}</span>
            <kbd style="font-size:11px;font-family:monospace;background:var(--grey);border:1px solid var(--grey-mid);padding:2px 7px;color:var(--navy);">{{ $k }}</kbd>
        </div>
        @endforeach
    </div>
</div>

<script>
var CSRF = document.querySelector('meta[name="csrf-token"]').content;
var ROLE_ICONS = {'super-admin':'★','admin':'⚡','committee':'📊','member':'👤'};

/* ── Toast ── */
function toast(msg, type) {
    type = type || 'success';
    var wrap = document.getElementById('rm-toast-wrap');
    var t    = document.createElement('div');
    t.className = 'rm-toast ' + type;
    t.innerHTML = (type === 'success' ? '✓' : '✕') + ' ' + msg;
    wrap.appendChild(t);
    setTimeout(function() {
        t.classList.add('out');
        setTimeout(function() { if (t.parentNode) t.remove(); }, 250);
    }, 3500);
}

/* ── Role filter (click card) ── */
function filterByRole(role) {
    document.getElementById('roleFilterHidden').value = (role === 'all') ? '' : role;
    document.getElementById('filterForm').submit();
}

/* ── Column sort ── */
function sortBy(col) {
    var current = document.querySelector('[name="sort"]').value;
    var newSort = col;
    if (current === col) newSort = col + '_desc';
    else if (current === col + '_desc') newSort = col;
    document.getElementById('sortHidden').value = newSort;
    document.getElementById('filterForm').submit();
}

/* ── Role change detection ── */
function onRoleChange(select) {
    var userId  = select.dataset.user;
    var btn     = document.getElementById('save-' + userId);
    var changed = select.value !== select.dataset.original;
    btn.classList.toggle('show', changed);
}

/* ── AJAX role save ── */
function saveRole(userId, btn) {
    var row     = btn.closest('tr');
    var select  = row.querySelector('.rm-role-select');
    var saving  = document.getElementById('saving-' + userId);
    var badge   = document.getElementById('badge-' + userId);
    var newRole = select.value;

    btn.style.display    = 'none';
    saving.style.display = 'inline';

    fetch('/admin/users/' + userId + '/role', {
        method:  'PATCH',
        headers: {
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     CSRF,
            'Accept':           'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ role: newRole }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        saving.style.display = 'none';
        if (data.success) {
            // Update badge
            badge.className   = 'rm-role-badge ' + newRole;
            badge.innerHTML   = '<span class="rm-role-icon">' + (ROLE_ICONS[newRole] || '👤') + '</span> ' + newRole;

            // Update avatar colour
            var av = row.querySelector('.rm-av');
            av.className = 'rm-av role-' + newRole;

            // Update row data attr & original
            row.dataset.role          = newRole;
            select.dataset.original   = newRole;
            btn.classList.remove('show');
            btn.style.display = '';

            // Recount cards
            updateRoleCards();
            toast(data.message || 'Role updated.', 'success');
        } else {
            btn.style.display = '';
            btn.classList.add('show');
            toast(data.message || 'Error updating role.', 'error');
        }
    })
    .catch(function() {
        saving.style.display = 'none';
        btn.style.display    = '';
        btn.classList.add('show');
        toast('Network error — please try again.', 'error');
    });
}

/* ── Update role cards after AJAX save ── */
function updateRoleCards() {
    var rows    = document.querySelectorAll('#rmTableBody tr[data-role]');
    var counts  = {'super-admin':0,'admin':0,'committee':0,'member':0};
    var total   = 0;
    rows.forEach(function(r) {
        var role = r.dataset.role;
        if (counts[role] !== undefined) { counts[role]++; total++; }
    });
    Object.keys(counts).forEach(function(role) {
        var card = document.querySelector('.rm-role-card[data-role="' + role + '"]');
        if (card) {
            card.querySelector('.rmc-count').textContent = counts[role];
            var pct = total > 0 ? Math.round((counts[role] / total) * 100) : 0;
            card.querySelector('.rmc-bar-fill').style.width = pct + '%';
        }
    });
}

/* ── Bulk selection ── */
function toggleAll(master) {
    document.querySelectorAll('.row-cb').forEach(function(cb) {
        cb.checked = master.checked;
        cb.closest('tr').classList.toggle('selected', master.checked);
    });
    updateBulkBar();
}

function updateBulkBar() {
    var checked = document.querySelectorAll('.row-cb:checked').length;
    document.getElementById('bulkCount').textContent = checked;
    document.getElementById('bulkBar').classList.toggle('visible', checked > 0);
    document.querySelectorAll('.row-cb').forEach(function(cb) {
        cb.closest('tr').classList.toggle('selected', cb.checked);
    });
    document.getElementById('selectAll').indeterminate =
        checked > 0 && checked < document.querySelectorAll('.row-cb').length;
    document.getElementById('selectAll').checked =
        checked === document.querySelectorAll('.row-cb').length && checked > 0;
}

function clearSelection() {
    document.querySelectorAll('.row-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateBulkBar();
}

/* ── Client-side live search ── */
var searchTimer = null;
document.getElementById('rmSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    var q = this.value.toLowerCase().trim();
    searchTimer = setTimeout(function() {
        var rows = document.querySelectorAll('#rmTableBody tr[data-search]');
        var vis  = 0;
        rows.forEach(function(row) {
            var match = !q || row.dataset.search.includes(q);
            row.classList.toggle('row-hidden', !match);
            if (match) vis++;
        });
        document.getElementById('rmResultCount').textContent = vis + ' user' + (vis !== 1 ? 's' : '');
    }, 150);
});

/* ── Keyboard shortcuts ── */
function toggleKb() {
    var ol = document.getElementById('kbOverlay');
    ol.style.display = ol.style.display === 'flex' ? 'none' : 'flex';
}
document.addEventListener('keydown', function(e) {
    var tag = document.activeElement.tagName.toLowerCase();
    var inInput = (tag === 'input' || tag === 'select' || tag === 'textarea');

    if (e.key === '?' && !inInput) { e.preventDefault(); toggleKb(); return; }
    if (e.key === 'Escape') {
        document.getElementById('kbOverlay').style.display = 'none';
        if (document.getElementById('rmSearch') === document.activeElement) {
            document.getElementById('rmSearch').value = '';
            document.getElementById('rmSearch').dispatchEvent(new Event('input'));
            document.getElementById('rmSearch').blur();
        }
        return;
    }
    if (e.key === '/' && !inInput) {
        e.preventDefault();
        document.getElementById('rmSearch').focus();
        document.getElementById('rmSearch').select();
        return;
    }
    if (e.key === 'a' && !inInput && !e.ctrlKey && !e.metaKey) {
        e.preventDefault();
        document.querySelectorAll('.row-cb').forEach(function(cb) { cb.checked = true; });
        document.getElementById('selectAll').checked = true;
        updateBulkBar();
        return;
    }
    if (e.key === 'd' && !inInput) {
        e.preventDefault(); clearSelection(); return;
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && document.getElementById('bulkBar').classList.contains('visible')) {
        if (confirm('Apply bulk role change to ' + document.getElementById('bulkCount').textContent + ' users?')) {
            document.getElementById('bulkForm').submit();
        }
    }
});

/* ── Animate bar fills on load ── */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rmc-bar-fill').forEach(function(el) {
        var w = el.style.width;
        el.style.width = '0';
        setTimeout(function() { el.style.width = w; }, 100);
    });
});
</script>
@endsection