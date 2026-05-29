@extends('layouts.admin')
@section('title', 'Activity Logs — Admin')
@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
:root {
    --navy:#003366; --navy-mid:#004080; --navy-faint:#e8eef5;
    --red:#C8102E; --red-faint:#fdf0f2;
    --white:#FFFFFF; --grey:#F2F2F2; --grey-mid:#dde2e8; --grey-dark:#9aa3ae;
    --text:#001f40; --text-mid:#2d4a6b; --text-muted:#6b7f96;
    --green:#1a6b3c; --green-bg:#eef7f2;
    --amber:#8a5500; --amber-bg:#fdf8ec;
    --purple:#5b21b6; --purple-bg:#f5f3ff;
    --teal:#0e7490; --teal-bg:#ecfeff;
    --font:Arial,'Helvetica Neue',Helvetica,sans-serif;
    --shadow-sm:0 1px 3px rgba(0,51,102,.09);
    --shadow-md:0 4px 14px rgba(0,51,102,.11);
}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:var(--font);background:var(--grey);color:var(--text);font-size:14px;}

.rn-header{background:var(--navy);border-bottom:4px solid var(--red);position:sticky;top:0;z-index:100;box-shadow:0 2px 10px rgba(0,0,0,.3);}
.rn-header-inner{max-width:1400px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.rn-brand{display:flex;align-items:center;gap:.85rem;padding:.75rem 0;}
.rn-logo-block{background:var(--red);width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rn-logo-block span{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.15;text-transform:uppercase;}
.rn-org{font-size:15px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.rn-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:2px;letter-spacing:.05em;text-transform:uppercase;}
.rn-back{font-size:12px;font-weight:bold;color:rgba(255,255,255,.8);text-decoration:none;border:1px solid rgba(255,255,255,.25);padding:.35rem .9rem;transition:all .15s;}
.rn-back:hover{background:rgba(255,255,255,.1);color:#fff;}

.page-band{background:var(--white);border-bottom:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);}
.page-band-inner{max-width:1400px;margin:0 auto;padding:1.25rem 1.5rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.page-eyebrow{font-size:11px;font-weight:bold;color:var(--red);text-transform:uppercase;letter-spacing:.18em;margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;}
.page-eyebrow::before{content:'';width:16px;height:2px;background:var(--red);display:inline-block;}
.page-title{font-size:26px;font-weight:bold;color:var(--navy);line-height:1;}
.page-desc{font-size:13px;color:var(--text-muted);margin-top:.35rem;}
.page-datestamp{font-size:11px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:.28rem .7rem;}
.page-actions{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;}

.btn-rn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.2rem;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.07em;transition:all .12s;text-decoration:none;border:1px solid;white-space:nowrap;}
.btn-navy{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-navy:hover{background:var(--navy-mid);}
.btn-red{background:var(--red);border-color:var(--red);color:#fff;}
.btn-red:hover{background:#a50e26;}
.btn-outline{background:transparent;border-color:var(--navy);color:var(--navy);}
.btn-outline:hover{background:var(--navy-faint);}

.ald-wrap{max-width:1400px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}

.alert-success{display:flex;align-items:center;gap:.6rem;padding:.65rem 1rem;margin-bottom:1.2rem;background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);font-size:13px;color:var(--green);font-weight:bold;}

.sec-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;padding-bottom:.5rem;border-bottom:2px solid var(--navy);margin-top:2rem;}
.sec-head:first-child,.sec-head.no-mt{margin-top:0;}
.sec-title{font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--navy);display:flex;align-items:center;gap:.5rem;}
.sec-title::before{content:'';width:3px;height:16px;background:var(--red);display:inline-block;}
.sec-badge{font-size:10px;font-weight:bold;background:var(--navy);color:#fff;padding:2px 8px;letter-spacing:.06em;}

/* ── Stat grid ── */
.stat-grid{display:grid;gap:.85rem;margin-bottom:1.25rem;}
.sg-2{grid-template-columns:repeat(2,1fr);}
.sg-3{grid-template-columns:repeat(3,1fr);}
.sg-4{grid-template-columns:repeat(4,1fr);}
.sg-5{grid-template-columns:repeat(5,1fr);}
@media(max-width:1100px){.sg-5{grid-template-columns:repeat(3,1fr);}.sg-4{grid-template-columns:1fr 1fr;}}
@media(max-width:700px){.sg-5,.sg-4,.sg-3,.sg-2{grid-template-columns:1fr;}}

.stat-card{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1rem 1.15rem .9rem;box-shadow:var(--shadow-sm);position:relative;transition:box-shadow .15s;}
.stat-card:hover{box-shadow:var(--shadow-md);}
.stat-card.sc-red{border-top-color:var(--red);}
.stat-card.sc-green{border-top-color:var(--green);}
.stat-card.sc-amber{border-top-color:#c49a00;}
.stat-card.sc-purple{border-top-color:var(--purple);}
.stat-card.sc-teal{border-top-color:var(--teal);}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--text-muted);margin-bottom:.4rem;}
.stat-value{font-size:32px;font-weight:bold;line-height:1;color:var(--navy);}
.sc-red .stat-value{color:var(--red);}
.sc-green .stat-value{color:var(--green);}
.sc-amber .stat-value{color:var(--amber);}
.sc-purple .stat-value{color:var(--purple);}
.sc-teal .stat-value{color:var(--teal);}
.stat-sub{font-size:12px;color:var(--text-muted);margin-top:.3rem;}
.stat-year-badge{position:absolute;top:.6rem;right:.8rem;font-size:10px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:1px 7px;}
.stat-delta{position:absolute;top:.6rem;right:.8rem;font-size:10px;font-weight:bold;padding:1px 7px;}
.delta-up{color:var(--green);background:var(--green-bg);}
.delta-down{color:var(--red);background:var(--red-faint);}

/* ── Chart grid ── */
.cg{display:grid;gap:1rem;margin-bottom:1.25rem;}
.cg-2-1{grid-template-columns:2fr 1fr;}
.cg-1-1{grid-template-columns:1fr 1fr;}
.cg-3{grid-template-columns:1fr 1fr 1fr;}
.cg-1{grid-template-columns:1fr;}
@media(max-width:900px){.cg-2-1,.cg-1-1,.cg-3{grid-template-columns:1fr;}}

.chart-card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);}
.chart-head{background:var(--navy);padding:.65rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;}
.chart-title{font-size:11px;font-weight:bold;color:rgba(255,255,255,.9);text-transform:uppercase;letter-spacing:.12em;}
.chart-sub{font-size:10px;color:rgba(255,255,255,.45);}
.chart-body{padding:1rem;position:relative;}

