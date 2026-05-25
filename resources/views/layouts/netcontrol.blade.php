<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#003366">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Net Control')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="manifest" href="/site.webmanifest">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; height:100%; }
        body { font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
               background:#f0f4f8; color:#003366; min-height:100vh;
               -webkit-font-smoothing:antialiased; }

        /* ── App top bar ── */
        .nc-appbar {
            position:sticky; top:0; z-index:1000;
            background:linear-gradient(135deg,#001a33,#003366);
            color:#fff; height:52px;
            display:flex; align-items:center; justify-content:space-between;
            padding:0 1rem;
            box-shadow:0 2px 12px rgba(0,0,0,.3);
            border-bottom:1px solid rgba(255,255,255,.08);
        }
        .nc-appbar-left  { display:flex; align-items:center; gap:.65rem; }
        .nc-appbar-logo  { height:28px; width:auto; }
        .nc-appbar-title { font-size:.82rem; font-weight:800; letter-spacing:.04em; opacity:.9; }
        .nc-appbar-right { display:flex; align-items:center; gap:.75rem; }
        .nc-appbar-cs    { font-family:monospace; font-weight:900; font-size:.95rem;
                           background:rgba(255,255,255,.12); padding:.2rem .65rem;
                           border-radius:6px; letter-spacing:.06em; }
        .nc-appbar-user  { font-size:.75rem; opacity:.6; }
        .nc-appbar-exit  { font-size:.72rem; font-weight:700; color:rgba(255,255,255,.6);
                           text-decoration:none; padding:.25rem .6rem;
                           border:1px solid rgba(255,255,255,.2); border-radius:6px;
                           white-space:nowrap; }
        .nc-appbar-exit:hover { background:rgba(255,255,255,.1); color:#fff; }

        @media(max-width:480px){
            .nc-appbar-user { display:none; }
        }
    </style>
    @stack('head')
</head>
<body>

{{-- Impersonate bar if active --}}
@if(session('impersonating'))
<div style="background:#7c2d00;border-bottom:3px solid #ea580c;padding:.4rem 1rem;font-size:.75rem;font-weight:700;color:#fed7aa;display:flex;align-items:center;justify-content:space-between;">
    <span>⚠️ Impersonating {{ session('impersonating_name') }}</span>
    <a href="/admin/impersonate/stop" style="color:#fdba74;font-weight:800;">Stop →</a>
</div>
@endif

{{-- App top bar --}}
<div class="nc-appbar">
    <div class="nc-appbar-left">
        <span class="nc-appbar-title">{{ \App\Helpers\RaynetSetting::groupName() }} — Net Control</span>
    </div>
    <div class="nc-appbar-right">
        @isset($net)
        <span class="nc-appbar-cs">{{ is_array($net) ? $net['callsign'] : $net }}</span>
        @endisset
        @auth
        <span class="nc-appbar-user">{{ auth()->user()->callsign }}</span>
        @endauth
        <a href="/" class="nc-appbar-exit">← Site</a>
    </div>
</div>

@yield('content')

@stack('scripts')
</body>
</html>
