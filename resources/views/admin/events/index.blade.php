@extends('layouts.admin')

@section('title', 'Manage Events')

@section('content')

<style>
/* ─── RAYNET BRAND TOKENS ─────────────────────────────────────────────────
   Navy Blue  #003366 · White #FFFFFF · Red #C8102E · Light Grey #F2F2F2
   Font: Arial / Helvetica Neue throughout
──────────────────────────────────────────────────────────────────────────── */
:root {
    --navy:        #003366;
    --navy-mid:    #004080;
    --navy-faint:  #e8eef5;
    --red:         #C8102E;
    --red-faint:   #fdf0f2;
    --white:       #FFFFFF;
    --grey:        #F2F2F2;
    --grey-mid:    #dde2e8;
    --grey-dark:   #9aa3ae;
    --text:        #001f40;
    --text-mid:    #2d4a6b;
    --text-muted:  #6b7f96;
    --green:       #1a6b3c;
    --green-bg:    #eef7f2;
    --green-border:#b8ddc9;
    --amber:       #8a5c00;
    --amber-bg:    #fef9ec;
    --amber-border:#e8c96a;
    --font: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-sm: 0 1px 3px rgba(0,51,102,.09);
    --shadow-md: 0 4px 16px rgba(0,51,102,.13);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--grey); color: var(--text); font-family: var(--font); font-size: 14px; min-height: 100vh; }

/* ─── HEADER ─── */
.rn-header {
    background: var(--navy); border-bottom: 3px solid var(--red);
    position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 12px rgba(0,0,0,.28);
}
.rn-header-inner {
    max-width: 1300px; margin: 0 auto; padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.rn-brand { display: flex; align-items: center; gap: .85rem; padding: .7rem 0; }
.rn-logo { background: var(--red); width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rn-logo span { font-size: 9px; font-weight: bold; color: var(--white); letter-spacing: .05em; text-align: center; line-height: 1.2; text-transform: uppercase; }
.rn-org { font-size: 13px; font-weight: bold; color: var(--white); letter-spacing: .04em; text-transform: uppercase; }
.rn-sub { font-size: 10px; color: rgba(255,255,255,.45); margin-top: 1px; text-transform: uppercase; letter-spacing: .06em; }
.rn-nav { display: flex; align-items: center; gap: .45rem; }
.rn-back {
    font-size: 11px; font-weight: bold; color: rgba(255,255,255,.75); text-decoration: none;
    border: 1px solid rgba(255,255,255,.2); padding: .32rem .85rem; transition: all .15s;
    white-space: nowrap; letter-spacing: .04em;
}
.rn-back:hover { background: rgba(255,255,255,.1); color: var(--white); border-color: rgba(255,255,255,.4); }
.rn-logout {
    font-size: 11px; font-weight: bold; color: rgba(255,255,255,.65);
    background: transparent; border: 1px solid rgba(200,16,46,.4); padding: .32rem .85rem;
    cursor: pointer; transition: all .15s; font-family: var(--font);
    white-space: nowrap; letter-spacing: .04em;
}
.rn-logout:hover { background: rgba(200,16,46,.15); color: var(--white); border-color: var(--red); }

/* ─── PAGE BAND ─── */
.page-band { background: var(--white); border-bottom: 1px solid var(--grey-mid); }
.page-band-inner {
    max-width: 1300px; margin: 0 auto; padding: 1.25rem 1.5rem;
    display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.page-eyebrow {
    font-size: 10px; font-weight: bold; color: var(--red); text-transform: uppercase;
    letter-spacing: .18em; margin-bottom: .3rem; display: flex; align-items: center; gap: .4rem;
}
.page-eyebrow::before { content: ''; width: 12px; height: 2px; background: var(--red); display: inline-block; }
.page-title { font-size: 21px; font-weight: bold; color: var(--navy); line-height: 1; }
.page-desc  { font-size: 12px; color: var(--text-muted); margin-top: .35rem; }
.page-band-actions { display: flex; align-items: center; gap: .45rem; flex-shrink: 0; }

/* ─── WRAP ─── */
.wrap { max-width: 1300px; margin: 0 auto; padding: 1.5rem 1.5rem 5rem; }

/* ─── ALERTS ─── */
.alert-success {
    display: flex; align-items: center; gap: .55rem; margin-bottom: 1.25rem;
    padding: .6rem .95rem; background: var(--green-bg);
    border: 1px solid var(--green-border); border-left: 3px solid var(--green);
    font-size: 13px; font-weight: bold; color: var(--green);
}
.alert-error {
    margin: .8rem 1.25rem 0; padding: .55rem .85rem;
    background: var(--red-faint); border: 1px solid rgba(200,16,46,.2); border-left: 3px solid var(--red);
    font-size: 12px; font-weight: bold; color: var(--red);
}

/* ─── UTILITY LINKS ─── */
.util-link {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .35rem .85rem; font-size: 11px; font-weight: bold;
    text-decoration: none; border: 1px solid; transition: all .15s;
    text-transform: uppercase; letter-spacing: .04em;
}
.util-navy  { color: var(--navy); border-color: rgba(0,51,102,.25); background: var(--navy-faint); }
.util-navy:hover  { background: #d0ddf0; border-color: var(--navy); }
.util-green { color: var(--green); border-color: var(--green-border); background: var(--green-bg); }
.util-green:hover { background: #d5ece0; border-color: var(--green); }

/* ─── BUTTONS ─── */
.btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .44rem 1.1rem; border: 1px solid; font-family: var(--font);
    font-size: 12px; font-weight: bold; cursor: pointer; transition: all .12s;
    white-space: nowrap; text-transform: uppercase; letter-spacing: .05em; text-decoration: none;
}
.btn-primary { background: var(--navy); border-color: var(--navy); color: var(--white); }
.btn-primary:hover { background: var(--navy-mid); }
.btn-green   { background: var(--green-bg); border-color: var(--green-border); color: var(--green); }
.btn-green:hover { background: #d5ece0; border-color: var(--green); }
.btn-amber   { background: var(--amber-bg); border-color: var(--amber-border); color: var(--amber); }
.btn-amber:hover { background: #fdeec5; border-color: #c8a030; }
.btn-ghost   { background: transparent; border-color: var(--grey-mid); color: var(--text-mid); }
.btn-ghost:hover { border-color: var(--navy); color: var(--navy); background: var(--navy-faint); }

/* ─── FORM CARD ─── */
.form-card {
    background: var(--white); border: 1px solid var(--grey-mid);
    border-top: 3px solid var(--navy); box-shadow: var(--shadow-sm); margin-bottom: 2rem;
}
.form-head {
    padding: .85rem 1.25rem; border-bottom: 1px solid var(--grey-mid); background: #fafbfc;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.form-head-title { font-size: 12px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; }
.form-head-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.form-body {
    padding: 1.25rem; display: grid; gap: .9rem;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
.form-field { display: flex; flex-direction: column; gap: .28rem; }
.form-field.full { grid-column: 1 / -1; }
.form-field label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); }
.form-field input,
.form-field select,
.form-field textarea {
    background: var(--white); border: 1px solid var(--grey-mid);
    padding: .48rem .75rem; color: var(--text); font-family: var(--font);
    font-size: 13px; outline: none; width: 100%; transition: border-color .15s, box-shadow .15s;
}
.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.07); }
.form-field input::placeholder,
.form-field textarea::placeholder { color: var(--grey-dark); }
.form-field textarea { resize: vertical; min-height: 76px; }
.form-footer {
    padding: .85rem 1.25rem; border-top: 1px solid var(--grey-mid);
    background: #fafbfc; display: flex; align-items: center; gap: .65rem;
}

/* ─── PRIVATE EVENT TOGGLE ─── */
.private-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .05em; padding: 2px 8px;
    background: var(--red-faint); border: 1px solid rgba(200,16,46,.25); color: var(--red);
    white-space: nowrap;
}
.form-privacy-row {
    grid-column: 1 / -1;
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .85rem 1rem;
    background: var(--red-faint); border: 1px solid rgba(200,16,46,.18);
    border-left: 3px solid var(--red);
}
.form-privacy-row input[type="checkbox"] {
    width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0;
    accent-color: var(--red); cursor: pointer;
}
.form-privacy-label { cursor: pointer; user-select: none; }
.form-privacy-label strong { color: var(--red); font-size: 13px; display: block; margin-bottom: 2px; }
.form-privacy-label span  { font-size: 11px; color: var(--text-muted); line-height: 1.5; }

/* ─── SECTION HEADER ─── */
.section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;
}
.section-left { display: flex; align-items: center; gap: .65rem; }
.section-title { font-size: 13px; font-weight: bold; color: var(--navy); text-transform: uppercase; letter-spacing: .06em; }
.count-badge {
    font-size: 11px; color: var(--text-muted); font-weight: bold;
    background: var(--white); border: 1px solid var(--grey-mid); padding: 1px 8px;
}
.search-wrap { position: relative; }
.search-wrap input {
    background: var(--white); border: 1px solid var(--grey-mid);
    padding: .36rem .85rem .36rem 2rem; color: var(--text);
    font-family: var(--font); font-size: 12px; width: 230px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.search-wrap input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.07); }
.search-wrap input::placeholder { color: var(--grey-dark); }
.search-icon { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: var(--grey-dark); font-size: 13px; pointer-events: none; }

/* ─── TABLE CARD ─── */
.table-card { background: var(--white); border: 1px solid var(--grey-mid); box-shadow: var(--shadow-sm); overflow: hidden; }

table { width: 100%; border-collapse: collapse; font-size: 13px; }
thead { background: var(--navy); border-bottom: 2px solid var(--red); }
thead th {
    padding: .55rem 1rem; text-align: left;
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .12em; color: rgba(255,255,255,.7); white-space: nowrap;
}
thead th.th-actions { text-align: right; width: 142px; }
tbody tr { border-top: 1px solid var(--grey-mid); transition: background .1s; }
tbody tr:hover { background: var(--navy-faint); }
tbody tr.docs-open { background: #fef9ec; }
tbody tr.docs-panel-row { background: transparent !important; }
tbody tr.docs-panel-row:hover { background: transparent !important; }
td { padding: .72rem 1rem; vertical-align: middle; }
td.td-actions { text-align: right; white-space: nowrap; padding-right: .9rem; }

.cell-title { font-weight: bold; color: var(--text); font-size: 13px; }
.cell-when  { font-size: 12px; color: var(--text-mid); font-weight: bold; white-space: nowrap; }
.cell-loc   { font-size: 12px; color: var(--text-muted); }

.type-pill {
    display: inline-flex; align-items: center; padding: 2px 8px;
    font-size: 10px; font-weight: bold; border: 1px solid;
    text-transform: uppercase; letter-spacing: .04em; white-space: nowrap;
}
.doc-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 11px; font-weight: bold; padding: 2px 7px; white-space: nowrap;
}
.doc-badge.has  { background: var(--navy-faint); color: var(--navy); border: 1px solid rgba(0,51,102,.18); }
.doc-badge.none { background: var(--grey); color: var(--grey-dark); border: 1px solid var(--grey-mid); }

/* ─── ACTION GROUP — primary + overflow ─── */
.action-group { display: inline-flex; align-items: center; }

.act-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 28px; padding: 0 .7rem; margin-left: -1px;
    font-family: var(--font); font-size: 11px; font-weight: bold;
    text-transform: uppercase; letter-spacing: .04em;
    border: 1px solid var(--grey-mid); background: var(--white);
    color: var(--text-mid); text-decoration: none; cursor: pointer; transition: all .12s;
}
.act-btn:first-child { margin-left: 0; }
.act-btn:hover { background: var(--navy-faint); border-color: var(--navy); color: var(--navy); z-index: 1; position: relative; }

.act-more-wrap { position: relative; display: inline-flex; margin-left: -1px; }
.act-more-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 28px; width: 30px; font-size: 18px; line-height: 1; padding-bottom: 2px;
    border: 1px solid var(--grey-mid); background: var(--white);
    color: var(--text-muted); cursor: pointer; font-family: var(--font); transition: all .12s;
}
.act-more-btn:hover { background: var(--navy-faint); border-color: var(--navy); color: var(--navy); z-index: 1; position: relative; }
.act-more-btn.is-open { background: var(--navy); border-color: var(--navy); color: var(--white); z-index: 10; position: relative; }

.act-dropdown {
    display: none; position: absolute;
    background: var(--white); border: 1px solid var(--grey-mid);
    box-shadow: var(--shadow-md); min-width: 152px; z-index: 9999;
}
.act-dropdown.is-open { display: block; }

.dd-item {
    display: flex; align-items: center; gap: .5rem;
    padding: .55rem .9rem; font-size: 12px; font-weight: bold;
    color: var(--text-mid); text-decoration: none; cursor: pointer;
    border: none; background: none; font-family: var(--font); width: 100%;
    text-align: left; text-transform: uppercase; letter-spacing: .03em;
    transition: background .1s; white-space: nowrap;
}
.dd-item:hover       { background: var(--navy-faint); color: var(--navy); }
.dd-item.dd-team:hover  { background: var(--green-bg);  color: var(--green); }
.dd-item.dd-docs:hover  { background: var(--amber-bg);  color: var(--amber); }
.dd-item.dd-delete      { color: var(--red); }
.dd-item.dd-delete:hover { background: var(--red-faint); color: var(--red); }
.dd-icon { font-size: 12px; opacity: .85; }
.dd-count {
    margin-left: auto; font-size: 10px; background: var(--navy-faint); color: var(--navy);
    border: 1px solid rgba(0,51,102,.15); padding: 0 5px; line-height: 16px;
}
.dd-divider { height: 1px; background: var(--grey-mid); margin: .2rem 0; }

/* ─── INLINE DOCS PANEL ─── */
.docs-panel-row td { padding: 0 !important; border: none !important; }
.docs-panel {
    border-top: 2px solid var(--amber-border); background: var(--amber-bg);
    padding: 1.1rem 1.25rem; display: none;
}
.docs-panel.is-open { display: block; }
.dp-label {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .12em; color: var(--amber); margin-bottom: .9rem;
    display: flex; align-items: center; gap: .4rem;
}
.dp-label::before { content: ''; width: 10px; height: 2px; background: var(--amber); display: inline-block; }

.doc-upload-area {
    border: 2px dashed var(--amber-border); background: var(--white);
    padding: .9rem 1rem; margin-bottom: .9rem;
    display: flex; align-items: flex-end; gap: .85rem; flex-wrap: wrap;
}
.doc-upload-field { display: flex; flex-direction: column; gap: .28rem; flex: 1; min-width: 185px; }
.doc-upload-field > label {
    font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted);
}
.doc-upload-field input[type="file"] {
    background: var(--white); border: 1px solid var(--grey-mid);
    padding: .32rem .6rem; color: var(--text); font-family: var(--font); font-size: 12px; width: 100%;
}
.doc-upload-field input[type="text"] {
    background: var(--white); border: 1px solid var(--grey-mid);
    padding: .42rem .75rem; color: var(--text); font-family: var(--font);
    font-size: 13px; width: 100%; outline: none; transition: border-color .15s;
}
.doc-upload-field input[type="text"]:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,51,102,.07); }
.doc-upload-field input[type="text"]::placeholder { color: var(--grey-dark); }
.doc-hint { font-size: 10px; color: var(--text-muted); margin-top: 2px; }

