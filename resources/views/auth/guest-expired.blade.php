@extends('layouts.app')
@section('title', 'Access Expired')
@section('content')
<style>
:root{--navy:#003366;--navy2:#00234a;--red:#C8102E;--white:#ffffff;--grey:#f4f5f7;--border:#e1e5ec;--text:#1a2332;--muted:#6b7a90;}
body{background:var(--grey);}
.exp-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:32px 16px;}
.exp-card{background:var(--white);border:1px solid var(--border);border-top:4px solid var(--red);max-width:520px;width:100%;padding:0;overflow:hidden;}
.exp-head{background:var(--navy2);padding:20px 32px;display:flex;align-items:center;gap:14px;}
.exp-logo{background:var(--red);width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;color:#fff;letter-spacing:.06em;text-align:center;line-height:1.2;text-transform:uppercase;flex-shrink:0;}
.exp-head-title{font-size:14px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.exp-head-sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.1em;text-transform:uppercase;margin-top:2px;}
.exp-body{padding:36px 32px;}
.exp-icon{font-size:48px;margin-bottom:16px;display:block;text-align:center;}
.exp-title{font-size:22px;font-weight:bold;color:var(--navy);text-align:center;margin-bottom:10px;}
.exp-msg{font-size:14px;color:var(--muted);text-align:center;line-height:1.7;margin-bottom:28px;}
.exp-box{background:#fdf0f2;border:1px solid rgba(200,16,46,.2);border-left:3px solid var(--red);padding:14px 18px;font-size:13px;color:#7a1224;line-height:1.6;margin-bottom:28px;}
.exp-box strong{color:var(--red);}
.exp-actions{display:flex;flex-direction:column;gap:10px;}
.exp-btn{display:block;text-align:center;padding:12px 20px;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;text-decoration:none;transition:all .15s;border:none;cursor:pointer;font-family:inherit;}
.exp-btn-primary{background:var(--navy);color:#fff;}
.exp-btn-primary:hover{background:var(--navy2);color:#fff;}
.exp-btn-ghost{background:var(--grey);color:var(--text);border:1px solid var(--border);}
.exp-btn-ghost:hover{border-color:var(--navy);color:var(--navy);}
.exp-foot{background:var(--grey);border-top:1px solid var(--border);padding:14px 32px;text-align:center;font-size:11px;color:var(--muted);}
</style>
<div class="exp-wrap">
    <div class="exp-card">
        <div class="exp-head">
            <div class="exp-logo">RAY<br>NET</div>
            <div>
                <div class="exp-head-title">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="exp-head-sub">Members' Area</div>
            </div>
        </div>
        <div class="exp-body">
            <span class="exp-icon">⏱</span>
            <div class="exp-title">Your Guest Access Has Expired</div>
            <div class="exp-msg">
                Your temporary guest account has reached its expiry time and your session has been ended automatically.
            </div>
            <div class="exp-box">
                <strong>Need more time?</strong><br>
                Please contact a {{ \App\Helpers\RaynetSetting::groupName() }} administrator if you require continued access to the members' area.
            </div>
            <div class="exp-actions">
                <a href="{{ route('home') }}" class="exp-btn exp-btn-primary">Return to Home Page</a>
                <a href="{{ route('request-support') }}" class="exp-btn exp-btn-ghost">Contact the Group</a>
            </div>
        </div>
        <div class="exp-foot">
            RAYNET — Radio Amateurs' Emergency Network &nbsp;·&nbsp; Voluntary communications support across the UK
        </div>
    </div>
</div>
@endsection
