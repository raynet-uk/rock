@extends('layouts.netcontrol')
@section('title', 'Thank You — Net Control')
@push('head')
<style>
  body { background: linear-gradient(135deg, #001a33 0%, #003366 50%, #001a33 100%); min-height: 100vh; }
  .ty-wrap { max-width: 560px; margin: 0 auto; padding: 3rem 1.5rem 4rem; text-align: center; }

  .ty-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(34,197,94,.15); color: #22c55e;
    border: 1px solid rgba(34,197,94,.3); border-radius: 999px;
    font-size: .78rem; font-weight: 800; padding: .35rem 1rem;
    margin-bottom: 1.75rem; letter-spacing: .04em;
  }
  .ty-badge::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; }

  .ty-icon { font-size: 4rem; margin-bottom: 1rem; display: block; }

  .ty-heading {
    font-size: 2rem; font-weight: 900; color: #fff;
    line-height: 1.2; margin-bottom: .5rem;
  }
  .ty-sub {
    font-size: 1rem; color: rgba(255,255,255,.55);
    margin-bottom: 2.5rem; line-height: 1.6;
  }

  .ty-card {
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; text-align: left;
  }
  .ty-card-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem 0; border-bottom: 1px solid rgba(255,255,255,.07);
    font-size: .88rem;
  }
  .ty-card-row:last-child { border-bottom: none; padding-bottom: 0; }
  .ty-card-label { color: rgba(255,255,255,.45); font-weight: 600; }
  .ty-card-value { color: #fff; font-weight: 700; font-family: monospace; font-size: .95rem; }
  .ty-card-value.name { font-family: inherit; font-size: .88rem; }

  .ty-duration {
    background: rgba(255,255,255,.08); border-radius: 12px;
    padding: 1.25rem; margin-bottom: 2rem; text-align: center;
  }
  .ty-duration-num { font-size: 2.8rem; font-weight: 900; color: #fff; line-height: 1; }
  .ty-duration-label { font-size: .78rem; color: rgba(255,255,255,.45); font-weight: 700;
                        letter-spacing: .08em; text-transform: uppercase; margin-top: .25rem; }

  .ty-btn {
    display: inline-block; padding: .85rem 2.5rem;
    background: #fff; color: #003366;
    border-radius: 10px; font-weight: 800; font-size: .95rem;
    text-decoration: none; letter-spacing: .02em;
    transition: opacity .15s;
  }
  .ty-btn:hover { opacity: .88; }

  .ty-footer {
    margin-top: 2.5rem; font-size: .75rem; color: rgba(255,255,255,.25);
    line-height: 1.6;
  }
</style>
@endpush
@section('content')
@php
  $cs       = strtoupper(strip_tags(request('cs', '')));
  $name     = strip_tags(request('name', 'Net Controller'));
  $net      = strtoupper(strip_tags(request('net', '')));
  $freq     = strip_tags(request('freq', ''));
  $from     = strip_tags(request('from', ''));
  $to       = strip_tags(request('to', ''));
  $duration = (int) request('duration', 0);
  $hrs      = intdiv($duration, 60);
  $mins     = $duration % 60;
  $timeStr  = $hrs > 0 ? $hrs.'h '.$mins.'m' : $mins.'m';
  $handover = (bool) request('handover', false);
@endphp
<div class="ty-wrap">
  <div class="ty-badge">{{ $handover ? 'Handover complete' : 'Net concluded' }}</div>

  <span class="ty-icon">{{ $handover ? '🤝' : '📻' }}</span>

  <h1 class="ty-heading">Thanks, {{ $name }}!</h1>
  @if($handover)
  <p class="ty-sub">Handover accepted — you're clear of the net.<br>The next controller has taken over. 73!</p>
  @else
  <p class="ty-sub">You've just wrapped up a net control shift.<br>Your service keeps RAYNET on air.</p>
  @endif

  @if(!$handover || $duration > 0)
  <div class="ty-duration">
    <div class="ty-duration-num">{{ $timeStr }}</div>
    <div class="ty-duration-label">Time on air</div>
  </div>
  @endif

  <div class="ty-card">
    @if($cs)
    <div class="ty-card-row">
      <span class="ty-card-label">Your callsign</span>
      <span class="ty-card-value">{{ $cs }}</span>
    </div>
    @endif
    @if($net)
    <div class="ty-card-row">
      <span class="ty-card-label">Net</span>
      <span class="ty-card-value">{{ $net }}</span>
    </div>
    @endif
    @if($freq)
    <div class="ty-card-row">
      <span class="ty-card-label">Frequency</span>
      <span class="ty-card-value">{{ $freq }} MHz</span>
    </div>
    @endif
    @if($from && $to)
    <div class="ty-card-row">
      <span class="ty-card-label">Your slot</span>
      <span class="ty-card-value">{{ $from }} – {{ $to }}</span>
    </div>
    @endif
  </div>

  <a href="/" class="ty-btn">Return home</a>

  <p class="ty-footer">
    {{ \App\Helpers\RaynetSetting::groupName() }}<br>
    Volunteer emergency communications for Merseyside · 73
  </p>
</div>
@endsection