.doc-list { display: flex; flex-direction: column; gap: .35rem; }
.doc-list-empty { font-size: 12px; color: var(--text-muted); font-style: italic; padding: .5rem 0; }
.doc-item {
    display: flex; align-items: center; gap: .7rem;
    background: var(--white); border: 1px solid var(--grey-mid); padding: .5rem .85rem;
}
.doc-item-icon { font-size: 17px; flex-shrink: 0; width: 26px; text-align: center; line-height: 1; }
.doc-item-info { flex: 1; min-width: 0; }
.doc-item-name {
    font-size: 13px; font-weight: bold; color: var(--navy);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none; display: block;
}
.doc-item-name:hover { text-decoration: underline; }
.doc-item-meta { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.doc-item-acts { display: flex; align-items: center; gap: .45rem; flex-shrink: 0; }
.doc-dl { font-size: 11px; font-weight: bold; color: var(--navy); text-decoration: none; text-transform: uppercase; letter-spacing: .04em; transition: opacity .12s; }
.doc-dl:hover { opacity: .65; }
.doc-rm { font-size: 11px; font-weight: bold; color: var(--red); background: none; border: none; cursor: pointer; padding: 0; text-transform: uppercase; letter-spacing: .04em; font-family: var(--font); transition: opacity .12s; }
.doc-rm:hover { opacity: .65; }

/* ─── MOBILE CARDS ─── */
.mobile-events { display: none; flex-direction: column; gap: .6rem; }

.m-card {
    background: var(--white); border: 1px solid var(--grey-mid);
    border-left: 3px solid var(--navy); box-shadow: var(--shadow-sm);
    overflow: hidden; transition: border-left-color .15s;
}
.m-card:hover      { border-left-color: var(--red); }
.m-card.docs-open  { border-left-color: var(--amber); }

.m-card-body { padding: .85rem 1rem; }
.m-card-top  { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; margin-bottom: .45rem; }
.m-card-title { font-weight: bold; color: var(--text); font-size: 14px; line-height: 1.3; flex: 1; min-width: 0; }
.m-card-meta  { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; font-size: 12px; color: var(--text-muted); }
.m-card-sep   { color: var(--grey-mid); }

.m-card-actions {
    display: flex; align-items: center; gap: .35rem; flex-wrap: wrap;
    padding: .6rem 1rem; border-top: 1px solid var(--grey-mid); background: #fafbfc;
}
.m-act {
    display: inline-flex; align-items: center; gap: .22rem;
    padding: .3rem .65rem; border: 1px solid; font-size: 11px; font-weight: bold;
    text-transform: uppercase; letter-spacing: .04em; text-decoration: none;
    cursor: pointer; font-family: var(--font); background: var(--white);
    transition: all .12s; white-space: nowrap;
}
.m-act-view   { color: var(--navy); border-color: rgba(0,51,102,.25); }
.m-act-view:hover  { background: var(--navy-faint); border-color: var(--navy); }
.m-act-edit   { color: var(--text-mid); border-color: var(--grey-mid); }
.m-act-edit:hover  { background: var(--navy-faint); color: var(--navy); border-color: var(--navy); }
.m-act-team   { color: var(--green); border-color: var(--green-border); background: var(--green-bg); }
.m-act-team:hover  { background: #d5ece0; border-color: var(--green); }
.m-act-docs   { color: var(--amber); border-color: var(--amber-border); background: var(--amber-bg); }
.m-act-docs:hover  { background: #fdeec5; border-color: #c8a030; }
.m-act-delete { color: var(--red); border-color: rgba(200,16,46,.25); }
.m-act-delete:hover { background: var(--red-faint); border-color: var(--red); }

.m-docs-panel {
    border-top: 2px solid var(--amber-border); background: var(--amber-bg);
    padding: 1rem; display: none;
}
.m-docs-panel.is-open { display: block; }

/* ─── PAGINATION ─── */
.pagination-wrap {
    padding: .65rem 1rem; border-top: 1px solid var(--grey-mid); background: #fafbfc;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.pagination-info { font-size: 11px; color: var(--text-muted); font-weight: bold; }
.page-links { display: flex; align-items: center; gap: .25rem; }
.page-link {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 28px; height: 28px; padding: 0 .35rem;
    font-family: var(--font); font-size: 11px; font-weight: bold;
    text-decoration: none; border: 1px solid var(--grey-mid);
    background: var(--white); color: var(--text-muted); transition: all .15s;
}
.page-link:hover  { border-color: var(--navy); color: var(--navy); }
.page-link.active { background: var(--navy); border-color: var(--navy); color: var(--white); }
.page-link.disabled { opacity: .35; pointer-events: none; }

/* ─── EMPTY STATE ─── */
.empty-state { padding: 3.5rem 1rem; text-align: center; }
.empty-icon  { font-size: 2.5rem; opacity: .14; margin-bottom: .75rem; }
.empty-text  { font-size: 13px; color: var(--text-muted); }

/* ─── ANIMATIONS ─── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }
.fade-in    { animation: fadeUp .28s ease both; }
.fade-in-d1 { animation-delay: .05s; }
.fade-in-d2 { animation-delay: .1s; }
.fade-in-d3 { animation-delay: .15s; }

/* ─── RESPONSIVE ─── */
@media (max-width: 860px) {
    .desktop-table { display: none; }
    .mobile-events { display: flex; }
    .page-band-actions { display: none; }
    .search-wrap input { width: 100%; }
    .section-header { flex-direction: column; align-items: stretch; }
    .section-left { justify-content: space-between; }
}

/* ─── EVENT MAP PICKER ─── */
.map-picker-section {
    grid-column: 1 / -1;
    border: 1px solid var(--grey-mid);
    background: var(--white);
}
.map-picker-header {
    padding: .5rem .85rem;
    background: var(--grey); border-bottom: 1px solid var(--grey-mid);
    display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
}
.map-picker-label {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: .1em; color: var(--text-muted);
}
.map-picker-tools { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
.map-tool-btn {
    font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
    padding: .25rem .65rem; border: 1px solid; cursor: pointer; font-family: var(--font);
    background: var(--white); transition: all .12s; white-space: nowrap;
}
.map-tool-btn.tool-pin    { color: var(--navy); border-color: rgba(0,51,102,.3); }
.map-tool-btn.tool-pin:hover    { background: var(--navy-faint); }
.map-tool-btn.tool-poly   { color: var(--green); border-color: var(--green-border); background: var(--green-bg); }
.map-tool-btn.tool-poly:hover   { background: #d5ece0; border-color: var(--green); }
.map-tool-btn.tool-clear  { color: var(--red); border-color: rgba(200,16,46,.25); }
.map-tool-btn.tool-clear:hover  { background: var(--red-faint); border-color: var(--red); }
.map-tool-btn.tool-active { outline: 2px solid currentColor; outline-offset: 1px; }
#event-map-picker { height: 320px; width: 100%; }
#map-fullscreen-overlay { display:none;position:fixed;inset:0;z-index:99999;background:#000;flex-direction:column; }
#map-fullscreen-overlay.active { display:flex; }
#map-fullscreen-overlay #event-map-picker { flex:1;height:calc(100vh - 44px) !important;width:100vw !important; }
#map-fullscreen-topbar { height:44px;background:#001f40;display:flex;align-items:center;padding:0 1rem;gap:.5rem;flex-shrink:0; }
.map-coord-row {
    display: flex; gap: .5rem; padding: .5rem .85rem;
    border-top: 1px solid var(--grey-mid); flex-wrap: wrap;
    background: #fafbfc;
}
.map-coord-field { display: flex; flex-direction: column; gap: .2rem; flex: 1; min-width: 120px; }
.map-coord-field label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); }
.map-coord-field input {
    border: 1px solid var(--grey-mid); padding: .32rem .5rem;
    font-family: var(--font); font-size: 12px; color: var(--text); outline: none;
    transition: border-color .15s;
}
.map-coord-field input:focus { border-color: var(--navy); }
.map-poly-status {
    font-size: 11px; color: var(--green); font-weight: bold;
    display: flex; align-items: center; gap: .35rem;
    padding: .35rem .85rem; background: var(--green-bg);
    border-top: 1px solid var(--green-border);
}

/* ─── ADDITIONAL MAP TOOL STATES ─── */
.map-tool-btn.tool-measure { color: #00897b; border-color: rgba(0,137,123,.3); background: #e0f2f1; }
.map-tool-btn.tool-measure:hover { background: #b2dfdb; border-color: #00897b; }
.map-tool-btn.tool-w3w { color: #e65c00; border-color: rgba(230,92,0,.3); background: #fff3e0; }
.map-tool-btn.tool-w3w:hover { background: #ffe0b2; border-color: #e65c00; }

/* ─── ELEVATION CHART ─── */
#elevation-panel { display:none; border-top:1px solid var(--grey-mid); background:#fafbfc; padding:.7rem .85rem; }
#elevation-panel.visible { display:block; }
.elevation-head { font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between; }
#elev-chart { width:100%; height:90px; display:block; }

/* ─── WEATHER BADGE ─── */
#weather-badge { display:none; padding:.4rem .85rem; background:linear-gradient(135deg,#e3f2fd,#e8f5e9); border-top:1px solid var(--grey-mid); font-size:12px; align-items:center; gap:.75rem; flex-wrap:wrap; }
#weather-badge.visible { display:flex; }

/* ─── MEASURE TOOLTIP ─── */
.measure-tooltip { background:#003366; color:#fff; font-size:11px; font-weight:bold; padding:3px 8px; border:none; white-space:nowrap; }

/* ─── RANGE RING LABEL ─── */
.range-label { background:rgba(0,51,102,.8); color:#fff; font-size:10px; font-weight:bold; padding:2px 6px; border:none; white-space:nowrap; border-radius:3px; }

/* ─── POI ROW ─── */
.poi-row {
    display: grid;
    grid-template-columns: 12px auto 1fr 1.4fr auto auto;
    align-items: center;
    gap: .4rem;
    padding: .4rem .85rem;
    border-bottom: 1px solid var(--grey-mid);
    background: var(--white);
    min-width: 0;
}
.poi-row:last-child { border-bottom: none; }
.poi-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.15); }
.poi-type-select {
    border: 1px solid var(--grey-mid); padding: .22rem .35rem; font-size: 11px;
    font-family: var(--font); color: var(--text); outline: none; background: var(--white);
    width: 100%; min-width: 0;
}
.poi-type-select:focus { border-color: var(--navy); }
.poi-name-input {
    width: 100%; min-width: 0; border: 1px solid var(--grey-mid); padding: .22rem .5rem;
    font-size: 12px; font-family: var(--font); color: var(--text); outline: none;
}
.poi-name-input:focus { border-color: var(--navy); }
.poi-desc-input {
    width: 100%; min-width: 0; border: 1px solid var(--grey-mid); padding: .22rem .5rem;
    font-size: 11px; font-family: var(--font); color: var(--text-muted); outline: none;
}
.poi-desc-input:focus { border-color: var(--navy); }
.poi-del {
    font-size: 13px; color: var(--red); background: none; border: none; cursor: pointer;
    padding: 0 .15rem; line-height: 1; flex-shrink: 0; font-family: var(--font);
}
.poi-del:hover { opacity: .7; }
.poi-locate {
    font-size: 14px; color: var(--navy); background: none; border: none;
    cursor: pointer; padding: 0 .15rem; font-family: var(--font); flex-shrink: 0;
}
.poi-locate:hover { opacity: .7; }

/* ─── ROUTE STATUS ─── */
.map-route-status {
    font-size: 11px; color: var(--navy); font-weight: bold;
    display: flex; align-items: center; gap: .35rem;
    padding: .35rem .85rem; background: var(--navy-faint);
    border-top: 1px solid rgba(0,51,102,.15);
}

/* ─── MASK — pointer-events must be off so clicks pass through to the map ─── */
.evt-mask-layer {
    pointer-events: none !important;
}
/* ─── RSVP PANEL ─── */
.rsvp-panel { border-top: 2px solid #b8ddc9; background: var(--green-bg); padding: 1.1rem 1.25rem; display: none; }
.rsvp-panel.is-open { display: block; }
.rsvp-summary { display: flex; gap: 1rem; margin-bottom: .85rem; flex-wrap: wrap; }
.rsvp-count { display: flex; flex-direction: column; align-items: center; padding: .5rem .85rem; background: var(--white); border: 1px solid var(--grey-mid); min-width: 72px; }
.rsvp-count-num { font-size: 22px; font-weight: bold; line-height: 1; }
.rsvp-count-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-top: 3px; }
.rsvp-list { display: flex; flex-direction: column; gap: .3rem; }
.rsvp-item { display: flex; align-items: center; gap: .75rem; background: var(--white); border: 1px solid var(--grey-mid); padding: .45rem .85rem; font-size: 12px; }
.rsvp-item-callsign { font-weight: bold; color: var(--navy); min-width: 70px; }
.rsvp-item-name { flex: 1; color: var(--text-muted); }
.rsvp-item-status { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; padding: 2px 7px; border: 1px solid; }
.rsvp-item-status.attending { color: var(--green); border-color: var(--green); background: var(--green-bg); }
.rsvp-item-status.maybe     { color: var(--amber); border-color: var(--amber-border); background: var(--amber-bg); }
.rsvp-item-status.declined  { color: var(--red); border-color: rgba(200,16,46,.25); background: var(--red-faint); }
.rsvp-item-note { font-size: 11px; color: var(--text-muted); font-style: italic; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rsvp-empty { font-size: 12px; color: var(--text-muted); font-style: italic; }

.m-rsvp-panel {
    border-top: 2px solid var(--green-border); background: var(--green-bg);
    padding: 1rem; display: none;
}
.m-rsvp-panel.is-open { display: block; }

</style>

{{-- ─── HEADER ─── --}}
<header class="rn-header fade-in">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Events</div>
            </div>
        </div>
        <nav class="rn-nav">
            <a href="{{ route('admin.dashboard') }}" class="rn-back">← Admin</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="rn-logout">⏻ Log out</button>
            </form>
        </nav>
    </div>
</header>

{{-- ─── PAGE BAND ─── --}}
<div class="page-band fade-in fade-in-d1">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Admin Panel</div>
            <h1 class="page-title">Manage Events</h1>
            <p class="page-desc">Events drive the group calendar and the "Next event" card on the home page.</p>
        </div>
        <div class="page-band-actions">
            <a href="{{ route('admin.events.export.csv') }}" class="util-link util-navy">↓ Export CSV</a>
            <a href="{{ route('admin.events.import') }}"     class="util-link util-green">↑ Import CSV</a>
        </div>
    </div>
</div>

<div class="wrap">

    @if (session('status'))
        <div class="alert-success fade-in">✓ {{ session('status') }}</div>
    @endif

    {{-- Mobile-only util strip --}}
    <div class="fade-in" style="display:flex;gap:.45rem;margin-bottom:1.1rem;flex-wrap:wrap;" id="mobileUtils">
        <a href="{{ route('admin.events.export.csv') }}" class="util-link util-navy">↓ Export CSV</a>
        <a href="{{ route('admin.events.import') }}"     class="util-link util-green">↑ Import CSV</a>
    </div>
    <style>@media(min-width:861px){#mobileUtils{display:none!important;}}</style>

    {{-- ─── ADD / EDIT FORM ─── --}}
    <div class="form-card fade-in fade-in-d2">
        <div class="form-head" id="form-card-toggle"
             style="cursor:pointer;user-select:none;"
             onclick="@if(!isset($editingEvent))toggleEventForm()@endif">
            <div>
                <div class="form-head-title">{{ isset($editingEvent) ? '✏  Edit Event' : '+  Add Event' }}</div>
                @if (isset($editingEvent))
                    <div class="form-head-sub">Editing: {{ $editingEvent->title }}</div>
                @else
                    <div class="form-head-sub" id="form-toggle-sub">New events appear on the calendar and members' hub immediately.</div>
                @endif
            </div>
            @if (isset($editingEvent))
                <a href="{{ route('admin.events') }}"
                   style="font-size:11px;font-weight:bold;color:var(--red);text-decoration:none;text-transform:uppercase;letter-spacing:.06em;">
                   ✕ Cancel
                </a>
            @else
                <span id="form-toggle-icon" style="font-size:1.1rem;color:var(--grey-dark);transition:transform .2s;">▼</span>
            @endif
        </div>

        @if ($errors->any())
            <div class="alert-error">⚠ {{ $errors->first() }}</div>
        @endif

        <form method="POST"
              action="{{ isset($editingEvent) ? route('admin.events.update', $editingEvent->id) : route('admin.events.store') }}">
            @csrf
            @if (isset($editingEvent)) @method('PUT') @endif

            <div id="event-form-body">
            <div class="form-body">
                <div class="form-field">
                    <label>Title *</label>
                    <input name="title" type="text"
                           value="{{ old('title', $editingEvent->title ?? '') }}"
                           placeholder="Monthly Net">
                </div>
                <div class="form-field">
                    <label>Location</label>
                    <input name="location" type="text"
                           value="{{ old('location', $editingEvent->location ?? '') }}"
                           placeholder="On-air / Clubroom">
                </div>
                <div class="form-field">
                    <label>Start (date &amp; time) *</label>
                    <input name="starts_at" type="datetime-local"
                           value="{{ old('starts_at', isset($editingEvent) ? $editingEvent->starts_at?->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="form-field">
                    <label>End (date &amp; time)</label>
                    <input name="ends_at" type="datetime-local"
                           value="{{ old('ends_at', isset($editingEvent) ? $editingEvent->ends_at?->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="form-field">
                    <label>Event type *</label>
                    <select name="event_type_id">
                        <option value="">— Select type —</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}"
                                {{ old('event_type_id', $editingEvent->event_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                {{ $_isTempAdmin && isset($type) && method_exists($type, 'piiVisible') && !$type->piiVisible() ? '●●●●●●●●●' : $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field full">
                    <label>Description <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(optional)</small></label>
                    <textarea name="description"
                              placeholder="Public description — shown to everyone…">{{ old('description', $editingEvent->description ?? '') }}</textarea>
                    <label style="display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:.85rem;margin-bottom:.3rem;">
                        Members Description <span style="font-weight:normal;font-style:italic;text-transform:none;letter-spacing:0;">— logged-in members only</span>
                    </label>
                    <textarea name="members_description"
                              placeholder="Additional details for logged-in members only (briefing notes, frequencies, grid refs etc.)…">{{ old('members_description', $editingEvent->members_description ?? '') }}</textarea>
                </div>


                <div class="form-field full">
                    <label>Event Origin</label>
                    <div style="display:flex;gap:.4rem;margin-bottom:.55rem;">
                        <label style="display:flex;align-items:center;gap:.35rem;padding:.3rem .75rem;border:1px solid var(--grey-mid);border-radius:999px;cursor:pointer;font-size:12px;font-weight:normal;text-transform:none;letter-spacing:0;white-space:nowrap;transition:all .12s;"
                               id="origin-own-lbl" onmouseover="this.style.borderColor='var(--navy)'" onmouseout="this.style.borderColor=''">
                            <input type="radio" name="event_origin" id="origin-own" value="own"
                                   onchange="toggleOrigin()"
                                   {{ old('event_origin', ($editingEvent->supporting_group ?? '') === '' || ($editingEvent->supporting_group ?? '') === '__OWN__' ? 'own' : 'supporting') === 'own' ? 'checked' : '' }}>
                            📡 Our own event
                        </label>
                        <label style="display:flex;align-items:center;gap:.35rem;padding:.3rem .75rem;border:1px solid var(--grey-mid);border-radius:999px;cursor:pointer;font-size:12px;font-weight:normal;text-transform:none;letter-spacing:0;white-space:nowrap;transition:all .12s;"
                               id="origin-sup-lbl" onmouseover="this.style.borderColor='var(--navy)'" onmouseout="this.style.borderColor=''">
                            <input type="radio" name="event_origin" id="origin-supporting" value="supporting"
                                   onchange="toggleOrigin()"
                                   {{ old('event_origin', ($editingEvent->supporting_group ?? '') !== '' && ($editingEvent->supporting_group ?? '') !== '__OWN__' ? 'supporting' : 'own') === 'supporting' ? 'checked' : '' }}>
                            🤝 Supporting another group
                        </label>
                    </div>
                    <div id="supporting-group-wrap" style="display:{{ old('event_origin', ($editingEvent->supporting_group ?? '') !== '' && ($editingEvent->supporting_group ?? '') !== '__OWN__' ? 'supporting' : 'own') === 'supporting' ? 'block' : 'none' }};">
                        <input name="supporting_group" type="text"
                               value="{{ old('supporting_group', (($editingEvent->supporting_group ?? '') !== '__OWN__') ? ($editingEvent->supporting_group ?? '') : '') }}"
                               placeholder="e.g. 10/NW/101 Cheshire RAYNET">
                        <small style="color:var(--grey-dark);font-size:11px;">Shown as a 🤝 badge on the homepage &amp; calendar</small>
                    </div>
                    {{-- Hidden field to send __OWN__ when our own event selected --}}
                    <input type="hidden" name="supporting_group" id="supporting-group-hidden" value="{{ old('supporting_group', $editingEvent->supporting_group ?? '') }}" {{ old('event_origin', ($editingEvent->supporting_group ?? '') !== '' ? 'supporting' : 'own') === 'supporting' ? 'disabled' : '' }}>
                </div>
                <script>
                function toggleOrigin() {
                    const isOwn = document.getElementById('origin-own').checked;
                    const wrap  = document.getElementById('supporting-group-wrap');
                    const hidden = document.getElementById('supporting-group-hidden');
                    const textInput = wrap.querySelector('input[type="text"]');
                    wrap.style.display = isOwn ? 'none' : 'block';
                    hidden.disabled  = !isOwn;
                    textInput.disabled = isOwn;
                    if (isOwn) hidden.value = '__OWN__';
                    else hidden.value = '';
                }
                toggleOrigin();
                </script>
              {{-- ─── PRIVATE EVENT TOGGLE ─── --}}
                <div class="form-privacy-row">
                    <input type="hidden" name="is_private" value="0">
                    <input type="checkbox" name="is_private" id="is_private" value="1"
                           {{ old('is_private', $editingEvent->is_private ?? false) ? 'checked' : '' }}>
                    <label class="form-privacy-label" for="is_private">
                        <strong>🔒 Private Event</strong>
                        <span>When enabled, event details (description, map, documents and action buttons) are only
                        visible to logged-in members. The event title, date and type will still appear on the
                        public calendar so members know it exists.</span>
                    </label>
                </div>

                {{-- Hidden fields for map data --}}
                <input type="hidden" name="event_lat"     id="event-lat"     value="{{ old('event_lat',     $editingEvent->event_lat     ?? '') }}">
                <input type="hidden" name="event_lng"     id="event-lng"     value="{{ old('event_lng',     $editingEvent->event_lng     ?? '') }}">
                @php
                    $existingPolygon = old('event_polygon');
                    if ($existingPolygon === null && isset($editingEvent) && $editingEvent->event_polygon) {
                        $existingPolygon = is_array($editingEvent->event_polygon)
                            ? json_encode($editingEvent->event_polygon)
                            : $editingEvent->event_polygon;
                    }
                @endphp
                <input type="hidden" name="event_polygon" id="event-polygon" value="{{ $existingPolygon ?? '' }}">
                <input type="hidden" name="event_polygon_name" id="event-polygon-name"
                       value="{{ old('event_polygon_name', $editingEvent->event_polygon_name ?? '') }}">
                @php
                    $existingRoute = old('event_route');
                    if ($existingRoute === null && isset($editingEvent) && ($editingEvent->event_route ?? null)) {
                        $existingRoute = is_array($editingEvent->event_route)
                            ? json_encode($editingEvent->event_route)
                            : $editingEvent->event_route;
                    }
                @endphp
                <input type="hidden" name="event_route" id="event-route" value="{{ $existingRoute ?? '' }}">
                @php
                    $existingPois = old('event_pois');
                    if ($existingPois === null && isset($editingEvent) && $editingEvent->event_pois) {
                        $existingPois = is_array($editingEvent->event_pois)
                            ? json_encode($editingEvent->event_pois)
                            : $editingEvent->event_pois;
                    }
                @endphp
                <input type="hidden" name="event_pois" id="event-pois" value="{{ $existingPois ?? '' }}">

                {{-- Map picker --}}
                <div class="form-field full map-picker-section">
                    <div class="map-picker-header">
                        <span class="map-picker-label">📍 Location Map — pin, polygon &amp; POIs</span>
                        <div class="map-picker-tools">
                            <button type="button" class="map-tool-btn tool-pin tool-active" id="tool-pin-btn"
                                    onclick="setMapTool('pin')">📍 Place Pin</button>
                            <button type="button" class="map-tool-btn tool-poly" id="tool-poly-btn"
                                    onclick="setMapTool('polygon')">✏ Draw Polygon</button>
                            <button type="button" class="map-tool-btn" id="tool-route-btn"
                                    style="color:#7c3aed;border-color:rgba(124,58,237,.3);background:#f5f3ff;"
                                    onclick="setMapTool('route')">〰 Draw Route</button>
                            <button type="button" id="route-finish-btn"
                                    style="display:none;color:#fff;background:#7c3aed;border-color:#7c3aed;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;padding:.25rem .65rem;border:1px solid;cursor:pointer;font-family:var(--font);"
                                    onclick="finishRoute()">✓ Finish Route</button>
                            <button type="button" class="map-tool-btn" id="tool-poi-btn"
                                    style="color:var(--red);border-color:rgba(200,16,46,.3);"
                                    onclick="setMapTool('poi')">🚩 Add POI</button>
                            <button type="button" class="map-tool-btn tool-clear"
                                    onclick="clearEventMap()">✕ Clear All</button>
                            <button type="button" class="map-tool-btn" style="color:var(--text-mid);border-color:var(--grey-mid);"
                                    onclick="toggleEventSat()" id="evt-sat-btn">🛰 Satellite</button>
                            <button type="button" class="map-tool-btn" id="tool-grid-btn"
                                    style="color:#1565c0;border-color:rgba(21,101,192,.3);"
                                    onclick="toggleOsGrid()">⊞ OS Grid</button>
                            <button type="button" class="map-tool-btn tool-measure" id="tool-measure-btn"
                                    onclick="setMapTool('measure')">📏 Measure</button>
                            <button type="button" class="map-tool-btn tool-w3w" id="tool-w3w-btn"
                                    onclick="toggleW3wMode()">/// W3W</button>
                            <button type="button" class="map-tool-btn" id="tool-weather-btn"
                                    style="color:#2e7d32;border-color:rgba(46,125,50,.3);"
                                    onclick="fetchEventWeather()">🌬 Forecast</button>
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;padding:.5rem .6rem;background:var(--grey);border-top:1px solid var(--grey-mid);border-bottom:1px solid var(--grey-mid);">
                        <div style="position:relative;flex:1;">
                            <span style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);font-size:13px;color:var(--grey-dark);pointer-events:none;">🔍</span>
                            <input type="text" id="map-location-search"
                                   placeholder="Search for a location to centre the map…"
                                   style="width:100%;padding:.45rem .75rem .45rem 2rem;border:1px solid var(--border);border-radius:4px;font-size:13px;font-family:var(--font);background:white;"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();mapLocationSearch();}">
                            <div id="map-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:white;border:1px solid var(--border);border-top:none;z-index:9999;max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.12);"></div>
                        </div>
                        <button type="button" onclick="mapLocationSearch()"
                                style="padding:.45rem .9rem;background:var(--navy);color:white;border:none;border-radius:4px;font-size:12px;font-weight:bold;font-family:var(--font);cursor:pointer;white-space:nowrap;">
                            Go
                        </button>
                    </div>
                    <div id="event-map-wrap" style="position:relative;">
                        <div id="event-map-picker"></div>
                        {{-- Floating toolbar --}}
                        <div id="map-float-toolbar" style="position:absolute;top:10px;right:10px;z-index:1000;display:flex;flex-direction:column;gap:4px;">
                            <button type="button" onclick="evtMapFullscreen()" title="Fullscreen"
                                    style="width:32px;height:32px;background:#fff;border:2px solid rgba(0,0,0,.25);border-radius:4px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 5px rgba(0,0,0,.2);">⛶</button>
                            <button type="button" onclick="evtMapSatToggle()" title="Satellite/Street" id="map-sat-btn"
                                    style="width:32px;height:32px;background:#fff;border:2px solid rgba(0,0,0,.25);border-radius:4px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 5px rgba(0,0,0,.2);">🛰</button>
                            <button type="button" onclick="evtMapLocate()" title="My Location"
                                    style="width:32px;height:32px;background:#fff;border:2px solid rgba(0,0,0,.25);border-radius:4px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 5px rgba(0,0,0,.2);">📍</button>
                            <button type="button" onclick="evtMapClearAll()" title="Clear all"
                                    style="width:32px;height:32px;background:#fff;border:2px solid rgba(0,0,0,.25);border-radius:4px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 5px rgba(0,0,0,.2);">🗑</button>
                        </div>
                    </div>
                    <div class="map-coord-row">
                        <div class="map-coord-field">
                            <label>Lat (pin)</label>
                            <input type="text" id="disp-evt-lat" placeholder="—" readonly
                                   style="background:var(--grey);cursor:default;">
                        </div>
                        <div class="map-coord-field">
                            <label>Lng (pin)</label>
                            <input type="text" id="disp-evt-lng" placeholder="—" readonly
                                   style="background:var(--grey);cursor:default;">
                        </div>
                        <div class="map-coord-field" style="flex:2;">
                            <label>Location name (auto-filled on pin · editable)</label>
                            <input type="text" id="disp-evt-loc" placeholder="Will auto-fill from pin…"
                                   oninput="document.querySelector('[name=location]').value=this.value">
                        </div>
                    </div>
                    <div class="map-poly-status" id="poly-status" style="display:none;">
                        ✓ <span id="poly-status-name">Site Boundary</span> — <span id="poly-vertex-count">0</span> vertices
                        <span style="font-size:10px;color:var(--green);opacity:.7;margin-left:.3rem;">(click boundary to rename/edit)</span>
                    </div>
                    <div class="map-route-status" id="route-status" style="display:none;">
                        〰 <span id="route-count-label">0 routes</span>
                        <span style="font-size:10px;opacity:.65;margin-left:.3rem;">(click a route to rename/edit/delete · draw another to add more)</span>
                        <button type="button" onclick="clearAllRoutes()"
                                style="margin-left:auto;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;color:var(--red);background:none;border:none;cursor:pointer;font-family:var(--font);">
                            ✕ Clear All Routes
                        </button>
                    </div>

                    {{-- Vertex-edit mode bar --}}
                    <div id="edit-mode-bar" style="display:none;padding:.5rem .85rem;background:#1a1a2e;border-top:2px solid #7c3aed;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
                        <span style="font-size:12px;font-weight:bold;color:#fff;">
                            ✏ Vertex edit mode — drag any point to reposition, click a vertex to delete it
                        </span>
                        <div style="display:flex;gap:.4rem;">
                            <button type="button" onclick="saveVertexEdit()"
                                    style="padding:.3rem .8rem;background:#7c3aed;border:none;color:#fff;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;font-family:var(--font);cursor:pointer;">
                                ✓ Save
                            </button>
                            <button type="button" onclick="cancelVertexEdit()"
                                    style="padding:.3rem .8rem;background:transparent;border:1px solid rgba(255,255,255,.3);color:rgba(255,255,255,.7);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;font-family:var(--font);cursor:pointer;">
                                ✕ Cancel
                            </button>
                        </div>
                    </div>

                    {{-- POI mode instruction banner --}}
                    <div id="poi-mode-hint" style="display:none;padding:.45rem .85rem;background:var(--red-faint);border-top:1px solid rgba(200,16,46,.2);font-size:12px;color:var(--red);font-weight:bold;">
                        🚩 POI mode active — click the map to place a named point of interest. Press Esc or switch tool to exit.
                    </div>

                    {{-- POI list --}}
                    <div id="poi-list-section" style="border-top:1px solid var(--grey-mid);background:#fafbfc;">
                        <div style="padding:.5rem .85rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            <span style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);">Points of Interest (<span id="poi-count">0</span>)</span>
                        </div>
                        <div id="poi-list" style="display:flex;flex-direction:column;gap:0;"></div>
                    </div>

                    {{-- Elevation profile panel --}}
                    <div id="elevation-panel">
                        <div class="elevation-head">
                            <span>⛰ Elevation Profile</span>
                            <span id="elev-stats" style="font-size:11px;color:var(--text-muted);font-weight:normal;"></span>
                        </div>
                        <svg id="elev-chart" viewBox="0 0 600 90" preserveAspectRatio="none"></svg>
                    </div>

                    {{-- Weather forecast badge --}}
                    <div id="weather-badge">
                        <span id="weather-icon" style="font-size:24px;"></span>
                        <div>
                            <div style="font-size:13px;font-weight:bold;color:#003366;" id="weather-summary"></div>
                            <div style="font-size:11px;color:var(--text-muted);" id="weather-detail"></div>
                        </div>
                        <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem;">
                            <svg id="wind-compass" width="36" height="36" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="16" fill="rgba(0,51,102,.08)" stroke="rgba(0,51,102,.2)" stroke-width="1"/>
                                <line id="wind-needle" x1="18" y1="8" x2="18" y2="28" stroke="#C8102E" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="2.5" fill="#003366"/>
                            </svg>
                            <span id="wind-label" style="font-size:11px;font-weight:bold;color:#003366;"></span>
                        </div>
                        <button onclick="document.getElementById('weather-badge').classList.remove('visible')"
                                style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;padding:0 .25rem;">✕</button>
                    </div>
                </div>

            </div>{{-- /form-body --}}
            <div class="form-footer">
                <button type="submit" class="{{ isset($editingEvent) ? 'btn btn-primary' : 'btn btn-green' }}">
                    {{ isset($editingEvent) ? '✓ Update Event' : '+ Save Event' }}
                </button>
                @if (isset($editingEvent))
                    <a href="{{ route('admin.events') }}" class="btn btn-ghost">Cancel</a>
                @endif
            </div>
            </div>{{-- /event-form-body --}}
<script>
@if(!isset($editingEvent))
(function(){
    var wrap = document.getElementById('event-form-body');
    var icon = document.getElementById('form-toggle-icon');
    var sub  = document.getElementById('form-toggle-sub');
    if (!wrap) return;
    wrap.style.display = 'none';
    if (icon) icon.style.transform = 'rotate(-90deg)';
    if (sub)  sub.textContent = 'Click to expand and add a new event.';
    window.toggleEventForm = function() {
        var open = wrap.style.display !== 'none';
        wrap.style.display = open ? 'none' : '';
        if (icon) icon.style.transform = open ? 'rotate(-90deg)' : 'rotate(0deg)';
        if (sub)  sub.textContent = open ? 'Click to expand and add a new event.' : "New events appear on the calendar and members' hub immediately.";
    };
})();
@endif
</script>

        </form>
    </div>

    {{-- ─── EVENTS LIST ─── --}}
    <div class="fade-in fade-in-d3">

        <div class="section-header">
            <div class="section-left">
                <span class="section-title">All Events</span>
                @if ($events->total() > 0)
                    <span class="count-badge">{{ $events->total() }} total</span>
                @endif
            </div>
            <div class="search-wrap">
                <span class="search-icon">⌕</span>
                <input type="text" placeholder="Filter events…" id="tableSearch" oninput="filterAll(this.value)">
            </div>
        </div>

        @if ($events->isEmpty())
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <div class="empty-text">No events yet — add the first one using the form above.</div>
                </div>
            </div>
        @else

        {{-- ══════════ DESKTOP TABLE ══════════ --}}
        <div class="table-card desktop-table">
            <table id="eventsTable">
                <thead>
                    <tr>
                        <th style="width:25%;">Title</th>
                        <th style="width:17%;">When</th>
                        <th style="width:15%;">Location</th>
                        <th style="width:12%;">Type</th>
                        <th style="width:7%;">Vis.</th>
                        <th style="width:7%;">Docs</th>
                        <th style="width:9%;">RSVPs</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($events as $event)
                @php
                    $type        = $event->type;
                    $badgeLabel  = $type?->name ?? null;
                    $badgeColour = $type?->colour ?: null;
                    $docCount    = method_exists($event, 'documents')
                        ? ($event->relationLoaded('documents') ? $event->documents->count() : $event->documents()->count())
                        : 0;
                    $openDocs    = (request('docs') == $event->id);
                    $eventDocs   = method_exists($event, 'documents')
                        ? ($event->relationLoaded('documents') ? $event->documents : $event->documents()->get())
                        : collect();
                        $rsvps      = $event->rsvps()->with('user')->get();
$rsvpCounts = $rsvps->groupBy('status')->map->count();
$openRsvp   = (request('rsvp') == $event->id);
                @endphp

                <tr class="{{ $openDocs ? 'docs-open' : '' }}" id="dt-row-{{ $event->id }}">
                    <td class="cell-title">{{ $event->title }}</td>
                    <td class="cell-when">
                        {{ method_exists($event, 'displayDate') ? $event->displayDate() : $event->starts_at }}
                    </td>
                    <td class="cell-loc">
                        @if ($event->location)
                            📍 {{ $event->location }}
                        @else
                            <span style="color:var(--grey-dark);">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($type)
                            @if ($badgeColour)
                                <span class="type-pill" style="background:{{ $badgeColour }}1a;border-color:{{ $badgeColour }};color:{{ $badgeColour }};">{{ $badgeLabel }}</span>
                            @else
                                <span class="type-pill" style="background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);">{{ $badgeLabel }}</span>
                            @endif
                        @else
                            <span style="color:var(--grey-dark);font-size:12px;">—</span>
                        @endif
                    </td>
                    {{-- Visibility column --}}
                    <td>
                        @if ($event->is_private)
                            <span class="private-badge">🔒 Private</span>
                        @else
                            <span style="font-size:11px;color:var(--grey-dark);">Public</span>
                        @endif
                    </td>
                    <td>
                        <span class="doc-badge {{ $docCount > 0 ? 'has' : 'none' }}">📎 {{ $docCount }}</span>
                    </td>
                    <td>
    @if($rsvps->count() > 0)
        <button type="button"
                onclick="toggleRsvp({{ $event->id }})"
                style="background:none;border:none;cursor:pointer;font-family:var(--font);font-size:11px;font-weight:bold;color:var(--green);padding:0;display:flex;align-items:center;gap:4px;">
            <span style="color:var(--green);">✓{{ $rsvpCounts['attending'] ?? 0 }}</span>
            <span style="color:var(--amber);">?{{ $rsvpCounts['maybe'] ?? 0 }}</span>
            <span style="color:var(--red);">✕{{ $rsvpCounts['declined'] ?? 0 }}</span>
        </button>
    @else
        <span style="font-size:11px;color:var(--grey-dark);">—</span>
    @endif
</td>
                    <td class="td-actions">
                        <div class="action-group">
                            <a href="{{ $event->url() }}" target="_blank"
                               class="act-btn" style="margin-left:0;">View ↗</a>
                            <a href="{{ route('admin.events', ['edit' => $event->id]) }}"
                               class="act-btn">Edit</a>
                            <div class="act-more-wrap" id="mw-{{ $event->id }}">
                                <button type="button"
                                        class="act-more-btn"
                                        onclick="toggleMore({{ $event->id }})"
                                        aria-label="More actions"
                                        title="More actions">⋯</button>
                                <div class="act-dropdown" id="dd-{{ $event->id }}">
                                    <a href="{{ route('admin.events.assignments', $event->id) }}"
                                       class="dd-item dd-team">
                                        <span class="dd-icon">👥</span> Team
                                    </a>
                                    <form method="POST" action="{{ route('admin.events.availability-request', $event->id) }}" style="display:contents;" onsubmit="return confirm('Send availability request email to ALL active members?')">
                                        @csrf
                                        <button type="submit" class="dd-item" style="color:#0e7490;">
                                            <span class="dd-icon">📣</span> Request Availability
                                        </button>
                                    </form>
                                    <button type="button"
        class="dd-item"
        style="color:var(--green);"
        onclick="toggleRsvp({{ $event->id }}); closeMore({{ $event->id }});">
    <span class="dd-icon">👥</span> RSVPs
    @if(($rsvpCounts['attending'] ?? 0) + ($rsvpCounts['maybe'] ?? 0) > 0)
        <span class="dd-count" style="background:var(--green-bg);color:var(--green);border-color:var(--green-border);">
            {{ $rsvps->count() }}
        </span>
    @endif
</button>
                                    <button type="button"
                                            class="dd-item dd-docs"
                                            onclick="toggleDocs({{ $event->id }}); closeMore({{ $event->id }});">
                                        <span class="dd-icon">📎</span> Docs
                                        @if ($docCount > 0)
                                            <span class="dd-count">{{ $docCount }}</span>
                                        @endif
                                    </button>
                                    <div class="dd-divider"></div>
                                    <form method="POST"
                                          action="{{ route('admin.events.delete', $event->id) }}"
                                          onsubmit="return confirmDelete(this, {{ json_encode($event->title) }})">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dd-item dd-delete">
                                            <span class="dd-icon">🗑</span> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                {{-- Inline docs panel --}}
                <tr class="docs-panel-row" id="dt-dp-row-{{ $event->id }}">
                    <td colspan="7">
                        <div class="docs-panel {{ $openDocs ? 'is-open' : '' }}" id="dt-dp-{{ $event->id }}">
                            <div class="dp-label">📁 Event Documentation — {{ $event->title }}</div>

                            @if (! \Illuminate\Support\Facades\Route::has('admin.events.documents.upload'))
                                <div style="padding:.7rem .9rem;font-size:12px;color:var(--amber);background:var(--white);border:1px dashed var(--amber-border);margin-bottom:.75rem;">
                                    ⚠ Document routes not yet registered.
                                </div>
                            @else
                            <form method="POST"
                                  action="{{ route('admin.events.documents.upload', $event->id) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="doc-upload-area">
                                    <div class="doc-upload-field">
                                        <label>Display Name <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(optional)</small></label>
                                        <input type="text" name="label" placeholder="Operator Briefing, Channel Plan, Route Map…">
                                    </div>
                                    <div class="doc-upload-field">
                                        <label>File *</label>
                                        <input type="file" name="document"
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.zip">
                                        <span class="doc-hint">PDF, Word, Excel, PowerPoint, images, ZIP · max 20 MB</span>
                                    </div>
                                    <div style="display:flex;align-items:flex-end;">
                                        <button type="submit" class="btn btn-amber">↑ Upload</button>
                                    </div>
                                </div>
                            </form>

                            @if ($eventDocs->isEmpty())
                                <p class="doc-list-empty">No documents attached yet.</p>
                            @else
                                <div class="doc-list">
                                @foreach ($eventDocs as $doc)
                                @php
                                    $ext  = strtolower(pathinfo($doc->filename, PATHINFO_EXTENSION));
                                    $icon = match($ext) {
                                        'pdf'              => '📄',
                                        'doc','docx'       => '📝',
                                        'xls','xlsx'       => '📊',
                                        'ppt','pptx'       => '📋',
                                        'jpg','jpeg','png' => '🖼',
                                        'zip'              => '🗜',
                                        default            => '📎',
                                    };
                                    $size = $doc->size_bytes
                                        ? ($doc->size_bytes < 1048576
                                            ? round($doc->size_bytes / 1024) . ' KB'
                                            : round($doc->size_bytes / 1048576, 1) . ' MB')
                                        : null;
                                @endphp
                                <div class="doc-item">
                                    <div class="doc-item-icon">{{ $icon }}</div>
                                    <div class="doc-item-info">
                                        <a href="{{ route('admin.events.documents.download', $doc->id) }}"
                                           class="doc-item-name" target="_blank">
                                            {{ $doc->label ?: $doc->filename }}
                                        </a>
                                        <div class="doc-item-meta">
                                            {{ strtoupper($ext) }}
                                            @if ($size) · {{ $size }} @endif
                                            · {{ $doc->created_at->format('j M Y') }}
                                            @if ($doc->uploader) · {{ $doc->uploader->name }} @endif
                                        </div>
                                    </div>
                                    <div class="doc-item-acts">
                                        <a href="{{ route('admin.events.documents.download', $doc->id) }}"
                                           class="doc-dl" target="_blank">↓ Download</a>
                                        <form method="POST"
                                              action="{{ route('admin.events.documents.delete', $doc->id) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Remove {{ addslashes($doc->label ?: $doc->filename) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="doc-rm">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                                </div>
                            @endif
                            @endif {{-- route check --}}
                        </div>
                    </td>
                </tr>
<tr class="docs-panel-row" id="dt-rsvp-row-{{ $event->id }}">
    <td colspan="8">
        <div class="rsvp-panel {{ $openRsvp ? 'is-open' : '' }}" id="dt-rsvp-{{ $event->id }}">
            <div class="dp-label" style="color:var(--green);">👥 RSVP Responses — {{ $event->title }}</div>
            <div class="rsvp-summary">
                <div class="rsvp-count">
                    <div class="rsvp-count-num" style="color:var(--green);">{{ $rsvpCounts['attending'] ?? 0 }}</div>
                    <div class="rsvp-count-lbl">Attending</div>
                </div>
                <div class="rsvp-count">
                    <div class="rsvp-count-num" style="color:var(--amber);">{{ $rsvpCounts['maybe'] ?? 0 }}</div>
                    <div class="rsvp-count-lbl">Maybe</div>
                </div>
                <div class="rsvp-count">
                    <div class="rsvp-count-num" style="color:var(--red);">{{ $rsvpCounts['declined'] ?? 0 }}</div>
                    <div class="rsvp-count-lbl">Declined</div>
                </div>
                <div class="rsvp-count">
                    <div class="rsvp-count-num" style="color:var(--navy);">{{ $rsvps->count() }}</div>
                    <div class="rsvp-count-lbl">Total</div>
                </div>
            </div>
            @if($rsvps->isEmpty())
                <p class="rsvp-empty">No responses yet.</p>
            @else
                <div class="rsvp-list">
                    @foreach($rsvps->sortBy('status') as $rsvp)
                    <div class="rsvp-item">
                        <div class="rsvp-item-callsign">{{ $rsvp->user?->callsign ?? '—' }}</div>
                        <div class="rsvp-item-name">{{ $rsvp->user?->name ?? 'Unknown' }}</div>
                        <div class="rsvp-item-status {{ $rsvp->status }}">
                            {{ ['attending'=>'✓ Going','maybe'=>'? Maybe','declined'=>'✕ Can\'t'][$rsvp->status] }}
                        </div>
                        @if($rsvp->note)
                            <div class="rsvp-item-note">{{ $rsvp->note }}</div>
                        @endif
                        <div style="font-size:10px;color:var(--grey-dark);flex-shrink:0;">
                            {{ $rsvp->updated_at->format('j M H:i') }}
                        </div>
                        <form method="POST"
                              action="{{ route('admin.events.rsvp.destroy', $rsvp->id) }}"
                              style="display:inline;flex-shrink:0;margin-left:auto;"
                              onsubmit="return confirm('Remove {{ addslashes($rsvp->user?->name ?? 'this person') }}\'s RSVP?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="doc-rm">Remove</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Admin Add RSVP --}}
            <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--grey-mid);">
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--grey-dark);margin-bottom:.5rem;">+ Add / Override RSVP</div>
                <form method="POST" action="{{ route('admin.events.rsvp.store', $event->id) }}" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                    @csrf
                    <select name="user_id" required style="padding:.35rem .65rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:12px;min-width:180px;outline:none;">
                        <option value="">-- Select member --</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->callsign ? ' ('.$u->callsign.')' : '' }}</option>
                        @endforeach
                    </select>
                    <select name="status" required style="padding:.35rem .65rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:12px;outline:none;">
                        <option value="attending">Going</option>
                        <option value="maybe">Maybe</option>
                        <option value="declined">Cannot Make It</option>
                    </select>
                    <input type="text" name="note" placeholder="Optional note" style="padding:.35rem .65rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:12px;flex:1;min-width:120px;outline:none;">
                    <button type="submit" style="padding:.35rem .85rem;background:var(--navy);color:#fff;border:none;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;">Save</button>
                </form>
            </div>

        </div>
    </td>
</tr>
                @endforeach
                </tbody>
            </table>

            @if ($events->lastPage() > 1)
            @php $c = $events->currentPage(); $l = $events->lastPage(); @endphp
            <div class="pagination-wrap">
                <div class="pagination-info">Showing {{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ $events->total() }}</div>
                <div class="page-links">
                    <a href="{{ $events->url($c - 1) }}" class="page-link {{ $c === 1 ? 'disabled' : '' }}">‹</a>
                    @for ($p = max(1, $c - 2); $p <= min($l, $c + 2); $p++)
                        <a href="{{ $events->url($p) }}" class="page-link {{ $p === $c ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $events->url($c + 1) }}" class="page-link {{ $c === $l ? 'disabled' : '' }}">›</a>
                </div>
            </div>
            @endif
        </div>

        {{-- ══════════ MOBILE CARDS ══════════ --}}
        <div class="mobile-events" id="mobileCards">
            @foreach ($events as $event)
            @php
                $type        = $event->type;
                $badgeLabel  = $type?->name ?? null;
                $badgeColour = $type?->colour ?: null;
                $docCount    = method_exists($event, 'documents')
                    ? ($event->relationLoaded('documents') ? $event->documents->count() : $event->documents()->count())
                    : 0;
                $openDocs    = (request('docs') == $event->id);
                $eventDocs   = method_exists($event, 'documents')
                    ? ($event->relationLoaded('documents') ? $event->documents : $event->documents()->get())
                    : collect();
                    $rsvps      = $event->rsvps()->with('user')->get();
$rsvpCounts = $rsvps->groupBy('status')->map->count();
$openRsvp   = (request('rsvp') == $event->id);
            @endphp

            <div class="m-card {{ $openDocs ? 'docs-open' : '' }}"
                 id="m-card-{{ $event->id }}"
                 data-search="{{ strtolower($event->title . ' ' . ($event->location ?? '') . ' ' . ($badgeLabel ?? '')) }}">

                <div class="m-card-body">
                    <div class="m-card-top">
                        <div class="m-card-title">
                            {{ $event->title }}
                            @if ($event->is_private)
                                <span class="private-badge" style="font-size:9px;vertical-align:middle;margin-left:4px;">🔒 Private</span>
                            @endif
                        </div>
                        @if ($badgeLabel)
                            @if ($badgeColour)
                                <span class="type-pill" style="background:{{ $badgeColour }}1a;border-color:{{ $badgeColour }};color:{{ $badgeColour }};flex-shrink:0;font-size:9px;">{{ $badgeLabel }}</span>
                            @else
                                <span class="type-pill" style="background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);flex-shrink:0;font-size:9px;">{{ $badgeLabel }}</span>
                            @endif
                        @endif
                    </div>
                    <div class="m-card-meta">
                        <span>📅 {{ method_exists($event, 'displayDate') ? $event->displayDate() : $event->starts_at }}</span>
                        @if ($event->location)
                            <span class="m-card-sep">·</span>
                            <span>📍 {{ $event->location }}</span>
                        @endif
                        @if ($docCount > 0)
                            <span class="m-card-sep">·</span>
                            <span style="color:var(--navy);font-weight:bold;">📎 {{ $docCount }} doc{{ $docCount !== 1 ? 's' : '' }}</span>
                        @endif
                        @if($rsvps->count() > 0)
    <span class="m-card-sep">·</span>
    <span style="color:var(--green);font-weight:bold;">
        👥 {{ $rsvpCounts['attending'] ?? 0 }} going
    </span>
@endif
                    </div>
                </div>

                <div class="m-card-actions">
                    <a href="{{ $event->url() }}" target="_blank" class="m-act m-act-view">View ↗</a>
                    <a href="{{ route('admin.events', ['edit' => $event->id]) }}" class="m-act m-act-edit">Edit</a>
                    <a href="{{ route('admin.events.assignments', $event->id) }}" class="m-act m-act-team">👥 Team</a>
                    <button type="button" class="m-act m-act-docs" onclick="toggleMDocs({{ $event->id }})">📎 Docs</button>
                    <form method="POST"
                          action="{{ route('admin.events.delete', $event->id) }}"
                          style="display:inline;"
                          onsubmit="return confirmDelete(this, {{ json_encode($event->title) }})">
                        @csrf @method('DELETE')
                        <button type="submit" class="m-act m-act-delete">Delete</button>
                    </form>
                </div>

                <div class="m-docs-panel {{ $openDocs ? 'is-open' : '' }}" id="m-dp-{{ $event->id }}">
                    <div class="dp-label">📁 Documentation — {{ $event->title }}</div>

                    @if (\Illuminate\Support\Facades\Route::has('admin.events.documents.upload'))
                    <form method="POST"
                          action="{{ route('admin.events.documents.upload', $event->id) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="doc-upload-area">
                            <div class="doc-upload-field">
                                <label>Display Name <small style="text-transform:none;letter-spacing:0;font-weight:normal;">(optional)</small></label>
                                <input type="text" name="label" placeholder="e.g. Operator Briefing…">
                            </div>
                            <div class="doc-upload-field">
                                <label>File *</label>
                                <input type="file" name="document"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.zip">
                                <span class="doc-hint">PDF, Word, Excel, images, ZIP · max 20 MB</span>
                            </div>
                            <div style="display:flex;align-items:flex-end;width:100%;">
                                <button type="submit" class="btn btn-amber" style="width:100%;">↑ Upload</button>
                            </div>
                        </div>
                    </form>
                    @endif

                    @if ($eventDocs->isEmpty())
                        <p class="doc-list-empty">No documents attached yet.</p>
                    @else
                        <div class="doc-list">
                        @foreach ($eventDocs as $doc)
                        @php
                            $ext  = strtolower(pathinfo($doc->filename, PATHINFO_EXTENSION));
                            $icon = match($ext) {
                                'pdf'              => '📄', 'doc','docx' => '📝',
                                'xls','xlsx'       => '📊', 'ppt','pptx' => '📋',
                                'jpg','jpeg','png' => '🖼', 'zip'         => '🗜',
                                default            => '📎',
                            };
                            $size = $doc->size_bytes
                                ? ($doc->size_bytes < 1048576
                                    ? round($doc->size_bytes / 1024) . ' KB'
                                    : round($doc->size_bytes / 1048576, 1) . ' MB')
                                : null;
                        @endphp
                        <div class="doc-item">
                            <div class="doc-item-icon">{{ $icon }}</div>
                            <div class="doc-item-info">
                                <a href="{{ route('admin.events.documents.download', $doc->id) }}"
                                   class="doc-item-name" target="_blank">
                                    {{ $doc->label ?: $doc->filename }}
                                </a>
                                <div class="doc-item-meta">
                                    {{ strtoupper($ext) }}
                                    @if ($size) · {{ $size }} @endif
                                    · {{ $doc->created_at->format('j M Y') }}
                                </div>
                            </div>
                            <div class="doc-item-acts">
                                <a href="{{ route('admin.events.documents.download', $doc->id) }}"
                                   class="doc-dl" target="_blank">↓</a>
                                <form method="POST"
                                      action="{{ route('admin.events.documents.delete', $doc->id) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('Remove {{ addslashes($doc->label ?: $doc->filename) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="doc-rm">✕</button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                        </div>
                    @endif
                </div>

            </div>
            @endforeach
@if($rsvps->count() > 0)
<button type="button" class="m-act m-act-team" onclick="toggleMRsvp({{ $event->id }})">
    👥 RSVPs <span style="margin-left:3px;opacity:.7;">{{ $rsvps->count() }}</span>
</button>
@endif
<div class="m-rsvp-panel {{ $openRsvp ? 'is-open' : '' }}" id="m-rsvp-{{ $event->id }}">
    <div class="dp-label" style="color:var(--green);">👥 RSVPs — {{ $event->title }}</div>
    <div class="rsvp-summary" style="margin-bottom:.75rem;">
        <div class="rsvp-count">
            <div class="rsvp-count-num" style="color:var(--green);">{{ $rsvpCounts['attending'] ?? 0 }}</div>
            <div class="rsvp-count-lbl">Going</div>
        </div>
        <div class="rsvp-count">
            <div class="rsvp-count-num" style="color:var(--amber);">{{ $rsvpCounts['maybe'] ?? 0 }}</div>
            <div class="rsvp-count-lbl">Maybe</div>
        </div>
        <div class="rsvp-count">
            <div class="rsvp-count-num" style="color:var(--red);">{{ $rsvpCounts['declined'] ?? 0 }}</div>
            <div class="rsvp-count-lbl">Can't</div>
        </div>
    </div>
    @if($rsvps->isEmpty())
        <p class="rsvp-empty">No responses yet.</p>
    @else
        <div class="rsvp-list">
            @foreach($rsvps->sortBy('status') as $rsvp)
            <div class="rsvp-item" style="flex-wrap:wrap;gap:.4rem;">
                <div class="rsvp-item-callsign">{{ $rsvp->user?->callsign ?? '—' }}</div>
                <div class="rsvp-item-name">{{ $rsvp->user?->name ?? 'Unknown' }}</div>
                <div class="rsvp-item-status {{ $rsvp->status }}">
                    {{ ['attending'=>'✓ Going','maybe'=>'? Maybe','declined'=>'✕ Can\'t'][$rsvp->status] }}
                </div>
                @if($rsvp->note)
                    <div class="rsvp-item-note" style="width:100%;">{{ $rsvp->note }}</div>
                @endif
                <form method="POST"
                      action="{{ route('admin.events.rsvp.destroy', $rsvp->id) }}"
                      style="margin-left:auto;"
                      onsubmit="return confirm('Remove {{ addslashes($rsvp->user?->name ?? 'this') }}\'s RSVP?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="doc-rm" style="font-size:10px;">Remove</button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</div>
            @if ($events->lastPage() > 1)
            @php $c = $events->currentPage(); $l = $events->lastPage(); @endphp
            <div class="pagination-wrap" style="background:var(--white);border:1px solid var(--grey-mid);margin-top:.25rem;">
                <div class="pagination-info">{{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ $events->total() }}</div>
                <div class="page-links">
                    <a href="{{ $events->url($c - 1) }}" class="page-link {{ $c === 1 ? 'disabled' : '' }}">‹</a>
                    @for ($p = max(1, $c - 2); $p <= min($l, $c + 2); $p++)
                        <a href="{{ $events->url($p) }}" class="page-link {{ $p === $c ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $events->url($c + 1) }}" class="page-link {{ $c === $l ? 'disabled' : '' }}">›</a>
                </div>
            </div>
            @endif
        </div>

        @endif {{-- events not empty --}}
    </div>

</div>

{{-- ─── DROPDOWN SCRIPT (must come before map script) ─── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.act-dropdown').forEach(function (dd) {
        document.body.appendChild(dd);
    });
});

function positionDropdown(btn, dd) {
    const r = btn.getBoundingClientRect();
    dd.style.top  = (r.bottom + window.scrollY + 4) + 'px';
    dd.style.left = (r.right  + window.scrollX - dd.offsetWidth) + 'px';
}

function toggleMore(id) {
    const dd  = document.getElementById('dd-' + id);
    const btn = document.querySelector('#mw-' + id + ' .act-more-btn');
    const wasOpen = dd.classList.contains('is-open');
    closeAllMore();
    if (!wasOpen) {
        dd.classList.add('is-open');
        btn && btn.classList.add('is-open');
        positionDropdown(btn, dd);
    }
}

function closeMore(id) {
    document.getElementById('dd-' + id)?.classList.remove('is-open');
    document.querySelector('#mw-' + id + ' .act-more-btn')?.classList.remove('is-open');
}
function closeAllMore() {
    document.querySelectorAll('.act-dropdown.is-open').forEach(d => d.classList.remove('is-open'));
    document.querySelectorAll('.act-more-btn.is-open').forEach(b => b.classList.remove('is-open'));
}

document.addEventListener('click', e => { if (!e.target.closest('.act-more-wrap') && !e.target.closest('.act-dropdown')) closeAllMore(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllMore(); });

function repositionOpenDropdown() {
    const openDd = document.querySelector('.act-dropdown.is-open');
    if (!openDd) return;
    const id  = openDd.id.replace('dd-', '');
    const btn = document.querySelector('#mw-' + id + ' .act-more-btn');
    if (btn) positionDropdown(btn, openDd);
}
window.addEventListener('scroll', repositionOpenDropdown, true);
window.addEventListener('resize', closeAllMore);

function toggleDocs(id) {
    const panel = document.getElementById('dt-dp-' + id);
    const row   = document.getElementById('dt-row-' + id);
    const wasOpen = panel.classList.contains('is-open');
    document.querySelectorAll('.docs-panel.is-open').forEach(p => p.classList.remove('is-open'));
    document.querySelectorAll('tbody tr.docs-open').forEach(r => r.classList.remove('docs-open'));
    if (!wasOpen) {
        panel.classList.add('is-open');
        row && row.classList.add('docs-open');
        setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 40);
    }
}
function toggleRsvp(id) {
    const panel = document.getElementById('dt-rsvp-' + id);
    const row   = document.getElementById('dt-row-' + id);
    const wasOpen = panel.classList.contains('is-open');
    document.querySelectorAll('.rsvp-panel.is-open').forEach(p => p.classList.remove('is-open'));
    document.querySelectorAll('tbody tr.docs-open').forEach(r => r.classList.remove('docs-open'));
    if (!wasOpen) {
        panel.classList.add('is-open');
        row && row.classList.add('docs-open');
        setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 40);
    }
}

function toggleMDocs(id) {
    const panel = document.getElementById('m-dp-' + id);
    const card  = document.getElementById('m-card-' + id);
    const wasOpen = panel.classList.contains('is-open');
    document.querySelectorAll('.m-docs-panel.is-open').forEach(p => p.classList.remove('is-open'));
    document.querySelectorAll('.m-card.docs-open').forEach(c => c.classList.remove('docs-open'));
    if (!wasOpen) {
        panel.classList.add('is-open');
        card && card.classList.add('docs-open');
        setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 40);
    }
}

function filterAll(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#eventsTable tbody tr:not(.docs-panel-row)').forEach(row => {
        const id = row.id.replace('dt-row-', '');
        const dpRow = document.getElementById('dt-dp-row-' + id);
        const show = row.textContent.toLowerCase().includes(q);
        row.style.display = show ? '' : 'none';
        if (dpRow) dpRow.style.display = show ? '' : 'none';
    });
    document.querySelectorAll('.m-card').forEach(card => {
        const show = (card.dataset.search || card.textContent.toLowerCase()).includes(q);
        card.style.display = show ? '' : 'none';
    });
}

function confirmDelete(form, title) {
    return confirm('Delete "' + title + '"?\n\nThis cannot be undone.');
}

(function () {
    const id = new URLSearchParams(window.location.search).get('docs');
    if (!id) return;
    const dp = document.getElementById('dt-dp-' + id);
    if (dp) {
        dp.classList.add('is-open');
        document.getElementById('dt-row-' + id)?.classList.add('docs-open');
        setTimeout(() => dp.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 250);
    }
    const mp = document.getElementById('m-dp-' + id);
    if (mp) {
        mp.classList.add('is-open');
        document.getElementById('m-card-' + id)?.classList.add('docs-open');
    }
})();
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
/* ══════════════════════════════════════════════════════════════════════════
   EVENT MAP PICKER — all map logic in one self-contained block.
══════════════════════════════════════════════════════════════════════════ */

var evtMap         = null;
var evtMarker      = null;
var evtPolyLayer   = null;
var evtMaskLayers  = [];
var evtRouteLayers = [];
var evtTileStreet  = null;
var evtTileSat     = null;
var evtSatOn       = false;
var evtDrawCtrl    = null;
var evtDrawn       = null;
var evtTool        = 'pin';
var activeDrawHandler = null;
var activeEditHandler = null;
var EVT_DEFAULT    = { lat: 53.4084, lng: -2.9916 };

(function () {
    const lat = document.getElementById('event-lat')?.value;
    const lng = document.getElementById('event-lng')?.value;
    const loc = document.querySelector('[name=location]')?.value;
    if (lat) document.getElementById('disp-evt-lat').value = lat;
    if (lng) document.getElementById('disp-evt-lng').value = lng;
    if (loc) document.getElementById('disp-evt-loc').value = loc;
})();

function initEventMap() {
    if (evtMap) return;

    const existLat = parseFloat(document.getElementById('event-lat').value) || EVT_DEFAULT.lat;
    const existLng = parseFloat(document.getElementById('event-lng').value) || EVT_DEFAULT.lng;
    const hasPin   = !!document.getElementById('event-lat').value;

    evtMap = L.map('event-map-picker', { center: [existLat, existLng], zoom: hasPin ? 15 : 12, zoomControl: true });

    // Fullscreen — teleport map into overlay
    const mapOriginalParent = document.getElementById('event-map-picker').parentElement;

    window.evtMapFullscreen = function() {
        const overlay = document.getElementById('map-fullscreen-overlay');
        const picker  = document.getElementById('event-map-picker');
        const toolbar = document.getElementById('map-float-toolbar');
        overlay.classList.add('active');
        overlay.insertBefore(picker, overlay.querySelector('#map-fullscreen-topbar').nextSibling);
        overlay.querySelector('#map-fullscreen-topbar').appendChild(toolbar);
        document.body.style.overflow = 'hidden';
        setTimeout(() => evtMap.invalidateSize(), 150);
    };

    window.evtMapExitFullscreen = function() {
        const overlay = document.getElementById('map-fullscreen-overlay');
        const picker  = document.getElementById('event-map-picker');
        const toolbar = document.getElementById('map-float-toolbar');
        const wrap    = document.getElementById('event-map-wrap');
        overlay.classList.remove('active');
        wrap.insertBefore(picker, wrap.firstChild);
        wrap.appendChild(toolbar);
        document.body.style.overflow = '';
        setTimeout(() => evtMap.invalidateSize(), 150);
    };

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { const o = document.getElementById('map-fullscreen-overlay'); if (o.classList.contains('active')) evtMapExitFullscreen(); }});

    // Satellite toggle
    let evtSatOn = false;
    window.evtMapSatToggle = function() {
        evtSatOn = !evtSatOn;
        if (evtSatOn) { evtTileStreet.remove(); evtTileSat.addTo(evtMap); }
        else { evtTileSat.remove(); evtTileStreet.addTo(evtMap); }
        document.getElementById('map-sat-btn').style.background = evtSatOn ? '#003366' : '#fff';
        document.getElementById('map-sat-btn').style.color = evtSatOn ? '#fff' : '';
    };

    // Locate
    window.evtMapLocate = function() {
        if (!navigator.geolocation) return alert('Geolocation not supported');
        navigator.geolocation.getCurrentPosition(p => {
            evtMap.setView([p.coords.latitude, p.coords.longitude], 15);
        }, () => alert('Could not get location'));
    };

    // Clear all
    window.evtMapClearAll = function() {
        if (!confirm('Clear pin, polygon, route and POIs?')) return;
        if (evtPin) { evtMap.removeLayer(evtPin); evtPin = null; }
        evtDrawn.clearLayers();
        document.getElementById('event-lat').value = '';
        document.getElementById('event-lng').value = '';
        document.getElementById('event-polygon').value = '';
        document.getElementById('event-route').value = '';
        document.getElementById('event-pois').value = '[]';
        document.getElementById('disp-evt-lat').value = '';
        document.getElementById('disp-evt-lng').value = '';
        if (typeof refreshPoiList === 'function') refreshPoiList();
    };

    evtTileStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    }).addTo(evtMap);

    evtTileSat = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        { attribution: '© Esri', maxZoom: 19 }
    );

    evtDrawn = new L.FeatureGroup().addTo(evtMap);
    evtDrawCtrl = new L.Control.Draw({
        draw: {
            polygon:  { shapeOptions: { color: '#1a6b3c', fillColor: '#1a6b3c', fillOpacity: .12, weight: 2 } },
            polyline: { shapeOptions: { color: '#7c3aed', weight: 4, opacity: .85 }, metric: true, showLength: true },
            rectangle: false, circle: false, marker: false, circlemarker: false,
        },
        edit: { featureGroup: evtDrawn },
    });

    if (hasPin) placeEvtPin(existLat, existLng, false);

    const polyJson = document.getElementById('event-polygon').value;
    if (polyJson) {
        try {
            const geo  = JSON.parse(polyJson);
            const rings = (geo.type === 'MultiPolygon' ? geo.coordinates[0] : geo.coordinates)
                           .map(ring => ring.map(c => [c[1], c[0]]));
            evtPolyLayer = L.polygon(rings, { color: '#1a6b3c', fillColor: '#1a6b3c', fillOpacity: .12, weight: 2 });
            evtDrawn.addLayer(evtPolyLayer);
            evtPolyLayer.addTo(evtMap);
            applyEvtMask();
            bindPolyPopup(evtPolyLayer);
            const polyName = document.getElementById('event-polygon-name').value || 'Site Boundary';
            updatePolyStatus(rings[0].length - 1, polyName);
            evtMap.fitBounds(evtPolyLayer.getBounds(), { padding: [20, 20] });
        } catch (e) { console.warn('Polygon restore error:', e); }
    }

    const routeJson = document.getElementById('event-route').value;
    if (routeJson) {
        try {
            const parsed   = JSON.parse(routeJson);
            const routeArr = Array.isArray(parsed)
                ? parsed
                : [{ id: 'r-legacy', name: 'Event Route', geometry: parsed }];
            routeArr.forEach(function (r) {
                restoreRouteLayer(r.id || ('r-' + Date.now()), r.name || 'Route', r.geometry);
            });
            if (!polyJson && evtRouteLayers.length > 0) {
                evtMap.fitBounds(evtRouteLayers[0].layer.getBounds(), { padding: [30, 30] });
            }
            updateRouteStatus();
        } catch (e) { console.warn('Route restore error:', e); }
    }

    evtMap.on('click', function (e) {
        if (evtTool === 'pin') {
            placeEvtPin(e.latlng.lat, e.latlng.lng, true);
        } else if (evtTool === 'poi') {
            const poi = makePoi(e.latlng.lat, e.latlng.lng);
            poi.grid_ref = latLngToOsgb(e.latlng.lat, e.latlng.lng);
            evtPois.push(poi);
            placePinOnMap(poi);
            renderPoiList();
            savePois();
            setTimeout(function () {
                const ni = document.querySelector('#poi-row-' + poi.id + ' .poi-name-input');
                if (ni) ni.focus();
            }, 50);
        }
    });

    evtMap.on(L.Draw.Event.CREATED, function (e) {
        const layer     = e.layer;
        const layerType = e.layerType;

        activeDrawHandler = null;
        document.getElementById('route-finish-btn').style.display = 'none';
        document.getElementById('tool-route-btn').classList.remove('tool-active');
        document.getElementById('tool-poly-btn').classList.remove('tool-active');
        evtTool = 'pin';
        document.getElementById('tool-pin-btn').classList.add('tool-active');

        if (layerType === 'polygon') {
            if (evtPolyLayer) { evtDrawn.removeLayer(evtPolyLayer); evtMap.removeLayer(evtPolyLayer); }
            evtPolyLayer = layer;
            evtDrawn.addLayer(evtPolyLayer);
            savePolygon();
            applyEvtMask();
            const polyName = document.getElementById('event-polygon-name').value || 'Site Boundary';
            const geo = evtPolyLayer.toGeoJSON();
            updatePolyStatus(geo.geometry.coordinates[0].length - 1, polyName);
            bindPolyPopup(evtPolyLayer);
        }

        if (layerType === 'polyline') {
            const newId   = 'r-' + Date.now();
            const newName = 'Route ' + (evtRouteLayers.length + 1);
            evtDrawn.addLayer(layer);
            evtRouteLayers.push({ id: newId, name: newName, layer });
            bindRoutePopup(newId);
            saveRoutes();
            updateRouteStatus();
        }
    });

    evtMap.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (l) {
            const geo  = l.toGeoJSON();
            const type = geo.geometry.type;
            if (type === 'Polygon' || type === 'MultiPolygon') {
                savePolygon();
                updatePolyStatus(geo.geometry.coordinates[0].length - 1,
                    document.getElementById('event-polygon-name').value || 'Site Boundary');
            }
            if (type === 'LineString' || type === 'MultiLineString') {
                saveRoutes();
                updateRouteStatus();
            }
        });
    });

    evtMap.on(L.Draw.Event.DELETED, function (e) {
        e.layers.eachLayer(function (l) {
            const geo = l.toGeoJSON().geometry;
            if (geo.type === 'Polygon' || geo.type === 'MultiPolygon') {
                document.getElementById('event-polygon').value      = '';
                document.getElementById('event-polygon-name').value = '';
                document.getElementById('poly-status').style.display = 'none';
                removeEvtMask();
                evtPolyLayer = null;
            }
            if (geo.type === 'LineString' || geo.type === 'MultiLineString') {
                evtRouteLayers = evtRouteLayers.filter(r => r.layer !== l);
                saveRoutes();
                updateRouteStatus();
            }
        });
    });
}

