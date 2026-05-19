@extends('layouts.admin')
@section('title', 'Notifications — Admin')
@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">

<style>
:root {
    --navy:       #001f40;
    --navy-mid:   #003366;
    --navy-light: #004080;
    --navy-faint: #e8eef5;
    --red:        #C8102E;
    --red-faint:  rgba(200,16,46,.08);
    --red-glow:   rgba(200,16,46,.25);
    --teal:       #0288d1;
    --white:      #ffffff;
    --off-white:  #f7f9fc;
    --grey:       #f0f4f8;
    --grey-mid:   #dce4ee;
    --grey-dark:  #9aa3ae;
    --text:       #001f40;
    --text-mid:   #2d4a6b;
    --text-muted: #6b7f96;
    --green:      #1a7a3c;
    --green-bg:   rgba(26,122,60,.08);
    --amber:      #b45309;
    --border:     #dce4ee;
    --sans:       Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --mono:       Arial, 'Helvetica Neue', Helvetica, sans-serif;
    --shadow-xs:  0 1px 3px rgba(0,31,64,.07);
    --shadow-sm:  0 2px 10px rgba(0,31,64,.09);
    --shadow-md:  0 4px 20px rgba(0,31,64,.13);
    --shadow-lg:  0 8px 40px rgba(0,31,64,.18);
    --radius:     8px;
    --transition: all .18s ease;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: var(--sans); background: var(--grey); color: var(--text); font-size: 14px; line-height: 1.6; min-height: 100vh; }

/* ══ PAGE HEADER ══ */
.rn-header {
    background: var(--navy);
    border-bottom: 3px solid var(--red);
    position: sticky; top: 0; z-index: 200;
    box-shadow: 0 2px 20px rgba(0,0,0,.35);
}
.rn-header-inner {
    max-width: 1400px; margin: 0 auto;
    padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.rn-brand { display: flex; align-items: center; gap: .9rem; padding: .85rem 0; }
.rn-logo {
    background: var(--red); width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rn-logo span { font-size: 9px; font-weight: 700; color: #fff; letter-spacing: .08em; text-align: center; line-height: 1.2; text-transform: uppercase; font-family: var(--mono); }
.rn-brand-text {}
.rn-org { font-size: 14px; font-weight: 700; color: #fff; letter-spacing: .06em; text-transform: uppercase; font-family: var(--mono); }
.rn-sub { font-size: 10px; color: rgba(255,255,255,.4); margin-top: 2px; text-transform: uppercase; letter-spacing: .1em; font-family: var(--mono); }
.rn-header-right { display: flex; align-items: center; gap: .75rem; }
.rn-back {
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.75);
    text-decoration: none; border: 1px solid rgba(255,255,255,.2);
    padding: .35rem .9rem; transition: var(--transition);
    font-family: var(--mono); letter-spacing: .06em; text-transform: uppercase;
}
.rn-back:hover { background: rgba(255,255,255,.08); color: #fff; border-color: rgba(255,255,255,.4); }

/* ══ PAGE BAND ══ */
.page-band {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-xs);
    margin-bottom: 2rem;
}
.page-band-inner {
    max-width: 1400px; margin: 0 auto;
    padding: 1.5rem;
    display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.page-eyebrow {
    font-size: 10px; font-weight: 700; color: var(--red);
    text-transform: uppercase; letter-spacing: .2em;
    display: flex; align-items: center; gap: .45rem;
    margin-bottom: .35rem; font-family: var(--mono);
}
.page-eyebrow::before { content: ''; width: 14px; height: 2px; background: var(--red); display: inline-block; }
.page-title { font-size: 26px; font-weight: 700; color: var(--navy); line-height: 1.15; }
.page-desc { font-size: 13px; color: var(--text-muted); margin-top: .3rem; }
.page-band-meta {
    display: flex; align-items: center; gap: .5rem;
    font-size: 11px; color: var(--text-muted);
    background: var(--grey); border: 1px solid var(--border);
    padding: .4rem .85rem; font-family: var(--mono);
    flex-shrink: 0;
}
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.3;} }

/* ══ WRAP + GRID ══ */
.wrap { max-width: 1400px; margin: 0 auto; padding: 0 1.5rem 4rem; }
@media(max-width:600px){ .wrap { padding: 0 1rem 4rem; } }

.page-grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 2rem;
    align-items: start;
}
@media(max-width:1100px){ .page-grid { grid-template-columns: 380px 1fr; gap: 1.5rem; } }
@media(max-width:900px){ .page-grid { grid-template-columns: 1fr; } }

/* ══ ALERT ══ */
.alert-success {
    display: flex; align-items: center; gap: .65rem;
    margin-bottom: 1.5rem; padding: .75rem 1rem;
    background: var(--green-bg); border: 1px solid rgba(26,122,60,.25);
    border-left: 3px solid var(--green);
    font-size: 13px; font-weight: 600; color: var(--green);
    animation: fadeUp .3s ease;
}

/* ══ COMPOSE PANEL ══ */
.compose-terminal {
    background: var(--white);
    border: 1px solid var(--border);
    border-top: 3px solid var(--red);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.compose-terminal::before { display: none; }
.terminal-header {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--navy);
}
.terminal-title {
    display: flex; align-items: center; gap: .65rem;
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.9);
    text-transform: uppercase; letter-spacing: .1em;
}
.terminal-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--red); animation: blink 1.6s ease-in-out infinite; }
.terminal-body { padding: 1.25rem; background: var(--white); }