/* ── Tables ── */
.table-card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.25rem;}
.table-toolbar{background:var(--navy);border-bottom:2px solid var(--red);padding:.65rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.table-title{font-size:12px;font-weight:bold;color:rgba(255,255,255,.9);text-transform:uppercase;letter-spacing:.1em;}
.table-count{font-size:11px;color:rgba(255,255,255,.5);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:2px 9px;font-weight:bold;}
.ald-table{width:100%;border-collapse:collapse;font-size:12px;}
.ald-table thead tr{background:#002244;border-bottom:2px solid var(--red);}
.ald-table thead th{padding:.55rem .85rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.5);white-space:nowrap;}
.ald-table tbody tr{border-bottom:1px solid var(--grey-mid);transition:background .1s;}
.ald-table tbody tr:last-child{border-bottom:none;}
.ald-table tbody tr:hover{background:#f7f9fc;}
.ald-table tbody td{padding:.65rem .85rem;vertical-align:middle;}
.td-muted{color:var(--text-muted);font-size:11px;}
.td-mono{font-family:monospace;letter-spacing:.04em;}

/* ── Chips ── */
.chip{display:inline-flex;align-items:center;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;padding:2px 7px;border:1px solid;line-height:1;white-space:nowrap;}
.chip-navy{background:var(--navy-faint);border-color:rgba(0,51,102,.3);color:var(--navy);}
.chip-green{background:var(--green-bg);border-color:#6bbf94;color:var(--green);}
.chip-amber{background:var(--amber-bg);border-color:#f5c842;color:var(--amber);}
.chip-grey{background:var(--grey);border-color:var(--grey-mid);color:var(--text-muted);}
.chip-hours{background:#f0f8ff;border-color:#90bcd8;color:#1a4f72;font-family:monospace;font-size:11px;}
.chip-purple{background:var(--purple-bg);border-color:rgba(91,33,182,.25);color:var(--purple);}

.user-av{width:28px;height:28px;border-radius:50%;background:var(--navy);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;flex-shrink:0;}

.act-btns{display:flex;gap:4px;}
.btn-tbl{font-size:10px;font-weight:bold;font-family:var(--font);padding:3px 9px;border:1px solid;cursor:pointer;background:transparent;text-decoration:none;display:inline-flex;align-items:center;text-transform:uppercase;letter-spacing:.04em;transition:all .1s;}
.btn-tbl-edit{color:var(--navy);border-color:rgba(0,51,102,.25);}
.btn-tbl-edit:hover{background:var(--navy-faint);border-color:var(--navy);}
.btn-tbl-del{color:var(--red);border-color:rgba(200,16,46,.2);}
.btn-tbl-del:hover{background:var(--red-faint);border-color:var(--red);}

.rank-badge{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;font-size:10px;font-weight:bold;}
.rank-1{background:var(--amber);color:#fff;}
.rank-2{background:#9aa3ae;color:#fff;}
.rank-3{background:#a0522d;color:#fff;}
.rank-n{background:var(--grey-mid);color:var(--text-muted);}

.hours-bar-wrap{display:flex;align-items:center;gap:.5rem;min-width:100px;}
.hours-bar-bg{flex:1;height:6px;background:var(--grey-mid);position:relative;}
.hours-bar-fill{position:absolute;top:0;left:0;height:100%;background:var(--navy);}
.hours-bar-val{font-size:11px;font-weight:bold;color:var(--navy);white-space:nowrap;min-width:36px;text-align:right;font-family:monospace;}

/* ── Streak pips ── */
.streak-pip{display:inline-block;width:11px;height:11px;margin-right:2px;border-radius:2px;}
.streak-pip.on{background:var(--green);}
.streak-pip.off{background:var(--grey-mid);}

/* ── Heatmap ── */
.heatmap-table{width:100%;border-collapse:collapse;font-size:11px;}
.heatmap-table th{padding:.4rem .5rem;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);white-space:nowrap;}
.heatmap-table td{padding:.35rem .45rem;border:1px solid var(--grey-mid);text-align:center;font-weight:bold;font-size:11px;white-space:nowrap;}

/* ── Shared day rows ── */
.shared-row{display:flex;align-items:center;justify-content:space-between;padding:.55rem .9rem;border-bottom:1px solid var(--grey-mid);gap:.75rem;flex-wrap:wrap;}
.shared-row:last-child{border-bottom:none;}
.shared-row:hover{background:var(--navy-faint);}
.shared-date{font-size:12px;font-weight:bold;color:var(--navy);font-family:monospace;flex-shrink:0;}
.shared-bar-bg{flex:1;height:8px;background:var(--grey-mid);position:relative;min-width:60px;}
.shared-bar-fill{position:absolute;top:0;left:0;height:100%;background:var(--red);}
.shared-meta{display:flex;gap:.4rem;flex-shrink:0;}

/* ── YoY strips ── */
.yoy-strip{display:flex;align-items:center;gap:1rem;padding:.75rem 1rem;border-bottom:1px solid var(--grey-mid);}
.yoy-strip:last-child{border-bottom:none;}
.yoy-label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);flex-shrink:0;min-width:80px;}
.yoy-bars{flex:1;display:flex;flex-direction:column;gap:4px;}
.yoy-bw{display:flex;align-items:center;gap:.5rem;}
.yoy-bl{font-size:10px;color:var(--text-muted);min-width:70px;}
.yoy-bg{flex:1;height:6px;background:var(--grey-mid);position:relative;}
.yoy-bf{position:absolute;top:0;left:0;height:100%;}
.yoy-bv{font-size:11px;font-weight:bold;min-width:40px;text-align:right;font-family:monospace;}

/* ── Event list ── */
.event-list{list-style:none;padding:0;margin:0;}
.event-list li{display:flex;align-items:center;gap:.65rem;padding:.45rem 0;border-bottom:1px solid var(--grey-mid);}
.event-list li:last-child{border-bottom:none;}
.event-rank{font-size:10px;font-weight:bold;color:var(--text-muted);width:18px;text-align:right;flex-shrink:0;}
.event-name{flex:1;font-size:12px;font-weight:bold;color:var(--text);min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.event-hours{font-size:11px;font-family:monospace;font-weight:bold;color:var(--navy);}
.event-count{font-size:10px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:1px 5px;}

/* ── Filter ── */
.filter-card{background:var(--white);border:1px solid var(--grey-mid);border-left:3px solid var(--navy);box-shadow:var(--shadow-sm);margin-bottom:1.25rem;}
.filter-head{background:var(--grey);padding:.6rem 1rem;border-bottom:1px solid var(--grey-mid);}
.filter-head-label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);}
.filter-body{padding:.9rem 1rem;}
.filter-row{display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;}
.filter-group{display:flex;flex-direction:column;gap:.3rem;flex:1;min-width:140px;}
.filter-group label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);}
.filter-group select,.filter-group input{background:var(--white);border:1px solid var(--grey-mid);padding:.45rem .75rem;color:var(--text);font-family:var(--font);font-size:12px;outline:none;width:100%;}
.filter-group select:focus,.filter-group input:focus{border-color:var(--navy);box-shadow:0 0 0 2px rgba(0,51,102,.08);}
.filter-actions{display:flex;gap:.4rem;flex-shrink:0;}
.btn-filter{padding:.45rem 1rem;background:var(--navy);border:1px solid var(--navy);color:#fff;font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;}
.btn-filter:hover{background:var(--navy-mid);}
.btn-reset-f{padding:.45rem .9rem;background:var(--white);border:1px solid var(--grey-mid);color:var(--text-muted);font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;}
.btn-reset-f:hover{border-color:var(--navy);color:var(--navy);}

.pagination-wrap{padding:.75rem 1rem;border-top:1px solid var(--grey-mid);background:var(--grey);}
.empty-state{text-align:center;padding:3rem 1rem;}
.empty-icon{font-size:2rem;opacity:.2;margin-bottom:.75rem;}
.empty-text{font-size:13px;color:var(--text-muted);}

@media print{
    .rn-header,.page-band,.filter-card,.no-print,.act-btns,.btn-rn{display:none !important;}
    .ald-wrap{padding:0 !important;}
    body{background:white !important;}
    .chart-card,.stat-card,.table-card{box-shadow:none !important;border:1px solid #ccc !important;break-inside:avoid;}
}
</style>

@php use Carbon\Carbon; $now = Carbon::now(); @endphp

<header class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo-block"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Activity &amp; Hours Analytics</div>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Admin</a>
    </div>
</header>

<div class="page-band">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Admin Panel</div>
            <h1 class="page-title">Activity Analytics Dashboard</h1>
            <p class="page-desc">Full attendance, hours, trends &amp; comparisons — {{ $yearLabel }} · all recorded volunteer activity</p>
        </div>
        <div class="page-actions">
            <div class="page-datestamp no-print">{{ $now->format('D d M Y · H:i') }}</div>
            <button onclick="exportPDF()" class="btn-rn btn-outline no-print">⬇ Export PDF</button>
            <button onclick="document.getElementById('import-modal').style.display='flex'" class="btn-rn btn-outline no-print">⚡ Import from Events</button>
            <a href="{{ route('admin.activity-logs.create') }}" class="btn-rn btn-red">+ New Entry</a>
        </div>
    </div>
</div>

<div class="ald-wrap" id="pdfContent">

    @if(session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    {{-- ══ ALL-TIME KPIs ══ --}}
    <div class="sec-head no-mt">
        <div class="sec-title">All-Time Overview</div>
        <span class="sec-badge">Since records began</span>
    </div>
    <div class="stat-grid sg-5">
        <div class="stat-card">
            <div class="stat-label">Total Entries</div>
            <div class="stat-value">{{ number_format($allTimeEntries) }}</div>
            <div class="stat-sub">All recorded activities</div>
        </div>
        <div class="stat-card sc-red">
            <div class="stat-label">Total Hours</div>
            <div class="stat-value">{{ number_format($allTimeHours, 1) }}</div>
            <div class="stat-sub">Volunteer hours, all time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Members Logged</div>
            <div class="stat-value">{{ number_format($allTimeUsers) }}</div>
            <div class="stat-sub">Unique members with activity</div>
        </div>
        <div class="stat-card sc-amber">
            <div class="stat-label">Avg Hrs / Entry</div>
            <div class="stat-value">{{ $allTimeEntries > 0 ? number_format($allTimeHours / $allTimeEntries, 1) : '0.0' }}</div>
            <div class="stat-sub">Mean hours per log entry</div>
        </div>
        <div class="stat-card sc-purple">
            <div class="stat-label">Busiest Month</div>
            <div class="stat-value" style="font-size:17px;margin-top:4px;">{{ $topMonth?->month_label ?? '—' }}</div>
            <div class="stat-sub">{{ $topMonth ? number_format($topMonth->hours, 1) . 'h · ' . $topMonth->entries . ' entries' : 'No data yet' }}</div>
        </div>
    </div>

    {{-- ══ ACADEMIC YEAR SNAPSHOT ══ --}}
    <div class="sec-head">
        <div class="sec-title">Academic Year Snapshot</div>
        <span class="sec-badge">{{ $yearLabel }} · {{ $yearStart->format('d M Y') }} – {{ $yearEnd->format('d M Y') }}</span>
    </div>
    @php
        $hoursChange   = $lastYearHours   > 0 ? round((($yearHours   - $lastYearHours)   / $lastYearHours)   * 100, 1) : null;
        $entriesChange = $lastYearEntries > 0 ? round((($yearEntries - $lastYearEntries) / $lastYearEntries) * 100, 1) : null;
    @endphp
    <div class="stat-grid sg-5">
        <div class="stat-card sc-green">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            @if ($hoursChange !== null)
                <div class="stat-delta {{ $hoursChange >= 0 ? 'delta-up' : 'delta-down' }}">{{ $hoursChange >= 0 ? '▲' : '▼' }} {{ abs($hoursChange) }}%</div>
            @endif
            <div class="stat-label">Hours This Year</div>
            <div class="stat-value">{{ number_format($yearHours, 1) }}</div>
            <div class="stat-sub">vs {{ number_format($lastYearHours, 1) }}h last year</div>
        </div>
        <div class="stat-card">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            @if ($entriesChange !== null)
                <div class="stat-delta {{ $entriesChange >= 0 ? 'delta-up' : 'delta-down' }}">{{ $entriesChange >= 0 ? '▲' : '▼' }} {{ abs($entriesChange) }}%</div>
            @endif
            <div class="stat-label">Entries This Year</div>
            <div class="stat-value">{{ number_format($yearEntries) }}</div>
            <div class="stat-sub">vs {{ number_format($lastYearEntries) }} last year</div>
        </div>
        <div class="stat-card">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Active Members</div>
            <div class="stat-value">{{ number_format($yearActiveUsers) }}</div>
            <div class="stat-sub">Members with ≥1 entry</div>
        </div>
        <div class="stat-card sc-amber">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Avg Hrs / Member</div>
            <div class="stat-value">{{ $avgHoursPerUser }}</div>
            <div class="stat-sub">Mean hours per active member</div>
        </div>
        <div class="stat-card sc-teal">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Busiest Day Ever</div>
            <div class="stat-value" style="font-size:17px;margin-top:4px;">
                {{ $busiestDay ? \Carbon\Carbon::parse($busiestDay->event_date)->format('d M Y') : '—' }}
            </div>
            <div class="stat-sub">{{ $busiestDay ? number_format($busiestDay->hours, 1) . 'h · ' . $busiestDay->entries . ' entries' : 'No data yet' }}</div>
        </div>
    </div>

    {{-- ══ TRENDS ══ --}}
    <div class="sec-head">
        <div class="sec-title">Activity Trends</div>
        <span class="sec-badge">{{ $yearLabel }}</span>
    </div>

    <div class="cg cg-2-1">
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Monthly Hours &amp; Entries</span>
                <span class="chart-sub">{{ $yearLabel }}</span>
            </div>
            <div class="chart-body" style="height:260px;"><canvas id="monthlyChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Cumulative Hours</span>
                <span class="chart-sub">Running total this year</span>
            </div>
            <div class="chart-body" style="height:260px;"><canvas id="cumulativeChart"></canvas></div>
        </div>
    </div>

    <div class="cg cg-1-1">
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Year-on-Year Hours</span>
                <span class="chart-sub">This year vs last year</span>
            </div>
            <div class="chart-body" style="height:240px;"><canvas id="yoyChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Day-of-Week Activity</span>
                <span class="chart-sub">All time · entries &amp; hours</span>
            </div>
            <div class="chart-body" style="height:240px;"><canvas id="dowChart"></canvas></div>
        </div>
    </div>

    <div class="cg cg-1-1">
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Avg Hours per Entry by Month</span>
                <span class="chart-sub">Mean session length trend</span>
            </div>
            <div class="chart-body" style="height:220px;"><canvas id="avgHoursChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Year-on-Year Summary</span>
                <span class="chart-sub">{{ $yearLabel }} vs previous</span>
            </div>
            <div class="chart-body" style="padding:0;">
                @php $maxH = max($yearHours, $lastYearHours, 1); $maxE = max($yearEntries, $lastYearEntries, 1); @endphp
                <div class="yoy-strip">
                    <div class="yoy-label">Hours</div>
                    <div class="yoy-bars">
                        <div class="yoy-bw">
                            <span class="yoy-bl">{{ $yearLabel }}</span>
                            <div class="yoy-bg"><div class="yoy-bf" style="width:{{ ($yearHours/$maxH)*100 }}%;background:var(--navy);"></div></div>
                            <span class="yoy-bv" style="color:var(--navy);">{{ number_format($yearHours,1) }}h</span>
                        </div>
                        <div class="yoy-bw">
                            <span class="yoy-bl">Previous year</span>
                            <div class="yoy-bg"><div class="yoy-bf" style="width:{{ ($lastYearHours/$maxH)*100 }}%;background:var(--grey-dark);"></div></div>
                            <span class="yoy-bv" style="color:var(--text-muted);">{{ number_format($lastYearHours,1) }}h</span>
                        </div>
                    </div>
                </div>
                <div class="yoy-strip">
                    <div class="yoy-label">Entries</div>
                    <div class="yoy-bars">
                        <div class="yoy-bw">
                            <span class="yoy-bl">{{ $yearLabel }}</span>
                            <div class="yoy-bg"><div class="yoy-bf" style="width:{{ ($yearEntries/$maxE)*100 }}%;background:var(--red);"></div></div>
                            <span class="yoy-bv" style="color:var(--red);">{{ number_format($yearEntries) }}</span>
                        </div>
                        <div class="yoy-bw">
                            <span class="yoy-bl">Previous year</span>
                            <div class="yoy-bg"><div class="yoy-bf" style="width:{{ ($lastYearEntries/$maxE)*100 }}%;background:var(--grey-dark);"></div></div>
                            <span class="yoy-bv" style="color:var(--text-muted);">{{ number_format($lastYearEntries) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MEMBER ANALYTICS ══ --}}
    <div class="sec-head">
        <div class="sec-title">Member Analytics</div>
        <span class="sec-badge">{{ $yearLabel }}</span>
    </div>

    <div class="cg cg-2-1">
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Hours by Member (Top 10)</span>
                <span class="chart-sub">{{ $yearLabel }}</span>
            </div>
            <div class="chart-body" style="height:260px;"><canvas id="userHoursChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Hours Distribution</span>
                <span class="chart-sub">Share of total hours</span>
            </div>
            <div class="chart-body" style="height:260px;display:flex;align-items:center;justify-content:center;">
                <canvas id="distroChart" style="max-height:240px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Heatmap --}}
    @if($heatmapGrid->count() > 0 && $heatmapMonths->count() > 0)
    <div class="chart-card" style="margin-bottom:1.25rem;">
        <div class="chart-head">
            <span class="chart-title">Member Activity Heatmap — Hours per Month</span>
            <span class="chart-sub">Darker green = more hours · grey = no activity</span>
        </div>
        <div class="chart-body" style="overflow-x:auto;padding:.75rem 1rem;">
            <table class="heatmap-table">
                <thead>
                    <tr>
                        <th style="text-align:left;min-width:120px;">Member</th>
                        @foreach ($heatmapMonths as $m)<th>{{ $m }}</th>@endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($heatmapGrid as $row)
                    @php
                        $rowMax   = max(max($row['data']), 1);
                        $rowTotal = array_sum($row['data']);
                    @endphp
                    <tr>
                        <td style="text-align:left;font-weight:bold;color:var(--text);padding:.35rem .6rem;">{{ $row['name'] }}</td>
                        @foreach ($row['data'] as $h)
                        @php
                            $intensity = $rowMax > 0 ? $h / $rowMax : 0;
                            $bg = $h == 0 ? '#f2f5f9' : 'rgba(26,107,60,' . max(0.12, min(0.85, $intensity * 0.9 + 0.1)) . ')';
                            $fg = $intensity > 0.5 ? '#fff' : ($h > 0 ? 'var(--green)' : 'var(--grey-dark)');
                        @endphp
                        <td style="background:{{ $bg }};color:{{ $fg }};">{{ $h > 0 ? $h . 'h' : '—' }}</td>
                        @endforeach
                        <td style="font-weight:bold;color:var(--navy);background:var(--navy-faint);">{{ $rowTotal }}h</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Consistency table --}}
    @if($memberStreaks->count() > 0)
    <div class="table-card">
        <div class="table-toolbar">
            <div style="display:flex;align-items:center;gap:.65rem;">
                <span class="table-title">Member Consistency — Months Active This Year</span>
                <span class="table-count">{{ $memberStreaks->count() }} members</span>
            </div>
        </div>
        <table class="ald-table">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Member</th>
                    <th>Month Activity</th>
                    <th>Consistency</th>
                    <th>Hours</th>
                    <th>Events</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($memberStreaks as $i => $ms)
                @php $pct = $totalMonthsSoFar > 0 ? round(($ms['months'] / $totalMonthsSoFar) * 100) : 0; @endphp
                <tr>
                    <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">{{ $i+1 }}</span></td>
                    <td style="font-weight:bold;">{{ $ms['name'] }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div>
                                @for ($m = 0; $m < $totalMonthsSoFar; $m++)
                                    <span class="streak-pip {{ $m < $ms['months'] ? 'on' : 'off' }}"></span>
                                @endfor
                            </div>
                            <span style="font-size:11px;font-weight:bold;color:var(--navy);">{{ $ms['months'] }}/{{ $totalMonthsSoFar }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div style="flex:1;height:6px;background:var(--grey-mid);position:relative;min-width:80px;">
                                <div style="position:absolute;top:0;left:0;height:100%;width:{{ $pct }}%;background:{{ $pct>=80?'var(--green)':($pct>=50?'#f59e0b':'var(--red)') }};"></div>
                            </div>
                            <span style="font-size:11px;font-weight:bold;color:var(--text-muted);">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td><span class="chip chip-hours">{{ $ms['hours'] }}h</span></td>
                    <td><span class="chip chip-grey">{{ $ms['events'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Per-member breakdown --}}
    <div class="table-card">
        <div class="table-toolbar">
            <div style="display:flex;align-items:center;gap:.65rem;">
                <span class="table-title">Per-Member Summary</span>
                <span class="table-count">{{ $perUserStats->count() }} members</span>
            </div>
        </div>
        @if($perUserStats->isEmpty())
            <div class="empty-state"><div class="empty-icon">👤</div><div class="empty-text">No activity data for this year yet.</div></div>
        @else
        <table class="ald-table">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Member</th>
                    <th>Hours This Year</th>
                    <th>Entries</th>
                    <th>Unique Events</th>
                    <th>Share of Total</th>
                    <th>Last Activity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perUserStats as $i => $stat)
                @php $maxH = $perUserStats->first()['hours'] ?: 1; @endphp
                <tr>
                    <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">{{ $i+1 }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="user-av">{{ strtoupper(substr($stat['name'],0,1)) }}</div>
                            <span style="font-size:13px;font-weight:bold;color:var(--text);">{{ $stat['name'] }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="hours-bar-wrap">
                            <div class="hours-bar-bg"><div class="hours-bar-fill" style="width:{{ min(100,($stat['hours']/$maxH)*100) }}%;"></div></div>
                            <span class="hours-bar-val">{{ $stat['hours'] }}h</span>
                        </div>
                    </td>
                    <td><span class="chip chip-navy">{{ $stat['entries'] }}</span></td>
                    <td><span class="chip chip-grey">{{ $stat['events'] }} events</span></td>
                    <td>
                        @php $sp = $yearHours > 0 ? round(($stat['hours']/$yearHours)*100,1) : 0; @endphp
                        <div style="display:flex;align-items:center;gap:.4rem;">
                            <div style="width:50px;height:5px;background:var(--grey-mid);position:relative;">
                                <div style="position:absolute;top:0;left:0;height:100%;width:{{ $sp }}%;background:var(--red);"></div>
                            </div>
                            <span style="font-size:11px;font-weight:bold;color:var(--text-muted);">{{ $sp }}%</span>
                        </div>
                    </td>
                    <td class="td-muted">{{ $stat['last'] ? \Carbon\Carbon::parse($stat['last'])->format('d M Y') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ══ SHARED ACTIVITY & EVENTS ══ --}}
    <div class="sec-head">
        <div class="sec-title">Multi-Member Activity &amp; Events</div>
        <span class="sec-badge">Days with simultaneous participation</span>
    </div>

    <div class="cg cg-1-1">
        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Shared Activity Days</span>
                <span class="chart-sub">Days multiple members logged hours</span>
            </div>
            <div class="chart-body" style="padding:0;max-height:320px;overflow-y:auto;">
                @if ($sameDayActivity->isEmpty())
                    <div style="padding:1.5rem;text-align:center;font-size:12px;color:var(--text-muted);">No shared activity days yet.</div>
                @else
                @php $maxM = $sameDayActivity->max('member_count'); @endphp
                @foreach ($sameDayActivity as $day)
                <div class="shared-row">
                    <div>
                        <div class="shared-date">{{ \Carbon\Carbon::parse($day->event_date)->format('d M Y') }}</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">{{ \Carbon\Carbon::parse($day->event_date)->format('l') }}</div>
                    </div>
                    <div class="shared-bar-bg">
                        <div class="shared-bar-fill" style="width:{{ ($day->member_count/$maxM)*100 }}%;"></div>
                    </div>
                    <div class="shared-meta">
                        <span class="chip chip-navy">{{ $day->member_count }} members</span>
                        <span class="chip chip-hours">{{ number_format($day->total_hours,1) }}h</span>
                        <span class="chip chip-grey">{{ $day->entries }} entries</span>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-head">
                <span class="chart-title">Top Events by Hours</span>
                <span class="chart-sub">All time</span>
            </div>
            <div class="chart-body" style="padding:.75rem 1rem;max-height:320px;overflow-y:auto;">
                @if($topEvents->isEmpty())
                    <div style="font-size:12px;color:var(--text-muted);text-align:center;padding:1rem;">No data yet</div>
                @else
                <ul class="event-list">
                    @foreach($topEvents as $i => $evt)
                    <li>
                        <span class="event-rank">{{ $i+1 }}</span>
                        <span class="event-name" title="{{ $evt->event_name }}">{{ $evt->event_name }}</span>
                        <span class="event-count">{{ $evt->entry_count }}×</span>
                        <span class="event-hours">{{ number_format($evt->total_hours,1) }}h</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ FILTER + LOG TABLE ══ --}}
    <div class="sec-head no-print">
        <div class="sec-title">Log Entries</div>
        <span class="sec-badge">Filtered view</span>
    </div>

    <div class="filter-card no-print">
        <div class="filter-head"><span class="filter-head-label">🔍 Filter Entries</span></div>
        <div class="filter-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Member</label>
                        <select name="user_id">
                            <option value="">All members</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $_isTempAdmin && !($u->isTemporaryGuest() || $u->isTemporaryAdmin()) ? '●●●●●●●●●' : $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Event Name</label>
                        <input type="text" name="event_name" value="{{ request('event_name') }}" placeholder="Search event…">
                    </div>
                    <div class="filter-group">
                        <label>From Date</label>
                        <input type="date" name="from" value="{{ request('from') }}">
                    </div>
                    <div class="filter-group">
                        <label>To Date</label>
                        <input type="date" name="to" value="{{ request('to') }}">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">Apply</button>
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn-reset-f">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request()->hasAny(['user_id','event_name','from','to']))
    <div class="stat-grid sg-3" style="margin-bottom:1rem;">
        <div class="stat-card">
            <div class="stat-label">Filtered Entries</div>
            <div class="stat-value" style="font-size:26px;">{{ number_format($totalEntries) }}</div>
        </div>
        <div class="stat-card sc-red">
            <div class="stat-label">Filtered Hours</div>
            <div class="stat-value" style="font-size:26px;">{{ number_format($totalHours, 1) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Unique Members</div>
            <div class="stat-value" style="font-size:26px;">{{ $logs->pluck('user_id')->unique()->count() }}</div>
        </div>
    </div>
    @endif

    <div class="table-card">
        <div class="table-toolbar">
            <div style="display:flex;align-items:center;gap:.65rem;">
                <span class="table-title">Activity Log Entries</span>
                <span class="table-count">{{ number_format($logs->total()) }} total</span>
            </div>
            <a href="{{ route('admin.activity-logs.create') }}" class="btn-rn btn-red no-print" style="padding:.35rem .9rem;font-size:11px;">+ New Entry</a>
        </div>
        @if($logs->isEmpty())
            <div class="empty-state"><div class="empty-icon">📋</div><div class="empty-text">No entries found matching your filters.</div></div>
        @else
        <div style="overflow-x:auto;">
            <table class="ald-table">
                <thead>
                    <tr>
                        <th style="width:42px;">#</th>
                        <th>Member</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Hours</th>
                        <th>Logged By</th>
                        <th>Created</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td class="td-muted td-mono">{{ $log->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <div class="user-av">{{ strtoupper(substr($log->user->name ?? '?',0,1)) }}</div>
                                <span style="font-weight:bold;">{{ ($_isTempAdmin && isset($log->user) && !($log->user->isTemporaryGuest() || $log->user->isTemporaryAdmin())) ? '●●●●●●●●●' : ($log->user->name ?? '—') }}</span>
                            </div>
                        </td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->event_name }}">{{ $log->event_name }}</td>
                        <td class="td-mono" style="white-space:nowrap;">{{ $log->event_date->format('d M Y') }}</td>
                        <td><span class="chip chip-hours">{{ $log->hours }}h</span></td>
                        <td class="td-muted">{{ $log->loggedByUser->name ?? '—' }}</td>
                        <td class="td-muted">{{ $log->created_at->format('d M Y') }}</td>
                        <td class="no-print">
                            <div class="act-btns">
                                <a href="{{ route('admin.activity-logs.edit', $log) }}" class="btn-tbl btn-tbl-edit">Edit</a>
                                <form method="POST" action="{{ route('admin.activity-logs.destroy', $log) }}" onsubmit="return confirm('Delete this log entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-tbl btn-tbl-del">Del</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="pagination-wrap no-print">{{ $logs->links() }}</div>
        @endif
        @endif
    </div>

</div>

<script>
const months         = @json($months);
const monthlyHours   = @json($monthlyHours);
const monthlyEntries = @json($monthlyEntries);
const monthlyAvgH    = @json($monthlyAvgHours);
const monthlyLY      = @json($monthlyLastYear);
const cumulative     = @json($cumulativeHours);
const userNames      = @json($chartUserNames);
const userHours      = @json($chartUserHours);
const dowLabels      = @json($dowLabels);
const dowEntries     = @json($dowEntries);
const dowHours       = @json($dowHours);

const NAVY   = 'rgba(0,51,102,0.85)';
const NAVYL  = 'rgba(0,51,102,0.15)';
const RED    = 'rgba(200,16,46,0.85)';
const REDL   = 'rgba(200,16,46,0.15)';
const GREEN  = 'rgba(26,107,60,0.85)';
const GREENL = 'rgba(26,107,60,0.15)';
const GREY   = 'rgba(154,163,174,0.6)';
const AMBER  = 'rgba(138,85,0,0.75)';
const palette = ['#003366','#C8102E','#1a6b3c','#8a5500','#004080','#0e7490','#7c3aed','#b45309','#047857','#be185d'];

Chart.defaults.font.family = "Arial,'Helvetica Neue',Helvetica,sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6b7f96';

// ── Monthly hours + entries ──
new Chart(document.getElementById('monthlyChart'), {
    type:'bar', data:{ labels:months, datasets:[
        { label:'Hours', data:monthlyHours, backgroundColor:NAVY, borderColor:'rgba(0,51,102,.9)', borderWidth:1, order:2, yAxisID:'y' },
        { label:'Entries', data:monthlyEntries, type:'line', borderColor:RED, backgroundColor:REDL, borderWidth:2, pointBackgroundColor:RED, pointRadius:3, tension:0.3, fill:false, order:1, yAxisID:'y1' }
    ]},
    options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
        plugins:{ legend:{position:'top',labels:{boxWidth:12,padding:12}} },
        scales:{ x:{grid:{display:false},ticks:{maxRotation:45}}, y:{position:'left',title:{display:true,text:'Hours'},beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'}}, y1:{position:'right',title:{display:true,text:'Entries'},beginAtZero:true,grid:{drawOnChartArea:false}} }
    }
});

// ── Cumulative ──
new Chart(document.getElementById('cumulativeChart'), {
    type:'line', data:{ labels:months, datasets:[
        { label:'Cumulative Hours', data:cumulative, borderColor:GREEN, backgroundColor:GREENL, borderWidth:2.5, pointBackgroundColor:GREEN, pointRadius:4, tension:0.4, fill:true }
    ]},
    options:{ responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y}h total`}} },
        scales:{ x:{grid:{display:false},ticks:{maxRotation:45}}, y:{beginAtZero:true,title:{display:true,text:'Cumulative Hours'},grid:{color:'rgba(0,0,0,.05)'}} }
    }
});

// ── YoY ──
new Chart(document.getElementById('yoyChart'), {
    type:'bar', data:{ labels:months, datasets:[
        { label:'This Year', data:monthlyHours, backgroundColor:NAVY, borderWidth:0 },
        { label:'Last Year', data:monthlyLY.slice(0, months.length), backgroundColor:GREY, borderWidth:0 }
    ]},
    options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
        plugins:{ legend:{position:'top',labels:{boxWidth:12,padding:12}} },
        scales:{ x:{grid:{display:false},ticks:{maxRotation:45}}, y:{beginAtZero:true,title:{display:true,text:'Hours'},grid:{color:'rgba(0,0,0,.05)'}} }
    }
});

// ── Day of week ──
new Chart(document.getElementById('dowChart'), {
    type:'bar', data:{ labels:dowLabels, datasets:[
        { label:'Entries', data:dowEntries, backgroundColor:RED, borderWidth:0, order:2, yAxisID:'y' },
        { label:'Hours', data:dowHours, type:'line', borderColor:NAVY, backgroundColor:NAVYL, borderWidth:2, pointBackgroundColor:NAVY, pointRadius:4, tension:0.3, fill:false, order:1, yAxisID:'y1' }
    ]},
    options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
        plugins:{ legend:{position:'top',labels:{boxWidth:12,padding:12}} },
        scales:{ x:{grid:{display:false}}, y:{position:'left',title:{display:true,text:'Entries'},beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'}}, y1:{position:'right',title:{display:true,text:'Hours'},beginAtZero:true,grid:{drawOnChartArea:false}} }
    }
});

// ── Avg hours per entry ──
new Chart(document.getElementById('avgHoursChart'), {
    type:'line', data:{ labels:months, datasets:[
        { label:'Avg Hrs/Entry', data:monthlyAvgH, borderColor:AMBER, backgroundColor:'rgba(138,85,0,.1)', borderWidth:2.5, pointBackgroundColor:AMBER, pointRadius:4, tension:0.4, fill:true }
    ]},
    options:{ responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y}h avg`}} },
        scales:{ x:{grid:{display:false},ticks:{maxRotation:45}}, y:{beginAtZero:true,title:{display:true,text:'Avg Hours'},grid:{color:'rgba(0,0,0,.05)'}} }
    }
});

// ── Hours by member ──
new Chart(document.getElementById('userHoursChart'), {
    type:'bar', data:{ labels:userNames, datasets:[{ label:'Hours', data:userHours, backgroundColor:userNames.map((_,i)=>palette[i%palette.length]), borderWidth:0 }]},
    options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.x}h`}} },
        scales:{ x:{beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'},title:{display:true,text:'Hours'}}, y:{grid:{display:false}} }
    }
});

// ── Donut ──
@php
    $dL = $perUserStats->take(8)->pluck('name');
    $dD = $perUserStats->take(8)->pluck('hours');
    if ($perUserStats->count() > 8) { $dL->push('Others'); $dD->push(round($perUserStats->skip(8)->sum('hours'),2)); }
@endphp
new Chart(document.getElementById('distroChart'), {
    type:'doughnut', data:{ labels:@json($dL), datasets:[{ data:@json($dD), backgroundColor:palette, borderWidth:2, borderColor:'#fff' }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'62%',
        plugins:{ legend:{position:'bottom',labels:{boxWidth:10,padding:8,font:{size:10}}}, tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.parsed}h`}} }
    }
});

