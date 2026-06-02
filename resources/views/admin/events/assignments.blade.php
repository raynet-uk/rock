@extends('layouts.admin')

@section('title', 'Event Team — ' . $event->title)

@section('content')

{{--
=============================================================================
MIGRATION — php artisan make:migration add_enhanced_team_fields_to_event_assignments_table

Schema::table('event_assignments', function (Blueprint $table) {
    $table->json('shifts')->nullable()->after('depart_time');          // multi-shift JSON
    $table->string('secondary_frequency', 20)->nullable();
    $table->string('secondary_mode', 10)->nullable();
    $table->string('secondary_ctcss', 10)->nullable();
    $table->string('fallback_frequency', 20)->nullable();
    $table->string('fallback_mode', 10)->nullable();
    $table->string('fallback_ctcss', 10)->nullable();
    $table->string('channel_label', 50)->nullable();
    $table->unsignedInteger('coverage_radius_m')->default(0);
    $table->json('equipment_items')->nullable();                        // replaces equipment text
    $table->text('medical_notes')->nullable();                         // private, print-suppressed
    $table->string('emergency_contact_name', 100)->nullable();
    $table->string('emergency_contact_phone', 20)->nullable();
});

EventAssignment model:
    protected $casts = ['shifts' => 'array', 'equipment_items' => 'array'];
    // Add all new fields to $fillable

Backend routes needed:
    POST   admin/events/{event}/assignments/bulk-status    → bulkStatus()
    POST   admin/events/{event}/duplicate-team            → duplicateTeam()
    PATCH  admin/assignments/{assignment}/position        → updatePosition()   ← existing
=============================================================================
--}}

@php
/* ── Normalise shifts: fall back to legacy start_time/end_time ── */
$normaliseShifts = function($a) {
    $raw = $a->shifts ?? null;
    if (is_string($raw)) $raw = json_decode($raw, true);
    if (!is_array($raw) || empty($raw)) {
        $raw = [];
        if ($a->start_time || $a->end_time) {
            $raw[] = ['type'=>'shift','label'=>'',
                'start' => $a->start_time ? substr($a->start_time,0,5) : null,
                'end'   => $a->end_time   ? substr($a->end_time,  0,5) : null];
        }
    }
    return $raw;
};

$calcHours = function($shifts) {
    $m = 0;
    foreach ($shifts as $s) {
        if (($s['type']??'shift')==='shift' && !empty($s['start']) && !empty($s['end'])) {
            $p = explode(':',$s['start']); $q = explode(':',$s['end']);
            $d = ((int)$q[0]*60+(int)$q[1]) - ((int)$p[0]*60+(int)$p[1]);
            if ($d>0) $m += $d;
        }
    }
    return $m>0 ? round($m/60,1) : null;
};

$totalTeamHours = 0;
foreach ($assignments as $a) {
    $sh = $normaliseShifts($a);
    $h  = $calcHours($sh);
    if ($h) $totalTeamHours += $h;
}
$totalTeamHours = $totalTeamHours > 0 ? round($totalTeamHours,1) : 0;

/* ── Build JS assignments data ── */
$assignmentsJs = $assignments->map(function($a) use ($normaliseShifts, $calcHours) {
    $shifts = $normaliseShifts($a);
    $equipItems = $a->equipment_items ?? null;
    if (is_string($equipItems)) $equipItems = json_decode($equipItems, true);
    if (!is_array($equipItems)) $equipItems = [];
    return [
        'id'             => $a->id,
        'name'           => $a->user->name,
        'callsign'       => $a->callsign,
        'role'           => $a->role,
        'frequency'      => $a->frequency,
        'mode'           => $a->mode,
        'ctcss_tone'     => $a->ctcss_tone,
        'channel_label'  => $a->channel_label ?? null,
        'secondary_frequency' => $a->secondary_frequency ?? null,
        'secondary_mode'      => $a->secondary_mode ?? null,
        'secondary_ctcss'     => $a->secondary_ctcss ?? null,
        'fallback_frequency'  => $a->fallback_frequency ?? null,
        'fallback_mode'       => $a->fallback_mode ?? null,
        'fallback_ctcss'      => $a->fallback_ctcss ?? null,
        'location_name'  => $a->location_name,
        'grid_ref'       => $a->grid_ref,
        'what3words'     => $a->what3words,
        'lat'            => $a->lat ? (float)$a->lat : null,
        'lng'            => $a->lng ? (float)$a->lng : null,
        'coverage_radius_m' => (int)($a->coverage_radius_m ?? 0),
        'report_time'    => $a->report_time ? substr($a->report_time,0,5) : null,
        'depart_time'    => $a->depart_time ? substr($a->depart_time,0,5) : null,
        'shifts'         => $shifts,
        'total_hours'    => $calcHours($shifts),
        'status'         => $a->status,
        'status_label'   => $a->statusLabel(),
        'marker_colour'  => $a->markerColour(),
        'has_vehicle'    => (bool)$a->has_vehicle,
        'vehicle_reg'    => $a->vehicle_reg,
        'first_aid_trained' => (bool)$a->first_aid_trained,
        'equipment_items' => $equipItems,
        'equipment'       => $a->equipment,
        'briefing_notes'  => $a->briefing_notes,
        'medical_notes'   => $a->medical_notes ?? null,
        'emergency_contact_name'  => $a->emergency_contact_name ?? null,
        'emergency_contact_phone' => $a->emergency_contact_phone ?? null,
        'briefing_sent'   => (bool)$a->briefing_sent,
    ];
})->values();

/* ── Centroid for map ── */
$mappedOps = $assignments->whereNotNull('lat')->whereNotNull('lng');
$centLat = $mappedOps->isNotEmpty() ? $mappedOps->avg('lat') : 53.4084;
$centLng = $mappedOps->isNotEmpty() ? $mappedOps->avg('lng') : -2.9916;
@endphp

<style>
:root {
    --navy:       #003366; --navy-mid:  #004080; --navy-faint: #e8eef5; --navy-deep: #001f40;
    --red:        #C8102E; --red-faint: #fdf0f2;
    --white:      #FFFFFF;
    --grey:       #F2F2F2; --grey-mid:  #dde2e8; --grey-dark: #9aa3ae;
    --text:       #001f40; --text-mid:  #2d4a6b; --text-muted: #6b7f96;
    --green:      #1a6b3c; --green-bg:  #eef7f2; --green-bdr: #b8ddc9;
    --amber:      #8a5c00; --amber-bg:  #fef9ec; --amber-bdr: #e8c96a;
    --font: Arial,'Helvetica Neue',Helvetica,sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.12);
    --shadow-lg: 0 8px 28px rgba(0,51,102,.16);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--grey);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;}

/* ─ HEADER ─ */
.rn-header{background:var(--navy);border-bottom:3px solid var(--red);position:sticky;top:0;z-index:200;box-shadow:0 2px 12px rgba(0,0,0,.35);}
.rn-header-inner{max-width:1440px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.rn-brand{display:flex;align-items:center;gap:.85rem;padding:.7rem 0;}
.rn-logo{background:var(--red);width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rn-logo span{font-size:9px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.2;text-transform:uppercase;}
.rn-org{font-size:13px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.rn-sub{font-size:10px;color:rgba(255,255,255,.5);margin-top:1px;text-transform:uppercase;letter-spacing:.04em;}
.rn-nav{display:flex;align-items:center;gap:.5rem;}
.rn-back{font-size:11px;font-weight:bold;color:rgba(255,255,255,.8);text-decoration:none;border:1px solid rgba(255,255,255,.25);padding:.3rem .85rem;transition:all .15s;white-space:nowrap;}
.rn-back:hover{background:rgba(255,255,255,.1);color:#fff;}
.rn-logout{font-size:11px;font-weight:bold;color:rgba(255,255,255,.7);background:transparent;border:1px solid rgba(200,16,46,.5);padding:.3rem .85rem;cursor:pointer;transition:all .15s;font-family:var(--font);white-space:nowrap;}
.rn-logout:hover{background:rgba(200,16,46,.15);color:#fff;border-color:var(--red);}

/* ─ HERO ─ */
.event-hero{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 60%,var(--navy-mid) 100%);border-bottom:3px solid var(--red);padding:1.4rem 0;}
.event-hero-inner{max-width:1440px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;}
.event-eyebrow{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:var(--red);margin-bottom:.4rem;display:flex;align-items:center;gap:.4rem;}
.event-eyebrow::before{content:'';width:10px;height:2px;background:var(--red);display:inline-block;}
.event-title{font-size:20px;font-weight:bold;color:#fff;line-height:1.2;margin-bottom:.5rem;}
.event-meta{display:flex;flex-wrap:wrap;gap:.65rem;}
.event-meta-chip{display:inline-flex;align-items:center;gap:.35rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:.28rem .75rem;font-size:11px;color:rgba(255,255,255,.85);font-weight:bold;}
.event-stats{display:flex;gap:.5rem;flex-wrap:wrap;}
.stat-card{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:.5rem .8rem;text-align:center;min-width:58px;}
.stat-val{font-size:18px;font-weight:bold;color:#fff;line-height:1;}
.stat-val.green{color:#6fdb9e;}.stat-val.amber{color:#ffd166;}.stat-val.red{color:#ff8fa3;}.stat-val.blue{color:#7ec8f5;}
.stat-lbl{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);margin-top:2px;}

/* ─ TABS ─ */
.tab-bar{background:var(--white);border-bottom:2px solid var(--grey-mid);box-shadow:var(--shadow-sm);position:sticky;top:58px;z-index:150;}
.tab-bar-inner{max-width:1440px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;gap:0;overflow-x:auto;}
.tab-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.75rem 1.1rem;font-family:var(--font);font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:none;border:none;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s;white-space:nowrap;}
.tab-btn:hover{color:var(--navy);}
.tab-btn.active{color:var(--navy);border-bottom-color:var(--red);}
.tab-icon{font-size:13px;}
.wrap{max-width:1440px;margin:0 auto;padding:1.5rem 1.5rem 5rem;}
.tab-pane{display:none;}.tab-pane.active{display:block;}

/* ─ ALERTS ─ */
.alert{display:flex;align-items:center;gap:.6rem;margin-bottom:1.25rem;padding:.65rem 1rem;font-size:13px;font-weight:bold;border-left:3px solid;}
.alert-success{background:var(--green-bg);border-color:var(--green);color:var(--green);}
.alert-error{background:var(--red-faint);border-color:var(--red);color:var(--red);}

/* ─ BUTTONS ─ */
.btn{display:inline-flex;align-items:center;gap:.35rem;padding:.45rem 1.1rem;border:1px solid;font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;transition:all .12s;white-space:nowrap;text-transform:uppercase;letter-spacing:.06em;text-decoration:none;background:none;}
.btn-primary{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-primary:hover{background:var(--navy-mid);}
.btn-green{background:var(--green-bg);border-color:var(--green-bdr);color:var(--green);}
.btn-green:hover{background:#d5ece0;border-color:var(--green);}
.btn-red{background:var(--red-faint);border-color:rgba(200,16,46,.35);color:var(--red);}
.btn-red:hover{background:#fce0e4;border-color:var(--red);}
.btn-amber{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}
.btn-amber:hover{background:#fdeec5;border-color:#c8a030;}
.btn-ghost{background:var(--white);border-color:var(--grey-mid);color:var(--text-muted);}
.btn-ghost:hover{border-color:var(--navy);color:var(--navy);}
.action-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;}

/* ─ PANELS ─ */
.panel{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:var(--shadow-sm);margin-bottom:1.25rem;}
.panel-head{padding:.75rem 1.1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;justify-content:space-between;}
.panel-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);}
.panel-sub{font-size:10px;color:var(--text-muted);margin-top:1px;}
.section-divider{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.18em;color:var(--text-muted);margin:1rem 0 .7rem;display:flex;align-items:center;gap:.5rem;}
.section-divider::after{content:'';flex:1;height:1px;background:var(--grey-mid);}
.empty-state{padding:3rem 1rem;text-align:center;}
.empty-icon{font-size:2.5rem;opacity:.15;margin-bottom:.75rem;}
.empty-text{font-size:13px;color:var(--text-muted);}

/* ─ BULK ACTION BAR ─ */
.bulk-bar{
    position:fixed;bottom:0;left:0;right:0;z-index:400;
    background:var(--navy);border-top:3px solid var(--red);
    padding:.75rem 1.5rem;
    display:none;align-items:center;gap:1rem;flex-wrap:wrap;
    box-shadow:0 -4px 20px rgba(0,0,0,.3);
}
.bulk-bar.visible{display:flex;}
.bulk-bar-label{font-size:12px;font-weight:bold;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;}
.bulk-select-btn{
    display:inline-flex;align-items:center;gap:.3rem;
    padding:.32rem .75rem;border:1px solid rgba(255,255,255,.3);
    font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;
    color:rgba(255,255,255,.8);background:transparent;cursor:pointer;
    font-family:var(--font);transition:all .12s;white-space:nowrap;
}
.bulk-select-btn:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.6);color:#fff;}
.bulk-select-btn.active{background:rgba(255,255,255,.15);border-color:#fff;color:#fff;}
.bulk-status-select{
    padding:.32rem .75rem;border:1px solid rgba(255,255,255,.3);
    background:rgba(255,255,255,.1);color:#fff;font-family:var(--font);
    font-size:11px;font-weight:bold;outline:none;cursor:pointer;
}
.bulk-status-select option{background:var(--navy);color:#fff;}

/* ─ ASSIGNMENT CARDS ─ */
.team-select-row{display:flex;align-items:center;gap:.75rem;}
.team-checkbox{width:16px;height:16px;cursor:pointer;flex-shrink:0;accent-color:var(--navy);}
.assignment-card{background:var(--white);border:1px solid var(--grey-mid);margin-bottom:.65rem;transition:box-shadow .15s;border-left:4px solid var(--grey-mid);}
.assignment-card.confirmed{border-left-color:var(--green);}
.assignment-card.standby{border-left-color:var(--amber-bdr);}
.assignment-card.declined{border-left-color:var(--red);opacity:.75;}
.assignment-card.selected{outline:2px solid var(--navy);outline-offset:1px;}
.assignment-card:hover{box-shadow:var(--shadow-md);}
.ac-head{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;cursor:pointer;user-select:none;}
.ac-avatar{width:36px;height:36px;background:var(--navy-faint);border:2px solid var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:bold;color:var(--navy);}
.ac-info{flex:1;min-width:0;}
.ac-name{font-size:13px;font-weight:bold;color:var(--text);}
.ac-role{font-size:11px;color:var(--text-muted);margin-top:1px;}
.ac-chips{display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;margin-top:.3rem;}
.ac-chip{font-size:10px;font-weight:bold;padding:1px 7px;border:1px solid;text-transform:uppercase;letter-spacing:.04em;}
.chip-navy{background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);}
.chip-green{background:var(--green-bg);border-color:var(--green-bdr);color:var(--green);}
.chip-amber{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}
.chip-red{background:var(--red-faint);border-color:rgba(200,16,46,.3);color:var(--red);}
.chip-grey{background:var(--grey);border-color:var(--grey-mid);color:var(--grey-dark);}
.ac-toggle{font-size:18px;color:var(--grey-dark);flex-shrink:0;transition:transform .2s;}
.ac-body{display:none;padding:0 1rem 1rem;border-top:1px solid var(--grey-mid);}
.ac-body.open{display:block;}
.ac-toggle.open{transform:rotate(180deg);}
.ac-details{display:grid;grid-template-columns:1fr 1fr;gap:.6rem 1.2rem;margin:.75rem 0;}
.ac-detail-item{display:flex;flex-direction:column;gap:2px;}
.ac-detail-label{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);}
.ac-detail-val{font-size:12px;color:var(--text);font-weight:bold;}
.ac-detail-val.muted{color:var(--grey-dark);font-weight:normal;font-style:italic;}
.ac-shifts-block{margin:.5rem 0;display:flex;flex-wrap:wrap;gap:.35rem;}
.shift-pill{display:inline-flex;align-items:center;gap:.3rem;padding:2px 8px;font-size:11px;font-weight:bold;border:1px solid;white-space:nowrap;}
.shift-pill.work{background:var(--navy-faint);border-color:rgba(0,51,102,.25);color:var(--navy);}
.shift-pill.brk{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}
.ac-hours-badge{font-size:11px;font-weight:bold;background:var(--green-bg);border:1px solid var(--green-bdr);color:var(--green);padding:1px 8px;margin-left:.5rem;}
.ac-actions{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--grey-mid);}

/* ─ CHANNEL PLAN TABLE ─ */
.freq-table{width:100%;border-collapse:collapse;font-size:12px;margin:.5rem 0;}
.freq-table th,.freq-table td{padding:.35rem .6rem;border:1px solid var(--grey-mid);text-align:left;}
.freq-table th{background:var(--navy-faint);font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);}
.freq-table tr:nth-child(even) td{background:#fafbfc;}
.freq-primary td:first-child::before{content:'★ ';color:var(--amber);}

/* ─ COVERAGE WARNING ─ */
.coverage-warning{display:flex;align-items:center;gap:.6rem;padding:.55rem .9rem;font-size:12px;font-weight:bold;margin-bottom:.6rem;border-left:3px solid;}
.coverage-warning.gap{background:var(--red-faint);border-color:var(--red);color:var(--red);}
.coverage-warning.overlap{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}
.coverage-warning.ok{background:var(--green-bg);border-color:var(--green);color:var(--green);}

/* ─ TIMELINE ─ */
.schedule-grid{overflow-x:auto;}
.timeline{position:relative;min-width:700px;background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:var(--shadow-sm);padding:1.25rem 1.5rem;}
.timeline-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);margin-bottom:1.25rem;}
.timeline-hours{display:flex;border-bottom:2px solid var(--grey-mid);}
.timeline-hour{flex:1;font-size:10px;font-weight:bold;color:var(--text-muted);text-align:center;padding-bottom:.35rem;border-left:1px dashed var(--grey-mid);min-width:50px;}
.timeline-hour:first-child{border-left:none;}
.timeline-row{display:flex;align-items:center;border-bottom:1px solid var(--grey-mid);min-height:46px;}
.timeline-row:last-child{border-bottom:none;}
.tl-name{width:155px;flex-shrink:0;padding:.4rem .6rem;border-right:2px solid var(--grey-mid);}
.tl-name-val{font-size:12px;font-weight:bold;color:var(--text);}
.tl-name-role{font-size:10px;color:var(--text-muted);}
.tl-name-hours{font-size:10px;font-weight:bold;color:var(--green);margin-top:1px;}
.tl-track{flex:1;position:relative;height:46px;}
.tl-bar{position:absolute;top:7px;height:22px;display:flex;align-items:center;padding:0 .45rem;font-size:10px;font-weight:bold;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.tl-break{position:absolute;top:7px;height:22px;background:repeating-linear-gradient(45deg,transparent,transparent 3px,rgba(0,0,0,.08) 3px,rgba(0,0,0,.08) 6px);}
.tl-report{position:absolute;top:0;bottom:0;width:2px;background:var(--amber-bdr);}
.tl-depart{position:absolute;top:0;bottom:0;width:2px;background:var(--red);opacity:.5;}
.tl-marker-label{position:absolute;top:1px;left:3px;font-size:9px;font-weight:bold;white-space:nowrap;}
/* Coverage heat row */
.tl-coverage-row{display:flex;align-items:stretch;height:18px;margin-top:.5rem;}
.tl-coverage-name{width:155px;flex-shrink:0;padding:.1rem .6rem;border-right:2px solid var(--grey-mid);font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);display:flex;align-items:center;}
.tl-coverage-track{flex:1;display:flex;}
.tl-cov-cell{flex:1;border-right:1px solid rgba(0,0,0,.06);}
.no-schedule{text-align:center;padding:2.5rem 1rem;color:var(--text-muted);font-size:13px;font-style:italic;}

/* ─ MAP ─ */
.map-layout{display:grid;grid-template-columns:1fr 280px;gap:1.25rem;align-items:start;}
@media(max-width:900px){.map-layout{grid-template-columns:1fr;}}
#map-container{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:var(--shadow-sm);overflow:hidden;}
#map-toolbar{padding:.6rem .9rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
#leaflet-map{height:560px;width:100%;}
.rn-popup .leaflet-popup-content-wrapper{border-radius:0!important;border:1px solid var(--grey-mid)!important;box-shadow:var(--shadow-md)!important;padding:0!important;font-family:var(--font)!important;}
.rn-popup .leaflet-popup-content{margin:0!important;width:230px!important;}
.rn-popup .leaflet-popup-tip-container{display:none;}
.popup-inner{padding:.75rem 1rem;}
.popup-name{font-size:13px;font-weight:bold;color:var(--navy);margin-bottom:3px;}
.popup-role{font-size:11px;color:var(--text-muted);margin-bottom:.45rem;}
.popup-row{font-size:11px;color:var(--text);display:flex;gap:.4rem;margin-bottom:2px;}
.popup-lbl{font-weight:bold;color:var(--text-muted);min-width:60px;}
.popup-shifts{font-size:11px;color:var(--text);margin-top:.4rem;}
.popup-footer{padding:.4rem .75rem;background:var(--navy);border-top:1px solid var(--grey-mid);font-size:10px;color:rgba(255,255,255,.6);font-weight:bold;text-transform:uppercase;letter-spacing:.08em;}
.popup-actions{padding:.45rem .75rem;border-top:1px solid var(--grey-mid);display:flex;gap:.4rem;}
.popup-act{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:.2rem .55rem;border:1px solid;cursor:pointer;font-family:var(--font);background:none;}
.popup-act-circle{color:var(--navy);border-color:rgba(0,51,102,.3);}
.popup-act-circle:hover,.popup-act-circle.active{background:var(--navy-faint);border-color:var(--navy);}
.legend-panel{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:var(--shadow-sm);margin-bottom:1rem;}
.legend-head{padding:.6rem .9rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);}
.legend-title{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--navy);}
.legend-body{padding:.75rem .9rem;}
.legend-item{display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;font-size:11px;color:var(--text);}
.legend-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0;}
.op-list-item{display:flex;align-items:center;gap:.6rem;padding:.5rem .9rem;border-bottom:1px solid var(--grey-mid);cursor:pointer;transition:background .1s;}
.op-list-item:hover{background:var(--navy-faint);}
.op-list-item:last-child{border-bottom:none;}
.op-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;border:1px solid rgba(0,0,0,.15);}
.op-info{flex:1;min-width:0;}
.op-name{font-size:12px;font-weight:bold;color:var(--text);}
.op-sub{font-size:10px;color:var(--text-muted);}
.op-locate{font-size:10px;font-weight:bold;color:var(--navy);opacity:0;transition:opacity .15s;text-transform:uppercase;}
.op-list-item:hover .op-locate{opacity:1;}
.gpx-upload-area{display:flex;align-items:center;gap:.6rem;padding:.5rem .9rem;background:var(--navy-faint);border:1px dashed rgba(0,51,102,.25);border-bottom:1px solid var(--grey-mid);}
.gpx-lbl{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);}
#gpx-status{font-size:11px;color:var(--green);font-weight:bold;}

/* ─ FORM FIELDS (modal) ─ */
.frow{display:grid;gap:.7rem;grid-template-columns:1fr 1fr;margin-bottom:.7rem;}
.frow.three{grid-template-columns:1fr 1fr 1fr;}
.frow.one{grid-template-columns:1fr;}
.ff{display:flex;flex-direction:column;gap:.25rem;}
.ff label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);}
.ff input,.ff select,.ff textarea{background:var(--white);border:1px solid var(--grey-mid);padding:.42rem .7rem;color:var(--text);font-family:var(--font);font-size:13px;width:100%;outline:none;transition:border-color .15s,box-shadow .15s;}
.ff input:focus,.ff select:focus,.ff textarea:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,51,102,.08);}
.ff input::placeholder,.ff textarea::placeholder{color:var(--grey-dark);}
.ff textarea{resize:vertical;min-height:56px;}
.ff-check{flex-direction:row;align-items:center;gap:.5rem;padding-top:.4rem;}
.ff-check input{width:auto;}
.ff-check label{font-size:12px;text-transform:none;letter-spacing:0;color:var(--text);margin:0;}