/* ══ PRIORITY GRID ══ */
.priority-section-label {
    font-size: 10px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .14em;
    margin-bottom: .5rem;
}
.priority-grid {
    display: grid; grid-template-columns: repeat(5,1fr); gap: .4rem;
    margin-bottom: .75rem;
}
.priority-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .2rem; padding: .65rem .2rem;
    border: 2px solid var(--border);
    cursor: pointer; font-family: var(--sans);
    font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    transition: var(--transition);
    background: var(--grey); color: var(--text-muted);
    text-align: center; line-height: 1.2;
    position: relative; overflow: hidden;
}
.priority-btn::after { display: none; }
.priority-btn:hover { border-color: var(--navy-mid); color: var(--navy); background: var(--navy-faint); }
.priority-btn.selected { color: #fff; box-shadow: var(--shadow-sm); }
.pb-icon { font-size: 16px; line-height: 1; }
.pb-num { font-size: 18px; font-weight: 700; line-height: 1; }

/* ══ EMAIL WARNING ══ */
.email-warning {
    display: none; align-items: center; gap: .65rem;
    padding: .6rem .9rem; margin-bottom: .85rem;
    font-size: 12px; font-weight: 700;
    border: 1px solid; letter-spacing: .02em;
}
.email-warning.show { display: flex; }

/* ══ PRIORITY LEGEND ══ */
.priority-legend {
    display: flex; flex-wrap: wrap; gap: .3rem;
    margin-bottom: 1rem; padding: .6rem .75rem;
    background: var(--grey); border: 1px solid var(--border);
}
.pl-chip {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: 9px; font-weight: 700; padding: 2px 7px;
    border: 1px solid; text-transform: uppercase; letter-spacing: .05em;
}

/* ══ FIELDS ══ */
.t-field { display: flex; flex-direction: column; gap: .3rem; margin-bottom: .85rem; }
.t-field label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: var(--text-muted);
    display: flex; align-items: center; gap: .4rem;
}
.t-field label .req { color: var(--red); }
.t-field input, .t-field textarea {
    background: var(--white); border: 1px solid var(--border);
    padding: .55rem .85rem; color: var(--text);
    font-family: var(--sans); font-size: 13px; outline: none; width: 100%;
    transition: border-color .15s, box-shadow .15s;
}
.t-field input::placeholder, .t-field textarea::placeholder { color: var(--grey-dark); }
.t-field input:focus, .t-field textarea:focus {
    border-color: var(--navy-mid);
    box-shadow: 0 0 0 3px rgba(0,51,102,.07);
}
.t-field textarea { resize: vertical; min-height: 80px; }
.t-field-error { font-size: 11px; color: var(--red); font-weight: 700; margin-top: 2px; }

