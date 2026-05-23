@extends('layouts.admin')
@section('title', $net ? 'Edit Net Session' : 'New Net Session')
@section('content')
<style>
.ns-wrap{max-width:680px;margin:0 auto;padding:2rem 1rem 4rem;}
.ns-title{font-size:1.4rem;font-weight:800;color:#003366;margin-bottom:.25rem;}
.ns-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;padding:1.5rem;margin-bottom:1.25rem;}
.ns-card-title{font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#003366;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid #eef1f5;}
.field{margin-bottom:1rem;}
.label{display:block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#003366;margin-bottom:.4rem;}
.label-hint{font-size:.7rem;font-weight:normal;color:#6b7f96;text-transform:none;letter-spacing:0;}
.input{width:100%;padding:.55rem .75rem;border:1px solid #dde2e8;border-radius:6px;font-size:.9rem;font-family:inherit;box-sizing:border-box;}
.input:focus{outline:none;border-color:#003366;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;background:#f8fafc;border-radius:8px;margin-bottom:.75rem;}
.toggle-label{font-size:.9rem;font-weight:700;color:#003366;}
.toggle-sub{font-size:.78rem;color:#6b7f96;margin-top:.1rem;}
.toggle-switch{position:relative;width:48px;height:26px;flex-shrink:0;}
.toggle-switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;inset:0;background:#dde2e8;border-radius:999px;cursor:pointer;transition:.25s;}
.slider:before{content:'';position:absolute;width:20px;height:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
input:checked+.slider{background:#003366;}
input:checked+.slider:before{transform:translateX(22px);}
input:checked+.slider.red{background:#C8102E;}
.day-grid{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.35rem;}
.day-btn{padding:.4rem .8rem;border:2px solid #dde2e8;border-radius:6px;font-size:.8rem;font-weight:700;cursor:pointer;background:#fff;color:#334155;transition:.15s;}
.day-btn.selected{border-color:#003366;background:#003366;color:#fff;}
.btn-save{background:#003366;color:#fff;border:none;padding:.65rem 1.75rem;border-radius:999px;font-size:.95rem;font-weight:700;cursor:pointer;}
.btn-cancel{background:#f1f5f9;color:#64748b;border:none;padding:.65rem 1.25rem;border-radius:999px;font-size:.95rem;font-weight:700;cursor:pointer;text-decoration:none;}
.alert-error{background:#fee2e2;border-left:3px solid #dc2626;padding:.65rem 1rem;border-radius:4px;font-size:.85rem;color:#991b1b;margin-bottom:1rem;}
.type-tab{display:flex;background:#f1f5f9;border-radius:8px;padding:3px;margin-bottom:1rem;}
.type-tab-btn{flex:1;padding:.5rem;text-align:center;font-size:.82rem;font-weight:700;border-radius:6px;cursor:pointer;border:none;background:transparent;color:#6b7f96;transition:.15s;}
.type-tab-btn.active{background:#fff;color:#003366;box-shadow:0 1px 4px rgba(0,0,0,.1);}
</style>

<div class="ns-wrap">
    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('admin.net-sessions.index') }}" style="color:#6b7f96;text-decoration:none;font-size:.85rem;">← Back to Net Sessions</a>
    </div>
    <div class="ns-title">{{ $net ? 'Edit Net Session' : 'New Net Session' }}</div>

    @if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $net ? route('admin.net-sessions.update', $net) : route('admin.net-sessions.store') }}">
        @csrf
        @if($net) @method('PUT') @endif

        {{-- Identity --}}
        <div class="ns-card">
            <div class="ns-card-title">Net Identity</div>
            <div class="field">
                <label class="label">Net Name *</label>
                <input type="text" name="name" class="input" placeholder="e.g. Monday Evening Net" value="{{ old('name', $net->name ?? '') }}" required>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label class="label">Callsign / Net Name *</label>
                    <input type="text" name="callsign" class="input" placeholder="e.g. LIVERPOOL CONTROL" value="{{ old('callsign', $net->callsign ?? '') }}" required>
                </div>
                <div class="field">
                    <label class="label">Frequency <span class="label-hint">(members only unless public)</span></label>
                    <input type="text" name="frequency" class="input" placeholder="e.g. 145.500 MHz" value="{{ old('frequency', $net->frequency ?? '') }}">
                </div>
            </div>
            <div class="field">
                <label class="label">Description / Strapline</label>
                <input type="text" name="description" class="input" placeholder="e.g. Weekly training net — all welcome" value="{{ old('description', $net->description ?? '') }}">
            </div>
            <div class="field">
                <label class="label">Net Controller Callsign</label>
                <input type="text" name="controller" class="input" placeholder="e.g. G4BDS" value="{{ old('controller', $net->controller ?? '') }}">
            </div>
        </div>

        {{-- Visibility --}}
        <div class="ns-card">
            <div class="ns-card-title">Visibility</div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Public Net</div>
                    <div class="toggle-sub">Frequency and controller visible to non-members on the homepage</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_public" value="1" {{ old('is_public', $net->is_public ?? false) ? 'checked' : '' }}>
                    <span class="slider red"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Active</div>
                    <div class="toggle-sub">Include in schedule — uncheck to temporarily disable</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="active" value="1" {{ old('active', $net->active ?? true) ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="ns-card">
            <div class="ns-card-title">Schedule</div>

            {{-- Type selector --}}
            <div class="type-tab" id="typeTab">
                <button type="button" class="type-tab-btn {{ old('is_recurring', $net->is_recurring ?? false) ? '' : 'active' }}" onclick="setType(false)">📅 One-off</button>
                <button type="button" class="type-tab-btn {{ old('is_recurring', $net->is_recurring ?? false) ? 'active' : '' }}" onclick="setType(true)">🔁 Recurring</button>
            </div>
            <input type="hidden" name="is_recurring" id="is_recurring" value="{{ old('is_recurring', $net->is_recurring ?? false) ? '1' : '0' }}">

            {{-- One-off date --}}
            <div id="oneOffSection" class="field" style="{{ old('is_recurring', $net->is_recurring ?? false) ? 'display:none' : '' }}">
                <label class="label">Date</label>
                <input type="date" name="specific_date" class="input" value="{{ old('specific_date', $net->specific_date?->format('Y-m-d') ?? '') }}">
            </div>

            {{-- Recurring days --}}
            <div id="recurringSection" style="{{ old('is_recurring', $net->is_recurring ?? false) ? '' : 'display:none' }}">
                <div class="field">
                    <label class="label">Days of Week</label>
                    <div class="day-grid" id="dayGrid">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $di => $dn)
                        <button type="button" class="day-btn {{ in_array($di, old('days_of_week', $net->days_of_week ?? [])) ? 'selected' : '' }}"
                            onclick="toggleDay({{ $di }}, this)">{{ $dn }}</button>
                        @endforeach
                    </div>
                    <div id="dayInputs">
                        @foreach(old('days_of_week', $net->days_of_week ?? []) as $d)
                        <input type="hidden" name="days_of_week[]" value="{{ $d }}" class="day-hidden">
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Times --}}
            <div class="grid-2">
                <div class="field">
                    <label class="label">Start Time *</label>
                    <input type="time" name="start_time" class="input" value="{{ old('start_time', $net->start_time ?? '') }}" required>
                </div>
                <div class="field">
                    <label class="label">End Time <span class="label-hint">(banner auto-hides)</span></label>
                    <input type="time" name="end_time" class="input" value="{{ old('end_time', $net->end_time ?? '') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Notes <span class="label-hint">(internal only)</span></label>
                <textarea name="notes" class="input" rows="2" placeholder="Internal notes about this net session...">{{ old('notes', $net->notes ?? '') }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;align-items:center;">
            <button type="submit" class="btn-save">{{ $net ? 'Save Changes' : 'Create Net Session' }}</button>
            <a href="{{ route('admin.net-sessions.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
function setType(recurring) {
    document.getElementById('is_recurring').value = recurring ? '1' : '0';
    document.getElementById('oneOffSection').style.display = recurring ? 'none' : '';
    document.getElementById('recurringSection').style.display = recurring ? '' : 'none';
    document.querySelectorAll('.type-tab-btn').forEach((b,i) => b.classList.toggle('active', recurring ? i===1 : i===0));
}

function toggleDay(day, btn) {
    btn.classList.toggle('selected');
    var existing = document.querySelector('.day-hidden[value="'+day+'"]');
    if (existing) { existing.remove(); }
    else {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'days_of_week[]';
        inp.value = day; inp.className = 'day-hidden';
        document.getElementById('dayInputs').appendChild(inp);
    }
}
</script>
@endsection
