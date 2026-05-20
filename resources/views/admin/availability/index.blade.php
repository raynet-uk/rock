@extends('layouts.admin')
@section('title', 'Member Availability')
@section('content')

<style>
:root {
    --navy:#003366; --navy-faint:#e8eef5; --navy-mid:#004080;
    --red:#C8102E; --red-faint:#fdf0f2;
    --green:#1a6b3c; --green-bg:#eef7f2;
    --amber:#8a5500; --amber-bg:#fffbeb; --amber-bdr:#e8c96a;
    --grey:#F2F2F2; --grey-mid:#dde2e8; --grey-dark:#9aa3ae;
    --text:#001f40; --text-mid:#2d4a6b; --text-muted:#6b7f96;
    --white:#fff;
    --font:Arial,'Helvetica Neue',Helvetica,sans-serif;
    --shadow-sm:0 1px 3px rgba(0,51,102,.09);
    --shadow-md:0 4px 14px rgba(0,51,102,.11);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

.rn-header{background:var(--navy);border-bottom:4px solid var(--red);position:sticky;top:0;z-index:100;box-shadow:0 2px 10px rgba(0,0,0,.3);}
.rn-header-inner{max-width:1340px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.rn-brand{display:flex;align-items:center;gap:.85rem;padding:.75rem 0;}
.rn-logo{background:var(--red);width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rn-logo span{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.15;text-transform:uppercase;}
.rn-org{font-size:15px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.rn-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:2px;letter-spacing:.05em;text-transform:uppercase;}
.rn-back{font-size:12px;font-weight:bold;color:rgba(255,255,255,.8);text-decoration:none;border:1px solid rgba(255,255,255,.25);padding:.35rem .9rem;transition:all .15s;}
.rn-back:hover{background:rgba(255,255,255,.1);color:#fff;}

.page-band{background:var(--white);border-bottom:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);margin-bottom:1.75rem;}
.page-band-inner{max-width:1340px;margin:0 auto;padding:1.25rem 1.5rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.page-eyebrow{font-size:11px;font-weight:bold;color:var(--red);text-transform:uppercase;letter-spacing:.18em;margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;}
.page-eyebrow::before{content:'';width:16px;height:2px;background:var(--red);display:inline-block;}
.page-title{font-size:26px;font-weight:bold;color:var(--navy);line-height:1;}
.page-desc{font-size:13px;color:var(--text-muted);margin-top:.35rem;}

.wrap{max-width:1340px;margin:0 auto;padding:0 1.5rem 4rem;}

/* ── STAT STRIP ── */
.stat-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem;}
@media(max-width:800px){.stat-strip{grid-template-columns:1fr 1fr;}}
.stat-tile{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1rem 1.1rem;box-shadow:var(--shadow-sm);}
.stat-tile.red{border-top-color:var(--red);}
.stat-tile.amber{border-top-color:#c49a00;}
.stat-tile.green{border-top-color:var(--green);}
.stat-lbl{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--text-muted);margin-bottom:.4rem;}
.stat-val{font-size:32px;font-weight:bold;line-height:1;color:var(--navy);}
.stat-tile.red .stat-val{color:var(--red);}
.stat-tile.amber .stat-val{color:#c49a00;}
.stat-tile.green .stat-val{color:var(--green);}
.stat-sub{font-size:12px;color:var(--text-muted);margin-top:.3rem;}

/* ── FILTER BAR ── */
.filter-bar{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);padding:.85rem 1.2rem;margin-bottom:1.25rem;display:flex;align-items:flex-end;gap:.65rem;flex-wrap:wrap;}
.ff{display:flex;flex-direction:column;gap:.25rem;min-width:120px;}
.ff label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);}
.ff input,.ff select{padding:.4rem .65rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--text);font-family:var(--font);font-size:12px;outline:none;transition:border-color .15s;}
.ff input:focus,.ff select:focus{border-color:var(--navy);}
.btn{display:inline-flex;align-items:center;gap:.35rem;padding:.42rem .9rem;border:1px solid;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;transition:all .12s;white-space:nowrap;text-transform:uppercase;letter-spacing:.05em;text-decoration:none;}
.btn-navy{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-navy:hover{background:var(--navy-mid);}
.btn-ghost{background:transparent;border-color:var(--grey-mid);color:var(--text-muted);}
.btn-ghost:hover{border-color:var(--navy);color:var(--navy);}

/* ── TIMELINE VIEW ── */
.timeline-card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);margin-bottom:1.25rem;overflow:hidden;}
.timeline-head{background:var(--navy);padding:.75rem 1.1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;}
.timeline-title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.9);}
.timeline-nav{display:flex;align-items:center;gap:.5rem;}
.timeline-nav a{color:rgba(255,255,255,.7);text-decoration:none;padding:.25rem .6rem;border:1px solid rgba(255,255,255,.2);font-size:12px;font-weight:bold;transition:all .12s;}
.timeline-nav a:hover{background:rgba(255,255,255,.1);color:#fff;}
.timeline-month-label{font-size:13px;font-weight:bold;color:#fff;}

.timeline-grid{overflow-x:auto;}
.timeline-table{border-collapse:collapse;min-width:100%;}
.timeline-table th{padding:.4rem .5rem;font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);text-align:center;border-right:1px solid var(--grey-mid);background:var(--grey);white-space:nowrap;min-width:30px;}
.timeline-table th.weekend{background:#f0f0f0;color:var(--grey-dark);}
.timeline-table th.today-col{background:rgba(200,16,46,.08);color:var(--red);}
.timeline-table th.member-col{text-align:left;min-width:160px;padding:.4rem .85rem;position:sticky;left:0;z-index:2;background:var(--grey);}
.timeline-table td{border-right:1px solid var(--grey-mid);border-top:1px solid var(--grey-mid);padding:0;height:36px;vertical-align:middle;}
.timeline-table td.member-name-cell{position:sticky;left:0;z-index:1;background:var(--white);padding:.4rem .85rem;border-right:2px solid var(--grey-mid);}
.timeline-table tr:hover td.member-name-cell{background:var(--navy-faint);}
.timeline-table tr:hover td{background:rgba(232,238,245,.4);}
.timeline-table td.weekend-cell{background:#fafafa;}
.timeline-table td.today-cell{background:rgba(200,16,46,.04);}
.timeline-table td.unavail-cell{background:#C8102E;position:relative;}
.timeline-table td.unavail-cell.unavail-start{border-radius:3px 0 0 3px;}
.timeline-table td.unavail-cell.unavail-end{border-radius:0 3px 3px 0;}
.timeline-table td.unavail-cell:hover{background:#a50e26;cursor:pointer;}
.member-name-inner{display:flex;align-items:center;gap:.55rem;}
.member-av{width:26px;height:26px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;color:#fff;flex-shrink:0;}
.member-name-text{font-size:12px;font-weight:bold;color:var(--text);white-space:nowrap;}
.member-callsign{font-size:10px;color:var(--text-muted);letter-spacing:.04em;}
.unavail-tip{position:absolute;bottom:calc(100%+4px);left:50%;transform:translateX(-50%);background:var(--navy);color:#fff;font-size:10px;padding:3px 8px;white-space:nowrap;pointer-events:none;z-index:10;display:none;box-shadow:var(--shadow-md);}
.unavail-cell:hover .unavail-tip{display:block;}

/* ── LIST VIEW ── */
.list-card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);overflow:hidden;}
.list-head{background:#002244;border-bottom:2px solid var(--red);display:grid;grid-template-columns:1fr 160px 160px 1fr 90px;}
.list-head div{padding:.5rem .85rem;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.5);}
.avail-row{display:grid;grid-template-columns:1fr 160px 160px 1fr 90px;align-items:center;border-bottom:1px solid var(--grey-mid);transition:background .1s;}
.avail-row:last-child{border-bottom:none;}
.avail-row:hover{background:var(--navy-faint);}
.avail-row.is-active{background:#fff8f0;border-left:3px solid #c49a00;}
.avail-row.is-active:hover{background:#fff3e6;}
.avail-cell{padding:.65rem .85rem;font-size:13px;}
.period-badge{display:inline-flex;align-items:center;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;padding:2px 8px;border:1px solid;}
.badge-now{background:#fff8e6;border-color:var(--amber-bdr);color:var(--amber);}
.badge-soon{background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);}
.badge-past{background:var(--grey);border-color:var(--grey-mid);color:var(--grey-dark);}
.empty-state{padding:3rem;text-align:center;color:var(--text-muted);font-size:13px;}

/* ── VIEW TOGGLE ── */
.view-toggle{display:flex;gap:2px;background:rgba(0,0,0,.08);padding:2px;}
.vt-btn{padding:.3rem .75rem;font-size:11px;font-weight:bold;font-family:var(--font);border:none;cursor:pointer;color:rgba(0,51,102,.5);background:transparent;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
.vt-btn.active{background:var(--navy);color:#fff;}

@keyframes fadeUp{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:none;}}
.fade-in{animation:fadeUp .3s ease both;}

@media(max-width:900px){
    .list-head,.avail-row{grid-template-columns:1fr 130px 100px 90px;}
    .list-head div:nth-child(4),.avail-cell:nth-child(4){display:none;}
}
@media(max-width:600px){
    .list-head,.avail-row{grid-template-columns:1fr 100px 90px;}
    .list-head div:nth-child(3),.avail-cell:nth-child(3),
    .list-head div:nth-child(4),.avail-cell:nth-child(4){display:none;}
}
</style>

@php
    use Carbon\Carbon;
    $now = Carbon::now();

    // Month navigation
    $monthParam = request('month', $now->format('Y-m'));
    try {
        $viewMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
    } catch(\Throwable $e) {
        $viewMonth = $now->copy()->startOfMonth();
    }
    $prevMonth = $viewMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $viewMonth->copy()->addMonth()->format('Y-m');
    $daysInMonth = $viewMonth->daysInMonth;

    // Filters
    $filterStatus = request('status', 'upcoming'); // upcoming | active | past | all
    $filterSearch = request('search', '');
    $filterFrom   = request('from', '');
    $filterTo     = request('to', '');
    $viewMode     = request('view', 'list'); // list | timeline

    // Query
    $query = \App\Models\MemberAvailability::with('user')
        ->when($filterSearch, fn($q) => $q->whereHas('user', fn($u) =>
            $u->where('name', 'like', "%{$filterSearch}%")
              ->orWhere('callsign', 'like', "%{$filterSearch}%")
        ))
        ->when($filterFrom, fn($q) => $q->where('to_date', '>=', $filterFrom))
        ->when($filterTo,   fn($q) => $q->where('from_date', '<=', $filterTo));

    if ($filterStatus === 'active') {
        $query->where('from_date', '<=', $now)->where('to_date', '>=', $now);
    } elseif ($filterStatus === 'upcoming') {
        $query->where('from_date', '>', $now);
    } elseif ($filterStatus === 'past') {
        $query->where('to_date', '<', $now);
    }

    $periods = $query->orderBy('from_date')->get();

    // Stats
    $totalActive   = \App\Models\MemberAvailability::where('from_date','<=',$now)->where('to_date','>=',$now)->count();
    $totalUpcoming = \App\Models\MemberAvailability::where('from_date','>',$now)->where('from_date','<=',$now->copy()->addDays(30))->count();
    $totalThisMonth= \App\Models\MemberAvailability::where(fn($q) =>
        $q->whereBetween('from_date',[$now->copy()->startOfMonth(),$now->copy()->endOfMonth()])
          ->orWhereBetween('to_date',[$now->copy()->startOfMonth(),$now->copy()->endOfMonth()])
    )->count();
    $membersAffected = \App\Models\MemberAvailability::where('from_date','<=',$now->copy()->addDays(30))
        ->where('to_date','>=',$now)
        ->distinct('user_id')->count('user_id');

    // Timeline data — all periods overlapping the viewed month
    $timelineStart = $viewMonth->copy()->startOfMonth();
    $timelineEnd   = $viewMonth->copy()->endOfMonth();
    $timelinePeriods = \App\Models\MemberAvailability::with('user')
        ->where('from_date', '<=', $timelineEnd)
        ->where('to_date', '>=', $timelineStart)
        ->orderBy('from_date')
        ->get();

    // Get unique users with periods in that month, ordered by name
    $timelineUsers = $timelinePeriods->pluck('user')->filter()->unique('id')->sortBy('name');
@endphp

{{-- ── HEADER ── --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Member Availability</div>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Back to admin</a>
    </div>
</header>

{{-- ── PAGE BAND ── --}}
<div class="page-band fade-in">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Admin Panel</div>
            <h1 class="page-title">Member Availability</h1>
            <p class="page-desc">Holidays, leave and unavailability periods submitted by members.</p>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;">
            <div class="view-toggle">
                <button class="vt-btn {{ $viewMode === 'list' ? 'active' : '' }}"
                        onclick="switchView('list')">≡ List</button>
                <button class="vt-btn {{ $viewMode === 'timeline' ? 'active' : '' }}"
                        onclick="switchView('timeline')">▦ Timeline</button>
            </div>
            <span style="font-size:11px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:.28rem .7rem;">
                {{ $now->format('D d M Y · H:i') }}
            </span>
        </div>
    </div>
</div>

<div class="wrap">

    {{-- ── STATS ── --}}
    <div class="stat-strip fade-in">
        <div class="stat-tile red">
            <div class="stat-lbl">Unavailable Now</div>
            <div class="stat-val">{{ $totalActive }}</div>
            <div class="stat-sub">Active periods today</div>
        </div>
        <div class="stat-tile amber">
            <div class="stat-lbl">Next 30 Days</div>
            <div class="stat-val">{{ $totalUpcoming }}</div>
            <div class="stat-sub">Upcoming periods</div>
        </div>
        <div class="stat-tile">
            <div class="stat-lbl">Members Affected</div>
            <div class="stat-val">{{ $membersAffected }}</div>
            <div class="stat-sub">Active or upcoming</div>
        </div>
        <div class="stat-tile green">
            <div class="stat-lbl">This Month</div>
            <div class="stat-val">{{ $totalThisMonth }}</div>
            <div class="stat-sub">Periods overlapping {{ $now->format('M Y') }}</div>
        </div>
    </div>

    {{-- ── FILTERS ── --}}
    <form method="GET" action="{{ route('admin.availability.index') }}" id="filterForm">
        <input type="hidden" name="view" id="viewInput" value="{{ $viewMode }}">
        <input type="hidden" name="month" value="{{ $monthParam }}">
        <div class="filter-bar fade-in">
            <div class="ff" style="flex:1;min-width:160px;">
                <label>Search member</label>
                <input type="text" name="search" value="{{ $filterSearch }}" placeholder="Name or callsign…">
            </div>
            <div class="ff">
                <label>Status</label>
                <select name="status">
                    <option value="all"      @selected($filterStatus==='all')>All periods</option>
                    <option value="active"   @selected($filterStatus==='active')>Active now</option>
                    <option value="upcoming" @selected($filterStatus==='upcoming')>Upcoming</option>
                    <option value="past"     @selected($filterStatus==='past')>Past</option>
                </select>
            </div>
            <div class="ff">
                <label>From</label>
                <input type="date" name="from" value="{{ $filterFrom }}">
            </div>
            <div class="ff">
                <label>To</label>
                <input type="date" name="to" value="{{ $filterTo }}">
            </div>
            <button type="submit" class="btn btn-navy">Filter</button>
            @if($filterSearch || $filterFrom || $filterTo || $filterStatus !== 'upcoming')
                <a href="{{ route('admin.availability.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    {{-- ══════════════
         LIST VIEW
    ══════════════ --}}
    <div id="listView" style="{{ $viewMode === 'timeline' ? 'display:none;' : '' }}">
        <div class="list-card fade-in">
            <div class="list-head">
                <div>Member</div>
                <div>From</div>
                <div>To</div>
                <div>Reason</div>
                <div>Status</div>
            </div>

            @forelse($periods as $period)
            @php
                $isActive = $period->from_date->lte($now) && $period->to_date->gte($now);
                $isPast   = $period->to_date->lt($now);
                $user     = $period->user;
                $initials = $user ? strtoupper(substr($user->name, 0, 1)) : '?';
            @endphp
            <div class="avail-row {{ $isActive ? 'is-active' : '' }}">
                <div class="avail-cell">
                    <div style="display:flex;align-items:center;gap:.55rem;">
                        <div class="member-av" style="{{ $isActive ? 'background:#c49a00;' : '' }}">{{ $initials }}</div>
                        <div>
                            <div style="font-size:13px;font-weight:bold;color:var(--text);">
                                {{ $user->name ?? 'Unknown' }}
                            </div>
                            @if($user?->callsign)
                                <div style="font-size:10px;color:var(--text-muted);letter-spacing:.06em;">{!! pii($user->callsign, $user->piiVisible()) !!}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="avail-cell">
                    <div style="font-size:13px;font-weight:bold;color:var(--navy);">{{ $period->from_date->format('d M Y') }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $period->from_date->format('l') }}</div>
                </div>
                <div class="avail-cell">
                    <div style="font-size:13px;font-weight:bold;color:var(--navy);">{{ $period->to_date->format('d M Y') }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $period->daysCount() }} day{{ $period->daysCount() !== 1 ? 's' : '' }}</div>
                </div>
                <div class="avail-cell" style="font-size:12px;color:var(--text-muted);">
                    {{ $period->reason ?: '—' }}
                </div>
                <div class="avail-cell">
                    @if($isActive)
                        <span class="period-badge badge-now">● Active</span>
                    @elseif($isPast)
                        <span class="period-badge badge-past">Past</span>
                    @else
                        <span class="period-badge badge-soon">Upcoming</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div style="font-size:2rem;opacity:.15;margin-bottom:.75rem;">📅</div>
                No availability periods found matching your filters.
            </div>
            @endforelse
        </div>
    </div>

    {{-- ══════════════
         TIMELINE VIEW
    ══════════════ --}}
    <div id="timelineView" style="{{ $viewMode === 'list' ? 'display:none;' : '' }}">
        <div class="timeline-card fade-in">
            <div class="timeline-head">
                <span class="timeline-title">📅 Availability Calendar</span>
                <div class="timeline-nav">
                    <a href="{{ route('admin.availability.index', array_merge(request()->except('month'), ['month' => $prevMonth, 'view' => 'timeline'])) }}">← Prev</a>
                    <span class="timeline-month-label">{{ $viewMonth->format('F Y') }}</span>
                    <a href="{{ route('admin.availability.index', array_merge(request()->except('month'), ['month' => $nextMonth, 'view' => 'timeline'])) }}">Next →</a>
                </div>
            </div>

            <div class="timeline-grid">
                <table class="timeline-table">
                    <thead>
                        <tr>
                            <th class="member-col">Member</th>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $date    = $viewMonth->copy()->day($d);
                                $isWknd  = $date->isWeekend();
                                $isToday = $date->isToday();
                            @endphp
                            <th class="{{ $isWknd ? 'weekend' : '' }} {{ $isToday ? 'today-col' : '' }}">
                                <div>{{ $d }}</div>
                                <div style="font-size:8px;opacity:.6;">{{ $date->format('D')[0] }}</div>
                            </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timelineUsers as $user)
                        @php
                            $userPeriods = $timelinePeriods->where('user_id', $user->id);
                            $initials    = strtoupper(substr($user->name, 0, 1));
                        @endphp
                        <tr>
                            <td class="member-name-cell">
                                <div class="member-name-inner">
                                    <div class="member-av">{{ $initials }}</div>
                                    <div>
                                        <div class="member-name-text">{!! pii($user->name, $user->piiVisible()) !!}</div>
                                        @if($user->callsign && (!$_isTempAdmin || ($user->isTemporaryGuest() || $user->isTemporaryAdmin())))
                                            <div class="member-callsign">{!! pii($user->callsign, $user->piiVisible()) !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $date      = $viewMonth->copy()->day($d);
                                $isWknd    = $date->isWeekend();
                                $isToday   = $date->isToday();
                                $unavail   = null;
                                $isStart   = false;
                                $isEnd     = false;
                                foreach($userPeriods as $p) {
                                    if($date->gte($p->from_date) && $date->lte($p->to_date)) {
                                        $unavail = $p;
                                        $isStart = $date->isSameDay($p->from_date);
                                        $isEnd   = $date->isSameDay($p->to_date);
                                        break;
                                    }
                                }
                            @endphp
                            <td class="{{ $isWknd ? 'weekend-cell' : '' }} {{ $isToday ? 'today-cell' : '' }} {{ $unavail ? 'unavail-cell' : '' }} {{ $unavail && $isStart ? 'unavail-start' : '' }} {{ $unavail && $isEnd ? 'unavail-end' : '' }}">
                                @if($unavail)
                                <div class="unavail-tip">
                                    {{ $unavail->from_date->format('d M') }}–{{ $unavail->to_date->format('d M') }}
                                    @if($unavail->reason) · {{ $unavail->reason }}@endif
                                </div>
                                @endif
                            </td>
                            @endfor
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 1 }}" class="empty-state">
                                No members have availability periods in {{ $viewMonth->format('F Y') }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding:.65rem 1.1rem;background:var(--grey);border-top:1px solid var(--grey-mid);display:flex;align-items:center;gap:1rem;font-size:11px;color:var(--text-muted);">
                <span style="display:inline-flex;align-items:center;gap:.35rem;">
                    <span style="width:16px;height:10px;background:var(--red);display:inline-block;border-radius:2px;"></span>
                    Unavailable
                </span>
                <span style="display:inline-flex;align-items:center;gap:.35rem;">
                    <span style="width:16px;height:10px;background:rgba(200,16,46,.04);border:1px solid rgba(200,16,46,.15);display:inline-block;"></span>
                    Today
                </span>
                <span style="display:inline-flex;align-items:center;gap:.35rem;">
                    <span style="width:16px;height:10px;background:#fafafa;border:1px solid var(--grey-mid);display:inline-block;"></span>
                    Weekend
                </span>
                <span style="margin-left:auto;">Hover a red cell to see reason</span>
            </div>
        </div>
    </div>

</div>

<script>
function switchView(v) {
    document.getElementById('viewInput').value = v;
    document.getElementById('listView').style.display      = v === 'list'     ? '' : 'none';
    document.getElementById('timelineView').style.display  = v === 'timeline' ? '' : 'none';
    document.querySelectorAll('.vt-btn').forEach(b => b.classList.toggle('active', b.textContent.trim().toLowerCase().includes(v === 'list' ? 'list' : 'time')));
    // Update URL without submit
    const url = new URL(window.location);
    url.searchParams.set('view', v);
    history.replaceState(null, '', url);
}
</script>
@endsection