/* ══ SEND TO TABS ══ */
.send-to-tabs { display: flex; gap: 0; margin-bottom: .85rem; border: 1px solid var(--border); }
.send-to-tab {
    flex: 1; padding: .5rem; font-family: var(--sans);
    font-size: 11px; font-weight: 700; text-align: center; cursor: pointer;
    border: none; background: var(--grey); color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .06em; transition: all .15s;
}
.send-to-tab.active { background: var(--navy); color: #fff; }
.send-to-tab:not(.active):hover { background: var(--navy-faint); color: var(--navy); }

/* ══ MEMBER LIST ══ */
.member-list-section { display: none; }
.member-list-section.show { display: block; }
.member-filter-input {
    width: 100%; padding: .5rem .85rem;
    background: var(--white); border: 1px solid var(--border);
    color: var(--text); font-family: var(--sans); font-size: 13px; outline: none;
    transition: var(--transition); margin-bottom: .5rem;
}
.member-filter-input::placeholder { color: var(--grey-dark); }
.member-filter-input:focus { border-color: var(--navy-mid); box-shadow: 0 0 0 3px rgba(0,51,102,.07); }

.member-list-wrap {
    border: 1px solid var(--border); max-height: 240px; overflow-y: auto;
    background: var(--white);
}
.member-list-wrap::-webkit-scrollbar { width: 4px; }
.member-list-wrap::-webkit-scrollbar-track { background: var(--grey); }
.member-list-wrap::-webkit-scrollbar-thumb { background: var(--grey-mid); }

.member-list-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .4rem .75rem; background: var(--grey);
    border-bottom: 1px solid var(--border);
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--text-muted);
    position: sticky; top: 0; z-index: 1;
}
.member-list-header button {
    font-size: 10px; font-weight: 700; color: var(--navy);
    background: none; border: none; cursor: pointer;
    font-family: var(--sans); text-transform: uppercase; letter-spacing: .06em;
    transition: color .15s;
}
.member-list-header button:hover { color: var(--red); }
.member-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .5rem .75rem; border-bottom: 1px solid var(--border);
    cursor: pointer; transition: background .1s; user-select: none;
}
.member-row:last-child { border-bottom: none; }
.member-row:hover { background: var(--navy-faint); }
.member-row.selected { background: #eef3ff; }
.member-row.hidden { display: none; }
.member-checkbox {
    width: 15px; height: 15px; border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .12s;
}
.member-row.selected .member-checkbox { background: var(--navy); border-color: var(--navy); }
.member-check-tick { font-size: 9px; color: #fff; display: none; }
.member-row.selected .member-check-tick { display: block; }
.member-av {
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--navy); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0; text-transform: uppercase;
}
.member-name { font-size: 12px; font-weight: 700; color: var(--text); flex: 1; }
.member-meta { font-size: 11px; color: var(--text-muted); }
.member-list-empty { padding: 1.25rem; text-align: center; font-size: 12px; color: var(--text-muted); }

.selected-summary {
    display: flex; align-items: center; justify-content: space-between;
    padding: .45rem .75rem;
    background: var(--navy-faint); border: 1px solid rgba(0,51,102,.15); border-top: none;
    font-size: 11px; font-weight: 700; color: var(--navy);
}
.selected-summary button {
    font-size: 10px; color: var(--red); background: none; border: none;
    cursor: pointer; font-family: var(--sans); font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
}
.selected-summary button:hover { text-decoration: underline; }

/* ══ SEND BUTTON ══ */
.send-btn {
    width: 100%; padding: .75rem;
    background: var(--navy); border: none; color: #fff;
    font-family: var(--sans); font-size: 13px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .1em;
    cursor: pointer; transition: var(--transition);
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    position: relative; overflow: hidden;
}
.send-btn::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,.06) 0%, transparent 50%);
    pointer-events: none;
}
.send-btn:hover { background: var(--navy-light); box-shadow: 0 4px 16px rgba(0,31,64,.25); transform: translateY(-1px); }
.send-btn:active { transform: translateY(0); }
.send-btn-divider { margin-top: .85rem; padding-top: .85rem; border-top: 1px solid var(--border); }

/* ══ PRIORITY REFERENCE ══ */
.priority-ref {
    background: var(--white); border: 1px solid var(--border);
    border-top: 3px solid var(--navy); box-shadow: var(--shadow-sm);
    margin-top: 1.25rem; overflow: hidden;
}
.priority-ref-head {
    padding: .75rem 1.15rem; background: var(--navy);
    display: flex; align-items: center; gap: .65rem;
}
.priority-ref-title {
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.8);
    text-transform: uppercase; letter-spacing: .1em; font-family: var(--mono);
}
.priority-ref-row {
    display: flex; align-items: center; gap: .85rem;
    padding: .65rem 1.15rem; border-bottom: 1px solid var(--border);
    transition: background .1s;
}
.priority-ref-row:last-child { border-bottom: none; }
.priority-ref-row:hover { background: var(--grey); }
.pref-icon {
    width: 34px; height: 34px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
}
.pref-info { flex: 1; }
.pref-name { font-size: 12px; font-weight: 700; color: var(--text); }
.pref-desc { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.pref-email-badge {
    font-size: 9px; font-weight: 700; padding: 2px 6px;
    background: #fff7ed; border: 1px solid #f59e0b; color: #78350f;
    text-transform: uppercase; letter-spacing: .05em; font-family: var(--mono); flex-shrink: 0;
}

/* ══ RIGHT COLUMN ══ */
.log-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1rem; padding-bottom: .65rem;
    border-bottom: 2px solid var(--navy);
}
.log-header-left { display: flex; align-items: center; gap: .65rem; }
.log-header-icon {
    width: 28px; height: 28px; background: var(--navy);
    display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.log-header-title {
    font-size: 11px; font-weight: 700; color: var(--navy);
    text-transform: uppercase; letter-spacing: .12em; font-family: var(--mono);
}
.log-total-badge {
    font-size: 10px; font-weight: 700; background: var(--navy); color: #fff;
    padding: 2px 10px; font-family: var(--mono);
}

/* ══ NOTIFICATION CARDS ══ */
.notif-log { display: flex; flex-direction: column; gap: 1rem; }

.notif-card {
    background: var(--white); border: 1px solid var(--border);
    box-shadow: var(--shadow-xs); overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    animation: fadeUp .3s ease both;
    border-left: 4px solid transparent;
}
.notif-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }

@keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

