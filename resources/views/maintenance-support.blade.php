<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="30">
<title>Maintenance — {{ \App\Helpers\RaynetSetting::groupName() }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;background:#0d1b2e;}
body{display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;padding:1.5rem;min-height:100vh;}
.bg{position:fixed;inset:0;background:radial-gradient(ellipse at 30% 20%,rgba(0,51,102,.4) 0%,transparent 60%),radial-gradient(ellipse at 70% 80%,rgba(200,16,46,.12) 0%,transparent 60%);pointer-events:none;}
.wrap{position:relative;z-index:1;max-width:480px;width:100%;text-align:center;}

.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.5);}
.top-bar{height:3px;background:linear-gradient(90deg,#003366,#C8102E,#003366);}

.logo-wrap{padding:2rem 2rem 1.25rem;}
.logo-box{display:inline-block;background:#C8102E;padding:6px 14px;font-size:11px;font-weight:bold;color:#fff;letter-spacing:.15em;text-transform:uppercase;border-radius:3px;margin-bottom:1rem;}

.spinner-wrap{margin:0 auto 1.5rem;width:64px;height:64px;position:relative;}
.spinner{width:64px;height:64px;border-radius:50%;border:2px solid rgba(255,255,255,.05);border-top-color:rgba(0,102,204,.6);border-right-color:rgba(200,16,46,.4);animation:spin 1.5s linear infinite;}
.spinner-inner{position:absolute;inset:8px;border-radius:50%;border:2px solid rgba(255,255,255,.05);border-bottom-color:rgba(0,102,204,.3);animation:spin 1s linear infinite reverse;}
.spinner-icon{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.4rem;}
@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}

.card-body{padding:0 2rem 2rem;}
.title{font-size:1.2rem;font-weight:bold;color:#fff;margin-bottom:.5rem;}
.subtitle{font-size:.875rem;color:rgba(255,255,255,.5);line-height:1.6;margin-bottom:1.5rem;}

.tech-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(26,107,60,.15);border:1px solid rgba(126,255,160,.2);border-radius:999px;padding:.4rem 1rem;margin-bottom:1.5rem;}
.tech-dot{width:7px;height:7px;border-radius:50%;background:#7effa0;animation:pulse 2s infinite;}
.tech-label{font-size:12px;color:#7effa0;font-weight:bold;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}

.info-row{display:flex;align-items:center;justify-content:center;gap:.5rem;font-size:11px;color:rgba(255,255,255,.25);margin-bottom:.35rem;}

.card-foot{padding:.85rem 2rem;border-top:1px solid rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;}
.foot-text{font-size:10px;color:rgba(255,255,255,.2);letter-spacing:.04em;}
</style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
    <div class="card">
        <div class="top-bar"></div>
        <div class="logo-wrap">
            <div class="logo-box">ROCK</div>
            <div class="spinner-wrap">
                <div class="spinner"></div>
                <div class="spinner-inner"></div>
                <div class="spinner-icon">🛠</div>
            </div>
        </div>
        <div class="card-body">
            <div class="title">Maintenance in Progress</div>
            <div class="subtitle">{{ $message }}</div>
            <div class="tech-badge">
                <span class="tech-dot"></span>
                <span class="tech-label">Support Technician Connected</span>
            </div>
            <div class="info-row">🔒 Secure support session active</div>
            <div class="info-row">⏱ This page refreshes automatically</div>
        </div>
        <div class="card-foot">
            <span class="foot-text">{{ \App\Helpers\RaynetSetting::groupName() }} · Powered by ROCK</span>
        </div>
    </div>
</div>
</body>
</html>
