<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $notif->title }} — {{ \App\Helpers\RaynetSetting::groupName() }}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Arial:wght@400;700&display=swap');
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f2f5f9; font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:14px; color:#001f40; }
  .shell { max-width:620px; margin:0 auto; background:#f2f5f9; padding:32px 16px; }
  /* Header */
  .header { background:#003366; border-bottom:4px solid #C8102E; padding:0; }
  .header-inner { display:flex; align-items:center; justify-content:space-between; padding:20px 28px; }
  .logo-block { background:#C8102E; width:44px; height:44px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .logo-block span { font-size:9px; font-weight:bold; color:#fff; letter-spacing:.06em; text-align:center; line-height:1.2; text-transform:uppercase; }
  .org { margin-left:12px; }
  .org-name { font-size:15px; font-weight:bold; color:#fff; letter-spacing:.04em; text-transform:uppercase; }
  .org-sub { font-size:11px; color:rgba(255,255,255,.5); margin-top:2px; text-transform:uppercase; letter-spacing:.04em; }
  .header-badge { font-size:10px; font-weight:bold; color:#fff; padding:4px 10px; border:1px solid rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.08em; }
  /* Priority band */
  .priority-band { padding:14px 28px; display:flex; align-items:center; gap:12px; }
  .priority-icon { font-size:22px; flex-shrink:0; }
  .priority-label { font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:.12em; }
  /* Body card */
  .body-card { background:#fff; border:1px solid #dde2e8; border-top:none; }
  .body-inner { padding:32px 28px; }
  .notif-title { font-size:22px; font-weight:bold; color:#001f40; line-height:1.3; margin-bottom:12px; }
  .notif-body { font-size:14px; color:#2d4a6b; line-height:1.7; margin-bottom:24px; }
  .divider { height:1px; background:#dde2e8; margin:24px 0; }
  /* Meta strip */
  .meta-strip { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
  .meta-chip { font-size:10px; font-weight:bold; padding:3px 9px; border:1px solid; text-transform:uppercase; letter-spacing:.04em; }
  /* CTA */
  .cta-wrap { text-align:center; margin-bottom:24px; }
  .cta-btn { display:inline-block; padding:12px 28px; background:#003366; color:#fff; font-size:13px; font-weight:bold; text-decoration:none; text-transform:uppercase; letter-spacing:.07em; }
  /* Action box for high priority */
  .action-box { padding:16px 20px; border-left:4px solid; margin-bottom:24px; }
  .action-box-title { font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; margin-bottom:6px; }
  .action-box-text { font-size:13px; line-height:1.6; }
  /* Footer */
  .card-footer { background:#f2f5f9; border:1px solid #dde2e8; border-top:none; padding:16px 28px; }
  .footer-text { font-size:11px; color:#6b7f96; line-height:1.6; }
  .footer-bottom { text-align:center; padding:20px 0 0; font-size:11px; color:#9aa3ae; }
</style>
</head>
<body>
<div class="shell">

  {{-- Header --}}
  <div class="header">
    <div class="header-inner">
      <div style="display:flex;align-items:center;">
        <div class="logo-block"><span>RAY<br>NET</span></div>
        <div class="org" style="margin-left:12px;">
          <div class="org-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
          <div class="org-sub">Member Notification</div>
        </div>
      </div>
      <div class="header-badge">{{ $cfg['icon'] }} {{ $cfg['label'] }}</div>
    </div>
  </div>

  {{-- Priority band --}}
  <div class="priority-band" style="background:{{ $cfg['colour'] }};">
    <span class="priority-icon">{{ $cfg['icon'] }}</span>
    <div>
      <div class="priority-label" style="color:{{ in_array($notif->priority, [1,2]) ? '#001f40' : '#fff' }};">
        Priority {{ $notif->priority }} — {{ $cfg['label'] }}
      </div>
      <div style="font-size:11px;color:{{ in_array($notif->priority, [1,2]) ? 'rgba(0,31,64,.65)' : 'rgba(255,255,255,.75)' }};margin-top:2px;">
        Sent by {{ $notif->sender->name ?? \App\Helpers\RaynetSetting::groupName() . ' Admin' }} · {{ $notif->created_at->format('d M Y · H:i') }}
      </div>
    </div>
  </div>

  {{-- Body card --}}
  <div class="body-card">
    <div class="body-inner">

      <div class="notif-title">{{ $notif->title }}</div>

      @if ($notif->body)
        <div class="notif-body">{{ $notif->body }}</div>
      @endif

      {{-- Action box for priority 4 & 5 --}}
      @if ($notif->priority <= 2)
      <div class="action-box" style="background:{{ $cfg['bg'] }};border-color:{{ $cfg['colour'] }};">
        <div class="action-box-title" style="color:{{ $cfg['text'] }};">
          {{ $notif->priority === 1 ? '🚨 Immediate action required' : '⚡ Prompt attention required' }}
        </div>
        <div class="action-box-text" style="color:{{ $cfg['text'] }};">
          {{ $notif->priority === 1
            ? 'This is an emergency notification. Please log in to the members portal immediately and follow any further instructions.'
            : 'This notification requires your prompt attention. Please log in to the members portal to review the details.' }}
        </div>
      </div>
      @endif

      {{-- Meta chips --}}
      <div class="meta-strip">
        <span class="meta-chip" style="background:{{ $cfg['bg'] }};color:{{ $cfg['text'] }};border-color:{{ $cfg['colour'] }}40;">
          {{ $cfg['icon'] }} {{ $notif->priority }} · {{ $cfg['label'] }}
        </span>
        @if ($notif->sent_to_all)
          <span class="meta-chip" style="background:#e8eef5;color:#003366;border-color:rgba(0,51,102,.2);">🌐 All members</span>
        @endif
        <span class="meta-chip" style="background:#f2f5f9;color:#6b7f96;border-color:#dde2e8;">
          {{ $notif->created_at->format('d M Y H:i') }}
        </span>
      </div>

      <div class="divider"></div>

      <div class="cta-wrap">
        <a href="{{ url('/members') }}" class="cta-btn">View in Members Portal →</a>
      </div>

    </div>

    {{-- Card footer --}}
    <div class="card-footer">
      <div class="footer-text">
        This notification was sent to you by {{ \App\Helpers\RaynetSetting::groupName() }} administration.
        You are receiving this because you are a registered member.
        @if ($notif->priority <= 3)
          <strong>Due to the priority level of this message, an email copy has been sent automatically.</strong>
        @endif
      </div>
    </div>
  </div>

  {{-- Bottom footer --}}
  <div class="footer-bottom">
    © {{ date('Y') }} {{ \App\Helpers\RaynetSetting::groupName() }} ({{ \App\Helpers\RaynetSetting::groupNumber() }})<br>
    Affiliated to RAYNET-UK · {{ \App\Helpers\RaynetSetting::groupName() }}<br>
    <span style="color:#dde2e8;">·</span>
    <a href="{{ url('/members') }}" style="color:#6b7f96;">Members Portal</a>
  </div>

</div>
{{-- Tracking pixel — loads silently when email is opened --}}
@if (!empty($emailToken))
<img src="{{ url('/track/email-open/' . $emailToken) }}"
     width="1" height="1" border="0"
     style="display:block;width:1px;height:1px;border:0;margin:0;padding:0;"
     alt="">
@endif
</body>
</html>