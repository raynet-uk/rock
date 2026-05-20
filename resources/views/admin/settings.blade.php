@extends('layouts.admin')
@section('title', 'Admin Settings')
@section('content')

<style>
.as-wrap { max-width: 860px; margin: 0 auto; padding: 0 0 4rem; }
.as-card { background: #fff; border: 1px solid #dde2e8; border-top: 3px solid #003366; box-shadow: 0 1px 3px rgba(0,51,102,.09); margin-bottom: 1.5rem; }
.as-card-head { padding: .75rem 1.2rem; background: #f4f5f7; border-bottom: 1px solid #dde2e8; }
.as-card-head h2 { font-size: 12px; font-weight: bold; color: #003366; text-transform: uppercase; letter-spacing: .06em; margin: 0; }
.as-card-body { padding: 1.25rem 1.2rem; display: flex; flex-direction: column; gap: 1.1rem; }
.as-field { display: flex; flex-direction: column; gap: .35rem; }
.as-label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: #6b7f96; }
.as-input { background: #fff; border: 1px solid #dde2e8; padding: .5rem .8rem; font-family: inherit; font-size: 13px; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s; width: 100%; box-sizing: border-box; }
.as-input:focus { border-color: #003366; box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.as-hint { font-size: 11px; color: #9aa3ae; }
.as-foot { padding: .75rem 1.2rem; border-top: 1px solid #dde2e8; background: #f4f5f7; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.as-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem 1.1rem; border: 1px solid; font-family: inherit; font-size: 12px; font-weight: bold; cursor: pointer; transition: all .12s; text-transform: uppercase; letter-spacing: .05em; text-decoration: none; }
.as-btn-primary { background: #003366; border-color: #003366; color: #fff; }
.as-btn-primary:hover { background: #002244; }
.as-btn-ghost { background: transparent; border-color: #dde2e8; color: #6b7f96; }
.as-btn-ghost:hover { border-color: #003366; color: #003366; }
.as-btn-danger { background: transparent; border-color: #C8102E; color: #C8102E; }
.as-btn-danger:hover { background: #fdf0f2; }
.as-notice { display: flex; align-items: center; gap: .55rem; padding: .65rem 1rem; margin-bottom: 1rem; font-size: 12.5px; font-weight: bold; }
.as-notice--ok  { background: #eef7f2; border: 1px solid #b8ddc9; border-left: 3px solid #1a6b3c; color: #1a6b3c; }
.as-notice--err { background: #fdf0f2; border: 1px solid rgba(200,16,46,.25); border-left: 3px solid #C8102E; color: #C8102E; }
.as-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.as-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
.logo-row { display: flex; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap; }
.logo-preview-box { width: 210px; min-height: 64px; background: #f4f5f7; border: 2px dashed #dde2e8; display: flex; align-items: center; justify-content: center; padding: .75rem; flex-shrink: 0; }
.logo-preview-box img { max-width: 190px; max-height: 70px; object-fit: contain; }
.logo-controls { flex: 1; min-width: 200px; }
.logo-upload-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .42rem .9rem; background: #003366; border: 1px solid #003366; color: #fff; font-size: 12px; font-weight: bold; font-family: inherit; cursor: pointer; text-transform: uppercase; letter-spacing: .04em; transition: background .12s; }
.logo-upload-btn:hover { background: #002244; }
.logo-file-name { font-size: 11px; color: #6b7f96; margin-top: .35rem; }
.nav-preview-wrap { margin-top: .85rem; background: #f4f5f7; border: 1px solid #dde2e8; padding: .75rem 1rem; }
.nav-preview-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: #9aa3ae; margin-bottom: .5rem; }
.nav-preview-bar { background: #fff; border: 2px solid #003366; padding: .5rem .9rem; display: inline-flex; align-items: center; gap: .6rem; box-shadow: 0 1px 4px rgba(0,51,102,.07); }
.nav-preview-bar img { max-height: 30px; width: auto; }
.nav-preview-tagline { font-size: 8px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: #C8102E; font-family: Arial, sans-serif; line-height: 1; }
.as-section-divider { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .12em; color: #9aa3ae; padding: .5rem 0 .25rem; border-bottom: 1px solid #e5e7eb; margin-bottom: .25rem; }
</style>

<div class="as-wrap">

    @if(session('status'))  <div class="as-notice as-notice--ok">✓ {{ session('status') }}</div>  @endif
    @if(session('success')) <div class="as-notice as-notice--ok">✓ {{ session('success') }}</div> @endif
    @if(session('error'))   <div class="as-notice as-notice--err">⚠ {{ session('error') }}</div>  @endif

    {{-- GROUP IDENTITY --}}
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="as-card">
            <div class="as-card-head"><h2>📻 Group Identity</h2></div>
            <div class="as-card-body">
                <div class="as-row">
                    <div class="as-field">
                        <label class="as-label" for="group_name">Group Name</label>
                        <input type="text" id="group_name" name="group_name" class="as-input"
                               value="{{ old('group_name', \App\Models\Setting::get('group_name', '')) }}"
                               placeholder="e.g. {{ \App\Helpers\RaynetSetting::groupName() }}" maxlength="80">
                        <div class="as-hint">Appears throughout the site, emails and footer.</div>
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" class="as-input"
                               value="{{ old('site_name', \App\Models\Setting::get('site_name', '')) }}"
                               placeholder="e.g. {{ \App\Helpers\RaynetSetting::groupName() }}" maxlength="80">
                        <div class="as-hint">Used in browser title and emails. Usually same as group name.</div>
                    </div>
                </div>
                <div class="as-row-3">
                    <div class="as-field">
                        <label class="as-label" for="group_number">Group Number</label>
                        <input type="text" id="group_number" name="group_number" class="as-input"
                               value="{{ old('group_number', \App\Models\Setting::get('group_number', '')) }}"
                               placeholder="e.g. {{ \App\Helpers\RaynetSetting::groupNumber() }}" maxlength="20">
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="group_callsign">Group Callsign</label>
                        <input type="text" id="group_callsign" name="group_callsign" class="as-input"
                               value="{{ old('group_callsign', \App\Models\Setting::get('group_callsign', '')) }}"
                               placeholder="e.g. M0XYZ" maxlength="20"
                               oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="raynet_zone">RAYNET Zone</label>
                        <input type="text" id="raynet_zone" name="raynet_zone" class="as-input"
                               value="{{ old('raynet_zone', \App\Models\Setting::get('raynet_zone', '')) }}"
                               placeholder="e.g. {{ \App\Helpers\RaynetSetting::groupRegion() }}" maxlength="20">
                    </div>
                </div>
                <div class="as-row">
                    <div class="as-field">
                        <label class="as-label" for="group_region">RAYNET Zone</label>
                        @php $currentRegion = old('group_region', \App\Models\Setting::get('group_region', '')); @endphp
                        <select id="group_region" name="group_region" class="as-input">
                            <option value="">— Select zone —</option>
                            @foreach([
                                'Zone 1'  => 'Zone 1 — North East England',
                                'Zone 2'  => 'Zone 2 — Yorkshire and Humberside',
                                'Zone 3'  => 'Zone 3 — East Midlands',
                                'Zone 4'  => 'Zone 4 — East Anglia',
                                'Zone 5'  => 'Zone 5 — Greater London',
                                'Zone 6'  => 'Zone 6 — South East England',
                                'Zone 7'  => 'Zone 7 — South West England & Channel Isles',
                                'Zone 8'  => 'Zone 8 — South Wales',
                                'Zone 9'  => 'Zone 9 — West Midlands',
                                'Zone 10' => 'Zone 10 — North West England',
                                'Zone 11' => 'Zone 11 — Northern Ireland',
                                'Zone 18' => 'Zone 18 — North Wales',
                                'Zone 20' => 'Zone 20 — Scotland',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ $currentRegion === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="as-hint">Your RAYNET zone. Used in homepage text and regional news feed.</div>
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="group_area">Local Area Name</label>
                        <input type="text" id="group_area" name="group_area" class="as-input"
                               value="{{ old('group_area', \App\Models\Setting::get('group_area', '')) }}"
                               placeholder="e.g. Merseyside" maxlength="80">
                        <div class="as-hint">Used in homepage, footers and emails — e.g. &quot;for Merseyside&quot;.</div>
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" class="as-input"
                               value="{{ old('site_url', \App\Models\Setting::get('site_url', config('app.url'))) }}"
                               placeholder="https://yourgroup.net" maxlength="120">
                        <div class="as-hint">Your public website URL.</div>
                    </div>
                </div>
                <div class="as-section-divider">Group Controller</div>
                <div class="as-row">
                    <div class="as-field">
                        <label class="as-label" for="gc_name">GC Name</label>
                        <input type="text" id="gc_name" name="gc_name" class="as-input"
                               value="{{ old('gc_name', \App\Models\Setting::get('gc_name', '')) }}"
                               placeholder="e.g. John Smith" maxlength="80">
                    </div>
                    <div class="as-field">
                        <label class="as-label" for="gc_email">GC Email</label>
                        <input type="email" id="gc_email" name="gc_email" class="as-input"
                               value="{{ old('gc_email', \App\Models\Setting::get('gc_email', '')) }}"
                               placeholder="gc@yourgroup.raynet-uk.net" maxlength="120">
                    </div>
                </div>
                <div class="as-field">
                    <label class="as-label" for="site_tagline">Navbar Tagline</label>
                    <input type="text" id="site_tagline" name="site_tagline" class="as-input"
                           value="{{ old('site_tagline', \App\Models\Setting::get('site_tagline', 'Robust, Resilient, Radio')) }}"
                           placeholder="e.g. Robust, Resilient, Radio" maxlength="120"
                           oninput="document.getElementById('previewTagline').textContent = this.value || '—'">
                    <div class="as-hint">Small red text beneath the logo in the navbar.</div>
                </div>
            </div>
            <div class="as-foot">
                <a href="{{ route('admin.dashboard') }}" class="as-btn as-btn-ghost">← Dashboard</a>
                <button type="submit" class="as-btn as-btn-primary">✓ Save Group Identity</button>
            </div>
        </div>
    </form>

    {{-- LOGO & BRANDING --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="as-card">
            <div class="as-card-head"><h2>🖼 Logo &amp; Branding</h2></div>
            <div class="as-card-body">
                <div class="as-field">
                    <div class="as-label">Site Logo</div>
                    <div class="logo-row">
                        <div class="logo-preview-box" id="logoPreviewBox">
                            @php
                                $lp = \App\Models\Setting::get('site_logo_path', '');
                                $logoSrc = $lp ? \Illuminate\Support\Facades\Storage::url($lp) : asset('images/raynet-uk-liverpool-banner.png');
                            @endphp
                            <img src="{{ $logoSrc }}" alt="Logo preview" id="logoPreviewImg">
                        </div>
                        <div class="logo-controls">
                            <label class="logo-upload-btn" for="logoFileInput">📁 Choose file</label>
                            <input type="file" name="site_logo" id="logoFileInput"
                                   accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   style="display:none"
                                   onchange="previewLogo(this)">
                            <div class="logo-file-name" id="logoFileName">No file chosen</div>
                            <div class="as-hint" style="margin-top:.4rem">PNG, JPG, SVG or WebP · Max 2 MB<br>Recommended: transparent PNG, roughly 280 × 40 px</div>
                            @if($lp)
                            <div style="margin-top:.65rem">
                                <input type="hidden" name="remove_logo" id="removeLogoInput" value="0">
                                <button type="button" class="as-btn as-btn-danger" style="font-size:11px;padding:.3rem .75rem" onclick="removeLogo()">✕ Remove custom logo</button>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="nav-preview-wrap">
                        <div class="nav-preview-label">Navbar preview</div>
                        <div class="nav-preview-bar">
                            <img src="{{ $logoSrc }}" alt="Preview" id="previewNavImg">
                            <span class="nav-preview-tagline" id="previewTagline">{{ \App\Models\Setting::get('site_tagline', 'Robust, Resilient, Radio') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="as-foot">
                <span class="as-hint">Logo stored in <code>storage/app/public/site/</code></span>
                <button type="submit" class="as-btn as-btn-primary">✓ Save Logo</button>
            </div>
        </div>
    </form>

    {{-- EMAIL SETTINGS --}}
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="as-card">
            <div class="as-card-head"><h2>📧 Email Settings</h2></div>
            <div class="as-card-body">
                <div class="as-field">
                    <label class="as-label" for="support_request_email">Support Request Email</label>
                    <input type="email" id="support_request_email" name="support_request_email" class="as-input"
                           value="{{ old('support_request_email', $supportEmail) }}" required>
                    <div class="as-hint">All support request form submissions will be sent here.</div>
                </div>
                <div class="as-field">
                    <label class="as-label" for="registration_notify_email">New Registration Notification Email</label>
                    <input type="email" id="registration_notify_email" name="registration_notify_email" class="as-input"
                           value="{{ old('registration_notify_email', $registrationNotifyEmail ?? '') }}"
                           placeholder="e.g. controller@yourgroup.net">
                    <div class="as-hint">When a new member registers, an alert will be sent here. Leave blank to disable.</div>
                </div>
            </div>
            <div class="as-foot">
                <a href="{{ route('admin.dashboard') }}" class="as-btn as-btn-ghost">← Dashboard</a>
                <button type="submit" class="as-btn as-btn-primary">✓ Save Email Settings</button>
            </div>
        </div>
    </form>

    {{-- TELEGRAM SETTINGS --}}
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="as-card">
            <div class="as-card-head"><h2>📡 Telegram Notifications</h2></div>
            <div class="as-card-body">
                <div class="as-field">
                    <label class="as-label">Group Chat ID</label>
                    <input type="text" name="telegram_group_chat_ids" class="as-input"
                           value="{{ old('telegram_group_chat_ids', $telegramGroupChatIds) }}"
                           placeholder="e.g. -1001234567890, -1009876543210" maxlength="50" style="font-family:monospace">
                    <div class="as-hint">
                        Always a negative number. Add <strong>@userinfobot</strong> to your group, send any message, copy the ID, then remove the bot.
                    </div>
                </div>
                <div class="as-field">
                    <label class="as-label">Topic / Thread ID <span style="font-weight:normal;text-transform:none;letter-spacing:0;font-size:11px">(optional)</span></label>
                    <input type="text" name="telegram_group_thread_id" class="as-input"
                           value="{{ old('telegram_group_thread_id', $telegramGroupThreadId) }}"
                           placeholder="e.g. 2 (leave blank for General)" style="font-family:monospace">
                    <div class="as-hint">Send <code>/id</code> inside a topic — the bot replies with the Thread ID. Paste it here. Leave blank to post to General.</div>
                </div>
            <div class="as-foot">
                <a href="{{ route('admin.settings.telegram-test') }}"
                   onclick="return confirm('Send a test alert to the configured Telegram group?')"
                   class="as-btn as-btn-ghost" style="border-color:#0088cc;color:#0088cc">✈ Send Test Message</a>
                <button type="submit" class="as-btn as-btn-primary">✓ Save Telegram Settings</button>
            </div>
        </div>
    </form>
    @include('admin.partials.telegram_perms_card')

    {{-- HEADER CODE --}}
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="as-card">
            <div class="as-card-head"><h2>🔧 Header Code</h2></div>
            <div class="as-card-body">
                <div class="as-field">
                    <label class="as-label">Header Code <span style="text-transform:none;letter-spacing:0;font-weight:normal;font-size:11px">(injected into &lt;head&gt; on every page)</span></label>
                    <textarea name="header_code" rows="6" class="as-input"
                              style="font-family:monospace;font-size:12px;resize:vertical;min-height:100px"
                              placeholder="&lt;!-- e.g. Google Analytics, Meta Pixel, etc. --&gt;">{{ old('header_code', $headerCode) }}</textarea>
                    <div class="as-hint">Paste any tracking scripts or meta tags here. Output is unescaped — only add trusted code.</div>
                </div>
            </div>
            <div class="as-foot">
                <a href="{{ route('admin.dashboard') }}" class="as-btn as-btn-ghost">← Dashboard</a>
                <button type="submit" class="as-btn as-btn-primary">✓ Save Header Code</button>
            </div>
        </div>
    </form>

</div>

<script>
function previewLogo(input) {
    if (!input.files || !input.files[0]) return;
    document.getElementById('logoFileName').textContent = input.files[0].name;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logoPreviewImg').src = e.target.result;
        document.getElementById('previewNavImg').src  = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}
function removeLogo() {
    if (!confirm('Remove the custom logo and revert to the default?')) return;
    document.getElementById('removeLogoInput').value = '1';
    const def = '{{ asset("images/raynet-uk-liverpool-banner.png") }}';
    document.getElementById('logoPreviewImg').src = def;
    document.getElementById('previewNavImg').src  = def;
    document.getElementById('logoFileName').textContent = 'Logo will be removed on save';
    event.target.disabled = true;
    event.target.style.opacity = '.4';
}
</script>
@endsection
