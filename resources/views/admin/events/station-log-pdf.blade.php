<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Station Log — {{ $groupName }}</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;background:#e8ecf0;padding:1rem;font-size:13px;}
.toolbar{display:flex;gap:.5rem;align-items:center;margin-bottom:.75rem;}
.toolbar button{padding:.4rem .9rem;border:none;border-radius:6px;font-weight:700;font-size:.8rem;cursor:pointer;}
.btn-dl{background:#003366;color:#fff;}
.btn-pr{background:#fff;color:#334155;border:1px solid #d1d5db!important;border:none;}
.toolbar a{font-size:.78rem;color:#64748b;text-decoration:none;margin-left:.25rem;}
#pdfContent{background:#fff;max-width:1050px;margin:0 auto;border-radius:8px;overflow:hidden;box-shadow:0 1px 12px rgba(0,0,0,.12);}

/* Header strip */
.hdr{background:#003366;padding:.8rem 1.1rem;display:flex;justify-content:space-between;align-items:center;}
.hdr-left h1{color:#fff;font-size:.95rem;font-weight:900;letter-spacing:.01em;line-height:1;}
.hdr-left p{color:rgba(255,255,255,.55);font-size:.68rem;margin-top:.2rem;}
.hdr-right{display:flex;flex-direction:column;align-items:flex-end;gap:.2rem;}
.count-badge{background:#C8102E;color:#fff;font-size:.72rem;font-weight:800;padding:.2rem .65rem;border-radius:999px;}
.hdr-date{color:rgba(255,255,255,.5);font-size:.65rem;}

/* Column headers */
.col-hdr{display:grid;grid-template-columns:1.6rem 3.2rem 5.5rem 1fr 4.5rem 6.5rem 3.8rem 3.5rem 4.8rem;gap:0;background:#f1f5f9;border-bottom:2px solid #e2e8f0;}
.col-hdr div{padding:.45rem .6rem;font-size:.6rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;}

/* Data rows */
.row{display:grid;grid-template-columns:1.6rem 3.2rem 5.5rem 1fr 4.5rem 6.5rem 3.8rem 3.5rem 4.8rem;gap:0;border-bottom:1px solid #f1f5f9;align-items:center;}
.row:nth-child(even){background:#fafbfc;}
.row div{padding:.42rem .6rem;font-size:.75rem;line-height:1.25;}
.cell-num{color:#cbd5e1;font-weight:800;font-size:.65rem;text-align:center;}
.cell-time{color:#94a3b8;font-family:monospace;font-size:.72rem;}
.cell-cs{font-family:monospace;font-weight:900;font-size:.82rem;color:#003366;}
.cell-name{font-weight:600;color:#1e293b;}
.cell-notes{color:#94a3b8;font-size:.68rem;}
.cell-loc,.cell-grid{color:#64748b;font-size:.72rem;}
.cell-grid{font-family:monospace;}
.cell-sig{font-family:monospace;font-weight:800;color:#059669;}
.badge-lic{display:inline-block;padding:.1rem .4rem;border-radius:999px;background:#dcfce7;color:#15803d;font-size:.62rem;font-weight:800;}
.badge-mem{display:inline-block;padding:.1rem .4rem;border-radius:999px;background:#fef9c3;color:#a16207;font-size:.62rem;font-weight:800;}
.cell-dash{color:#e2e8f0;}

/* Footer */
.ftr{padding:.5rem 1.1rem;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;}
.ftr span{font-size:.65rem;color:#94a3b8;}
.ftr strong{font-size:.65rem;color:#64748b;font-weight:700;}

/* Empty */
.empty{text-align:center;padding:2.5rem;color:#94a3b8;font-size:.82rem;}

@media print{
  body{background:#fff;padding:0;}
  .toolbar{display:none!important;}
  #pdfContent{box-shadow:none;border-radius:0;max-width:100%;}
}
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn-dl" onclick="exportPDF()">⬇ Download PDF</button>
  <button class="btn-pr" onclick="window.print()">🖨 Print</button>
  <a href="/admin/events/net-status">← Back</a>
</div>

<div id="pdfContent">
  <div class="hdr">
    <div class="hdr-left">
      <h1>{{ $groupName }} — Station Log</h1>
      <p>Net: <strong style="color:rgba(255,255,255,.8);">{{ $netName }}</strong></p>
    </div>
    <div class="hdr-right">
      <span class="count-badge">{{ $stations->count() }} station{{ $stations->count() !== 1 ? 's' : '' }}</span>
      <span class="hdr-date">{{ $date }}</span>
    </div>
  </div>

  <div class="col-hdr">
    <div>#</div><div>Time</div><div>Callsign</div><div>Name</div>
    <div>Licence</div><div>Location</div><div>Grid</div><div>Signal</div><div>Member</div>
  </div>

  @forelse($stations as $i => $s)
    @php $qrz = is_array($s->qrz_data) ? $s->qrz_data : (json_decode($s->qrz_data ?? '{}', true) ?? []); @endphp
    <div class="row">
      <div class="cell-num">{{ $i+1 }}</div>
      <div class="cell-time">{{ $s->checked_in_at->format('H:i') }}</div>
      <div class="cell-cs">{{ $s->callsign }}</div>
      <div>
        <span class="cell-name">{{ $s->name ?? '—' }}</span>
        @if($s->notes)<span class="cell-notes"> · {{ $s->notes }}</span>@endif
      </div>
      <div>
        @if(!empty($qrz['licence_class']))<span class="badge-lic">{{ $qrz['licence_class'] }}</span>
        @else<span class="cell-dash">—</span>@endif
      </div>
      <div class="cell-loc">{{ $qrz['location'] ?? '—' }}</div>
      <div class="cell-grid">{{ $qrz['grid'] ?? '—' }}</div>
      <div class="cell-sig">{{ $s->signal_report ?? '—' }}</div>
      <div>
        @if($s->is_registered)<span class="badge-mem">✓ Member</span>
        @else<span style="display:inline-block;padding:.1rem .4rem;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:.62rem;font-weight:800;">✗ Not Member</span>@endif
      </div>
    </div>
  @empty
    <div class="empty">No stations have been logged for this session.</div>
  @endforelse

  <div class="ftr">
    <span>{{ $groupName }} · Generated by ROCK</span>
    <strong>{{ $date }}</strong>
  </div>
</div>

<script>
async function exportPDF() {
    var btn = document.querySelector('.btn-dl');
    btn.textContent = '⏳ Generating...';
    btn.disabled = true;
    try {
        var el = document.getElementById('pdfContent');
        var canvas = await html2canvas(el, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            windowWidth: 1080
        });
        var { jsPDF } = window.jspdf;
        var pdf    = new jsPDF({orientation:'landscape', unit:'pt', format:'a4'});
        var pgW    = pdf.internal.pageSize.getWidth();
        var pgH    = pdf.internal.pageSize.getHeight();
        var imgW   = canvas.width;
        var imgH   = canvas.height;
        var ratio  = pgW / imgW;
        var scaledH = imgH * ratio;
        var y = 0;
        var pageH = pgH / ratio;
        var page = 0;
        while (y < imgH) {
            if (page > 0) pdf.addPage();
            var sliceH = Math.min(pageH, imgH - y);
            var sliceCanvas = document.createElement('canvas');
            sliceCanvas.width = imgW;
            sliceCanvas.height = sliceH;
            sliceCanvas.getContext('2d').drawImage(canvas, 0, y, imgW, sliceH, 0, 0, imgW, sliceH);
            pdf.addImage(sliceCanvas.toDataURL('image/png'), 'PNG', 0, 0, pgW, sliceH * ratio);
            y += sliceH;
            page++;
        }
        pdf.save('station-log-{{ now()->format("Y-m-d-Hi") }}.pdf');
    } catch(e) {
        alert('Error: ' + e.message);
    }
    btn.textContent = '⬇ Download PDF';
    btn.disabled = false;
}
</script>
</body>
</html>
