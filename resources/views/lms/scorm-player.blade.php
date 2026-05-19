<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $lesson->title }} — SCORM Player</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; overflow: hidden; font-family: Arial, sans-serif; background: #001f40; }

.player-chrome {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.player-bar {
    background: #003366;
    border-bottom: 3px solid #C8102E;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 14px;
    flex-shrink: 0;
    gap: 10px;
}

.player-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.player-logo {
    width: 26px; height: 26px;
    background: #C8102E;
    display: flex; align-items: center; justify-content: center;
    font-size: 7px; font-weight: bold; color: #fff;
    text-align: center; line-height: 1.2; text-transform: uppercase;
    flex-shrink: 0;
}

.player-title {
    font-size: 12px; font-weight: bold; color: #fff;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    letter-spacing: .03em;
}

.player-status {
    font-size: 10px; font-weight: bold;
    padding: 2px 8px; border: 1px solid;
    text-transform: uppercase; letter-spacing: .06em;
    flex-shrink: 0;
}

.status-incomplete { background: rgba(200,16,46,.15); border-color: rgba(200,16,46,.4); color: #ffb3be; }
.status-complete   { background: rgba(26,107,60,.2);  border-color: rgba(26,107,60,.4);  color: #86efac; }
.status-failed     { background: rgba(200,16,46,.15); border-color: rgba(200,16,46,.4);  color: #fca5a5; }

.player-back {
    font-size: 11px; font-weight: bold; color: rgba(255,255,255,.65);
    text-decoration: none; padding: 4px 10px;
    border: 1px solid rgba(255,255,255,.2); flex-shrink: 0;
    transition: color .15s, border-color .15s;
}
.player-back:hover { color: #fff; border-color: rgba(255,255,255,.5); }

.player-frame-wrap {
    flex: 1;
    position: relative;
    overflow: hidden;
}

#scormFrame {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}

.player-loading {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 12px;
    background: #001f40; color: rgba(255,255,255,.5);
    font-size: 13px;
    transition: opacity .3s;
}

.loading-spinner {
    width: 32px; height: 32px;
    border: 3px solid rgba(255,255,255,.15);
    border-top-color: #C8102E;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.complete-banner {
    display: none;
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #1a6b3c; color: #fff;
    padding: 10px 20px;
    font-size: 13px; font-weight: bold;
    text-align: center;
    z-index: 100;
    animation: slideUp .3s ease;
}
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
</style>
</head>
<body>

<div class="player-chrome">
    <div class="player-bar">
        <div class="player-bar-left">
            <div class="player-logo">RAY<br>NET</div>
            <div class="player-title">{{ $lesson->title }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="player-status status-incomplete" id="statusBadge">In Progress</span>
            <a href="{{ route('lms.course', ['slug' => $lesson->module->course->slug ?? '']) }}" class="player-back">← Exit</a>
        </div>
    </div>

    <div class="player-frame-wrap">
        <div class="player-loading" id="loadingOverlay">
            <div class="loading-spinner"></div>
            <div>Loading SCORM content…</div>
        </div>
        <iframe
            id="scormFrame"
            src="{{ $launchUrl }}"
            allowfullscreen
            allow="fullscreen"
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-top-navigation-by-user-activation"
        ></iframe>
    </div>
</div>

<div class="complete-banner" id="completeBanner">
    ✓ Module complete — your progress has been saved.
</div>

<script>
// ─────────────────────────────────────────────────────────────────────────────
// SCORM 1.2 API — runs in the PARENT page, available to the iframe via
// window.parent.API (SCORM packages look up the DOM tree for window.API)
// ─────────────────────────────────────────────────────────────────────────────

const LESSON_ID   = {{ $lesson->id }};
const CSRF        = '{{ $csrfToken }}';
const COMPLETE_URL = '{{ $completeUrl }}';

// CMI data pre-loaded server-side — available synchronously for LMSInitialize
var _scormData     = {!! json_encode($scormData) !!};
var _initialized   = false;
var _completed     = {{ $scormData->has('cmi.core.lesson_status') && in_array($scormData->get('cmi.core.lesson_status'), ['passed','completed']) ? 'true' : 'false' }};

function _persist(key, value) {
    fetch('/my-training/scorm/' + LESSON_ID + '/api/set', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ key, value })
    }).catch(() => {});
}

function _markComplete() {
    if (_completed) return;
    _completed = true;

    // Hit the existing LMS completion endpoint
    fetch(COMPLETE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({})
    }).catch(() => {});

    document.getElementById('statusBadge').textContent  = 'Complete';
    document.getElementById('statusBadge').className    = 'player-status status-complete';
    document.getElementById('completeBanner').style.display = 'block';
    setTimeout(() => { document.getElementById('completeBanner').style.display = 'none'; }, 5000);
}

// ── SCORM 1.2 API ────────────────────────────────────────────────────────────
window.API = {
    LMSInitialize: function(param) {
        _initialized = true;
        return 'true';
    },
    LMSFinish: function(param) {
        _initialized = false;
        return 'true';
    },
    LMSGetValue: function(element) {
        // Sensible defaults
        if (element === 'cmi.core.lesson_status'  && !_scormData[element]) return 'not attempted';
        if (element === 'cmi.core.lesson_location' && !_scormData[element]) return '';
        if (element === 'cmi.core.score.raw'       && !_scormData[element]) return '';
        if (element === 'cmi.core.score.min'       && !_scormData[element]) return '0';
        if (element === 'cmi.core.score.max'       && !_scormData[element]) return '100';
        if (element === 'cmi.suspend_data'         && !_scormData[element]) return '';
        if (element === 'cmi.core.student_name'    ) return '{{ auth()->user()->name ?? "Learner" }}';
        if (element === 'cmi.core.student_id'      ) return '{{ auth()->id() ?? "0" }}';
        return _scormData[element] !== undefined ? _scormData[element] : '';
    },
    LMSSetValue: function(element, value) {
        _scormData[element] = value;
        _persist(element, value);

        // Watch for completion signals
        if (element === 'cmi.core.lesson_status') {
            const s = String(value).toLowerCase();
            if (s === 'passed' || s === 'completed') _markComplete();
            if (s === 'failed') {
                document.getElementById('statusBadge').textContent = 'Not Passed';
                document.getElementById('statusBadge').className   = 'player-status status-failed';
            }
        }
        return 'true';
    },
    LMSCommit: function(param) {
        return 'true';
    },
    LMSGetLastError: function() { return '0'; },
    LMSGetErrorString: function(errorCode) { return ''; },
    LMSGetDiagnostic:  function(errorCode) { return ''; },
};

// ── SCORM 2004 API ────────────────────────────────────────────────────────────
window.API_1484_11 = {
    Initialize:   function(s) { _initialized = true; return 'true'; },
    Terminate:    function(s) { _initialized = false; return 'true'; },
    GetValue:     function(e) { return window.API.LMSGetValue(e); },
    SetValue:     function(e, v) {
        // Map SCORM 2004 status keys to 1.2 equivalents for our bridge
        if (e === 'cmi.completion_status') {
            _scormData['cmi.core.lesson_status'] = v;
            _persist('cmi.core.lesson_status', v);
            if (v === 'completed') _markComplete();
        }
        if (e === 'cmi.success_status') {
            if (v === 'passed') _markComplete();
        }
        _scormData[e] = v;
        _persist(e, v);
        return 'true';
    },
    Commit:           function(s) { return 'true'; },
    GetLastError:     function()  { return '0'; },
    GetErrorString:   function(c) { return ''; },
    GetDiagnostic:    function(c) { return ''; },
};

// ── Hide loading overlay when iframe loads ────────────────────────────────────
document.getElementById('scormFrame').addEventListener('load', function () {
    const ov = document.getElementById('loadingOverlay');
    ov.style.opacity = '0';
    setTimeout(() => ov.style.display = 'none', 300);
});
</script>

</body>
</html>