function placeEvtPin(lat, lng, updateFields) {
    const icon = L.divIcon({
        className: '',
        html: '<div style="width:26px;height:26px;background:#C8102E;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,.45);"></div>',
        iconSize: [26, 26], iconAnchor: [13, 26], popupAnchor: [0, -28]
    });
    if (evtMarker) evtMap.removeLayer(evtMarker);
    evtMarker = L.marker([lat, lng], { icon, draggable: true }).addTo(evtMap);

    evtMarker.bindPopup(function () {
        const locVal = document.querySelector('[name=location]')?.value || '';
        return mapPopupHtml('Event Pin', '#C8102E', `
            <div style="font-size:11px;color:#6b7f96;margin-bottom:6px;">Drag to reposition · click to edit name or delete</div>
            <div style="display:flex;gap:4px;align-items:center;margin-bottom:4px;">
                <input id="pin-name-input" type="text" value="${escHtml(locVal)}" placeholder="Location name…"
                    style="flex:1;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;"
                    oninput="syncPinName(this.value)">
                <button type="button" onclick="commitPinName()"
                    style="padding:4px 8px;background:#003366;border:none;color:#fff;font-size:11px;font-weight:bold;font-family:Arial,sans-serif;cursor:pointer;">✓</button>
            </div>
            ${popupBtn('✕ Remove Pin', '#C8102E', '#fdf0f2', 'removeEvtPin()')}
        `);
    }, { maxWidth: 240 });

    evtMarker.on('dragend', function (e) {
        const p = e.target.getLatLng();
        setEvtCoords(p.lat, p.lng);
    });
    if (updateFields) setEvtCoords(lat, lng);
}

