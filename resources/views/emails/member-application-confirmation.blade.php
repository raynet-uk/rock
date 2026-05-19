<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Received — {{ $groupName }}</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

        {{-- HEADER --}}
        <tr>
          <td style="background:#003366;border-radius:12px 12px 0 0;padding:0;overflow:hidden;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#C8102E;height:4px;font-size:0;line-height:0;">&nbsp;</td>
              </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:28px 36px 24px;">
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:#C8102E;width:44px;height:44px;border-radius:8px;text-align:center;vertical-align:middle;">
                        <span style="font-size:22px;line-height:44px;">📻</span>
                      </td>
                      <td style="padding-left:14px;">
                        <div style="font-size:16px;font-weight:bold;color:#ffffff;letter-spacing:0.04em;text-transform:uppercase;">{{ $groupName }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.5);letter-spacing:0.06em;text-transform:uppercase;margin-top:2px;">Members' Portal</div>
                      </td>
                    </tr>
                  </table>
                  <div style="margin-top:24px;">
                    <div style="font-size:11px;font-weight:bold;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.14em;margin-bottom:8px;">Membership Application</div>
                    <div style="font-size:26px;font-weight:bold;color:#ffffff;line-height:1.2;">Application Received</div>
                    <div style="font-size:14px;color:rgba(255,255,255,0.6);margin-top:8px;line-height:1.6;">Thank you for applying to join {{ $groupName }}. Your REG-02 form has been submitted successfully.</div>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- BODY --}}
        <tr>
          <td style="background:#ffffff;padding:36px;">

            <div style="font-size:15px;color:#1a2e45;margin-bottom:20px;line-height:1.6;">
              Dear <strong>{{ $forenames }}</strong>,
            </div>

            <table width="100%" cellpadding="0" cellspacing="0" style="background:#eaf4ea;border:1px solid rgba(40,120,40,0.2);border-left:3px solid #2a7a2a;border-radius:0 6px 6px 0;margin-bottom:28px;">
              <tr>
                <td style="padding:14px 16px;">
                  <div style="font-size:13px;font-weight:bold;color:#1a5c1a;margin-bottom:4px;">✓ &nbsp;Your application has been received</div>
                  <div style="font-size:13px;color:#2d5c2d;line-height:1.6;">Your completed REG-02 membership application form has been forwarded to the Group Controller for review. You will be contacted directly once your application has been processed.</div>
                </td>
              </tr>
            </table>

            <div style="font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:12px;">Application Summary</div>

            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #dde2e8;border-radius:8px;overflow:hidden;margin-bottom:28px;">
              <tr style="background:#f0f4f8;">
                <td style="padding:10px 16px;font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.08em;width:40%;border-bottom:1px solid #dde2e8;">Full Name</td>
                <td style="padding:10px 16px;font-size:13px;color:#1a2e45;border-bottom:1px solid #dde2e8;">{{ $forenames }} {{ $surname }}</td>
              </tr>
              @if($callsign)
              <tr>
                <td style="padding:10px 16px;font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.08em;width:40%;border-bottom:1px solid #dde2e8;background:#f8fafc;">Callsign</td>
                <td style="padding:10px 16px;font-size:13px;color:#1a2e45;border-bottom:1px solid #dde2e8;">{{ strtoupper($callsign) }}</td>
              </tr>
              @endif
              <tr style="background:#f0f4f8;">
                <td style="padding:10px 16px;font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.08em;width:40%;border-bottom:1px solid #dde2e8;">Email</td>
                <td style="padding:10px 16px;font-size:13px;color:#1a2e45;border-bottom:1px solid #dde2e8;">{{ $email }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.08em;width:40%;border-bottom:1px solid #dde2e8;background:#f8fafc;">Date Submitted</td>
                <td style="padding:10px 16px;font-size:13px;color:#1a2e45;border-bottom:1px solid #dde2e8;">{{ $submittedAt }}</td>
              </tr>
              <tr style="background:#f0f4f8;">
                <td style="padding:10px 16px;font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.08em;width:40%;">Signature</td>
                <td style="padding:10px 16px;font-size:13px;color:#1a2e45;">{{ $hasSig ? '✓ Digital signature captured' : '⚠ Requires handwritten signature' }}</td>
              </tr>
            </table>

            <div style="font-size:12px;font-weight:bold;color:#6b7f96;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:12px;">What Happens Next?</div>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
              <tr><td style="padding-bottom:12px;">
                <table cellpadding="0" cellspacing="0"><tr>
                  <td style="vertical-align:top;padding-right:12px;"><div style="background:#003366;color:#ffffff;font-size:11px;font-weight:bold;width:22px;height:22px;border-radius:50%;text-align:center;line-height:22px;">1</div></td>
                  <td style="vertical-align:top;"><div style="font-size:13px;font-weight:bold;color:#1a2e45;margin-bottom:2px;">Review by Group Controller</div><div style="font-size:13px;color:#4a6080;line-height:1.5;">Your REG-02 form will be reviewed by the Group Controller who will verify your details.</div></td>
                </tr></table>
              </td></tr>
              <tr><td style="padding-bottom:12px;">
                <table cellpadding="0" cellspacing="0"><tr>
                  <td style="vertical-align:top;padding-right:12px;"><div style="background:#003366;color:#ffffff;font-size:11px;font-weight:bold;width:22px;height:22px;border-radius:50%;text-align:center;line-height:22px;">2</div></td>
                  <td style="vertical-align:top;"><div style="font-size:13px;font-weight:bold;color:#1a2e45;margin-bottom:2px;">Identity Verification</div><div style="font-size:13px;color:#4a6080;line-height:1.5;">You may be contacted to arrange identity verification with your supporting documents.</div></td>
                </tr></table>
              </td></tr>
              <tr><td>
                <table cellpadding="0" cellspacing="0"><tr>
                  <td style="vertical-align:top;padding-right:12px;"><div style="background:#003366;color:#ffffff;font-size:11px;font-weight:bold;width:22px;height:22px;border-radius:50%;text-align:center;line-height:22px;">3</div></td>
                  <td style="vertical-align:top;"><div style="font-size:13px;font-weight:bold;color:#1a2e45;margin-bottom:2px;">Confirmation &amp; Welcome</div><div style="font-size:13px;color:#4a6080;line-height:1.5;">Once approved, you'll receive a welcome email with details of your membership and next steps.</div></td>
                </tr></table>
              </td></tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;"><tr><td style="border-top:1px solid #dde2e8;font-size:0;height:1px;">&nbsp;</td></tr></table>

            <div style="font-size:14px;color:#1a2e45;line-height:1.8;">
              73 de <strong>{{ $groupName }}</strong>
            </div>

          </td>
        </tr>

        {{-- FOOTER --}}
        <tr>
          <td style="background:#f0f4f8;border:1px solid #dde2e8;border-top:none;border-radius:0 0 12px 12px;padding:20px 36px;">
            <div style="font-size:12px;color:#6b7f96;line-height:1.6;">
              This email was sent to <strong>{{ $email }}</strong> because a membership application was submitted on the {{ $groupName }} website.
              If you did not submit this application, please contact your local RAYNET Group Controller immediately.
            </div>
            <div style="margin-top:10px;font-size:11px;color:#9aa3ae;">
              {{ $groupName }}<br>
              Radio Amateurs' Emergency Network
            </div>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
