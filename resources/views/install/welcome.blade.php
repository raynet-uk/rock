<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome to RAYNET-OS</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;background:#0a0f1a;min-height:100vh;color:#111827;overflow-x:hidden}
.iz-bg{position:fixed;inset:0;z-index:0;overflow:hidden}
.iz-bg-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,51,102,.15) 1px,transparent 1px),linear-gradient(90deg,rgba(0,51,102,.15) 1px,transparent 1px);background-size:40px 40px;animation:gridMove 20s linear infinite}
@keyframes gridMove{0%{background-position:0 0}100%{background-position:40px 40px}}
.iz-bg-glow{position:absolute;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(26,107,60,.3) 0%,transparent 70%);top:-150px;left:-150px;animation:glowPulse 8s ease-in-out infinite}
.iz-bg-glow2{position:absolute;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(0,51,102,.3) 0%,transparent 70%);bottom:-100px;right:-100px;animation:glowPulse 6s ease-in-out infinite reverse}
@keyframes glowPulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}
.iz-wrap{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem}
.iz-card{width:100%;max-width:620px;background:rgba(255,255,255,.97);box-shadow:0 0 0 1px rgba(255,255,255,.1),0 24px 80px rgba(0,0,0,.5);overflow:hidden;animation:fadeUp .6s ease both}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.iz-card-accent{height:5px;background:linear-gradient(90deg,#1a6b3c,#2d9a5a 40%,#003366)}
.iz-card-body{padding:2.5rem 2rem}
.iz-card-foot{padding:1.25rem 2rem;background:#f8fafc;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}

/* Hero */
.wl-hero{text-align:center;margin-bottom:2rem}
.wl-icon{font-size:4rem;margin-bottom:1rem;animation:pop .6s .2s cubic-bezier(.34,1.56,.64,1) both}
@keyframes pop{from{transform:scale(0)}to{transform:scale(1)}}
.wl-title{font-size:1.75rem;font-weight:bold;color:#003366;margin-bottom:.5rem;letter-spacing:-.02em}
.wl-sub{font-size:.95rem;color:#6b7f96;line-height:1.6}
.wl-name{color:#003366;font-weight:bold}

/* Steps */
.wl-steps{display:flex;flex-direction:column;gap:.75rem;margin:1.75rem 0}
.wl-step{display:flex;align-items:flex-start;gap:1rem;padding:.85rem 1rem;background:#f8fafc;border:1px solid #e5e7eb;border-left:3px solid #003366}
.wl-step-num{width:28px;height:28px;background:#003366;color:#fff;font-size:.8rem;font-weight:bold;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:50%}
.wl-step-body{flex:1}
.wl-step-title{font-size:.85rem;font-weight:bold;color:#003366;margin-bottom:.2rem}
.wl-step-desc{font-size:.78rem;color:#6b7f96;line-height:1.5}

/* Info box */
.wl-info{background:#eef7f2;border:1px solid #b8ddc9;border-left:3px solid #1a6b3c;padding:.85rem 1rem;font-size:.82rem;color:#1a3a1f;line-height:1.6;margin-top:1.5rem;display:flex;gap:.65rem;align-items:flex-start}
.wl-info-icon{font-size:1.1rem;flex-shrink:0}

/* Buttons */
.wl-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.5rem;border:1.5px solid;font-family:inherit;font-size:.82rem;font-weight:bold;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.06em;text-decoration:none}
.wl-btn-primary{background:linear-gradient(135deg,#003366,#004d99);border-color:#003366;color:#fff;box-shadow:0 4px 12px rgba(0,51,102,.3)}
.wl-btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(0,51,102,.4)}
.wl-btn-ghost{background:#fff;border-color:#d1d5db;color:#6b7f96}
.wl-btn-ghost:hover{border-color:#003366;color:#003366}

.iz-footer{margin-top:1.5rem;font-size:.7rem;color:rgba(255,255,255,.25);text-align:center}
.iz-footer a{color:rgba(255,255,255,.4);text-decoration:none}
</style>
</head>
<body>
<div class="iz-bg">
    <div class="iz-bg-grid"></div>
    <div class="iz-bg-glow"></div>
    <div class="iz-bg-glow2"></div>
</div>

<div class="iz-wrap">
    <div class="iz-card">
        <div class="iz-card-accent"></div>
        <div class="iz-card-body">
            <div class="wl-hero">
                <div class="wl-icon">🎉</div>
                <h1 class="wl-title">Welcome to RAYNET-OS!</h1>
                <p class="wl-sub">
                    <span class="wl-name">{{ Auth::user()->name }}</span>, your site is live and you're logged in as a super-administrator.
                    Here's what to do next to get your group website fully set up.
                </p>
            </div>

            <div class="wl-steps">
                <div class="wl-step">
                    <div class="wl-step-num">1</div>
                    <div class="wl-step-body">
                        <div class="wl-step-title">Upload your group logo</div>
                        <div class="wl-step-desc">Go to <strong>Admin → Settings</strong> and upload your group logo. It will appear in the header, emails and certificates.</div>
                    </div>
                </div>
                <div class="wl-step">
                    <div class="wl-step-num">2</div>
                    <div class="wl-step-body">
                        <div class="wl-step-title">Customise your pages</div>
                        <div class="wl-step-desc">Go to <strong>Admin → Pages</strong> to edit your Home, About, Training and Event Support pages using the visual page builder.</div>
                    </div>
                </div>
                <div class="wl-step">
                    <div class="wl-step-num">3</div>
                    <div class="wl-step-body">
                        <div class="wl-step-title">Install modules</div>
                        <div class="wl-step-desc">Go to <strong>Admin → Module Manager</strong> to browse and install additional features from the RAYNET-OS module repository.</div>
                    </div>
                </div>
                <div class="wl-step">
                    <div class="wl-step-num">4</div>
                    <div class="wl-step-body">
                        <div class="wl-step-title">Invite your members</div>
                        <div class="wl-step-desc">Share your site URL with your group. Members can register and you can approve them from <strong>Admin → Users</strong>.</div>
                    </div>
                </div>
            </div>

            <div class="wl-info">
                <span class="wl-info-icon">📻</span>
                <div>
                    <strong>Built by RAYNET Liverpool</strong> (G4BDS &amp; M7NDN) for all RAYNET UK affiliated groups.<br>
                    For support, visit <a href="https://raynet-liverpool.net/request-support" style="color:#1a6b3c;font-weight:bold">raynet-liverpool.net/request-support</a>
                    or check the <a href="https://github.com/raynet-uk/raynet-cms" style="color:#1a6b3c;font-weight:bold">GitHub repository</a>.
                </div>
            </div>
        </div>
        <div class="iz-card-foot">
            <span style="font-size:.75rem;color:#9ca3af">
                Logged in as <strong>{{ Auth::user()->callsign }}</strong> · Super Administrator
            </span>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                <a href="{{ route('admin.settings') }}" class="wl-btn wl-btn-ghost">⚙ Settings</a>
                <a href="{{ route('admin.dashboard') }}" class="wl-btn wl-btn-primary">
                    Go to Admin Panel →
                </a>
            </div>
        </div>
    </div>

    <div class="iz-footer">
        RAYNET-OS &middot; <a href="https://www.raynet-uk.net">RAYNET UK</a> &middot;
        <a href="https://github.com/raynet-uk/raynet-cms">GitHub</a>
    </div>
</div>
</body>
</html>