/* ─ SHIFT BUILDER ─ */
.shift-builder{border:1px solid var(--grey-mid);background:#fafbfc;margin-bottom:.7rem;}
.shift-builder-head{padding:.55rem .85rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;}
.shift-builder-title{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);}
.preset-chips{display:flex;gap:.3rem;flex-wrap:wrap;}
.preset-chip{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:.2rem .6rem;border:1px solid rgba(0,51,102,.25);background:var(--navy-faint);color:var(--navy);cursor:pointer;font-family:var(--font);transition:all .12s;}
.preset-chip:hover{background:var(--navy);color:#fff;border-color:var(--navy);}
.shift-rows{padding:.65rem .85rem;}
.shift-row{display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;padding:.5rem .6rem;border:1px solid var(--grey-mid);background:var(--white);}
.shift-row.break-row{background:var(--amber-bg);border-color:var(--amber-bdr);}
.shift-type-badge{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border:1px solid;white-space:nowrap;flex-shrink:0;}
.shift-type-badge.work{background:var(--navy-faint);border-color:rgba(0,51,102,.25);color:var(--navy);}
.shift-type-badge.brk{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}
.shift-time-input{border:1px solid var(--grey-mid);padding:.28rem .45rem;font-family:var(--font);font-size:12px;color:var(--text);outline:none;width:82px;}
.shift-time-input:focus{border-color:var(--navy);}
.shift-label-input{border:1px solid var(--grey-mid);padding:.28rem .45rem;font-family:var(--font);font-size:12px;color:var(--text);outline:none;flex:1;min-width:60px;}
.shift-label-input:focus{border-color:var(--navy);}
.shift-time-sep{font-size:11px;color:var(--text-muted);flex-shrink:0;}
.shift-del{font-size:14px;color:var(--red);background:none;border:none;cursor:pointer;padding:0 .2rem;line-height:1;flex-shrink:0;}
.shift-del:hover{opacity:.7;}
.shift-builder-footer{padding:.5rem .85rem;border-top:1px solid var(--grey-mid);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;justify-content:space-between;}
.shift-add-btn{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:.28rem .75rem;border:1px solid;cursor:pointer;font-family:var(--font);background:none;transition:all .12s;}
.shift-add-shift{color:var(--navy);border-color:rgba(0,51,102,.25);background:var(--navy-faint);}
.shift-add-shift:hover{background:var(--navy);color:#fff;}
.shift-add-break{color:var(--amber);border-color:var(--amber-bdr);background:var(--amber-bg);}
.shift-add-break:hover{background:var(--amber);color:#fff;}
.shift-hours-display{font-size:11px;font-weight:bold;color:var(--green);}

/* ─ EQUIPMENT CHECKLIST ─ */
.equip-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.3rem .5rem;padding:.65rem .85rem;border:1px solid var(--grey-mid);background:var(--white);margin-bottom:.7rem;}
.equip-item{display:flex;align-items:center;gap:.4rem;font-size:12px;color:var(--text);cursor:pointer;}
.equip-item input[type="checkbox"]{accent-color:var(--navy);width:14px;height:14px;flex-shrink:0;cursor:pointer;}
.equip-custom-row{padding:.35rem .85rem;border-top:1px solid var(--grey-mid);background:#fafbfc;display:flex;gap:.4rem;}
.equip-custom-input{flex:1;border:1px solid var(--grey-mid);padding:.28rem .55rem;font-size:12px;font-family:var(--font);outline:none;}
.equip-custom-input:focus{border-color:var(--navy);}

/* ─ CHANNEL PLAN (modal) ─ */
.channel-block{border:1px solid var(--grey-mid);margin-bottom:.7rem;}
.channel-block-head{padding:.4rem .7rem;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;background:var(--grey);border-bottom:1px solid var(--grey-mid);}
.channel-block-head.primary-head{background:var(--navy);color:#fff;}
.channel-block-head.secondary-head{background:var(--navy-faint);color:var(--navy);}
.channel-block-head.fallback-head{background:var(--grey);color:var(--text-muted);}
.channel-block-body{padding:.6rem .7rem;}
.channel-freq-row{display:grid;grid-template-columns:1fr auto auto;gap:.5rem;align-items:end;}

/* ─ BRIEFING SHEET ─ */
.version-stamp{display:inline-flex;align-items:center;gap:.4rem;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);border:1px solid var(--grey-mid);padding:2px 8px;background:var(--grey);}
.version-stamp .ver-num{color:var(--navy);}
.briefing-header{background:var(--navy);color:#fff;padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1.25rem;margin-bottom:1.25rem;}
.briefing-logo{background:var(--red);width:52px;height:52px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.briefing-h1{font-size:18px;font-weight:bold;margin-bottom:.15rem;}
.briefing-h2{font-size:12px;color:rgba(255,255,255,.65);}
.briefing-table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:1rem;}
.briefing-table th,.briefing-table td{border:1px solid var(--grey-mid);padding:.45rem .7rem;text-align:left;}
.briefing-table th{background:var(--navy);color:#fff;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;}
.briefing-table tr:nth-child(even) td{background:var(--grey);}
.brief-section-head{background:var(--navy-deep)!important;}
.qr-cell{text-align:center;vertical-align:middle;width:90px;}
.qr-cell img{width:70px;height:70px;}
.brief-page{margin-bottom:2rem;}
.brief-page-title{font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);padding:.6rem 1rem;background:var(--grey);border:1px solid var(--grey-mid);border-left:4px solid var(--navy);margin-bottom:1rem;}
.channel-plan-table{width:100%;border-collapse:collapse;font-size:12px;}
.channel-plan-table th,.channel-plan-table td{border:1px solid var(--grey-mid);padding:.45rem .75rem;text-align:left;}
.channel-plan-table th{background:var(--navy);color:#fff;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;}
.channel-plan-table .pri td{background:rgba(0,51,102,.04);}
.channel-tier-badge{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:1px 5px;border:1px solid;white-space:nowrap;}
.tier-pri{background:var(--navy-faint);border-color:rgba(0,51,102,.3);color:var(--navy);}
.tier-sec{background:var(--green-bg);border-color:var(--green-bdr);color:var(--green);}
.tier-fal{background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);}

/* ─ MODAL ─ */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,31,64,.55);z-index:500;display:none;align-items:center;justify-content:center;padding:1rem;}
.modal-backdrop.open{display:flex;}
.modal{background:var(--white);width:100%;max-width:780px;max-height:92vh;overflow-y:auto;box-shadow:var(--shadow-lg);border-top:4px solid var(--navy);animation:slideUp .2s ease;}
@keyframes slideUp{from{transform:translateY(20px);opacity:0;}to{transform:none;opacity:1;}}
.modal-head{padding:.85rem 1.25rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1;}
.modal-title{font-size:13px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;}
.modal-close{font-size:18px;color:var(--text-muted);background:none;border:none;cursor:pointer;padding:0 .25rem;line-height:1;transition:color .15s;}
.modal-close:hover{color:var(--red);}
.modal-body{padding:1.25rem;}
.modal-footer{padding:.85rem 1.25rem;border-top:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;gap:.75rem;position:sticky;bottom:0;z-index:1;}

/* ─ ANIMATIONS ─ */
@keyframes fadeUp{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:none;}}
.fade-in{animation:fadeUp .3s ease both;}

/* Route marching-ants animation — applied to Leaflet SVG path via JS */
@keyframes routeMarch {
    from { stroke-dashoffset: 24; }
    to   { stroke-dashoffset: 0;  }
}
.route-animated {
    stroke-dasharray: 16 8;
    animation: routeMarch 0.6s linear infinite;
}

/* ─ PULSE ANIMATIONS ─ */
@keyframes attPulse {
    0%   { transform: scale(1);   opacity: .7; }
    70%  { transform: scale(2.2); opacity: 0;  }
    100% { transform: scale(2.2); opacity: 0;  }
}
.pulse-ring {
    position: absolute; width: 30px; height: 30px;
    border-radius: 50%; border: 2px solid;
    animation: attPulse 2s ease-out infinite;
    pointer-events: none;
}
/* ─ FULLSCREEN HUD ─ */
#map-hud {
    display: none; position: absolute; top: 12px; right: 12px; z-index: 1200;
    background: rgba(0,31,64,.88); border: 1px solid rgba(0,51,102,.5);
    padding: .55rem .85rem; min-width: 180px; pointer-events: none;
}
#map-wrap:fullscreen #map-hud,
#map-wrap:-webkit-full-screen #map-hud { display: block; }
#map-wrap:fullscreen,
#map-wrap:-webkit-full-screen { background: #000; }
#map-wrap:fullscreen #leaflet-map,
#map-wrap:-webkit-full-screen #leaflet-map { height: 100% !important; }
#map-hud .hud-title  { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.55); margin-bottom: .35rem; }
#map-hud .hud-row    { display: flex; justify-content: space-between; gap: .75rem; font-size: 12px; font-weight: bold; color: #fff; margin-bottom: 2px; }
#map-hud .hud-muted  { color: rgba(255,255,255,.55); font-weight: normal; }
/* ─ TIMELINE REPLAY ─ */
#timeline-panel {
    display: none; padding: .6rem .9rem; background: var(--grey);
    border-top: 1px solid var(--grey-mid); align-items: center; gap: .75rem; flex-wrap: wrap;
}
#timeline-panel.visible { display: flex; }
#timeline-scrubber { flex: 1; min-width: 120px; }
/* ─ CONTEXT MENU ─ */
#map-ctx-menu {
    display: none; position: absolute; z-index: 2000;
    background: var(--white); border: 1px solid var(--grey-mid);
    box-shadow: 0 4px 16px rgba(0,51,102,.15); min-width: 160px;
}
#map-ctx-menu.open { display: block; }
.ctx-item {
    display: flex; align-items: center; gap: .5rem; padding: .5rem .85rem;
    font-size: 12px; font-weight: bold; color: var(--text-mid); cursor: pointer;
    text-transform: uppercase; letter-spacing: .04em; transition: background .1s;
}
.ctx-item:hover { background: var(--navy-faint); color: var(--navy); }
.ctx-divider { height: 1px; background: var(--grey-mid); margin: .2rem 0; }

