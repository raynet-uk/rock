@extends('layouts.netcontrol')
@section('title', 'Handover Accepted — Net Control')
@push('head')
<style>
  body { background: linear-gradient(135deg, #001a33 0%, #003366 50%, #001a33 100%); min-height: 100vh; }

  .ha-wrap {
    max-width: 520px; margin: 0 auto;
    padding: 2.5rem 1.25rem 4rem;
    text-align: center;
  }

  .ha-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(34,197,94,.15); color: #22c55e;
    border: 1px solid rgba(34,197,94,.3); border-radius: 999px;
    font-size: .78rem; font-weight: 800; padding: .35rem 1rem;
    margin-bottom: 1.75rem; letter-spacing: .04em;
  }
  .ha-badge::before {
    content: ''; width: 8px; height: 8px;
    border-radius: 50%; background: #22c55e;
    animation: pulse 1.2s ease-in-out infinite;
  }
  @keyframes pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .4; transform: scale(.75); }
  }

  .ha-icon { font-size: 3.5rem; margin-bottom: 1rem; display: block; }

  .ha-heading {
    font-size: 1.75rem; font-weight: 900; color: #fff;
    line-height: 1.2; margin-bottom: .5rem;
  }
  .ha-sub {
    font-size: .95rem; color: rgba(255,255,255,.55);
    margin-bottom: 2rem; line-height: 1.6;
  }

  .ha-card {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px; padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem; text-align: left;
  }
  .ha-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .55rem 0; border-bottom: 1px solid rgba(255,255,255,.07);
    font-size: .875rem;
  }
  .ha-row:last-child { border-bottom: none; padding-bottom: 0; }
  .ha-label { color: rgba(255,255,255,.45); font-weight: 600; }
  .ha-value { color: #fff; font-weight: 700; font-family: monospace; font-size: .95rem; }

  .ha-countdown-wrap {
    background: rgba(255,255,255,.08);
    border-radius: 14px; padding: 1.25rem 1rem;
    margin-bottom: 1.75rem;
  }
  .ha-countdown-num {
    font-size: 3rem; font-weight: 900; color: #fff;
    line-height: 1; font-variant-numeric: tabular-nums;
  }
  .ha-countdown-label {
    font-size: .72rem; color: rgba(255,255,255,.4);
    font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; margin-top: .35rem;
  }

  .ha-progress {
    width: 100%; height: 4px;
    background: rgba(255,255,255,.1);
    border-radius: 999px; overflow: hidden;
    margin-top: 1rem;
  }
  .ha-progress-bar {
    height: 100%; background: #22c55e;
    border-radius: 999px;
    transition: width .95s linear;
  }

  .ha-btn {
    display: inline-block; padding: .85rem 2.5rem;
    background: #fff; color: #003366;
    border-radius: 10px; font-weight: 800; font-size: .95rem;
    text-decoration: none; letter-spacing: .02em;
    transition: opacity .15s; width: 100%; box-sizing: border-box;
    text-align: center;
  }
  .ha-btn:hover { opacity: .88; }

  .ha-footer {
    margin-top: 2rem; font-size: .72rem;
    color: rgba(255,255,255,.2); line-height: 1.6;
  }
</style>
@endpush
@section('content')
<div class="ha-wrap">
  <div class="ha-badge">Handover accepted</div>

  <span class="ha-icon">✅</span>

  <h1 class="ha-heading">You're in control</h1>
  <p class="ha-sub">
    Handover from <strong style="color:#fff;font-family:monospace;">{{ $requesterCallsign }}</strong> accepted.<br>
    Their slot ended at <strong style="color:#fff;">{{ $nowTime }}</strong> — please QSY to frequency and take over net control now.
  </p>

  <div class="ha-card">
    <div class="ha-row">
      <span class="ha-label">Handed over from</span>
      <span class="ha-value">{{ $requesterCallsign }}</span>
    </div>
    <div class="ha-row">
      <span class="ha-label">Effective time</span>
      <span class="ha-value">{{ $nowTime }}</span>
    </div>
    <div class="ha-row">
      <span class="ha-label">Frequency</span>
      <span class="ha-value">145.500 MHz</span>
    </div>
  </div>

  <div class="ha-countdown-wrap">
    <div class="ha-countdown-num" id="cdNum">15</div>
    <div class="ha-countdown-label">Redirecting to your net control panel&hellip;</div>
    <div class="ha-progress">
      <div class="ha-progress-bar" id="cdBar" style="width:100%"></div>
    </div>
  </div>

  <a href="/net-control" class="ha-btn" id="goBtn">Go to Net Control now →</a>

  <p class="ha-footer">
    {{ $groupName }}<br>
    Volunteer emergency communications for Merseyside · 73
  </p>
</div>
<script>
(function(){
  const numEl = document.getElementById('cdNum');
  const barEl = document.getElementById('cdBar');
  const FALLBACK = 15;

  function startCountdown(redirectAt) {
    function tick() {
      var secsLeft = Math.max(0, Math.ceil((redirectAt - Date.now()) / 1000));
      if (numEl) numEl.textContent = secsLeft;
      if (barEl) barEl.style.width = Math.min(100, (secsLeft / FALLBACK) * 100) + '%';
      if (secsLeft <= 0) {
        window.location.href = '/net-control';
      } else {
        setTimeout(tick, 500);
      }
    }
    tick();
  }

  // Fetch the shared redirect_at timestamp so both sides are in sync
  fetch('/net-control/handover-redirect-at', {cache:'no-store'})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (d.redirect_at) {
        startCountdown(d.redirect_at);
      } else {
        startCountdown(Date.now() + FALLBACK * 1000);
      }
    })
    .catch(function(){
      startCountdown(Date.now() + FALLBACK * 1000);
    });
})();
</script>
@endsection
