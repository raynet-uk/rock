<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="580" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,51,102,.1);">
      <tr><td style="background:#003366;padding:28px 36px;border-bottom:4px solid #C8102E;">
        <div style="font-size:20px;font-weight:bold;color:#fff;">📸 Your photo has been approved!</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">{{ $groupName }} Gallery — Level 1 Approval</div>
      </td></tr>
      <tr><td style="padding:32px 36px;">
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">Hi {{ $user->name }},</p>
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">
          Your photo <strong>{{ $photo->original_filename }}</strong> has been reviewed and approved by
          <strong>{{ $approver->name }}</strong>@if($approver->callsign) ({{ strtoupper($approver->callsign) }})@endif.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#e8eef5;border-left:4px solid #003366;border-radius:0 6px 6px 0;margin-bottom:20px;">
          <tr><td style="padding:14px 18px;">
            <div style="font-size:12px;font-weight:bold;color:#003366;margin-bottom:4px;">🔒 Currently visible to members only</div>
            <div style="font-size:12px;color:#2d4a6b;line-height:1.6;">
              Your photo is now visible to all logged-in {{ $groupName }} members in the members gallery.
              It is awaiting a second review from an administrator before it appears on the public-facing gallery.
            </div>
          </td></tr>
        </table>
        @if($photo->caption)<p style="font-size:13px;color:#6b7f96;margin:0 0 20px;">Caption: <em>{{ $photo->caption }}</em></p>@endif
        <a href="{{ url('/gallery') }}" style="display:inline-block;background:#003366;color:#fff;padding:12px 28px;border-radius:999px;font-weight:bold;text-decoration:none;font-size:14px;">View Members Gallery →</a>
      </td></tr>
      <tr><td style="background:#f2f5f9;padding:16px 36px;border-top:1px solid #dde2e8;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