// ── PDF Export ──
async function exportPDF() {
    const btn = document.querySelector('[onclick="exportPDF()"]');
    btn.textContent = '⏳ Generating…'; btn.disabled = true;
    try {
        const { jsPDF } = window.jspdf;
        const canvas = await html2canvas(document.getElementById('pdfContent'), { scale:1.5, useCORS:true, backgroundColor:'#ffffff', logging:false, ignoreElements:el=>el.classList.contains('no-print') });
        const imgData = canvas.toDataURL('image/jpeg', 0.92);
        const pdf = new jsPDF({ orientation:'p', unit:'mm', format:'a4' });
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgH = (canvas.height * pdfW) / canvas.width;
        let yPos = 0;
        while (yPos < imgH) { if (yPos > 0) pdf.addPage(); pdf.addImage(imgData, 'JPEG', 0, -yPos, pdfW, imgH); yPos += pdfH; }
        pdf.save(`raynet-activity-logs-${new Date().toISOString().slice(0,10)}.pdf`);
    } catch(e) { alert('PDF generation failed. Use browser Print → Save as PDF instead.'); }
    btn.textContent = '⬇ Export PDF'; btn.disabled = false;
}
</script>
{{-- Import from Events modal --}}
<div id="import-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-top:4px solid var(--navy);border-radius:0;padding:1.5rem;max-width:480px;width:100%;margin:1rem;box-shadow:0 8px 32px rgba(0,0,0,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div style="font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--red);margin-bottom:.2rem;">Import from Events</div>
                <div style="font-size:11px;color:var(--text-muted);">Creates activity log entries from confirmed/standby operator shift times.</div>
            </div>
            <button onclick="document.getElementById('import-modal').style.display='none'" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--grey-dark);padding:0 .25rem;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.activity-logs.import-from-events') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);display:block;margin-bottom:.35rem;">Specific Event (optional)</label>
                <select name="event_id" style="width:100%;padding:.5rem .75rem;border:1px solid var(--grey-mid);font-family:var(--font);font-size:13px;outline:none;">
                    <option value="">— All events (import everything) —</option>
                    @foreach(\App\Models\Event::orderByDesc('starts_at')->get() as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->title }} — {{ $ev->starts_at?->format('j M Y') }}</option>
                    @endforeach
                </select>
            </div>
            <div style="background:var(--amber-bg);border:1px solid #e8c96a;border-left:3px solid #c8a030;padding:.6rem .85rem;font-size:11px;color:var(--amber);margin-bottom:1rem;">
                ⚠ Only imports operators with shift or report/depart times set. Already-logged entries are skipped automatically.
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('import-modal').style.display='none'"
                        style="padding:.5rem 1.1rem;background:transparent;border:1px solid var(--grey-mid);font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:.5rem 1.1rem;background:var(--navy);border:1px solid var(--navy);color:#fff;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;">
                    ⚡ Import Now
                </button>
            </div>
        </form>

        <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--grey-mid);">
            <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--red);margin-bottom:.5rem;">⚠ Reverse an Import</div>
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:.75rem;">Removes all activity log entries that match an event name and date. Cannot be undone.</div>
            <form method="POST" action="{{ route('admin.activity-logs.reverse-event') }}"
                  onsubmit="return confirm('This will permanently delete all activity log entries for this event. Are you sure?')">
                @csrf
                <div style="display:flex;gap:.5rem;align-items:flex-end;">
                    <div style="flex:1;">
                        <label style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);display:block;margin-bottom:.3rem;">Select Event</label>
                        <select name="event_id" required style="width:100%;padding:.45rem .75rem;border:1px solid var(--grey-mid);font-family:var(--font);font-size:12px;outline:none;">
                            <option value="">— Choose event to reverse —</option>
                            @foreach(\App\Models\Event::orderByDesc('starts_at')->get() as $ev)
                                <option value="{{ $ev->id }}" data-title="{{ $ev->title }}" data-date="{{ $ev->starts_at?->toDateString() }}">
                                    {{ $ev->title }} — {{ $ev->starts_at?->format('j M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            style="padding:.45rem 1rem;background:var(--red);border:1px solid var(--red);color:#fff;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">
                        ✕ Reverse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection