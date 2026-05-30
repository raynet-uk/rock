@extends('layouts.admin')
@section('title', 'Net Control Dashboard')
@section('content')
@php
use App\Models\NetSchedule;
$bands     = NetSchedule::$bands;
$priorities= NetSchedule::$priorities;

$sevenDay = [];
foreach ($schedules->where('is_active', true) as $s) {
    foreach ($s->nextOccurrences(7) as $occ) {
        $sevenDay[] = $occ;
    }
}
usort($sevenDay, fn($a,$b) => $a['start']->timestamp <=> $b['start']->timestamp);

$conflicts = [];
foreach ($schedules->where('is_active',true) as $a) {
    foreach ($schedules->where('is_active',true) as $b) {
        if ($a->id >= $b->id) continue;
        $sharedDays = array_intersect($a->days_of_week ?? [], $b->days_of_week ?? []);
        if (empty($sharedDays)) continue;
        $aStart = \Carbon\Carbon::createFromFormat('H:i:s',$a->start_time);
        $aEnd   = \Carbon\Carbon::createFromFormat('H:i:s',$a->end_time);
        $bStart = \Carbon\Carbon::createFromFormat('H:i:s',$b->start_time);
        $bEnd   = \Carbon\Carbon::createFromFormat('H:i:s',$b->end_time);
        if ($aStart->lt($bEnd) && $bStart->lt($aEnd)) {
            $conflicts[] = ['a'=>$a,'b'=>$b,'days'=>$sharedDays];
        }
    }
}
@endphp
<style>
:root{--navy:#003366;--red:#C8102E;--border:#dde2e8;--muted:#6b7f96;--light:#f2f5f9;}
*{box-sizing:border-box;}
.nc-wrap{max-width:1200px;margin:0 auto;padding:2rem 1rem 5rem;}
.nc-header{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;}
.nc-heading{font-size:1.6rem;font-weight:900;color:var(--navy);letter-spacing:-.02em;}
.nc-heading span{color:var(--red);}
.nc-sub{font-size:.85rem;color:var(--muted);margin-top:.2rem;}
.nc-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:2rem;overflow-x:auto;}
.nc-tab{padding:.65rem 1.4rem;font-size:.88rem;font-weight:700;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s;white-space:nowrap;}
.nc-tab:hover{color:var(--navy);}
.nc-tab.active{color:var(--navy);border-bottom-color:var(--red);}
.nc-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;}
.nc-card-title{font-size:1rem;font-weight:800;color:var(--navy);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
@media(max-width:640px){.grid-2,.grid-3{grid-template-columns:1fr;}}
.field{margin-bottom:1rem;}
.label{display:block;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--navy);margin-bottom:.4rem;}
.input{width:100%;padding:.55rem .75rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;font-family:inherit;transition:border .2s;background:#fff;}
.input:focus{outline:none;border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,51,102,.07);}
select.input{background:#fff;}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;background:var(--light);border-radius:10px;margin-bottom:1rem;}
.toggle-label{font-size:.95rem;font-weight:700;color:var(--navy);}
.toggle-sub{font-size:.8rem;color:var(--muted);margin-top:.1rem;}
.toggle-switch{position:relative;width:52px;height:28px;flex-shrink:0;}
.toggle-switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;inset:0;background:var(--border);border-radius:999px;cursor:pointer;transition:.3s;}
.slider:before{content:'';position:absolute;width:22px;height:22px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.2);}
input:checked+.slider{background:var(--red);}
input:checked+.slider:before{transform:translateX(24px);}
.days-grid{display:flex;gap:.4rem;flex-wrap:wrap;}
.day-btn{position:relative;}
.day-btn input{position:absolute;opacity:0;width:0;height:0;}
.day-btn label{display:flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;font-size:.78rem;font-weight:800;text-transform:uppercase;cursor:pointer;border:2px solid var(--border);color:var(--muted);transition:all .2s;user-select:none;}
.day-btn input:checked+label{background:var(--navy);color:#fff;border-color:var(--navy);}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.4rem;border-radius:999px;font-size:.88rem;font-weight:700;cursor:pointer;border:none;transition:all .2s;text-decoration:none;}
.btn-primary{background:var(--navy);color:#fff;}
.btn-primary:hover{background:#002244;}
.btn-danger{background:var(--red);color:#fff;}
.btn-ghost{background:var(--light);color:var(--navy);border:1.5px solid var(--border);}
.btn-success{background:#059669;color:#fff;}
.btn-sm{padding:.35rem .9rem;font-size:.8rem;}
.sched-table{width:100%;border-collapse:collapse;}
.sched-table th{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:.6rem .75rem;text-align:left;border-bottom:2px solid var(--border);}
.sched-table td{padding:.75rem;border-bottom:1px solid #f0f3f7;vertical-align:middle;font-size:.88rem;}
.sched-table tr:last-child td{border-bottom:none;}
.sched-table tr:hover td{background:#fafbfd;}
.badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.72rem;font-weight:800;padding:.2rem .55rem;border-radius:999px;}
.badge-live{background:#d1fae5;color:#065f46;}
.badge-off{background:#f3f4f6;color:#6b7280;}
.badge-auto{background:#dbeafe;color:#1e40af;}
.badge-urgent{background:#fef3c7;color:#92400e;}
.badge-emergency{background:#fee2e2;color:#991b1b;}
.day-chip{display:inline-block;font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:4px;background:#e8edf4;color:var(--navy);margin:.1rem .1rem .1rem 0;text-transform:uppercase;}
.alert-success{background:#d1fae5;border-left:4px solid #059669;padding:.65rem 1rem;border-radius:6px;font-size:.88rem;color:#065f46;font-weight:700;margin-bottom:1.25rem;}
.alert-error{background:#fee2e2;border-left:4px solid var(--red);padding:.65rem 1rem;border-radius:6px;font-size:.88rem;color:#991b1b;font-weight:700;margin-bottom:1.25rem;}
.alert-warning{background:#fef3c7;border-left:4px solid #d97706;padding:.65rem 1rem;border-radius:6px;font-size:.88rem;color:#92400e;font-weight:700;margin-bottom:1.25rem;}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-backdrop.open{display:flex;}
.modal{background:#fff;border-radius:14px;padding:2rem;width:100%;max-width:640px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);}
.modal-title{font-size:1.15rem;font-weight:900;color:var(--navy);margin-bottom:1.25rem;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;}
@media(max-width:768px){.stat-grid{grid-template-columns:repeat(2,1fr);}}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:10px;padding:1.1rem 1.25rem;}
.stat-val{font-size:1.8rem;font-weight:900;color:var(--navy);line-height:1;}
.stat-lbl{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-top:.3rem;}
.tab-pane{display:none;}
.tab-pane.active{display:block;}
.preview-wrap{background:#0a0a1a;border-radius:10px;border:1px solid #1a1a3e;overflow:hidden;position:relative;}
.preview-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(200,16,46,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(200,16,46,.04) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;}
.preview-inner{max-width:100%;padding:1rem 1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;position:relative;}
.slot-row{display:flex;gap:.5rem;align-items:center;background:var(--light);border-radius:8px;padding:.5rem .75rem;margin-bottom:.5rem;}
.section-sep{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin:1.25rem 0 .75rem;padding-bottom:.4rem;border-bottom:1px solid var(--border);}
@keyframes pulse{0%,100%{transform:scale(1);opacity:.6;}50%{transform:scale(2);opacity:0;}}
</style>

<div class="nc-wrap">
<div class="nc-header">
  <div>
    <div class="nc-heading">📻 Net Control <span>Dashboard</span></div>
    <div class="nc-sub">Manage live net status, scheduled nets, and session history</div>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
    <button class="btn btn-ghost btn-sm" onclick="openModal('modal7day')">📅 7-Day Preview</button>
    <button class="btn btn-primary" onclick="openModal('modalAdd')">+ New Schedule</button>
  </div>
</div>

@if(session('success'))<div class="alert-success">✓ {{ session('success') }}</div>@endif
@if($errors->any())<div class="alert-error">✗ {{ $errors->first() }}</div>@endif

@if(!empty($conflicts))
<div class="alert-warning">
  ⚠ <strong>Schedule conflicts detected:</strong>
  @foreach($conflicts as $c)
    <br>· <strong>{{ $c['a']->callsign }}</strong> and <strong>{{ $c['b']->callsign }}</strong> overlap on {{ implode(', ', $c['days']) }}
  @endforeach
</div>
@endif

@php
  $liveNow = $schedules->filter(fn($s) => $s->isLiveNow())->first();
@endphp

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-val" style="color:{{ $settings['net_active']==='1' ? '#C8102E' : '#003366' }}">{{ $settings['net_active']==='1' ? 'LIVE' : 'OFF' }}</div>
    <div class="stat-lbl">Net Status</div>
  </div>
  <div class="stat-card">
    <div class="stat-val">{{ $schedules->count() }}</div>
    <div class="stat-lbl">Schedules</div>
  </div>
  <div class="stat-card">
    <div class="stat-val" style="color:#059669">{{ $schedules->where('is_active',true)->count() }}</div>
    <div class="stat-lbl">Active</div>
  </div>
  <div class="stat-card">
    <div class="stat-val">{{ count($sevenDay) }}</div>
    <div class="stat-lbl">Next 7 Days</div>
  </div>
</div>

<div class="nc-tabs">
  <div class="nc-tab active" onclick="switchTab('live',this)">🔴 Live Net</div>
  <div class="nc-tab" onclick="switchTab('schedules',this)">📅 Schedules</div>
  <div class="nc-tab" onclick="switchTab('calendar',this)">🗓 Calendar</div>
  <div class="nc-tab" onclick="switchTab('sessions',this)">📋 Session Log</div>
    <div class="nc-tab" onclick="switchTab('checkins',this)">📻 Station Log</div>
    <div class="nc-tab" onclick="switchTab('loghistory',this)">📂 Net Log History</div>
</div>

{{-- LIVE TAB --}}
<div class="tab-pane active" id="tab-live">
  @if($liveNow)
  <div style="background:linear-gradient(135deg,rgba(200,16,46,.08),rgba(0,51,102,.05));border:1.5px solid rgba(200,16,46,.25);border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;">
    <span style="font-size:1.2rem;">⏰</span>
    <div style="flex:1;">
      <div style="font-size:.85rem;font-weight:800;color:#C8102E;">Scheduled Net Active Now</div>
      <div style="font-size:.8rem;color:var(--muted);margin-top:.1rem;">{{ $liveNow->name }} — {{ $liveNow->callsign }} on {{ $liveNow->frequency }} until {{ substr($liveNow->end_time,0,5) }}</div>
    </div>
    @if($liveNow->priority !== 'routine')
    <span class="badge badge-{{ $liveNow->priority }}">{{ ucfirst($liveNow->priority) }}</span>
    @endif
  </div>
  @endif

  <form method="POST" action="{{ route('admin.events.net-status.update') }}" id="liveNetForm">
    @csrf
    <div class="nc-card">
      <div class="nc-card-title">⚡ Live Status Control</div>
      <div class="toggle-row">
        <div><div class="toggle-label">Net Active</div><div class="toggle-sub">Show the live net banner on the homepage</div></div>
        <label class="toggle-switch"><input type="checkbox" name="net_active" value="1" id="netActiveToggle" {{ ($settings['net_active']??'0')==='1'?'checked':'' }}><span class="slider"></span></label>
      </div>
      <div class="toggle-row">
        <div><div class="toggle-label">Station Logging</div><div class="toggle-sub">Enable the station check-in log and show counter on the banner</div></div>
        <label class="toggle-switch"><input type="checkbox" name="net_station_logging" value="1" {{ ($settings['net_station_logging']??'0')==='1'?'checked':'' }}><span class="slider"></span></label>
      </div>
      <div class="grid-2">
        <div class="field"><label class="label">Callsign</label><input type="text" name="net_callsign" class="input" value="{{ $settings['net_callsign']??'' }}" id="fCallsign"></div>
        <div class="field"><label class="label">Frequency</label><input type="text" name="net_frequency" class="input" value="{{ $settings['net_frequency']??'' }}" id="fFreq"></div>
        <div class="field" style="grid-column:1/-1;">
          <div class="section-sep" style="margin:.25rem 0 .5rem;">Controller Time Slots <span style="font-weight:400;font-size:.8rem;">(optional) — callsign shown on banner changes automatically by time</span></div>
          <div id="liveSlots" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.5rem;"></div>
          <button type="button" onclick="addLiveSlot()" class="btn btn-ghost btn-sm">+ Add Controller Slot</button>
        </div>
        <div class="field">
          <label class="label">Band</label>
          <select name="net_band" class="input" id="fBand">
            <option value="">— Select Band —</option>
            @foreach($bands as $key => $b)
            <option value="{{ $key }}" {{ ($settings['net_band']??'')===$key?'selected':'' }}>{{ $b['label'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="field"><label class="label">Description</label><input type="text" name="net_description" class="input" value="{{ $settings['net_description']??'' }}" id="fDesc"></div>
        <div class="field"><label class="label">Announcement</label><input type="text" name="net_announcement" class="input" placeholder="e.g. Emergency exercise tonight — all welcome" value="{{ $settings['net_announcement']??'' }}"></div>
        <div class="field">
          <label class="label">Priority</label>
          <select name="net_priority" class="input">
            @foreach($priorities as $key => $p)
            <option value="{{ $key }}" {{ ($settings['net_priority']??'routine')===$key?'selected':'' }}>{{ $p['label'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="field"><label class="label">Start Time</label><input type="time" name="net_start_time" class="input" value="{{ $settings['net_start_time']??'' }}"></div>
        <div class="field"><label class="label">End Time</label><input type="time" name="net_end_time" class="input" value="{{ $settings['net_end_time']??'' }}"></div>
      </div>
      <input type="hidden" name="net_controller_slots_json" id="liveSlotsJson">
      <button type="submit" class="btn btn-primary">Save Live Status</button>
    </div>
  </form>

  <div class="nc-card">
    <div class="nc-card-title">👁 Live Preview</div>
    <div class="preview-wrap">
      <div class="preview-grid"></div>
      <div class="preview-inner">
        <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
          <div style="position:relative;width:12px;height:12px;">
            <span style="position:absolute;inset:0;background:#C8102E;border-radius:50%;opacity:.6;animation:pulse 1.5s infinite;"></span>
            <span style="position:absolute;inset:1px;background:#ff1a3a;border-radius:50%;"></span>
          </div>
          <span style="font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.2em;color:#ff4466;">Live Net</span>
        </div>
        <div style="width:1px;height:36px;background:linear-gradient(to bottom,transparent,rgba(200,16,46,.5),transparent);flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <span style="font-size:1.1rem;font-weight:900;color:#fff;font-family:monospace;" id="previewCallsign">{{ strtoupper($settings['net_callsign']?:'CALLSIGN') }}</span>
            <span id="previewBandBadge" style="display:none;font-size:.72rem;font-weight:900;padding:.15rem .5rem;border-radius:4px;font-family:monospace;letter-spacing:.05em;"></span>
            <span style="font-size:.88rem;font-weight:700;color:#C8102E;font-family:monospace;background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);padding:.1rem .5rem;border-radius:4px;" id="previewFreq">{{ $settings['net_frequency']?:'000.000 MHz' }}</span>
          </div>
          <div style="font-size:.82rem;color:rgba(255,255,255,.55);margin-top:.2rem;" id="previewDesc">{{ $settings['net_description']?:'Net description' }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:1.25rem;flex-shrink:0;">
          <div style="text-align:center;" id="previewCtrlRow">
            <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);">Controller</div>
            <div style="font-size:.9rem;font-weight:800;color:#fff;font-family:monospace;" id="previewCtrl">{{ strtoupper($settings['net_controller']??'') }}</div>
          </div>
          <div style="background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;font-size:.75rem;font-weight:800;padding:.4rem .9rem;border-radius:999px;">Join Net →</div>
        </div>
      </div>
    </div>
    <div style="font-size:.78rem;margin-top:.6rem;" id="previewStatusNote">
      @if(($settings['net_active']??'0')!=='1')
      <span style="color:#f59e0b;">⚠ Net is currently <strong>inactive</strong></span>
      @else
      <span style="color:#059669;">✓ Banner is <strong>live</strong> on the homepage</span>
      @endif
    </div>
  </div>
</div>

{{-- SCHEDULES TAB --}}
<div class="tab-pane" id="tab-schedules">
  <div class="nc-card" style="padding:0;overflow:hidden;">
    @if($schedules->isEmpty())
    <div style="padding:3rem;text-align:center;color:var(--muted);">
      <div style="font-size:2rem;margin-bottom:.5rem;">📅</div>
      <div style="font-weight:700;">No schedules yet</div>
      <button class="btn btn-primary" style="margin-top:1rem;" onclick="openModal('modalAdd')">+ New Schedule</button>
    </div>
    @else
    <table class="sched-table">
      <thead><tr>
        <th>Callsign / Name</th><th>Days</th><th>Time</th><th>Repeat</th><th>Band</th><th>Priority</th><th>Auto</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
      @foreach($schedules as $sched)
      <tr>
        <td>
          <div style="font-weight:800;color:var(--navy);font-family:monospace;">{{ strtoupper($sched->callsign) }}</div>
          <div style="font-size:.78rem;color:var(--muted);">{{ $sched->name }}</div>
        </td>
        <td>@foreach($sched->days_of_week as $d)<span class="day-chip">{{ $d }}</span>@endforeach</td>
        <td style="font-family:monospace;font-size:.88rem;color:var(--navy);">{{ substr($sched->start_time,0,5) }}–{{ substr($sched->end_time,0,5) }}</td>
        <td style="font-size:.82rem;color:var(--muted);">{{ ucfirst($sched->repeat_type ?? 'weekly') }}</td>
        <td>
          @if($sched->band && isset($bands[$sched->band]))
          <span style="font-size:.72rem;font-weight:900;color:{{ $bands[$sched->band]['colour'] }};background:{{ $bands[$sched->band]['bg'] }};border:1px solid {{ $bands[$sched->band]['border'] }};padding:.15rem .45rem;border-radius:4px;font-family:monospace;">{{ $bands[$sched->band]['label'] }}</span>
          @else<span style="color:var(--muted);font-size:.8rem;">—</span>@endif
        </td>
        <td>
          @if($sched->priority === 'emergency')<span class="badge badge-emergency">Emergency</span>
          @elseif($sched->priority === 'urgent')<span class="badge badge-urgent">Urgent</span>
          @else<span style="font-size:.8rem;color:var(--muted);">Routine</span>@endif
        </td>
        <td>@if($sched->auto_activate)<span class="badge badge-auto">Auto</span>@else<span style="color:#9ca3af;font-size:.8rem;">Manual</span>@endif</td>
        <td>
          @if($sched->isLiveNow())<span class="badge badge-live">🔴 Live</span>
          @elseif($sched->is_active)<span class="badge badge-auto">Active</span>
          @else<span class="badge badge-off">Paused</span>@endif
        </td>
        <td>
          <div style="display:flex;gap:.3rem;justify-content:flex-end;flex-wrap:wrap;">
            <button class="btn btn-ghost btn-sm" onclick="openEditModal({{ $sched->toJson() }})">Edit</button>
            <button class="btn btn-ghost btn-sm" onclick="cloneSchedule({{ $sched->id }})">Clone</button>
            <form method="POST" action="{{ route('admin.events.net-schedule.toggle', $sched->id) }}" style="display:inline;">
              @csrf @method('PATCH')
              <button class="btn btn-sm {{ $sched->is_active ? 'btn-ghost' : 'btn-success' }}">{{ $sched->is_active ? 'Pause' : 'Enable' }}</button>
            </form>
            <form method="POST" action="{{ route('admin.events.net-schedule.destroy', $sched->id) }}" style="display:inline;" onsubmit="return confirm('Delete this schedule?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">✕</button>
            </form>
          </div>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>

{{-- CALENDAR TAB --}}
<div class="tab-pane" id="tab-calendar">
  <div class="nc-card">
    <div class="nc-card-title">🗓 Next 7 Days</div>
    @if(empty($sevenDay))
    <div style="text-align:center;padding:2rem;color:var(--muted);">No scheduled nets in the next 7 days.</div>
    @else
    <div style="display:flex;flex-direction:column;gap:.75rem;">
      @foreach($sevenDay as $occ)
      @php $s = $occ['schedule']; $bm = isset($bands[$s->band]) ? $bands[$s->band] : null; @endphp
      <div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1rem;background:{{ $s->priority==='emergency'?'#fee2e2':($s->priority==='urgent'?'#fef3c7':'#f8fafd') }};border-radius:10px;border:1px solid var(--border);">
        <div style="min-width:70px;text-align:center;">
          <div style="font-size:1.1rem;font-weight:900;color:var(--navy);">{{ \Carbon\Carbon::parse($occ['date'])->format('D') }}</div>
          <div style="font-size:.75rem;color:var(--muted);">{{ \Carbon\Carbon::parse($occ['date'])->format('j M') }}</div>
        </div>
        <div style="width:1px;height:40px;background:var(--border);"></div>
        <div style="flex:1;">
          <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <span style="font-weight:900;color:var(--navy);font-family:monospace;">{{ strtoupper($s->callsign) }}</span>
            @if($bm)<span style="font-size:.7rem;font-weight:900;color:{{ $bm['colour'] }};background:{{ $bm['bg'] }};border:1px solid {{ $bm['border'] }};padding:.1rem .4rem;border-radius:4px;font-family:monospace;">{{ $bm['label'] }}</span>@endif
            @if($s->priority !== 'routine')<span class="badge badge-{{ $s->priority }}">{{ ucfirst($s->priority) }}</span>@endif
          </div>
          <div style="font-size:.82rem;color:var(--muted);margin-top:.2rem;">{{ $s->name }} · {{ $s->frequency ?: '—' }} · Controller: {{ strtoupper($s->controller ?: '—') }}</div>
        </div>
        <div style="font-family:monospace;font-size:.9rem;font-weight:800;color:var(--navy);white-space:nowrap;">
          {{ $occ['start']->format('H:i') }} – {{ $occ['end']->format('H:i') }}
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>
</div>

{{-- SESSIONS TAB --}}
<div class="tab-pane" id="tab-sessions">
  <div class="nc-card">
    <div class="nc-card-title">📋 Recent Net Sessions</div>
    @if($sessions->isEmpty())
    <div style="text-align:center;padding:2rem;color:var(--muted);"><div style="font-size:1.5rem;margin-bottom:.5rem;">📭</div>No sessions recorded yet.</div>
    @else
    @foreach($sessions as $session)
    <div style="display:flex;align-items:flex-start;gap:1rem;padding:.85rem 0;border-bottom:1px solid #f0f3f7;">
      <div style="width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:.4rem;background:{{ $session->status==='open'?'#059669':'#9ca3af' }};"></div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
          <span style="font-weight:800;color:var(--navy);font-family:monospace;">{{ strtoupper($session->name) }}</span>
          <span class="badge {{ $session->status==='open'?'badge-live':'badge-off' }}">{{ ucfirst($session->status) }}</span>
          @if($session->frequency)<span style="font-size:.78rem;font-family:monospace;color:var(--red);">{{ $session->frequency }}</span>@endif
        </div>
        <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem;">
          Started {{ $session->created_at->diffForHumans() }}
          @if($session->closed_at) · Closed {{ \Carbon\Carbon::parse($session->closed_at)->diffForHumans() }}@endif
          @if($session->net_control) · Controller: <strong>{{ strtoupper($session->net_control) }}</strong>@endif
        </div>
      </div>
      <div style="font-size:.75rem;color:#9ca3af;white-space:nowrap;">{{ $session->created_at->format('d M H:i') }}</div>
    </div>
    @endforeach
    @endif
  </div>
</div>
<div class="tab-pane" id="tab-checkins">

  {{-- Status banner --}}
  <div id="ciStatusBanner" style="border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.82rem;font-weight:700;"></div>

  {{-- Entry form --}}
  <div class="nc-card" id="ciFormCard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
      <div class="nc-card-title" style="margin:0;">📻 Log a Station</div>
      <div style="display:flex;align-items:center;gap:.75rem;">
        <span id="ciLiveCount" style="font-size:.82rem;font-weight:700;color:var(--muted);">0 stations</span>
        <a href="{{ route('admin.events.station-log.export-pdf') }}" target="_blank"
           style="font-size:.78rem;font-weight:700;color:#C8102E;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;padding:.3rem .75rem;text-decoration:none;white-space:nowrap;">
          ⬇ Export PDF
        </a>
      </div>
    </div>

    {{-- QRZ preview card --}}
    <div id="ciQrzCard" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:linear-gradient(135deg,#f0f4ff,#f8fafc);border:1px solid #c7d7ff;border-radius:10px;">
      <div style="display:flex;align-items:center;gap:1rem;">
        <img id="ciQrzPhoto" src="" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #c7d7ff;display:none;flex-shrink:0;">
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <span id="ciQrzCallsign" style="font-family:monospace;font-weight:900;font-size:1.1rem;color:#003366;"></span>
            <span id="ciQrzLicence" style="display:none;font-size:.7rem;font-weight:800;padding:.15rem .5rem;border-radius:999px;background:#dcfce7;color:#15803d;"></span>
            <span id="ciQrzRegistered" style="display:none;font-size:.7rem;font-weight:800;padding:.15rem .5rem;border-radius:999px;background:#fef9c3;color:#a16207;">✓ On RAYNET</span>
            <a id="ciQrzLink" href="#" target="_blank" style="font-size:.7rem;color:#6366f1;font-weight:700;text-decoration:none;">QRZ ↗</a>
          </div>
          <div id="ciQrzName" style="font-weight:700;color:#334155;font-size:.9rem;margin-top:.15rem;"></div>
          <div style="display:flex;gap:1rem;margin-top:.2rem;flex-wrap:wrap;">
            <span id="ciQrzLocation" style="font-size:.75rem;color:#64748b;display:none;">📍 <span></span></span>
            <span id="ciQrzGrid" style="font-size:.75rem;color:#64748b;font-family:monospace;display:none;">Grid: <span></span></span>
            <span id="ciQrzDxcc" style="font-size:.75rem;color:#64748b;display:none;">DXCC: <span></span></span>
            <span id="ciQrzLotw" style="font-size:.75rem;color:#64748b;display:none;">LoTW ✓</span>
          </div>
        </div>
        <div id="ciInviteBtn" style="display:none;">
          <button onclick="openInviteModal()" style="font-size:.75rem;font-weight:700;background:linear-gradient(135deg,#003366,#001a33);color:#fff;border:none;border-radius:8px;padding:.4rem .85rem;cursor:pointer;white-space:nowrap;">
            ✉ Invite to RAYNET
          </button>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 2fr auto;gap:.75rem;align-items:start;">
      <div>
        <label class="label">Callsign *</label>
        <input type="text" id="ciCallsign" class="input"
               placeholder="e.g. G4BDS"
               style="text-transform:uppercase;font-family:monospace;font-weight:800;font-size:.95rem;letter-spacing:.05em;"
               maxlength="20" autocomplete="off">
      </div>
      <div>
        <label class="label">Signal Report</label>
        <input type="text" id="ciReport" class="input" placeholder="59" maxlength="10"
               style="font-family:monospace;font-weight:700;">
      </div>
      <div>
        <label class="label">Notes</label>
        <input type="text" id="ciNotes" class="input" placeholder="Optional notes">
      </div>
      <div style="padding-top:1.6rem;">
        <button onclick="logCheckin()" id="ciSubmitBtn" class="btn btn-primary" style="width:100%;white-space:nowrap;">
          + Log
        </button>
      </div>
    </div>
    <div id="ciError" style="color:#C8102E;font-size:.78rem;margin-top:.6rem;display:none;padding:.4rem .6rem;background:#fff1f2;border-radius:6px;border:1px solid #fecdd3;"></div>
  </div>

  {{-- Log table --}}
  <div class="nc-card" style="padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,#fafbff,#fff);">
      <div style="font-weight:800;color:var(--navy);font-size:.95rem;">Station Log</div>
      <div style="display:flex;gap:.5rem;">
        <button onclick="archiveAndClear()" style="font-size:.75rem;font-weight:700;color:#003366;background:#f0f4ff;border:1px solid #c7d7ff;border-radius:6px;padding:.25rem .65rem;cursor:pointer;white-space:nowrap;">
          📂 Archive &amp; Clear
        </button>
        <button onclick="clearLog()" style="font-size:.75rem;font-weight:700;color:#C8102E;background:none;border:1px solid #fecdd3;border-radius:6px;padding:.25rem .65rem;cursor:pointer;">
          Clear
        </button>
      </div>
    </div>
    {{-- Table header --}}
    <div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 6rem 2.5rem;gap:.5rem;padding:.5rem 1.25rem;background:#f8fafc;border-bottom:1px solid var(--border);">
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">#</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Time</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Callsign</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Name</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Licence</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Location</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Grid</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Signal</div>
      <div style="font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Member</div>
      <div></div>
    </div>
    <div id="ciTypingBar" style="display:none;padding:.4rem 1.25rem;background:#f0f4ff;border-bottom:1px solid #c7d7ff;font-size:.75rem;color:#4338ca;font-weight:600;transition:all .3s;"></div>
    <div id="ciLog"></div>
    <div id="ciEmpty" style="text-align:center;padding:2.5rem;color:var(--muted);font-size:.85rem;">
      <div style="font-size:1.5rem;margin-bottom:.5rem;">📭</div>No stations logged yet
    </div>
  </div>
</div>

<div class="tab-pane" id="tab-loghistory">
  <div class="nc-card" style="padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,#fafbff,#fff);">
      <div style="font-weight:800;color:var(--navy);font-size:.95rem;">📂 Net Log History</div>
      <div id="histCount" style="font-size:.8rem;color:var(--muted);font-weight:700;"></div>
    </div>
    <div id="histList" style="min-height:80px;"></div>
    <div id="histEmpty" style="text-align:center;padding:2.5rem;color:var(--muted);font-size:.85rem);">
      <div style="font-size:1.5rem;margin-bottom:.5rem;">📭</div>No net logs archived yet
    </div>
  </div>
</div>



{{-- Offline Sync Modal --}}
<div id="offlineSyncModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1002;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:2rem;max-width:520px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
      <div style="font-size:1.5rem;">📶</div>
      <div>
        <div style="font-size:1rem;font-weight:900;color:var(--navy);">Connection Restored</div>
        <div style="font-size:.78rem;color:var(--muted);margin-top:.1rem;">You have offline-logged stations ready to sync</div>
      </div>
    </div>
    <div id="offlineSyncList" style="background:#f8fafc;border-radius:8px;border:1px solid var(--border);margin-bottom:1.25rem;overflow:hidden;"></div>
    <div id="offlineSyncError" style="display:none;color:#C8102E;font-size:.78rem;margin-bottom:.75rem;padding:.4rem .6rem;background:#fff1f2;border-radius:6px;border:1px solid #fecdd3;"></div>
    <div style="display:flex;gap:.75rem;">
      <button id="offlineSyncImportBtn" onclick="importOfflineQueue()" class="btn btn-primary" style="flex:1;">📥 Import All to Station Log</button>
      <button onclick="discardOfflineQueue()" style="font-size:.82rem;font-weight:700;color:#C8102E;background:none;border:1px solid #fecdd3;border-radius:8px;padding:.5rem .85rem;cursor:pointer;">Discard</button>
      <button onclick="closeOfflineSyncModal()" style="font-size:.82rem;font-weight:700;color:var(--muted);background:none;border:1px solid var(--border);border-radius:8px;padding:.5rem .85rem;cursor:pointer;">Later</button>
    </div>
  </div>
</div>

{{-- Invite Modal --}}
<div id="inviteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:2rem;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <div style="font-size:1.1rem;font-weight:900;color:var(--navy);">✉ Invite to RAYNET</div>
      <button onclick="closeInviteModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    <div id="inviteCallsignDisplay" style="font-family:monospace;font-size:1.2rem;font-weight:900;color:var(--navy);background:#f0f4ff;padding:.4rem .85rem;border-radius:8px;display:inline-block;margin-bottom:1rem;"></div>
    <div id="inviteQrzEmailRow" style="display:none;margin-bottom:.75rem;padding:.6rem .85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:.82rem;">
      <div style="font-weight:700;color:#15803d;margin-bottom:.3rem;">📧 Email found on QRZ:</div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span id="inviteQrzEmail" style="font-family:monospace;font-weight:700;color:#334155;"></span>
        <button onclick="useQrzEmail()" style="font-size:.72rem;font-weight:700;background:#15803d;color:#fff;border:none;border-radius:5px;padding:.2rem .55rem;cursor:pointer;">Use this</button>
      </div>
    </div>
    <div class="field">
      <label class="label">Email Address *</label>
      <input type="email" id="inviteEmail" class="input" placeholder="operator@example.com">
    </div>
    <div id="inviteError" style="color:#C8102E;font-size:.78rem;margin:.4rem 0;display:none;"></div>
    <div style="display:flex;gap:.75rem;margin-top:1rem;">
      <button onclick="sendInvite()" id="inviteSendBtn" class="btn btn-primary" style="flex:1;">Send Invitation</button>
      <button onclick="closeInviteModal()" class="btn btn-ghost">Cancel</button>
    </div>
    <div id="inviteSuccess" style="display:none;text-align:center;padding:.75rem;background:#f0fdf4;border-radius:8px;color:#15803d;font-weight:700;margin-top:.75rem;">✓ Invitation sent successfully!</div>
  </div>
</div>




{{-- 7-DAY MODAL --}}
<div class="modal-backdrop" id="modal7day">
  <div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <div class="modal-title">📅 Next 7 Days Preview</div>
      <button onclick="closeModal('modal7day')" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    @if(empty($sevenDay))
    <p style="color:var(--muted);text-align:center;padding:2rem 0;">No nets scheduled in the next 7 days.</p>
    @else
    @foreach($sevenDay as $occ)
    @php $s = $occ['schedule']; @endphp
    <div style="display:flex;gap:1rem;align-items:center;padding:.7rem 0;border-bottom:1px solid var(--border);">
      <div style="min-width:60px;font-weight:900;color:var(--navy);font-size:.85rem;">{{ $occ['label'] }}</div>
      <div style="flex:1;">
        <span style="font-weight:800;font-family:monospace;color:var(--navy);">{{ strtoupper($s->callsign) }}</span>
        <span style="font-size:.8rem;color:var(--muted);margin-left:.5rem;">{{ $s->name }}</span>
      </div>
      <div style="font-family:monospace;font-size:.85rem;color:var(--navy);">{{ $occ['start']->format('H:i') }}–{{ $occ['end']->format('H:i') }}</div>
    </div>
    @endforeach
    @endif
  </div>
</div>

{{-- ADD MODAL --}}
<div class="modal-backdrop" id="modalAdd">
  <div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <div class="modal-title">📅 New Net Schedule</div>
      <button onclick="closeModal('modalAdd')" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.events.net-schedule.store') }}">
      @csrf
      <div class="field"><label class="label">Schedule Name</label><input type="text" name="name" class="input" placeholder="e.g. Weekly Tuesday Net" required></div>
      <div class="grid-2">
        <div class="field"><label class="label">Callsign</label><input type="text" name="callsign" class="input" required></div>
        <div class="field"><label class="label">Frequency</label><input type="text" name="frequency" class="input" placeholder="e.g. 145.500 MHz"></div>
        <div class="field">
          <label class="label">Band</label>
          <select name="band" class="input">
            <option value="">— Select Band —</option>
            @foreach($bands as $key => $b)<option value="{{ $key }}">{{ $b['label'] }}</option>@endforeach
          </select>
        </div>
        <div class="field">
          <label class="label">Priority</label>
          <select name="priority" class="input">
            @foreach($priorities as $key => $p)<option value="{{ $key }}">{{ $p['label'] }}</option>@endforeach
          </select>
        </div>
        <div class="field"><label class="label">Net Controller (default)</label><input type="text" name="controller" class="input" placeholder="e.g. G4BDS"></div>
        <div class="field">
          <label class="label">Repeat</label>
          <select name="repeat_type" class="input">
            <option value="weekly">Weekly</option>
            <option value="fortnightly">Fortnightly</option>
            <option value="monthly">Monthly (same week)</option>
          </select>
        </div>
        <div class="field"><label class="label">Start Time</label><input type="time" name="start_time" class="input" required></div>
        <div class="field"><label class="label">End Time</label><input type="time" name="end_time" class="input" required></div>
      </div>
      <div class="field"><label class="label">Description</label><input type="text" name="description" class="input" placeholder="e.g. Weekly training net — all welcome"></div>
      <div class="field"><label class="label">Pre-Net Announcement</label><input type="text" name="announcement" class="input" placeholder="e.g. Exercise tonight — all members please join"></div>
      <div class="field">
        <label class="label">Days of Week</label>
        <div class="days-grid">
          @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $day)
          <div class="day-btn"><input type="checkbox" name="days_of_week[]" value="{{ $day }}" id="add_{{ $day }}"><label for="add_{{ $day }}">{{ ucfirst($day) }}</label></div>
          @endforeach
        </div>
      </div>
      <div class="section-sep">Controller Time Slots <span style="font-weight:400;font-size:.8rem;">(optional)</span></div>
      <div id="addSlots"></div>
      <button type="button" onclick="addSlot('addSlots')" class="btn btn-ghost btn-sm" style="margin-bottom:1rem;">+ Add Controller Slot</button>
      <div class="toggle-row">
        <div><div class="toggle-label" style="font-size:.88rem;">Auto Activate</div><div class="toggle-sub">Automatically sets the live banner during this window</div></div>
        <label class="toggle-switch"><input type="checkbox" name="auto_activate" value="1"><span class="slider"></span></label>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem;">
        <button type="submit" class="btn btn-primary">Create Schedule</button>
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalAdd')">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal-backdrop" id="modalEdit">
  <div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <div class="modal-title">✏️ Edit Schedule</div>
      <button onclick="closeModal('modalEdit')" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    <form method="POST" id="editForm" action="">
      @csrf @method('PATCH')
      <div class="field"><label class="label">Schedule Name</label><input type="text" name="name" id="editName" class="input" required></div>
      <div class="grid-2">
        <div class="field"><label class="label">Callsign</label><input type="text" name="callsign" id="editCallsign" class="input" required></div>
        <div class="field"><label class="label">Frequency</label><input type="text" name="frequency" id="editFrequency" class="input"></div>
        <div class="field">
          <label class="label">Band</label>
          <select name="band" id="editBand" class="input">
            <option value="">— Select Band —</option>
            @foreach($bands as $key => $b)<option value="{{ $key }}">{{ $b['label'] }}</option>@endforeach
          </select>
        </div>
        <div class="field">
          <label class="label">Priority</label>
          <select name="priority" id="editPriority" class="input">
            @foreach($priorities as $key => $p)<option value="{{ $key }}">{{ $p['label'] }}</option>@endforeach
          </select>
        </div>
        <div class="field"><label class="label">Net Controller (default)</label><input type="text" name="controller" id="editController" class="input"></div>
        <div class="field">
          <label class="label">Repeat</label>
          <select name="repeat_type" id="editRepeatType" class="input">
            <option value="weekly">Weekly</option>
            <option value="fortnightly">Fortnightly</option>
            <option value="monthly">Monthly (same week)</option>
          </select>
        </div>
        <div class="field"><label class="label">Start Time</label><input type="time" name="start_time" id="editStartTime" class="input" required></div>
        <div class="field"><label class="label">End Time</label><input type="time" name="end_time" id="editEndTime" class="input" required></div>
      </div>
      <div class="field"><label class="label">Description</label><input type="text" name="description" id="editDescription" class="input"></div>
      <div class="field"><label class="label">Pre-Net Announcement</label><input type="text" name="announcement" id="editAnnouncement" class="input"></div>
      <div class="field">
        <label class="label">Days of Week</label>
        <div class="days-grid">
          @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $day)
          <div class="day-btn"><input type="checkbox" name="days_of_week[]" value="{{ $day }}" id="edit_{{ $day }}"><label for="edit_{{ $day }}">{{ ucfirst($day) }}</label></div>
          @endforeach
        </div>
      </div>
      <div class="section-sep">Controller Time Slots</div>
      <div id="editSlots"></div>
      <button type="button" onclick="addSlot('editSlots')" class="btn btn-ghost btn-sm" style="margin-bottom:1rem;">+ Add Controller Slot</button>
      <div class="toggle-row">
        <div><div class="toggle-label" style="font-size:.88rem;">Auto Activate</div><div class="toggle-sub">Automatically sets the live banner during this window</div></div>
        <label class="toggle-switch"><input type="checkbox" name="auto_activate" value="1" id="editAutoActivate"><span class="slider"></span></label>
      </div>
      <div class="toggle-row">
        <div><div class="toggle-label" style="font-size:.88rem;">Schedule Active</div><div class="toggle-sub">Disable to pause without deleting</div></div>
        <label class="toggle-switch"><input type="checkbox" name="is_active" value="1" id="editIsActive"><span class="slider"></span></label>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- CLONE MODAL --}}
<div class="modal-backdrop" id="modalClone">
  <div class="modal" style="max-width:400px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <div class="modal-title">📋 Clone Schedule</div>
      <button onclick="closeModal('modalClone')" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    <form method="POST" id="cloneForm" action="">
      @csrf
      <div class="field"><label class="label">New Schedule Name</label><input type="text" name="name" id="cloneName" class="input" required></div>
      <div class="field">
        <label class="label">Days of Week for Clone</label>
        <div class="days-grid">
          @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $day)
          <div class="day-btn"><input type="checkbox" name="days_of_week[]" value="{{ $day }}" id="clone_{{ $day }}"><label for="clone_{{ $day }}">{{ ucfirst($day) }}</label></div>
          @endforeach
        </div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem;">
        <button type="submit" class="btn btn-primary">Clone</button>
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalClone')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
var bandData = @json($bands);

function switchTab(name,el){
  document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nc-tab').forEach(t=>t.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  if (el) el.classList.add('active');
  history.replaceState(null,'','#'+name);
}

// Restore tab from URL hash on load
document.addEventListener('DOMContentLoaded', function(){
  var hash = window.location.hash.replace('#','');
  var validTabs = ['live','schedules','calendar','sessions','checkins','loghistory'];
  if (hash && validTabs.indexOf(hash) !== -1) {
    var tabEl = document.querySelector('.nc-tab[onclick*="\'' + hash + '\'"]');
    switchTab(hash, tabEl);
  }
});
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
document.querySelectorAll('.modal-backdrop').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open');});});

function addSlot(containerId, data) {
  var container = document.getElementById(containerId);
  var idx = container.querySelectorAll('.slot-row').length;
  var row = document.createElement('div');
  row.className = 'slot-row';
  row.style.cssText = 'display:flex;gap:.5rem;align-items:center;margin-bottom:.35rem;';
  row.innerHTML =
    '<input type="text" name="controller_slots['+idx+'][callsign]" class="input" placeholder="Callsign" style="flex:2;" value="'+(data&&data.callsign?data.callsign:'')+'">' +
    '<input type="time" name="controller_slots['+idx+'][from]" class="input" style="flex:1;" value="'+(data&&data.from?data.from:'')+'">' +
    '<span style="color:var(--muted);font-size:.8rem;white-space:nowrap;">to</span>' +
    '<input type="time" name="controller_slots['+idx+'][to]" class="input" style="flex:1;" value="'+(data&&data.to?data.to:'')+'">' +
    '<button type="button" onclick="this.parentNode.remove()" style="background:none;border:none;cursor:pointer;color:#C8102E;font-size:1.1rem;flex-shrink:0;">✕</button>';
  container.appendChild(row);
}

function openEditModal(sched) {
  document.getElementById('editForm').action = '/admin/events/net-schedule/' + sched.id;
  document.getElementById('editName').value         = sched.name || '';
  document.getElementById('editCallsign').value     = sched.callsign || '';
  document.getElementById('editFrequency').value    = sched.frequency || '';
  document.getElementById('editController').value   = sched.controller || '';
  document.getElementById('editDescription').value  = sched.description || '';
  document.getElementById('editAnnouncement').value = sched.announcement || '';
  document.getElementById('editStartTime').value    = (sched.start_time||'').substring(0,5);
  document.getElementById('editEndTime').value      = (sched.end_time||'').substring(0,5);
  document.getElementById('editAutoActivate').checked = !!sched.auto_activate;
  document.getElementById('editIsActive').checked   = !!sched.is_active;
  document.getElementById('editBand').value         = sched.band || '';
  document.getElementById('editPriority').value     = sched.priority || 'routine';
  document.getElementById('editRepeatType').value   = sched.repeat_type || 'weekly';
  var days = Array.isArray(sched.days_of_week) ? sched.days_of_week : (typeof sched.days_of_week==='string' ? JSON.parse(sched.days_of_week) : []);
  ['mon','tue','wed','thu','fri','sat','sun'].forEach(function(d){document.getElementById('edit_'+d).checked=days.includes(d);});
  var slotContainer = document.getElementById('editSlots');
  slotContainer.innerHTML = '';
  var slots = sched.controller_slots || [];
  if (typeof slots === 'string') slots = JSON.parse(slots);
  slots.forEach(function(slot){ addSlot('editSlots', slot); });
  openModal('modalEdit');
}

function cloneSchedule(id) {
  document.getElementById('cloneForm').action = '/admin/events/net-schedule/' + id + '/clone';
  document.getElementById('cloneName').value = '';
  ['mon','tue','wed','thu','fri','sat','sun'].forEach(function(d){document.getElementById('clone_'+d).checked=false;});
  openModal('modalClone');
}

function updatePreview(){
  var callsign = (document.querySelector('[name="net_callsign"]').value||'CALLSIGN').toUpperCase();
  var freq     = document.querySelector('[name="net_frequency"]').value||'000.000 MHz';
  var desc     = document.querySelector('[name="net_description"]').value||'Net description';
  var ctrl     = (document.querySelector('[name="net_controller"]').value||'').toUpperCase();
  var active   = document.getElementById('netActiveToggle').checked;
  var band     = document.getElementById('fBand').value;
  document.getElementById('previewCallsign').textContent = callsign;
  document.getElementById('previewFreq').textContent     = freq;
  document.getElementById('previewDesc').textContent     = desc;
  document.getElementById('previewCtrl').textContent     = ctrl;
  document.getElementById('previewCtrlRow').style.display = ctrl ? '' : 'none';
  var bb = document.getElementById('previewBandBadge');
  if (band && bandData[band]) {
    bb.style.display    = '';
    bb.textContent      = bandData[band].label;
    bb.style.color      = bandData[band].colour;
    bb.style.background = bandData[band].bg;
    bb.style.border     = '1px solid ' + bandData[band].border;
  } else {
    bb.style.display = 'none';
  }
  document.getElementById('previewStatusNote').innerHTML = active
    ? '<span style="color:#059669;">✓ Banner is <strong>live</strong> on the homepage.</span>'
    : '<span style="color:#f59e0b;">⚠ Net is currently <strong>inactive</strong></span>';
}
document.querySelectorAll('[name="net_callsign"],[name="net_frequency"],[name="net_description"],[name="net_controller"],[name="net_active"]').forEach(function(el){
  el.addEventListener('input',updatePreview); el.addEventListener('change',updatePreview);
});
document.getElementById('fBand').addEventListener('change',updatePreview);
</script>

<script>
// ── Live net controller slots ──
function addLiveSlot(data) {
  var container = document.getElementById('liveSlots');
  if (!container) return;
  var row = document.createElement('div');
  row.className = 'slot-row';
  row.style.cssText = 'display:flex;gap:.5rem;align-items:center;margin-bottom:.35rem;';
  row.innerHTML =
    '<input type="text" class="input ls-callsign" placeholder="Callsign" style="flex:2;text-transform:uppercase;" value="'+(data&&data.callsign?data.callsign:'')+'">' +
    '<input type="time" class="input ls-from" style="flex:1;" value="'+(data&&data.from?data.from:'')+'">' +
    '<span style="color:var(--muted);font-size:.8rem;white-space:nowrap;">to</span>' +
    '<input type="time" class="input ls-to" style="flex:1;" value="'+(data&&data.to?data.to:'')+'">' +
    '<button type="button" onclick="this.parentNode.remove();" style="background:none;border:none;cursor:pointer;color:#C8102E;font-size:1.1rem;flex-shrink:0;">✕</button>';
  container.appendChild(row);
}

function reindexLiveSlots() {
  var rows = document.querySelectorAll('#liveSlots .slot-row');
  rows.forEach(function(row, i) {
    row.querySelectorAll('input[name]').forEach(function(inp) {
      inp.name = inp.name.replace(/net_controller_slots\[\d+\]/, 'net_controller_slots['+i+']');
    });
  });
}

// Pre-populate from saved settings
document.addEventListener('DOMContentLoaded', function() {
  // Pre-populate saved slots
  var saved = @json(json_decode($settings['net_controller_slots'] ?? '[]', true) ?? []);
  if (Array.isArray(saved) && saved.length) {
    saved.forEach(function(slot) { addLiveSlot(slot); });
  }

  // Sync hidden JSON field from slot rows
  function syncSlots() {
    var slots = [];
    document.querySelectorAll('#liveSlots .slot-row').forEach(function(row) {
      var cs  = row.querySelector('.ls-callsign');
      var fr  = row.querySelector('.ls-from');
      var to  = row.querySelector('.ls-to');
      var callsign = cs  ? cs.value.trim().toUpperCase() : '';
      var from     = fr  ? fr.value.trim() : '';
      var toVal    = to  ? to.value.trim() : '';
      if (callsign) slots.push({callsign: callsign, from: from, to: toVal});
    });
    var hidden = document.getElementById('liveSlotsJson');
    if (hidden) hidden.value = JSON.stringify(slots);
    return slots;
  }

  // Wire submit on the live net form by ID
  var form = document.getElementById('liveNetForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      syncSlots();
    });
  }

  // Also sync whenever any slot input changes
  document.getElementById('liveSlots').addEventListener('input', syncSlots);
});

function pickCtrl(callsign) {
  var input = document.getElementById('fCtrl');
  if (!input) return;
  input.value = callsign;
  // Update any live preview elements that show the controller
  var previewEls = document.querySelectorAll('[id*="previewCtrl"], [data-preview="controller"]');
  previewEls.forEach(function(el) { el.textContent = callsign; });
  // Visual feedback on the input
  input.style.transition = 'border-color .2s, box-shadow .2s';
  input.style.borderColor = '#22c55e';
  input.style.boxShadow = '0 0 0 3px rgba(34,197,94,.25)';
  setTimeout(function() {
    input.style.borderColor = '';
    input.style.boxShadow = '';
  }, 1200);
  // Highlight active button
  document.querySelectorAll('[data-callsign]').forEach(function(btn) {
    btn.style.background = '';
    btn.style.color = 'var(--navy)';
    btn.style.borderColor = 'var(--border)';
  });
  var active = document.querySelector('[data-callsign="' + callsign + '"]');
  if (active) {
    active.style.background = 'var(--navy)';
    active.style.color = '#fff';
    active.style.borderColor = 'var(--navy)';
  }
}
</script>


<script>
var _ciQrzData = {};
var _ciInviteCallsign = '';

function isOfflineMode() {
    return !navigator.onLine;
}

function logCheckin() {
    var cs    = document.getElementById('ciCallsign').value.trim().toUpperCase();
    var rep   = document.getElementById('ciReport').value.trim();
    var notes = document.getElementById('ciNotes').value.trim();
    var err   = document.getElementById('ciError');
    if (!cs) { err.textContent = 'Callsign is required'; err.style.display=''; return; }
    // Client-side duplicate check against current log
    var logEls = document.querySelectorAll('#ciLog [data-remove]');
    var alreadyLogged = false;
    document.querySelectorAll('#ciLog span[style*="monospace"]').forEach(function(el){
        if (el.textContent.trim() === cs) alreadyLogged = true;
    });
    if (alreadyLogged) {
        err.textContent = cs + ' is already logged on this net';
        err.style.display = '';
        return;
    }
    err.style.display = 'none';

    // Offline mode — skip all online checks, log directly
    if (isOfflineMode()) {
        doLogCheckin(cs, rep, notes, err);
        return;
    }

    fetch('/admin/events/station-log/logging-status', {cache:'no-store',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
    })
    .then(function(r){ return r.json(); })
    .then(function(status){
        if (!status.enabled) {
            err.textContent = 'Station logging is not enabled — enable it in Live Status Control first';
            err.style.display = '';
            return;
        }
        doLogCheckin(cs, rep, notes, err);
    })
    .catch(function(){ doLogCheckin(cs, rep, notes, err); });
}

// ── Offline local store ───────────────────────────────────────────────────
var OFFLINE_STORE_KEY = 'raynet_offline_log';

// Self-heal: clear any corrupted data from old service worker on first load
(function(){
    try {
        var raw = localStorage.getItem(OFFLINE_STORE_KEY);
        if (!raw) return;
        var q = JSON.parse(raw);
        if (!Array.isArray(q)) { localStorage.removeItem(OFFLINE_STORE_KEY); return; }
        // Valid entries must have callsign + logged_at and NOT have id or checked_in_at (server format)
        var clean = q.filter(function(e){
            return e && typeof e.callsign === 'string' && e.logged_at && !e.id && !e.checked_in_at;
        });
        if (clean.length !== q.length) {
            console.warn('ROCK: cleared ' + (q.length - clean.length) + ' corrupted offline queue entries');
            localStorage.setItem(OFFLINE_STORE_KEY, JSON.stringify(clean));
        }
    } catch(e) { localStorage.removeItem(OFFLINE_STORE_KEY); }
})();

function getOfflineQueue() {
    try { return JSON.parse(localStorage.getItem(OFFLINE_STORE_KEY) || '[]'); } catch(e) { return []; }
}

function saveOfflineQueue(q) {
    localStorage.setItem(OFFLINE_STORE_KEY, JSON.stringify(q));
}

function addToOfflineQueue(cs, rep, notes) {
    var q = getOfflineQueue();
    q.push({callsign: cs, signal_report: rep, notes: notes, logged_at: new Date().toISOString()});
    saveOfflineQueue(q);
    renderOfflineLog();
}

function removeFromOfflineQueue(idx) {
    var q = getOfflineQueue();
    q.splice(idx, 1);
    saveOfflineQueue(q);
    renderOfflineLog();
}

function clearOfflineQueue() {
    localStorage.removeItem(OFFLINE_STORE_KEY);
    renderOfflineLog();
}

function renderOfflineLog() {
    var q    = getOfflineQueue();
    var log  = document.getElementById('ciLog');
    var empty = document.getElementById('ciEmpty');
    var cnt  = document.getElementById('ciLiveCount');
    if (!log) return;
    if (cnt) cnt.textContent = q.length + ' station' + (q.length !== 1 ? 's' : '') + ' (offline)';
    if (!q.length) {
        log.innerHTML = '';
        if (empty) empty.style.display = '';
        return;
    }
    if (empty) empty.style.display = 'none';
    log.innerHTML = q.map(function(e, i) {
        var time = new Date(e.logged_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
        return '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 4rem 2.5rem;gap:.5rem;align-items:center;'
            + 'padding:.6rem 1.25rem;background:' + (i%2===0?'#fffbeb':'#fef9c3') + ';border-bottom:1px solid #fde68a;">'
            + '<div style="font-size:.68rem;font-weight:800;color:#92400e;text-align:center;">' + (i+1) + '</div>'
            + '<div style="font-size:.72rem;color:#92400e;font-family:monospace;">' + time + '</div>'
            + '<div style="font-family:monospace;font-weight:900;font-size:.88rem;color:#003366;">' + escHtml(e.callsign) + '</div>'
            + '<div style="font-size:.78rem;color:#64748b;">' + escHtml(e.notes||'') + '</div>'
            + '<div style="font-size:.68rem;font-weight:800;padding:.1rem .4rem;border-radius:999px;background:#fbbf24;color:#1e293b;text-align:center;">⏳ Queued</div>'
            + '<button data-offline-remove="' + i + '" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:.9rem;padding:0;text-align:center;">✕</button>'
            + '</div>';
    }).join('');
}

function doLogCheckin(cs, rep, notes, err) {
    // Offline — store locally, show in list
    if (isOfflineMode()) {
        addToOfflineQueue(cs, rep, notes);
        document.getElementById('ciCallsign').value = '';
        document.getElementById('ciReport').value   = '';
        document.getElementById('ciNotes').value    = '';
        hideQrzCard();
        var nameEl = document.getElementById('ciQrzName');
        if (nameEl) nameEl.textContent = '';
        return;
    }

    fetch('{{ route("admin.events.station-log.store") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({callsign:cs, signal_report:rep, notes:notes})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            document.getElementById('ciCallsign').value = '';
            document.getElementById('ciReport').value   = '';
            document.getElementById('ciNotes').value    = '';
            hideQrzCard();
            var nameEl = document.getElementById('ciQrzName');
            if (nameEl) nameEl.textContent = '';
            loadLog();
        } else if (d.error) {
            err.textContent = d.error;
            err.style.display = '';
        }
    });
}

function qrzLookup(cs) {
    if (!cs || cs.length < 3) { hideQrzCard(); return; }

    // Offline mode — skip QRZ, show manual entry hint
    if (isOfflineMode()) {
        hideQrzCard();
        var nameEl = document.getElementById('ciQrzName');
        if (nameEl) {
            nameEl.textContent = '📴 Offline — name will be looked up on sync';
            nameEl.style.color = '#fbbf24';
        }
        return;
    }

    fetch('/admin/events/station-log/qrz?callsign=' + encodeURIComponent(cs), {
        headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        _ciQrzData[cs] = d;
        if (!d.found) { hideQrzCard(); return; }
        showQrzCard(cs, d);
    })
    .catch(function(){ hideQrzCard(); });
}

function showQrzCard(cs, d) {
    var card = document.getElementById('ciQrzCard');
    if (!card) return;

    document.getElementById('ciQrzCallsign').textContent = cs;
    document.getElementById('ciQrzName').textContent     = d.name || '';

    var photo = document.getElementById('ciQrzPhoto');
    if (d.photo) { photo.src = '/admin/events/station-log/qrz-photo?callsign=' + encodeURIComponent(cs); photo.style.display = ''; }
    else         { photo.style.display = 'none'; }

    var lic = document.getElementById('ciQrzLicence');
    if (d.licence_class) { lic.textContent = d.licence_class; lic.style.display = ''; }
    else                 { lic.style.display = 'none'; }

    var reg = document.getElementById('ciQrzRegistered');
    reg.style.display = d.is_registered ? '' : 'none';

    var link = document.getElementById('ciQrzLink');
    link.href = d.qrz_url || ('https://www.qrz.com/db/' + cs);

    var loc = document.getElementById('ciQrzLocation');
    if (d.location) { loc.querySelector('span').textContent = d.location; loc.style.display = ''; }
    else            { loc.style.display = 'none'; }

    var grid = document.getElementById('ciQrzGrid');
    if (d.grid) { grid.querySelector('span').textContent = d.grid; grid.style.display = ''; }
    else        { grid.style.display = 'none'; }

    var dxcc = document.getElementById('ciQrzDxcc');
    if (d.dxcc) { dxcc.querySelector('span').textContent = d.dxcc; dxcc.style.display = ''; }
    else        { dxcc.style.display = 'none'; }

    var lotw = document.getElementById('ciQrzLotw');
    lotw.style.display = (d.lotw === '1' || d.lotw === 'Y') ? '' : 'none';

    var invBtn = document.getElementById('ciInviteBtn');
    invBtn.style.display = (!d.is_registered) ? '' : 'none';
    _ciInviteCallsign = cs;

    card.style.display = '';
    card.style.animation = 'none'; void card.offsetWidth;
    card.style.animation = 'badgeFadeIn .35s ease forwards';
}

function hideQrzCard() {
    var card = document.getElementById('ciQrzCard');
    if (card) card.style.display = 'none';
}

function openInviteModal() {
    var cs   = _ciInviteCallsign;
    var d    = _ciQrzData[cs] || {};
    var modal = document.getElementById('inviteModal');
    document.getElementById('inviteCallsignDisplay').textContent = cs;
    document.getElementById('inviteEmail').value = '';
    document.getElementById('inviteError').style.display = 'none';
    document.getElementById('inviteSuccess').style.display = 'none';
    document.getElementById('inviteSendBtn').disabled = false;
    var qrzEmailRow = document.getElementById('inviteQrzEmailRow');
    if (d.email) {
        document.getElementById('inviteQrzEmail').textContent = d.email;
        qrzEmailRow.style.display = '';
    } else {
        qrzEmailRow.style.display = 'none';
    }
    modal.style.display = 'flex';
}

function closeInviteModal() {
    document.getElementById('inviteModal').style.display = 'none';
}

function useQrzEmail() {
    var email = document.getElementById('inviteQrzEmail').textContent;
    document.getElementById('inviteEmail').value = email;
}

function sendInvite() {
    var cs    = _ciInviteCallsign;
    var d     = _ciQrzData[cs] || {};
    var email = document.getElementById('inviteEmail').value.trim();
    var err   = document.getElementById('inviteError');
    var btn   = document.getElementById('inviteSendBtn');
    if (!email) { err.textContent = 'Email address is required'; err.style.display = ''; return; }
    err.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Sending...';
    fetch('{{ route("admin.events.station-log.invite") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({callsign:cs, email:email, name: d.name || cs})
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            document.getElementById('inviteSuccess').style.display = '';
            btn.textContent = '✓ Sent';
            setTimeout(closeInviteModal, 2000);
        } else {
            err.textContent = res.error || 'Failed to send';
            err.style.display = '';
            btn.disabled = false;
            btn.textContent = 'Send Invitation';
        }
    });
}

function removeCheckin(id) {
    fetch('{{ url("admin/events/station-log") }}/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
    }).then(function(){ loadLog(); });
}

function clearLog() {
    if (!confirm('Clear all logged stations?')) return;
    fetch('{{ route("admin.events.station-log.clear") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
    }).then(function(){ loadLog(); });
}

function archiveAndClear() {
    if (!confirm('Archive the current station log and clear it? This will save all entries to Net Log History.')) return;
    fetch('{{ route("admin.events.station-log.archive-and-clear") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            loadLog();
            loadHistory();
            var msg = d.archived > 0 ? d.archived + ' station' + (d.archived !== 1 ? 's' : '') + ' archived.' : 'Log was empty.';
            var banner = document.getElementById('ciStatusBanner');
            if (banner) { var orig = banner.innerHTML; banner.innerHTML = '📂 ' + msg; setTimeout(function(){ banner.innerHTML = orig; }, 3000); }
        }
    });
}

function renderStations(stations) {
    if (!stations || !stations.length) return '<div style="padding:1rem;text-align:center;color:var(--muted);font-size:.82rem;">No stations in this log</div>';
    var cols = '2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 5rem';
    var hdr = '<div style="display:grid;grid-template-columns:' + cols + ';gap:.5rem;padding:.4rem .75rem;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">'
        + ['#','Time','Callsign','Name','Licence','Location','Grid','Signal','Member'].map(function(x){ return '<div style="font-size:.62rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">' + x + '</div>'; }).join('')
        + '</div>';
    var rows = stations.map(function(s, i) {
        var qrz = s.qrz_data || {};
        if (typeof qrz === 'string') { try { qrz = JSON.parse(qrz); } catch(e){ qrz={}; } }
        var time = s.checked_in_at ? new Date(s.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}) : '---';
        var bg = (i%2===0) ? '#fff' : '#f9fafb';
        return '<div style="display:grid;grid-template-columns:' + cols + ';gap:.5rem;padding:.5rem .75rem;background:' + bg + ';border-bottom:1px solid #f1f5f9;align-items:center;">'
            + '<div style="font-size:.65rem;color:#cbd5e1;text-align:center;">' + (i+1) + '</div>'
            + '<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">' + time + '</div>'
            + '<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">' + escHtml(s.callsign||'') + '</div>'
            + '<div style="font-weight:600;color:#334155;font-size:.8rem;">' + escHtml(s.name||'---') + '</div>'
            + '<div>' + (qrz.licence_class ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#15803d;">' + escHtml(qrz.licence_class) + '</span>' : '<span style="color:#e2e8f0;">---</span>') + '</div>'
            + '<div style="font-size:.72rem;color:#64748b;">' + escHtml(qrz.location||'---') + '</div>'
            + '<div style="font-size:.72rem;color:#64748b;font-family:monospace;">' + escHtml(qrz.grid||'---') + '</div>'
            + '<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">' + escHtml(s.signal_report||'---') + '</div>'
            + '<div>' + (s.is_registered
                ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fef9c3;color:#a16207;">Member</span>'
                : '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fee2e2;color:#b91c1c;">Not</span>') + '</div>'
            + '</div>';
    }).join('');
    return hdr + rows;
}

function toggleHistEntry(id) {
    var body  = document.getElementById('hist-body-' + id);
    var arrow = document.getElementById('hist-arrow-' + id);
    if (!body) return;
    if (body.style.display === 'none' || !body.style.display) {
        body.style.display = '';
        if (arrow) arrow.textContent = '▲';
        if (!body.dataset.loaded) {
            body.innerHTML = '<div style="padding:1rem;text-align:center;color:var(--muted);">Loading...</div>';
            fetch('/admin/events/net-log-history/' + id)
            .then(function(r){ return r.json(); })
            .then(function(h){
                var stations = Array.isArray(h.stations) ? h.stations : JSON.parse(h.stations||'[]');
                body.innerHTML = renderStations(stations);
                body.dataset.loaded = '1';
            });
        }
    } else {
        body.style.display = 'none';
        if (arrow) arrow.textContent = '▼';
    }
}

function loadHistory() {
    fetch('{{ route("admin.events.net-log-history.index") }}')
    .then(function(r){ return r.json(); })
    .then(function(data){
        var list  = document.getElementById('histList');
        var empty = document.getElementById('histEmpty');
        var cnt   = document.getElementById('histCount');
        if (!list) return;
        if (cnt) cnt.textContent = data.length + ' session' + (data.length !== 1 ? 's' : '');
        if (!data.length) {
            list.innerHTML = '';
            if (empty) empty.style.display = '';
            return;
        }
        if (empty) empty.style.display = 'none';
        list.innerHTML = data.map(function(h) {
            var ended   = new Date(h.ended_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
            var started = h.started_at ? new Date(h.started_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}) : '---';
            return '<div style="border-bottom:1px solid #f1f5f9;">'
                + '<div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1.25rem;cursor:pointer;" data-toggle-hist=' + h.id + '>'
                    + '<div style="width:36px;height:36px;border-radius:8px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">📻</div>'
                    + '<div style="flex:1;min-width:0;">'
                        + '<div style="font-weight:800;color:var(--navy);font-size:.88rem;">' + escHtml(h.net_callsign||'Unknown Net')
                            + (h.frequency ? '<span style="color:var(--muted);font-weight:600;"> · ' + escHtml(h.frequency) + '</span>' : '')
                        + '</div>'
                        + '<div style="font-size:.72rem;color:var(--muted);margin-top:.1rem;">' + ended + ' · Started: ' + started + '</div>'
                    + '</div>'
                    + '<div style="display:flex;align-items:center;gap:.45rem;flex-shrink:0;">'
                        + '<span style="font-size:.82rem;font-weight:900;color:var(--navy);">' + h.station_count + '</span>'
                        + '<span style="font-size:.65rem;color:var(--muted);margin-right:.2rem;">stations</span>'
                        + '<a href="/admin/events/net-log-history/' + h.id + '/pdf" target="_blank" onclick="event.stopPropagation()" style="font-size:.7rem;font-weight:700;color:#C8102E;background:#fff1f2;border:1px solid #fecdd3;border-radius:5px;padding:.18rem .45rem;text-decoration:none;">PDF</a>'
                        + '<a href="/admin/events/net-log-history/' + h.id + '/adif" onclick="event.stopPropagation()" style="font-size:.7rem;font-weight:700;color:#059669;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;padding:.18rem .45rem;text-decoration:none;">ADIF</a>'
                        + '<button onclick="event.stopPropagation();deleteHistory(' + h.id + ')" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:.9rem;padding:0 .15rem;">✕</button>'
                        + '<span id="hist-arrow-' + h.id + '" style="color:var(--muted);font-size:.7rem;margin-left:.2rem;">▼</span>'
                    + '</div>'
                + '</div>'
                + '<div id="hist-body-' + h.id + '" style="display:none;border-top:1px solid #f1f5f9;"></div>'
                + '</div>';
        }).join('');
    });
}

function openHistModal(id) {
    fetch('{{ route("admin.events.net-log-history.index") }}')
    .then(function(r){ return r.json(); })
    .then(function(data){
        var h = data.find(function(x){ return x.id === id; });
        if (!h) return;
        var modal = document.getElementById('histModal');
        var title = document.getElementById('histModalTitle');
        var body  = document.getElementById('histModalBody');
        var ended = new Date(h.ended_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
        title.textContent = (h.net_callsign||'Net') + ' · ' + ended;
        var stations = Array.isArray(h.stations) ? h.stations : [];
        body.innerHTML = '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 5rem;gap:.5rem;padding:.4rem .75rem;background:#f8fafc;border-bottom:1px solid var(--border);margin-bottom:.25rem;">'
            + ['#','Time','Callsign','Name','Licence','Location','Grid','Signal','Member'].map(function(h){ return '<div style="font-size:.62rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">' + h + '</div>'; }).join('')
            + '</div>'
            + stations.map(function(s, i) {
                var qrz = s.qrz_data || {};
                if (typeof qrz === 'string') { try { qrz = JSON.parse(qrz); } catch(e){ qrz={}; } }
                var time = s.checked_in_at ? new Date(s.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}) : '—';
                var even = i % 2 === 0;
                return '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 5rem;gap:.5rem;padding:.5rem .75rem;background:' + (even?'#fff':'#f9fafb') + ';border-bottom:1px solid #f1f5f9;align-items:center;">'
                    + '<div style="font-size:.65rem;color:#cbd5e1;text-align:center;">' + (i+1) + '</div>'
                    + '<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">' + time + '</div>'
                    + '<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">' + escHtml(s.callsign||'') + '</div>'
                    + '<div style="font-weight:600;color:#334155;font-size:.8rem;">' + escHtml(s.name||'—') + '</div>'
                    + '<div>' + (qrz.licence_class ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#15803d;">' + escHtml(qrz.licence_class) + '</span>' : '<span style="color:#e2e8f0;">—</span>') + '</div>'
                    + '<div style="font-size:.72rem;color:#64748b;">' + escHtml(qrz.location||'—') + '</div>'
                    + '<div style="font-size:.72rem;color:#64748b;font-family:monospace;">' + escHtml(qrz.grid||'—') + '</div>'
                    + '<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">' + escHtml(s.signal_report||'—') + '</div>'
                    + '<div>' + (s.is_registered ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fef9c3;color:#a16207;">✓ Member</span>' : '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fee2e2;color:#b91c1c;">✗ Not Member</span>') + '</div>'
                    + '</div>';
            }).join('');
        modal.style.display = 'flex';
    });
}

function closeHistModal() {
    document.getElementById('histModal').style.display = 'none';
}

function deleteHistory(id) {
    if (!confirm('Delete this net log entry?')) return;
    fetch('{{ url("admin/events/net-log-history") }}/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    }).then(function(){ loadHistory(); });
}

function archiveAndClear() {
    if (!confirm('Archive the current station log and clear it? This will save all entries to Net Log History.')) return;
    fetch('{{ route("admin.events.station-log.archive-and-clear") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            loadLog();
            loadHistory();
            var msg = d.archived > 0 ? d.archived + ' station' + (d.archived !== 1 ? 's' : '') + ' archived.' : 'Log was empty.';
            var banner = document.getElementById('ciStatusBanner');
            if (banner) { var orig = banner.innerHTML; banner.innerHTML = '📂 ' + msg; setTimeout(function(){ banner.innerHTML = orig; }, 3000); }
        }
    });
}

function loadHistory() {
    fetch('{{ route("admin.events.net-log-history.index") }}')
    .then(function(r){ return r.json(); })
    .then(function(data){
        var list  = document.getElementById('histList');
        var empty = document.getElementById('histEmpty');
        var cnt   = document.getElementById('histCount');
        if (!list) return;
        if (cnt) cnt.textContent = data.length + ' session' + (data.length !== 1 ? 's' : '');
        if (!data.length) {
            list.innerHTML = '';
            if (empty) empty.style.display = '';
            return;
        }
        if (empty) empty.style.display = 'none';
        list.innerHTML = data.map(function(h) {
            var ended = new Date(h.ended_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
            var started = h.started_at ? new Date(h.started_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}) : '—';
            return '<div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .15s;" data-hist-id="' + h.id + '">'
                + '<div style="width:40px;height:40px;border-radius:10px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📻</div>'
                + '<div style="flex:1;min-width:0;">'
                    + '<div style="font-weight:800;color:var(--navy);font-size:.88rem;">' + escHtml(h.net_callsign||'Unknown Net') + (h.frequency ? ' · ' + escHtml(h.frequency) : '') + '</div>'
                    + '<div style="font-size:.75rem;color:var(--muted);margin-top:.1rem;">' + ended + ' · Started: ' + started + '</div>'
                + '</div>'
                + '<div style="text-align:right;flex-shrink:0;">'
                    + '<div style="font-size:.85rem;font-weight:900;color:var(--navy);">' + h.station_count + '</div>'
                    + '<div style="font-size:.65rem;color:var(--muted);">stations</div>'
                + '</div>'
                + '<button data-hist-delete="' + h.id + '" onclick="event.stopPropagation();deleteHistory(' + h.id + ')" title="Delete" '
                    + 'style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:.9rem;padding:0 .2rem;flex-shrink:0;">✕</button>'
                + '</div>';
        }).join('');
    });
}

function openHistModal(id) {
    fetch('{{ route("admin.events.net-log-history.index") }}')
    .then(function(r){ return r.json(); })
    .then(function(data){
        var h = data.find(function(x){ return x.id === id; });
        if (!h) return;
        var modal = document.getElementById('histModal');
        var title = document.getElementById('histModalTitle');
        var body  = document.getElementById('histModalBody');
        var ended = new Date(h.ended_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
        title.textContent = (h.net_callsign||'Net') + ' · ' + ended;
        var stations = Array.isArray(h.stations) ? h.stations : [];
        body.innerHTML = '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 5rem;gap:.5rem;padding:.4rem .75rem;background:#f8fafc;border-bottom:1px solid var(--border);margin-bottom:.25rem;">'
            + ['#','Time','Callsign','Name','Licence','Location','Grid','Signal','Member'].map(function(h){ return '<div style="font-size:.62rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">' + h + '</div>'; }).join('')
            + '</div>'
            + stations.map(function(s, i) {
                var qrz = s.qrz_data || {};
                if (typeof qrz === 'string') { try { qrz = JSON.parse(qrz); } catch(e){ qrz={}; } }
                var time = s.checked_in_at ? new Date(s.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}) : '—';
                var even = i % 2 === 0;
                return '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 5rem;gap:.5rem;padding:.5rem .75rem;background:' + (even?'#fff':'#f9fafb') + ';border-bottom:1px solid #f1f5f9;align-items:center;">'
                    + '<div style="font-size:.65rem;color:#cbd5e1;text-align:center;">' + (i+1) + '</div>'
                    + '<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">' + time + '</div>'
                    + '<div style="font-family:monospace;font-weight:900;font-size:.85rem;color:#003366;">' + escHtml(s.callsign||'') + '</div>'
                    + '<div style="font-weight:600;color:#334155;font-size:.8rem;">' + escHtml(s.name||'—') + '</div>'
                    + '<div>' + (qrz.licence_class ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#15803d;">' + escHtml(qrz.licence_class) + '</span>' : '<span style="color:#e2e8f0;">—</span>') + '</div>'
                    + '<div style="font-size:.72rem;color:#64748b;">' + escHtml(qrz.location||'—') + '</div>'
                    + '<div style="font-size:.72rem;color:#64748b;font-family:monospace;">' + escHtml(qrz.grid||'—') + '</div>'
                    + '<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.8rem;">' + escHtml(s.signal_report||'—') + '</div>'
                    + '<div>' + (s.is_registered ? '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fef9c3;color:#a16207;">✓ Member</span>' : '<span style="font-size:.65rem;font-weight:800;padding:.1rem .35rem;border-radius:999px;background:#fee2e2;color:#b91c1c;">✗ Not Member</span>') + '</div>'
                    + '</div>';
            }).join('');
        modal.style.display = 'flex';
    });
}

function closeHistModal() {
    document.getElementById('histModal').style.display = 'none';
}

function deleteHistory(id) {
    if (!confirm('Delete this net log entry?')) return;
    fetch('{{ url("admin/events/net-log-history") }}/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    }).then(function(){ loadHistory(); });
}

function showOfflineSyncModal(q) {
    var list = document.getElementById('offlineSyncList');
    var btn  = document.getElementById('offlineSyncImportBtn');
    var err  = document.getElementById('offlineSyncError');
    if (!list) return;
    // Reset state
    if (btn) { btn.disabled = false; btn.textContent = '📥 Import ' + q.length + ' station' + (q.length!==1?'s':'') + ' to Log'; btn.style.background = ''; }
    if (err) err.style.display = 'none';
    list.innerHTML = q.map(function(e, i) {
        var time = new Date(e.logged_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
        return '<div style="display:flex;align-items:center;gap:.75rem;padding:.55rem .85rem;'
            + (i%2===0?'background:#fff':'background:#f9fafb') + ';border-bottom:1px solid #f1f5f9;">'
            + '<span style="font-family:monospace;font-weight:900;color:#003366;font-size:.9rem;min-width:80px;">' + escHtml(e.callsign) + '</span>'
            + '<span style="font-size:.75rem;color:#94a3b8;font-family:monospace;min-width:40px;">' + time + '</span>'
            + (e.signal_report ? '<span style="font-size:.75rem;font-weight:700;color:#059669;font-family:monospace;">' + escHtml(e.signal_report) + '</span>' : '')
            + (e.notes ? '<span style="font-size:.75rem;color:#64748b;">' + escHtml(e.notes) + '</span>' : '')
            + '</div>';
    }).join('');
    var modal = document.getElementById('offlineSyncModal');
    modal.style.display = 'flex';
}

function closeOfflineSyncModal() {
    document.getElementById('offlineSyncModal').style.display = 'none';
}

function discardOfflineQueue() {
    if (!confirm('Discard all ' + getOfflineQueue().length + ' offline entries? This cannot be undone.')) return;
    clearOfflineQueue();
    closeOfflineSyncModal();
    loadLog();
}

function importOfflineQueue() {
    var q   = getOfflineQueue();
    if (!q.length) { closeOfflineSyncModal(); return; }
    var btn = document.getElementById('offlineSyncImportBtn');
    var err = document.getElementById('offlineSyncError');
    btn.disabled = true;
    btn.textContent = 'Importing...';
    err.style.display = 'none';

    var done = 0, failed = 0;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    function importNext(i) {
        if (i >= q.length) {
            // All done
            loadLog();
            if (failed === 0) {
                btn.textContent = '✓ All imported!';
                btn.style.background = '#15803d';
                clearOfflineQueue();
                setTimeout(closeOfflineSyncModal, 1800);
            } else {
                // Keep only failed entries
                var remaining = q.filter(function(_, idx){ return idx >= done; });
                saveOfflineQueue(remaining);
                err.textContent = failed + ' entr' + (failed===1?'y':'ies') + ' failed — tap Retry to try again.';
                err.style.display = '';
                btn.disabled = false;
                btn.textContent = 'Retry Failed';
                btn.style.background = '';
            }
            return;
        }
        var entry = q[i];
        btn.textContent = 'Importing ' + (i+1) + ' of ' + q.length + '...';
        fetch('{{ route("admin.events.station-log.store") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf, 'X-Offline-Replay': '1'},
            body: JSON.stringify({callsign: entry.callsign, signal_report: entry.signal_report, notes: entry.notes})
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success) { done++; } else { failed++; }
            importNext(i + 1);
        })
        .catch(function(){
            failed++;
            importNext(i + 1);
        });
    }
    importNext(0);
}

var _lastLogHash = '';

function pollTyping() {
    fetch('{{ route("admin.events.station-log.typing.get") }}', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(data){
        var bar = document.getElementById('ciTypingBar');
        if (!bar) return;
        // Filter stale (>6s old)
        var now = Date.now();
        var active = data.filter(function(t){
            return (now - new Date(t.at).getTime()) < 6000;
        });
        if (!active.length) {
            bar.style.display = 'none';
            return;
        }
        var msg = active.map(function(t){
            return '<strong>' + escHtml(t.name) + '</strong> is logging <span style="font-family:monospace;font-weight:900;color:#003366;">' + escHtml(t.callsign) + '</span>';
        }).join(' &nbsp;·&nbsp; ');
        bar.innerHTML = '✏️ ' + msg;
        bar.style.display = '';
    }).catch(function(){});
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadLog() {
    // Offline — show local queue only
    if (isOfflineMode()) {
        renderOfflineLog();
        return;
    }
    fetch('{{ route("admin.events.station-log.index") }}')
    .then(function(r){ return r.json(); })
    .then(function(data){
        var log   = document.getElementById('ciLog');
        var empty = document.getElementById('ciEmpty');
        var cnt   = document.getElementById('ciLiveCount');
        if (!log) return;
        // Smart diff — only re-render if data changed
        var hash = data.map(function(e){ return e.id + ':' + e.callsign; }).join(',');
        if (hash === _lastLogHash) return;
        _lastLogHash = hash;
        if (cnt) cnt.textContent = data.length + ' station' + (data.length !== 1 ? 's' : '');
        if (!data.length) {
            log.innerHTML = '';
            if (empty) empty.style.display = '';
            return;
        }
        if (empty) empty.style.display = 'none';
        log.innerHTML = data.map(function(e, i) {
            var qrz  = e.qrz_data || {};
            var even = i % 2 === 0;
            var time = new Date(e.checked_in_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
            var licBadge = qrz.licence_class
                ? '<span style="font-size:.68rem;font-weight:800;padding:.1rem .4rem;border-radius:999px;background:#dcfce7;color:#15803d;">' + escHtml(qrz.licence_class) + '</span>'
                : '';
            var memberBadge = e.is_registered
                ? '<span style="font-size:.68rem;font-weight:800;padding:.1rem .4rem;border-radius:999px;background:#fef9c3;color:#a16207;">✓ Member</span>'
                : '<span style="font-size:.68rem;font-weight:800;padding:.1rem .4rem;border-radius:999px;background:#fee2e2;color:#b91c1c;">✗ Not Member</span>';
            var photo = e.photo_url
                ? '<img src="/admin/events/station-log/qrz-photo?callsign=' + encodeURIComponent(e.callsign) + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid #e2e8f0;margin-right:.4rem;vertical-align:middle;">'
                : '<div style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;color:#94a3b8;margin-right:.4rem;vertical-align:middle;flex-shrink:0;">📡</div>';
            var qrzLink = qrz.qrz_url
                ? '<a href="' + escHtml(qrz.qrz_url) + '" target="_blank" style="font-size:.65rem;color:#6366f1;font-weight:700;text-decoration:none;margin-left:.3rem;">QRZ↗</a>'
                : '';
            return '<div style="display:grid;grid-template-columns:2rem 3.5rem 6rem 1fr 5rem 7rem 4rem 4rem 6rem 2.5rem;gap:.5rem;align-items:center;padding:.6rem 1.25rem;background:' + (even?'#fff':'#f9fafb') + ';border-bottom:1px solid #f1f5f9;" data-even="' + (even?'1':'0') + '">'
                + '<div style="font-size:.68rem;font-weight:800;color:#cbd5e1;text-align:center;">' + (i+1) + '</div>'
                + '<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;white-space:nowrap;">' + time + '</div>'
                + '<div style="display:flex;align-items:center;">' + photo + '<span style="font-family:monospace;font-weight:900;font-size:.88rem;color:#003366;">' + escHtml(e.callsign) + '</span>' + qrzLink + '</div>'
                + '<div><span style="font-weight:700;color:#334155;font-size:.82rem;">' + escHtml(e.name||'---') + '</span>' + (e.notes ? '<span style="color:#94a3b8;font-size:.75rem;"> · ' + escHtml(e.notes) + '</span>' : '') + '</div>'
                + '<div>' + licBadge + '</div>'
                + '<div style="font-size:.75rem;color:#64748b;">' + escHtml(qrz.location||'---') + '</div>'
                + '<div style="font-size:.75rem;color:#64748b;font-family:monospace;">' + escHtml(qrz.grid||'---') + '</div>'
                + '<div style="font-family:monospace;font-weight:800;color:#059669;font-size:.82rem;">' + escHtml(e.signal_report||'---') + '</div>'
                + '<div style="display:flex;align-items:center;gap:.35rem;">' + memberBadge
                    + (!e.is_registered ? '<button data-invite="' + e.id + '" data-callsign="' + escHtml(e.callsign) + '" data-name="' + escHtml(e.name||e.callsign) + '" title="Invite to RAYNET" style="font-size:.65rem;font-weight:700;background:#003366;color:#fff;border:none;border-radius:4px;padding:.15rem .4rem;cursor:pointer;white-space:nowrap;">✉</button>' : '')
                + '</div>'
                + '<button data-remove="' + e.id + '" title="Remove" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:.9rem;padding:0;text-align:center;line-height:1;">✕</button>'
                + '</div>';
        }).join('');
    });
}


function updateStatusBanner() {
    // Offline mode — show offline banner and keep form fully enabled
    if (isOfflineMode()) {
        var banner    = document.getElementById('ciStatusBanner');
        var formCard  = document.getElementById('ciFormCard');
        var submitBtn = document.getElementById('ciSubmitBtn');
        if (banner) {
            banner.style.cssText = 'border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.82rem;font-weight:700;background:#1e293b;color:#fbbf24;';
            banner.innerHTML = '<span style="font-size:1rem;">📴</span> <strong style="margin:0 .2rem;">Offline mode</strong> — entries will sync when connection is restored';
        }
        if (formCard) formCard.style.opacity = '1';
        if (submitBtn) submitBtn.disabled = false;
        return;
    }

    fetch('/net-status-json', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(d){
        var banner   = document.getElementById('ciStatusBanner');
        var formCard = document.getElementById('ciFormCard');
        var submitBtn = document.getElementById('ciSubmitBtn');
        if (!banner) return;
        if (!d.active) {
            banner.style.cssText = 'border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.82rem;font-weight:700;background:#f1f5f9;color:#64748b;';
            banner.innerHTML = '<span style="font-size:1rem;">⚫</span> Net is not currently active';
            if (formCard) formCard.style.opacity = '.55';
            if (submitBtn) submitBtn.disabled = true;
        } else if (!d.station_logging) {
            banner.style.cssText = 'border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.82rem;font-weight:700;background:#fff7ed;color:#c2410c;';
            banner.innerHTML = '<span style="font-size:1rem;">⚠️</span> Station logging is <strong style="margin:0 .2rem;">disabled</strong> — enable it in the Live Status Control tab';
            if (formCard) formCard.style.opacity = '.55';
            if (submitBtn) submitBtn.disabled = true;
        } else {
            banner.style.cssText = 'border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.82rem;font-weight:700;background:#f0fdf4;color:#15803d;';
            banner.innerHTML = '<span style="font-size:1rem;">🟢</span> Station logging is <strong style="margin:0 .2rem;">active</strong> — type a callsign and press Enter to log';
            if (formCard) formCard.style.opacity = '1';
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    updateStatusBanner();
    loadLog();
    // Fast log poll — every 3s, smart diff
    setInterval(loadLog, 3000);
    // Slow banner poll — every 10s
    setInterval(updateStatusBanner, 10000);
    // Typing indicator poll — every 2s
    setInterval(pollTyping, 2000);

    // On page load, if there's a pending offline queue and we're online, prompt sync
    (function(){
        var q = getOfflineQueue();
        if (q.length > 0 && navigator.onLine) {
            setTimeout(function(){ showOfflineSyncModal(q); }, 1500);
        }
    })();

    // Re-run banner check when connectivity changes
    window.addEventListener('offline', function(){ updateStatusBanner(); loadLog(); });
    window.addEventListener('online',  function(){
        updateStatusBanner();
        // Small delay to let connection stabilise before showing popup or loading
        setTimeout(function(){
            var q = getOfflineQueue();
            if (q.length > 0) {
                showOfflineSyncModal(q);
            } else {
                loadLog();
            }
        }, 800);
    });

    // Delegated handlers for rows and remove buttons
    var ciLog = document.getElementById('ciLog');
    if (ciLog) {
        ciLog.addEventListener('click', function(e) {
            var offRemove = e.target.closest('[data-offline-remove]');
            if (offRemove) { removeFromOfflineQueue(parseInt(offRemove.dataset.offlineRemove)); return; }
            var removeBtn = e.target.closest('[data-remove]');
            if (removeBtn) { removeCheckin(parseInt(removeBtn.dataset.remove)); return; }
            var invBtn = e.target.closest('[data-invite]');
            if (invBtn) {
                _ciInviteCallsign = invBtn.dataset.callsign;
                _ciQrzData[_ciInviteCallsign] = _ciQrzData[_ciInviteCallsign] || {name: invBtn.dataset.name};
                openInviteModal();
            }
        });
        ciLog.addEventListener('mouseover', function(e) {
            var btn = e.target.closest('[data-remove]');
            if (btn) { btn.style.color = '#C8102E'; return; }
            var row = e.target.closest('[data-even]');
            if (row) row.style.background = '#f0f4ff';
        });
        ciLog.addEventListener('mouseout', function(e) {
            var btn = e.target.closest('[data-remove]');
            if (btn) { btn.style.color = '#fca5a5'; return; }
            var row = e.target.closest('[data-even]');
            if (row) row.style.background = row.dataset.even === '1' ? '#fff' : '#f9fafb';
        });
    }

    var ci = document.getElementById('ciCallsign');
    if (ci) {
        ci.addEventListener('keydown', function(e){
            if (e.key === 'Enter') logCheckin();
        });
        ci.addEventListener('input', function(){
            hideQrzCard();
        });
        var qrzTimer;
        ci.addEventListener('input', function(){
            clearTimeout(qrzTimer);
            var val = ci.value.trim().toUpperCase();
            if (val.length >= 3) {
                qrzTimer = setTimeout(function(){ qrzLookup(val); }, 600);
            }
        });
        ci.addEventListener('blur', function(){
            var val = ci.value.trim().toUpperCase();
            if (val.length >= 3) qrzLookup(val);
        });
    }

    // Close invite modal on backdrop click
    document.getElementById('inviteModal').addEventListener('click', function(e){
        if (e.target === this) closeInviteModal();
    });
    // Load and auto-refresh history
    loadHistory();
    setInterval(loadHistory, 30000);

    // History row delegated click
    var _histList = document.getElementById('histList');
    if (_histList) {
        _histList.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.tagName === 'BUTTON') return;
            var row = e.target.closest('[data-toggle-hist]');
            if (row) toggleHistEntry(parseInt(row.getAttribute('data-toggle-hist')));
        });
    }
});
</script>

@endsection