function syncPinName(val) {
    const loc  = document.querySelector('[name=location]');
    const disp = document.getElementById('disp-evt-loc');
    if (loc)  loc.value  = val;
    if (disp) disp.value = val;
}

function commitPinName() { evtMap.closePopup(); }

function removeEvtPin() {
    evtMap.closePopup();
    if (evtMarker) { evtMap.removeLayer(evtMarker); evtMarker = null; }
    document.getElementById('event-lat').value      = '';
    document.getElementById('event-lng').value      = '';
    document.getElementById('disp-evt-lat').value   = '';
    document.getElementById('disp-evt-lng').value   = '';
}

function setEvtCoords(lat, lng) {
    document.getElementById('event-lat').value      = lat.toFixed(6);
    document.getElementById('event-lng').value      = lng.toFixed(6);
    document.getElementById('disp-evt-lat').value   = lat.toFixed(5);
    document.getElementById('disp-evt-lng').value   = lng.toFixed(5);

    const locInput = document.querySelector('[name=location]');
    const dispLoc  = document.getElementById('disp-evt-loc');
    if (!locInput.value) {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
            .then(r => r.json())
            .then(d => {
                if (d && !locInput.value) {
                    const a    = d.address || {};
                    const name = [a.leisure || a.amenity || a.road, a.suburb || a.village || a.town || a.city]
                        .filter(Boolean).join(', ')
                        || d.display_name.split(',').slice(0, 2).join(',').trim();
                    locInput.value = name;
                    dispLoc.value  = name;
                }
            })
            .catch(() => {});
    }
}

