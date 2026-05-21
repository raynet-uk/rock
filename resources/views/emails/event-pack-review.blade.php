<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="560" style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(0,51,102,.12);">
      <tr><td style="background:linear-gradient(135deg,#003366 0%,#004a99 100%);padding:32px 36px;border-bottom:4px solid #f59e0b;">
        <div style="font-size:20px;font-weight:800;color:#fff;">📋 Event Pack Awaiting Review</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">{{ $groupName }}</div>
      </td></tr>
      <tr><td style="padding:32px 36px;">
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">
          <strong>{{ $submitter->name }}</strong>@if($submitter->callsign) ({{ strtoupper($submitter->callsign) }})@endif has submitted an event support pack for review.
        </p>
        <table width="100%" style="background:#e8eef5;border-left:4px solid #003366;border-radius:0 6px 6px 0;margin-bottom:24px;">
          <tr><td style="padding:16px 20px;">
            <div style="font-size:15px;font-weight:700;color:#003366;margin-bottom:4px;">{{ $pack->event_name }}</div>
            <div style="font-size:13px;color:#2d4a6b;">{{ $pack->event_date->format('d M Y') }} · {{ $pack->location ?? 'Location TBC' }}</div>
            @if($pack->rag_status)<div style="margin-top:8px;font-size:12px;font-weight:bold;color:{{ $pack->ragColour() }};">RAG: {{ $pack->ragLabel() }}</div>@endif
          </td></tr>
        </table>
        <a href="{{ $url }}" style="display:inline-block;background:#C8102E;color:#fff;padding:13px 32px;border-radius:999px;font-weight:700;text-decoration:none;font-size:14px;">Review Event Pack →</a>
      </td></tr>
      <tr><td style="background:#f2f5f9;padding:16px 36px;border-top:1px solid #dde2e8;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK · 10/ME/179</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