/* ─ PRINT ─ */
@media print {
    .rn-header,.tab-bar,.action-bar,.bulk-bar,#tab-team,#tab-schedule,#tab-map,#tab-attendance,.ac-actions,.btn,header,nav,#assignModal{display:none!important;}
    #tab-briefing{display:block!important;}
    body{background:#fff;font-size:11pt;}
    .panel{box-shadow:none;border:1px solid #ccc;}
    .brief-page{page-break-after:always;}
    .brief-page:last-child{page-break-after:avoid;}
}

/* ─ RESPONSIVE ─ */
@media(max-width:640px){
    .event-stats{gap:.35rem;}
    .stat-card{min-width:50px;padding:.4rem .55rem;}
    .stat-val{font-size:16px;}
    .frow{grid-template-columns:1fr;}
    .frow.three{grid-template-columns:1fr;}
    .ac-details{grid-template-columns:1fr;}
}
</style>

{{-- HEADER --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Event Team Management</div>
            </div>
        </div>
        <nav class="rn-nav">
            <a href="{{ route('admin.events') }}" class="rn-back">← Back to Events</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="rn-logout">⏻ Log out</button>
            </form>
        </nav>
    </div>
</header>

{{-- HERO --}}
<div class="event-hero fade-in">
    <div class="event-hero-inner">
        <div class="event-hero-left">
            <div class="event-eyebrow">Event Team Management</div>
            <div class="event-title">{{ $event->title }}</div>
            <div class="event-meta">
                @if ($event->starts_at)
                    <span class="event-meta-chip">📅 {{ $event->starts_at->format('D j M Y') }}</span>
                    <span class="event-meta-chip">🕐 {{ $event->starts_at->format('H:i') }}{{ $event->ends_at ? ' – '.$event->ends_at->format('H:i') : '' }}</span>
                @endif
                @if ($event->location)
                    <span class="event-meta-chip">📍 {{ $event->location }}</span>
                @endif
                @if ($event->type)
                    <span class="event-meta-chip">🏷 {{ $event->type->name }}</span>
                @endif
            </div>
        </div>
        <div class="event-stats">
            <div class="stat-card"><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Assigned</div></div>
            <div class="stat-card"><div class="stat-val green">{{ $stats['confirmed'] }}</div><div class="stat-lbl">Confirmed</div></div>
            <div class="stat-card"><div class="stat-val amber">{{ $stats['pending'] }}</div><div class="stat-lbl">Pending</div></div>
            <div class="stat-card"><div class="stat-val red">{{ $stats['declined'] }}</div><div class="stat-lbl">Declined</div></div>
            <div class="stat-card"><div class="stat-val blue">{{ $totalTeamHours }}h</div><div class="stat-lbl">Team Hrs</div></div>
            <div class="stat-card"><div class="stat-val">{{ $stats['mapped'] }}</div><div class="stat-lbl">Mapped</div></div>
            <div class="stat-card"><div class="stat-val">{{ $stats['vehicles'] }}</div><div class="stat-lbl">Vehicles</div></div>
            <div class="stat-card"><div class="stat-val">{{ $stats['first_aid'] }}</div><div class="stat-lbl">First Aid</div></div>
        </div>
    </div>
</div>

{{-- TAB BAR --}}
<div class="tab-bar fade-in">
    <div class="tab-bar-inner">
        <button class="tab-btn active" onclick="switchTab('team')"     id="tabbtn-team">    <span class="tab-icon">👥</span> Team</button>
        <button class="tab-btn"        onclick="switchTab('schedule')" id="tabbtn-schedule"><span class="tab-icon">🕐</span> Schedule</button>
        <button class="tab-btn"        onclick="switchTab('map')"      id="tabbtn-map">     <span class="tab-icon">🗺</span> Map</button>
        <button class="tab-btn"        onclick="switchTab('briefing')" id="tabbtn-briefing"><span class="tab-icon">📋</span> Briefing</button>
        <button class="tab-btn"        onclick="switchTab('attendance')" id="tabbtn-attendance"><span class="tab-icon">✅</span> Attendance</button>
        <button class="tab-btn" onclick="switchTab('availability')" id="tabbtn-availability"><span class="tab-icon">📣</span> Availability</button>
        <button class="tab-btn" onclick="switchTab('bulkfill')" id="tabbtn-bulkfill"><span class="tab-icon">⚡</span> Bulk Fill</button>
    </div>
</div>

<div class="wrap">

    @if (session('status'))
        <div class="alert alert-success fade-in">✓ {{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error fade-in">⚠ {{ session('error') }}</div>
    @endif

    {{-- ══════════════════════════════ TAB: CREW ══════════════════════════════ --}}
    <div id="tab-team" class="tab-pane active fade-in">

        <div class="action-bar">
            <button class="btn btn-primary" onclick="openAddModal()">+ Assign Member</button>

            <button class="btn btn-primary" onclick="document.getElementById('briefingModal').classList.add('open');">✉ Send Briefings</button>
            <a href="{{ route('admin.events.assignments.ops-pack', $event->id) }}" class="btn btn-ghost" target="_blank" style="color:#1a6b3c;border-color:rgba(26,107,60,.4);">⬇ Download Ops Pack PDF</a>

            {{-- Duplicate team from past event --}}
            @if (isset($pastEvents) && $pastEvents->isNotEmpty())
            <form method="POST" action="{{ route('admin.events.duplicate-team', $event->id) }}"
                  style="display:inline;display:flex;align-items:center;gap:.3rem;">
                @csrf
                <select name="source_event_id" style="padding:.38rem .6rem;border:1px solid var(--grey-mid);font-family:var(--font);font-size:11px;outline:none;">
                    <option value="">Copy team from…</option>
                    @foreach ($pastEvents as $pe)
                        <option value="{{ $pe->id }}">{{ $pe->title }} ({{ $pe->starts_at?->format('j M Y') }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-ghost"
                        onclick="return confirm('Copy all team assignments from that event to this one?')">↓ Duplicate</button>
            </form>
            @endif

            <button class="btn btn-ghost" onclick="toggleBulkMode()">☑ Bulk Select</button>
            <button class="btn btn-ghost" onclick="switchTab('map')">🗺 Map</button>
            <button class="btn btn-ghost" onclick="switchTab('briefing')">📋 Briefing</button>
        </div>

        @if ($assignments->isEmpty())
            <div class="panel">
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <div class="empty-text">No team assigned yet. Click <strong>Assign Member</strong> to get started.</div>
                </div>
            </div>
        @else
            @php
                $statusGroups = [
                    'confirmed' => ['✓ Confirmed',          'green'],
                    'standby'   => ['⏳ Standby',            'amber'],
                    'pending'   => ['⋯ Pending Confirmation','navy'],
                    'declined'  => ['✕ Declined',            'red'],
                ];
            @endphp
            @foreach ($statusGroups as $status => [$label, $colour])
                @php $group = $assignments->where('status', $status); @endphp
                @if ($group->isNotEmpty())
                    <div class="section-divider" style="color:var(--{{ $colour }});">{{ $label }} ({{ $group->count() }})</div>
                    @foreach ($group as $asgn)
                    @php
                        $words    = explode(' ', $asgn->user->name);
                        $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words,0,2)));
                        $chipClass = 'chip-' . $asgn->statusColour();

                        $aShifts = $normaliseShifts($asgn);
                        $aHours  = $calcHours($aShifts);

                        $aEquipItems = $asgn->equipment_items ?? null;
                        if (is_string($aEquipItems)) $aEquipItems = json_decode($aEquipItems,true);
                        if (!is_array($aEquipItems)) $aEquipItems = [];
                    @endphp
                    <div class="assignment-card {{ $asgn->status }}" id="card-{{ $asgn->id }}">
                        <div class="ac-head" onclick="handleCardClick(event, {{ $asgn->id }})">
                            {{-- Bulk checkbox (hidden until bulk mode) --}}
                            <input type="checkbox" class="team-checkbox bulk-cb" id="cb-{{ $asgn->id }}"
                                   value="{{ $asgn->id }}" style="display:none;" onclick="event.stopPropagation();toggleSelect({{ $asgn->id }})">
                            <div class="ac-avatar">{{ $initials }}</div>
                            <div class="ac-info">
                                <div class="ac-name">
                                    {!! pii($asgn->user->name, $asgn->user->piiVisible()) !!}
                                    @if ($asgn->callsign)
                                        <span style="font-size:11px;color:var(--text-muted);font-weight:normal;">({!! pii($asgn->callsign, $asgn->user->piiVisible()) !!})</span>
                                    @endif
                                    @if ($aHours)
                                        <span class="ac-hours-badge">{{ $aHours }}h</span>
                                    @endif
                                </div>
                                <div class="ac-role">{{ $asgn->role ?: 'No role set' }}</div>
                                <div class="ac-chips">
                                    <span class="ac-chip {{ $chipClass }}">{{ $asgn->statusLabel() }}</span>
                                    @if (($unavailableUserIds ?? collect())->contains($asgn->user_id))
                                        <span class="ac-chip" style="background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);" title="This member has marked themselves unavailable for this event date">⚠ Unavailable</span>
                                    @endif
                                    @if ($asgn->attendance_status !== 'not_arrived')
                                        <span class="ac-chip chip-{{ $asgn->attendanceColour() }}">
                                            {{ match($asgn->attendance_status) {
                                                'checked_in'  => '✓ On Site',
                                                'on_break'    => '⏸ On Break',
                                                'checked_out' => '⏹ Left Site',
                                                default       => ''
                                            } }}
                                        </span>
                                    @endif
                                    @if (!empty($aShifts))
                                        @foreach ($aShifts as $sh)
                                            @if (($sh['type']??'shift')==='shift' && (!empty($sh['start']) || !empty($sh['end'])))
                                                <span class="ac-chip chip-navy">
                                                    🕐 {{ $sh['start']??'?' }}–{{ $sh['end']??'?' }}
                                                    {{ !empty($sh['label']) ? ' · '.$sh['label'] : '' }}
                                                </span>
                                            @elseif (($sh['type']??'shift')==='break')
                                                <span class="ac-chip chip-amber">⏸ {{ $sh['start']??'?' }}–{{ $sh['end']??'?' }}</span>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if ($asgn->frequency)
                                        <span class="ac-chip chip-navy">📻 {{ $asgn->frequency }} {{ $asgn->mode }}</span>
                                    @endif
                                    @if ($asgn->location_name)
                                        <span class="ac-chip chip-grey">📍 {{ $asgn->location_name }}</span>
                                    @endif
                                    @if ($asgn->has_vehicle)
                                        <span class="ac-chip chip-green">🚗 Vehicle</span>
                                    @endif
                                    @if ($asgn->first_aid_trained)
                                        <span class="ac-chip chip-red">🩺 First Aid</span>
                                    @endif
                                    @if ($asgn->briefing_sent)
                                        <span class="ac-chip chip-grey">✉ Briefed</span>
                                    @endif
                                </div>
                            </div>
                            <span class="ac-toggle" id="toggle-{{ $asgn->id }}">⌄</span>
                        </div>

                        <div class="ac-body" id="body-{{ $asgn->id }}">
                            <div class="ac-details">
                                <div class="ac-detail-item"><span class="ac-detail-label">Location</span><span class="ac-detail-val {{ $asgn->location_name?'':'muted' }}">{{ $asgn->location_name ?: 'Not set' }}</span></div>
                                <div class="ac-detail-item"><span class="ac-detail-label">Grid Ref</span><span class="ac-detail-val {{ $asgn->grid_ref?'':'muted' }}">{{ $asgn->grid_ref ?: 'Not set' }}</span></div>
                                <div class="ac-detail-item"><span class="ac-detail-label">Primary Freq</span><span class="ac-detail-val {{ $asgn->frequency?'':'muted' }}">{{ $asgn->frequency ? $asgn->frequency.' '.$asgn->mode.($asgn->ctcss_tone?' (CTCSS '.$asgn->ctcss_tone.')':'') : 'Not set' }}</span></div>
                                @if ($asgn->secondary_frequency ?? null)
                                <div class="ac-detail-item"><span class="ac-detail-label">Secondary Freq</span><span class="ac-detail-val">{{ $asgn->secondary_frequency }} {{ $asgn->secondary_mode ?? '' }}</span></div>
                                @endif
                                @if ($asgn->fallback_frequency ?? null)
                                <div class="ac-detail-item"><span class="ac-detail-label">Fallback Freq</span><span class="ac-detail-val">{{ $asgn->fallback_frequency }} {{ $asgn->fallback_mode ?? '' }}</span></div>
                                @endif
                                <div class="ac-detail-item"><span class="ac-detail-label">Report Time</span><span class="ac-detail-val {{ $asgn->report_time?'':'muted' }}">{{ $asgn->report_time ? substr($asgn->report_time,0,5) : 'Not set' }}</span></div>
                                <div class="ac-detail-item"><span class="ac-detail-label">Depart Time</span><span class="ac-detail-val {{ $asgn->depart_time?'':'muted' }}">{{ $asgn->depart_time ? substr($asgn->depart_time,0,5) : 'Not set' }}</span></div>
                                <div class="ac-detail-item"><span class="ac-detail-label">Vehicle</span><span class="ac-detail-val {{ $asgn->has_vehicle?'':'muted' }}">{{ $asgn->has_vehicle ? ($asgn->vehicle_reg ?: 'Yes') : 'No' }}</span></div>
                                <div class="ac-detail-item"><span class="ac-detail-label">What3Words</span><span class="ac-detail-val {{ ($asgn->what3words??null)?'':'muted' }}">{{ $asgn->what3words ?: 'Not set' }}</span></div>
                                @if ($asgn->emergency_contact_name ?? null)
                                <div class="ac-detail-item"><span class="ac-detail-label">Emergency Contact</span><span class="ac-detail-val">{{ $asgn->emergency_contact_name }}{{ ($asgn->emergency_contact_phone ?? null) ? ' · '.$asgn->emergency_contact_phone : '' }}</span></div>
                                @endif
                            </div>

                            @if (!empty($aEquipItems))
                            <div style="margin-bottom:.6rem;">
                                <div class="ac-detail-label" style="margin-bottom:.35rem;">Equipment</div>
                                <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
                                    @foreach ($aEquipItems as $item)
                                        <span style="font-size:11px;background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);color:var(--navy);padding:1px 7px;font-weight:bold;">{{ $item }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if ($asgn->briefing_notes)
                            <div style="margin-bottom:.6rem;">
                                <div class="ac-detail-label" style="margin-bottom:.3rem;">Briefing Notes</div>
                                <div style="font-size:12px;background:var(--amber-bg);border:1px solid var(--amber-bdr);border-left:3px solid var(--amber-bdr);padding:.45rem .7rem;">{{ $asgn->briefing_notes }}</div>
                            </div>
                            @endif

                            <div class="ac-actions">
                                <button class="btn btn-primary" onclick="openEditModal({{ $asgn->id }})">✏ Edit</button>
                                <button class="btn btn-ghost" onclick="openSingleBriefingModal({{ $asgn->id }}, '{{ addslashes($asgn->user->name) }}');">✉ Briefing</button>
                                <a href="{{ route('admin.events.assignments.briefing-pdf', $asgn->id) }}" class="btn btn-ghost" target="_blank">⬇ PDF</a>
                                @if ($asgn->lat && $asgn->lng)
                                    <button class="btn btn-ghost" onclick="switchTab('map');setTimeout(function(){flyToMarker({{ $asgn->id }});},300)">🗺 Locate on Map</button>
                                @endif
                                <form method="POST" action="{{ route('admin.events.assignments.destroy', $asgn->id) }}" style="display:inline;" onsubmit="return confirm('Remove {{ addslashes($asgn->user->name) }} from this event?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-red">✕ Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            @endforeach
        @endif
    </div>

    {{-- ══════════════════════════════ TAB: SCHEDULE ══════════════════════════════ --}}
    <div id="tab-schedule" class="tab-pane fade-in">

        <div id="coverage-warnings"></div>

        @php
            $withTimes = $assignments->filter(function($a) use ($normaliseShifts) {
                $sh = $normaliseShifts($a);
                return $a->report_time || $a->depart_time || count($sh) > 0;
            });
            $allHours = [];
            foreach ($withTimes as $a) {
                if ($a->report_time) $allHours[] = (int)substr($a->report_time,0,2);
                if ($a->depart_time) $allHours[] = (int)substr($a->depart_time,0,2);
                foreach ($normaliseShifts($a) as $sh) {
                    if (!empty($sh['start'])) $allHours[] = (int)substr($sh['start'],0,2);
                    if (!empty($sh['end']))   $allHours[] = (int)substr($sh['end'],  0,2);
                }
            }
            $tlStart = $allHours ? max(0,  min($allHours)-1) : 6;
            $tlEnd   = $allHours ? min(24, max($allHours)+1) : 20;
            $tlSpan  = max(1, $tlEnd - $tlStart);
        @endphp

        <div class="schedule-grid">
            <div class="timeline">
                <div class="timeline-title">Team Schedule — {{ $event->title }}</div>

                @if ($withTimes->isEmpty())
                    <div class="no-schedule">No schedule times set. Edit assignments in the Team tab to add shift times.</div>
                @else
                    {{-- Hour headers --}}
                    <div class="timeline-hours">
                        <div style="width:155px;flex-shrink:0;border-right:2px solid var(--grey-mid);"></div>
                        @for ($h = $tlStart; $h <= $tlEnd; $h++)
                            <div class="timeline-hour">{{ sprintf('%02d:00',$h) }}</div>
                        @endfor
                    </div>

                    {{-- Operator rows --}}
                    @foreach ($withTimes as $asgn)
                    @php
                        $aShifts = $normaliseShifts($asgn);
                        $aHours  = $calcHours($aShifts);
                        $barColours = ['confirmed'=>'#1a6b3c','standby'=>'#8a5c00','pending'=>'#003366','declined'=>'#C8102E'];
                        $barColour  = $barColours[$asgn->status] ?? '#003366';
                        $toFrac = function($t) use ($tlStart,$tlSpan) {
                            if (!$t) return null;
                            $parts = explode(':',$t);
                            $h = (int)($parts[0]??0); $m = (int)($parts[1]??0);
                            return ($h + $m/60 - $tlStart) / $tlSpan * 100;
                        };
                    @endphp
                    <div class="timeline-row">
                        <div class="tl-name">
                            <div class="tl-name-val">{!! pii($asgn->user->name, $asgn->user->piiVisible()) !!}</div>
                            <div class="tl-name-role">{{ $asgn->role ?: '—' }}</div>
                            @if ($aHours)
                                <div class="tl-name-hours">{{ $aHours }}h total</div>
                            @endif
                        </div>
                        <div class="tl-track">
                            {{-- Report marker --}}
                            @php $rf = $toFrac($asgn->report_time ? substr($asgn->report_time,0,5) : null); @endphp
                            @if ($rf !== null && $rf >= 0 && $rf <= 100)
                                <div class="tl-report" style="left:{{ $rf }}%;">
                                    <div class="tl-marker-label" style="color:var(--amber);">{{ substr($asgn->report_time,0,5) }}</div>
                                </div>
                            @endif

                            {{-- Shifts & breaks --}}
                            @foreach ($aShifts as $shIdx => $sh)
                            @php
                                $sf = $toFrac($sh['start'] ?? null);
                                $ef = $toFrac($sh['end']   ?? null);
                                $isBreak = ($sh['type'] ?? 'shift') === 'break';
                                $barW = ($sf !== null && $ef !== null) ? max(0, $ef - $sf) : 0;
                            @endphp
                            @if ($sf !== null && $barW > 0)
                                @if ($isBreak)
                                    <div class="tl-break" style="left:{{ $sf }}%;width:{{ $barW }}%;border:1px dashed var(--amber-bdr);" title="Break {{ $sh['start']??'' }}–{{ $sh['end']??'' }}"></div>
                                @else
                                    <div class="tl-bar" style="left:{{ $sf }}%;width:{{ $barW }}%;background:{{ $barColour }};" title="{{ $sh['label']??'' }} {{ $sh['start']??'' }}–{{ $sh['end']??'' }}">
                                        {{ !empty($sh['label']) ? $sh['label'] : (($sh['start']??'').($sh['end']??'?'?'–'.$sh['end']:'')) }}
                                    </div>
                                @endif
                            @endif
                            @endforeach

                            {{-- Depart marker --}}
                            @php $df = $toFrac($asgn->depart_time ? substr($asgn->depart_time,0,5) : null); @endphp
                            @if ($df !== null && $df >= 0 && $df <= 100)
                                <div class="tl-depart" style="left:{{ $df }}%;">
                                    <div class="tl-marker-label" style="color:var(--red);top:auto;bottom:1px;">{{ substr($asgn->depart_time,0,5) }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    {{-- Coverage heat row --}}
                    <div class="tl-coverage-row" style="margin-top:.25rem;">
                        <div class="tl-coverage-name">Coverage</div>
                        <div class="tl-coverage-track" id="coverage-heat"></div>
                    </div>

                    {{-- Legend --}}
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--grey-mid);">
                        <div style="display:flex;align-items:center;gap:.4rem;font-size:11px;color:var(--text-muted);"><div style="width:2px;height:14px;background:var(--amber-bdr);"></div>Report time</div>
                        <div style="display:flex;align-items:center;gap:.4rem;font-size:11px;color:var(--text-muted);"><div style="width:22px;height:12px;background:var(--green);"></div>Confirmed shift</div>
                        <div style="display:flex;align-items:center;gap:.4rem;font-size:11px;color:var(--text-muted);"><div style="width:22px;height:12px;background:var(--navy);"></div>Pending shift</div>
                        <div style="display:flex;align-items:center;gap:.4rem;font-size:11px;color:var(--text-muted);"><div style="width:22px;height:12px;background:repeating-linear-gradient(45deg,transparent,transparent 3px,rgba(0,0,0,.15) 3px,rgba(0,0,0,.15) 6px);border:1px dashed var(--amber-bdr);"></div>Break</div>
                        <div style="display:flex;align-items:center;gap:.4rem;font-size:11px;color:var(--text-muted);"><div style="width:2px;height:14px;background:var(--red);opacity:.5;"></div>Depart</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Frequency / channel plan table --}}
        @php $withFreq = $assignments->filter(fn($a) => $a->frequency || ($a->secondary_frequency??null) || ($a->fallback_frequency??null)); @endphp
        @if ($withFreq->isNotEmpty())
        <div class="panel" style="margin-top:1.5rem;">
            <div class="panel-head"><div><div class="panel-title">📻 Channel Plan</div><div class="panel-sub">All frequencies across primary, secondary and fallback</div></div></div>
            <div style="padding:0;">
                <table class="channel-plan-table">
                    <thead><tr><th>Operator</th><th>Callsign</th><th>Role</th><th>Label</th><th>Tier</th><th>Frequency</th><th>Mode</th><th>CTCSS</th><th>Location</th></tr></thead>
                    <tbody>
                    @foreach ($withFreq as $a)
                        @if ($a->frequency)
                        <tr class="pri">
                            <td rowspan="{{ (($a->secondary_frequency??null)?1:0)+(($a->fallback_frequency??null)?1:0)+1 }}"><strong>{!! pii($a->user->name, $a->user->piiVisible()) !!}</strong></td>
                            <td rowspan="{{ (($a->secondary_frequency??null)?1:0)+(($a->fallback_frequency??null)?1:0)+1 }}">{{ $a->callsign ?: '—' }}</td>
                            <td rowspan="{{ (($a->secondary_frequency??null)?1:0)+(($a->fallback_frequency??null)?1:0)+1 }}">{{ $a->role ?: '—' }}</td>
                            <td>{{ $a->channel_label ?? '—' }}</td>
                            <td><span class="channel-tier-badge tier-pri">Primary</span></td>
                            <td><strong>{{ $a->frequency }}</strong></td><td>{{ $a->mode }}</td><td>{{ $a->ctcss_tone ?: '—' }}</td>
                            <td rowspan="{{ (($a->secondary_frequency??null)?1:0)+(($a->fallback_frequency??null)?1:0)+1 }}">{{ $a->location_name ?: '—' }}</td>
                        </tr>
                        @endif
                        @if ($a->secondary_frequency ?? null)
                        <tr>
                            <td>{{ $a->channel_label ?? '—' }}</td>
                            <td><span class="channel-tier-badge tier-sec">Secondary</span></td>
                            <td><strong>{{ $a->secondary_frequency }}</strong></td><td>{{ $a->secondary_mode ?? '—' }}</td><td>{{ $a->secondary_ctcss ?? '—' }}</td>
                        </tr>
                        @endif
                        @if ($a->fallback_frequency ?? null)
                        <tr>
                            <td>{{ $a->channel_label ?? '—' }}</td>
                            <td><span class="channel-tier-badge tier-fal">Fallback</span></td>
                            <td><strong>{{ $a->fallback_frequency }}</strong></td><td>{{ $a->fallback_mode ?? '—' }}</td><td>{{ $a->fallback_ctcss ?? '—' }}</td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════ TAB: MAP ══════════════════════════════ --}}
    <div id="tab-map" class="tab-pane fade-in">
        @php
            $mappedOps   = $assignments->whereNotNull('lat')->whereNotNull('lng');
            $unmappedOps = $assignments->filter(fn($a) => !$a->lat || !$a->lng);
        @endphp
        <div class="map-layout">
            <div id="map-container">
                {{-- GPX upload bar --}}
                <div class="gpx-upload-area">
                    <span class="gpx-lbl">🗺 GPX Route</span>
                    <input type="file" id="gpx-input" accept=".gpx" onchange="loadGpx(this)" style="font-size:11px;flex:1;">
                    <button class="btn btn-ghost" style="padding:.25rem .65rem;font-size:10px;" onclick="clearGpx()">✕ Clear</button>
                    <span id="gpx-status"></span>
                    <button class="btn btn-ghost" style="padding:.25rem .65rem;font-size:10px;margin-left:auto;" onclick="toggleAllCircles()" id="circles-btn">⊙ Coverage Circles</button>
                </div>
                <div id="map-toolbar">
                    <span style="font-size:11px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.06em;">Operations Map — {{ $event->title }}</span>
                    <div style="margin-left:auto;display:flex;gap:.5rem;flex-wrap:wrap;">
                        <button class="btn btn-ghost" onclick="resetMapView()" style="padding:.3rem .75rem;">⌂ Reset</button>
                        <button class="btn btn-ghost" onclick="toggleSatellite()" id="sat-btn" style="padding:.3rem .75rem;">🛰 Satellite</button>
                        <button class="btn btn-ghost" onclick="toggleHeatmap()" id="heatmap-btn" style="padding:.3rem .75rem;" title="Operator density heatmap">🌡 Density</button>
                        <button class="btn btn-ghost" onclick="toggleGapDetector()" id="gap-btn" style="padding:.3rem .75rem;" title="Coverage gap detector">🕳 Gaps</button>
                        <button class="btn btn-ghost" onclick="toggleBearingTool()" id="bearing-btn" style="padding:.3rem .75rem;" title="Alt+hover for bearing/distance">🧭 Bearing</button>
                        <button class="btn btn-ghost" onclick="toggleCommsMode()" id="comms-btn" style="padding:.3rem .75rem;" title="Draw radio links between operators">📻 Comms</button>
                        <button class="btn btn-ghost" onclick="toggleTimeline()" id="timeline-btn" style="padding:.3rem .75rem;" title="Attendance timeline replay">⏱ Replay</button>
                        <button class="btn btn-ghost" onclick="enterFullscreen()" id="fs-btn" style="padding:.3rem .75rem;" title="Fullscreen operations view">⛶ Fullscreen</button>
                    </div>
                </div>
                <div id="map-wrap" style="position:relative;">
                    <div id="leaflet-map"></div>
                    {{-- Fullscreen HUD — absolutely positioned over the map --}}
                    <div id="map-hud">
                        <div class="hud-title">{{ $event->title }}</div>
                        <div class="hud-row"><span class="hud-muted">On site</span> <span id="hud-on-site" style="color:#6ee7b7;">0</span></div>
                        <div class="hud-row"><span class="hud-muted">On break</span><span id="hud-on-break" style="color:#fcd34d;">0</span></div>
                        <div class="hud-row"><span class="hud-muted">Checked out</span><span id="hud-out" style="color:#93c5fd;">0</span></div>
                        <div class="hud-row"><span class="hud-muted">Not arrived</span><span id="hud-na" style="color:rgba(255,255,255,.4);">0</span></div>
                        <div style="margin-top:.4rem;font-size:11px;color:rgba(255,255,255,.45);" id="hud-clock"></div>
                    </div>
                    {{-- Right-click context menu — absolutely positioned over the map --}}
                    <div id="map-ctx-menu">
                        <div class="ctx-item" onclick="ctxAddRing(0.5)">⊙ 0.5 mile ring</div>
                        <div class="ctx-item" onclick="ctxAddRing(1)">⊙ 1 mile ring</div>
                        <div class="ctx-item" onclick="ctxAddRing(2)">⊙ 2 mile ring</div>
                        <div class="ctx-item" onclick="ctxAddRing(5)">⊙ 5 mile ring</div>
                        <div class="ctx-divider"></div>
                        <div class="ctx-item" onclick="ctxClearRings()" style="color:var(--red);">✕ Clear all rings</div>
                    </div>
                </div>
                {{-- Attendance timeline replay panel --}}
                <div id="timeline-panel">
                    <span style="font-size:11px;font-weight:bold;color:var(--navy);">⏱ Timeline Replay</span>
                    <input type="range" id="timeline-scrubber" min="0" max="1440" value="0" step="1" oninput="replayTimeline(this.value)">
                    <span id="timeline-clock" style="font-size:12px;font-weight:bold;color:var(--navy);min-width:40px;">00:00</span>
                    <button class="btn btn-ghost" onclick="toggleTimeline()" style="padding:.2rem .6rem;font-size:10px;">✕</button>
                </div>
            </div>

            <div>
                <div class="legend-panel">
                    <div class="legend-head"><div class="legend-title">Map Legend</div></div>
                    <div class="legend-body">
                        <div class="legend-item"><div class="legend-dot" style="background:#1a6b3c;"></div>Confirmed operator</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#8a5c00;border:1px solid #e8c96a;"></div>Standby operator</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#003366;"></div>Pending operator</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#C8102E;"></div>Declined operator</div>
                        <div class="legend-item"><div style="width:24px;height:2px;background:#C8102E;opacity:.7;flex-shrink:0;"></div>GPX route</div>
                        <div class="legend-item"><div style="width:24px;height:2px;background:#7c3aed;opacity:.9;flex-shrink:0;"></div>Event route</div>
                        <div class="legend-item"><div style="width:12px;height:12px;border-radius:50%;border:2px dashed #003366;background:rgba(0,51,102,.07);flex-shrink:0;"></div>Coverage radius</div>
                        @if ($event->hasPolygon())
                        <div class="legend-item">
                            <div style="width:20px;height:14px;background:rgba(0,51,102,.06);border:2px dashed #003366;flex-shrink:0;"></div>
                            Site boundary
                        </div>
                        <div class="legend-item">
                            <div style="width:20px;height:14px;background:rgba(0,31,64,.42);flex-shrink:0;"></div>
                            Outside site
                        </div>
                        @endif
                        @if (!empty($event->event_pois))
                        <div class="legend-item" style="margin-top:.3rem;padding-top:.3rem;border-top:1px solid var(--grey-mid);">
                            <span style="font-size:12px;">🚪</span> Entrance / Exit
                        </div>
                        <div class="legend-item"><span style="font-size:12px;">🅿</span> Car Park</div>
                        <div class="legend-item"><span style="font-size:12px;">🩺</span> Medical</div>
                        <div class="legend-item"><span style="font-size:12px;">📡</span> Control</div>
                        <div class="legend-item"><span style="font-size:12px;">⚠</span> Hazard</div>
                        <div class="legend-item"><span style="font-size:12px;">🚩</span> Custom POI</div>
                        @endif
                    </div>
                </div>

                <div class="legend-panel">
                    <div class="legend-head"><div class="legend-title">Operators ({{ $assignments->count() }})</div></div>
                    @if ($assignments->isEmpty())
                        <div style="padding:.75rem .9rem;font-size:12px;color:var(--text-muted);font-style:italic;">No operators assigned.</div>
                    @else
                        <div>
                        @foreach ($assignments as $asgn)
                            @php $colours=['confirmed'=>'#1a6b3c','standby'=>'#8a5c00','pending'=>'#003366','declined'=>'#C8102E']; $col=$colours[$asgn->status]??'#003366'; @endphp
                            <div class="op-list-item" onclick="flyToMarker({{ $asgn->id }})">
                                <div class="op-dot" style="background:{{ $col }};"></div>
                                <div class="op-info">
                                    <div class="op-name">{!! pii($asgn->user->name, $asgn->user->piiVisible()) !!}{{ $asgn->callsign ? ' ('.$asgn->callsign.')' : '' }}</div>
                                    <div class="op-sub">{{ $asgn->location_name ?: ($asgn->lat ? 'Mapped' : 'No position') }}</div>
                                </div>
                                @if ($asgn->lat && $asgn->lng)
                                    <div class="op-locate">→</div>
                                @else
                                    <div style="font-size:10px;color:var(--grey-dark);">—</div>
                                @endif
                            </div>
                        @endforeach
                        </div>
                    @endif
                </div>

                @if ($unmappedOps->isNotEmpty())
                <div class="legend-panel">
                    <div class="legend-head"><div class="legend-title" style="color:var(--amber);">⚠ No Position ({{ $unmappedOps->count() }})</div></div>
                    <div style="padding:.65rem .9rem;font-size:11px;color:var(--text-muted);">Edit each operator's assignment to add GPS coordinates or drag a pin from the map.</div>
                </div>
                @endif

                <div style="padding:.7rem .9rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.15);font-size:11px;color:var(--text-mid);line-height:1.6;">
                    <strong>Tips:</strong> Click a marker for details · Drag marker to update position · Use Coverage Circles to visualise radio range · Upload a GPX file to overlay the event route
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════ TAB: BRIEFING ══════════════════════════════ --}}
    <div id="tab-briefing" class="tab-pane fade-in">
        <div class="action-bar">
            <button class="btn btn-primary" onclick="printBriefing()">🖨 Print / Save PDF</button>
            <span class="version-stamp">Version <span class="ver-num" id="brief-version">1</span> · <span id="brief-date">—</span></span>
        </div>

        <div class="panel">

            {{-- PAGE 1: Event details + team roster --}}
            <div class="brief-page">
                <div class="briefing-header">
                    <div class="briefing-logo"><span style="font-size:14px;font-weight:bold;color:#fff;line-height:1.2;text-align:center;">RAY<br>NET</span></div>
                    <div>
                        <div class="briefing-h1">{{ $event->title }}</div>
                        <div class="briefing-h2">{{ \App\Helpers\RaynetSetting::groupName() }} Group · Operator Briefing Sheet{{ $event->starts_at ? ' · '.$event->starts_at->format('D j M Y') : '' }}</div>
                    </div>
                    <div style="margin-left:auto;text-align:right;font-size:11px;color:rgba(255,255,255,.55);">
                        Group Ref: {{ \App\Helpers\RaynetSetting::groupNumber() }}<br>
                        Printed: {{ now()->format('j M Y H:i') }}<br>
                        RESTRICTED — MEMBERS ONLY
                    </div>
                </div>

                <div style="padding:1.25rem 1.5rem;">
                    <table class="briefing-table" style="margin-bottom:1.5rem;">
                        <thead><tr><th colspan="4" class="brief-section-head">Event Details</th></tr></thead>
                        <tbody>
                            <tr><td style="font-weight:bold;width:140px;">Event Title</td><td>{{ $event->title }}</td><td style="font-weight:bold;width:140px;">Event Type</td><td>{{ $event->type?->name ?? '—' }}</td></tr>
                            <tr><td style="font-weight:bold;">Date</td><td>{{ $event->starts_at?->format('l j F Y') ?? '—' }}</td><td style="font-weight:bold;">Time</td><td>{{ ($event->starts_at?->format('H:i') ?? '—') . ($event->ends_at ? ' – '.$event->ends_at->format('H:i') : '') }}</td></tr>
                            <tr><td style="font-weight:bold;">Location</td><td colspan="3">{{ $event->location ?? '—' }}</td></tr>
                            @if ($event->description)
                                <tr><td style="font-weight:bold;">Description</td><td colspan="3">{{ $event->description }}</td></tr>
                            @endif
                        </tbody>
                    </table>

                    {{-- Team roster with QR codes --}}
                    <div class="brief-page-title">👥 Team Roster</div>
                    <table class="briefing-table">
                        <thead>
                            <tr>
                                <th>Name</th><th>Callsign</th><th>Role</th>
                                <th>Report</th><th>Shifts</th>
                                <th>Frequency</th><th>Location</th><th>Grid</th>
                                <th>Status</th><th class="qr-cell" style="width:90px;">QR</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($assignments->whereNotIn('status',['declined']) as $a)
                        @php
                            $aShifts = $normaliseShifts($a);
                            $shiftStr = implode(', ', array_map(function($s){
                                $t = ($s['type']??'shift')==='break'?'⏸':'🕐';
                                return $t.' '.($s['start']??'?').'–'.($s['end']??'?');
                            }, $aShifts));
                        @endphp
                        <tr>
                            <td><strong>{!! pii($a->user->name, $a->user->piiVisible()) !!}</strong></td>
                            <td>{{ $a->callsign ?: '—' }}</td>
                            <td>{{ $a->role ?: '—' }}</td>
                            <td>{{ $a->report_time ? substr($a->report_time,0,5) : '—' }}</td>
                            <td style="font-size:11px;">{{ $shiftStr ?: '—' }}</td>
                            <td>{{ $a->frequency ? $a->frequency.' '.$a->mode : '—' }}</td>
                            <td>{{ $a->location_name ?: '—' }}</td>
                            <td>{{ $a->grid_ref ?: '—' }}</td>
                            <td><strong>{{ $a->statusLabel() }}</strong></td>
                            <td class="qr-cell">
                                @if ($a->briefing_token)
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode($a->briefingUrl()) }}"
                                         alt="QR" width="70" height="70">
                                @else
                                    <span style="font-size:9px;color:var(--grey-dark);">Run migration</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Totals bar --}}
                    <div style="display:flex;gap:1.5rem;padding:.6rem 0;font-size:12px;color:var(--text-muted);border-top:1px solid var(--grey-mid);margin-top:.5rem;">
                        <span><strong style="color:var(--navy);">{{ $stats['confirmed'] }}</strong> Confirmed</span>
                        <span><strong style="color:var(--amber);">{{ $stats['pending'] }}</strong> Pending</span>
                        <span><strong style="color:var(--green);">{{ $stats['standby'] ?? 0 }}</strong> Standby</span>
                        <span><strong style="color:var(--navy);">{{ $totalTeamHours }}h</strong> Total team hours</span>
                    </div>
                </div>
            </div>

            {{-- PAGE 2: Channel Plan --}}
            @php $withFreq2 = $assignments->whereNotIn('status',['declined'])->filter(fn($a) => $a->frequency || ($a->secondary_frequency??null) || ($a->fallback_frequency??null)); @endphp
            <div class="brief-page" style="padding:1.25rem 1.5rem;">
                <div class="brief-page-title">📻 Channel Plan</div>
                @if ($withFreq2->isNotEmpty())
                <table class="channel-plan-table">
                    <thead><tr><th>Operator</th><th>Callsign</th><th>Role / Position</th><th>Label</th><th>Tier</th><th>Frequency</th><th>Mode</th><th>CTCSS</th></tr></thead>
                    <tbody>
                    @foreach ($withFreq2 as $a)
                        @if ($a->frequency)
                        <tr class="pri">
                            <td><strong>{!! pii($a->user->name, $a->user->piiVisible()) !!}</strong></td><td>{{ $a->callsign ?: '—' }}</td><td>{{ $a->role ?: '—' }}</td>
                            <td>{{ $a->channel_label ?? '—' }}</td><td><span class="channel-tier-badge tier-pri">Primary</span></td>
                            <td><strong>{{ $a->frequency }}</strong></td><td>{{ $a->mode }}</td><td>{{ $a->ctcss_tone ?: '—' }}</td>
                        </tr>
                        @endif
                        @if ($a->secondary_frequency ?? null)
                        <tr>
                            <td></td><td></td><td></td><td>{{ $a->channel_label ?? '—' }}</td>
                            <td><span class="channel-tier-badge tier-sec">Secondary</span></td>
                            <td><strong>{{ $a->secondary_frequency }}</strong></td><td>{{ $a->secondary_mode ?? '—' }}</td><td>{{ $a->secondary_ctcss ?? '—' }}</td>
                        </tr>
                        @endif
                        @if ($a->fallback_frequency ?? null)
                        <tr>
                            <td></td><td></td><td></td><td>{{ $a->channel_label ?? '—' }}</td>
                            <td><span class="channel-tier-badge tier-fal">Fallback</span></td>
                            <td><strong>{{ $a->fallback_frequency }}</strong></td><td>{{ $a->fallback_mode ?? '—' }}</td><td>{{ $a->fallback_ctcss ?? '—' }}</td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                @else
                    <p style="font-size:13px;color:var(--text-muted);font-style:italic;">No frequencies assigned.</p>
                @endif
            </div>

            {{-- PAGE 3: Per-operator individual briefs --}}
            @foreach ($assignments->whereNotIn('status',['declined']) as $a)
            @php
                $aShifts     = $normaliseShifts($a);
                $aHours      = $calcHours($aShifts);
                $aEquipItems = $a->equipment_items ?? null;
                if (is_string($aEquipItems)) $aEquipItems = json_decode($aEquipItems,true);
                if (!is_array($aEquipItems)) $aEquipItems = [];
            @endphp
            <div class="brief-page" style="padding:1.25rem 1.5rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <div class="brief-page-title" style="display:inline-flex;">{!! pii($a->user->name, $a->user->piiVisible()) !!}{{ $a->callsign ? ' ('.$a->callsign.')' : '' }} — Individual Brief</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:.4rem;">Role: <strong>{{ $a->role ?: 'Not assigned' }}</strong> · Status: <strong>{{ $a->statusLabel() }}</strong>{{ $aHours ? ' · '.$aHours.'h total' : '' }}</div>
                    </div>
                    @if ($a->briefing_token)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($a->briefingUrl()) }}"
                             alt="QR" width="90" height="90" style="flex-shrink:0;">
                    @else
                        <div style="width:90px;height:90px;background:var(--grey);border:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:9px;color:var(--grey-dark);text-align:center;padding:4px;">Run migration</div>
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    {{-- Left: Details --}}
                    <div>
                        <table class="briefing-table" style="margin-bottom:.85rem;">
                            <thead><tr><th colspan="2" class="brief-section-head">Position &amp; Schedule</th></tr></thead>
                            <tbody>
                                <tr><td style="font-weight:bold;width:130px;">Location</td><td>{{ $a->location_name ?: '—' }}</td></tr>
                                <tr><td style="font-weight:bold;">Grid Ref</td><td>{{ $a->grid_ref ?: '—' }}</td></tr>
                                @if ($a->what3words)
                                    <tr><td style="font-weight:bold;">What3Words</td><td>///{{ $a->what3words }}</td></tr>
                                @endif
                                @if ($a->lat && $a->lng)
                                    <tr><td style="font-weight:bold;">Coordinates</td><td>{{ number_format($a->lat,5) }}, {{ number_format($a->lng,5) }}</td></tr>
                                @endif
                                <tr><td style="font-weight:bold;">Report Time</td><td>{{ $a->report_time ? substr($a->report_time,0,5) : '—' }}</td></tr>
                                <tr><td style="font-weight:bold;">Depart Time</td><td>{{ $a->depart_time ? substr($a->depart_time,0,5) : '—' }}</td></tr>
                                @foreach ($aShifts as $sh)
                                    @if (($sh['type']??'shift')==='shift')
                                    <tr><td style="font-weight:bold;">Shift{{ !empty($sh['label']) ? ' ('.$sh['label'].')' : '' }}</td><td>{{ $sh['start']??'?' }} – {{ $sh['end']??'?' }}</td></tr>
                                    @else
                                    <tr><td style="font-weight:bold;color:var(--amber);">Break</td><td style="color:var(--amber);">{{ $sh['start']??'?' }} – {{ $sh['end']??'?' }}</td></tr>
                                    @endif
                                @endforeach
                                @if ($a->has_vehicle)
                                    <tr><td style="font-weight:bold;">Vehicle</td><td>Yes{{ $a->vehicle_reg ? ' — '.$a->vehicle_reg : '' }}</td></tr>
                                @endif
                            </tbody>
                        </table>
                        @if ($a->briefing_notes)
                        <div style="background:var(--amber-bg);border:1px solid var(--amber-bdr);border-left:3px solid var(--amber-bdr);padding:.55rem .75rem;font-size:12px;margin-top:.5rem;">
                            <strong>Briefing Notes:</strong> {{ $a->briefing_notes }}
                        </div>
                        @endif
                    </div>

                    {{-- Right: Frequencies + Equipment --}}
                    <div>
                        <table class="briefing-table" style="margin-bottom:.85rem;">
                            <thead><tr><th colspan="3" class="brief-section-head">Frequencies</th></tr></thead>
                            <thead><tr><th>Tier</th><th>Frequency</th><th>Mode / CTCSS</th></tr></thead>
                            <tbody>
                                @if ($a->frequency)
                                <tr><td><span class="channel-tier-badge tier-pri">Primary</span></td><td><strong>{{ $a->frequency }}</strong></td><td>{{ $a->mode }}{{ $a->ctcss_tone ? ' / '.$a->ctcss_tone : '' }}</td></tr>
                                @endif
                                @if ($a->secondary_frequency ?? null)
                                <tr><td><span class="channel-tier-badge tier-sec">Secondary</span></td><td><strong>{{ $a->secondary_frequency }}</strong></td><td>{{ ($a->secondary_mode ?? '—') . ($a->secondary_ctcss ?? null ? ' / '.$a->secondary_ctcss : '') }}</td></tr>
                                @endif
                                @if ($a->fallback_frequency ?? null)
                                <tr><td><span class="channel-tier-badge tier-fal">Fallback</span></td><td><strong>{{ $a->fallback_frequency }}</strong></td><td>{{ ($a->fallback_mode ?? '—') . ($a->fallback_ctcss ?? null ? ' / '.$a->fallback_ctcss : '') }}</td></tr>
                                @endif
                                @if (!$a->frequency && !($a->secondary_frequency??null))
                                    <tr><td colspan="3" style="color:var(--grey-dark);font-style:italic;">No frequencies assigned</td></tr>
                                @endif
                            </tbody>
                        </table>

                        @if (!empty($aEquipItems) || $a->equipment)
                        <table class="briefing-table">
                            <thead><tr><th colspan="2" class="brief-section-head">Equipment to Bring</th></tr></thead>
                            <tbody>
                                @foreach ($aEquipItems as $item)
                                <tr><td>☐</td><td>{{ $item }}</td></tr>
                                @endforeach
                                @if ($a->equipment && empty($aEquipItems))
                                <tr><td colspan="2">{{ $a->equipment }}</td></tr>
                                @endif
                            </tbody>
                        </table>
                        @endif

                        @if ($a->emergency_contact_name ?? null)
                        <div style="margin-top:.75rem;padding:.5rem .7rem;background:var(--red-faint);border:1px solid rgba(200,16,46,.2);border-left:3px solid var(--red);font-size:12px;">
                            <strong>Emergency Contact:</strong> {{ $a->emergency_contact_name }}
                            {{ ($a->emergency_contact_phone ?? null) ? ' · '.$a->emergency_contact_phone : '' }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Signature block --}}
                <div style="margin-top:1.25rem;padding-top:.9rem;border-top:1px solid var(--grey-mid);display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    @foreach (['Operator Signature','Event Controller','Time Checked In'] as $sig)
                    <div>
                        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.5rem;">{{ $sig }}</div>
                        <div style="height:36px;border-bottom:1px solid var(--text);margin-bottom:.25rem;"></div>
                        <div style="font-size:9px;color:var(--text-muted);">Print name / Callsign</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Final page: Emergency contacts --}}
            @php $withEC = $assignments->filter(fn($a) => ($a->emergency_contact_name ?? null)); @endphp
            @if ($withEC->isNotEmpty())
            <div style="padding:1.25rem 1.5rem;">
                <div class="brief-page-title">🆘 Emergency Contacts — RESTRICTED</div>
                <table class="briefing-table">
                    <thead><tr><th>Operator</th><th>Emergency Contact Name</th><th>Phone Number</th><th>Medical Notes</th></tr></thead>
                    <tbody>
                    @foreach ($withEC as $a)
                    <tr>
                        <td><strong>{!! pii($a->user->name, $a->user->piiVisible()) !!}</strong>{{ $a->first_aid_trained ? ' 🩺' : '' }}</td>
                        <td>{{ $a->emergency_contact_name }}</td>
                        <td><strong>{{ $a->emergency_contact_phone ?? '—' }}</strong></td>
                        <td style="font-size:11px;">{{ $a->medical_notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <div style="font-size:10px;color:var(--red);font-weight:bold;margin-top:.5rem;">
                    ⚠ SENSITIVE — Do not leave unattended. Destroy after event or return to Group Controller.
                </div>
            </div>
            @endif

            {{-- Global footer --}}
            <div style="padding:1rem 1.5rem;border-top:2px solid var(--grey-mid);text-align:center;font-size:10px;color:var(--text-muted);">
                {{ \App\Helpers\RaynetSetting::groupName() }} Group ({{ \App\Helpers\RaynetSetting::groupNumber() }}) · Member of RAYNET-UK · This document is for authorised personnel only
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════ TAB: ATTENDANCE ══════════════════════════════ --}}
    <div id="tab-attendance" class="tab-pane fade-in">

        {{-- Summary stat row --}}
        @php
            $attNotArrived  = $assignments->where('attendance_status','not_arrived')->count();
            $attCheckedIn   = $assignments->where('attendance_status','checked_in')->count();
            $attOnBreak     = $assignments->where('attendance_status','on_break')->count();
            $attCheckedOut  = $assignments->where('attendance_status','checked_out')->count();
        @endphp

        <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <div style="flex:1;min-width:110px;background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--grey-dark);padding:.75rem 1rem;text-align:center;box-shadow:var(--shadow-sm);">
                <div style="font-size:26px;font-weight:bold;color:var(--grey-dark);">{{ $attNotArrived }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">Not Arrived</div>
            </div>
            <div style="flex:1;min-width:110px;background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--green);padding:.75rem 1rem;text-align:center;box-shadow:var(--shadow-sm);">
                <div style="font-size:26px;font-weight:bold;color:var(--green);">{{ $attCheckedIn }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">On Site</div>
            </div>
            <div style="flex:1;min-width:110px;background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--amber-bdr);padding:.75rem 1rem;text-align:center;box-shadow:var(--shadow-sm);">
                <div style="font-size:26px;font-weight:bold;color:var(--amber);">{{ $attOnBreak }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">On Break</div>
            </div>
            <div style="flex:1;min-width:110px;background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:.75rem 1rem;text-align:center;box-shadow:var(--shadow-sm);">
                <div style="font-size:26px;font-weight:bold;color:var(--navy);">{{ $attCheckedOut }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">Checked Out</div>
            </div>
        </div>

        {{-- Auto-refresh notice --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;">
            <div style="font-size:12px;color:var(--text-muted);">
                Page auto-refreshes every 30 seconds · Last updated: <span id="attend-last-update">{{ now()->format('H:i:s') }}</span>
            </div>
            <button class="btn btn-ghost" onclick="location.reload()" style="padding:.32rem .85rem;font-size:11px;">↻ Refresh Now</button>
        </div>

        {{-- Operator attendance cards --}}
        @php
            $attGroups = [
                'checked_in'  => ['✓ On Site',        'green',    $assignments->where('attendance_status','checked_in')],
                'on_break'    => ['⏸ On Break',       'amber',    $assignments->where('attendance_status','on_break')],
                'not_arrived' => ['⏳ Not Yet Arrived','grey-dark',$assignments->where('attendance_status','not_arrived')],
                'checked_out' => ['⏹ Checked Out',    'navy',     $assignments->where('attendance_status','checked_out')],
            ];
        @endphp

        @foreach ($attGroups as $attStatus => [$attLabel, $attColour, $attGroup])
        @if ($attGroup->isNotEmpty())

        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--{{ $attColour }});margin:1rem 0 .6rem;display:flex;align-items:center;gap:.5rem;">
            {{ $attLabel }} ({{ $attGroup->count() }})
            <span style="flex:1;height:1px;background:var(--grey-mid);display:inline-block;"></span>
        </div>

        @foreach ($attGroup as $asgn)
        @php
            $log        = array_reverse($asgn->attendance_log ?? []);
            $dutyMins   = $asgn->dutyMinutes();
            $breakMins  = $asgn->totalBreakMinutes();
            $checkInTime = null;
            $checkOutTime = null;
            foreach (array_reverse($log) as $entry) {
                if ($entry['type'] === 'check_in' && !$checkInTime) $checkInTime = $entry['time'];
                if ($entry['type'] === 'check_out') $checkOutTime = $entry['time'];
            }
            $dutyDisplay  = $dutyMins !== null
                ? (floor($dutyMins/60) > 0 ? floor($dutyMins/60).'h ' : '') . ($dutyMins%60).'m'
                : '—';
            $breakDisplay = $breakMins > 0
                ? (floor($breakMins/60) > 0 ? floor($breakMins/60).'h ' : '') . ($breakMins%60).'m'
                : '—';
        @endphp

        <div style="background:var(--white);border:1px solid var(--grey-mid);border-left:4px solid var(--{{ $attColour }});margin-bottom:.5rem;box-shadow:var(--shadow-sm);">

            {{-- Card header --}}
            <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;">
                {{-- Avatar --}}
                @php
                    $words = explode(' ', $asgn->user->name);
                    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words,0,2)));
                @endphp
                <div style="width:36px;height:36px;background:var(--navy-faint);border:2px solid var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:bold;color:var(--navy);">{{ $initials }}</div>

                {{-- Info --}}
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:bold;color:var(--text);">
                        {!! pii($asgn->user->name, $asgn->user->piiVisible()) !!}
                        @if ($asgn->callsign)
                            <span style="font-size:11px;color:var(--text-muted);font-weight:normal;">({!! pii($asgn->callsign, $asgn->user->piiVisible()) !!})</span>
                        @endif
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:1px;">{{ $asgn->role ?: 'No role' }}</div>
                </div>

                {{-- Attendance badge --}}
                <span style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border:1px solid;
                    {{ match($asgn->attendance_status) {
                        'checked_in'  => 'background:var(--green-bg);border-color:var(--green-bdr);color:var(--green);',
                        'on_break'    => 'background:var(--amber-bg);border-color:var(--amber-bdr);color:var(--amber);',
                        'checked_out' => 'background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);',
                        default       => 'background:var(--grey);border-color:var(--grey-mid);color:var(--grey-dark);',
                    } }}
                ">{{ $asgn->attendanceLabel() }}</span>
            </div>

            {{-- Stats row --}}
            @if ($asgn->attendance_status !== 'not_arrived')
            <div style="display:flex;border-top:1px solid var(--grey-mid);">
                <div style="flex:1;padding:.5rem .75rem;text-align:center;border-right:1px solid var(--grey-mid);">
                    <div style="font-size:14px;font-weight:bold;color:var(--green);">
                        {{ $checkInTime ? \Carbon\Carbon::parse($checkInTime)->format('H:i') : '—' }}
                    </div>
                    <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:1px;">Checked In</div>
                </div>
                <div style="flex:1;padding:.5rem .75rem;text-align:center;border-right:1px solid var(--grey-mid);">
                    <div style="font-size:14px;font-weight:bold;color:var(--navy);">{{ $dutyDisplay }}</div>
                    <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:1px;">Duty Time</div>
                </div>
                <div style="flex:1;padding:.5rem .75rem;text-align:center;border-right:1px solid var(--grey-mid);">
                    <div style="font-size:14px;font-weight:bold;color:{{ $breakMins > 0 ? 'var(--amber)' : 'var(--grey-dark)' }};">{{ $breakDisplay }}</div>
                    <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:1px;">On Break</div>
                </div>
                @if ($checkOutTime)
                <div style="flex:1;padding:.5rem .75rem;text-align:center;">
                    <div style="font-size:14px;font-weight:bold;color:var(--navy);">{{ \Carbon\Carbon::parse($checkOutTime)->format('H:i') }}</div>
                    <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:1px;">Checked Out</div>
                </div>
                @endif
            </div>
            @endif

            {{-- Log entries (collapsed toggle) --}}
            @if (!empty($asgn->attendance_log))
            <div style="border-top:1px solid var(--grey-mid);">
                <button type="button"
                        onclick="toggleAttLog({{ $asgn->id }})"
                        style="width:100%;text-align:left;padding:.45rem 1rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);background:var(--grey);border:none;cursor:pointer;font-family:var(--font);display:flex;align-items:center;justify-content:space-between;">
                    <span>Activity Log ({{ count($asgn->attendance_log) }} {{ count($asgn->attendance_log) === 1 ? 'entry' : 'entries' }})</span>
                    <span id="attlog-icon-{{ $asgn->id }}">⌄</span>
                </button>
                <div id="attlog-{{ $asgn->id }}" style="display:none;">
                    @foreach (array_reverse($asgn->attendance_log) as $entry)
                    @php
                        $typeLabels = [
                            'check_in'    => ['✓ Checked In',    'var(--green)'],
                            'break_start' => ['⏸ Break Started', 'var(--amber)'],
                            'break_end'   => ['▶ Break Ended',   'var(--green)'],
                            'check_out'   => ['⏹ Checked Out',   'var(--navy)'],
                        ];
                        [$logLabel, $logColour] = $typeLabels[$entry['type']] ?? [$entry['type'], 'var(--text-muted)'];
                    @endphp
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem 1rem;border-bottom:1px solid var(--grey-mid);">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $logColour }};flex-shrink:0;"></div>
                        <div style="flex:1;min-width:0;">
                            <span style="font-size:12px;font-weight:bold;color:{{ $logColour }};">{{ $logLabel }}</span>
                            @if (!empty($entry['note']))
                                <span style="font-size:11px;color:var(--text-muted);margin-left:.4rem;">· {{ $entry['note'] }}</span>
                            @endif
                        </div>
                        <div style="font-size:11px;font-weight:bold;color:var(--text-muted);white-space:nowrap;flex-shrink:0;">
                            {{ \Carbon\Carbon::parse($entry['time'])->format('H:i') }}
                            <span style="font-size:10px;font-weight:normal;">{{ \Carbon\Carbon::parse($entry['time'])->format('D j M') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Brief link + admin reset for admin --}}
            @if ($asgn->briefing_token)
            <div style="padding:.45rem 1rem;border-top:1px solid var(--grey-mid);background:#fafbfc;display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
                <span style="font-size:11px;color:var(--text-muted);">
                    Brief page:
                    <a href="{{ $asgn->briefingUrl() }}" target="_blank" style="color:var(--navy);font-weight:bold;text-decoration:none;">
                        {{ $asgn->briefingUrl() }}
                    </a>
                </span>
                @if (!$asgn->briefing_sent)
                <span style="font-size:10px;color:var(--amber);font-weight:bold;text-transform:uppercase;letter-spacing:.06em;">✉ Brief not sent</span>
                @else
                <span style="font-size:10px;color:var(--green);font-weight:bold;text-transform:uppercase;letter-spacing:.06em;">✉ Brief sent {{ $asgn->briefing_sent_at?->format('j M H:i') }}</span>
                @endif
            </div>
            @endif

            {{-- Admin attendance reset --}}
            @if ($asgn->attendance_status !== 'not_arrived')
            <div style="padding:.45rem 1rem;border-top:1px solid var(--grey-mid);background:var(--red-faint);">
                <form method="POST"
                      action="{{ route('admin.events.assignments.reset-attendance', $asgn) }}"
                      onsubmit="return confirm('Reset attendance for {{ addslashes($asgn->user->name) }}? This will clear all check-in times and logs.')">
                    @csrf
                    <button type="submit"
                            style="width:100%;padding:.4rem .75rem;background:none;border:1px solid rgba(200,16,46,.3);color:var(--red);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;font-family:var(--font);display:flex;align-items:center;justify-content:center;gap:.4rem;">
                        ↺ Reset Attendance
                    </button>
                </form>
            </div>
            @endif

        </div>
        @endforeach
        @endif
        @endforeach

        @if ($assignments->isEmpty())
        <div class="panel">
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <div class="empty-text">No operators assigned yet.</div>
            </div>
        </div>
        @endif

    </div>

</div>

{{-- ════════════════ BULK ACTION BAR ════════════════ --}}
<div class="bulk-bar" id="bulkBar">
    <span class="bulk-bar-label" id="bulk-count-label">0 selected</span>
    <select class="bulk-status-select" id="bulk-status-select">
        <option value="">Change status to…</option>
        <option value="confirmed">Confirmed</option>
        <option value="standby">Standby</option>
        <option value="pending">Pending</option>
        <option value="declined">Declined</option>
    </select>
    <button class="bulk-select-btn" onclick="applyBulkStatus()">Apply</button>
    <button class="bulk-select-btn" onclick="selectAll()">Select All</button>
    <button class="bulk-select-btn" onclick="clearSelection()">Clear</button>
    <button class="bulk-select-btn" onclick="toggleBulkMode()" style="margin-left:auto;color:rgba(255,255,255,.5);">✕ Exit Bulk Mode</button>
</div>

{{-- ════════════════ ADD / EDIT MODAL ════════════════ --}}
<div class="modal-backdrop" id="assignModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title" id="modal-title">Assign Member</div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form method="POST" id="assignForm" onsubmit="serializeShifts()">
            @csrf
            <span id="method-spoofed"></span>
            <input type="hidden" name="shifts_json" id="shifts-json-input">
            <input type="hidden" name="equipment_items_json" id="equip-json-input">

            <div class="modal-body">
                {{-- Member select --}}
                <div id="member-select-row">
                    <div class="ff" style="margin-bottom:.9rem;">
                        <label>Member *</label>
                        <select name="user_ids[]" id="modal-user" multiple size="8" style="width:100%;border:1px solid var(--grey-mid);padding:.35rem .5rem;font-family:var(--font);font-size:13px;outline:none;background:var(--white);">
                            @php
                                $availGroup   = $availableMembers->whereNotIn('id', $unavailableUserIds ?? []);
                                $unavailGroup = $availableMembers->whereIn('id', $unavailableUserIds ?? []);
                            @endphp
                            @if ($unavailGroup->isNotEmpty())
                            <optgroup label="✓ Available ({{ $availGroup->count() }})">
                            @endif
                            @foreach ($availGroup as $m)
                                <option value="{{ $m->id }}" data-callsign="{!! pii($m->callsign, $m->piiVisible()) !!}">{!! pii($m->name, $m->piiVisible()) !!}{{ $m->callsign ? ' ('.$m->callsign.')' : '' }}</option>
                            @endforeach
                            @if ($unavailGroup->isNotEmpty())
                            </optgroup>
                            <optgroup label="⚠ Unavailable on this date ({{ $unavailGroup->count() }})">
                            @foreach ($unavailGroup as $m)
                                <option value="{{ $m->id }}" data-callsign="{!! pii($m->callsign, $m->piiVisible()) !!}" data-unavailable="1"
                                        style="color:#8a5c00;">
                                    ⚠ {!! pii($m->name, $m->piiVisible()) !!}{{ $m->callsign ? ' ('.$m->callsign.')' : '' }} — unavailable
                                </option>
                            @endforeach
                            </optgroup>
                            @endif
                        </select>
                        @if (($unavailableUserIds ?? collect())->isNotEmpty())
                        <div style="font-size:11px;color:#8a5c00;margin-top:.35rem;display:flex;align-items:center;gap:.3rem;">
                            ⚠ {{ $unavailableUserIds->count() }} member{{ $unavailableUserIds->count() !== 1 ? 's have' : ' has' }} marked themselves unavailable for this event's date.
                        </div>
                        @endif
                    </div>
                </div>

                <div class="section-divider">Role &amp; Callsign</div>
                <div class="frow">
                    <div class="ff"><label>Role / Position</label><input type="text" name="role" id="modal-role" placeholder="Net Control, Field Operator…"></div>
                    <div class="ff"><label>Callsign</label><input type="text" name="callsign" id="modal-callsign" placeholder="M0ABC"></div>
                </div>

                <div class="section-divider">Frequencies &amp; Channels</div>
                <div class="frow one" style="margin-bottom:.4rem;">
                    <div class="ff"><label>Channel / Net Label</label><input type="text" name="channel_label" id="modal-channel-label" placeholder="e.g. Net 1, Control Ch, Repeater Alpha…"></div>
                </div>
                <div class="channel-block">
                    <div class="channel-block-head primary-head">★ Primary Frequency</div>
                    <div class="channel-block-body">
                        <div class="channel-freq-row">
                            <div class="ff"><label>Frequency</label><input type="text" name="frequency" id="modal-frequency" placeholder="145.500"></div>
                            <div class="ff"><label>Mode</label><select name="mode" id="modal-mode"><option value="FM">FM</option><option value="AM">AM</option><option value="SSB">SSB</option><option value="DMR">DMR</option><option value="C4FM">C4FM</option><option value="Other">Other</option></select></div>
                            <div class="ff"><label>CTCSS</label><input type="text" name="ctcss_tone" id="modal-ctcss" placeholder="94.8" style="width:80px;"></div>
                        </div>
                    </div>
                </div>
                <div class="channel-block">
                    <div class="channel-block-head secondary-head">Secondary Frequency</div>
                    <div class="channel-block-body">
                        <div class="channel-freq-row">
                            <div class="ff"><label>Frequency</label><input type="text" name="secondary_frequency" id="modal-sec-freq" placeholder="Optional"></div>
                            <div class="ff"><label>Mode</label><select name="secondary_mode" id="modal-sec-mode"><option value="FM">FM</option><option value="AM">AM</option><option value="SSB">SSB</option><option value="DMR">DMR</option><option value="C4FM">C4FM</option><option value="Other">Other</option></select></div>
                            <div class="ff"><label>CTCSS</label><input type="text" name="secondary_ctcss" id="modal-sec-ctcss" placeholder="" style="width:80px;"></div>
                        </div>
                    </div>
                </div>
                <div class="channel-block" style="margin-bottom:.7rem;">
                    <div class="channel-block-head fallback-head">Fallback Frequency</div>
                    <div class="channel-block-body">
                        <div class="channel-freq-row">
                            <div class="ff"><label>Frequency</label><input type="text" name="fallback_frequency" id="modal-fal-freq" placeholder="Optional"></div>
                            <div class="ff"><label>Mode</label><select name="fallback_mode" id="modal-fal-mode"><option value="FM">FM</option><option value="AM">AM</option><option value="SSB">SSB</option><option value="DMR">DMR</option><option value="C4FM">C4FM</option><option value="Other">Other</option></select></div>
                            <div class="ff"><label>CTCSS</label><input type="text" name="fallback_ctcss" id="modal-fal-ctcss" placeholder="" style="width:80px;"></div>
                        </div>
                    </div>
                </div>

                <div class="section-divider">Position</div>
                @if(!empty($event->event_pois))
                <div class="frow" style="margin-bottom:.6rem;">
                    <div class="ff" style="flex:2;">
                        <label>📍 Link to Event POI Checkpoint</label>
                        <select id="modal-poi-link" onchange="applyPoiLink(this.value)"
                                style="width:100%;padding:.45rem .7rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:13px;font-family:var(--font);background:white;color:var(--text);">
                            <option value="">— Select a checkpoint to auto-fill location —</option>
                            @foreach($event->event_pois as $poi)
                                <option value="{{ json_encode(['name'=>$poi['name'],'lat'=>$poi['lat'],'lng'=>$poi['lng'],'grid_ref'=>$poi['grid_ref']??'','w3w'=>$poi['w3w']??'']) }}">
                                    {{ $poi['name'] ?? 'Unnamed POI' }}
                                    @if(!empty($poi['grid_ref'])) — {{ $poi['grid_ref'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <div class="frow">
                    <div class="ff"><label>Location Name</label><input type="text" name="location_name" id="modal-locname" placeholder="Checkpoint Alpha"></div>
                    <div class="ff"><label>OS Grid Reference</label><input type="text" name="grid_ref" id="modal-grid" placeholder="SJ394905" oninput="scheduleGridLookup(this.value)" style="font-weight:bold;letter-spacing:.04em;"></div>
                </div>

                {{-- Pin picker map --}}
                <div style="margin-bottom:.7rem;">
                    <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.35rem;">
                        Position — Click map to place pin, or enter coordinates manually
                    </div>
                    <div id="modal-map-picker" style="height:220px;width:100%;border:1px solid var(--grey-mid);background:var(--grey);"></div>
                    <div style="display:flex;gap:.5rem;margin-top:.4rem;">
                        <div class="ff" style="flex:1;">
                            <label>Latitude</label>
                            <input type="number" step="any" name="lat" id="modal-lat" placeholder="53.408"
                                   oninput="syncPickerFromFields()">
                        </div>
                        <div class="ff" style="flex:1;">
                            <label>Longitude</label>
                            <input type="number" step="any" name="lng" id="modal-lng" placeholder="-2.991"
                                   oninput="syncPickerFromFields()">
                        </div>
                        <div style="display:flex;align-items:flex-end;padding-bottom:1px;">
                            <button type="button"
                                    onclick="clearPickerPin()"
                                    style="height:32px;padding:0 .65rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;border:1px solid var(--grey-mid);background:var(--white);color:var(--red);cursor:pointer;font-family:var(--font);white-space:nowrap;">
                                ✕ Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="frow">
                    <div class="ff"><label>What3Words</label><input type="text" name="what3words" id="modal-w3w" placeholder="filled.count.soap"></div>
                    <div class="ff"><label>Coverage Radius (metres)</label><input type="number" name="coverage_radius_m" id="modal-radius" placeholder="0" min="0" step="100"></div>
                </div>

                <div class="section-divider">Shifts &amp; Schedule</div>
                <div class="frow">
                    <div class="ff"><label>Report Time</label><input type="time" name="report_time" id="modal-report"></div>
                    <div class="ff"><label>Depart Time</label><input type="time" name="depart_time" id="modal-depart"></div>
                </div>

                {{-- SHIFT BUILDER --}}
                <div class="shift-builder">
                    <div class="shift-builder-head">
                        <span class="shift-builder-title">Shifts &amp; Breaks</span>
                        <div class="preset-chips">
                            <button type="button" class="preset-chip" onclick="applyPreset('morning')">Morning</button>
                            <button type="button" class="preset-chip" onclick="applyPreset('afternoon')">Afternoon</button>
                            <button type="button" class="preset-chip" onclick="applyPreset('fullday')">Full Day</button>
                            <button type="button" class="preset-chip" onclick="applyPreset('split')">Split Shift</button>
                            <button type="button" class="preset-chip" onclick="fillShiftFromReportDepart()" style="color:#0369a1;border-color:rgba(3,105,161,.3);background:rgba(3,105,161,.06);">⏱ Report→Depart</button>
                            <button type="button" class="preset-chip" onclick="clearShifts()" style="color:var(--red);border-color:rgba(200,16,46,.3);">Clear</button>
                        </div>
                    </div>
                    <div class="shift-rows" id="shift-rows">
                        {{-- JS populates this --}}
                    </div>
                    <div class="shift-builder-footer">
                        <div style="display:flex;gap:.4rem;">
                            <button type="button" class="shift-add-btn shift-add-shift" onclick="addShiftRow('shift')">+ Shift</button>
                            <button type="button" class="shift-add-btn shift-add-break" onclick="addShiftRow('break')">+ Break</button>
                        </div>
                        <span class="shift-hours-display" id="shift-hours-display"></span>
                    </div>
                </div>

                <div class="section-divider">Equipment</div>
                <div class="equip-grid" id="equip-grid">
                    @php $equipChoices = ['Handheld Radio (HT)','Spare Battery / Power Bank','Hi-vis Vest','Rain Jacket','Logbook','Antenna / Mast','Laptop / Tablet','Vehicle-mounted Radio','First Aid Kit','Headset / Earpiece','Torch / Head Torch','Water / Snacks']; @endphp
                    @foreach ($equipChoices as $choice)
                    <label class="equip-item">
                        <input type="checkbox" class="equip-cb" value="{{ $choice }}">
                        {{ $choice }}
                    </label>
                    @endforeach
                </div>
                <div class="equip-custom-row">
                    <input type="text" class="equip-custom-input" id="equip-custom-input" placeholder="Add custom item…">
                    <button type="button" class="btn btn-ghost" style="padding:.28rem .65rem;font-size:11px;" onclick="addCustomEquip()">+ Add</button>
                </div>

                <div class="section-divider">Status &amp; Logistics</div>
                <div class="frow">
                    <div class="ff"><label>Status</label><select name="status" id="modal-status"><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="standby">Standby</option><option value="declined">Declined</option></select></div>
                    <div class="ff"><label>Vehicle Registration</label><input type="text" name="vehicle_reg" id="modal-vreg" placeholder="AB12 CDE"></div>
                </div>
                <div class="frow">
                    <div class="ff ff-check"><input type="checkbox" name="has_vehicle" id="modal-hasveh" value="1"><label for="modal-hasveh">Has vehicle available</label></div>
                    <div class="ff ff-check"><input type="checkbox" name="first_aid_trained" id="modal-fa" value="1"><label for="modal-fa">First aid trained</label></div>
                </div>

                <div class="section-divider">Emergency Contact</div>
                <div class="frow">
                    <div class="ff"><label>Contact Name</label><input type="text" name="emergency_contact_name" id="modal-ec-name" placeholder="Full name of next of kin / emergency contact"></div>
                    <div class="ff"><label>Contact Phone</label><input type="text" name="emergency_contact_phone" id="modal-ec-phone" placeholder="07700 900000"></div>
                </div>

                <div class="section-divider">Notes</div>
                <div class="frow one">
                    <div class="ff"><label>Briefing Notes <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(shown on briefing sheet)</small></label><textarea name="briefing_notes" id="modal-notes" placeholder="Notes for this operator…"></textarea></div>
                </div>
                <div class="frow one">
                    <div class="ff"><label>Medical Notes <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(private — emergency contacts page only)</small></label><textarea name="medical_notes" id="modal-medical" placeholder="Allergies, medication, conditions relevant to operational safety…"></textarea></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="modal-submit">+ Assign</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <div style="margin-left:auto;font-size:11px;color:var(--text-muted);">* Required fields</div>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════ DATA + SCRIPTS ════════════════ --}}
@php
$assignmentsJs2 = $assignments->map(function($a) use ($normaliseShifts, $calcHours) {
    $shifts = $normaliseShifts($a);
    $equipItems = $a->equipment_items ?? null;
    if (is_string($equipItems)) $equipItems = json_decode($equipItems, true);
    if (!is_array($equipItems)) $equipItems = [];
    return [
        'id'             => $a->id,
        'name'           => $a->user->name,
        'callsign'       => $a->callsign,
        'role'           => $a->role,
        'frequency'      => $a->frequency,
        'mode'           => $a->mode,
        'ctcss_tone'     => $a->ctcss_tone,
        'channel_label'  => $a->channel_label ?? null,
        'secondary_frequency' => $a->secondary_frequency ?? null,
        'secondary_mode'      => $a->secondary_mode ?? null,
        'secondary_ctcss'     => $a->secondary_ctcss ?? null,
        'fallback_frequency'  => $a->fallback_frequency ?? null,
        'fallback_mode'       => $a->fallback_mode ?? null,
        'fallback_ctcss'      => $a->fallback_ctcss ?? null,
        'location_name'  => $a->location_name,
        'grid_ref'       => $a->grid_ref,
        'what3words'     => $a->what3words,
        'lat'            => $a->lat ? (float)$a->lat : null,
        'lng'            => $a->lng ? (float)$a->lng : null,
        'coverage_radius_m' => (int)($a->coverage_radius_m ?? 0),
        'report_time'    => $a->report_time ? substr($a->report_time,0,5) : null,
        'depart_time'    => $a->depart_time ? substr($a->depart_time,0,5) : null,
        'shifts'         => $shifts,
        'total_hours'    => $calcHours($shifts),
        'status'         => $a->status,
        'status_label'   => $a->statusLabel(),
        'marker_colour'  => $a->markerColour(),
        'has_vehicle'    => (bool)$a->has_vehicle,
        'vehicle_reg'    => $a->vehicle_reg,
        'first_aid_trained' => (bool)$a->first_aid_trained,
        'equipment_items' => $equipItems,
        'briefing_notes'  => $a->briefing_notes,
        'medical_notes'   => $a->medical_notes ?? null,
        'emergency_contact_name'  => $a->emergency_contact_name ?? null,
        'emergency_contact_phone' => $a->emergency_contact_phone ?? null,
        'briefing_sent'   => (bool)$a->briefing_sent,
    ];
})->values();
@endphp

<script>
const AD = @json($assignmentsJs2);
const EVENT_CENTRE = { lat: {{ $centLat }}, lng: {{ $centLng }} };
@php
    $eventPolygon = null;
    if ($event->event_polygon) {
        $eventPolygon = is_array($event->event_polygon)
            ? $event->event_polygon
            : json_decode($event->event_polygon, true);
    }
@endphp
const EVENT_POLYGON = {!! $eventPolygon ? json_encode($eventPolygon) : 'null' !!};
@php
    $eventPois = null;
    if ($event->event_pois) {
        $eventPois = is_array($event->event_pois) ? $event->event_pois : json_decode($event->event_pois, true);
    }
@endphp
const EVENT_POIS = {!! $eventPois ? json_encode($eventPois) : '[]' !!};
@php
    $eventRoute = null;
    if ($event->event_route) {
        $rawRoute = is_array($event->event_route) ? $event->event_route : json_decode($event->event_route, true);
        // Normalise to array of {id,name,geometry} — handle legacy single geometry
        if ($rawRoute && isset($rawRoute['type'])) {
            $eventRoute = [['id' => 'r-legacy', 'name' => 'Event Route', 'geometry' => $rawRoute]];
        } elseif (is_array($rawRoute) && !empty($rawRoute)) {
            $eventRoute = $rawRoute;
        }
    }
@endphp
const EVENT_ROUTES = {!! $eventRoute ? json_encode($eventRoute) : '[]' !!};
const ROUTES = {
    store:      "{{ route('admin.events.assignments.store', $event->id) }}",
    update:     "{{ url('admin/assignments') }}/",
    position:   "{{ url('admin/assignments') }}/",
    bulkStatus: "{{ url('admin/events/'.$event->id.'/assignments/bulk-status') }}",
    bulkFill:   "{{ url('admin/events/'.$event->id.'/assignments/bulk-fill') }}",
};
const CSRF   = "{{ csrf_token() }}";
const EVENT_ID = {{ $event->id }};
const TL_START = {{ $tlStart ?? 6 }};
const TL_END   = {{ $tlEnd   ?? 20 }};

/* ══ TABS ══ */
function switchTab(n) {
    document.querySelectorAll('.tab-pane').forEach(e=>e.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(e=>e.classList.remove('active'));
    document.getElementById('tab-'+n).classList.add('active');
    document.getElementById('tabbtn-'+n).classList.add('active');
    if (n==='map'&&!mapInitialised){mapInitialised=true;initMap();}
    else if(n==='map'&&theMap){setTimeout(()=>theMap.invalidateSize(),100);}
    if (n==='schedule'){setTimeout(renderCoverageHeat,50);runGapDetection();}
    if (n==='briefing'){initBriefingVersion();}
    if (n==='attendance'){startAttendanceRefresh();}
}

/* ══ COVERAGE GAP DETECTION ══ */
function toMins(t) {
    if (!t) return null;
    const p = t.split(':');
    return parseInt(p[0]) * 60 + parseInt(p[1] || 0);
}

function runGapDetection() {
    const el = document.getElementById('coverage-warnings');
    if (!el) return;
    const active = AD.filter(op => op.status !== 'declined');
    if (!active.length) { el.innerHTML = ''; return; }
    // Collect all shift intervals
    const intervals = [];
    active.forEach(op => {
        (op.shifts || []).forEach(s => {
            if (s.type === 'shift' && s.start && s.end) {
                intervals.push({ start: toMins(s.start), end: toMins(s.end), op });
            }
        });
    });
    if (!intervals.length) { el.innerHTML = ''; return; }
    // Find overall event window
    const minStart = Math.min(...intervals.map(i => i.start));
    const maxEnd   = Math.max(...intervals.map(i => i.end));
    // Find gaps: walk minute by minute (in 15-min steps for perf)
    const gaps = [];
    let inGap = false; let gapStart = null;
    for (let t = minStart; t <= maxEnd; t += 15) {
        const covered = intervals.some(i => i.start <= t && i.end >= t);
        if (!covered && !inGap) { inGap = true; gapStart = t; }
        if (covered && inGap) { inGap = false; gaps.push({ start: gapStart, end: t }); }
    }
    if (inGap) gaps.push({ start: gapStart, end: maxEnd });
    // Find overlaps: any two operators at same role/location with overlapping shifts
    const overlaps = [];
    for (let i = 0; i < intervals.length; i++) {
        for (let j = i + 1; j < intervals.length; j++) {
            const a = intervals[i], b = intervals[j];
            if (a.op.role && b.op.role && a.op.role === b.op.role) {
                const oStart = Math.max(a.start, b.start);
                const oEnd   = Math.min(a.end,   b.end);
                if (oEnd > oStart) overlaps.push({ a: a.op, b: b.op, start: oStart, end: oEnd, role: a.op.role });
            }
        }
    }
    const toHM = m => String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0');
    let html = '';
    if (!gaps.length && !overlaps.length) {
        html = '<div class="coverage-warning ok">✓ No coverage gaps or role conflicts detected.</div>';
    }
    gaps.forEach(g => {
        html += `<div class="coverage-warning gap">⚠ Coverage gap: ${toHM(g.start)} – ${toHM(g.end)} — no operators on shift.</div>`;
    });
    overlaps.forEach(o => {
        html += `<div class="coverage-warning overlap">⚠ Role overlap: ${o.a.name} &amp; ${o.b.name} both in "${o.role}" ${toHM(o.start)}–${toHM(o.end)}.</div>`;
    });
    el.innerHTML = html;
}

/* ══ COVERAGE HEAT ROW ══ */
function renderCoverageHeat() {
    const el = document.getElementById('coverage-heat');
    if (!el) return;
    const span = TL_END - TL_START;
    const step = 0.25; // 15-min slots
    const slots = Math.floor(span / step);
    let html = '';
    for (let i = 0; i < slots; i++) {
        const t = TL_START + i * step;
        const count = AD.filter(op => op.status !== 'declined').reduce((acc, op) => {
            const inShift = (op.shifts||[]).some(s => s.type==='shift' && s.start && s.end && toMins(s.start)/60 <= t && toMins(s.end)/60 >= t);
            return acc + (inShift ? 1 : 0);
        }, 0);
        const bg = count === 0 ? 'rgba(200,16,46,.25)' : count === 1 ? 'rgba(138,92,0,.25)' : 'rgba(26,107,60,.25)';
        html += `<div class="tl-cov-cell" style="background:${bg};" title="${String(Math.floor(t)).padStart(2,'0')}:${count*0 || (t%1*60<10?'0':'')+(t%1*60|0)} — ${count} operator(s)"></div>`;
    }
    el.innerHTML = html;
}

/* ══ MAP ══ */
var mapInitialised = false;
var _gridTimer = null;
var _gridMarker = null;
function scheduleGridLookup(val) {
    clearTimeout(_gridTimer);
    val = val.replace(/\s/g,'');
    if (val.length < 6 || !/^[A-Za-z]{2}\d+$/.test(val)) return;
    _gridTimer = setTimeout(function() { doGridLookup(val); }, 700);
}
function doGridLookup(val) {
    const latEl = document.getElementById('modal-lat');
    const lngEl = document.getElementById('modal-lng');
    fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(val) + '&format=json&countrycodes=gb&limit=1', {
        headers: { 'Accept': 'application/json', 'User-Agent': 'RAYNET-ROCK/1.0' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data || !data.length) return;
        const lat = parseFloat(data[0].lat);
        const lng = parseFloat(data[0].lon);
        if (isNaN(lat) || isNaN(lng)) return;
        if (latEl) latEl.value = lat.toFixed(6);
        if (lngEl) lngEl.value = lng.toFixed(6);
        if (!theMap) return;
        if (_gridMarker) theMap.removeLayer(_gridMarker);
        _gridMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: '<div style="width:22px;height:22px;background:#003366;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.5);"></div>',
                iconSize: [22,22], iconAnchor: [11,11]
            }),
            draggable: true,
            title: val
        }).addTo(theMap);
        _gridMarker.on('dragend', function(e) {
            const p = e.target.getLatLng();
            if (latEl) latEl.value = p.lat.toFixed(6);
            if (lngEl) lngEl.value = p.lng.toFixed(6);
        });
        theMap.setView([lat, lng], 16);
    })
    .catch(function() {});
}

