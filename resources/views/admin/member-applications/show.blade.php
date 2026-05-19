@extends('layouts.admin')
@section('title', strtoupper($application->surname) . ', ' . $application->forenames)

@section('content')
<style>
.ma-wrap{max-width:1100px;margin:0 auto;padding:0 0 4rem}
.ma-field-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
.ma-dl{display:grid;grid-template-columns:140px 1fr;gap:.35rem .75rem;font-size:12.5px}
.ma-dt{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);padding-top:2px}
.ma-dd{color:var(--text)}
.status-pill{display:inline-flex;align-items:center;padding:2px 10px;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;border:1px solid}
.sp-pending{background:#fffbeb;border-color:rgba(146,64,14,.3);color:#92400e}
.sp-approved{background:var(--green-bg);border-color:#b8ddc9;color:var(--green)}
.sp-rejected{background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red)}
.ma-decision-bar{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.1rem;background:var(--amber-bg);border:1px solid rgba(146,64,14,.2);border-left:3px solid #d97706;margin-bottom:1.5rem;flex-wrap:wrap}
.ma-info-bar{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.1rem;margin-bottom:1.5rem;border-left:3px solid}
.ma-info-bar--blue{background:var(--navy-faint);border-color:var(--navy);border:1px solid rgba(0,51,102,.2);border-left:3px solid var(--navy)}
.ma-info-bar--green{background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green)}
.ma-tick{color:var(--green);font-weight:bold}
.ma-cross{color:var(--grey-dark)}
.yn-yes{display:inline-flex;align-items:center;padding:1px 7px;background:var(--red-faint);border:1px solid rgba(200,16,46,.25);color:var(--red);font-size:10px;font-weight:bold;text-transform:uppercase}
.yn-no{display:inline-flex;align-items:center;padding:1px 7px;background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);font-size:10px;font-weight:bold;text-transform:uppercase}
.ma-sig-box{border:1px solid var(--grey-mid);padding:8px;display:inline-block;background:#fff}
.comms-grid{display:grid;grid-template-columns:1fr 1fr;gap:.3rem .5rem;font-size:12px}
</style>

<div class="ma-wrap">

    {{-- Header --}}
    <div class="rn-page-header">
        <div class="rn-page-eyebrow">
            <a href="{{ route('admin.member-applications.index') }}" style="color:inherit;text-decoration:none">Applications</a>
            &nbsp;/&nbsp; {{ strtoupper($application->surname) }}, {{ $application->forenames }}
        </div>
        <div class="rn-page-header-row">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div class="rn-page-title">{{ strtoupper($application->surname) }}, {{ $application->forenames }}</div>
                <span class="status-pill sp-{{ $application->status }}">{{ $application->status }}</span>
            </div>
            <div class="rn-page-actions">
                <a href="{{ route('admin.member-applications.index') }}" class="rn-btn rn-btn-ghost rn-btn-sm">&larr; All Applications</a>
                @if($application->pdf_path)
                <a href="{{ route('admin.member-applications.download-pdf', $application) }}" class="rn-btn rn-btn-sm" style="background:var(--navy);border-color:var(--navy);color:#fff">⬇ Download PDF</a>
                @endif
                @if($application->pdf_path)
                <a href="{{ route('admin.member-applications.download-pdf', $application) }}" class="rn-btn rn-btn-sm" style="background:var(--navy);border-color:var(--navy);color:#fff">⬇ Download PDF</a>
                @endif
            </div>
        </div>
        <div class="rn-page-desc">
            Submitted {{ $application->created_at->format('d M Y \a\t H:i') }}
            &nbsp;&bull;&nbsp; {{ $application->created_at->diffForHumans() }}
        </div>
    </div>

    {{-- Decision bar --}}
    @if($application->status === 'pending')
    <div class="ma-decision-bar">
        <div style="flex:1;font-size:12.5px;font-weight:bold;color:#92400e">
            ⏳ Awaiting decision — review the details below then approve or reject.
        </div>
        <form method="POST" action="{{ route('admin.member-applications.convert', $application) }}" style="display:inline">
            @csrf
            <button class="rn-btn rn-btn-sm" style="background:var(--green);border-color:var(--green);color:#fff"
                    onclick="return confirm('Approve and send account invite to {{ $_isTempAdmin && isset($application) && method_exists($application, 'piiVisible') && !$application->piiVisible() ? '●●●●●●●' : $application->email }}?')">
                ✓ Approve &amp; Send Invite
            </button>
        </form>
        <form method="POST" action="{{ route('admin.member-applications.reject', $application) }}" style="display:inline">
            @csrf
            <button class="rn-btn rn-btn-danger rn-btn-sm"
                    onclick="return confirm('Mark this application as rejected?')">✕ Reject</button>
        </form>
    </div>
    @elseif($application->status === 'approved' && !$application->converted_user_id)
    <div class="ma-info-bar ma-info-bar--blue" style="font-size:12.5px;font-weight:bold;color:var(--navy)">
        ✉ Invite sent to <strong>{{ $_isTempAdmin && isset($application) && method_exists($application, 'piiVisible') && !$application->piiVisible() ? '●●●●●●●' : $application->email }}</strong>
        {{ $application->invite_sent_at?->diffForHumans() }} — waiting for applicant to set up their account.
    </div>
    @elseif($application->converted_user_id)
    <div class="ma-info-bar ma-info-bar--green" style="font-size:12.5px;font-weight:bold;color:var(--green)">
        ✓ Account created for <strong>{{ $application->convertedUser?->name }}</strong>.
        <a href="{{ route('admin.users.edit', $application->converted_user_id) }}" style="color:var(--green);margin-left:.5rem">View user &rarr;</a>
    </div>
    @endif

    {{-- Section 1: Personal Details --}}
    <div class="rn-section">
        <div class="rn-section-head">
            <div class="rn-section-head-left">
                <div class="rn-section-icon" style="color:#fff">👤</div>
                <div class="rn-section-title">1. Personal Details</div>
            </div>
        </div>
        <div class="rn-card">
            <div class="rn-card-body">
                <div class="ma-field-grid">
                    <div>
                        <dl class="ma-dl">
                            <dt class="ma-dt">Callsign</dt>
                            <dd class="ma-dd"><span style="font-family:monospace;font-weight:bold;font-size:13px;color:var(--navy)">{{ strtoupper($application->callsign ?? '—') }}</span></dd>
                            <dt class="ma-dt">Title</dt>
                            <dd class="ma-dd">{{ $application->title ?? '—' }}</dd>
                            <dt class="ma-dt">Surname</dt>
                            <dd class="ma-dd" style="font-weight:bold">{{ strtoupper($application->surname) }}</dd>
                            <dt class="ma-dt">Forenames</dt>
                            <dd class="ma-dd">{{ $application->forenames }}</dd>
                            <dt class="ma-dt">Known As</dt>
                            <dd class="ma-dd">{{ $application->known_as ?? '—' }}</dd>
                            <dt class="ma-dt">Date of Birth</dt>
                            <dd class="ma-dd">{{ $application->dob->format('d/m/Y') }}</dd>
                            <dt class="ma-dt">Email</dt>
                            <dd class="ma-dd"><a href="mailto:{{ $_isTempAdmin && isset($application) && method_exists($application, 'piiVisible') && !$application->piiVisible() ? '●●●●●●●' : $application->email }}" style="color:var(--navy)">{{ $_isTempAdmin && isset($application) && method_exists($application, 'piiVisible') && !$application->piiVisible() ? '●●●●●●●' : $application->email }}</a></dd>
                        </dl>
                    </div>
                    <div>
                        <dl class="ma-dl">
                            <dt class="ma-dt">Home Tel</dt>
                            <dd class="ma-dd">{{ $application->home_tel ?? '—' }}{{ $application->home_tel_ex ? ' <span class="stat-pill sp-grey" style="font-size:9px">Ex-dir</span>' : '' }}</dd>
                            <dt class="ma-dt">Mobile</dt>
                            <dd class="ma-dd">{{ $application->mobile ?? '—' }}{{ $application->mobile_ex ? ' <span class="stat-pill sp-grey" style="font-size:9px">Ex-dir</span>' : '' }}</dd>
                            <dt class="ma-dt">Nationality</dt>
                            <dd class="ma-dd">{{ $application->nationality ?? '—' }}</dd>
                            <dt class="ma-dt">Former Nat.</dt>
                            <dd class="ma-dd">{{ $application->former_nationality ?? '—' }}</dd>
                            <dt class="ma-dt">Place of Birth</dt>
                            <dd class="ma-dd">{{ $application->place_of_birth ?? '—' }}</dd>
                            <dt class="ma-dt">Address</dt>
                            <dd class="ma-dd" style="line-height:1.6">{!! nl2br(e($application->address)) !!}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2A: Identity Documents --}}
    <div class="rn-section">
        <div class="rn-section-head">
            <div class="rn-section-head-left">
                <div class="rn-section-icon" style="color:#fff">🪪</div>
                <div class="rn-section-title">2A. Certification of Identity</div>
            </div>
        </div>
        <div class="rn-card">
            <div class="rn-card-body" style="padding:0">
                <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                    <thead>
                        <tr style="background:var(--grey)">
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid);width:40px">&nbsp;</th>
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Document Type</th>
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Date of Issue</th>
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Reference Number</th>
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Document</th>
                            <th style="padding:.5rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:.65rem .9rem;border-bottom:1px solid var(--grey-mid);font-weight:bold;color:var(--navy)">A</td>
                            <td style="padding:.65rem .9rem;border-bottom:1px solid var(--grey-mid)">{{ $application->doc_a_type ?? '—' }}</td>
                            <td style="padding:.65rem .9rem;border-bottom:1px solid var(--grey-mid)">{{ $application->doc_a_date ?? '—' }}</td>
                            <td style="padding:.65rem .9rem;border-bottom:1px solid var(--grey-mid);font-family:monospace">{{ $application->doc_a_ref ?? '—' }}</td>
                            <td style="padding:.65rem .9rem;border-bottom:1px solid var(--grey-mid)">
                                @if($application->doc_a_file)
                                <a href="{{ route('admin.member-applications.download-doc', [$application, 'a']) }}" class="rn-btn rn-btn-ghost rn-btn-sm" style="font-size:11px">⬇ Download</a>
                                @else<span style="color:var(--text-muted);font-size:11px">Not uploaded</span>@endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:.65rem .9rem;font-weight:bold;color:var(--navy)">B</td>
                            <td style="padding:.65rem .9rem">{{ $application->doc_b_type ?? '—' }}</td>
                            <td style="padding:.65rem .9rem">{{ $application->doc_b_date ?? '—' }}</td>
                            <td style="padding:.65rem .9rem;font-family:monospace">{{ $application->doc_b_ref ?? '—' }}</td>
                            <td style="padding:.65rem .9rem">
                                @if($application->doc_b_file)
                                <a href="{{ route('admin.member-applications.download-doc', [$application, 'b']) }}" class="rn-btn rn-btn-ghost rn-btn-sm" style="font-size:11px">⬇ Download</a>
                                @else<span style="color:var(--text-muted);font-size:11px">Not uploaded</span>@endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Section 2B: Criminal Record --}}
    <div class="rn-section">
        <div class="rn-section-head">
            <div class="rn-section-head-left">
                <div class="rn-section-icon" style="color:#fff">🛡</div>
                <div class="rn-section-title">2B. Criminal Record Declaration</div>
            </div>
        </div>
        <div class="rn-card">
            <div class="rn-card-body" style="display:flex;flex-direction:column;gap:.85rem">
                @foreach([
                    ['1', 'Have you ever been convicted or found guilty by a Court of any offence in any country?', $application->criminal_1, $application->criminal_1_detail],
                    ['2', 'Have you ever been convicted by a Court Martial or sentenced to detention whilst serving in the Armed Forces?', $application->criminal_2, $application->criminal_2_detail],
                    ['3', 'Do you know of any other matter that might cause your reliability or suitability to have access to government assets to be called into question?', $application->criminal_3, $application->criminal_3_detail],
                ] as [$num, $q, $yn, $detail])
                <div>
                    <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.35rem">
                        <span style="font-size:10px;font-weight:bold;color:var(--text-muted);min-width:16px;padding-top:2px">{{ $num }}.</span>
                        <span style="font-size:12px;color:var(--text-muted);flex:1">{{ $q }}</span>
                        <span class="{{ ($yn === 'yes') ? 'yn-yes' : 'yn-no' }}">{{ strtoupper($yn ?? 'no') }}</span>
                    </div>
                    @if($yn === 'yes' && $detail)
                    <div style="margin-left:1.5rem;padding:.55rem .85rem;background:var(--red-faint);border-left:3px solid var(--red);font-size:12px;color:var(--text)">
                        {{ $detail }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Section 4: Communications + Signature side by side --}}
    <div class="ma-field-grid" style="margin-bottom:2rem">

        {{-- Communications --}}
        <div class="rn-section" style="margin-bottom:0">
            <div class="rn-section-head">
                <div class="rn-section-head-left">
                    <div class="rn-section-icon" style="color:#fff">📡</div>
                    <div class="rn-section-title">4. Communications Preferences</div>
                </div>
            </div>
            <div class="rn-card">
                <div class="rn-card-body" style="padding:0">
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead>
                            <tr style="background:var(--grey)">
                                <th style="padding:.45rem .9rem;text-align:left;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Channel</th>
                                <th style="padding:.45rem .9rem;text-align:center;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">National</th>
                                <th style="padding:.45rem .9rem;text-align:center;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);border-bottom:1px solid var(--grey-mid)">Group</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['Email',     'comms_national_email', 'comms_group_email'],
                                ['Telephone', 'comms_national_tel',   'comms_group_tel'],
                                ['SMS',       'comms_national_sms',   'comms_group_sms'],
                                ['Post',      'comms_national_post',  'comms_group_post'],
                            ] as [$ch, $nat, $grp])
                            <tr>
                                <td style="padding:.55rem .9rem;border-bottom:1px solid var(--grey-mid);font-weight:bold">{{ $ch }}</td>
                                <td style="padding:.55rem .9rem;border-bottom:1px solid var(--grey-mid);text-align:center">{!! $application->$nat ? '<span style="color:var(--green);font-weight:bold;font-size:14px">✓</span>' : '<span style="color:var(--grey-dark)">—</span>' !!}</td>
                                <td style="padding:.55rem .9rem;border-bottom:1px solid var(--grey-mid);text-align:center">{!! $application->$grp ? '<span style="color:var(--green);font-weight:bold;font-size:14px">✓</span>' : '<span style="color:var(--grey-dark)">—</span>' !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Signature --}}
        <div class="rn-section" style="margin-bottom:0">
            <div class="rn-section-head">
                <div class="rn-section-head-left">
                    <div class="rn-section-icon" style="color:#fff">✍</div>
                    <div class="rn-section-title">5. Declaration &amp; Signature</div>
                </div>
            </div>
            <div class="rn-card" style="height:calc(100% - 43px)">
                <div class="rn-card-body" style="display:flex;flex-direction:column;align-items:flex-start;gap:.75rem">
                    @if($application->signature_data)
                    <div class="ma-sig-box">
                        <img src="{{ $application->signature_data }}" style="max-height:70px;max-width:340px;display:block" alt="Signature">
                    </div>
                    <div class="stat-pill sp-green">✓ Digital signature captured</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $application->created_at->format('d M Y \a\t H:i') }}</div>
                    @else
                    <div class="rn-notice rn-notice--warn" style="margin:0;font-size:12px">
                        ⚠ No digital signature — handwritten signature required on paper form.
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Delete --}}
    <div style="padding-top:1rem;border-top:1px solid var(--grey-mid)">
        <form method="POST" action="{{ route('admin.member-applications.destroy', $application) }}"
              onsubmit="return confirm('Permanently delete this application? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="rn-btn rn-btn-danger rn-btn-sm">🗑 Delete Application</button>
        </form>
    </div>

</div>
@endsection
