@extends('layouts.admin')
@section('title', 'New Temporary Guest')
@section('content')
<style>
:root{--navy:#003366;--navy2:#00234a;--red:#C8102E;--white:#ffffff;--grey:#f4f5f7;--border:#e1e5ec;--text:#1a2332;--muted:#6b7a90;--green:#1a6b3c;}
.tg-page{max-width:700px;margin:0 auto;padding:28px clamp(16px,3vw,32px) 64px;}
.tg-back{display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:12px;}
.tg-back:hover{color:var(--navy);}
.tg-title{font-size:22px;font-weight:bold;color:var(--navy);}
.tg-subtitle{font-size:13px;color:var(--muted);margin-top:3px;}
.tg-card{background:var(--white);border:1px solid var(--border);overflow:hidden;margin-top:20px;}
.tg-card-head{background:var(--navy2);padding:12px 18px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.6);}
.tg-card-body{padding:24px;}
.tg-card-foot{padding:14px 24px;border-top:1px solid var(--border);background:var(--grey);display:flex;gap:8px;align-items:center;justify-content:flex-end;}
.tg-row{margin-bottom:18px;}
.tg-label{display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:5px;}
.tg-label-req::after{content:' *';color:var(--red);}
.tg-input{width:100%;border:1px solid var(--border);padding:9px 11px;font-size:13px;font-family:inherit;color:var(--text);outline:none;transition:border-color .15s;background:var(--white);box-sizing:border-box;}
.tg-input:focus{border-color:var(--navy);}
.tg-input.error{border-color:var(--red);}
.tg-hint{font-size:11px;color:var(--muted);margin-top:4px;line-height:1.5;}
.tg-error{font-size:11px;color:var(--red);margin-top:4px;}
.tg-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:520px){.tg-grid-2{grid-template-columns:1fr;}}
.tg-toggle-row{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.tg-toggle-label{font-size:13px;font-weight:600;color:var(--text);}
.tg-toggle{position:relative;width:40px;height:22px;flex-shrink:0;}
.tg-toggle input{opacity:0;width:0;height:0;}
.tg-toggle-slider{position:absolute;inset:0;background:var(--border);border-radius:22px;cursor:pointer;transition:background .2s;}
.tg-toggle-slider::before{content:'';position:absolute;width:16px;height:16px;border-radius:50%;background:white;left:3px;top:3px;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.tg-toggle input:checked+.tg-toggle-slider{background:var(--navy);}
.tg-toggle input:checked+.tg-toggle-slider::before{transform:translateX(18px);}
.tg-expiry-section{display:none;border:1px solid var(--border);padding:16px;background:var(--grey);margin-bottom:18px;}
.tg-expiry-section.visible{display:block;}
.tg-password-section{display:none;border:1px solid var(--border);padding:16px 16px 2px;background:#fffbf0;border-color:#f0c040;margin-bottom:18px;}
.tg-password-section.visible{display:block;}
.tg-presets{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;}
.tg-preset{font-size:11px;font-weight:bold;padding:4px 11px;border:1px solid var(--border);background:var(--white);cursor:pointer;color:var(--text);font-family:inherit;transition:all .12s;}
.tg-preset:hover{border-color:var(--navy);color:var(--navy);}
.tg-check-row{display:flex;align-items:flex-start;gap:10px;}
.tg-check-row input[type=checkbox]{width:16px;height:16px;margin-top:2px;flex-shrink:0;accent-color:var(--navy);}
.tg-check-label{font-size:13px;color:var(--text);line-height:1.5;}
.tg-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;text-decoration:none;border:none;cursor:pointer;transition:all .15s;font-family:inherit;}
.tg-btn-primary{background:var(--red);color:#fff;}
.tg-btn-primary:hover{background:#a50f26;color:#fff;}
.tg-btn-ghost{background:var(--grey);color:var(--text);border:1px solid var(--border);}
.tg-btn-ghost:hover{border-color:var(--navy);color:var(--navy);}
.tg-info{padding:12px 16px;background:rgba(0,51,102,.05);border:1px solid rgba(0,51,102,.15);border-left:3px solid var(--navy);font-size:12px;color:var(--text);line-height:1.65;margin-bottom:20px;}
.tg-info strong{color:var(--navy);}
</style>
<div class="tg-page">
    <a href="{{ route('admin.temporary-guests.index') }}" class="tg-back">← Back to guests</a>
    <div class="tg-title">New Temporary Guest</div>
    <div class="tg-subtitle">Create a time-limited account with read-only member access</div>

    <div class="tg-info">
        <strong>What can a temporary guest do?</strong><br>
        They can log in and see everything a regular member sees — hub, calendar, events, training, ops map, and resources.
        They <em>cannot</em> see personal details of other members and have no access to the committee area.
    </div>

    <div class="tg-card">
        <div class="tg-card-head">Guest Details</div>
        <form method="POST" action="{{ route('admin.temporary-guests.store') }}">
        @csrf
        <div class="tg-card-body">
            <div class="tg-grid-2">
                <div class="tg-row">
                    <label class="tg-label tg-label-req">Full Name</label>
                    <input type="text" name="name" class="tg-input {{ $errors->has('name') ? 'error' : '' }}"
                           value="{{ old('name') }}" placeholder="e.g. Jane Smith" required>
                    @error('name')<div class="tg-error">{{ $message }}</div>@enderror
                </div>
                <div class="tg-row">
                    <label class="tg-label">Callsign (optional)</label>
                    <input type="text" name="callsign" class="tg-input {{ $errors->has('callsign') ? 'error' : '' }}"
                           value="{{ old('callsign') }}" placeholder="e.g. M0ABC" style="text-transform:uppercase;">
                    @error('callsign')<div class="tg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="tg-row">
                <label class="tg-label tg-label-req">Email Address</label>
                <input type="email" name="email" class="tg-input {{ $errors->has('email') ? 'error' : '' }}"
                       value="{{ old('email') }}" placeholder="guest@example.com" required>
                <div class="tg-hint">The guest will use this address to log in.</div>
                @error('email')<div class="tg-error">{{ $message }}</div>@enderror
            </div>

            {{-- Access Level --}}
            <input type="hidden" name="access_level" id="access_level_input" value="{{ old("access_level","guest") }}">
            <div class="tg-row">
                <label class="tg-label tg-label-req">Access Level</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div id="card_guest" onclick="setAccessLevel('guest')" style="cursor:pointer;border:2px solid var(--navy);background:var(--navy-faint);padding:14px;text-align:center;transition:all .15s;">
                        <div style="font-size:20px;margin-bottom:6px;">👤</div>
                        <div style="font-size:12px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;">Temporary Member</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:4px;line-height:1.5;">Can view the members area. Cannot see personal details of other members.</div>
                    </div>
                    <div id="card_admin" onclick="setAccessLevel('admin')" style="cursor:pointer;border:2px solid var(--border);background:var(--grey);padding:14px;text-align:center;transition:all .15s;">
                        <div style="font-size:20px;margin-bottom:6px;">🔑</div>
                        <div style="font-size:12px;font-weight:bold;color:var(--text);text-transform:uppercase;letter-spacing:.05em;">Temporary Admin</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:4px;line-height:1.5;">Can access the admin panel but only sees other temporary accounts. GDPR compliant.</div>
                    </div>
                </div>
                <div id="admin_warning" style="display:none;margin-top:8px;padding:10px 14px;background:rgba(180,83,9,.08);border:1px solid rgba(180,83,9,.3);border-left:3px solid #b45309;font-size:12px;color:#92400e;line-height:1.6;">
                    ⚠ Temporary admins can log into the admin panel but will <strong>only</strong> see other temporary guest and admin accounts — never real member data. All other admin sections are blocked.
                </div>
            </div>

            {{-- Expiry toggle --}}
            <div class="tg-toggle-row">
                <label class="tg-toggle">
                    <input type="checkbox" id="expiryToggle" {{ old('expires_at') ? 'checked' : '' }}
                           onchange="document.getElementById('expirySection').classList.toggle('visible',this.checked)">
                    <span class="tg-toggle-slider"></span>
                </label>
                <span class="tg-toggle-label">Set an automatic expiry date &amp; time</span>
            </div>
            <div class="tg-expiry-section {{ old('expires_at') ? 'visible' : '' }}" id="expirySection">
                <div class="tg-label" style="margin-bottom:8px;">Quick Presets</div>
                <div class="tg-presets">
                    <button type="button" class="tg-preset" onclick="setExpiry(1,'day')">1 Day</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(3,'day')">3 Days</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(1,'week')">1 Week</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(2,'week')">2 Weeks</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(1,'month')">1 Month</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(3,'month')">3 Months</button>
                    <button type="button" class="tg-preset" onclick="setExpiry(6,'month')">6 Months</button>
                </div>
                <label class="tg-label">Exact Date &amp; Time</label>
                <input type="datetime-local" name="expires_at" id="expiryInput"
                       class="tg-input {{ $errors->has('expires_at') ? 'error' : '' }}"
                       value="{{ old('expires_at') }}">
                <div class="tg-hint">Account is automatically disabled at this time. The scheduler runs hourly.</div>
                @error('expires_at')<div class="tg-error">{{ $message }}</div>@enderror
            </div>

            {{-- Welcome email toggle --}}
            <div class="tg-toggle-row">
                <label class="tg-toggle">
                    <input type="checkbox" id="welcomeToggle" name="send_welcome" value="1"
                           {{ old('send_welcome', '1') ? 'checked' : '' }}
                           onchange="togglePasswordSection(this.checked)">
                    <span class="tg-toggle-slider"></span>
                </label>
                <span class="tg-toggle-label">Send welcome email with password-set link</span>
            </div>
            <div class="tg-hint" style="margin-top:-10px;margin-bottom:14px;">
                The guest receives a branded email explaining their temporary access, with a link to set their own password.
            </div>

            {{-- Manual password section (shown when welcome email is OFF) --}}
            <div class="tg-password-section" id="passwordSection">
                <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#856404;margin-bottom:12px;">
                    ⚠ No email will be sent — set a password to give to the guest manually
                </div>
                <div class="tg-grid-2">
                    <div class="tg-row">
                        <label class="tg-label tg-label-req">Password</label>
                        <input type="password" name="manual_password"
                               class="tg-input {{ $errors->has('manual_password') ? 'error' : '' }}"
                               placeholder="Min 8 characters" autocomplete="new-password">
                        @error('manual_password')<div class="tg-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="tg-row">
                        <label class="tg-label tg-label-req">Confirm Password</label>
                        <input type="password" name="manual_password_confirmation"
                               class="tg-input" placeholder="Repeat password" autocomplete="new-password">
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="tg-row">
                <label class="tg-label">Internal Notes (admin only)</label>
                <textarea name="notes" class="tg-input" rows="3"
                          placeholder="Why was this guest account created? Who approved it?">{{ old('notes') }}</textarea>
                <div class="tg-hint">Never shown to the guest.</div>
            </div>
        </div>
        <div class="tg-card-foot">
            <a href="{{ route('admin.temporary-guests.index') }}" class="tg-btn tg-btn-ghost">Cancel</a>
            <button type="submit" class="tg-btn tg-btn-primary">Create Guest Account</button>
        </div>
        </form>
    </div>
</div>
<script>
function setAccessLevel(level){
    document.getElementById("access_level_input").value = level;
    var isAdmin = level === "admin";
    document.getElementById("card_guest").style.border = isAdmin ? "2px solid var(--border)" : "2px solid var(--navy)";
    document.getElementById("card_guest").style.background = isAdmin ? "var(--grey)" : "var(--navy-faint)";
    document.getElementById("card_admin").style.border = isAdmin ? "2px solid #b45309" : "2px solid var(--border)";
    document.getElementById("card_admin").style.background = isAdmin ? "rgba(180,83,9,.08)" : "var(--grey)";
    document.getElementById("admin_warning").style.display = isAdmin ? "block" : "none";
}
// Init from old value
setAccessLevel(document.getElementById("access_level_input").value || "guest");
function setExpiry(amount,unit){
    var d=new Date();
    if(unit==='day') d.setDate(d.getDate()+amount);
    if(unit==='week') d.setDate(d.getDate()+amount*7);
    if(unit==='month') d.setMonth(d.getMonth()+amount);
    var pad=n=>String(n).padStart(2,'0');
    document.getElementById('expiryInput').value=d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
}
function togglePasswordSection(emailOn){
    document.getElementById('passwordSection').classList.toggle('visible', !emailOn);
}
// Set initial state
togglePasswordSection(document.getElementById('welcomeToggle').checked);
</script>
@endsection
