<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ROCK — Install Preview Complete</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;background:#0a0f1a;min-height:100vh;color:#111827;overflow-x:hidden}
.iz-bg{position:fixed;inset:0;z-index:0;overflow:hidden}
.iz-bg-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,51,102,.15) 1px,transparent 1px),linear-gradient(90deg,rgba(0,51,102,.15) 1px,transparent 1px);background-size:40px 40px;animation:gridMove 20s linear infinite}
@keyframes gridMove{0%{background-position:0 0}100%{background-position:40px 40px}}
.iz-bg-glow{position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,51,102,.4) 0%,transparent 70%);top:-100px;left:-100px;animation:glowPulse 8s ease-in-out infinite}
.iz-bg-glow2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(26,107,60,.2) 0%,transparent 70%);bottom:-50px;right:-50px;animation:glowPulse 6s ease-in-out infinite reverse}
@keyframes glowPulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}
.preview-bar{position:fixed;top:0;left:0;right:0;z-index:9999;background:linear-gradient(90deg,#f59e0b,#d97706);color:#fff;text-align:center;padding:.5rem 1rem;font-size:.78rem;font-weight:bold;letter-spacing:.06em;text-transform:uppercase;box-shadow:0 2px 8px rgba(0,0,0,.3)}
.iz-wrap{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 1rem 2rem}
.iz-brand{display:flex;align-items:center;gap:.85rem;margin-bottom:2rem}
.iz-logo{width:52px;height:52px;background:linear-gradient(135deg,#003366,#004d99);border:2px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.iz-logo span{font-size:9px;font-weight:bold;color:#fff;text-align:center;line-height:1.3;text-transform:uppercase}
.iz-brand-name{font-size:1.25rem;font-weight:bold;color:#fff}
.iz-brand-sub{font-size:.75rem;color:rgba(255,255,255,.45);margin-top:.1rem;text-transform:uppercase;letter-spacing:.05em}
.iz-card{width:100%;max-width:680px;background:rgba(255,255,255,.97);box-shadow:0 0 0 1px rgba(255,255,255,.1),0 24px 80px rgba(0,0,0,.5);overflow:hidden;animation:fadeUp .5s ease both}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.iz-card-accent{height:4px;background:linear-gradient(90deg,#1a6b3c,#2d9a5a 40%,#003366)}
.iz-card-head{padding:1.5rem 1.75rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f8fafc}
.iz-card-title{font-size:1.05rem;font-weight:bold;color:#003366;display:flex;align-items:center;gap:.6rem}
.iz-card-sub{font-size:.78rem;color:#6b7f96;margin-top:.35rem;line-height:1.55}
.iz-card-body{padding:0}
.iz-card-foot{padding:1.1rem 1.75rem;background:#f8fafc;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between}

/* Success banner */
.iz-success{background:#eef7f2;border-bottom:1px solid #b8ddc9;padding:1rem 1.75rem;font-size:.85rem;color:#1a6b3c;font-weight:bold;display:flex;align-items:center;gap:.65rem}
.iz-success-icon{font-size:1.25rem;flex-shrink:0}

/* Section blocks */
.iz-section{border-bottom:1px solid #e5e7eb}
.iz-section:last-child{border-bottom:none}
.iz-section-head{padding:.75rem 1.75rem;background:#f8fafc;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:.6rem;cursor:pointer;user-select:none}
.iz-section-head:hover{background:#f0f4f8}
.iz-section-badge{font-size:.65rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;padding:.2rem .55rem;border-radius:2px;flex-shrink:0}
.iz-section-badge-db   {background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
.iz-section-badge-art  {background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
.iz-section-badge-red  {background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.iz-section-badge-nav  {background:#faf5ff;color:#6b21a8;border:1px solid #e9d5ff}
.iz-section-title{font-size:.82rem;font-weight:bold;color:#374151;flex:1}
.iz-section-count{font-size:.72rem;color:#9ca3af;font-weight:normal}
.iz-section-body{padding:0 1.75rem .75rem}
.iz-section-toggle{font-size:.7rem;color:#9ca3af;margin-left:auto}

/* Table rows */
.iz-table{width:100%;border-collapse:collapse;margin-top:.75rem}
.iz-table th{font-size:.68rem;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:#9ca3af;padding:.35rem .6rem;text-align:left;border-bottom:2px solid #f3f4f6}
.iz-table td{font-size:.8rem;padding:.45rem .6rem;border-bottom:1px solid #f9fafb;vertical-align:top}
.iz-table tr:last-child td{border-bottom:none}
.iz-table .key{font-family:ui-monospace,monospace;color:#003366;font-weight:bold;font-size:.75rem;white-space:nowrap}
.iz-table .val{color:#374151;word-break:break-all}
.iz-table .badge-insert{display:inline-block;font-size:.62rem;font-weight:bold;padding:.1rem .35rem;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;text-transform:uppercase;letter-spacing:.05em;margin-right:.35rem;flex-shrink:0}
.iz-table .badge-upsert{display:inline-block;font-size:.62rem;font-weight:bold;padding:.1rem .35rem;background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;text-transform:uppercase;letter-spacing:.05em;margin-right:.35rem;flex-shrink:0}
.iz-table .badge-set{display:inline-block;font-size:.62rem;font-weight:bold;padding:.1rem .35rem;background:#fef3c7;color:#92400e;border:1px solid #fde68a;text-transform:uppercase;letter-spacing:.05em;margin-right:.35rem;flex-shrink:0}
.iz-table .cmd{font-family:ui-monospace,monospace;background:#1e1e2e;color:#cdd6f4;padding:.3rem .6rem;font-size:.75rem;display:inline-block}

/* Confirm box */
.iz-confirm{margin:0 1.75rem 1.25rem;background:#fffbeb;border:1px solid #fde68a;border-left:3px solid #f59e0b;padding:.85rem 1rem;font-size:.8rem;color:#78350f;line-height:1.7}

/* Buttons */
.iz-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.4rem;border:1.5px solid;font-family:inherit;font-size:.8rem;font-weight:bold;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.06em;text-decoration:none}
.iz-btn-primary{background:linear-gradient(135deg,#003366,#004d99);border-color:#003366;color:#fff}
.iz-btn-primary:hover{transform:translateY(-1px)}
.iz-btn-ghost{background:#fff;border-color:#d1d5db;color:#6b7f96}
.iz-btn-ghost:hover{border-color:#003366;color:#003366}
.iz-footer{margin-top:1.5rem;font-size:.7rem;color:rgba(255,255,255,.25);text-align:center}
.iz-footer a{color:rgba(255,255,255,.4);text-decoration:none}
</style>
</head>
<body>
<div class="preview-bar">&#9888; PREVIEW MODE &mdash; No data was saved. This is a complete dry run.</div>

<div class="iz-bg">
    <div class="iz-bg-grid"></div>
    <div class="iz-bg-glow"></div>
    <div class="iz-bg-glow2"></div>
</div>

<div class="iz-wrap">

    <div class="iz-brand">
        <div class="iz-logo"><span>RAY<br>NET</span></div>
        <div>
            <div class="iz-brand-name">ROCK</div>
            <div class="iz-brand-sub">Installer &mdash; Dry Run Complete</div>
        </div>
    </div>

    <div class="iz-card">
        <div class="iz-card-accent"></div>

        <div class="iz-card-head">
            <div class="iz-card-title">&#129514; Dry Run Summary &mdash; Everything that would happen</div>
            <div class="iz-card-sub">The full installer ran in preview mode. Below is a complete record of every database write, artisan command, and redirect that would occur on a real installation. Nothing was saved.</div>
        </div>

        <div class="iz-success">
            <span class="iz-success-icon">&#10003;</span>
            All {{ count($groupSettings) + count($adminAccount) }} data points validated &mdash; installer would complete successfully with no errors.
        </div>

        <div class="iz-card-body">

            {{-- ── Section 1: Settings table writes ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-db">DB</span>
                    <span class="iz-section-title">Settings Table &mdash; Group Configuration <span class="iz-section-count">({{ count($groupSettings) }} rows)</span></span>
                </div>
                <div class="iz-section-body">
                    @if(count($groupSettings))
                    <table class="iz-table">
                        <thead><tr><th>Action</th><th>Key</th><th>Value</th></tr></thead>
                        <tbody>
                        @foreach($groupSettings as $key => $value)
                        <tr>
                            <td><span class="badge-upsert">UPSERT</span></td>
                            <td class="key">{{ $key }}</td>
                            <td class="val">{{ $value }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="font-size:.8rem;color:#9ca3af;padding:.75rem 0">No group data captured &mdash; go back and complete step 1 first.</p>
                    @endif
                </div>
            </div>

            {{-- ── Section 2: Install flag ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-db">DB</span>
                    <span class="iz-section-title">Settings Table &mdash; Install Flag</span>
                </div>
                <div class="iz-section-body">
                    <table class="iz-table">
                        <thead><tr><th>Action</th><th>Key</th><th>Value</th><th>Effect</th></tr></thead>
                        <tbody>
                        <tr>
                            <td><span class="badge-set">SET</span></td>
                            <td class="key">installed</td>
                            <td class="val">1</td>
                            <td class="val" style="color:#6b7f96">Disables the install wizard and redirects to the site</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Section 3: Users table ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-db">DB</span>
                    <span class="iz-section-title">Users Table &mdash; Admin Account <span class="iz-section-count">(1 row)</span></span>
                </div>
                <div class="iz-section-body">
                    @if(count($adminAccount))
                    <table class="iz-table">
                        <thead><tr><th>Action</th><th>Column</th><th>Value</th></tr></thead>
                        <tbody>
                        @foreach($adminAccount as $key => $value)
                        <tr>
                            <td><span class="badge-insert">INSERT</span></td>
                            <td class="key">{{ $key }}</td>
                            <td class="val">{{ $value }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="font-size:.8rem;color:#9ca3af;padding:.75rem 0">No admin data captured &mdash; go back and complete step 2 first.</p>
                    @endif
                </div>
            </div>

            {{-- ── Section 4: Roles ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-db">DB</span>
                    <span class="iz-section-title">model_has_roles Table &mdash; Role Assignment</span>
                </div>
                <div class="iz-section-body">
                    <table class="iz-table">
                        <thead><tr><th>Action</th><th>Column</th><th>Value</th></tr></thead>
                        <tbody>
                        <tr><td><span class="badge-insert">INSERT</span></td><td class="key">role_id</td><td class="val">ID of <code>super-admin</code> role</td></tr>
                        <tr><td><span class="badge-insert">INSERT</span></td><td class="key">model_type</td><td class="val">App\Models\User</td></tr>
                        <tr><td><span class="badge-insert">INSERT</span></td><td class="key">model_id</td><td class="val">ID of newly created admin user</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Section 5: Artisan commands ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-art">CLI</span>
                    <span class="iz-section-title">Artisan Commands <span class="iz-section-count">({{ count($artisanCommands) }} commands)</span></span>
                </div>
                <div class="iz-section-body">
                    <table class="iz-table">
                        <thead><tr><th>Command</th><th>Effect</th></tr></thead>
                        <tbody>
                        @foreach($artisanCommands as $cmd)
                        <tr>
                            <td><span class="cmd">{{ $cmd['cmd'] }}</span></td>
                            <td class="val" style="color:#6b7f96">{{ $cmd['detail'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Section 6: Redirect ── --}}
            <div class="iz-section">
                <div class="iz-section-head">
                    <span class="iz-section-badge iz-section-badge-nav">HTTP</span>
                    <span class="iz-section-title">Final Redirect</span>
                </div>
                <div class="iz-section-body">
                    <table class="iz-table">
                        <thead><tr><th>From</th><th>To</th><th>Message</th></tr></thead>
                        <tbody>
                        @foreach($redirects as $r)
                        <tr>
                            <td class="key">{{ $r['from'] }}</td>
                            <td class="key">{{ $r['to'] }}</td>
                            <td class="val" style="color:#6b7f96">{{ $r['detail'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Confirm box ── --}}
            <div class="iz-confirm">
                <strong>&#9989; Preview confirmed:</strong> The installer ran through all three steps and validated successfully.
                No data was written to the database, no user was created, and no artisan commands were executed.
                On a real installation with the same inputs, every operation above would complete in the order shown.
            </div>

        </div>

        <div class="iz-card-foot">
            <a href="{{ route('install.preview.index') }}" class="iz-btn iz-btn-ghost">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                Run Again
            </a>
            <a href="{{ route('admin.dashboard') }}" class="iz-btn iz-btn-primary">
                Back to Admin
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <div class="iz-footer">
        ROCK &middot; <a href="https://www.raynet-uk.net">RAYNET UK</a> &middot; Preview Mode
    </div>

</div>
</body>
</html>