var theMap, tileStreet, tileSat, satelliteOn = false;
var markers = {}, circles = {}, circlesVisible = false, gpxLayer = null;
var eventPolygonLayer = null, eventMaskLayer = null;
var eventRouteLayers  = [];   // keeps references to all rendered route layers for fitBounds

function initMap() {
    theMap = L.map('leaflet-map',{center:[EVENT_CENTRE.lat,EVENT_CENTRE.lng],zoom:13});
    tileStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap',maxZoom:19}).addTo(theMap);
    tileSat    = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{attribution:'© Esri',maxZoom:19});

    if (EVENT_POLYGON) { addEventPolygon(); }
    if (EVENT_ROUTES && EVENT_ROUTES.length) { EVENT_ROUTES.forEach(r => addEventRoute(r)); }
    if (EVENT_POIS && EVENT_POIS.length) { addEventPois(); }

    AD.forEach(op => { if (op.lat && op.lng) addMarker(op); });

    // Hook new features
    theMap.on('mousemove', handleBearingHover);
    setupContextMenu();
    setTimeout(startAttendancePoll, 2000);
    updateHud();

    const withPos = AD.filter(o=>o.lat&&o.lng);
    if (EVENT_POLYGON) {
        try {
            theMap.fitBounds(L.geoJSON(EVENT_POLYGON).getBounds(), {padding:[40,40]});
        } catch(e) {
            if (withPos.length > 1) theMap.fitBounds(withPos.map(o=>[o.lat,o.lng]),{padding:[40,40]});
        }
    } else if (EVENT_ROUTES && EVENT_ROUTES.length > 0 && eventRouteLayers.length > 0) {
        try {
            const group = L.featureGroup(eventRouteLayers);
            theMap.fitBounds(group.getBounds(), {padding:[50,50]});
        } catch(e) {}
    } else if (withPos.length > 1) {
        theMap.fitBounds(withPos.map(o=>[o.lat,o.lng]),{padding:[40,40]});
    }
}