function setMapTool(tool) {
    evtTool = tool;
    document.getElementById('tool-pin-btn').classList.toggle('tool-active',     tool === 'pin');
    document.getElementById('tool-poly-btn').classList.toggle('tool-active',    tool === 'polygon');
    document.getElementById('tool-route-btn').classList.toggle('tool-active',   tool === 'route');
    document.getElementById('tool-poi-btn').classList.toggle('tool-active',     tool === 'poi');
    document.getElementById('tool-measure-btn')?.classList.toggle('tool-active', tool === 'measure');
    document.getElementById('poi-mode-hint').style.display        = (tool === 'poi')   ? 'block'        : 'none';
    document.getElementById('route-finish-btn').style.display     = (tool === 'route') ? 'inline-block' : 'none';

    if (activeDrawHandler) { try { activeDrawHandler.disable(); } catch (e) {} activeDrawHandler = null; }
    if (tool !== 'measure') stopMeasure();
    if (tool !== 'w3w' && w3wOn) toggleW3wMode();

    if (!evtMap) return;

    if (tool === 'polygon') {
        evtMap.addControl(evtDrawCtrl);
        activeDrawHandler = new L.Draw.Polygon(evtMap, evtDrawCtrl.options.draw.polygon);
        activeDrawHandler.enable();
    } else if (tool === 'route') {
        evtMap.addControl(evtDrawCtrl);
        activeDrawHandler = new L.Draw.Polyline(evtMap, evtDrawCtrl.options.draw.polyline);
        activeDrawHandler.enable();
    } else if (tool === 'measure') {
        startMeasure();
    } else {
        try { evtMap.removeControl(evtDrawCtrl); } catch (e) {}
    }
}

