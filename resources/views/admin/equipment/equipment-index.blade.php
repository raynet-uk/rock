@extends('layouts.admin')

@section('title', 'Equipment Registry')

@section('content')
<style>
:root {
    --navy:#003366; --navy-faint:#e8eef8; --red:#C8102E; --red-faint:#fdf0f2;
    --green:#1a6b3c; --green-bg:#eaf4ee; --green-bdr:#a8d5b8;
    --amber:#8a5c00; --amber-bg:#fff8e6; --amber-bdr:#e8c96a;
    --grey:#f5f6f8; --grey-mid:#dde2e8; --grey-dark:#6b7f96;
    --text:#1a2332; --text-muted:#6b7f96; --white:#fff;
    --shadow-sm:0 1px 4px rgba(0,51,102,.08);
    --font:Arial,'Helvetica Neue',sans-serif;
}
* { box-sizing:border-box; }
body { font-family:var(--font); color:var(--text); background:var(--grey); }

.page-header { background:var(--navy); color:#fff; padding:1.1rem 1.5rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
.page-header h1 { font-size:18px; font-weight:bold; letter-spacing:.04em; margin:0; }
.page-header .badge-count { font-size:11px; background:rgba(255,255,255,.15); padding:3px 10px; border-radius:3px; }

.toolbar { background:var(--white); border-bottom:1px solid var(--grey-mid); padding:.65rem 1.5rem; display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.toolbar input[type=text], .toolbar select { font-size:12px; padding:.35rem .65rem; border:1px solid var(--grey-mid); background:var(--white); color:var(--text); font-family:var(--font); height:32px; }
.toolbar input[type=text] { width:220px; }
.btn { display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .85rem; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:.05em; border:1px solid; cursor:pointer; font-family:var(--font); text-decoration:none; height:32px; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-navy { background:var(--navy); color:#fff; border-color:var(--navy); }
.btn-ghost { background:var(--white); color:var(--navy); border-color:var(--grey-mid); }
.btn-red { background:var(--red-faint); color:var(--red); border-color:rgba(200,16,46,.3); }
.btn-green { background:var(--green-bg); color:var(--green); border-color:var(--green-bdr); }
.ml-auto { margin-left:auto; }

.overdue-banner { background:var(--red-faint); border-bottom:1px solid rgba(200,16,46,.2); padding:.6rem 1.5rem; display:flex; align-items:center; gap:.65rem; font-size:13px; color:var(--red); font-weight:bold; }

.content { padding:1.25rem 1.5rem; }

.panel { background:var(--white); border:1px solid var(--grey-mid); margin-bottom:1.25rem; }
.panel-head { padding:.75rem 1.1rem; border-bottom:1px solid var(--grey-mid); display:flex; align-items:center; gap:.65rem; }
.panel-title { font-size:13px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:var(--navy); }

/* Equipment table */
.equip-table { width:100%; border-collapse:collapse; font-size:13px; }
.equip-table th { padding:.5rem .85rem; text-align:left; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); border-bottom:2px solid var(--grey-mid); background:var(--grey); white-space:nowrap; }
.equip-table td { padding:.55rem .85rem; border-bottom:1px solid var(--grey-mid); vertical-align:middle; }
.equip-table tr:last-child td { border-bottom:none; }
.equip-table tr:hover td { background:#f8f9fb; }
.equip-table tr.overdue td { background:#fff8f8; }

.badge { display:inline-block; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.05em; padding:2px 8px; border:1px solid; border-radius:2px; white-space:nowrap; }
.badge-green  { background:var(--green-bg);  border-color:var(--green-bdr);  color:var(--green); }
.badge-amber  { background:var(--amber-bg);  border-color:var(--amber-bdr);  color:var(--amber); }
.badge-red    { background:var(--red-faint);  border-color:rgba(200,16,46,.3); color:var(--red); }
.badge-grey   { background:var(--grey);       border-color:var(--grey-mid);   color:var(--grey-dark); }
.badge-navy   { background:var(--navy-faint); border-color:rgba(0,51,102,.2); color:var(--navy); }

.type-icon { font-size:16px; }

/* Add/Edit form modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,31,64,.45); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal { background:var(--white); width:100%; max-width:580px; max-height:90vh; overflow-y:auto; }
.modal-head { background:var(--navy); color:#fff; padding:.85rem 1.1rem; display:flex; align-items:center; justify-content:space-between; }
.modal-head h2 { font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; margin:0; }
.modal-body { padding:1.1rem; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:.75rem; }
.form-row.full { grid-template-columns:1fr; }
.form-row.three { grid-template-columns:1fr 1fr 1fr; }
.field label { display:block; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); margin-bottom:.3rem; }
.field input, .field select, .field textarea {
    width:100%; padding:.45rem .65rem; font-size:13px; border:1px solid var(--grey-mid);
    background:var(--white); color:var(--text); font-family:var(--font);
}
.field textarea { resize:vertical; min-height:70px; }
.modal-foot { padding:.75rem 1.1rem; border-top:1px solid var(--grey-mid); display:flex; justify-content:flex-end; gap:.5rem; background:var(--grey); }

.flash { padding:.65rem 1.1rem; font-size:13px; font-weight:bold; margin-bottom:1rem; }
.flash-success { background:var(--green-bg); border-left:3px solid var(--green); color:var(--green); }
.flash-error   { background:var(--red-faint); border-left:3px solid var(--red);   color:var(--red); }

.empty-state { padding:2.5rem; text-align:center; color:var(--text-muted); font-size:14px; }
.pagination { padding:.75rem 1.1rem; display:flex; gap:.35rem; align-items:center; justify-content:flex-end; font-size:12px; border-top:1px solid var(--grey-mid); }
</style>

<div class="page-header">
    <h1>📻 Equipment Registry</h1>
    <span class="badge-count">{{ $equipment->total() }} items</span>
    @if ($overdueCount > 0)
    <span style="background:rgba(200,16,46,.3);color:#fff;font-size:11px;font-weight:bold;padding:3px 10px;border-radius:3px;">
        ⚠ {{ $overdueCount }} overdue test{{ $overdueCount !== 1 ? 's' : '' }}
    </span>
    @endif
    <div style="margin-left:auto;display:flex;gap:.5rem;">
        <a href="{{ route('admin.equipment.export') }}" class="btn btn-ghost" style="color:#fff;border-color:rgba(255,255,255,.3);">⬇ Export CSV</a>
        <button class="btn btn-green" onclick="openAddModal()">+ Add Equipment</button>
    </div>
</div>

@if ($overdueCount > 0)
<div class="overdue-banner">
    ⚠ {{ $overdueCount }} piece{{ $overdueCount !== 1 ? 's' : '' }} of equipment have an overdue test.
    <a href="{{ route('admin.equipment') }}?overdue=1" style="color:var(--red);text-decoration:underline;margin-left:.4rem;">View overdue only →</a>
</div>
@endif

<div class="toolbar">
    <form method="GET" action="{{ route('admin.equipment') }}" style="display:contents;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search make, model, callsign, member…">
        <select name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach (\App\Models\Equipment::TYPES as $key => $meta)
            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $meta['icon'] }} {{ $meta['label'] }}</option>
            @endforeach
        </select>
        <label style="font-size:12px;display:flex;align-items:center;gap:.35rem;cursor:pointer;">
            <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} onchange="this.form.submit()">
            Overdue only
        </label>
        <button type="submit" class="btn btn-navy">Search</button>
        @if (request()->hasAny(['search','type','overdue']))
        <a href="{{ route('admin.equipment') }}" class="btn btn-ghost">✕ Clear</a>
        @endif
    </form>
</div>

<div class="content">

    @if (session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif

    @if (session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
    @endif

    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">All Equipment</span>
            <span style="font-size:11px;color:var(--text-muted);margin-left:auto;">{{ $equipment->firstItem() }}–{{ $equipment->lastItem() }} of {{ $equipment->total() }}</span>
        </div>

        @if ($equipment->isEmpty())
        <div class="empty-state">No equipment registered yet. Add your first item above.</div>
        @else
        <div style="overflow-x:auto;">
        <table class="equip-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Make / Model</th>
                    <th>Serial No</th>
                    <th>Callsign</th>
                    <th>Licence</th>
                    <th>Member</th>
                    <th>Last Tested</th>
                    <th>Next Due</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($equipment as $item)
            @php
                $badge = $item->testStatusBadge();
                $isOverdue = $item->isTestOverdue();
            @endphp
            <tr class="{{ $isOverdue ? 'overdue' : '' }}">
                <td>
                    <span class="type-icon" title="{{ $item->type_label }}">{{ $item->type_icon }}</span>
                    <span style="font-size:10px;color:var(--text-muted);">{{ $item->type_label }}</span>
                </td>
                <td>
                    <div style="font-weight:bold;color:var(--navy);">{{ $item->make }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $item->model }}</div>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $item->serial_number ?? '—' }}</td>
                <td>
                    @if ($item->callsign)
                    <span style="font-weight:bold;letter-spacing:.04em;">{{ $_isTempAdmin && isset($item) && method_exists($item, 'piiVisible') && !$item->piiVisible() ? '●●●●●' : $item->callsign }}</span>
                    @else <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    @if ($item->licence_class)
                    <span class="badge badge-navy">{{ $item->licence_class }}</span>
                    @else <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    @if ($item->user)
                    <div style="font-size:13px;">{{ $_isTempAdmin && isset($item->user) && method_exists($item->user, 'piiVisible') && !$item->user->piiVisible() ? '●●●●●●●●●' : $item->user->name }}</div>
                    @if ($item->user->callsign)
                    <div style="font-size:11px;color:var(--text-muted);">{{ $_isTempAdmin && isset($item->user) && method_exists($item->user, 'piiVisible') && !$item->user->piiVisible() ? '●●●●●' : $item->user->callsign }}</div>
                    @endif
                    @else <span style="color:var(--text-muted);">Unassigned</span>
                    @endif
                </td>
                <td style="white-space:nowrap;">{{ $item->last_tested_date?->format('d M Y') ?? '—' }}</td>
                <td style="white-space:nowrap;">
                    @if ($item->next_test_due)
                        <span style="color:{{ $item->isTestOverdue() ? 'var(--red)' : 'var(--text)' }};font-weight:{{ $item->isTestOverdue() ? 'bold' : 'normal' }};">
                            {{ $item->next_test_due->format('d M Y') }}
                        </span>
                    @elseif ($item->last_tested_date)
                        <span style="color:{{ $item->isTestOverdue() ? 'var(--red)' : 'var(--text-muted)' }};">
                            {{ $item->last_tested_date->addYear()->format('d M Y') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td><span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span></td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-ghost" style="font-size:11px;padding:.25rem .6rem;"
                            onclick="openEditModal({{ $item->id }})">✏ Edit</button>
                    <form method="POST" action="{{ route('admin.equipment.destroy', $item) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Remove {{ addslashes($item->display_name) }} from the registry?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-red" style="font-size:11px;padding:.25rem .6rem;">✕</button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        @if ($equipment->hasPages())
        <div class="pagination">
            {{ $equipment->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── ADD MODAL ─────────────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="add-modal">
    <div class="modal">
        <div class="modal-head">
            <h2>Add Equipment</h2>
            <button onclick="closeModals()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.equipment.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="field">
                        <label>Make *</label>
                        <input type="text" name="make" required placeholder="e.g. Yaesu">
                    </div>
                    <div class="field">
                        <label>Model *</label>
                        <input type="text" name="model" required placeholder="e.g. FT-65E">
                    </div>
                </div>
                <div class="form-row three">
                    <div class="field">
                        <label>Type *</label>
                        <select name="equipment_type" required>
                            @foreach (\App\Models\Equipment::TYPES as $key => $meta)
                            <option value="{{ $key }}">{{ $meta['icon'] }} {{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" placeholder="Optional">
                    </div>
                    <div class="field">
                        <label>Assigned Member</label>
                        <select name="user_id">
                            <option value="">— Unassigned —</option>
                            @foreach ($members as $m)
                            <option value="{{ $m->id }}">{{ $_isTempAdmin && isset($m) && method_exists($m, 'piiVisible') && !$m->piiVisible() ? '●●●●●●●●●' : $m->name }}{{ $m->callsign ? ' ('.$m->callsign.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row three">
                    <div class="field">
                        <label>Callsign</label>
                        <input type="text" name="callsign" placeholder="e.g. M7ABC" style="text-transform:uppercase;">
                    </div>
                    <div class="field">
                        <label>Licence Class</label>
                        <select name="licence_class">
                            <option value="">— None —</option>
                            @foreach (\App\Models\Equipment::LICENCE_CLASSES as $lc)
                            <option value="{{ $lc }}">{{ $lc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>&nbsp;</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Last Tested Date</label>
                        <input type="date" name="last_tested_date">
                    </div>
                    <div class="field">
                        <label>Next Test Due</label>
                        <input type="date" name="next_test_due">
                    </div>
                </div>
                <div class="form-row full">
                    <div class="field">
                        <label>Notes</label>
                        <textarea name="notes" placeholder="Optional notes about this equipment…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn btn-navy">Add Equipment</button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT MODALS (one per item) ────────────────────────────────────────── --}}
@foreach ($equipment as $item)
<div class="modal-overlay" id="edit-modal-{{ $item->id }}">
    <div class="modal">
        <div class="modal-head">
            <h2>Edit — {{ $item->display_name }}</h2>
            <button onclick="closeModals()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1;">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.equipment.update', $item) }}">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="field">
                        <label>Make *</label>
                        <input type="text" name="make" required value="{{ $item->make }}">
                    </div>
                    <div class="field">
                        <label>Model *</label>
                        <input type="text" name="model" required value="{{ $item->model }}">
                    </div>
                </div>
                <div class="form-row three">
                    <div class="field">
                        <label>Type *</label>
                        <select name="equipment_type" required>
                            @foreach (\App\Models\Equipment::TYPES as $key => $meta)
                            <option value="{{ $key }}" {{ $item->equipment_type === $key ? 'selected' : '' }}>{{ $meta['icon'] }} {{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" value="{{ $item->serial_number }}">
                    </div>
                    <div class="field">
                        <label>Assigned Member</label>
                        <select name="user_id">
                            <option value="">— Unassigned —</option>
                            @foreach ($members as $m)
                            <option value="{{ $m->id }}" {{ $item->user_id == $m->id ? 'selected' : '' }}>
                                {{ $_isTempAdmin && isset($m) && method_exists($m, 'piiVisible') && !$m->piiVisible() ? '●●●●●●●●●' : $m->name }}{{ $m->callsign ? ' ('.$m->callsign.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row three">
                    <div class="field">
                        <label>Callsign</label>
                        <input type="text" name="callsign" value="{{ $_isTempAdmin && isset($item) && method_exists($item, 'piiVisible') && !$item->piiVisible() ? '●●●●●' : $item->callsign }}" style="text-transform:uppercase;">
                    </div>
                    <div class="field">
                        <label>Licence Class</label>
                        <select name="licence_class">
                            <option value="">— None —</option>
                            @foreach (\App\Models\Equipment::LICENCE_CLASSES as $lc)
                            <option value="{{ $lc }}" {{ $item->licence_class === $lc ? 'selected' : '' }}>{{ $lc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"></div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Last Tested Date</label>
                        <input type="date" name="last_tested_date" value="{{ $item->last_tested_date?->format('Y-m-d') }}">
                    </div>
                    <div class="field">
                        <label>Next Test Due</label>
                        <input type="date" name="next_test_due" value="{{ $item->next_test_due?->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-row full">
                    <div class="field">
                        <label>Notes</label>
                        <textarea name="notes">{{ $item->notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn btn-navy">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<script>
function openAddModal()  { document.getElementById('add-modal').classList.add('open'); }
function openEditModal(id) { document.getElementById('edit-modal-'+id).classList.add('open'); }
function closeModals()   { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open')); }
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if(e.target===m) closeModals(); }));
</script>
@endsection
