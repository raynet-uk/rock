<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ops Pack — {{ $event->title }}</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f3f8; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.card { background: white; border-radius: 12px; padding: 2rem 2.5rem; box-shadow: 0 4px 24px rgba(0,51,102,.12); max-width: 460px; width: 100%; text-align: center; }
.logo { background: #003366; display: inline-block; padding: 4px 12px; font-size: 11px; font-weight: bold; color: #fff; letter-spacing: .1em; margin-bottom: 1rem; }
h1 { font-size: 1.3rem; color: #003366; margin-bottom: .3rem; }
.subtitle { font-size: .9rem; color: #6b7f96; margin-bottom: 1.5rem; }
.btn { display: block; padding: .75rem 1.75rem; background: #003366; color: white; font-size: .95rem; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; width: 100%; margin-bottom: .75rem; transition: background .15s; }
.btn:hover:not(:disabled) { background: #002244; }
.btn-red { background: #C8102E; }
.btn-red:hover:not(:disabled) { background: #a00d25; }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.back { font-size: .85rem; color: #6b7f96; text-decoration: none; display: block; margin-top: .75rem; }
.back:hover { color: #003366; }

/* Progress UI */
#progress-wrap { display: none; margin-top: 1.5rem; text-align: left; }
.prog-track { height: 4px; background: #e8eef5; border-radius: 0; overflow: hidden; margin-bottom: 1rem; position: relative; }
.prog-indeterminate { position: absolute; top: 0; left: 0; height: 100%; width: 30%; background: #003366; border-radius: 0; animation: indeterminate 1.4s cubic-bezier(0.65,0.05,0.35,0.95) infinite; }
.prog-indeterminate::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #C8102E; transform: scaleX(0); transform-origin: left; animation: indeterminate-pulse 1.4s cubic-bezier(0.65,0.05,0.35,0.95) infinite 0.3s; }
@keyframes indeterminate { 0% { left: -35%; right: 100%; } 60%,100% { left: 100%; right: -90%; } }
@keyframes indeterminate-pulse { 0%,60% { transform: scaleX(0); } 30% { transform: scaleX(0.6); } 100% { transform: scaleX(0); } }
.prog-track.done-track { animation: none; }
.prog-track.done-track .prog-indeterminate { animation: none; width: 100%; background: #1a6b3c; }
.prog-step { font-size: .88rem; color: #003366; font-weight: 600; min-height: 1.3rem; margin-bottom: .2rem; }
.prog-sub { font-size: .8rem; color: #6b7f96; min-height: 1rem; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.prog-done { display: none; margin-top: 1rem; padding: .65rem 1rem; background: #eef7f2; border: 1px solid #b8ddc9; border-radius: 6px; color: #1a6b3c; font-size: .88rem; font-weight: bold; }
</style>
</head>
<body>
<div class="card">
    <div class="logo">RAYNET</div>
    <h1>{{ $event->title }}</h1>
    <p class="subtitle">Event Operations Pack &nbsp;·&nbsp; {{ $event->starts_at?->format('l j F Y') }} &nbsp;·&nbsp; {{ count($assignmentsData) }} operators</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.75rem;">
        <div style="border:1px solid #dde2e8;border-radius:8px;padding:.8rem;text-align:left;">
            <div style="font-size:.7rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;margin-bottom:.4rem;">Full Team</div>
            <button class="btn" id="btn-all" onclick="startGenerate(false,'colour')" style="margin-bottom:.4rem;">⬇ Colour PDF</button>
            <button class="btn" id="btn-all-bw" onclick="startGenerate(false,'bw')" style="background:#1a1a2e;">⬇ Black &amp; White</button>
        </div>
        <div style="border:1px solid #dde2e8;border-radius:8px;padding:.8rem;text-align:left;">
            <div style="font-size:.7rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;margin-bottom:.4rem;">Confirmed Only</div>
            <button class="btn btn-red" id="btn-conf" onclick="startGenerate(true,'colour')" style="margin-bottom:.4rem;">⬇ Colour PDF</button>
            <button class="btn btn-red" id="btn-conf-bw" onclick="startGenerate(true,'bw')" style="background:#555;">⬇ Black &amp; White</button>
        </div>
    </div>
    <a href="{{ route('admin.events.assignments', $event->id) }}" class="back" id="back-link">← Back to Assignments</a>

    <div id="progress-wrap">
        <div class="prog-track" id="prog-track"><div class="prog-indeterminate"></div></div>
        <div class="prog-step" id="prog-step">Starting…</div>
        <div class="prog-sub" id="prog-sub"></div>
        <div class="prog-done" id="prog-done">✓ PDF downloaded successfully</div>
    </div>
</div>

<script>
const EVENT = @json($eventData);

const ASSIGNMENTS = @json($assignmentsData);

const POIS = @json($pois);

const NAVY  = [0, 51, 102];
const RED   = [200, 16, 46];
const WHITE = [255, 255, 255];
const LGREY = [244, 245, 247];
const MGREY = [221, 226, 232];
const MUTED = [107, 127, 150];

function buildStepsList(operators) {}
function markStep(id, done) {}

function setProgress(pct, stepText, subText) {
    document.getElementById('prog-step').textContent = stepText || '';
    document.getElementById('prog-sub').textContent  = subText  || '';
}

function setStatus(msg) {
    const isError = msg.startsWith('Error');
    document.getElementById('prog-step').textContent = msg;
    if (!isError) {
        const track = document.getElementById('prog-track');
        if (track) track.className = 'prog-track done-track';
        document.getElementById('prog-done').style.display = 'block';
        document.getElementById('prog-sub').textContent = '';
        ['btn-all','btn-all-bw','btn-conf','btn-conf-bw'].forEach(id => {
            const el = document.getElementById(id); if (el) el.disabled = false;
        });
    }
}

function startGenerate(confirmedOnly, mode) {
    ['btn-all','btn-all-bw','btn-conf','btn-conf-bw'].forEach(id => {
        const el = document.getElementById(id); if (el) el.disabled = true;
    });
    document.getElementById('progress-wrap').style.display = 'block';
    document.getElementById('prog-done').style.display = 'none';
    document.getElementById('back-link').style.display = 'none';
    generatePdf(confirmedOnly, mode === 'bw');
}

function rgb(bw, r, g, b) {
    if (bw) { const l = Math.round(r*.299+g*.587+b*.114); return [l,l,l]; }
    return [r,g,b];
}

function pageHeader(doc, title, subtitle) {
    doc.setFillColor(0, 51, 102);
    doc.rect(0, 0, 210, 20, 'F');
    doc.setFillColor(200, 16, 46);
    doc.rect(0, 20, 210, 1.5, 'F');
    doc.setFontSize(13); doc.setTextColor(255, 255, 255); doc.setFont('helvetica','bold');
    doc.text(title, 14, 13);
    doc.setFontSize(8); doc.setFont('helvetica','normal'); doc.setTextColor(200,200,200);
    doc.text(subtitle, 14, 18.5);
    doc.setFontSize(8); doc.setTextColor(180,180,180);
    doc.text(EVENT.group_name + '  ·  RESTRICTED', 196, 13, {align:'right'});
    doc.text(EVENT.issued_at, 196, 18.5, {align:'right'});
}

function pageFooter(doc, pageNum, totalPages) {
    const y = 292;
    doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.3);
    doc.line(14, y-2, 196, y-2);
    doc.setFontSize(7); doc.setTextColor(107, 127, 150); doc.setFont('helvetica','normal');
    doc.text(EVENT.group_name + ' · ' + EVENT.title + ' · Ops Pack · RESTRICTED', 14, y+2);
    doc.text('Page ' + pageNum + ' of ' + totalPages, 196, y+2, {align:'right'});
}

function sectionTitle(doc, text, y) {
    doc.setFontSize(7.5); doc.setFont('helvetica','bold');
    doc.setTextColor(107, 127, 150);
    doc.text(text.toUpperCase(), 14, y);
    doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.6);
    doc.line(14, y+1, 196, y+1);
    doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2);
    return y + 4;
}

function infoRow(doc, label, value, y, x1, x2, w) {
    x1 = x1 || 14; x2 = x2 || 50; w = w || 146;
    doc.setFillColor(244, 245, 247);
    doc.rect(x1, y-3.5, x2-x1, 6, 'F');
    doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2);
    doc.rect(x1, y-3.5, w, 6, 'S');
    doc.setFontSize(9); doc.setFont('helvetica','bold'); doc.setTextColor(0, 51, 102);
    doc.text(label, x1+2, y+0.5);
    doc.setFont('helvetica','normal'); doc.setTextColor(30,30,50);
    doc.text(value || '—', x2+2, y+0.5);
    return y + 6;
}

function loadImage(url) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width  = img.naturalWidth  || img.width;
                canvas.height = img.naturalHeight || img.height;
                canvas.getContext('2d').drawImage(img, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            } catch(e) { resolve(null); }
        };
        img.onerror = () => resolve(null);
        img.src = url + '&_t=' + Date.now();
    });
}

async function generatePdf(confirmedOnly, bw) {
    setProgress(2, 'Initialising…', '');
    try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const assignments = confirmedOnly
        ? ASSIGNMENTS.filter(a => a.status === 'confirmed' || a.status === 'standby')
        : ASSIGNMENTS;

    // ── COVER PAGE ──────────────────────────────────────────────────────────
    doc.setFillColor(0, 51, 102);
    doc.rect(0, 0, 210, 65, 'F');
    doc.setFillColor(200, 16, 46);
    doc.rect(0, 65, 210, 3, 'F');

    doc.setFontSize(9); doc.setFont('helvetica','bold');
    doc.setFillColor(200, 16, 46);
    doc.rect(14, 14, 28, 7, 'F');
    doc.setTextColor(255, 255, 255);
    doc.text('RAYNET-UK', 15.5, 19.5);

    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.setTextColor(180,200,230);
    doc.text(EVENT.group_name + '  (Group ' + EVENT.group_number + ')', 14, 32);

    doc.setFontSize(20); doc.setFont('helvetica','bold');
    doc.setTextColor(255, 255, 255);
    const titleLines = doc.splitTextToSize(EVENT.title, 155);
    doc.text(titleLines, 14, 43);

    doc.setFontSize(11); doc.setFont('helvetica','normal');
    doc.setTextColor(180,200,230);
    doc.text('Event Operations Pack', 14, 43 + titleLines.length * 8);

    doc.setFillColor(200, 16, 46);
    doc.rect(0, 68, 210, 8, 'F');
    doc.setFontSize(8); doc.setFont('helvetica','bold');
    doc.setTextColor(255, 255, 255);
    doc.text('RESTRICTED — AUTHORISED PERSONNEL ONLY', 105, 73.5, {align:'center'});

    let y = 82;
    doc.setFontSize(9); doc.setFont('helvetica','bold'); doc.setTextColor(0, 51, 102);
    y = sectionTitle(doc, 'Event Summary', y);
    y = infoRow(doc, 'Event', EVENT.title, y);
    y = infoRow(doc, 'Date', EVENT.date, y);
    y = infoRow(doc, 'Time', EVENT.time, y);
    y = infoRow(doc, 'Location', EVENT.location, y);
    if (EVENT.type)       y = infoRow(doc, 'Event Type', EVENT.type, y);
    if (EVENT.supporting) y = infoRow(doc, 'Supporting', EVENT.supporting, y);
    y = infoRow(doc, 'Pack Issued', EVENT.issued_at, y);
    y = infoRow(doc, 'Issued By', EVENT.issued_by, y);

    y += 6;
    y = sectionTitle(doc, 'Team Summary', y);

    const stats = [
        ['Assigned', assignments.length],
        ['Confirmed', assignments.filter(a=>a.status==='confirmed').length],
        ['Standby',   assignments.filter(a=>a.status==='standby').length],
        ['Mapped',    assignments.filter(a=>a.lat).length],
        ['Vehicles',  assignments.filter(a=>a.has_vehicle).length],
        ['First Aid', assignments.filter(a=>a.first_aid_trained).length],
    ];
    const statW = 30.3, statH = 14;
    stats.forEach((s,i) => {
        const sx = 14 + i*statW;
        doc.setFillColor(244, 245, 247);
        doc.rect(sx, y, statW-1, statH, 'F');
        doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2);
        doc.rect(sx, y, statW-1, statH, 'S');
        doc.setFontSize(16); doc.setFont('helvetica','bold'); doc.setTextColor(0, 51, 102);
        doc.text(String(s[1]), sx+(statW-1)/2, y+9, {align:'center'});
        doc.setFontSize(7); doc.setFont('helvetica','normal'); doc.setTextColor(107, 127, 150);
        doc.text(s[0].toUpperCase(), sx+(statW-1)/2, y+13, {align:'center'});
    });
    y += statH + 8;

    if (EVENT.description) {
        y = sectionTitle(doc, 'Event Description', y);
        doc.setFontSize(9); doc.setFont('helvetica','normal'); doc.setTextColor(30,30,50);
        const descLines = doc.splitTextToSize(EVENT.description, 182);
        descLines.slice(0,8).forEach(line => { doc.text(line, 14, y); y += 5; });
    }

    y = sectionTitle(doc, 'Contents', y+4);
    const contents = [
        ['Page 1', 'Cover Page & Event Summary'],
        ['Page 2', 'Team Roster'],
        ['Page 3', 'Communications Plan'],
    ];
    if (POIS && POIS.length) contents.push(['Page 4', 'Checkpoints & Points of Interest']);
    contents.push(['Following pages', 'Individual Operator Briefings (one per member)']);
    contents.forEach(c => { y = infoRow(doc, c[0], c[1], y); });

    doc.setFillColor(0, 51, 102);
    doc.rect(0, 285, 210, 12, 'F');
    doc.setFontSize(8); doc.setFont('helvetica','normal'); doc.setTextColor(180,200,230);
    doc.text(EVENT.group_name + ' · ' + EVENT.group_region + ' · Affiliated to RAYNET-UK', 105, 292, {align:'center'});

    // ── PAGE 2: TEAM ROSTER ──────────────────────────────────────────────────
    markStep('cover', true); markStep('roster', false); setProgress(20, 'Building team roster…', '');
    doc.addPage();
    pageHeader(doc, 'Team Roster', EVENT.title + ' · ' + EVENT.date);

    doc.autoTable({
        startY: 26,
        head: [['Name', 'Callsign', 'Role', 'Location', 'Grid Ref', 'Report', 'Depart', 'Status']],
        body: assignments.map(a => [
            a.name + (a.first_aid_trained?' 🩺':'') + (a.has_vehicle?' 🚗':''),
            a.callsign || '—',
            a.role || '—',
            a.location_name || '—',
            a.grid_ref || '—',
            a.report_time || '—',
            a.depart_time || '—',
            a.status.charAt(0).toUpperCase() + a.status.slice(1),
        ]),
        headStyles: { fillColor: [0, 51, 102], textColor: [255, 255, 255], fontSize: 7.5, fontStyle: 'bold', cellPadding: 3 },
        bodyStyles: { fontSize: 8, textColor: [20,20,40], cellPadding: 3 },
        alternateRowStyles: { fillColor: [244, 245, 247] },
        columnStyles: { 0:{fontStyle:'bold'}, 7:{cellWidth:18} },
        margin: { left: 14, right: 14 },
        theme: 'grid',
        tableLineColor: [221, 226, 232], tableLineWidth: 0.2,
    });

    // ── PAGE 3: COMMS PLAN ───────────────────────────────────────────────────
    markStep('roster', true); markStep('comms', false); setProgress(35, 'Building communications plan…', '');
    doc.addPage();
    pageHeader(doc, 'Communications Plan', EVENT.title + ' · ' + EVENT.date);

    doc.autoTable({
        startY: 26,
        head: [['Operator', 'Callsign', 'Location', 'Primary Freq', 'Mode', 'CTCSS', 'Fallback', 'Channel']],
        body: assignments.map(a => [
            a.name,
            a.callsign || '—',
            a.location_name || '—',
            a.frequency || '—',
            a.mode || '—',
            a.ctcss_tone || '—',
            a.fallback_frequency || '—',
            a.channel_label || '—',
        ]),
        headStyles: { fillColor: [0, 51, 102], textColor: [255, 255, 255], fontSize: 7.5, fontStyle: 'bold', cellPadding: 3 },
        bodyStyles: { fontSize: 8, textColor: [20,20,40], cellPadding: 3 },
        alternateRowStyles: { fillColor: [244, 245, 247] },
        columnStyles: { 0:{fontStyle:'bold'} },
        margin: { left: 14, right: 14 },
        theme: 'grid',
        tableLineColor: [221, 226, 232], tableLineWidth: 0.2,
    });

    const withGrid = assignments.filter(a => a.grid_ref);
    if (withGrid.length) {
        const afterY = doc.lastAutoTable.finalY + 8;
        doc.setFontSize(8); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
        doc.text('GRID REFERENCES & COORDINATES', 14, afterY);
        doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.6);
        doc.line(14, afterY+1, 196, afterY+1);
        doc.autoTable({
            startY: afterY + 4,
            head: [['Operator', 'Grid Ref', 'Lat / Lng', 'What3Words', 'Location']],
            body: withGrid.map(a => [
                a.name,
                a.grid_ref || '—',
                a.lat && a.lng ? a.lat+', '+a.lng : '—',
                a.what3words ? '///'+a.what3words : '—',
                a.location_name || '—',
            ]),
            headStyles: { fillColor: [0, 51, 102], textColor: [255, 255, 255], fontSize: 8, fontStyle: 'bold' },
            bodyStyles: { fontSize: 8.5 },
            alternateRowStyles: { fillColor: [244, 245, 247] },
            margin: { left: 14, right: 14 },
            theme: 'grid',
            tableLineColor: [221, 226, 232], tableLineWidth: 0.2,
        });
    }

    // ── PAGE 4: POIs ─────────────────────────────────────────────────────────
    if (POIS && POIS.length) {
        markStep('comms', true); markStep('pois', false); setProgress(48, 'Building checkpoints & POIs…', '');
        doc.addPage();
        pageHeader(doc, 'Checkpoints & Points of Interest', EVENT.title + ' · ' + EVENT.date);

        const poiEmojis = {entrance:'Entrance',exit:'Exit',car_park:'Car Park',medical:'Medical',control:'Control Point',checkpoint:'Checkpoint',repeater:'Repeater',hazard:'Hazard',info:'Info Point',custom:'Custom'};
        doc.autoTable({
            startY: 26,
            head: [['Type', 'Name', 'Description / Operators', 'Grid Ref', 'What3Words', 'Lat / Lng']],
            body: POIS.map(p => [
                poiEmojis[p.type] || 'Custom',
                p.name || '—',
                p.description || '—',
                p.grid_ref || '—',
                p.w3w ? '///'+p.w3w : '—',
                p.lat && p.lng ? Number(p.lat).toFixed(5)+', '+Number(p.lng).toFixed(5) : '—',
            ]),
            headStyles: { fillColor: [0, 51, 102], textColor: [255, 255, 255], fontSize: 8, fontStyle: 'bold' },
            bodyStyles: { fontSize: 8.5, textColor: [20,20,40] },
            alternateRowStyles: { fillColor: [244, 245, 247] },
            columnStyles: { 1:{fontStyle:'bold'}, 2:{cellWidth:45} },
            margin: { left: 14, right: 14 },
            theme: 'grid',
            tableLineColor: [221, 226, 232], tableLineWidth: 0.2,
        });
    }

    // ── INDIVIDUAL BRIEFINGS ─────────────────────────────────────────────────
    buildStepsList(assignments);
    markStep('cover', false); setProgress(5, 'Building cover page…', '');
    for (let i = 0; i < assignments.length; i++) {
        const a = assignments[i];
        if (i === 0) markStep('pois', true);
        markStep('op-' + (i-1), true);
        markStep('op-' + i, false);
        const pct = 50 + Math.round((i / assignments.length) * 48);
        setProgress(pct, 'Briefing ' + (i+1) + ' of ' + assignments.length, a.name + (a.callsign ? ' (' + a.callsign + ')' : ''));
        doc.addPage();

        // Header with name
        doc.setFillColor(0, 51, 102);
        doc.rect(0, 0, 210, 22, 'F');
        doc.setFillColor(200, 16, 46);
        doc.rect(0, 22, 210, 1.5, 'F');
        doc.setFontSize(14); doc.setFont('helvetica','bold'); doc.setTextColor(255, 255, 255);
        doc.text(a.name + (a.callsign ? ' ('+a.callsign+')' : ''), 14, 13);
        doc.setFontSize(8.5); doc.setFont('helvetica','normal'); doc.setTextColor(180,200,230);
        doc.text(EVENT.title + ' · Personal Briefing', 14, 19);
        doc.setFontSize(8); doc.setTextColor(160,180,210);
        doc.text('Group ' + EVENT.group_number + '  ·  ' + EVENT.issued_at + '  ·  RESTRICTED', 196, 13, {align:'right'});

        let y = 32;

        // Two column layout
        const col1x = 14, col2x = 110, colW = 90;

        // LEFT: Event details + assignment
        doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
        doc.text('EVENT DETAILS', col1x, y);
        doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.5); doc.line(col1x, y+1, col1x+colW, y+1);
        y += 5;

        const eventRows = [
            ['Event', EVENT.title],
            ['Date', EVENT.date],
            ['Time', EVENT.time],
            ['Location', EVENT.location],
        ];
        if (EVENT.supporting) eventRows.push(['Supporting', EVENT.supporting]);
        eventRows.forEach(r => { y = infoRow(doc, r[0], r[1], y, col1x, col1x+36, colW); });

        y += 4;
        doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
        doc.text('YOUR ASSIGNMENT', col1x, y);
        doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.5); doc.line(col1x, y+1, col1x+colW, y+1);
        y += 5;

        const assignRows = [];
        if (a.role)          assignRows.push(['Role', a.role]);
        if (a.callsign)      assignRows.push(['Callsign', a.callsign]);
        if (a.report_time)   assignRows.push(['Report Time', a.report_time]);
        if (a.depart_time)   assignRows.push(['Depart Time', a.depart_time]);
        if (a.location_name) assignRows.push(['Position', a.location_name]);
        if (a.grid_ref)      assignRows.push(['Grid Ref', a.grid_ref]);
        if (a.what3words)    assignRows.push(['What3Words', '///'+a.what3words]);
        if (a.lat && a.lng)  assignRows.push(['Lat/Lng', a.lat+', '+a.lng]);
        if (a.has_vehicle)   assignRows.push(['Vehicle', a.vehicle_reg || 'Yes']);
        assignRows.forEach(r => { y = infoRow(doc, r[0], r[1], y, col1x, col1x+36, colW); });

        // RIGHT: Comms
        let y2 = 32;
        doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
        doc.text('COMMUNICATIONS', col2x, y2);
        doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.5); doc.line(col2x, y2+1, col2x+colW, y2+1);
        y2 += 5;

        const commsRows = [];
        if (a.frequency)           commsRows.push(['Primary Freq', a.frequency]);
        if (a.mode)                commsRows.push(['Mode', a.mode]);
        if (a.ctcss_tone)          commsRows.push(['CTCSS', a.ctcss_tone]);
        if (a.channel_label)       commsRows.push(['Channel', a.channel_label]);
        if (a.secondary_frequency) commsRows.push(['Secondary', a.secondary_frequency + ' ' + (a.secondary_mode||'')]);
        if (a.fallback_frequency)  commsRows.push(['Fallback', a.fallback_frequency + ' ' + (a.fallback_mode||'')]);
        if (!commsRows.length)     commsRows.push(['Frequency', '—']);
        commsRows.forEach(r => { y2 = infoRow(doc, r[0], r[1], y2, col2x, col2x+36, colW); });

        // Shifts
        if (a.shifts && a.shifts.length) {
            y2 += 4;
            doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
            doc.text('SHIFTS', col2x, y2);
            doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.5); doc.line(col2x, y2+1, col2x+colW, y2+1);
            y2 += 5;
            a.shifts.forEach(s => {
                const label = (s.type==='break'?'Break':'Shift') + ': ' + (s.start||'') + '–' + (s.end||'') + (s.label?' · '+s.label:'');
                if (s.type==='break') { doc.setFillColor(255,251,236); } else { doc.setFillColor(244,245,247); }
                doc.rect(col2x, y2-3.5, colW, 6, 'F');
                doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2); doc.rect(col2x, y2-3.5, colW, 6, 'S');
                doc.setFontSize(9); doc.setFont('helvetica', s.type==='break'?'italic':'normal');
                if (s.type==='break') { doc.setTextColor(138,92,0); } else { doc.setTextColor(20,20,50); }
                doc.text(label, col2x+3, y2+0.5);
                y2 += 7;
            });
        }

        // Equipment
        if (a.equipment_items && a.equipment_items.length) {
            y2 += 4;
            doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
            doc.text('EQUIPMENT TO BRING', col2x, y2);
            doc.setDrawColor(0, 51, 102); doc.setLineWidth(0.5); doc.line(col2x, y2+1, col2x+colW, y2+1);
            y2 += 5;
            a.equipment_items.forEach(item => {
                doc.setFontSize(9); doc.setFont('helvetica','normal'); doc.setTextColor(20,20,50);
                doc.text('- ' + item, col2x+2, y2);
                y2 += 5.5;
            });
        }

        // Below both columns
        let yBelow = Math.max(y, y2) + 8;

        // Briefing notes
        if (a.briefing_notes) {
            doc.setFillColor(255, 251, 236);
            const noteLines = doc.splitTextToSize(a.briefing_notes, 176);
            const noteH = noteLines.length * 5 + 8;
            doc.rect(14, yBelow, 182, noteH, 'F');
            doc.setDrawColor(200, 160, 48); doc.setLineWidth(0.8);
            doc.line(14, yBelow, 14, yBelow+noteH);
            doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2);
            doc.rect(14, yBelow, 182, noteH, 'S');
            doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
            doc.text('BRIEFING NOTES', 18, yBelow+5);
            doc.setFontSize(9); doc.setFont('helvetica','normal'); doc.setTextColor(20,20,50);
            noteLines.forEach((line, idx) => {
                doc.text(line, 18, yBelow + 10 + idx*5);
            });
            yBelow += noteH + 6;
        }

        // Medical / emergency
        if (a.medical_notes || a.emergency_contact_name) {
            doc.setFillColor(253, 240, 242);
            const medLines = [];
            if (a.medical_notes) medLines.push('Medical: ' + a.medical_notes);
            if (a.emergency_contact_name) medLines.push('Emergency Contact: ' + a.emergency_contact_name + (a.emergency_contact_phone ? ' · '+a.emergency_contact_phone : ''));
            const medH = medLines.length * 6 + 8;
            doc.rect(14, yBelow, 182, medH, 'F');
            doc.setDrawColor(200, 16, 46); doc.setLineWidth(0.8);
            doc.line(14, yBelow, 14, yBelow+medH);
            doc.setDrawColor(221, 226, 232); doc.setLineWidth(0.2);
            doc.rect(14, yBelow, 182, medH, 'S');
            doc.setFontSize(7.5); doc.setFont('helvetica','bold'); doc.setTextColor(107, 127, 150);
            doc.text('MEDICAL & EMERGENCY CONTACT', 18, yBelow+5);
            doc.setFontSize(9); doc.setFont('helvetica','normal'); doc.setTextColor(20,20,50);
            medLines.forEach((line, idx) => {
                doc.text(line, 18, yBelow+10+idx*6);
            });
            yBelow += medH + 6;
        }

        // Map & Street View images
        if (a.lat && a.lng) {
            try {
                const svUrl = '/admin/streetview-thumbnail?lat=' + a.lat + '&lng=' + a.lng;

                const svImg = await loadImage(svUrl);

                const imgY = yBelow;
                doc.setFontSize(7.5); doc.setFont('helvetica','bold');
                doc.setTextColor(bw ? 80 : 107, bw ? 80 : 127, bw ? 80 : 150);
                doc.text('OPERATOR LOCATION', 14, imgY);
                doc.setDrawColor(bw ? 80 : 0, bw ? 80 : 51, bw ? 80 : 102);
                doc.setLineWidth(0.5); doc.line(14, imgY+1, 196, imgY+1);

                if (svImg) {
                    if (bw) {
                        // Convert to greyscale via canvas
                        const bwData = await new Promise(resolve => {
                            const tmpImg = new Image();
                            tmpImg.onload = function() {
                                const c = document.createElement('canvas');
                                c.width = tmpImg.naturalWidth || tmpImg.width;
                                c.height = tmpImg.naturalHeight || tmpImg.height;
                                const ctx3 = c.getContext('2d');
                                ctx3.drawImage(tmpImg, 0, 0);
                                const id3 = ctx3.getImageData(0, 0, c.width, c.height);
                                for (let p = 0; p < id3.data.length; p += 4) {
                                    const g = Math.round(id3.data[p]*.299 + id3.data[p+1]*.587 + id3.data[p+2]*.114);
                                    id3.data[p] = id3.data[p+1] = id3.data[p+2] = g;
                                }
                                ctx3.putImageData(id3, 0, 0);
                                resolve(c.toDataURL('image/png'));
                            };
                            tmpImg.onerror = () => resolve(svImg);
                            tmpImg.src = svImg;
                        });
                        doc.addImage(bwData, 'PNG', 14, imgY+3, 182, 100);
                    } else {
                        doc.addImage(svImg, 'PNG', 14, imgY+3, 182, 100);
                    }
                } else {
                    doc.setFillColor(220,226,232); doc.rect(14, imgY+3, 182, 100, 'F');
                    doc.setFontSize(8); doc.setTextColor(107,127,150);
                    doc.text('Location image unavailable', 105, imgY+53, {align:'center'});
                }
                yBelow += 110;
            } catch(imgErr) {
                console.warn('Image load failed for ' + a.name, imgErr);
            }
        }

    // Signature blocks
        yBelow = Math.min(yBelow, 262);
        doc.setDrawColor(20,20,50); doc.setLineWidth(0.4);
        doc.line(14, yBelow+12, 95, yBelow+12);
        doc.line(115, yBelow+12, 196, yBelow+12);
        doc.setFontSize(7.5); doc.setFont('helvetica','normal'); doc.setTextColor(107, 127, 150);
        doc.text('OPERATOR SIGNATURE & DATE', 14, yBelow+17);
        doc.text('CONTROLLER SIGNATURE & DATE', 115, yBelow+17);
    }

    // ── Add page numbers ─────────────────────────────────────────────────────
    const totalPages = doc.getNumberOfPages();
    for (let p = 1; p <= totalPages; p++) {
        doc.setPage(p);
        if (p > 1) pageFooter(doc, p, totalPages);
    }

    const filename = 'OPS_PACK_' + EVENT.title.replace(/[^a-z0-9]/gi,'_').toUpperCase() + '_' + new Date().toISOString().slice(0,10) + '.pdf';
    markStep('op-' + (assignments.length-1), true);
    setProgress(99, 'Saving PDF…', '');
    doc.save(filename);
    setStatus('✓ PDF downloaded: ' + filename);
    } catch(err) {
        console.error('PDF generation error:', err);
        setStatus('Error: ' + err.message + ' — check browser console (F12)');
        document.getElementById('bulk-apply-btn') && (document.getElementById('bulk-apply-btn').disabled = false);
    }
}
</script>
</body>
</html>