function finishRoute() {
    if (activeDrawHandler) {
        try { activeDrawHandler.completeShape(); } catch (e) {
            try { activeDrawHandler.disable(); } catch (e2) {}
        }
        activeDrawHandler = null;
    }
    document.getElementById('route-finish-btn').style.display = 'none';
    document.getElementById('tool-route-btn').classList.remove('tool-active');
    evtTool = 'pin';
    document.getElementById('tool-pin-btn').classList.add('tool-active');
}

function toggleEventSat() {
    if (!evtMap) return;
    evtSatOn = !evtSatOn;
    if (evtSatOn) { evtTileStreet.remove(); evtTileSat.addTo(evtMap); }
    else          { evtTileSat.remove(); evtTileStreet.addTo(evtMap); }
    document.getElementById('evt-sat-btn').textContent = evtSatOn ? '🗺 Street' : '🛰 Satellite';
}

async function mapLocationSearch() {
    const input = document.getElementById('map-location-search');
    const results = document.getElementById('map-search-results');
    const q = (input ? input.value : '').trim();
    if (!q || !evtMap) return;

    input.disabled = true;
    results.style.display = 'none';
    results.innerHTML = '';

    try {
        const url = 'https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) +
                    '&format=json&limit=5&countrycodes=gb&addressdetails=1';
        const resp = await fetch(url, { headers: { 'Accept-Language': 'en' } });
        const data = await resp.json();

        if (!data.length) {
            results.innerHTML = '<div style="padding:.6rem 1rem;font-size:12px;color:#888;">No results found.</div>';
            results.style.display = 'block';
            return;
        }

        data.forEach(function(place) {
            const div = document.createElement('div');
            div.style.cssText = 'padding:.55rem 1rem;font-size:12px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            div.textContent = place.display_name;
            div.onmouseover = function(){ this.style.background = '#f0f4ff'; };
            div.onmouseout  = function(){ this.style.background = ''; };
            div.onclick = function() {
                const lat = parseFloat(place.lat);
                const lng = parseFloat(place.lon);
                evtMap.setView([lat, lng], 15);
                results.style.display = 'none';
                input.value = place.display_name.split(',')[0];
            };
            results.appendChild(div);
        });

        results.style.display = 'block';
    } catch(e) {
        results.innerHTML = '<div style="padding:.6rem 1rem;font-size:12px;color:#c00;">Search failed.</div>';
        results.style.display = 'block';
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    const results = document.getElementById('map-search-results');
    const input   = document.getElementById('map-location-search');
    if (results && input && !results.contains(e.target) && e.target !== input) {
        results.style.display = 'none';
    }
});


var osGridLayer = null, osGridOn = false;

function toggleOsGrid() {
    osGridOn = !osGridOn;
    document.getElementById('tool-grid-btn').classList.toggle('tool-active', osGridOn);
    if (!evtMap) return;
    if (osGridOn) {
        osGridLayer = L.layerGroup().addTo(evtMap);
        drawOsGrid();
        evtMap.on('moveend zoomend', drawOsGrid);
    } else {
        evtMap.off('moveend zoomend', drawOsGrid);
        if (osGridLayer) { evtMap.removeLayer(osGridLayer); osGridLayer = null; }
    }
}

function drawOsGrid() {
    if (!osGridLayer || !osGridOn) return;
    osGridLayer.clearLayers();
    const zoom = evtMap.getZoom();
    if (zoom < 11) return;
    const step = zoom >= 14 ? 1000 : zoom >= 12 ? 5000 : 10000;
    const bounds = evtMap.getBounds().pad(0.1);
    const mpdLat = 111320;
    const mpdLng = 111320 * Math.cos(evtMap.getCenter().lat * Math.PI / 180);
    const latStep = step / mpdLat, lngStep = step / mpdLng;
    const minLat = Math.floor(bounds.getSouth() / latStep) * latStep;
    const minLng = Math.floor(bounds.getWest()  / lngStep) * lngStep;
    for (let lat = minLat; lat <= bounds.getNorth() + latStep; lat += latStep) {
        L.polyline([[lat, bounds.getWest()], [lat, bounds.getEast()]],
            { color: 'rgba(21,101,192,.25)', weight: .7, dashArray: '4 4', interactive: false }).addTo(osGridLayer);
    }
    for (let lng = minLng; lng <= bounds.getEast() + lngStep; lng += lngStep) {
        L.polyline([[bounds.getSouth(), lng], [bounds.getNorth(), lng]],
            { color: 'rgba(21,101,192,.25)', weight: .7, dashArray: '4 4', interactive: false }).addTo(osGridLayer);
    }
    if (zoom >= 13) {
        for (let lat = minLat; lat <= bounds.getNorth(); lat += latStep) {
            for (let lng = minLng; lng <= bounds.getEast(); lng += lngStep) {
                if (bounds.contains([lat, lng])) {
                    L.marker([lat, lng], {
                        icon: L.divIcon({ className: '', html: `<div style="font-size:8px;color:rgba(21,101,192,.65);font-family:Arial;white-space:nowrap;font-weight:bold;">${lat.toFixed(3)},${lng.toFixed(3)}</div>`, iconAnchor: [0, 0] }),
                        interactive: false
                    }).addTo(osGridLayer);
                }
            }
        }
    }
}

var measurePoints = [], measureMarkers = [], measureOn = false;

function startMeasure() {
    if (!evtMap) return;
    measureMarkers.forEach(m => evtMap.removeLayer(m));
    measurePoints = []; measureMarkers = [];
    measureOn = true;
    evtMap.getContainer().style.cursor = 'crosshair';
    evtMap.on('click', measureClick);
    evtMap.once('dblclick', function (e) { L.DomEvent.stopPropagation(e); finishMeasure(); });
}

function stopMeasure() {
    if (!measureOn) return;
    measureOn = false;
    if (!evtMap) return;
    evtMap.getContainer().style.cursor = '';
    evtMap.off('click', measureClick);
}

function measureClick(e) {
    if (!measureOn || e.originalEvent?.detail === 2) return;
    L.DomEvent.stopPropagation(e);
    measurePoints.push(e.latlng);
    const dot = L.circleMarker(e.latlng, { radius: 5, color: '#00897b', fillColor: '#00897b', fillOpacity: 1, weight: 1.5, interactive: false }).addTo(evtMap);
    measureMarkers.push(dot);
    if (measurePoints.length >= 2) {
        const p1 = measurePoints[measurePoints.length - 2], p2 = measurePoints[measurePoints.length - 1];
        const line = L.polyline([p1, p2], { color: '#00897b', weight: 2, dashArray: '6 3', interactive: false }).addTo(evtMap);
        measureMarkers.push(line);
        const totalM = measurePoints.slice(1).reduce((t, p, i) => t + measurePoints[i].distanceTo(p), 0);
        const km = totalM >= 1000 ? (totalM / 1000).toFixed(2) + 'km' : Math.round(totalM) + 'm';
        const mi = (totalM / 1609.34).toFixed(2) + 'mi';
        const lbl = L.marker(p2, {
            icon: L.divIcon({ className: '', html: `<div style="background:#003366;color:#fff;font-size:10px;font-weight:bold;padding:2px 7px;border-radius:3px;white-space:nowrap;">${km} / ${mi}</div>`, iconAnchor: [-6, -10] }),
            interactive: false
        }).addTo(evtMap);
        measureMarkers.push(lbl);
    }
}

function finishMeasure() {
    stopMeasure();
    evtTool = 'pin';
    document.getElementById('tool-pin-btn').classList.add('tool-active');
    document.getElementById('tool-measure-btn').classList.remove('tool-active');
}

var w3wOn = false;

function toggleW3wMode() {
    w3wOn = !w3wOn;
    document.getElementById('tool-w3w-btn').classList.toggle('tool-active', w3wOn);
    if (!evtMap) return;
    if (w3wOn) evtMap.on('click', w3wClick);
    else evtMap.off('click', w3wClick);
}

function w3wClick(e) {
    if (!w3wOn) return;
    L.DomEvent.stopPropagation(e);
    const lat = e.latlng.lat.toFixed(6), lng = e.latlng.lng.toFixed(6);
    const popup = L.popup().setLatLng(e.latlng).setContent(
        `<div style="font-family:Arial,sans-serif;min-width:160px;text-align:center;padding:4px;">
            <div style="font-size:11px;color:#e65c00;font-weight:bold;margin-bottom:4px;">/// What3Words</div>
            <div style="font-size:11px;color:#6b7f96;">Looking up…</div></div>`
    ).openOn(evtMap);
    const W3W_KEY = 'IJ8DEDUN';
    if (!W3W_KEY) {
        popup.setContent(`<div style="font-family:Arial,sans-serif;min-width:140px;font-size:11px;">
            <div style="font-weight:bold;color:#e65c00;margin-bottom:4px;">/// Coordinates</div>
            <strong>${lat}</strong>, <strong>${lng}</strong>
        </div>`);
        return;
    }
    fetch(`https://api.what3words.com/v3/convert-to-3wa?coordinates=${lat}%2C${lng}&language=en&format=json&key=${W3W_KEY}`)
        .then(r => r.json())
        .then(d => {
            const words = d.words;
            if (!words) { popup.setContent(`<div style="font-family:Arial;font-size:11px;padding:4px;">${lat}, ${lng}</div>`); return; }
            popup.setContent(`<div style="font-family:Arial,sans-serif;min-width:160px;padding:4px 2px;">
                <div style="font-size:10px;font-weight:bold;color:#e65c00;margin-bottom:5px;">/// What3Words</div>
                <div style="font-size:15px;font-weight:bold;color:#003366;margin-bottom:6px;">///\${words}</div>
                <div style="font-size:10px;color:#9aa3ae;margin-bottom:6px;">${lat}, ${lng}</div>
                <button onclick="navigator.clipboard.writeText('///\${words}').then(()=>this.textContent='✓ Copied!')"
                    style="font-size:10px;background:#003366;color:#fff;border:none;padding:3px 8px;cursor:pointer;font-family:Arial;width:100%;">
                    Copy ///\${words}
                </button></div>`);
        })
        .catch(() => popup.setContent(`<div style="font-family:Arial;font-size:11px;padding:4px;">${lat}, ${lng}</div>`));
}