/**
 * Adds the site boundary polygon (dashed navy outline, faint fill) plus an
 * inverse "donut" mask layer that dims the world outside the polygon at 42%
 * opacity, drawing attention to the site area.
 */
function addEventPolygon() {
    if (!EVENT_POLYGON || !EVENT_POLYGON.coordinates) return;

    eventPolygonLayer = L.geoJSON(
        { type:'Feature', geometry:EVENT_POLYGON },
        { style:{ color:'#003366', weight:2.5, opacity:0.85, fillColor:'#003366', fillOpacity:0.06, dashArray:'6 3' } }
    ).addTo(theMap);

    eventPolygonLayer.bindTooltip(
        '<strong style="font-size:11px;letter-spacing:.04em;text-transform:uppercase;">Event Site Boundary</strong>',
        { sticky:true }
    );

    // Inverse mask — world rectangle with site polygon cut out as a hole
    const worldRing = [[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];
    const isMulti   = EVENT_POLYGON.type === 'MultiPolygon';
    const polys     = isMulti ? EVENT_POLYGON.coordinates : [EVENT_POLYGON.coordinates];

    polys.forEach(function(rings) {
        const mask = L.geoJSON(
            { type:'Feature', geometry:{ type:'Polygon', coordinates:[worldRing, rings[0]] } },
            { style:{ color:'transparent', weight:0, fillColor:'#001f40', fillOpacity:0.42 }, interactive:false }
        ).addTo(theMap);
        if (!eventMaskLayer) eventMaskLayer = mask;
    });
}

/**
 * Renders a single route object {id, name, geometry} as an animated
 * purple polyline with white shadow, direction arrow, and START/FINISH labels.
 * Called once per route in EVENT_ROUTES.
 */
function addEventRoute(routeObj) {
    const geometry = routeObj.geometry || routeObj; // handle legacy plain geometry
    if (!geometry || !geometry.coordinates) return;

    const routeName = routeObj.name || 'Event Route';

    // ── White shadow for legibility on satellite ──────────────────────────────
    L.geoJSON(
        { type: 'Feature', geometry },
        { style: { color: '#fff', weight: 8, opacity: 0.3, lineCap: 'round', lineJoin: 'round' }, interactive: false }
    ).addTo(theMap);

    // ── Solid base fills dash gaps ────────────────────────────────────────────
    L.geoJSON(
        { type: 'Feature', geometry },
        { style: { color: '#7c3aed', weight: 4, opacity: 0.35, lineCap: 'round', lineJoin: 'round' }, interactive: false }
    ).addTo(theMap);

    // ── Animated marching-ants overlay ────────────────────────────────────────
    const animLayer = L.geoJSON(
        { type: 'Feature', geometry },
        { style: { color: '#7c3aed', weight: 4, opacity: 1, lineCap: 'butt', lineJoin: 'round' } }
    ).addTo(theMap);

    eventRouteLayers.push(animLayer);

    setTimeout(function() {
        animLayer.eachLayer(function(l) {
            const el = l.getElement ? l.getElement() : null;
            if (el) el.classList.add('route-animated');
        });
    }, 100);

    // ── Distance ──────────────────────────────────────────────────────────────
    let totalKm = 0;
    try {
        const coords = geometry.type === 'LineString'
            ? geometry.coordinates
            : geometry.coordinates.flat();
        for (let i = 1; i < coords.length; i++) {
            totalKm += L.latLng(coords[i-1][1], coords[i-1][0])
                        .distanceTo(L.latLng(coords[i][1], coords[i][0])) / 1000;
        }
    } catch(e) {}
    const distLabel = totalKm > 0
        ? ` · ${totalKm < 1 ? Math.round(totalKm * 1000) + 'm' : totalKm.toFixed(1) + 'km'}`
        : '';

    animLayer.bindTooltip(
        `<strong style="font-size:11px;letter-spacing:.04em;text-transform:uppercase;">${routeName}${distLabel}</strong>`,
        { sticky: true }
    );

    // ── START / FINISH markers ────────────────────────────────────────────────
    const coords = geometry.type === 'LineString'
        ? geometry.coordinates
        : geometry.coordinates[0];

    if (coords && coords.length >= 2) {
        const mkLabel = (text, bg) => L.divIcon({
            className: '',
            html: `<div style="display:inline-flex;align-items:center;gap:4px;background:${bg};color:#fff;font-size:10px;font-weight:bold;letter-spacing:.06em;padding:3px 8px;white-space:nowrap;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.45);font-family:Arial,sans-serif;">${text}</div>`,
            iconAnchor: [28, 11],
        });

        try {
            const mid   = Math.floor(coords.length / 2);
            const angle = Math.atan2(coords[mid][1] - coords[mid-1][1], coords[mid][0] - coords[mid-1][0]) * 180 / Math.PI;
            L.marker([coords[mid][1], coords[mid][0]], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-bottom:16px solid #7c3aed;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4));transform:rotate(${angle-90}deg);transform-origin:center;"></div>`,
                    iconSize:[16,16], iconAnchor:[8,8],
                }),
                interactive: false
            }).addTo(theMap);
        } catch(e) {}

        const startLabel = EVENT_ROUTES.length > 1 ? `▶ ${routeName}` : '▶ START';
        const finishLabel = EVENT_ROUTES.length > 1 ? `⬛ ${routeName} End` : '⬛ FINISH';

        L.marker([coords[0][1], coords[0][0]], { icon: mkLabel(startLabel, '#1a6b3c'), interactive: false }).addTo(theMap);
        L.marker([coords[coords.length-1][1], coords[coords.length-1][0]], { icon: mkLabel(finishLabel, '#C8102E'), interactive: false }).addTo(theMap);
    }
}

