@extends('layouts.admin')
@section('title', 'Manage members')
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
    --orange:    #c2410c;
    --orange-bg: #fff7ed;
    --purple:    #5b21b6;
    --purple-bg: #f5f3ff;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.11);
    --shadow-xl: 0 20px 60px rgba(0,0,0,.25);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

/* ── HEADER ── */
.rn-header{background:var(--navy);border-bottom:4px solid var(--red);position:sticky;top:0;z-index:100;box-shadow:0 2px 10px rgba(0,0,0,.3);}
.rn-header-inner{max-width:1340px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.rn-brand{display:flex;align-items:center;gap:.85rem;padding:.75rem 0;}
.rn-logo-block{background:var(--red);width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rn-logo-block span{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.15;text-transform:uppercase;}
.rn-org{font-size:15px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.rn-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:2px;letter-spacing:.05em;text-transform:uppercase;}
.rn-back{font-size:12px;font-weight:bold;color:rgba(255,255,255,.8);text-decoration:none;border:1px solid rgba(255,255,255,.25);padding:.35rem .9rem;transition:all .15s;}
.rn-back:hover{background:rgba(255,255,255,.1);color:#fff;}

/* ── PAGE BAND ── */
.page-band{background:var(--white);border-bottom:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);}
.page-band-inner{max-width:1340px;margin:0 auto;padding:1.25rem 1.5rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.page-eyebrow{font-size:11px;font-weight:bold;color:var(--red);text-transform:uppercase;letter-spacing:.18em;margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;}
.page-eyebrow::before{content:'';width:16px;height:2px;background:var(--red);display:inline-block;}
.page-title{font-size:26px;font-weight:bold;color:var(--navy);line-height:1;}
.page-desc{font-size:13px;color:var(--text-muted);margin-top:.35rem;}
.page-datestamp{font-size:11px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:.28rem .7rem;}

/* ── WRAP ── */
.wrap{max-width:1340px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}

/* ── STAT CARDS ── */
.stat-grid{display:grid;gap:.85rem;margin-bottom:.85rem;}
.stat-grid-4{grid-template-columns:repeat(4,1fr);}
.stat-grid-3{grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem;}
@media(max-width:900px){.stat-grid-4,.stat-grid-3{grid-template-columns:1fr 1fr;}}
.stat-card{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1rem 1.15rem .9rem;box-shadow:var(--shadow-sm);position:relative;transition:box-shadow .15s;}
.stat-card:hover{box-shadow:var(--shadow-md);}
.stat-card.sc-red{border-top-color:var(--red);}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--text-muted);margin-bottom:.4rem;}
.stat-value{font-size:34px;font-weight:bold;line-height:1;color:var(--navy);}
.sc-red .stat-value{color:var(--red);}
.stat-sub{font-size:12px;color:var(--text-muted);margin-top:.3rem;}
.stat-year-badge{position:absolute;top:.6rem;right:.8rem;font-size:10px;color:var(--text-muted);background:var(--grey);border:1px solid var(--grey-mid);padding:1px 7px;}

/* ── ALERTS ── */
.alert-success{display:flex;align-items:center;gap:.6rem;padding:.65rem 1rem;margin-bottom:1.2rem;background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);font-size:13px;color:var(--green);font-weight:bold;}

