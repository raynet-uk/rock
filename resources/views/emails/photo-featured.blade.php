<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="580" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,51,102,.1);">
      <tr><td style="background:#003366;padding:28px 36px;border-bottom:4px solid #f59e0b;">
        <div style="font-size:20px;font-weight:bold;color:#fff;">⭐ Your photo has been featured!</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">{{ $groupName }} Homepage</div>
      </td></tr>
      <tr><td style="padding:32px 36px;">
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">Hi {{ $user->name }},</p>
        <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 16px;">
          Your photo has been selected as a featured photo and is now displayed on the {{ $groupName }} homepage.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 6px 6px 0;margin-bottom:20px;">
          <tr><td style="padding:14px 18px;">
            <div style="font-size:12px;color:#92400e;line-height:1.7;">
              @if($l1approver)<div style="margin-bottom:4px;"><strong>Level 1 approved by:</strong> {{ $l1approver->name }}@if($l1approver->callsign) ({{ strtoupper($l1approver->callsign) }})@endif</div>@endif
              @if($l2approver)<div style="margin-bottom:4px;"><strong>Level 2 approved by:</strong> {{ $l2approver->name }}@if($l2approver->callsign) ({{ strtoupper($l2approver->callsign) }})@endif</div>@endif
              <div><strong>Featured by:</strong> {{ $featuredBy->name }}@if($featuredBy->callsign) ({{ strtoupper($featuredBy->callsign) }})@endif</div>
            </div>
          </td></tr>
        </table>
        <a href="{{ url('/') }}" style="display:inline-block;background:#f59e0b;color:#fff;padding:12px 28px;border-radius:999px;font-weight:bold;text-decoration:none;font-size:14px;">View Homepage →</a>
      </td></tr>
      <tr><td style="background:#f2f5f9;padding:16px 36px;border-top:1px solid #dde2e8;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
