<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $subject }} — {{ \App\Helpers\RaynetSetting::groupName() }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f2f5f9; font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:14px; color:#001f40; }
  .shell { max-width:620px; margin:0 auto; background:#f2f5f9; padding:32px 16px; }
  .header { background:#003366; border-bottom:4px solid #C8102E; }
  .header-inner { display:flex; align-items:center; justify-content:space-between; padding:20px 28px; }
  .logo-block { background:#C8102E; width:44px; height:44px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .logo-block span { font-size:9px; font-weight:bold; color:#fff; letter-spacing:.06em; text-align:center; line-height:1.2; text-transform:uppercase; }
  .org-name { font-size:15px; font-weight:bold; color:#fff; letter-spacing:.04em; text-transform:uppercase; margin-left:12px; }
  .org-sub { font-size:11px; color:rgba(255,255,255,.5); margin-top:2px; text-transform:uppercase; letter-spacing:.04em; margin-left:12px; }
  .header-badge { font-size:10px; font-weight:bold; color:#fff; padding:4px 10px; border:1px solid rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.08em; }
  .status-band { padding:14px 28px; display:flex; align-items:center; gap:12px; background:{{ $bandColour }}; }
  .status-icon { font-size:22px; flex-shrink:0; }
  .status-label { font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:.12em; color:{{ $bandTextColour }}; }
  .status-sub { font-size:11px; color:{{ $bandTextColour }}; opacity:.75; margin-top:2px; }
  .body-card { background:#fff; border:1px solid #dde2e8; border-top:none; }
  .body-inner { padding:32px 28px; }
  .email-title { font-size:20px; font-weight:bold; color:#001f40; line-height:1.3; margin-bottom:16px; }
  .email-body { font-size:14px; color:#2d4a6b; line-height:1.7; margin-bottom:24px; white-space:pre-line; }
  .info-box { padding:16px 20px; border-left:4px solid {{ $boxBorderColour }}; background:{{ $boxBgColour }}; margin-bottom:24px; }
  .info-box-title { font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; color:{{ $boxTextColour }}; margin-bottom:6px; }
  .info-box-text { font-size:13px; color:{{ $boxTextColour }}; line-height:1.6; }
  .divider { height:1px; background:#dde2e8; margin:24px 0; }
  .cta-wrap { text-align:center; margin-bottom:24px; }
  .cta-btn { display:inline-block; padding:12px 28px; background:#003366; color:#fff; font-size:13px; font-weight:bold; text-decoration:none; text-transform:uppercase; letter-spacing:.07em; }
  .meta-strip { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
  .meta-chip { font-size:10px; font-weight:bold; padding:3px 9px; border:1px solid #dde2e8; text-transform:uppercase; letter-spacing:.04em; background:#f2f5f9; color:#6b7f96; }
  .card-footer { background:#f2f5f9; border:1px solid #dde2e8; border-top:none; padding:16px 28px; }
  .footer-text { font-size:11px; color:#6b7f96; line-height:1.6; }
  .footer-bottom { text-align:center; padding:20px 0 0; font-size:11px; color:#9aa3ae; }
</style>
</head>
<body>
<div class="shell">

  <div class="header">
    <div class="header-inner">
      <div style="display:flex;align-items:center;">
        <div class="logo-block"><span>RAY<br>NET</span></div>
        <div>
          <div class="org-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
          <div class="org-sub">RAYNET Drive</div>
        </div>
      </div>
      <div class="header-badge">{!! $statusIcon !!} {{ $statusLabel }}</div>
    </div>
  </div>

  <div class="status-band">
    <span class="status-icon">{!! $statusIcon !!}</span>
    <div>
      <div class="status-label">{{ $statusLabel }}</div>
      <div class="status-sub">{{ \App\Helpers\RaynetSetting::groupName() }} · {{ now()->format('d M Y · H:i') }}</div>
    </div>
  </div>

  <div class="body-card">
    <div class="body-inner">

      <div class="email-title">{{ $subject }}</div>
      <div class="email-body">{{ $bodyText }}</div>

      @if($infoTitle && $infoText)
      <div class="info-box">
        <div class="info-box-title">{{ $infoTitle }}</div>
        <div class="info-box-text">{{ $infoText }}</div>
      </div>
      @endif

      <div class="meta-strip">
        <span class="meta-chip">&#128193; {{ $drive }}</span>
        <span class="meta-chip">&#128336; {{ now()->format('d M Y H:i') }}</span>
      </div>

      @if($ctaUrl && $ctaText)
      <div class="divider"></div>
      <div class="cta-wrap">
        <a href="{{ $ctaUrl }}" class="cta-btn">{!! $ctaText !!}</a>
      </div>
      @endif

    </div>
    <div class="card-footer">
      <div class="footer-text">
        This is an automated message from the Liverpool RAYNET Drive. Please do not reply to this email.
        If you need assistance, contact your Group Controller.
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    &copy; {{ date('Y') }} {{ \App\Helpers\RaynetSetting::groupName() }} ({{ \App\Helpers\RaynetSetting::groupNumber() }})<br>
    Affiliated to RAYNET-UK &nbsp;&middot;&nbsp;
    <a href="{{ url('/library') }}" style="color:#6b7f96;">RAYNET Drive</a>
    &nbsp;&middot;&nbsp;
    <a href="{{ url('/members') }}" style="color:#6b7f96;">Members Portal</a>
  </div>

</div>
</body>
</html>
