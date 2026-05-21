<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="580" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,51,102,.1);">
      <tr><td style="background:#003366;padding:28px 36px;border-bottom:4px solid #dc2626;">
        <div style="font-size:20px;font-weight:bold;color:#fff;">📸 Your photo was not approved</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">{{ $groupName }} Gallery</div>
      </td></tr>
      <tr><td style="padding:32px 36px;">
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">Hi {{ $user->name }},</p>
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">
          Your photo <strong>{{ $photo->original_filename }}</strong> was reviewed by
          <strong>{{ $reviewer->name }}</strong>@if($reviewer->callsign) ({{ strtoupper($reviewer->callsign) }})@endif
          and has not been approved for the {{ $groupName }} gallery.
        </p>
        @if($reason)
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fee2e2;border-left:4px solid #dc2626;border-radius:0 6px 6px 0;margin-bottom:20px;">
          <tr><td style="padding:14px 18px;">
            <div style="font-size:12px;font-weight:bold;color:#991b1b;margin-bottom:4px;">Reason given</div>
            <div style="font-size:13px;color:#7f1d1d;line-height:1.6;">{{ $reason }}</div>
          </td></tr>
        </table>
        @else
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fee2e2;border-left:4px solid #dc2626;border-radius:0 6px 6px 0;margin-bottom:20px;">
          <tr><td style="padding:14px 18px;">
            <div style="font-size:12px;color:#7f1d1d;line-height:1.6;">No specific reason was provided. Please contact a group administrator if you have any questions.</div>
          </td></tr>
        </table>
        @endif
        <p style="font-size:13px;color:#6b7f96;line-height:1.6;margin:0;">
          If you believe this decision was made in error, please contact your group administrator directly.
        </p>
      </td></tr>
      <tr><td style="background:#f2f5f9;padding:16px 36px;border-top:1px solid #dde2e8;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
