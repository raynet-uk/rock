<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>You're invited to join {{ $groupName }}</title>
</head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f5f9;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,51,102,0.10);">

        {{-- Header --}}
        <tr>
          <td style="background:#003366;padding:32px 40px;border-bottom:4px solid #C8102E;">
            <div style="background:#C8102E;display:inline-block;padding:8px 14px;margin-bottom:16px;">
              <span style="font-size:11px;font-weight:bold;color:#fff;letter-spacing:.08em;text-transform:uppercase;">📡 RAYNET-UK</span>
            </div>
            <div style="font-size:22px;font-weight:bold;color:#ffffff;line-height:1.2;">You've been invited to join<br><span style="color:#90caf9;">{{ $groupName }}</span></div>
            <div style="font-size:13px;color:rgba(255,255,255,0.55);margin-top:8px;">Radio Amateurs' Emergency Network</div>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:36px 40px;">

            <p style="font-size:15px;color:#001f40;margin:0 0 16px;">Hi {{ $name }},</p>

            <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 20px;">
              <strong>{{ $referrer->name }}</strong>
              @if($referrer->callsign) ({{ strtoupper($referrer->callsign) }}) @endif
              has invited you to join <strong>{{ $groupName }}</strong> — a team of volunteers providing resilient radio communications support for {{ $groupRegion }} and surrounding areas.
            </p>

            @if($isOperator)
            <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 24px;">
              As a licensed amateur (<strong>{{ $callsign }}</strong>), you already have the key qualification needed to operate on our radio networks. RAYNET volunteers support local resilience forums, public events and major incidents when normal communications fail or are overloaded.
            </p>
            @else
            <p style="font-size:14px;color:#2d4a6b;line-height:1.7;margin:0 0 24px;">
              RAYNET welcomes people from all backgrounds — you don't need to be a licensed radio operator to get involved. We have roles for support staff, administrators and those interested in learning more about emergency communications. Many of our members have gone on to gain their amateur radio licence after joining.
            </p>
            @endif

            {{-- What we do --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f5f9;border-radius:6px;margin-bottom:28px;">
              <tr>
                <td style="padding:20px 24px;">
                  <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:#6b7f96;margin-bottom:12px;">What we do</div>
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td width="50%" style="padding:6px 12px 6px 0;font-size:13px;color:#2d4a6b;">🎪 Public &amp; major events</td>
                      <td width="50%" style="padding:6px 0;font-size:13px;color:#2d4a6b;">📻 Training &amp; exercises</td>
                    </tr>
                    <tr>
                      <td style="padding:6px 12px 6px 0;font-size:13px;color:#2d4a6b;">🤝 Multi-agency working</td>
                      <td style="padding:6px 0;font-size:13px;color:#2d4a6b;">🌐 Community resilience</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            {{-- How to apply --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border:1px solid #fcd34d;border-left:4px solid #f59e0b;border-radius:6px;margin-bottom:28px;">
              <tr>
                <td style="padding:20px 24px;">
                  <div style="font-size:13px;font-weight:bold;color:#92400e;margin-bottom:14px;">📋 How to apply — 3 simple steps</div>
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:8px 0;font-size:13px;color:#2d4a6b;">
                        <strong style="color:#003366;">1.</strong> &nbsp;Open the attached <strong>REG-02 application form</strong> (PDF attached to this email), print it out and fill in your details
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;font-size:13px;color:#2d4a6b;">
                        <strong style="color:#003366;">2.</strong> &nbsp;Contact the Group Controller to arrange a meeting — you will need to bring your completed form, a <strong>wet signature</strong>, and original identity documents for verification
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;font-size:13px;color:#2d4a6b;">
                        <strong style="color:#003366;">3.</strong> &nbsp;Get in touch with the Group Controller to arrange this:
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:4px 0 4px 24px;">
                        <a href="mailto:{{ $gcEmail }}" style="font-size:15px;font-weight:bold;color:#003366;text-decoration:none;">{{ $gcEmail }}</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            {{-- Data consent notice --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:6px;margin-bottom:28px;">
              <tr>
                <td style="padding:16px 20px;">
                  <div style="font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#1e40af;margin-bottom:8px;">🔒 Your personal data</div>
                  <p style="font-size:12px;color:#1e3a5f;line-height:1.7;margin:0;">
                    By completing and submitting the REG-02 application form, you are giving your consent for {{ $groupName }} and RAYNET-UK to collect, hold and use the personal data provided for the purposes of membership administration, operational deployment and regulatory compliance. Your data will be handled in accordance with the UK General Data Protection Regulation (UK GDPR) and will not be shared with third parties outside of RAYNET-UK without your consent. You may request access to, correction of, or deletion of your data at any time by contacting the Group Controller.
                  </p>
                </td>
              </tr>
            </table>

            <p style="font-size:13px;color:#2d4a6b;line-height:1.7;margin:0;">
              If you have any questions before applying, feel free to speak with {{ $referrer->name }}
              @if($referrer->callsign)({{ strtoupper($referrer->callsign) }})@endif
              or contact the Group Controller at <a href="mailto:{{ $gcEmail }}" style="color:#003366;">{{ $gcEmail }}</a>.
            </p>

          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f2f5f9;padding:20px 40px;border-top:1px solid #dde2e8;">
            <p style="font-size:11px;color:#9aa3ae;margin:0;line-height:1.6;">
              This invitation was sent by {{ $referrer->name }} via the {{ $groupName }} member portal.<br>
              {{ $groupName }} · RAYNET-UK<br>
              If you did not expect this email, you can safely ignore it.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
