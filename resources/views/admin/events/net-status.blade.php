@extends('layouts.admin')
@section('title', 'Net Status')
@section('content')
<style>
.ns-wrap{max-width:680px;margin:0 auto;padding:2rem 1rem 4rem;}
.ns-card{background:#fff;border:1px solid #dde2e8;border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;}
.ns-title{font-size:1.4rem;font-weight:800;color:#003366;margin-bottom:.25rem;}
.ns-sub{font-size:.88rem;color:#6b7f96;margin-bottom:1.5rem;}
.field{margin-bottom:1rem;}
.label{display:block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#003366;margin-bottom:.4rem;}
.input{width:100%;padding:.55rem .75rem;border:1px solid #dde2e8;border-radius:6px;font-size:.9rem;font-family:inherit;}
.input:focus{outline:none;border-color:#003366;}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;background:#f2f5f9;border-radius:8px;margin-bottom:1rem;}
.toggle-label{font-size:1rem;font-weight:700;color:#003366;}
.toggle-sub{font-size:.82rem;color:#6b7f96;margin-top:.15rem;}
.toggle-switch{position:relative;width:52px;height:28px;flex-shrink:0;}
.toggle-switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;inset:0;background:#dde2e8;border-radius:999px;cursor:pointer;transition:.3s;}
.slider:before{content:'';position:absolute;width:22px;height:22px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.2);}
input:checked + .slider{background:#C8102E;}
input:checked + .slider:before{transform:translateX(24px);}
.btn-save{background:#003366;color:#fff;border:none;padding:.65rem 1.75rem;border-radius:999px;font-size:.95rem;font-weight:700;cursor:pointer;}
.alert-success{background:#d1fae5;border-left:3px solid #059669;padding:.65rem 1rem;border-radius:4px;font-size:.88rem;color:#065f46;font-weight:bold;margin-bottom:1rem;}
.preview-banner{background:linear-gradient(135deg,#C8102E 0%,#a50e24 100%);color:#fff;border-radius:10px;padding:1.25rem 1.5rem;margin-top:1rem;}
.preview-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;opacity:.7;margin-bottom:.4rem;}
.preview-title{font-size:1.1rem;font-weight:800;margin-bottom:.2rem;}
.preview-meta{font-size:.85rem;opacity:.85;}
</style>

<div class="ns-wrap">
    <div class="ns-title">📻 Net Status & Strapline</div>
    <div class="ns-sub">Configure the live net banner shown on the homepage. When active it appears as a prominent strapline above the hero section.</div>

    @if(session('success'))
    <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.events.net-status.update') }}">
        @csrf

        <div class="ns-card">
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Net Active</div>
                    <div class="toggle-sub">Show the live net banner on the homepage</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="net_active" value="1" {{ ($settings['net_active'] ?? '0') === '1' ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="field">
                <label class="label">Net / Callsign</label>
                <input type="text" name="net_callsign" class="input" placeholder="e.g. LIVERPOOL CONTROL or GB3MP Net" value="{{ $settings['net_callsign'] ?? '' }}">
            </div>
            <div class="field">
                <label class="label">Frequency</label>
                <input type="text" name="net_frequency" class="input" placeholder="e.g. 145.500 MHz" value="{{ $settings['net_frequency'] ?? '' }}">
            </div>
            <div class="field">
                <label class="label">Net Controller</label>
                <input type="text" name="net_controller" class="input" placeholder="e.g. G4BDS" value="{{ $settings['net_controller'] ?? '' }}">
            </div>
            <div class="field">
                <label class="label">Description / Strapline Text</label>
                <input type="text" name="net_description" class="input" placeholder="e.g. Weekly training net now in progress — all welcome" value="{{ $settings['net_description'] ?? '' }}">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="field">
                    <label class="label">Net Start Time <span style="font-size:.7rem;font-weight:normal;color:#6b7f96;">(shows countdown 90 min before)</span></label>
                    <input type="time" name="net_start_time" class="input" value="{{ $settings['net_start_time'] ?? '' }}">
                </div>
                <div class="field">
                    <label class="label">Net End Time <span style="font-size:.7rem;font-weight:normal;color:#6b7f96;">(auto-hides banner after this)</span></label>
                    <input type="time" name="net_end_time" class="input" value="{{ $settings['net_end_time'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="ns-card">
            <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;margin-bottom:.75rem;">Live Preview</div>
            <div style="position:relative;overflow:hidden;background:#0a0a1a;border-radius:8px;border:1px solid #1a1a3e;">
                <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(200,16,46,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(200,16,46,.04) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;"></div>
                <div style="position:absolute;top:-40px;left:15%;width:300px;height:120px;background:radial-gradient(ellipse,rgba(200,16,46,.25) 0%,transparent 70%);pointer-events:none;"></div>
                <div style="max-width:100%;padding:1rem 1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;position:relative;">
                    <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                        <div style="position:relative;width:12px;height:12px;">
                            <span style="position:absolute;inset:0;background:#C8102E;border-radius:50%;opacity:.6;"></span>
                            <span style="position:absolute;inset:1px;background:#ff1a3a;border-radius:50%;"></span>
                        </div>
                        <span style="font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.2em;color:#ff4466;">Live Net</span>
                    </div>
                    <div style="width:1px;height:36px;background:linear-gradient(to bottom,transparent,rgba(200,16,46,.5),transparent);flex-shrink:0;"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:baseline;gap:.75rem;flex-wrap:wrap;">
                            <span style="font-size:1.1rem;font-weight:900;color:#fff;letter-spacing:.02em;font-family:monospace;" id="previewCallsign">{{ strtoupper($settings['net_callsign'] ?: 'NET CALLSIGN') }}</span>
                            <span style="font-size:.9rem;font-weight:700;color:#C8102E;font-family:monospace;background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);padding:.1rem .5rem;border-radius:4px;" id="previewFreq">{{ $settings['net_frequency'] ?: '000.000 MHz' }}</span>
                        </div>
                        <div style="font-size:.82rem;color:rgba(255,255,255,.55);margin-top:.2rem;" id="previewDesc">{{ $settings['net_description'] ?: 'Net description will appear here' }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:1.25rem;flex-shrink:0;">
                        @if($settings['net_controller'] ?? '')
                        <div style="text-align:center;" id="previewCtrlRow">
                            <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);margin-bottom:.15rem;">Controller</div>
                            <div style="font-size:.9rem;font-weight:800;color:#fff;font-family:monospace;" id="previewCtrl">{{ strtoupper($settings['net_controller']) }}</div>
                        </div>
                        <div style="width:1px;height:28px;background:rgba(255,255,255,.1);"></div>
                        @endif
                        <div style="text-align:center;">
                            <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.35);margin-bottom:.15rem;">Group</div>
                            <div style="font-size:.78rem;font-weight:700;color:rgba(255,255,255,.7);">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                        </div>
                        <div style="background:linear-gradient(135deg,#C8102E,#8b0000);color:#fff;font-size:.75rem;font-weight:800;padding:.4rem .9rem;border-radius:999px;letter-spacing:.05em;border:1px solid rgba(200,16,46,.4);">Join Net →</div>
                    </div>
                </div>
            </div>
            <div style="font-size:.75rem;color:#6b7f96;margin-top:.6rem;" id="previewStatus">
                @if(($settings['net_active'] ?? '0') !== '1')
                <span style="color:#f59e0b;">⚠ Net is currently <strong>inactive</strong> — toggle on to show this banner on the homepage.</span>
                @else
                <span style="color:#059669;">✓ Banner is <strong>live</strong> on the homepage.</span>
                @endif
            </div>
        </div>

        <button type="submit" class="btn-save">Save Net Status</button>
    </form>
</div>

<script>
function updatePreview() {
    var callsign = document.querySelector('[name="net_callsign"]').value.toUpperCase() || 'NET CALLSIGN';
    var freq     = document.querySelector('[name="net_frequency"]').value || '000.000 MHz';
    var desc     = document.querySelector('[name="net_description"]').value || 'Net description will appear here';
    var ctrl     = document.querySelector('[name="net_controller"]').value.toUpperCase();
    var active   = document.querySelector('[name="net_active"]').checked;

    document.getElementById('previewCallsign').textContent = callsign;
    document.getElementById('previewFreq').textContent = freq;
    document.getElementById('previewDesc').textContent = desc;
    document.getElementById('previewCtrl').textContent = ctrl;
    document.getElementById('previewCtrlRow').style.display = ctrl ? '' : 'none';
    document.getElementById('previewStatus').innerHTML = active
        ? '<span style="color:#059669;">✓ Banner is <strong>live</strong> on the homepage.</span>'
        : '<span style="color:#f59e0b;">⚠ Net is currently <strong>inactive</strong> — toggle on to show this banner on the homepage.</span>';
}
document.querySelectorAll('input[name="net_callsign"],input[name="net_frequency"],input[name="net_description"],input[name="net_controller"],input[name="net_active"]').forEach(function(el) {
    el.addEventListener('input', updatePreview);
    el.addEventListener('change', updatePreview);
});
</script>
@endsection
