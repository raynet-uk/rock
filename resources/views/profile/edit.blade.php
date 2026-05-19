@extends('layouts.app')
@section('title', 'My Profile')
@section('content')

@php
    $userName        = $user->name;
    $userEmail       = $user->email;
    $userCallsign    = $user->callsign;
    $userDmrId       = $user->dmr_id;
    $pendingCallsign = $user->pending_callsign;
    $userLicence     = $user->licence_class;
    $userRole        = $user->role;
    $userLevel       = $user->level;
    $userStatus      = $user->status;
    $userPhone       = $user->phone;
    $userJoined      = $user->joined_at;
    $userNotes       = $user->notes;
    $isOperator      = !empty($userRole);
    $levelLabels = [1=>'Operator',2=>'Advanced Operator',3=>'Specialist',4=>'Team Leader',5=>'Instructor'];
    $levelLabel  = $userLevel !== null ? ($levelLabels[$userLevel] ?? 'Level '.$userLevel) : null;
    $statusColours = [
        'Active'    => ['dot'=>'#22d47d','glow'=>'rgba(34,212,125,.5)','bg'=>'rgba(34,212,125,.08)','border'=>'rgba(34,212,125,.3)','text'=>'#086c3a'],
        'Standby'   => ['dot'=>'#fbbf24','glow'=>'rgba(251,191,36,.4)','bg'=>'rgba(251,191,36,.08)','border'=>'rgba(251,191,36,.3)','text'=>'#7a5000'],
        'Inactive'  => ['dot'=>'#64748b','glow'=>'none','bg'=>'rgba(100,116,139,.08)','border'=>'rgba(100,116,139,.3)','text'=>'#4a5568'],
        'Suspended' => ['dot'=>'#f87171','glow'=>'rgba(248,113,113,.4)','bg'=>'rgba(248,113,113,.08)','border'=>'rgba(248,113,113,.3)','text'=>'#c0392b'],
    ];
    $sc = $statusColours[$userStatus ?? ''] ?? null;
    $licenceConfig = [
        'Foundation'   => ['bg'=>'#fdf8ec','border'=>'#f5d87a','text'=>'#8a5500','label'=>'Foundation Licence','desc'=>'Entry-level amateur licence','dot'=>'#c49a00','icon'=>'📻','slug'=>'foundation'],
        'Intermediate' => ['bg'=>'#e8eef5','border'=>'rgba(0,51,102,.3)','text'=>'#003366','label'=>'Intermediate Licence','desc'=>'Intermediate amateur licence','dot'=>'#003366','icon'=>'🎛️','slug'=>'intermediate'],
        'Full'         => ['bg'=>'#eef7f2','border'=>'#b8ddc9','text'=>'#1a6b3c','label'=>'Full Licence','desc'=>'Full amateur licence holder','dot'=>'#1a6b3c','icon'=>'📡','slug'=>'full'],
    ];
    $lc = $userLicence ? ($licenceConfig[$userLicence] ?? null) : null;
    $initials = collect(explode(' ',$userName))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $openTab  = $errors->any() ? 'profile' : (session('_tab') ?? 'profile');
    $validTabs = ['profile','radio','operator','training'];
    if (!in_array($openTab,$validTabs)) $openTab = 'profile';
@endphp

<style>
:root{
    --navy:#003366;--navy-mid:#004080;--navy-faint:#e8eef5;
    --red:#C8102E;--red-faint:#fdf0f2;
    --teal:#0288d1;
    --green:#1a6b3c;--green-bg:#eef7f2;
    --amber:#8a5500;--amber-bg:#fdf8ec;
    --purple:#5b21b6;
    --grey:#dde2e8;--light:#f2f5f9;--white:#fff;
    --text:#001f40;--text-mid:#2d4a6b;--muted:#6b7f96;
    --shadow-sm:0 2px 8px rgba(0,51,102,.07);
    --shadow-md:0 6px 20px rgba(0,51,102,.12);
    --transition:all .2s ease;
    --font:Arial,"Helvetica Neue",Helvetica,sans-serif;
    --tab-h:46px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--light);color:var(--text);font-family:var(--font);font-size:15px;line-height:1.55;min-height:100vh;}
.wrap{max-width:1180px;margin:0 auto;padding:0 0 3rem;}

