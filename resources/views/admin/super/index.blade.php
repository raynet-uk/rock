{{-- resources/views/admin/super/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Super Admin Panel')
@section('content')

@php
// ── Stats ──────────────────────────────────────────────────────────────────
$activeSessions  = \DB::table('sessions')->count();
$loginsToday     = \App\Models\LoginHistory::where('successful', true)->whereDate('logged_in_at', today())->count();
$failedToday     = \App\Models\LoginHistory::where('successful', false)->whereDate('logged_in_at', today())->count();
$auditToday      = \App\Models\AdminAuditLog::whereDate('created_at', today())->count();
$totalUsers      = \App\Models\User::count();
$totalAdmins     = \App\Models\User::where('is_admin', true)->count();
$superAdminCount = \App\Models\User::where('is_super_admin', true)->count();

// ── Maintenance ────────────────────────────────────────────────────────────
$maintOn         = filter_var(\App\Models\Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);
$maintMsg        = \App\Models\Setting::get('maintenance_message', '');
$maintTitle      = \App\Models\Setting::get('maintenance_title', 'Back Soon');
$maintHeadline   = \App\Models\Setting::get('maintenance_headline', '');
$maintContact    = \App\Models\Setting::get('maintenance_contact', '');
$maintReturnAt   = \App\Models\Setting::get('maintenance_return_at', '');
$maintAutoOff    = \App\Models\Setting::get('maintenance_auto_disable_at', '');
$maintWhitelistRaw = \App\Models\Setting::get('maintenance_ip_whitelist', '[]');
$maintWhitelist  = json_decode($maintWhitelistRaw, true) ?? [];
$maintStarted    = \App\Models\Setting::get('maintenance_started_at', '');

// ── Sessions ──────────────────────────────────────────────────────────────
$sessions = \DB::table('sessions')
    ->leftJoin('users','sessions.user_id','=','users.id')
    ->select('sessions.*','users.name as user_name','users.email as user_email',
             'users.callsign','users.is_admin','users.is_super_admin')
    ->orderByDesc('sessions.last_activity')
    ->get();
$searchSess  = request('search_sess','');
if ($searchSess) {
    $sessions = $sessions->filter(fn($s) =>
        str_contains(strtolower($s->user_name ?? ''), strtolower($searchSess)) ||
        str_contains(strtolower($s->user_email ?? ''), strtolower($searchSess)) ||
        str_contains($s->ip_address ?? '', $searchSess)
    );
}

// ── Login History ─────────────────────────────────────────────────────────
$lhStatus  = request('lh_status', '');
$lhUser    = request('lh_user', '');
$lhFrom    = request('lh_from', '');
$lhTo      = request('lh_to', '');
$loginHistoryQuery = \App\Models\LoginHistory::with('user')
    ->when($lhStatus, fn($q) => $q->where('successful', $lhStatus === 'success'))
    ->when($lhUser,   fn($q) => $q->where(fn($q2) =>
        $q2->where('email', 'like', "%{$lhUser}%")
           ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$lhUser}%"))
    ))
    ->when($lhFrom,   fn($q) => $q->where('logged_in_at', '>=', $lhFrom))
    ->when($lhTo,     fn($q) => $q->where('logged_in_at', '<=', $lhTo . ' 23:59:59'))
    ->orderByDesc('logged_in_at');
$loginHistoryTotal = $loginHistoryQuery->count();
$loginHistory      = $loginHistoryQuery->paginate(25, ['*'], 'lh_page');

// ── Audit Log ─────────────────────────────────────────────────────────────
$auditAdmin  = request('audit_admin','');
$auditAction = request('audit_action','');
$auditFrom   = request('audit_from','');
$auditTo     = request('audit_to','');
$auditQuery = \App\Models\AdminAuditLog::with('admin')
    ->when($auditAdmin,  fn($q) => $q->where('admin_id', $auditAdmin))
    ->when($auditAction, fn($q) => $q->where('action','like',"%{$auditAction}%"))
    ->when($auditFrom,   fn($q) => $q->whereDate('created_at','>=',$auditFrom))
    ->when($auditTo,     fn($q) => $q->whereDate('created_at','<=',$auditTo))
    ->orderByDesc('created_at');
$auditTotal = $auditQuery->count();
$auditLogs  = $auditQuery->paginate(25, ['*'], 'al_page');
$auditAdmins = \App\Models\User::where('is_admin', true)->orderBy('name')->get();

// ── Super Admins ──────────────────────────────────────────────────────────
$superAdmins    = \App\Models\User::where('is_super_admin', true)->orderBy('name')->get();
$eligibleAdmins = \App\Models\User::where('is_admin', true)->where('is_super_admin', false)
                    ->where('id', '!=', auth()->id())->orderBy('name')->get();

// ── Login chart data (last 24h) ────────────────────────────────────────────
$chartData = [];
for ($h = 23; $h >= 0; $h--) {
    $hour = now()->subHours($h);
    $ok   = \App\Models\LoginHistory::whereBetween('logged_in_at',[$hour->copy()->startOfHour(),$hour->copy()->endOfHour()])->where('successful',true)->count();
    $fail = \App\Models\LoginHistory::whereBetween('logged_in_at',[$hour->copy()->startOfHour(),$hour->copy()->endOfHour()])->where('successful',false)->count();
    $chartData[] = ['h' => $hour->format('H:00'), 'ok' => $ok, 'fail' => $fail];
}

// Authenticated vs guest session counts
$authSessCount  = $sessions->whereNotNull('user_name')->count();
$guestSessCount = $sessions->whereNull('user_name')->count();

// Bot detection patterns
$botPatterns = ['bot','crawl','spider','claude','googlebot','bingbot','ahrefsbot','semrushbot','facebookexternalhit'];
$botCount = $sessions->filter(fn($s) => collect($botPatterns)->contains(fn($p) => str_contains(strtolower($s->user_agent ?? ''), $p)))->count();

// Failed logins this week by IP
$failedByIp = \App\Models\LoginHistory::where('successful', false)
    ->where('logged_in_at', '>=', now()->subWeek())
    ->select('ip_address', \DB::raw('count(*) as cnt'))
    ->groupBy('ip_address')
    ->orderByDesc('cnt')
    ->limit(5)
    ->get();

// Login history extra stats
$loginsThisWeek  = \App\Models\LoginHistory::where('successful', true)->where('logged_in_at', '>=', now()->subWeek())->count();
$failedThisWeek  = \App\Models\LoginHistory::where('successful', false)->where('logged_in_at', '>=', now()->subWeek())->count();
$uniqueIpsToday  = \App\Models\LoginHistory::whereDate('logged_in_at', today())->distinct('ip_address')->count('ip_address');
$mobileLogins    = \App\Models\LoginHistory::where('successful', true)->whereDate('logged_in_at', today())
    ->where(fn($q) => $q->where('user_agent','like','%Mobile%')->orWhere('user_agent','like','%iPhone%')->orWhere('user_agent','like','%Android%'))
    ->count();

// System health
$dbOk      = true; try { \DB::select('SELECT 1'); } catch(\Throwable $e) { $dbOk = false; }
$cacheOk   = true; try { \Cache::put('_sa_ping',1,5); $cacheOk = \Cache::get('_sa_ping')===1; } catch(\Throwable $e) { $cacheOk = false; }
$storageOk = is_writable(storage_path());
@endphp

<style>
/* ══════════════════════════════════════════════════════
   SUPER ADMIN PANEL — {{ \App\Helpers\RaynetSetting::groupName() }}
   Purple-on-navy premium design
══════════════════════════════════════════════════════ */
:root {
    --sa-purple:     #7c3aed;
    --sa-purple-mid: #6d28d9;
    --sa-purple-dark:#1e0040;
    --sa-purple-faint:#f5f3ff;
    --navy:          #003366;
    --navy-mid:      #004080;
    --navy-faint:    #e8eef5;
    --red:           #C8102E;
    --red-faint:     #fdf0f2;
    --white:         #fff;
    --grey:          #f2f5f9;
    --grey-mid:      #dde2e8;
    --grey-dark:     #9aa3ae;
    --text:          #001f40;
    --text-mid:      #2d4a6b;
    --text-muted:    #6b7f96;
    --green:         #16a34a;
    --green-bg:      #eef7f2;
    --amber:         #d97706;
    --amber-bg:      #fffbeb;
    --orange:        #ea580c;
    --orange-bg:     #fff7ed;
    --font: Arial,'Helvetica Neue',Helvetica,sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

/* ── HERO HEADER ── */
.sa-hero {
    background: linear-gradient(135deg, #0a0020 0%, var(--sa-purple-dark) 40%, #1a0050 100%);
    border-bottom: 4px solid var(--sa-purple);
    margin: -1.5rem -1rem 0;
    position: relative; overflow: hidden;
}
.sa-hero::before {
    content:''; position:absolute; inset:0;
    background: repeating-linear-gradient(-45deg,transparent,transparent 32px,rgba(124,58,237,.04) 32px,rgba(124,58,237,.04) 64px);
}
.sa-hero::after {
    content:''; position:absolute; bottom:-30%; right:-10%;
    width:50%; padding-top:50%; border-radius:50%;
    background: radial-gradient(circle, rgba(124,58,237,.15) 0%, transparent 70%);
    pointer-events:none;
}
.sa-hero-inner {
    max-width:1400px; margin:0 auto; padding:1.5rem 1.5rem 0;
    position:relative; z-index:1;
    display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;
}
.sa-hero-brand { display:flex; align-items:center; gap:.85rem; }
.sa-logo {
    width:46px; height:46px; background:var(--sa-purple);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
    border: 2px solid rgba(167,139,250,.3);
}
.sa-logo span { font-size:11px; font-weight:700; color:#fff; text-align:center; line-height:1.15; text-transform:uppercase; letter-spacing:.06em; }
.sa-org { font-size:15px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.04em; }
.sa-sub { font-size:11px; color:rgba(167,139,250,.6); margin-top:2px; text-transform:uppercase; letter-spacing:.06em; }
.sa-hero-right { display:flex; flex-direction:column; align-items:flex-end; gap:.5rem; }
.sa-who {
    display:flex; align-items:center; gap:.55rem;
    padding:.4rem .9rem; background:rgba(124,58,237,.2);
    border:1px solid rgba(124,58,237,.4);
}
.sa-who-dot { width:8px; height:8px; border-radius:50%; background:#a78bfa; animation:pulsePurple 1.8s ease-in-out infinite; flex-shrink:0; }
@keyframes pulsePurple{0%,100%{opacity:1;box-shadow:0 0 0 3px rgba(167,139,250,.15);}50%{opacity:.5;box-shadow:0 0 0 6px rgba(167,139,250,.04);}}
.sa-who-text { font-size:12px; font-weight:700; color:#c4b5fd; }
.sa-back {
    font-size:11px; font-weight:700; color:rgba(255,255,255,.5); text-decoration:none;
    border:1px solid rgba(255,255,255,.15); padding:4px 10px; font-family:var(--font);
    text-transform:uppercase; letter-spacing:.07em; transition:all .12s;
}
.sa-back:hover { color:#fff; background:rgba(255,255,255,.08); }

/* ── HERO STATS STRIP ── */
.sa-stats-strip {
    border-top:1px solid rgba(255,255,255,.07); margin-top:1.5rem;
    display:grid; grid-template-columns:repeat(7,1fr); position:relative; z-index:1;
}
@media(max-width:1200px){.sa-stats-strip{grid-template-columns:repeat(4,1fr);}}
@media(max-width:700px){.sa-stats-strip{grid-template-columns:repeat(2,1fr);}}
.sa-stat {
    padding:.9rem 1.1rem; border-right:1px solid rgba(255,255,255,.06);
    cursor:default; transition:background .15s;
}
.sa-stat:hover { background:rgba(255,255,255,.04); }
.sa-stat:last-child { border-right:none; }
.sa-stat-lbl { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.14em; color:rgba(255,255,255,.3); margin-bottom:.4rem; }
.sa-stat-val { font-size:28px; font-weight:700; line-height:1; color:#fff; }
.sa-stat-val.red  { color:#f87171; }
.sa-stat-val.green{ color:#4ade80; }
.sa-stat-val.amber{ color:#fbbf24; }
.sa-stat-sub { font-size:10px; color:rgba(255,255,255,.3); margin-top:.3rem; }

/* ── TAB BAR ── */
.sa-tab-bar {
    display:flex; background:rgba(255,255,255,.04); overflow-x:auto;
    border-bottom:1px solid rgba(255,255,255,.08); position:relative; z-index:1;
}
.sa-tab {
    padding:.8rem 1.3rem; background:transparent; border:none; border-bottom:3px solid transparent;
    margin-bottom:-1px; color:rgba(255,255,255,.35); font-family:var(--font);
    font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase;
    cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:.4rem; white-space:nowrap;
}
.sa-tab:hover { color:rgba(255,255,255,.7); }
.sa-tab.active { color:#c4b5fd; border-bottom-color:var(--sa-purple); }
.sa-tab-badge {
    background:rgba(124,58,237,.3); border:1px solid rgba(124,58,237,.4);
    color:#a78bfa; font-size:10px; padding:1px 6px;
}
.sa-tab-badge.red { background:rgba(200,16,46,.3); border-color:rgba(200,16,46,.4); color:#fca5a5; }

/* ── WRAP ── */
.sa-wrap { max-width:1400px; margin:0 auto; padding:1.5rem 1.5rem 5rem; }

/* ── ALERT ── */
.sa-alert { padding:.65rem 1rem; margin-bottom:1.25rem; font-size:13px; font-weight:700;
            display:flex; align-items:center; gap:.6rem; }
.sa-alert.ok  { background:var(--green-bg); border:1px solid rgba(22,163,74,.3); border-left:3px solid var(--green); color:#14532d; }
.sa-alert.err { background:var(--red-faint); border:1px solid rgba(200,16,46,.25); border-left:3px solid var(--red); color:var(--red); }
.sa-alert.warn{ background:var(--amber-bg); border:1px solid rgba(217,119,6,.25); border-left:3px solid var(--amber); color:#92400e; }

/* ── CARDS ── */
.sa-card { background:#fff; border:1px solid var(--grey-mid); box-shadow:0 1px 4px rgba(0,51,102,.06); margin-bottom:1rem; overflow:hidden; }
.sa-card-head {
    padding:.75rem 1.1rem; background:var(--grey); border-bottom:1px solid var(--grey-mid);
    display:flex; align-items:center; gap:.65rem; flex-wrap:wrap;
}
.sa-card-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:var(--sa-purple-dark); }
.sa-card-head-right { margin-left:auto; display:flex; align-items:center; gap:.5rem; }

/* ── MAINTENANCE SECTION ── */
.maint-live-bar {
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    padding:.9rem 1.25rem;
}
.maint-live-indicator { display:flex; align-items:center; gap:.65rem; }
.maint-on-dot {
    width:14px; height:14px; border-radius:50%; flex-shrink:0;
    background:var(--orange); box-shadow:0 0 0 4px rgba(234,88,12,.2);
    animation:maintPulse 2s ease-in-out infinite;
}
@keyframes maintPulse{0%,100%{box-shadow:0 0 0 4px rgba(234,88,12,.2);}50%{box-shadow:0 0 0 8px rgba(234,88,12,.06);}}
.maint-off-dot { width:14px; height:14px; border-radius:50%; flex-shrink:0; background:var(--green); box-shadow:0 0 0 4px rgba(22,163,74,.2); }
.maint-live-label { font-size:15px; font-weight:700; color:var(--text); }
.maint-live-sub   { font-size:11px; color:var(--text-muted); margin-top:1px; }
.maint-timer { font-family:'Courier New',monospace; font-size:13px; font-weight:700; color:var(--orange); background:var(--orange-bg); border:1px solid rgba(234,88,12,.2); padding:.3rem .75rem; }
.maint-timer.off { color:var(--green); background:var(--green-bg); border-color:rgba(22,163,74,.2); }
.maint-presets { display:grid; grid-template-columns:repeat(4,1fr); gap:.65rem; padding:1rem 1.25rem; border-bottom:1px solid var(--grey-mid); background:rgba(124,58,237,.03); }
@media(max-width:900px){ .maint-presets { grid-template-columns:1fr 1fr; } }
.maint-preset-btn { padding:.7rem .85rem; border:2px solid var(--grey-mid); background:#fff; cursor:pointer; transition:all .15s; text-align:left; font-family:var(--font); }
.maint-preset-btn:hover { border-color:var(--sa-purple); background:var(--sa-purple-faint); }
.maint-preset-btn.selected { border-color:var(--sa-purple); background:var(--sa-purple-faint); }
.maint-preset-icon { font-size:18px; margin-bottom:.35rem; }
.maint-preset-name { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--sa-purple-dark); }
.maint-preset-desc { font-size:10px; color:var(--text-muted); margin-top:2px; line-height:1.4; }
.maint-form-wrap { display:grid; grid-template-columns:1fr 320px; gap:1.5rem; padding:1.25rem; align-items:start; }
@media(max-width:900px){ .maint-form-wrap { grid-template-columns:1fr; } }
.maint-field { display:flex; flex-direction:column; gap:.3rem; margin-bottom:.85rem; }
.maint-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); }
.maint-input { background:#fff; border:1px solid var(--grey-mid); padding:.5rem .75rem; color:var(--text); font-family:var(--font); font-size:13px; outline:none; width:100%; transition:border-color .15s, box-shadow .15s; }
.maint-input:focus { border-color:var(--sa-purple); box-shadow:0 0 0 3px rgba(124,58,237,.08); }
.maint-toggle-card { display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; border:2px solid var(--grey-mid); background:#fff; cursor:pointer; transition:all .15s; }
.maint-toggle-card:hover { border-color:var(--orange); }
.maint-toggle-card.active { border-color:var(--orange); background:var(--orange-bg); }
.maint-toggle-card input[type="checkbox"] { width:18px; height:18px; accent-color:var(--orange); cursor:pointer; flex-shrink:0; }
.maint-preview-pane { position:sticky; top:80px; border:1px solid var(--grey-mid); overflow:hidden; box-shadow:0 4px 16px rgba(0,51,102,.1); }
.maint-preview-label { background:var(--sa-purple-dark); padding:.5rem .85rem; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:rgba(167,139,250,.6); display:flex; align-items:center; justify-content:space-between; }
.maint-preview-live { font-size:9px; color:#4ade80; font-weight:700; animation:blink 1.4s ease-in-out infinite; }
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.3;}}
.maint-preview-screen { background: linear-gradient(135deg, #001428 0%, var(--navy) 100%); padding:2rem 1.25rem; text-align:center; min-height:220px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1rem; }
.maint-preview-icon-wrap { width:52px; height:52px; background:rgba(124,58,237,.2); border:2px solid rgba(124,58,237,.35); display:flex; align-items:center; justify-content:center; margin:0 auto; font-size:1.4rem; }
.maint-preview-title-text { font-size:18px; font-weight:700; color:#fff; line-height:1.2; }
.maint-preview-headline-text { font-size:11px; font-weight:700; color:rgba(255,255,255,.55); text-transform:uppercase; letter-spacing:.08em; margin-top:.15rem; }
.maint-preview-msg-text { font-size:12px; color:rgba(255,255,255,.45); line-height:1.6; max-width:220px; }
.maint-preview-footer { padding:.6rem .85rem; background:#001428; border-top:1px solid rgba(255,255,255,.06); font-size:9px; text-align:center; color:rgba(255,255,255,.2); }
.maint-impact-row { display:flex; gap:.65rem; flex-wrap:wrap; padding:.85rem 1.25rem; border-top:1px solid var(--grey-mid); background:rgba(0,51,102,.02); }
.maint-impact-chip { display:flex; align-items:center; gap:.4rem; padding:.35rem .75rem; font-size:12px; font-weight:700; border:1px solid; }
.mic-auth  { background:rgba(234,88,12,.08); border-color:rgba(234,88,12,.2); color:var(--orange); }
.mic-guest { background:var(--grey); border-color:var(--grey-mid); color:var(--text-muted); }
.mic-ok    { background:var(--green-bg); border-color:rgba(22,163,74,.2); color:var(--green); }
.ip-tag { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .65rem; background:var(--navy-faint); border:1px solid rgba(0,51,102,.2); font-family:monospace; font-size:12px; font-weight:700; color:var(--navy); }
.ip-tag-remove { background:none; border:none; cursor:pointer; color:var(--grey-dark); font-size:14px; padding:0; font-family:var(--font); line-height:1; }
.ip-tag-remove:hover { color:var(--red); }
.maint-schedule-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
@media(max-width:600px){ .maint-schedule-grid { grid-template-columns:1fr; } }
.maint-checklist { padding:1rem 1.25rem; border-top:1px solid var(--grey-mid); background:rgba(0,51,102,.02); }
.maint-checklist-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:var(--text-muted); margin-bottom:.65rem; }
.maint-check-item { display:flex; align-items:center; gap:.5rem; padding:.4rem .65rem; background:#fff; border:1px solid var(--grey-mid); font-size:12px; color:var(--text-mid); }
.maint-check-item input { accent-color:var(--sa-purple); width:14px; height:14px; flex-shrink:0; }

/* ── SESSIONS ── */
.sess-summary { display:grid; grid-template-columns:repeat(4,1fr); gap:0; border-bottom:1px solid var(--grey-mid); }
@media(max-width:700px){ .sess-summary { grid-template-columns:repeat(2,1fr); } }
.sess-sum-tile { padding:.85rem 1.1rem; text-align:center; border-right:1px solid var(--grey-mid); }
.sess-sum-tile:last-child { border-right:none; }
.sess-sum-num  { font-size:32px; font-weight:700; line-height:1; }
.sess-sum-lbl  { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); margin-top:.3rem; }
.sess-sum-tile.auth  .sess-sum-num { color:var(--green); }
.sess-sum-tile.guest .sess-sum-num { color:var(--text-muted); }
.sess-sum-tile.bot   .sess-sum-num { color:var(--amber); }
.sess-sum-tile.total .sess-sum-num { color:var(--navy); }

/* Data table */
.data-table { width:100%; border-collapse:collapse; font-size:13px; font-family:var(--font); }
.data-table thead { background:var(--sa-purple-dark); border-bottom:2px solid var(--sa-purple); }
.data-table th { padding:.55rem .9rem; text-align:left; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.55); white-space:nowrap; }
.data-table tr { border-bottom:1px solid var(--grey-mid); transition:background .1s; }
.data-table tr:hover { background:var(--navy-faint); }
.data-table tr:last-child { border-bottom:none; }
.data-table td { padding:.65rem .9rem; vertical-align:middle; }
.empty-td { text-align:center; padding:3rem 1rem; color:var(--text-muted); font-style:italic; }
.filter-strip { display:flex; align-items:flex-end; gap:.65rem; flex-wrap:wrap; padding:.85rem 1.1rem; background:var(--grey); border-bottom:1px solid var(--grey-mid); }
.ff { display:flex; flex-direction:column; gap:.3rem; flex:1; min-width:120px; max-width:200px; }
.ff label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:var(--text-muted); }
.ff input, .ff select { background:#fff; border:1px solid var(--grey-mid); padding:.38rem .65rem; color:var(--text); font-family:var(--font); font-size:12px; outline:none; transition:border-color .15s; }
.ff input:focus,.ff select:focus { border-color:var(--sa-purple); }

/* Badges */
.badge { display:inline-flex; align-items:center; padding:2px 8px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; border:1px solid; white-space:nowrap; }
.b-ok     { background:var(--green-bg); border-color:rgba(22,163,74,.25); color:var(--green); }
.b-fail   { background:var(--red-faint); border-color:rgba(200,16,46,.25); color:var(--red); }
.b-amber  { background:var(--amber-bg); border-color:rgba(217,119,6,.2); color:var(--amber); }
.b-navy   { background:var(--navy-faint); border-color:rgba(0,51,102,.2); color:var(--navy); }
.b-purple { background:var(--sa-purple-faint); border-color:rgba(124,58,237,.2); color:var(--sa-purple); }
.b-super  { background:var(--sa-purple-dark); border-color:rgba(124,58,237,.5); color:#c4b5fd; }
.b-grey   { background:var(--grey); border-color:var(--grey-mid); color:var(--grey-dark); }
.b-bot    { background:#fff7ed; border-color:#fed7aa; color:var(--orange); }

/* Buttons */
.btn { display:inline-flex; align-items:center; gap:.35rem; padding:.38rem .9rem; border:1px solid; font-family:var(--font); font-size:11px; font-weight:700; cursor:pointer; transition:all .12s; white-space:nowrap; text-transform:uppercase; letter-spacing:.05em; text-decoration:none; }
.btn-primary { background:var(--sa-purple-dark); border-color:var(--sa-purple-dark); color:#fff; }
.btn-primary:hover { background:#2d0070; }
.btn-red { background:var(--red-faint); border-color:rgba(200,16,46,.3); color:var(--red); }
.btn-red:hover { background:rgba(200,16,46,.12); border-color:var(--red); }
.btn-green { background:var(--green-bg); border-color:rgba(22,163,74,.25); color:var(--green); }
.btn-green:hover { background:#d6ede3; }
.btn-navy { background:var(--navy); border-color:var(--navy); color:#fff; }
.btn-navy:hover { background:var(--navy-mid); }
.btn-ghost { background:transparent; border-color:var(--grey-mid); color:var(--text-muted); }
.btn-ghost:hover { border-color:var(--navy); color:var(--navy); }
.btn-orange { background:var(--orange); border-color:var(--orange); color:#fff; }
.btn-orange:hover { background:#c2410c; }
.btn-sm { padding:.25rem .65rem; font-size:10px; }

/* Audit styles */
.act { font-size:10px; font-weight:700; padding:2px 7px; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; border:1px solid; }
.act-red    { background:var(--red-faint); border-color:rgba(200,16,46,.25); color:var(--red); }
.act-green  { background:var(--green-bg); border-color:rgba(22,163,74,.25); color:var(--green); }
.act-blue   { background:var(--navy-faint); border-color:rgba(0,51,102,.2); color:var(--navy); }
.act-orange { background:var(--orange-bg); border-color:#fed7aa; color:var(--orange); }
.act-purple { background:var(--sa-purple-faint); border-color:rgba(124,58,237,.2); color:var(--sa-purple); }
.act-amber  { background:var(--amber-bg); border-color:rgba(217,119,6,.2); color:var(--amber); }

.user-cell { display:flex; flex-direction:column; gap:1px; }
.user-name  { font-size:13px; font-weight:700; color:var(--text); }
.user-email { font-size:11px; color:var(--text-muted); }
.user-meta  { display:flex; gap:.3rem; flex-wrap:wrap; margin-top:2px; }
.ua-short { font-size:10px; color:var(--grey-dark); font-family:monospace; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ip-mono  { font-family:monospace; font-size:12px; font-weight:700; color:var(--navy); }

/* Super admins */
.sa-member-row { display:flex; align-items:center; gap:.85rem; padding:.85rem 1.1rem; border-bottom:1px solid var(--grey-mid); }
.sa-member-row:last-child { border-bottom:none; }
.sa-av { width:38px; height:38px; border-radius:50%; background:var(--sa-purple-dark); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#c4b5fd; flex-shrink:0; border:2px solid rgba(124,58,237,.3); }
.sa-member-name  { font-size:14px; font-weight:700; color:var(--text); }
.sa-member-email { font-size:12px; color:var(--text-muted); margin-top:1px; }
.sa-member-meta  { display:flex; gap:.35rem; flex-wrap:wrap; margin-top:3px; }

/* System health */
.health-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; padding:1.1rem; }
@media(max-width:900px){ .health-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .health-grid { grid-template-columns:1fr; } }
.health-item { padding:.85rem 1rem; background:var(--grey); border:1px solid var(--grey-mid); display:flex; align-items:flex-start; gap:.65rem; }
.health-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:3px; }
.health-dot.ok   { background:var(--green); box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.health-dot.warn { background:var(--amber); }
.health-dot.fail { background:var(--red); }
.health-lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); margin-bottom:.15rem; }
.health-val { font-size:13px; font-weight:700; color:var(--text); }

/* Pagination */
.sa-pagination { padding:.75rem 1.1rem; background:var(--grey); border-top:1px solid var(--grey-mid); display:flex; align-items:center; justify-content:space-between; gap:.5rem; flex-wrap:wrap; font-size:12px; color:var(--text-muted); }
.sa-pagination { padding:.75rem 1.1rem; background:var(--grey); border-top:1px solid var(--grey-mid); display:flex; align-items:center; justify-content:space-between; gap:.5rem; flex-wrap:wrap; font-size:12px; color:var(--text-muted); }
.sa-pagination svg { width:12px; height:12px; display:inline-block; vertical-align:middle; }
.al-pagination svg { width:12px; height:12px; display:inline-block; vertical-align:middle; }
.lh-pagination svg { width:12px; height:12px; display:inline-block; vertical-align:middle; }
@keyframes fadeUp{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:none;}}
.tab-pane { display:none; }
.tab-pane.active { display:block; animation:fadeUp .2s ease; }

@media(max-width:700px){
    .data-table th:nth-child(3),.data-table td:nth-child(3),
    .data-table th:nth-child(4),.data-table td:nth-child(4){ display:none; }
}


/* ════════════════════════════════════════════════════════════════
   LOGIN HISTORY — PREMIUM REDESIGN
════════════════════════════════════════════════════════════════ */

/* ── Hero metric strip ── */
.lh-hero {
    background: linear-gradient(135deg, #0d0025 0%, #1a0050 50%, #0d1a40 100%);
    position: relative; overflow: hidden;
    margin-bottom: 1rem;
    border: 1px solid rgba(124,58,237,.3);
    box-shadow: 0 8px 32px rgba(0,0,0,.15), 0 0 0 1px rgba(124,58,237,.15);
}
.lh-hero::before {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(
        -55deg, transparent, transparent 40px,
        rgba(124,58,237,.025) 40px, rgba(124,58,237,.025) 41px
    );
}
.lh-hero::after {
    content: ''; position: absolute; top: -40%; right: -5%;
    width: 40%; padding-top: 40%; border-radius: 50%;
    background: radial-gradient(circle, rgba(124,58,237,.12) 0%, transparent 70%);
    pointer-events: none;
}
.lh-hero-head {
    padding: 1.25rem 1.5rem .85rem;
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; position: relative; z-index: 1;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.lh-hero-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .18em; color: rgba(167,139,250,.6); margin-bottom: .3rem;
    display: flex; align-items: center; gap: .5rem;
}
.lh-hero-title::before {
    content: ''; width: 16px; height: 2px; background: var(--sa-purple);
    display: inline-block;
}
.lh-hero-heading {
    font-size: 1.4rem; font-weight: 700; color: #fff; letter-spacing: -.01em;
}
.lh-live-pill {
    display: flex; align-items: center; gap: .45rem;
    background: rgba(74,222,128,.1); border: 1px solid rgba(74,222,128,.25);
    padding: .3rem .75rem; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .1em; color: #4ade80;
}
.lh-live-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #4ade80;
    animation: lhPulse 2s ease-in-out infinite;
}
@keyframes lhPulse {
    0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,.2); }
    50%      { box-shadow: 0 0 0 5px rgba(74,222,128,.05); }
}

/* ── Metric tiles ── */
.lh-metrics {
    display: grid; grid-template-columns: repeat(6, 1fr);
    position: relative; z-index: 1;
}
@media(max-width:1100px){ .lh-metrics { grid-template-columns: repeat(3,1fr); } }
@media(max-width:640px)  { .lh-metrics { grid-template-columns: repeat(2,1fr); } }
.lh-metric {
    padding: 1rem 1.25rem;
    border-right: 1px solid rgba(255,255,255,.06);
    position: relative; overflow: hidden; cursor: default;
    transition: background .15s;
}
.lh-metric:hover { background: rgba(255,255,255,.03); }
.lh-metric:last-child { border-right: none; }
.lh-metric-icon {
    font-size: 1.1rem; margin-bottom: .45rem;
    display: block;
}
.lh-metric-val {
    font-size: 2rem; font-weight: 700; line-height: 1;
    letter-spacing: -.02em;
    background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,.7) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.lh-metric-val.green {
    background: linear-gradient(135deg, #4ade80, #22c55e);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.lh-metric-val.red {
    background: linear-gradient(135deg, #f87171, #ef4444);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.lh-metric-val.amber {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.lh-metric-val.purple {
    background: linear-gradient(135deg, #c4b5fd, #a78bfa);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.lh-metric-lbl {
    font-size: 9px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: rgba(255,255,255,.3); margin-top: .35rem;
}
.lh-metric-trend {
    font-size: 10px; color: rgba(255,255,255,.25); margin-top: 2px;
    display: flex; align-items: center; gap: .25rem;
}
.lh-metric-bg-icon {
    position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
    font-size: 2.8rem; opacity: .04; pointer-events: none; user-select: none;
}

/* ── Chart area ── */
.lh-chart-section {
    background: #fff; border: 1px solid var(--grey-mid);
    margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(0,51,102,.06);
    overflow: hidden;
}
.lh-chart-header {
    padding: .9rem 1.25rem;
    background: var(--sa-purple-dark);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
}
.lh-chart-header-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: rgba(167,139,250,.7);
    display: flex; align-items: center; gap: .5rem;
}
.lh-chart-header-title::before {
    content: ''; width: 10px; height: 2px; background: var(--sa-purple);
}
.lh-chart-legend {
    display: flex; align-items: center; gap: 1rem;
}
.lh-legend-item {
    display: flex; align-items: center; gap: .4rem;
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.4);
    text-transform: uppercase; letter-spacing: .06em;
}
.lh-legend-swatch {
    width: 12px; height: 12px; border-radius: 2px;
}
.lh-legend-swatch.success { background: rgba(74,222,128,.8); }
.lh-legend-swatch.failed  { background: rgba(248,113,113,.8); }

.lh-chart-body {
    padding: 1.25rem 1.5rem 1rem;
    background: #fafbfd;
}
.lh-chart-canvas-wrap {
    position: relative; height: 180px;
}

.lh-chart-summary-row {
    display: flex; gap: 1px; border-top: 1px solid var(--grey-mid);
    background: var(--grey-mid);
}
.lh-chart-sum-tile {
    flex: 1; background: var(--grey); padding: .65rem 1rem;
    text-align: center;
}
.lh-chart-sum-val { font-size: 18px; font-weight: 700; line-height: 1; }
.lh-chart-sum-val.s-green { color: var(--green); }
.lh-chart-sum-val.s-red   { color: var(--red); }
.lh-chart-sum-val.s-amber { color: var(--amber); }
.lh-chart-sum-val.s-navy  { color: var(--navy); }
.lh-chart-sum-lbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--text-muted); margin-top: .2rem; }

/* ── Threat intelligence panel ── */
.lh-threat-panel {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    margin-bottom: 1rem;
}
@media(max-width:900px){ .lh-threat-panel { grid-template-columns: 1fr; } }

.lh-threat-card {
    background: #fff; border: 1px solid var(--grey-mid);
    box-shadow: 0 1px 4px rgba(0,51,102,.06);
    overflow: hidden;
}
.lh-threat-head {
    padding: .7rem 1rem;
    background: linear-gradient(135deg, #1a0a00, #2d1200);
    border-bottom: 2px solid var(--orange);
    display: flex; align-items: center; gap: .5rem;
}
.lh-threat-head-title {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: rgba(251,146,60,.7);
}
.lh-threat-head-badge {
    margin-left: auto; font-size: 10px; font-weight: 700;
    background: rgba(251,146,60,.15); border: 1px solid rgba(251,146,60,.25);
    color: #fb923c; padding: 2px 8px;
}

.lh-threat-row {
    padding: .65rem 1rem; border-bottom: 1px solid var(--grey-mid);
    display: flex; align-items: center; gap: .75rem;
    transition: background .1s;
}
.lh-threat-row:last-child { border-bottom: none; }
.lh-threat-row:hover { background: #fff7ed; }
.lh-threat-rank {
    width: 20px; height: 20px; background: var(--sa-purple-dark); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 700; color: #c4b5fd; flex-shrink: 0;
}
.lh-threat-ip {
    font-family: 'Courier New', monospace; font-size: 12px; font-weight: 700;
    color: var(--red); min-width: 120px;
}
.lh-threat-bar-wrap {
    flex: 1; height: 6px; background: var(--grey); position: relative;
    overflow: hidden;
}
.lh-threat-bar {
    height: 100%; position: absolute; left: 0; top: 0;
    background: linear-gradient(90deg, #C8102E, #f87171);
    transition: width .8s cubic-bezier(.16,1,.3,1);
}
.lh-threat-count {
    font-size: 11px; font-weight: 700; color: var(--red);
    background: var(--red-faint); border: 1px solid rgba(200,16,46,.2);
    padding: 2px 7px; min-width: 60px; text-align: right;
    font-family: monospace;
}

/* Device breakdown card */
.lh-device-head {
    padding: .7rem 1rem;
    background: linear-gradient(135deg, #001428, #002050);
    border-bottom: 2px solid var(--navy);
    display: flex; align-items: center; gap: .5rem;
}
.lh-device-head-title {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: rgba(147,197,253,.7);
}
.lh-device-row {
    padding: .7rem 1rem; border-bottom: 1px solid var(--grey-mid);
    display: flex; align-items: center; gap: .75rem;
    transition: background .1s;
}
.lh-device-row:last-child { border-bottom: none; }
.lh-device-row:hover { background: var(--navy-faint); }
.lh-device-icon {
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    background: var(--grey); border: 1px solid var(--grey-mid);
}
.lh-device-name { font-size: 12px; font-weight: 700; color: var(--text); flex: 1; }
.lh-device-bar-wrap { width: 100px; height: 6px; background: var(--grey); position: relative; overflow: hidden; }
.lh-device-bar {
    height: 100%; position: absolute; left: 0; top: 0;
    background: linear-gradient(90deg, var(--navy), #4a90d9);
    transition: width .8s cubic-bezier(.16,1,.3,1);
}
.lh-device-pct { font-size: 11px; font-weight: 700; color: var(--navy); min-width: 36px; text-align: right; font-family: monospace; }

/* ── Premium filter bar ── */
.lh-filter-bar {
    padding: .85rem 1.25rem;
    background: linear-gradient(135deg, #f8f6ff, #f2f5f9);
    border-bottom: 1px solid var(--grey-mid);
    display: flex; align-items: flex-end; gap: .65rem; flex-wrap: wrap;
    border-top: 2px solid var(--sa-purple);
}
.lh-filter-group {
    display: flex; flex-direction: column; gap: .3rem;
    flex: 1; min-width: 110px; max-width: 200px;
}
.lh-filter-label {
    font-size: 9px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: var(--sa-purple);
}
.lh-filter-input {
    background: #fff; border: 1px solid rgba(124,58,237,.2); padding: .4rem .65rem;
    color: var(--text); font-family: var(--font); font-size: 12px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.lh-filter-input:focus {
    border-color: var(--sa-purple);
    box-shadow: 0 0 0 3px rgba(124,58,237,.08);
}
.lh-filter-actions { display: flex; gap: .5rem; align-items: flex-end; }
.lh-filter-submit {
    padding: .4rem 1rem; background: var(--sa-purple-dark); border: 1px solid var(--sa-purple-dark);
    color: #fff; font-family: var(--font); font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em; cursor: pointer;
    transition: background .12s; display: flex; align-items: center; gap: .3rem;
}
.lh-filter-submit:hover { background: var(--sa-purple); }
.lh-filter-clear {
    padding: .4rem .8rem; background: transparent; border: 1px solid rgba(124,58,237,.25);
    color: var(--sa-purple); font-family: var(--font); font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em; cursor: pointer;
    text-decoration: none; transition: all .12s; display: flex; align-items: center; gap: .3rem;
}
.lh-filter-clear:hover { background: var(--sa-purple-faint); border-color: var(--sa-purple); }

/* ── Premium table ── */
.lh-table-card {
    background: #fff; border: 1px solid var(--grey-mid);
    box-shadow: 0 1px 4px rgba(0,51,102,.06);
    overflow: hidden;
}
.lh-table-head-bar {
    padding: .75rem 1.25rem;
    background: linear-gradient(135deg, var(--sa-purple-dark), #2d0060);
    display: flex; align-items: center; gap: .65rem; flex-wrap: wrap;
}
.lh-table-head-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .14em; color: rgba(196,181,253,.7);
    display: flex; align-items: center; gap: .5rem;
}
.lh-table-head-title::before { content:''; width:10px; height:2px; background:var(--sa-purple); }
.lh-table-count-pill {
    background: rgba(124,58,237,.25); border: 1px solid rgba(124,58,237,.4);
    color: #c4b5fd; font-size: 10px; font-weight: 700; padding: 2px 8px;
    letter-spacing: .06em;
}
.lh-table-fail-pill {
    background: rgba(200,16,46,.2); border: 1px solid rgba(200,16,46,.3);
    color: #fca5a5; font-size: 10px; font-weight: 700; padding: 2px 8px;
    letter-spacing: .06em;
}

.lh-premium-table { width: 100%; border-collapse: collapse; font-family: var(--font); }
.lh-premium-table thead tr {
    background: #f8f6ff;
    border-bottom: 1px solid rgba(124,58,237,.15);
}
.lh-premium-table th {
    padding: .55rem .85rem; text-align: left; font-size: 9px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .14em; color: var(--sa-purple);
    white-space: nowrap;
}

/* Row types */
.lh-tr { border-bottom: 1px solid var(--grey-mid); transition: all .12s; position: relative; }
.lh-tr:last-child { border-bottom: none; }
.lh-tr.success-row:hover { background: rgba(22,163,74,.03); }
.lh-tr.failed-row  { background: rgba(200,16,46,.012); }
.lh-tr.failed-row:hover { background: rgba(200,16,46,.04); }

/* Left accent stripe */
.lh-tr td:first-child { padding-left: 0 !important; position: relative; }
.lh-tr td:first-child::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
}
.lh-tr.success-row td:first-child::before { background: var(--green); }
.lh-tr.failed-row  td:first-child::before { background: var(--red); }

.lh-td { padding: .7rem .85rem; vertical-align: middle; }

/* Status indicator */
.lh-status-wrap { display: flex; align-items: center; gap: .5rem; padding-left: .6rem; }
.lh-status-icon {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .75rem;
}
.lh-status-icon.ok   { background: var(--green-bg); border: 1.5px solid rgba(22,163,74,.3); }
.lh-status-icon.fail { background: var(--red-faint); border: 1.5px solid rgba(200,16,46,.3); }
.lh-status-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
.lh-status-label.ok   { color: var(--green); }
.lh-status-label.fail { color: var(--red); }
.lh-status-time { font-size: 9px; color: var(--grey-dark); margin-top: 1px; }

/* User cell */
.lh-user-wrap { display: flex; align-items: center; gap: .6rem; }
.lh-user-av {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    background: var(--navy);
}
.lh-user-av.unknown { background: var(--grey-dark); }
.lh-user-name  { font-size: 12px; font-weight: 700; color: var(--text); }
.lh-user-email { font-size: 10px; color: var(--text-muted); margin-top: 1px; }
.lh-user-badge { font-size: 9px; font-weight: 700; padding: 1px 5px; margin-top: 2px; display: inline-flex; text-transform: uppercase; letter-spacing: .06em; }
.lh-user-badge.callsign { background: var(--navy-faint); border: 1px solid rgba(0,51,102,.2); color: var(--navy); font-family: monospace; }

/* IP cell */
.lh-ip-wrap { display: flex; flex-direction: column; gap: 2px; }
.lh-ip-addr {
    font-family: 'Courier New', monospace; font-size: 12px; font-weight: 700;
    color: var(--navy); background: var(--navy-faint); padding: 2px 7px;
    display: inline-block; border: 1px solid rgba(0,51,102,.12);
}
.lh-ip-addr.threat { color: var(--red); background: var(--red-faint); border-color: rgba(200,16,46,.2); }
.lh-ip-flag { font-size: 9px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-top: 1px; }

/* Device cell */
.lh-device-wrap { display: flex; align-items: center; gap: .5rem; }
.lh-device-emoji { font-size: 1.1rem; flex-shrink: 0; }
.lh-device-type { font-size: 12px; font-weight: 700; color: var(--text-mid); }
.lh-device-browser { font-size: 10px; color: var(--text-muted); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px; }

/* Duration cell */
.lh-duration {
    font-family: 'Courier New', monospace; font-size: 12px; font-weight: 700;
    color: var(--text-mid); background: var(--grey); padding: 2px 7px;
    border: 1px solid var(--grey-mid); display: inline-block;
}

/* Empty state */
.lh-empty {
    padding: 4rem 1rem; text-align: center;
}
.lh-empty-icon { font-size: 3rem; opacity: .1; margin-bottom: 1rem; display: block; }
.lh-empty-text { font-size: 14px; color: var(--text-muted); font-weight: 700; }
.lh-empty-sub  { font-size: 12px; color: var(--grey-dark); margin-top: .3rem; }

/* Premium pagination */
.lh-pagination {
    padding: .85rem 1.25rem;
    background: linear-gradient(135deg, #f8f6ff, #f2f5f9);
    border-top: 1px solid rgba(124,58,237,.1);
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; flex-wrap: wrap;
}
.lh-pagination-info {
    font-size: 11px; font-weight: 700; color: var(--sa-purple);
    text-transform: uppercase; letter-spacing: .08em;
}
</style>


{{-- ── HERO ── --}}
<div class="sa-hero">
    <div class="sa-hero-inner">
        <div class="sa-hero-brand">
            <div class="sa-logo"><span>RAY<br>NET</span></div>
            <div>
                <div class="sa-org">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
                <div class="sa-sub">Super Admin Control Panel</div>
            </div>
        </div>
        <div class="sa-hero-right">
            <div class="sa-who">
                <div class="sa-who-dot"></div>
                <div class="sa-who-text">★ Super Administrator — {{ auth()->user()->name }}</div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="sa-back">← Admin Dashboard</a>
        </div>
    </div>

    <div class="sa-stats-strip">
        <div class="sa-stat">
            <div class="sa-stat-lbl">Active Sessions</div>
            <div class="sa-stat-val {{ $authSessCount > 20 ? 'amber' : '' }}">{{ $sessions->count() }}</div>
            <div class="sa-stat-sub">{{ $authSessCount }} auth · {{ $guestSessCount }} guest</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Logins Today</div>
            <div class="sa-stat-val green">{{ $loginsToday }}</div>
            <div class="sa-stat-sub">Successful</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Failed Logins</div>
            <div class="sa-stat-val {{ $failedToday > 0 ? 'red' : '' }}">{{ $failedToday }}</div>
            <div class="sa-stat-sub">Today</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Bot Sessions</div>
            <div class="sa-stat-val {{ $botCount > 0 ? 'amber' : '' }}">{{ $botCount }}</div>
            <div class="sa-stat-sub">Crawlers detected</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Audit Events</div>
            <div class="sa-stat-val">{{ $auditToday }}</div>
            <div class="sa-stat-sub">Today</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Total Users</div>
            <div class="sa-stat-val">{{ $totalUsers }}</div>
            <div class="sa-stat-sub">Registered</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-lbl">Maintenance</div>
            <div class="sa-stat-val {{ $maintOn ? 'red' : 'green' }}">{{ $maintOn ? 'ON' : 'OFF' }}</div>
            <div class="sa-stat-sub">Portal status</div>
        </div>
    </div>

    <div class="sa-tab-bar">
        <button class="sa-tab" data-tab="maintenance">🔧 Maintenance <span class="sa-tab-badge {{ $maintOn ? 'red' : '' }}">{{ $maintOn ? 'LIVE' : 'OFF' }}</span></button>
        <button class="sa-tab" data-tab="sessions">🖥 Sessions <span class="sa-tab-badge">{{ $sessions->count() }}</span></button>
        <button class="sa-tab" data-tab="login-history">🔐 Login History <span class="sa-tab-badge {{ $failedToday > 0 ? 'red' : '' }}">{{ $failedToday > 0 ? $failedToday.' failed' : $loginsToday }}</span></button>
        <button class="sa-tab" data-tab="audit-log">📋 Audit Log <span class="sa-tab-badge">{{ $auditToday }} today</span></button>
        <button class="sa-tab" data-tab="super-admins">★ Super Admins <span class="sa-tab-badge">{{ $superAdminCount }}</span></button>
        <button class="sa-tab" data-tab="system">⚙ System Health</button>
    </div>
</div>

<div class="sa-wrap">

@if(session('status'))
    <div class="sa-alert ok">✓ {{ session('status') }}</div>
@endif
@if(session('error') || $errors->any())
    <div class="sa-alert err">✕ {{ session('error') ?? $errors->first() }}</div>
@endif

{{-- ══════════════════════════════════
     TAB: MAINTENANCE
══════════════════════════════════ --}}
<div class="tab-pane" id="tab-maintenance">
    <div class="sa-card">
        <div class="maint-live-bar" style="background:{{ $maintOn ? '#fff7ed' : '#eef7f2' }};border-bottom:1px solid {{ $maintOn ? '#fed7aa' : '#b8ddc9' }};">
            <div class="maint-live-indicator">
                <div class="{{ $maintOn ? 'maint-on-dot' : 'maint-off-dot' }}"></div>
                <div>
                    <div class="maint-live-label">Maintenance mode is <strong>{{ $maintOn ? 'ACTIVE' : 'INACTIVE' }}</strong></div>
                    <div class="maint-live-sub">
                        @if($maintOn)
                            Portal is offline for non-admin users
                            @if($maintStarted) · Active since {{ \Carbon\Carbon::parse($maintStarted)->diffForHumans() }} @endif
                        @else
                            Portal is publicly accessible — all routes available
                        @endif
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:.65rem;">
                @if($maintOn)
                    <div class="maint-timer" id="maintTimer">00:00:00</div>
                @else
                    <div class="maint-timer off">● LIVE</div>
                @endif
                @if($maintAutoOff && $maintOn)
                    <div style="font-size:11px;font-weight:700;color:var(--amber);background:var(--amber-bg);border:1px solid rgba(217,119,6,.2);padding:.3rem .75rem;">
                        Auto-off: {{ \Carbon\Carbon::parse($maintAutoOff)->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>
        <div class="maint-impact-row">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Impact if enabled:</span>
            <div class="maint-impact-chip mic-auth">⚡ {{ $authSessCount }} authenticated sessions affected</div>
            <div class="maint-impact-chip mic-guest">👤 {{ $guestSessCount }} guest sessions affected</div>
            <div class="maint-impact-chip mic-ok">✓ Admins bypass automatically</div>
        </div>
        <div class="sa-card-head" style="border-top:1px solid var(--grey-mid);">
            <span class="sa-card-title">🎨 Quick Presets</span>
            <span style="font-size:11px;color:var(--text-muted);margin-left:auto;">Click to pre-fill the form below</span>
        </div>
        <div class="maint-presets">
            <button class="maint-preset-btn" type="button" data-title="Back Soon" data-headline="Planned Maintenance" data-msg="{{ \App\Helpers\RaynetSetting::groupName() }} Members Portal is temporarily offline for scheduled maintenance. We'll be back shortly. Thank you for your patience." onclick="applyPreset(this)">
                <div class="maint-preset-icon">🔧</div><div class="maint-preset-name">Planned Maintenance</div><div class="maint-preset-desc">Scheduled downtime with friendly message</div>
            </button>
            <button class="maint-preset-btn" type="button" data-title="Emergency Downtime" data-headline="Unplanned Outage" data-msg="We are currently experiencing an unexpected issue and the portal is temporarily unavailable. Our team is working to restore access as quickly as possible." onclick="applyPreset(this)">
                <div class="maint-preset-icon">🚨</div><div class="maint-preset-name">Emergency Outage</div><div class="maint-preset-desc">Unplanned, urgent downtime message</div>
            </button>
            <button class="maint-preset-btn" type="button" data-title="Brief Outage" data-headline="System Update in Progress" data-msg="We're performing a quick system update. The portal will be back online in just a few minutes." onclick="applyPreset(this)">
                <div class="maint-preset-icon">⚡</div><div class="maint-preset-name">Brief Outage</div><div class="maint-preset-desc">Short downtime, minutes not hours</div>
            </button>
            <button class="maint-preset-btn" type="button" data-title="Training Day" data-headline="Portal Temporarily Suspended" data-msg="The portal is temporarily suspended during today's training exercise. Normal access will resume after the exercise concludes." onclick="applyPreset(this)">
                <div class="maint-preset-icon">🎓</div><div class="maint-preset-name">Training Exercise</div><div class="maint-preset-desc">Suspend portal during RAYNET exercises</div>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.super.maintenance') }}">
            @csrf
            <div class="maint-form-wrap">
                <div>
                    <label class="maint-toggle-card {{ $maintOn ? 'active' : '' }}" id="maintToggleCard">
                        <input type="checkbox" name="maintenance_mode" id="maintToggle" value="1" {{ $maintOn ? 'checked' : '' }} onchange="updateMaintPreview();this.closest('label').classList.toggle('active',this.checked)">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--text);">🔧 Maintenance mode active</div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">All non-admin pages return 503. Admins bypass automatically.</div>
                        </div>
                    </label>
                    <div style="height:.85rem;"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        <div class="maint-field">
                            <label class="maint-label">Page Title</label>
                            <input type="text" name="maintenance_title" id="maintTitle" class="maint-input" value="{{ old('maintenance_title', $maintTitle) }}" placeholder="Back Soon" oninput="updateMaintPreview()">
                        </div>
                        <div class="maint-field">
                            <label class="maint-label">Headline / Subheading</label>
                            <input type="text" name="maintenance_headline" id="maintHeadline" class="maint-input" value="{{ old('maintenance_headline', $maintHeadline) }}" placeholder="e.g. Planned Maintenance" oninput="updateMaintPreview()">
                        </div>
                    </div>
                    <div class="maint-field">
                        <label class="maint-label">Message <span style="text-transform:none;letter-spacing:0;font-weight:normal;">(shown to visitors)</span></label>
                        <textarea name="maintenance_message" id="maintMsg" class="maint-input" rows="3" placeholder="We'll be back soon…" style="resize:vertical;" oninput="updateMaintPreview()">{{ old('maintenance_message', $maintMsg) }}</textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        <div class="maint-field">
                            <label class="maint-label">Expected Return Time</label>
                            <input type="datetime-local" name="maintenance_return_at" id="maintReturnAt" class="maint-input" value="{{ old('maintenance_return_at', $maintReturnAt) }}" oninput="updateMaintPreview()">
                        </div>
                        <div class="maint-field">
                            <label class="maint-label">Contact Email</label>
                            <input type="email" name="maintenance_contact" class="maint-input" value="{{ old('maintenance_contact', $maintContact) }}" placeholder="gc@{{ \App\Helpers\RaynetSetting::siteUrl() }}" oninput="updateMaintPreview()">
                        </div>
                    </div>
                    <div class="sa-card-head" style="margin:-1px -1px 0;background:rgba(124,58,237,.05);border:1px solid rgba(124,58,237,.15);">
                        <span class="sa-card-title" style="color:var(--sa-purple);">⏱ Auto-disable Timer</span>
                        <span style="font-size:11px;color:var(--text-muted);margin-left:auto;">Automatically turn off maintenance at a set time</span>
                    </div>
                    <div style="padding:.85rem;background:rgba(124,58,237,.03);border:1px solid rgba(124,58,237,.1);margin-bottom:.85rem;">
                        <div class="maint-schedule-grid">
                            <div class="maint-field" style="margin-bottom:0;">
                                <label class="maint-label">Auto-disable at</label>
                                <input type="datetime-local" name="maintenance_auto_disable_at" class="maint-input" value="{{ old('maintenance_auto_disable_at', $maintAutoOff) }}">
                            </div>
                            <div class="maint-field" style="margin-bottom:0;">
                                <label class="maint-label">Quick duration</label>
                                <select class="maint-input" id="quickDuration" onchange="setQuickDuration(this.value)">
                                    <option value="">— Select preset —</option>
                                    <option value="15">15 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="60">1 hour</option>
                                    <option value="120">2 hours</option>
                                    <option value="240">4 hours</option>
                                    <option value="480">8 hours</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:center;padding-top:.35rem;">
                        @if($maintOn)
                        <button type="submit" class="btn btn-green">✓ Update &amp; Keep Active</button>
                        <button type="submit" name="maintenance_mode" value="0" class="btn btn-ghost" onclick="document.getElementById('maintToggle').checked=false;">✕ Disable Maintenance Mode</button>
                        @else
                        <button type="submit" class="btn btn-orange">⚠ Enable Maintenance Mode</button>
                        <button type="submit" class="btn btn-ghost">💾 Save Settings Only</button>
                        @endif
                    </div>
                </div>
                <div class="maint-preview-pane">
                    <div class="maint-preview-label">Preview — Visitor View<span class="maint-preview-live">● LIVE</span></div>
                    <div class="maint-preview-screen">
                        <div class="maint-preview-icon-wrap">🔧</div>
                        <div>
                            <div class="maint-preview-title-text" id="prevTitle">{{ $maintTitle ?: 'Back Soon' }}</div>
                            <div class="maint-preview-headline-text" id="prevHeadline">{{ $maintHeadline ?: \App\Helpers\RaynetSetting::groupName() }}</div>
                        </div>
                        <div class="maint-preview-msg-text" id="prevMsg">{{ $maintMsg ?: \App\Helpers\RaynetSetting::groupName() . ' Members Portal is temporarily offline.' }}</div>
                        <div id="prevReturn" style="display:{{ $maintReturnAt ? 'block' : 'none' }};font-size:11px;font-weight:700;color:rgba(255,255,255,.5);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);padding:.3rem .65rem;">
                            Expected back: <span id="prevReturnVal">{{ $maintReturnAt ? \Carbon\Carbon::parse($maintReturnAt)->format('D d M H:i') : '' }}</span>
                        </div>
                    </div>
                    <div class="maint-preview-footer">{{ \App\Helpers\RaynetSetting::groupName() }} (Group {{ \App\Helpers\RaynetSetting::groupNumber() }}) · {{ \App\Helpers\RaynetSetting::groupRegion() }}</div>
                    <div style="padding:.65rem .85rem;border-top:1px solid var(--grey-mid);background:var(--grey);display:flex;flex-direction:column;gap:.3rem;">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);margin-bottom:.2rem;">System Status</div>
                        @foreach([['DB',$dbOk,$dbOk?'Connected':'Error'],['Cache',$cacheOk,$cacheOk?'Working':'Check config'],['Storage',$storageOk,$storageOk?'Writable':'Read-only'],['Portal',true,'Laravel '.app()->version()]] as [$lbl,$ok,$val])
                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;">
                            <span style="color:var(--text-muted);font-weight:700;">{{ $lbl }}</span>
                            <span style="color:{{ $ok ? 'var(--green)' : 'var(--red)' }};font-weight:700;">{{ $ok ? '●' : '✕' }} {{ $val }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
        <div class="sa-card-head" style="border-top:2px solid var(--grey-mid);">
            <span class="sa-card-title">🌐 IP Bypass Whitelist</span>
            <span style="font-size:11px;color:var(--text-muted);margin-left:auto;">IPs that can access the portal even during maintenance</span>
        </div>
        <div style="padding:1rem 1.25rem;">
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.85rem;min-height:36px;" id="ipTagList">
                @if(empty($maintWhitelist))<span style="font-size:12px;color:var(--grey-dark);font-style:italic;">No IPs whitelisted — admins bypass automatically via role.</span>@endif
                @foreach($maintWhitelist as $ip)
                <div class="ip-tag"><span>{{ $ip }}</span>
                    <form method="POST" action="{{ route('admin.super.maintenance.whitelist.remove') }}" style="display:contents;">@csrf<input type="hidden" name="ip" value="{{ $ip }}"><button type="submit" class="ip-tag-remove" title="Remove {{ $ip }}">×</button></form>
                </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.super.maintenance.whitelist.add') }}" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                @csrf
                <input type="text" name="ip" class="maint-input" placeholder="e.g. 192.168.1.100 or 203.0.113.0/24" style="max-width:280px;" pattern="^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$">
                <button type="submit" class="btn btn-navy">+ Add IP</button>
                <button type="button" class="btn btn-ghost" onclick="addMyIp()">+ Add my IP</button>
                <span style="font-size:11px;color:var(--grey-dark);">Your IP: <strong id="myIpDisplay">{{ request()->ip() }}</strong></span>
            </form>
        </div>
        <div class="maint-checklist">
            <div class="maint-checklist-title">📋 Maintenance Checklist</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.5rem;">Before enabling</div>
                    @foreach(['Notify members via broadcast message','Note current active session count','Confirm auto-disable timer is set','Back up recent data if needed','Inform committee via group radio/chat'] as $item)
                    <div class="maint-check-item"><input type="checkbox"><span>{{ $item }}</span></div>
                    @endforeach
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.5rem;">After disabling</div>
                    @foreach(['Verify portal loads correctly','Check no error logs from downtime','Clear broadcast message if set','Send update to members if needed','Log work completed in audit notes'] as $item)
                    <div class="maint-check-item"><input type="checkbox"><span>{{ $item }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="sa-card-head" style="border-top:2px solid var(--grey-mid);">
            <span class="sa-card-title">📜 Recent Maintenance History</span>
        </div>
        @php $maintHistory = \App\Models\AdminAuditLog::where('action','like','%Maintenance%')->orderByDesc('created_at')->limit(8)->get(); @endphp
        @if($maintHistory->isEmpty())
        <div style="padding:1.5rem;text-align:center;font-size:12px;color:var(--grey-dark);font-style:italic;">No maintenance history recorded yet.</div>
        @else
        <table class="data-table">
            <thead><tr><th>When</th><th>Action</th><th>Admin</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
            @foreach($maintHistory as $entry)
            <tr>
                <td><div style="font-size:12px;font-weight:700;">{{ $entry->created_at->format('d M Y H:i') }}</div><div style="font-size:10px;color:var(--grey-dark);">{{ $entry->created_at->diffForHumans() }}</div></td>
                <td>@if(str_contains($entry->action,'ON'))<span class="act act-orange">Enabled</span>@else<span class="act act-green">Disabled</span>@endif</td>
                <td style="font-size:12px;font-weight:700;color:var(--navy);">{{ $entry->admin->name ?? '—' }}</td>
                <td style="font-size:11px;color:var(--text-muted);">{{ Str::limit($entry->description ?? '',60) }}</td>
                <td><span style="font-family:monospace;font-size:11px;">{{ $entry->ip_address }}</span></td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>{{-- /tab-maintenance --}}


{{-- ══════════════════════════════════
     TAB: SESSIONS
══════════════════════════════════ --}}
<div class="tab-pane" id="tab-sessions">
    <div class="sa-card">
        <div class="sess-summary">
            <div class="sess-sum-tile auth"><div class="sess-sum-num">{{ $authSessCount }}</div><div class="sess-sum-lbl">Authenticated</div></div>
            <div class="sess-sum-tile guest"><div class="sess-sum-num">{{ $guestSessCount }}</div><div class="sess-sum-lbl">Guest / Unauthenticated</div></div>
            <div class="sess-sum-tile bot"><div class="sess-sum-num">{{ $botCount }}</div><div class="sess-sum-lbl">Bots / Crawlers</div></div>
            <div class="sess-sum-tile total"><div class="sess-sum-num">{{ $sessions->count() }}</div><div class="sess-sum-lbl">Total Sessions</div></div>
        </div>
        <div class="sa-card-head">
            <span class="sa-card-title">🖥 All Active Sessions</span>
            <div class="sa-card-head-right">
                <span style="font-size:11px;color:var(--text-muted);">{{ $sessions->count() }} total</span>
                <form method="POST" action="{{ route('admin.super.sessions.terminate-all') }}" onsubmit="return confirm('Kill ALL {{ $sessions->count() }} sessions? Every user (including you) will be logged out.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red btn-sm">⚡ Kill All Sessions</button>
                </form>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.super.index') }}#sessions">
            <div class="filter-strip">
                <div class="ff"><label>Search user / IP</label><input type="text" name="search_sess" value="{{ $searchSess }}" placeholder="Name, email or IP…"></div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if($searchSess)<a href="{{ route('admin.super.index') }}#sessions" class="btn btn-ghost btn-sm">Clear</a>@endif
            </div>
        </form>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th></th><th>User</th><th>Device / Browser</th><th>IP Address</th><th>Last Active</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($sessions as $sess)
                    @php
                        $ua=$sess->user_agent??'';
                        $isBot=collect(['bot','crawl','spider','claude','googlebot','bingbot','ahrefsbot','semrushbot'])->contains(fn($p)=>str_contains(strtolower($ua),$p));
                        $isMobile=str_contains(strtolower($ua),'mobile')||str_contains(strtolower($ua),'iphone')||str_contains(strtolower($ua),'android');
                        $deviceIcon=$isBot?'🤖':($isMobile?'📱':'💻');
                        $isMe=$sess->user_id===auth()->id();
                        $age=now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp($sess->last_activity));
                    @endphp
                    <tr>
                        <td style="text-align:center;width:32px;">
                            @if($age<5)<span style="width:8px;height:8px;border-radius:50%;background:#16a34a;display:inline-block;box-shadow:0 0 0 2px rgba(22,163,74,.2);"></span>
                            @elseif($age<60)<span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
                            @else<span style="width:8px;height:8px;border-radius:50%;background:var(--grey-dark);display:inline-block;"></span>@endif
                        </td>
                        <td>
                            <div class="user-cell">
                                @if($sess->user_name)
                                    <div class="user-name">{{ $sess->user_name }}</div>
                                    <div class="user-email">{{ $sess->user_email }}</div>
                                    <div class="user-meta">
                                        @if($sess->callsign)<span class="badge b-navy" style="font-family:monospace;font-size:9px;">{{ $sess->callsign }}</span>@endif
                                        @if($sess->is_super_admin)<span class="badge b-super">★ Super</span>@elseif($sess->is_admin)<span class="badge b-purple">Admin</span>@endif
                                        @if($isMe)<span class="badge b-ok">Your session</span>@endif
                                    </div>
                                @else
                                    <div class="user-name" style="color:var(--grey-dark);">{{ $isBot?'Bot / Crawler':'Guest' }}</div>
                                    <span class="badge {{ $isBot?'b-bot':'b-grey' }}">{{ $isBot?'🤖 Automated':'Unauthenticated' }}</span>
                                @endif
                            </div>
                        </td>
                        <td><div style="font-size:13px;">{{ $deviceIcon }} {{ $isMobile?'Mobile':($isBot?'Bot':'Desktop') }}</div><div class="ua-short" title="{{ $ua }}">{{ $ua }}</div></td>
                        <td><span class="ip-mono">{{ $sess->ip_address }}</span></td>
                        <td><div style="font-size:12px;font-weight:700;">{{ \Carbon\Carbon::createFromTimestamp($sess->last_activity)->diffForHumans() }}</div><div style="font-size:10px;color:var(--grey-dark);">{{ \Carbon\Carbon::createFromTimestamp($sess->last_activity)->format('d M H:i') }}</div></td>
                        <td>
                            @if($isMe)<span style="font-size:11px;color:var(--text-muted);font-style:italic;">Your session</span>
                            @else
                            <form method="POST" action="{{ route('admin.super.sessions.terminate',$sess->id) }}" style="display:contents;" onsubmit="return confirm('Kill this session?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-red btn-sm">✕ Kill</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-td">No active sessions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>{{-- /tab-sessions --}}


{{-- ══════════════════════════════════════════════════════════
     TAB: LOGIN HISTORY  ── PREMIUM REDESIGN
══════════════════════════════════════════════════════════ --}}
<div class="tab-pane" id="tab-login-history">

    {{-- ── HERO METRIC STRIP ── --}}
    <div class="lh-hero">
        <div class="lh-hero-head">
            <div>
                <div class="lh-hero-title">Security Intelligence</div>
                <div class="lh-hero-heading">Login History &amp; Access Monitoring</div>
            </div>
            <div class="lh-live-pill">
                <div class="lh-live-dot"></div>
                Live monitoring
            </div>
        </div>
        <div class="lh-metrics">
            <div class="lh-metric">
                <span class="lh-metric-icon">✓</span>
                <div class="lh-metric-val green" data-count="{{ $loginsToday }}">{{ $loginsToday }}</div>
                <div class="lh-metric-lbl">Successful today</div>
                <div class="lh-metric-trend">↑ {{ $loginsThisWeek }} this week</div>
                <div class="lh-metric-bg-icon">✓</div>
            </div>
            <div class="lh-metric">
                <span class="lh-metric-icon">✕</span>
                <div class="lh-metric-val {{ $failedToday > 0 ? 'red' : '' }}" data-count="{{ $failedToday }}">{{ $failedToday }}</div>
                <div class="lh-metric-lbl">Failed today</div>
                <div class="lh-metric-trend">{{ $failedThisWeek }} this week</div>
                <div class="lh-metric-bg-icon">✕</div>
            </div>
            <div class="lh-metric">
                <span class="lh-metric-icon">🌐</span>
                <div class="lh-metric-val purple" data-count="{{ $uniqueIpsToday }}">{{ $uniqueIpsToday }}</div>
                <div class="lh-metric-lbl">Unique IPs today</div>
                <div class="lh-metric-trend">Distinct sources</div>
                <div class="lh-metric-bg-icon">🌐</div>
            </div>
            <div class="lh-metric">
                <span class="lh-metric-icon">📱</span>
                <div class="lh-metric-val amber" data-count="{{ $mobileLogins }}">{{ $mobileLogins }}</div>
                <div class="lh-metric-lbl">Mobile logins today</div>
                <div class="lh-metric-trend">{{ $loginsToday > 0 ? round(($mobileLogins/$loginsToday)*100) : 0 }}% of total</div>
                <div class="lh-metric-bg-icon">📱</div>
            </div>
            <div class="lh-metric">
                <span class="lh-metric-icon">📋</span>
                <div class="lh-metric-val" data-count="{{ $loginHistoryTotal }}">{{ number_format($loginHistoryTotal) }}</div>
                <div class="lh-metric-lbl">Total records</div>
                <div class="lh-metric-trend">All time</div>
                <div class="lh-metric-bg-icon">📋</div>
            </div>
            <div class="lh-metric">
                <span class="lh-metric-icon">⚠</span>
                <div class="lh-metric-val {{ $failedByIp->isNotEmpty() ? 'red' : 'green' }}" data-count="{{ $failedByIp->count() }}">{{ $failedByIp->count() }}</div>
                <div class="lh-metric-lbl">Threat IPs (7d)</div>
                <div class="lh-metric-trend">Repeated failures</div>
                <div class="lh-metric-bg-icon">⚠</div>
            </div>
        </div>
    </div>

    {{-- ── CHART ── --}}
    <div class="lh-chart-section">
        <div class="lh-chart-header">
            <div class="lh-chart-header-title">Login Activity — Last 24 Hours</div>
            <div class="lh-chart-legend">
                <div class="lh-legend-item"><div class="lh-legend-swatch success"></div>Successful</div>
                <div class="lh-legend-item"><div class="lh-legend-swatch failed"></div>Failed</div>
            </div>
        </div>
        <div class="lh-chart-body">
            <div class="lh-chart-canvas-wrap">
                <canvas id="lhChartCanvas"></canvas>
            </div>
        </div>
        <div class="lh-chart-summary-row">
            @php $chartOkTotal=$chartFailTotal=0; foreach($chartData as $d){$chartOkTotal+=$d['ok'];$chartFailTotal+=$d['fail'];} $chartRate=$chartOkTotal+$chartFailTotal>0?round($chartOkTotal/($chartOkTotal+$chartFailTotal)*100):0; @endphp
            <div class="lh-chart-sum-tile"><div class="lh-chart-sum-val s-green">{{ $chartOkTotal }}</div><div class="lh-chart-sum-lbl">Successful (24h)</div></div>
            <div class="lh-chart-sum-tile"><div class="lh-chart-sum-val s-red">{{ $chartFailTotal }}</div><div class="lh-chart-sum-lbl">Failed (24h)</div></div>
            <div class="lh-chart-sum-tile"><div class="lh-chart-sum-val s-navy">{{ $chartOkTotal + $chartFailTotal }}</div><div class="lh-chart-sum-lbl">Total attempts</div></div>
            <div class="lh-chart-sum-tile"><div class="lh-chart-sum-val {{ $chartRate >= 80 ? 's-green' : ($chartRate >= 50 ? 's-amber' : 's-red') }}">{{ $chartRate }}%</div><div class="lh-chart-sum-lbl">Success rate</div></div>
        </div>
    </div>

    {{-- ── THREAT INTELLIGENCE + DEVICE BREAKDOWN ── --}}
    <div class="lh-threat-panel">

        {{-- Failed IP threat table --}}
        <div class="lh-threat-card">
            <div class="lh-threat-head">
                <span style="font-size:1rem;">🚨</span>
                <span class="lh-threat-head-title">Threat Intelligence — Top Failed IPs</span>
                <span class="lh-threat-head-badge">Last 7 days</span>
            </div>
            @if($failedByIp->isEmpty())
                <div style="padding:2rem;text-align:center;font-size:12px;color:var(--grey-dark);font-style:italic;">
                    No repeated failures detected — all clear ✓
                </div>
            @else
                @php $maxFail = $failedByIp->first()->cnt; @endphp
                @foreach($failedByIp as $i => $fip)
                <div class="lh-threat-row">
                    <div class="lh-threat-rank">{{ $i + 1 }}</div>
                    <div class="lh-threat-ip">{{ $fip->ip_address }}</div>
                    <div class="lh-threat-bar-wrap">
                        <div class="lh-threat-bar" style="width:{{ round(($fip->cnt/$maxFail)*100) }}%;" data-width="{{ round(($fip->cnt/$maxFail)*100) }}"></div>
                    </div>
                    <div class="lh-threat-count">{{ $fip->cnt }}× fails</div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Device / browser breakdown --}}
        @php
            $deskLogins   = \App\Models\LoginHistory::where('successful',true)->whereDate('logged_in_at',today())->where(fn($q)=>$q->where('user_agent','not like','%Mobile%')->where('user_agent','not like','%iPhone%')->where('user_agent','not like','%Android%'))->count();
            $tabletLogins = \App\Models\LoginHistory::where('successful',true)->whereDate('logged_in_at',today())->where('user_agent','like','%iPad%')->count();
            $devTotal     = max(1, $deskLogins + $mobileLogins + $tabletLogins);
            $devices = [
                ['💻', 'Desktop', $deskLogins],
                ['📱', 'Mobile',  $mobileLogins],
                ['⬜', 'Tablet',  $tabletLogins],
            ];
            $maxDev = max(1, collect($devices)->max(fn($d)=>$d[2]));
        @endphp
        <div class="lh-threat-card">
            <div class="lh-device-head">
                <span style="font-size:1rem;">📊</span>
                <span class="lh-device-head-title">Device Breakdown — Today's Successful Logins</span>
            </div>
            @foreach($devices as [$icon,$name,$count])
            <div class="lh-device-row">
                <div class="lh-device-icon">{{ $icon }}</div>
                <div>
                    <div class="lh-device-name">{{ $name }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $loginsToday > 0 ? round($count/$devTotal*100) : 0 }}% of today's logins</div>
                </div>
                <div class="lh-device-bar-wrap">
                    <div class="lh-device-bar" style="width:{{ $maxDev > 0 ? round($count/$maxDev*100) : 0 }}%;"></div>
                </div>
                <div class="lh-device-pct">{{ $count }}</div>
            </div>
            @endforeach
            {{-- Success rate donut substitute: simple ratio strip --}}
            <div style="padding:.75rem 1rem;border-top:1px solid var(--grey-mid);background:var(--grey);">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.45rem;">Today's Success Rate</div>
                @php $srPct = ($loginsToday + $failedToday) > 0 ? round($loginsToday/($loginsToday+$failedToday)*100) : 0; @endphp
                <div style="height:8px;background:var(--red-faint);border:1px solid rgba(200,16,46,.15);overflow:hidden;">
                    <div style="height:100%;width:{{ $srPct }}%;background:linear-gradient(90deg,var(--green),#4ade80);transition:width .8s ease;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:.35rem;font-size:11px;font-weight:700;">
                    <span style="color:var(--green);">{{ $srPct }}% successful</span>
                    <span style="color:var(--red);">{{ 100-$srPct }}% failed</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── FILTER BAR ── --}}
    <form method="GET" action="{{ route('admin.super.index') }}#login-history">
        <div class="lh-filter-bar">
            <div class="lh-filter-group">
                <label class="lh-filter-label">User / Email</label>
                <input type="text" name="lh_user" value="{{ $lhUser }}" class="lh-filter-input" placeholder="Name or email…">
            </div>
            <div class="lh-filter-group" style="max-width:150px;">
                <label class="lh-filter-label">Status</label>
                <select name="lh_status" class="lh-filter-input">
                    <option value="" @selected(!$lhStatus)>All statuses</option>
                    <option value="success" @selected($lhStatus==='success')>✓ Successful</option>
                    <option value="failed"  @selected($lhStatus==='failed')>✕ Failed only</option>
                </select>
            </div>
            <div class="lh-filter-group" style="max-width:165px;">
                <label class="lh-filter-label">From date</label>
                <input type="date" name="lh_from" value="{{ $lhFrom }}" class="lh-filter-input">
            </div>
            <div class="lh-filter-group" style="max-width:165px;">
                <label class="lh-filter-label">To date</label>
                <input type="date" name="lh_to" value="{{ $lhTo }}" class="lh-filter-input">
            </div>
            <div class="lh-filter-actions">
                <button type="submit" class="lh-filter-submit">⚡ Apply Filter</button>
                @if($lhStatus||$lhUser||$lhFrom||$lhTo)
                    <a href="{{ route('admin.super.index') }}#login-history" class="lh-filter-clear">✕ Clear</a>
                @endif
            </div>
        </div>
    </form>

   {{-- ── PREMIUM TABLE ── --}}
    <div class="lh-table-card" id="lh-table-card">
        <div class="lh-table-head-bar">
            <div class="lh-table-head-title">Access Records</div>
            <span class="lh-table-count-pill">{{ $loginHistoryTotal }} total records</span>
            @if($failedToday > 0)
                <span class="lh-table-fail-pill">⚠ {{ $failedToday }} failed today</span>
            @endif
            {{-- Clear all / bulk delete controls --}}
            <div style="margin-left:auto; display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.super.login-history.delete-failed') }}"
                      onsubmit="return confirm('Delete ALL failed login records? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red btn-sm">✕ Clear Failed Logins</button>
                </form>
                <form method="POST" action="{{ route('admin.super.login-history.delete-all') }}"
                      onsubmit="return confirm('Delete the ENTIRE login history? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red btn-sm" style="background:rgba(200,16,46,.1);">🗑 Clear All History</button>
                </form>
            </div>
        </div>

        {{-- Bulk action bar (shown when rows are checked) --}}
        <div class="lh-bulk-bar" id="lhBulkBar" style="display:none;">
            <span class="lh-bulk-bar-label">⚡ Bulk Actions</span>
            <span id="lhSelectedCount" style="font-size:12px;font-weight:700;color:var(--text);">0 selected</span>
            <form method="POST" action="{{ route('admin.super.login-history.delete-bulk') }}"
                  id="lhBulkForm" onsubmit="return confirmBulkDelete()">
                @csrf @method('DELETE')
                <div id="lhBulkIds"></div>
                <button type="submit" class="btn btn-red btn-sm">🗑 Delete Selected</button>
            </form>
            <button type="button" class="btn btn-ghost btn-sm" onclick="clearLhSelection()">✕ Deselect All</button>
        </div>

        <div style="overflow-x:auto;">
            <table class="lh-premium-table" id="lhTable">
                <thead>
                    <tr>
                        <th style="width:36px;">
                            <label class="lh-select-all-wrap" title="Select all on this page">
                                <input type="checkbox" id="lhSelectAll" onchange="toggleAllLh(this)">
                            </label>
                        </th>
                        <th>Status &amp; Time</th>
                        <th>Member</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Duration</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($loginHistory as $lh)
                @php
                    $isSuccess = $lh->successful;
                    $ua3 = $lh->user_agent ?? '';
                    $isMob3 = str_contains(strtolower($ua3),'mobile') || str_contains(strtolower($ua3),'iphone') || str_contains(strtolower($ua3),'android');
                    $isTab3 = str_contains(strtolower($ua3),'ipad') || str_contains(strtolower($ua3),'tablet');
                    $isBot3 = collect($botPatterns)->contains(fn($p)=>str_contains(strtolower($ua3),$p));
                    $deviceEmoji3 = $isBot3 ? '🤖' : ($isTab3 ? '⬜' : ($isMob3 ? '📱' : '💻'));
                    $deviceType3  = $isBot3 ? 'Bot' : ($isTab3 ? 'Tablet' : ($isMob3 ? 'Mobile' : 'Desktop'));
                    $browser3 = 'Unknown browser';
                    if (str_contains($ua3,'Firefox'))      $browser3 = 'Firefox';
                    elseif(str_contains($ua3,'Chrome'))    $browser3 = 'Chrome';
                    elseif(str_contains($ua3,'Safari'))    $browser3 = 'Safari';
                    elseif(str_contains($ua3,'Edge'))      $browser3 = 'Edge';
                    elseif(str_contains($ua3,'MSIE') || str_contains($ua3,'Trident')) $browser3 = 'IE';
                    $isThreatIp = $failedByIp->contains('ip_address', $lh->ip_address) && $failedByIp->where('ip_address',$lh->ip_address)->first()?->cnt >= 3;
                    $userInitial = $lh->user ? strtoupper(substr($lh->user->name,0,1)) : '?';
                @endphp
                <tr class="lh-tr {{ $isSuccess ? 'success-row' : 'failed-row' }}" data-id="{{ $lh->id }}">
                    {{-- Checkbox --}}
                    <td class="lh-td" style="text-align:center;padding:.7rem .5rem;">
                        <input type="checkbox" class="lh-row-check lh-check"
                               value="{{ $lh->id }}" onchange="updateLhBulk()">
                    </td>

                    {{-- Status + time --}}
                    <td class="lh-td">
                        <div class="lh-status-wrap">
                            <div class="lh-status-icon {{ $isSuccess ? 'ok' : 'fail' }}">
                                {{ $isSuccess ? '✓' : '✕' }}
                            </div>
                            <div>
                                <div class="lh-status-label {{ $isSuccess ? 'ok' : 'fail' }}">
                                    {{ $isSuccess ? 'Success' : 'Failed' }}
                                </div>
                                <div class="lh-status-time">{{ $lh->logged_in_at->format('d M Y · H:i:s') }}</div>
                                <div class="lh-status-time" style="color:var(--sa-purple);font-weight:700;">{{ $lh->logged_in_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Member --}}
                    <td class="lh-td">
                        <div class="lh-user-wrap">
                            <div class="lh-user-av {{ $lh->user ? '' : 'unknown' }}">{{ $userInitial }}</div>
                            <div>
                                @if($lh->user)
                                    <div class="lh-user-name">{{ $lh->user->name }}</div>
                                    <div class="lh-user-email">{{ $lh->email }}</div>
                                    @if($lh->user->callsign)
                                        <span class="lh-user-badge callsign">{{ $lh->user->callsign }}</span>
                                    @endif
                                @else
                                    <div class="lh-user-name" style="color:var(--red);">Unknown</div>
                                    <div class="lh-user-email">{{ $lh->email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- IP --}}
                    <td class="lh-td">
                        <div class="lh-ip-wrap">
                            <span class="lh-ip-addr {{ $isThreatIp ? 'threat' : '' }}">{{ $lh->ip_address }}</span>
                            @if($isThreatIp)
                                <div class="lh-ip-flag">⚠ Repeated failures</div>
                            @endif
                        </div>
                    </td>

                    {{-- Device --}}
                    <td class="lh-td">
                        <div class="lh-device-wrap">
                            <span class="lh-device-emoji">{{ $deviceEmoji3 }}</span>
                            <div>
                                <div class="lh-device-type">{{ $deviceType3 }}</div>
                                <div class="lh-device-browser" title="{{ $ua3 }}">{{ $browser3 }} · {{ $ua3 ? Str::limit($ua3,40) : 'Unknown agent' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Duration --}}
                    <td class="lh-td">
                        @if($lh->session_duration)
                            <span class="lh-duration">{{ $lh->session_duration }}</span>
                        @else
                            <span style="font-size:11px;color:var(--grey-dark);">—</span>
                        @endif
                    </td>

                    {{-- Delete button --}}
                    <td class="lh-td" style="text-align:center;padding:.7rem .5rem;">
                        <form method="POST"
                              action="{{ route('admin.super.login-history.delete', $lh->id) }}"
                              onsubmit="return confirm('Delete this login record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="lh-del-btn" title="Delete this record">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="lh-empty">
                            <span class="lh-empty-icon">🔐</span>
                            <div class="lh-empty-text">No login records found</div>
                            <div class="lh-empty-sub">Try adjusting your filters or date range</div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($loginHistory->hasPages())
        <div class="lh-pagination">
            <span class="lh-pagination-info">
                Records {{ $loginHistory->firstItem() }}–{{ $loginHistory->lastItem() }} of {{ $loginHistory->total() }}
            </span>
            {{ $loginHistory->appends(request()->except('lh_page'))->links() }}
        </div>
        @endif
    </div>
</div>{{-- /tab-login-history --}}


{{-- ══════════════════════════════════
     TAB: AUDIT LOG
══════════════════════════════════ --}}
<div class="tab-pane" id="tab-audit-log">

<style>
.al-top-strip{display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid var(--grey-mid);}
.al-top-tile{padding:.85rem 1.1rem;border-right:1px solid var(--grey-mid);text-align:center;}
.al-top-tile:last-child{border-right:none;}
.al-top-num{font-size:28px;font-weight:700;line-height:1;}
.al-top-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);margin-top:.3rem;}
.al-num-purple{color:var(--sa-purple);}
.al-num-navy{color:var(--navy);}
.al-num-red{color:var(--red);}
.al-num-green{color:var(--green);}
.al-filter-bar{display:flex;align-items:flex-end;gap:.65rem;flex-wrap:wrap;padding:.85rem 1.1rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);}
.al-ff{display:flex;flex-direction:column;gap:.25rem;flex:1;min-width:110px;max-width:200px;}
.al-ff label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);}
.al-ff input,.al-ff select{background:#fff;border:1px solid var(--grey-mid);padding:.38rem .65rem;color:var(--text);font-family:var(--font);font-size:12px;outline:none;transition:border-color .15s;}
.al-ff input:focus,.al-ff select:focus{border-color:var(--sa-purple);box-shadow:0 0 0 2px rgba(124,58,237,.08);}
.al-table{width:100%;border-collapse:collapse;font-family:var(--font);}
.al-table thead{position:sticky;top:0;z-index:2;}
.al-table thead tr{background:var(--sa-purple-dark);}
.al-table th{padding:.5rem .85rem;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.45);white-space:nowrap;border-bottom:2px solid var(--sa-purple);}
.al-table th:first-child{width:140px;}
.al-row{border-bottom:1px solid var(--grey-mid);transition:background .1s;cursor:pointer;}
.al-row:last-child{border-bottom:none;}
.al-row:hover{background:rgba(124,58,237,.03);}
.al-row.expanded{background:var(--sa-purple-faint);}
.al-row.expanded:hover{background:#ede9fe;}
.al-td{padding:.7rem .85rem;vertical-align:top;font-size:12px;color:var(--text-mid);}
.al-td-time{width:120px;}
.al-td-time-main{font-size:12px;font-weight:700;color:var(--text);font-family:monospace;}
.al-td-time-ago{font-size:10px;color:var(--text-muted);margin-top:2px;}
.al-actor{display:flex;align-items:center;gap:.55rem;}
.al-av{width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;}
.al-av-admin{background:var(--sa-purple);}
.al-av-super{background:var(--sa-purple-dark);border:2px solid rgba(124,58,237,.4);}
.al-av-system{background:var(--grey-dark);}
.al-actor-name{font-size:12px;font-weight:700;color:var(--text);}
.al-actor-badge{font-size:9px;font-weight:700;padding:1px 5px;text-transform:uppercase;letter-spacing:.05em;margin-top:2px;display:inline-flex;}
.al-actor-badge.super{background:var(--sa-purple-dark);color:#c4b5fd;border:1px solid rgba(124,58,237,.3);}
.al-actor-badge.admin{background:var(--red-faint);color:var(--red);border:1px solid rgba(200,16,46,.2);}
.al-actor-badge.self{background:var(--green-bg);color:var(--green);border:1px solid rgba(22,163,74,.2);}
.al-action-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:10px;font-weight:700;padding:3px 8px;text-transform:uppercase;letter-spacing:.04em;border:1px solid;white-space:nowrap;}
.al-cat-auth{background:#fdf0ff;border-color:rgba(124,58,237,.25);color:#6d28d9;}
.al-cat-danger{background:var(--red-faint);border-color:rgba(200,16,46,.25);color:var(--red);}
.al-cat-success{background:var(--green-bg);border-color:rgba(22,163,74,.25);color:var(--green);}
.al-cat-warning{background:#fff7ed;border-color:rgba(234,88,12,.25);color:#c2410c;}
.al-cat-info{background:var(--navy-faint);border-color:rgba(0,51,102,.2);color:var(--navy);}
.al-cat-maint{background:#fff7ed;border-color:rgba(217,119,6,.2);color:#d97706;}
.al-cat-profile{background:#f0fdf4;border-color:rgba(22,163,74,.2);color:#15803d;}
.al-cat-neutral{background:var(--grey);border-color:var(--grey-mid);color:var(--grey-dark);}
.al-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;display:inline-block;}
.al-target-name{font-size:12px;font-weight:700;color:var(--text);}
.al-target-id{font-size:10px;color:var(--text-muted);font-family:monospace;margin-top:2px;}
.al-target-type{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-top:2px;}
.al-ip{font-family:monospace;font-size:11px;font-weight:700;color:var(--navy);background:var(--navy-faint);padding:2px 6px;display:inline-block;}
.al-expand-icon{font-size:10px;color:var(--text-muted);transition:transform .2s;display:inline-block;}
.al-row.expanded .al-expand-icon{transform:rotate(90deg);}
.al-detail-row{display:none;background:var(--sa-purple-faint);border-bottom:2px solid rgba(124,58,237,.15);}
.al-detail-row.open{display:table-row;}
.al-detail-inner{padding:.85rem 1.1rem 1rem;}
.al-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:700px){.al-detail-grid{grid-template-columns:1fr;}}
.al-diff-card{background:#fff;border:1px solid var(--grey-mid);overflow:hidden;}
.al-diff-head{padding:.4rem .75rem;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;border-bottom:1px solid var(--grey-mid);display:flex;align-items:center;gap:.4rem;}
.al-diff-head.old{background:#fff2f2;color:#991b1b;border-bottom-color:rgba(200,16,46,.15);}
.al-diff-head.new{background:#f0fdf4;color:#15803d;border-bottom-color:rgba(22,163,74,.15);}
.al-diff-body{padding:.65rem .75rem;}
.al-diff-row{display:flex;align-items:flex-start;gap:.5rem;padding:.3rem 0;border-bottom:1px solid rgba(0,0,0,.04);font-size:11px;}
.al-diff-row:last-child{border-bottom:none;}
.al-diff-key{font-weight:700;color:var(--text-muted);min-width:120px;flex-shrink:0;font-family:monospace;font-size:10px;}
.al-diff-val{color:var(--text-mid);word-break:break-all;}
.al-diff-val.removed{color:#991b1b;text-decoration:line-through;opacity:.7;}
.al-diff-val.added{color:#15803d;font-weight:700;}
.al-diff-empty{font-size:11px;color:var(--text-muted);font-style:italic;padding:.2rem 0;}
.al-desc-row{margin-bottom:.75rem;padding:.5rem .75rem;background:#fff;border-left:3px solid var(--sa-purple);font-size:12px;color:var(--text-mid);font-weight:700;}
.al-pagination{padding:.75rem 1.1rem;background:var(--grey);border-top:1px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;font-size:12px;color:var(--text-muted);}
.al-empty{padding:3.5rem 1rem;text-align:center;}
.al-empty-icon{font-size:2.5rem;opacity:.1;margin-bottom:.75rem;}
.al-empty-text{font-size:13px;color:var(--text-muted);}
.al-tip-strip{padding:.75rem 1.1rem;border-top:1px solid var(--grey-mid);background:rgba(124,58,237,.03);display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;}
.al-tip-code{font-family:monospace;font-size:11px;background:var(--sa-purple-dark);color:#c4b5fd;padding:.3rem .75rem;display:inline-block;line-height:1.6;}
/* ── Delete actions ── */
.lh-del-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .6rem; background: transparent;
    border: 1px solid rgba(200,16,46,.25); color: var(--red);
    font-family: var(--font); font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.lh-del-btn:hover { background: var(--red-faint); border-color: var(--red); }
.lh-bulk-bar {
    padding: .65rem 1.25rem;
    background: #fff2f2; border-bottom: 1px solid rgba(200,16,46,.2);
    display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
}
.lh-bulk-bar-label { font-size: 11px; font-weight: 700; color: var(--red); text-transform: uppercase; letter-spacing: .08em; }
.lh-select-all-wrap { display: flex; align-items: center; gap: .4rem; font-size: 11px; font-weight: 700; color: var(--text-muted); cursor: pointer; }
.lh-select-all-wrap input { accent-color: var(--red); width: 14px; height: 14px; cursor: pointer; }
.lh-row-check { accent-color: var(--red); width: 14px; height: 14px; cursor: pointer; }
</style>

<div class="sa-card">
    @php
        $alTotal=$auditTotal;$alToday=$auditToday;
        $alDanger=\App\Models\AdminAuditLog::whereDate('created_at',today())->where(fn($q)=>$q->where('action','like','%.suspend%')->orWhere('action','like','%.delete%')->orWhere('action','like','%.revoke%')->orWhere('action','like','%login_failed%'))->count();
        $alAdmins=\App\Models\AdminAuditLog::whereDate('created_at',today())->distinct('admin_id')->count('admin_id');
    @endphp
    <div class="al-top-strip">
        <div class="al-top-tile"><div class="al-top-num al-num-purple">{{ number_format($alTotal) }}</div><div class="al-top-lbl">Total entries</div></div>
        <div class="al-top-tile"><div class="al-top-num al-num-navy">{{ $alToday }}</div><div class="al-top-lbl">Today</div></div>
        <div class="al-top-tile"><div class="al-top-num al-num-red">{{ $alDanger }}</div><div class="al-top-lbl">High-risk today</div></div>
        <div class="al-top-tile"><div class="al-top-num al-num-green">{{ $alAdmins }}</div><div class="al-top-lbl">Admins active today</div></div>
    </div>
    <div class="sa-card-head"><span class="sa-card-title">📋 Audit Log</span><div class="sa-card-head-right"><span style="font-size:11px;color:var(--text-muted);">{{ $auditTotal }} total entries</span></div></div>
    <form method="GET" action="{{ route('admin.super.index') }}#audit-log">
        <div class="al-filter-bar">
            <div class="al-ff"><label>Admin</label><select name="audit_admin"><option value="" @selected(!$auditAdmin)>All admins</option>@foreach($auditAdmins as $aa)<option value="{{ $aa->id }}" @selected($auditAdmin==$aa->id)>{{ $aa->name }}</option>@endforeach</select></div>
            <div class="al-ff"><label>Action contains</label><input type="text" name="audit_action" value="{{ $auditAction }}" placeholder="e.g. suspend, login…"></div>
            <div class="al-ff" style="max-width:150px;"><label>From</label><input type="date" name="audit_from" value="{{ $auditFrom }}"></div>
            <div class="al-ff" style="max-width:150px;"><label>To</label><input type="date" name="audit_to" value="{{ $auditTo }}"></div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if($auditAdmin||$auditAction||$auditFrom||$auditTo)<a href="{{ route('admin.super.index') }}#audit-log" class="btn btn-ghost btn-sm">Clear</a>@endif
        </div>
    </form>
    <div style="overflow-x:auto;">
        <table class="al-table">
            <thead><tr><th></th><th>When</th><th>Actor</th><th>Action</th><th>Target</th><th>Description</th><th>IP</th><th></th></tr></thead>
            <tbody>
            @forelse($auditLogs as $log)
            @php
                $action=strtolower($log->action??'');
                if(str_contains($action,'auth.')||str_contains($action,'login')||str_contains($action,'logout')||str_contains($action,'magic')){$chipClass='al-cat-auth';$dotColor='#7c3aed';$catLabel='Auth';}
                elseif(str_contains($action,'suspend')||str_contains($action,'delete')||str_contains($action,'revoke')||str_contains($action,'kill')||str_contains($action,'failed')||str_contains($action,'blocked')){$chipClass='al-cat-danger';$dotColor='#C8102E';$catLabel='Danger';}
                elseif(str_contains($action,'approve')||str_contains($action,'grant')||str_contains($action,'verified')||str_contains($action,'unsuspend')||str_contains($action,'created')){$chipClass='al-cat-success';$dotColor='#16a34a';$catLabel='Success';}
                elseif(str_contains($action,'maintenance')||str_contains($action,'whitelist')){$chipClass='al-cat-maint';$dotColor='#d97706';$catLabel='System';}
                elseif(str_contains($action,'profile')||str_contains($action,'callsign')||str_contains($action,'radio')||str_contains($action,'training')){$chipClass='al-cat-profile';$dotColor='#15803d';$catLabel='Profile';}
                elseif(str_contains($action,'session')||str_contains($action,'force')||str_contains($action,'password')||str_contains($action,'reset')){$chipClass='al-cat-warning';$dotColor='#ea580c';$catLabel='Access';}
                elseif(str_contains($action,'role')||str_contains($action,'permission')||str_contains($action,'super')){$chipClass='al-cat-info';$dotColor='#003366';$catLabel='Roles';}
                else{$chipClass='al-cat-neutral';$dotColor='#9aa3ae';$catLabel='General';}
                $actionDisplay=str_replace('_',' ',last(explode('.',$log->action??'')));
                $actor=$log->admin;$actorInitials=$actor?strtoupper(substr($actor->name,0,1)):'?';$isMe=$actor&&$actor->id===auth()->id();$isSuperActor=$actor&&$actor->is_super_admin;
                $oldVals=$log->old_values;$newVals=$log->new_values;
                if(is_string($oldVals))$oldVals=json_decode($oldVals,true);
                if(is_string($newVals))$newVals=json_decode($newVals,true);
                $hasDiff=(!empty($oldVals)||!empty($newVals));
            @endphp
            <tr class="al-row" onclick="toggleAuditRow({{ $log->id }})" id="al-row-{{ $log->id }}">
                <td class="al-td" style="width:28px;text-align:center;padding:.7rem .5rem;">@if($hasDiff||$log->description)<span class="al-expand-icon">▶</span>@endif</td>
                <td class="al-td al-td-time"><div class="al-td-time-main">{{ $log->created_at->format('d M H:i') }}</div><div class="al-td-time-ago">{{ $log->created_at->diffForHumans() }}</div><div style="font-size:9px;color:var(--text-muted);margin-top:1px;">{{ $log->created_at->format('Y') }}</div></td>
                <td class="al-td"><div class="al-actor"><div class="al-av {{ $isSuperActor?'al-av-super':($actor?'al-av-admin':'al-av-system') }}">{{ $actorInitials }}</div><div><div class="al-actor-name">{{ $actor->name??'System' }}</div><div>@if($isMe)<span class="al-actor-badge self">You</span>@elseif($isSuperActor)<span class="al-actor-badge super">★ Super</span>@elseif($actor)<span class="al-actor-badge admin">Admin</span>@endif</div></div></div></td>
                <td class="al-td"><span class="al-action-chip {{ $chipClass }}"><span class="al-dot" style="background:{{ $dotColor }};"></span>{{ $actionDisplay }}</span><div style="font-size:9px;color:var(--text-muted);margin-top:3px;text-transform:uppercase;letter-spacing:.08em;">{{ $catLabel }}</div></td>
                <td class="al-td">
                    @if($log->entity_label)
                        <div class="al-target-name">{{ $log->entity_label }}</div>
                        @if($log->entity_id)
                            <div class="al-target-id">ID #{{ $log->entity_id }}</div>
                        @endif
                        @if($log->entity_type)
                            <div class="al-target-type">{{ $log->entity_type }}</div>
                        @endif
                    @else
                        <span style="font-size:11px;color:var(--grey-dark);">—</span>
                    @endif
                </td>
                <td class="al-td" style="max-width:220px;"><div style="font-size:12px;color:var(--text-mid);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;" title="{{ $log->description }}">{{ $log->description??'—' }}</div>@if($hasDiff)<div style="font-size:10px;color:var(--sa-purple);margin-top:2px;font-weight:700;">↕ {{ count(array_keys((array)$oldVals+(array)$newVals)) }} field(s) changed</div>@endif</td>
                <td class="al-td">@if($log->ip_address)<span class="al-ip">{{ $log->ip_address }}</span>@else<span style="font-size:11px;color:var(--grey-dark);">—</span>@endif</td>
                <td class="al-td" style="width:20px;padding:.7rem .5rem;"></td>
            </tr>
            @if($hasDiff||$log->description)
            <tr class="al-detail-row" id="al-detail-{{ $log->id }}">
                <td colspan="8" class="al-td" style="padding:0;">
                    <div class="al-detail-inner">
                        @if($log->description)<div class="al-desc-row">{{ $log->description }}</div>@endif
                        @if($hasDiff)
                        <div class="al-detail-grid">
                            <div class="al-diff-card"><div class="al-diff-head old"><span style="font-size:14px;">−</span> Before</div><div class="al-diff-body">@if(!empty($oldVals))@foreach($oldVals as $key=>$val)<div class="al-diff-row"><span class="al-diff-key">{{ $key }}</span><span class="al-diff-val removed">@if(is_null($val))<em style="opacity:.5;">null</em>@elseif(is_bool($val)){{ $val?'true':'false' }}@elseif(is_array($val)){{ implode(', ',$val) }}@else{{ Str::limit((string)$val,80) }}@endif</span></div>@endforeach@else<div class="al-diff-empty">No previous values recorded</div>@endif</div></div>
                            <div class="al-diff-card"><div class="al-diff-head new"><span style="font-size:14px;">+</span> After</div><div class="al-diff-body">@if(!empty($newVals))@foreach($newVals as $key=>$val)<div class="al-diff-row"><span class="al-diff-key">{{ $key }}</span><span class="al-diff-val added">@if(is_null($val))<em style="opacity:.5;">null</em>@elseif(is_bool($val)){{ $val?'true':'false' }}@elseif(is_array($val)){{ implode(', ',$val) }}@else{{ Str::limit((string)$val,80) }}@endif</span></div>@endforeach@else<div class="al-diff-empty">No new values recorded</div>@endif</div></div>
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="8"><div class="al-empty"><div class="al-empty-icon">📋</div><div class="al-empty-text">No audit log entries found matching your filters.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
    <div class="al-pagination"><span>Showing {{ $auditLogs->firstItem() }}–{{ $auditLogs->lastItem() }} of {{ $auditLogs->total() }}</span>{{ $auditLogs->appends(request()->except('al_page'))->links() }}</div>
    @endif
    <div class="al-tip-strip"><span style="font-size:11px;font-weight:700;color:var(--text-muted);">Log actions from controllers:</span><code class="al-tip-code">use App\Helpers\AuditLogger; &nbsp; AuditLogger::log('user.suspended', $user, "Suspended {$user->name}");</code></div>
</div>
<script>function toggleAuditRow(id){const row=document.getElementById('al-row-'+id);const detail=document.getElementById('al-detail-'+id);if(!detail)return;const isOpen=detail.classList.contains('open');detail.classList.toggle('open',!isOpen);row.classList.toggle('expanded',!isOpen);}</script>
</div>{{-- /tab-audit-log --}}


{{-- ══════════════════════════════════
     TAB: SUPER ADMINS
══════════════════════════════════ --}}
<div class="tab-pane" id="tab-super-admins">
    <div class="sa-card">
        <div class="sa-card-head"><span class="sa-card-title">★ Current Super Administrators</span><div class="sa-card-head-right"><span class="badge b-super">{{ $superAdmins->count() }} super admins</span></div></div>
        @foreach($superAdmins as $sa)
        @php $isMe=$sa->id===auth()->id(); @endphp
        <div class="sa-member-row">
            <div class="sa-av">{{ strtoupper(substr($sa->name,0,1)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="sa-member-name">{{ $sa->name }}@if($isMe)<span class="badge b-ok" style="font-size:9px;margin-left:.35rem;">You</span>@endif</div>
                <div class="sa-member-email">{{ $sa->email }}</div>
                <div class="sa-member-meta">@if($sa->callsign)<span class="badge b-navy" style="font-size:10px;font-family:monospace;">{{ $sa->callsign }}</span>@endif<span class="badge b-super">★ Super Admin</span><span class="badge b-{{ $sa->status==='Active'?'ok':'grey' }}">{{ $sa->status }}</span></div>
            </div>
            <div>
                @if(!$isMe)
                <form method="POST" action="{{ route('admin.super.super-admins.revoke',$sa->id) }}" onsubmit="return confirm('Revoke Super Admin from {{ addslashes($sa->name) }}? They will retain admin access.')">@csrf<button type="submit" class="btn btn-red btn-sm">↓ Revoke Super Admin</button></form>
                @else<span style="font-size:11px;color:var(--grey-dark);font-style:italic;">Cannot modify own account</span>@endif
            </div>
        </div>
        @endforeach
        <div style="padding:.85rem 1.1rem;border-top:1px solid var(--grey-mid);background:rgba(91,33,182,.04);font-size:12px;color:var(--text-muted);line-height:1.6;">ℹ Revoking Super Admin demotes the account to regular admin — they retain full admin panel access but lose access to this panel and all Super Admin restricted areas.</div>
    </div>
    @if($eligibleAdmins->isNotEmpty())
    <div class="sa-card">
        <div class="sa-card-head"><span class="sa-card-title">⬆ Elevate Admin to Super Admin</span><span style="font-size:11px;color:var(--text-muted);margin-left:auto;">Grant Super Admin access to an existing administrator</span></div>
        <div style="padding:1.1rem;">
            <div style="padding:.75rem 1rem;background:var(--amber-bg);border:1px solid rgba(217,119,6,.25);border-left:3px solid var(--amber);font-size:12px;color:#92400e;margin-bottom:1rem;">⚠ Only elevate admins you fully trust. Super Admins can manage maintenance mode, kill all sessions, view login history, access audit logs, and grant or revoke Super Admin status.</div>
            <form id="grantSaForm" method="POST" action="" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:.3rem;flex:1;min-width:200px;">
                    <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);">Select Administrator</label>
                    <select name="user_id" class="maint-input" required id="grantSaSelect" onchange="document.getElementById('grantSaForm').action='/admin/super/super-admins/'+this.value+'/grant'">
                        <option value="">— Choose admin to elevate —</option>
                        @foreach($eligibleAdmins as $ea)<option value="{{ $ea->id }}">{{ $ea->name }}{{ $ea->callsign?' ('.$ea->callsign.')':'' }} — {{ $ea->email }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" onclick="return this.form.action&&confirm('Grant Super Admin access? This gives full system-level control.')">★ Grant Super Admin</button>
            </form>
        </div>
    </div>
    @else
    <div class="sa-card"><div class="sa-card-head"><span class="sa-card-title">⬆ Elevate Admin to Super Admin</span></div><div style="padding:1.5rem;text-align:center;font-size:12px;color:var(--grey-dark);font-style:italic;">All administrators are already Super Admins, or there are no other admins to elevate.</div></div>
    @endif
</div>{{-- /tab-super-admins --}}


{{-- ══════════════════════════════════
     TAB: SYSTEM HEALTH
══════════════════════════════════ --}}
<div class="tab-pane" id="tab-system">
    @php
        $diskTotal=disk_total_space('/');$diskFree=disk_free_space('/');$diskUsed=$diskTotal-$diskFree;
        $diskPct=$diskTotal>0?round(($diskUsed/$diskTotal)*100):0;
        $sessionCount=\DB::table('sessions')->count();$loginCount=\App\Models\LoginHistory::count();$auditCount=\App\Models\AdminAuditLog::count();
        try{$userCount=\App\Models\User::count();}catch(\Throwable $e){$userCount=0;}
    @endphp
    <div class="sa-card">
        <div class="sa-card-head"><span class="sa-card-title">⚙ System Health Overview</span></div>
        <div class="health-grid">
            <div class="health-item"><div class="health-dot {{ $dbOk?'ok':'fail' }}"></div><div><div class="health-lbl">Database</div><div class="health-val">{{ $dbOk?'● Connected':'✕ Error' }}</div></div></div>
            <div class="health-item"><div class="health-dot {{ $cacheOk?'ok':'warn' }}"></div><div><div class="health-lbl">Cache</div><div class="health-val">{{ $cacheOk?'● Working':'⚠ Check config' }}</div></div></div>
            <div class="health-item"><div class="health-dot {{ $storageOk?'ok':'fail' }}"></div><div><div class="health-lbl">Storage</div><div class="health-val">{{ $storageOk?'● Writable':'✕ Read-only' }}</div></div></div>
            <div class="health-item"><div class="health-dot {{ $diskPct<80?'ok':($diskPct<90?'warn':'fail') }}"></div><div><div class="health-lbl">Disk Space</div><div class="health-val">{{ round($diskFree/1073741824,1) }} GB free</div><div style="font-size:11px;color:var(--text-muted);">{{ $diskPct }}% used of {{ round($diskTotal/1073741824,1) }} GB</div><div style="height:4px;background:var(--grey-mid);margin-top:4px;"><div style="height:100%;background:{{ $diskPct>90?'var(--red)':($diskPct>80?'var(--amber)':'var(--green)') }};width:{{ $diskPct }}%;"></div></div></div></div>
            <div class="health-item"><div class="health-dot ok"></div><div><div class="health-lbl">PHP Version</div><div class="health-val">{{ PHP_VERSION }}</div><div style="font-size:11px;color:var(--text-muted);">{{ php_uname('s') }}</div></div></div>
            <div class="health-item"><div class="health-dot ok"></div><div><div class="health-lbl">Laravel</div><div class="health-val">{{ app()->version() }}</div><div style="font-size:11px;color:var(--text-muted);">{{ ucfirst(app()->environment()) }} · Debug {{ config('app.debug')?'ON':'OFF' }}</div></div></div>
        </div>
    </div>
    <div class="sa-card">
        <div class="sa-card-head"><span class="sa-card-title">📊 Database Stats</span></div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;">
            @foreach([['Users',$userCount,'Registered accounts'],['Sessions',$sessionCount,'Active sessions'],['Logins',$loginCount,'Login history rows'],['Audit',$auditCount,'Audit log entries']] as [$lbl,$val,$sub])
            <div style="padding:1rem;border-right:1px solid var(--grey-mid);text-align:center;"><div style="font-size:30px;font-weight:700;color:var(--sa-purple-dark);line-height:1;">{{ number_format($val) }}</div><div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-top:.3rem;">{{ $lbl }}</div><div style="font-size:10px;color:var(--grey-dark);margin-top:2px;">{{ $sub }}</div></div>
            @endforeach
        </div>
    </div>
    <div class="sa-card">
        <div class="sa-card-head"><span class="sa-card-title">🔧 Quick Actions</span></div>
        <div style="padding:1.1rem;display:flex;gap:.65rem;flex-wrap:wrap;">
            <a href="{{ route('admin.super.permissions.index') }}" class="btn btn-primary">★ Permission Management</a>
            <a href="{{ route('admin.super.operations') }}" class="btn btn-navy">⚡ Operations Centre</a>
            <a href="{{ route('admin.settings') }}" class="btn btn-ghost">⚙ Site Settings</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">👥 Manage Members</a>
            <a href="{{ route('admin.users.roles') }}" class="btn btn-ghost">🎭 Role Management</a>
        </div>
    </div>
</div>{{-- /tab-system --}}

</div>{{-- /sa-wrap --}}

{{-- ── Chart.js from CDN ── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
/* ── Tab switching ── */
(function(){
    const btns=document.querySelectorAll('.sa-tab');
    const panes=document.querySelectorAll('.tab-pane');
    function activate(name){
        btns.forEach(b=>b.classList.toggle('active',b.dataset.tab===name));
        panes.forEach(p=>p.classList.toggle('active',p.id==='tab-'+name));
        history.replaceState(null,'','#'+name);
    }
    btns.forEach(b=>b.addEventListener('click',()=>activate(b.dataset.tab)));
const hash=location.hash.replace('#','');
const valid=['maintenance','sessions','login-history','audit-log','super-admins','system'];

// Check URL params to infer which tab should be active
const params = new URLSearchParams(location.search);
let activeTab = 'maintenance';
if (valid.includes(hash)) {
    activeTab = hash;
} else if (params.has('al_page') || params.has('audit_admin') || params.has('audit_action') || params.has('audit_from') || params.has('audit_to')) {
    activeTab = 'audit-log';
} else if (params.has('lh_page') || params.has('lh_user') || params.has('lh_status') || params.has('lh_from') || params.has('lh_to')) {
    activeTab = 'login-history';
} else if (params.has('search_sess')) {
    activeTab = 'sessions';
}
activate(activeTab);
})();

/* ── Login history Chart.js ── */
(function(){
    const labels = {!! json_encode(array_column($chartData,'h')) !!};
    const okData = {!! json_encode(array_column($chartData,'ok')) !!};
    const failData = {!! json_encode(array_column($chartData,'fail')) !!};

    const ctx = document.getElementById('lhChartCanvas');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Successful',
                    data: okData,
                    backgroundColor: 'rgba(74,222,128,.75)',
                    borderColor: 'rgba(74,222,128,1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 2,
                },
                {
                    label: 'Failed',
                    data: failData,
                    backgroundColor: 'rgba(248,113,113,.8)',
                    borderColor: 'rgba(248,113,113,1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 1,
                },
                {
                    label: 'Trend',
                    data: okData.map((v,i)=>v+failData[i]),
                    type: 'line',
                    borderColor: 'rgba(167,139,250,.6)',
                    backgroundColor: 'rgba(167,139,250,.06)',
                    borderWidth: 2,
                    pointRadius: 2,
                    pointBackgroundColor: '#a78bfa',
                    tension: 0.4,
                    fill: true,
                    order: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode:'index', intersect:false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e0040',
                    titleColor: '#c4b5fd',
                    bodyColor: 'rgba(255,255,255,.75)',
                    borderColor: 'rgba(124,58,237,.4)',
                    borderWidth: 1,
                    padding: 10,
                    titleFont: { weight:'bold', size:11 },
                    callbacks: {
                        title: ctx => ctx[0].label,
                        afterBody: items => {
                            const ok   = items.find(i=>i.dataset.label==='Successful')?.parsed.y||0;
                            const fail = items.find(i=>i.dataset.label==='Failed')?.parsed.y||0;
                            const total = ok + fail;
                            const rate  = total ? Math.round(ok/total*100) : 0;
                            return [`Success rate: ${rate}%`];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color:'rgba(0,0,0,.04)', drawBorder:false },
                    ticks: {
                        color:'#9aa3ae', font:{ size:9, weight:'bold' },
                        maxRotation:0,
                        callback:(v,i)=> i % 4 === 0 ? labels[i] : ''
                    }
                },
                y: {
                    grid: { color:'rgba(0,0,0,.04)', drawBorder:false },
                    ticks: { color:'#9aa3ae', font:{ size:9 }, precision:0 },
                    beginAtZero: true,
                }
            },
            animation: {
                duration: 800,
                easing: 'easeInOutQuart'
            }
        }
    });
})();

/* ── Maintenance preview ── */
function updateMaintPreview(){
    const title=document.getElementById('maintTitle')?.value.trim();
    const headline=document.getElementById('maintHeadline')?.value.trim();
    const msg=document.getElementById('maintMsg')?.value.trim();
    const returnAt=document.getElementById('maintReturnAt')?.value;
    const prevT=document.getElementById('prevTitle');
    const prevH=document.getElementById('prevHeadline');
    const prevM=document.getElementById('prevMsg');
    const prevR=document.getElementById('prevReturn');
    const prevRV=document.getElementById('prevReturnVal');
    if(prevT)prevT.textContent=title||'Back Soon';
    if(prevH)prevH.textContent=headline||'{{ \App\Helpers\RaynetSetting::groupName() }}';
    if(prevM)prevM.textContent=msg||'{{ \App\Helpers\RaynetSetting::groupName() }} Members Portal is temporarily offline.';
    if(prevR&&returnAt){prevR.style.display='block';const d=new Date(returnAt);if(prevRV)prevRV.textContent=d.toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});}
    else if(prevR){prevR.style.display='none';}
}
function applyPreset(btn){
    document.querySelectorAll('.maint-preset-btn').forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
    const t=document.getElementById('maintTitle');const h=document.getElementById('maintHeadline');const m=document.getElementById('maintMsg');
    if(t)t.value=btn.dataset.title;if(h)h.value=btn.dataset.headline;if(m)m.value=btn.dataset.msg;
    updateMaintPreview();
}
function setQuickDuration(minutes){
    if(!minutes)return;
    const autoField=document.querySelector('input[name="maintenance_auto_disable_at"]');
    if(!autoField)return;
    const d=new Date(Date.now()+parseInt(minutes)*60000);
    const pad=n=>String(n).padStart(2,'0');
    autoField.value=`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
function addMyIp(){
    const ipEl=document.getElementById('myIpDisplay');
    const input=document.querySelector('input[name="ip"]');
    if(ipEl&&input)input.value=ipEl.textContent.trim();
}

/* ── Maintenance timer ── */
@if($maintOn && $maintStarted)
const maintStartTs={{ \Carbon\Carbon::parse($maintStarted)->timestamp }}*1000;
function updateMaintTimer(){
    const elapsed=Math.floor((Date.now()-maintStartTs)/1000);
    const h=Math.floor(elapsed/3600);const m=Math.floor((elapsed%3600)/60);const s=elapsed%60;
    const el=document.getElementById('maintTimer');
    if(el)el.textContent=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
}
updateMaintTimer();setInterval(updateMaintTimer,1000);
@endif
/* ── Login history bulk delete ── */
function toggleAllLh(master) {
    document.querySelectorAll('.lh-check').forEach(cb => cb.checked = master.checked);
    updateLhBulk();
}
function updateLhBulk() {
    const checked = document.querySelectorAll('.lh-check:checked');
    const bar     = document.getElementById('lhBulkBar');
    const counter = document.getElementById('lhSelectedCount');
    const master  = document.getElementById('lhSelectAll');
    const all     = document.querySelectorAll('.lh-check');
    bar.style.display     = checked.length > 0 ? 'flex' : 'none';
    counter.textContent   = checked.length + ' selected';
    master.indeterminate  = checked.length > 0 && checked.length < all.length;
    master.checked        = checked.length === all.length && all.length > 0;
    // Rebuild hidden id inputs
    const container = document.getElementById('lhBulkIds');
    container.innerHTML = '';
    checked.forEach(cb => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
    });
}
function clearLhSelection() {
    document.querySelectorAll('.lh-check').forEach(cb => cb.checked = false);
    document.getElementById('lhSelectAll').checked = false;
    updateLhBulk();
}
function confirmBulkDelete() {
    const n = document.querySelectorAll('.lh-check:checked').length;
    return n > 0 && confirm('Delete ' + n + ' selected login record(s)? This cannot be undone.');
}
</script>
@endsection