@extends('layouts.app')
@section('title', 'Set Up Your Account')
@section('content')

<style>
:root{--navy:#003366;--navy-mid:#004080;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--green:#1a6b3c;--green-bg:#eef7f2;--text:#001f40;--muted:#6b7f96;--font:Arial,"Helvetica Neue",Helvetica,sans-serif}
*,*::before,*::after{box-sizing:border-box}
.ai-page{display:flex;min-height:calc(100vh - 60px);flex-direction:column}
@media(min-width:820px){.ai-page{flex-direction:row}}
.ai-left{background:var(--navy);padding:2.5rem 2rem;display:flex;flex-direction:column;position:relative;overflow:hidden;flex-shrink:0}
.ai-left::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 20px,rgba(255,255,255,.018) 20px,rgba(255,255,255,.018) 21px);pointer-events:none}
.ai-left::after{content:'';position:absolute;bottom:-20%;right:-15%;width:60%;padding-top:60%;border-radius:50%;background:radial-gradient(circle,rgba(200,16,46,.14) 0%,transparent 65%);pointer-events:none}
@media(min-width:820px){.ai-left{width:40%;padding:3rem 3rem 3rem 2.5rem}}
.ai-left-inner{position:relative;z-index:1;display:flex;flex-direction:column;height:100%}
.ai-brand{display:flex;align-items:center;gap:.85rem;margin-bottom:3rem}
.ai-rn-logo{background:var(--red);width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ai-rn-logo span{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.15;text-transform:uppercase}
.ai-brand-name{font-size:15px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase}
.ai-brand-sub{font-size:11px;color:rgba(255,255,255,.4);margin-top:2px;text-transform:uppercase;letter-spacing:.06em}
.ai-eyebrow{font-size:.68rem;font-weight:bold;text-transform:uppercase;letter-spacing:.16em;color:rgba(255,255,255,.35);margin-bottom:.6rem}
.ai-title{font-size:clamp(1.6rem,3.5vw,2.2rem);font-weight:bold;color:#fff;line-height:1.15;margin-bottom:.85rem}
.ai-title span{color:#90caf9}
.ai-desc{font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:2rem}
.ai-check-list{list-style:none;display:flex;flex-direction:column;gap:.55rem;margin-bottom:2.5rem;padding:0}
.ai-check-list li{display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:rgba(255,255,255,.55)}
.ai-check-list li::before{content:'';width:18px;height:18px;background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.3);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;color:#4ade80;flex-shrink:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2 6l3 3 5-5' stroke='%234ade80' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:center}
.ai-approved-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.25);border-left:3px solid #4ade80;font-size:.78rem;color:rgba(255,255,255,.65)}
.ai-approved-badge strong{color:#4ade80}
.ai-footer{margin-top:auto;padding-top:2rem;font-size:.7rem;color:rgba(255,255,255,.2);line-height:1.6}
.ai-right{background:var(--white);flex:1;display:flex;flex-direction:column;border-left:4px solid var(--red)}
.ai-right-head{padding:2rem 2.5rem 1.5rem;border-bottom:1px solid var(--grey-mid);background:var(--grey)}
@media(min-width:820px){.ai-right-head{padding:2.5rem 3.5rem 1.5rem}}
.ai-step-label{font-size:.68rem;font-weight:bold;text-transform:uppercase;letter-spacing:.16em;color:var(--red);margin-bottom:.35rem}
.ai-right-title{font-size:1.3rem;font-weight:bold;color:var(--navy)}
.ai-right-sub{font-size:.82rem;color:var(--muted);margin-top:.25rem}
.ai-right-body{padding:2rem 2.5rem;flex:1}
@media(min-width:820px){.ai-right-body{padding:2.5rem 3.5rem}}
.ai-prefill-box{background:var(--grey);border:1px solid var(--grey-mid);border-left:3px solid var(--navy);padding:.75rem 1rem;margin-bottom:1.75rem}
.ai-prefill-row{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--muted);margin-bottom:.3rem}
.ai-prefill-row:last-child{margin-bottom:0}
.ai-prefill-label{font-size:.7rem;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);min-width:55px}
.ai-prefill-val{font-weight:bold;color:var(--text)}
.ai-field{margin-bottom:1.25rem}
.ai-label{display:block;font-size:.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.4rem}
.ai-input{width:100%;border:1px solid var(--grey-mid);background:#fff;padding:.6rem .85rem;font-family:var(--font);font-size:.9rem;color:var(--text);outline:none;transition:border-color .15s,box-shadow .15s}
.ai-input:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,51,102,.08)}
.ai-input.is-invalid{border-color:var(--red)}
.ai-input-hint{font-size:.72rem;color:var(--muted);margin-top:.3rem}
.ai-invalid-msg{font-size:.75rem;color:var(--red);margin-top:.3rem;font-weight:bold}
.ai-divider{height:1px;background:var(--grey-mid);margin:1.5rem 0}
.ai-submit{width:100%;padding:.75rem 1.5rem;background:var(--navy);border:none;color:#fff;font-family:var(--font);font-size:.88rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;cursor:pointer;transition:background .12s;margin-top:.5rem}
.ai-submit:hover{background:var(--navy-mid)}
.ai-error-box{background:#fdf0f2;border:1px solid rgba(200,16,46,.25);border-left:3px solid var(--red);padding:.75rem 1rem;margin-bottom:1.5rem}
.ai-error-box p{font-size:.82rem;color:var(--red);font-weight:bold;margin-bottom:.25rem}
.ai-error-box ul{margin:0;padding-left:1.1rem;font-size:.82rem;color:var(--red)}
</style>

<div class="ai-page">

    <div class="ai-left">
        <div class="ai-left-inner">
            <div class="ai-brand">
                <div class="ai-rn-logo"><span>RAY<br>NET</span></div>
                <div>
                    <div class="ai-brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                    <div class="ai-brand-sub">Member Portal</div>
                </div>
            </div>
            <div class="ai-eyebrow">Application Approved</div>
            <div class="ai-title">Welcome to <span>{{ \App\Helpers\RaynetSetting::groupName() }}</span></div>
            <div class="ai-desc">
                Your REG-02 application has been reviewed and approved by the Group Controller.
                Complete this short form to activate your member account.
            </div>
            <ul class="ai-check-list">
                <li>Access the members-only portal</li>
                <li>View events, net logs &amp; callout alerts</li>
                <li>Track your training &amp; qualifications</li>
                <li>Update your availability &amp; callout status</li>
            </ul>
            <div class="ai-approved-badge">
                &#x2713; Approved &nbsp;&bull;&nbsp; <strong>{{ $application->created_at->format('d M Y') }}</strong>
            </div>
            <div class="ai-footer">
                {{ \App\Helpers\RaynetSetting::groupName() }} &bull; RAYNET-UK &bull; Member Portal
            </div>
        </div>
    </div>

    <div class="ai-right">
        <div class="ai-right-head">
            <div class="ai-step-label">Account Setup</div>
            <div class="ai-right-title">Set up your account, {{ $application->forenames }}</div>
            <div class="ai-right-sub">Choose your callsign and a secure password to complete your registration.</div>
        </div>
        <div class="ai-right-body">

            @if($errors->any())
            <div class="ai-error-box">
                <p>&#x26A0; Please correct the following:</p>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="ai-prefill-box">
                <div class="ai-prefill-row">
                    <span class="ai-prefill-label">Name</span>
                    <span class="ai-prefill-val">{{ $application->forenames }} {{ strtoupper($application->surname) }}</span>
                </div>
                <div class="ai-prefill-row">
                    <span class="ai-prefill-label">Email</span>
                    <span class="ai-prefill-val">{{ $application->email }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('member-application.accept-invite.submit', $token) }}">
                @csrf

                <div class="ai-field">
                    <label for="callsign" class="ai-label">Callsign <span style="color:var(--red)">*</span></label>
                    <input type="text" id="callsign" name="callsign"
                           class="ai-input{{ $errors->has('callsign') ? ' is-invalid' : '' }}"
                           value="{{ old('callsign', $application->callsign) }}"
                           placeholder="e.g. M7ABC" maxlength="20" autocomplete="username" required>
                    <div class="ai-input-hint">Your amateur radio callsign. Displayed on your profile and ID card.</div>
                    @error('callsign')<div class="ai-invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="ai-divider"></div>

                <div class="ai-field">
                    <label for="password" class="ai-label">Password <span style="color:var(--red)">*</span></label>
                    <input type="password" id="password" name="password"
                           class="ai-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           minlength="8" autocomplete="new-password" required>
                    <div class="ai-input-hint">Minimum 8 characters.</div>
                    @error('password')<div class="ai-invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="ai-field">
                    <label for="password_confirmation" class="ai-label">Confirm Password <span style="color:var(--red)">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="ai-input" minlength="8" autocomplete="new-password" required>
                </div>

                <button type="submit" class="ai-submit">&#x2713; &nbsp;Create My Account</button>

                <p style="font-size:.72rem;color:var(--muted);margin-top:1rem;text-align:center;line-height:1.6">
                    By creating an account you agree to abide by the rules of {{ \App\Helpers\RaynetSetting::groupName() }} and RAYNET-UK.
                    This link expires 7 days after approval.
                </p>
            </form>
        </div>
    </div>

</div>

<script>
document.getElementById('callsign').addEventListener('input', function() {
    var pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
@endsection