/* guest banner */
.guest-banner{position:sticky;top:60px;z-index:300;background:linear-gradient(90deg,#92400e,#b45309);border-bottom:2px solid #d97706;padding:.5rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.guest-banner-left{display:flex;align-items:center;gap:.65rem;}
.guest-banner-icon{font-size:14px;flex-shrink:0;}
.guest-banner-text{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.guest-banner-sub{font-size:10px;color:rgba(255,255,255,.7);margin-top:1px;}
.guest-banner-right{display:flex;align-items:center;gap:.5rem;flex-shrink:0;}
.guest-countdown{font-size:12px;font-weight:bold;color:#fff;font-family:monospace;background:rgba(0,0,0,.2);padding:.25rem .65rem;border:1px solid rgba(255,255,255,.2);}
.guest-banner-badge{font-size:10px;font-weight:bold;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;text-transform:uppercase;letter-spacing:.05em;}
/* guest banner */
.guest-banner{position:sticky;top:60px;z-index:300;background:linear-gradient(90deg,#92400e,#b45309);border-bottom:2px solid #d97706;padding:.45rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.guest-banner-left{display:flex;align-items:center;gap:.65rem;}
.guest-banner-text{font-size:11px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;}
.guest-banner-sub{font-size:10px;color:rgba(255,255,255,.75);margin-top:1px;}
.guest-countdown{font-size:12px;font-weight:bold;color:#fff;font-family:monospace;background:rgba(0,0,0,.25);padding:.22rem .65rem;border:1px solid rgba(255,255,255,.2);}
.guest-banner-badge{font-size:10px;font-weight:bold;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;text-transform:uppercase;letter-spacing:.05em;}
/* topbar */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:2px solid var(--navy);gap:1rem;flex-wrap:wrap;}
.brand{display:flex;align-items:center;gap:.8rem;}
.brand-badge{width:40px;height:40px;background:var(--navy);color:white;display:flex;align-items:center;justify-content:center;font-size:1.4rem;border-radius:8px;}
.brand-name{font-size:1.25rem;font-weight:bold;color:var(--navy);}
.brand-sub{font-size:.8rem;color:var(--muted);}
.back-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border:1px solid var(--grey);border-radius:8px;background:white;color:var(--muted);font-size:.88rem;text-decoration:none;transition:var(--transition);}
.back-btn:hover{border-color:var(--navy);color:var(--navy);}

/* hero */
.profile-hero{background:var(--navy);padding:1.4rem 1.25rem 0;position:relative;overflow:hidden;}
.profile-hero::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 20px,rgba(255,255,255,.02) 20px,rgba(255,255,255,.02) 21px);}
.hero-inner{position:relative;z-index:1;}
.hero-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.hero-brand{display:flex;align-items:center;gap:.55rem;}
.hero-brand-name{font-size:.72rem;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:.08em;text-transform:uppercase;}
.hero-badge{font-size:.66rem;color:rgba(255,255,255,.4);border:1px solid rgba(255,255,255,.18);border-radius:999px;padding:.16rem .6rem;letter-spacing:.05em;}
.hero-body{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding-bottom:1.2rem;}
.hero-avatar{width:54px;height:54px;border-radius:50%;background:var(--red);border:2px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:bold;color:#fff;flex-shrink:0;}
.hero-name{font-size:1.25rem;font-weight:bold;color:#fff;line-height:1.2;margin-bottom:.3rem;}
.hero-chips{display:flex;align-items:center;gap:.38rem;flex-wrap:wrap;}
.chip-callsign{font-family:monospace;font-size:.75rem;font-weight:bold;color:#fff;background:rgba(2,136,209,.45);border:1px solid rgba(2,136,209,.55);border-radius:5px;padding:.1rem .45rem;letter-spacing:.08em;}
.chip-role{font-size:.65rem;font-weight:bold;color:#ffb3be;background:rgba(200,16,46,.28);border:1px solid rgba(200,16,46,.4);border-radius:4px;padding:.1rem .45rem;text-transform:uppercase;letter-spacing:.05em;}
.chip-level{font-size:.65rem;color:rgba(255,255,255,.7);background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:4px;padding:.1rem .45rem;}
.hero-status{display:flex;align-items:center;gap:.32rem;font-size:.7rem;color:rgba(255,255,255,.5);}
.hero-sdot{width:6px;height:6px;border-radius:50%;}

/* sticky tab bar */
.tab-bar{
    position:sticky;top:60px;z-index:200;
    display:flex;
    background:#fff;
    border-bottom:2px solid var(--grey);
    box-shadow:0 2px 10px rgba(0,51,102,.1);
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
    height:var(--tab-h);
}
.tab-bar::-webkit-scrollbar{display:none;}
.tab-btn{
    flex:1;min-width:80px;
    display:flex;align-items:center;justify-content:center;gap:.38rem;
    height:var(--tab-h);padding:0 .75rem;
    border:none;border-bottom:3px solid transparent;
    background:none;font-family:var(--font);
    font-size:.82rem;font-weight:bold;color:var(--muted);
    cursor:pointer;white-space:nowrap;
    transition:color .15s,border-color .15s;
    position:relative;
}
.tab-btn:hover{color:var(--navy);}
.tab-btn.active{color:var(--navy);border-bottom-color:var(--red);}
.tab-btn .ti{font-size:.88rem;}
.tab-err-dot{width:6px;height:6px;border-radius:50%;background:var(--red);position:absolute;top:8px;right:10px;}

/* alerts */
.alerts-wrap{padding:.85rem 1.25rem 0;}
.toast-ok,.toast-err{display:flex;align-items:flex-start;gap:.75rem;padding:.65rem .95rem;font-size:.86rem;font-weight:bold;border:1px solid;border-left:3px solid;border-radius:0 8px 8px 0;margin-bottom:.7rem;}
.toast-ok{background:var(--green-bg);border-color:#b8ddc9;border-left-color:var(--green);color:var(--green);}
.toast-err{background:var(--red-faint);border-color:rgba(200,16,46,.25);border-left-color:var(--red);color:var(--red);}
.toast-err ul{margin:.35rem 0 0 1.1rem;list-style:disc;}
.toast-err li{margin:.2rem 0;font-weight:normal;}

/* page grid */
.page-layout{display:grid;grid-template-columns:1fr;gap:1.5rem;padding:1.15rem 1.25rem 0;}
@media(min-width:820px){.page-layout{grid-template-columns:1fr 278px;}}

/* tab panes */
.tab-pane{display:none;}
.tab-pane.active{display:block;animation:tabIn .17s ease;}
@keyframes tabIn{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:translateY(0);}}

/* cards */
.card{background:white;border:1px solid var(--grey);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:1.1rem;}
.card:last-child{margin-bottom:0;}
.card-head{display:flex;align-items:center;gap:.7rem;padding:.65rem 1.1rem;background:var(--light);border-bottom:1px solid var(--grey);}
.card-head-icon{width:29px;height:29px;border-radius:7px;background:var(--navy-faint);border:1px solid rgba(0,51,102,.15);display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;}
.card-head h2{font-size:.68rem;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.1em;}
.card-head p{font-size:.73rem;color:var(--muted);margin-top:.06rem;}
.card-body{padding:1.1rem;}

/* section dividers inside card */
.sec-div{display:flex;align-items:center;gap:.5rem;margin:1.25rem 0 .9rem;}
.sec-div-label{font-size:.6rem;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--muted);white-space:nowrap;}
.sec-div::before{content:'';width:10px;height:2px;background:var(--red);flex-shrink:0;}
.sec-div::after{content:'';flex:1;height:1px;background:var(--grey);}

/* form fields */
.field{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.95rem;}
.field:last-of-type{margin-bottom:0;}
.field label{font-size:.66rem;font-weight:bold;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;}
.field-note{font-size:.72rem;color:var(--muted);margin-top:.12rem;}
.field-note.warn{color:var(--amber);}
.input-wrap{position:relative;}
.input-icon{position:absolute;left:.78rem;top:50%;transform:translateY(-50%);font-size:.86rem;color:var(--muted);pointer-events:none;}
.field input{width:100%;padding:.58rem .78rem .58rem 2rem;border:1.5px solid var(--grey);border-radius:8px;font-size:.9rem;background:white;color:var(--text);transition:var(--transition);font-family:var(--font);}
.field input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(2,136,209,.1);outline:none;}
.field input:disabled{background:var(--light);color:var(--muted);cursor:not-allowed;}
#callsign{font-family:monospace;text-transform:uppercase;letter-spacing:.05em;}
#dmr_id{font-family:monospace;letter-spacing:.05em;}
.approved-tag{display:inline-flex;align-items:center;gap:.3rem;padding:.14rem .5rem;background:var(--green-bg);border:1px solid #b8ddc9;border-radius:5px;font-size:.76rem;font-weight:bold;color:var(--green);margin-bottom:.3rem;}
.approved-tag span{font-size:.7rem;color:var(--muted);font-weight:normal;}
.pending-banner{display:flex;align-items:flex-start;gap:.42rem;padding:.52rem .78rem;background:var(--amber-bg);border:1px solid #f5d87a;border-left:3px solid #c49a00;border-radius:0 6px 6px 0;margin-top:.3rem;font-size:.78rem;color:var(--amber);}
#callsign.cs-valid{border-color:#16a34a!important;box-shadow:0 0 0 3px rgba(22,163,74,.1)!important;}
#callsign.cs-invalid{border-color:#C8102E!important;box-shadow:0 0 0 3px rgba(200,16,46,.08)!important;}
.cs-feedback{display:none;align-items:center;gap:.38rem;font-size:.68rem;font-weight:bold;margin-top:.18rem;padding:.26rem .52rem;border:1px solid;border-radius:5px;}
.cs-feedback.show{display:flex;}
.cs-feedback.ok{background:var(--green-bg);border-color:#b8ddc9;color:var(--green);}
.cs-feedback.err{background:var(--red-faint);border-color:rgba(200,16,46,.2);color:var(--red);}
.cs-help{font-size:.67rem;color:var(--muted);margin-top:.18rem;line-height:1.5;}
.cs-help a{color:var(--navy);font-weight:bold;text-decoration:none;}
.cs-help a:hover{text-decoration:underline;}
@keyframes qrzSpin{to{transform:rotate(360deg);}}
.form-actions{display:flex;align-items:center;gap:.9rem;flex-wrap:wrap;margin-top:1rem;padding-top:.9rem;border-top:1px solid var(--grey);}
.btn-save{padding:.52rem 1.3rem;border:none;border-radius:8px;background:var(--navy);color:white;font-size:.86rem;font-weight:bold;cursor:pointer;font-family:var(--font);letter-spacing:.04em;transition:var(--transition);}
.btn-save:hover{background:var(--navy-mid);transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,51,102,.2);}
.pwd-link{font-size:.86rem;color:var(--red);text-decoration:none;font-weight:bold;}
.pwd-link:hover{text-decoration:underline;}
.avf-btn{padding:3px 9px;font-size:10px;font-weight:bold;font-family:var(--font);border:1px solid var(--grey);background:var(--white);color:var(--muted);cursor:pointer;border-radius:4px;transition:all .12s;letter-spacing:.04em;text-transform:uppercase;}
.avf-btn:hover{border-color:var(--navy);color:var(--navy);}
.avf-active{background:var(--navy);border-color:var(--navy);color:#fff!important;}

/* QRZ photo prompt */
.qrz-photo-prompt{border-radius:10px;overflow:hidden;margin-top:.75rem;animation:tabIn .2s ease;}
.qrz-photo-prompt-head{padding:.65rem .95rem;display:flex;align-items:center;gap:.75rem;}
.qrz-photo-prompt-thumb{width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0;}
.qrz-photo-prompt-body{flex:1;min-width:0;}
.qrz-photo-prompt-title{font-size:.82rem;font-weight:bold;margin-bottom:.18rem;}
.qrz-photo-prompt-desc{font-size:.72rem;color:var(--text-mid);line-height:1.45;}
.qrz-photo-prompt-actions{padding:.55rem .95rem;display:flex;align-items:center;gap:.5rem;border-top-width:1px;border-top-style:solid;}
.qrz-photo-dismiss-btn{font-size:.78rem;padding:.38rem .9rem;border:1px solid var(--grey);border-radius:8px;background:none;color:var(--muted);cursor:pointer;font-family:var(--font);transition:var(--transition);}
.qrz-photo-dismiss-btn:hover{border-color:var(--navy);color:var(--navy);}
.qrz-photo-importing{display:none;align-items:center;gap:.5rem;font-size:.78rem;font-weight:bold;padding:.55rem .95rem;color:var(--muted);}
.qrz-photo-importing span.spin{display:inline-block;width:13px;height:13px;border:2px solid var(--grey);border-top-color:var(--navy);border-radius:50%;animation:qrzSpin .7s linear infinite;flex-shrink:0;}

/* licence */
.licence-block{display:flex;align-items:center;gap:.8rem;padding:.82rem .95rem;border:1px solid;border-left-width:3px;border-radius:8px;}
.lic-icon{width:31px;height:31px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;background:white;border:1px solid;border-radius:6px;}
.lic-info{flex:1;min-width:0;}
.lic-name{font-size:.84rem;font-weight:bold;}
.lic-desc{font-size:.71rem;opacity:.7;margin-top:1px;}
.lic-pill{font-size:.64rem;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;padding:.14rem .48rem;border:1px solid;border-radius:4px;flex-shrink:0;}

/* DMR */
.dmr-panel{margin-top:.82rem;background:var(--navy);border-radius:8px;overflow:hidden;}
.dmr-panel-top{display:flex;align-items:center;justify-content:space-between;padding:.82rem 1.05rem;}
.dmr-label{font-size:.6rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-bottom:2px;}
.dmr-value{font-family:monospace;font-size:1.05rem;font-weight:bold;color:white;letter-spacing:.08em;}
.dmr-live-strip{border-top:1px solid rgba(255,255,255,.08);padding:.72rem 1.05rem;display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
.dmr-live-block{display:flex;flex-direction:column;gap:3px;}
.dmr-live-label{font-size:.57rem;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3);}
.dmr-live-value{font-size:.84rem;font-weight:bold;color:white;display:flex;align-items:center;gap:.38rem;}
.dmr-live-tg{display:inline-flex;align-items:center;background:rgba(91,33,182,.4);border:1px solid rgba(91,33,182,.5);border-radius:4px;padding:.1rem .46rem;font-size:.74rem;font-weight:bold;color:#c4b5fd;font-family:monospace;letter-spacing:.04em;}
.dmr-heard-row{display:flex;align-items:center;gap:.32rem;}
.dmr-heard-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.dmr-heard-dot.active{background:#22d47d;box-shadow:0 0 0 3px rgba(34,212,125,.25);animation:dmrPulse 2s ease infinite;}
.dmr-heard-dot.recent{background:#fbbf24;}
.dmr-heard-dot.stale{background:rgba(255,255,255,.2);}
.dmr-heard-text{font-size:.8rem;font-weight:bold;color:rgba(255,255,255,.7);}
@keyframes dmrPulse{0%,100%{box-shadow:0 0 0 3px rgba(34,212,125,.25);}50%{box-shadow:0 0 0 6px rgba(34,212,125,.08);}}
.dmr-loading-shimmer{height:13px;background:rgba(255,255,255,.06);border-radius:4px;width:70%;animation:shimmer 1.5s ease infinite;}
@keyframes shimmer{0%,100%{opacity:.4;}50%{opacity:1;}}
.dmr-live-footer{padding:.46rem 1.05rem;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;font-size:.61rem;color:rgba(255,255,255,.25);}
.dmr-live-footer a{color:rgba(255,255,255,.4);text-decoration:none;border-bottom:1px solid rgba(255,255,255,.15);transition:color .15s;}
.dmr-live-footer a:hover{color:rgba(255,255,255,.7);}
.info-note{padding:.48rem .78rem;font-size:.72rem;color:var(--navy);background:var(--navy-faint);border:1px solid rgba(0,51,102,.18);border-left:3px solid var(--navy);border-radius:0 6px 6px 0;margin-top:.82rem;}

/* operator */
.status-banner{display:flex;align-items:center;gap:.58rem;padding:.58rem .85rem;margin-bottom:.95rem;font-size:.86rem;border:1px solid;border-left-width:3px;border-radius:0 8px 8px 0;}
.sbdot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.op-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;}
.op-tile{background:var(--light);border:1px solid var(--grey);border-radius:8px;padding:.68rem .9rem;}
.op-tile-label{font-size:.61rem;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);margin-bottom:.18rem;}
.op-tile-value{font-size:.9rem;font-weight:bold;color:var(--navy);}
.op-tile-sub{font-size:.68rem;color:var(--muted);margin-top:2px;}
.level-bar-wrap{margin-top:.75rem;padding:.75rem .9rem;background:var(--light);border:1px solid var(--grey);border-radius:8px;}
.level-bar-header{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.42rem;}
.level-bar-title{font-size:.62rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);}
.level-bar-value{font-size:.78rem;font-weight:bold;color:var(--navy);}
.level-bar-track{height:5px;background:var(--grey);border-radius:999px;overflow:hidden;}
.level-bar-fill{height:100%;background:var(--navy);border-radius:999px;transition:width .6s ease;}
.notes-block{margin-top:.75rem;padding:.72rem .9rem;background:var(--light);border:1px solid var(--grey);border-left:3px solid var(--navy);border-radius:0 8px 8px 0;}
.notes-label{font-size:.61rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.25rem;}
.no-op-notice{padding:2rem;text-align:center;color:var(--muted);font-size:.9rem;}

/* training */
.training-section-label{font-size:.61rem;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--muted);display:flex;align-items:center;gap:.46rem;margin-bottom:.75rem;margin-top:.95rem;}
.training-section-label:first-of-type{margin-top:0;}
.training-section-label::before{content:'';width:10px;height:2px;background:var(--red);display:inline-block;flex-shrink:0;}
.training-section-label::after{content:'';flex:1;height:1px;background:var(--grey);display:inline-block;}
.hex-row{display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:.3rem;}
.hex-wrap{display:flex;flex-direction:column;align-items:center;gap:.32rem;width:66px;position:relative;}
.hex{position:relative;width:52px;height:52px;cursor:default;}
.hex svg{width:52px;height:52px;display:block;}
.hex-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:bold;z-index:1;transition:var(--transition);}
.hex-label{font-size:.56rem;font-weight:bold;text-align:center;line-height:1.3;color:var(--muted);max-width:66px;text-transform:uppercase;letter-spacing:.04em;}
.hex-wrap.locked   .hex-num{color:rgba(0,0,0,.18);}
.hex-wrap.locked   .hex-label{color:var(--grey);}
.hex-wrap.unlocked .hex-num{color:#fff;}
.hex-wrap.unlocked .hex-label{color:var(--text-mid);font-weight:bold;}
.hex-wrap.unlocked .hex{filter:drop-shadow(0 3px 8px rgba(0,0,0,.18));}
.hex-tooltip{display:none;position:absolute;bottom:calc(100% + 7px);left:50%;transform:translateX(-50%);background:var(--navy);color:#fff;font-size:.66rem;font-weight:600;padding:.28rem .58rem;white-space:nowrap;z-index:20;box-shadow:var(--shadow-md);pointer-events:none;line-height:1.4;text-align:center;}
.hex-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:var(--navy);}
.hex-wrap:hover .hex-tooltip{display:block;}
.training-progress-strip{display:flex;align-items:center;gap:.7rem;padding:.58rem .82rem;background:var(--light);border:1px solid var(--grey);margin-bottom:.85rem;}
.tps-label{font-size:.61rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);flex-shrink:0;}
.tps-track{flex:1;height:5px;background:var(--grey);overflow:hidden;}
.tps-fill{height:100%;background:var(--navy);transition:width .6s ease;}
.tps-count{font-size:.68rem;font-weight:bold;color:var(--navy);flex-shrink:0;font-family:monospace;}
.hex-legend{display:flex;align-items:center;gap:.9rem;margin-top:.65rem;padding-top:.58rem;border-top:1px solid var(--grey);flex-wrap:wrap;}
.hex-legend-item{display:flex;align-items:center;gap:.28rem;font-size:.65rem;color:var(--muted);}
.hex-legend-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0;}

/* sidebar (desktop only) */
.side-col{display:none;}
@media(min-width:820px){.side-col{display:block;}}
.snap-card{background:white;border:1px solid var(--grey);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-md);position:sticky;top:calc(60px + var(--tab-h) + 1.15rem);}
.snap-tabs{display:flex;border-bottom:1px solid var(--grey);}
.snap-tab-btn{flex:1;padding:.46rem .2rem;border:none;background:none;font-family:var(--font);font-size:.58rem;font-weight:bold;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;transition:all .12s;text-transform:uppercase;letter-spacing:.07em;}
.snap-tab-btn.active{color:var(--navy);border-bottom-color:var(--red);}
.snap-tab-btn:hover{color:var(--navy);}
.snap-header{background:var(--navy);padding:1.25rem .95rem 1.05rem;display:flex;flex-direction:column;align-items:center;gap:.46rem;position:relative;overflow:hidden;}
.snap-header::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 18px,rgba(255,255,255,.02) 18px,rgba(255,255,255,.02) 19px);}
.snap-header::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--red);}
.snap-avatar{width:52px;height:52px;background:var(--red);border-radius:50%;border:3px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:bold;color:#fff;position:relative;z-index:1;}
.snap-name{font-size:.9rem;font-weight:bold;color:#fff;text-align:center;position:relative;z-index:1;}
.snap-callsign{font-family:monospace;font-size:.77rem;font-weight:bold;color:#fff;background:rgba(2,136,209,.4);border:1px solid rgba(2,136,209,.5);border-radius:4px;padding:.12rem .46rem;letter-spacing:.08em;position:relative;z-index:1;}
.snap-lic{font-size:.63rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;padding:.14rem .5rem;border:1px solid;border-radius:4px;position:relative;z-index:1;}
.snap-lic-foundation{background:var(--amber-bg);border-color:#f5d87a;color:var(--amber);}
.snap-lic-intermediate{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);color:rgba(255,255,255,.85);}
.snap-lic-full{background:var(--green-bg);border-color:#b8ddc9;color:var(--green);}
.snap-role{font-size:.63rem;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;padding:.14rem .5rem;background:rgba(200,16,46,.28);border:1px solid rgba(200,16,46,.4);border-radius:4px;color:#ffb3be;position:relative;z-index:1;}
.snap-level{font-size:.63rem;color:rgba(255,255,255,.65);background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:4px;padding:.14rem .5rem;position:relative;z-index:1;}
.snap-status-row{display:flex;align-items:center;gap:.35rem;font-size:.68rem;color:rgba(255,255,255,.5);position:relative;z-index:1;}
.snap-sdot{width:6px;height:6px;border-radius:50%;}
.snap-dmr-live{background:rgba(0,0,0,.2);margin:.42rem .68rem .18rem;border-radius:7px;overflow:hidden;position:relative;z-index:1;}
.snap-dmr-live-inner{padding:.52rem .76rem;display:grid;grid-template-columns:1fr 1fr;gap:.4rem;}
.snap-dmr-live-label{font-size:.52rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);margin-bottom:1px;}
.snap-dmr-live-val{font-size:.75rem;font-weight:bold;color:white;display:flex;align-items:center;gap:.26rem;}
.snap-dmr-tg{background:rgba(91,33,182,.4);border:1px solid rgba(91,33,182,.5);border-radius:3px;padding:.07rem .36rem;font-size:.67rem;font-weight:bold;color:#c4b5fd;font-family:monospace;}
.snap-dmr-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.snap-dmr-dot.active{background:#22d47d;box-shadow:0 0 0 2px rgba(34,212,125,.25);animation:dmrPulse 2s ease infinite;}
.snap-dmr-dot.recent{background:#fbbf24;}
.snap-dmr-dot.stale{background:rgba(255,255,255,.2);}
.snap-dmr-loading{height:8px;background:rgba(255,255,255,.06);border-radius:3px;animation:shimmer 1.5s ease infinite;}
.snap-row{display:flex;justify-content:space-between;align-items:baseline;padding:.48rem .9rem;border-bottom:1px solid var(--grey);}
.snap-row:last-child{border-bottom:none;}
.snap-dt{font-size:.6rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);}
.snap-dd{font-size:.8rem;font-weight:bold;text-align:right;}
.snap-dd.mono{font-family:monospace;letter-spacing:.05em;}
.snap-dd.muted{color:var(--muted);font-weight:normal;}
.snap-dd.amber{color:var(--amber);}
.snap-foot{padding:.65rem .9rem;background:var(--light);border-top:1px solid var(--grey);font-size:.68rem;color:var(--muted);text-align:center;line-height:1.5;}
</style>

<div class="wrap">

  <nav class="topbar">
    <div class="brand">
      <div class="brand-badge">📻</div>
      <div>
        <div class="brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
        <div class="brand-sub">members' portal</div>
      </div>
    </div>
    <a href="{{ route('members') }}" class="back-btn">← Back to hub</a>
  </nav>

@if(auth()->user()->hasRole('temporary_guest') || auth()->user()->guest_expires_at)
<div class="guest-banner">
    <div class="guest-banner-left">
        <span style="font-size:16px;flex-shrink:0;">⏱</span>
        <div>
            <div class="guest-banner-text">Temporary Guest Access</div>
            <div class="guest-banner-sub">
                @if(auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isFuture())
                    Expires {{ auth()->user()->guest_expires_at->format('d M Y \a\t H:i') }}
                @elseif(auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isPast())
                    Access expired {{ auth()->user()->guest_expires_at->diffForHumans() }}
                @else
                    No expiry set — contact your administrator for details
                @endif
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
        @if(auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isFuture())
            <div class="guest-countdown" id="guestCountdown">--:--:--</div>
        @endif
        <span class="guest-banner-badge">⏱ Guest</span>
    </div>
</div>
@endif

  <div class="profile-hero">
    <div class="hero-inner">
      <div class="hero-top">
        <div class="hero-brand">
          <span style="font-size:.95rem;">📻</span>
          <span class="hero-brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</span>
        </div>
        <span class="hero-badge">Member Record</span>
      </div>
      <div class="hero-body">
        @if($user->hasRole("temporary_guest") || $user->guest_expires_at)
          <div class="hero-avatar" style="padding:0;overflow:hidden;background:transparent;border:2px solid rgba(180,83,9,.4);">
            <img src="{{ Storage::url('avatars/TempAvatar.png') }}" style="width:100%;height:100%;object-fit:cover;" alt="Temporary Guest">
          </div>
        @elseif($user->avatar)
          <div class="hero-avatar" style="padding:0;overflow:hidden;background:transparent;">
            <img src="{{ Storage::url($user->avatar) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
          </div>
        @else
          <div class="hero-avatar">{{ $initials ?: '?' }}</div>
        @endif
        <div>
          <div class="hero-name">{{ $userName }}</div>
          <div class="hero-chips">
            @if($userCallsign)<span class="chip-callsign">{{ strtoupper($userCallsign) }}</span>@endif
            @if($isOperator && $userRole)<span class="chip-role">{{ $userRole }}</span>@endif
            @if($userLevel !== null)<span class="chip-level">L{{ $userLevel }} · {{ $levelLabel }}</span>@endif
            @if($userStatus && $sc)
              <div class="hero-status">
                <div class="hero-sdot" style="background:{{ $sc['dot'] }};box-shadow:0 0 6px {{ $sc['glow'] }};"></div>
                {{ $userStatus }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- STICKY TAB BAR --}}
  <nav class="tab-bar" id="mainTabBar" role="tablist">
    <button class="tab-btn" data-tab="profile" onclick="switchTab('profile')" role="tab">
      <span class="ti">👤</span> Profile
      @if($errors->any())<span class="tab-err-dot"></span>@endif
    </button>
    <button class="tab-btn" data-tab="radio" onclick="switchTab('radio')" role="tab">
      <span class="ti">📡</span> Radio
    </button>
    <button class="tab-btn" data-tab="operator" onclick="switchTab('operator')" role="tab">
      <span class="ti">🎖</span> Operator
    </button>
    <button class="tab-btn" data-tab="training" onclick="switchTab('training')" role="tab">
      <span class="ti">🏅</span> Training
    </button>
  </nav>

  @if(session('status') || $errors->any())
  <div class="alerts-wrap">
    @if(session('status'))<div class="toast-ok">✓ {{ session('status') }}</div>@endif
    @if($errors->any())
      <div class="toast-err">
        <div><strong>⚠ Please fix the following:</strong>
          <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      </div>
    @endif
  </div>
  @endif

  <div class="page-layout">

    {{-- MAIN --}}
    <div>

      {{-- ── PROFILE TAB ── --}}
      <div class="tab-pane" id="tab-profile" role="tabpanel">
        <div class="card">
          <div class="card-head">
            <div class="card-head-icon">👤</div>
            <div><h2>Edit Profile</h2><p>Photo, personal details and radio identifiers.</p></div>
          </div>
          <div class="card-body">

            <div class="sec-div"><span class="sec-div-label">Profile Photo</span></div>

            {{-- Current avatar display --}}
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.95rem;">
              @if($user->hasRole("temporary_guest") || $user->guest_expires_at)
                <img src="{{ Storage::url('avatars/TempAvatar.png') }}" style="width:62px;height:62px;border-radius:50%;object-fit:cover;border:3px solid rgba(180,83,9,.4);flex-shrink:0;" alt="Temporary Guest">
              @elseif($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" style="width:62px;height:62px;border-radius:50%;object-fit:cover;border:3px solid var(--grey);flex-shrink:0;" alt="">
                <div>
                  <div style="font-size:.81rem;font-weight:bold;color:var(--text);margin-bottom:.28rem;">Current photo</div>
                  <form method="POST" action="{{ route('profile.avatar.destroy') }}" onsubmit="return confirm('Remove your profile photo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-save" style="background:var(--red);font-size:.76rem;padding:.35rem .85rem;">Remove photo</button>
                  </form>
                </div>
              @else
                <div style="width:62px;height:62px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:bold;color:#fff;flex-shrink:0;border:3px solid var(--grey);">{{ $initials ?: '?' }}</div>
                <div style="font-size:.78rem;color:var(--muted);">No photo set — upload one below.</div>
              @endif
            </div>

            {{-- ── QRZ PHOTO IMPORT PROMPT ──
                 Shown by JS once a QRZ lookup returns an image_url.
                 Two variants: one when the user already has an avatar, one when they don't.
            --}}
            <div id="qrzPhotoPrompt" style="display:none;">

              {{-- Variant A: user HAS an existing avatar — ask before replacing --}}
              <div id="qrzPhotoHasExisting" class="qrz-photo-prompt" style="display:none;border:1.5px solid rgba(0,51,102,.28);">
                <div class="qrz-photo-prompt-head" style="background:var(--navy-faint);">
                  <img id="qrzPhotoThumbA" src="" alt="QRZ photo" class="qrz-photo-prompt-thumb" style="border:2px solid rgba(0,51,102,.2);">
                  <div class="qrz-photo-prompt-body">
                    <div class="qrz-photo-prompt-title" style="color:var(--navy);">📡 QRZ photo available</div>
                    <div class="qrz-photo-prompt-desc">A profile photo was found on QRZ.com for <strong id="qrzPhotoCallsignA" style="font-family:monospace;letter-spacing:.05em;"></strong>. Would you like to use it instead of your current photo?</div>
                  </div>
                </div>
                <div class="qrz-photo-prompt-actions" style="background:white;border-top-color:var(--grey);">
                  <button type="button" onclick="importQrzPhoto()" class="btn-save" style="font-size:.78rem;padding:.38rem .9rem;">Use QRZ photo</button>
                  <button type="button" onclick="dismissQrzPhoto()" class="qrz-photo-dismiss-btn">Keep current</button>
                </div>
                <div class="qrz-photo-importing" id="qrzPhotoImportingA">
                  <span class="spin"></span> Importing photo from QRZ.com…
                </div>
              </div>

              {{-- Variant B: user has NO avatar — green "import" prompt --}}
              <div id="qrzPhotoNoExisting" class="qrz-photo-prompt" style="display:none;border:1.5px solid #b8ddc9;">
                <div class="qrz-photo-prompt-head" style="background:var(--green-bg);">
                  <img id="qrzPhotoThumbB" src="" alt="QRZ photo" class="qrz-photo-prompt-thumb" style="border:2px solid #b8ddc9;">
                  <div class="qrz-photo-prompt-body">
                    <div class="qrz-photo-prompt-title" style="color:var(--green);">📡 Import photo from QRZ.com</div>
                    <div class="qrz-photo-prompt-desc">A profile photo was found for <strong id="qrzPhotoCallsignB" style="font-family:monospace;letter-spacing:.05em;"></strong> on QRZ.com. Would you like to use it as your profile photo?</div>
                  </div>
                </div>
                <div class="qrz-photo-prompt-actions" style="background:white;border-top-color:#b8ddc9;">
                  <button type="button" onclick="importQrzPhoto()" class="btn-save" style="font-size:.78rem;padding:.38rem .9rem;">Import photo</button>
                  <button type="button" onclick="dismissQrzPhoto()" class="qrz-photo-dismiss-btn">No thanks</button>
                </div>
                <div class="qrz-photo-importing" id="qrzPhotoImportingB">
                  <span class="spin"></span> Importing photo from QRZ.com…
                </div>
              </div>

            </div>{{-- /qrzPhotoPrompt --}}

            {{-- Hidden form: submits the QRZ image URL to the backend for server-side fetch + save --}}
            <form id="qrzImportForm" method="POST" action="{{ route('profile.avatar.qrz') }}" style="display:none;">
              @csrf
              <input type="hidden" name="qrz_image_url" id="qrzImportUrl">
            </form>

            {{-- Upload new photo --}}
            <div class="field">
              <label for="avatarRaw">Upload new photo</label>
              <input id="avatarRaw" type="file" accept="image/*"
                style="padding:.48rem .75rem;border:1.5px solid var(--grey);border-radius:8px;font-size:.84rem;width:100%;"
                onchange="avatarOpenCropper(this)">
              @error('avatar')<div class="field-note" style="color:var(--red);">{{ $message }}</div>@enderror
            </div>

            {{-- Crop panel --}}
            <div id="avatarCropperPanel" style="display:none;margin-top:.85rem;border:1.5px solid var(--grey);border-radius:10px;overflow:hidden;background:var(--light);">
              <div style="position:relative;width:100%;height:250px;background:#1a1a2e;overflow:hidden;cursor:grab;" id="cropDragArea">
                <canvas id="cropCanvas" style="display:block;width:100%;height:100%;"></canvas>
                <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;" id="cropMaskSvg">
                  <defs><mask id="circleMask"><rect width="100%" height="100%" fill="white"/><circle id="maskCircle" fill="black"/></mask></defs>
                  <rect width="100%" height="100%" fill="rgba(0,0,0,0.55)" mask="url(#circleMask)"/>
                  <circle id="guideCircle" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2" stroke-dasharray="6,4"/>
                </svg>
              </div>
              <div style="padding:.75rem .95rem;display:flex;flex-direction:column;gap:.65rem;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                  <span style="font-size:.65rem;font-weight:bold;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;min-width:38px;">Zoom</span>
                  <input type="range" id="cropZoom" min="50" max="300" value="100" step="1" style="flex:1;" oninput="avatarRender()">
                  <span id="cropZoomVal" style="font-size:.75rem;font-weight:bold;color:var(--navy);font-family:monospace;min-width:34px;">100%</span>
                </div>
                <div>
                  <div style="font-size:.65rem;font-weight:bold;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.38rem;">Filter preset</div>
                  <div style="display:flex;gap:.32rem;flex-wrap:wrap;">
                    <button type="button" class="avf-btn avf-active" data-preset="none"  onclick="avatarPreset(this,'none')">None</button>
                    <button type="button" class="avf-btn" data-preset="warm"  onclick="avatarPreset(this,'warm')">Warm</button>
                    <button type="button" class="avf-btn" data-preset="cool"  onclick="avatarPreset(this,'cool')">Cool</button>
                    <button type="button" class="avf-btn" data-preset="mono"  onclick="avatarPreset(this,'mono')">Mono</button>
                    <button type="button" class="avf-btn" data-preset="vivid" onclick="avatarPreset(this,'vivid')">Vivid</button>
                    <button type="button" class="avf-btn" data-preset="faded" onclick="avatarPreset(this,'faded')">Faded</button>
                  </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.45rem .65rem;">
                  <div style="display:flex;align-items:center;gap:.42rem;"><span style="font-size:.65rem;font-weight:bold;color:var(--muted);min-width:56px;">Brightness</span><input type="range" id="slBrightness" min="50" max="150" value="100" step="1" style="flex:1;" oninput="avatarRender()"></div>
                  <div style="display:flex;align-items:center;gap:.42rem;"><span style="font-size:.65rem;font-weight:bold;color:var(--muted);min-width:50px;">Contrast</span><input type="range" id="slContrast" min="50" max="150" value="100" step="1" style="flex:1;" oninput="avatarRender()"></div>
                  <div style="display:flex;align-items:center;gap:.42rem;"><span style="font-size:.65rem;font-weight:bold;color:var(--muted);min-width:56px;">Saturation</span><input type="range" id="slSaturation" min="0" max="200" value="100" step="1" style="flex:1;" oninput="avatarRender()"></div>
                  <div style="display:flex;align-items:center;gap:.42rem;"><span style="font-size:.65rem;font-weight:bold;color:var(--muted);min-width:50px;">Sharpness</span><input type="range" id="slSharpness" min="0" max="100" value="0" step="1" style="flex:1;" oninput="avatarRender()"></div>
                </div>
                <div style="display:flex;gap:.42rem;padding-top:.18rem;border-top:1px solid var(--grey);">
                  <button type="button" onclick="avatarConfirmCrop()" class="btn-save" style="flex:1;">✓ Use this photo</button>
                  <button type="button" onclick="avatarCancelCrop()" class="btn-save" style="background:var(--muted);flex:0 0 auto;">Cancel</button>
                </div>
              </div>
            </div>

            <form id="avatarCropForm" method="POST" action="{{ route('profile.avatar.crop') }}" style="display:none;">
              @csrf<input type="hidden" name="avatar_data" id="avatarDataInput">
            </form>

            <form method="POST" action="{{ route('profile.update') }}">
              @csrf
              <div class="sec-div" style="margin-top:1.4rem;"><span class="sec-div-label">Personal Details</span></div>
              <div class="field">
                <label for="name">Full Name</label>
                <div class="input-wrap"><span class="input-icon">👤</span>
                  <input id="name" type="text" value="{{ old('name',$userName) }}" disabled>
                  <input type="hidden" name="name" value="{{ old('name',$userName) }}">
                </div>
                <div class="field-note warn">Name is managed by an administrator.</div>
              </div>
              <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap"><span class="input-icon">✉</span>
                  <input id="email" type="email" value="{{ $userEmail }}" disabled>
                </div>
                <div class="field-note warn">Login email is managed by an administrator.</div>
              </div>

              <div class="sec-div" style="margin-top:1.3rem;"><span class="sec-div-label">Radio Identifiers</span></div>
              <div class="field">
                <label for="callsign">Callsign</label>
                @if($userCallsign)<div class="approved-tag">✓ {{ strtoupper($userCallsign) }} <span>approved</span></div>@endif
                <div class="input-wrap"><span class="input-icon">📡</span>
                  <input id="callsign" name="callsign" type="text"
                    value="{{ old('callsign',$pendingCallsign ?? $userCallsign) }}"
                    placeholder="e.g. G4BDS" autocomplete="off" autocorrect="off"
                    autocapitalize="characters" spellcheck="false" maxlength="10"
                    oninput="validateCallsign(this)">
                </div>
                <div class="cs-feedback ok" id="cs-ok">✓ Valid callsign format</div>
                <div class="cs-feedback err" id="cs-err">✕ <span id="cs-err-msg">Not a recognised amateur radio callsign</span></div>
                @error('callsign')<div class="cs-feedback err show">✕ {{ $message }}</div>@enderror
                @if($pendingCallsign)
                  <div class="pending-banner">
                    <div style="font-size:.95rem;flex-shrink:0;">⏳</div>
                    <div style="line-height:1.45;"><strong>Awaiting admin approval — {{ strtoupper($pendingCallsign) }}</strong> is pending review. Current approved callsign {{ $userCallsign ? '('.strtoupper($userCallsign).')' : '(none)' }} remains active until approved.</div>
                  </div>
                @else
                  <div class="field-note">Changes require admin approval before taking effect.</div>
                @endif
                <div class="cs-help">Format: prefix + number + suffix — e.g. G4BDS, M0ABC, 2E0XYZ. UK: Foundation M7 · Intermediate 2E0/2M0 · Full G/M/GM/GW/GI. <a href="https://www.ofcom.org.uk/manage-your-licence/radiocommunication-licences/amateur-radio" target="_blank" rel="noopener">Ofcom ↗</a></div>
                <div id="qrzCard" style="display:none;margin-top:.65rem;">

                  {{-- Loading --}}
                  <div id="qrzLoading" style="display:none;align-items:center;gap:.5rem;padding:.6rem .85rem;background:var(--light);border:1px solid var(--grey);border-radius:8px;font-size:.74rem;color:var(--muted);">
                    <span style="display:inline-block;width:13px;height:13px;border:2px solid var(--grey);border-top-color:var(--navy);border-radius:50%;animation:qrzSpin .7s linear infinite;flex-shrink:0;"></span>
                    Looking up on QRZ.com…
                  </div>

                  {{-- Not found — proof upload panel --}}
                  <div id="qrzNotFound" style="display:none;border:1.5px solid #fca5a5;border-radius:10px;overflow:hidden;">
                    <div style="background:#fef2f2;padding:.7rem .95rem;display:flex;align-items:flex-start;gap:.55rem;">
                      <span style="font-size:1.1rem;flex-shrink:0;margin-top:.05rem;">⚠</span>
                      <div>
                        <div style="font-size:.84rem;font-weight:bold;color:#b91c1c;margin-bottom:.22rem;">Callsign not found on QRZ.com</div>
                        <div style="font-size:.76rem;color:#7f1d1d;line-height:1.55;">
                          We couldn't verify <strong id="qrzMissingCallsign" style="font-family:monospace;letter-spacing:.05em;"></strong> in the QRZ database.
                          To save this callsign you must either
                          <a href="https://www.qrz.com/register" target="_blank" rel="noopener"
                             style="color:#b91c1c;font-weight:bold;text-decoration:underline;">register it on QRZ.com</a>
                          (free — your callsign will then be auto-verified next time)
                          or upload proof of your licence below.
                        </div>
                      </div>
                    </div>
                    <div style="background:white;padding:.75rem .95rem;border-top:1px solid #fecaca;">
                      <label style="font-size:.64rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);display:block;margin-bottom:.42rem;">📎 Upload licence proof to continue</label>
                      <input type="file" id="licenceProofInput" name="licence_proof" accept="image/*,.pdf"
                        style="display:block;width:100%;font-size:.82rem;padding:.45rem .7rem;border:1.5px dashed var(--grey);border-radius:7px;background:var(--light);cursor:pointer;transition:border-color .15s;"
                        onchange="onLicenceProofChange(this)"
                        onfocus="this.style.borderColor='var(--teal)'" onblur="this.style.borderColor='var(--grey)'">
                      <div id="licenceProofStatus" style="display:none;margin-top:.42rem;align-items:center;gap:.38rem;font-size:.76rem;font-weight:bold;color:var(--green);">
                        <span>✓</span><span>Proof attached — you can now save your changes.</span>
                      </div>
                      <div id="licenceProofHint" style="margin-top:.38rem;font-size:.69rem;color:var(--muted);line-height:1.5;">
                        Accepted: photo or PDF scan of your Ofcom licence. Max 10 MB.
                        Once reviewed, an admin will mark your callsign as verified.
                      </div>
                    </div>
                  </div>

                  {{-- Found — operator card --}}
                  <div id="qrzResult" style="display:none;border:1.5px solid #b8ddc9;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,51,102,.08);">
                    <div style="background:var(--navy);padding:.65rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                      <div style="display:flex;align-items:center;gap:.55rem;">
                        <span style="font-size:.58rem;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.45);">✓ QRZ Verified</span>
                        <span id="qrzCallsignBadge" style="font-family:monospace;font-size:.9rem;font-weight:bold;color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);padding:.1rem .55rem;border-radius:5px;letter-spacing:.1em;"></span>
                      </div>
                      <a id="qrzLink" href="#" target="_blank" rel="noopener"
                         style="font-size:.64rem;font-weight:bold;color:rgba(255,255,255,.5);text-decoration:none;border-bottom:1px solid rgba(255,255,255,.2);white-space:nowrap;transition:color .15s;"
                         onmouseover="this.style.color='rgba(255,255,255,.85)'" onmouseout="this.style.color='rgba(255,255,255,.5)'">qrz.com ↗</a>
                    </div>
                    <div style="background:white;padding:.85rem 1rem;display:flex;align-items:flex-start;gap:.85rem;">
                      <div id="qrzAvatar" style="width:52px;height:52px;border-radius:8px;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:1.15rem;font-weight:bold;color:#fff;flex-shrink:0;overflow:hidden;border:2px solid var(--grey);"></div>
                      <div style="flex:1;min-width:0;">
                        <div id="qrzName" style="font-size:.95rem;font-weight:bold;color:var(--text);line-height:1.2;margin-bottom:.22rem;"></div>
                        <div id="qrzLocation" style="font-size:.75rem;color:var(--muted);margin-bottom:.38rem;display:flex;align-items:center;gap:.3rem;"></div>
                        <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;">
                          <span id="qrzLicenceBadge" style="display:none;font-size:.63rem;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;padding:.14rem .5rem;background:var(--green-bg);border:1px solid #b8ddc9;border-radius:5px;color:var(--green);"></span>
                          <span id="qrzGridBadge"    style="display:none;font-size:.63rem;font-weight:bold;font-family:monospace;padding:.14rem .5rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);border-radius:5px;color:var(--navy);"></span>
                          <span id="qrzExtraBadge"   style="font-size:.63rem;color:var(--muted);"></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Service error --}}
                  <div id="qrzError" style="display:none;padding:.5rem .85rem;background:var(--light);border:1px solid var(--grey);border-radius:8px;font-size:.71rem;color:var(--muted);">
                    ⚠ QRZ lookup unavailable — <span id="qrzErrorDetail">could not reach the lookup service</span>.
                  </div>

                </div>
              </div>
              <div class="field">
                <label for="dmr_id">DMR ID</label>
                <div class="input-wrap"><span class="input-icon">🔢</span>
                  <input id="dmr_id" name="dmr_id" type="text" inputmode="numeric" value="{{ old('dmr_id',$userDmrId) }}" placeholder="e.g. 2346001">
                </div>
                <div class="field-note">Your DMR radio ID. Numbers only.</div>
              </div>
              <div class="field">
                <label for="telegram_chat_id">Telegram Chat ID</label>
                <div class="input-wrap"><span class="input-icon">📡</span>
                  <input id="telegram_chat_id" name="telegram_chat_id" type="text" inputmode="numeric" value="{{ old('telegram_chat_id',$user->telegram_chat_id) }}" placeholder="e.g. 5257679106">
                </div>
                <div class="field-note">DM <strong>@raynet_liverpool_bot</strong> and send <code>/id</code> to get your Chat ID.</div>
              </div>
              <div class="form-actions">
                <button type="submit" id="saveBtn" class="btn-save">Save Changes</button>
                <a href="{{ route('password.change') }}" class="pwd-link">Change password →</a>
              </div>
            </form>
          </div>
        </div>
      </div>{{-- /profile --}}

      {{-- ── RADIO TAB ── --}}
      <div class="tab-pane" id="tab-radio" role="tabpanel">
        <div class="card">
          <div class="card-head">
            <div class="card-head-icon">📜</div>
            <div><h2>Radio Identity</h2><p>Ofcom licence class and live DMR network activity.</p></div>
          </div>
          <div class="card-body">
            @if($lc)
              <div class="licence-block" style="background:{{ $lc['bg'] }};border-color:{{ $lc['border'] }};border-left-color:{{ $lc['dot'] }};">
                <div class="lic-icon" style="border-color:{{ $lc['border'] }};">{{ $lc['icon'] }}</div>
                <div class="lic-info">
                  <div class="lic-name" style="color:{{ $lc['text'] }};">{{ $lc['label'] }}</div>
                  <div class="lic-desc" style="color:{{ $lc['text'] }};">{{ $lc['desc'] }}</div>
                </div>
                <div class="lic-pill" style="background:{{ $lc['bg'] }};border-color:{{ $lc['border'] }};color:{{ $lc['text'] }};">{{ strtoupper($userLicence) }}</div>
              </div>
            @else
              <div style="padding:.72rem .95rem;background:var(--light);border:1px solid var(--grey);border-radius:8px;font-size:.86rem;color:var(--muted);">No licence class recorded — contact your Group Controller to update.</div>
            @endif
            <div class="dmr-panel">
              <div class="dmr-panel-top">
                <div>
                  <div class="dmr-label">DMR ID</div>
                  @if($userDmrId)<div class="dmr-value">{{ $userDmrId }}</div>
                  @else<span style="font-size:.86rem;color:rgba(255,255,255,.35);">Not set — update in Profile tab</span>@endif
                </div>
                @if($userDmrId)
                  <div style="text-align:right;">
                    <div style="font-size:.6rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">BrandMeister</div>
                    <a href="https://brandmeister.network/?page=device&id={{ $userDmrId }}01" target="_blank" style="font-size:.74rem;font-weight:bold;color:rgba(255,255,255,.6);text-decoration:none;border-bottom:1px solid rgba(255,255,255,.2);">View profile ↗</a>
                  </div>
                @endif
              </div>
              @if($userCallsign && $userDmrId)
              <div class="dmr-live-strip" id="dmrLiveStrip">
                <div class="dmr-live-block">
                  <div class="dmr-live-label">Last talkgroup</div>
                  <div class="dmr-live-value" id="dmrTg"><div class="dmr-loading-shimmer" style="width:80px;"></div></div>
                </div>
                <div class="dmr-live-block">
                  <div class="dmr-live-label">Last heard</div>
                  <div class="dmr-live-value" id="dmrHeard"><div class="dmr-loading-shimmer" style="width:65px;"></div></div>
                </div>
              </div>
              <div class="dmr-live-footer">
                <span>RadioID.net · <span id="dmrCallsignLabel">{{ strtoupper($userCallsign) }}</span></span>
                <a href="https://database.radioid.net/database/view#!entry?id={{ $userDmrId }}" target="_blank">Full record ↗</a>
              </div>
              @endif
            </div>
            <div class="info-note">ℹ Licence class is set by your Group Controller. DMR ID can be updated in the Profile tab.</div>
          </div>
        </div>
      </div>{{-- /radio --}}

      {{-- ── OPERATOR TAB ── --}}
      <div class="tab-pane" id="tab-operator" role="tabpanel">
        <div class="card">
          <div class="card-head">
            <div class="card-head-icon">📡</div>
            <div><h2>Operator Profile</h2><p>Your RAYNET role, level and deployment status. Set by your Group Controller.</p></div>
          </div>
          <div class="card-body">
            @if($isOperator)
              @if($userStatus && $sc)
                <div class="status-banner" style="background:{{ $sc['bg'] }};border-color:{{ $sc['border'] }};border-left-color:{{ $sc['dot'] }};">
                  <div class="sbdot" style="background:{{ $sc['dot'] }};box-shadow:0 0 6px {{ $sc['glow'] }};"></div>
                  <div>
                    <div style="font-weight:bold;color:{{ $sc['text'] }};">{{ $userStatus }}</div>
                    <div style="font-size:.68rem;color:var(--muted);">Operator status as recorded by the Group Controller</div>
                  </div>
                </div>
              @endif
              <div class="op-grid">
                <div class="op-tile"><div class="op-tile-label">Role</div><div class="op-tile-value">{{ $userRole }}</div></div>
                <div class="op-tile">
                  <div class="op-tile-label">Level</div>
                  @if($userLevel !== null)<div class="op-tile-value">Level {{ $userLevel }}</div><div class="op-tile-sub">{{ $levelLabel }}</div>
                  @else<div class="op-tile-value" style="color:var(--muted);font-weight:normal;">Not assigned</div>@endif
                </div>
                @if($userPhone)<div class="op-tile"><div class="op-tile-label">Contact</div><div class="op-tile-value">{{ $userPhone }}</div></div>@endif
                @if($userJoined)<div class="op-tile"><div class="op-tile-label">Joined RAYNET</div><div class="op-tile-value">{{ \Carbon\Carbon::parse($userJoined)->format('d M Y') }}</div></div>@endif
              </div>
              @if($userLevel !== null)
                <div class="level-bar-wrap">
                  <div class="level-bar-header">
                    <span class="level-bar-title">Operator Level</span>
                    <span class="level-bar-value">{{ $userLevel }} / 5 — {{ $levelLabel }}</span>
                  </div>
                  <div class="level-bar-track"><div class="level-bar-fill" style="width:{{ ($userLevel/5)*100 }}%;"></div></div>
                </div>
              @endif
              @if($userNotes)
                <div class="notes-block">
                  <div class="notes-label">Notes from Group Controller</div>
                  <div style="font-size:.84rem;color:var(--text-mid);">{{ $userNotes }}</div>
                </div>
              @endif
              @if($userLicence === 'Full' && $userLevel !== null && $userLevel >= 3)
                <div class="info-note" style="margin-top:.78rem;">⚡ Full Licence &amp; Level {{ $userLevel }} — eligible for net control duties and inter-group liaison roles.</div>
              @endif
            @else
              <div class="no-op-notice">
                <div style="font-size:2rem;opacity:.35;margin-bottom:.75rem;">📡</div>
                <div style="font-weight:bold;margin-bottom:.38rem;">Not yet registered as a RAYNET operator</div>
                <div style="font-size:.84rem;">Once your Group Controller assigns you a role and level, your operator profile will appear here.</div>
              </div>
            @endif
          </div>
        </div>
      </div>{{-- /operator --}}

      {{-- ── TRAINING TAB ── --}}
      <div class="tab-pane" id="tab-training" role="tabpanel">
        <div class="card">
          <div class="card-head">
            <div class="card-head-icon">🏅</div>
            <div><h2>Training Progression</h2><p>Tier badges unlock with course completion. Specialisms are independent.</p></div>
          </div>
          <div class="card-body">
            @php
            $completedCourseIds = collect($completedCourseIds ?? []);
            $tiers=[
              ['id'=>1,'num'=>1,'label'=>'Operator','colour'=>'#003366','border'=>'#001f40','desc'=>'RAYNET Basics: mission, Ofcom regs, message precedence'],
              ['id'=>2,'num'=>2,'label'=>'Adv. Operator','colour'=>'#0277bd','border'=>'#01579b','desc'=>'Prereq: Operator. Station running & structured net traffic.'],
              ['id'=>3,'num'=>3,'label'=>'Specialist','colour'=>'#1a7a3c','border'=>'#145c2e','desc'=>'Prereq: Adv. Operator. Technical or operational specialism.'],
              ['id'=>4,'num'=>4,'label'=>'Team Leader','colour'=>'#b45309','border'=>'#92400e','desc'=>'Prereq: Specialist. Incident coordination & deployment lead.'],
              ['id'=>5,'num'=>5,'label'=>'Instructor','colour'=>'#C8102E','border'=>'#9a0e22','desc'=>'Prereq: Team Leader. Deliver courses, assess & mentor.'],
            ];
            $specTech=[['id'=>101,'num'=>'T1','label'=>'Power Systems','colour'=>'#5b21b6','border'=>'#4c1d95','desc'=>'Battery, generator & solar power for ops.'],['id'=>102,'num'=>'T2','label'=>'Digital Modes','colour'=>'#5b21b6','border'=>'#4c1d95','desc'=>'DMR, D-STAR, Fusion & APRS. Ofcom rules.']];
            $specOps=[['id'=>111,'num'=>'O1','label'=>'Mapping','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'OS grid references, what3words, GIS basics.'],['id'=>112,'num'=>'O2','label'=>'Severe Weather','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'Storm ops, flood deployment, welfare.'],['id'=>113,'num'=>'O3','label'=>'First Aid Comms','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'Coordinating comms with medical teams.'],['id'=>114,'num'=>'O4','label'=>'Marathon Ops','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'Large event management & checkpoint liaison.'],['id'=>115,'num'=>'O5','label'=>'Air Support','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'Comms in support of air operations.'],['id'=>116,'num'=>'O6','label'=>'Water Ops','colour'=>'#0f766e','border'=>'#0d5e57','desc'=>'Flood, canal & coastal operations.']];
            $specAdmin=[['id'=>121,'num'=>'A1','label'=>'GDPR','colour'=>'#be185d','border'=>'#9d174d','desc'=>'Data protection for RAYNET volunteers.'],['id'=>122,'num'=>'A2','label'=>'Media Liaison','colour'=>'#be185d','border'=>'#9d174d','desc'=>'Press, social media & public communications.'],['id'=>123,'num'=>'A3','label'=>'Safeguarding','colour'=>'#be185d','border'=>'#9d174d','desc'=>'Protecting vulnerable persons during ops.'],['id'=>124,'num'=>'A4','label'=>'No Secret Codes','colour'=>'#be185d','border'=>'#9d174d','desc'=>'Ofcom rules: plain language only on amateur bands.']];
            $addl=[['id'=>201,'num'=>'K1','label'=>'Antennas','colour'=>'#374151','border'=>'#1f2937','desc'=>'Practical antenna theory & field erection.'],['id'=>202,'num'=>'K2','label'=>'NVIS','colour'=>'#374151','border'=>'#1f2937','desc'=>'Near Vertical Incidence Skywave. Ofcom notes.']];
            $allCourses=count($tiers)+count($specTech)+count($specOps)+count($specAdmin)+count($addl);
            $allIds=collect(array_merge($tiers,$specTech,$specOps,$specAdmin,$addl))->pluck('id');
            $completedCount=$allIds->intersect($completedCourseIds)->count();
            $pct=$allCourses>0?round(($completedCount/$allCourses)*100):0;
            @endphp
            <div class="training-progress-strip">
              <span class="tps-label">Overall</span>
              <div class="tps-track"><div class="tps-fill" style="width:{{ $pct }}%;"></div></div>
              <span class="tps-count">{{ $completedCount }}/{{ $allCourses }}</span>
            </div>
            <div class="training-section-label">Tier Progression</div>
            <div class="hex-row">
              @foreach($tiers as $i=>$course)
              @php $prereqsMet=true;for($p=0;$p<$i;$p++){if(!$completedCourseIds->contains($tiers[$p]['id'])){$prereqsMet=false;break;}}$done=$completedCourseIds->contains($course['id']);$state=$done?'unlocked':'locked';$fill=$done?$course['colour']:'#e2e8f0';$stroke=$done?$course['border']:'#c8d4e0'; @endphp
              @php
                $tipExtra = (!$done && !$prereqsMet) ? '<br><span style="color:#fca5a5;">🔒 Prerequisites needed</span>'
                          : ((!$done && $prereqsMet)  ? '<br><span style="color:#fde68a;">Ready to unlock</span>'
                          : '<br><span style="color:#86efac;">✓ Completed</span>');
                $svgShine = $done ? '<polygon points="28,4 50,16 42,4" fill="rgba(255,255,255,.12)" stroke="none"/>' : '';
                $numColor = $done ? '#fff' : 'rgba(0,0,0,.18)';
              @endphp
              <div class="hex-wrap {{ $state }}">
                <div class="hex-tooltip">
                  <strong>{{ $course['label'] }}</strong><br>
                  <span style="font-weight:normal;opacity:.8;">{{ $course['desc'] }}</span>
                  {!! $tipExtra !!}
                </div>
                <div class="hex">
                  <svg viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="28,3 51,15.5 51,40.5 28,53 5,40.5 5,15.5" fill="{{ $fill }}" stroke="{{ $stroke }}" stroke-width="2"/>
                    {!! $svgShine !!}
                  </svg>
                  <div class="hex-num" style="color:{{ $numColor }};">{{ $course['num'] }}</div>
                </div>
                <div class="hex-label">{{ $course['label'] }}</div>
              </div>
              @endforeach
            </div>
            @foreach([['Specialisms — Technical',$specTech],['Specialisms — Operational',$specOps],['Specialisms — Administrative',$specAdmin],['Additional Knowledge',$addl]] as [$sl,$sc2])
            <div class="training-section-label">{{ $sl }}</div>
            <div class="hex-row">
              @foreach($sc2 as $course)
              @php $done=$completedCourseIds->contains($course['id']);$state=$done?'unlocked':'locked';$fill=$done?$course['colour']:'#e2e8f0';$stroke=$done?$course['border']:'#c8d4e0'; @endphp
              @php
                $tipStatus2 = $done ? '<br><span style="color:#86efac;">✓ Completed</span>' : '<br><span style="color:#fde68a;">Available</span>';
                $svgShine2  = $done ? '<polygon points="28,4 50,16 42,4" fill="rgba(255,255,255,.12)" stroke="none"/>' : '';
                $numColor2  = $done ? '#fff' : 'rgba(0,0,0,.18)';
              @endphp
              <div class="hex-wrap {{ $state }}">
                <div class="hex-tooltip">
                  <strong>{{ $course['label'] }}</strong><br>
                  <span style="font-weight:normal;opacity:.8;">{{ $course['desc'] }}</span>
                  {!! $tipStatus2 !!}
                </div>
                <div class="hex">
                  <svg viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="28,3 51,15.5 51,40.5 28,53 5,40.5 5,15.5" fill="{{ $fill }}" stroke="{{ $stroke }}" stroke-width="2"/>
                    {!! $svgShine2 !!}
                  </svg>
                  <div class="hex-num" style="font-size:.76rem;color:{{ $numColor2 }};">{{ $course['num'] }}</div>
                </div>
                <div class="hex-label">{{ $course['label'] }}</div>
              </div>
              @endforeach
            </div>
            @endforeach
            <div class="hex-legend">
              <div class="hex-legend-item"><div class="hex-legend-dot" style="background:var(--navy);"></div>Completed</div>
              <div class="hex-legend-item"><div class="hex-legend-dot" style="background:#e2e8f0;border:1px solid #c8d4e0;"></div>Not yet done</div>
              <div class="hex-legend-item"><div class="hex-legend-dot" style="background:#5b21b6;"></div>Technical</div>
              <div class="hex-legend-item"><div class="hex-legend-dot" style="background:#0f766e;"></div>Operational</div>
              <div class="hex-legend-item"><div class="hex-legend-dot" style="background:#be185d;"></div>Administrative</div>
            </div>
          </div>
        </div>
      </div>{{-- /training --}}

    </div>{{-- /main --}}

    {{-- SIDEBAR (desktop only) --}}
    <div class="side-col">
      <div class="snap-card">
        <div class="snap-tabs">
          <button class="snap-tab-btn" data-tab="profile"  onclick="switchTab('profile')">Profile</button>
          <button class="snap-tab-btn" data-tab="radio"    onclick="switchTab('radio')">Radio</button>
          <button class="snap-tab-btn" data-tab="operator" onclick="switchTab('operator')">Operator</button>
          <button class="snap-tab-btn" data-tab="training" onclick="switchTab('training')">Training</button>
        </div>
        <div class="snap-header">
          @if($user->avatar)
            <div class="snap-avatar" style="padding:0;overflow:hidden;background:transparent;"><img src="{{ Storage::url($user->avatar) }}" style="width:100%;height:100%;object-fit:cover;" alt=""></div>
          @else
            <div class="snap-avatar">{{ $initials ?: '?' }}</div>
          @endif
          <div class="snap-name">{{ $userName }}</div>
          @if($userCallsign)<div class="snap-callsign">{{ strtoupper($userCallsign) }}</div>@endif
          @if($lc)<div class="snap-lic snap-lic-{{ $lc['slug'] }}">{{ $userLicence }} Licence</div>@endif
          @if($isOperator && $userRole)<div class="snap-role">{{ $userRole }}</div>@endif
          @if($userLevel !== null)<div class="snap-level">Level {{ $userLevel }} · {{ $levelLabel }}</div>@endif
          <div class="snap-status-row">
            @if($isOperator && $userStatus && $sc)
              <div class="snap-sdot" style="background:{{ $sc['dot'] }};box-shadow:0 0 0 3px {{ $sc['glow'] }};"></div>{{ $userStatus }}
            @else
              <div class="snap-sdot" style="background:rgba(255,255,255,.25);"></div>Active member
            @endif
          </div>
          @if($userCallsign && $userDmrId)
          <div class="snap-dmr-live" style="width:100%;">
            <div style="padding:.32rem .76rem .18rem;font-size:.52rem;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);">DMR Live</div>
            <div class="snap-dmr-live-inner">
              <div><div class="snap-dmr-live-label">Last TG</div><div class="snap-dmr-live-val" id="snapDmrTg"><div class="snap-dmr-loading" style="width:48px;"></div></div></div>
              <div><div class="snap-dmr-live-label">Last heard</div><div class="snap-dmr-live-val" id="snapDmrHeard"><div class="snap-dmr-loading" style="width:44px;"></div></div></div>
            </div>
          </div>
          @endif
        </div>
        <dl>
          <div class="snap-row"><dt class="snap-dt">Name</dt><dd class="snap-dd">{{ $userName }}</dd></div>
          <div class="snap-row"><dt class="snap-dt">Email</dt><dd class="snap-dd" style="font-size:.74rem;word-break:break-all;">{{ $userEmail }}</dd></div>
          <div class="snap-row"><dt class="snap-dt">Callsign</dt>
            @if($userCallsign)<dd class="snap-dd mono">{{ strtoupper($userCallsign) }}</dd>
            @else<dd class="snap-dd muted">Not set</dd>@endif
          </div>
          <div class="snap-row"><dt class="snap-dt">Licence</dt>
            @if($userLicence && $lc)<dd class="snap-dd" style="color:{{ $lc['text'] }};">{{ $userLicence }}</dd>
            @else<dd class="snap-dd muted">Not recorded</dd>@endif
          </div>
          <div class="snap-row"><dt class="snap-dt">DMR ID</dt>
            @if($userDmrId)<dd class="snap-dd mono">{{ $userDmrId }}</dd>
            @else<dd class="snap-dd muted">Not set</dd>@endif
          </div>
          @if($pendingCallsign)<div class="snap-row"><dt class="snap-dt">Pending</dt><dd class="snap-dd amber">{{ strtoupper($pendingCallsign) }} ⏳</dd></div>@endif
          @if($isOperator)
            <div class="snap-row"><dt class="snap-dt">Role</dt><dd class="snap-dd" style="color:var(--navy);">{{ $userRole }}</dd></div>
            @if($userLevel !== null)<div class="snap-row"><dt class="snap-dt">Level</dt><dd class="snap-dd mono">L{{ $userLevel }} — {{ $levelLabel }}</dd></div>@endif
            @if($userStatus && $sc)<div class="snap-row"><dt class="snap-dt">Status</dt><dd class="snap-dd" style="color:{{ $sc['text'] }};">{{ $userStatus }}</dd></div>@endif
            @if($userJoined)<div class="snap-row"><dt class="snap-dt">Joined</dt><dd class="snap-dd">{{ \Carbon\Carbon::parse($userJoined)->format('d M Y') }}</dd></div>@endif
          @endif
          <div class="snap-row"><dt class="snap-dt">Member Since</dt><dd class="snap-dd">{{ optional($user->created_at)->format('d M Y') ?? 'Unknown' }}</dd></div>
        </dl>
        <div class="snap-foot">
          @if($isOperator)Role and level are assigned by your Group Controller and cannot be self-edited.
          @else Operator details are assigned by the Group Controller once your training is recorded.@endif
        </div>
      </div>
    </div>

  </div>{{-- /page-layout --}}
</div>{{-- /wrap --}}

<script>
const VALID_TABS=['profile','radio','operator','training'];
function switchTab(id){
    if(!VALID_TABS.includes(id))id='profile';
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.toggle('active',p.id==='tab-'+id));
    document.querySelectorAll('#mainTabBar .tab-btn').forEach(b=>b.classList.toggle('active',b.dataset.tab===id));
    document.querySelectorAll('.snap-tab-btn').forEach(b=>b.classList.toggle('active',b.dataset.tab===id));
    try{history.replaceState(null,'','#'+id);}catch(e){}
}
(function(){
    const php='{{ $openTab }}',hash=location.hash.replace('#','');
    const init=VALID_TABS.includes(hash)?hash:php;
    switchTab(init);
    const btn=document.querySelector(`#mainTabBar [data-tab="${init}"]`);
    if(btn)btn.scrollIntoView({block:'nearest',inline:'center',behavior:'instant'});
})();
</script>

<script>
(function(){
    let img=new Image(),offsetX=0,offsetY=0,dragStartX=0,dragStartY=0,isDragging=false;
    const PRESETS={none:{brightness:100,contrast:100,saturation:100,sharpness:0},warm:{brightness:105,contrast:105,saturation:130,sharpness:10},cool:{brightness:100,contrast:100,saturation:85,sharpness:0},mono:{brightness:100,contrast:115,saturation:0,sharpness:5},vivid:{brightness:108,contrast:120,saturation:160,sharpness:20},faded:{brightness:115,contrast:80,saturation:70,sharpness:0}};
    window.avatarOpenCropper=function(input){if(!input.files||!input.files[0])return;const reader=new FileReader();reader.onload=function(e){img=new Image();img.onload=function(){offsetX=0;offsetY=0;document.getElementById('cropZoom').value=100;document.getElementById('slBrightness').value=100;document.getElementById('slContrast').value=100;document.getElementById('slSaturation').value=100;document.getElementById('slSharpness').value=0;document.querySelectorAll('.avf-btn').forEach(b=>b.classList.remove('avf-active'));document.querySelector('[data-preset="none"]').classList.add('avf-active');document.getElementById('avatarCropperPanel').style.display='block';initCanvas();avatarRender();};img.src=e.target.result;};reader.readAsDataURL(input.files[0]);};
    function initCanvas(){const area=document.getElementById('cropDragArea'),canvas=document.getElementById('cropCanvas'),W=area.offsetWidth,H=area.offsetHeight;canvas.width=W*window.devicePixelRatio;canvas.height=H*window.devicePixelRatio;canvas.style.width=W+'px';canvas.style.height=H+'px';const r=Math.min(W,H)*0.42,cx=W/2,cy=H/2,mc=document.getElementById('maskCircle'),gc=document.getElementById('guideCircle');mc.setAttribute('cx',cx);mc.setAttribute('cy',cy);mc.setAttribute('r',r);gc.setAttribute('cx',cx);gc.setAttribute('cy',cy);gc.setAttribute('r',r);area.onmousedown=function(e){isDragging=true;dragStartX=e.clientX-offsetX;dragStartY=e.clientY-offsetY;area.style.cursor='grabbing';};area.onmousemove=function(e){if(!isDragging)return;offsetX=e.clientX-dragStartX;offsetY=e.clientY-dragStartY;avatarRender();};area.onmouseup=area.onmouseleave=function(){isDragging=false;area.style.cursor='grab';};area.ontouchstart=function(e){const t=e.touches[0];isDragging=true;dragStartX=t.clientX-offsetX;dragStartY=t.clientY-offsetY;};area.ontouchmove=function(e){if(!isDragging)return;const t=e.touches[0];offsetX=t.clientX-dragStartX;offsetY=t.clientY-dragStartY;avatarRender();e.preventDefault();};area.ontouchend=function(){isDragging=false;};area.onwheel=function(e){const z=document.getElementById('cropZoom');z.value=Math.min(300,Math.max(50,parseInt(z.value)-Math.sign(e.deltaY)*5));avatarRender();e.preventDefault();};}
    window.avatarRender=function(){const canvas=document.getElementById('cropCanvas'),ctx=canvas.getContext('2d'),W=canvas.width,H=canvas.height,dpr=window.devicePixelRatio||1,zoom=parseInt(document.getElementById('cropZoom').value)/100;document.getElementById('cropZoomVal').textContent=Math.round(zoom*100)+'%';const b=document.getElementById('slBrightness').value,c=document.getElementById('slContrast').value,s=document.getElementById('slSaturation').value;ctx.clearRect(0,0,W,H);ctx.filter=`brightness(${b}%) contrast(${c}%) saturate(${s}%)`;ctx.drawImage(img,W/2-img.width*zoom*dpr/2+offsetX*dpr,H/2-img.height*zoom*dpr/2+offsetY*dpr,img.width*zoom*dpr,img.height*zoom*dpr);ctx.filter='none';};
    window.avatarPreset=function(btn,name){document.querySelectorAll('.avf-btn').forEach(b=>b.classList.remove('avf-active'));btn.classList.add('avf-active');const p=PRESETS[name];document.getElementById('slBrightness').value=p.brightness;document.getElementById('slContrast').value=p.contrast;document.getElementById('slSaturation').value=p.saturation;document.getElementById('slSharpness').value=p.sharpness;avatarRender();};
    window.avatarConfirmCrop=function(){const area=document.getElementById('cropDragArea'),canvas=document.getElementById('cropCanvas'),dpr=window.devicePixelRatio||1,W=area.offsetWidth,H=area.offsetHeight,r=Math.min(W,H)*0.42,cx=W/2,cy=H/2,out=document.createElement('canvas'),size=Math.round(r*2*dpr);out.width=size;out.height=size;const octx=out.getContext('2d');octx.beginPath();octx.arc(size/2,size/2,size/2,0,Math.PI*2);octx.clip();octx.drawImage(canvas,(cx-r)*dpr,(cy-r)*dpr,size,size,0,0,size,size);document.getElementById('avatarDataInput').value=out.toDataURL('image/jpeg',0.92);document.getElementById('avatarCropForm').submit();};
    window.avatarCancelCrop=function(){document.getElementById('avatarCropperPanel').style.display='none';document.getElementById('avatarRaw').value='';};
})();
</script>

<script>
(function(){
    /* ── State ── */
    let qrzState    = 'idle'; // idle | loading | found | notfound | error
    let proofUploaded = false;
    let qrzTimer    = null;
    let qrzLast     = '';
    let qrzActive   = false;
    let qrzImageUrl = null; // image_url from last successful QRZ lookup
    const HAS_AVATAR = {{ $user->avatar ? 'true' : 'false' }};

    /* ── DOM refs ── */
    const field       = document.getElementById('callsign');
    const qrzCard     = document.getElementById('qrzCard');
    const qrzLoading  = document.getElementById('qrzLoading');
    const qrzNotFound = document.getElementById('qrzNotFound');
    const qrzResult   = document.getElementById('qrzResult');
    const qrzError    = document.getElementById('qrzError');

    /* ── Save button helpers ── */
    function enableSave(btn)  { btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = ''; btn.title = ''; }
    function disableSave(btn, reason) { btn.disabled = true; btn.style.opacity = '0.4'; btn.style.cursor = 'not-allowed'; btn.title = reason; }

    function updateSaveBtn() {
        const btn = document.getElementById('saveBtn');
        if (!btn) return;
        const cs = field ? field.value.trim() : '';
        if (!cs) { enableSave(btn); return; }
        if (field && field.classList.contains('cs-invalid')) { disableSave(btn, 'Fix the callsign format before saving'); return; }
        if (qrzState === 'notfound' && !proofUploaded)       { disableSave(btn, 'Upload licence proof to save this callsign'); return; }
        enableSave(btn);
    }

    /* ── Callsign format validation ── */
    const PATTERNS = [
        /^G[MWIDGJUC]?[0-9][A-Z]{2,3}$/,
        /^M[MWIDGJUC]?[0-9][A-Z]{2,3}$/,
        /^2[EWMID][0-9][A-Z]{2,3}$/,
        /^[0-9]?[A-Z]{1,2}[0-9]{1,2}[A-Z]{1,4}$/
    ];

    window.validateCallsign = function(input) {
        const raw = input.value.trim(), upper = raw.toUpperCase();
        if (raw !== upper) { const p = input.selectionStart; input.value = upper; try { input.setSelectionRange(p,p); } catch(e){} }
        const okEl = document.getElementById('cs-ok'), errEl = document.getElementById('cs-err'), msgEl = document.getElementById('cs-err-msg');
        if (!upper) {
            input.classList.remove('cs-valid','cs-invalid');
            okEl.classList.remove('show'); errEl.classList.remove('show');
            updateSaveBtn(); return;
        }
        function fail(msg) {
            input.classList.remove('cs-valid'); input.classList.add('cs-invalid');
            okEl.classList.remove('show'); msgEl.textContent = msg; errEl.classList.add('show');
            updateSaveBtn();
        }
        function pass() {
            input.classList.remove('cs-invalid'); input.classList.add('cs-valid');
            okEl.classList.add('show'); errEl.classList.remove('show');
            updateSaveBtn();
        }
        if (upper.length < 3)              return fail('Too short — callsigns are at least 3 characters');
        if (/[^A-Z0-9]/.test(upper))       return fail('Letters and numbers only — no spaces or symbols');
        if (!/[A-Z]/.test(upper))          return fail('Callsigns must contain letters');
        if (!/[0-9]/.test(upper))          return fail('Callsigns must contain a district number');
        if (!PATTERNS.some(re=>re.test(upper))) return fail('Not a recognised format — e.g. G4BDS, M0ABC, 2E0XYZ');
        pass();
    };

    /* Block submit on invalid format with shake */
    const form = field?.closest('form');
    if (form && field) {
        form.addEventListener('submit', function(e) {
            if (field.value.trim() && field.classList.contains('cs-invalid')) {
                e.preventDefault(); field.focus();
                field.style.transition = 'transform .07s ease';
                [1,2,3,4].forEach(i => setTimeout(() => { field.style.transform = i%2 ? 'translateX(5px)' : 'translateX(-5px)'; }, i*70));
                setTimeout(() => { field.style.transform = ''; }, 350);
            }
        });
    }

    /* ── QRZ panel helpers ── */
    function qrzShow(el) {
        [qrzLoading, qrzNotFound, qrzResult, qrzError].forEach(e => { if (e) e.style.display = 'none'; });
        if (!el) return;
        el.style.display = (el === qrzLoading) ? 'flex' : 'block';
    }
    function qrzHide() {
        if (qrzCard) qrzCard.style.display = 'none';
        hideQrzPhotoPrompt();
    }

    /* ── QRZ photo prompt helpers ── */
    function showQrzPhotoPrompt(data) {
        const url      = data.image_url || null;
        const callsign = data.callsign  || '';
        if (!url) { hideQrzPhotoPrompt(); return; }

        qrzImageUrl = url;

        const prompt    = document.getElementById('qrzPhotoPrompt');
        const withExist = document.getElementById('qrzPhotoHasExisting');
        const noExist   = document.getElementById('qrzPhotoNoExisting');

        // Populate thumbnails and callsign labels in both variants
        ['A','B'].forEach(function(v) {
            const thumb = document.getElementById('qrzPhotoThumb' + v);
            const cs    = document.getElementById('qrzPhotoCallsign' + v);
            if (thumb) { thumb.src = url; thumb.onerror = function(){ this.style.display='none'; }; }
            if (cs)    { cs.textContent = callsign; }
        });

        // Set the hidden form value
        const importUrlInput = document.getElementById('qrzImportUrl');
        if (importUrlInput) importUrlInput.value = url;

        // Show the correct variant
       if (HAS_AVATAR) {
    // User already has a photo — show the prompt and let them decide
    if (prompt)    prompt.style.display    = 'block';
    if (withExist) withExist.style.display = 'block';
    if (noExist)   noExist.style.display   = 'none';

    ['A','B'].forEach(function(v) {
        const imp = document.getElementById('qrzPhotoImporting' + v);
        if (imp) imp.style.display = 'none';
    });
} else {
    // No existing photo — silently import without prompting
    const importUrlInput = document.getElementById('qrzImportUrl');
    if (importUrlInput) importUrlInput.value = url;
    const f = document.getElementById('qrzImportForm');
    if (f) f.submit();
}

        // Ensure import/loading states are reset
        ['A','B'].forEach(function(v) {
            const imp = document.getElementById('qrzPhotoImporting' + v);
            if (imp) imp.style.display = 'none';
        });
    }

    function hideQrzPhotoPrompt() {
        qrzImageUrl = null;
        const prompt = document.getElementById('qrzPhotoPrompt');
        if (prompt) prompt.style.display = 'none';
    }

    /* Called by "Use QRZ photo" / "Import photo" buttons */
    window.importQrzPhoto = function() {
        // Show a loading state on whichever variant is visible
        ['A','B'].forEach(function(v) {
            const panel = document.getElementById('qrzPhotoHasExisting');
            const panel2 = document.getElementById('qrzPhotoNoExisting');
            const imp = document.getElementById('qrzPhotoImporting' + v);
            const actions = document.querySelector(
                (v === 'A' ? '#qrzPhotoHasExisting' : '#qrzPhotoNoExisting') + ' .qrz-photo-prompt-actions'
            );
            if (imp && actions) {
                const visible = (v === 'A' && panel && panel.style.display !== 'none') ||
                                (v === 'B' && panel2 && panel2.style.display !== 'none');
                if (visible) {
                    actions.style.display = 'none';
                    imp.style.display = 'flex';
                }
            }
        });
        const f = document.getElementById('qrzImportForm');
        if (f) f.submit();
    };

    /* Called by "Keep current" / "No thanks" buttons */
    window.dismissQrzPhoto = function() {
        hideQrzPhotoPrompt();
    };

    /* ── QRZ result rendering ── */
    function qrzRender(data) {
        const badge = document.getElementById('qrzCallsignBadge');
        if (badge) badge.textContent = data.callsign || '';

        const lic = document.getElementById('qrzLicenceBadge');
        if (lic) {
            if (data.licence_class) { lic.textContent = data.licence_class; lic.style.display = 'inline-block'; }
            else { lic.style.display = 'none'; }
        }

        const gridBadge = document.getElementById('qrzGridBadge');
        if (gridBadge) {
            if (data.grid) { gridBadge.textContent = 'Grid ' + data.grid; gridBadge.style.display = 'inline-block'; }
            else { gridBadge.style.display = 'none'; }
        }

        const nameEl = document.getElementById('qrzName');
        if (nameEl) nameEl.textContent = data.name || data.callsign || '';

        const locEl = document.getElementById('qrzLocation');
        if (locEl) locEl.textContent = data.location || '';

        const extraEl = document.getElementById('qrzExtraBadge');
        if (extraEl) extraEl.textContent = data.p_call ? 'Prev: ' + data.p_call : '';

        const avatarEl = document.getElementById('qrzAvatar');
        if (avatarEl) {
            if (data.image_url) {
                const img = document.createElement('img');
                img.src = data.image_url;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                img.onerror = () => { avatarEl.textContent = '📡'; };
                avatarEl.innerHTML = '';
                avatarEl.appendChild(img);
            } else {
                const ini = (data.name || data.callsign || '?').split(' ').map(w => w[0]||'').join('').slice(0,2).toUpperCase();
                avatarEl.textContent = ini || '📡';
            }
        }

        const linkEl = document.getElementById('qrzLink');
        if (linkEl && data.callsign) linkEl.href = 'https://www.qrz.com/db/' + encodeURIComponent(data.callsign);

        qrzShow(qrzResult);
        qrzState = 'found';

        // ── Show the photo import prompt if QRZ returned an image ──
        if (data.image_url) {
            showQrzPhotoPrompt(data);
        } else {
            hideQrzPhotoPrompt();
        }

        updateSaveBtn();
    }

    async function qrzFetch(callsign) {
        if (!qrzCard || !callsign) return;
        if (callsign === qrzLast && qrzResult && qrzResult.style.display !== 'none') return;
        qrzLast = callsign; qrzActive = true;
        qrzState = 'loading'; updateSaveBtn();
        qrzCard.style.display = 'block';
        hideQrzPhotoPrompt();
        qrzShow(qrzLoading);
        const detailEl = document.getElementById('qrzErrorDetail');
        try {
            const res = await fetch('/qrz-lookup/' + encodeURIComponent(callsign), {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                qrzState = 'error';
                if (detailEl) detailEl.textContent = 'server returned HTTP ' + res.status;
                qrzShow(qrzError); updateSaveBtn(); qrzActive = false; return;
            }
            const json = await res.json();
            console.log('[QRZ]', callsign, json);
            if (!qrzActive || field.value.trim().toUpperCase() !== callsign) return;

            if (res.status === 422) {
                qrzHide(); qrzState = 'idle';
            } else if (json.found && json.data) {
                qrzRender(json.data);
            } else if (json.service_error || json.error || json.exception) {
                qrzState = 'error';
                if (detailEl) detailEl.textContent = json.reason || json.message || json.error || 'QRZ service error';
                qrzShow(qrzError);
            } else if (json.found === false || json.found === 0) {
                qrzState = 'notfound';
                hideQrzPhotoPrompt();
                const missing = document.getElementById('qrzMissingCallsign');
                if (missing) missing.textContent = callsign;
                proofUploaded = false;
                const proofInput  = document.getElementById('licenceProofInput');
                const proofStatus = document.getElementById('licenceProofStatus');
                const proofHint   = document.getElementById('licenceProofHint');
                if (proofInput)  proofInput.value = '';
                if (proofStatus) proofStatus.style.display = 'none';
                if (proofHint)   proofHint.style.display = 'block';
                qrzShow(qrzNotFound);
            } else {
                qrzState = 'error';
                if (detailEl) detailEl.textContent = 'unexpected response — check browser console';
                qrzShow(qrzError);
            }
        } catch(err) {
            qrzState = 'error';
            if (detailEl) detailEl.textContent = err.message || 'network error';
            qrzShow(qrzError);
        } finally {
            qrzActive = false;
            updateSaveBtn();
        }
    }

    /* Licence proof upload handler */
    window.onLicenceProofChange = function(input) {
        proofUploaded = input.files && input.files.length > 0;
        const status = document.getElementById('licenceProofStatus');
        const hint   = document.getElementById('licenceProofHint');
        if (status) status.style.display = proofUploaded ? 'flex' : 'none';
        if (hint)   hint.style.display   = proofUploaded ? 'none'  : 'block';
        updateSaveBtn();
    };

    /* Wrap validateCallsign to also trigger QRZ debounce */
    const _origValidate = window.validateCallsign;
    window.validateCallsign = function(input) {
        _origValidate(input);
        clearTimeout(qrzTimer);
        const upper = input.value.trim().toUpperCase();
        if (!upper || input.classList.contains('cs-invalid')) {
            qrzHide(); qrzLast = '';
            qrzState = 'idle'; proofUploaded = false;
            hideQrzPhotoPrompt();
            updateSaveBtn(); return;
        }
        qrzTimer = setTimeout(() => qrzFetch(upper), 600);
    };

    /* ── Init ── */
    if (field && field.value.trim()) validateCallsign(field);
    if (field && field.value.trim() && field.classList.contains('cs-valid')) {
        qrzFetch(field.value.trim().toUpperCase());
    }
    updateSaveBtn();
})();
</script>

@if($userCallsign && $userDmrId)
<script>
(function(){
    const callsign='{{ strtoupper($userCallsign) }}';
    function rt(d){if(!d)return{label:'Never',cls:'stale'};const diff=Math.floor((Date.now()-new Date(d))/86400000);if(diff===0)return{label:'Today',cls:'active'};if(diff===1)return{label:'Yesterday',cls:'active'};if(diff<7)return{label:diff+'d ago',cls:'active'};if(diff<30)return{label:diff+'d ago',cls:'recent'};if(diff<365)return{label:Math.floor(diff/30)+'mo ago',cls:'stale'};return{label:Math.floor(diff/365)+'y ago',cls:'stale'};}
    fetch('/members/radioid-lookup/'+encodeURIComponent(callsign)).then(r=>r.json()).then(data=>{
        const u=data?.results?.[0]??null,lasttg=u?.lasttg??null,heard=u?.lastheard??null,{label,cls}=rt(heard);
        const tg=document.getElementById('dmrTg'),h=document.getElementById('dmrHeard');
        if(tg)tg.innerHTML=lasttg?`<span class="dmr-live-tg">TG ${lasttg}</span>`:`<span style="color:rgba(255,255,255,.3);font-size:.74rem;">No data</span>`;
        if(h)h.innerHTML=`<span class="dmr-heard-row"><span class="dmr-heard-dot ${cls}"></span><span class="dmr-heard-text">${label}</span></span>`;
        const st=document.getElementById('snapDmrTg'),sh=document.getElementById('snapDmrHeard');
        if(st)st.innerHTML=lasttg?`<span class="snap-dmr-tg">TG ${lasttg}</span>`:`<span style="color:rgba(255,255,255,.25);font-size:.68rem;">—</span>`;
        if(sh)sh.innerHTML=`<span class="snap-dmr-dot ${cls}"></span><span style="font-size:.73rem;color:rgba(255,255,255,.65);">${label}</span>`;
    }).catch(()=>{['dmrTg','dmrHeard','snapDmrTg','snapDmrHeard'].forEach(id=>{const el=document.getElementById(id);if(el)el.innerHTML='<span style="color:rgba(255,255,255,.2);font-size:.68rem;">—</span>';});});
})();
</script>
@endif


@if(auth()->user()->hasRole('temporary_guest') && auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isFuture())
<script>
(function(){
    var expiry = new Date('{{ auth()->user()->guest_expires_at->toIso8601String() }}').getTime();
    var el = document.getElementById('guestCountdown');
    if (!el) return;
    function tick() {
        var diff = Math.max(0, Math.floor((expiry - Date.now()) / 1000));
        if (diff <= 0) { el.textContent = 'EXPIRED'; el.style.background = 'rgba(200,16,46,.4)'; return; }
        var d = Math.floor(diff / 86400);
        var h = Math.floor((diff % 86400) / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        var pad = n => String(n).padStart(2,'0');
        el.textContent = d > 0
            ? d + 'd ' + pad(h) + ':' + pad(m) + ':' + pad(s)
            : pad(h) + ':' + pad(m) + ':' + pad(s);
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endif

@if((auth()->user()->hasRole('temporary_guest') || auth()->user()->guest_expires_at) && auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isFuture())
<script>
(function(){
    var expiry = new Date({{ auth()->user()->guest_expires_at->valueOf() }}).getTime();
    var el = document.getElementById('guestCountdown');
    if (!el) return;
    function tick() {
        var diff = Math.max(0, Math.floor((expiry - Date.now()) / 1000));
        if (diff <= 0) { el.textContent = 'EXPIRED'; el.style.background = 'rgba(200,16,46,.5)'; return; }
        var d = Math.floor(diff / 86400);
        var h = Math.floor((diff % 86400) / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        var pad = function(n){ return String(n).padStart(2,'0'); };
        el.textContent = d > 0
            ? d + 'd ' + pad(h) + ':' + pad(m) + ':' + pad(s)
            : pad(h) + ':' + pad(m) + ':' + pad(s);
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endif
@endsection