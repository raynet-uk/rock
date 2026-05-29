{{-- resources/views/admin/activity-logs/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'New Activity Log — Admin')
@section('content')

<style>
:root {
    --navy:      #003366;
    --navy-mid:  #004080;
    --navy-faint:#e8eef5;
    --red:       #C8102E;
    --red-faint: #fdf0f2;
    --white:     #FFFFFF;
    --grey:      #F2F2F2;
    --grey-mid:  #dde2e8;
    --grey-dark: #9aa3ae;
    --text:      #001f40;
    --text-mid:  #2d4a6b;
    --text-muted:#6b7f96;
    --green:     #1a6b3c;
    --green-bg:  #eef7f2;
    --amber:     #8a5500;
    --amber-bg:  #fdf8ec;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.11);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--grey); color: var(--text); font-family: var(--font); font-size: 14px; min-height: 100vh; }

/* ── HEADER ── */
.rn-header { background: var(--navy); border-bottom: 4px solid var(--red); position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,.3); }
.rn-header-inner { max-width: 900px; margin: 0 auto; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.rn-brand { display: flex; align-items: center; gap: .85rem; padding: .75rem 0; }
.rn-logo-block { background: var(--red); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo-block span { font-size: 11px; font-weight: bold; color: #fff; letter-spacing: .06em; text-align: center; line-height: 1.15; text-transform: uppercase; }
.rn-org { font-size: 15px; font-weight: bold; color: #fff; letter-spacing: .04em; text-transform: uppercase; }
.rn-sub { font-size: 11px; color: rgba(255,255,255,.55); margin-top: 2px; letter-spacing: .05em; text-transform: uppercase; }
.rn-back { font-size: 12px; font-weight: bold; color: rgba(255,255,255,.8); text-decoration: none; border: 1px solid rgba(255,255,255,.25); padding: .35rem .9rem; transition: all .15s; }
.rn-back:hover { background: rgba(255,255,255,.1); color: #fff; }

/* ── PAGE BAND ── */
.page-band { background: var(--white); border-bottom: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
.page-band-inner { max-width: 900px; margin: 0 auto; padding: 1.25rem 1.5rem; display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.page-eyebrow { font-size: 11px; font-weight: bold; color: var(--red); text-transform: uppercase; letter-spacing: .18em; margin-bottom: .3rem; display: flex; align-items: center; gap: .5rem; }
.page-eyebrow::before { content: ''; width: 16px; height: 2px; background: var(--red); display: inline-block; }
.page-title { font-size: 24px; font-weight: bold; color: var(--navy); line-height: 1; }
.page-desc { font-size: 13px; color: var(--text-muted); margin-top: .35rem; }

/* ── WRAP ── */
.wrap { max-width: 900px; margin: 0 auto; padding: 0 1.5rem 4rem; }

/* ── CARD ── */
.card { background: var(--white); border: 1px solid var(--grey-mid); border-top: 3px solid var(--navy); box-shadow: var(--shadow-sm); }
.card-head { padding: .8rem 1.25rem; background: var(--grey); border-bottom: 1px solid var(--grey-mid); display: flex; align-items: center; gap: .65rem; }
.card-head-icon { width: 30px; height: 30px; background: var(--navy-faint); border: 1px solid rgba(0,51,102,.15); display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.card-head h2 { font-size: 12px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .08em; }
.card-body { padding: 1.5rem 1.25rem; }

/* ── FORM GRID ── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grid.full { grid-template-columns: 1fr; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
.span-2 { grid-column: span 2; }
@media (max-width: 600px) { .span-2 { grid-column: span 1; } }

/* ── FIELDS ── */
.field { display: flex; flex-direction: column; gap: .3rem; }
.field label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); display: flex; align-items: center; gap: .3rem; }
.field label .req { color: var(--red); font-size: 13px; line-height: 1; }
.field label .opt { font-size: 10px; color: var(--grey-dark); font-weight: normal; text-transform: none; letter-spacing: 0; }
.input-wrap { position: relative; }
.input-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); font-size: .85rem; color: var(--text-muted); pointer-events: none; }
.field input,
.field select,
.field textarea {
    width: 100%; padding: .52rem .75rem .52rem 2.1rem;
    border: 1px solid var(--grey-mid); background: var(--white); color: var(--text);
    font-family: var(--font); font-size: 13px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.field textarea { padding-left: 2.1rem; resize: vertical; min-height: 80px; }
.field input:focus,
.field select:focus,
.field textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.field-hint { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.field-error { font-size: 11px; color: var(--red); font-weight: bold; margin-top: 2px; }

/* ── SECTION DIVIDER ── */
.form-section { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .15em; color: var(--text-muted); padding-bottom: .4rem; margin-bottom: .75rem; margin-top: 1.25rem; border-bottom: 1px solid var(--grey-mid); grid-column: 1 / -1; }
.form-section:first-child { margin-top: 0; }

/* ── EVENT PICKER ── */
.event-picker-block {
    grid-column: span 2;
    border: 1px solid var(--grey-mid);
    background: var(--navy-faint);
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: .65rem;
}
@media (max-width: 600px) { .event-picker-block { grid-column: span 1; } }

.event-picker-label {
    font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em;
    color: var(--navy); display: flex; align-items: center; gap: .4rem;
}
.event-picker-label::before { content: ''; width: 10px; height: 2px; background: var(--navy); display: inline-block; }

.event-picker-row { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.event-picker-row .input-wrap { flex: 1; min-width: 220px; }
.event-picker-row select {
    width: 100%; padding: .5rem .75rem .5rem 2.1rem;
    border: 1px solid var(--grey-mid); background: var(--white); color: var(--text);
    font-family: var(--font); font-size: 13px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.event-picker-row select:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }

.toggle-custom-btn {
    white-space: nowrap; font-size: 11px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .05em; color: var(--text-mid); background: var(--white);
    border: 1px solid var(--grey-mid); padding: .45rem .9rem; cursor: pointer;
    font-family: var(--font); transition: all .12s; flex-shrink: 0;
}
.toggle-custom-btn:hover { border-color: var(--navy); color: var(--navy); background: var(--navy-faint); }
.toggle-custom-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }

.prefilled-notice {
    display: none; font-size: 11px; color: var(--green); font-weight: bold;
    background: var(--green-bg); border: 1px solid #b8ddc9; padding: .3rem .7rem;
    align-items: center; gap: .35rem;
}
.prefilled-notice.visible { display: flex; }

.custom-mode-notice {
    display: none; font-size: 11px; color: var(--amber); font-weight: bold;
    background: var(--amber-bg); border: 1px solid #e8c96a; padding: .3rem .7rem;
    align-items: center; gap: .35rem;
}
.custom-mode-notice.visible { display: flex; }

/* ── ACTIONS ── */
.form-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--grey-mid); background: var(--grey); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.form-footer-note { font-size: 11px; color: var(--text-muted); font-weight: bold; }
.form-footer-btns { display: flex; gap: .6rem; }
.btn-submit {
    padding: .52rem 1.4rem; background: var(--navy); color: var(--white);
    border: 1px solid var(--navy); font-family: var(--font);
    font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    cursor: pointer; transition: background .12s, box-shadow .12s;
    display: inline-flex; align-items: center; gap: .4rem;
}
.btn-submit:hover { background: var(--navy-mid); box-shadow: 0 4px 12px rgba(0,51,102,.18); }
.btn-cancel {
    padding: .52rem 1.1rem; background: var(--white); color: var(--text-muted);
    border: 1px solid var(--grey-mid); font-family: var(--font);
    font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: all .12s;
}
.btn-cancel:hover { border-color: var(--navy); color: var(--navy); }

/* ── ERROR ALERT ── */
.error-alert { display: flex; align-items: flex-start; gap: .65rem; padding: .75rem 1rem; margin-bottom: 1.25rem; background: var(--red-faint); border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red); color: var(--red); font-size: 12px; }
.error-alert ul { margin: .3rem 0 0 1rem; padding: 0; }
.error-alert li { margin: .2rem 0; }

@keyframes fadeUp { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
.fade-in { animation: fadeUp .3s ease both; }
</style>

{{-- ── HEADER ── --}}
<header class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo-block"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Activity Logs</div>
            </div>
        </div>
        <a href="{{ route('admin.activity-logs.index') }}" class="rn-back">← Back to logs</a>
    </div>
</header>

{{-- ── PAGE BAND ── --}}
<div class="page-band fade-in">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Activity Logs</div>
            <h1 class="page-title">New Log Entry</h1>
            <p class="page-desc">Record a member's attendance and volunteer hours for an event.</p>
        </div>
        <div style="font-size:11px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:.28rem .7rem;">
            {{ now()->format('D d M Y · H:i') }}
        </div>
    </div>
</div>

<div class="wrap">

    @if ($errors->any())
        <div class="error-alert fade-in">
            <div>
                <strong>✕ Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card fade-in">
        <div class="card-head">
            <div class="card-head-icon">📋</div>
            <h2>Activity Log Details</h2>
        </div>

        {{-- Mode toggle --}}
        <div style="display:flex;gap:.5rem;padding:.75rem 1.25rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);align-items:center">
            <span style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)">Mode:</span>
            <button type="button" id="btn-single" onclick="setMode('single')" style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;padding:.35rem .9rem;border:1px solid var(--navy);background:var(--navy);color:#fff;cursor:pointer;font-family:var(--font)">👤 Single Member</button>
            <button type="button" id="btn-bulk" onclick="setMode('bulk')" style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;padding:.35rem .9rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--text-muted);cursor:pointer;font-family:var(--font)">👥 Multiple Members</button>
        </div>

        <form method="POST" id="log-form" action="{{ route('admin.activity-logs.store') }}">
            @csrf

            {{--
                Pass published events as a JSON map for JS to consume.
                Expects $events to be a collection of published Event models,
                each with: id, title, starts_at (Carbon).
                In your controller: $events = \App\Models\Event::published()->orderBy('starts_at')->get();
            --}}
            @php
                $eventsJson = $events->map(fn($e) => [
                    'id'    => $e->id,
                    'title' => $e->title,
                    'date'  => $e->starts_at ? $e->starts_at->format('Y-m-d') : null,
                    'label' => $e->title . ' — ' . ($e->starts_at ? $e->starts_at->format('j M Y') : 'No date'),
                ])->values()->toJson();

                // Restore previous selection across validation failures
                $oldEventId   = old('linked_event_id');
                $oldEventName = old('event_name');
                $oldEventDate = old('event_date', date('Y-m-d'));
                $isCustom     = old('is_custom_event', '0') === '1';
            @endphp

            <div class="card-body">
                <div class="form-grid">

                    <div class="form-section">Event Details</div>

                    {{-- ── EVENT PICKER BLOCK ── --}}
                    <div class="event-picker-block">
                        <div class="event-picker-label">Select from published events, or enter a custom event below</div>

                        <div class="event-picker-row">
                            <div class="input-wrap">
                                <span class="input-icon">📅</span>
                                <select id="event-picker-select" {{ $isCustom ? 'disabled' : '' }}>
                                    <option value="">— Choose a published event —</option>
                                    @foreach ($events as $e)
                                        <option
                                            value="{{ $e->id }}"
                                            data-title="{{ $e->title }}"
                                            data-date="{{ $e->starts_at ? $e->starts_at->format('Y-m-d') : '' }}"
                                            {{ (!$isCustom && $oldEventId == $e->id) ? 'selected' : '' }}>
                                            {{ $e->title }} — {{ $e->starts_at ? $e->starts_at->format('j M Y') : 'No date' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button"
                                    class="toggle-custom-btn {{ $isCustom ? 'active' : '' }}"
                                    id="toggle-custom-btn"
                                    onclick="toggleCustomMode()">
                                {{ $isCustom ? '✓ Custom event' : '+ Custom event' }}
                            </button>
                        </div>

                        <div class="prefilled-notice" id="prefilled-notice"
                             style="{{ (!$isCustom && $oldEventId) ? 'display:flex;' : '' }}">
                            ✓ Event details pre-filled from the selected event — you can edit them below if needed.
                        </div>

                        <div class="custom-mode-notice {{ $isCustom ? 'visible' : '' }}" id="custom-mode-notice">
                            ✏ Custom mode — enter the event name and date manually below.
                        </div>
                    </div>

                    {{-- Hidden fields to track picker state --}}
                    <input type="hidden" name="linked_event_id" id="linked-event-id"
                           value="{{ $isCustom ? '' : ($oldEventId ?? '') }}">
                    <input type="hidden" name="is_custom_event" id="is-custom-event"
                           value="{{ $isCustom ? '1' : '0' }}">

                    {{-- Member selector (single / bulk) --}}
                    <div class="field" id="field-single-member">
                        <label for="user_id">Member <span class="req">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon">👤</span>
                            <select id="user_id" name="user_id">
                                <option value="">— Select member —</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('user_id')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div id="field-bulk-members" style="display:none;grid-column:span 2">
                        <div class="field">
                            <label>Members <span class="req">*</span> <span class="opt">— hold Ctrl/Cmd to select multiple</span></label>
                            <div style="display:flex;gap:.5rem;align-items:flex-start">
                                <div style="flex:1">
                                    <select id="user_ids" name="user_ids[]" multiple size="8"
                                        style="width:100%;padding:.5rem;border:1px solid var(--grey-mid);font-family:var(--font);font-size:13px;color:var(--text)">
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="display:flex;flex-direction:column;gap:.35rem;flex-shrink:0">
                                    <button type="button" onclick="selectAllMembers()" style="font-size:11px;font-weight:bold;padding:.35rem .75rem;border:1px solid var(--navy);background:var(--navy-faint);color:var(--navy);cursor:pointer;font-family:var(--font);white-space:nowrap">✓ Select All</button>
                                    <button type="button" onclick="clearAllMembers()" style="font-size:11px;font-weight:bold;padding:.35rem .75rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--text-muted);cursor:pointer;font-family:var(--font);white-space:nowrap">✕ Clear All</button>
                                </div>
                            </div>
                            <div id="bulk-count" style="font-size:11px;color:var(--text-muted);margin-top:.25rem">0 members selected</div>
                        </div>
                    </div>

                    {{-- Hours --}}
                    <div class="field">
                        <label for="hours">Volunteer Hours <span class="req">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon">⏱</span>
                            <input type="number" id="hours" name="hours"
                                   value="{{ old('hours') }}"
                                   min="0.5" max="24" step="0.5"
                                   placeholder="e.g. 3.5" required>
                        </div>
                        <div class="field-hint">Minimum 0.5 hours, maximum 24.</div>
                        @error('hours')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Event Name --}}
                    <div class="field span-2">
                        <label for="event_name">Event Name <span class="req">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon">📋</span>
                            <input type="text" id="event_name" name="event_name"
                                   value="{{ $oldEventName ?? '' }}"
                                   placeholder="e.g. Mersey Marathon 2026"
                                   required>
                        </div>
                        <div class="field-hint" id="event-name-hint" style="{{ $isCustom ? '' : 'display:none;' }}">
                            Enter the event name manually.
                        </div>
                        @error('event_name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Event Date --}}
                    <div class="field">
                        <label for="event_date">Event Date <span class="req">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon">🗓</span>
                            <input type="date" id="event_date" name="event_date"
                                   value="{{ $oldEventDate }}" required>
                        </div>
                        <div class="field-hint" id="event-date-hint" style="{{ $isCustom ? '' : 'display:none;' }}">
                            Enter the date manually.
                        </div>
                        @error('event_date')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Notes --}}
                    <div class="field span-2">
                        <label for="notes">Notes <span class="opt">(optional)</span></label>
                        <div class="input-wrap">
                            <span class="input-icon" style="top:1rem;transform:none;">📝</span>
                            <textarea id="notes" name="notes" placeholder="Any additional notes about this activity…">{{ old('notes') }}</textarea>
                        </div>
                        @error('notes')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

            <div class="form-footer">
                <div class="form-footer-note">🔐 Entry will be attributed to the logged-in admin — <span id="bulk-footer-note" style="display:none;color:var(--navy);font-weight:bold">one entry per selected member</span></div>
                <div class="form-footer-btns">
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">✓ Save Entry</button>
                </div>
            </div>

        </form>
    </div>

</div>

<script>
(function () {
    // Map of event id → { title, date } built from the picker's own options
    // so we don't need to embed a separate JSON blob.
    var customMode = {{ $isCustom ? 'true' : 'false' }};

    var picker   = document.getElementById('event-picker-select');
    var nameInput = document.getElementById('event_name');
    var dateInput = document.getElementById('event_date');
    var hiddenId  = document.getElementById('linked-event-id');
    var hiddenCustom = document.getElementById('is-custom-event');
    var toggleBtn    = document.getElementById('toggle-custom-btn');
    var prefilledNotice = document.getElementById('prefilled-notice');
    var customNotice    = document.getElementById('custom-mode-notice');
    var nameHint = document.getElementById('event-name-hint');
    var dateHint = document.getElementById('event-date-hint');

    // ── Handle picker selection ──────────────────────────────────────────
    picker.addEventListener('change', function () {
        var opt = picker.options[picker.selectedIndex];
        if (!opt || !opt.value) {
            // Cleared back to placeholder
            hiddenId.value = '';
            setPrefilledNotice(false);
            return;
        }

        var title = opt.getAttribute('data-title') || '';
        var date  = opt.getAttribute('data-date')  || '';

        nameInput.value = title;
        if (date) dateInput.value = date;

        hiddenId.value = opt.value;
        setPrefilledNotice(true);

        // Auto-fill hours from event assignments
        fetchAssignmentHours(opt.value);
    });

    function fetchAssignmentHours(eventId) {
        if (!eventId) return;
        fetch('/admin/event-assignment-hours?event_id=' + eventId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            window._assignmentHours = data;

            // If a member is already selected, fill hours immediately
            autoFillHoursForSelectedMember();

            // Show a hint if hours are available
            const hoursInput = document.getElementById('hours');
            const hint = document.getElementById('hours-autofill-hint');
            if (hint) {
                const withHours = data.filter(a => a.hours);
                hint.textContent = withHours.length
                    ? '⚡ ' + withHours.length + ' operator(s) have shift hours — selecting a member will auto-fill.'
                    : 'No shift hours recorded for this event.';
                hint.style.display = '';
            }
        })
        .catch(() => {});
    }

    function autoFillHoursForSelectedMember() {
        const data = window._assignmentHours;
        if (!data || !data.length) return;
        const userSelect = document.getElementById('user_id');
        const hoursInput = document.getElementById('hours');
        if (!userSelect || !hoursInput) return;
        const userId = parseInt(userSelect.value);
        if (!userId) return;
        const match = data.find(a => a.user_id === userId);
        if (match && match.hours) {
            hoursInput.value = match.hours;
            const hint = document.getElementById('hours-autofill-hint');
            if (hint) {
                hint.textContent = '✓ Hours auto-filled from shift data (' + match.hours + 'h). Edit if needed.';
                hint.style.color = '#1a6b3c';
            }
        }
    }

    // Also listen for member selection changes
    document.getElementById('user_id')?.addEventListener('change', autoFillHoursForSelectedMember);

    // ── Toggle custom mode ───────────────────────────────────────────────
    window.toggleCustomMode = function () {
        customMode = !customMode;

        picker.disabled  = customMode;
        hiddenCustom.value = customMode ? '1' : '0';

        toggleBtn.textContent = customMode ? '✓ Custom event' : '+ Custom event';
        toggleBtn.classList.toggle('active', customMode);

        customNotice.classList.toggle('visible', customMode);
        nameHint.style.display = customMode ? '' : 'none';
        dateHint.style.display = customMode ? '' : 'none';

        if (customMode) {
            // Clear any prefilled data from the dropdown
            picker.value   = '';
            hiddenId.value = '';
            nameInput.value = '';
            dateInput.value = '';
            setPrefilledNotice(false);
            nameInput.focus();
        } else {
            setPrefilledNotice(false);
        }
    };

    function setPrefilledNotice(show) {
        prefilledNotice.classList.toggle('visible', show);
    }

    // ── On page load: if a picker option was previously selected (after a
    //    validation failure), ensure the notice is shown correctly.
    if (!customMode && picker.value) {
        setPrefilledNotice(true);
    }
}());
</script>

<script>
var currentMode = 'single';

function setMode(mode) {
    currentMode = mode;
    var form = document.getElementById('log-form');
    var singleField = document.getElementById('field-single-member');
    var bulkField   = document.getElementById('field-bulk-members');
    var singleUser  = document.getElementById('user_id');
    var bulkUsers   = document.getElementById('user_ids');
    var btnSingle   = document.getElementById('btn-single');
    var btnBulk     = document.getElementById('btn-bulk');
    var bulkNote    = document.getElementById('bulk-footer-note');

    if (mode === 'bulk') {
        form.action = '{{ route("admin.activity-logs.store-bulk") }}';
        singleField.style.display = 'none';
        bulkField.style.display   = '';
        singleUser.required = false;
        singleUser.name     = '';
        bulkUsers.required  = true;
        bulkNote.style.display = '';
        btnSingle.style.background = 'var(--white)';
        btnSingle.style.color      = 'var(--text-muted)';
        btnSingle.style.borderColor= 'var(--grey-mid)';
        btnBulk.style.background   = 'var(--navy)';
        btnBulk.style.color        = '#fff';
        btnBulk.style.borderColor  = 'var(--navy)';
    } else {
        form.action = '{{ route("admin.activity-logs.store") }}';
        singleField.style.display = '';
        bulkField.style.display   = 'none';
        singleUser.required = true;
        singleUser.name     = 'user_id';
        bulkUsers.required  = false;
        bulkNote.style.display = 'none';
        btnSingle.style.background = 'var(--navy)';
        btnSingle.style.color      = '#fff';
        btnSingle.style.borderColor= 'var(--navy)';
        btnBulk.style.background   = 'var(--white)';
        btnBulk.style.color        = 'var(--text-muted)';
        btnBulk.style.borderColor  = 'var(--grey-mid)';
    }
}

function selectAllMembers() {
    var sel = document.getElementById('user_ids');
    for (var i = 0; i < sel.options.length; i++) sel.options[i].selected = true;
    updateBulkCount();
}

function clearAllMembers() {
    var sel = document.getElementById('user_ids');
    for (var i = 0; i < sel.options.length; i++) sel.options[i].selected = false;
    updateBulkCount();
}

function updateBulkCount() {
    var sel = document.getElementById('user_ids');
    var count = Array.from(sel.options).filter(o => o.selected).length;
    document.getElementById('bulk-count').textContent = count + ' member' + (count !== 1 ? 's' : '') + ' selected';
}

document.getElementById('user_ids').addEventListener('change', updateBulkCount);
</script>

@endsection