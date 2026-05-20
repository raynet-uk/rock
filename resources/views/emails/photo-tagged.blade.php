<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="580" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,51,102,.1);">
      <tr><td style="background:#003366;padding:28px 36px;border-bottom:4px solid #C8102E;">
        <div style="font-size:20px;font-weight:bold;color:#fff;">🏷 You've been tagged in a photo!</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">{{ $groupName }} Gallery</div>
      </td></tr>
      <tr><td style="padding:32px 36px;">
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">Hi {{ $user->name }},</p>
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 20px;">
          <strong>{{ $tagger->name }}</strong>
          @if($tagger->callsign)({{ strtoupper($tagger->callsign) }})@endif
          has tagged you in a photo in the {{ $groupName }} gallery.
          @if($photo->caption) The photo is captioned: <em>"{{ $photo->caption }}"</em>@endif
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="padding:0 8px 0 0;">
              <a href="{{ $taggedUrl }}" style="display:block;text-align:center;background:#003366;color:#fff;padding:12px 20px;border-radius:999px;font-weight:bold;text-decoration:none;font-size:13px;">
                📸 View My Tagged Photos
              </a>
            </td>
            <td style="padding:0 0 0 8px;">
              <a href="{{ $removeUrl }}" style="display:block;text-align:center;background:#fee2e2;color:#991b1b;padding:12px 20px;border-radius:999px;font-weight:bold;text-decoration:none;font-size:13px;">
                ✕ Remove My Tag
              </a>
            </td>
          </tr>
        </table>
        <p style="font-size:12px;color:#9aa3ae;line-height:1.6;">
          If you didn't want to be tagged you can remove the tag using the button above, or by visiting your tagged photos page.
        </p>
      </td></tr>
      <tr><td style="background:#f2f5f9;padding:16px 36px;border-top:1px solid #dde2e8;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
