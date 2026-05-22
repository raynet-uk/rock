<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ \App\Helpers\RaynetSetting::groupName() }}</title>
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --navy:#003366;--navy-mid:#004080;--navy-faint:#e8eef5;--navy-darker:#002244;
        --red:#C8102E;--red-faint:#fdf0f2;
        --white:#fff;--grey:#f4f5f7;--grey-mid:#dde2e8;--grey-dark:#9aa3ae;
        --text:#001f40;--text-mid:#2d4a6b;--text-muted:#6b7f96;
        --green:#1a6b3c;--green-bg:#eef7f2;
        --amber:#92400e;--amber-bg:#fffbeb;
        --purple:#5b21b6;--purple-bg:#f5f3ff;
        --teal:#0e7490;--teal-bg:#ecfeff;
        --sidebar-w:240px;--topbar-h:52px;
        --font:Arial,'Helvetica Neue',Helvetica,sans-serif;
        --shadow-sm:0 1px 3px rgba(0,51,102,.09);
        --shadow-md:0 4px 14px rgba(0,51,102,.11);
    }
    html,body{height:100%;font-family:var(--font);font-size:13px;color:var(--text);background:var(--grey)}
    .rn-shell{display:flex;min-height:100vh}

    /* SIDEBAR */
    .rn-sidebar{
        width:var(--sidebar-w);background:var(--navy-darker);
        display:flex;flex-direction:column;
        position:fixed;top:0;left:0;bottom:0;z-index:200;
        overflow-y:auto;overflow-x:hidden;
        scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;
    }
    .rn-sidebar::-webkit-scrollbar{width:4px}
    .rn-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12)}
    .sb-brand{display:flex;align-items:center;gap:.7rem;padding:1rem .9rem;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}
    .sb-logo{width:34px;height:34px;background:var(--red);flex-shrink:0;display:flex;align-items:center;justify-content:center}
    .sb-logo span{font-size:8px;font-weight:bold;color:#fff;text-align:center;line-height:1.2;text-transform:uppercase;letter-spacing:.04em}
    .sb-site{font-size:12px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;line-height:1.2}
    .sb-sub{font-size:9px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-top:2px}
    .sb-nav{flex:1;padding:.5rem 0}
    .sb-section-label{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.3);padding:1rem .9rem .35rem;display:flex;align-items:center;gap:.5rem}
    .sb-section-label::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06)}
    .sb-item{display:flex;align-items:center;gap:.6rem;padding:.52rem .9rem;color:rgba(255,255,255,.65);text-decoration:none;font-size:12.5px;font-weight:500;cursor:pointer;transition:background .12s,color .12s;border-left:3px solid transparent;white-space:nowrap}
    .sb-item:hover{background:rgba(255,255,255,.07);color:#fff}
    .sb-item.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--red)}
    .sb-icon{font-size:14px;width:18px;flex-shrink:0;text-align:center}
    .sb-badge{margin-left:auto;font-size:9px;font-weight:bold;background:var(--red);color:#fff;min-width:18px;height:16px;padding:0 4px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px}
    .sb-badge--amber{background:#d97706}
    .sb-group{}
    .sb-group-toggle{display:flex;align-items:center;gap:.6rem;padding:.52rem .9rem;color:rgba(255,255,255,.65);font-size:12.5px;font-weight:500;cursor:pointer;transition:background .12s,color .12s;border-left:3px solid transparent;user-select:none}
    .sb-group-toggle:hover{background:rgba(255,255,255,.07);color:#fff}
    .sb-group-toggle.open{color:#fff}
    .sb-chevron{margin-left:auto;font-size:9px;transition:transform .2s;color:rgba(255,255,255,.3)}
    .sb-chevron.open{transform:rotate(180deg)}
    .sb-subnav{display:none;background:rgba(0,0,0,.15)}
    .sb-subnav.open{display:block}
    .sb-subitem{display:flex;align-items:center;gap:.5rem;padding:.42rem .9rem .42rem 2.2rem;color:rgba(255,255,255,.5);text-decoration:none;font-size:12px;transition:background .12s,color .12s;border-left:3px solid transparent}
    .sb-subitem:hover{background:rgba(255,255,255,.05);color:rgba(255,255,255,.85)}
    .sb-subitem.active{color:#fff;border-left-color:var(--red);background:rgba(200,16,46,.15)}
    .sb-subitem .sb-badge{font-size:8px}
    .sb-divider{height:1px;background:rgba(255,255,255,.06);margin:.4rem 0}
    .sb-footer{padding:.75rem .9rem;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
    .sb-user{display:flex;align-items:center;gap:.6rem;margin-bottom:.6rem}
    .sb-avatar{width:28px;height:28px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;color:#fff;flex-shrink:0}
    .sb-user-name{font-size:12px;font-weight:bold;color:#fff}
    .sb-user-role{font-size:9px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em}
    .sb-logout{display:flex;align-items:center;gap:.5rem;width:100%;padding:.4rem .7rem;background:rgba(200,16,46,.15);border:1px solid rgba(200,16,46,.25);color:rgba(255,100,100,.9);font-size:11px;font-family:var(--font);font-weight:bold;cursor:pointer;letter-spacing:.04em;text-transform:uppercase;transition:background .12s}
    .sb-logout:hover{background:rgba(200,16,46,.3)}

    /* TOPBAR */
    .rn-topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:#fff;border-bottom:1px solid var(--grey-mid);box-shadow:0 1px 4px rgba(0,51,102,.07);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;z-index:100}
    .rn-topbar__left{display:flex;align-items:center;gap:.75rem}
    .rn-breadcrumb{font-size:12px;color:var(--text-muted)}
    .rn-breadcrumb strong{color:var(--navy);font-weight:bold}
    .rn-topbar__right{display:flex;align-items:center;gap:.75rem}
    .topbar-chip{display:flex;align-items:center;gap:.45rem;padding:.3rem .75rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.15);font-size:11px;color:var(--navy);font-weight:bold}
    .topbar-time{font-size:11px;color:var(--text-muted)}
    .online-dot{width:7px;height:7px;border-radius:50%;background:#22d47d;flex-shrink:0}
    .sb-mobile-toggle{display:none;background:none;border:none;cursor:pointer;padding:.4rem;color:var(--navy);font-size:18px}

    /* MAIN */
    .rn-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);min-height:calc(100vh - var(--topbar-h));padding:1.75rem 1.5rem 4rem;flex:1;min-width:0}

    /* PAGE HEADER */
    .rn-page-header{margin-bottom:1.5rem}
    .rn-page-eyebrow{font-size:10px;font-weight:bold;color:var(--red);text-transform:uppercase;letter-spacing:.18em;display:flex;align-items:center;gap:.4rem;margin-bottom:.3rem}
    .rn-page-eyebrow::before{content:'';width:14px;height:2px;background:var(--red);display:inline-block}
    .rn-page-title{font-size:22px;font-weight:bold;color:var(--navy);letter-spacing:-.01em}
    .rn-page-desc{font-size:12px;color:var(--text-muted);margin-top:.3rem}
    .rn-page-header-row{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap}
    .rn-page-actions{display:flex;gap:.5rem;align-items:center}

    /* SECTION */
    .rn-section{margin-bottom:2rem}
    .rn-section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;padding-bottom:.5rem;border-bottom:2px solid var(--navy)}
    .rn-section-head-left{display:flex;align-items:center;gap:.6rem}
    .rn-section-icon{width:26px;height:26px;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
    .rn-section-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy)}
    .rn-section-badge{font-size:9px;font-weight:bold;background:var(--red);color:#fff;padding:1px 7px;letter-spacing:.04em}

    /* CARDS */
    .rn-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:var(--shadow-sm)}
    .rn-card-head{padding:.75rem 1.1rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .rn-card-head h2{font-size:12px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.06em}
    .rn-card-body{padding:1rem 1.1rem}
    .module-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem}
    .module-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);text-decoration:none;color:var(--text);display:flex;flex-direction:column;box-shadow:var(--shadow-sm);transition:all .12s;overflow:hidden}
    .module-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);border-top-color:var(--red)}
    .module-card.accent-red{border-top-color:var(--red)}
    .module-card.accent-green{border-top-color:var(--green)}
    .module-card.accent-amber{border-top-color:#c49a00}
    .module-card.accent-purple{border-top-color:var(--purple)}
    .module-card.accent-teal{border-top-color:var(--teal)}
    .module-card-head{padding:.85rem 1rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);display:flex;align-items:center;gap:.65rem}
    .module-card-icon{width:34px;height:34px;background:var(--navy-faint);border:1px solid rgba(0,51,102,.15);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
    .module-card-meta{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)}
    .module-card-title{font-size:14px;font-weight:bold;color:var(--navy)}
    .module-card-body{padding:.85rem 1rem;flex:1}
    .module-card-desc{font-size:12px;color:var(--text-muted);line-height:1.55}
    .module-card-foot{padding:.6rem 1rem;border-top:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;gap:.5rem;background:#fff}
    .module-card-stats{display:flex;gap:.35rem;flex-wrap:wrap}
    .module-card-arrow{font-size:13px;font-weight:bold;color:var(--grey-dark);transition:all .12s;flex-shrink:0}
    .module-card:hover .module-card-arrow{color:var(--red);transform:translateX(3px)}

    /* BUTTONS */
    .rn-btn{display:inline-flex;align-items:center;gap:.35rem;padding:.42rem 1.1rem;border:1px solid;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;transition:all .12s;white-space:nowrap;text-transform:uppercase;letter-spacing:.05em;text-decoration:none}
    .rn-btn-primary{background:var(--navy);border-color:var(--navy);color:#fff}
    .rn-btn-primary:hover{background:var(--navy-mid)}
    .rn-btn-danger{background:transparent;border-color:var(--red);color:var(--red)}
    .rn-btn-danger:hover{background:var(--red-faint)}
    .rn-btn-ghost{background:#fff;border-color:var(--grey-mid);color:var(--text)}
    .rn-btn-ghost:hover{background:var(--grey)}
    .rn-btn-sm{padding:.3rem .75rem;font-size:11px}
    .btn{display:inline-flex;align-items:center;gap:.35rem;padding:.42rem 1.1rem;border:1px solid;font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;transition:all .12s;white-space:nowrap;text-transform:uppercase;letter-spacing:.05em}
    .btn-primary{background:var(--navy);border-color:var(--navy);color:#fff}
    .btn-primary:hover{background:var(--navy-mid)}
    .btn-danger{background:transparent;border-color:var(--red);color:var(--red)}
    .btn-danger:hover{background:var(--red-faint)}
    .btn-sm{padding:.3rem .75rem;font-size:11px}

    /* NOTICES */
    .rn-notice,.alert-success{display:flex;align-items:flex-start;gap:.55rem;padding:.65rem 1rem;margin-bottom:1rem;font-size:12.5px;line-height:1.45}
    .rn-notice--ok,.alert-success{background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);color:var(--green)}
    .rn-notice--err{background:var(--red-faint);border:1px solid rgba(200,16,46,.25);border-left:3px solid var(--red);color:var(--red)}
    .rn-notice--warn{background:var(--amber-bg);border:1px solid rgba(146,64,14,.25);border-left:3px solid #d97706;color:var(--amber)}
    .rn-notice--info{background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);border-left:3px solid var(--navy);color:var(--navy)}

    /* STAT PILLS */
    .stat-pill{font-size:10px;font-weight:bold;padding:2px 7px;border:1px solid;letter-spacing:.03em;white-space:nowrap}
    .sp-blue{background:var(--navy-faint);border-color:rgba(0,51,102,.25);color:var(--navy)}
    .sp-green{background:var(--green-bg);border-color:#b8ddc9;color:var(--green)}
    .sp-amber{background:var(--amber-bg);border-color:rgba(146,64,14,.25);color:var(--amber)}
    .sp-red{background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red)}
    .sp-purple{background:var(--purple-bg);border-color:rgba(91,33,182,.25);color:var(--purple)}
    .sp-teal{background:var(--teal-bg);border-color:rgba(14,116,144,.25);color:var(--teal)}
    .sp-grey{background:var(--grey);border-color:var(--grey-mid);color:var(--text-muted)}

    @keyframes fadeUp{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeUp .25s ease both}

    /* QUICK LINKS */
    .quick-row{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1.75rem}
    .quick-link{display:inline-flex;align-items:center;gap:.5rem;padding:.45rem 1rem;background:#fff;border:1px solid var(--grey-mid);border-left:3px solid var(--navy);font-size:12px;font-weight:bold;color:var(--navy);text-decoration:none;box-shadow:var(--shadow-sm);transition:all .12s;white-space:nowrap}
    .quick-link:hover{background:var(--navy-faint);border-left-color:var(--red);color:var(--red)}

    /* MOBILE */
    @media(max-width:900px){
        .rn-sidebar{transform:translateX(-100%);transition:transform .2s}
        .rn-sidebar.open{transform:translateX(0)}
        .rn-topbar{left:0}
        .rn-main{margin-left:0;padding:1rem 1rem 3rem}
        .sb-mobile-toggle{display:block}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:199}
        .sidebar-overlay.open{display:block}
        .module-grid{grid-template-columns:1fr}
    }
    </style>
    @stack('styles')
</head>
<body>
@php
    $pendingCount = 0;
    try { $pendingCount = \App\Models\User::where('registration_pending', true)->count(); } catch(\Throwable $e) {}
    $currentRoute = request()->route()?->getName() ?? '';
    $isSuperAdmin = auth()->user()?->is_super_admin ?? false;
    $adminName    = session('admin_name', auth()->user()?->name ?? 'Admin');
@endphp
<div class="rn-shell">

    <aside class="rn-sidebar" id="rnSidebar">
        <div class="sb-brand">
            <div class="sb-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="sb-site">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="sb-sub">Admin Panel</div>
            </div>
        </div>
        <nav class="sb-nav">

            <a href="{{ route('admin.dashboard') }}" class="sb-item {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
                <span class="sb-icon">⊞</span> Dashboard
            </a>

            <div class="sb-divider"></div>
            <div class="sb-section-label">People</div>

            <div class="sb-group" id="grp-members">
                <div class="sb-group-toggle" onclick="toggleGroup('grp-members')">
                    <span class="sb-icon">👥</span> Members
                    @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif
                    <span class="sb-chevron" id="grp-members-chevron">▼</span>
                </div>
                <div class="sb-subnav" id="grp-members-sub">
                    <a href="{{ route('admin.users.index') }}" class="sb-subitem {{ $currentRoute === 'admin.users.index' ? 'active' : '' }}">
                        All Members @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif
                    </a>
                    <a href="{{ route('admin.roles') }}" class="sb-subitem {{ $currentRoute === 'admin.roles' ? 'active' : '' }}">Roles</a>
                    <a href="{{ route('admin.temporary-guests.index') }}" class="sb-subitem {{ str_starts_with($currentRoute, 'admin.temporary-guests') ? 'active' : '' }}">Temporary Guests</a>
                    <a href="{{ route('admin.availability.index') }}" class="sb-subitem {{ $currentRoute === 'admin.availability.index' ? 'active' : '' }}">Availability</a>
                    <a href="{{ route('admin.online') }}" class="sb-subitem {{ $currentRoute === 'admin.online' ? 'active' : '' }}">Who's Online</a>
                    <a href="{{ route('admin.member-applications.index') }}" class="sb-subitem {{ str_starts_with($currentRoute,'admin.member-applications') ? 'active' : '' }}">
                        Applications
                        @php try { $pendingApps = \App\Models\MemberApplication::where('status','pending')->count(); } catch(\Throwable $e) { $pendingApps = 0; } @endphp
                        @if($pendingApps > 0)<span class="sb-badge sb-badge--amber">{{ $pendingApps }}</span>@endif
                    </a>
                </div>
            </div>

            <div class="sb-divider"></div>
            <div class="sb-section-label">Events</div>

            <div class="sb-group" id="grp-events">
                <div class="sb-group-toggle" onclick="toggleGroup('grp-events')">
                    <span class="sb-icon">📅</span> Events
                    <span class="sb-chevron" id="grp-events-chevron">▼</span>
                </div>
                <div class="sb-subnav" id="grp-events-sub">
                    <a href="{{ route('admin.events') }}" class="sb-subitem {{ $currentRoute === 'admin.events' ? 'active' : '' }}">All Events</a>
                    <a href="{{ route('admin.event-types') }}" class="sb-subitem {{ $currentRoute === 'admin.event-types' ? 'active' : '' }}">Event Types</a>
                    <a href="{{ route('admin.events.net-status') }}" class="sb-subitem {{ $currentRoute === 'admin.events.net-status' ? 'active' : '' }}">📻 Net Status</a>
                    <a href="{{ route('calendar') }}" class="sb-subitem" target="_blank">Public Calendar ↗</a>
                </div>
            </div>

            <div class="sb-divider"></div>
            <div class="sb-section-label">Gallery</div>
            <a href="{{ route('admin.super.admin.gallery.index') }}" class="sb-item {{ str_starts_with($currentRoute,'admin.super.admin.gallery') ? 'active' : '' }}">
                <span class="sb-icon">📸</span> Gallery Management
                @php $pendingPhotos = \App\Models\Photo::where('status','pending')->count(); @endphp
                @if($pendingPhotos > 0)
                    <span style="margin-left:auto;background:#f59e0b;color:#fff;font-size:9px;font-weight:bold;padding:1px 6px;border-radius:999px;">{{ $pendingPhotos }}</span>
                @endif
            </a>
            <div class="sb-section-label">Operations</div>

            <a href="{{ route('admin.dashboard') }}" class="sb-item">
                <span class="sb-icon">⚠</span> Alert Status
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="sb-item {{ str_starts_with($currentRoute,'admin.notifications') ? 'active' : '' }}">
                <span class="sb-icon">🔔</span> Notifications
            </a>

            @if(\Illuminate\Support\Facades\Route::has('admin.netlog.index'))
            <a href="{{ route('admin.netlog.index') }}" class="sb-item {{ str_starts_with($currentRoute,'admin.netlog') ? 'active' : '' }}">
                <span class="sb-icon">📻</span> Net Log
            </a>
            @endif

            @if(\Illuminate\Support\Facades\Route::has('admin.announcements.index'))
            <a href="{{ route('admin.announcements.index') }}" class="sb-item {{ str_starts_with($currentRoute,'admin.announcements') ? 'active' : '' }}">
                <span class="sb-icon">📢</span> Announcements
            </a>
            @endif

            @stack('sidebar_nav')

            <div class="sb-divider"></div>
            <div class="sb-section-label">Training</div>

            <div class="sb-group" id="grp-lms">
                <div class="sb-group-toggle" onclick="toggleGroup('grp-lms')">
                    <span class="sb-icon">🎓</span> Training
                    <span class="sb-chevron" id="grp-lms-chevron">▼</span>
                </div>
                <div class="sb-subnav" id="grp-lms-sub">
                    <a href="{{ route('admin.lms.index') }}" class="sb-subitem {{ $currentRoute === 'admin.lms.index' ? 'active' : '' }}">Course Builder</a>
                    <a href="{{ route('admin.lms.scorm-builder') }}" class="sb-subitem">SCORM Builder</a>
                    <a href="{{ route('lms.index') }}" class="sb-subitem" target="_blank">Training Portal ↗</a>
                </div>
            </div>

            <div class="sb-divider"></div>
            <div class="sb-section-label">Analytics</div>

            <a href="{{ route('admin.activity-logs.index') }}" class="sb-item {{ str_starts_with($currentRoute,'admin.activity') ? 'active' : '' }}">
                <span class="sb-icon">📊</span> Activity Logs
            </a>
            <a href="{{ route('data-dashboard') }}" class="sb-item" target="_blank">
                <span class="sb-icon">📡</span> Data Dashboard ↗
            </a>

            <div class="sb-divider"></div>
            <div class="sb-section-label">System</div>

            <div class="sb-group" id="grp-system">
                <div class="sb-group-toggle" onclick="toggleGroup('grp-system')">
                    <span class="sb-icon">⚙️</span> System
                    <span class="sb-chevron" id="grp-system-chevron">▼</span>
                </div>
                <div class="sb-subnav" id="grp-system-sub">
                    <a href="{{ route('admin.settings') }}" class="sb-subitem {{ $currentRoute === 'admin.settings' ? 'active' : '' }}">Site Settings</a>
                    <a href="{{ route('admin.modules.index') }}" class="sb-subitem {{ str_starts_with($currentRoute,'admin.modules') ? 'active' : '' }}">Module Manager</a>
                    @if($isSuperAdmin)
                    <a href="{{ route('admin.super.index') }}" class="sb-subitem">Super Admin</a>
                    <a href="{{ route('admin.super.permissions.index') }}" class="sb-subitem {{ $currentRoute === 'admin.super.permissions.index' ? 'active' : '' }}">Permissions</a>
                    <a href="{{ route('admin.oauth.clients') }}" class="sb-subitem {{ str_starts_with($currentRoute,'admin.oauth') ? 'active' : '' }}">SSO / OAuth</a>
                    @endif
                    <a href="{{ route('admin.aprs.index') }}" class="sb-subitem">APRS Locations</a>
                </div>
            </div>

        </nav>
        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar">{{ strtoupper(substr($adminName, 0, 1)) }}</div>
                <div>
                    <div class="sb-user-name">{{ $adminName }}</div>
                    <div class="sb-user-role">{{ $isSuperAdmin ? 'Super Admin' : 'Administrator' }}</div>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="sb-logout">⏻ Log Out</button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="rn-topbar">
        <div class="rn-topbar__left">
            <button class="sb-mobile-toggle" onclick="toggleSidebar()">☰</button>
            <div class="rn-breadcrumb">
                <strong>{{ \App\Helpers\RaynetSetting::groupName() }}</strong>
                <span> / @yield('title', 'Dashboard')</span>
            </div>
        </div>
        <div class="rn-topbar__right">
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .75rem;background:var(--navy-faint);border:1px solid rgba(0,51,102,.2);color:var(--navy);font-size:11px;font-weight:bold;text-decoration:none;" target="_blank">↗ View Site</a>
            @if($pendingCount > 0)
            <a href="{{ route('admin.users.index') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .75rem;background:var(--red-faint);border:1px solid rgba(200,16,46,.3);color:var(--red);font-size:11px;font-weight:bold;text-decoration:none;">
                ⏳ {{ $pendingCount }} pending
            </a>
            @endif
            <div class="topbar-chip"><div class="online-dot"></div><span>{{ $adminName }}</span></div>
            <div class="topbar-time" id="topbarTime"></div>
        </div>
    </div>

    <main class="rn-main">
        @if(session('success'))
        <div class="rn-notice rn-notice--ok fade-in">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="rn-notice rn-notice--err fade-in">⚠ {{ session('error') }}</div>
        @endif
        @if(session('status'))
        <div class="rn-notice rn-notice--ok fade-in">✓ {{ session('status') }}</div>
        @endif

        
    {{-- Temporary Admin Banner --}}
    @if(auth()->check() && auth()->user()->isTemporaryAdmin())
    <div style="background:linear-gradient(90deg,#92400e,#b45309);border-bottom:3px solid #d97706;padding:.5rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.65rem;">
            <span style="font-size:16px;flex-shrink:0;">🔑</span>
            <div>
                <div style="font-size:11px;font-weight:bold;color:#fff;letter-spacing:.04em;text-transform:uppercase;">Temporary Admin · Read-Only Access</div>
                <div style="font-size:10px;color:rgba(255,255,255,.8);margin-top:1px;">
                    You can manage temporary guest accounts only. All member data and write actions are restricted.
                    @if(auth()->user()->guest_expires_at && auth()->user()->guest_expires_at->isFuture())
                        · Expires {{ auth()->user()->guest_expires_at->format('d M Y \a\t H:i') }}
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
            <span style="font-size:9px;font-weight:bold;color:#fff;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.2);padding:2px 8px;text-transform:uppercase;letter-spacing:.05em;">👁 Read Only</span>
            <span style="font-size:9px;font-weight:bold;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;text-transform:uppercase;letter-spacing:.05em;">⏱ Temp Admin</span>
        </div>
    </div>
    @endif

    {{-- Temporary Admin Blocked Toast --}}
    @if(session('temp_admin_blocked'))
    <div id="tempAdminToast" style="position:fixed;bottom:24px;right:24px;z-index:9999;max-width:380px;background:#1a2332;border:1px solid rgba(180,83,9,.5);border-left:4px solid #b45309;padding:14px 18px;box-shadow:0 8px 32px rgba(0,0,0,.35);animation:toastIn .3s ease;">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <span style="font-size:20px;flex-shrink:0;margin-top:1px;">🔒</span>
            <div style="flex:1;">
                <div style="font-size:12px;font-weight:bold;color:#fef3c7;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Read-Only Access</div>
                <div style="font-size:12px;color:rgba(255,255,255,.8);line-height:1.5;">{{ session('temp_admin_blocked') }}</div>
            </div>
            <button onclick="document.getElementById('tempAdminToast').remove()" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:16px;flex-shrink:0;padding:0;line-height:1;">✕</button>
        </div>
    </div>
    <style>
    @keyframes toastIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
    </style>
    <script>
    setTimeout(function(){ var t=document.getElementById('tempAdminToast'); if(t) t.style.opacity='0', t.style.transition='opacity .4s', setTimeout(function(){t.remove()},400); }, 5000);
    </script>
    @endif
@yield('content')
    </main>
</div>

<script>
function updateClock(){const el=document.getElementById('topbarTime');if(el)el.textContent=new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',second:'2-digit'})+' · '+new Date().toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short'})}
updateClock();setInterval(updateClock,1000);
function toggleGroup(id){
    const sub=document.getElementById(id+'-sub');
    const chev=document.getElementById(id+'-chevron');
    const tog=sub.previousElementSibling;
    const o=sub.classList.contains('open');
    sub.classList.toggle('open',!o);
    tog.classList.toggle('open',!o);
    if(chev)chev.classList.toggle('open',!o);
}
function toggleSidebar(){document.getElementById('rnSidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('open')}
function closeSidebar(){document.getElementById('rnSidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('open')}

// Auto-open active groups
document.querySelectorAll('.sb-subitem.active').forEach(el=>{
    const sub=el.closest('.sb-subnav');
    if(sub){
        const id=sub.id.replace('-sub','');
        sub.classList.add('open');
        const tog=sub.previousElementSibling;
        if(tog)tog.classList.add('open');
        const chev=document.getElementById(id+'-chevron');
        if(chev)chev.classList.add('open');
    }
});
</script>
@stack('scripts')
</body>
</html>