/* ── EVENT POIs ── */
const POI_TYPE_META = {
    entrance: { emoji:'🚪', label:'Entrance'   },
    exit:     { emoji:'🚪', label:'Exit'        },
    car_park: { emoji:'🅿',  label:'Car Park'   },
    medical:  { emoji:'🩺', label:'Medical'     },
    control:  { emoji:'📡', label:'Control'     },
    hazard:   { emoji:'⚠',  label:'Hazard'      },
    info:     { emoji:'ℹ',  label:'Info Point'  },
    custom:   { emoji:'🚩', label:'POI'         },
};

function addEventPois() {
    EVENT_POIS.forEach(function(poi) {
        const meta    = POI_TYPE_META[poi.type] || POI_TYPE_META.custom;
        const colour  = poi.colour || '#C8102E';
        const icon = L.divIcon({
            className: '',
            html: `<div style="
                display:flex;align-items:center;justify-content:center;
                width:30px;height:30px;border-radius:50%;
                background:${colour};border:2.5px solid #fff;
                box-shadow:0 2px 7px rgba(0,0,0,.45);font-size:15px;line-height:1;
            ">${meta.emoji}</div>`,
            iconSize:   [30, 30],
            iconAnchor: [15, 30],
            popupAnchor:[0, -33],
        });
        const marker = L.marker([poi.lat, poi.lng], { icon, title: poi.name || meta.label });
        marker.bindPopup(`
            <div style="font-family:Arial,sans-serif;padding:4px 2px;min-width:140px;">
                <div style="font-size:12px;font-weight:bold;color:#003366;margin-bottom:2px;">${poi.name || meta.label}</div>
                ${poi.description ? `<div style="font-size:11px;color:#6b7f96;margin-bottom:3px;">${poi.description}</div>` : ''}
                ${poi.grid_ref ? `<div style="font-size:11px;font-weight:bold;letter-spacing:.04em;color:#003366;margin-bottom:2px;">📍 ${poi.grid_ref}</div>` : ''}
                ${poi.w3w ? `<div style="font-size:11px;color:#e65c00;font-weight:bold;">/// ${poi.w3w}</div>` : ''}
                <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#9aa3ae;margin-top:3px;">${meta.label}</div>
            </div>
        `, { maxWidth: 220, className: 'rn-popup' });
        marker.addTo(theMap);
    });
}

function makeIcon(colour, isHQ) {
    const s=isHQ?36:30, b=isHQ?'border:3px solid #C8102E;':'border:2px solid rgba(0,0,0,.25);';
    return L.divIcon({className:'',html:`<div style="width:${s}px;height:${s}px;background:${colour};${b}border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 6px rgba(0,0,0,.35);"></div>`,iconSize:[s,s],iconAnchor:[s/2,s],popupAnchor:[0,-(s+4)]});
}

function addMarker(op) {
    const isHQ = op.role && /net\s*control|hq|control/i.test(op.role);
    const m = L.marker([op.lat,op.lng],{icon:makeIcon(op.marker_colour,isHQ),draggable:true,title:op.name});
    m.bindPopup(buildPopup(op),{className:'rn-popup',maxWidth:250});
    m.on('dragend', e => { const p=e.target.getLatLng(); updatePosition(op.id,p.lat,p.lng); });
    m.addTo(theMap);
    markers[op.id] = m;
    // Coverage circle
    if (op.coverage_radius_m > 0) {
        circles[op.id] = L.circle([op.lat,op.lng],{radius:op.coverage_radius_m,color:'#003366',fillColor:'#003366',fillOpacity:.07,weight:1.5,dashArray:'6 4'});
    }
}

function buildPopup(op) {
    const shiftStr = (op.shifts||[]).filter(s=>s.type==='shift'&&s.start&&s.end).map(s=>`${s.start}–${s.end}${s.label?' ('+s.label+')':''}`).join(', ') || '—';
    let freqStr = op.frequency ? `${op.frequency} ${op.mode}` : '—';
    if (op.secondary_frequency) freqStr += ` / ${op.secondary_frequency}`;
    return `<div class="popup-inner">
        <div class="popup-name">${op.name}</div>
        <div class="popup-role">${op.role||'No role assigned'}</div>
        ${op.callsign?`<div class="popup-row"><span class="popup-lbl">Callsign</span>${op.callsign}</div>`:''}
        <div class="popup-row"><span class="popup-lbl">Freq</span>${freqStr}</div>
        ${op.location_name?`<div class="popup-row"><span class="popup-lbl">Position</span>${op.location_name}</div>`:''}
        ${op.grid_ref?`<div class="popup-row"><span class="popup-lbl">Grid</span>${op.grid_ref}</div>`:''}
        ${op.report_time?`<div class="popup-row"><span class="popup-lbl">Report</span>${op.report_time}</div>`:''}
        <div class="popup-shifts">🕐 ${shiftStr}</div>
        ${op.total_hours?`<div class="popup-row"><span class="popup-lbl">Hours</span><strong>${op.total_hours}h</strong></div>`:''}
    </div>
    <div class="popup-actions">
        <button class="popup-act popup-act-circle" onclick="toggleCircle(${op.id})" id="circ-btn-${op.id}">${circles[op.id]?'◎ Circle':'⊙ Circle'}</button>
    </div>
    <div class="popup-footer">${op.status_label}${op.has_vehicle?' · 🚗':''}${op.first_aid_trained?' · 🩺':''}</div>`;
}

function flyToMarker(id) {
    const m=markers[id];
    if (!m){alert('No map position set for this operator.');return;}
    if (!mapInitialised){switchTab('map');setTimeout(()=>flyToMarker(id),600);return;}
    theMap.flyTo(m.getLatLng(),16,{animate:true,duration:1});
    setTimeout(()=>m.openPopup(),1100);
}
/* ══════════════════════════════════════════════════════════════
   FEATURE 1 — Live attendance pulse animations
   Green=checked_in  Amber=on_break  Grey=not_arrived
   Polls every 30s; only updates changed markers.
══════════════════════════════════════════════════════════════ */
var attendanceCache = {};

function startAttendancePoll() {
    updatePulses();
    setInterval(updatePulses, 30000);
}

function updatePulses() {
    AD.forEach(op => {
        const m = markers[op.id];
        if (!m) return;
        const colour = op.attendance_status === 'checked_in'  ? '#1a6b3c'
                     : op.attendance_status === 'on_break'    ? '#8a5c00'
                     : null;
        const el = m.getElement ? m.getElement()?.parentNode : null;
        if (!el) return;
        // Remove old pulse
        el.querySelectorAll('.pulse-ring').forEach(p => p.remove());
        if (colour) {
            const ring = document.createElement('div');
            ring.className = 'pulse-ring';
            ring.style.cssText = `border-color:${colour};top:-1px;left:-1px;`;
            el.appendChild(ring);
        }
    });
    // Refresh attendance data via AJAX
    const url = `{{ url('admin/events/'.$event->id.'/assignments/attendance-status') }}`;
    fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            data.forEach(d => {
                const op = AD.find(a => a.id === d.id);
                if (op) op.attendance_status = d.attendance_status;
            });
            updatePulses();
            updateHud();
        }).catch(() => {});
}

function updateHud() {
    const counts = { checked_in:0, on_break:0, checked_out:0, not_arrived:0 };
    AD.forEach(a => { if (counts[a.attendance_status] !== undefined) counts[a.attendance_status]++; });
    document.getElementById('hud-on-site').textContent  = counts.checked_in;
    document.getElementById('hud-on-break').textContent = counts.on_break;
    document.getElementById('hud-out').textContent      = counts.checked_out;
    document.getElementById('hud-na').textContent       = counts.not_arrived;
    document.getElementById('hud-clock').textContent    = new Date().toLocaleTimeString('en-GB', {hour:'2-digit',minute:'2-digit'});
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 2 — Comms link planner
   Click one operator then another to draw a radio link.
   Green=strong  Amber=medium  Red=weak (set via popup).
══════════════════════════════════════════════════════════════ */
var commsMode = false, commsFirst = null, commsLinks = [];

function toggleCommsMode() {
    commsMode = !commsMode;
    const btn = document.getElementById('comms-btn');
    btn.classList.toggle('btn-primary', commsMode);
    commsFirst = null;
    theMap.getContainer().style.cursor = commsMode ? 'crosshair' : '';
    if (commsMode) {
        showToast('📻 Comms mode — click an operator marker to start a link');
    }
}

function commsMarkerClick(opId) {
    if (!commsMode) return;
    if (!commsFirst) {
        commsFirst = opId;
        showToast('Click a second operator to complete the link');
        return;
    }
    if (commsFirst === opId) { commsFirst = null; return; }
    const op1 = AD.find(a => a.id === commsFirst);
    const op2 = AD.find(a => a.id === opId);
    if (!op1?.lat || !op2?.lat) { commsFirst = null; return; }
    drawCommsLink(op1, op2, 'strong');
    commsFirst = null;
}

function drawCommsLink(op1, op2, quality) {
    const colours = { strong:'#1a6b3c', medium:'#8a5c00', weak:'#C8102E' };
    const col = colours[quality] || '#003366';
    const line = L.polyline([[op1.lat,op1.lng],[op2.lat,op2.lng]], {
        color:col, weight:2.5, opacity:.75, dashArray: quality==='weak'?'6 4':null,
        interactive:true
    }).addTo(theMap);
    const link = { op1:op1.id, op2:op2.id, quality, line };
    commsLinks.push(link);
    line.bindPopup(buildCommsPopup(link)).openPopup();
}

function buildCommsPopup(link) {
    const op1 = AD.find(a=>a.id===link.op1), op2 = AD.find(a=>a.id===link.op2);
    return `<div style="font-family:Arial,sans-serif;min-width:160px;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;color:#003366;margin-bottom:5px;">📻 Comms Link</div>
        <div style="font-size:12px;margin-bottom:6px;">${op1?.name||'?'} ↔ ${op2?.name||'?'}</div>
        <div style="margin-bottom:5px;font-size:11px;">Quality:
            <select onchange="updateCommsQuality(${link.op1},${link.op2},this.value)" style="font-size:11px;padding:2px 4px;border:1px solid #dde2e8;">
                <option value="strong" ${link.quality==='strong'?'selected':''}>✓ Strong</option>
                <option value="medium" ${link.quality==='medium'?'selected':''}>~ Medium</option>
                <option value="weak"   ${link.quality==='weak'  ?'selected':''}>✗ Weak</option>
            </select>
        </div>
        <button onclick="removeCommsLink(${link.op1},${link.op2})" style="font-size:10px;background:#fdf0f2;border:1px solid #C8102E;color:#C8102E;padding:3px 8px;cursor:pointer;font-family:Arial;width:100%;">✕ Remove Link</button>
    </div>`;
}

function updateCommsQuality(id1, id2, q) {
    const link = commsLinks.find(l=>l.op1===id1&&l.op2===id2);
    if (!link) return;
    link.quality = q;
    theMap.removeLayer(link.line);
    const op1 = AD.find(a=>a.id===id1), op2 = AD.find(a=>a.id===id2);
    drawCommsLink(op1, op2, q);
    commsLinks = commsLinks.filter(l=>!(l.op1===id1&&l.op2===id2));
}

function removeCommsLink(id1, id2) {
    const idx = commsLinks.findIndex(l=>l.op1===id1&&l.op2===id2);
    if (idx !== -1) { theMap.removeLayer(commsLinks[idx].line); commsLinks.splice(idx,1); }
    theMap.closePopup();
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 3 — Bearing & distance tool
   Alt+hover shows bearing + distance from nearest operator.
   Right-click any marker for full bearing list.
══════════════════════════════════════════════════════════════ */
var bearingMode = false, bearingTooltip = null;

function toggleBearingTool() {
    bearingMode = !bearingMode;
    document.getElementById('bearing-btn').classList.toggle('btn-primary', bearingMode);
    if (!bearingMode && bearingTooltip) { bearingTooltip.remove(); bearingTooltip = null; }
}

function handleBearingHover(e) {
    if (!bearingMode || !e.originalEvent?.altKey) return;
    const cursor = e.latlng;
    const placed = AD.filter(o => o.lat && o.lng);
    if (!placed.length) return;
    // Find closest operator
    let closest = placed[0], minD = Infinity;
    placed.forEach(op => {
        const d = L.latLng(op.lat, op.lng).distanceTo(cursor);
        if (d < minD) { minD = d; closest = op; }
    });
    const bearing = computeBearing(closest.lat, closest.lng, cursor.lat, cursor.lng);
    const km = (minD/1000).toFixed(2);
    const html = `<div style="background:#003366;color:#fff;font-size:11px;font-weight:bold;padding:4px 8px;border-radius:3px;white-space:nowrap;">
        From ${closest.name}: ${Math.round(bearing)}° · ${km}km</div>`;
    if (!bearingTooltip) {
        bearingTooltip = L.popup({ closeButton:false, offset:[0,-5], className:'' })
            .setLatLng(cursor).setContent(html).openOn(theMap);
    } else {
        bearingTooltip.setLatLng(cursor).setContent(html);
    }
}

function computeBearing(lat1, lng1, lat2, lng2) {
    const toRad = d => d * Math.PI / 180;
    const dLng = toRad(lng2 - lng1);
    const y = Math.sin(dLng) * Math.cos(toRad(lat2));
    const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) - Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLng);
    return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 4 — Range rings (right-click context menu)
══════════════════════════════════════════════════════════════ */
var ctxLatLng = null, rangeRings = [];

function setupContextMenu() {
    theMap.on('contextmenu', function(e) {
        L.DomEvent.preventDefault(e);
        ctxLatLng = e.latlng;
        const menu    = document.getElementById('map-ctx-menu');
        const wrapper = document.getElementById('map-wrap');
        const wRect   = wrapper.getBoundingClientRect();
        const mRect   = theMap.getContainer().getBoundingClientRect();
        // containerPoint is relative to the map div; offset to wrapper coords
        menu.style.left = (e.containerPoint.x + (mRect.left - wRect.left)) + 'px';
        menu.style.top  = (e.containerPoint.y + (mRect.top  - wRect.top))  + 'px';
        menu.classList.add('open');
    });
    document.addEventListener('click', () => document.getElementById('map-ctx-menu').classList.remove('open'));
}

function ctxAddRing(miles) {
    document.getElementById('map-ctx-menu').classList.remove('open');
    if (!ctxLatLng) return;
    const metres = miles * 1609.344;
    const ring = L.circle(ctxLatLng, {
        radius: metres, color:'#003366', fillColor:'transparent',
        fillOpacity:0, weight:1.5, dashArray:'8 4', interactive:false
    }).addTo(theMap);
    const label = L.marker(L.latLng(ctxLatLng.lat + metres/111320, ctxLatLng.lng), {
        icon: L.divIcon({ className:'', html:`<div style="background:rgba(0,51,102,.75);color:#fff;font-size:9px;font-weight:bold;padding:2px 6px;border-radius:3px;white-space:nowrap;">${miles}mi<button onclick="removeRing(${rangeRings.length})" style="margin-left:4px;background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:9px;">✕</button></div>`, iconAnchor:[0,0] }),
        interactive:true
    }).addTo(theMap);
    rangeRings.push({ ring, label });
}

function removeRing(idx) {
    if (rangeRings[idx]) {
        theMap.removeLayer(rangeRings[idx].ring);
        theMap.removeLayer(rangeRings[idx].label);
        rangeRings[idx] = null;
    }
}

function ctxClearRings() {
    document.getElementById('map-ctx-menu').classList.remove('open');
    rangeRings.forEach(r => { if(r) { theMap.removeLayer(r.ring); theMap.removeLayer(r.label); }});
    rangeRings = [];
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 5 — Attendance timeline replay
   Drag the scrubber to see operator statuses at any point in the day.
══════════════════════════════════════════════════════════════ */
function toggleTimeline() {
    const panel = document.getElementById('timeline-panel');
    panel.classList.toggle('visible');
    document.getElementById('timeline-btn').classList.toggle('btn-primary', panel.classList.contains('visible'));
}

function replayTimeline(minutesSinceMidnight) {
    const mins = parseInt(minutesSinceMidnight);
    const hh = String(Math.floor(mins/60)).padStart(2,'0');
    const mm = String(mins%60).padStart(2,'0');
    document.getElementById('timeline-clock').textContent = `${hh}:${mm}`;
    const targetTime = `${hh}:${mm}`;
    AD.forEach(op => {
        const log = op.attendance_log || [];
        let status = 'not_arrived';
        // Walk through log entries up to targetTime
        [...log].sort((a,b)=>a.time.localeCompare(b.time)).forEach(entry => {
            const entryTime = entry.time.substring(11,16); // HH:MM from ISO
            if (entryTime <= targetTime) {
                if (entry.type === 'check_in')    status = 'checked_in';
                if (entry.type === 'break_start') status = 'on_break';
                if (entry.type === 'break_end')   status = 'checked_in';
                if (entry.type === 'check_out')   status = 'checked_out';
            }
        });
        const m = markers[op.id];
        if (m) {
            const col = status==='checked_in' ? '#1a6b3c' : status==='on_break' ? '#8a5c00' : status==='checked_out' ? '#555' : '#C8102E';
            m.setIcon(makeIcon(col, false));
        }
    });
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 6 — Coverage gap detector
   Grid-samples the site polygon and shades uncovered zones red.
══════════════════════════════════════════════════════════════ */
var gapLayer = null, gapOn = false;

function toggleGapDetector() {
    gapOn = !gapOn;
    const btn = document.getElementById('gap-btn');
    btn.classList.toggle('btn-primary', gapOn);
    if (!gapOn) { if(gapLayer){theMap.removeLayer(gapLayer);gapLayer=null;} return; }
    if (!EVENT_POLYGON) { showToast('Draw a site boundary first'); gapOn=false; btn.classList.remove('btn-primary'); return; }
    computeGaps();
}

function pointInPoly(lat, lng, coords) {
    let inside = false;
    for (let i=0,j=coords.length-1;i<coords.length;j=i++) {
        const xi=coords[i][0],yi=coords[i][1],xj=coords[j][0],yj=coords[j][1];
        if (((yi>lng)!==(yj>lng))&&(lat<(xj-xi)*(lng-yi)/(yj-yi)+xi)) inside=!inside;
    }
    return inside;
}

function computeGaps() {
    const ring = EVENT_POLYGON.type==='MultiPolygon' ? EVENT_POLYGON.coordinates[0][0] : EVENT_POLYGON.coordinates[0];
    const lngs = ring.map(c=>c[0]), lats = ring.map(c=>c[1]);
    const minLat=Math.min(...lats),maxLat=Math.max(...lats),minLng=Math.min(...lngs),maxLng=Math.max(...lngs);
    const gridM = 50; // 50m grid
    const latStep = gridM/111320, lngStep = gridM/(111320*Math.cos((minLat+maxLat)/2*Math.PI/180));
    const RADIUS_M = 500;
    const ops = AD.filter(o=>o.lat&&o.lng&&o.status!=='declined');
    const uncovered = [];
    for (let lat=minLat;lat<=maxLat;lat+=latStep) {
        for (let lng=minLng;lng<=maxLng;lng+=lngStep) {
            if (!pointInPoly(lng,lat,ring)) continue;
            const covered = ops.some(op => L.latLng(lat,lng).distanceTo(L.latLng(op.lat,op.lng)) <= RADIUS_M);
            if (!covered) uncovered.push([lat,lng]);
        }
    }
    const total = (() => { let c=0; for(let lat=minLat;lat<=maxLat;lat+=latStep) for(let lng=minLng;lng<=maxLng;lng+=lngStep) if(pointInPoly(lng,lat,ring))c++; return c; })();
    const pct = total > 0 ? Math.round((1 - uncovered.length/total)*100) : 100;
    if (gapLayer) theMap.removeLayer(gapLayer);
    gapLayer = L.layerGroup().addTo(theMap);
    uncovered.forEach(([lat,lng]) => {
        L.rectangle([[lat,lng],[lat+latStep,lng+lngStep]], {
            color:'transparent', fillColor:'#C8102E', fillOpacity:.35, weight:0, interactive:false
        }).addTo(gapLayer);
    });
    // Badge
    L.popup({ closeButton:false })
        .setLatLng([(minLat+maxLat)/2,(minLng+maxLng)/2])
        .setContent(`<div style="font-family:Arial;font-size:13px;font-weight:bold;text-align:center;padding:4px 8px;">${pct}% covered<div style="font-size:10px;font-weight:normal;color:#6b7f96;">${RADIUS_M}m per operator</div></div>`)
        .openOn(theMap);
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 7 — Fullscreen operations view
══════════════════════════════════════════════════════════════ */
function enterFullscreen() {
    const el = document.getElementById('map-wrap');
    if (el.requestFullscreen) el.requestFullscreen();
    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
    setTimeout(() => theMap.invalidateSize(), 300);
    setInterval(updateHud, 5000);
    updateHud();
    document.addEventListener('fullscreenchange', function onFs() {
        if (!document.fullscreenElement) {
            // Restore map height after exiting fullscreen
            document.getElementById('leaflet-map').style.height = '';
            theMap.invalidateSize();
            document.removeEventListener('fullscreenchange', onFs);
        } else {
            // Fill the wrapper when fullscreen
            document.getElementById('leaflet-map').style.height = '100%';
            theMap.invalidateSize();
        }
    });
}

/* ══════════════════════════════════════════════════════════════
   FEATURE 8 — Operator density heatmap (canvas overlay)
══════════════════════════════════════════════════════════════ */
var heatCanvas = null, heatOn = false;

function toggleHeatmap() {
    heatOn = !heatOn;
    document.getElementById('heatmap-btn').classList.toggle('btn-primary', heatOn);
    if (!heatOn) { if(heatCanvas){heatCanvas.remove();heatCanvas=null;} return; }
    renderHeatmap();
    theMap.on('moveend zoomend', renderHeatmap);
}

function renderHeatmap() {
    if (!heatOn) return;
    const ops = AD.filter(o=>o.lat&&o.lng&&o.status!=='declined');
    if (!ops.length) return;
    const mapSize = theMap.getSize();
    if (!heatCanvas) {
        heatCanvas = document.createElement('canvas');
        heatCanvas.style.cssText = `position:absolute;top:0;left:0;pointer-events:none;z-index:400;opacity:.65;`;
        // Append to map-wrap so it overlays the map tile div correctly
        const mapEl = theMap.getContainer();
        mapEl.appendChild(heatCanvas);
    }
    heatCanvas.width  = mapSize.x;
    heatCanvas.height = mapSize.y;
    const ctx = heatCanvas.getContext('2d');
    ctx.clearRect(0, 0, mapSize.x, mapSize.y);
    const RADIUS = Math.max(40, Math.min(120, mapSize.x / 8));
    ops.forEach(op => {
        const pt = theMap.latLngToContainerPoint([op.lat, op.lng]);
        const grad = ctx.createRadialGradient(pt.x, pt.y, 0, pt.x, pt.y, RADIUS);
        grad.addColorStop(0,   'rgba(200,16,46,0.6)');
        grad.addColorStop(0.5, 'rgba(0,51,102,0.3)');
        grad.addColorStop(1,   'rgba(0,51,102,0)');
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, RADIUS, 0, Math.PI*2);
        ctx.fillStyle = grad;
        ctx.fill();
    });
}

/* ══ TOAST HELPER ══ */
function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText='position:absolute;bottom:60px;left:50%;transform:translateX(-50%);background:rgba(0,31,64,.9);color:#fff;font-size:12px;font-weight:bold;padding:6px 14px;border-radius:4px;z-index:1500;pointer-events:none;white-space:nowrap;';
    document.getElementById('map-wrap').appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

function resetMapView() {
    if (!theMap) return;
    if (EVENT_POLYGON && eventPolygonLayer) {
        theMap.fitBounds(eventPolygonLayer.getBounds(), {padding:[40,40]});
        return;
    }
    if (eventRouteLayers.length > 0) {
        try {
            theMap.fitBounds(L.featureGroup(eventRouteLayers).getBounds(), {padding:[50,50]});
            return;
        } catch(e) {}
    }
    const wp = AD.filter(o=>o.lat&&o.lng);
    if (wp.length > 1) theMap.fitBounds(wp.map(o=>[o.lat,o.lng]),{padding:[40,40]});
    else theMap.setView([EVENT_CENTRE.lat,EVENT_CENTRE.lng],13);
}
function toggleSatellite() {
    if (!theMap) return;
    satelliteOn=!satelliteOn;
    if (satelliteOn){tileStreet.remove();tileSat.addTo(theMap);}
    else{tileSat.remove();tileStreet.addTo(theMap);}
    document.getElementById('sat-btn').textContent = satelliteOn ? '🗺 Street' : '🛰 Satellite';
}
function toggleCircle(id) {
    if (!theMap) return;
    const op = AD.find(o=>o.id===id);
    if (!op || !op.coverage_radius_m) { alert('No coverage radius set for this operator. Edit the assignment to add one.'); return; }
    if (!circles[id]) circles[id] = L.circle([op.lat,op.lng],{radius:op.coverage_radius_m,color:'#003366',fillColor:'#003366',fillOpacity:.07,weight:1.5,dashArray:'6 4'});
    if (theMap.hasLayer(circles[id])) { theMap.removeLayer(circles[id]); }
    else { circles[id].addTo(theMap); }
}
function toggleAllCircles() {
    if (!theMap) { switchTab('map'); setTimeout(toggleAllCircles,600); return; }
    circlesVisible = !circlesVisible;
    AD.forEach(op => {
        if (!op.lat||!op.lng||!op.coverage_radius_m) return;
        if (!circles[op.id]) circles[op.id] = L.circle([op.lat,op.lng],{radius:op.coverage_radius_m,color:'#003366',fillColor:'#003366',fillOpacity:.07,weight:1.5,dashArray:'6 4'});
        if (circlesVisible) circles[op.id].addTo(theMap);
        else theMap.removeLayer(circles[op.id]);
    });
    document.getElementById('circles-btn').style.background = circlesVisible ? 'var(--navy-faint)' : '';
}
function updatePosition(id,lat,lng) {
    fetch(ROUTES.position+id+'/position',{method:'PATCH',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({lat,lng})}).catch(e=>console.error(e));
}

/* GPX */
function loadGpx(input) {
    const file=input.files[0]; if (!file) return;
    const reader=new FileReader();
    reader.onload=function(e) {
        const xml=new DOMParser().parseFromString(e.target.result,'text/xml');
        const pts=[...xml.querySelectorAll('trkpt,rtept,wpt')].map(p=>[parseFloat(p.getAttribute('lat')),parseFloat(p.getAttribute('lon'))]);
        if (!pts.length){document.getElementById('gpx-status').textContent='No track points found.';return;}
        if (gpxLayer) theMap.removeLayer(gpxLayer);
        if (!theMap){switchTab('map');setTimeout(()=>loadGpx(input),600);return;}
        gpxLayer=L.polyline(pts,{color:'#C8102E',weight:3,opacity:.8}).addTo(theMap);
        theMap.fitBounds(gpxLayer.getBounds(),{padding:[20,20]});
        document.getElementById('gpx-status').textContent=`✓ ${pts.length} points`;
    };
    reader.readAsText(file);
}
function clearGpx() {
    if (gpxLayer&&theMap) theMap.removeLayer(gpxLayer);
    gpxLayer=null;
    document.getElementById('gpx-input').value='';
    document.getElementById('gpx-status').textContent='';
}

/* ══ SHIFT BUILDER ══ */
var shiftData = [];

function addShiftRow(type, start, end, label) {
    shiftData.push({ type, start:start||'', end:end||'', label:label||'' });
    renderShiftRows();
}

function renderShiftRows() {
    const container = document.getElementById('shift-rows');
    if (!container) return;
    if (!shiftData.length) { container.innerHTML = '<div style="font-size:12px;color:var(--text-muted);font-style:italic;padding:.3rem 0;">No shifts added yet.</div>'; updateShiftHours(); return; }
    container.innerHTML = shiftData.map((s,i) => `
        <div class="shift-row ${s.type==='break'?'break-row':''}">
            <span class="shift-type-badge ${s.type==='break'?'brk':'work'}">${s.type==='break'?'⏸ Break':'▶ Shift'}</span>
            <input type="time" class="shift-time-input" value="${s.start}" onchange="shiftData[${i}].start=this.value;updateShiftHours()">
            <span class="shift-time-sep">→</span>
            <input type="time" class="shift-time-input" value="${s.end}" onchange="shiftData[${i}].end=this.value;updateShiftHours()">
            <input type="text" class="shift-label-input" value="${s.label}" placeholder="${s.type==='break'?'Lunch, Rest…':'Label (optional)'}" onchange="shiftData[${i}].label=this.value">
            <button type="button" class="shift-del" onclick="removeShiftRow(${i})">✕</button>
        </div>
    `).join('');
    updateShiftHours();
}

function removeShiftRow(i) { shiftData.splice(i,1); renderShiftRows(); }
function clearShifts() { shiftData=[]; renderShiftRows(); }

function applyPreset(name) {
    clearShifts();
    const presets = {
        morning:   [{type:'shift',start:'08:00',end:'13:00',label:'Morning'}],
        afternoon: [{type:'shift',start:'13:00',end:'18:00',label:'Afternoon'}],
        fullday:   [{type:'shift',start:'08:00',end:'12:00',label:'AM'},{type:'break',start:'12:00',end:'13:00',label:'Lunch'},{type:'shift',start:'13:00',end:'18:00',label:'PM'}],
        split:     [{type:'shift',start:'07:00',end:'10:00',label:'Early'},{type:'break',start:'10:00',end:'14:00',label:'Break'},{type:'shift',start:'14:00',end:'19:00',label:'Late'}],
    };
    (presets[name]||[]).forEach(s=>shiftData.push(s));
    renderShiftRows();
}

function updateShiftHours() {
    let mins=0;
    shiftData.forEach(s=>{
        if (s.type==='shift'&&s.start&&s.end){
            const p=s.start.split(':'),q=s.end.split(':');
            const d=(parseInt(q[0])*60+parseInt(q[1]))-(parseInt(p[0])*60+parseInt(p[1]));
            if (d>0) mins+=d;
        }
    });
    const el=document.getElementById('shift-hours-display');
    if (el) el.textContent = mins>0 ? `Total: ${Math.floor(mins/60)}h ${mins%60?mins%60+'m':''}` : '';
}

function serializeShifts() {
    document.getElementById('shifts-json-input').value = JSON.stringify(shiftData);
    // Equipment
    const checked = [...document.querySelectorAll('.equip-cb:checked')].map(cb=>cb.value);
    document.getElementById('equip-json-input').value = JSON.stringify(checked);
}

/* ══ EQUIPMENT ══ */
function addCustomEquip() {
    const inp = document.getElementById('equip-custom-input');
    const val = inp.value.trim(); if (!val) return;
    const grid = document.getElementById('equip-grid');
    const lbl = document.createElement('label');
    lbl.className = 'equip-item';
    lbl.innerHTML = `<input type="checkbox" class="equip-cb" value="${val}" checked> ${val}`;
    grid.appendChild(lbl);
    inp.value = '';
}

/* ══ CARD EXPAND ══ */
var bulkMode = false;
function handleCardClick(e, id) {
    if (bulkMode) { toggleSelect(id); return; }
    toggleCard(id);
}
function toggleCard(id) {
    const body=document.getElementById('body-'+id), tog=document.getElementById('toggle-'+id);
    const isOpen=body.classList.contains('open');
    document.querySelectorAll('.ac-body.open').forEach(el=>el.classList.remove('open'));
    document.querySelectorAll('.ac-toggle.open').forEach(el=>el.classList.remove('open'));
    if (!isOpen){body.classList.add('open');tog.classList.add('open');}
}

/* ══ BULK ACTIONS ══ */
var selectedIds = new Set();
function toggleBulkMode() {
    bulkMode = !bulkMode;
    document.querySelectorAll('.bulk-cb').forEach(cb => cb.style.display = bulkMode ? 'block' : 'none');
    if (!bulkMode) { clearSelection(); document.getElementById('bulkBar').classList.remove('visible'); }
}
function toggleSelect(id) {
    const cb = document.getElementById('cb-'+id);
    const card = document.getElementById('card-'+id);
    if (selectedIds.has(id)) { selectedIds.delete(id); card.classList.remove('selected'); if(cb) cb.checked=false; }
    else { selectedIds.add(id); card.classList.add('selected'); if(cb) cb.checked=true; }
    updateBulkBar();
}
function selectAll() { AD.forEach(op=>{ selectedIds.add(op.id); const cb=document.getElementById('cb-'+op.id); if(cb)cb.checked=true; document.getElementById('card-'+op.id)?.classList.add('selected'); }); updateBulkBar(); }
function clearSelection() { selectedIds.forEach(id=>{ document.getElementById('card-'+id)?.classList.remove('selected'); const cb=document.getElementById('cb-'+id); if(cb)cb.checked=false; }); selectedIds.clear(); updateBulkBar(); }
function updateBulkBar() {
    const bar=document.getElementById('bulkBar'), lbl=document.getElementById('bulk-count-label');
    lbl.textContent = selectedIds.size + ' selected';
    bar.classList.toggle('visible', bulkMode);
}
function applyBulkStatus() {
    const status = document.getElementById('bulk-status-select').value;
    if (!status) { alert('Please select a status to apply.'); return; }
    if (!selectedIds.size) { alert('No operators selected.'); return; }
    if (!confirm(`Set ${selectedIds.size} operator(s) to "${status}"?`)) return;
    fetch(ROUTES.bulkStatus, {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({ ids:[...selectedIds], status })
    }).then(r=>r.json()).then(()=>location.reload()).catch(e=>{alert('Error: '+e.message);});
}

/* ══ MODAL ══ */
var editingId = null;
/* ══ MODAL PIN-PICKER MAP ══ */
var pickerMap = null, pickerMarker = null;

function initPickerMap() {
    if (pickerMap) { pickerMap.invalidateSize(); return; }
    const lat = parseFloat(document.getElementById('modal-lat').value) || EVENT_CENTRE.lat;
    const lng = parseFloat(document.getElementById('modal-lng').value) || EVENT_CENTRE.lng;
    pickerMap = L.map('modal-map-picker', { center: [lat, lng], zoom: 14, zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(pickerMap);

    // If editing and already has coords, place pin
    const existLat = parseFloat(document.getElementById('modal-lat').value);
    const existLng = parseFloat(document.getElementById('modal-lng').value);
    if (existLat && existLng) {
        placePickerPin(existLat, existLng, false);
        pickerMap.setView([existLat, existLng], 16);
    }

    pickerMap.on('click', function(e) {
        placePickerPin(e.latlng.lat, e.latlng.lng, true);
    });
}

function placePickerPin(lat, lng, updateFields) {
    const icon = L.divIcon({
        className: '',
        html: '<div style="width:26px;height:26px;background:#C8102E;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,.45);"></div>',
        iconSize: [26, 26], iconAnchor: [13, 26], popupAnchor: [0, -28]
    });
    if (pickerMarker) pickerMap.removeLayer(pickerMarker);
    pickerMarker = L.marker([lat, lng], { icon, draggable: true }).addTo(pickerMap);
    pickerMarker.on('dragend', function(e) {
        const p = e.target.getLatLng();
        setPickerCoords(p.lat, p.lng);
    });
    if (updateFields) setPickerCoords(lat, lng);
}

function setPickerCoords(lat, lng) {
    document.getElementById('modal-lat').value = lat.toFixed(6);
    document.getElementById('modal-lng').value = lng.toFixed(6);
    // Reverse geocode location name via Nominatim if field is empty
    const locField = document.getElementById('modal-locname');
    if (!locField.value) {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
            .then(r => r.json())
            .then(d => {
                if (d && d.display_name && !locField.value) {
                    // Use a short version: road + suburb or town
                    const a = d.address || {};
                    const short = [a.road, a.suburb || a.village || a.town || a.city]
                        .filter(Boolean).join(', ');
                    locField.value = short || d.display_name.split(',').slice(0,2).join(',').trim();
                }
            })
            .catch(() => {});
    }
}

function syncPickerFromFields() {
    const lat = parseFloat(document.getElementById('modal-lat').value);
    const lng = parseFloat(document.getElementById('modal-lng').value);
    if (!pickerMap || isNaN(lat) || isNaN(lng)) return;
    placePickerPin(lat, lng, false);
    pickerMap.setView([lat, lng], 16);
}

function clearPickerPin() {
    if (pickerMarker && pickerMap) pickerMap.removeLayer(pickerMarker);
    pickerMarker = null;
    document.getElementById('modal-lat').value = '';
    document.getElementById('modal-lng').value = '';
}

function openAddModal() {
    editingId = null;
    document.getElementById('modal-title').textContent = 'Assign Member to Event';
    document.getElementById('modal-submit').textContent = '+ Assign';
    document.getElementById('assignForm').action = ROUTES.store;
    document.getElementById('method-spoofed').innerHTML = '';
    document.getElementById('member-select-row').style.display = '';
    clearModalFields();
    shiftData = [];
    renderShiftRows();
    document.getElementById('assignModal').classList.add('open');
    const poiDrop = document.getElementById('modal-poi-link');
    if (poiDrop) poiDrop.value = '';
    // Destroy old picker so it reinitialises fresh at the default centre
    if (pickerMap) { pickerMap.remove(); pickerMap = null; pickerMarker = null; }
    setTimeout(initPickerMap, 120);
}

function openEditModal(id) {
    const op = AD.find(o=>o.id===id);
    if (!op) return;
    editingId = id;
    document.getElementById('modal-title').textContent = 'Edit Assignment — ' + op.name;
    document.getElementById('modal-submit').textContent = '✓ Save Changes';
    document.getElementById('assignForm').action = ROUTES.update + id;
    document.getElementById('method-spoofed').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('member-select-row').style.display = 'none';
    const set = (elId,val) => { const el=document.getElementById(elId); if(el) el.value=val||''; };
    set('modal-role',op.role); set('modal-callsign',op.callsign);
    set('modal-channel-label',op.channel_label);
    set('modal-frequency',op.frequency); set('modal-ctcss',op.ctcss_tone);
    set('modal-sec-freq',op.secondary_frequency); set('modal-sec-ctcss',op.secondary_ctcss);
    set('modal-fal-freq',op.fallback_frequency);  set('modal-fal-ctcss',op.fallback_ctcss);
    set('modal-locname',op.location_name); set('modal-grid',op.grid_ref);
    set('modal-lat',op.lat!==null?op.lat:''); set('modal-lng',op.lng!==null?op.lng:'');
    set('modal-w3w',op.what3words); set('modal-radius',op.coverage_radius_m||0);
    set('modal-report',op.report_time); set('modal-depart',op.depart_time);
    set('modal-vreg',op.vehicle_reg);
    set('modal-notes',op.briefing_notes); set('modal-medical',op.medical_notes);
    set('modal-ec-name',op.emergency_contact_name); set('modal-ec-phone',op.emergency_contact_phone);
    document.getElementById('modal-mode').value = op.mode||'FM';
    document.getElementById('modal-sec-mode').value = op.secondary_mode||'FM';
    document.getElementById('modal-fal-mode').value = op.fallback_mode||'FM';
    document.getElementById('modal-status').value = op.status||'pending';
    document.getElementById('modal-hasveh').checked = !!op.has_vehicle;
    document.getElementById('modal-fa').checked = !!op.first_aid_trained;
    // Shifts
    shiftData = op.shifts ? JSON.parse(JSON.stringify(op.shifts)) : [];
    renderShiftRows();
    // Equipment
    document.querySelectorAll('.equip-cb').forEach(cb => { cb.checked = (op.equipment_items||[]).includes(cb.value); });
    document.getElementById('assignModal').classList.add('open');
    // Reinitialise picker map with the operator's existing coords
    if (pickerMap) { pickerMap.remove(); pickerMap = null; pickerMarker = null; }
    setTimeout(initPickerMap, 120);
}

function applyPoiLink(val) {
    if (!val) return;
    try {
        const poi = JSON.parse(val);
        const set = (id, v) => { const el = document.getElementById(id); if (el && v) el.value = v; };
        set('modal-locname', poi.name);
        set('modal-grid',    poi.grid_ref);
        set('modal-w3w',     poi.w3w);
        if (poi.lat && poi.lng) {
            set('modal-lat', poi.lat);
            set('modal-lng', poi.lng);
            syncPickerFromFields();
        }
        // Reset the dropdown so it shows placeholder again
        document.getElementById('modal-poi-link').value = '';
    } catch(e) {}
}


function fillShiftFromReportDepart() {
    const report = document.getElementById('modal-report')?.value;
    const depart = document.getElementById('modal-depart')?.value;
    if (!report || !depart) {
        alert('Please fill in both Report Time and Depart Time first.');
        return;
    }
    shiftData = [{ type: 'shift', start: report, end: depart, label: '' }];
    renderShiftRows();
}

function clearModalFields() {
    ['modal-role','modal-callsign','modal-channel-label','modal-frequency','modal-ctcss','modal-sec-freq','modal-sec-ctcss','modal-fal-freq','modal-fal-ctcss','modal-locname','modal-grid','modal-lat','modal-lng','modal-w3w','modal-radius','modal-report','modal-depart','modal-vreg','modal-notes','modal-medical','modal-ec-name','modal-ec-phone'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('modal-mode').value='FM';
    document.getElementById('modal-sec-mode').value='FM';
    document.getElementById('modal-fal-mode').value='FM';
    document.getElementById('modal-status').value='pending';
    document.getElementById('modal-hasveh').checked=false;
    document.getElementById('modal-fa').checked=false;
    document.querySelectorAll('.equip-cb').forEach(cb=>cb.checked=false);
    if (document.getElementById('modal-user')) { [...document.getElementById('modal-user').options].forEach(o => o.selected = false); }
    updateMemberCount();
}

function updateMemberCount() {
    const sel = document.getElementById('modal-user');
    const count = sel ? [...sel.options].filter(o => o.selected).length : 0;
    const badge = document.getElementById('member-count-badge');
    if (badge) badge.textContent = count + ' selected';
}

function filterMembers(query) {
    const sel = document.getElementById('modal-user');
    const q = query.toLowerCase().trim();
    [...sel.options].forEach(opt => {
        const name = opt.dataset.name || opt.textContent.toLowerCase();
        opt.style.display = (q === '' || name.includes(q)) ? '' : 'none';
    });
}

function closeModal() { document.getElementById('assignModal').classList.remove('open'); }

document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeModal(); });

/* ══ BRIEFING VERSION ══ */
/* ══ ATTENDANCE TAB ══ */
function toggleAttLog(id) {
    const el   = document.getElementById('attlog-' + id);
    const icon = document.getElementById('attlog-icon-' + id);
    if (!el) return;
    const open = el.style.display !== 'none';
    el.style.display   = open ? 'none' : 'block';
    if (icon) icon.textContent = open ? '⌄' : '⌃';
}

var attendRefreshTimer = null;
function startAttendanceRefresh() {
    if (attendRefreshTimer) return; // already running
    attendRefreshTimer = setInterval(function () {
        // Only auto-refresh if attendance tab is still active
        if (document.getElementById('tab-attendance')?.classList.contains('active')) {
            const el = document.getElementById('attend-last-update');
            // Reload the page to get fresh data
            location.reload();
        }
    }, 30000);
}

function initBriefingVersion() {
    const key = 'briefing_v_'+EVENT_ID;
    let v = parseInt(localStorage.getItem(key)||'1');
    document.getElementById('brief-version').textContent = v;
    document.getElementById('brief-date').textContent = new Date().toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
}
function printBriefing() {
    const key = 'briefing_v_'+EVENT_ID;
    let v = parseInt(localStorage.getItem(key)||'1');
    localStorage.setItem(key, v+1);
    document.getElementById('brief-version').textContent = v+1;
    window.print();
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<div id="tab-availability" class="tab-pane fade-in">
        @php
            $availabilityResponses = \App\Models\UserEventAvailability::with('user')
                ->where('event_id', $event->id)
                ->orderByDesc('responded_at')
                ->get();
            $availableCount   = $availabilityResponses->where('available', true)->count();
            $unavailableCount = $availabilityResponses->where('available', false)->count();
            $totalMembers     = \App\Models\User::where('registration_pending', false)->whereNull('suspended_at')->count();
        @endphp
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <div style="flex:1;min-width:110px;background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--green);padding:.75rem 1rem;text-align:center;">
                <div style="font-size:26px;font-weight:bold;color:var(--green);">{{ $availableCount }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">Available</div>
            </div>
            <div style="flex:1;min-width:110px;background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--red);padding:.75rem 1rem;text-align:center;">
                <div style="font-size:26px;font-weight:bold;color:var(--red);">{{ $unavailableCount }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">Unavailable</div>
            </div>
            <div style="flex:1;min-width:110px;background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--grey-dark);padding:.75rem 1rem;text-align:center;">
                <div style="font-size:26px;font-weight:bold;color:var(--grey-dark);">{{ $totalMembers - $availabilityResponses->count() }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">No Response</div>
            </div>
            <div style="flex:1;min-width:110px;background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:.75rem 1rem;text-align:center;">
                <div style="font-size:26px;font-weight:bold;color:var(--navy);">{{ $totalMembers }}</div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:3px;">Total Members</div>
            </div>
        </div>
        <div style="margin-bottom:1.25rem;">
            <form method="POST" action="{{ route('admin.events.availability-request', $event->id) }}" style="display:inline;" onsubmit="return confirm('Send availability request email to ALL active members?')">
                @csrf
                <button type="submit" class="btn btn-primary">📣 Send Availability Request</button>
            </form>
        </div>
        @if($availabilityResponses->isEmpty())
            <div class="panel"><div class="empty-state"><div class="empty-icon">📣</div><div class="empty-text">No responses yet. Use the button above to request availability from all members.</div></div></div>
        @else
            @foreach([[true,'var(--green)','✓ Available'],[false,'var(--red)','✕ Unavailable']] as [$avail,$col,$lbl])
            @php $group = $availabilityResponses->where('available', $avail); @endphp
            @if($group->isNotEmpty())
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:{{ $col }};margin:1rem 0 .6rem;display:flex;align-items:center;gap:.5rem;">{{ $lbl }} ({{ $group->count() }})<span style="flex:1;height:1px;background:var(--grey-mid);display:inline-block;"></span></div>
            @foreach($group as $r)
            @php $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $r->user->name),0,2))); @endphp
            <div style="background:#fff;border:1px solid var(--grey-mid);border-left:4px solid {{ $col }};margin-bottom:.5rem;padding:.65rem 1rem;display:flex;align-items:center;gap:.75rem;">
                <div style="width:36px;height:36px;background:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;color:#fff;flex-shrink:0;">{{ $initials }}</div>
                <div style="flex:1;"><div style="font-size:13px;font-weight:bold;">{!! pii($r->user->name, $r->user->piiVisible()) !!}</div>@if($r->user->callsign)<div style="font-size:11px;color:var(--text-muted);">{!! pii($r->user->callsign, $r->user->piiVisible()) !!}</div>@endif</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $r->responded_at?->format('j M H:i') }}</div>
            </div>
            @endforeach
            @endif
            @endforeach
            @php $noResponse = \App\Models\User::where('registration_pending', false)->whereNull('suspended_at')->whereNotIn('id', $availabilityResponses->pluck('user_id'))->orderBy('name')->get(); @endphp
            @if($noResponse->isNotEmpty())
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--grey-dark);margin:1rem 0 .6rem;display:flex;align-items:center;gap:.5rem;">No Response ({{ $noResponse->count() }})<span style="flex:1;height:1px;background:var(--grey-mid);display:inline-block;"></span></div>
            <div style="background:#fff;border:1px solid var(--grey-mid);">
            @foreach($noResponse as $m)
            @php $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $m->name),0,2))); @endphp
            <div style="display:flex;align-items:center;gap:.75rem;padding:.55rem 1rem;border-bottom:1px solid var(--grey-mid);">
                <div style="width:30px;height:30px;background:var(--grey-mid);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;color:#fff;flex-shrink:0;">{{ $initials }}</div>
                <div style="flex:1;"><div style="font-size:13px;font-weight:bold;color:var(--text-muted);">{!! pii($m->name, $m->piiVisible()) !!}</div>@if($m->callsign)<div style="font-size:11px;color:var(--grey-dark);">{!! pii($m->callsign, $m->piiVisible()) !!}</div>@endif</div>
                <span style="font-size:10px;color:var(--grey-dark);">No response</span>
            </div>
            @endforeach
            </div>
            @endif
        @endif
    </div>
<script>
function setBriefingMode(mode) {
    document.getElementById("briefing-send-mode").value = mode;
    document.getElementById("briefing-mode-status").style.display = mode === "status" ? "" : "none";
    document.getElementById("briefing-mode-individual").style.display = mode === "individual" ? "" : "none";
    document.getElementById("mode-btn-status").style.background = mode === "status" ? "var(--navy)" : "var(--grey)";
    document.getElementById("mode-btn-status").style.color = mode === "status" ? "#fff" : "var(--text-muted)";
    document.getElementById("mode-btn-individual").style.background = mode === "individual" ? "var(--navy)" : "var(--grey)";
    document.getElementById("mode-btn-individual").style.color = mode === "individual" ? "#fff" : "var(--text-muted)";
}
function filterBriefingMembers(q) {
    q = q.toLowerCase();
    document.querySelectorAll(".briefing-member-row").forEach(row => {
    });
}
function selectAllBriefingMembers() {
    document.querySelectorAll(".briefing-member-row input").forEach(cb => cb.checked = true);
    updateBriefingMemberCount();
}
function clearAllBriefingMembers() {
    document.querySelectorAll(".briefing-member-row input").forEach(cb => cb.checked = false);
    updateBriefingMemberCount();
}
function updateBriefingMemberCount() {
    const n = document.querySelectorAll(".briefing-member-row input:checked").length;
    const el = document.getElementById("briefing-member-count");
    if (el) el.textContent = n + " selected";
}
</script>
    {{-- ══════════════════════════════ TAB: BULK FILL ══════════════════════════════ --}}
    <div id="tab-bulkfill" class="tab-pane fade-in">
        <div class="action-bar">
            <span style="font-size:12px;color:var(--text-muted);font-weight:bold;">Fill common briefing fields across multiple members at once.</span>
        </div>
        <div class="panel" style="padding:1.5rem;">

            {{-- Member selector --}}
            <div class="section-divider">Select Members to Update</div>
            <div style="display:flex;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap;">
                <button type="button" class="btn btn-ghost" onclick="bulkSelectAll()">☑ Select All</button>
                <button type="button" class="btn btn-ghost" onclick="bulkSelectNone()">☐ Deselect All</button>
            </div>
            <div id="bulk-member-list" style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:1.25rem;max-height:280px;overflow-y:auto;border:1px solid var(--grey-mid);border-radius:4px;padding:.5rem;">
                @foreach($assignments as $asgn)
                <label style="display:flex;align-items:center;gap:.65rem;padding:.45rem .6rem;border-radius:4px;cursor:pointer;transition:background .1s;" onmouseover="this.style.background='var(--navy-faint)'" onmouseout="this.style.background=''">
                    <input type="checkbox" class="bulk-member-cb" value="{{ $asgn->id }}" checked
                           style="width:16px;height:16px;accent-color:var(--navy);flex-shrink:0;cursor:pointer;">
                    <div>
                        <div style="font-size:13px;font-weight:bold;color:var(--navy);">{{ $asgn->user->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $asgn->callsign ?: '—' }} · {{ $asgn->role ?: 'No role' }} · {{ $asgn->location_name ?: 'No location' }}</div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Fields to fill --}}
            <div class="section-divider">Fields to Apply</div>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:1rem;">Leave a field blank to skip it — only filled fields will be applied.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;margin-bottom:1rem;">
                <div class="ff">
                    <label>Frequency</label>
                    <input type="text" id="bulk-frequency" placeholder="145.500 MHz">
                </div>
                <div class="ff">
                    <label>Mode</label>
                    <select id="bulk-mode">
                        <option value="">— don't change —</option>
                        <option value="FM">FM</option>
                        <option value="AM">AM</option>
                        <option value="SSB">SSB</option>
                        <option value="DMR">DMR</option>
                        <option value="C4FM">C4FM</option>
                    </select>
                </div>
                <div class="ff">
                    <label>CTCSS Tone</label>
                    <input type="text" id="bulk-ctcss" placeholder="88.5">
                </div>
                <div class="ff">
                    <label>Channel Label</label>
                    <input type="text" id="bulk-channel" placeholder="CH1">
                </div>
                <div class="ff">
                    <label>Report Time</label>
                    <input type="time" id="bulk-report">
                </div>
                <div class="ff">
                    <label>Depart Time</label>
                    <input type="time" id="bulk-depart">
                </div>
                <div class="ff">
                    <label>Fallback Frequency</label>
                    <input type="text" id="bulk-fal-freq" placeholder="433.500 MHz">
                </div>
                <div class="ff">
                    <label>Fallback Mode</label>
                    <select id="bulk-fal-mode">
                        <option value="">— don't change —</option>
                        <option value="FM">FM</option>
                        <option value="AM">AM</option>
                        <option value="SSB">SSB</option>
                        <option value="DMR">DMR</option>
                        <option value="C4FM">C4FM</option>
                    </select>
                </div>
            </div>
            <div class="ff" style="margin-bottom:.75rem;">
                <label>Briefing Notes (appended to each member's existing notes)</label>
                <textarea id="bulk-notes" placeholder="Notes to add to all selected members…" style="min-height:80px;width:100%;padding:.5rem .7rem;border:1px solid var(--grey-mid);font-family:var(--font);font-size:13px;resize:vertical;"></textarea>
            </div>
            <div class="ff" style="margin-bottom:1.25rem;">
                <label>
                    <input type="checkbox" id="bulk-notes-replace" style="accent-color:var(--navy);margin-right:.3rem;">
                    Replace existing briefing notes instead of appending
                </label>
            </div>

            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <button type="button" class="btn btn-primary" onclick="applyBulkFill()" id="bulk-apply-btn">
                    ⚡ Apply to Selected Members
                </button>
                <button type="button" class="btn btn-ghost" onclick="bulkClearFields()">✕ Clear Fields</button>
                <span id="bulk-status" style="font-size:12px;font-weight:bold;color:var(--green);display:none;"></span>
            </div>
        </div>
    </div>

@endsection

{{-- ════════════════ BULK BRIEFING MODAL ════════════════ --}}
<div class="modal-backdrop" id="briefingModal" onclick="if(event.target===this)document.getElementById('briefingModal').classList.remove('open')">
    <div class="modal" style="max-width:540px;">
        <div class="modal-head">
            <div class="modal-title">✉ Send Team Briefings</div>
            <button class="modal-close" onclick="document.getElementById('briefingModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.events.assignments.briefings-bulk', $event->id) }}" id="bulkBriefingForm">
            @csrf
            <input type="hidden" name="send_mode" id="briefing-send-mode" value="status">
            <div class="modal-body">
                <div style="display:flex;gap:0;margin-bottom:1rem;border:1px solid var(--grey-mid);overflow:hidden;">
                    <button type="button" id="mode-btn-status" onclick="setBriefingMode('status')"
                        style="flex:1;padding:.55rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;border:none;cursor:pointer;font-family:var(--font);background:var(--navy);color:#fff;border-right:1px solid rgba(255,255,255,.2);">By Status</button>
                    <button type="button" id="mode-btn-individual" onclick="setBriefingMode('individual')"
                        style="flex:1;padding:.55rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;border:none;cursor:pointer;font-family:var(--font);background:var(--grey);color:var(--text-muted);">Select Individuals</button>
                </div>
                <div id="briefing-mode-status">
                    <div class="section-divider">Who to send to</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:13px;cursor:pointer;"><input type="checkbox" name="statuses[]" value="confirmed" checked style="accent-color:var(--navy);"> <span style="color:var(--green);font-weight:bold;">&#10003; Confirmed ({{ $stats['confirmed'] }})</span></label>
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:13px;cursor:pointer;"><input type="checkbox" name="statuses[]" value="standby" checked style="accent-color:var(--navy);"> <span style="color:var(--amber);font-weight:bold;">Standby ({{ $stats['standby'] ?? 0 }})</span></label>
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:13px;cursor:pointer;"><input type="checkbox" name="statuses[]" value="pending" style="accent-color:var(--navy);"> <span style="color:var(--navy);font-weight:bold;">Pending ({{ $stats['pending'] }})</span></label>
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:13px;cursor:pointer;"><input type="checkbox" name="statuses[]" value="declined" style="accent-color:var(--navy);"> <span style="color:var(--red);font-weight:bold;">Declined ({{ $stats['declined'] }})</span></label>
                    </div>
                </div>
                <div id="briefing-mode-individual" style="display:none;">
                    <div class="section-divider">Select members to brief</div>
                    <input type="text" placeholder="Search team..." oninput="filterBriefingMembers(this.value)"
                        style="width:100%;border:1px solid var(--grey-mid);padding:.4rem .65rem;font-family:var(--font);font-size:12px;outline:none;margin-bottom:.4rem;">
                    <div style="display:flex;gap:.3rem;margin-bottom:.35rem;">
                        <button type="button" onclick="selectAllBriefingMembers()" style="font-size:10px;font-weight:bold;padding:.28rem .65rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--navy);cursor:pointer;font-family:var(--font);">All</button>
                        <button type="button" onclick="clearAllBriefingMembers()" style="font-size:10px;font-weight:bold;padding:.28rem .65rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--red);cursor:pointer;font-family:var(--font);">Clear</button>
                        <span id="briefing-member-count" style="font-size:11px;color:var(--text-muted);line-height:2;margin-left:.25rem;">0 selected</span>
                    </div>
                    <div style="max-height:220px;overflow-y:auto;border:1px solid var(--grey-mid);">
                        @foreach($assignments->whereNotIn('status',['declined']) as $asgn)
                        <label class="briefing-member-row" data-name="{{ strtolower($asgn->user->name) }}"
                            style="display:flex;align-items:center;gap:.65rem;padding:.55rem .75rem;border-bottom:1px solid var(--grey-mid);cursor:pointer;">
                            <input type="checkbox" name="assignment_ids[]" value="{{ $asgn->id }}"
                                onchange="updateBriefingMemberCount()"
                                style="width:16px;height:16px;flex-shrink:0;accent-color:var(--navy);cursor:pointer;">
                            <span style="flex:1;">
                                <span style="display:block;font-size:13px;font-weight:bold;color:var(--text);">{!! pii($asgn->user->name, $asgn->user->piiVisible()) !!}</span>
                                <span style="font-size:10px;color:var(--text-muted);">{{ $asgn->callsign ?: 'No callsign' }} · {{ ucfirst($asgn->status) }} · {{ $asgn->role ?: 'No role' }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="section-divider" style="margin-top:1rem;">Optional message</div>
                <div class="ff" style="margin-bottom:1rem;">
                    <label>Custom message <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(optional)</small></label>
                    <textarea name="custom_message" rows="3" placeholder="e.g. Please ensure you have read the event plan..."
                        style="width:100%;border:1px solid var(--grey-mid);padding:.5rem .7rem;font-family:var(--font);font-size:13px;resize:vertical;outline:none;"></textarea>
                </div>
                <div style="font-size:11px;color:var(--text-muted);padding:.5rem .75rem;background:var(--grey);border:1px solid var(--grey-mid);">
                    Each member receives a personalised email with their shifts, frequency, location and equipment, plus a PDF attached.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Send briefing emails to selected team?')">&#9993; Send Briefings</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('briefingModal').classList.remove('open')">Cancel</button>
            </div>
        </form>

    </div>
</div>

{{-- ════════════════ SINGLE BRIEFING MODAL ════════════════ --}}
<div class="modal-backdrop" id="singleBriefingModal" onclick="if(event.target===this)document.getElementById('singleBriefingModal').classList.remove('open')">
    <div class="modal" style="max-width:480px;">
        <div class="modal-head">
            <div class="modal-title">✉ Send Briefing — <span id="single-briefing-name"></span></div>
            <button class="modal-close" onclick="document.getElementById('singleBriefingModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" id="singleBriefingForm">
            @csrf
            <div class="modal-body">
                <div class="ff" style="margin-bottom:1rem;">
                    <label>Custom message <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(optional)</small></label>
                    <textarea name="custom_message" rows="4" placeholder="Optional personal message for this operator..." style="width:100%;border:1px solid var(--grey-mid);padding:.5rem .7rem;font-family:var(--font);font-size:13px;resize:vertical;outline:none;"></textarea>
                </div>
                <div style="font-size:11px;color:var(--text-muted);padding:.5rem .75rem;background:var(--grey);border:1px solid var(--grey-mid);">
                    A personalised briefing email with PDF attachment will be sent to this operator.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">✉ Send Briefing</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('singleBriefingModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSingleBriefingModal(id, name) {
    document.getElementById('single-briefing-name').textContent = name;
    document.getElementById('singleBriefingForm').action = '/admin/assignments/' + id + '/briefing';
    document.getElementById('singleBriefingModal').classList.add('open');
}

// ── BULK FILL ─────────────────────────────────────────────────────────────
function bulkSelectAll()  { document.querySelectorAll('.bulk-member-cb').forEach(cb => cb.checked = true); }
function bulkSelectNone() { document.querySelectorAll('.bulk-member-cb').forEach(cb => cb.checked = false); }
function bulkClearFields() {
    ['bulk-frequency','bulk-ctcss','bulk-channel','bulk-report','bulk-depart','bulk-fal-freq','bulk-notes'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    document.getElementById('bulk-mode').value = '';
    document.getElementById('bulk-fal-mode').value = '';
    document.getElementById('bulk-notes-replace').checked = false;
}

async function applyBulkFill() {
    const selected = [...document.querySelectorAll('.bulk-member-cb:checked')].map(cb => parseInt(cb.value));
    if (!selected.length) { alert('Please select at least one member.'); return; }

    const fields = {};
    const get = id => (document.getElementById(id) || {}).value || '';
    if (get('bulk-frequency'))  fields.frequency      = get('bulk-frequency');
    if (get('bulk-mode'))       fields.mode           = get('bulk-mode');
    if (get('bulk-ctcss'))      fields.ctcss_tone     = get('bulk-ctcss');
    if (get('bulk-channel'))    fields.channel_label  = get('bulk-channel');
    if (get('bulk-report'))     fields.report_time    = get('bulk-report');
    if (get('bulk-depart'))     fields.depart_time    = get('bulk-depart');
    if (get('bulk-fal-freq'))   fields.fallback_frequency = get('bulk-fal-freq');
    if (get('bulk-fal-mode'))   fields.fallback_mode  = get('bulk-fal-mode');
    if (get('bulk-notes'))      fields.briefing_notes = get('bulk-notes');
    fields.notes_replace = document.getElementById('bulk-notes-replace').checked ? 1 : 0;

    if (!Object.keys(fields).filter(k => k !== 'notes_replace').length) {
        alert('Please fill in at least one field to apply.'); return;
    }

    const btn = document.getElementById('bulk-apply-btn');
    const status = document.getElementById('bulk-status');
    btn.disabled = true; btn.textContent = '⏳ Applying…';
    status.style.display = 'none';

    try {
        const resp = await fetch(ROUTES.bulkFill, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ assignment_ids: selected, fields: fields }),
        });
        const data = await resp.json();
        if (data.success) {
            status.textContent = '✓ Updated ' + data.updated + ' member(s) successfully';
            status.style.color = 'var(--green)';
            status.style.display = '';
            // Update local AD data
            if (data.assignments) {
                data.assignments.forEach(a => {
                    const idx = AD.findIndex(o => o.id === a.id);
                    if (idx !== -1) AD[idx] = Object.assign(AD[idx], a);
                });
            }
            setTimeout(() => { status.style.display = 'none'; }, 4000);
        } else {
            status.textContent = '⚠ Error: ' + (data.message || 'Unknown error');
            status.style.color = 'var(--red)';
            status.style.display = '';
        }
    } catch(e) {
        status.textContent = '⚠ Request failed';
        status.style.color = 'var(--red)';
        status.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = '⚡ Apply to Selected Members';
    }
}

</script>
