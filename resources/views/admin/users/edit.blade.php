@extends('layouts.admin')

@section('title', 'Edit – ' . $user->name)

@section('content')

<style>
:root {
    --navy:       #003366;
    --navy-mid:   #004080;
    --navy-faint: #e8eef5;
    --red:        #C8102E;
    --red-faint:  #fdf0f2;
    --white:      #FFFFFF;
    --grey:       #F2F2F2;
    --grey-mid:   #dde2e8;
    --grey-dark:  #9aa3ae;
    --text:       #001f40;
    --text-mid:   #2d4a6b;
    --text-muted: #6b7f96;
    --green:      #1a6b3c;
    --green-bg:   #eef7f2;
    --amber:      #8a5500;
    --amber-bg:   #fdf8ec;
    --orange:     #7c2d00;
    --orange-bg:  #fff7ed;
    --orange-brd: #fed7aa;
    --blue:       #1e3a5f;
    --blue-bg:    #eff6ff;
    --blue-brd:   #bfdbfe;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 14px rgba(0,51,102,.11);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--grey); color: var(--text); font-family: var(--font); font-size: 14px; min-height: 100vh; }

.rn-header {
    background: var(--navy); border-bottom: 4px solid var(--red);
    position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,.3);
}
.rn-header-inner {
    max-width: 960px; margin: 0 auto; padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.rn-brand { display: flex; align-items: center; gap: .85rem; padding: .75rem 0; }
.rn-logo { background: var(--red); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo span { font-size: 10px; font-weight: bold; color: var(--white); letter-spacing: .06em; text-align: center; line-height: 1.15; text-transform: uppercase; }
.rn-org  { font-size: 14px; font-weight: bold; color: var(--white); letter-spacing: .04em; text-transform: uppercase; }
.rn-sub  { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }
.rn-back { font-size: 12px; font-weight: bold; color: rgba(255,255,255,.8); text-decoration: none; border: 1px solid rgba(255,255,255,.25); padding: .35rem .9rem; transition: all .15s; }
.rn-back:hover { background: rgba(255,255,255,.1); color: var(--white); }

.page-band { background: var(--white); border-bottom: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm); }
.page-band-inner { max-width: 960px; margin: 0 auto; padding: 1.1rem 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.user-avatar {
    width: 46px; height: 46px; background: var(--navy); flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: bold; color: var(--white); letter-spacing: .04em;
}
.page-band-eyebrow { font-size: 10px; font-weight: bold; color: var(--red); text-transform: uppercase; letter-spacing: .18em; margin-bottom: 2px; display: flex; align-items: center; gap: .4rem; }
.page-band-eyebrow::before { content: ''; width: 14px; height: 2px; background: var(--red); display: inline-block; }
.page-band-name { font-size: 20px; font-weight: bold; color: var(--navy); line-height: 1; }
.page-band-chips { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .4rem; }
.chip { display: inline-flex; align-items: center; padding: 2px 9px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; }
.chip-navy   { background: var(--navy-faint); border: 1px solid rgba(0,51,102,.25); color: var(--navy); }
.chip-red    { background: var(--red-faint);  border: 1px solid rgba(200,16,46,.25); color: var(--red); }
.chip-green  { background: var(--green-bg);   border: 1px solid #b8ddc9; color: var(--green); }
.chip-amber  { background: var(--amber-bg);   border: 1px solid #f5d87a; color: var(--amber); }
.chip-grey   { background: var(--grey);       border: 1px solid var(--grey-mid); color: var(--text-muted); }
.chip-orange { background: var(--orange-bg);  border: 1px solid var(--orange-brd); color: var(--orange); }
.chip-super  { background: #1e0040; border: 1px solid rgba(91,33,182,.5); color: #c4b5fd; }

.page-band-actions { margin-left: auto; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }

.wrap { max-width: 960px; margin: 0 auto; padding: 1.5rem 1.5rem 4rem; }

.alert { padding: .65rem 1rem; margin-bottom: 1.1rem; font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: .6rem; }
.alert-success { background: var(--green-bg); color: var(--green); border: 1px solid #b8ddc9; border-left: 3px solid var(--green); }
.alert-error   { background: var(--red-faint); color: var(--red); border: 1px solid rgba(200,16,46,.25); border-left: 3px solid var(--red); }

.tab-bar { display: flex; border-bottom: 2px solid var(--grey-mid); margin-bottom: 1.25rem; background: var(--white); box-shadow: var(--shadow-sm); overflow-x: auto; }
.tab-btn {
    padding: .8rem 1.4rem; background: transparent; border: none; border-bottom: 3px solid transparent;
    color: var(--text-muted); font-family: var(--font); font-size: 12px; font-weight: bold;
    letter-spacing: .06em; text-transform: uppercase; cursor: pointer;
    transition: all .15s; display: flex; align-items: center; gap: .4rem; margin-bottom: -2px; white-space: nowrap;
}
.tab-btn:hover { color: var(--navy); }
.tab-btn.active { color: var(--navy); border-bottom-color: var(--red); }
.tab-count { background: var(--navy-faint); border: 1px solid rgba(0,51,102,.2); color: var(--navy); font-size: 10px; padding: 1px 6px; min-width: 18px; text-align: center; }
.tab-pane { display: none; }
.tab-pane.active { display: block; animation: fadeUp .25s ease; }

.card { background: var(--white); border: 1px solid var(--grey-mid); margin-bottom: 1rem; box-shadow: var(--shadow-sm); }
.card:last-child { margin-bottom: 0; }
.card-head { padding: .75rem 1.1rem; border-bottom: 1px solid var(--grey-mid); background: var(--grey); display: flex; align-items: center; gap: .65rem; }
.card-icon { width: 28px; height: 28px; background: var(--white); border: 1px solid var(--grey-mid); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.card-title { font-size: 13px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .05em; }
.card-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.card-head-right { margin-left: auto; font-size: 11px; color: var(--text-muted); }

.form-grid { padding: 1.1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .85rem; }
.form-field { display: flex; flex-direction: column; gap: .3rem; }
.form-field.full { grid-column: 1 / -1; }
.form-field label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); }
.form-field label small { text-transform: none; letter-spacing: 0; color: var(--grey-dark); font-weight: normal; font-size: 10px; }
.form-field input,
.form-field select,
.form-field textarea {
    background: var(--white); border: 1px solid var(--grey-mid);
    padding: .5rem .75rem; color: var(--text); font-family: var(--font);
    font-size: 13px; outline: none; width: 100%; resize: vertical; transition: border-color .15s, box-shadow .15s;
}
.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.field-error { font-size: 11px; color: var(--red); margin-top: 2px; font-weight: bold; }

.toggle-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .65rem .85rem; background: var(--grey); border: 1px solid var(--grey-mid);
    cursor: pointer; transition: border-color .15s;
}
.toggle-row:hover { border-color: var(--navy); }
.toggle-row input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--navy); cursor: pointer; flex-shrink: 0; padding: 0; border: none; background: none; box-shadow: none; }
.toggle-label { font-size: 13px; font-weight: bold; }
.toggle-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

.check-grid {
    padding: 1rem 1.1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: .5rem;
}
.check-item {
    display: flex; align-items: center; gap: .5rem;
    padding: .45rem .7rem; border: 1px solid var(--grey-mid); background: var(--grey);
    cursor: pointer; transition: border-color .15s, background .15s;
    font-size: 12px; font-weight: bold; color: var(--text-mid);
}
.check-item:hover { border-color: var(--navy); background: var(--navy-faint); }
.check-item input[type="checkbox"] {
    width: 14px; height: 14px; accent-color: var(--navy);
    flex-shrink: 0; padding: 0; border: none; background: none; box-shadow: none; cursor: pointer;
}
.check-item input:checked + span { color: var(--navy); }
.check-item:has(input:checked) { border-color: var(--navy); background: var(--navy-faint); }
.check-section-label {
    padding: .5rem 1.1rem 0;
    font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; color: var(--text-muted);
}

.meta-row {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: .6rem; padding: .85rem 1.1rem; border-top: 1px solid var(--grey-mid); background: var(--grey);
}
.meta-item { display: flex; flex-direction: column; gap: 3px; }
.meta-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; color: var(--text-muted); }
.meta-val   { font-size: 12px; color: var(--text-mid); font-weight: bold; }
.meta-green { color: var(--green); }
.meta-amber { color: var(--amber); }
.meta-red   { color: var(--red); }

.card-footer {
    padding: .85rem 1.1rem; border-top: 1px solid var(--grey-mid);
    background: var(--grey); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.footer-meta { font-size: 11px; color: var(--text-muted); }

.btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .48rem 1.1rem; border: 1px solid; font-family: var(--font);
    font-size: 12px; font-weight: bold; cursor: pointer; transition: all .12s;
    white-space: nowrap; text-transform: uppercase; letter-spacing: .05em; text-decoration: none;
}
.btn-primary { background: var(--navy); border-color: var(--navy); color: var(--white); }
.btn-primary:hover { background: var(--navy-mid); }
.btn-ghost   { background: transparent; border-color: var(--grey-mid); color: var(--text-muted); }
.btn-ghost:hover { border-color: var(--navy); color: var(--navy); }
.btn-green   { background: var(--green-bg); border-color: #b8ddc9; color: var(--green); }
.btn-green:hover { background: #d6ede3; border-color: var(--green); }
.btn-red     { background: var(--red-faint); border-color: rgba(200,16,46,.3); color: var(--red); }
.btn-red:hover   { background: rgba(200,16,46,.12); border-color: var(--red); }
.btn-amber   { background: var(--amber-bg); border-color: #f5d87a; color: var(--amber); }
.btn-amber:hover { background: #faefc4; border-color: #d4a017; }
.btn-orange  { background: var(--orange-bg); border-color: var(--orange-brd); color: var(--orange); }
.btn-orange:hover { background: #fde8cc; border-color: #f97316; }
.btn-blue    { background: var(--blue-bg); border-color: var(--blue-brd); color: var(--blue); }
.btn-blue:hover  { background: #dbeafe; border-color: #93c5fd; }
.btn-sm { padding: .28rem .75rem; font-size: 11px; }

.pending-banner { border: 1px solid #f5d87a; border-left: 4px solid #c49a00; margin-bottom: 1rem; box-shadow: var(--shadow-sm); }
.pending-banner .card-head { background: var(--amber-bg); border-bottom-color: #fde68a; }
.pending-banner .card-title { color: var(--amber); }

.stat-tiles { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; padding: 1rem 1.1rem; border-bottom: 1px solid var(--grey-mid); }
@media(max-width:480px) { .stat-tiles { grid-template-columns: 1fr 1fr; } }
.stat-tile { background: var(--grey); border: 1px solid var(--grey-mid); border-top: 3px solid var(--navy); padding: .75rem .9rem; }
.stat-tile-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; color: var(--text-muted); margin-bottom: .3rem; }
.stat-tile-value { font-size: 26px; font-weight: bold; line-height: 1; color: var(--navy); }
.stat-tile-sub   { font-size: 11px; color: var(--text-muted); margin-top: 3px; }
.st-red .stat-tile-value   { color: var(--red); }
.st-green .stat-tile-value { color: var(--green); }

.log-table { width: 100%; border-collapse: collapse; }
.log-table th { padding: .5rem 1.1rem; text-align: left; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); border-bottom: 1px solid var(--grey-mid); background: var(--grey); }
.log-table td { padding: .65rem 1.1rem; font-size: 13px; border-bottom: 1px solid var(--grey-mid); color: var(--text-mid); vertical-align: middle; }
.log-table tr:last-child td { border-bottom: none; }
.log-table tr:hover td { background: var(--navy-faint); }
.log-td-name  { color: var(--text); font-weight: bold; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.log-td-hours { color: var(--navy); font-weight: bold; }
.log-td-actions { display: flex; align-items: center; gap: .4rem; }
.log-empty-row td { text-align: center; padding: 2rem 1rem; color: var(--text-muted); border-bottom: none; }

.log-form-grid { padding: .9rem 1.1rem; display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: .75rem; align-items: end; border-bottom: 1px solid var(--grey-mid); }
@media(max-width:700px) { .log-form-grid { grid-template-columns: 1fr 1fr; } }

.override-grid { padding: .9rem 1.1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: .75rem; align-items: end; }
.override-note { padding: .55rem 1.1rem; font-size: 11px; color: var(--amber); border-top: 1px solid var(--grey-mid); background: var(--amber-bg); border-left: 3px solid #c49a00; font-weight: bold; }

.danger-card { border: 1px solid rgba(200,16,46,.3); border-left: 4px solid var(--red); }
.danger-card .card-head { background: var(--red-faint); border-bottom-color: rgba(200,16,46,.2); }
.danger-card .card-title { color: var(--red); }

.warning-card { border: 1px solid var(--orange-brd); border-left: 4px solid #f97316; }
.warning-card .card-head { background: var(--orange-bg); border-bottom-color: var(--orange-brd); }
.warning-card .card-title { color: var(--orange); }

.info-note { padding: .55rem 1.1rem; font-size: 11px; color: var(--text-muted); border-top: 1px solid var(--grey-mid); background: var(--navy-faint); border-left: 3px solid var(--navy); font-weight: bold; }

.control-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    padding: .9rem 1.1rem; border-bottom: 1px solid var(--grey-mid);
}
.control-row:last-child { border-bottom: none; }
.control-row-info { flex: 1; }
.control-row-title { font-size: 13px; font-weight: bold; color: var(--text); margin-bottom: .2rem; }
.control-row-sub   { font-size: 11px; color: var(--text-muted); }
.control-row-sub strong { color: var(--amber); }

.suspension-active {
    background: #fff7ed; border-left: 4px solid #f97316;
    padding: .75rem 1.1rem; font-size: 12px; color: var(--orange); font-weight: bold;
    border-bottom: 1px solid var(--orange-brd); display: flex; align-items: center; gap: .5rem;
}
.suspension-active span { font-weight: normal; color: #92400e; }

.message-active {
    background: #eff6ff; border-left: 4px solid #3b82f6;
    padding: .75rem 1.1rem; font-size: 12px; color: #1e3a5f; font-weight: bold;
    border-bottom: 1px solid #bfdbfe; display: flex; align-items: center; gap: .5rem;
}
.message-active span { font-weight: normal; color: #1e40af; }

.verify-unverified {
    background: #fefce8; border-left: 4px solid #eab308;
    padding: .75rem 1.1rem; font-size: 12px; color: #713f12; font-weight: bold;
    border-bottom: 1px solid #fde68a; display: flex; align-items: center; gap: .5rem;
}
.verify-verified {
    background: var(--green-bg); border-left: 4px solid var(--green);
    padding: .75rem 1.1rem; font-size: 12px; color: var(--green); font-weight: bold;
    border-bottom: 1px solid #b8ddc9; display: flex; align-items: center; gap: .5rem;
}

/* ── SESSIONS TAB ── */
.session-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: .75rem;
    padding: 1.1rem;
}
@media(min-width:640px) { .session-grid { grid-template-columns: repeat(2,1fr); } }

.session-card {
    background: var(--grey);
    border: 1px solid var(--grey-mid);
    border-left: 3px solid var(--grey-mid);
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
}
.session-card.current {
    border-left-color: var(--green);
    background: var(--green-bg);
}
.session-card.recent  { border-left-color: var(--navy); }
.session-card.stale   { border-left-color: var(--grey-dark); }

.session-card-head {
    display: flex; align-items: center; gap: .75rem;
    padding: .8rem 1rem;
    border-bottom: 1px solid var(--grey-mid);
    background: rgba(255,255,255,.6);
}
.session-device-icon {
    width: 36px; height: 36px; border-radius: 6px;
    background: var(--navy); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.session-card.current .session-device-icon { background: var(--green); }
.session-card.stale   .session-device-icon { background: var(--grey-dark); }

.session-device-name { font-size: 13px; font-weight: bold; color: var(--text); }
.session-device-sub  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

.session-current-badge {
    margin-left: auto; flex-shrink: 0;
    display: inline-flex; align-items: center; gap: .3rem;
    padding: 2px 8px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
    background: var(--green-bg); border: 1px solid #b8ddc9; color: var(--green);
}
.session-current-badge::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--green); flex-shrink: 0;
}

.session-card-body { padding: .75rem 1rem; }
.session-meta-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: .5rem;
}
.session-meta-item { display: flex; flex-direction: column; gap: 2px; }
.session-meta-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); }
.session-meta-val   { font-size: 12px; font-weight: bold; color: var(--text-mid); font-family: monospace; }
.session-meta-val.normal { font-family: var(--font); }

.session-ua {
    margin-top: .65rem;
    padding: .5rem .65rem;
    background: var(--white);
    border: 1px solid var(--grey-mid);
    font-size: 10px;
    color: var(--text-muted);
    font-family: monospace;
    word-break: break-all;
    line-height: 1.5;
}

.session-card-foot {
    padding: .6rem 1rem;
    border-top: 1px solid var(--grey-mid);
    background: rgba(255,255,255,.5);
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem;
}
.session-age { font-size: 11px; color: var(--text-muted); font-weight: bold; }
.session-age.fresh  { color: var(--green); }
.session-age.recent { color: var(--navy); }
.session-age.stale  { color: var(--grey-dark); }

.sessions-empty {
    padding: 3rem 1.5rem; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: .75rem;
}
.sessions-empty-icon {
    width: 56px; height: 56px; border-radius: 50%;
    background: var(--grey); border: 1px solid var(--grey-mid);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.sessions-empty-title { font-size: 14px; font-weight: bold; color: var(--text-muted); }
.sessions-empty-sub   { font-size: 12px; color: var(--grey-dark); max-width: 280px; line-height: 1.5; }

.sessions-action-strip {
    padding: .75rem 1.1rem;
    border-top: 1px solid var(--grey-mid);
    background: var(--grey);
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.sessions-action-meta { font-size: 11px; color: var(--text-muted); }

.modal-overlay { display: none; position: fixed; inset: 0; z-index: 200; background: rgba(0,20,50,.7); align-items: center; justify-content: center; padding: 1rem; }
.modal-overlay.open { display: flex; }
.modal { background: var(--white); border: 1px solid var(--grey-mid); width: 100%; max-width: 460px; box-shadow: var(--shadow-md); animation: fadeUp .2s ease; }
.modal-head { padding: .8rem 1.1rem; border-bottom: 1px solid var(--grey-mid); background: var(--navy); display: flex; align-items: center; justify-content: space-between; }
.modal-head-title { font-size: 13px; font-weight: bold; color: var(--white); text-transform: uppercase; letter-spacing: .05em; }
.modal-close { background: transparent; border: 1px solid rgba(255,255,255,.3); color: rgba(255,255,255,.8); cursor: pointer; font-size: 13px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; transition: all .12s; }
.modal-close:hover { border-color: var(--red); color: var(--red); }
.modal-body { padding: 1.1rem; display: flex; flex-direction: column; gap: .8rem; }
.modal-foot { padding: .8rem 1.1rem; border-top: 1px solid var(--grey-mid); background: var(--grey); display: flex; align-items: center; justify-content: flex-end; gap: .5rem; }

@keyframes fadeUp { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
/* ── Temporary Admin PII Protection ── */
.pii-protected {
    position: relative;
    display: inline-block;
    width: 100%;
}
.pii-protected input,
.pii-protected select,
.pii-protected textarea {
    filter: blur(4px);
    user-select: none;
    pointer-events: none;
    color: transparent !important;
    text-shadow: 0 0 8px rgba(0,0,0,.5);
}
.pii-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(248,249,250,.6);
    cursor: not-allowed;
    z-index: 10;
    font-size: 11px;
    font-weight: bold;
    color: #6b7f96;
    text-transform: uppercase;
    letter-spacing: .06em;
    gap: 5px;
}
.pii-blur-text {
    filter: blur(4px);
    user-select: none;
    pointer-events: none;
    display: inline-block;
}
.pii-section-banner {
    background: rgba(180,83,9,.06);
    border: 1px solid rgba(180,83,9,.2);
    border-left: 3px solid #b45309;
    padding: 10px 14px;
    font-size: 12px;
    color: #92400e;
    font-weight: bold;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fade-in { animation: fadeUp .3s ease both; }

@media(max-width:600px) {
    .page-band-inner { gap: .75rem; }
    .page-band-actions { margin-left: 0; width: 100%; }
    .tab-btn { padding: .7rem .85rem; font-size: 11px; }
    .form-grid { grid-template-columns: 1fr; }
    .log-form-grid { grid-template-columns: 1fr; }
    .card-footer { flex-direction: column; align-items: stretch; }
    .card-footer .btn { justify-content: center; }
    .check-grid { grid-template-columns: 1fr 1fr; }
    .control-row { flex-direction: column; align-items: flex-start; }
    .session-meta-grid { grid-template-columns: 1fr; }
}
</style>

@php
    use Carbon\Carbon;
    $now       = Carbon::now();
    $yearStart = $now->month >= 9
        ? Carbon::create($now->year, 9, 1)
        : Carbon::create($now->year - 1, 9, 1);
    $yearEnd   = $yearStart->copy()->addYear()->subDay();
    $yearLabel = $yearStart->format('M Y') . ' – ' . $yearEnd->format('M Y');

    $initials  = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    $activeTab = session('active_tab', 'profile');

    $allModes = [
        'Voice'   => ['FM', 'SSB', 'AM', 'CW'],
        'Digital' => ['DMR', 'D-STAR', 'C4FM / Fusion', 'P25', 'TETRA'],
        'Data'    => ['APRS', 'FT8', 'WSPR', 'JS8Call', 'Winlink', 'Packet'],
    ];
    $userModes = is_array($user->modes) ? $user->modes : (json_decode($user->modes ?? '[]', true) ?? []);

    $isSuspended = ! is_null($user->suspended_at);
    $isTemporaryGuest = $user->hasRole("temporary_guest") || ($user->guest_expires_at !== null);
    $isVerified  = ! is_null($user->email_verified_at);

    $currentSessionId = session()->getId();

    $parsedSessions = collect($sessions ?? [])->map(function ($s) use ($currentSessionId) {
        $ua = $s->user_agent ?? '';
        $lastActivity = Carbon::createFromTimestamp($s->last_activity);
        $minutesAgo = $lastActivity->diffInMinutes(now());

        $isMobile  = preg_match('/Mobile|Android|iPhone|iPad/i', $ua);
        $isTablet  = preg_match('/iPad|Tablet/i', $ua);
        $isWindows = preg_match('/Windows/i', $ua);
        $isMac     = preg_match('/Macintosh|Mac OS/i', $ua);
        $isLinux   = preg_match('/Linux/i', $ua) && ! $isMobile;
        $isChrome  = preg_match('/Chrome/i', $ua) && ! preg_match('/Edg/i', $ua);
        $isFirefox = preg_match('/Firefox/i', $ua);
        $isSafari  = preg_match('/Safari/i', $ua) && ! $isChrome;
        $isEdge    = preg_match('/Edg/i', $ua);

        if ($isTablet)       { $deviceIcon = '📱'; $deviceType = 'Tablet'; }
        elseif ($isMobile)   { $deviceIcon = '📱'; $deviceType = 'Mobile'; }
        elseif ($isMac)      { $deviceIcon = '💻'; $deviceType = 'Mac'; }
        elseif ($isWindows)  { $deviceIcon = '🖥️'; $deviceType = 'Windows PC'; }
        elseif ($isLinux)    { $deviceIcon = '🖥️'; $deviceType = 'Linux'; }
        else                 { $deviceIcon = '🌐'; $deviceType = 'Unknown device'; }

        if ($isEdge)         $browser = 'Microsoft Edge';
        elseif ($isFirefox)  $browser = 'Firefox';
        elseif ($isSafari)   $browser = 'Safari';
        elseif ($isChrome)   $browser = 'Chrome';
        else                 $browser = 'Unknown browser';

        $isCurrent = $s->id === $currentSessionId;
        $ageClass  = $minutesAgo < 5 ? 'fresh' : ($minutesAgo < 60 ? 'recent' : 'stale');
        $cardClass = $isCurrent ? 'current' : ($minutesAgo < 60 ? 'recent' : 'stale');

        return (object)[
            'id'           => $s->id,
            'ip'           => $s->ip_address ?? '—',
            'ua'           => $ua,
            'deviceIcon'   => $deviceIcon,
            'deviceType'   => $deviceType,
            'browser'      => $browser,
            'lastActivity' => $lastActivity,
            'minutesAgo'   => $minutesAgo,
            'ageClass'     => $ageClass,
            'cardClass'    => $cardClass,
            'isCurrent'    => $isCurrent,
            'humanAgo'     => $lastActivity->diffForHumans(),
        ];
    })->sortByDesc('minutesAgo')->values();
@endphp

<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Edit Member</div>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rn-back">← Members</a>
    </div>
</header>

<div class="page-band fade-in">
    <div class="page-band-inner">
        @if($isTemporaryGuest)
        <img src="{{ Storage::url('avatars/TempAvatar.png') }}"
             style="width:46px;height:46px;object-fit:cover;flex-shrink:0;border:2px solid rgba(180,83,9,.4);"
             alt="Temporary Guest" title="Temporary Guest">
    @elseif($user->avatar)
        <img src="{{ Storage::url($user->avatar) }}" style="width:46px;height:46px;border-radius:0;object-fit:cover;flex-shrink:0;" alt="">
    @else
        <div class="user-avatar">{{ $initials }}</div>
    @endif
        <div class="page-band-text">
            <div class="page-band-eyebrow">Edit Member · ID #{{ $user->id }}</div>
            <div class="page-band-name">{{ $user->name }}</div>
            <div class="page-band-chips">
                @if ($user->callsign)
                    <span class="chip chip-navy">@if($user->piiVisible()){{ strtoupper($user->callsign) }}@else<span style="filter:blur(3px);user-select:none;">●●●●●</span>@endif</span>
                @endif
                @if ($user->dmr_id)
                    <span class="chip chip-navy">DMR @if($user->piiVisible()){{ $user->dmr_id }}@else<span style="filter:blur(3px);user-select:none;">●●●●●●●</span>@endif</span>
                @endif
                @if ($user->licence_class)
                    <span class="chip chip-navy">@if($user->piiVisible()){{ $user->licence_class }}@else<span style="filter:blur(3px);user-select:none;">●●●</span>@endif</span>
                @endif
                @if ($user->role)
                    <span class="chip chip-navy">{{ $user->role }}</span>
                @endif
                @if ($user->is_admin)
                    <span class="chip chip-red">⚡ Admin</span>
                @endif
                @if ($user->is_super_admin)
                    <span class="chip chip-super">★ Super Admin</span>
                @endif
                @if ($isSuspended)
                    <span class="chip chip-orange">🔒 Suspended</span>
                @elseif ($user->status === 'Active')
                    <span class="chip chip-green">● Active</span>
                @elseif ($user->status)
                    <span class="chip chip-grey">{{ $user->status }}</span>
                @endif
                @if (! $isVerified)
                    <span class="chip chip-amber">✉ Unverified email</span>
                @endif
                @if ($user->force_password_reset)
                    <span class="chip chip-amber">⚠ Password reset required</span>
                @endif
                @if ($user->admin_message)
                    <span class="chip chip-amber">📩 Message pending</span>
                @endif
                @if ($user->available_for_callout)
                    <span class="chip chip-green">📻 On-call</span>
                @endif
                @if ($user->pending_callsign)
                    <span class="chip chip-amber">⏳ Pending callsign</span>
                @endif
                @if ($parsedSessions->count() > 0)
                    <span class="chip chip-navy">🖥 {{ $parsedSessions->count() }} {{ Str::plural('session', $parsedSessions->count()) }}</span>
                @endif
            </div>
        </div>

        <div class="page-band-actions">
            @if (! $user->is_admin && ! $isSuspended)
            <form method="POST" action="{{ route('admin.impersonate', $user->id) }}"
                  onsubmit="return confirm('Log in as {{ addslashes($user->name) }}?\n\nYou will be able to browse the site as this member. Use the orange banner to return.');">
                @csrf
                <button type="submit" class="btn btn-orange btn-sm">👤 Impersonate</button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.users.force-logout', $user->id) }}"
                  onsubmit="return confirm('Immediately log out {{ addslashes($user->name) }}?');">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">⏏ Force Logout</button>
            </form>
        </div>
    </div>
</div>

<div class="wrap">
@if($isTemporaryGuest)
<div class="fade-in" style="background:#fffbf0;border:1px solid #f0c040;border-left:4px solid #b45309;padding:14px 18px;margin-bottom:1rem;display:flex;align-items:flex-start;gap:14px;">
    <div style="font-size:24px;flex-shrink:0;">⏱</div>
    <div style="flex:1;">
        <div style="font-size:13px;font-weight:bold;color:#b45309;margin-bottom:4px;">Temporary Guest Account</div>
        <div style="font-size:12px;color:#92400e;line-height:1.6;">
            This is a temporary guest account with read-only member access. Many profile fields are not applicable.
            @if($user->guest_expires_at)
                <br><strong>Expiry:</strong>
                @if($user->guest_expires_at->isPast())
                    <span style="color:#C8102E;font-weight:bold;">Expired {{ $user->guest_expires_at->diffForHumans() }} ({{ $user->guest_expires_at->format('d M Y H:i') }})</span>
                @else
                    <span style="color:#b45309;font-weight:bold;">{{ $user->guest_expires_at->format('d M Y H:i') }} ({{ $user->guest_expires_at->diffForHumans() }})</span>
                @endif
            @else
                <br><strong>Expiry:</strong> No expiry set — access is indefinite.
            @endif
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.temporary-guests.edit', $user) }}" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:#b45309;color:#fff;font-size:11px;font-weight:bold;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">⚙ Manage Guest Settings</a>
            <a href="{{ route('admin.temporary-guests.index') }}" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:#fff;border:1px solid #f0c040;color:#b45309;font-size:11px;font-weight:bold;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">← All Guests</a>
        </div>
    </div>
</div>
@endif
@if ($user->is_super_admin && ! auth()->user()->is_super_admin)
<div class="alert fade-in" style="background:#1e0040;color:#c4b5fd;border:1px solid rgba(91,33,182,.4);border-left:3px solid #7c3aed;">
    ★ This is a Super Administrator account. You can view their profile but cannot make changes.
</div>
@endif
    @if (session('success') || session('status'))
        <div class="alert alert-success fade-in">✓ {{ session('success') ?? session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error fade-in">✕ {{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error fade-in">✕ Please fix the errors below.</div>
    @endif

    <div class="tab-bar fade-in">
        <button class="tab-btn" data-tab="profile">👤 Profile</button>
        <button class="tab-btn" data-tab="radio" @if($isTemporaryGuest) style="display:none" @endif>📻 Radio</button>
        <button class="tab-btn" data-tab="activity" @if($isTemporaryGuest) style="display:none" @endif>
            📊 Activity
            <span class="tab-count">{{ $activityLogs->count() }}</span>
        </button>
        <button class="tab-btn" data-tab="sessions">
            🖥 Sessions
            @if ($parsedSessions->count() > 0)
                <span class="tab-count">{{ $parsedSessions->count() }}</span>
            @endif
        </button>
        <button class="tab-btn" data-tab="access">🔑 Access</button>
        <button class="tab-btn" data-tab="control">⚙ Account Control</button>
        <button class="tab-btn" data-tab="training" @if($isTemporaryGuest) style="display:none" @endif>🏅 Training</button>
    </div>


    {{-- ════════════════════════════════
         TAB: PROFILE
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-profile">

        @if ($user->pending_callsign)
        <div class="card pending-banner fade-in">
            <div class="card-head">
                <div class="card-icon">⏳</div>
                <div>
                    <div class="card-title">Pending Callsign Approval</div>
                    <div class="card-sub">Member has requested a change</div>
                </div>
            </div>
            <div style="padding:1rem 1.1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                <div style="display:inline-flex;align-items:center;gap:.65rem;">
                    @if ($user->callsign)
                        <span style="font-size:13px;font-weight:bold;color:var(--text-muted);text-decoration:line-through;">{{ strtoupper($user->callsign) }}</span>
                        <span style="color:var(--text-muted);">→</span>
                    @else
                        <span style="font-size:12px;color:var(--text-muted);">No callsign</span>
                        <span style="color:var(--text-muted);">→</span>
                    @endif
                    <span style="font-size:15px;font-weight:bold;color:var(--amber);padding:.2rem .65rem;background:var(--amber-bg);border:1px solid #f5d87a;letter-spacing:.1em;">
                        {{ strtoupper($user->pending_callsign) }}
                    </span>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <form method="POST" action="{{ route('admin.callsign.approve', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-green btn-sm">✓ Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.callsign.reject', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-red btn-sm">✕ Reject</button>
                    </form>
                </div>
            </div>
        </div>
        @endif


            {{-- AVATAR --}}
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">📷</div>
                    <div>
                        <div class="card-title">Profile Photo</div>
                        <div class="card-sub">Shown on the member's profile, portal, and DMR dashboard</div>
                    </div>
                </div>
                <div style="padding:1.1rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}"
                             style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--grey-mid);flex-shrink:0;" alt="">
                    @else
                        <div style="width:64px;height:64px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:bold;color:#fff;flex-shrink:0;">{{ $initials }}</div>
                    @endif
                    <div style="flex:1;min-width:200px;">
                        <form method="POST" action="{{ route('admin.users.avatar.update', $user->id) }}" enctype="multipart/form-data"
                              style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            @csrf
                            <input type="file" name="avatar" accept="image/*" style="font-size:12px;flex:1;min-width:160px;">
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </form>
                        @if($user->avatar)
                            <form method="POST" action="{{ route('admin.users.avatar.destroy', $user->id) }}"
                                  style="margin-top:.5rem;"
                                  onsubmit="return confirm('Remove this member\'s profile photo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-red btn-sm">✕ Remove photo</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="info-note">ℹ Max 2 MB — JPG, PNG, WebP or GIF. Also sent to the DMR dashboard on next login.</div>
            </div>

        {{-- Main profile form — no nested forms inside --}}
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_section" value="profile">

            {{-- Identity --}}
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">👤</div>
                    <div>
                        <div class="card-title">Identity</div>
                        <div class="card-sub">Name, callsign &amp; DMR ID</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Full name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Callsign <small>(admin override — bypasses approval)</small></label>
                        @if($user->piiVisible())
                        <input type="text" name="callsign" value="{{ old('callsign', $user->callsign) }}"
                               placeholder="e.g. M0XYZ" oninput="this.value=this.value.toUpperCase()">
                        @else
                        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●" readonly disabled></div>
                        @endif
                        @error('callsign')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>DMR ID <small>(optional)</small></label>
                        <input type="text" name="dmr_id" value="{{ old('dmr_id', $user->dmr_id) }}"
                               placeholder="e.g. 2346001" inputmode="numeric">
                        @error('dmr_id')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">✉️</div>
                    <div>
                        <div class="card-title">Contact</div>
                        <div class="card-sub">Email &amp; phone</div>
                    </div>
                </div>
                <div class="form-grid">
                    @if($isTemporaryGuest || !auth()->user()->isTemporaryAdmin())
                    <div class="form-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Phone <small>(optional)</small></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="07700 900000">
                        @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Telegram Chat ID <small>(optional)</small></label>
                        <input type="text" name="telegram_chat_id"
                               value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}"
                               placeholder="e.g. 123456789">
                        @error('telegram_chat_id')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    @else
                    <div class="form-field full" style="grid-column:1/-1;">
                        <div class="pii-section-banner">🔒 Contact details are hidden to protect member privacy (GDPR)</div>
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <div class="pii-protected">
                            <div class="pii-overlay">🔒 Protected</div>
                            <input type="text" value="●●●●●●●●●●●●●●" readonly disabled>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Phone</label>
                        <div class="pii-protected">
                            <div class="pii-overlay">🔒 Protected</div>
                            <input type="text" value="●●●●●●●●●●" readonly disabled>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Telegram Chat ID</label>
                        <div class="pii-protected">
                            <div class="pii-overlay">🔒 Protected</div>
                            <input type="text" value="●●●●●●●" readonly disabled>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="meta-row">
                    <div class="meta-item">
                        <div class="meta-label">Email verified</div>
                        <div class="meta-val {{ $isVerified ? 'meta-green' : 'meta-red' }}">
                            @if($user->piiVisible()){{ $isVerified ? $user->email_verified_at->format('d M Y') : 'Not verified' }}@else<span style="filter:blur(3px);user-select:none;">●● ●●● ●●●●</span>@endif
                        </div>
                        {{-- Buttons trigger hidden forms outside this form to avoid nesting --}}
                        @if (! $isVerified)
                            <div style="display:flex;gap:.4rem;margin-top:.4rem;flex-wrap:wrap;">
                                <button type="button" class="btn btn-green btn-sm"
                                        onclick="document.getElementById('form-verify-email').submit()">✓ Mark verified</button>
                                <button type="button" class="btn btn-blue btn-sm"
                                        onclick="document.getElementById('form-send-verification').submit()">✉ Send link</button>
                            </div>
                        @else
                            <div style="margin-top:.4rem;">
                                <button type="button" class="btn btn-ghost btn-sm"
                                        onclick="document.getElementById('form-send-verification').submit()">↺ Resend link</button>
                            </div>
                        @endif
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Member since</div>
                        @if(auth()->user()->is_super_admin)
                            <input type="date" name="created_at_override"
                                   value="{{ old('created_at_override', $user->created_at->format('Y-m-d')) }}"
                                   style="border:1px solid var(--grey-mid);padding:4px 8px;font-size:12px;font-family:var(--font);color:var(--text);outline:none;width:100%;"
                                   title="Super admin: edit account creation date">
                            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">⭐ Super admin edit</div>
                        @else
                            <div class="meta-val">{{ $user->created_at->format('d M Y') }}</div>
                        @endif
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Last updated</div>
                        <div class="meta-val">{{ $user->updated_at->format('d M Y H:i') }}</div>
                    </div>
</div>
            <div class="info-note">
                ℹ To get a member's Telegram Chat ID — they must message <strong>@raynet_liverpool_bot</strong> and send <strong>/start</strong>. Their personal chat ID will then appear in the bot's <code>getUpdates</code> feed. Once saved, priority 1–3 notifications will also be sent to them via Telegram DM.
            </div>
        </div>

           {{-- Operator Profile --}}
@if(!$isTemporaryGuest)
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">📡</div>
                    <div>
                        <div class="card-title">Operator Profile</div>
                        <div class="card-sub">Role, level, status &amp; deployment info</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Role</label>
                        <select name="role">
                            <option value="">— Not set —</option>
                            @foreach (['Operator', 'Net Controller', 'Group Controller', 'Deputy Controller', 'Liaison Officer', 'Trainee'] as $r)
                                <option value="{{ $r }}" {{ old('role', $user->operator_title) == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Level</label>
                        <select name="level">
                            <option value="">— Not set —</option>
                            @foreach ([
                                1 => 'Level 1 — Operator',
                                2 => 'Level 2 — Advanced Operator',
                                3 => 'Level 3 — Specialist',
                                4 => 'Level 4 — Team Leader',
                                5 => 'Level 5 — Instructor',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ old('level', $user->level) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Status</label>
                        <select name="status">
                            <option value="">— Not set —</option>
                            @foreach (['Active', 'Standby', 'Inactive', 'Suspended'] as $s)
                                <option value="{{ $s }}" {{ old('status', $user->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Joined RAYNET</label>
                        <input type="date" name="joined_at" value="{{ old('joined_at', $user->joined_at?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-field full">
                        <label>Notes <small>(internal — not visible to member)</small></label>
                        <textarea name="notes" rows="3" placeholder="Equipment, availability, training progress…">{{ old('notes', $user->notes) }}</textarea>
                    </div>
                </div>
            </div>

            @endif
{{-- Deployment Availability --}}
@if(!$isTemporaryGuest)
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">🚗</div>
                    <div>
                        <div class="card-title">Deployment Availability</div>
                        <div class="card-sub">Callout availability &amp; transport</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label style="margin-bottom:.5rem;">Callout availability</label>
                        <input type="hidden" name="available_for_callout" value="0">
                        <label class="toggle-row">
                            <input type="checkbox" name="available_for_callout" value="1"
                                   {{ old('available_for_callout', $user->available_for_callout) ? 'checked' : '' }}>
                            <div>
                                <div class="toggle-label">Available for callout</div>
                                <div class="toggle-sub">Can be deployed to RAYNET activations</div>
                            </div>
                        </label>
                    </div>
                    <div class="form-field">
                        <label style="margin-bottom:.5rem;">Transport</label>
                        <input type="hidden" name="has_vehicle" value="0">
                        <label class="toggle-row">
                            <input type="checkbox" name="has_vehicle" value="1"
                                   {{ old('has_vehicle', $user->has_vehicle) ? 'checked' : '' }}>
                            <div>
                                <div class="toggle-label">Has own vehicle</div>
                                <div class="toggle-sub">Can self-deploy to events</div>
                            </div>
                        </label>
                    </div>
                    <div class="form-field">
                        <label>Vehicle type <small>(optional)</small></label>
                        <input type="text" name="vehicle_type" value="{{ old('vehicle_type', $user->vehicle_type) }}"
                               placeholder="e.g. Car, Van, Motorcycle">
                    </div>
                    <div class="form-field">
                        <label>Max travel <small>(miles)</small></label>
                        <input type="number" name="max_travel_miles" min="0" max="999" step="5"
                               value="{{ old('max_travel_miles', $user->max_travel_miles) }}"
                               placeholder="e.g. 25">
                    </div>
                </div>
            </div>

            @endif
{{-- Emergency Contact --}}
@if(!$isTemporaryGuest)
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">🆘</div>
                    <div>
                        <div class="card-title">Emergency Contact</div>
                        <div class="card-sub">Next of kin — not shared with member</div>
                    </div>
                </div>
                @if(auth()->user()->isTemporaryAdmin() && !$isTemporaryGuest)
                <div class="form-grid">
                    <div class="form-field full" style="grid-column:1/-1;">
                        <div class="pii-section-banner">🔒 Emergency contact details are hidden to protect member privacy (GDPR)</div>
                    </div>
                    <div class="form-field">
                        <label>Full name</label>
                        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●●●●●●●" readonly disabled></div>
                    </div>
                    <div class="form-field">
                        <label>Relationship</label>
                        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●●●" readonly disabled></div>
                    </div>
                    <div class="form-field">
                        <label>Phone number</label>
                        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●●●●●●" readonly disabled></div>
                    </div>
                </div>
                @else
                <div class="form-grid">
                    <div class="form-field">
                        <label>Full name</label>
                        <input type="text" name="nok_name" value="{{ old('nok_name', $user->nok_name) }}"
                               placeholder="e.g. Jane Smith">
                        @error('nok_name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Relationship</label>
                        <select name="nok_relationship">
                            <option value="">— Not set —</option>
                            @foreach (['Spouse / Partner', 'Parent', 'Sibling', 'Child', 'Friend', 'Other'] as $rel)
                                <option value="{{ $rel }}" {{ old('nok_relationship', $user->nok_relationship) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Phone number</label>
                        <input type="text" name="nok_phone" value="{{ old('nok_phone', $user->nok_phone) }}"
                               placeholder="07700 900000">
                        @error('nok_phone')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="info-note">
                    ℹ This information is visible to admins only and is never shown to the member or other users.
                </div>
                @endif
            </div>

            @endif
{{-- Password + Save --}}
            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">🔒</div>
                    <div>
                        <div class="card-title">Set New Password</div>
                        <div class="card-sub">Leave blank to keep existing</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label>New password <small>(optional)</small></label>
                        <input type="password" name="password" autocomplete="new-password">
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Confirm password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>
    <div class="card-footer">
                <div class="footer-meta">
                    ID #{{ $user->id }}
                    @if($user->callsign) · @if($user->piiVisible()){{ strtoupper($user->callsign) }}@else<span style="filter:blur(3px);user-select:none;">●●●●●</span>@endif @endif
                    @if($user->role) · {{ ucwords(str_replace(['-','_'],' ',$user->role)) }} @endif
                </div>
                @if ($user->is_super_admin && ! auth()->user()->is_super_admin)
                    <button type="button" class="btn btn-ghost" disabled title="Super Admin — protected">🔒 Protected</button>
                @else
                    <button type="submit" class="btn btn-primary">✓ Save Profile</button>
                @endif
            </div>
        </div>{{-- /card --}}

        </form>{{-- /profile form --}}

        </form>{{-- /profile form --}}

        {{-- Hidden email action forms outside the profile form to prevent nesting --}}
        <form id="form-verify-email" method="POST" action="{{ route('admin.users.verify-email', $user->id) }}" style="display:none;">
            @csrf
        </form>
        <form id="form-send-verification" method="POST" action="{{ route('admin.users.send-verification', $user->id) }}" style="display:none;">
            @csrf
        </form>

    </div>{{-- /tab-profile --}}


    {{-- ════════════════════════════════
         TAB: RADIO
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-radio">

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_section" value="radio">

            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">📜</div>
                    <div>
                        <div class="card-title">Amateur Licence</div>
                        <div class="card-sub">Ofcom licence details</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label>Licence class</label>
                        <select name="licence_class">
                            <option value="">— Not set —</option>
                            @foreach (['Foundation', 'Intermediate', 'Full'] as $lc)
                                <option value="{{ $lc }}" {{ old('licence_class', $user->licence_class) == $lc ? 'selected' : '' }}>{{ $lc }}</option>
                            @endforeach
                        </select>
                        @error('licence_class')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Ofcom licence number <small>(optional)</small></label>
                        @if($user->piiVisible())
                        <input type="text" name="licence_number"
                               value="{{ old('licence_number', $user->licence_number) }}"
                               placeholder="e.g. AB1234567"
                               oninput="this.value=this.value.toUpperCase()">
                        @else
                        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●●●●●" readonly disabled></div>
                        @endif
                        @error('licence_number')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="info-note">
                    ℹ Ofcom amateur licences do not expire. The licence number can be verified at <strong>ofcom.org.uk/manage-your-licence</strong>.
                </div>
            </div>

            <div class="card fade-in">
    <div class="card-head">
        <div class="card-icon">🌐</div>
        <div>
            <div class="card-title">Network Node &amp; VoIP IDs</div>
            <div class="card-sub">DMR, Echolink, D-STAR, C4FM, APRS, AllStar, SVXLink &amp; RAYNET VoIP</div>
        </div>
    </div>
    <div class="form-grid">
        <div class="form-field">
            <label>DMR ID <small>(RadioID.net)</small></label>
            @if($user->piiVisible())
        <input type="text" name="dmr_id" value="{{ old('dmr_id', $user->dmr_id) }}" placeholder="e.g. 2346001" inputmode="numeric">
        @else
        <div class="pii-protected"><div class="pii-overlay">🔒 Protected</div><input type="text" value="●●●●●●●" readonly disabled></div>
        @endif
            @error('dmr_id')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>Echolink number <small>(optional)</small></label>
            <input type="text" name="echolink_number" value="{{ old('echolink_number', $user->echolink_number) }}" placeholder="e.g. 123456" inputmode="numeric">
            @error('echolink_number')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>D-STAR callsign <small>(optional)</small></label>
            <input type="text" name="dstar_callsign" value="{{ old('dstar_callsign', $user->dstar_callsign) }}" placeholder="e.g. M0XYZ   E" oninput="this.value=this.value.toUpperCase()">
            @error('dstar_callsign')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>C4FM / Wires-X callsign <small>(optional)</small></label>
            <input type="text" name="c4fm_callsign" value="{{ old('c4fm_callsign', $user->c4fm_callsign) }}" placeholder="e.g. M0XYZ" oninput="this.value=this.value.toUpperCase()">
            @error('c4fm_callsign')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>APRS SSID <small>(optional)</small></label>
            <input type="text" name="aprs_ssid" value="{{ old('aprs_ssid', $user->aprs_ssid) }}" placeholder="e.g. M0XYZ-9" oninput="this.value=this.value.toUpperCase()">
            @error('aprs_ssid')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>AllStar node number <small>(optional)</small></label>
            <input type="text" name="allstar_node" value="{{ old('allstar_node', $user->allstar_node) }}" placeholder="e.g. 54321" inputmode="numeric">
            @error('allstar_node')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>SVXLink network <small>(optional — type which network)</small></label>
            <input type="text" name="svxlink_network" value="{{ old('svxlink_network', $user->svxlink_network) }}" placeholder="e.g. EchoLink, SK6BA, local node name">
            @error('svxlink_network')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-field">
            <label>RAYNET VoIP number <small>(optional)</small></label>
            <input type="text" name="raynet_voip" value="{{ old('raynet_voip', $user->raynet_voip) }}" placeholder="e.g. 5000" inputmode="numeric">
            @error('raynet_voip')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">🎛️</div>
                    <div>
                        <div class="card-title">Operating Capabilities</div>
                        <div class="card-sub">Modes &amp; bands this operator can use</div>
                    </div>
                </div>
                @foreach ($allModes as $group => $modes)
                    <div class="check-section-label">{{ $group }}</div>
                    <div class="check-grid">
                        @foreach ($modes as $mode)
                            <label class="check-item">
                                <input type="checkbox" name="modes[]" value="{{ $mode }}"
                                       {{ in_array($mode, old('modes', $userModes)) ? 'checked' : '' }}>
                                <span>{{ $mode }}</span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
                <div style="height:.75rem;"></div>
            </div>

            <div class="card fade-in" style="border:none;background:transparent;box-shadow:none;">
                <div class="card-footer" style="background:transparent;border:none;justify-content:flex-end;padding:0;">
                    <button type="submit" class="btn btn-primary">✓ Save Radio Details</button>
                </div>
            </div>

        </form>
    </div>{{-- /tab-radio --}}


    {{-- ════════════════════════════════
         TAB: ACTIVITY
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-activity">

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">📊</div>
                <div>
                    <div class="card-title">Annual Stats</div>
                    <div class="card-sub">RAYNET year: {{ $yearLabel }}</div>
                </div>
            </div>
            <div class="stat-tiles">
                <div class="stat-tile {{ $user->attended_event_this_year ? 'st-green' : '' }}">
                    <div class="stat-tile-label">Attended this year</div>
                    <div class="stat-tile-value">{{ $user->attended_event_this_year ? 'Yes' : 'No' }}</div>
                    <div class="stat-tile-sub">{{ $user->attended_event_this_year ? 'Active member' : 'No events yet' }}</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-label">Events</div>
                    <div class="stat-tile-value">{{ $user->events_attended_this_year ?? 0 }}</div>
                    <div class="stat-tile-sub">Individual check-ins</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-label">Vol. hours</div>
                    <div class="stat-tile-value">{{ number_format($user->volunteering_hours_this_year ?? 0, 1) }}</div>
                    <div class="stat-tile-sub">Hours this year</div>
                </div>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">📋</div>
                <div>
                    <div class="card-title">Event Log</div>
                    <div class="card-sub">All logged entries</div>
                </div>
                <div class="card-head-right">{{ $activityLogs->count() }} {{ Str::plural('entry', $activityLogs->count()) }}</div>
            </div>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Hours</th>
                        <th>Logged by</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityLogs as $log)
                    <tr>
                        <td>{{ $log->event_date->format('d M Y') }}</td>
                        <td class="log-td-name">{{ $log->event_name ?? '—' }}</td>
                        <td class="log-td-hours">{{ number_format($log->hours, 1) }}h</td>
                        <td>{{ $log->logger?->name ?? 'Admin' }}</td>
                        <td>
                            <div class="log-td-actions">
                                <button type="button" class="btn btn-ghost btn-sm"
                                    onclick="openEditModal({{ $log->id }},'{{ addslashes($log->event_name ?? '') }}','{{ $log->event_date->format('Y-m-d') }}',{{ $log->hours }})">
                                    ✎ Edit
                                </button>
                                <form method="POST" action="{{ route('admin.users.activity.log.destroy', [$user->id, $log->id]) }}"
                                      onsubmit="return confirm('Delete this entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-red btn-sm">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="log-empty-row">
                        <td colspan="5">No events logged yet for this member.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">➕</div>
                <div>
                    <div class="card-title">Log Event Attendance</div>
                    <div class="card-sub">Adds entry and increments annual totals</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.activity.add', $user->id) }}">
                @csrf
                <div class="log-form-grid">
                    <div class="form-field">
                        <label>Event name <small>(optional)</small></label>
                        <input type="text" name="event_name" placeholder="e.g. Liverpool Half Marathon">
                    </div>
                    <div class="form-field">
                        <label>Event date</label>
                        <input type="date" name="event_date" value="{{ date('Y-m-d') }}">
                        @error('event_date')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label>Hours</label>
                        <input type="number" name="event_hours" min="0" max="24" step="0.5" placeholder="e.g. 4" required>
                        @error('event_hours')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <button type="submit" class="btn btn-green">+ Log</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">⚙️</div>
                <div>
                    <div class="card-title">Manual Override</div>
                    <div class="card-sub">Directly set annual counter values</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.activity.override', $user->id) }}">
                @csrf
                <div class="override-grid">
                    <div class="form-field">
                        <label>Attended this year</label>
                        <select name="attended_event_this_year">
                            <option value="0" {{ !($user->attended_event_this_year ?? false) ? 'selected' : '' }}>No</option>
                            <option value="1" {{ ($user->attended_event_this_year ?? false) ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Events count</label>
                        <input type="number" name="events_attended_this_year" min="0" step="1" value="{{ $user->events_attended_this_year ?? 0 }}">
                    </div>
                    <div class="form-field">
                        <label>Volunteering hours</label>
                        <input type="number" name="volunteering_hours_this_year" min="0" step="0.5" value="{{ number_format($user->volunteering_hours_this_year ?? 0, 1) }}">
                    </div>
                    <div class="form-field" style="justify-content:flex-end;">
                        <button type="submit" class="btn btn-amber">✓ Override</button>
                    </div>
                </div>
                <div class="override-note">
                    ⚠ Override sets counters directly without writing to the event log. Use "Log Event Attendance" for normal use.
                </div>
            </form>
        </div>

    </div>{{-- /tab-activity --}}
{{-- ════════════════════════════════
     TAB: TRAINING
════════════════════════════════ --}}
<div class="tab-pane" id="tab-training">

<style>
.training-section-head {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .14em; color: var(--text-muted);
    display: flex; align-items: center; gap: .5rem;
    padding: .65rem 1.1rem; background: var(--grey);
    border-bottom: 1px solid var(--grey-mid);
}
.training-section-head::before { content: ''; width: 12px; height: 2px; background: var(--red); display: inline-block; flex-shrink: 0; }

.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px,1fr));
    gap: 0;
}
.course-row {
    display: flex; align-items: center; gap: .85rem;
    padding: .7rem 1.1rem;
    border-bottom: 1px solid var(--grey-mid);
    border-right: 1px solid var(--grey-mid);
    cursor: pointer; transition: background .1s;
    user-select: none;
}
.course-row:hover { background: var(--navy-faint); }
.course-row:has(input:checked) { background: #f0f5ff; }
.course-row input[type="checkbox"] {
    width: 15px; height: 15px; accent-color: var(--navy);
    flex-shrink: 0; cursor: pointer;
    padding: 0; border: none; background: none; box-shadow: none;
}
.course-hex-mini {
    width: 32px; height: 32px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.course-hex-mini svg { width: 32px; height: 32px; }
.course-info { flex: 1; min-width: 0; }
.course-info-name { font-size: 12px; font-weight: bold; color: var(--text); line-height: 1.2; }
.course-info-desc { font-size: 11px; color: var(--text-muted); margin-top: 1px; line-height: 1.4; }
.course-completed-badge {
    font-size: 9px; font-weight: bold; padding: 1px 6px;
    background: var(--green-bg); border: 1px solid #b8ddc9; color: var(--green);
    text-transform: uppercase; letter-spacing: .05em; flex-shrink: 0;
    display: none;
}
.course-row:has(input:checked) .course-completed-badge { display: inline-flex; }

.training-progress-strip {
    display: flex; align-items: center; gap: .75rem;
    padding: .65rem 1.1rem;
    background: var(--navy-faint); border-bottom: 1px solid var(--grey-mid);
}
.tps-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); flex-shrink: 0; }
.tps-track { flex: 1; height: 5px; background: var(--grey-mid); overflow: hidden; }
.tps-fill { height: 100%; background: var(--navy); }
.tps-count { font-size: 11px; font-weight: bold; color: var(--navy); flex-shrink: 0; font-family: monospace; }
</style>

@php
$completedIds = is_array($user->completed_course_ids)
    ? $user->completed_course_ids
    : (json_decode($user->completed_course_ids ?? '[]', true) ?? []);

$allCourses = [
    'Tier Progression' => [
        'colour' => '#003366', 'courses' => [
            ['id'=>1,  'num'=>'T1', 'label'=>'Operator',             'desc'=>'RAYNET Basics: mission, Ofcom regs, message precedence'],
            ['id'=>2,  'num'=>'T2', 'label'=>'Checkpoint Supervisor','desc'=>'Checkpoint & event coordination. Prereq: Operator'],
            ['id'=>3,  'num'=>'T3', 'label'=>'Net Controller',       'desc'=>'HF/VHF net control. Prereq: Checkpoint Supervisor'],
            ['id'=>4,  'num'=>'T4', 'label'=>'Event Manager',        'desc'=>'Full event management. Prereq: Net Controller'],
            ['id'=>5,  'num'=>'T5', 'label'=>'Response Manager',     'desc'=>'Group-level response lead. Prereq: Event Manager'],
        ]
    ],
    'Technical Specialisms' => [
        'colour' => '#5b21b6', 'courses' => [
            ['id'=>101, 'num'=>'T1', 'label'=>'Power Systems',  'desc'=>'Battery, generator & solar power for ops.'],
            ['id'=>102, 'num'=>'T2', 'label'=>'Digital Modes',  'desc'=>'DMR, D-STAR, Fusion & APRS. Ofcom rules.'],
        ]
    ],
    'Operational Specialisms' => [
        'colour' => '#0f766e', 'courses' => [
            ['id'=>111, 'num'=>'O1', 'label'=>'Mapping',          'desc'=>'OS grid references, what3words, GIS basics.'],
            ['id'=>112, 'num'=>'O2', 'label'=>'Severe Weather',   'desc'=>'Storm ops, flood deployment, welfare.'],
            ['id'=>113, 'num'=>'O3', 'label'=>'First Aid Comms',  'desc'=>'Coordinating comms with medical teams.'],
            ['id'=>114, 'num'=>'O4', 'label'=>'Marathon Ops',     'desc'=>'Large event management & checkpoint liaison.'],
            ['id'=>115, 'num'=>'O5', 'label'=>'Air Support',      'desc'=>'Comms in support of air operations.'],
            ['id'=>116, 'num'=>'O6', 'label'=>'Water Ops',        'desc'=>'Flood, canal & coastal operations.'],
        ]
    ],
    'Administrative Specialisms' => [
        'colour' => '#be185d', 'courses' => [
            ['id'=>121, 'num'=>'A1', 'label'=>'GDPR',            'desc'=>'Data protection for RAYNET volunteers.'],
            ['id'=>122, 'num'=>'A2', 'label'=>'Media Liaison',   'desc'=>'Press, social media & public communications.'],
            ['id'=>123, 'num'=>'A3', 'label'=>'Safeguarding',    'desc'=>'Protecting vulnerable persons during ops.'],
            ['id'=>124, 'num'=>'A4', 'label'=>'No Secret Codes', 'desc'=>'Ofcom rules: plain language only on amateur bands.'],
        ]
    ],
    'Additional Knowledge' => [
        'colour' => '#374151', 'courses' => [
            ['id'=>201, 'num'=>'K1', 'label'=>'Antennas', 'desc'=>'Practical antenna theory & field erection.'],
            ['id'=>202, 'num'=>'K2', 'label'=>'NVIS',     'desc'=>'Near Vertical Incidence Skywave. Ofcom notes.'],
        ]
    ],
];

$totalCourses    = collect($allCourses)->flatMap(fn($s) => $s['courses'])->count();
$completedCount  = collect($allCourses)->flatMap(fn($s) => $s['courses'])->filter(fn($c) => in_array($c['id'], $completedIds))->count();
$pct             = $totalCourses > 0 ? round(($completedCount / $totalCourses) * 100) : 0;
@endphp

<form method="POST" action="{{ route('admin.users.update', $user->id) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="_section" value="training">

    <div class="card fade-in">
        <div class="card-head">
            <div class="card-icon">🏅</div>
            <div>
                <div class="card-title">Training Completion</div>
                <div class="card-sub">Tick courses the member has completed. Will be automated via Moodle in future.</div>
            </div>
            <div class="card-head-right">{{ $completedCount }}/{{ $totalCourses }} completed</div>
        </div>

        {{-- Progress strip --}}
        <div class="training-progress-strip">
            <span class="tps-label">Progress</span>
            <div class="tps-track"><div class="tps-fill" style="width:{{ $pct }}%;"></div></div>
            <span class="tps-count">{{ $completedCount }}/{{ $totalCourses }}</span>
        </div>

        @foreach ($allCourses as $sectionName => $section)
        <div class="training-section-head">{{ $sectionName }}</div>
        <div class="course-grid">
            @foreach ($section['courses'] as $course)
            @php $done = in_array($course['id'], $completedIds); @endphp
            <label class="course-row">
                <input type="checkbox"
                       name="completed_course_ids[]"
                       value="{{ $course['id'] }}"
                       {{ $done ? 'checked' : '' }}>
                <div class="course-hex-mini">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="16,2 29,9 29,23 16,30 3,23 3,9"
                                 fill="{{ $done ? $section['colour'] : '#dde2e8' }}"
                                 stroke="{{ $done ? $section['colour'] : '#c8d4e0' }}"
                                 stroke-width="1.5"/>
                        @if($done)
                        <polygon points="16,2.5 28,9 23,2.5" fill="rgba(255,255,255,.12)" stroke="none"/>
                        @endif
                        <text x="16" y="20" text-anchor="middle"
                              font-family="Arial,sans-serif" font-size="8" font-weight="bold"
                              fill="{{ $done ? '#fff' : 'rgba(0,0,0,.2)' }}">{{ $course['num'] }}</text>
                    </svg>
                </div>
                <div class="course-info">
                    <div class="course-info-name">{{ $course['label'] }}</div>
                    <div class="course-info-desc">{{ $course['desc'] }}</div>
                </div>
                <span class="course-completed-badge">✓ Done</span>
            </label>
            @endforeach
        </div>
        @endforeach

        <div class="card-footer">
            <div class="footer-meta">
                Changes take effect immediately on the member's profile page.
                Future Moodle integration will update these automatically on course completion.
            </div>
            <button type="submit" class="btn btn-primary">✓ Save Training Records</button>
        </div>
    </div>

</form>

<div class="info-note" style="margin-top:1rem;">
    ℹ Course IDs are stable — when Moodle integration is added, completed course IDs will be written here automatically on pass. Manual overrides will remain possible for legacy completions.
</div>

</div>{{-- /tab-training --}}

    {{-- ════════════════════════════════
         TAB: SESSIONS
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-sessions">

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">🖥</div>
                <div>
                    <div class="card-title">Active Sessions</div>
                    <div class="card-sub">All current login sessions for {{ $user->name }}</div>
                </div>
                <div class="card-head-right">{{ $parsedSessions->count() }} {{ Str::plural('session', $parsedSessions->count()) }}</div>
            </div>

            @if ($parsedSessions->isEmpty())
                <div class="sessions-empty">
                    <div class="sessions-empty-icon">🖥</div>
                    <div class="sessions-empty-title">No active sessions</div>
                    <div class="sessions-empty-sub">{{ $user->name }} is not currently logged in on any device.</div>
                </div>
            @else
                <div class="session-grid">
                    @foreach ($parsedSessions as $sess)
                        <div class="session-card {{ $sess->cardClass }}">
                            <div class="session-card-head">
                                <div class="session-device-icon">{{ $sess->deviceIcon }}</div>
                                <div>
                                    <div class="session-device-name">{{ $sess->deviceType }}</div>
                                    <div class="session-device-sub">{{ $sess->browser }}</div>
                                </div>
                                @if ($sess->isCurrent)
                                    <div class="session-current-badge">Your session</div>
                                @endif
                            </div>
                            <div class="session-card-body">
                                <div class="session-meta-grid">
                                    <div class="session-meta-item">
                                        <div class="session-meta-label">IP Address</div>
                                        <div class="session-meta-val">@if(auth()->user()->isTemporaryAdmin() && !$isTemporaryGuest)<span class="pii-blur-text">●●●.●●●.●●●.●●●</span>@else{{ $sess->ip }}@endif</div>
                                    </div>
                                    <div class="session-meta-item">
                                        <div class="session-meta-label">Last Activity</div>
                                        <div class="session-meta-val normal">{{ $sess->lastActivity->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="session-meta-item">
                                        <div class="session-meta-label">Session ID</div>
                                        <div class="session-meta-val">{{ substr($sess->id, 0, 12) }}…</div>
                                    </div>
                                    <div class="session-meta-item">
                                        <div class="session-meta-label">Platform</div>
                                        <div class="session-meta-val normal">{{ $sess->deviceType }}</div>
                                    </div>
                                </div>
                                <div class="session-ua">{{ Str::limit($sess->ua, 120) }}</div>
                            </div>
                            <div class="session-card-foot">
                                <div class="session-age {{ $sess->ageClass }}">
                                    {{ ucfirst($sess->humanAgo) }}
                                </div>
                                @if (! $sess->isCurrent)
                                    <form method="POST"
                                          action="{{ route('admin.users.session.terminate', [$user->id, $sess->id]) }}"
                                          onsubmit="return confirm('Terminate this session? The user will be logged out on their next page load.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-red btn-sm">✕ Terminate</button>
                                    </form>
                                @else
                                    <span style="font-size:11px;color:var(--text-muted);font-style:italic;">Current admin session</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="sessions-action-strip">
                    <div class="sessions-action-meta">
                        {{ $parsedSessions->count() }} active {{ Str::plural('session', $parsedSessions->count()) }} ·
                        Oldest active: {{ $parsedSessions->last()->humanAgo }}
                    </div>
                    <form method="POST" action="{{ route('admin.users.force-logout', $user->id) }}"
                          onsubmit="return confirm('Terminate ALL sessions for {{ addslashes($user->name) }}? They will be logged out everywhere immediately.')">
                        @csrf
                        <button type="submit" class="btn btn-red btn-sm">⏏ Terminate All Sessions</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="info-note" style="margin-top:1rem;">
            ℹ Session data is pulled from the <code>sessions</code> table. Sessions expire automatically after the configured lifetime. "Terminate" removes the session row — the user is logged out on their next page load.
        </div>

    </div>{{-- /tab-sessions --}}


  {{-- ════════════════════════════════
         TAB: ACCESS
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-access">

        {{-- Current Role (read-only, managed via Role Management) --}}
        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">🎭</div>
                <div>
                    <div class="card-title">Assigned Role</div>
                    <div class="card-sub">Managed via Role Management — changes take effect immediately</div>
                </div>
                <div class="card-head-right">
                    <a href="{{ route('admin.users.roles') }}" class="btn btn-blue btn-sm">
                        ⚙ Manage Roles
                    </a>
                </div>
            </div>
            <div style="padding:1rem 1.1rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                @php
                    $userRole = $user->getRoleNames()->first() ?? 'member';
                    $roleColours = [
                        'super-admin' => ['bg'=>'#1e0040','border'=>'rgba(124,58,237,.4)','color'=>'#c4b5fd','icon'=>'★'],
                        'admin'       => ['bg'=>'var(--red-faint)','border'=>'rgba(200,16,46,.3)','color'=>'var(--red)','icon'=>'⚡'],
                        'committee'   => ['bg'=>'#fef3c7','border'=>'rgba(217,119,6,.3)','color'=>'#d97706','icon'=>'📊'],
                        'member'      => ['bg'=>'var(--green-bg)','border'=>'rgba(22,163,74,.3)','color'=>'var(--green)','icon'=>'👤'],
                    ];
                    $rc = $roleColours[$userRole] ?? $roleColours['member'];
                @endphp
                <div style="display:inline-flex;align-items:center;gap:.65rem;
                            padding:.6rem 1.1rem;
                            background:{{ $rc['bg'] }};
                            border:2px solid {{ $rc['border'] }};
                            color:{{ $rc['color'] }};">
                    <span style="font-size:18px;">{{ $rc['icon'] }}</span>
                    <div>
                        <div style="font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">
                            {{ $userRole }}
                        </div>
                        <div style="font-size:11px;opacity:.7;margin-top:1px;">
                            @switch($userRole)
                                @case('super-admin') Full platform control — bypasses all permission checks @break
                                @case('admin')       Admin panel, user management, LMS, events, settings @break
                                @case('committee')   Operational management, readiness, assets, exercises @break
                                @default             Standard member — training, members area, DMR network
                            @endswitch
                        </div>
                    </div>
                </div>
                <div style="font-size:12px;color:var(--text-muted);font-family:var(--font);">
                    To change this user's role, use the
                    <a href="{{ route('admin.users.roles') }}" style="color:var(--navy);font-weight:700;">
                        Role Management
                    </a> page.
                </div>
            </div>
            <div class="meta-row">
                @foreach($user->roles as $role)
                <div class="meta-item">
                    <div class="meta-label">Spatie Role</div>
                    <div class="meta-val">{{ $role->name }}</div>
                </div>
                @endforeach
                @foreach($user->permissions as $perm)
                <div class="meta-item">
                    <div class="meta-label">Direct Permission</div>
                    <div class="meta-val" style="font-size:11px;">{{ $perm->name }}</div>
                </div>
                @endforeach
                @if($user->permissions->isEmpty())
                <div class="meta-item">
                    <div class="meta-label">Direct Permissions</div>
                    <div class="meta-val" style="color:var(--grey-dark);">None (inherited from role)</div>
                </div>
                @endif
            </div>
        </div>
@if(config('raynet.dmr_enabled'))
<div class="card fade-in">
    <div class="card-head">
        <div class="card-icon">📡</div>
        <div>
            <div class="card-title">DMR Network Access</div>
            <div class="card-sub">Control access level to the {{ \App\Helpers\RaynetSetting::groupName() }} DMR Network</div>
        </div>
    </div>

    {{-- ── Full Dashboard ── --}}
    <div class="control-row">
        <div class="control-row-info">
            <div class="control-row-title">Full Dashboard Access</div>
            <div class="control-row-sub">
                SSO login to the full HBMon dashboard — live QSOs, peers, bridges, call log &amp; system info at <strong>m0kkn.dragon-net.pl:8010</strong>.
                Also grants access to the embedded portal network page.
                @if($user->hasDirectPermission('view dmr dashboard'))
                    <br><strong style="color:var(--green);">✓ Currently granted.</strong>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            @if($user->hasDirectPermission('view dmr dashboard'))
                <span style="background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);padding:3px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">
                    ✓ Granted
                </span>
                <form method="POST" action="{{ route('admin.users.dmr.revoke', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-red btn-sm"
                        onclick="return confirm('Revoke full DMR dashboard access for {{ addslashes($user->name) }}?')">
                        ✕ Revoke
                    </button>
                </form>
            @else
                <span style="background:var(--red-faint);border:1px solid rgba(200,16,46,.25);color:var(--red);padding:3px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">
                    ✗ Not Granted
                </span>
                <form method="POST" action="{{ route('admin.users.dmr.grant', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-green btn-sm">✓ Grant</button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Masters Only ── --}}
    <div class="control-row">
        <div class="control-row-info">
            <div class="control-row-title">Masters View Only</div>
            <div class="control-row-sub">
                Shows the embedded Network page on the portal — master system status only.
                No access to the external HBMon dashboard, last heard, or call log.
                @if($user->hasDirectPermission('view dmr masters'))
                    <br><strong style="color:var(--green);">✓ Currently granted.</strong>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            @if($user->hasDirectPermission('view dmr masters'))
                <span style="background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);padding:3px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">
                    ✓ Granted
                </span>
                <form method="POST" action="{{ route('admin.users.dmr.masters.revoke', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-red btn-sm"
                        onclick="return confirm('Revoke masters-only access for {{ addslashes($user->name) }}?')">
                        ✕ Revoke
                    </button>
                </form>
            @else
                <span style="background:var(--red-faint);border:1px solid rgba(200,16,46,.25);color:var(--red);padding:3px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">
                    ✗ Not Granted
                </span>
                <form method="POST" action="{{ route('admin.users.dmr.masters.grant', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-green btn-sm">✓ Grant</button>
                </form>
            @endif
        </div>
    </div>

    <div class="info-note">
        ℹ Full dashboard access implies masters access — no need to grant both. Sessions last 12 hours. Access changes take effect immediately.
    </div>
</div>
@endif
        {{-- Password reset flag --}}
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_section"             value="access">
            <input type="hidden" name="force_password_reset" value="0">

            <div class="card fade-in">
                <div class="card-head">
                    <div class="card-icon">🔑</div>
                    <div>
                        <div class="card-title">Password Settings</div>
                        <div class="card-sub">Force a password change on next login</div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label style="margin-bottom:.5rem;">Force password reset</label>
                        <label class="toggle-row">
                            <input type="checkbox" name="force_password_reset" value="1"
                                   {{ old('force_password_reset', $user->force_password_reset) ? 'checked' : '' }}>
                            <div>
                                <div class="toggle-label">Require reset on next login</div>
                                <div class="toggle-sub">Member must change their password before accessing the site</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="meta-item">
                        <div class="meta-label">Password last changed</div>
                        <div class="meta-val {{ $user->password_changed_at ? 'meta-green' : 'meta-amber' }}">
                            {{ $user->password_changed_at
                                ? \Carbon\Carbon::parse($user->password_changed_at)->format('d M Y H:i')
                                : 'Never changed' }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Reset flag</div>
                        <div class="meta-val {{ $user->force_password_reset ? 'meta-amber' : 'meta-green' }}">
                            {{ $user->force_password_reset ? '⚠ Reset required' : '✓ Not required' }}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div></div>
                    <button type="submit" class="btn btn-primary">✓ Save</button>
                </div>
            </div>
        </form>

        {{-- Danger zone --}}


        {{-- Convert to Full Member --}}
        @if($isTemporaryGuest)
        <div class="card fade-in" style="border:1px solid rgba(26,107,60,.3);border-left:4px solid #1a6b3c;">
            <div class="card-head" style="background:#eef7f2;border-bottom-color:rgba(26,107,60,.2);">
                <div class="card-icon" style="background:#d6ede3;border-color:rgba(26,107,60,.2);">👤</div>
                <div>
                    <div class="card-title" style="color:#1a6b3c;">Convert to Full Member</div>
                    <div class="card-sub">Upgrade this temporary guest to a permanent member account</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.convert-to-member', $user->id) }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field">
                        <label>Notes (internal)</label>
                        <textarea name="notes" rows="3" placeholder="Reason for converting to full member…"
                                  style="width:100%;border:1px solid var(--grey-mid);padding:.5rem .75rem;font-family:var(--font);font-size:13px;resize:vertical;">{{ $user->notes }}</textarea>
                    </div>
                </div>
                <div class="form-field" style="padding:0 1.1rem .75rem;">
                    <label class="toggle-row" style="cursor:pointer;">
                        <input type="checkbox" name="send_notification" value="1" checked
                               style="width:16px;height:16px;accent-color:var(--navy);flex-shrink:0;">
                        <div>
                            <div class="toggle-label">Send email notification to {{ $user->name }}</div>
                            <div class="toggle-sub">Informs them they now have full member access with no time limit</div>
                        </div>
                    </label>
                </div>
                <div class="card-footer" style="background:#eef7f2;border-top-color:rgba(26,107,60,.15);">
                    <div class="footer-meta" style="color:#1a6b3c;">
                        This will assign the <strong>member</strong> role, remove the temporary_guest role,
                        and clear the expiry date permanently.
                    </div>
                    <button type="submit" class="btn" style="background:#1a6b3c;border-color:#1a6b3c;color:#fff;"
                            onclick="return confirm('Convert {{ addslashes($user->name) }} to a full member?\n\nThis will remove their guest restrictions and expiry date.')">
                        👤 Convert to Member
                    </button>
                </div>
            </form>
        </div>
        @endif
        {{-- Convert to Temporary Guest --}}
        @if(!$isTemporaryGuest)
        <div class="card fade-in" style="border:1px solid rgba(180,83,9,.3);border-left:4px solid #b45309;">
            <div class="card-head" style="background:#fffbf0;border-bottom-color:rgba(180,83,9,.2);">
                <div class="card-icon" style="background:#fff3d6;border-color:rgba(180,83,9,.2);">⏱</div>
                <div>
                    <div class="card-title" style="color:#b45309;">Convert to Temporary Guest</div>
                    <div class="card-sub">Give this account time-limited read-only member access</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.convert-to-guest', $user->id) }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field">
                        <label style="margin-bottom:.5rem;">Set Expiry Date &amp; Time</label>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(1,'day')">1 Day</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(3,'day')">3 Days</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(1,'week')">1 Week</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(2,'week')">2 Weeks</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(1,'month')">1 Month</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setConvertExpiry(3,'month')">3 Months</button>
                        </div>
                        <input type="datetime-local" name="expires_at" id="convertExpiryInput"
                               style="width:100%;border:1px solid var(--grey-mid);padding:.5rem .75rem;font-family:var(--font);font-size:13px;outline:none;">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Leave blank for no expiry.</div>
                    </div>
                    <div class="form-field">
                        <label>Notes (internal)</label>
                        <textarea name="notes" rows="3" placeholder="Reason for temporary guest conversion…"
                                  style="width:100%;border:1px solid var(--grey-mid);padding:.5rem .75rem;font-family:var(--font);font-size:13px;resize:vertical;">{{ $user->notes }}</textarea>
                    </div>
                </div>
                <div class="form-field" style="padding:0 1.1rem .75rem;">
                    <label class="toggle-row" style="cursor:pointer;">
                        <input type="checkbox" name="send_notification" value="1" checked
                               style="width:16px;height:16px;accent-color:var(--navy);flex-shrink:0;">
                        <div>
                            <div class="toggle-label">Send email notification to {{ $user->name }}</div>
                            <div class="toggle-sub">Informs them their account has been converted to temporary guest access</div>
                        </div>
                    </label>
                </div>
                <div class="card-footer" style="background:#fffbf0;border-top-color:rgba(180,83,9,.15);">
                    <div class="footer-meta" style="color:#92400e;">
                        This will assign the <strong>temporary_guest</strong> role and remove all other roles.
                        The member will lose committee/admin access if they have it.
                    </div>
                    <button type="submit" class="btn" style="background:#b45309;border-color:#b45309;color:#fff;"
                            onclick="return confirm('Convert {{ addslashes($user->name) }} to a temporary guest?\n\nThis will remove their current role and assign temporary_guest access.')">
                        ⏱ Convert to Guest
                    </button>
                </div>
            </form>
        </div>
        @endif
        <div class="card danger-card fade-in">
            <div class="card-head">
                <div class="card-icon">⚠️</div>
                <div>
                    <div class="card-title">Danger Zone</div>
                    <div class="card-sub">Irreversible actions</div>
                </div>
            </div>
            <div class="control-row">
                <div class="control-row-info">
                    <div class="control-row-title">Delete member</div>
                    <div class="control-row-sub">
                        Permanently removes {{ $user->name }} and all their data. Cannot be undone.
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                      onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?\n\nThis cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red">✕ Delete Member</button>
                </form>
            </div>
        </div>

    </div>{{-- /tab-access --}}
    
    {{-- ════════════════════════════════
         TAB: ACCOUNT CONTROL
    ════════════════════════════════ --}}
    <div class="tab-pane" id="tab-control">

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">✉️</div>
                <div>
                    <div class="card-title">Email Verification</div>
                    <div class="card-sub">Manage email address verification status</div>
                </div>
            </div>
            @if ($isVerified)
                <div class="verify-verified">
                    ✓ Verified on @if($user->piiVisible()){{ $user->email_verified_at->format('d M Y \a\t H:i') }}@else<span style="filter:blur(3px);user-select:none;">●● ●●● ●●●● ●●:●●</span>@endif
                </div>
                <div class="control-row">
                    <div class="control-row-info">
                        <div class="control-row-title">Resend verification email</div>
                        <div class="control-row-sub">Sends a fresh signed link to <strong>@if(auth()->user()->isTemporaryAdmin() && !$isTemporaryGuest)<span class="pii-blur-text">●●●●●●●●●@●●●●●●.●●●</span>@else{{ $user->email }}@endif</strong>.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.send-verification', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-blue">✉ Resend link</button>
                    </form>
                </div>
            @else
                <div class="verify-unverified">
                    ⚠ Email address has not been verified
                </div>
                <div class="control-row">
                    <div class="control-row-info">
                        <div class="control-row-title">Mark as verified</div>
                        <div class="control-row-sub">Manually sets <code>email_verified_at</code> to now.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.verify-email', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-green">✓ Mark verified</button>
                    </form>
                </div>
                <div class="control-row">
                    <div class="control-row-info">
                        <div class="control-row-title">Send verification email</div>
                        <div class="control-row-sub">Sends a signed link to <strong>@if(auth()->user()->isTemporaryAdmin() && !$isTemporaryGuest)<span class="pii-blur-text">●●●●●●●●●@●●●●●●.●●●</span>@else{{ $user->email }}@endif</strong>.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.send-verification', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-blue">✉ Send link</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">🖥️</div>
                <div>
                    <div class="card-title">Session Control</div>
                    <div class="card-sub">Manage active login sessions</div>
                </div>
            </div>
            <div class="control-row">
                <div class="control-row-info">
                    <div class="control-row-title">Force logout</div>
                    <div class="control-row-sub">Immediately invalidates all active sessions. The member will be logged out on their next page load.</div>
                </div>
                <form method="POST" action="{{ route('admin.users.force-logout', $user->id) }}"
                      onsubmit="return confirm('Force {{ addslashes($user->name) }} out of all active sessions?');">
                    @csrf
                    <button type="submit" class="btn btn-amber">⏏ Force Logout</button>
                </form>
            </div>
            <div class="control-row">
                <div class="control-row-info">
                    <div class="control-row-title">Force password reset</div>
                    <div class="control-row-sub">
                        Flags the account and kills active sessions.
                        @if ($user->force_password_reset)
                            <br><strong>⚠ This flag is currently active.</strong>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    @if (! $user->force_password_reset)
                        <form method="POST" action="{{ route('admin.users.force-password-reset', $user->id) }}"
                              onsubmit="return confirm('Force {{ addslashes($user->name) }} to reset their password?');">
                            @csrf
                            <button type="submit" class="btn btn-amber">🔑 Force Reset</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.clear-password-reset', $user->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-green">✓ Clear Flag</button>
                        </form>
                    @endif
                </div>
            </div>
            @if (! $user->is_admin)
            <div class="control-row">
                <div class="control-row-info">
                    <div class="control-row-title">Impersonate member</div>
                    <div class="control-row-sub">Browse the site exactly as this member sees it.</div>
                </div>
                @if ($isSuspended)
                    <span style="font-size:11px;color:var(--text-muted);font-style:italic;">Unavailable — account suspended</span>
                @else
                    <form method="POST" action="{{ route('admin.impersonate', $user->id) }}"
                          onsubmit="return confirm('Log in as {{ addslashes($user->name) }}?\n\nUse the orange banner to return.');">
                        @csrf
                        <button type="submit" class="btn btn-orange">👤 Impersonate</button>
                    </form>
                @endif
            </div>
            @endif
        </div>

        <div class="card {{ $isSuspended ? 'warning-card' : '' }} fade-in">
            <div class="card-head">
                <div class="card-icon">🔒</div>
                <div>
                    <div class="card-title">Account Suspension</div>
                    <div class="card-sub">Block access with a custom message</div>
                </div>
            </div>
            @if ($isSuspended)
                <div class="suspension-active">
                    🔒 Suspended since {{ \Carbon\Carbon::parse($user->suspended_at)->format('d M Y H:i') }}
                    @if ($user->suspension_message)
                        — <span>{{ $user->suspension_message }}</span>
                    @endif
                </div>
                <div class="control-row">
                    <div class="control-row-info">
                        <div class="control-row-title">Reinstate account</div>
                        <div class="control-row-sub">Lifts the suspension immediately.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.unsuspend', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-green">✓ Lift Suspension</button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}"
                      onsubmit="return confirm('Suspend {{ addslashes($user->name) }}? They will be locked out immediately.');">
                    @csrf
                    <div class="form-grid" style="padding-bottom:0;">
                        <div class="form-field full">
                            <label>Suspension message <small>(shown to member on login)</small></label>
                            <textarea name="suspension_message" rows="2"
                                      placeholder="e.g. Your account has been suspended pending a review."></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="footer-meta">The member will be logged out immediately and shown this message.</div>
                        <button type="submit" class="btn btn-red">🔒 Suspend Account</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="card fade-in">
            <div class="card-head">
                <div class="card-icon">📩</div>
                <div>
                    <div class="card-title">Admin Message</div>
                    <div class="card-sub">Send a one-time notice to this member</div>
                </div>
            </div>
            @if ($user->admin_message)
                <div class="message-active">
                    📩 Message pending — <span>{{ $user->admin_message }}</span>
                </div>
                <div class="control-row">
                    <div class="control-row-info">
                        <div class="control-row-title">Message is queued</div>
                        <div class="control-row-sub">Appears as a blue banner until dismissed.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.message.clear', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">✕ Clear message</button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('admin.users.message.send', $user->id) }}">
                    @csrf
                    <div class="form-grid" style="padding-bottom:0;">
                        <div class="form-field full">
                            <label>Message <small>(max 1000 characters)</small></label>
                            <textarea name="admin_message" rows="2" maxlength="1000"
                                      placeholder="e.g. Please update your contact details." required></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="footer-meta">Appears as a blue banner until the member dismisses it.</div>
                        <button type="submit" class="btn btn-primary">📩 Send Message</button>
                    </div>
                </form>
            @endif
        </div>

    </div>{{-- /tab-control --}}

</div>{{-- /wrap --}}


{{-- Edit log entry modal --}}
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-head-title">Edit Log Entry</div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="editLogForm" method="POST">
            @csrf @method('PATCH')
            <div class="modal-body">
                <div class="form-field">
                    <label>Event name <small>(optional)</small></label>
                    <input type="text" name="event_name" id="modal_event_name" placeholder="e.g. Marathon support">
                </div>
                <div class="form-field">
                    <label>Event date</label>
                    <input type="date" name="event_date" id="modal_event_date" required>
                </div>
                <div class="form-field">
                    <label>Hours</label>
                    <input type="number" name="hours" id="modal_hours" min="0" max="24" step="0.5" required>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">✓ Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const btns  = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    const init  = '{{ $activeTab }}';

    function activate(name) {
        btns.forEach(b  => b.classList.toggle('active', b.dataset.tab === name));
        panes.forEach(p => p.classList.toggle('active', p.id === 'tab-' + name));
        history.replaceState(null, '', '#' + name);
    }

    btns.forEach(b => b.addEventListener('click', () => activate(b.dataset.tab)));
    const hash = location.hash.replace('#', '');
    activate(hash && document.getElementById('tab-' + hash) ? hash : init);
})();

const baseUpdateUrl = "{{ rtrim(url('admin/users/' . $user->id . '/activity/log'), '/') }}";

function openEditModal(logId, name, date, hours) {
    document.getElementById('modal_event_name').value = name;
    document.getElementById('modal_event_date').value = date;
    document.getElementById('modal_hours').value      = hours;
    document.getElementById('editLogForm').action     = baseUpdateUrl + '/' + logId;
    document.getElementById('editModal').classList.add('open');
}
function closeModal() {
    document.getElementById('editModal').classList.remove('open');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
function setConvertExpiry(amount, unit) {
    var d = new Date();
    if (unit === 'day')   d.setDate(d.getDate() + amount);
    if (unit === 'week')  d.setDate(d.getDate() + amount * 7);
    if (unit === 'month') d.setMonth(d.getMonth() + amount);
    var pad = n => String(n).padStart(2, '0');
    document.getElementById('convertExpiryInput').value =
        d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) +
        'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
</script>

@endsection