/* ── PENDING PANELS ── */
.pending-panel{background:var(--white);border:1px solid #e8d98a;border-left:4px solid #c49a00;margin-bottom:1.5rem;box-shadow:var(--shadow-sm);}
.pending-head{display:flex;align-items:center;gap:.8rem;padding:.75rem 1.2rem;border-bottom:1px solid #f5e8a0;background:var(--amber-bg);}
.pending-head-icon{width:28px;height:28px;background:#fde68a;border:1px solid #fcd34d;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.pending-title{font-size:14px;font-weight:bold;color:var(--amber);}
.pending-meta{font-size:11px;color:var(--text-muted);margin-top:2px;}
.pending-body{padding:.35rem;}
.pending-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.6rem .85rem;flex-wrap:wrap;transition:background .1s;}
.pending-row:hover{background:var(--amber-bg);}
.pname{font-size:14px;font-weight:bold;}
.pid{font-size:11px;color:var(--text-muted);}
.pcall-old{font-size:12px;color:var(--text-muted);text-decoration:line-through;}
.pcall-new{font-size:13px;font-weight:bold;color:var(--amber);padding:1px 8px;background:#fef3c7;border:1px solid #fde68a;letter-spacing:.06em;}
.parrow{color:var(--text-muted);font-size:13px;margin:0 4px;}

/* ════════════════════════════════════════════
   MEMBER LIST
════════════════════════════════════════════ */
.list-card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);overflow:visible;}

.list-toolbar{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.2rem;border-bottom:2px solid var(--navy);background:var(--navy);gap:1rem;flex-wrap:wrap;}
.list-title{font-size:13px;font-weight:bold;color:rgba(255,255,255,.9);text-transform:uppercase;letter-spacing:.1em;}
.list-count{font-size:11px;color:rgba(255,255,255,.5);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:2px 9px;font-weight:bold;}
.filter-group{display:flex;gap:2px;background:rgba(0,0,0,.2);padding:2px;border:1px solid rgba(255,255,255,.1);}
.filter-btn{padding:4px 11px;font-size:11px;font-weight:bold;font-family:var(--font);cursor:pointer;border:none;background:transparent;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
.filter-btn:hover{color:#fff;background:rgba(255,255,255,.1);}
.filter-active{background:var(--red) !important;color:#fff !important;}
.search-wrap{position:relative;}
.search-wrap input{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:.4rem .9rem .4rem 2.1rem;color:#fff;font-family:var(--font);font-size:12px;width:220px;outline:none;transition:border-color .15s,box-shadow .15s;}
.search-wrap input:focus{background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.5);}
.search-wrap input::placeholder{color:rgba(255,255,255,.4);}
.search-icon{position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.4);font-size:14px;pointer-events:none;}

.list-headers{
    display:grid;
    grid-template-columns: 56px 1fr 170px 160px 115px 170px 210px;
    background:#002244;
    border-bottom:2px solid var(--red);
}
.lh{padding:.5rem .85rem;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:rgba(255,255,255,.45);}

.member-row{
    display:grid;
    grid-template-columns: 56px 1fr 170px 160px 115px 170px 210px;
    align-items:stretch;
    border-bottom:1px solid var(--grey-mid);
    transition:background .1s;
    position:relative;
}
.member-row:hover{background:#f7f9fc;}
.member-row[data-status="suspended"] {
    background: #fef2f2;
    border-left: 4px solid var(--red);
    border-bottom-color: rgba(200,16,46,.15);
}
.member-row[data-status="suspended"]:hover { background: #fde4e4; }
.member-row[data-guest="1"] { background: #fffbf0; border-left: 3px solid #b45309; }
.member-row[data-guest="1"]:hover { background: #fff3d6; }
.member-row[data-guest="1"] .mc-av { border-right-color: rgba(180,83,9,.15); }
.member-row[data-guest="1"] .mc-identity,
.member-row[data-guest="1"] .mc-callsign,
.member-row[data-guest="1"] .mc-role,
.member-row[data-guest="1"] .mc-status,
.member-row[data-guest="1"] .mc-activity { border-right-color: rgba(180,83,9,.15); }
.member-row[data-test="1"] { background: #f0fdf4; border-left: 3px solid #16a34a; }
.member-row[data-test="1"]:hover { background: #dcfce7; }
.member-row[data-test="1"] .mc-av { border-right-color: rgba(22,163,74,.15); }
.member-row[data-test="1"] .mc-identity,
.member-row[data-test="1"] .mc-callsign,
.member-row[data-test="1"] .mc-role,
.member-row[data-test="1"] .mc-status,
.member-row[data-test="1"] .mc-activity { border-right-color: rgba(22,163,74,.15); }
.member-row[data-status="suspended"] .mc-av { border-right-color: rgba(200,16,46,.15); }
.member-row[data-status="suspended"] .mc-identity,
.member-row[data-status="suspended"] .mc-callsign,
.member-row[data-status="suspended"] .mc-role,
.member-row[data-status="suspended"] .mc-status,
.member-row[data-status="suspended"] .mc-activity { border-right-color: rgba(200,16,46,.15); }
.member-row[data-status="suspended"] .mc-actions { background: #fde4e4; }
.member-row[data-status="suspended"]:hover .mc-actions { background: #fcd4d4; }
.member-row[data-status="suspended"] .member-name { color: var(--red); }
.member-row[data-status="suspended"] .member-email { color: rgba(200,16,46,.5); }
.member-row:hover .mc-actions{background:#eef2f8;}
.member-row:last-child{border-bottom:none;}

.mc-av{display:flex;align-items:flex-start;justify-content:center;padding:.9rem .5rem .9rem .75rem;border-right:1px solid var(--grey-mid);}
.av-circle{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;color:#fff;flex-shrink:0;letter-spacing:-.01em;margin-top:2px;box-shadow:0 1px 4px rgba(0,0,0,.15);}
.av-regular{background:var(--navy);}
.av-officer{background:var(--navy-mid);}
.av-admin  {background:var(--red);}

.mc-identity{padding:.85rem .9rem .85rem .85rem;display:flex;flex-direction:column;justify-content:center;border-right:1px solid var(--grey-mid);}
.member-name{font-size:13.5px;font-weight:bold;color:var(--text);line-height:1.3;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;}
.member-email{font-size:11px;color:var(--text-muted);margin-top:3px;}
.pii-redacted{filter:blur(4px);transition:filter .2s;cursor:default;user-select:none;pointer-events:none;color:var(--text-muted);}
.member-badges{display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;align-items:center;}

.level-row{display:flex;align-items:center;gap:2px;}
.level-pip{width:13px;height:5px;border:1px solid;border-radius:1px;transition:background .1s;}
.level-label{font-size:10px;font-weight:700;color:var(--text-muted);margin-left:5px;letter-spacing:.04em;}

.tag{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:700;line-height:1;padding:3px 7px;border:1px solid;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;flex-shrink:0;}

.bi{font-size:11px;font-weight:700;padding:3px 7px;text-transform:uppercase;letter-spacing:.05em;display:inline-flex;align-items:center;line-height:1;border:1px solid;white-space:nowrap;}
.bi-admin  {background:var(--navy-faint);border-color:rgba(0,51,102,.25);color:var(--navy);}
.bi-reset  {background:#fef3c7;border-color:#fde68a;color:var(--amber);}
.bi-unverif{background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red);}
.bi-nopwd  {background:var(--grey);border-color:var(--grey-mid);color:var(--text-muted);}
.bi-new    {background:#e0f7fa;border-color:#4fc3f7;color:#0277bd;}
.bi-super    {background:#1e0040;border-color:rgba(91,33,182,.5);color:#c4b5fd;}
.bi-suspended { background:var(--red-faint); border-color:rgba(200,16,46,.35); color:var(--red); }

.mc-callsign{padding:.85rem .9rem;display:flex;flex-direction:column;justify-content:center;gap:5px;border-right:1px solid var(--grey-mid);}

.callsign-chip{display:inline-flex;align-items:center;line-height:1;font-size:11px;font-weight:700;font-family:monospace;padding:3px 9px;border:1px solid rgba(0,51,102,.3);background:var(--navy-faint);color:var(--navy);letter-spacing:.14em;white-space:nowrap;align-self:flex-start;}
.pending-chip-sm{display:inline-flex;align-items:center;line-height:1;font-size:11px;font-weight:700;padding:3px 7px;border:1px solid #fde68a;background:#fef3c7;color:var(--amber);letter-spacing:.05em;white-space:nowrap;align-self:flex-start;text-transform:uppercase;}
.no-callsign{font-size:11px;color:var(--grey-dark);font-style:italic;line-height:1.5;}

.lic-badge{display:inline-flex;align-items:center;line-height:1;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:3px 7px;border:1px solid;align-self:flex-start;white-space:nowrap;}
.lic-foundation  {background:var(--amber-bg);border-color:#f5c842;color:var(--amber);}
.lic-intermediate{background:var(--navy-faint);border-color:rgba(0,51,102,.3);color:var(--navy-mid);}
.lic-full        {background:var(--green-bg);border-color:#6bbf94;color:var(--green);}

.dmr-badge{display:inline-flex;align-items:center;gap:5px;line-height:1;font-size:11px;font-weight:700;letter-spacing:.04em;padding:3px 8px;border:1px solid rgba(0,64,128,.5);background:var(--navy);color:rgba(255,255,255,.9);align-self:flex-start;white-space:nowrap;}
.dmr-badge-digits{font-family:monospace;letter-spacing:.1em;}
.dmr-label{color:rgba(255,255,255,.4);font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;font-family:var(--font);}

/* ── DMR Live Activity (RadioID lookup) ── */
.dmr-live{display:flex;flex-direction:column;gap:2px;align-self:flex-start;}
.dmr-live-tg{
    display:inline-flex;align-items:center;gap:.3rem;line-height:1;
    font-size:10px;font-weight:700;letter-spacing:.04em;
    padding:2px 6px;border:1px solid rgba(91,33,182,.25);
    background:var(--purple-bg);color:var(--purple);white-space:nowrap;
    align-self:flex-start;
}
.dmr-live-heard{
    display:flex;align-items:center;gap:.3rem;
    font-size:10px;font-weight:700;color:var(--text-muted);line-height:1;
}
.dmr-live-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;flex-shrink:0;}
.dmr-live-dot.stale{background:var(--grey-dark);}
.dmr-live-loading{font-size:10px;color:var(--grey-dark);font-style:italic;}

.mc-role{padding:.85rem .9rem;display:flex;flex-direction:column;justify-content:center;gap:6px;border-right:1px solid var(--grey-mid);}
.role-pill{display:inline-flex;align-items:center;line-height:1;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:3px 8px;border:1px solid rgba(0,51,102,.25);background:var(--navy-faint);color:var(--navy-mid);align-self:flex-start;white-space:nowrap;}

.mc-status{padding:.85rem .9rem;display:flex;flex-direction:column;justify-content:center;border-right:1px solid var(--grey-mid);}
.status-block{display:inline-flex;align-items:center;gap:.55rem;}
.status-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.dot-active   {background:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.2);}
.dot-standby  {background:#d97706;box-shadow:0 0 0 3px rgba(217,119,6,.15);}
.dot-inactive {background:var(--grey-dark);}
.dot-suspended{background:var(--red);box-shadow:0 0 0 3px rgba(200,16,46,.15);}
.status-text{font-size:12px;font-weight:700;letter-spacing:.02em;}
.st-active   {color:#166534;}
.st-standby  {color:#92600a;}
.st-inactive {color:var(--text-muted);}
.st-suspended{color:var(--red);}

.mc-activity{padding:.85rem .9rem;display:flex;flex-direction:column;justify-content:center;gap:5px;border-right:1px solid var(--grey-mid);}
.act-chip{display:inline-flex;align-items:center;gap:3px;line-height:1;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:3px 7px;border:1px solid;align-self:flex-start;white-space:nowrap;}
.act-attended{background:var(--green-bg);border-color:#6bbf94;color:var(--green);}
.act-absent  {background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red);}
.act-events  {background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);}
.act-hours   {background:#f0f8ff;border-color:#90bcd8;color:#1a4f72;}

.mc-actions{padding:.7rem .8rem;display:flex;flex-direction:column;justify-content:center;gap:4px;background:#fafcff;transition:background .1s;}
.btn-a{font-size:11px;font-weight:bold;font-family:var(--font);padding:5px 10px;min-height:27px;border:1px solid;cursor:pointer;background:transparent;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:4px;transition:all .12s;white-space:nowrap;text-transform:uppercase;letter-spacing:.05em;line-height:1;width:100%;}
.btn-edit      {color:var(--navy);border-color:rgba(0,51,102,.25);background:var(--white);}
.btn-edit:hover{background:var(--navy-faint);border-color:var(--navy);}
.btn-promote      {color:var(--green);border-color:#b8ddc9;background:var(--white);}
.btn-promote:hover{background:var(--green-bg);border-color:var(--green);}
.btn-impersonate      {color:var(--orange);border-color:rgba(194,65,12,.35);background:var(--orange-bg);}
.btn-impersonate:hover{background:#fed7aa;border-color:var(--orange);color:#7c2d00;}
.btn-delete      {color:var(--red);border-color:rgba(200,16,46,.3);background:var(--white);}
.btn-delete:hover{background:var(--red-faint);border-color:var(--red);}

.btn-view   {font-size:11px;font-weight:bold;font-family:var(--font);color:var(--text-muted);text-decoration:none;padding:3px 10px;border:1px solid var(--grey-mid);transition:all .12s;text-transform:uppercase;letter-spacing:.04em;display:inline-flex;}
.btn-view:hover{border-color:var(--navy);color:var(--navy);}
.btn-approve{font-size:11px;font-weight:bold;font-family:var(--font);cursor:pointer;padding:3px 10px;border:1px solid #b8ddc9;background:var(--green-bg);color:var(--green);transition:all .12s;text-transform:uppercase;letter-spacing:.04em;}
.btn-approve:hover{background:#d6ede3;border-color:var(--green);}
.btn-reject {font-size:11px;font-weight:bold;font-family:var(--font);cursor:pointer;padding:3px 10px;border:1px solid rgba(200,16,46,.3);background:var(--red-faint);color:var(--red);transition:all .12s;text-transform:uppercase;letter-spacing:.04em;}
.btn-reject:hover{background:rgba(200,16,46,.12);border-color:var(--red);}

.btn-register{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.2rem;background:var(--red);border:1px solid var(--red);color:#fff;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.07em;transition:all .12s;text-decoration:none;}
.btn-register:hover{background:#a50e26;border-color:#a50e26;}
.btn-register-outline{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.2rem;background:transparent;border:1px solid var(--navy);color:var(--navy);font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.07em;transition:all .12s;}
.btn-register-outline:hover{background:var(--navy-faint);}

.empty-state{padding:3.5rem 1rem;text-align:center;}
.empty-icon{font-size:2rem;opacity:.2;margin-bottom:.75rem;}
.empty-text{font-size:13px;color:var(--text-muted);}
.pagination-wrap{padding:.8rem 1.2rem;border-top:1px solid var(--grey-mid);background:var(--grey);}

.impersonate-bar{background:#7c2d00;border-bottom:3px solid #ea580c;display:flex;align-items:center;justify-content:space-between;padding:.55rem 1.5rem;gap:1rem;flex-wrap:wrap;box-shadow:0 3px 12px rgba(0,0,0,.4);}
.impersonate-bar-left{display:flex;align-items:center;gap:.75rem;}
.impersonate-bar-icon{width:28px;height:28px;background:#ea580c;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.impersonate-bar-text{font-size:13px;font-weight:bold;color:#fed7aa;letter-spacing:.02em;}
.impersonate-bar-text em{color:#fdba74;font-style:normal;}
.impersonate-bar-sub{font-size:11px;color:#fb923c;margin-top:1px;font-weight:normal;}
.btn-exit-impersonate{padding:.4rem 1.1rem;background:#ea580c;border:1px solid #c2410c;color:#fff;font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.08em;transition:all .12s;white-space:nowrap;flex-shrink:0;}
.btn-exit-impersonate:hover{background:#c2410c;border-color:#9a3412;}

/* ── DRAWER ── */
.drawer-overlay{position:fixed;inset:0;z-index:500;background:rgba(0,10,30,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:opacity .25s;}
.drawer-overlay.open{opacity:1;pointer-events:all;}
.drawer{position:fixed;top:0;right:0;bottom:0;z-index:501;width:680px;max-width:100vw;background:var(--white);display:flex;flex-direction:column;transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-xl);}
.drawer.open{transform:translateX(0);}
.drawer-header{background:var(--navy);border-bottom:3px solid var(--red);padding:1.1rem 1.4rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-shrink:0;}
.drawer-header-left{display:flex;align-items:center;gap:.9rem;}
.drawer-icon{background:var(--red);width:38px;height:38px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.drawer-title{font-size:16px;font-weight:bold;color:#fff;letter-spacing:.02em;}
.drawer-sub{font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;text-transform:uppercase;letter-spacing:.06em;}
.drawer-close{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:18px;cursor:pointer;transition:all .12s;flex-shrink:0;font-family:var(--font);line-height:1;}
.drawer-close:hover{background:rgba(255,255,255,.2);}
.drawer-tabs{display:flex;background:var(--grey);border-bottom:1px solid var(--grey-mid);flex-shrink:0;}
.dtab{padding:.65rem 1.2rem;font-size:12px;font-weight:bold;cursor:pointer;font-family:var(--font);text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:2px solid transparent;background:transparent;border-top:none;border-left:none;border-right:none;transition:all .12s;}
.dtab:hover{color:var(--navy);}
.dtab.active{color:var(--navy);border-bottom-color:var(--red);background:var(--white);}
#registerForm{flex:1;display:flex;flex-direction:column;overflow:hidden;min-height:0;}
.drawer-body{flex:1;overflow-y:auto;padding:1.4rem;min-height:0;}
.dsec{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--text-muted);padding-bottom:.4rem;margin-bottom:.9rem;border-bottom:1px solid var(--grey-mid);margin-top:1.4rem;}
.dsec:first-child{margin-top:0;}
.dform-grid{display:grid;gap:.75rem;}
.dform-2{grid-template-columns:1fr 1fr;}
.dform-3{grid-template-columns:1fr 1fr 1fr;}
@media(max-width:600px){.dform-2,.dform-3{grid-template-columns:1fr;}}
.dform-field{display:flex;flex-direction:column;gap:.3rem;}
.dform-field.span2{grid-column:span 2;}
.dform-field.span3{grid-column:1 / -1;}
.dform-label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;}
.dform-label .req{color:var(--red);font-size:13px;line-height:1;}
.dform-label .opt{font-size:10px;color:var(--grey-dark);font-weight:normal;text-transform:none;letter-spacing:0;}
.dform-input,.dform-select,.dform-textarea{background:var(--white);border:1px solid var(--grey-mid);padding:.5rem .75rem;color:var(--text);font-family:var(--font);font-size:13px;outline:none;width:100%;transition:border-color .15s,box-shadow .15s;}
.dform-input:focus,.dform-select:focus,.dform-textarea:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,51,102,.08);}
.dform-input.is-error{border-color:var(--red);}
.dform-hint{font-size:11px;color:var(--text-muted);margin-top:1px;}
.dform-error{font-size:11px;color:var(--red);font-weight:bold;margin-top:2px;}
.pwd-wrap{position:relative;}
.pwd-toggle{position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:13px;color:var(--text-muted);padding:0;font-family:var(--font);}
.pwd-strength{display:flex;gap:3px;margin-top:5px;}
.pwd-seg{height:4px;flex:1;background:var(--grey-mid);transition:background .2s;}
.pwd-label{font-size:10px;font-weight:bold;margin-top:3px;}
.toggle-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.3rem;}
.toggle-item{display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border:1px solid var(--grey-mid);background:var(--grey);cursor:pointer;transition:all .12s;user-select:none;}
.toggle-item:hover{border-color:var(--navy);background:var(--navy-faint);}
.toggle-item input[type="checkbox"]{width:15px;height:15px;accent-color:var(--navy);flex-shrink:0;cursor:pointer;}
.toggle-item-label{font-size:12px;font-weight:bold;color:var(--text-mid);line-height:1.3;}
.toggle-item-sub{font-size:10px;color:var(--text-muted);margin-top:1px;}
.autogen-row{display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;}
.autogen-btn{font-size:11px;font-weight:bold;font-family:var(--font);padding:4px 10px;border:1px solid var(--grey-mid);background:var(--grey);color:var(--text-muted);cursor:pointer;text-transform:uppercase;letter-spacing:.04em;transition:all .12s;white-space:nowrap;}
.autogen-btn:hover{border-color:var(--navy);color:var(--navy);background:var(--navy-faint);}
.drawer-footer{padding:1rem 1.4rem;border-top:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;flex-shrink:0;}
.drawer-footer-note{font-size:11px;color:var(--text-muted);}
.drawer-footer-btns{display:flex;gap:.6rem;}
.btn-cancel{padding:.5rem 1.1rem;border:1px solid var(--grey-mid);background:var(--white);color:var(--text-muted);font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
.btn-cancel:hover{border-color:var(--navy);color:var(--navy);}
.btn-submit-drawer{padding:.5rem 1.4rem;background:var(--navy);border:1px solid var(--navy);color:#fff;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;display:flex;align-items:center;gap:.4rem;}
.btn-submit-drawer:hover{background:var(--navy-mid);}
.btn-submit-drawer:disabled{opacity:.5;cursor:not-allowed;}
.drawer-progress{display:flex;align-items:center;gap:.3rem;padding:.6rem 1.4rem;border-bottom:1px solid var(--grey-mid);background:var(--white);flex-shrink:0;}
.dp-step{display:flex;align-items:center;gap:.35rem;font-size:11px;font-weight:bold;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;}
.dp-step.done{color:var(--green);}
.dp-step.active{color:var(--navy);}
.dp-dot{width:20px;height:20px;border:2px solid;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.dp-step.done   .dp-dot{border-color:var(--green);background:var(--green);color:#fff;}
.dp-step        .dp-dot{border-color:var(--grey-mid);background:var(--grey);color:var(--text-muted);}
.dp-step.active .dp-dot{border-color:var(--navy);background:var(--navy);color:#fff;}
.dp-line{flex:1;height:1px;background:var(--grey-mid);}

@keyframes fadeUp{from{opacity:0;transform:translateY(5px);}to{opacity:1;transform:none;}}
.fade-in{animation:fadeUp .3s ease both;}
.stagger>*{animation:fadeUp .3s ease both;}
.stagger>*:nth-child(1){animation-delay:.05s;}
.stagger>*:nth-child(2){animation-delay:.1s;}
.stagger>*:nth-child(3){animation-delay:.15s;}
.stagger>*:nth-child(4){animation-delay:.2s;}

@media(max-width:920px){
    .list-headers{display:none;}
    .member-row{display:block;border-bottom:1px solid var(--grey-mid);padding:.9rem 1rem;}
    .mc-av,.mc-identity,.mc-callsign,.mc-role,.mc-status,.mc-activity,.mc-actions{display:block;padding:0;border:none;background:transparent;}
    .mc-av{display:none;}
    .mob-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.5rem;}
    .mob-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;margin-bottom:.5rem;}
    .mob-actions{display:flex;gap:.4rem;flex-wrap:wrap;padding-top:.65rem;border-top:1px solid var(--grey-mid);margin-top:.5rem;}
    .mob-actions .btn-a{flex:1;justify-content:center;min-width:70px;width:auto;}
    .mc-desktop{display:none !important;}
}
@media(min-width:921px){
    .mob-top,.mob-meta,.mob-actions{display:none;}
}
@media(max-width:640px){
    .list-toolbar{flex-direction:column;align-items:stretch;}
    .search-wrap input{width:100%;}
    .search-wrap{flex:1;}
    .stat-grid-4{grid-template-columns:1fr 1fr;}
    .stat-grid-3{grid-template-columns:1fr;}
    .page-band-inner{flex-direction:column;align-items:flex-start;}
    .filter-group{flex-wrap:wrap;}
}
</style>

@php
    use Carbon\Carbon;
    $now        = Carbon::now();
    $yearStart  = $now->month >= 9
        ? Carbon::create($now->year, 9, 1)
        : Carbon::create($now->year - 1, 9, 1);
    $yearEnd    = $yearStart->copy()->addYear()->subDay();
    $yearLabel  = $yearStart->format('M Y') . ' – ' . $yearEnd->format('M Y');

    $allUsers   = \App\Models\User::role(['admin','committee','member','super-admin'])->get();
    $totalActiveThisYear = $allUsers->where('attended_event_this_year', true)->count();
    $totalVolHours       = $allUsers->sum('volunteering_hours_this_year');
    $totalAttendances    = $allUsers->sum('events_attended_this_year');
    $roles               = \App\Models\Role::orderBy('name')->pluck('name')->toArray();
    $isImpersonating     = (bool) session('original_admin_id');
@endphp

{{-- ── HEADER ── --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo-block"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Members &amp; Operators</div>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Back to admin</a>
    </div>
</header>

{{-- ── IMPERSONATION WARNING BAR ── --}}
@if ($isImpersonating)
<div class="impersonate-bar" role="alert">
    <div class="impersonate-bar-left">
        <div class="impersonate-bar-icon">👤</div>
        <div>
            <div class="impersonate-bar-text">
                ⚠ Admin impersonation active — you are logged in as
                <em>{{ auth()->user()->name }} ({{ auth()->user()->email }})</em>
            </div>
            <div class="impersonate-bar-sub">Actions taken now affect this member's account. Exit to return to your admin session.</div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.impersonate.exit') }}">
        @csrf
        <button type="submit" class="btn-exit-impersonate">✕ Exit impersonation</button>
    </form>
</div>
@endif

{{-- ── PAGE BAND ── --}}
<div class="page-band fade-in">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Admin Panel</div>
            <h1 class="page-title">Manage Members</h1>
            <p class="page-desc">View, edit and manage all members and operators — callsigns, roles, levels, status and credentials.</p>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <div class="page-datestamp">{{ now()->format('D d M Y · H:i') }}</div>
            @if (!$isImpersonating)
            <button type="button" class="btn-register" onclick="openDrawer()">+ Register Member</button>
            @endif
        </div>
    </div>
</div>

<div class="wrap">

    @if (session('status'))
        <div class="alert-success fade-in">✓ {{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-success fade-in" style="background:var(--red-faint);border-color:rgba(200,16,46,.3);border-left-color:var(--red);color:var(--red);">
            ✗ Please fix the errors in the registration form.
        </div>
    @endif

    {{-- ── STATS: MEMBERSHIP ── --}}
    <div class="stat-grid stat-grid-4 stagger">
        <div class="stat-card">
            <div class="stat-label">Full Members</div>
            <div class="stat-value">{{ $memberCount }}</div>
            <div class="stat-sub">admin · committee · member</div>
        </div>
        <div class="stat-card" style="border-top-color:#7c3aed;">
            <div class="stat-label">Temporary</div>
            <div class="stat-value" style="color:#7c3aed;">{{ $tempGuestCount + $tempAdminCount }}</div>
            <div class="stat-sub">
                @if($tempGuestCount)<span style="display:inline-flex;align-items:center;gap:.25rem;margin-right:.5rem;"><span style="width:7px;height:7px;border-radius:50%;background:#7c3aed;display:inline-block;"></span>{{ $tempGuestCount }} guest{{ $tempGuestCount !== 1 ? 's' : '' }}</span>@endif
                @if($tempAdminCount)<span style="display:inline-flex;align-items:center;gap:.25rem;"><span style="width:7px;height:7px;border-radius:50%;background:#a855f7;display:inline-block;"></span>{{ $tempAdminCount }} admin{{ $tempAdminCount !== 1 ? 's' : '' }}</span>@endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">With callsign</div>
            <div class="stat-value">{{ \App\Models\User::role(['admin','committee','member'])->whereNotNull('callsign')->count() }}</div>
            <div class="stat-sub">Licensed operators</div>
        </div>
        <div class="stat-card sc-red">
            <div class="stat-label">Pending registration</div>
            <div class="stat-value">{{ \App\Models\User::where('registration_pending', true)->count() }}</div>
            <div class="stat-sub">Awaiting approval</div>
        </div>
    </div>

    {{-- ── STATS: ANNUAL ACTIVITY ── --}}
    <div class="stat-grid stat-grid-3 stagger">
        <div class="stat-card">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Active this year</div>
            <div class="stat-value">{{ $totalActiveThisYear }}</div>
            <div class="stat-sub">Members attended ≥1 event</div>
        </div>
        <div class="stat-card">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Total vol. hours</div>
            <div class="stat-value">{{ number_format($totalVolHours, 1) }}</div>
            <div class="stat-sub">Across all members</div>
        </div>
        <div class="stat-card">
            <div class="stat-year-badge">{{ $yearLabel }}</div>
            <div class="stat-label">Total attendances</div>
            <div class="stat-value">{{ $totalAttendances }}</div>
            <div class="stat-sub">Individual event check-ins</div>
        </div>
    </div>

    {{-- ── PENDING REGISTRATIONS ── --}}
    @php $pendingRegs = \App\Models\User::where('registration_pending', true)->orderBy('created_at')->get(); @endphp
    @if ($pendingRegs->count())
    <div class="pending-panel fade-in" style="border-left-color:var(--red);border-color:rgba(200,16,46,.3);">
        <div class="pending-head" style="background:var(--red-faint);border-bottom-color:rgba(200,16,46,.15);">
            <div class="pending-head-icon" style="background:var(--red-faint);border-color:rgba(200,16,46,.3);">⏳</div>
            <div>
                <div class="pending-title" style="color:var(--red);">New Registrations — Awaiting Approval</div>
                <div class="pending-meta">{{ $pendingRegs->count() }} {{ Str::plural('account', $pendingRegs->count()) }} waiting for a Group Controller to approve.</div>
            </div>
        </div>
        <div class="pending-body">
            @foreach ($pendingRegs as $reg)
            <div class="pending-row">
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                    <span class="pname">{{ $reg->name }}</span>
                    <span class="pid">{{ $reg->email }}</span>
                    <span class="pcall-new" style="color:var(--navy);background:var(--navy-faint);border-color:rgba(0,51,102,.2);">
                        📡 {{ $reg->callsign ?? '—' }}
                    </span>
                    <span style="font-size:11px;color:var(--text-muted);">Registered {{ $reg->created_at->diffForHumans() }}</span>
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <a href="{{ route('admin.users.edit', $reg->id) }}" class="btn-view">View</a>
                    <form method="POST" action="{{ route('admin.users.registration.approve', $reg->id) }}" style="display:contents;">
                        @csrf <button type="submit" class="btn-approve">✓ Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.registration.reject', $reg->id) }}" style="display:contents;"
                          onsubmit="return confirm('Permanently delete the registration for {{ addslashes($reg->name) }}?')">
                        @csrf <button type="submit" class="btn-reject">✕ Reject</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── PENDING CALLSIGN APPROVALS ── --}}
    @php $pendingUsers = $users->getCollection()->whereNotNull('pending_callsign'); @endphp
    @if ($pendingUsers->count())
    <div class="pending-panel fade-in">
        <div class="pending-head">
            <div class="pending-head-icon">⚑</div>
            <div>
                <div class="pending-title">Pending Callsign Approvals</div>
                <div class="pending-meta">{{ $pendingUsers->count() }} {{ Str::plural('request', $pendingUsers->count()) }} awaiting review</div>
            </div>
        </div>
        <div class="pending-body">
            @foreach ($pendingUsers as $pu)
            <div class="pending-row">
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                    <span class="pname">{{ $pu->name }}</span>
                    <span class="pid">ID #{{ $pu->id }}</span>
                    <div style="display:inline-flex;align-items:center;">
                        @if ($pu->callsign)
                            <span class="pcall-old">{{ strtoupper($pu->callsign) }}</span>
                        @else
                            <span style="font-size:11px;color:var(--text-muted);">No callsign</span>
                        @endif
                        <span class="parrow"> → </span>
                        <span class="pcall-new">{{ strtoupper($pu->pending_callsign) }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <a href="{{ route('admin.users.edit', $pu->id) }}" class="btn-view">View</a>
                    <form method="POST" action="{{ route('admin.callsign.approve', $pu->id) }}" style="display:contents;">
                        @csrf <button type="submit" class="btn-approve">✓ Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.callsign.reject', $pu->id) }}" style="display:contents;">
                        @csrf <button type="submit" class="btn-reject">✕ Reject</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         MEMBER LIST
    ══════════════════════════════════════════ --}}
    @if ($users->isEmpty())
        <div class="list-card fade-in">
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <div class="empty-text">No members found.</div>
            </div>
        </div>
    @else
    <div class="list-card fade-in" id="memberList">

        <div class="list-toolbar">
            <div style="display:flex;align-items:center;gap:.65rem;">
                <span class="list-title">Member List</span>
                <span class="list-count" id="visibleCount">{{ $memberCount }} members</span>
            </div>
            <div style="display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;">
                <div class="filter-group">
                    @foreach (['All','Active','Standby','Inactive','No role','Attended','Not attended'] as $f)
                    <button onclick="filterStatus('{{ $f }}')" data-filter="{{ $f }}"
                            class="filter-btn {{ $f === 'All' ? 'filter-active' : '' }}">{{ $f }}</button>
                    @endforeach
                </div>
                <div class="search-wrap">
                    <span class="search-icon">⌕</span>
                    <input type="text" id="searchInput" placeholder="Search name, callsign, DMR…" oninput="applyFilters()">
                </div>
            </div>
        </div>

        <div class="list-headers">
            <div class="lh"></div>
            <div class="lh">Member</div>
            <div class="lh">Callsign &amp; Licence</div>
            <div class="lh">Role &amp; Level</div>
            <div class="lh">Status</div>
            <div class="lh">Activity <span style="font-size:9px;opacity:.5;font-weight:normal;letter-spacing:.01em;text-transform:none;">{{ $yearLabel }}</span></div>
            <div class="lh">Actions</div>
        </div>

     @foreach ($users as $user)
    @php
        $dotClass  = match($user->status ?? '') {
            'Active'    => 'dot-active',
            'Standby'   => 'dot-standby',
            'Inactive'  => 'dot-inactive',
            'Suspended' => 'dot-suspended',
            default     => 'dot-inactive',
        };
        $textClass = match($user->status ?? '') {
            'Active'    => 'st-active',
            'Standby'   => 'st-standby',
            'Inactive'  => 'st-inactive',
            'Suspended' => 'st-suspended',
            default     => 'st-inactive',
        };
        $maxLevel  = 5;
        $attended  = (bool)($user->attended_event_this_year ?? false);
        $licClass  = match($user->licence_class ?? '') {
            'Foundation'   => 'lic-foundation',
            'Intermediate' => 'lic-intermediate',
            'Full'         => 'lic-full',
            default        => null,
        };
        $nameParts = explode(' ', trim($user->name));
        $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
        $avClass        = $user->is_admin ? 'av-admin' : ($user->role ? 'av-officer' : 'av-regular');
        $isSuspended    = !is_null($user->suspended_at);
        $canEdit        = $user->canBeManagedBy(auth()->user());
        $canImpersonate = !$isImpersonating && $user->id !== auth()->id()
            && (
                auth()->id() === 2
                    ? !$isSuspended
                    : (!$user->is_admin && !$isSuspended && $canEdit)
            );

        $suspensionLog    = $suspensionLogs[$user->id] ?? null;
        $suspendedTooltip = $isSuspended
            ? 'Suspended ' . ($suspensionLog?->created_at?->format('d M Y H:i') ?? 'unknown date')
              . ($suspensionLog?->admin_id
                    ? ' by ' . (\App\Models\User::find($suspensionLog->admin_id)?->name ?? 'unknown')
                    : '')
            : '';
    @endphp
    
<div class="member-row"
     data-guest="{{ $user->guest_expires_at || $user->hasRole('temporary_guest') ? '1' : '0' }}"
     data-test="{{ $user->hasRole('test_user') ? '1' : '0' }}"
     data-status="{{ $isSuspended ? 'suspended' : strtolower($user->status ?? '') }}"
     data-role="{{ strtolower($user->role ?? '') }}"
     data-attended="{{ $attended ? '1' : '0' }}"
     data-search="{{ auth()->user()->isTemporaryAdmin() && !($user->guest_expires_at || $user->hasRole('temporary_guest') || $user->hasRole('temporary_admin')) ? strtolower($user->name.' '.($user->callsign ?? '').' '.($user->role ?? '')) : strtolower($user->name.' '.$user->email.' '.($user->callsign ?? '').' '.($user->role ?? '').' '.($user->status ?? '').' '.($user->licence_class ?? '').' '.($user->dmr_id ?? '')) }}">

    {{-- ── MOBILE ── --}}
    <div class="mob-top">
        <div style="flex:1;min-width:0;">
            <div class="member-name">
                {{ $user->name }}
                @if ($user->is_admin)    <span class="bi bi-admin">⚡ Admin</span> @endif
                @if ($user->is_super_admin) <span class="bi bi-super">★ Super</span> @endif
                @if ($user->operator_title === 'Group Controller') <span class="bi" style="background:#003366;color:#fff;">🎖 GC</span> @endif
                @if ($user->operator_title === 'Deputy Controller') <span class="bi" style="background:#1a3a5c;color:#fff;">🎖 DGC</span> @endif
                @if ($user->force_password_reset) <span class="bi bi-reset">⚑ Reset</span> @endif
                @if (!$user->email_verified_at)   <span class="bi bi-unverif">✗ Unverif.</span> @endif
                @if ($user->created_at->gt(Carbon::now()->subDays(3))) <span class="bi bi-new">New</span> @endif
                @if ($isSuspended)
    <span class="bi bi-suspended" title="{{ $suspendedTooltip }}">⊘ Suspended</span>
@endif
            </div>
            <div class="member-email">
                @if(auth()->user()->isTemporaryAdmin() && !($user->guest_expires_at || $user->hasRole("temporary_guest") || $user->hasRole("temporary_admin")))
                    <span class="pii-redacted">{{ $user->email }}</span>
                @else
                    {{ $user->email }}
                @endif
            </div>
        </div>
        @if ($user->status)
        <div class="status-block" style="flex-shrink:0;margin-top:2px;">
            <span class="status-dot {{ $dotClass }}"></span>
            <span class="status-text {{ $textClass }}">{{ $user->status }}</span>
        </div>
        @endif
    </div>
    <div class="mob-meta">
        @if ($user->callsign)
            <span class="callsign-chip">
                @if($user->piiVisible()) {{ strtoupper($user->callsign) }} @else <span class="pii-redacted">{{ strtoupper($user->callsign) }}</span> @endif
            </span>
        @endif
        @if ($user->pending_callsign) <span class="pending-chip-sm">⏳ {{ strtoupper($user->pending_callsign) }}</span> @endif
        @if ($licClass)               <span class="lic-badge {{ $licClass }}">{{ $user->licence_class }}</span> @endif
        @if ($user->dmr_id)
            <span class="dmr-badge"><span class="dmr-label">DMR</span><span class="dmr-badge-digits">@if($user->piiVisible()){{ $user->dmr_id }}@else<span class="pii-redacted">{{ $user->dmr_id }}</span>@endif</span></span>
            @if ($user->callsign)
                <span class="dmr-live" data-callsign="{{ strtoupper($user->callsign) }}">
                    <span class="dmr-live-loading">…</span>
                </span>
            @endif
        @endif
        @if ($user->role) <span class="role-pill">{{ ucwords(str_replace(['-','_'],' ',$user->role ?? '')) }}</span> @endif
        @if ($user->hasRole("temporary_guest") || $user->guest_expires_at)
            @if($user->guest_expires_at && $user->guest_expires_at->isPast())
                <span class="bi" style="background:rgba(200,16,46,.08);border:1px solid rgba(200,16,46,.3);color:#C8102E;font-size:10px;font-weight:700;padding:2px 7px;letter-spacing:.05em;text-transform:uppercase;">⏱ Guest Expired</span>
            @else
                <span class="bi" style="background:rgba(180,83,9,.12);border:1px solid rgba(180,83,9,.4);color:#b45309;font-size:10px;font-weight:700;padding:2px 7px;letter-spacing:.05em;text-transform:uppercase;">⏱ Temp Guest</span>
            @endif
            @if ($user->guest_expires_at)
                <span class="bi" style="background:rgba(180,83,9,.06);border:1px solid rgba(180,83,9,.25);color:#b45309;font-size:10px;padding:2px 7px;font-family:monospace;">{{ $user->guest_expires_at->isPast() ? "Expired " . $user->guest_expires_at->diffForHumans() : $user->guest_expires_at->diffForHumans() }}</span>
            @endif
        @endif
        @if ($attended) <span class="act-chip act-attended">✓ Attended</span>
        @else           <span class="act-chip act-absent">✗ Not attended</span> @endif
    </div>

    {{-- ── MOBILE ACTIONS ── --}}
    <div class="mob-actions">
        @if ($canEdit)
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-a btn-edit">✏ Edit</a>
            <form method="POST" action="{{ route('admin.users.promote', $user->id) }}" style="display:contents;">
                @csrf <button type="submit" class="btn-a btn-promote">↑ Promote</button>
            </form>
        @endif
        @if ($canImpersonate)
            <form method="POST" action="{{ route('admin.impersonate', $user->id) }}" style="display:contents;"
                  onsubmit="return confirm('Log in as {{ addslashes($user->name) }}?');">
                @csrf <button type="submit" class="btn-a btn-impersonate">👤 Login as</button>
            </form>
        @endif
        @if ($canEdit)
            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:contents;"
                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-a btn-delete">✕ Delete</button>
            </form>
        @endif
    </div>

            {{-- ── DESKTOP: Avatar ── --}}
            <div class="mc-av mc-desktop">
                @if($user->guest_expires_at || $user->hasRole("temporary_guest"))
                    <img src="{{ Storage::url('avatars/TempAvatar.png') }}"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;margin-top:2px;border:2px solid rgba(180,83,9,.4);box-shadow:0 1px 4px rgba(0,0,0,.15);flex-shrink:0;" alt="Temporary Guest" title="Temporary Guest">
                @elseif($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;margin-top:2px;border:2px solid rgba(0,51,102,.15);box-shadow:0 1px 4px rgba(0,0,0,.15);flex-shrink:0;" alt="">
                @else
                    <div class="av-circle {{ $avClass }}">{{ $initials }}</div>
                @endif
            </div>

            {{-- ── DESKTOP: Identity ── --}}
            <div class="mc-identity mc-desktop">
                <div class="member-name">{{ $user->name }}</div>
                <div class="member-email">
                    @if(auth()->user()->isTemporaryAdmin() && !($user->guest_expires_at || $user->hasRole("temporary_guest") || $user->hasRole("temporary_admin")))
                        <span class="pii-redacted">{{ $user->email }}</span>
                    @else
                        {{ $user->email }}
                    @endif
                </div>
                <div class="member-badges">
                    @if ($user->is_admin)           <span class="bi bi-admin">⚡ Admin</span>     @endif
                    @if ($user->is_super_admin) <span class="bi bi-super">★ Super</span> @endif
                    @if ($user->operator_title === 'Group Controller') <span class="bi" style="background:#003366;color:#fff;">🎖 GC</span> @endif
                    @if ($user->operator_title === 'Deputy Controller') <span class="bi" style="background:#1a3a5c;color:#fff;">🎖 DGC</span> @endif
                    @if ($user->force_password_reset)<span class="bi bi-reset">⚑ Reset</span>     @endif
                    @if (!$user->email_verified_at) <span class="bi bi-unverif">✗ Unverif.</span> @endif
                    @if (empty($user->password))    <span class="bi bi-nopwd">No pwd</span>       @endif
                    @if ($user->created_at->gt(Carbon::now()->subDays(3))) <span class="bi bi-new">New</span> @endif
                    @if ($isSuspended)
    <span class="bi bi-suspended" title="{{ $suspendedTooltip }}">⊘ Suspended</span>
@endif
                    @if ($user->hasRole("temporary_guest") || $user->guest_expires_at)
                        @if($user->guest_expires_at && $user->guest_expires_at->isPast())
                            <span class="bi" style="background:rgba(200,16,46,.08);border:1px solid rgba(200,16,46,.3);color:#C8102E;font-size:10px;font-weight:700;padding:2px 7px;letter-spacing:.05em;text-transform:uppercase;">⏱ Guest Expired</span>
                        @else
                            <span class="bi" style="background:rgba(180,83,9,.12);border:1px solid rgba(180,83,9,.4);color:#b45309;font-size:10px;font-weight:700;padding:2px 7px;letter-spacing:.05em;text-transform:uppercase;">⏱ Temp Guest</span>
                        @endif
                        @if ($user->guest_expires_at)
                            <span class="bi" style="background:rgba(180,83,9,.06);border:1px solid rgba(180,83,9,.25);color:#b45309;font-size:10px;padding:2px 7px;font-family:monospace;">
                                {{ $user->guest_expires_at->isPast() ? "Expired " . $user->guest_expires_at->diffForHumans() : "Exp " . $user->guest_expires_at->diffForHumans() }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- ── DESKTOP: Callsign + Licence + DMR ── --}}
            <div class="mc-callsign mc-desktop">
                @if ($user->callsign)
                    <span class="callsign-chip">
                        @if($user->piiVisible()) {{ strtoupper($user->callsign) }} @else <span class="pii-redacted">{{ strtoupper($user->callsign) }}</span> @endif
                    </span>
                @else
                    <span class="no-callsign">— None</span>
                @endif
                @if ($user->pending_callsign)
                    <span class="pending-chip-sm">⏳ {{ strtoupper($user->pending_callsign) }}</span>
                @endif
                @if ($licClass)
                    <span class="lic-badge {{ $licClass }}">{{ $user->licence_class }}</span>
                @endif
                @if ($user->dmr_id)
                    <span class="dmr-badge">
                        <span class="dmr-label">DMR</span>
                        <span class="dmr-badge-digits">@if($user->piiVisible()){{ $user->dmr_id }}@else<span class="pii-redacted">{{ $user->dmr_id }}</span>@endif</span>
                    </span>
                    {{-- Live RadioID activity — populated by JS --}}
                    @if ($user->callsign)
                        <span class="dmr-live" data-callsign="{{ strtoupper($user->callsign) }}">
                            <span class="dmr-live-loading">…</span>
                        </span>
                    @endif
                @endif
            </div>

            {{-- ── DESKTOP: Role & Level ── --}}
            <div class="mc-role mc-desktop">
                @if ($user->role)
                    <span class="role-pill">{{ ucwords(str_replace(['-','_'],' ',$user->role ?? '')) }}</span>
                    @if ($user->level !== null)
                        <div class="level-row" title="Level {{ $user->level }} / {{ $maxLevel }}">
                            @for ($i = 1; $i <= $maxLevel; $i++)
                                <div class="level-pip" style="background:{{ $i <= $user->level ? 'var(--navy)' : 'var(--grey-mid)' }};border-color:{{ $i <= $user->level ? 'rgba(0,51,102,.4)' : 'var(--grey-mid)' }};"></div>
                            @endfor
                            <span class="level-label">L{{ $user->level }}</span>
                        </div>
                    @endif
                @else
                    <span style="font-size:12px;color:var(--grey-dark);font-style:italic;">No role</span>
                @endif
            </div>

            {{-- ── DESKTOP: Status ── --}}
            <div class="mc-status mc-desktop">
                @if ($user->status)
                    <div class="status-block">
                        <span class="status-dot {{ $dotClass }}"></span>
                        <span class="status-text {{ $textClass }}">{{ $user->status }}</span>
                    </div>
                @else
                    <span style="font-size:12px;color:var(--grey-dark);">—</span>
                @endif
            </div>

            {{-- ── DESKTOP: Activity ── --}}
            <div class="mc-activity mc-desktop">
                @if ($attended) <span class="act-chip act-attended">✓ Attended</span>
                @else           <span class="act-chip act-absent">✗ None yet</span> @endif
                @if (($user->events_attended_this_year ?? 0) > 0)
                    <span class="act-chip act-events">{{ $user->events_attended_this_year }} {{ Str::plural('event', $user->events_attended_this_year) }}</span>
                @endif
                @if (($user->volunteering_hours_this_year ?? 0) > 0)
                    <span class="act-chip act-hours">{{ number_format($user->volunteering_hours_this_year, 1) }}h</span>
                @endif
            </div>

          {{-- ── DESKTOP: Actions ── --}}
<div class="mc-actions mc-desktop">
@if ($user->is_super_admin && $user->id !== auth()->id() && auth()->id() !== 2)
    <span style="font-size:11px;color:var(--text-muted);font-style:italic;padding:.4rem .5rem;line-height:1.4;">★ Super Admin — protected</span>
@else
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-a btn-edit">✏&nbsp;Edit</a>
        <form method="POST" action="{{ route('admin.users.promote', $user->id) }}" style="display:contents;">
            @csrf
            <button type="submit" class="btn-a btn-promote">↑&nbsp;Promote</button>
        </form>
        @if ($canImpersonate)
        <form method="POST" action="{{ route('admin.impersonate', $user->id) }}" style="display:contents;"
              onsubmit="return confirm('You will be logged in as {{ addslashes($user->name) }} until you exit. Continue?');">
            @csrf
            <button type="submit" class="btn-a btn-impersonate">👤&nbsp;Login as</button>
        </form>
        @endif
        @if ($user->id !== auth()->id())
        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:contents;"
              onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn-a btn-delete">✕&nbsp;Delete</button>
        </form>
        @endif
    @endif
</div>

        </div>{{-- /member-row --}}
        @endforeach

        @if ($users->hasPages())
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </div>
    @endif

</div>{{-- /wrap --}}

{{-- ═══════════════════════════════════════════════════════════════
     REGISTER MEMBER DRAWER
═══════════════════════════════════════════════════════════════ --}}
@if (!$isImpersonating)
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer" id="registerDrawer" role="dialog" aria-modal="true" aria-label="Register new member">
    <div class="drawer-header">
        <div class="drawer-header-left">
            <div class="drawer-icon">👤</div>
            <div>
                <div class="drawer-title">Register New Member</div>
                <div class="drawer-sub">Admin manual registration — bypasses public form</div>
            </div>
        </div>
        <button type="button" class="drawer-close" onclick="closeDrawer()" aria-label="Close">✕</button>
    </div>
    <div class="drawer-progress" id="drawerProgress">
        <div class="dp-step active" id="step-ind-1"><div class="dp-dot">1</div><span>Identity</span></div>
        <div class="dp-line"></div>
        <div class="dp-step" id="step-ind-2"><div class="dp-dot">2</div><span>Operator</span></div>
        <div class="dp-line"></div>
        <div class="dp-step" id="step-ind-3"><div class="dp-dot">3</div><span>Security</span></div>
        <div class="dp-line"></div>
        <div class="dp-step" id="step-ind-4"><div class="dp-dot">4</div><span>Review</span></div>
    </div>
    <div class="drawer-tabs">
        <button class="dtab active" data-tab="1" onclick="switchTab(1)">1 · Identity</button>
        <button class="dtab" data-tab="2" onclick="switchTab(2)">2 · Operator</button>
        <button class="dtab" data-tab="3" onclick="switchTab(3)">3 · Security</button>
        <button class="dtab" data-tab="4" onclick="switchTab(4)">4 · Review</button>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}" id="registerForm" onsubmit="return validateForm()">
        @csrf
        <div class="drawer-body">
            {{-- TAB 1: IDENTITY --}}
            <div class="dtab-panel" id="panel-1">
                <div class="dsec">Personal Details</div>
                <div class="dform-grid dform-2">
                    <div class="dform-field span2">
                        <label class="dform-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" class="dform-input" id="f-name" value="{{ old('name') }}" placeholder="e.g. John Smith" autocomplete="off" required>
                        @error('name')<div class="dform-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Email Address <span class="req">*</span></label>
                        <input type="email" name="email" class="dform-input" id="f-email" value="{{ old('email') }}" placeholder="john@example.com" autocomplete="off" required>
                        @error('email')<div class="dform-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Phone <span class="opt">(optional)</span></label>
                        <input type="tel" name="phone" class="dform-input" value="{{ old('phone') }}" placeholder="+44 7700 000000">
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Joined Date <span class="opt">(optional)</span></label>
                        <input type="date" name="joined_at" class="dform-input" value="{{ old('joined_at') }}">
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Account Status <span class="req">*</span></label>
                        <select name="status" class="dform-select" required>
                            @foreach (['Active','Standby','Inactive','Suspended'] as $s)
                                <option value="{{ $s }}" {{ old('status','Active') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dform-field span2">
                        <label class="dform-label">Notes <span class="opt">(optional — admin only)</span></label>
                        <textarea name="notes" class="dform-textarea" rows="3" placeholder="Any internal notes about this member…">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="dsec" style="margin-top:1.4rem;">Portal Access Role</div>
<div class="dform-grid dform-2">
    <div class="dform-field">
        <label class="dform-label">Spatie Role <span class="req">*</span></label>
        <select name="spatie_role" class="dform-select" required>
            <option value="member"    {{ old('spatie_role','member')    === 'member'    ? 'selected' : '' }}>👤 Member — standard access</option>
            <option value="committee" {{ old('spatie_role')             === 'committee' ? 'selected' : '' }}>📊 Committee — operational management</option>
            <option value="admin"     {{ old('spatie_role')             === 'admin'     ? 'selected' : '' }}>⚡ Admin — full admin panel</option>
            @if(auth()->user()->isSuperAdmin())
            <option value="super-admin" {{ old('spatie_role')           === 'super-admin'? 'selected' : '' }}>★ Super Admin — unrestricted</option>
            @endif
        </select>
        <div class="dform-hint">Controls which menus and sections this member can access.</div>
    </div>
</div>
                <div class="dsec" style="margin-top:1.4rem;">Account Flags</div>
                <div class="toggle-grid">
                    <label class="toggle-item">
                        <input type="checkbox" name="email_verified" value="1" {{ old('email_verified','1') ? 'checked' : '' }}>
                        <div><div class="toggle-item-label">✓ Mark email verified</div><div class="toggle-item-sub">Sets email_verified_at to now</div></div>
                    </label>
                    <label class="toggle-item">
                        <input type="checkbox" name="force_password_reset" value="1" {{ old('force_password_reset') ? 'checked' : '' }}>
                        <div><div class="toggle-item-label">⚑ Force password reset</div><div class="toggle-item-sub">Member must change on next login</div></div>
                    </label>
                    <label class="toggle-item">
                        <input type="checkbox" name="registration_pending" value="1" {{ old('registration_pending') ? 'checked' : '' }}>
                        <div><div class="toggle-item-label">⏳ Mark as pending</div><div class="toggle-item-sub">Holds account for approval</div></div>
                    </label>
                </div>
            </div>
            {{-- TAB 2: OPERATOR --}}
            <div class="dtab-panel" id="panel-2" style="display:none;">
                <div class="dsec">Callsign &amp; Role</div>
                <div class="dform-grid dform-2">
                    <div class="dform-field">
                        <label class="dform-label">Callsign <span class="opt">(optional)</span></label>
                        <input type="text" name="callsign" class="dform-input" id="f-callsign" value="{{ old('callsign') }}" placeholder="e.g. G0ABC" style="text-transform:uppercase;letter-spacing:.1em;" oninput="this.value=this.value.toUpperCase()">
                        <div class="dform-hint">Leave blank if not yet assigned.</div>
                        @error('callsign')<div class="dform-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Pending Callsign <span class="opt">(optional)</span></label>
                        <input type="text" name="pending_callsign" class="dform-input" value="{{ old('pending_callsign') }}" placeholder="e.g. G0XYZ" style="text-transform:uppercase;letter-spacing:.1em;" oninput="this.value=this.value.toUpperCase()">
                        <div class="dform-hint">If member has a callsign change request.</div>
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Role <span class="opt">(optional)</span></label>
                        <select name="role" class="dform-select">
                            <option value="">— No role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Operator Level <span class="opt">(1–5, optional)</span></label>
                        <select name="level" class="dform-select">
                            <option value="">— Not set —</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="dsec">Annual Activity <span style="font-size:10px;font-weight:normal;color:var(--text-muted);letter-spacing:0;text-transform:none;">{{ $yearLabel }}</span></div>
                <div class="dform-grid dform-3">
                    <div class="dform-field">
                        <label class="dform-label">Events Attended</label>
                        <input type="number" name="events_attended_this_year" class="dform-input" value="{{ old('events_attended_this_year',0) }}" min="0" step="1">
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Vol. Hours</label>
                        <input type="number" name="volunteering_hours_this_year" class="dform-input" value="{{ old('volunteering_hours_this_year',0) }}" min="0" step="0.5">
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Attended This Year?</label>
                        <select name="attended_event_this_year" class="dform-select">
                            <option value="0" {{ old('attended_event_this_year','0') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('attended_event_this_year') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>
            </div>
            {{-- TAB 3: SECURITY --}}
            <div class="dtab-panel" id="panel-3" style="display:none;">
                <div class="dsec">Password</div>
                <div class="autogen-row">
                    <button type="button" class="autogen-btn" onclick="generatePassword()">⟳ Auto-generate password</button>
                    <span id="autogenHint" style="font-size:11px;color:var(--text-muted);display:none;">Password generated — will be shown after saving</span>
                </div>
                <div class="dform-grid dform-2">
                    <div class="dform-field">
                        <label class="dform-label">Password <span class="req">*</span></label>
                        <div class="pwd-wrap">
                            <input type="password" name="password" class="dform-input" id="f-password" placeholder="Min. 8 characters" autocomplete="new-password" oninput="checkStrength(this.value)">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('f-password',this)">👁</button>
                        </div>
                        <div class="pwd-strength">
                            <div class="pwd-seg" id="ps1"></div><div class="pwd-seg" id="ps2"></div>
                            <div class="pwd-seg" id="ps3"></div><div class="pwd-seg" id="ps4"></div>
                        </div>
                        <div class="pwd-label" id="pwdLabel" style="color:var(--text-muted);">Enter a password</div>
                        @error('password')<div class="dform-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="dform-field">
                        <label class="dform-label">Confirm Password <span class="req">*</span></label>
                        <div class="pwd-wrap">
                            <input type="password" name="password_confirmation" class="dform-input" id="f-password2" placeholder="Repeat password" autocomplete="new-password" oninput="checkMatch()">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('f-password2',this)">👁</button>
                        </div>
                        <div class="dform-hint" id="matchHint"></div>
                    </div>
                </div>
                <div style="margin-top:1.2rem;padding:.8rem 1rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.15);border-left:3px solid var(--navy);font-size:12px;color:var(--text-mid);line-height:1.65;">
                    <strong>Tip:</strong> Auto-generate a secure password above. Enable <em>Force password reset</em> in Tab 1 so the member must change it on first login. Passwords are hashed with bcrypt — never stored in plain text.
                </div>
            </div>
            {{-- TAB 4: REVIEW --}}
            <div class="dtab-panel" id="panel-4" style="display:none;">
                <div class="dsec">Review &amp; Confirm</div>
                <div id="reviewSummary" style="display:grid;gap:.5rem;"></div>
                <div style="margin-top:1.2rem;padding:.8rem 1rem;background:#fef3c7;border:1px solid #fde68a;border-left:3px solid #f59e0b;font-size:12px;color:var(--amber);line-height:1.65;">
                    <strong>Before submitting:</strong> Confirm the details above are correct. An account will be created immediately. You can edit the member afterwards from the members list.
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <div class="drawer-footer-note" id="footerNote">Step 1 of 4</div>
            <div class="drawer-footer-btns">
                <button type="button" class="btn-cancel" id="btnPrev" onclick="prevTab()" style="display:none;">← Back</button>
                <button type="button" class="btn-register-outline" id="btnNext" onclick="nextTab()">Next →</button>
                <button type="submit" class="btn-submit-drawer" id="btnSubmit" style="display:none;">✓ Create Member</button>
            </div>
        </div>
    </form>
</div>
@endif

<script>
/* ── FILTER / SEARCH ── */
let activeFilter = 'All';
function filterStatus(filter) {
    activeFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.toggle('filter-active', b.dataset.filter === filter));
    applyFilters();
}
function applyFilters() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    let visible = 0;
    document.querySelectorAll('.member-row').forEach(row => {
        const status   = row.dataset.status   || '';
        const role     = row.dataset.role     || '';
        const attended = row.dataset.attended || '0';
        const search   = row.dataset.search   || '';
        const matchSearch = !q || search.includes(q);
        let matchFilter = true;
        if (activeFilter === 'Active')       matchFilter = status === 'active';
        if (activeFilter === 'Standby')      matchFilter = status === 'standby';
        if (activeFilter === 'Inactive')     matchFilter = status === 'inactive';
        if (activeFilter === 'No role')      matchFilter = !role;
        if (activeFilter === 'Attended')     matchFilter = attended === '1';
        if (activeFilter === 'Not attended') matchFilter = attended === '0';
        const show = matchSearch && matchFilter;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('visibleCount').textContent = visible + ' member' + (visible !== 1 ? 's' : '');
}

/* ── DRAWER ── */
function openDrawer() {
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('registerDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';
    switchTab(1);
}
function closeDrawer() {
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('registerDrawer').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

/* ── TABS ── */
let currentTab = 1;
const totalTabs = 4;
function switchTab(n) {
    currentTab = n;
    document.querySelectorAll('.dtab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + n).style.display = 'block';
    document.querySelectorAll('.dtab').forEach(t => t.classList.toggle('active', parseInt(t.dataset.tab) === n));
    for (let i = 1; i <= totalTabs; i++) {
        const el = document.getElementById('step-ind-' + i);
        el.className = 'dp-step' + (i < n ? ' done' : '') + (i === n ? ' active' : '');
        el.querySelector('.dp-dot').textContent = i < n ? '✓' : i;
    }
    document.getElementById('btnPrev').style.display   = n > 1          ? 'inline-flex' : 'none';
    document.getElementById('btnNext').style.display   = n < totalTabs  ? 'inline-flex' : 'none';
    document.getElementById('btnSubmit').style.display = n === totalTabs ? 'inline-flex' : 'none';
    document.getElementById('footerNote').textContent  = 'Step ' + n + ' of ' + totalTabs;
    if (n === totalTabs) buildReview();
    document.getElementById('registerDrawer').querySelector('.drawer-body').scrollTop = 0;
}
function nextTab() { if (currentTab < totalTabs) switchTab(currentTab + 1); }
function prevTab() { if (currentTab > 1) switchTab(currentTab - 1); }

/* ── REVIEW ── */
function buildReview() {
    const fd = new FormData(document.getElementById('registerForm'));
    const rows = [
        ['Full Name',       fd.get('name')  || '—'],
        ['Email',           fd.get('email') || '—'],
        ['Phone',           fd.get('phone') || '—'],
        ['Status',          fd.get('status') || '—'],
        ['Joined',          fd.get('joined_at') || '—'],
        ['Callsign',        (fd.get('callsign') || '').toUpperCase() || '—'],
        ['Role',            fd.get('role') || '—'],
        ['Level',           fd.get('level') || '—'],
        ['Admin?',          fd.get('is_admin') ? 'Yes' : 'No'],
        ['Portal Role',     fd.get('spatie_role') || 'member'],
        ['Email verified?', fd.get('email_verified') ? 'Yes' : 'No'],
        ['Force pwd reset?',fd.get('force_password_reset') ? 'Yes' : 'No'],
        ['Pending?',        fd.get('registration_pending') ? 'Yes' : 'No'],
        ['Password',        fd.get('password') ? '●●●●●●●● (set)' : '⚠ Not set'],
    ];
    document.getElementById('reviewSummary').innerHTML = rows.map(([k,v]) => `
        <div style="display:flex;align-items:baseline;gap:.75rem;padding:.45rem .75rem;background:var(--grey);border:1px solid var(--grey-mid);">
            <span style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);min-width:130px;flex-shrink:0;">${k}</span>
            <span style="font-size:13px;font-weight:bold;color:var(--text);">${v}</span>
        </div>
    `).join('');
}

/* ── PASSWORD ── */
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.textContent = isText ? '👁' : '🙈';
}
function checkStrength(pwd) {
    const segs  = ['ps1','ps2','ps3','ps4'].map(id => document.getElementById(id));
    const label = document.getElementById('pwdLabel');
    let score = 0;
    if (pwd.length >= 8)           score++;
    if (/[A-Z]/.test(pwd))         score++;
    if (/[0-9]/.test(pwd))         score++;
    if (/[^A-Za-z0-9]/.test(pwd))  score++;
    const colours = ['','#ef4444','#f97316','#eab308','#22c55e'];
    const labels  = ['','Weak','Fair','Good','Strong'];
    segs.forEach((s,i) => s.style.background = i < score ? colours[score] : 'var(--grey-mid)');
    label.textContent = pwd.length ? labels[score] : 'Enter a password';
    label.style.color = pwd.length ? colours[score] : 'var(--text-muted)';
    checkMatch();
}
function checkMatch() {
    const p1   = document.getElementById('f-password').value;
    const p2   = document.getElementById('f-password2').value;
    const hint = document.getElementById('matchHint');
    if (!p2) { hint.textContent = ''; return; }
    if (p1 === p2) { hint.textContent = '✓ Passwords match';       hint.style.color = 'var(--green)'; }
    else           { hint.textContent = '✗ Passwords do not match'; hint.style.color = 'var(--red)'; }
}
function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    let pwd = '';
    for (let i = 0; i < 12; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
    const f1 = document.getElementById('f-password');
    const f2 = document.getElementById('f-password2');
    f1.value = pwd; f2.value = pwd;
    f1.type = 'text'; f2.type = 'text';
    checkStrength(pwd);
    document.getElementById('autogenHint').style.display = 'inline';
}

/* ── VALIDATION ── */
function validateForm() {
    const name  = document.getElementById('f-name').value.trim();
    const email = document.getElementById('f-email').value.trim();
    const pwd   = document.getElementById('f-password').value;
    const pwd2  = document.getElementById('f-password2').value;
    if (!name)       { switchTab(1); alert('Name is required.');        return false; }
    if (!email)      { switchTab(1); alert('Email is required.');       return false; }
    if (!pwd)        { switchTab(3); alert('Password is required.');    return false; }
    if (pwd !== pwd2){ switchTab(3); alert('Passwords do not match.');  return false; }
    return true;
}

@if ($errors->any())
openDrawer();
switchTab({{ $errors->has('name') || $errors->has('email') || $errors->has('phone') ? 1 : ($errors->has('callsign') ? 2 : 3) }});
@endif

/* ── RadioID DMR live activity lookup ── */
(function () {
    // Deduplicate — one fetch per unique callsign even if they appear
    // in both the mobile and desktop DOM copies
    const seen    = new Map(); // callsign → Promise<result>
    const widgets = document.querySelectorAll('.dmr-live[data-callsign]');
    if (!widgets.length) return;

    function relativeTime(dateStr) {
        if (!dateStr) return { label: 'Never', recent: false };
        const diff  = Math.floor((Date.now() - new Date(dateStr)) / 86400000);
        let label;
        if      (diff === 0)  label = 'Today';
        else if (diff === 1)  label = 'Yesterday';
        else if (diff < 30)   label = diff + 'd ago';
        else if (diff < 365)  label = Math.floor(diff / 30) + 'mo ago';
        else                  label = Math.floor(diff / 365) + 'y ago';
        return { label, recent: diff < 30 };
    }

    function renderWidget(el, result) {
        if (!result) {
            el.innerHTML = '';   // blank — not in RadioID database
            return;
        }
        const { lasttg, lastheard } = result;
        const { label, recent }     = relativeTime(lastheard);
        el.innerHTML = `
            ${lasttg
                ? `<span class="dmr-live-tg">TG&nbsp;${lasttg}</span>`
                : ''
            }
            <span class="dmr-live-heard">
                <span class="dmr-live-dot ${recent ? '' : 'stale'}"></span>
                ${label}
            </span>
        `;
    }

    async function fetchCallsign(callsign) {
        if (seen.has(callsign)) return seen.get(callsign);
        const promise = fetch(
    `/admin/radioid-lookup/${encodeURIComponent(callsign)}`
        )
        .then(r => r.json())
        .then(data => data?.results?.[0] ?? null)
        .catch(() => null);
        seen.set(callsign, promise);
        return promise;
    }

    widgets.forEach(async el => {
        const callsign = el.dataset.callsign;
        if (!callsign) { el.innerHTML = ''; return; }
        const result = await fetchCallsign(callsign);
        renderWidget(el, result);
    });
})();
</script>
@endsection