@extends('layouts.app')
@section('title', 'New Event Support Pack')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:860px;margin:0 auto;padding:2rem 1rem 4rem;}
.page-head{text-align:center;margin-bottom:2rem;}
.page-title{font-size:2rem;font-weight:800;color:var(--navy);margin-bottom:.3rem;}
.page-sub{font-size:.95rem;color:var(--muted);}
/* Progress */
.progress-bar{height:4px;background:var(--grey-mid);border-radius:2px;margin-bottom:2rem;overflow:hidden;}
.progress-fill{height:100%;background:var(--red);transition:width .3s;border-radius:2px;}
.step-labels{display:flex;justify-content:space-between;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:1.5rem;}
.step-labels span.active{color:var(--navy);}
/* Pages */
.esp-page{display:none;}
.esp-page.active{display:block;}
.esp-page-title{font-size:1.4rem;font-weight:700;color:var(--navy);margin-bottom:.25rem;}
.esp-page-sub{font-size:.88rem;color:var(--muted);margin-bottom:1.5rem;}
.esp-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:1.5rem;margin-bottom:1rem;}
.esp-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--grey-mid);}
.field{margin-bottom:1rem;}
.label{display:block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--navy);margin-bottom:.4rem;}
.input{width:100%;padding:.55rem .75rem;border:1px solid var(--grey-mid);border-radius:6px;font-size:.9rem;font-family:inherit;}
.input:focus{outline:none;border-color:var(--navy);}
textarea.input{resize:vertical;min-height:80px;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
@media(max-width:600px){.grid-2,.grid-3{grid-template-columns:1fr;}}
/* Pills */
.pill-group{display:flex;flex-wrap:wrap;gap:.4rem;}
.pill{display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .85rem;border:1px solid var(--grey-mid);border-radius:999px;cursor:pointer;font-size:.85rem;transition:all .15s;background:#fff;user-select:none;}
.pill:hover{border-color:var(--navy);background:var(--grey);}
.pill.selected{background:var(--navy);border-color:var(--navy);color:#fff;}
.pill input{display:none;}
/* Buttons */
.btn-row{display:flex;gap:.75rem;margin-top:2rem;align-items:center;}
.btn{padding:.7rem 1.75rem;border-radius:999px;font-size:.95rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;}
.btn-primary{background:var(--red);color:#fff;}
.btn-secondary{background:#e8eef5;color:var(--navy);}
.btn-green{background:#059669;color:#fff;}
.autosave-indicator{font-size:.78rem;color:var(--muted);margin-left:auto;}
/* Scope warning */
.scope-warning{background:#fef3c7;border-left:4px solid #f59e0b;padding:.75rem 1rem;border-radius:0 6px 6px 0;font-size:.85rem;color:#92400e;display:none;margin-top:.5rem;}
/* Review */
.rag-banner{padding:1.25rem 1.5rem;border-radius:8px;text-align:center;margin-bottom:1.25rem;}
.risk-table{width:100%;border-collapse:collapse;font-size:.83rem;margin-top:.75rem;}
.risk-table th{background:var(--navy);color:#fff;padding:.5rem .75rem;text-align:left;font-size:.78rem;}
.risk-table td{padding:.5rem .75rem;border-bottom:1px solid var(--grey-mid);}
.res-Low{background:#d1fae5;color:#065f46;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;}
.res-Medium{background:#fef3c7;color:#92400e;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;}
.res-High{background:#fee2e2;color:#991b1b;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;}
</style>

<div class="wrap">
    <div class="page-head">
        <div class="page-title">Event Support Pack Generator</div>
        <div class="page-sub">Answer the questions below. The system will generate hazards, controls, and all documents automatically.</div>
    </div>

    @if(isset($clonePack))
    <div style="background:#dbeafe;border-left:4px solid #3b82f6;padding:.75rem 1rem;border-radius:0 6px 6px 0;margin-bottom:1.5rem;font-size:.88rem;color:#1e40af;">
        📋 Cloned from: <strong>{{ optional($clonePack)->event_name }}</strong> — update the date and details below.
    </div>
    @endif

    <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:8.33%"></div></div>
    <div class="step-labels">
        @foreach(['Identity','Diary','Assistance','Role','Operators','Comms','Access','Equipment','Welfare','Review','Approve','Generate'] as $i => $s)
        <span id="stepLabel{{ $i+1 }}" class="{{ $i===0 ? 'active' : '' }}">{{ $s }}</span>
        @endforeach
    </div>

    <form id="espForm">
        @csrf

        {{-- STEP 1: Event Identity --}}
        <div class="esp-page active" data-page="1">
            <div class="esp-page-title">Tell us about the event</div>
            <div class="esp-page-sub">This information identifies the event and appears on all generated documents.</div>
            <div class="esp-card">
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Event Name *</label>
                        <input type="text" name="event_name" class="input" placeholder="e.g. Rainford Walking Day 2026" required value="{{ optional($clonePack)->event_name ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Event Date *</label>
                        <input type="date" name="event_date" class="input" required value="{{ optional($clonePack)->event_date?->format('Y-m-d') ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Number of Days</label>
                        <input type="number" name="duration_days" class="input" value="{{ optional($clonePack)->duration_days ?? 1 }}" min="1" max="14">
                    </div>
                    <div class="field">
                        <label class="label">Event Type</label>
                        <select name="event_type" class="input">
                            <option value="">Select...</option>
                            @foreach(['Public event','Exercise','Internal meeting','Training','Emergency support','Planned resilience support','Other'] as $opt)
                            <option value="{{ $opt }}" {{ (optional($clonePack)->event_type ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Location *</label>
                        <input type="text" name="location" class="input" placeholder="Full address or venue" required value="{{ optional($clonePack)->location ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Town / Area</label>
                        <input type="text" name="town_area" class="input" placeholder="e.g. Rainford, WA11" value="{{ optional($clonePack)->town_area ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Start Time</label>
                        <input type="time" name="start_time" class="input" value="{{ optional($clonePack)->start_time ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Finish Time</label>
                        <input type="time" name="finish_time" class="input" value="{{ optional($clonePack)->finish_time ?? '' }}">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Event Description</label>
                    <textarea name="event_description" class="input" placeholder="Plain English description of the event...">{{ optional($clonePack)->event_description ?? '' }}</textarea>
                </div>
            </div>
            <div class="esp-card">
                <div class="esp-card-title">Organiser / User Service</div>
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Organiser / User Service Name</label>
                        <input type="text" name="organiser_name" class="input" placeholder="e.g. St John Ambulance" value="{{ optional($clonePack)->organiser_name ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Organiser Contact Name</label>
                        <input type="text" name="organiser_contact" class="input" value="{{ optional($clonePack)->organiser_contact ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Organiser Phone</label>
                        <input type="tel" name="organiser_phone" class="input" value="{{ optional($clonePack)->organiser_phone ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Organiser Email</label>
                        <input type="email" name="organiser_email" class="input" value="{{ optional($clonePack)->organiser_email ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: National Diary --}}
        <div class="esp-page" data-page="2">
            <div class="esp-page-title">National Diary Information</div>
            <div class="esp-page-sub">Captures all fields required for the RAYNET-UK national event diary.</div>
            <div class="esp-card">
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Group</label>
                        <input type="text" name="group_ref" class="input" value="10/ME/179 - Liverpool" readonly style="background:#f3f4f6;">
                    </div>
                    <div class="field">
                        <label class="label">Operational Controller / Contact Callsign *</label>
                        <input type="text" name="controller_callsign" class="input" placeholder="e.g. M0ABC" value="{{ optional($clonePack)->controller_callsign ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Primary Working Frequency *</label>
                        <input type="text" name="primary_frequency" class="input" placeholder="e.g. 145.500 MHz" value="{{ optional($clonePack)->primary_frequency ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Make Frequency Public?</label>
                        <select name="frequency_public" class="input">
                            <option value="0" {{ empty(optional($clonePack)->frequency_public) ? 'selected' : '' }}>No</option>
                            <option value="1" {{ !empty(optional($clonePack)->frequency_public) ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Will Talk-Through Be Used?</label>
                        <select name="talkthrough_used" class="input">
                            @foreach(['Unknown','Yes','No'] as $opt)
                            <option value="{{ $opt }}" {{ (optional($clonePack)->talkthrough_used ?? 'Unknown') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Make Talk-Through Usage Public?</label>
                        <select name="talkthrough_public" class="input">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">User Services</label>
                    <div class="pill-group">
                        @foreach(['None/Internal','Ambulance','British Red Cross','Emergency Planning Unit','Fire','Health authority','HM Coastguard','Other','Police','Royal Voluntary Service','Salvation Army','St Andrew Ambulance','St John Ambulance','Utility services'] as $svc)
                        <label class="pill"><input type="checkbox" name="services[]" value="{{ $svc }}">{{ $svc }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 3: External Assistance --}}
        <div class="esp-page" data-page="3">
            <div class="esp-page-title">Do we need help from other groups?</div>
            <div class="esp-page-sub">Complete this section if Liverpool needs external assistance from other RAYNET groups.</div>
            <div class="esp-card">
                <div class="field">
                    <label class="label">Make Help Request Visible Publicly?</label>
                    <div class="pill-group">
                        @foreach(['No','Yes'] as $opt)
                        <label class="pill" data-radio="assistance_visible"><input type="radio" name="assistance_visible" value="{{ $opt === 'Yes' ? '1' : '0' }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Contact Name for Helpers</label>
                        <input type="text" name="assistance_contact" class="input" value="{{ optional($clonePack)->assistance_contact ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Contact Phone / Email</label>
                        <input type="text" name="assistance_phone_email" class="input" value="{{ optional($clonePack)->assistance_phone_email ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Type of Duty</label>
                        <input type="text" name="duty_type" class="input" placeholder="e.g. Checkpoint, mobile, control" value="{{ optional($clonePack)->duty_type ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Rough Number of Outstations</label>
                        <input type="text" name="outstations" class="input" placeholder="e.g. 6–8" value="{{ optional($clonePack)->outstations ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Skill Level Required</label>
                        <select name="skill_level" class="input">
                            <option value="">Select...</option>
                            @foreach(['Foundation/Basic','Experienced','Controller','Technical'] as $opt)
                            <option value="{{ $opt }}" {{ (optional($clonePack)->skill_level ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Expected Message Traffic Level</label>
                        <select name="traffic_level" class="input">
                            <option value="">Select...</option>
                            @foreach(['Low','Medium','High'] as $opt)
                            <option value="{{ $opt }}" {{ (optional($clonePack)->traffic_level ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">What Operators Will Be Doing</label>
                    <textarea name="duty_description" class="input" placeholder="Clear description of the duty...">{{ optional($clonePack)->duty_description ?? '' }}</textarea>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Data Comms Requirement</label>
                        <input type="text" name="data_comms" class="input" placeholder="e.g. None, APRS, LoRa" value="{{ optional($clonePack)->data_comms ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Equipment and Power Required</label>
                        <input type="text" name="equipment_power" class="input" placeholder="e.g. Handheld, mobile, battery" value="{{ optional($clonePack)->equipment_power ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Operating Environment</label>
                        <input type="text" name="operating_environment" class="input" placeholder="e.g. Outdoor, exposed, roadside" value="{{ optional($clonePack)->operating_environment ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Food / Refreshments</label>
                        <input type="text" name="welfare_food" class="input" placeholder="e.g. Provided, bring own" value="{{ optional($clonePack)->welfare_food ?? '' }}">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Other Welfare Arrangements</label>
                    <textarea name="welfare_other" class="input" placeholder="Shelter, toilets, parking, check-ins...">{{ optional($clonePack)->welfare_other ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- STEP 4: RAYNET Role --}}
        <div class="esp-page" data-page="4">
            <div class="esp-page-title">What is RAYNET being asked to do?</div>
            <div class="esp-page-sub">Select the communications support being requested. The system will flag anything outside normal RAYNET scope.</div>
            <div class="esp-card">
                <div class="field">
                    <label class="label">RAYNET Roles</label>
                    <div class="pill-group">
                        @foreach(['Control station','Checkpoint operators','Mobile operators','Sweep support','Repeater support','Link to organiser','Link to first aid','Link to event safety team','Rural communications','Message handling','Data communications','APRS/tracking','LoRa/telemetry','Other'] as $opt)
                        <label class="pill"><input type="checkbox" name="raynet_roles[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="esp-card">
                <div class="esp-card-title">⚠ Scope Check — These questions detect out-of-scope tasks</div>
                @foreach([
                    ['scope_traffic','Will RAYNET be asked to direct traffic?'],
                    ['scope_marshalling','Will RAYNET be asked to marshal crowds?'],
                    ['scope_children','Will RAYNET be asked to supervise children?'],
                    ['scope_first_aid','Will RAYNET be asked to provide first aid?'],
                    ['scope_transport','Will RAYNET be asked to transport non-RAYNET personnel?'],
                    ['scope_casualties','Will RAYNET be asked to physically manage casualties?'],
                ] as [$name,$question])
                <div class="field">
                    <label class="label">{{ $question }}</label>
                    <div class="pill-group">
                        @foreach(['No','Possibly','Yes'] as $opt)
                        <label class="pill" data-radio="{{ $name }}"><input type="radio" name="{{ $name }}" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                    <div class="scope-warning" id="warn_{{ $name }}">⚠ RAYNET's normal role is communications support. This task needs review before acceptance.</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- STEP 5: Operators and Posts --}}
        <div class="esp-page" data-page="5">
            <div class="esp-page-title">Operators and Posts</div>
            <div class="esp-page-sub">Define the checkpoint and post structure. You can add operators after saving.</div>
            <div class="esp-card">
                <div class="esp-card-title">Deployment Scale</div>
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Expected Attendance</label>
                        <select name="expected_attendance" class="input">
                            <option value="">Select...</option>
                            @foreach(['Under 100','100–500','500–2000','Over 2000'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Number of Operators Needed</label>
                        <select name="operator_count" class="input">
                            <option value="">Select...</option>
                            @foreach(['1–3','4–8','9–15','15+'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Operator Roles</label>
                    <div class="pill-group">
                        @foreach(['Control','Mobile operators','Fixed checkpoints','Sweeps','Technical support','Vehicle support'] as $opt)
                        <label class="pill"><input type="checkbox" name="roles[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="esp-card">
                <div class="esp-card-title">Posts / Checkpoints <span style="font-size:.75rem;color:var(--muted);font-weight:normal;">(You can add detailed posts after saving)</span></div>
                <div id="postsList"></div>
                <button type="button" onclick="addPostRow()" style="background:#e8eef5;color:var(--navy);border:none;padding:.45rem 1rem;border-radius:999px;font-size:.82rem;font-weight:bold;cursor:pointer;margin-top:.5rem;">+ Add Post</button>
            </div>
        </div>

        {{-- STEP 6: Communications Plan --}}
        <div class="esp-page" data-page="6">
            <div class="esp-page-title">Communications Plan</div>
            <div class="esp-page-sub">Define frequencies, controller details and fallback procedures.</div>
            <div class="esp-card">
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Secondary / Fallback Frequency</label>
                        <input type="text" name="secondary_frequency" class="input" placeholder="e.g. 433.500 MHz" value="{{ optional($clonePack)->secondary_frequency ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Repeater Details</label>
                        <input type="text" name="repeater_details" class="input" placeholder="e.g. GB3MP 145.6875" value="{{ optional($clonePack)->repeater_details ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Control Callsign</label>
                        <input type="text" name="control_callsign" class="input" placeholder="e.g. LIVERPOOL CONTROL" value="{{ optional($clonePack)->control_callsign ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Event Controller</label>
                        <input type="text" name="event_controller" class="input" placeholder="Name or callsign" value="{{ optional($clonePack)->event_controller ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Deputy Controller</label>
                        <input type="text" name="deputy_controller" class="input" value="{{ optional($clonePack)->deputy_controller ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Net Control Location</label>
                        <input type="text" name="net_control_location" class="input" value="{{ optional($clonePack)->net_control_location ?? '' }}">
                    </div>
                    <div class="field">
                        <label class="label">Routine Call-Round Interval</label>
                        <select name="call_round_interval" class="input">
                            <option value="">Select...</option>
                            @foreach(['Every 15 minutes','Every 30 minutes','Every hour','As required'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Fallback Communications Methods</label>
                    <textarea name="fallback_methods" class="input" placeholder="e.g. Mobile phone, manual relay, secondary channel">{{ optional($clonePack)->fallback_methods ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- STEP 7: Access and Movement --}}
        <div class="esp-page" data-page="7">
            <div class="esp-page-title">Access and Movement</div>
            <div class="esp-page-sub">Helps detect access, terrain, road and vehicle hazards.</div>
            <div class="esp-card">
                <div class="field">
                    <label class="label">Terrain</label>
                    <div class="pill-group">
                        @foreach(['Urban','Rural','Estate road','Off-road track','Hill/exposed ground','Woodland','Water nearby','Roadside','Remote','Mixed'] as $opt)
                        <label class="pill"><input type="checkbox" name="terrain[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label class="label">Access Conditions</label>
                    <div class="pill-group">
                        @foreach(['Normal road','4x4 preferred','Unsuitable tracks','Controlled parking','One-way system','Vehicle pass required','Grid refs needed','Restricted access','Public road crossing','Active traffic nearby'] as $opt)
                        <label class="pill"><input type="checkbox" name="access_conditions[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="grid-3">
                    <div class="field">
                        <label class="label">Operator Movement</label>
                        <div class="pill-group">
                            @foreach(['Static','Walking','Vehicle'] as $opt)
                            <label class="pill" data-radio="operator_movement"><input type="radio" name="operator_movement" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Road Exposure</label>
                        <div class="pill-group">
                            @foreach(['None','Adjacent','Active crossings'] as $opt)
                            <label class="pill" data-radio="road_exposure"><input type="radio" name="road_exposure" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Vehicles Operating on Site?</label>
                        <div class="pill-group">
                            @foreach(['No','Yes'] as $opt)
                            <label class="pill" data-radio="vehicles_operating"><input type="radio" name="vehicles_operating" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 8: Equipment --}}
        <div class="esp-page" data-page="8">
            <div class="esp-page-title">Equipment and Technical Setup</div>
            <div class="esp-page-sub">Technical hazards are generated automatically from your selections.</div>
            <div class="esp-card">
                <div class="field">
                    <label class="label">Equipment Being Used</label>
                    <div class="pill-group">
                        @foreach(['Handheld radios','Mobile radios','Temporary mast','Antenna guys/ground anchors','Feeder cables','Generator','Battery system','Mains power','Vehicle antenna','Repeater','Laptop/tablet logging','Public-facing control area','Lighting','Shelter/gazebo','Cabling','Other'] as $opt)
                        <label class="pill"><input type="checkbox" name="equipment[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label class="label">Primary Power Source</label>
                    <div class="pill-group">
                        @foreach(['Battery','Mains','Generator','Multiple'] as $opt)
                        <label class="pill" data-radio="power_source"><input type="radio" name="power_source" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label class="label">Infrastructure</label>
                    <div class="pill-group">
                        @foreach(['Repeater','Temporary mast','Generator','Temporary power'] as $opt)
                        <label class="pill"><input type="checkbox" name="infrastructure[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 9: Welfare --}}
        <div class="esp-page" data-page="9">
            <div class="esp-page-title">Welfare and Operating Conditions</div>
            <div class="esp-page-sub">Welfare risks are generated automatically. Answer honestly.</div>
            <div class="esp-card">
                <div class="field">
                    <label class="label">Deployment Duration</label>
                    <div class="pill-group">
                        @foreach(['Under 4 hours','4–8 hours','8–12 hours','Over 12 hours'] as $opt)
                        <label class="pill" data-radio="deployment_duration"><input type="radio" name="deployment_duration" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label class="label">Facilities Available</label>
                    <div class="pill-group">
                        @foreach(['Toilets','Water','Shelter','Food','No facilities','Unknown'] as $opt)
                        <label class="pill"><input type="checkbox" name="facilities[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label class="label">Welfare Risks</label>
                    <div class="pill-group">
                        @foreach(['Lone working','Remote post','Night operation','Hot weather','Cold/wet/windy weather','Public interaction','Aggressive behaviour','Animals','Horses','Children present','Vulnerable persons'] as $opt)
                        <label class="pill"><input type="checkbox" name="welfare_risks[]" value="{{ $opt }}">{{ $opt }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="grid-3">
                    <div class="field">
                        <label class="label">Lone Working</label>
                        <div class="pill-group">
                            @foreach(['No','Possible','Expected'] as $opt)
                            <label class="pill" data-radio="lone_working"><input type="radio" name="lone_working" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Night Operation</label>
                        <div class="pill-group">
                            @foreach(['No','Yes'] as $opt)
                            <label class="pill" data-radio="night_operation"><input type="radio" name="night_operation" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Under-18 Participants</label>
                        <div class="pill-group">
                            @foreach(['No','Yes'] as $opt)
                            <label class="pill" data-radio="under_18"><input type="radio" name="under_18" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label class="label">Weather Exposure</label>
                        <div class="pill-group">
                            @foreach(['Low','Moderate','High'] as $opt)
                            <label class="pill" data-radio="weather_exposure"><input type="radio" name="weather_exposure" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Weather Contingency</label>
                        <div class="pill-group">
                            @foreach(['Green','Amber','Red'] as $opt)
                            <label class="pill" data-radio="weather_contingency"><input type="radio" name="weather_contingency" value="{{ $opt }}">{{ $opt }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Additional Notes</label>
                    <textarea name="notes" class="input" placeholder="Any other relevant operational information..."></textarea>
                </div>
            </div>
        </div>

        {{-- STEP 10: Review --}}
        <div class="esp-page" data-page="10">
            <div class="esp-page-title">Risk Review</div>
            <div class="esp-page-sub">Generated from your answers. Review before proceeding to approval.</div>
            <div id="reviewContent">
                <div style="text-align:center;padding:3rem;color:var(--muted);">⚡ Generating risk assessment...</div>
            </div>
        </div>

        {{-- STEP 11: Approval --}}
        <div class="esp-page" data-page="11">
            <div class="esp-page-title">Approval</div>
            <div class="esp-page-sub">Submit for review, or approve if you have authority.</div>
            <div id="approvalContent">
                <div class="esp-card">
                    <div id="ragSummaryApproval" style="margin-bottom:1rem;"></div>
                    <div id="redWarning" style="display:none;background:#fee2e2;border-left:4px solid #dc2626;padding:1rem;border-radius:0 6px 6px 0;margin-bottom:1rem;font-size:.88rem;color:#991b1b;">
                        <strong>⚠ This assessment contains High residual risks.</strong> It requires Group Controller review before approval. You may generate a Draft PDF for review purposes.
                    </div>
                    <div id="amberConfirm" style="display:none;background:#fffbeb;border-left:4px solid #f59e0b;padding:1rem;border-radius:0 6px 6px 0;margin-bottom:1rem;font-size:.88rem;">
                        <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;">
                            <input type="checkbox" name="confirm_amber" id="confirmAmberCheck" style="margin-top:3px;width:16px;height:16px;accent-color:#f59e0b;">
                            <span style="color:#92400e;">I confirm that the amber controls have been reviewed and will be included in the event briefing.</span>
                        </label>
                    </div>
                    <div class="field">
                        <label class="label">Approval Statement (optional)</label>
                        <textarea name="approval_statement" class="input" placeholder="I confirm that this event plan has been reviewed and is suitable for Liverpool RAYNET use, subject to the controls listed."></textarea>
                    </div>
                    <div class="field">
                        <label class="label">Comments</label>
                        <textarea name="comments" class="input" placeholder="Any comments or conditions..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 12: Generate --}}
        <div class="esp-page" data-page="12">
            <div class="esp-page-title">Generate Event Pack</div>
            <div class="esp-page-sub">Your event support pack has been saved. Download the documents below.</div>
            <div id="generateContent">
                <div class="esp-card">
                    <div id="packSummaryGenerate" style="margin-bottom:1.5rem;"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;" id="documentButtons"></div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="btn-row">
            <button type="button" class="btn btn-secondary" id="espBtnBack" style="display:none;" onclick="espNav(-1)">← Back</button>
            <button type="button" class="btn btn-primary" id="espBtnNext" onclick="espNav(1)">Next →</button>
            <button type="button" class="btn btn-primary" id="espBtnReview" style="display:none;" onclick="buildReview()">⚡ Generate Risk Assessment</button>
            <button type="button" class="btn btn-green" id="espBtnSubmit" style="display:none;" onclick="submitPack()">✓ Save &amp; Submit for Review</button>
            <span class="autosave-indicator" id="autosaveLabel"></span>
        </div>
    </form>
</div>

<script>
var espPage = 1;
var espTotal = 12;
var espPackId = null;
var espRag = null;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Pill toggle
document.querySelectorAll('.pill').forEach(function(pill) {
    pill.addEventListener('click', function(e) {
        e.preventDefault();
        var input = this.querySelector('input');
        if (input.type === 'radio') {
            var name = input.name;
            document.querySelectorAll('input[name="'+name+'"]').forEach(function(r) {
                r.checked = false;
                r.closest('.pill').classList.remove('selected');
            });
            input.checked = true;
            this.classList.add('selected');
            // Scope warnings
            if (name.startsWith('scope_')) {
                var warn = document.getElementById('warn_'+name);
                if (warn) warn.style.display = input.value !== 'No' ? 'block' : 'none';
            }
        } else {
            this.classList.toggle('selected');
            input.checked = this.classList.contains('selected');
        }
    });
});

function espNav(dir) {
    if (dir === 1 && espPage === 1) {
        if (!document.querySelector('[name="event_name"]').value.trim()) { alert('Please enter an event name.'); return; }
        if (!document.querySelector('[name="event_date"]').value) { alert('Please select an event date.'); return; }
        if (!document.querySelector('[name="location"]').value.trim()) { alert('Please enter a location.'); return; }
    }
    var newPage = espPage + dir;
    if (newPage < 1 || newPage > espTotal) return;
    document.querySelector('.esp-page[data-page="'+espPage+'"]').classList.remove('active');
    espPage = newPage;
    document.querySelector('.esp-page[data-page="'+espPage+'"]').classList.add('active');
    updateNav();
    // Special actions on page entry
    if (espPage === 10) buildReview();
    if (espPage === 11) buildApproval();
    if (espPage === 12) buildGenerate();
    // Update progress
    document.getElementById('progressFill').style.width = (espPage/espTotal*100)+'%';
    document.querySelectorAll('.step-labels span').forEach(function(s,i){ s.classList.toggle('active', i+1===espPage); });
    window.scrollTo({top:0,behavior:'smooth'});
}

function updateNav() {
    document.getElementById('espBtnBack').style.display = espPage > 1 ? '' : 'none';
    document.getElementById('espBtnNext').style.display = espPage < 10 ? '' : 'none';
    document.getElementById('espBtnReview').style.display = (espPage === 10 && !espPackId) ? '' : 'none';
    document.getElementById('espBtnSubmit').style.display = espPage === 11 ? '' : 'none';
    if (espPage >= 12) {
        document.getElementById('espBtnNext').style.display = 'none';
        document.getElementById('espBtnReview').style.display = 'none';
        document.getElementById('espBtnSubmit').style.display = 'none';
    }
}

function collectData() {
    var fd = new FormData(document.getElementById('espForm'));
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
    document.getElementById('reviewContent').innerHTML = '<div style="text-align:center;padding:3rem;color:var(--muted);">⚡ Saving and generating risks...</div>';
    document.getElementById('espBtnNext').style.display = 'none';
    document.getElementById('espBtnReview').style.display = 'none';

    var data = collectData();
    fetch('/event-pack', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify(data)
    }).then(function(r){ return r.json(); }).then(function(res) {
        espPackId = res.id;
        espRag    = res.rag;
        var rag   = res.rag;
        var ragColours = {green:'#059669',amber:'#f59e0b',red:'#dc2626'};
        var ragLabels  = {green:'GREEN — Suitable to proceed',amber:'AMBER — Proceed with controls',red:'RED — Do not proceed without further review'};

        var html = '<div class="rag-banner" style="background:'+ragColours[rag]+';color:#fff;">';
        html += '<div style="font-size:.85rem;text-transform:uppercase;letter-spacing:.1em;font-weight:700;opacity:.8;">Overall Event Risk Status</div>';
        html += '<div style="font-size:1.5rem;font-weight:900;margin-top:.2rem;">'+ragLabels[rag]+'</div>';
        html += '</div>';

        if (rag === 'red') {
            html += '<div style="background:#fee2e2;border-left:4px solid #dc2626;padding:1rem 1.25rem;border-radius:0 6px 6px 0;margin-bottom:1.25rem;font-size:.88rem;color:#991b1b;"><strong>⚠ This assessment contains High residual risks.</strong> Group Controller review required before approval.</div>';
        }

        html += '<table class="risk-table"><thead><tr><th>Hazard</th><th>Who at Risk</th><th>Controls</th><th>Likelihood</th><th>Severity</th><th>Residual</th></tr></thead><tbody>';
        res.risks.forEach(function(r) {
            html += '<tr><td><strong>'+r.hazard+'</strong><br><small style="color:#6b7f96;">'+r.cause+'</small></td>';
            html += '<td>'+r.persons_at_risk+'</td><td>'+r.controls+'</td><td>'+r.likelihood+'</td><td>'+r.severity+'</td>';
            html += '<td><span class="res-'+r.residual+'">'+r.residual+'</span></td></tr>';
        });
        html += '</tbody></table>';
        html += '<div style="margin-top:1.5rem;"><button type="button" class="btn btn-primary" onclick="espNav(1)">Proceed to Approval →</button></div>';
        document.getElementById('reviewContent').innerHTML = html;
        updateNav();
    }).catch(function(e) {
        document.getElementById('reviewContent').innerHTML = '<div style="color:#dc2626;padding:1rem;">Failed to generate. Please try again.</div>';
        document.getElementById('espBtnReview').style.display = '';
    });
}

function buildApproval() {
    if (!espPackId) return;
    var ragColours = {green:'#059669',amber:'#f59e0b',red:'#dc2626'};
    var ragLabels  = {green:'GREEN — Suitable to proceed',amber:'AMBER — Proceed with controls',red:'RED — Do not proceed without further review'};
    var rag = espRag || 'green';
    document.getElementById('ragSummaryApproval').innerHTML = '<div style="background:'+ragColours[rag]+';color:#fff;padding:.75rem 1.25rem;border-radius:6px;font-weight:700;font-size:1rem;">'+ragLabels[rag]+'</div>';
    document.getElementById('redWarning').style.display  = rag === 'red' ? 'block' : 'none';
    document.getElementById('amberConfirm').style.display = rag === 'amber' ? 'block' : 'none';
}

function buildGenerate() {
    if (!espPackId) return;
    var ragLabels = {green:'GREEN',amber:'AMBER',red:'RED'};
    var ragColours = {green:'#059669',amber:'#f59e0b',red:'#dc2626'};
    var rag = espRag || 'green';
    document.getElementById('packSummaryGenerate').innerHTML =
        '<div style="background:'+ragColours[rag]+';color:#fff;padding:.75rem 1.25rem;border-radius:6px;font-weight:700;margin-bottom:.5rem;">'+ragLabels[rag]+'</div>'+
        '<div style="font-size:.88rem;color:var(--muted);">Event pack #'+espPackId+' saved successfully. Download your documents:</div>';
    var btns = [
        ['risk','📋 Risk Assessment','btn-primary'],
        ['operator','👥 Operator Brief','btn-navy'],
        ['assist','🤝 Assistance Request','btn-navy'],
        ['joining','📍 Joining Instructions','btn-navy'],
    ];
    var html = '';
    btns.forEach(function(b) {
        html += '<a href="/event-pack/'+espPackId+'/pdf/'+b[0]+'" target="_blank" class="btn '+b[2]+'" style="justify-content:center;">'+b[1]+'</a>';
    });
    html += '<a href="/event-pack/'+espPackId+'" class="btn btn-secondary" style="justify-content:center;grid-column:span 2;">📋 View Full Event Pack</a>';
    document.getElementById('documentButtons').innerHTML = html;
}

function submitPack() {
    if (!espPackId) { alert('Please complete the risk review first.'); return; }
    if (espRag === 'amber') {
        var check = document.getElementById('confirmAmberCheck');
        if (check && !check.checked) { alert('Please confirm the amber controls have been reviewed.'); return; }
    }
    document.getElementById('autosaveLabel').textContent = '⏳ Submitting...';
    fetch('/event-pack/'+espPackId+'/submit', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: '{}'
    }).then(function(r){ return r.json ? r.json() : r; }).then(function() {
        document.getElementById('autosaveLabel').textContent = '✓ Submitted';
        espNav(1);
    }).catch(function(){
        // Fallback - navigate anyway
        espNav(1);
    });
}

// Post management
var postCount = 0;
function addPostRow() {
    postCount++;
    var div = document.createElement('div');
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:.5rem;margin-bottom:.5rem;align-items:end;';
    div.innerHTML =
        '<input type="text" name="post_names[]" class="input" placeholder="Post name" style="padding:.45rem .65rem;font-size:.85rem;">'+
        '<input type="text" name="post_callsigns[]" class="input" placeholder="Tactical callsign" style="padding:.45rem .65rem;font-size:.85rem;">'+
        '<input type="text" name="post_locations[]" class="input" placeholder="Location" style="padding:.45rem .65rem;font-size:.85rem;">'+
        '<button type="button" onclick="this.closest(\'div\').remove()" style="background:#fee2e2;color:#991b1b;border:none;padding:.45rem .65rem;border-radius:4px;cursor:pointer;font-size:.85rem;">✕</button>';
    document.getElementById('postsList').appendChild(div);
}
</script>
@endsection
