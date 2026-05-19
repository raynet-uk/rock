@extends('layouts.app')
@section('title', 'New Member Application — REG-02')
@section('content')

<style>
.reg-wrap { max-width: 860px; margin: 0 auto; padding: 0 1rem 3rem; }
.reg-header { background: linear-gradient(135deg, var(--navy), #004d99); color: #fff; padding: 2rem 1.5rem; margin-bottom: 2rem; border-bottom: 4px solid var(--red); }
.reg-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: .35rem; }
.reg-header p { font-size: .88rem; opacity: .8; }
.reg-section { background: var(--white); border: 1px solid var(--border); margin-bottom: 1.5rem; overflow: hidden; }
.reg-section-head { background: var(--navy); color: #fff; padding: .6rem 1rem; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; display: flex; align-items: center; gap: .5rem; }
.reg-section-body { padding: 1.25rem 1.5rem; }
.reg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
.reg-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .85rem; }
.reg-grid-wide { display: grid; grid-template-columns: 2fr 1fr; gap: .85rem; }
@media(max-width:600px){ .reg-grid,.reg-grid-3,.reg-grid-wide { grid-template-columns: 1fr; } }
.reg-field { display: flex; flex-direction: column; gap: .25rem; }
.reg-field label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); }
.reg-field label .req { color: var(--red); }
.reg-input, .reg-select, .reg-textarea {
    background: var(--off-white); border: 1px solid var(--border);
    padding: .55rem .75rem; color: var(--text); font-family: inherit;
    font-size: .88rem; outline: none; width: 100%; transition: border-color .15s;
    border-radius: 0;
}
.reg-input:focus, .reg-select:focus, .reg-textarea:focus { border-color: var(--navy-mid); box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.reg-textarea { resize: vertical; min-height: 70px; }
.reg-checkbox-group { display: flex; align-items: center; gap: .5rem; margin-top: .25rem; }
.reg-checkbox-group input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--navy); flex-shrink: 0; }
.reg-checkbox-group label { font-size: .8rem; color: var(--text); font-weight: normal; text-transform: none; letter-spacing: 0; cursor: pointer; }
.reg-yn { display: flex; gap: 1rem; margin-top: .25rem; }
.reg-yn label { display: flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: normal; text-transform: none; letter-spacing: 0; cursor: pointer; }
.reg-yn input { width: 16px; height: 16px; accent-color: var(--navy); }
.detail-toggle { display: none; margin-top: .65rem; }
.reg-info { background: #f0f5ff; border: 1px solid rgba(0,51,102,.2); border-left: 3px solid var(--navy); padding: .75rem 1rem; font-size: .82rem; color: var(--text-mid); margin-bottom: 1rem; line-height: 1.6; }
.reg-warn { background: #fff7ed; border: 1px solid rgba(245,158,11,.4); border-left: 3px solid #f59e0b; padding: .65rem 1rem; font-size: .8rem; color: #78350f; margin-bottom: 1rem; }
.comms-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.comms-table th { background: var(--navy); color: #fff; padding: .45rem .75rem; text-align: left; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
.comms-table td { border: 1px solid var(--border); padding: .5rem .75rem; vertical-align: middle; }
.comms-table tr:nth-child(even) td { background: var(--off-white); }
.submit-section { background: var(--navy); padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.submit-btn { background: var(--red); border: none; color: #fff; padding: .85rem 2rem; font-family: inherit; font-size: .9rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; cursor: pointer; transition: all .15s; }
.submit-btn:hover { background: #a50d24; }
.submit-note { font-size: .75rem; color: rgba(255,255,255,.6); }
.err-msg { font-size: .72rem; color: var(--red); margin-top: .2rem; }
</style>

<div class="reg-header">
    <div style="max-width:860px;margin:0 auto">
        <h1>📋 REG-02 — New Member Application</h1>
        <p>Complete this form to apply for RAYNET-UK membership with {{ \App\Helpers\RaynetSetting::groupName() }}. Your application will be sent directly to the Group Controller for processing.</p>
    </div>
</div>

<div class="reg-wrap">

    @if($errors->any())
    <div class="reg-warn">⚠ Please correct the errors below before submitting.</div>
    @endif

    <div class="reg-info">
        ℹ Please complete all required fields marked <span style="color:var(--red);font-weight:700">*</span>. Once submitted, a completed REG-02 PDF will be generated and emailed to the Group Controller. You will also receive a confirmation email.
    </div>

    <form method="POST" action="{{ route('member-application.submit') }}" enctype="multipart/form-data">
    @csrf

    {{-- Section 1 --}}
    <div class="reg-section">
        <div class="reg-section-head">👤 1. Personal Details</div>
        <div class="reg-section-body">
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Callsign</label>
                    <input type="text" name="callsign" class="reg-input" value="{{ old('callsign') }}" placeholder="e.g. M7ABC" style="text-transform:uppercase">
                </div>
                <div></div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Title</label>
                    <select name="title" class="reg-select">
                        <option value="">— Select —</option>
                        @foreach(['Mr','Mrs','Miss','Ms','Dr','Prof','Rev'] as $t)
                        <option value="{{ $t }}" {{ old('title')===$t?'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="reg-field">
                    <label>Surname <span class="req">*</span></label>
                    <input type="text" name="surname" class="reg-input" value="{{ old('surname') }}" required>
                    @error('surname')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Forenames <span class="req">*</span></label>
                    <input type="text" name="forenames" class="reg-input" value="{{ old('forenames') }}" required>
                    @error('forenames')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="reg-field">
                    <label>Known As <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">(preferred name on ID card)</span></label>
                    <input type="text" name="known_as" class="reg-input" value="{{ old('known_as') }}">
                </div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="date" name="dob" class="reg-input" value="{{ old('dob') }}" required>
                    @error('dob')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="reg-field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" class="reg-input" value="{{ old('email') }}" required>
                    @error('email')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Home Telephone</label>
                    <input type="tel" name="home_tel" class="reg-input" value="{{ old('home_tel') }}">
                    <div class="reg-checkbox-group"><input type="checkbox" name="home_tel_ex" id="home_tel_ex" value="1" {{ old('home_tel_ex')?'checked':'' }}><label for="home_tel_ex">Ex-directory</label></div>
                </div>
                <div class="reg-field">
                    <label>Mobile Phone</label>
                    <input type="tel" name="mobile" class="reg-input" value="{{ old('mobile') }}">
                    <div class="reg-checkbox-group"><input type="checkbox" name="mobile_ex" id="mobile_ex" value="1" {{ old('mobile_ex')?'checked':'' }}><label for="mobile_ex">Ex-directory</label></div>
                </div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Nationality</label>
                    <input type="text" name="nationality" class="reg-input" value="{{ old('nationality','British') }}">
                </div>
                <div class="reg-field">
                    <label>Former/Dual Nationality</label>
                    <input type="text" name="former_nationality" class="reg-input" value="{{ old('former_nationality') }}" placeholder="If applicable">
                </div>
            </div>
            <div class="reg-grid" style="margin-bottom:.85rem">
                <div class="reg-field">
                    <label>Place of Birth</label>
                    <input type="text" name="place_of_birth" class="reg-input" value="{{ old('place_of_birth') }}">
                </div>
            </div>
            <div class="reg-field">
                <label>Home Address inc. County &amp; Postcode <span class="req">*</span></label>
                <textarea name="address" class="reg-textarea" rows="3" required>{{ old('address') }}</textarea>
                @error('address')<div class="err-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Section 2A --}}
    <div class="reg-section">
        <div class="reg-section-head">🪪 2A. Certification of Identity</div>
        <div class="reg-section-body">
            <div class="reg-info">
                You must provide <strong>one item from List A and one item from List B</strong>. These may be copies endorsed by your Group Controller.<br>
                <strong>List A:</strong> Passport, UK Driving Licence, Birth Certificate, Adoption Certificate, Firearms Certificate, Citizencard, Gender Recognition Certificate, Police Registration Document, HM Forces ID, National ID Card.<br>
                <strong>List B:</strong> HMRC Document (inc P60), DWP Benefits letter, Utility bill (last 6 months), Council Tax bill, Bank statement (last 6 months), Mortgage statement, Court order, Tenancy Agreement, Record of Official Home Visit.
            </div>
            <div class="reg-grid-wide" style="margin-bottom:1rem">
                <div class="reg-field">
                    <label>Document A — Type &amp; Name</label>
                    <input type="text" name="doc_a_type" class="reg-input" value="{{ old('doc_a_type') }}" placeholder="e.g. Full UK Passport">
                </div>
                <div class="reg-field">
                    <label>Date of Issue</label>
                    <input type="text" name="doc_a_date" class="reg-input" value="{{ old('doc_a_date') }}" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="reg-field" style="margin-bottom:1rem">
                <label>Document A — Reference Number</label>
                <input type="text" name="doc_a_ref" class="reg-input" value="{{ old('doc_a_ref') }}" placeholder="Passport number, account number, etc.">
            </div>
            <div class="reg-field" style="margin-bottom:1rem">
                <label>Document A — Upload Copy <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">(PDF, JPG or PNG, max 5MB)</span></label>
                <input type="file" name="doc_a_file" class="reg-input" accept=".pdf,.jpg,.jpeg,.png" style="padding:.4rem .75rem;cursor:pointer">
                @error('doc_a_file')<div class="err-msg">{{ $message }}</div>@enderror
            </div>
            <div class="reg-field" style="margin-bottom:1rem">
                <label>Document A — Upload Copy <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">(PDF, JPG or PNG, max 5MB)</span></label>
                <input type="file" name="doc_a_file" class="reg-input" accept=".pdf,.jpg,.jpeg,.png" style="padding:.4rem .75rem;cursor:pointer">
                @error('doc_a_file')<div class="err-msg">{{ $message }}</div>@enderror
            </div>
            <div class="reg-grid-wide" style="margin-bottom:1rem">
                <div class="reg-field">
                    <label>Document B — Type &amp; Name</label>
                    <input type="text" name="doc_b_type" class="reg-input" value="{{ old('doc_b_type') }}" placeholder="e.g. Recent utility bill">
                </div>
                <div class="reg-field">
                    <label>Date of Issue</label>
                    <input type="text" name="doc_b_date" class="reg-input" value="{{ old('doc_b_date') }}" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="reg-field" style="margin-bottom:1rem">
                <label>Document B — Reference Number</label>
                <input type="text" name="doc_b_ref" class="reg-input" value="{{ old('doc_b_ref') }}" placeholder="Account number, reference, etc.">
            </div>
            <div class="reg-field">
                <label>Document B — Upload Copy <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">(PDF, JPG or PNG, max 5MB)</span></label>
                <input type="file" name="doc_b_file" class="reg-input" accept=".pdf,.jpg,.jpeg,.png" style="padding:.4rem .75rem;cursor:pointer">
                @error('doc_b_file')<div class="err-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Section 2B --}}
    <div class="reg-section">
        <div class="reg-section-head">⚖️ 2B. Criminal Record Declaration</div>
        <div class="reg-section-body">
            <div class="reg-warn">You need not declare convictions classed as "spent" under the Rehabilitation of Offenders Act (1974). A conviction is not necessarily a barrier to membership but must be declared if unspent.</div>

            @foreach([
                ['num'=>'1','name'=>'criminal_1','detail'=>'criminal_1_detail','label'=>'Have you ever been convicted or found guilty by a Court of any offence in any country (excluding parking but including all motoring offences), or received a community rehabilitation order, or absolutely/conditionally discharged or bound over, or is there any action pending against you?'],
                ['num'=>'2','name'=>'criminal_2','detail'=>'criminal_2_detail','label'=>'Have you ever been convicted by a Court Martial or sentenced to detention or dismissal whilst serving in the Armed Forces of the UK or any Commonwealth or foreign country?'],
                ['num'=>'3','name'=>'criminal_3','detail'=>'criminal_3_detail','label'=>'Do you know of any other matter in your background that might cause your reliability or suitability to have access to government assets to be called into question?'],
            ] as $q)
            <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border)">
                <p style="font-size:.85rem;margin-bottom:.5rem"><strong>{{ $q['num'] }}.</strong> {{ $q['label'] }}</p>
                <div class="reg-yn">
                    <label><input type="radio" name="{{ $q['name'] }}" value="yes" onchange="toggleDetail('{{ $q['detail'] }}', true)" {{ old($q['name'])==='yes'?'checked':'' }}> Yes</label>
                    <label><input type="radio" name="{{ $q['name'] }}" value="no" onchange="toggleDetail('{{ $q['detail'] }}', false)" {{ old($q['name'],'no')==='no'?'checked':'' }}> No</label>
                </div>
                <div class="detail-toggle" id="detail-{{ $q['detail'] }}" style="{{ old($q['name'])==='yes'?'display:block':'' }}">
                    <div class="reg-field" style="margin-top:.5rem">
                        <label>Please provide details (type, date, sentence):</label>
                        <textarea name="{{ $q['detail'] }}" class="reg-textarea" rows="3">{{ old($q['detail']) }}</textarea>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Section 4 --}}
    <div class="reg-section">
        <div class="reg-section-head">📬 4. Member Communications</div>
        <div class="reg-section-body">
            <table class="comms-table">
                <thead>
                    <tr>
                        <th>Communication Type</th>
                        <th style="text-align:center">National</th>
                        <th style="text-align:center">Group</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Email','comms_national_email','comms_group_email'],
                        ['Telephone','comms_national_tel','comms_group_tel'],
                        ['SMS','comms_national_sms','comms_group_sms'],
                        ['Post','comms_national_post','comms_group_post'],
                    ] as $row)
                    <tr>
                        <td>{{ $row[0] }}</td>
                        <td style="text-align:center"><input type="checkbox" name="{{ $row[1] }}" value="1" {{ old($row[1])?'checked':'' }}></td>
                        <td style="text-align:center"><input type="checkbox" name="{{ $row[2] }}" value="1" {{ old($row[2])?'checked':'' }}></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 5 --}}
    <div class="reg-section">
        <div class="reg-section-head">✍️ 5. Declaration &amp; Signature</div>
        <div class="reg-section-body">
            <div class="reg-info">
                By signing below I declare that:<br>
                • I agree to become a member of RAYNET-UK<br>
                • I am in sympathy with the Company's aims and agree to abide by its rules<br>
                • Should the company be dissolved, I promise to pay the maximum of £5.00 towards its debts if asked<br>
                • I am over 18 years of age<br>
                • The information I have given is true and complete to the best of my knowledge and belief
            </div>

            {{-- Hidden fields --}}
            <input type="hidden" name="signature" id="signatureData">
            <input type="hidden" name="sig_token" id="sigToken">

            {{-- MOBILE: inline signature pad --}}
            <div id="mobileSigSection" style="display:none">
                <div class="reg-field" style="margin-bottom:.5rem">
                    <label>Your Signature <span class="req">*</span></label>
                </div>
                <div style="border:2px solid var(--border);background:#fff;position:relative;touch-action:none;margin-bottom:.5rem">
                    <canvas id="mobileSigCanvas" style="display:block;width:100%;height:180px"></canvas>
                    <div style="position:absolute;bottom:36px;left:12px;right:12px;height:1px;background:var(--border);pointer-events:none"></div>
                    <div style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);font-size:.68rem;color:var(--text-muted);pointer-events:none;white-space:nowrap">Sign above this line</div>
                </div>
                <div style="display:flex;gap:.5rem;margin-bottom:.75rem">
                    <button type="button" onclick="mobileClearSig()" style="flex:1;padding:.55rem;background:var(--grey);border:1px solid var(--border);color:var(--text-muted);font-family:inherit;font-size:.78rem;font-weight:700;cursor:pointer;text-transform:uppercase">✕ Clear</button>
                    <div id="mobileSigStatus" style="flex:2;display:flex;align-items:center;justify-content:center;font-size:.78rem;color:var(--text-muted);font-style:italic">Draw your signature above</div>
                </div>
            </div>

            {{-- DESKTOP: QR code approach --}}
            <div id="desktopSigSection" style="display:none">
                <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap">
                    <div id="qrBox" style="flex-shrink:0;text-align:center">
                        <div style="border:2px solid var(--border);padding:1rem;background:#fff;display:inline-block;margin-bottom:.5rem" id="qrCode"></div>
                        <div style="font-size:.72rem;color:var(--text-muted)">Scan with your phone camera</div>
                    </div>
                    <div style="flex:1;min-width:200px">
                        <div style="font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:.5rem">Sign on your phone</div>
                        <div style="font-size:.82rem;color:var(--text-muted);line-height:1.6;margin-bottom:.85rem">
                            1. Scan the QR code with your phone<br>
                            2. Sign with your finger on the signature pad<br>
                            3. Tap "Confirm Signature"<br>
                            4. Your signature will appear here automatically
                        </div>
                        <div id="sigPollStatus" style="display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:var(--text-muted)">
                            <span style="width:8px;height:8px;border-radius:50%;background:var(--border);display:inline-block;animation:blink-dot 1.5s ease-in-out infinite"></span>
                            Waiting for signature…
                        </div>
                        <div id="sigPreview" style="display:none;margin-top:.75rem">
                            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--green);margin-bottom:.35rem">✓ Signature received</div>
                            <div style="border:2px solid rgba(26,122,60,.3);background:#f8fff9;padding:.5rem;display:inline-block">
                                <img id="sigPreviewImg" src="" alt="Signature" style="max-height:80px;max-width:100%;display:block">
                            </div>
                            <button type="button" onclick="resetDesktopSig()" style="display:block;margin-top:.4rem;background:none;border:none;color:var(--red);font-size:.72rem;cursor:pointer;font-family:inherit;text-decoration:underline">Re-sign</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sig loaded indicator (both paths) --}}
            <div id="sigCaptured" style="display:none;background:rgba(26,122,60,.08);border:1px solid rgba(26,122,60,.25);border-left:3px solid var(--green);padding:.6rem .9rem;font-size:.82rem;color:var(--green);margin-top:.5rem">
                ✓ Signature captured — ready to submit
            </div>

        </div>
    </div>

    <div class="submit-section">
        <div class="submit-note">
            ℹ Your completed REG-02 will be emailed as a PDF to the Group Controller.<br>
            You will receive a confirmation email at the address you provided.
        </div>
        <button type="submit" class="submit-btn">📋 Submit Application →</button>
    </div>

    </form>
