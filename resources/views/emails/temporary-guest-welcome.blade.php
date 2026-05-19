<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Your Temporary Guest Access</title>
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
              <div style="font-size:10px;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Temporary Guest Access</div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- Body --}}
    <tr>
      <td style="background:#ffffff;padding:36px 32px;">

        <p style="margin:0 0 20px;font-size:17px;font-weight:bold;color:#003366;">Hello {{ $user->name }},</p>

        <p style="margin:0 0 16px;line-height:1.7;color:#2d4a6b;">
            You have been granted <strong>temporary guest access</strong> to the
            {{ \App\Helpers\RaynetSetting::groupName() }} members' area.
        </p>

        {{-- Guest access info box --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#e8eef5;border-left:3px solid #003366;padding:16px 20px;">
              <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7a90;margin-bottom:8px;">Your Access</div>
              <div style="font-size:13px;color:#1a2332;line-height:1.7;">
                As a temporary guest you can view the members' hub, calendar, events, training portal, ops map, and resources.
                You will <strong>not</strong> be able to see the personal details of other members.
              </div>
              @if($user->guest_expires_at)
              <div style="margin-top:12px;padding:8px 12px;background:#fff3cd;border:1px solid #ffc107;font-size:12px;font-weight:bold;color:#856404;">
                ⏱ Your access expires on {{ $user->guest_expires_at->format('l j F Y \a\t H:i') }}
              </div>
              @endif
            </td>
          </tr>
        </table>

        <p style="margin:0 0 8px;font-size:14px;font-weight:bold;color:#003366;">Your login details</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border:1px solid #e1e5ec;">
          <tr>
            <td style="padding:10px 16px;background:#f4f5f7;border-bottom:1px solid #e1e5ec;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7a90;">Email address</td>
          </tr>
          <tr>
            <td style="padding:12px 16px;font-size:14px;font-weight:bold;font-family:monospace;color:#003366;">{{ $user->email }}</td>
          </tr>
        </table>

        <p style="margin:0 0 20px;font-size:13px;line-height:1.7;color:#2d4a6b;">
            You need to set your password before you can log in. Click the button below — this link is valid for 60 minutes.
        </p>

        {{-- CTA button --}}
        <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr>
            <td style="background:#C8102E;">
              <a href="{{ $resetUrl }}"
                 style="display:inline-block;padding:13px 28px;font-size:13px;font-weight:bold;color:#ffffff;text-decoration:none;text-transform:uppercase;letter-spacing:.06em;">
                Set My Password &amp; Log In →
              </a>
            </td>
          </tr>
        </table>

        <p style="margin:0 0 8px;font-size:12px;color:#6b7a90;line-height:1.6;">
            If the button doesn't work, copy and paste this link into your browser:
        </p>
        <p style="margin:0 0 24px;font-size:11px;font-family:monospace;color:#003366;word-break:break-all;">
            {{ $resetUrl }}
        </p>

        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="border-top:1px solid #e1e5ec;padding-top:20px;font-size:12px;color:#6b7a90;line-height:1.7;">
                If you were not expecting this email, you can safely ignore it. No account changes will be made unless you click the link above.<br><br>
                This is an automated message from {{ \App\Helpers\RaynetSetting::groupName() }}.
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