function fetchEventWeather() {
    const lat = document.getElementById('event-lat').value;
    const lng = document.getElementById('event-lng').value;
    if (!lat || !lng) { alert('Place a location pin first.'); return; }
    const startsEl  = document.querySelector('[name=starts_at]');
    const eventDate = startsEl?.value ? startsEl.value.split('T')[0] : new Date().toISOString().split('T')[0];
    const btn = document.getElementById('tool-weather-btn');
    btn.textContent = '⌛ Loading…';
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&hourly=temperature_2m,precipitation_probability,windspeed_10m,winddirection_10m,weathercode&timezone=Europe%2FLondon&start_date=${eventDate}&end_date=${eventDate}`;
    fetch(url).then(r => r.json()).then(d => {
        btn.textContent = '🌬 Forecast';
        const h = d.hourly;
        if (!h) return;
        const idx    = Math.max(0, h.time?.findIndex(t => t.includes('T12:')) ?? 6);
        const temp   = Math.round(h.temperature_2m?.[idx] ?? 0);
        const wind   = Math.round(h.windspeed_10m?.[idx] ?? 0);
        const dir    = Math.round(h.winddirection_10m?.[idx] ?? 0);
        const precip = Math.round(h.precipitation_probability?.[idx] ?? 0);
        const code   = h.weathercode?.[idx] ?? 0;
        document.getElementById('weather-icon').textContent    = wCode2Icon(code);
        document.getElementById('weather-summary').textContent = `${wCode2Label(code)} · ${temp}°C`;
        document.getElementById('weather-detail').textContent  = `Wind: ${wind}km/h from ${deg2compass(dir)} · Rain: ${precip}%`;
        document.getElementById('wind-label').textContent      = `${wind}km/h`;
        document.getElementById('wind-needle').setAttribute('transform', `rotate(${dir} 18 18)`);
        document.getElementById('weather-badge').classList.add('visible');
    }).catch(() => { btn.textContent = '🌬 Forecast'; alert('Could not load forecast.'); });
}

const _wLabels = ['Clear sky','Partly cloudy','Overcast','','Foggy','','','','','','Drizzle','','','','','','','','','','Rain','','','','','','','','','','Snow','','','','','Rain showers','','','','','','','','','','','','','','','','Thunderstorm'];
function wCode2Label(c) { return _wLabels[Math.min(c, 99)] || (c <= 3 ? 'Partly cloudy' : c <= 9 ? 'Overcast' : c <= 49 ? 'Precipitation' : c <= 69 ? 'Rain' : c <= 79 ? 'Snow' : c <= 94 ? 'Showers' : 'Thunderstorm'); }
function wCode2Icon(c)  { return c === 0 ? '☀️' : c <= 3 ? '⛅' : c <= 9 ? '🌫' : c <= 39 ? '🌦' : c <= 49 ? '🌨' : c <= 69 ? '🌧' : c <= 79 ? '❄️' : c <= 94 ? '🌦' : '⛈'; }
function deg2compass(d) { return ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][Math.round(d / 45) % 8]; }

function fetchElevationForRoute() {
    if (evtRouteLayers.length === 0) return;
    const geo = evtRouteLayers[0].layer.toGeoJSON().geometry;
    if (!geo?.coordinates) return;
    const coords  = geo.type === 'LineString' ? geo.coordinates : geo.coordinates[0];
    const step    = Math.max(1, Math.floor(coords.length / 80));
    const sampled = coords.filter((_, i) => i % step === 0);
    if (sampled.length < 2) return;
    fetch('https://api.open-elevation.com/api/v1/lookup', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ locations: sampled.map(c => ({ latitude: c[1], longitude: c[0] })) })
    })
    .then(r => r.json())
    .then(d => { if (d.results?.length >= 2) renderElevChart(d.results.map(r => r.elevation), sampled); })
    .catch(() => {});
}

function renderElevChart(elevs, coords) {
    const W = 600, H = 90, pL = 8, pR = 8, pT = 10, pB = 22;
    const minE = Math.min(...elevs), maxE = Math.max(...elevs);
    const range = Math.max(maxE - minE, 10);
    let cum = [0];
    for (let i = 1; i < coords.length; i++) {
        const dlat = (coords[i][1] - coords[i-1][1]) * 111320;
        const dlng = (coords[i][0] - coords[i-1][0]) * 111320 * Math.cos(coords[i][1] * Math.PI / 180);
        cum.push(cum[cum.length - 1] + Math.sqrt(dlat * dlat + dlng * dlng));
    }
    const td = cum[cum.length - 1], xS = (W - pL - pR) / td, yS = (H - pT - pB) / range;
    const pts  = elevs.map((e, i) => [pL + cum[i] * xS, H - pB - (e - minE) * yS]);
    const path = `M${pts[0][0]},${pts[0][1]} ` + pts.slice(1).map(p => `L${p[0]},${p[1]}`).join(' ');
    const area = path + ` L${pts[pts.length-1][0]},${H-pB} L${pL},${H-pB} Z`;
    const gain = Math.round(elevs.map((e, i) => i > 0 ? Math.max(0, e - elevs[i-1]) : 0).reduce((a, b) => a + b, 0));
    document.getElementById('elev-stats').textContent = `${minE}m – ${maxE}m · +${gain}m · ${(td / 1000).toFixed(1)}km`;
    document.getElementById('elev-chart').innerHTML = `
        <defs><linearGradient id="eg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#7c3aed" stop-opacity=".45"/>
            <stop offset="100%" stop-color="#7c3aed" stop-opacity=".05"/>
        </linearGradient></defs>
        <path d="${area}" fill="url(#eg)"/>
        <path d="${path}" fill="none" stroke="#7c3aed" stroke-width="1.5"/>
        <text x="${pL}" y="${H-5}" font-size="8" fill="#6b7f96" font-family="Arial">0</text>
        <text x="${W-pR}" y="${H-5}" font-size="8" fill="#6b7f96" font-family="Arial" text-anchor="end">${(td/1000).toFixed(1)}km</text>
        <text x="${pL}" y="${pT+8}" font-size="8" fill="#6b7f96" font-family="Arial">${maxE}m</text>`;
    document.getElementById('elevation-panel').classList.add('visible');
}

function savePolygon() {
    if (!evtPolyLayer) return;
    document.getElementById('event-polygon').value = JSON.stringify(evtPolyLayer.toGeoJSON().geometry);
}

function applyEvtMask() {
    removeEvtMask();
    if (!evtMap || !evtPolyLayer) return;
    const geo = evtPolyLayer.toGeoJSON().geometry;
    if (!geo || !geo.coordinates) return;
    const worldRing = [[-90, -180], [-90, 180], [90, 180], [90, -180], [-90, -180]];
    const polys     = geo.type === 'MultiPolygon' ? geo.coordinates : [geo.coordinates];
    polys.forEach(function (rings) {
        const mask = L.geoJSON(
            { type: 'Feature', geometry: { type: 'Polygon', coordinates: [worldRing, rings[0]] } },
            { style: { color: 'transparent', weight: 0, fillColor: '#001f40', fillOpacity: 0.40, className: 'evt-mask-layer' }, interactive: false }
        ).addTo(evtMap);
        mask.eachLayer(function (l) {
            if (l._path) { l._path.style.pointerEvents = 'none'; l._path.setAttribute('pointer-events', 'none'); }
        });
        evtMaskLayers.push(mask);
    });
}

function removeEvtMask() {
    evtMaskLayers.forEach(m => { try { evtMap.removeLayer(m); } catch (e) {} });
    evtMaskLayers = [];
}

function deletePolygon() {
    evtMap.closePopup();
    if (evtPolyLayer) {
        try { evtDrawn.removeLayer(evtPolyLayer); } catch (e) {}
        evtMap.removeLayer(evtPolyLayer);
        evtPolyLayer = null;
    }
    removeEvtMask();
    document.getElementById('event-polygon').value      = '';
    document.getElementById('event-polygon-name').value = '';
    document.getElementById('poly-status').style.display = 'none';
}

function saveRoutes() {
    const arr = evtRouteLayers.map(r => ({ id: r.id, name: r.name, geometry: r.layer.toGeoJSON().geometry }));
    document.getElementById('event-route').value = arr.length ? JSON.stringify(arr) : '';
    setTimeout(fetchElevationForRoute, 200);
}

function updateRouteStatus() {
    const el = document.getElementById('route-status');
    if (!el) return;
    if (evtRouteLayers.length === 0) { el.style.display = 'none'; return; }
    el.style.display = 'flex';
    const lbl = document.getElementById('route-count-label');
    if (lbl) lbl.textContent = evtRouteLayers.length + ' route' + (evtRouteLayers.length > 1 ? 's' : '') + ' drawn';
}

function restoreRouteLayer(id, name, geometry) {
    if (!geometry || !geometry.coordinates) return;
    const latlngs = (geometry.type === 'LineString' ? geometry.coordinates : geometry.coordinates[0])
        .map(c => [c[1], c[0]]);
    const layer = L.polyline(latlngs, { color: '#7c3aed', weight: 4, opacity: .85 });
    evtDrawn.addLayer(layer);
    layer.addTo(evtMap);
    evtRouteLayers.push({ id, name, layer });
    bindRoutePopup(id);
}

function deleteRoute(id) {
    evtMap.closePopup();
    const idx = evtRouteLayers.findIndex(r => r.id === id);
    if (idx === -1) return;
    const r = evtRouteLayers[idx];
    try { evtDrawn.removeLayer(r.layer); } catch (e) {}
    evtMap.removeLayer(r.layer);
    evtRouteLayers.splice(idx, 1);
    saveRoutes(); updateRouteStatus();
}

function updateRouteNameInArray(id, name) {
    const r = evtRouteLayers.find(x => x.id === id);
    if (r) { r.name = name; saveRoutes(); }
}

function clearAllRoutes(silent) {
    if (activeEditHandler) { try { activeEditHandler.disable(); } catch (e) {} activeEditHandler = null; hideEditBar(); }
    evtRouteLayers.forEach(function (r) {
        try { if (evtDrawn) evtDrawn.removeLayer(r.layer); } catch (e) {}
        if (evtMap) evtMap.removeLayer(r.layer);
    });
    evtRouteLayers = [];
    if (!silent) { saveRoutes(); updateRouteStatus(); }
}

function clearEventMap() {
    if (!evtMap) return;
    if (activeEditHandler) { try { activeEditHandler.disable(); } catch (e) {} activeEditHandler = null; }
    const bar = document.getElementById('edit-mode-bar');
    if (bar) bar.style.display = 'none';
    if (evtMarker)    { evtMap.removeLayer(evtMarker); evtMarker = null; }
    if (evtPolyLayer) { try { evtDrawn.removeLayer(evtPolyLayer); } catch (e) {} evtMap.removeLayer(evtPolyLayer); evtPolyLayer = null; }
    removeEvtMask();
    clearAllRoutes(true);
    if (evtDrawn) evtDrawn.clearLayers();
    document.getElementById('event-lat').value          = '';
    document.getElementById('event-lng').value          = '';
    document.getElementById('event-polygon').value      = '';
    document.getElementById('event-polygon-name').value = '';
    document.getElementById('event-route').value        = '';
    document.getElementById('disp-evt-lat').value       = '';
    document.getElementById('disp-evt-lng').value       = '';
    document.getElementById('poly-status').style.display  = 'none';
    document.getElementById('route-status').style.display = 'none';
    clearAllPois();
}

function mapPopupHtml(title, colour, bodyHtml) {
    return `<div style="font-family:Arial,sans-serif;min-width:200px;padding:2px 0;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:${colour};margin-bottom:7px;">${title}</div>
        ${bodyHtml}
    </div>`;
}

function popupBtn(label, colour, bg, onclick) {
    return `<button type="button" onclick="${onclick}"
        style="display:block;width:100%;margin-top:4px;padding:5px 10px;
               background:${bg};border:1px solid ${colour};color:${colour};
               font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;
               font-family:Arial,sans-serif;cursor:pointer;text-align:left;">${label}</button>`;
}

function nameInputHtml(inputId, currentName, placeholder, onInput) {
    return `<div style="margin-bottom:5px;">
        <div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">Name</div>
        <div style="display:flex;gap:3px;">
            <input type="text" id="${inputId}" value="${escHtml(currentName)}" placeholder="${placeholder}"
                style="flex:1;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;"
                oninput="${onInput}">
            <button type="button" onclick="evtMap.closePopup()"
                style="padding:4px 8px;background:#003366;border:none;color:#fff;font-size:11px;font-weight:bold;font-family:Arial,sans-serif;cursor:pointer;">✓</button>
        </div>
    </div>`;
}

function bindPolyPopup(layer) {
    layer.on('click', function (e) {
        if (evtTool === 'poi') return;
        L.DomEvent.stopPropagation(e);
        const currentName = document.getElementById('event-polygon-name').value || 'Site Boundary';
        const html = mapPopupHtml('✏ Site Boundary', '#1a6b3c',
            nameInputHtml('poly-name-popup', currentName, 'Site Boundary…',
                "document.getElementById('event-polygon-name').value=this.value;updatePolyStatusName(this.value)") +
            popupBtn('⬡ Edit Vertices', '#003366', '#e8eef5', 'startVertexEdit()') +
            popupBtn('✕ Delete Boundary', '#C8102E', '#fdf0f2', 'deletePolygon()')
        );
        L.popup({ maxWidth: 240, closeButton: true }).setLatLng(e.latlng).setContent(html).openOn(evtMap);
    });
    layer.on('mouseover', function () { evtMap.getContainer().style.cursor = 'pointer'; });
    layer.on('mouseout',  function () { evtMap.getContainer().style.cursor = ''; });
}

function bindRoutePopup(routeId) {
    const routeObj = evtRouteLayers.find(r => r.id === routeId);
    if (!routeObj) return;
    const layer = routeObj.layer;
    layer.off('click'); layer.off('mouseover'); layer.off('mouseout');
    layer.on('click', function (e) {
        if (evtTool === 'poi') return;
        L.DomEvent.stopPropagation(e);
        const html = mapPopupHtml('〰 Route', '#7c3aed',
            nameInputHtml('route-name-popup-' + routeId, routeObj.name, 'Route name…',
                `updateRouteNameInArray('${routeId}', this.value)`) +
            popupBtn('⬡ Edit Vertices', '#7c3aed', '#f5f3ff', `startVertexEdit('${routeId}')`) +
            popupBtn('✕ Delete Route',  '#C8102E', '#fdf0f2', `deleteRoute('${routeId}')`)
        );
        L.popup({ maxWidth: 240, closeButton: true }).setLatLng(e.latlng).setContent(html).openOn(evtMap);
    });
    layer.on('mouseover', function () { evtMap.getContainer().style.cursor = 'pointer'; });
    layer.on('mouseout',  function () { evtMap.getContainer().style.cursor = ''; });
}

function startVertexEdit(routeId) {
    evtMap.closePopup();
    if (!evtDrawn || evtDrawn.getLayers().length === 0) return;
    if (activeEditHandler) { try { activeEditHandler.disable(); } catch (e) {} }
    try {
        activeEditHandler = new L.EditToolbar.Edit(evtMap, { featureGroup: evtDrawn });
        activeEditHandler.enable();
    } catch (e) { console.warn('Vertex edit error:', e); return; }
    document.getElementById('edit-mode-bar').style.display = 'flex';
    evtMap.off('click');
}

function saveVertexEdit() {
    if (activeEditHandler) { try { activeEditHandler.save(); activeEditHandler.disable(); } catch (e) {} activeEditHandler = null; }
    savePolygon(); applyEvtMask(); saveRoutes();
    if (evtPolyLayer) {
        const geo = evtPolyLayer.toGeoJSON();
        updatePolyStatus(geo.geometry.coordinates[0].length - 1, document.getElementById('event-polygon-name').value || 'Site Boundary');
    }
    updateRouteStatus();
    evtRouteLayers.forEach(r => bindRoutePopup(r.id));
    hideEditBar(); rebindMapClick();
}

function cancelVertexEdit() {
    if (activeEditHandler) { try { activeEditHandler.revertLayers(); activeEditHandler.disable(); } catch (e) {} activeEditHandler = null; }
    hideEditBar(); rebindMapClick();
}

function hideEditBar() { document.getElementById('edit-mode-bar').style.display = 'none'; }

function rebindMapClick() {
    evtMap.on('click', function (e) {
        if (evtTool === 'pin') {
            placeEvtPin(e.latlng.lat, e.latlng.lng, true);
        } else if (evtTool === 'poi') {
            const poi = makePoi(e.latlng.lat, e.latlng.lng);
            poi.grid_ref = latLngToOsgb(e.latlng.lat, e.latlng.lng);
            evtPois.push(poi);
            placePinOnMap(poi);
            renderPoiList();
            savePois();
            setTimeout(function () {
                const ni = document.querySelector('#poi-row-' + poi.id + ' .poi-name-input');
                if (ni) ni.focus();
            }, 50);
        }
    });
}

