<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Your Account Access Has Changed</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:15px;color:#1a2332;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

    {{-- Header --}}
    <tr>
      <td style="background:#00234a;border-bottom:3px solid #C8102E;padding:20px 32px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <div style="display:inline-block;background:#C8102E;padding:6px 10px;font-size:10px;font-weight:bold;color:#fff;letter-spacing:.08em;text-transform:uppercase;line-height:1.3;">RAY<br>NET</div>
            </td>
            <td style="padding-left:14px;vertical-align:middle;">
              <div style="font-size:15px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
              <div style="font-size:10px;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Account Access Notification</div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- Alert bar --}}
    <tr>
      <td style="background:#b45309;padding:12px 32px;">
        <div style="font-size:12px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;">
          ⏱ &nbsp; Your account has been changed to temporary guest access
        </div>
      </td>
    </tr>

    {{-- Body --}}
    <tr>
      <td style="background:#ffffff;padding:36px 32px;">

        <p style="margin:0 0 20px;font-size:17px;font-weight:bold;color:#003366;">Hello {{ $user->name }},</p>

        <p style="margin:0 0 16px;line-height:1.7;color:#2d4a6b;">
            A {{ \App\Helpers\RaynetSetting::groupName() }} administrator has changed your account to
            <strong>temporary guest access</strong>. Your previous access level has been removed.
        </p>

        {{-- What has changed box --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#fffbf0;border-left:3px solid #b45309;padding:16px 20px;">
              <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7a90;margin-bottom:10px;">What Has Changed</div>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;width:130px;vertical-align:top;">Account</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a2332;">{{ $user->email }}</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">New access level</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#b45309;">Temporary Guest</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Changed on</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a2332;">{{ now()->format('l j F Y \a\t H:i') }}</td>
                </tr>
                @if($user->guest_expires_at)
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Access expires</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#b45309;">
                    {{ $user->guest_expires_at->format('l j F Y \a\t H:i') }}
                    <span style="font-weight:normal;color:#6b7a90;">({{ $user->guest_expires_at->diffForHumans() }})</span>
                  </td>
                </tr>
                @else
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Access expires</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a2332;">No expiry set</td>
                </tr>
                @endif
              </table>
            </td>
          </tr>
        </table>

        {{-- What you can still do --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#e8eef5;border-left:3px solid #003366;padding:16px 20px;">
              <div style="font-size:12px;font-weight:bold;color:#003366;margin-bottom:8px;">What You Can Still Access</div>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Members' hub &amp; dashboard</td>
                </tr>
                <tr>
                  <td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Events calendar &amp; upcoming events</td>
                </tr>
                <tr>
                  <td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Training portal</td>
                </tr>
                <tr>
                  <td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Resources &amp; library</td>
                </tr>
                <tr>
                  <td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Operational map &amp; data dashboard</td>
                </tr>
              </table>
              <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(0,51,102,.1);">
                <div style="font-size:12px;font-weight:bold;color:#C8102E;margin-bottom:4px;">What You Can No Longer Access</div>
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding:3px 0;font-size:12px;color:#7a1224;">✕ &nbsp; Personal details of other members</td>
                  </tr>
                  <tr>
                    <td style="padding:3px 0;font-size:12px;color:#7a1224;">✕ &nbsp; Committee area &amp; management tools</td>
                  </tr>
                  <tr>
                    <td style="padding:3px 0;font-size:12px;color:#7a1224;">✕ &nbsp; Admin panel (if previously accessible)</td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
        </table>

        <p style="margin:0 0 20px;line-height:1.7;color:#2d4a6b;">
            You can continue to log in using your existing email address and password.
            If you have any questions about this change, please contact a group administrator.
        </p>

        {{-- CTA --}}
        <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr>
            <td style="background:#003366;">
              <a href="{{ $loginUrl }}"
                 style="display:inline-block;padding:13px 28px;font-size:13px;font-weight:bold;color:#ffffff;text-decoration:none;text-transform:uppercase;letter-spacing:.06em;">
                Log In to Members' Area →
              </a>
            </td>
          </tr>
        </table>

        {{-- Contact --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#f4f5f7;border-left:3px solid #e1e5ec;padding:14px 20px;">
              <div style="font-size:12px;color:#2d4a6b;line-height:1.6;">
                If you believe this change was made in error or you need your previous access restored,
                please contact {{ \App\Helpers\RaynetSetting::groupName() }} via the website.
              </div>
              <div style="margin-top:8px;">
                <a href="{{ url('/request-support') }}"
                   style="display:inline-block;padding:7px 16px;background:#fff;border:1px solid #e1e5ec;color:#003366;font-size:11px;font-weight:bold;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;">
                  Contact the Group →
                </a>
              </div>
            </td>
          </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="border-top:1px solid #e1e5ec;padding-top:20px;font-size:12px;color:#6b7a90;line-height:1.7;">
                This is an automated message from {{ \App\Helpers\RaynetSetting::groupName() }} — please do not reply to this email.
            </td>
          </tr>
        </table>

      </td>
    </tr>

    {{-- Footer --}}
    <tr>
      <td style="background:#00234a;padding:16px 32px;">
        <p style="margin:0;font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.06em;text-transform:uppercase;">
            RAYNET — Radio Amateurs' Emergency Network &nbsp;·&nbsp; Voluntary communications support across the UK
        </p>
      </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>
