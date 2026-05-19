@extends('layouts.app')
@section('title', 'Alert Levels')
@section('content')

<style>
:root {
    --navy: #003366;
    --red: #C8102E;
    --light: #F2F2F2;
    --muted: #4A4A4A;
    --border: #D0D0D0;
    --shadow-sm: 0 2px 8px rgba(0,51,102,0.06);
    --shadow-md: 0 4px 16px rgba(0,51,102,0.1);
    --transition: all 0.2s ease;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: var(--light);
    color: var(--navy);
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    font-size: 15px;
    line-height: 1.55;
}
.wrap { max-width: 860px; margin: 0 auto; padding: 2rem 1rem 4rem; }
.back-link {
    display: inline-flex; align-items: center; gap: 0.4rem;
    color: var(--red); font-weight: bold; font-size: 0.9rem;
    text-decoration: none; margin-bottom: 1.8rem;
}
.back-link:hover { text-decoration: underline; }
.page-eyebrow {
    font-size: 0.85rem; font-weight: bold; color: var(--red);
    text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem;
}
.page-title { font-size: 2rem; font-weight: bold; color: var(--navy); line-height: 1.15; margin-bottom: 0.8rem; }
.page-intro { font-size: 1rem; color: var(--muted); max-width: 600px; margin-bottom: 2rem; }

.current-banner {
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    background: white; border: 1px solid var(--border); border-radius: 10px;
    padding: 1rem 1.4rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);
}
.current-banner-label { font-size: 0.85rem; color: var(--muted); }
.current-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }

.levels-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; }
.level-card {
    background: white; border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    display: flex; box-shadow: var(--shadow-sm); transition: var(--transition);
}
.level-card.is-active { box-shadow: var(--shadow-md); border-width: 2px; }
.level-stripe { width: 5px; flex-shrink: 0; }
.level-content { padding: 1.1rem 1.3rem; flex: 1; }
.level-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
.level-number {
    width: 38px; height: 38px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; font-weight: 800; flex-shrink: 0;
}
.level-name { font-size: 1rem; font-weight: 700; color: var(--navy); flex: 1; }
.level-current-badge {
    font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.6rem;
    border-radius: 999px; background: rgba(46,125,50,0.12); color: #2E7D32;
}
.level-desc { font-size: 0.9rem; color: #1A1A1A; line-height: 1.5; margin-bottom: 0.7rem; }
.level-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.level-tag {
    font-size: 0.78rem; padding: 0.2rem 0.65rem;
    border-radius: 999px; border: 1px solid var(--border);
    color: var(--muted); background: var(--light);
}
.info-box {
    background: white; border: 1px solid var(--border);
    border-radius: 10px; padding: 1.4rem; box-shadow: var(--shadow-sm);
}
.info-box h2 { font-size: 1.05rem; font-weight: bold; color: var(--navy); margin-bottom: 0.7rem; }
.info-box p { font-size: 0.92rem; color: #1A1A1A; margin-bottom: 0.6rem; line-height: 1.55; }
.info-box p:last-child { margin-bottom: 0; }
.info-box a { color: var(--red); font-weight: bold; text-decoration: none; }
.info-box a:hover { text-decoration: underline; }
</style>

@php
    $alertStatus  = \App\Models\AlertStatus::query()->first();
    $currentLevel = $alertStatus->level ?? null;
    $config       = \App\Models\AlertStatus::config();

    $descriptions = [
        1 => 'RAYNET is fully activated and providing communications support during an active incident. All available operators are deployed or on immediate standby. Nets are open and operational.',
        2 => 'An incident is imminent and RAYNET activation is expected very soon. Members are on immediate standby, equipment is prepared, and the Group Controller is in direct contact with the requesting agency.',
        3 => 'Conditions suggest an incident is probable in the near term. Members should ensure equipment is serviceable and remain reachable. The Group Controller is monitoring closely and liaising with partner agencies.',
        4 => 'Conditions exist where RAYNET could be called upon, or a planned training exercise is in progress. Members should maintain general readiness and treat exercises as real activations.',
        5 => 'Normal peacetime readiness. There are no active incidents, imminent threats, or scheduled exercises. Members are not required to take any special action. The group remains available to respond if circumstances change.',
    ];

    $tags = [
        1 => ['Full activation', 'Nets open', 'All operators deployed', 'Incident ongoing'],
        2 => ['Immediate standby', 'Equipment ready', 'Deployment expected', 'Controller engaged'],
        3 => ['Elevated readiness', 'Equipment checks', 'Monitor channels', 'Situation developing'],
        4 => ['General readiness', 'Exercise possible', 'Treat as real', 'Routine monitoring'],
        5 => ['Normal readiness', 'No active incidents', 'No action required', 'Standing by'],
    ];
@endphp

<div class="wrap">

    <a href="{{ route('home') }}" class="back-link">← Back to Home</a>

    <div class="page-eyebrow">{{ \App\Helpers\RaynetSetting::groupNumber() }}</div>
    <h1 class="page-title">Alert Levels</h1>
    <p class="page-intro">
        {{ \App\Helpers\RaynetSetting::groupName() }} operates on a five-level alert system. The current level is set by the Group Controller and communicated to all members.
    </p>

    @if ($alertStatus)
    @php $activeMeta = $config[$currentLevel] ?? []; @endphp
    <div class="current-banner">
        <span class="current-banner-label">Current status:</span>
        <span style="display:inline-flex;align-items:center;gap:0.5rem;font-weight:700;font-size:1rem;">
            <span class="current-dot" style="background:{{ $activeMeta['colour'] ?? '#22c55e' }};"></span>
            Level {{ $currentLevel }} — {{ $activeMeta['title'] ?? '' }}
        </span>
        @if (!empty($alertStatus->message))
            <span style="font-size:0.85rem;color:var(--muted);width:100%;">📢 {{ $alertStatus->message }}</span>
        @endif
    </div>
    @endif

    <div class="levels-list">
        @foreach ($config as $num => $meta)
            @php
                $isActive   = ($currentLevel == $num);
                $colour     = $meta['colour'] ?? '#22c55e';
                $textColour = in_array($num, [1, 2, 4]) ? '#0b1120' : '#ffffff';
            @endphp
            <div class="level-card {{ $isActive ? 'is-active' : '' }}"
                 style="{{ $isActive ? 'border-color:'.$colour.';' : '' }}">
                <div class="level-stripe" style="background:{{ $colour }};"></div>
                <div class="level-content">
                    <div class="level-header">
                        <div class="level-number"
                             style="background:{{ $colour }};color:{{ $textColour }};">{{ $num }}</div>
                        <div class="level-name">{{ $meta['title'] ?? 'Level '.$num }}</div>
                        @if ($isActive)
                            <span class="level-current-badge">● Current</span>
                        @endif
                    </div>
                    <p class="level-desc">{{ $descriptions[$num] ?? $meta['description'] ?? '' }}</p>
                    <div class="level-tags">
                        @foreach ($tags[$num] ?? [] as $tag)
                            <span class="level-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="info-box">
        <h2>How Alert Levels Are Set</h2>
        <p>
            Alert levels are assigned by the Group Controller based on information received from blue-light services, local resilience forums, or the national RAYNET network. Changes are communicated to members via the member portal, email, and primary calling frequencies.
        </p>
        <p>
            If you are an emergency service or local authority wishing to request RAYNET support, please use our <a href="{{ route('request-support') }}">Support Request form</a>. For general enquiries, visit our <a href="{{ route('about') }}">About page</a>.
        </p>
    </div>

</div>

@endsection
