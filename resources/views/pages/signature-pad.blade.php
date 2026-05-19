<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>Sign — RAYNET Application</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1rem}
.card{background:#fff;border:1px solid #dce4ee;border-top:4px solid #C8102E;width:100%;max-width:480px;overflow:hidden}
.card-head{background:#001f40;padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem}
.card-head img{width:40px}
.card-title{color:#fff;font-size:1rem;font-weight:700}
.card-sub{color:rgba(255,255,255,.6);font-size:.75rem;margin-top:.15rem}
.card-body{padding:1.25rem}
.instruction{font-size:.85rem;color:#2d4a6b;margin-bottom:1rem;line-height:1.5;background:#f0f5ff;border:1px solid rgba(0,51,102,.2);border-left:3px solid #003366;padding:.65rem .9rem}
.canvas-wrap{border:2px solid #dce4ee;background:#fff;position:relative;touch-action:none}
canvas{display:block;width:100%;height:200px}
.canvas-label{position:absolute;bottom:8px;left:50%;transform:translateX(-50%);font-size:.7rem;color:#9aa3ae;pointer-events:none;white-space:nowrap}
.canvas-line{position:absolute;bottom:44px;left:12px;right:12px;height:1px;background:#dce4ee;pointer-events:none}
.btn-row{display:flex;gap:.5rem;margin-top:.85rem}
.btn{flex:1;padding:.7rem;border:none;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;transition:all .15s}
.btn-clear{background:#f0f4f8;color:#6b7f96;border:1px solid #dce4ee}
.btn-clear:hover{background:#dce4ee}
.btn-submit{background:#001f40;color:#fff}
.btn-submit:hover{background:#003366}
.btn-submit:disabled{background:#9aa3ae;cursor:not-allowed}
.success-msg{display:none;text-align:center;padding:2rem 1rem}
.success-icon{font-size:3rem;margin-bottom:.75rem}
.success-title{font-size:1.1rem;font-weight:700;color:#1a7a3c;margin-bottom:.35rem}
.success-text{font-size:.85rem;color:#6b7f96}
.expired-msg{text-align:center;padding:2rem 1rem}
.err-msg{background:#fef2f2;border:1px solid rgba(200,16,46,.3);padding:.6rem .9rem;font-size:.8rem;color:#C8102E;margin-top:.5rem;display:none}
</style>
</head>
<body>
<div class="card">
    <div class="card-head">
        <div>
            <div class="card-title">REG-02 Signature</div>
            <div class="card-sub">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
        </div>
    </div>
    <div class="card-body">

    @if($already)
        <div style="text-align:center;padding:1.5rem 0">
            <div style="font-size:2.5rem;margin-bottom:.5rem">✅</div>
            <div style="font-size:1rem;font-weight:700;color:#1a7a3c;margin-bottom:.25rem">Already Signed</div>
            <div style="font-size:.82rem;color:#6b7f96">Your signature has been received. You can close this page.</div>
        </div>
    @else
        <div id="signContent">
            <div class="instruction">
                📝 Use your finger or stylus to sign in the box below. This signature will appear on your REG-02 application form.
            </div>
            <div class="canvas-wrap">
                <canvas id="sigCanvas"></canvas>
                <div class="canvas-line"></div>
                <div class="canvas-label">Sign above this line</div>
            </div>
            <div class="err-msg" id="errMsg">Please draw your signature before submitting.</div>
            <div class="btn-row">
                <button class="btn btn-clear" onclick="clearSig()">✕ Clear</button>
                <button class="btn btn-submit" id="submitBtn" onclick="submitSig()">✓ Confirm Signature</button>
            </div>
        </div>
        <div class="success-msg" id="successMsg">
            <div class="success-icon">✅</div>
            <div class="success-title">Signature Received</div>
            <div class="success-text">Your signature has been sent to the application form. You can now close this page and return to your computer to complete the submission.</div>
        </div>
    @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const TOKEN  = @json($token);
const canvas = document.getElementById('sigCanvas');
let pad;

if (canvas) {
    // Size canvas to physical pixels
    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect  = canvas.getBoundingClientRect();
        canvas.width  = rect.width  * ratio;
        canvas.height = rect.height * ratio;
        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);
        if (pad) pad.clear();
    }

    pad = new SignaturePad(canvas, {
        minWidth: 1.5,
        maxWidth: 3,
        penColor: '#001f40',
        backgroundColor: 'rgba(255,255,255,0)',
    });

    window.addEventListener('resize', resize);
    resize();
}

function clearSig() {
    if (pad) pad.clear();
    document.getElementById('errMsg').style.display = 'none';
}

async function submitSig() {
    if (!pad || pad.isEmpty()) {
        document.getElementById('errMsg').style.display = 'block';
        return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Sending…';

    const dataUrl = pad.toDataURL('image/png');

    try {
        const resp = await fetch('/member-application/sign/' + TOKEN, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ signature: dataUrl })
        });
        const json = await resp.json();
        if (json.ok) {
            document.getElementById('signContent').style.display = 'none';
            document.getElementById('successMsg').style.display  = 'block';
        } else {
            btn.disabled = false;
            btn.textContent = '✓ Confirm Signature';
            document.getElementById('errMsg').textContent = json.error || 'Failed — please try again.';
            document.getElementById('errMsg').style.display = 'block';
        }
    } catch(e) {
        btn.disabled = false;
        btn.textContent = '✓ Confirm Signature';
        document.getElementById('errMsg').textContent = 'Network error — please try again.';
        document.getElementById('errMsg').style.display = 'block';
    }
}
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
</body>
</html>