function updatePolyStatusName(name) {
    const el = document.getElementById('poly-status-name');
    if (el) el.textContent = name || 'Site Boundary';
}

function updatePolyStatus(vertCount, name) {
    document.getElementById('poly-status').style.display = 'flex';
    document.getElementById('poly-vertex-count').textContent = vertCount;
    if (name) updatePolyStatusName(name);
}

const POI_TYPES = {
    entrance: { label: 'Entrance',   emoji: '🚪', colour: '#1a6b3c' },
    exit:     { label: 'Exit',       emoji: '🚪', colour: '#C8102E' },
    car_park: { label: 'Car Park',   emoji: '🅿',  colour: '#003366' },
    medical:  { label: 'Medical',    emoji: '🩺',  colour: '#dc2626' },
    control:    { label: 'Control',    emoji: '📡',  colour: '#7c3aed' },
    checkpoint: { label: 'Checkpoint',  emoji: '🏁',  colour: '#0369a1' },
    repeater:   { label: 'Repeater',    emoji: '📶',  colour: '#059669' },
    hazard:     { label: 'Hazard',      emoji: '⚠',  colour: '#d97706' },
    info:     { label: 'Info Point', emoji: 'ℹ',  colour: '#0284c7' },
    custom:   { label: 'Custom',     emoji: '🚩',  colour: '#C8102E' },
};

var evtPois    = [];
var poiMarkers = {};

function makePoi(lat, lng) {
    return {
        id:          'poi-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6),
        type:        'entrance', name: '', description: '', grid_ref: '', w3w: '',
        lat:         parseFloat(lat.toFixed(7)), lng: parseFloat(lng.toFixed(7)),
        colour:      POI_TYPES.entrance.colour,
    };
}

function poiIcon(poi) {
    const pt  = POI_TYPES[poi.type] || POI_TYPES.custom;
    const col = poi.colour || pt.colour;
    return L.divIcon({
        className: '',
        html: `<div style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:${col};border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4);font-size:14px;line-height:1;">${pt.emoji}</div>`,
        iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -30],
    });
}

function placePinOnMap(poi) {
    if (!evtMap) return;
    const marker = L.marker([poi.lat, poi.lng], { icon: poiIcon(poi), draggable: true, title: poi.name || (POI_TYPES[poi.type]?.label ?? 'POI') });

    function buildPoiPopupContent() {
        const pt = POI_TYPES[poi.type] || POI_TYPES.custom;
        const typeOptions = Object.entries(POI_TYPES).map(([k, v]) => `<option value="${k}" ${poi.type === k ? 'selected' : ''}>${v.emoji} ${v.label}</option>`).join('');
        return `<div style="font-family:Arial,sans-serif;min-width:200px;padding:2px 0;">
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:${poi.colour || pt.colour};margin-bottom:6px;">${pt.emoji} Point of Interest</div>
            <div style="margin-bottom:4px;"><div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">Type</div>
            <select onchange="poiPopupTypeChange('${poi.id}', this.value, this)" style="width:100%;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;background:#fff;">${typeOptions}</select></div>
            <div style="margin-bottom:4px;"><div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">Name</div>
            <input type="text" id="pop-name-${poi.id}" value="${escHtml(poi.name)}" placeholder="${pt.label} name…" style="width:100%;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;" oninput="poiPopupFieldChange('${poi.id}','name',this.value)"></div>
            <div style="margin-bottom:4px;"><div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">Description</div>
            <input type="text" id="pop-desc-${poi.id}" value="${escHtml(poi.description)}" placeholder="Optional description…" style="width:100%;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;" oninput="poiPopupFieldChange('${poi.id}','description',this.value)"></div>
            <div style="margin-bottom:4px;"><div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">OS Grid Ref (6-figure)</div>
            <input type="text" id="pop-grid-${poi.id}" value="${escHtml(poi.grid_ref)}" placeholder="e.g. SJ385908" style="width:100%;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;font-weight:bold;letter-spacing:.04em;" oninput="poiPopupFieldChange('${poi.id}','grid_ref',this.value)"></div>
            <div style="margin-bottom:6px;"><div style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:2px;">What3Words</div>
            <div style="display:flex;gap:4px;">
            <input type="text" id="pop-w3w-${poi.id}" value="${escHtml(poi.w3w)}" placeholder="word.word.word" style="flex:1;border:1px solid #dde2e8;padding:4px 6px;font-size:12px;font-family:Arial,sans-serif;outline:none;color:#e65c00;font-weight:bold;" oninput="poiPopupFieldChange('${poi.id}','w3w',this.value)">
            <button type="button" onclick="lookupPoiW3w('${poi.id}')" style="padding:4px 7px;background:#fff3e0;border:1px solid #e65c00;color:#e65c00;font-size:10px;font-weight:bold;font-family:Arial,sans-serif;cursor:pointer;white-space:nowrap;">/// Lookup</button></div></div>
            <button type="button" onclick="removePoi('${poi.id}');evtMap.closePopup();" style="display:block;width:100%;padding:5px 10px;background:#fdf0f2;border:1px solid #C8102E;color:#C8102E;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;font-family:Arial,sans-serif;cursor:pointer;text-align:left;">✕ Delete POI</button>
        </div>`;
    }

    marker.bindPopup(buildPoiPopupContent, { maxWidth: 240 });
    marker.on('click', function(e) {
        if (evtTool === 'poi') { L.DomEvent.stopPropagation(e); evtMap.fire('click', { latlng: e.latlng, originalEvent: e.originalEvent }); }
    });
    marker.addTo(evtMap);
    poiMarkers[poi.id] = marker;
}

function renderPoiList() {
    const list = document.getElementById('poi-list');
    const countEl = document.getElementById('poi-count');
    if (!list) return;
    countEl.textContent = evtPois.length;
    if (!evtPois.length) {
        list.innerHTML = '<div style="padding:.5rem .85rem;font-size:12px;color:var(--text-muted);font-style:italic;">No POIs added yet. Select 🚩 Add POI then click the map.</div>';
        return;
    }
    list.innerHTML = evtPois.map(poi => {
        const pt = POI_TYPES[poi.type] || POI_TYPES.custom;
        const typeOptions = Object.entries(POI_TYPES).map(([k, v]) => `<option value="${k}" ${poi.type === k ? 'selected' : ''}>${v.emoji} ${v.label}</option>`).join('');
        return `<div class="poi-row" id="poi-row-${poi.id}">
            <div class="poi-dot" style="background:${poi.colour || pt.colour};"></div>
            <select class="poi-type-select" onchange="updatePoiType('${poi.id}',this.value)">${typeOptions}</select>
            <input type="text" class="poi-name-input" value="${escHtml(poi.name)}" placeholder="${pt.label} name…" oninput="updatePoiField('${poi.id}','name',this.value)">
            <input type="text" class="poi-desc-input" value="${escHtml(poi.description)}" placeholder="Description (optional)…" oninput="updatePoiField('${poi.id}','description',this.value)">
            <input type="text" class="poi-grid-input" value="${escHtml(poi.grid_ref || '')}" placeholder="Grid ref…" style="width:80px;font-weight:bold;letter-spacing:.04em;font-size:11px;" oninput="updatePoiField('${poi.id}','grid_ref',this.value)" title="6-figure OS grid reference">
            <input type="text" class="poi-w3w-input" value="${escHtml(poi.w3w || '')}" placeholder="///word.word.word" style="width:110px;color:#e65c00;font-size:11px;" oninput="updatePoiField('${poi.id}','w3w',this.value)" title="What3Words address">
            <button type="button" class="poi-locate" title="Fly to" onclick="flyToPoi('${poi.id}')">⌖</button>
            <button type="button" class="poi-del" title="Remove POI" onclick="removePoi('${poi.id}')">✕</button>
        </div>`;
    }).join('');
}

function poiPopupFieldChange(id, field, value) {
    const idx = evtPois.findIndex(x => x.id === id);
    if (idx === -1) return;
    evtPois[idx][field] = value;
    const classMap = { name: 'poi-name-input', description: 'poi-desc-input', grid_ref: 'poi-grid-input', w3w: 'poi-w3w-input' };
    const input = document.querySelector('#poi-row-' + id + ' .' + classMap[field]);
    if (input) input.value = value;
    savePois();
}

function poiPopupTypeChange(id, type, selectEl) { updatePoiType(id, type); }

function updatePoiField(id, field, value) {
    const idx = evtPois.findIndex(x => x.id === id);
    if (idx === -1) return;
    evtPois[idx][field] = value;
    savePois();
}

function updatePoiType(id, type) {
    const idx = evtPois.findIndex(x => x.id === id);
    if (idx === -1) return;
    evtPois[idx].type   = type;
    evtPois[idx].colour = POI_TYPES[type]?.colour || '#C8102E';
    if (poiMarkers[id] && evtMap) { evtMap.removeLayer(poiMarkers[id]); delete poiMarkers[id]; placePinOnMap(evtPois[idx]); }
    const dot = document.querySelector('#poi-row-' + id + ' .poi-dot');
    if (dot) dot.style.background = evtPois[idx].colour;
    savePois();
}

function removePoi(id) {
    if (poiMarkers[id] && evtMap) { evtMap.removeLayer(poiMarkers[id]); delete poiMarkers[id]; }
    evtPois = evtPois.filter(x => x.id !== id);
    renderPoiList(); savePois();
}

function flyToPoi(id) {
    const poi = evtPois.find(x => x.id === id);
    if (!poi || !evtMap) return;
    evtMap.flyTo([poi.lat, poi.lng], 17, { animate: true, duration: .8 });
    if (poiMarkers[id]) poiMarkers[id].openPopup();
}

function clearAllPois() {
    Object.values(poiMarkers).forEach(m => { if (evtMap) evtMap.removeLayer(m); });
    poiMarkers = {}; evtPois = [];
    renderPoiList(); savePois();
}

function savePois() {
    const el = document.getElementById('event-pois');
    if (el) el.value = JSON.stringify(evtPois);
}

function lookupPoiW3w(id) {
    const poi = evtPois.find(p => p.id === id);
    if (!poi) return;
    const btn = document.querySelector('#pop-w3w-' + id)?.nextElementSibling;
    if (btn) btn.textContent = '…';
    const W3W_KEY = 'IJ8DEDUN';
    if (!W3W_KEY) { if (btn) btn.textContent = '/// Lookup'; return; }
    fetch(`https://api.what3words.com/v3/convert-to-3wa?coordinates=${poi.lat}%2C${poi.lng}&language=en&format=json&key=${W3W_KEY}`)
        .then(r => r.json())
        .then(d => {
            if (d.words) {
                poi.w3w = d.words;
                const input = document.getElementById('pop-w3w-' + id);
                if (input) { input.value = d.words; input.style.color = '#e65c00'; }
                const rowInput = document.querySelector('#poi-row-' + id + ' .poi-w3w-input');
                if (rowInput) rowInput.value = d.words;
                savePois();
            }
            if (btn) btn.textContent = '/// Lookup';
        })
        .catch(() => { if (btn) btn.textContent = '/// Lookup'; });
}

function latLngToOsgb(lat, lng) {
    try {
        const a = 6378137.000, b = 6356752.3142, e2 = 1 - (b * b) / (a * a);
        const phi = lat * Math.PI / 180, lam = lng * Math.PI / 180;
        const nu  = a / Math.sqrt(1 - e2 * Math.sin(phi) * Math.sin(phi));
        const tx = -446.448, ty = 125.157, tz = -542.060;
        const rx = -0.1502 / 206265, ry = -0.2470 / 206265, rz = -0.8421 / 206265, s = 20.4894e-6;
        const x = nu * Math.cos(phi) * Math.cos(lam), y = nu * Math.cos(phi) * Math.sin(lam), z = nu * (1 - e2) * Math.sin(phi);
        const x2 = tx + (1 + s) * (+x + rz * y - ry * z), y2 = ty + (1 + s) * (-rz * x + y + rx * z), z2 = tz + (1 + s) * (+ry * x - rx * y + z);
        const a2 = 6377563.396, b2 = 6356256.910, e22 = 1 - (b2 * b2) / (a2 * a2);
        let ph = Math.atan2(z2, Math.sqrt(x2 * x2 + y2 * y2) * (1 - e22));
        for (let i = 0; i < 10; i++) { const nu2 = a2 / Math.sqrt(1 - e22 * Math.sin(ph) * Math.sin(ph)); ph = Math.atan2(z2 + e22 * nu2 * Math.sin(ph), Math.sqrt(x2 * x2 + y2 * y2)); }
        const lm = Math.atan2(y2, x2);
        const F0 = 0.9996012717, lat0 = 49 * Math.PI / 180, lon0 = -2 * Math.PI / 180, N0 = -100000, E0 = 400000, n = (a2 - b2) / (a2 + b2);
        const nu3 = a2 * F0 / Math.sqrt(1 - e22 * Math.sin(ph) * Math.sin(ph)), rho = a2 * F0 * (1 - e22) / Math.pow(1 - e22 * Math.sin(ph) * Math.sin(ph), 1.5), eta2 = nu3 / rho - 1;
        const M = b2 * F0 * ((1 + n + 5/4*n*n + 5/4*n*n*n) * (ph - lat0) - (3*n + 3*n*n + 21/8*n*n*n) * Math.sin(ph - lat0) * Math.cos(ph + lat0) + (15/8*n*n + 15/8*n*n*n) * Math.sin(2*(ph - lat0)) * Math.cos(2*(ph + lat0)) - (35/24*n*n*n) * Math.sin(3*(ph - lat0)) * Math.cos(3*(ph + lat0)));
        const I = M + N0, II = nu3 / 2 * Math.sin(ph) * Math.cos(ph);
        const III = nu3 / 24 * Math.sin(ph) * Math.pow(Math.cos(ph), 3) * (5 - Math.tan(ph) * Math.tan(ph) + 9 * eta2);
        const IIIA = nu3 / 720 * Math.sin(ph) * Math.pow(Math.cos(ph), 5) * (61 - 58 * Math.tan(ph) * Math.tan(ph) + Math.pow(Math.tan(ph), 4));
        const IV = nu3 * Math.cos(ph), V = nu3 / 6 * Math.pow(Math.cos(ph), 3) * (nu3 / rho - Math.tan(ph) * Math.tan(ph));
        const VI = nu3 / 120 * Math.pow(Math.cos(ph), 5) * (5 - 18 * Math.tan(ph) * Math.tan(ph) + Math.pow(Math.tan(ph), 4) + 14 * eta2 - 58 * Math.tan(ph) * Math.tan(ph) * eta2);
        const dl = lm - lon0;
        const N = Math.round(I + II*dl*dl + III*dl*dl*dl*dl + IIIA*dl*dl*dl*dl*dl*dl);
        const E = Math.round(E0 + IV*dl + V*dl*dl*dl + VI*dl*dl*dl*dl*dl);
        const gridLetters = 'VWXYZQRSTULMNOPFGHJKABCDE';
        const e100 = Math.floor(E / 100000), n100 = Math.floor(N / 100000);
        const l1 = gridLetters.charAt((19 - n100) * 5 + e100 % 5 + Math.floor(e100 / 5) * 25);
        const l2 = gridLetters.charAt((4 - n100 % 5) * 5 + e100 % 5);
        return l1 + l2 + String(E % 100000).padStart(5, '0').slice(0, 3) + String(N % 100000).padStart(5, '0').slice(0, 3);
    } catch (e) { return ''; }
}

function escHtml(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('event-map-picker');
    if (!mapEl) return;

    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) { initEventMap(); obs.disconnect(); }
        }, { threshold: 0.1 });
        obs.observe(mapEl);
    } else {
        initEventMap();
    }

    const locInput = document.querySelector('[name=location]');
    const dispLoc  = document.getElementById('disp-evt-loc');
    if (locInput && dispLoc) {
        locInput.addEventListener('input', () => dispLoc.value = locInput.value);
    }

    const poisRaw = document.getElementById('event-pois')?.value;
    if (poisRaw) {
        try {
            const loaded = JSON.parse(poisRaw);
            if (Array.isArray(loaded) && loaded.length > 0) {
                evtPois = loaded;
                const waitForMap = setInterval(function () {
                    if (evtMap) {
                        clearInterval(waitForMap);
                        evtPois.forEach(poi => placePinOnMap(poi));
                        renderPoiList();
                    }
                }, 80);
            }
        } catch (e) { console.warn('POI restore error:', e); }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && evtTool === 'poi') setMapTool('pin');
    });
});
</script>

{{-- Map fullscreen overlay --}}
<div id="map-fullscreen-overlay">
    <div id="map-fullscreen-topbar">
        <span style="color:#fff;font-size:13px;font-weight:bold;flex:1;">🗺 Event Map — Fullscreen Mode</span>
        <button type="button" onclick="evtMapExitFullscreen()" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.3);color:#fff;padding:.3rem .9rem;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">✕ Exit Fullscreen</button>
    </div>
</div>
@endsection