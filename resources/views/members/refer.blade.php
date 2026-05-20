@extends('layouts.app')
@section('title', 'Invite a Radio Amateur')
@section('content')

<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;--font:Arial,"Helvetica Neue",Helvetica,sans-serif;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.ref-wrap{max-width:780px;margin:0 auto;padding:2rem 1rem 4rem;}
.ref-head{margin-bottom:2rem;}
.ref-eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.5rem;}
.ref-title{font-size:1.8rem;font-weight:bold;color:var(--navy);margin-bottom:.5rem;}
.ref-desc{font-size:.95rem;color:var(--muted);line-height:1.6;}
.ref-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:1.8rem;box-shadow:0 2px 8px rgba(0,51,102,.06);margin-bottom:1.5rem;}
.ref-card-title{font-size:1rem;font-weight:bold;color:var(--navy);margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem;}
.ref-field{margin-bottom:1.2rem;}
.ref-label{display:block;font-size:.8rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:.4rem;}
.ref-input{width:100%;padding:.7rem 1rem;border:1px solid var(--grey-mid);border-radius:6px;font-size:.95rem;color:var(--text);font-family:var(--font);transition:border-color .15s;}
.ref-input:focus{outline:none;border-color:var(--navy);}
.ref-input-group{display:flex;gap:.6rem;}
.ref-input-group .ref-input{flex:1;}
.ref-btn{padding:.7rem 1.4rem;border-radius:999px;font-size:.9rem;font-weight:bold;border:none;cursor:pointer;transition:all .2s;}
.ref-btn-lookup{background:var(--navy);color:#fff;}
.ref-btn-lookup:hover{background:#004080;}
.ref-btn-send{background:var(--red);color:#fff;padding:.8rem 2rem;}
.ref-btn-send:hover{background:#a00d25;}
.ref-qrz-result{background:var(--grey);border:1px solid var(--grey-mid);border-radius:8px;padding:1rem 1.2rem;margin-top:.8rem;display:none;}
.ref-qrz-result.show{display:flex;gap:1rem;align-items:center;}
.ref-qrz-avatar{width:52px;height:52px;border-radius:6px;object-fit:cover;flex-shrink:0;}
.ref-qrz-name{font-size:1rem;font-weight:bold;color:var(--navy);}
.ref-qrz-meta{font-size:.82rem;color:var(--muted);}
.ref-qrz-none{background:#fff8e1;border:1px solid #fcd34d;border-radius:8px;padding:.75rem 1rem;margin-top:.8rem;font-size:.85rem;color:#92400e;display:none;}
.ref-qrz-none.show{display:block;}
.ref-alert-success{background:#eef7f2;border:1px solid #b8ddc9;border-left:3px solid #1a6b3c;padding:.8rem 1rem;border-radius:6px;font-size:.9rem;color:#1a6b3c;font-weight:bold;margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem;}
.ref-alert-error{background:#fdf0f2;border:1px solid rgba(200,16,46,.25);border-left:3px solid #C8102E;padding:.8rem 1rem;border-radius:6px;font-size:.9rem;color:#C8102E;margin-bottom:1.5rem;}
.ref-hint{font-size:.78rem;color:var(--muted);margin-top:.35rem;}
.ref-divider{border:none;border-top:1px solid var(--grey-mid);margin:1.5rem 0;}
</style>

<div class="ref-wrap">

    <div class="ref-head">
        <div class="ref-eyebrow">Members Area</div>
        <h1 class="ref-title">📡 Invite Someone to Join RAYNET</h1>
        <p class="ref-desc">Know someone who would be a great fit for {{ $groupName }}? Whether they hold an amateur radio licence or simply want to get involved and support us, look up their details and send them a personalised invitation to join.</p>
    </div>

    @if(session('refer_success'))
        <div class="ref-alert-success">✓ {{ session('refer_success') }}</div>
    @endif

    @if($errors->any())
        <div class="ref-alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('members.refer.send') }}" id="referForm">
        @csrf

        {{-- Step 1: Callsign lookup --}}
        <div id="callsignSection">
        <div class="ref-card">
            <div class="ref-card-title">1️⃣ Look up their callsign</div>

            <div class="ref-field">
                <label class="ref-label" for="callsign_input">Callsign</label>
                <div class="ref-input-group">
                    <input type="text" id="callsign_input" placeholder="e.g. M0ABC"
                           class="ref-input" style="text-transform:uppercase;font-family:monospace;font-size:1rem;"
                           value="{{ old('callsign') }}">
                    <button type="button" class="ref-btn ref-btn-lookup" onclick="doLookup()">🔍 Look up</button>
                </div>
                <input type="hidden" name="callsign" id="callsign_hidden" value="{{ old('callsign') }}">
                <div class="ref-hint">Enter their amateur radio callsign to look up their details via QRZ.</div>
            </div>

            {{-- QRZ result --}}
            <div class="ref-qrz-result" id="qrzResult">
                <img id="qrzAvatar" src="" alt="" class="ref-qrz-avatar" style="display:none;">
                <div>
                    <div class="ref-qrz-name" id="qrzName"></div>
                    <div class="ref-qrz-meta" id="qrzMeta"></div>
                </div>
            </div>
            <div class="ref-qrz-none" id="qrzNone">
                ⚠ No QRZ data found for this callsign. You can still send an invite — just enter their details below.
            </div>
        </div>

        </div>{{-- end callsignSection --}}
        {{-- Step 1b: Operator toggle --}}
        <div class="ref-card" id="typeCard">
            <div class="ref-card-title">1️⃣b Are they a licensed radio operator?</div>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;padding:.6rem 1.2rem;border:2px solid var(--grey-mid);border-radius:8px;flex:1;min-width:180px;transition:all .2s;" id="labelYes">
                    <input type="radio" name="is_operator" value="1" id="opYes" style="accent-color:var(--navy);width:16px;height:16px;"
                        {{ old('is_operator', '1') == '1' ? 'checked' : '' }}>
                    <div>
                        <div style="font-weight:bold;font-size:.9rem;color:var(--navy);">📻 Licensed operator</div>
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.1rem;">They hold an amateur radio licence</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;padding:.6rem 1.2rem;border:2px solid var(--grey-mid);border-radius:8px;flex:1;min-width:180px;transition:all .2s;" id="labelNo">
                    <input type="radio" name="is_operator" value="0" id="opNo" style="accent-color:var(--navy);width:16px;height:16px;"
                        {{ old('is_operator') == '0' ? 'checked' : '' }}>
                    <div>
                        <div style="font-weight:bold;font-size:.9rem;color:var(--navy);">🤝 Support / interested</div>
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.1rem;">No licence — support staff or keen to learn</div>
                    </div>
                </label>
            </div>
            <div class="ref-hint" style="margin-top:.8rem;">This determines the wording of the invitation email sent to them.</div>
        </div>

        {{-- Step 2: Details --}}
        <div class="ref-card">
            <div class="ref-card-title">2️⃣ Confirm their details</div>

            <div class="ref-field">
                <label class="ref-label" for="name_input">Their Name</label>
                <input type="text" name="name" id="name_input" class="ref-input"
                       placeholder="Full name (optional — pulled from QRZ if available)"
                       value="{{ old('name') }}">
            </div>

            <div class="ref-field">
                <label class="ref-label" for="email_input">Their Email Address <span style="color:var(--red);">*</span></label>
                <input type="email" name="email" id="email_input" class="ref-input"
                       placeholder="their@email.com" value="{{ old('email') }}" required>
                <div class="ref-hint">Required to send the invite. QRZ may provide this automatically.</div>
            </div>
        </div>

        {{-- Step 3: Send --}}
        <div class="ref-card" style="text-align:center;background:var(--navy);">
            <p style="color:rgba(255,255,255,.7);font-size:.9rem;margin-bottom:1rem;">
                A personalised invitation email will be sent on your behalf to join {{ $groupName }}.
            </p>
            <button type="submit" class="ref-btn ref-btn-send">
                📨 Send Invitation
            </button>
        </div>

    </form>

</div>

<script>
async function doLookup() {
    const input    = document.getElementById('callsign_input');
    const callsign = input.value.trim().toUpperCase();
    if (!callsign) return;

    input.value = callsign;
    document.getElementById('callsign_hidden').value = callsign;

    const btn = document.querySelector('.ref-btn-lookup');
    btn.textContent = '⏳ Looking up...';
    btn.disabled = true;

    document.getElementById('qrzResult').classList.remove('show');
    document.getElementById('qrzNone').classList.remove('show');

    try {
        const res  = await fetch(`/members/refer/lookup/${encodeURIComponent(callsign)}`);
        const data = await res.json();

        if (data && data.callsign) {
            document.getElementById('qrzName').textContent = data.name || data.callsign;

            let meta = [];
            if (data.class)   meta.push(data.class + ' licence');
            if (data.country) meta.push(data.country);
            if (data.grid)    meta.push('Grid: ' + data.grid);
            document.getElementById('qrzMeta').textContent = meta.join(' · ');

            if (data.image) {
                const img = document.getElementById('qrzAvatar');
                img.src   = data.image;
                img.style.display = 'block';
            }

            if (data.email) {
                document.getElementById('email_input').value = data.email;
            }
            if (data.name) {
                document.getElementById('name_input').value = data.name;
            }

            document.getElementById('qrzResult').classList.add('show');
        } else {
            document.getElementById('qrzNone').classList.add('show');
        }
    } catch (e) {
        document.getElementById('qrzNone').classList.add('show');
    }

    btn.textContent = '🔍 Look up';
    btn.disabled = false;
}

// Operator toggle styling
document.querySelectorAll("input[name=is_operator]").forEach(function(r) {
    r.addEventListener("change", function() {
        var isOp = document.getElementById("opYes").checked;
        document.getElementById("labelYes").style.borderColor = isOp ? "var(--navy)" : "var(--grey-mid)";
        document.getElementById("labelNo").style.borderColor  = isOp ? "var(--grey-mid)" : "var(--navy)";
        document.getElementById("callsignSection").style.display = isOp ? "block" : "none";
    });
});
// Init
(function(){
    var isOp = document.getElementById("opYes").checked;
    document.getElementById("labelYes").style.borderColor = isOp ? "var(--navy)" : "var(--grey-mid)";
    document.getElementById("labelNo").style.borderColor  = isOp ? "var(--grey-mid)" : "var(--navy)";
    document.getElementById("callsignSection").style.display = isOp ? "block" : "none";
})();

// Allow Enter key in callsign field
document.getElementById('callsign_input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); doLookup(); }
});
</script>

@endsection
