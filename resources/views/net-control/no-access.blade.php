@extends('layouts.app')
@section('title','Net Control Portal')
@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
  <div style="max-width:480px;width:100%;text-align:center;">
    <div style="font-size:3rem;margin-bottom:1rem;">📻</div>
    <h1 style="font-size:1.5rem;font-weight:900;color:#003366;margin-bottom:.5rem;">Net Control Portal</h1>

    @if($nextSlot)
      <div style="background:#f0f4ff;border:1px solid #c7d7ff;border-radius:12px;padding:1.5rem;margin:1.5rem 0;">
        <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#6366f1;margin-bottom:.5rem;">Your Next Slot</div>
        <div style="font-size:2rem;font-weight:900;font-family:monospace;color:#003366;">
          {{ $nextSlot['from'] }} – {{ $nextSlot['to'] }}
        </div>
        <div style="font-size:.82rem;color:#64748b;margin-top:.5rem;">
          Access opens {{ \App\Http\Middleware\NetControllerAccess::WINDOW_MINUTES }} minute(s) before your slot starts
        </div>
        <div id="ncCountdown" style="font-size:1.1rem;font-weight:800;color:#C8102E;margin-top:.75rem;font-family:monospace;"></div>
      </div>
      <script>
      (function(){
        var target = new Date();
        var parts  = '{{ $nextSlot['from'] }}'.split(':');
        target.setHours(parseInt(parts[0]), parseInt(parts[1]) - {{ \App\Http\Middleware\NetControllerAccess::WINDOW_MINUTES }}, 0, 0);
        // If target is in the past (past midnight), move to next day
        if (target < new Date()) { target = new Date(target.getTime() + 86400000); }
        function tick(){
          var diff = Math.floor((target - Date.now()) / 1000);
          if (diff <= 0) { location.reload(); return; }
          var h = Math.floor(diff/3600), m = Math.floor((diff%3600)/60), s = diff%60;
          document.getElementById('ncCountdown').textContent =
            (h?String(h).padStart(2,'0')+':':'') + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' until access opens';
          setTimeout(tick, 1000);
        }
        tick();
      })();
      </script>
    @else
      <div style="background:#f1f5f9;border-radius:12px;padding:1.5rem;margin:1.5rem 0;color:#64748b;font-size:.9rem;">
        You are not currently scheduled as Net Controller.<br>
        Contact your group administrator if you believe this is an error.
      </div>
    @endif

    <a href="{{ url('/') }}" style="font-size:.85rem;color:#6366f1;text-decoration:none;font-weight:700;">← Back to Home</a>
  </div>
</div>
@endsection
