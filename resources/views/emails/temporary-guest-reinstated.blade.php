<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Your Guest Access Has Been Reinstated</title>
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
              <div style="font-size:10px;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Guest Access Notification</div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- Reinstated alert bar --}}
    <tr>
      <td style="background:#1a6b3c;padding:12px 32px;">
        <div style="font-size:12px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;">
          ✓ &nbsp; Your temporary guest access has been reinstated
        </div>
      </td>
    </tr>

    {{-- Body --}}
    <tr>
      <td style="background:#ffffff;padding:36px 32px;">

        <p style="margin:0 0 20px;font-size:17px;font-weight:bold;color:#003366;">Hello {{ $user->name }},</p>

        <p style="margin:0 0 16px;line-height:1.7;color:#2d4a6b;">
            Good news — a {{ \App\Helpers\RaynetSetting::groupName() }} administrator has reinstated your temporary guest access to the members' area.
            You can log back in immediately using your existing email address and password.
        </p>

        {{-- Access info box --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#eef7f2;border-left:3px solid #1a6b3c;padding:16px 20px;">
              <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7a90;margin-bottom:8px;">Access Details</div>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding:4px 0;font-size:12px;color:#6b7a90;width:120px;">Account</td>
                  <td style="padding:4px 0;font-size:12px;font-weight:bold;color:#1a2332;">{{ $user->email }}</td>
                </tr>
                <tr>
                  <td style="padding:4px 0;font-size:12px;color:#6b7a90;">Reinstated</td>
                  <td style="padding:4px 0;font-size:12px;font-weight:bold;color:#1a6b3c;">{{ now()->format('l j F Y \a\t H:i') }}</td>
                </tr>
                @if($user->guest_expires_at)
                <tr>
                  <td style="padding:4px 0;font-size:12px;color:#6b7a90;">New expiry</td>
                  <td style="padding:4px 0;font-size:12px;font-weight:bold;color:#b45309;">{{ $user->guest_expires_at->format('l j F Y \a\t H:i') }}</td>
                </tr>
                @else
                <tr>
                  <td style="padding:4px 0;font-size:12px;color:#6b7a90;">Expiry</td>
                  <td style="padding:4px 0;font-size:12px;font-weight:bold;color:#1a2332;">No expiry set</td>
                </tr>
                @endif
              </table>
            </td>
          </tr>
        </table>

        {{-- What you can access --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#e8eef5;border-left:3px solid #003366;padding:14px 20px;">
              <div style="font-size:12px;font-weight:bold;color:#003366;margin-bottom:6px;">Your Access</div>
              <div style="font-size:12px;color:#2d4a6b;line-height:1.7;">
                As a temporary guest you can view the members' hub, calendar, events, training portal, ops map, and resources.
                You will <strong>not</strong> be able to see the personal details of other members.
              </div>
            </td>
          </tr>
        </table>

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
                If you were not expecting this email or did not request reinstatement, please contact {{ \App\Helpers\RaynetSetting::groupName() }} immediately.<br><br>
                This is an automated message — please do not reply to this email.
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