</div>

<script>
function toggleDetail(name, show) {
    const el = document.getElementById('detail-' + name);
    if (el) el.style.display = show ? 'block' : 'none';
}
document.querySelectorAll('input[type=radio][name^="criminal_"]').forEach(r => {
    r.addEventListener('change', () => toggleDetail(r.name.replace('criminal_','criminal_') + '_detail', r.value === 'yes'));
});
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
@keyframes blink-dot{0%,100%{opacity:1}50%{opacity:.25}}
</style>
<script>
const isMobile = /Mobi|Android|iPhone|iPad|iPod|Touch/i.test(navigator.userAgent) || window.innerWidth < 768;
let mobilePad = null, pollInterval = null, currentToken = null, sigCaptured = false;

document.addEventListener('DOMContentLoaded', function() {
    if (isMobile) initMobile();
    else initDesktop();

    // Validate signature before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!sigCaptured) {
            e.preventDefault();
            const section = isMobile
                ? document.getElementById('mobileSigSection')
                : document.getElementById('desktopSigSection');
            section.scrollIntoView({behavior:'smooth'});
            alert('Please add your signature before submitting.');
        }
    });
});

// ── MOBILE ──────────────────────────────────────────────────────────────
function initMobile() {
    document.getElementById('mobileSigSection').style.display = 'block';
    const canvas = document.getElementById('mobileSigCanvas');
    const ratio  = Math.max(window.devicePixelRatio || 1, 1);
    const rect   = canvas.getBoundingClientRect();
    canvas.width  = rect.width  * ratio;
    canvas.height = rect.height * ratio;
    canvas.getContext('2d').scale(ratio, ratio);

    mobilePad = new SignaturePad(canvas, {
        minWidth: 1.5, maxWidth: 3.5,
        penColor: '#001f40',
        backgroundColor: 'rgba(255,255,255,0)',
    });

    mobilePad.addEventListener('endStroke', captureMobileSig);
}

