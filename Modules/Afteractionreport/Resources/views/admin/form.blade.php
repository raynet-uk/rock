@extends('layouts.app')
@section('title', isset($item) ? 'Edit PXR — ' . $item->exercise_name : 'New Post-Exercise Report')
@section('content')
<div style="max-width:850px;margin:0 auto;padding:2rem 1rem;font-family:inherit">

    <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.afteractionreport.index') }}" style="color:#6b7280;font-size:.85rem;text-decoration:none">← Back to Reports</a>
        <h1 style="font-size:1.35rem;font-weight:700;margin:.4rem 0 0">
            {{ isset($item) ? 'Edit PXR — ' . $item->exercise_name : 'New Post-Exercise Report (PXR)' }}
        </h1>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.5rem;font-size:.875rem">
        <strong>Please correct the following:</strong>
        <ul style="margin:.4rem 0 0;padding-left:1.2rem">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @php
        $ls = 'display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.3rem';
        $is = 'width:100%;border:1px solid #d1d5db;border-radius:7px;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;font-family:inherit';
        $ts = $is . ';resize:vertical;min-height:80px';
        $cs = 'background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:1.25rem';
        $hs = 'font-size:.95rem;font-weight:700;color:#1e3a8a;margin:0 0 1rem;padding-bottom:.4rem;border-bottom:2px solid #c7d2fe';
        $gs = 'display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem';
    @endphp

    <form action="{{ isset($item) ? route('admin.afteractionreport.update', $item->id) : route('admin.afteractionreport.store') }}"
          method="POST" id="pxr-form">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        {{-- Section 1 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">1. Administrative Overview</h2>
            <div style="{{ $gs }}">
                <div>
                    <label style="{{ $ls }}">Exercise Name <span style="color:#dc2626">*</span></label>
                    <input type="text" name="exercise_name" value="{{ old('exercise_name', $item->exercise_name ?? '') }}" style="{{ $is }}" required>
                </div>
                <div>
                    <label style="{{ $ls }}">Exercise Date <span style="color:#dc2626">*</span></label>
                    <input type="date" name="exercise_date" value="{{ old('exercise_date', isset($item->exercise_date) ? \Carbon\Carbon::parse($item->exercise_date)->format('Y-m-d') : '') }}" style="{{ $is }}" required>
                </div>
            </div>
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">Exercise Type <span style="color:#dc2626">*</span></label>
                <select name="exercise_type" style="{{ $is }}" required>
                    @foreach(['Tabletop','Command Post','Full-Scale Live Exercise','Communications Exercise','Interoperability Exercise'] as $t)
                        <option value="{{ $t }}" {{ old('exercise_type', $item->exercise_type ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div style="{{ $gs }}">
                <div>
                    <label style="{{ $ls }}">Author Name</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $item->author_name ?? '') }}" style="{{ $is }}" placeholder="Full name">
                </div>
                <div>
                    <label style="{{ $ls }}">Author Callsign</label>
                    <input type="text" name="author_callsign" value="{{ old('author_callsign', $item->author_callsign ?? '') }}" style="{{ $is }}" placeholder="e.g. M0XYZ">
                </div>
            </div>
            <div style="{{ $gs }}">
                <div>
                    <label style="{{ $ls }}">RAYNET Group Name</label>
                    <input type="text" name="group_name" value="{{ old('group_name', $item->group_name ?? '') }}" style="{{ $is }}" placeholder="e.g. Liverpool">
                </div>
                <div>
                    <label style="{{ $ls }}">Location / NGR</label>
                    <input type="text" name="location" value="{{ old('location', $item->location ?? '') }}" style="{{ $is }}" placeholder="ICP location or grid reference">
                </div>
            </div>
            <div style="{{ $gs }}">
                <div>
                    <label style="{{ $ls }}">Operational Start</label>
                    <input type="datetime-local" name="op_start" value="{{ old('op_start', isset($item->op_start) ? \Carbon\Carbon::parse($item->op_start)->format('Y-m-d\TH:i') : '') }}" style="{{ $is }}">
                </div>
                <div>
                    <label style="{{ $ls }}">Operational End</label>
                    <input type="datetime-local" name="op_end" value="{{ old('op_end', isset($item->op_end) ? \Carbon\Carbon::parse($item->op_end)->format('Y-m-d\TH:i') : '') }}" style="{{ $is }}">
                </div>
            </div>
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">Participants</label>
                <textarea name="participants" style="{{ $ts }}" placeholder="List RAYNET personnel and participating agencies">{{ old('participants', $item->participants ?? '') }}</textarea>
            </div>
            <div>
                <label style="{{ $ls }}">Distribution</label>
                <input type="text" name="distribution" value="{{ old('distribution', $item->distribution ?? '') }}" style="{{ $is }}" placeholder="e.g. Local Resilience Forum, User Services, RAYNET County Coordinator">
            </div>
        </div>

        {{-- Section 2 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">2. Objectives &amp; Scope</h2>
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">Primary Objective</label>
                <textarea name="primary_objective" style="{{ $ts }}" placeholder="e.g. To establish a resilient VHF voice link between the FCP and the SCC where mobile networks have failed.">{{ old('primary_objective', $item->primary_objective ?? '') }}</textarea>
            </div>
            <div>
                <label style="{{ $ls }}">JESIP Principles Being Tested</label>
                <input type="text" name="jesip_principles_tested" value="{{ old('jesip_principles_tested', $item->jesip_principles_tested ?? '') }}" style="{{ $is }}" placeholder="e.g. Co-location, Communication, Coordination">
            </div>
        </div>

        {{-- Section 3 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">3. Chronology of Events</h2>
            <p style="font-size:.8rem;color:#6b7280;margin:-.5rem 0 1rem">High-level timeline of RAYNET involvement.</p>
            <div id="chrono-rows"></div>
            <button type="button" onclick="addChronoRow()" style="background:#f0f4ff;border:1px dashed #6366f1;color:#4338ca;padding:.4rem .9rem;border-radius:7px;font-size:.82rem;cursor:pointer;font-family:inherit">+ Add Event</button>
            <input type="hidden" name="chronology" id="chronology-json" value="{{ old('chronology', $item->chronology ?? '[]') }}">
        </div>

        {{-- Section 4 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">4. Analysis of JESIP Principles</h2>
            @foreach([
                ['jesip_colocation',       'Co-location',                   'Was the RAYNET LO physically positioned with the Incident Commander?'],
                ['jesip_communication',    'Communication',                  'Was Plain Language used? Were M/ETHANE reports delivered accurately and timely?'],
                ['jesip_coordination',     'Coordination',                   'Did RAYNET assets integrate effectively with the User Service\'s tactical plan?'],
                ['jesip_joint_risk',       'Joint Understanding of Risk',    'Were RAYNET operators briefed on site hazards (e.g. flood risk, downed power lines)?'],
                ['jesip_shared_awareness', 'Shared Situational Awareness',   'Did the RAYNET net contribute to the Common Operating Picture (COP)?'],
            ] as $f)
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">{{ $f[1] }}</label>
                <p style="font-size:.78rem;color:#9ca3af;margin:-.2rem 0 .3rem;font-style:italic">{{ $f[2] }}</p>
                <textarea name="{{ $f[0] }}" style="{{ $ts }}">{{ old($f[0], $item->{$f[0]} ?? '') }}</textarea>
            </div>
            @endforeach
        </div>

        {{-- Section 5 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">5. Technical &amp; Operational Performance</h2>
            @foreach([
                ['rf_coverage',           'RF Coverage',           'Assessment of the Comms Map. Identify any dead spots in the local topography.'],
                ['equipment_reliability', 'Equipment Reliability', 'Performance of masts, antennas, and power supplies in the field.'],
                ['logistics',             'Logistics',             'Efficiency of the call-out system and personnel welfare (rations/rest cycles).'],
                ['digital_performance',   'Data / Digital',        'If used, success rate of digital message delivery (e.g. Winlink/VARA) compared to voice.'],
            ] as $f)
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">{{ $f[1] }}</label>
                <p style="font-size:.78rem;color:#9ca3af;margin:-.2rem 0 .3rem;font-style:italic">{{ $f[2] }}</p>
                <textarea name="{{ $f[0] }}" style="{{ $ts }}">{{ old($f[0], $item->{$f[0]} ?? '') }}</textarea>
            </div>
            @endforeach
        </div>

        {{-- Section 6 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">6. Lessons Identified</h2>
            <p style="font-size:.8rem;color:#6b7280;margin:-.5rem 0 1rem">Observation → Discussion → Recommendation format.</p>
            <div id="lessons-rows"></div>
            <button type="button" onclick="addLessonRow()" style="background:#fffbeb;border:1px dashed #f59e0b;color:#92400e;padding:.4rem .9rem;border-radius:7px;font-size:.82rem;cursor:pointer;font-family:inherit">+ Add Lesson</button>
            <input type="hidden" name="lessons" id="lessons-json" value="{{ old('lessons', $item->lessons ?? '[]') }}">
        </div>

        {{-- Section 7 --}}
        <div style="{{ $cs }}">
            <h2 style="{{ $hs }}">7. Conclusion &amp; Sign-off</h2>
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">Overall Grade</label>
                <select name="overall_grade" style="{{ $is }}">
                    <option value="">— Select —</option>
                    @foreach(['Objective Met','Partially Met','Not Met'] as $g)
                        <option value="{{ $g }}" {{ old('overall_grade', $item->overall_grade ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:1rem">
                <label style="{{ $ls }}">Closing Statement</label>
                <textarea name="closing_statement" style="{{ $ts }}" placeholder="A brief summary of the value RAYNET provided to the User Service.">{{ old('closing_statement', $item->closing_statement ?? '') }}</textarea>
            </div>
            <div style="{{ $gs }}">
                <div>
                    <label style="{{ $ls }}">Signed By</label>
                    <input type="text" name="signed_by" value="{{ old('signed_by', $item->signed_by ?? '') }}" style="{{ $is }}" placeholder="Name / Callsign">
                </div>
                <div>
                    <label style="{{ $ls }}">Date Signed</label>
                    <input type="date" name="signed_date" value="{{ old('signed_date', isset($item->signed_date) ? \Carbon\Carbon::parse($item->signed_date)->format('Y-m-d') : '') }}" style="{{ $is }}">
                </div>
            </div>
        </div>

        <div style="display:flex;gap:.6rem;padding:.5rem 0 2rem">
            <button type="submit" style="background:#1d4ed8;color:#fff;border:none;padding:.55rem 1.5rem;border-radius:7px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit">
                {{ isset($item) ? 'Save Changes' : 'Create Report' }}
            </button>
            <a href="{{ route('admin.afteractionreport.index') }}" style="background:#fff;border:1px solid #d1d5db;color:#374151;padding:.55rem 1.1rem;border-radius:7px;font-size:.875rem;text-decoration:none;font-weight:500">Cancel</a>
        </div>
    </form>
</div>

<script>
let chronoData = [];
try { chronoData = JSON.parse(document.getElementById('chronology-json').value || '[]'); } catch(e) {}

function renderChronoRows() {
    const c = document.getElementById('chrono-rows');
    c.innerHTML = '';
    chronoData.forEach(function(e, i) {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;gap:.6rem;margin-bottom:.5rem;align-items:center';
        row.innerHTML = '<input type="text" placeholder="T+00" value="' + esc(e.offset||'') + '" style="width:90px;border:1px solid #d1d5db;border-radius:7px;padding:.45rem .6rem;font-size:.85rem;font-family:monospace;box-sizing:border-box" oninput="updateChrono('+i+',\'offset\',this.value)">'
            + '<input type="text" placeholder="Event description" value="' + esc(e.event||'') + '" style="flex:1;border:1px solid #d1d5db;border-radius:7px;padding:.45rem .6rem;font-size:.875rem;box-sizing:border-box;font-family:inherit" oninput="updateChrono('+i+',\'event\',this.value)">'
            + '<button type="button" onclick="removeChrono('+i+')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;padding:.2rem .4rem">✕</button>';
        c.appendChild(row);
    });
}
function addChronoRow()           { chronoData.push({offset:'',event:''}); renderChronoRows(); syncChrono(); }
function removeChrono(i)          { chronoData.splice(i,1); renderChronoRows(); syncChrono(); }
function updateChrono(i,k,v)      { chronoData[i][k]=v; syncChrono(); }
function syncChrono()             { document.getElementById('chronology-json').value = JSON.stringify(chronoData); }

let lessonsData = [];
try { lessonsData = JSON.parse(document.getElementById('lessons-json').value || '[]'); } catch(e) {}

function renderLessonRows() {
    const c = document.getElementById('lessons-rows');
    c.innerHTML = '';
    lessonsData.forEach(function(l, i) {
        const card = document.createElement('div');
        card.style.cssText = 'background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:1rem;margin-bottom:.75rem';
        card.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem"><strong style="font-size:.82rem;color:#92400e">Lesson '+(i+1)+'</strong><button type="button" onclick="removeLesson('+i+')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.8rem;padding:0;font-family:inherit">Remove</button></div>'
            + '<div style="margin-bottom:.6rem"><label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.25rem">Observation</label><textarea rows="2" style="width:100%;border:1px solid #fcd34d;border-radius:6px;padding:.45rem .6rem;font-size:.875rem;box-sizing:border-box;font-family:inherit;resize:vertical;background:#fff" oninput="updateLesson('+i+',\'observation\',this.value)">'+esc(l.observation||'')+'</textarea></div>'
            + '<div style="margin-bottom:.6rem"><label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.25rem">Discussion</label><textarea rows="2" style="width:100%;border:1px solid #fcd34d;border-radius:6px;padding:.45rem .6rem;font-size:.875rem;box-sizing:border-box;font-family:inherit;resize:vertical;background:#fff" oninput="updateLesson('+i+',\'discussion\',this.value)">'+esc(l.discussion||'')+'</textarea></div>'
            + '<div><label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.25rem">Recommendation</label><textarea rows="2" style="width:100%;border:1px solid #fcd34d;border-radius:6px;padding:.45rem .6rem;font-size:.875rem;box-sizing:border-box;font-family:inherit;resize:vertical;background:#fff" oninput="updateLesson('+i+',\'recommendation\',this.value)">'+esc(l.recommendation||'')+'</textarea></div>';
        c.appendChild(card);
    });
}
function addLessonRow()           { lessonsData.push({observation:'',discussion:'',recommendation:''}); renderLessonRows(); syncLessons(); }
function removeLesson(i)          { lessonsData.splice(i,1); renderLessonRows(); syncLessons(); }
function updateLesson(i,k,v)      { lessonsData[i][k]=v; syncLessons(); }
function syncLessons()            { document.getElementById('lessons-json').value = JSON.stringify(lessonsData); }

document.getElementById('pxr-form').addEventListener('submit', function() { syncChrono(); syncLessons(); });

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

renderChronoRows();
renderLessonRows();
</script>
@endsection
