<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RAYNET-OS Updated</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;background:#003366;}
body{display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;overflow:hidden;}

.bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;}
.red-bar{position:fixed;top:0;left:0;right:0;height:5px;background:#C8102E;}

.card{position:relative;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:3rem 3.5rem;max-width:560px;width:100%;text-align:center;backdrop-filter:blur(10px);}

.logo-wrap{margin-bottom:2rem;}
.logo-box{display:inline-block;background:#C8102E;padding:8px 18px;font-size:13px;font-weight:bold;color:#fff;letter-spacing:.12em;text-transform:uppercase;margin-bottom:.75rem;}

.checkmark{width:64px;height:64px;border-radius:50%;background:rgba(26,107,60,.3);border:2px solid #1a6b3c;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:28px;animation:pop .4s cubic-bezier(.175,.885,.32,1.275) .2s both;}
@keyframes pop{from{transform:scale(0);opacity:0;}to{transform:scale(1);opacity:1;}}

.title{font-size:1.8rem;font-weight:bold;color:#fff;margin-bottom:.5rem;}
.subtitle{font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:2rem;}

.version-row{display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:2rem;}
.v-chip{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:.35rem 1rem;font-family:monospace;font-size:.95rem;color:rgba(255,255,255,.7);}
.v-arrow{color:rgba(255,255,255,.4);font-size:1.2rem;}
.v-chip.new{background:rgba(26,107,60,.3);border-color:#1a6b3c;color:#7effa0;font-weight:bold;}

.features{text-align:left;background:rgba(0,0,0,.2);border-radius:8px;padding:1rem 1.25rem;margin-bottom:2rem;}
.feature{display:flex;align-items:center;gap:.6rem;padding:.3rem 0;font-size:.85rem;color:rgba(255,255,255,.75);}
.feature::before{content:'✓';color:#7effa0;font-weight:bold;flex-shrink:0;}

.btn{display:block;width:100%;padding:.85rem;background:#C8102E;color:#fff;font-size:1rem;font-weight:bold;border:none;border-radius:6px;cursor:pointer;letter-spacing:.04em;transition:background .15s;}
.btn:hover{background:#a00d25;}

.meta{font-size:.75rem;color:rgba(255,255,255,.3);margin-top:1.25rem;}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="red-bar"></div>

<div class="card">
    <div class="logo-wrap">
        <div class="logo-box">RAYNET-OS</div>
    </div>

    <div class="checkmark">✓</div>

    <h1 class="title">Successfully Updated</h1>
    <p class="subtitle">{{ \App\Helpers\RaynetSetting::groupName() }} is now running the latest version.</p>

    <div class="version-row">
        <div class="v-chip">v{{ \App\Models\Setting::get('last_updated_version', '—') }}</div>
        <div class="v-arrow">→</div>
        <div class="v-chip new">v{{ trim(file_get_contents(base_path('VERSION'))) }}</div>
    </div>

    <div class="features">
        <div class="feature">Database migrations applied successfully</div>
        <div class="feature">All caches cleared and rebuilt</div>
        <div class="feature">Your data, settings and files are unchanged</div>
        <div class="feature">Latest features and fixes now active</div>
    </div>

    <form method="POST" action="{{ route('admin.cms-update.dismiss') }}">
        @csrf
        <button type="submit" class="btn">Continue to Dashboard →</button>
    </form>

    <div class="meta">Updated {{ \Carbon\Carbon::parse(\App\Models\Setting::get('last_updated_at'))->format('j M Y \a\t H:i') }}</div>
</div>
</body>
</html>