function captureMobileSig() {
    if (mobilePad && !mobilePad.isEmpty()) {
        const dataUrl = mobilePad.toDataURL('image/png');
        document.getElementById('signatureData').value = dataUrl;
        document.getElementById('mobileSigStatus').textContent = '✓ Signature drawn';
        document.getElementById('mobileSigStatus').style.color = 'var(--green)';
        document.getElementById('sigCaptured').style.display = 'block';
        sigCaptured = true;
    }
}

function mobileClearSig() {
    if (mobilePad) mobilePad.clear();
    document.getElementById('signatureData').value = '';
    document.getElementById('mobileSigStatus').textContent = 'Draw your signature above';
    document.getElementById('mobileSigStatus').style.color = 'var(--text-muted)';
    document.getElementById('sigCaptured').style.display = 'none';
    sigCaptured = false;
}

// ── DESKTOP ──────────────────────────────────────────────────────────────
async function initDesktop() {
    document.getElementById('desktopSigSection').style.display = 'block';

    // Generate token
    const resp = await fetch('/member-application/sign-token', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    });
    const json = await resp.json();
    currentToken = json.token;
    document.getElementById('sigToken').value = currentToken;

    // Generate QR code
    const signUrl = window.location.origin + '/member-application/sign/' + currentToken;
    new QRCode(document.getElementById('qrCode'), {
        text: signUrl,
        width: 160,
        height: 160,
        colorDark: '#001f40',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });

    // Start polling
    startPolling();
}

