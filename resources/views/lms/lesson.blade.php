@extends('layouts.app')
@section('title', $lesson->title . ' — ' . $course->title)
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--teal:#0288d1;--green:#1a6b3c;--green-bg:#eef7f2;--amber:#8a5500;--amber-bg:#fdf8ec;--grey:#f2f5f9;--grey-mid:#dde2e8;--white:#fff;--text:#001f40;--text-mid:#2d4a6b;--muted:#6b7f96;--shadow-sm:0 1px 3px rgba(0,51,102,.09);--font:Arial,'Helvetica Neue',Helvetica,sans-serif;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--font);background:var(--grey);color:var(--text);}
.lesson-header{background:var(--navy);border-bottom:3px solid var(--red);padding:0 1.5rem;position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(0,0,0,.25);}
.lesson-header-inner{max-width:1000px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:52px;gap:1rem;}
.lesson-header-left{display:flex;align-items:center;gap:.75rem;min-width:0;}
.lesson-header-course{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.45);}
.lesson-header-title{font-size:13px;font-weight:bold;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px;}
.lesson-header-type{font-size:10px;font-weight:bold;padding:2px 7px;border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;}
.btn{display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;border:1px solid;font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;white-space:nowrap;}
.btn-primary{background:var(--navy);border-color:var(--navy);color:#fff;}
.btn-primary:hover{background:#002244;}
.btn-teal{background:var(--teal);border-color:var(--teal);color:#fff;}
.btn-ghost{background:transparent;border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.7);}
.btn-ghost:hover{border-color:rgba(255,255,255,.5);color:#fff;}
.btn-green{background:var(--green-bg);border-color:#b8ddc9;color:var(--green);}
.btn-sm{padding:.25rem .65rem;font-size:10px;}
.wrap{max-width:1000px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}
.lesson-layout{display:grid;grid-template-columns:1fr 260px;gap:1.5rem;align-items:start;}
@media(max-width:800px){.lesson-layout{grid-template-columns:1fr;}}
.lesson-content{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);}
.lesson-content-head{padding:.75rem 1.1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);font-size:12px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.06em;}
.lesson-content-body{padding:1.5rem;font-size:14px;line-height:1.75;color:var(--text-mid);}
.lesson-content-body h1,.lesson-content-body h2,.lesson-content-body h3{color:var(--navy);margin:1.25rem 0 .6rem;font-weight:bold;}
.lesson-content-body p{margin-bottom:.85rem;}
.lesson-content-body ul,.lesson-content-body ol{margin:.5rem 0 .85rem 1.5rem;}
.lesson-content-body li{margin-bottom:.3rem;}
.lesson-content-body strong{color:var(--text);font-weight:bold;}
.lesson-content-foot{padding:.85rem 1.1rem;border-top:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;}
.video-wrap{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;margin-bottom:1rem;}
.video-wrap iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:none;}
.scorm-wrap{width:100%;height:600px;border:1px solid var(--grey-mid);}
.complete-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.3rem;background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);font-family:var(--font);font-size:12px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
.complete-btn:hover{background:#d6ede3;}
.complete-btn.done{background:var(--green);color:#fff;cursor:default;}
.complete-btn:disabled{opacity:.4;cursor:not-allowed;}
.nav-sidebar{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);position:sticky;top:62px;}
.nav-sidebar-head{padding:.65rem 1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);}
.nav-lesson-link{display:flex;align-items:center;gap:.6rem;padding:.5rem 1rem;font-size:12px;text-decoration:none;color:var(--text-mid);border-bottom:1px solid var(--grey-mid);border-left:3px solid transparent;transition:background .1s;}
.nav-lesson-link:last-child{border-bottom:none;}
.nav-lesson-link:hover{background:#f5f8ff;}
.nav-lesson-link.active{background:#e8eef5;border-left-color:var(--navy);font-weight:bold;color:var(--navy);}
.nav-lesson-link.done{border-left-color:var(--green);}
.nav-lesson-icon{font-size:12px;flex-shrink:0;width:16px;text-align:center;}
.nav-lesson-check{font-size:12px;color:var(--green);margin-left:auto;flex-shrink:0;}
.complete-success{background:var(--green-bg);border:1px solid #b8ddc9;border-left:3px solid var(--green);padding:.6rem 1rem;font-size:12px;font-weight:bold;color:var(--green);display:none;margin-top:.75rem;}

/* ── Audio player ── */
.audio-player-wrap{background:var(--navy);padding:1.5rem;text-align:center;margin-bottom:1rem;}
.audio-player-wrap audio{width:100%;outline:none;}
.audio-player-wrap .audio-title{font-size:13px;font-weight:bold;color:#fff;margin-bottom:.75rem;}

/* ── Document viewer ── */
.doc-viewer-wrap{border:1px solid var(--grey-mid);margin-bottom:1rem;overflow:hidden;}
.doc-viewer-toolbar{background:var(--navy);padding:.65rem 1rem;display:flex;align-items:center;justify-content:space-between;}
.doc-viewer-title{font-size:12px;font-weight:bold;color:#fff;}
.doc-viewer-frame{width:100%;height:600px;border:none;display:block;}
.doc-viewer-fallback{padding:2rem;text-align:center;}

/* ── Presentation embed ── */
.presentation-wrap{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;margin-bottom:1rem;border:1px solid var(--grey-mid);}
.presentation-wrap iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:none;}

/* ── External link ── */
.external-link-card{border:1px solid var(--grey-mid);overflow:hidden;margin-bottom:1rem;}
.external-link-header{background:var(--navy);padding:1.5rem;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.75rem;text-align:center;}
.external-link-icon{font-size:3rem;}
.external-link-title{font-size:16px;font-weight:bold;color:#fff;}
.external-link-url{font-size:11px;color:rgba(255,255,255,.5);font-family:monospace;word-break:break-all;}
.external-link-body{padding:1.25rem;text-align:center;}
.external-open-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.75rem;background:var(--teal);border:none;color:#fff;font-family:var(--font);font-size:13px;font-weight:bold;cursor:pointer;text-decoration:none;text-transform:uppercase;letter-spacing:.06em;transition:all .12s;}
.external-open-btn:hover{background:#0277bd;}
.external-visited-note{font-size:11px;color:var(--muted);margin-top:.75rem;}

/* ── Checklist ── */
.checklist-wrap{padding:.5rem 0;}
.checklist-item{display:flex;align-items:flex-start;gap:.85rem;padding:.75rem 1rem;border-bottom:1px solid var(--grey-mid);cursor:pointer;transition:background .1s;user-select:none;}
.checklist-item:last-child{border-bottom:none;}
.checklist-item:hover{background:#f5f8ff;}
.checklist-item.checked{background:var(--green-bg);}
.checklist-checkbox{width:20px;height:20px;border:2px solid var(--grey-mid);flex-shrink:0;margin-top:1px;display:flex;align-items:center;justify-content:center;transition:all .15s;background:var(--white);}
.checklist-item.checked .checklist-checkbox{background:var(--green);border-color:var(--green);color:#fff;font-size:12px;}
.checklist-item-text{flex:1;font-size:13px;color:var(--text-mid);line-height:1.5;}
.checklist-item.checked .checklist-item-text{color:var(--green);text-decoration:line-through;opacity:.75;}
.checklist-progress{padding:.75rem 1rem;background:var(--grey);border-top:1px solid var(--grey-mid);display:flex;align-items:center;gap:.75rem;}
.checklist-progress-bar{flex:1;height:6px;background:var(--grey-mid);overflow:hidden;}
.checklist-progress-fill{height:100%;background:var(--green);transition:width .3s ease;}
.checklist-progress-text{font-size:11px;font-weight:bold;color:var(--muted);flex-shrink:0;}

/* ── Watch notice ── */
.watch-notice{display:flex;align-items:center;gap:.6rem;padding:.6rem .85rem;border:1px solid #f59e0b;border-left:3px solid #f59e0b;font-size:12px;font-weight:bold;color:#78350f;margin-bottom:.75rem;background:#fffbeb;}
.watch-notice.unlocked{background:var(--green-bg);border-color:#b8ddc9;border-left-color:var(--green);color:var(--green);}
</style>

@php
$typeIcons = [
    'text'         => '📄',
    'video'        => '🎬',
    'scorm'        => '📦',
    'quiz'         => '❓',
    'audio'        => '🎧',
    'document'     => '📋',
    'presentation' => '📊',
    'external'     => '🔗',
    'checklist'    => '✅',
];
$typeLabels = [
    'text'         => 'Reading',
    'video'        => 'Video',
    'scorm'        => 'SCORM',
    'quiz'         => 'Quiz',
    'audio'        => 'Audio',
    'document'     => 'Document',
    'presentation' => 'Presentation',
    'external'     => 'External Link',
    'checklist'    => 'Checklist',
];
$isDone = $progressRecord && $progressRecord->completed_at;
$minSeconds = ($lesson->type === 'text' && !empty($lesson->duration_minutes)) ? (int)$lesson->duration_minutes * 60 : 0;

// Video URL conversion
$videoUrl   = $lesson->video_url ?? '';
$isYoutube  = false;
$isVimeo    = false;
$videoId    = '';
if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $videoUrl, $m)) {
    $isYoutube = true; $videoId = $m[1];
    $videoUrl  = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&rel=0';
} elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $videoUrl, $m)) {
    $isYoutube = true; $videoId = $m[1];
    $videoUrl  = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&rel=0';
} elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $videoUrl, $m)) {
    $isYoutube = true; $videoId = $m[1];
    $videoUrl  = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&rel=0';
} elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
    $isVimeo  = true; $videoId = $m[1];
    $videoUrl = 'https://player.vimeo.com/video/' . $videoId . '?api=1';
}

// Parse checklist items from content (one per line)
$checklistItems = [];
if ($lesson->type === 'checklist' && $lesson->content) {
    $checklistItems = array_values(array_filter(
        array_map('trim', explode("\n", $lesson->content)),
        fn($l) => !empty($l)
    ));
}

$allLessons = $course->lessons()->orderBy('sort_order')->get();
$allProgress = \App\Models\CourseProgress::where('user_id', auth()->id())
    ->where('course_id', $course->id)
    ->pluck('completed_at','lesson_id');
@endphp

<div class="lesson-header">
    <div class="lesson-header-inner">
        <div class="lesson-header-left">
            <a href="{{ route('lms.course', $course->slug) }}" class="btn btn-ghost btn-sm">←</a>
            <div>
                <div class="lesson-header-course">{{ $course->title }}</div>
                <div class="lesson-header-title">{{ $lesson->title }}</div>
            </div>
        </div>
        <span class="lesson-header-type">
            {{ $typeIcons[$lesson->type] ?? '📄' }} {{ $typeLabels[$lesson->type] ?? $lesson->type }}
        </span>
    </div>
</div>

<div class="wrap">
    <div class="lesson-layout">
        <div>
            <div class="lesson-content">
                <div class="lesson-content-head">{{ $lesson->title }}</div>
                <div class="lesson-content-body">

                    {{-- ── VIDEO ── --}}
                    @if($lesson->type === 'video' && $lesson->video_url)
                    @if(!$isDone)
                    <div class="watch-notice" id="watchNotice">
                        ⏳ Watch the full video to unlock the complete button.
                    </div>
                    @endif
                    <div class="video-wrap">
                        <iframe id="lessonVideoFrame"
                                src="{{ $videoUrl }}"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>
                    @if($lesson->content)
                        <div style="margin-top:.75rem;padding:.85rem 1rem;background:var(--grey);border:1px solid var(--grey-mid);border-left:3px solid var(--navy);">
                            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.5rem;">Notes / Transcript</div>
                            {!! nl2br(e($lesson->content)) !!}
                        </div>
                    @endif

                    {{-- ── AUDIO ── --}}
                    @elseif($lesson->type === 'audio' && $lesson->video_url)
@if(!$isDone)
<div class="watch-notice" id="watchNotice">
    🎧 Listen to the full recording to unlock the complete button.
</div>
@endif
<div class="audio-player-wrap">
                        <div class="audio-title">🎧 {{ $lesson->title }}</div>
                        <audio id="lessonAudio" controls preload="metadata">
                            <source src="{{ $lesson->video_url }}" type="audio/mpeg">
                            <source src="{{ $lesson->video_url }}" type="audio/ogg">
                            Your browser does not support audio playback.
                        </audio>
                    </div>
                    @if($lesson->content)
                        <div style="margin-top:.75rem;">
                            {!! nl2br(e($lesson->content)) !!}
                        </div>
                    @endif

                    {{-- ── DOCUMENT / PDF ── --}}
                    @elseif($lesson->type === 'document')
                    @if($lesson->video_url)
                    <div class="doc-viewer-wrap">
                        <div class="doc-viewer-toolbar">
                            <span class="doc-viewer-title">📋 {{ $lesson->title }}</span>
                            <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-ghost btn-sm">↗ Open in new tab</a>
                        </div>
                        <iframe class="doc-viewer-frame"
                                src="{{ Str::contains($lesson->video_url, 'docs.google') ? $lesson->video_url : 'https://docs.google.com/viewer?url=' . urlencode($lesson->video_url) . '&embedded=true' }}"
                                title="{{ $lesson->title }}">
                        </iframe>
                    </div>
                    @endif
                    @if($lesson->content)
                        {!! nl2br(e($lesson->content)) !!}
                    @endif

                    {{-- ── PRESENTATION ── --}}
                    @elseif($lesson->type === 'presentation' && $lesson->video_url)
                    <div class="presentation-wrap">
                        <iframe src="{{ $lesson->video_url }}"
                                allowfullscreen
                                allow="autoplay">
                        </iframe>
                    </div>
                    @if($lesson->content)
                        <div style="margin-top:.75rem;">
                            {!! nl2br(e($lesson->content)) !!}
                        </div>
                    @endif

                    {{-- ── EXTERNAL LINK ── --}}
                    @elseif($lesson->type === 'external' && $lesson->video_url)
                    <div class="external-link-card">
                        <div class="external-link-header">
                            <div class="external-link-icon">🔗</div>
                            <div class="external-link-title">{{ $lesson->title }}</div>
                            <div class="external-link-url">{{ $lesson->video_url }}</div>
                        </div>
                        <div class="external-link-body">
                            @if($lesson->content)
                                <p style="font-size:13px;color:var(--text-mid);margin-bottom:1rem;">{{ $lesson->content }}</p>
                            @endif
                            <a href="{{ $lesson->video_url }}"
                               target="_blank" rel="noopener noreferrer"
                               class="external-open-btn"
                               id="externalLink"
                               onclick="markExternalVisited()">
                                ↗ Open External Resource
                            </a>
                            @if(!$isDone)
                            <div class="external-visited-note" id="externalNote">
                                Click the link above to visit the resource, then mark this lesson complete.
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── CHECKLIST ── --}}
                    @elseif($lesson->type === 'checklist')
                    @if($lesson->content)
                    <div style="font-size:13px;color:var(--text-mid);margin-bottom:.85rem;padding:.75rem 1rem;background:var(--grey);border-left:3px solid var(--navy);">
                        Tick each item as you complete it. All items must be checked before you can mark this lesson as done.
                    </div>
                    <div class="checklist-wrap" id="checklistWrap">
                        @foreach($checklistItems as $ci => $item)
                        <div class="checklist-item" id="check-{{ $ci }}" onclick="toggleCheckItem({{ $ci }}, {{ count($checklistItems) }})">
                            <div class="checklist-checkbox" id="checkbox-{{ $ci }}"></div>
                            <div class="checklist-item-text">{{ $item }}</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="checklist-progress">
                        <div class="checklist-progress-bar">
                            <div class="checklist-progress-fill" id="checklistProgressFill" style="width:0%;"></div>
                        </div>
                        <span class="checklist-progress-text" id="checklistProgressText">0 / {{ count($checklistItems) }}</span>
                    </div>
                    @endif

{{-- ── SCORM ── --}}
@elseif($lesson->type === 'scorm' && $lesson->video_url)
<div style="padding:2rem;text-align:center;background:var(--navy);border:1px solid var(--grey-mid);">
    <div style="font-size:3rem;margin-bottom:.75rem;">📦</div>
    <div style="font-size:15px;font-weight:bold;color:#fff;margin-bottom:.5rem;">{{ $lesson->title }}</div>
    <div style="font-size:12px;color:rgba(255,255,255,.5);margin-bottom:1.25rem;">SCORM interactive module — opens in full screen</div>
    <a href="{{ route('lms.scorm.play', $lesson->id) }}"
       target="_blank"
       class="btn btn-teal"
       style="font-size:13px;padding:.65rem 1.75rem;"
       onclick="setTimeout(function(){ markComplete({{ $lesson->id }}); }, 3000)">
        ▶ Launch Module
    </a>
    @if($isDone)
        <div style="margin-top:1rem;font-size:12px;font-weight:bold;color:#86efac;">✓ Completed</div>
    @endif
</div>
                    {{-- ── TEXT (default) ── --}}
                    @else
                    @if($lesson->content)
                        {!! nl2br(e($lesson->content)) !!}
                    @else
                        <div style="padding:2rem;text-align:center;color:var(--muted);">No content yet.</div>
                    @endif
                    @endif

                    {{-- Quiz block --}}
                    @if($lesson->type === 'quiz' && $lesson->quiz)
                    <div style="padding:1rem;background:var(--grey);border:1px solid var(--grey-mid);margin-top:1rem;text-align:center;">
                        <div style="font-size:2rem;margin-bottom:.5rem;">❓</div>
                        <div style="font-size:14px;font-weight:bold;color:var(--navy);margin-bottom:.3rem;">{{ $lesson->quiz->title }}</div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:.85rem;">
                            {{ $lesson->quiz->questions->count() }} questions · Pass mark: {{ $lesson->quiz->pass_mark }}%
                        </div>
                        @php $prog = \App\Models\CourseProgress::where('user_id',auth()->id())->where('lesson_id',$lesson->id)->first(); @endphp
                        @if($prog && $prog->completed_at)
                            <div style="font-size:12px;font-weight:bold;color:var(--green);margin-bottom:.75rem;">✓ Passed with {{ $prog->quiz_score }}%</div>
                        @endif
                        @if(!$prog || $prog->attempts < $lesson->quiz->attempts_allowed)
                            <a href="{{ route('lms.quiz', [$course->slug, $lesson->quiz->id]) }}" class="btn btn-teal" style="display:inline-flex;">
                                {{ $prog && $prog->attempts > 0 ? '↺ Retry Quiz' : '▶ Start Quiz' }}
                            </a>
                        @else
                            <span style="font-size:11px;color:var(--red);font-weight:bold;">No attempts remaining.</span>
                        @endif
                    </div>
                    @endif

                </div>
                <div class="lesson-content-foot">
                    <div style="display:flex;gap:.5rem;">
                        @if($prevLesson)
                            <a href="{{ route('lms.lesson', [$course->slug, $prevLesson->id]) }}" class="btn btn-ghost" style="border-color:var(--grey-mid);color:var(--muted);">← Previous</a>
                        @endif
                        @if($nextLesson)
                            <a href="{{ route('lms.lesson', [$course->slug, $nextLesson->id]) }}" id="nextBtn" class="btn btn-primary">Next →</a>
                        @else
                            <a href="{{ route('lms.course', $course->slug) }}" class="btn btn-primary">Back to Course →</a>
                        @endif
                    </div>
                    @if($lesson->type !== 'quiz')
@php
    $btnLabel = $isDone ? '✓ Completed'
        : ($lesson->type === 'video'     ? '⏳ Watch video first'
        : ($lesson->type === 'audio'     ? '⏳ Listen first'
        : ($lesson->type === 'checklist' ? '⏳ Complete all items first'
        : ($minSeconds > 0             ? '⏳ Reading...'
        : '✓ Mark Complete'))));
@endphp
<button class="complete-btn {{ $isDone ? 'done' : '' }}"
        id="completeBtn"
        onclick="markComplete({{ $lesson->id }})"
        @if(!$isDone && in_array($lesson->type, ['video','audio','checklist'])) disabled @endif
        @if(!$isDone && $minSeconds > 0) disabled @endif>{{ $btnLabel }}</button>
                    @endif
                </div>
                <div class="complete-success" id="completeSuccess">✓ Lesson marked as complete!</div>
            </div>
        </div>

        {{-- Lesson nav sidebar --}}
        <div class="nav-sidebar">
            <div class="nav-sidebar-head">Lessons</div>
            @foreach($allLessons as $l)
            @php
                $lDone    = isset($allProgress[$l->id]) && $allProgress[$l->id];
                $isActive = $l->id === $lesson->id;
            @endphp
            <a href="{{ route('lms.lesson', [$course->slug, $l->id]) }}"
               class="nav-lesson-link {{ $isActive?'active':'' }} {{ $lDone?'done':'' }}">
                <span class="nav-lesson-icon">{{ $typeIcons[$l->type] ?? '📄' }}</span>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;">{{ $l->title }}</span>
                @if($lDone)<span class="nav-lesson-check">✓</span>@endif
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
// ── Mark complete ──────────────────────────────────────────────────────────
async function markComplete(lessonId) {
    const btn = document.getElementById('completeBtn');
    if (btn && btn.disabled) return;
    if (btn && btn.classList.contains('done')) return;
    if (btn) btn.textContent = '…';
    const r = await fetch('/my-training/lesson/' + lessonId + '/complete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });
    const data = await r.json();
    if (data.success) {
        if (btn) { btn.textContent = '✓ Completed'; btn.classList.add('done'); btn.disabled = false; }
        document.getElementById('completeSuccess').style.display = 'block';
    }
}

function unlockComplete(msg) {
    const btn = document.getElementById('completeBtn');
    const notice = document.getElementById('watchNotice');
    if (!btn) return;
    btn.disabled      = false;
    btn.style.opacity = '';
    btn.style.cursor  = '';
    btn.textContent   = '✓ Mark Complete';
    if (notice) {
        notice.className   = 'watch-notice unlocked';
        notice.textContent = msg || '✓ You can now mark this lesson as complete.';
    }
}

// ── VIDEO ──────────────────────────────────────────────────────────────────
@if($lesson->type === 'video' && !$isDone)
const IS_YOUTUBE = {{ $isYoutube ? 'true' : 'false' }};
const IS_VIMEO   = {{ $isVimeo ? 'true' : 'false' }};

@if($isYoutube)
var tag = document.createElement('script');
tag.src = 'https://www.youtube.com/iframe_api';
document.head.appendChild(tag);
var ytPlayer;
function onYouTubeIframeAPIReady() {
    ytPlayer = new YT.Player('lessonVideoFrame', {
        events: { onStateChange: function(e) { if (e.data === 0) unlockComplete('✓ Video complete — mark this lesson as done.'); } }
    });
}
@endif

@if($isVimeo)
var vs = document.createElement('script');
vs.src = 'https://player.vimeo.com/api/player.js';
vs.onload = function() {
    var vp = new Vimeo.Player(document.getElementById('lessonVideoFrame'));
    vp.on('ended', function() { unlockComplete('✓ Video complete — mark this lesson as done.'); });
};
document.head.appendChild(vs);
@endif

@if(!$isYoutube && !$isVimeo)
setTimeout(function() { unlockComplete('✓ You can now mark this lesson as done.'); }, 5000);
@endif
@endif

// ── AUDIO ──────────────────────────────────────────────────────────────────
@if($lesson->type === 'audio' && !$isDone)
document.addEventListener('DOMContentLoaded', function() {
    var audio = document.getElementById('lessonAudio');
    if (!audio) return;

    // Track actual listened segments — prevents seek-to-end bypass
    var listenedSegments = [];
    var lastTime = 0;
    var audioUnlocked = false;

    function addSegment(start, end) {
        if (end <= start) return;
        listenedSegments.push([start, end]);
        // Merge overlapping segments
        listenedSegments.sort(function(a, b) { return a[0] - b[0]; });
        var merged = [listenedSegments[0]];
        for (var i = 1; i < listenedSegments.length; i++) {
            var last = merged[merged.length - 1];
            if (listenedSegments[i][0] <= last[1]) {
                last[1] = Math.max(last[1], listenedSegments[i][1]);
            } else {
                merged.push(listenedSegments[i]);
            }
        }
        listenedSegments = merged;
    }

    function totalListened() {
        return listenedSegments.reduce(function(sum, seg) {
            return sum + (seg[1] - seg[0]);
        }, 0);
    }

    function checkUnlock() {
        if (audioUnlocked) return;
        if (!audio.duration) return;
        var pct = totalListened() / audio.duration;
        // Update progress notice
        var notice = document.getElementById('watchNotice');
        var listenedPct = Math.min(100, Math.round(pct * 100));
        if (notice && !audioUnlocked) {
            notice.textContent = '🎧 Listening… ' + listenedPct + '% heard — listen to the full recording to unlock.';
        }
        // Require 95% listened to account for tiny gaps
        if (pct >= 0.95) {
            audioUnlocked = true;
            unlockComplete('✓ Audio complete — mark this lesson as done.');
        }
    }

    // Record position before any seek
    audio.addEventListener('seeking', function() {
        addSegment(lastTime, audio.currentTime);
    });

    audio.addEventListener('timeupdate', function() {
        // Only count forward playback (not seeks)
        if (!audio.seeking && Math.abs(audio.currentTime - lastTime) < 1.5) {
            addSegment(lastTime, audio.currentTime);
        }
        lastTime = audio.currentTime;
        checkUnlock();
    });

    audio.addEventListener('ended', function() {
        addSegment(lastTime, audio.duration);
        lastTime = audio.currentTime;
        checkUnlock();
        // Force unlock on ended regardless — they made it to the end
        if (!audioUnlocked) {
            audioUnlocked = true;
            unlockComplete('✓ Audio complete — mark this lesson as done.');
        }
    });

    // Show initial notice
    var notice = document.getElementById('watchNotice');
    if (notice) {
        notice.textContent = '🎧 Listen to the full recording to unlock the complete button.';
    }
});
@endif
// ── EXTERNAL LINK ──────────────────────────────────────────────────────────
@if($lesson->type === 'external' && !$isDone)
function markExternalVisited() {
    setTimeout(function() {
        unlockComplete('✓ Resource visited — mark this lesson as done.');
        var note = document.getElementById('externalNote');
        if (note) note.textContent = '✓ Resource opened. You can now mark this lesson as complete.';
    }, 500);
}
@endif

// ── CHECKLIST ──────────────────────────────────────────────────────────────
@if($lesson->type === 'checklist')
var checkedItems = new Set();
var totalItems   = {{ count($checklistItems) }};

function toggleCheckItem(idx, total) {
    const item     = document.getElementById('check-' + idx);
    const checkbox = document.getElementById('checkbox-' + idx);
    if (!item || !checkbox) return;

    if (checkedItems.has(idx)) {
        checkedItems.delete(idx);
        item.classList.remove('checked');
        checkbox.textContent = '';
    } else {
        checkedItems.add(idx);
        item.classList.add('checked');
        checkbox.textContent = '✓';
    }

    const count = checkedItems.size;
    const pct   = total > 0 ? Math.round((count / total) * 100) : 0;

    const fill = document.getElementById('checklistProgressFill');
    const text = document.getElementById('checklistProgressText');
    if (fill) fill.style.width = pct + '%';
    if (text) text.textContent = count + ' / ' + total;

    if (count >= total) {
        unlockComplete('✓ All items checked — mark this lesson as done.');
    } else {
        const btn = document.getElementById('completeBtn');
        if (btn && !btn.classList.contains('done')) {
            btn.disabled      = true;
            btn.textContent   = '⏳ Complete all items first';
        }
    }
}
@endif
// ── TEXT READING TIMER ─────────────────────────────────────────────────────
@if($lesson->type === 'text' && !$isDone && $minSeconds > 0)
(function() {
    var minSeconds = {{ $minSeconds }};
    var elapsed    = 0;
    var unlocked   = false;

    function tick() {
        if (document.hidden) return;
        elapsed++;
        var remaining = minSeconds - elapsed;
        var btn = document.getElementById('completeBtn');
        if (remaining > 0) {
            var mins = Math.floor(remaining / 60);
            var secs = remaining % 60;
            var display = mins > 0 ? mins + 'm ' + secs + 's remaining' : secs + 's remaining';
            if (btn) btn.textContent = '⏳ ' + display;
        } else {
            if (unlocked) return;
            unlocked = true;
            unlockComplete('✓ You can now mark this lesson as complete.');
        }
    }

    setInterval(tick, 1000);
})();
@endif
</script>
@endsection