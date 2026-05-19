<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 9pt; color: #000; }
.page { padding: 15mm 15mm 10mm 15mm; }

.header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px; border-bottom: 2px solid #C8102E; padding-bottom: 8px; }
.logo-box { border: 2px solid #C8102E; padding: 4px 8px; text-align: center; }
.logo-raynet { font-size: 20pt; font-weight: bold; color: #C8102E; letter-spacing: 2px; }
.logo-uk { font-size: 14pt; font-weight: bold; color: #003366; }
.header-right { text-align: right; font-size: 7.5pt; color: #333; line-height: 1.5; }
.header-right strong { display: block; }

.form-title { font-size: 16pt; font-weight: bold; margin: 8px 0 4px 0; color: #003366; }
.member-id-box { border: 1px solid #000; padding: 4px 10px; float: right; margin-top: -24px; }
.member-id-label { font-size: 8pt; font-weight: bold; }
.member-id-sub { font-size: 7pt; color: #666; font-style: italic; }
.member-id-field { border: none; border-bottom: 1px solid #000; min-width: 80px; height: 16px; display: block; margin-top: 2px; }

.instruction { font-style: italic; font-size: 8pt; margin: 4px 0 8px 0; }

.section-title { font-size: 11pt; font-weight: bold; color: #003366; border-bottom: 1px solid #003366; margin: 10px 0 6px 0; padding-bottom: 2px; }

.field-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.field-table td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
.field-label { font-weight: bold; font-size: 8.5pt; width: 28%; background: #f5f5f5; }
.field-value { font-size: 9pt; min-height: 16px; }
.field-value-wide { font-size: 9pt; min-height: 16px; }

.doc-table { width: 100%; border-collapse: collapse; margin: 6px 0; }
.doc-table th { background: #003366; color: #fff; font-size: 8pt; padding: 4px 6px; text-align: left; border: 1px solid #000; }
.doc-table td { border: 1px solid #000; padding: 4px 6px; font-size: 8pt; vertical-align: middle; }
.doc-table .row-label { background: #003366; color: #fff; font-size: 12pt; font-weight: bold; text-align: center; width: 30px; }
.doc-subrow { border-top: none; }
.doc-subrow td { border-top: none; font-size: 7.5pt; color: #555; }

.yn-row td { padding: 3px 6px; }
.yn-box { display: inline-block; border: 1px solid #000; width: 12px; height: 12px; margin-right: 3px; text-align: center; font-size: 8pt; line-height: 12px; vertical-align: middle; }
.yn-box.checked { font-weight: bold; }
.detail-box { border: 1px solid #000; min-height: 25px; padding: 4px 6px; font-size: 8.5pt; margin: 4px 0 8px 0; }

.comms-table { width: 100%; border-collapse: collapse; margin: 6px 0; }
.comms-table td { border: 1px solid #ccc; padding: 4px 6px; font-size: 8pt; vertical-align: middle; }
.comms-table .cb { width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 14px; }

.declaration-box { border: 1px solid #000; padding: 6px; margin: 6px 0; font-size: 8.5pt; line-height: 1.6; }
.sig-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
.sig-table td { border: 1px solid #000; padding: 5px 8px; }
.sig-label { font-weight: bold; font-size: 8.5pt; background: #f5f5f5; width: 25%; }
.sig-field { min-height: 20px; }

.footer { font-size: 7pt; color: #555; border-top: 1px solid #ccc; margin-top: 8px; padding-top: 4px; }
.page-num { text-align: right; }

.notice-box { border: 1px solid #666; padding: 5px 8px; font-size: 7.5pt; margin: 6px 0; line-height: 1.5; }

.clearfix::after { content: ''; display: table; clear: both; }

@page { margin: 0; size: A4; }
</style>
</head>
<body>

{{-- ═══ PAGE 1 ═══ --}}
<div class="page">

    {{-- Header --}}
    <div class="header clearfix">
        <div class="logo-box">
            <div style="font-size:8pt;color:#003366;font-weight:bold">Communications by</div>
            <div class="logo-raynet">RAYNET<span class="logo-uk">-UK</span></div>
            <div style="font-size:7pt;color:#666">www.raynet-uk.net</div>
        </div>
        <div class="header-right">
            A company limited by guarantee. Registered in England No 2771954<br>
            Registered Charity in England &amp; Wales (1047725) and in Scotland (SC046184)<br>
            <strong>Registered Office: 9 Conigre, Chinnor, Oxfordshire OX39 4JY</strong>
        </div>
    </div>

    <div class="clearfix">
        <div class="member-id-box" style="float:right">
            <div class="member-id-label">Member ID:</div>
            <div class="member-id-sub">(For Office Use Only)</div>
            <div class="member-id-field"></div>
        </div>
        <div class="form-title">REG-02 – NEW MEMBER APPLICATION</div>
    </div>

    <p class="instruction">Please complete the form in <strong>BLOCK CAPITALS</strong> and <strong>Black Ink</strong></p>

    {{-- Section 1 --}}
    <div class="section-title">1. PERSONAL DETAILS</div>

    <table class="field-table">
        <tr>
            <td class="field-label">Callsign:</td>
            <td class="field-value" colspan="3">{{ strtoupper($data['callsign'] ?? '') }}</td>
        </tr>
        <tr>
            <td class="field-label">Title:</td>
            <td class="field-value" style="width:22%">{{ $data['title'] ?? '' }}</td>
            <td class="field-label" style="width:22%">Surname:</td>
            <td class="field-value">{{ strtoupper($data['surname'] ?? '') }}</td>
        </tr>
        <tr>
            <td class="field-label">Forenames:</td>
            <td class="field-value">{{ strtoupper($data['forenames'] ?? '') }}</td>
            <td class="field-label">Known As:</td>
            <td class="field-value">{{ $data['known_as'] ?? '' }}
                <div style="font-size:7pt;color:#666;font-style:italic">Preferred name on ID Card</div>
            </td>
        </tr>
        <tr>
            <td class="field-label">Date of Birth:</td>
            <td class="field-value" colspan="3">{{ $data['dob'] ? \Carbon\Carbon::parse($data['dob'])->format('d/m/Y') : '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Home Tel No:</td>
            <td class="field-value">{{ $data['home_tel'] ?? '' }}
                <div style="font-size:7pt;color:#666">
                    <span style="border:1px solid #000;padding:0 2px">{{ !empty($data['home_tel_ex']) ? '✓' : ' ' }}</span>
                    Ex directory
                </div>
            </td>
            <td class="field-label">Mobile Phone No:</td>
            <td class="field-value">{{ $data['mobile'] ?? '' }}
                <div style="font-size:7pt;color:#666">
                    <span style="border:1px solid #000;padding:0 2px">{{ !empty($data['mobile_ex']) ? '✓' : ' ' }}</span>
                    Ex directory
                </div>
            </td>
        </tr>
        <tr>
            <td class="field-label">Nationality:</td>
            <td class="field-value">{{ $data['nationality'] ?? '' }}</td>
            <td class="field-label" rowspan="3" style="vertical-align:top">Home Address<br>inc. County &amp;<br>Postcode:</td>
            <td class="field-value" rowspan="3" style="white-space:pre-line">{{ $data['address'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Former/Dual Nationality:</td>
            <td class="field-value">{{ $data['former_nationality'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Place of Birth:</td>
            <td class="field-value">{{ $data['place_of_birth'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Email Address:</td>
            <td class="field-value" colspan="3">{{ $data['email'] ?? '' }}</td>
        </tr>
    </table>

    {{-- Section 2 --}}
    <div class="section-title">2. MEMBER VERIFICATION CHECK</div>
    <div class="notice-box">
        There is an increasing requirement on all types of organisations that its members' are confirmed as to who they are.
        This equally applies to voluntary and charitable organisations of which RAYNET-UK is one and is considered to be good
        recruitment practice. The requirement includes checks on each member to confirm their identity, where they live, that
        they are entitled to reside in this country and that they have no unspent convictions. RAYNET-UK requires that all new
        members undergo this check upon membership application.
    </div>

    <p style="font-size:9pt;font-weight:bold;margin:6px 0 4px 0">A: CERTIFICATION OF IDENTITY (Including Document Reference Number)</p>
    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:30px"></th>
                <th style="width:65%">Document Type:</th>
                <th>Date of Issue:<br><span style="font-size:7pt;font-weight:normal">(dd/mm/yyyy)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="row-label" rowspan="2">A</td>
                <td style="color:#999;font-style:italic">{{ $data['doc_a_type'] ?? 'Type & name of document - List A' }}</td>
                <td>{{ $data['doc_a_date'] ?? '' }}</td>
            </tr>
            <tr class="doc-subrow">
                <td colspan="2" style="color:#999;font-style:italic">Document Reference Number: {{ $data['doc_a_ref'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="row-label" rowspan="2">B</td>
                <td style="color:#999;font-style:italic">{{ $data['doc_b_type'] ?? 'Type & name of document - List B' }}</td>
                <td>{{ $data['doc_b_date'] ?? '' }}</td>
            </tr>
            <tr class="doc-subrow">
                <td colspan="2" style="color:#999;font-style:italic">Document Reference Number: {{ $data['doc_b_ref'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>Form REG-02 V2.2 (Rev 2023-03) — Submitted via {{ $groupName }} Member Portal — {{ now()->format('d M Y') }}</div>
    </div>
</div>

{{-- ═══ PAGE 2 ═══ --}}
<div class="page" style="page-break-before:always">

    <div class="header clearfix" style="margin-bottom:6px">
        <div class="logo-box">
            <div style="font-size:8pt;color:#003366;font-weight:bold">Communications by</div>
            <div class="logo-raynet">RAYNET<span class="logo-uk">-UK</span></div>
        </div>
        <div class="header-right">
            A company limited by guarantee. Registered in England No 2771954<br>
            Registered Charity in England &amp; Wales (1047725) and in Scotland (SC046184)<br>
            <strong>Registered Office: 9 Conigre, Chinnor, Oxfordshire OX39 4JY</strong>
        </div>
    </div>

    <p style="font-size:9pt;font-weight:bold;margin:6px 0 4px 0">B: CRIMINAL RECORD DECLARATION</p>
    <div class="notice-box">
        RAYNET-UK may require access to or hold material or information that is the property of the Government.
        The organisation has a duty to protect these assets while in its possession and this obligation extends to its members.
        Since you are or may become such a person, please complete the following sections:
    </div>

    @foreach([
        ['num'=>'1.','text'=>'Have you ever been convicted or found guilty by a Court of any offence in any country (excluding parking but including all motoring offences even where a spot fine has been administered by the police), or have you ever received a community rehabilitation order (previously called probation), or absolutely/conditionally discharged or bound over after being charged with any offence, or is there any action pending against you? You need not declare convictions which are classed as "spent" under the Rehabilitation of Offenders Act (1974). Please give dates and sentence.', 'key'=>'criminal_1','detail_key'=>'criminal_1_detail'],
        ['num'=>'2.','text'=>'Have you ever been convicted by a Court Martial or sentenced to detention or dismissal whilst serving in the Armed Forces of the UK or any Commonwealth or foreign country? You need not declare convictions which are classed as "spent" under the Rehabilitation of Offenders Act (1974). Please give dates and sentence.','key'=>'criminal_2','detail_key'=>'criminal_2_detail'],
        ['num'=>'3.','text'=>'Do you know of any other matter in your background that might cause your reliability or suitability to have access to government assets to be called into question? Please give details.','key'=>'criminal_3','detail_key'=>'criminal_3_detail'],
    ] as $q)
    <table style="width:100%;border-collapse:collapse;margin-bottom:2px">
        <tr>
            <td style="width:20px;vertical-align:top;font-weight:bold;padding-top:2px">{{ $q['num'] }}</td>
            <td style="font-size:8.5pt;line-height:1.4">{{ $q['text'] }}</td>
        </tr>
    </table>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2px">
        <tr>
            <td style="font-size:8.5pt;font-style:italic;padding:2px 0">If Yes, please give details here</td>
            <td style="text-align:right;width:140px">
                <span style="border:1px solid #000;padding:2px 8px;margin-right:6px">
                    {{ ($data[$q['key']] ?? '') === 'yes' ? '✓' : ' ' }} Yes
                </span>
                <span style="border:1px solid #000;padding:2px 8px">
                    {{ ($data[$q['key']] ?? 'no') === 'no' ? '✓' : ' ' }} No
                </span>
            </td>
        </tr>
    </table>
    <div class="detail-box">{{ $data[$q['detail_key']] ?? '' }}</div>
    @endforeach

    <div class="section-title">3. RAYNET-UK EMAIL ADDRESS</div>
    <div class="notice-box">
        All members will be allocated a RAYNET-UK M365 account. This gives the member an @raynet-uk.net email address as part of
        a Microsoft 365 package. Also included are online versions of Microsoft Office packages (Word, Excel, Powerpoint, Outlook).
        All national mailings will be sent to the members M365 email addresses, incoming emails can be forwarded to the members
        personal email addresses.<br>
        Members must adhere to the Microsoft 365 End User Agreement as per the REG-02 Supplementary Information.
    </div>

    <div class="footer">
        <div>Form REG-02 V2.2 (Rev 2023-03) — Submitted via {{ $groupName }} Member Portal — {{ now()->format('d M Y') }}</div>
    </div>
</div>

{{-- ═══ PAGE 3 ═══ --}}
<div class="page" style="page-break-before:always">

    <div class="header clearfix" style="margin-bottom:6px">
        <div class="logo-box">
            <div style="font-size:8pt;color:#003366;font-weight:bold">Communications by</div>
            <div class="logo-raynet">RAYNET<span class="logo-uk">-UK</span></div>
        </div>
        <div class="header-right">
            A company limited by guarantee. Registered in England No 2771954<br>
            Registered Charity in England &amp; Wales (1047725) and in Scotland (SC046184)<br>
            <strong>Registered Office: 9 Conigre, Chinnor, Oxfordshire OX39 4JY</strong>
        </div>
    </div>

    <div class="section-title">4. MEMBER COMMUNICATIONS</div>
    <p style="font-size:8pt;text-align:right;font-style:italic;margin-bottom:4px">Please tick where appropriate</p>
    <table class="comms-table">
        @foreach([
            ['nat_key'=>'comms_national_email','grp_key'=>'comms_group_email','nat_label'=>'Yes please - I would like to receive RAYNET National communications by email (these will be sent to the member\'s RAYNET M365 Account)','grp_label'=>'Yes please - I would like to receive RAYNET Group communications by email'],
            ['nat_key'=>'comms_national_tel','grp_key'=>'comms_group_tel','nat_label'=>'Yes please - I would like to receive RAYNET National communications by telephone','grp_label'=>'Yes please - I would like to receive RAYNET Group communications by telephone'],
            ['nat_key'=>'comms_national_sms','grp_key'=>'comms_group_sms','nat_label'=>'Yes, please - I would like to receive RAYNET National communications by SMS','grp_label'=>'Yes, please - I would like to receive RAYNET Group communications by SMS'],
            ['nat_key'=>'comms_national_post','grp_key'=>'comms_group_post','nat_label'=>'Yes please - I would like to receive RAYNET National communications by post','grp_label'=>'Yes please - I would like to receive RAYNET Group communications by post'],
        ] as $row)
        <tr>
            <td style="width:45%">{{ $row['nat_label'] }}</td>
            <td style="width:50px;text-align:center">
                <span class="cb">{{ !empty($data[$row['nat_key']]) ? '✓' : '' }}</span>
            </td>
            <td style="width:45%">{{ $row['grp_label'] }}</td>
            <td style="width:50px;text-align:center">
                <span class="cb">{{ !empty($data[$row['grp_key']]) ? '✓' : '' }}</span>
            </td>
        </tr>
        @endforeach
    </table>

    <div class="section-title">5. DECLARATION</div>
    <div class="declaration-box">
        <p>I agree to become a member of RAYNET-UK</p>
        <p>I agree that I am in sympathy with the Company's aims and objects and agree to abide by its rules as circulated by RAYNET-UK</p>
        <p>Should the company be dissolved, I promise to pay the maximum of £5.00 towards its debts if asked to do so</p>
        <p>I am over 18 years of age</p>
        <p>I declare that the information that I have given on this form is true and complete to the best of my knowledge and belief</p>
    </div>
    <table class="sig-table">
        <tr>
            <td class="sig-label">Signature:</td>
            <td class="sig-field" style="padding:2px 4px">
                @if(!empty($signature))
                    <img src="{{ $signature }}" style="max-height:45px;max-width:240px;display:block">
                @endif
            </td>
            <td class="sig-label" style="width:15%">Date:</td>
            <td style="width:25%">{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
    <p style="font-size:7.5pt;font-style:italic;text-align:center;margin:2px 0 8px 0">
        @if(!empty($signature))
            ✓ Digitally signed via {{ $groupName }} member portal — {{ now()->format('d M Y H:i') }}
        @else
            Handwritten signature required
        @endif
    </p>

    <div class="section-title">6. DECLARATION FOR JUNIOR MEMBERS</div>
    <div class="notice-box" style="font-size:8pt">
        I, the undersigned, am legally responsible for the above named applicant and certify that to the best of my knowledge
        and belief, they are in sympathy with the Company's aims and objectives and agrees to abide by its rules as circulated
        by RAYNET-UK in so far as it is fair and reasonable for a person under the age of 18 years to do so.
    </div>
    <table class="sig-table">
        <tr>
            <td class="sig-label">Name:</td>
            <td class="sig-field"></td>
            <td class="sig-label" style="width:15%">Date:</td>
            <td style="width:25%"></td>
        </tr>
        <tr>
            <td class="sig-label">Signature:</td>
            <td class="sig-field"></td>
            <td colspan="2">
                <span style="border:1px solid #000;padding:2px 6px;margin-right:6px">Parent</span>
                <span style="border:1px solid #000;padding:2px 6px">Guardian</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div>Form REG-02 V2.2 (Rev 2023-03) — Submitted via {{ $groupName }} Member Portal — {{ now()->format('d M Y') }}</div>
    </div>
</div>

</body>
</html>