function startPolling() {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        if (!currentToken) return;
        try {
            const resp = await fetch('/member-application/sign/' + currentToken + '/status', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data.ok && data.signature) {
                clearInterval(pollInterval);
                document.getElementById('signatureData').value = data.signature;
                document.getElementById('sigToken').value = currentToken;
                document.getElementById('sigPreviewImg').src = data.signature;
                document.getElementById('sigPreview').style.display = 'block';
                document.getElementById('sigPollStatus').innerHTML =
                    '<span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block"></span> Signature received ✓';
                document.getElementById('sigPollStatus').style.color = 'var(--green)';
                document.getElementById('sigCaptured').style.display = 'block';
                sigCaptured = true;
            }
        } catch(e) {}
    }, 2500);
}

async function resetDesktopSig() {
    sigCaptured = false;
    document.getElementById('signatureData').value = '';
    document.getElementById('sigPreview').style.display = 'none';
    document.getElementById('sigCaptured').style.display = 'none';
    document.getElementById('sigPollStatus').innerHTML =
        '<span style="width:8px;height:8px;border-radius:50%;background:var(--border);display:inline-block;animation:blink-dot 1.5s ease-in-out infinite"></span> Waiting for signature…';
    document.getElementById('sigPollStatus').style.color = 'var(--text-muted)';
    document.getElementById('qrCode').innerHTML = '';
    await initDesktop();
}
</script>

@endsection
