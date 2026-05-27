<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #001f40; line-height: 1.5; }
.page-break { page-break-after: always; }

/* ── COVER ── */
.cover { padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
.cover-header { background: #003366; border-bottom: 6px solid #C8102E; padding: 28px 30px 20px; }
.cover-logo { background: #C8102E; display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: bold; color: #fff; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 10px; }
.cover-group { font-size: 13px; color: rgba(255,255,255,.7); margin-bottom: 6px; letter-spacing: .04em; }
.cover-title { font-size: 26px; font-weight: bold; color: #fff; line-height: 1.2; margin-bottom: 4px; }
.cover-sub { font-size: 13px; color: rgba(255,255,255,.55); }
.cover-body { padding: 24px 30px; flex: 1; }
.cover-meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.cover-meta-table td { padding: 6px 10px; border: 1px solid #dde2e8; font-size: 12px; }
.cover-meta-table .lbl { font-weight: bold; color: #003366; background: #f4f5f7; width: 140px; }
.cover-stats { display: table; width: 100%; margin-bottom: 20px; }
.cover-stat { display: table-cell; text-align: center; padding: 12px 8px; background: #f4f5f7; border: 1px solid #dde2e8; }
.cover-stat-num { font-size: 22px; font-weight: bold; color: #003366; line-height: 1; }
.cover-stat-lbl { font-size: 9px; text-transform: uppercase; letter-spacing: .1em; color: #6b7f96; margin-top: 3px; }
.cover-footer { background: #003366; padding: 10px 30px; text-align: center; font-size: 9px; color: rgba(255,255,255,.5); letter-spacing: .05em; }
.restricted-bar { background: #C8102E; color: #fff; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; padding: 5px 30px; text-align: center; }

/* ── GENERAL ── */
.page-header { background: #003366; border-bottom: 3px solid #C8102E; padding: 10px 18px; margin-bottom: 14px; display: table; width: 100%; }
.page-header-left { display: table-cell; vertical-align: middle; }
.page-header-title { font-size: 14px; font-weight: bold; color: #fff; }
.page-header-sub { font-size: 9px; color: rgba(255,255,255,.55); margin-top: 1px; }
.page-header-right { display: table-cell; text-align: right; vertical-align: middle; font-size: 9px; color: rgba(255,255,255,.45); }
.body { padding: 0 18px 18px; }
.section-title { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .14em; color: #6b7f96; margin: 14px 0 5px; padding-bottom: 2px; border-bottom: 2px solid #003366; }
.detail-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 8px; }
.detail-table td { padding: 4px 7px; border: 1px solid #dde2e8; vertical-align: top; }
.detail-table .lbl { font-weight: bold; color: #003366; background: #f4f5f7; width: 130px; }
.two-col { display: table; width: 100%; }
.col { display: table-cell; width: 48%; vertical-align: top; padding-right: 3%; }
.col:last-child { padding-right: 0; padding-left: 3%; }
.footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #dde2e8; text-align: center; font-size: 8px; color: #9aa3ae; }

/* ── ROSTER TABLE ── */
.roster-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px; }
.roster-table th { background: #003366; color: #fff; padding: 5px 7px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .07em; font-weight: bold; }
.roster-table td { padding: 5px 7px; border: 1px solid #dde2e8; vertical-align: top; }
.roster-table tr:nth-child(even) td { background: #f8f9fb; }
.status-pill { display: inline-block; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 1px 5px; border-radius: 2px; letter-spacing: .05em; }
.status-confirmed { background: rgba(26,107,60,.15); color: #1a6b3c; }
.status-standby   { background: rgba(138,92,0,.15);  color: #8a5c00; }
.status-pending   { background: rgba(0,51,102,.1);   color: #003366; }
.status-declined  { background: rgba(200,16,46,.1);  color: #C8102E; }

/* ── COMMS TABLE ── */
.comms-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px; }
.comms-table th { background: #003366; color: #fff; padding: 5px 7px; font-size: 9px; text-transform: uppercase; letter-spacing: .06em; font-weight: bold; text-align: left; }
.comms-table td { padding: 5px 7px; border: 1px solid #dde2e8; vertical-align: top; }
.comms-table tr:nth-child(even) td { background: #f8f9fb; }

/* ── POI TABLE ── */
.poi-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px; }
.poi-table th { background: #003366; color: #fff; padding: 5px 7px; font-size: 9px; text-transform: uppercase; letter-spacing: .06em; font-weight: bold; text-align: left; }
.poi-table td { padding: 5px 7px; border: 1px solid #dde2e8; vertical-align: top; }
.poi-table tr:nth-child(even) td { background: #f8f9fb; }

/* ── INDIVIDUAL BRIEFING ── */
.brief-header { background: #003366; border-bottom: 3px solid #C8102E; padding: 10px 18px; margin-bottom: 12px; display: table; width: 100%; }
.brief-name { font-size: 15px; font-weight: bold; color: #fff; }
.brief-callsign { font-size: 11px; color: rgba(255,255,255,.65); margin-top: 1px; }
.brief-right { display: table-cell; text-align: right; vertical-align: middle; font-size: 9px; color: rgba(255,255,255,.45); }
.brief-left { display: table-cell; vertical-align: middle; }
.notes-box { background: #fffbec; border-left: 3px solid #c8a030; padding: 7px 9px; font-size: 11px; line-height: 1.6; margin-bottom: 8px; }
.emergency-box { background: #fdf0f2; border-left: 3px solid #C8102E; padding: 7px 9px; font-size: 11px; }
.shift-pill { display: inline-block; background: #e8eef5; border: 1px solid rgba(0,51,102,.2); color: #003366; font-size: 10px; font-weight: bold; padding: 2px 7px; margin: 2px; }
.shift-pill.break { background: #fffbec; border-color: #e8c96a; color: #8a5c00; }
.equip-list { margin: 0; padding: 0; list-style: none; }
.equip-list li { padding: 2px 4px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
.equip-list li::before { content: "\2610  "; }
.sig-line { border-bottom: 1px solid #001f40; height: 24px; margin-bottom: 3px; margin-top: 16px; }
.sig-label { font-size: 8px; text-transform: uppercase; letter-spacing: .08em; color: #9aa3ae; }
</style>
</head>
<body>

{{-- ══ COVER PAGE ══ --}}
<div class="cover">
    <div class="cover-header">
        <div class="cover-logo">RAYNET-UK</div>
        <div class="cover-group">{{ \App\Helpers\RaynetSetting::groupName() }} (Group {{ \App\Helpers\RaynetSetting::groupNumber() }})</div>
        <div class="cover-title">{{ $event->title }}</div>
        <div class="cover-sub">Event Operations Pack</div>
    </div>
    <div class="restricted-bar">Restricted — Authorised Personnel Only</div>
    <div class="cover-body">
        <div class="section-title">Event Summary</div>
        <table class="cover-meta-table">
            <tr><td class="lbl">Event</td><td><strong>{{ $event->title }}</strong></td></tr>
            <tr><td class="lbl">Date</td><td>{{ $event->starts_at?->format('l j F Y') }}</td></tr>
            <tr><td class="lbl">Time</td><td>{{ $event->starts_at?->format('H:i') }}{{ $event->ends_at ? ' – '.$event->ends_at->format('H:i') : '' }}</td></tr>
            <tr><td class="lbl">Location</td><td>{{ $event->location ?: '—' }}</td></tr>
            @if($event->type)<tr><td class="lbl">Event Type</td><td>{{ $event->type->name }}</td></tr>@endif
            @if($event->supporting_group)<tr><td class="lbl">Supporting</td><td>🤝 {{ $event->supporting_group }}</td></tr>@endif
            <tr><td class="lbl">Pack Issued</td><td>{{ now()->format('j M Y H:i') }}</td></tr>
            <tr><td class="lbl">Issued By</td><td>{{ auth()->user()->name }} ({{ auth()->user()->callsign ?? 'No callsign' }})</td></tr>
        </table>

        <div class="section-title">Team Summary</div>
        <table class="cover-stats">
            <tr>
                <td class="cover-stat"><div class="cover-stat-num">{{ $assignments->count() }}</div><div class="cover-stat-lbl">Assigned</div></td>
                <td class="cover-stat"><div class="cover-stat-num" style="color:#1a6b3c;">{{ $assignments->where('status','confirmed')->count() }}</div><div class="cover-stat-lbl">Confirmed</div></td>
                <td class="cover-stat"><div class="cover-stat-num" style="color:#8a5c00;">{{ $assignments->where('status','standby')->count() }}</div><div class="cover-stat-lbl">Standby</div></td>
                <td class="cover-stat"><div class="cover-stat-num">{{ $assignments->whereNotNull('lat')->count() }}</div><div class="cover-stat-lbl">Mapped</div></td>
                <td class="cover-stat"><div class="cover-stat-num">{{ $assignments->where('has_vehicle',true)->count() }}</div><div class="cover-stat-lbl">Vehicles</div></td>
                <td class="cover-stat"><div class="cover-stat-num">{{ $assignments->where('first_aid_trained',true)->count() }}</div><div class="cover-stat-lbl">First Aid</div></td>
            </tr>
        </table>

        @if($event->description)
        <div class="section-title">Event Description</div>
        <div style="font-size:11px;line-height:1.6;margin-bottom:12px;">{{ $event->description }}</div>
        @endif

        <div class="section-title">Contents of This Pack</div>
        <table class="detail-table">
            <tr><td class="lbl">Page 1</td><td>Cover Page &amp; Event Summary</td></tr>
            <tr><td class="lbl">Page 2</td><td>Team Roster &amp; Assignments</td></tr>
            <tr><td class="lbl">Page 3</td><td>Communications Plan</td></tr>
            @if(!empty($pois))<tr><td class="lbl">Page 4</td><td>Checkpoints &amp; Points of Interest</td></tr>@endif
            <tr><td class="lbl">Following pages</td><td>Individual Operator Briefings (one per member)</td></tr>
        </table>
    </div>
    <div class="cover-footer">{{ \App\Helpers\RaynetSetting::groupName() }} · {{ \App\Helpers\RaynetSetting::groupRegion() }} · Affiliated to RAYNET-UK</div>
</div>

<div class="page-break"></div>

{{-- ══ PAGE 2: TEAM ROSTER ══ --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-title">Team Roster</div>
        <div class="page-header-sub">{{ $event->title }} · {{ $event->starts_at?->format('j M Y') }}</div>
    </div>
    <div class="page-header-right">{{ \App\Helpers\RaynetSetting::groupName() }}<br>RESTRICTED</div>
</div>
<div class="body">
    <table class="roster-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Callsign</th>
                <th>Role</th>
                <th>Location</th>
                <th>Grid Ref</th>
                <th>Report</th>
                <th>Depart</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $a)
            <tr>
                <td><strong>{{ $a->user->name }}</strong>{{ $a->first_aid_trained ? ' 🩺' : '' }}{{ $a->has_vehicle ? ' 🚗' : '' }}</td>
                <td>{{ $a->callsign ?: '—' }}</td>
                <td>{{ $a->role ?: '—' }}</td>
                <td>{{ $a->location_name ?: '—' }}</td>
                <td>{{ $a->grid_ref ?: '—' }}</td>
                <td>{{ $a->report_time ? substr($a->report_time,0,5) : '—' }}</td>
                <td>{{ $a->depart_time ? substr($a->depart_time,0,5) : '—' }}</td>
                <td><span class="status-pill status-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($assignments->where('has_vehicle',true)->count())
    <div class="section-title">Vehicles on Duty</div>
    <table class="detail-table">
        @foreach($assignments->where('has_vehicle',true) as $a)
        <tr><td class="lbl">{{ $a->user->name }}</td><td>{{ $a->vehicle_reg ?: 'Reg not recorded' }}</td></tr>
        @endforeach
    </table>
    @endif

    @if($assignments->where('first_aid_trained',true)->count())
    <div class="section-title">First Aid Trained Operators</div>
    <table class="detail-table">
        @foreach($assignments->where('first_aid_trained',true) as $a)
        <tr><td class="lbl">{{ $a->user->name }}</td><td>{{ $a->callsign ?: '—' }} · {{ $a->location_name ?: 'No location' }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="footer">{{ \App\Helpers\RaynetSetting::groupName() }} · Team Roster · {{ now()->format('j M Y H:i') }} · RESTRICTED</div>
</div>

<div class="page-break"></div>

{{-- ══ PAGE 3: COMMS PLAN ══ --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-title">Communications Plan</div>
        <div class="page-header-sub">{{ $event->title }} · {{ $event->starts_at?->format('j M Y') }}</div>
    </div>
    <div class="page-header-right">{{ \App\Helpers\RaynetSetting::groupName() }}<br>RESTRICTED</div>
</div>
<div class="body">
    <table class="comms-table">
        <thead>
            <tr>
                <th>Operator</th>
                <th>Callsign</th>
                <th>Location</th>
                <th>Primary Freq</th>
                <th>Mode</th>
                <th>CTCSS</th>
                <th>Fallback Freq</th>
                <th>Channel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $a)
            <tr>
                <td><strong>{{ $a->user->name }}</strong></td>
                <td>{{ $a->callsign ?: '—' }}</td>
                <td>{{ $a->location_name ?: '—' }}</td>
                <td>{{ $a->frequency ?: '—' }}</td>
                <td>{{ $a->mode ?: '—' }}</td>
                <td>{{ $a->ctcss_tone ?: '—' }}</td>
                <td>{{ $a->fallback_frequency ?: '—' }}</td>
                <td>{{ $a->channel_label ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $withW3w = $assignments->filter(fn($a) => $a->what3words);
        $withGrid = $assignments->filter(fn($a) => $a->grid_ref);
    @endphp

    @if($withGrid->count())
    <div class="section-title">Grid References</div>
    <table class="detail-table">
        @foreach($withGrid as $a)
        <tr><td class="lbl">{{ $a->user->name }}</td><td>{{ $a->grid_ref }} · {{ $a->location_name ?: '—' }}@if($a->lat && $a->lng) · {{ number_format($a->lat,5) }}, {{ number_format($a->lng,5) }}@endif</td></tr>
        @endforeach
    </table>
    @endif

    @if($withW3w->count())
    <div class="section-title">What3Words References</div>
    <table class="detail-table">
        @foreach($withW3w as $a)
        <tr><td class="lbl">{{ $a->user->name }}</td><td>///{{ $a->what3words }} · {{ $a->location_name ?: '—' }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="footer">{{ \App\Helpers\RaynetSetting::groupName() }} · Communications Plan · {{ now()->format('j M Y H:i') }} · RESTRICTED</div>
</div>

@if(!empty($pois))
<div class="page-break"></div>

{{-- ══ PAGE 4: CHECKPOINTS & POIs ══ --}}
<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-title">Checkpoints &amp; Points of Interest</div>
        <div class="page-header-sub">{{ $event->title }} · {{ $event->starts_at?->format('j M Y') }}</div>
    </div>
    <div class="page-header-right">{{ \App\Helpers\RaynetSetting::groupName() }}<br>RESTRICTED</div>
</div>
<div class="body">
    <table class="poi-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Description</th>
                <th>Grid Ref</th>
                <th>What3Words</th>
                <th>Lat / Lng</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pois as $poi)
            @php
                $poiEmojis = ['entrance'=>'🚪','exit'=>'🚪','car_park'=>'🅿','medical'=>'🩺','control'=>'📡','checkpoint'=>'🏁','repeater'=>'📶','hazard'=>'⚠','info'=>'ℹ','custom'=>'🚩'];
                $em = $poiEmojis[$poi['type'] ?? 'custom'] ?? '🚩';
                $typeName = ucfirst(str_replace('_',' ',$poi['type'] ?? 'custom'));
            @endphp
            <tr>
                <td>{{ $em }} {{ $typeName }}</td>
                <td><strong>{{ $poi['name'] ?? '—' }}</strong></td>
                <td>{{ $poi['description'] ?? '—' }}</td>
                <td>{{ $poi['grid_ref'] ?? '—' }}</td>
                <td>{{ isset($poi['w3w']) && $poi['w3w'] ? '///'.$poi['w3w'] : '—' }}</td>
                <td>{{ isset($poi['lat']) ? number_format($poi['lat'],5).', '.number_format($poi['lng'],5) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ \App\Helpers\RaynetSetting::groupName() }} · Checkpoints &amp; POIs · {{ now()->format('j M Y H:i') }} · RESTRICTED</div>
</div>
@endif

{{-- ══ INDIVIDUAL BRIEFING PAGES ══ --}}
@foreach($assignments as $a)
<div class="page-break"></div>

<div class="brief-header">
    <div class="brief-left">
        <div class="brief-name">{{ $a->user->name }}@if($a->callsign) ({{ $a->callsign }})@endif</div>
        <div class="brief-callsign">{{ $event->title }} · Personal Briefing</div>
    </div>
    <div class="brief-right">
        Group {{ \App\Helpers\RaynetSetting::groupNumber() }}<br>
        {{ now()->format('j M Y H:i') }}<br>
        RESTRICTED
    </div>
</div>

<div class="body">
    <div class="two-col">
        <div class="col">
            <div class="section-title">Event Details</div>
            <table class="detail-table">
                <tr><td class="lbl">Event</td><td><strong>{{ $event->title }}</strong></td></tr>
                <tr><td class="lbl">Date</td><td>{{ $event->starts_at?->format('D j M Y') }}</td></tr>
                <tr><td class="lbl">Time</td><td>{{ $event->starts_at?->format('H:i') }}{{ $event->ends_at ? ' – '.$event->ends_at->format('H:i') : '' }}</td></tr>
                <tr><td class="lbl">Location</td><td>{{ $event->location ?: '—' }}</td></tr>
                @if($event->supporting_group)<tr><td class="lbl">Supporting</td><td>{{ $event->supporting_group }}</td></tr>@endif
            </table>

            <div class="section-title">Your Assignment</div>
            <table class="detail-table">
                <tr><td class="lbl">Role</td><td><strong>{{ $a->role ?: '—' }}</strong></td></tr>
                <tr><td class="lbl">Callsign</td><td>{{ $a->callsign ?: '—' }}</td></tr>
                @if($a->report_time)<tr><td class="lbl">Report Time</td><td><strong>{{ substr($a->report_time,0,5) }}</strong></td></tr>@endif
                @if($a->depart_time)<tr><td class="lbl">Depart Time</td><td>{{ substr($a->depart_time,0,5) }}</td></tr>@endif
                @if($a->location_name)<tr><td class="lbl">Position</td><td>{{ $a->location_name }}</td></tr>@endif
                @if($a->grid_ref)<tr><td class="lbl">Grid Ref</td><td>{{ $a->grid_ref }}</td></tr>@endif
                @if($a->what3words)<tr><td class="lbl">What3Words</td><td>///{{ $a->what3words }}</td></tr>@endif
                @if($a->has_vehicle)<tr><td class="lbl">Vehicle</td><td>Yes{{ $a->vehicle_reg ? ' — '.$a->vehicle_reg : '' }}</td></tr>@endif
            </table>
        </div>
        <div class="col">
            <div class="section-title">Communications</div>
            <table class="detail-table">
                <tr><td class="lbl">Primary Freq</td><td><strong>{{ $a->frequency ?: '—' }}</strong></td></tr>
                <tr><td class="lbl">Mode</td><td>{{ $a->mode ?: '—' }}</td></tr>
                @if($a->ctcss_tone)<tr><td class="lbl">CTCSS</td><td>{{ $a->ctcss_tone }}</td></tr>@endif
                @if($a->channel_label)<tr><td class="lbl">Channel</td><td>{{ $a->channel_label }}</td></tr>@endif
                @if($a->secondary_frequency)<tr><td class="lbl">Secondary</td><td>{{ $a->secondary_frequency }} {{ $a->secondary_mode }}</td></tr>@endif
                @if($a->fallback_frequency)<tr><td class="lbl">Fallback</td><td>{{ $a->fallback_frequency }} {{ $a->fallback_mode }}</td></tr>@endif
            </table>

            @if($a->shifts && count($a->shifts))
            <div class="section-title">Shifts</div>
            <div style="margin-bottom:6px;">
                @foreach($a->shifts as $s)
                    <span class="shift-pill {{ $s['type']==='break'?'break':'' }}">
                        {{ $s['type']==='break'?'Break':'Shift' }}: {{ $s['start'] ?? '' }}–{{ $s['end'] ?? '' }}
                        @if(!empty($s['label'])) · {{ $s['label'] }}@endif
                    </span>
                @endforeach
            </div>
            @endif

            @if($a->equipment_items && count($a->equipment_items))
            <div class="section-title">Equipment to Bring</div>
            <ul class="equip-list">
                @foreach($a->equipment_items as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

    @if($a->briefing_notes)
    <div class="section-title">Briefing Notes</div>
    <div class="notes-box">{{ $a->briefing_notes }}</div>
    @endif

    @if($a->medical_notes || $a->emergency_contact_name)
    <div class="section-title">Medical &amp; Emergency Contact</div>
    <div class="emergency-box">
        @if($a->medical_notes)<div><strong>Medical:</strong> {{ $a->medical_notes }}</div>@endif
        @if($a->emergency_contact_name)<div><strong>Emergency Contact:</strong> {{ $a->emergency_contact_name }}{{ $a->emergency_contact_phone ? ' · '.$a->emergency_contact_phone : '' }}</div>@endif
    </div>
    @endif

    <div class="two-col" style="margin-top:16px;">
        <div class="col">
            <div class="sig-line"></div>
            <div class="sig-label">Operator Signature &amp; Date</div>
        </div>
        <div class="col">
            <div class="sig-line"></div>
            <div class="sig-label">Controller Signature &amp; Date</div>
        </div>
    </div>

    <div class="footer">{{ \App\Helpers\RaynetSetting::groupName() }} · {{ $a->user->name }} · Personal Briefing · RESTRICTED</div>
</div>
@endforeach

</body>
</html>
