<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>You Now Have Full Member Access</title>
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
      <td style="background:#1a6b3c;padding:12px 32px;">
        <div style="font-size:12px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;">
          ✓ &nbsp; Your account has been upgraded to full member access
        </div>
      </td>
    </tr>

    {{-- Body --}}
    <tr>
      <td style="background:#ffffff;padding:36px 32px;">

        <p style="margin:0 0 20px;font-size:17px;font-weight:bold;color:#003366;">Hello {{ $user->name }},</p>

        <p style="margin:0 0 16px;line-height:1.7;color:#2d4a6b;">
            Great news — a {{ \App\Helpers\RaynetSetting::groupName() }} administrator has upgraded your account
            from temporary guest access to a <strong>full member account</strong>.
            You now have complete access to the members' area with no time limit.
        </p>

        {{-- What has changed box --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#eef7f2;border-left:3px solid #1a6b3c;padding:16px 20px;">
              <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7a90;margin-bottom:10px;">What Has Changed</div>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;width:130px;vertical-align:top;">Account</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a2332;">{{ $user->email }}</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Previous access</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#b45309;">Temporary Guest</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">New access level</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a6b3c;">Full Member</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Upgraded on</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a2332;">{{ now()->format('l j F Y \a\t H:i') }}</td>
                </tr>
                <tr>
                  <td style="padding:5px 0;font-size:12px;color:#6b7a90;vertical-align:top;">Expiry</td>
                  <td style="padding:5px 0;font-size:12px;font-weight:bold;color:#1a6b3c;">None — permanent access</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        {{-- What you can now access --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#e8eef5;border-left:3px solid #003366;padding:16px 20px;">
              <div style="font-size:12px;font-weight:bold;color:#003366;margin-bottom:8px;">Your Full Member Access Includes</div>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Members' hub &amp; dashboard</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Events calendar, RSVP &amp; activity log</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Training portal &amp; course enrolments</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Full resources &amp; document library</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Operational map &amp; data dashboard</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; DMR network (if granted)</td></tr>
                <tr><td style="padding:3px 0;font-size:12px;color:#2d4a6b;">✓ &nbsp; Member directory &amp; contact details</td></tr>
              </table>
            </td>
          </tr>
        </table>

        <p style="margin:0 0 20px;line-height:1.7;color:#2d4a6b;">
            You can log in now using your existing email address and password.
            Welcome to {{ \App\Helpers\RaynetSetting::groupName() }}!
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

        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="border-top:1px solid #e1e5ec;padding-top:20px;font-size:12px;color:#6b7a90;line-height:1.7;">
                If you have any questions about your membership, please don't hesitate to get in touch.<br><br>
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
