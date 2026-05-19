<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate — {{ $course->title }}</title>
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;background:#f0f4f8;display:flex;flex-direction:column;align-items:center;min-height:100vh;padding:2rem 1rem;}
    .cert-actions{display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;justify-content:center;}
    .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.2rem;border:1px solid;font-family:Arial,sans-serif;font-size:12px;font-weight:bold;cursor:pointer;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
    .btn-navy{background:#003366;border-color:#003366;color:#fff;}
    .btn-navy:hover{background:#002244;}
    .btn-ghost{background:#fff;border-color:#dde2e8;color:#6b7f96;}
    .btn-ghost:hover{border-color:#003366;color:#003366;}
    .cert-wrapper{width:100%;max-width:860px;}
    .cert{background:#fff;border:1px solid #c8d4e0;box-shadow:0 8px 40px rgba(0,51,102,.15);padding:0;overflow:hidden;position:relative;}

    /* Outer border */
    .cert-border{position:absolute;inset:12px;border:3px solid #003366;pointer-events:none;z-index:1;}
    .cert-border-inner{position:absolute;inset:18px;border:1px solid #c49a00;pointer-events:none;z-index:1;}

    /* Header band */
    .cert-header{background:#003366;padding:1.75rem 3rem 1.5rem;position:relative;overflow:hidden;}
    .cert-header::after{content:'';position:absolute;bottom:0;left:0;right:0;height:4px;background:#C8102E;}
    .cert-header-deco{position:absolute;top:-20px;right:-20px;width:120px;height:120px;background:rgba(200,16,46,.08);border-radius:50%;pointer-events:none;}
    .cert-org{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.18em;color:rgba(255,255,255,.5);margin-bottom:.4rem;}
    .cert-org-name{font-size:22px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;}
    .cert-region{font-size:11px;color:rgba(255,255,255,.4);margin-top:.2rem;letter-spacing:.1em;text-transform:uppercase;}
    .cert-logo-block{position:absolute;right:3rem;top:50%;transform:translateY(-50%);text-align:center;}
    .cert-logo-hex{width:64px;height:64px;position:relative;}
    .cert-logo-hex svg{width:64px;height:64px;}

    /* Body */
    .cert-body{padding:2.5rem 3rem;}
    .cert-presents{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.18em;color:#6b7f96;margin-bottom:1rem;text-align:center;}
    .cert-name{font-size:2rem;font-weight:bold;color:#003366;text-align:center;border-bottom:2px solid #C8102E;padding-bottom:.5rem;margin-bottom:.35rem;letter-spacing:.02em;}
    .cert-callsign{text-align:center;font-size:13px;color:#6b7f96;font-family:monospace;letter-spacing:.12em;margin-bottom:1.5rem;}
    .cert-awarded-text{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:#6b7f96;text-align:center;margin-bottom:.6rem;}
    .cert-course{font-size:1.2rem;font-weight:bold;color:#001f40;text-align:center;margin-bottom:.5rem;line-height:1.3;}
    .cert-course-category{font-size:11px;color:#0288d1;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;text-align:center;margin-bottom:1.5rem;}
    .cert-body-text{font-size:12px;color:#2d4a6b;text-align:center;line-height:1.7;max-width:520px;margin:0 auto 1.75rem;}

    /* Decorative line */
    .cert-divider{display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;}
    .cert-divider-line{flex:1;height:1px;background:#dde2e8;}
    .cert-divider-diamond{width:8px;height:8px;background:#C8102E;transform:rotate(45deg);flex-shrink:0;}

    /* Footer row */
    .cert-footer{display:grid;grid-template-columns:1fr auto 1fr;gap:1rem;align-items:end;padding-top:.5rem;border-top:1px solid #e8eef5;}
    .cert-sig{text-align:center;}
    .cert-sig-line{width:140px;border-bottom:1px solid #001f40;margin:0 auto .35rem;}
    .cert-sig-name{font-size:12px;font-weight:bold;color:#001f40;}
    .cert-sig-role{font-size:10px;color:#6b7f96;text-transform:uppercase;letter-spacing:.08em;}
    .cert-center-badge{text-align:center;}
    .cert-seal{width:72px;height:72px;margin:0 auto;}
    .cert-seal svg{width:72px;height:72px;}
    .cert-number{font-size:10px;font-weight:bold;text-align:center;color:#6b7f96;letter-spacing:.08em;text-transform:uppercase;margin-top:.75rem;}
    .cert-date{font-size:12px;font-weight:bold;color:#001f40;}
    .cert-date-label{font-size:10px;color:#6b7f96;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem;}

    /* Watermark */
    .cert-watermark{position:absolute;bottom:80px;left:50%;transform:translateX(-50%);font-size:90px;font-weight:bold;color:rgba(0,51,102,.03);text-transform:uppercase;letter-spacing:.3em;pointer-events:none;white-space:nowrap;z-index:0;}

    @media print{
        body{background:#fff;padding:0;}
        .cert-actions{display:none;}
        .cert{box-shadow:none;border:none;}
        .cert-border{border-color:#003366;}
    }
    @media(max-width:600px){
        .cert-body{padding:1.5rem;}
        .cert-header{padding:1.25rem 1.5rem;}
        .cert-logo-block{display:none;}
        .cert-name{font-size:1.4rem;}
        .cert-footer{grid-template-columns:1fr;gap:.75rem;text-align:center;}
        .cert-sig-line{margin:0 auto .35rem;}
    }
    </style>
</head>
<body>

<div class="cert-actions">
    <a href="{{ route('lms.course', $course->slug) }}" class="btn btn-ghost">← Back to Course</a>
    <a href="{{ route('lms.index') }}" class="btn btn-ghost">🏠 Training Portal</a>
    <button onclick="window.print()" class="btn btn-navy">🖨 Print / Save PDF</button>
</div>

<div class="cert-wrapper">
<div class="cert">
    <div class="cert-border"></div>
    <div class="cert-border-inner"></div>
    <div class="cert-watermark">RAYNET</div>

    {{-- Header --}}
    <div class="cert-header">
        <div class="cert-header-deco"></div>
        <div class="cert-org">Certificate of Completion</div>
        <div class="cert-org-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
<div class="cert-region">{{ \App\Helpers\RaynetSetting::groupName() }} · Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
        <div class="cert-logo-block">
            <svg class="cert-logo-hex" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <polygon points="32,4 58,18 58,46 32,60 6,46 6,18" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.3)" stroke-width="2"/>
                <polygon points="32,12 50,22 50,42 32,52 14,42 14,22" fill="none" stroke="rgba(200,16,46,.7)" stroke-width="1.5"/>
                <text x="32" y="38" text-anchor="middle" font-family="Arial,sans-serif" font-size="14" font-weight="bold" fill="#fff" letter-spacing="1">RN</text>
            </svg>
        </div>
    </div>

    {{-- Body --}}
    <div class="cert-body">
        <div class="cert-presents">This is to certify that</div>

        <div class="cert-name">{{ $user->name }}</div>

        @if($user->callsign)
        <div class="cert-callsign">{{ strtoupper($user->callsign) }}</div>
        @endif

        <div class="cert-awarded-text">has successfully completed</div>

        <div class="cert-course">{{ $course->title }}</div>

        @if($course->category)
        <div class="cert-course-category">{{ $course->category }}</div>
        @endif

        <div class="cert-body-text">
            {{ $course->certificate_text ?: 'Having completed all required modules and assessments for this RAYNET training course, demonstrating the knowledge and competency required to operate as a RAYNET volunteer.' }}
        </div>

        <div class="cert-divider">
            <div class="cert-divider-line"></div>
            <div class="cert-divider-diamond"></div>
            <div class="cert-divider-line"></div>
        </div>

        <div class="cert-footer">
            <div class="cert-sig">
                <div class="cert-sig-line"></div>
                <div class="cert-sig-name">Ian Jones</div>
                <div class="cert-sig-role">Group Controller</div>
            </div>

            <div class="cert-center-badge">
                <svg class="cert-seal" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="36" cy="36" r="33" fill="#003366" stroke="#C8102E" stroke-width="2.5"/>
                    <circle cx="36" cy="36" r="27" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1"/>
                    <polygon points="36,14 40,28 55,28 43,37 47,51 36,42 25,51 29,37 17,28 32,28" fill="#C8102E" stroke="none"/>
                    <text x="36" y="62" text-anchor="middle" font-family="Arial,sans-serif" font-size="6" font-weight="bold" fill="rgba(255,255,255,.6)" letter-spacing="1">RAYNET-UK</text>
                </svg>
                <div class="cert-number">Cert No: {{ $certificate->certificate_number }}</div>
            </div>

            <div class="cert-sig" style="text-align:center;">
                <div class="cert-date-label">Date Awarded</div>
                <div class="cert-date">{{ $certificate->issued_at->format('d F Y') }}</div>
                <div style="margin-top:.5rem;font-size:10px;color:#6b7f96;">Affiliated to RAYNET-UK</div>
            </div>
        </div>

    </div>
</div>
</div>

</body>
</html>