.notif-card-head {
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
}
.nch-left { display: flex; align-items: flex-start; gap: .75rem; flex: 1; min-width: 0; }
.nch-priority-block {
    width: 42px; height: 42px; flex-shrink: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0; font-size: 16px;
    position: relative;
}
.nch-priority-block::after {
    content: ''; position: absolute; inset: 0;
    border-radius: 2px; opacity: .15;
}
.nch-text { flex: 1; min-width: 0; }
.nch-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: .2rem; line-height: 1.3; }
.nch-body {
    font-size: 12px; color: var(--text-muted); line-height: 1.5;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 420px;
}
.nch-meta { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: .4rem; }
.meta-chip {
    display: inline-flex; align-items: center; gap: .2rem;
    font-size: 10px; font-weight: 700; padding: 2px 7px; border: 1px solid;
    text-transform: uppercase; letter-spacing: .04em; font-family: var(--mono);
    white-space: nowrap;
}
.mc-navy  { background: var(--navy-faint); border-color: rgba(0,51,102,.2); color: var(--navy-mid); }
.mc-green { background: var(--green-bg); border-color: rgba(26,122,60,.25); color: var(--green); }
.mc-amber { background: #fffbeb; border-color: rgba(180,83,9,.25); color: var(--amber); }
.mc-grey  { background: var(--grey); border-color: var(--border); color: var(--text-muted); }
.mc-red   { background: var(--red-faint); border-color: rgba(200,16,46,.25); color: var(--red); }
.mc-email { background: #fff7ed; border-color: #f59e0b; color: #78350f; }

.nch-actions { display: flex; gap: .4rem; flex-shrink: 0; align-items: flex-start; }

/* ══ RECIPIENTS TOGGLE ══ */
.recipients-toggle {
    width: 100%; display: flex; align-items: center; justify-content: space-between;
    padding: .5rem 1rem; background: var(--grey); border: none;
    border-top: 1px solid var(--border);
    font-family: var(--mono); font-size: 10px; font-weight: 700; color: var(--text-muted);
    cursor: pointer; text-transform: uppercase; letter-spacing: .1em;
    transition: background .12s, color .12s; text-align: left;
}
.recipients-toggle:hover { background: var(--navy-faint); color: var(--navy); }
.rt-toggle-left { display: flex; align-items: center; gap: .5rem; }
.rt-count-badge {
    font-size: 9px; font-weight: 700; padding: 1px 6px;
    background: var(--navy); color: #fff; font-family: var(--mono);
}
.rt-chevron { transition: transform .2s; font-size: 9px; }
.rt-chevron.open { transform: rotate(180deg); }
.recipients-panel { display: none; border-top: 1px solid var(--border); }
.recipients-panel.open { display: block; }

/* ══ RECIPIENTS TABLE ══ */
.rt-wrap { overflow-x: auto; }
.recipients-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.recipients-table thead { background: var(--navy); }
.recipients-table th {
    padding: .5rem .85rem; text-align: left;
    font-size: 9px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: rgba(255,255,255,.55);
    font-family: var(--mono); white-space: nowrap;
}
.recipients-table th:last-child { text-align: right; }
.recipients-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.recipients-table tbody tr:last-child { border-bottom: none; }
.recipients-table tbody tr:hover { background: var(--off-white); }
.recipients-table td { padding: .55rem .85rem; vertical-align: middle; }
.rt-removed { opacity: .45; }
.read-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.read-dot.read { background: #22c55e; box-shadow: 0 0 4px rgba(34,197,94,.4); }
.read-dot.unread { background: var(--border); }
.read-status { display: flex; align-items: center; gap: .4rem; font-size: 11px; }
.read-time { font-size: 10px; color: var(--text-muted); font-family: var(--mono); }

/* ══ BUTTONS ══ */
.btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .45rem 1rem; border: 1px solid;
    font-family: var(--mono); font-size: 11px; font-weight: 700;
    cursor: pointer; transition: var(--transition);
    white-space: nowrap; text-transform: uppercase; letter-spacing: .06em; text-decoration: none;
}
.btn-primary { background: var(--navy); border-color: var(--navy); color: #fff; }
.btn-primary:hover { background: var(--navy-light); }
.btn-danger { background: transparent; border-color: rgba(200,16,46,.4); color: var(--red); }
.btn-danger:hover { background: var(--red-faint); border-color: var(--red); }
.btn-ghost { background: transparent; border-color: var(--border); color: var(--text-muted); }
.btn-ghost:hover { border-color: var(--navy); color: var(--navy); }
.btn-sm { padding: .3rem .75rem; font-size: 10px; }

/* ══ EMPTY ══ */
.empty-state { text-align: center; padding: 4rem 1rem; }
.empty-icon { font-size: 2.5rem; opacity: .15; margin-bottom: .75rem; }
.empty-text { font-size: 13px; color: var(--text-muted); }

/* ══ PAGINATION ══ */
.pagination-wrap {
    padding: .75rem 1rem; border-top: 1px solid var(--border);
    background: var(--grey);
}

/* ══ MOBILE BOTTOM SHEET ══ */
@media(max-width:900px) {
    .page-grid { gap: 1.25rem; }
    .compose-terminal { order: 1; }
    .log-col { order: 2; }
    .nch-body { max-width: 200px; }
    .page-band-meta { display: none; }
}
@media(max-width:600px) {
    .nch-meta { gap: .3rem; }
    .meta-chip { font-size: 9px; padding: 1px 5px; }
    .notif-card-head { flex-direction: column; gap: .65rem; }
    .nch-actions { align-self: flex-end; }
    .recipients-table th:nth-child(2),
    .recipients-table td:nth-child(2),
    .recipients-table th:nth-child(3),
    .recipients-table td:nth-child(3) { display: none; }
}

/* ══ STAGGER ANIMATIONS ══ */
.notif-card:nth-child(1) { animation-delay: 0s; }
.notif-card:nth-child(2) { animation-delay: .05s; }
.notif-card:nth-child(3) { animation-delay: .1s; }
.notif-card:nth-child(4) { animation-delay: .15s; }
.notif-card:nth-child(5) { animation-delay: .2s; }
</style>

@php
    $priorityConfig = \App\Models\AdminNotification::priorityConfig();
    $allUsers = \App\Models\User::where('registration_pending', false)
        ->orderBy('name')
        ->get(['id','name','email','callsign']);
@endphp

{{-- ══ HEADER ══ --}}
<header class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-brand">
            <div class="rn-logo"><span>RAY<br>NET</span></div>
            <div class="rn-brand-text">
                <div class="rn-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="rn-sub">Admin · Notifications</div>
            </div>
        </div>
        <div class="rn-header-right">
            <a href="{{ route('admin.dashboard') }}" class="rn-back">← Dashboard</a>
        </div>
    </div>
</header>

{{-- ══ PAGE BAND ══ --}}
<div class="page-band">
    <div class="page-band-inner">
        <div>
            <div class="page-eyebrow">Admin Panel</div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-desc">Broadcast priority messages to members. Priority 3+ triggers an automatic email.</p>
        </div>
        <div class="page-band-meta">
            <div class="live-dot"></div>
            <span>{{ now()->format('D d M Y · H:i') }}</span>
        </div>
    </div>
</div>

<div class="wrap">

    @if (session('status'))
        <div class="alert-success">✓ {{ session('status') }}</div>
    @endif

    <div class="page-grid">

        {{-- ══ LEFT: COMPOSE TERMINAL ══ --}}
        <div>
            <div class="compose-terminal">
                <div class="terminal-header">
                    <div class="terminal-title">
                        <div class="terminal-dot"></div>
                        Broadcast Terminal
                    </div>
                    <span style="font-size:10px;color:rgba(255,255,255,.25);font-family:var(--mono);">
                        {{ $totalUsers }} recipients online
                    </span>
                </div>
                <div class="terminal-body">
                    <form method="POST" action="{{ route('admin.notifications.store') }}" id="notifForm">
                        @csrf

                        {{-- Priority --}}
                        <div style="margin-bottom:1rem;">
                            <div class="priority-section-label">// Priority Level</div>
                            <div class="priority-grid">
                                @foreach ($priorityConfig as $level => $cfg)
                                <button type="button"
                                        class="priority-btn {{ $level === 1 ? 'selected' : '' }}"
                                        data-level="{{ $level }}"
                                        onclick="selectPriority({{ $level }})"
                                        style="{{ $level === 1 ? 'background:'.$cfg['colour'].';border-color:'.$cfg['colour'].';color:#fff;box-shadow:0 0 12px '.$cfg['colour'].'66;' : '' }}">
                                    <span class="pb-icon">{{ $cfg['icon'] }}</span>
                                    <span class="pb-num">{{ $level }}</span>
                                    <span>{{ $cfg['label'] }}</span>
                                </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="priority" id="priorityInput" value="1">

                            {{-- Priority legend --}}
                            <div class="priority-legend">
                                @foreach ($priorityConfig as $level => $cfg)
                                <span class="pl-chip" style="background:{{ $cfg['bg'] }};color:{{ $cfg['text'] }};border-color:{{ $cfg['colour'] }}40;">
                                    {{ $cfg['icon'] }} {{ $level }}. {{ $cfg['label'] }}
                                </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Email warning --}}
                        <div class="email-warning" id="emailWarning">
                            <span style="font-size:15px;">📧</span>
                            <span>Priority 1-3 — email sent automatically to all recipients</span>
                        </div>

                        {{-- Title --}}
                        <div class="t-field">
                            <label>Message Title <span class="req">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   placeholder="e.g. Exercise Cancelled — Storm Warning"
                                   required maxlength="255">
                            @error('title')<div class="t-field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Body --}}
                        <div class="t-field">
                            <label>Body <span style="font-weight:400;text-transform:none;letter-spacing:0;color:rgba(255,255,255,.25);">(optional)</span></label>
                            <textarea name="body" placeholder="Additional detail shown below the title…" maxlength="2000">{{ old('body') }}</textarea>
                            @error('body')<div class="t-field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Send to --}}
                        <div style="margin-bottom:.85rem;">
                            <div class="priority-section-label" style="margin-bottom:.4rem;">// Recipients</div>
                            <div class="send-to-tabs">
                                <button type="button" class="send-to-tab active" id="tabAll" onclick="setSendTo('all')">
                                    🌐 All members ({{ $totalUsers }})
                                </button>
                                <button type="button" class="send-to-tab" id="tabSelected" onclick="setSendTo('selected')">
                                    👥 Select members
                                </button>
                            </div>
                            <input type="hidden" name="send_to" id="sendToInput" value="all">
                        </div>

                        {{-- Member list --}}
                        <div class="member-list-section" id="memberListSection" style="margin-bottom:.85rem;">
                            <input type="text" class="member-filter-input" id="memberFilterInput"
                                   placeholder="Filter by name, email or callsign…"
                                   oninput="filterMembers(this.value)">
                            <div class="member-list-wrap">
                                <div class="member-list-header">
                                    <span id="memberListCount">{{ $allUsers->count() }} members</span>
                                    <div style="display:flex;gap:.75rem;">
                                        <button type="button" onclick="selectAllVisible()">+ All</button>
                                        <button type="button" onclick="clearAll()">✕ Clear</button>
                                    </div>
                                </div>
                                <div id="memberListBody">
                                    @foreach ($allUsers as $u)
                                    <div class="member-row"
                                         data-id="{{ $u->id }}"
                                         data-name="{{ strtolower($u->name) }}"
                                         data-email="{{ strtolower($u->email) }}"
                                         data-callsign="{{ strtolower($u->callsign ?? '') }}"
                                         onclick="toggleMember({{ $u->id }})">
                                        <div class="member-checkbox">
                                            <span class="member-check-tick">✓</span>
                                        </div>
                                        <div class="member-av">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="member-name">{{ $u->name }}</div>
                                            <div class="member-meta">{{ $u->email }}{{ $u->callsign ? ' · ' . $u->callsign : '' }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="member-list-empty" id="memberListEmpty" style="display:none;">No members match.</div>
                            </div>
                            <div class="selected-summary" id="selectedSummary">
                                <span id="selectedCount" style="font-family:var(--mono);">0 selected</span>
                                <button type="button" onclick="clearAll()">✕ Clear</button>
                            </div>
                            <div id="selectedUsersInputs"></div>
                            @error('user_ids')<div class="t-field-error" style="margin-top:.4rem;">{{ $message }}</div>@enderror
                        </div>

                        <div class="send-btn-divider">
                            <button type="submit" class="send-btn" id="sendBtn">
                                <span>📣</span>
                                <span>Broadcast Notification</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Priority reference --}}
            <div class="priority-ref">
                <div class="priority-ref-head">
                    <span style="font-size:13px;">📊</span>
                    <span class="priority-ref-title">Priority Reference</span>
                </div>
                @foreach ($priorityConfig as $level => $cfg)
                <div class="priority-ref-row" style="border-left: 3px solid {{ $cfg['colour'] }};">
                    <div class="pref-icon" style="background:{{ $cfg['colour'] }}18;">
                        <span>{{ $cfg['icon'] }}</span>
                    </div>
                    <div class="pref-info">
                        <div class="pref-name">{{ $level }}. {{ $cfg['label'] }}</div>
                        <div class="pref-desc">
                            @switch($level)
                                @case(1) Immediate action required — all members respond @break
                                @case(2) Prompt attention required @break
                                @case(3) Operational update — check for actions @break
                                @case(4) Members should be aware of this @break
                                @case(5) General information, no action required @break
                            @endswitch
                        </div>
                    </div>
                    @if ($level <= 3)
                        <span class="pref-email-badge">📧 Email</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ RIGHT: LOG ══ --}}
        <div class="log-col">
            <div class="log-header">
                <div class="log-header-left">
                    <div class="log-header-icon">🔔</div>
                    <span class="log-header-title">Sent Notifications</span>
                </div>
                <span class="log-total-badge">{{ $notifications->total() }} total</span>
            </div>

            @if ($notifications->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">🔔</div>
                    <div class="empty-text">No notifications sent yet.</div>
                </div>
            @else
            <div class="notif-log">
                @foreach ($notifications as $notif)
                @php
                    $pm           = $notif->priorityMeta();
                    $allR         = $notif->recipients;
                    $activeR      = $allR->whereNull('removed_at');
                    $readCount    = $activeR->whereNotNull('read_at')->count();
                    $totalActive  = $activeR->count();
                    $removedCount = $allR->whereNotNull('removed_at')->count();
                    $emailOpened  = $notif->priority <= 3
                        ? $activeR->whereNotNull('email_opened_at')->count()
                        : null;
                @endphp
                <div class="notif-card" style="border-left-color:{{ $pm['colour'] }};">
                    <div class="notif-card-head">
                        <div class="nch-left">
                            <div class="nch-priority-block" style="background:{{ $pm['colour'] }}18;">
                                <span style="font-size:22px;">{{ $pm['icon'] }}</span>
                            </div>
                            <div class="nch-text">
                                @if(!$notif->sent_by)
                                <span style="display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:1px 6px;background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);color:#C8102E;margin-bottom:4px;">📡 HQ Broadcast</span>
                            @endif
                            <div class="nch-title">{{ $notif->title }}</div>
                                @if ($notif->body)
                                    <div class="nch-body" title="{{ $notif->body }}">{{ $notif->body }}</div>
                                @endif
                                <div class="nch-meta">
                                    <span class="meta-chip" style="background:{{ $pm['bg'] }};color:{{ $pm['text'] }};border-color:{{ $pm['colour'] }}40;">
                                        {{ $pm['icon'] }} {{ $notif->priority }} · {{ $pm['label'] }}
                                    </span>
                                    @if ($emailOpened !== null)
                                        <span class="meta-chip mc-email">📧 {{ $emailOpened }}/{{ $totalActive }} opened</span>
                                    @endif
                                    @if ($notif->sent_to_all)
                                        <span class="meta-chip mc-navy">🌐 All</span>
                                    @endif
                                    <span class="meta-chip mc-navy">👥 {{ $totalActive }}</span>
                                    <span class="meta-chip mc-green">✓ {{ $readCount }}</span>
                                    @if ($totalActive - $readCount > 0)
                                        <span class="meta-chip mc-amber">○ {{ $totalActive - $readCount }} unread</span>
                                    @endif
                                    @if ($removedCount > 0)
                                        <span class="meta-chip mc-grey">✕ {{ $removedCount }}</span>
                                    @endif
                                    <span class="meta-chip mc-grey">{{ $notif->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="nch-actions">
                            <form method="POST" action="{{ route('admin.notifications.destroy', $notif) }}"
                                  onsubmit="return confirm('Delete this notification for all recipients?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">✕ Delete</button>
                            </form>
                        </div>
                    </div>

                    <button type="button" class="recipients-toggle" onclick="toggleRecipients({{ $notif->id }})">
                        <span class="rt-toggle-left">
                            <span>👥 Recipients &amp; read status</span>
                            <span class="rt-count-badge">{{ $totalActive }}</span>
                        </span>
                        <span class="rt-chevron" id="chevron-{{ $notif->id }}">▼</span>
                    </button>

                    <div class="recipients-panel" id="recipients-{{ $notif->id }}">
                        @if ($allR->isEmpty())
                            <div style="padding:1rem;text-align:center;font-size:12px;color:var(--text-muted);">No recipient records.</div>
                        @else
                        <div class="rt-wrap">
                            <table class="recipients-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>Callsign</th>
                                        <th>Portal Read</th>
                                        <th>Email Opened</th>
                                        <th>Status</th>
                                        <th style="text-align:right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allR->sortByDesc('created_at') as $r)
                                    <tr class="{{ $r->removed_at ? 'rt-removed' : '' }}">
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.45rem;">
                                                <div style="width:24px;height:24px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;flex-shrink:0;text-transform:uppercase;font-family:var(--mono);">
                                                    {{ strtoupper(substr($r->user->name ?? '?', 0, 1)) }}
                                                </div>
                                                <span style="font-size:12px;font-weight:600;">{{ $r->user->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td style="font-size:11px;color:var(--text-muted);">{{ $r->user->email ?? '—' }}</td>
                                        <td style="font-size:11px;color:var(--text-muted);font-family:var(--mono);">{{ $r->user->callsign ?? '—' }}</td>
                                        <td>
                                            @if ($r->read_at)
                                                <div class="read-status">
                                                    <span class="read-dot read"></span>
                                                    <div>
                                                        <div style="font-size:11px;font-weight:700;color:var(--green);">Read</div>
                                                        <div class="read-time">{{ $r->read_at->format('d M Y H:i') }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="read-status">
                                                    <span class="read-dot unread"></span>
                                                    <span style="font-size:11px;color:var(--text-muted);">Unread</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->email_token)
                                                @if ($r->email_opened_at)
                                                    <div class="read-status">
                                                        <span class="read-dot read"></span>
                                                        <div>
                                                            <div style="font-size:11px;font-weight:700;color:var(--green);">Opened</div>
                                                            <div class="read-time">{{ $r->email_opened_at->format('d M Y H:i') }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="read-status">
                                                        <span class="read-dot unread"></span>
                                                        <span style="font-size:11px;color:var(--text-muted);">Not opened</span>
                                                    </div>
                                                @endif
                                            @else
                                                <span style="font-size:11px;color:var(--border);font-family:var(--mono);">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->removed_at)
                                                <span style="font-size:10px;font-weight:700;padding:2px 7px;background:var(--grey);border:1px solid var(--border);color:var(--text-muted);font-family:var(--mono);">✕ Removed</span>
                                            @else
                                                <span style="font-size:10px;font-weight:700;padding:2px 7px;background:var(--green-bg);border:1px solid rgba(26,122,60,.25);color:var(--green);font-family:var(--mono);">● Active</span>
                                            @endif
                                        </td>
                                        <td style="text-align:right;">
                                            @if (!$r->removed_at)
                                                <button type="button" class="btn btn-ghost btn-sm"
                                                        onclick="removeRecipient({{ $notif->id }}, {{ $r->user_id }}, this)">
                                                    ✕ Remove
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if ($notifications->hasPages())
                <div class="pagination-wrap" style="margin-top:1rem;">{{ $notifications->links() }}</div>
            @endif
            @endif
        </div>

    </div>
</div>

<script>
const PRIORITY_CONFIG = @json($priorityConfig);
let selectedMemberIds = new Set();

/* ── Priority selector ── */
function selectPriority(level) {
    document.getElementById('priorityInput').value = level;
    document.querySelectorAll('.priority-btn').forEach(btn => {
        const l   = parseInt(btn.dataset.level);
        const cfg = PRIORITY_CONFIG[l];
        if (l === level) {
            btn.classList.add('selected');
            btn.style.background  = cfg.colour;
            btn.style.borderColor = cfg.colour;
            btn.style.color       = '#fff';
            btn.style.boxShadow   = `0 0 14px ${cfg.colour}66`;
        } else {
            btn.classList.remove('selected');
            btn.style.background  = '';
            btn.style.borderColor = '';
            btn.style.color       = '';
            btn.style.boxShadow   = '';
        }
    });

    const warning = document.getElementById('emailWarning');
    if (warning) {
        const show = level <= 3;
        warning.classList.toggle('show', show);
        if (level === 5) {
            warning.style.cssText = 'display:flex;background:#fef2f2;border-color:#C8102E;color:#7f1d1d;';
        } else if (level === 4) {
            warning.style.cssText = 'display:flex;background:#fff7ed;border-color:#ea580c;color:#7c2d12;';
        } else if (level === 3) {
            warning.style.cssText = 'display:flex;background:#fffbeb;border-color:#f59e0b;color:#78350f;';
        }
        if (!show) warning.style.display = 'none';
    }
}

/* ── Send to ── */
function setSendTo(val) {
    document.getElementById('sendToInput').value = val;
    document.getElementById('tabAll').classList.toggle('active', val === 'all');
    document.getElementById('tabSelected').classList.toggle('active', val === 'selected');
    document.getElementById('memberListSection').classList.toggle('show', val === 'selected');
    if (val === 'selected') document.getElementById('memberFilterInput').focus();
}

/* ── Filter members ── */
function filterMembers(q) {
    const term = q.toLowerCase().trim();
    const rows = document.querySelectorAll('.member-row');
    let visible = 0;
    rows.forEach(row => {
        const matches = !term ||
            row.dataset.name.includes(term) ||
            row.dataset.email.includes(term) ||
            row.dataset.callsign.includes(term);
        row.classList.toggle('hidden', !matches);
        if (matches) visible++;
    });
    document.getElementById('memberListEmpty').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('memberListCount').textContent = term ? `${visible} matching` : `${rows.length} members`;
}

/* ── Toggle member ── */
function toggleMember(id) {
    const row = document.querySelector(`.member-row[data-id="${id}"]`);
    if (!row) return;
    if (selectedMemberIds.has(id)) {
        selectedMemberIds.delete(id);
        row.classList.remove('selected');
    } else {
        selectedMemberIds.add(id);
        row.classList.add('selected');
    }
    syncHiddenInputs();
    updateCount();
}

function selectAllVisible() {
    document.querySelectorAll('.member-row:not(.hidden)').forEach(row => {
        const id = parseInt(row.dataset.id);
        selectedMemberIds.add(id);
        row.classList.add('selected');
    });
    syncHiddenInputs(); updateCount();
}

function clearAll() {
    selectedMemberIds.clear();
    document.querySelectorAll('.member-row').forEach(r => r.classList.remove('selected'));
    syncHiddenInputs(); updateCount();
}

function syncHiddenInputs() {
    const c = document.getElementById('selectedUsersInputs');
    c.innerHTML = '';
    selectedMemberIds.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'user_ids[]'; inp.value = id;
        c.appendChild(inp);
    });
}

function updateCount() {
    const n = selectedMemberIds.size;
    document.getElementById('selectedCount').textContent = n === 0 ? '0 selected' : `${n} selected`;
}

/* ── Recipients panel ── */
function toggleRecipients(id) {
    const panel   = document.getElementById('recipients-' + id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen  = panel.classList.toggle('open');
    chevron.classList.toggle('open', isOpen);
}

/* ── Remove recipient ── */
async function removeRecipient(notifId, userId, btn) {
    if (!confirm('Remove this notification for this member?')) return;
    btn.disabled = true; btn.textContent = '…';
    try {
        const resp = await fetch(`/admin/notifications/${notifId}/remove/${userId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        if (resp.ok) {
            const row = btn.closest('tr');
            if (row) {
                row.classList.add('rt-removed');
                btn.closest('td').innerHTML = '';
                const sc = row.cells[5];
                if (sc) sc.innerHTML = `<span style="font-size:10px;font-weight:700;padding:2px 7px;background:var(--grey);border:1px solid var(--border);color:var(--text-muted);font-family:var(--mono);">✕ Removed</span>`;
            }
        } else {
            alert('Failed — please try again.');
            btn.disabled = false; btn.textContent = '✕ Remove';
        }
    } catch {
        alert('Network error.');
        btn.disabled = false; btn.textContent = '✕ Remove';
    }
}
</script>

@endsection