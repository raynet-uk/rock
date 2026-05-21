@extends('layouts.app')
@section('title', 'Event Risk Assessment Generator')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.ra-wrap{max-width:780px;margin:0 auto;padding:2rem 1rem 4rem;}
.ra-head{text-align:center;margin-bottom:2rem;}
.ra-title{font-size:2rem;font-weight:800;color:var(--navy);margin-bottom:.4rem;}
.ra-sub{font-size:.95rem;color:var(--muted);margin-bottom:.25rem;}
.ra-helper{font-size:.82rem;color:var(--muted);font-style:italic;}
/* Progress */
.ra-progress{display:flex;align-items:center;gap:0;margin-bottom:2.5rem;position:relative;}
.ra-progress::before{content:'';position:absolute;top:16px;left:16px;right:16px;height:2px;background:var(--grey-mid);z-index:0;}
.ra-step-dot{display:flex;flex-direction:column;align-items:center;gap:.3rem;flex:1;position:relative;z-index:1;}
.ra-dot{width:32px;height:32px;border-radius:50%;background:var(--grey-mid);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:bold;color:#fff;transition:all .3s;border:2px solid #fff;}
.ra-dot.active{background:var(--navy);}
.ra-dot.done{background:#059669;}
.ra-dot-label{font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);text-align:center;max-width:60px;}
/* Pages */
.ra-page{display:none;}
.ra-page.active{display:block;}
.ra-page-title{font-size:1.4rem;font-weight:700;color:var(--navy);margin-bottom:.3rem;}
.ra-page-sub{font-size:.9rem;color:var(--muted);margin-bottom:1.5rem;}
.ra-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:1.5rem;margin-bottom:1rem;}
.ra-field{margin-bottom:1.25rem;}
.ra-label{display:block;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:.5rem;}
.ra-input{width:100%;padding:.55rem .75rem;border:1px solid var(--grey-mid);border-radius:6px;font-size:.95rem;font-family:inherit;transition:border-color .15s;}
.ra-input:focus{outline:none;border-color:var(--navy);}
.ra-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:600px){.ra-grid{grid-template-columns:1fr;}}
/* Checkboxes & Radios */
.ra-check-group{display:flex;flex-wrap:wrap;gap:.5rem;}
.ra-check-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .85rem;border:1px solid var(--grey-mid);border-radius:999px;cursor:pointer;font-size:.88rem;transition:all .15s;background:#fff;}
.ra-check-item:hover{border-color:var(--navy);background:var(--grey);}
.ra-check-item input{display:none;}
.ra-check-item.selected{background:var(--navy);border-color:var(--navy);color:#fff;}
.ra-radio-group{display:flex;flex-wrap:wrap;gap:.5rem;}
.ra-radio-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .85rem;border:1px solid var(--grey-mid);border-radius:999px;cursor:pointer;font-size:.88rem;transition:all .15s;background:#fff;}
.ra-radio-item:hover{border-color:var(--navy);background:var(--grey);}
.ra-radio-item input{display:none;}
.ra-radio-item.selected{background:var(--navy);border-color:var(--navy);color:#fff;}
/* Buttons */
.ra-btn-row{display:flex;gap:.75rem;margin-top:2rem;}
.ra-btn{padding:.7rem 1.75rem;border-radius:999px;font-size:.95rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;}
.ra-btn-primary{background:var(--red);color:#fff;}
.ra-btn-primary:hover{background:#a50e24;}
.ra-btn-secondary{background:#e8eef5;color:var(--navy);}
.ra-btn-secondary:hover{background:#d0dcef;}
/* Review */
.ra-risk-table{width:100%;border-collapse:collapse;font-size:.85rem;margin-top:.75rem;}
.ra-risk-table th{background:var(--navy);color:#fff;padding:.5rem .75rem;text-align:left;}
.ra-risk-table td{padding:.5rem .75rem;border-bottom:1px solid var(--grey-mid);}
.rag-green{background:#d1fae5;color:#065f46;font-weight:bold;padding:.25rem .75rem;border-radius:999px;display:inline-block;}
.rag-amber{background:#fef3c7;color:#92400e;font-weight:bold;padding:.25rem .75rem;border-radius:999px;display:inline-block;}
.rag-red{background:#fee2e2;color:#991b1b;font-weight:bold;padding:.25rem .75rem;border-radius:999px;display:inline-block;}
.residual-Low{background:#d1fae5;color:#065f46;}
.residual-Medium{background:#fef3c7;color:#92400e;}
.residual-High{background:#fee2e2;color:#991b1b;}
</style>

<div class="ra-wrap">
    <div class="ra-head">
        <div class="ra-title">Event Risk Assessment Generator</div>
        <div class="ra-sub">Create a structured event risk assessment from operational questions.</div>
        <div class="ra-helper">This tool supports planning and does not replace dynamic risk assessment or organiser responsibilities.</div>
    </div>

    {{-- Progress --}}
    <div class="ra-progress" id="raProgress">
        @foreach(['Event Details','Deployment','Site Conditions','Welfare','Technical','Dynamic','Review'] as $i => $step)
        <div class="ra-step-dot" data-step="{{ $i+1 }}">
            <div class="ra-dot {{ $i===0 ? 'active' : '' }}">{{ $i+1 }}</div>
            <div class="ra-dot-label">{{ $step }}</div>
        </div>
        @endforeach
    </div>

    <form id="raForm">
        @csrf

        {{-- Page 1: Event Details --}}
        <div class="ra-page active" data-page="1">
            <div class="ra-page-title">Tell us about the event</div>
            <div class="ra-page-sub">Enter basic information so we can tailor the assessment.</div>
            <div class="ra-card">
                <div class="ra-grid">
                    <div class="ra-field">
                        <label class="ra-label">Event Name *</label>
                        <input type="text" name="event_name" class="ra-input" placeholder="e.g. Rainford Walking Day 2026" required>
                    </div>
                    <div class="ra-field">
                        <label class="ra-label">Location *</label>
                        <input type="text" name="location" class="ra-input" placeholder="e.g. Rainford Village, WA11">
                    </div>
                    <div class="ra-field">
                        <label class="ra-label">Date</label>
                        <input type="date" name="event_date" class="ra-input">
                    </div>
                    <div class="ra-field">
                        <label class="ra-label">Start Time</label>
                        <input type="time" name="start_time" class="ra-input">
                    </div>
                    <div class="ra-field">
                        <label class="ra-label">Finish Time</label>
                        <input type="time" name="finish_time" class="ra-input">
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Expected Attendance</label>
                    <div class="ra-radio-group" data-name="attendance">
                        @foreach(['<100','100–500','500–2000','>2000'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="attendance" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Environment</label>
                    <div class="ra-check-group" data-name="environment[]">
                        @foreach(['Urban','Rural','Remote','Mixed'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="environment[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Event Type</label>
                    <div class="ra-check-group" data-name="event_type[]">
                        @foreach(['Static','Moving event','Walking event','Motorsport','Public gathering','Emergency exercise','Other'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="event_type[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Other Agencies Involved</label>
                    <div class="ra-check-group" data-name="other_agencies[]">
                        @foreach(['Police','Ambulance','Fire','Event Safety Team','Mountain Rescue','None'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="other_agencies[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page 2: Deployment --}}
        <div class="ra-page" data-page="2">
            <div class="ra-page-title">How will Liverpool RAYNET support?</div>
            <div class="ra-page-sub">Describe the deployment plan.</div>
            <div class="ra-card">
                <div class="ra-field">
                    <label class="ra-label">Number of Operators</label>
                    <div class="ra-radio-group" data-name="operator_count">
                        @foreach(['1–3','4–8','9–15','15+'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="operator_count" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Roles</label>
                    <div class="ra-check-group">
                        @foreach(['Control','Mobile operators','Fixed checkpoints','Sweeps','Technical support','Vehicle support'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="roles[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Communications</label>
                    <div class="ra-check-group">
                        @foreach(['Voice','DMR','LoRa','APRS','Telephone fallback'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="communications[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Infrastructure</label>
                    <div class="ra-check-group">
                        @foreach(['Repeater','Temporary mast','Generator','Temporary power'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="infrastructure[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page 3: Site Conditions --}}
        <div class="ra-page" data-page="3">
            <div class="ra-page-title">What conditions will members work in?</div>
            <div class="ra-page-sub">Help us understand the site and environment.</div>
            <div class="ra-card">
                <div class="ra-field">
                    <label class="ra-label">Terrain</label>
                    <div class="ra-check-group">
                        @foreach(['Roads','Pavement','Fields','Hills','Woodland','Water'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="terrain[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Operator Movement</label>
                    <div class="ra-radio-group">
                        @foreach(['Static','Walking','Vehicle'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="operator_movement" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Weather Exposure</label>
                    <div class="ra-radio-group">
                        @foreach(['Low','Moderate','High'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="weather_exposure" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Road Exposure</label>
                    <div class="ra-radio-group">
                        @foreach(['None','Adjacent','Active crossings'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="road_exposure" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Access</label>
                    <div class="ra-radio-group">
                        @foreach(['Easy','Limited','Remote'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="access" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page 4: Welfare --}}
        <div class="ra-page" data-page="4">
            <div class="ra-page-title">Help us understand operator welfare</div>
            <div class="ra-page-sub">Welfare considerations affect the risk profile significantly.</div>
            <div class="ra-card">
                <div class="ra-field">
                    <label class="ra-label">Expected Deployment Duration</label>
                    <div class="ra-radio-group">
                        @foreach(['<4h','4–8h','8–12h','>12h'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="deployment_duration" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Facilities Available</label>
                    <div class="ra-check-group">
                        @foreach(['Toilets','Water','Shelter','Catering'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="facilities[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Lone Working</label>
                    <div class="ra-radio-group">
                        @foreach(['No','Possible','Expected'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="lone_working" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Under-18 Participants</label>
                    <div class="ra-radio-group">
                        @foreach(['No','Yes'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="under_18" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Night Operation</label>
                    <div class="ra-radio-group">
                        @foreach(['No','Yes'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="night_operation" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page 5: Technical --}}
        <div class="ra-page" data-page="5">
            <div class="ra-page-title">Will specialist equipment be used?</div>
            <div class="ra-page-sub">Technical hazards are generated automatically.</div>
            <div class="ra-card">
                <div class="ra-field">
                    <label class="ra-label">Equipment</label>
                    <div class="ra-check-group">
                        @foreach(['Mast','Generator','Temporary power','Cabling','Mobile control'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="equipment[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Power Source</label>
                    <div class="ra-radio-group">
                        @foreach(['Battery','Mains','Generator'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="power_source" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Vehicles Operating on Site?</label>
                    <div class="ra-radio-group">
                        @foreach(['No','Yes'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="vehicles_operating" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page 6: Dynamic --}}
        <div class="ra-page" data-page="6">
            <div class="ra-page-title">Operational considerations</div>
            <div class="ra-page-sub">Final operational context before generating your assessment.</div>
            <div class="ra-card">
                <div class="ra-field">
                    <label class="ra-label">Public Order</label>
                    <div class="ra-radio-group">
                        @foreach(['None','Possible','Expected'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="public_order" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Weather Contingency</label>
                    <div class="ra-radio-group">
                        @foreach(['Green','Amber','Red'] as $opt)
                        <label class="ra-radio-item"><input type="radio" name="weather_contingency" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Fallback Communications</label>
                    <div class="ra-check-group">
                        @foreach(['Radio','Mobile','Manual relay'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="fallback_comms[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Withdrawal Authority</label>
                    <div class="ra-check-group">
                        @foreach(['Event controller','Group controller','Any operator'] as $opt)
                        <label class="ra-check-item"><input type="checkbox" name="withdrawal_authority[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="ra-field">
                    <label class="ra-label">Additional Notes</label>
                    <textarea name="notes" class="ra-input" rows="3" placeholder="Any other relevant operational information…" style="resize:vertical;"></textarea>
                </div>
            </div>
        </div>

        {{-- Page 7: Review --}}
        <div class="ra-page" data-page="7">
            <div class="ra-page-title">Review your assessment</div>
            <div class="ra-page-sub">Generated automatically from your answers. Review before saving.</div>
            <div id="raReviewContent">
                <div style="text-align:center;padding:3rem;color:var(--muted);">Generating assessment…</div>
            </div>
        </div>

        <div class="ra-btn-row">
            <button type="button" class="ra-btn ra-btn-secondary" id="raBtnBack" style="display:none;" onclick="raNav(-1)">← Back</button>
            <button type="button" class="ra-btn ra-btn-primary" id="raBtnNext" onclick="raNav(1)">Next →</button>
            <button type="button" class="ra-btn ra-btn-primary" id="raBtnGenerate" style="display:none;" onclick="raGenerate()">⚡ Generate Risk Assessment</button>
        </div>
    </form>
</div>

<script>
var raCurrentPage = 1;
var raTotalPages  = 7;
var raData = {};
var raId   = null;

// Pill toggle behaviour
document.querySelectorAll('.ra-check-item, .ra-radio-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        var input = this.querySelector('input');
        if (input.type === 'radio') {
            var name = input.name;
            document.querySelectorAll('input[name="'+name+'"]').forEach(function(r) {
                r.closest('.ra-radio-item').classList.remove('selected');
                r.checked = false;
            });
            this.classList.add('selected');
            input.checked = true;
        } else {
            this.classList.toggle('selected');
            input.checked = this.classList.contains('selected');
        }
    });
});

function raNav(dir) {
    if (dir === 1 && raCurrentPage === 1) {
        var name = document.querySelector('input[name="event_name"]').value.trim();
        if (!name) { alert('Please enter an event name.'); return; }
    }
    var newPage = raCurrentPage + dir;
    if (newPage < 1 || newPage > raTotalPages) return;

    document.querySelector('.ra-page[data-page="'+raCurrentPage+'"]').classList.remove('active');
    raCurrentPage = newPage;
    document.querySelector('.ra-page[data-page="'+raCurrentPage+'"]').classList.add('active');

    // Update dots
    document.querySelectorAll('.ra-step-dot').forEach(function(dot, i) {
        dot.querySelector('.ra-dot').classList.remove('active','done');
        if (i+1 < raCurrentPage) dot.querySelector('.ra-dot').classList.add('done');
        if (i+1 === raCurrentPage) dot.querySelector('.ra-dot').classList.add('active');
    });

    document.getElementById('raBtnBack').style.display = raCurrentPage > 1 ? '' : 'none';

    if (raCurrentPage === 7) {
        document.getElementById('raBtnNext').style.display = 'none';
        document.getElementById('raBtnGenerate').style.display = '';
        buildReview();
    } else {
        document.getElementById('raBtnNext').style.display = '';
        document.getElementById('raBtnGenerate').style.display = 'none';
    }

    window.scrollTo({top:0,behavior:'smooth'});
}

function collectFormData() {
    var fd = new FormData(document.getElementById('raForm'));
    var data = {};
    for (var [k,v] of fd.entries()) {
        if (k.endsWith('[]')) {
            var key = k.slice(0,-2);
            if (!data[key]) data[key] = [];
            data[key].push(v);
        } else {
            data[k] = v;
        }
    }
    return data;
}

function buildReview() {
    var content = document.getElementById('raReviewContent');
    content.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--muted);">⚡ Analysing your answers…</div>';
    var data = collectFormData();

    fetch('/risk-assessment', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify(data)
    }).then(r=>r.json()).then(function(res) {
        raId = res.id;
        var rag = res.rag;
        var ragLabels = {green:'GREEN — Suitable to proceed',amber:'AMBER — Proceed with controls',red:'RED — Do not proceed without further review'};
        var ragColours = {green:'#059669',amber:'#f59e0b',red:'#dc2626'};

        var html = '<div style="background:'+ragColours[rag]+';color:#fff;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">';
        html += '<div style="font-size:1.2rem;font-weight:800;">Overall Event Risk Status</div>';
        html += '<div style="font-size:1.5rem;font-weight:900;margin-top:.25rem;">'+ragLabels[rag]+'</div>';
        if(rag==='green') html += '<div style="font-size:.85rem;margin-top:.4rem;opacity:.85;">The event appears suitable to support using normal controls.</div>';
        if(rag==='amber') html += '<div style="font-size:.85rem;margin-top:.4rem;opacity:.85;">The event may be supported, but controls must be checked and briefed.</div>';
        if(rag==='red')   html += '<div style="font-size:.85rem;margin-top:.4rem;opacity:.85;">The event should not proceed in its current form without further review.</div>';
        html += '</div>';

        if (rag === 'red') {
            html += '<div style="background:#fee2e2;border-left:4px solid #dc2626;padding:1rem 1.25rem;border-radius:0 6px 6px 0;margin-bottom:1.5rem;font-size:.88rem;color:#991b1b;"><strong>⚠ This assessment contains one or more high residual risks.</strong> It requires further review before the event can be approved. You may generate a PDF marked as Draft – Not Approved.</div>';
        }
        if (rag === 'amber') {
            html += '<div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:1rem 1.25rem;border-radius:0 6px 6px 0;margin-bottom:1.5rem;font-size:.88rem;color:#92400e;">Amber controls must be reviewed and included in the event briefing before approval.</div>';
        }

        html += '<div style="font-size:.95rem;font-weight:700;color:var(--navy);margin-bottom:.75rem;">Detected Risks & Controls</div>';
        html += '<table class="ra-risk-table"><thead><tr><th>Hazard</th><th>Control Measures</th><th>Likelihood</th><th>Severity</th><th>Residual</th></tr></thead><tbody>';
        res.risks.forEach(function(r) {
            html += '<tr><td><strong>'+r.hazard+'</strong><br><small style="color:#6b7f96;">'+r.cause+'</small></td>';
            html += '<td>'+r.control+'</td>';
            html += '<td>'+r.likelihood+'</td>';
            html += '<td>'+r.severity+'</td>';
            html += '<td><span class="residual-'+r.residual+'" style="padding:.2rem .5rem;border-radius:4px;font-weight:bold;">'+r.residual+'</span></td></tr>';
        });
        html += '</tbody></table>';

        html += '<div style="margin-top:2rem;display:flex;gap:.75rem;flex-wrap:wrap;">';
        html += '<a href="/risk-assessment/'+raId+'/pdf" class="ra-btn ra-btn-primary" target="_blank">📥 Download PDF</a>';
        html += '<a href="/risk-assessment/'+raId+'" class="ra-btn ra-btn-secondary">View Saved Assessment</a>';
        html += '</div>';

        content.innerHTML = html;
    }).catch(function(e) {
        content.innerHTML = '<div style="color:#dc2626;padding:1rem;">Failed to generate assessment. Please try again.</div>';
    });
}

function raGenerate() {
    if (raId) {
        window.location.href = '/risk-assessment/' + raId + '/pdf';
    } else {
        buildReview();
    }
}
</script>
@endsection
