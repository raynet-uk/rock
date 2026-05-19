@extends('layouts.admin')
@section('title', 'Analytics: ' . $course->title)
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--teal:#0288d1;--green:#1a6b3c;--green-bg:#eef7f2;--amber:#8a5500;--amber-bg:#fdf8ec;--grey:#f2f2f2;--grey-mid:#dde2e8;--white:#fff;--text:#001f40;--text-mid:#2d4a6b;--muted:#6b7f96;--shadow-sm:0 1px 3px rgba(0,51,102,.09);--font:Arial,'Helvetica Neue',Helvetica,sans-serif;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--font);background:var(--grey);color:var(--text);}
.a-header{background:var(--navy);border-bottom:4px solid var(--red);padding:0 1.5rem;}
.a-header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;}
.a-title{font-size:14px;font-weight:bold;color:#fff;}
.a-sub{font-size:10px;color:rgba(255,255,255,.45);margin-top:1px;}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .95rem;border:1px solid;font-family:var(--font);font-size:11px;font-weight:bold;cursor:pointer;text-decoration:none;text-transform:uppercase;letter-spacing:.05em;transition:all .12s;}
.btn-ghost{background:transparent;border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.7);}
.btn-ghost:hover{border-color:rgba(255,255,255,.4);color:#fff;}
.btn-ghost-dark{background:transparent;border-color:var(--grey-mid);color:var(--muted);}
.btn-ghost-dark:hover{border-color:var(--navy);color:var(--navy);}
.btn-sm{padding:.25rem .65rem;font-size:10px;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}

/* Stat tiles */
.stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;}
@media(max-width:900px){.stat-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.stat-grid{grid-template-columns:1fr 1fr;}}
.stat-tile{background:var(--white);border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:.9rem 1rem;box-shadow:var(--shadow-sm);}
.stat-tile.t-green{border-top-color:var(--green);}
.stat-tile.t-red{border-top-color:var(--red);}
.stat-tile.t-teal{border-top-color:var(--teal);}
.stat-tile.t-amber{border-top-color:var(--amber);}
.stat-num{font-size:28px;font-weight:bold;color:var(--navy);line-height:1;}
.stat-tile.t-green .stat-num{color:var(--green);}
.stat-tile.t-red .stat-num{color:var(--red);}
.stat-tile.t-teal .stat-num{color:var(--teal);}
.stat-tile.t-amber .stat-num{color:var(--amber);}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.3rem;}
.stat-sub{font-size:11px;color:var(--muted);margin-top:.2rem;}

/* Section */
.sec-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;gap:.75rem;flex-wrap:wrap;}
.sec-title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.14em;color:var(--muted);display:flex;align-items:center;gap:.5rem;}
.sec-title::before{content:'';width:3px;height:14px;background:var(--red);display:inline-block;}

/* Card */
.card{background:var(--white);border:1px solid var(--grey-mid);box-shadow:var(--shadow-sm);margin-bottom:1.25rem;overflow:hidden;}
.card-head{padding:.75rem 1.1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;}
.card-head-title{font-size:12px;font-weight:bold;color:var(--navy);text-transform:uppercase;letter-spacing:.06em;}
.card-head-meta{font-size:11px;color:var(--muted);}

/* Tables */
.table{width:100%;border-collapse:collapse;}
.table th{padding:.5rem 1rem;text-align:left;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);border-bottom:1px solid var(--grey-mid);background:var(--grey);white-space:nowrap;}
.table td{padding:.6rem 1rem;font-size:13px;border-bottom:1px solid var(--grey-mid);color:var(--text-mid);vertical-align:middle;}
.table tr:last-child td{border-bottom:none;}
.table tr:hover td{background:#f5f8ff;}
.table-wrap{overflow-x:auto;}

/* Progress bar */
.prog-bar{height:6px;background:var(--grey-mid);overflow:hidden;min-width:80px;}
.prog-fill{height:100%;background:var(--navy);transition:width .4s ease;}
.prog-fill.complete{background:var(--green);}
.prog-fill.fail{background:var(--red);}

/* Badge */
.badge{display:inline-flex;align-items:center;padding:2px 8px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;}
.badge-green{background:var(--green-bg);border:1px solid #b8ddc9;color:var(--green);}
.badge-amber{background:var(--amber-bg);border:1px solid #f5d87a;color:var(--amber);}
.badge-grey{background:var(--grey);border:1px solid var(--grey-mid);color:var(--muted);}
.badge-red{background:#fdf0f2;border:1px solid rgba(200,16,46,.25);color:var(--red);}
.badge-teal{background:#e0f2fe;border:1px solid #7dd3fc;color:#0369a1;}
.badge-navy{background:#e8eef5;border:1px solid rgba(0,51,102,.2);color:var(--navy);}

/* Quiz section */
.quiz-card{border-top:3px solid var(--teal);margin-bottom:1.5rem;}
.quiz-stat-row{display:flex;gap:1rem;padding:.85rem 1.1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);flex-wrap:wrap;}
.quiz-stat{display:flex;flex-direction:column;gap:2px;}
.quiz-stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);}
.quiz-stat-val{font-size:16px;font-weight:bold;color:var(--navy);}

/* SCORM section */
.scorm-card{border-top:3px solid var(--navy);margin-bottom:1.5rem;}
.scorm-stat-row{display:flex;gap:1rem;padding:.85rem 1.1rem;border-bottom:1px solid var(--grey-mid);background:var(--grey);flex-wrap:wrap;}
.scorm-bar-wrap{padding:.85rem 1.1rem;border-bottom:1px solid var(--grey-mid);}
.scorm-bar-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);margin-bottom:.75rem;}
.scorm-bar-row{display:flex;align-items:center;gap:.75rem;margin-bottom:.45rem;}
.scorm-status-name{font-size:11px;color:var(--text-mid);min-width:140px;flex-shrink:0;}
.scorm-status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.scorm-status-dot.passed   {background:var(--green);}
.scorm-status-dot.failed   {background:var(--red);}
.scorm-status-dot.incomplete{background:var(--amber);}
.scorm-status-dot.not-attempted{background:var(--grey-mid);}

/* Member accordion */
.member-accordion-row{cursor:pointer;}
.member-accordion-row:hover td{background:#f0f5ff !important;}
.member-accordion-body{display:none;}
.member-accordion-body.open{display:table-row;}
.accordion-inner{padding:1rem 1.1rem;background:#fafbfd;border-top:1px solid var(--grey-mid);}

/* Attempt row */
.attempt-block{background:var(--white);border:1px solid var(--grey-mid);margin-bottom:.75rem;overflow:hidden;}
.attempt-head{padding:.55rem .85rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.attempt-num{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);}
.attempt-score{font-size:13px;font-weight:bold;}
.attempt-score.pass{color:var(--green);}
.attempt-score.fail{color:var(--red);}
.attempt-body{padding:.75rem .85rem;}
.question-result{display:flex;align-items:flex-start;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--grey-mid);}
.question-result:last-child{border-bottom:none;}
.q-result-icon{font-size:14px;flex-shrink:0;margin-top:1px;width:20px;text-align:center;}
.q-result-text{flex:1;}
.q-result-question{font-size:12px;font-weight:bold;color:var(--text);margin-bottom:.25rem;}
.q-result-answer{font-size:11px;color:var(--text-mid);}
.q-result-answer.correct{color:var(--green);font-weight:bold;}
.q-result-answer.wrong{color:var(--red);}
.q-correct-was{font-size:11px;color:var(--green);margin-top:.2rem;}

/* Lesson progress grid */
.lesson-prog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.5rem;padding:1rem 1.1rem;}
.lesson-prog-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .65rem;border:1px solid var(--grey-mid);background:var(--grey);font-size:11px;}
.lesson-prog-item.done{background:var(--green-bg);border-color:#b8ddc9;}
.lesson-prog-item.done .lpi-icon{color:var(--green);}
.lesson-prog-item.not-done .lpi-icon{color:var(--grey-mid);}
.lpi-icon{font-size:12px;flex-shrink:0;}
.lpi-name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-mid);}
.lpi-name.done{color:var(--green);font-weight:bold;}

/* Expand toggle */
.expand-toggle{font-size:10px;font-weight:bold;color:var(--teal);background:none;border:1px solid var(--grey-mid);padding:2px 8px;cursor:pointer;font-family:var(--font);transition:all .12s;}
.expand-toggle:hover{border-color:var(--teal);background:#e0f2fe;}
</style>

<div class="a-header">
    <div class="a-header-inner">
        <div>
            <div class="a-title">📊 {{ $course->title }}</div>
            <div class="a-sub">Course Analytics · {{ $course->category ?? 'Training' }}</div>
        </div>
        <div style="display:flex;gap:.5rem;">
            <a href="{{ route('admin.lms.edit', $course->id) }}" class="btn btn-ghost btn-sm">✎ Builder</a>
            <a href="{{ route('admin.lms.index') }}" class="btn btn-ghost btn-sm">← Courses</a>
        </div>
    </div>
</div>

<div class="wrap">

    {{-- ── OVERVIEW STATS ── --}}
    <div class="stat-grid">
        <div class="stat-tile">
            <div class="stat-label">Enrolled</div>
            <div class="stat-num">{{ $enrollments->count() }}</div>
            <div class="stat-sub">Total members</div>
        </div>
        <div class="stat-tile t-green">
            <div class="stat-label">Completed</div>
            <div class="stat-num">{{ $enrollments->whereNotNull('completed_at')->count() }}</div>
            <div class="stat-sub">Full course done</div>
        </div>
        <div class="stat-tile t-teal">
            <div class="stat-label">Completion Rate</div>
            <div class="stat-num">{{ $completionRate }}%</div>
            <div class="stat-sub">Of enrolled members</div>
        </div>
        <div class="stat-tile">
            <div class="stat-label">Lessons</div>
            <div class="stat-num">{{ $totalLessons }}</div>
            <div class="stat-sub">In course</div>
        </div>
        <div class="stat-tile t-amber">
            <div class="stat-label">Quizzes</div>
            <div class="stat-num">{{ $quizzes->count() }}</div>
            <div class="stat-sub">In course</div>
        </div>
    </div>

    {{-- ── SCORM ANALYTICS ── --}}
    @if(!empty($scormLessons) && $scormLessons->isNotEmpty())
    <div class="sec-head">
        <div class="sec-title">SCORM Module Analytics</div>
    </div>

    @foreach($scormLessons as $sd)
    @php
        $statusGroups = $sd->memberData->groupBy(fn($d) => strtolower($d['status']));
        $passedCount     = ($statusGroups->get('passed',    collect()))->count()
                         + ($statusGroups->get('completed', collect()))->count()
                         + ($statusGroups->get('complete',  collect()))->count();
        $failedCount     = ($statusGroups->get('failed',    collect()))->count();
        $incompleteCount = ($statusGroups->get('incomplete',collect()))->count();
        $notStarted      = $sd->totalEnrolled - $sd->memberData->count();
    @endphp
    <div class="card scorm-card">
        <div class="card-head">
            <div>
                <div class="card-head-title">📦 {{ $sd->lesson->title }}</div>
                <div class="card-head-meta">SCORM Module · {{ $sd->lesson->video_url ?? 'No package URL' }}</div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                @if($sd->avgScore !== null)
                    <span class="badge badge-navy">Avg score: {{ $sd->avgScore }}%</span>
                @endif
                <span class="badge {{ $sd->passRate >= 70 ? 'badge-green' : ($sd->passRate >= 40 ? 'badge-amber' : 'badge-red') }}">
                    {{ $sd->passRate }}% completion rate
                </span>
            </div>
        </div>

        {{-- Stat row --}}
        <div class="scorm-stat-row">
            <div class="quiz-stat">
                <span class="quiz-stat-label">Total Enrolled</span>
                <span class="quiz-stat-val">{{ $sd->totalEnrolled }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Passed / Completed</span>
                <span class="quiz-stat-val" style="color:var(--green);">{{ $passedCount }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Failed</span>
                <span class="quiz-stat-val" style="color:var(--red);">{{ $failedCount }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">In Progress</span>
                <span class="quiz-stat-val" style="color:var(--amber);">{{ $incompleteCount }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Not Started</span>
                <span class="quiz-stat-val" style="color:var(--muted);">{{ $notStarted }}</span>
            </div>
            @if($sd->avgScore !== null)
            <div class="quiz-stat">
                <span class="quiz-stat-label">Avg Score</span>
                <span class="quiz-stat-val">{{ $sd->avgScore }}%</span>
            </div>
            @endif
        </div>

        {{-- Completion bar --}}
        <div class="scorm-bar-wrap">
            <div class="scorm-bar-label">Completion Breakdown</div>
            @if($sd->totalEnrolled > 0)
            <div style="height:20px;background:var(--grey-mid);display:flex;overflow:hidden;border-radius:2px;margin-bottom:.65rem;">
                @if($passedCount > 0)
                    <div style="width:{{ round($passedCount/$sd->totalEnrolled*100) }}%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;color:#fff;min-width:20px;transition:width .4s;" title="Passed/Completed"></div>
                @endif
                @if($failedCount > 0)
                    <div style="width:{{ round($failedCount/$sd->totalEnrolled*100) }}%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;color:#fff;min-width:16px;transition:width .4s;" title="Failed"></div>
                @endif
                @if($incompleteCount > 0)
                    <div style="width:{{ round($incompleteCount/$sd->totalEnrolled*100) }}%;background:var(--amber);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;color:#fff;min-width:16px;transition:width .4s;" title="Incomplete"></div>
                @endif
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:var(--green);border-radius:50%;display:inline-block;"></span>Passed/Completed ({{ $passedCount }})</span>
                <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:var(--red);border-radius:50%;display:inline-block;"></span>Failed ({{ $failedCount }})</span>
                <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:var(--amber);border-radius:50%;display:inline-block;"></span>In Progress ({{ $incompleteCount }})</span>
                <span style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:var(--grey-mid);border-radius:50%;display:inline-block;"></span>Not Started ({{ $notStarted }})</span>
            </div>
            @endif
        </div>

        {{-- Per-member table --}}
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Callsign</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Progress / Location</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Members with SCORM data --}}
                    @foreach($sd->memberData as $userId => $data)
                    @php
                        $u         = $sd->users->get($userId);
                        $status    = strtolower($data['status']);
                        $isPassed  = in_array($status, ['passed','completed','complete']);
                        $isFailed  = $status === 'failed';
                        $isInProg  = $status === 'incomplete';
                    @endphp
                    <tr>
                        <td style="font-weight:bold;color:var(--text);">{{ ($_isTempAdmin && isset($u) && !($u->isTemporaryGuest() || $u->isTemporaryAdmin())) ? '●●●●●●●●●' : ($u?->name ?? 'User #'.$userId) }}</td>
                        <td style="font-family:monospace;font-size:11px;">{{ $u?->callsign ?? '—' }}</td>
                        <td>
                            @if($isPassed)
                                <span class="badge badge-green">✓ {{ ucfirst($data['status']) }}</span>
                            @elseif($isFailed)
                                <span class="badge badge-red">✕ Failed</span>
                            @elseif($isInProg)
                                <span class="badge badge-amber">⏳ In Progress</span>
                            @else
                                <span class="badge badge-grey">{{ ucfirst($data['status'] ?? 'Not attempted') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($data['score'] !== null)
                                <span style="font-weight:bold;color:{{ $isPassed ? 'var(--green)' : ($isFailed ? 'var(--red)' : 'var(--navy)') }};">
                                    {{ $data['score'] }}%
                                </span>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:var(--muted);">
                            {{ $data['location'] ?? '—' }}
                        </td>
                    </tr>
                    @endforeach

                    {{-- Members with no SCORM data yet --}}
                    @foreach($enrollments as $e)
                    @if(!$sd->memberData->has($e->user_id))
                    <tr>
                        <td style="font-weight:bold;color:var(--text);">{{ ($_isTempAdmin && isset($e->user) && !($e->user->isTemporaryGuest() || $e->user->isTemporaryAdmin())) ? '●●●●●●●●●' : $e->user->name }}</td>
                        <td style="font-family:monospace;font-size:11px;">{{ $e->user->callsign ?? '—' }}</td>
                        <td><span class="badge badge-grey">Not Started</span></td>
                        <td><span style="color:var(--muted);">—</span></td>
                        <td><span style="color:var(--muted);">—</span></td>
                    </tr>
                    @endif
                    @endforeach

                    @if($sd->memberData->isEmpty() && $enrollments->isEmpty())
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--muted);">No data recorded yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif

    {{-- ── QUIZ ANALYTICS ── --}}
    @if($quizzes->isNotEmpty())
    <div class="sec-head">
        <div class="sec-title">Quiz Performance</div>
    </div>

    @foreach($quizzes as $qData)
    <div class="card quiz-card">
        <div class="card-head">
            <div>
                <div class="card-head-title">❓ {{ $qData->quiz->title }}</div>
                <div class="card-head-meta">Lesson: {{ $qData->lesson->title }} · Pass mark: {{ $qData->quiz->pass_mark }}%</div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                @if($qData->avgScore !== null)
                    <span class="badge badge-navy">Avg score: {{ $qData->avgScore }}%</span>
                @endif
                @if($qData->avgAttempts !== null)
                    <span class="badge badge-teal">Avg attempts: {{ $qData->avgAttempts }}</span>
                @endif
                <span class="badge {{ $qData->passRate >= 70 ? 'badge-green' : ($qData->passRate >= 40 ? 'badge-amber' : 'badge-red') }}">
                    {{ $qData->passRate }}% pass rate
                </span>
            </div>
        </div>

        <div class="quiz-stat-row">
            <div class="quiz-stat">
                <span class="quiz-stat-label">Attempted</span>
                <span class="quiz-stat-val">{{ $qData->totalMembers }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Passed</span>
                <span class="quiz-stat-val" style="color:var(--green);">{{ $qData->passCount }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Failed</span>
                <span class="quiz-stat-val" style="color:var(--red);">{{ $qData->totalMembers - $qData->passCount }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Total Submissions</span>
                <span class="quiz-stat-val">{{ $qData->submissions->count() }}</span>
            </div>
            <div class="quiz-stat">
                <span class="quiz-stat-label">Questions</span>
                <span class="quiz-stat-val">{{ $qData->quiz->questions->count() }}</span>
            </div>
        </div>

        {{-- Per-question stats --}}
        @if($qData->submissions->count() > 0 && $qData->quiz->questions->count() > 0)
        <div style="padding:.75rem 1.1rem;border-bottom:1px solid var(--grey-mid);">
            <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);margin-bottom:.65rem;">Question Difficulty (% answered correctly)</div>
            @foreach($qData->quiz->questions->sortBy('sort_order') as $qi => $question)
            @php
                $correctAnswerIds = $question->answers->where('is_correct', true)->pluck('id')->map(fn($id)=>(int)$id)->sort()->values()->toArray();
                $totalAttempts    = $qData->submissions->count();
                $correctCount     = 0;
                foreach($qData->submissions as $sub) {
                    $submitted = $sub->answers[$question->id] ?? null;
                    if($submitted === null) continue;
                    $submittedArr = array_map('intval', is_array($submitted) ? $submitted : [$submitted]);
                    sort($submittedArr);
                    if($submittedArr === $correctAnswerIds) $correctCount++;
                }
                $correctPct = $totalAttempts > 0 ? round($correctCount / $totalAttempts * 100) : 0;
            @endphp
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                <div style="font-size:11px;color:var(--text-mid);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    Q{{ $qi+1 }}: {{ Str::limit($question->question, 60) }}
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                    <div class="prog-bar" style="width:120px;">
                        <div class="prog-fill {{ $correctPct >= 70 ? 'complete' : ($correctPct >= 40 ? '' : 'fail') }}"
                             style="width:{{ $correctPct }}%;{{ $correctPct < 40 ? 'background:var(--red);' : ($correctPct >= 70 ? '' : 'background:var(--amber);') }}"></div>
                    </div>
                    <span style="font-size:11px;font-weight:bold;color:{{ $correctPct >= 70 ? 'var(--green)' : ($correctPct >= 40 ? 'var(--amber)' : 'var(--red)') }};min-width:35px;text-align:right;">
                        {{ $correctPct }}%
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Per-member submissions --}}
        @if($qData->submissions->count() > 0)
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Attempts</th>
                        <th>Best Score</th>
                        <th>Last Score</th>
                        <th>Status</th>
                        <th>Last Attempt</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qData->submissions->groupBy('user_id') as $userId => $userSubs)
                    @php
                        $firstSub  = $userSubs->first();
                        $memberUser = $firstSub->user;
                        $bestScore = $userSubs->max('score');
                        $lastScore = $userSubs->sortByDesc('created_at')->first()->score;
                        $hasPassed = $userSubs->where('passed', true)->count() > 0;
                        $attempts  = $userSubs->count();
                        $rowId     = 'quiz-' . $qData->quiz->id . '-user-' . $userId;
                    @endphp
                    <tr class="member-accordion-row" onclick="toggleAccordion('{{ $rowId }}')">
                        <td style="font-weight:bold;color:var(--text);">
                            {{ ($_isTempAdmin && isset($memberUser) && !($memberUser->isTemporaryGuest() || $memberUser->isTemporaryAdmin())) ? '●●●●●●●●●' : ($memberUser?->name ?? 'Unknown') }}
                            @if($memberUser?->callsign && (!$_isTempAdmin || ($memberUser->isTemporaryGuest() || $memberUser->isTemporaryAdmin())))
                                <span style="font-size:10px;color:var(--muted);font-family:monospace;margin-left:.35rem;">{{ $memberUser->callsign }}</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:bold;color:var(--navy);">{{ $attempts }}</span>
                            <span style="font-size:11px;color:var(--muted);">/{{ $qData->quiz->attempts_allowed }}</span>
                        </td>
                        <td>
                            <span style="font-weight:bold;color:{{ $bestScore >= $qData->quiz->pass_mark ? 'var(--green)' : 'var(--red)' }};">
                                {{ $bestScore }}%
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:bold;color:{{ $lastScore >= $qData->quiz->pass_mark ? 'var(--green)' : 'var(--red)' }};">
                                {{ $lastScore }}%
                            </span>
                        </td>
                        <td>
                            @if($hasPassed)
                                <span class="badge badge-green">✓ Passed</span>
                            @else
                                <span class="badge badge-red">✕ Not passed</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:var(--muted);">
                            {{ $userSubs->sortByDesc('created_at')->first()->created_at->format('d M Y H:i') }}
                        </td>
                        <td>
                            <button class="expand-toggle" onclick="event.stopPropagation();toggleAccordion('{{ $rowId }}')">
                                Details ▼
                            </button>
                        </td>
                    </tr>
                    <tr class="member-accordion-body" id="{{ $rowId }}">
                        <td colspan="7" style="padding:0;">
                            <div class="accordion-inner">
                                <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.65rem;">
                                    All Attempts — {{ ($_isTempAdmin && isset($memberUser) && !($memberUser->isTemporaryGuest() || $memberUser->isTemporaryAdmin())) ? '●●●●●●●●●' : $memberUser?->name }}
                                </div>
                                @foreach($userSubs->sortBy('attempt_number') as $sub)
                                <div class="attempt-block">
                                    <div class="attempt-head">
                                        <span class="attempt-num">Attempt {{ $sub->attempt_number }}</span>
                                        <span class="attempt-score {{ $sub->passed ? 'pass' : 'fail' }}">
                                            {{ $sub->score }}%
                                        </span>
                                        <span class="badge {{ $sub->passed ? 'badge-green' : 'badge-red' }}">
                                            {{ $sub->passed ? '✓ Pass' : '✕ Fail' }}
                                        </span>
                                        <span style="font-size:11px;color:var(--muted);margin-left:auto;">
                                            {{ $sub->created_at->format('d M Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="attempt-body">
                                        @foreach($qData->quiz->questions->sortBy('sort_order') as $question)
                                        @php
                                            $submitted = $sub->answers[$question->id] ?? null;
                                            $submittedArr = $submitted !== null
                                                ? array_map('intval', is_array($submitted) ? $submitted : [$submitted])
                                                : [];
                                            sort($submittedArr);
                                            $correctIds = $question->answers->where('is_correct',true)->pluck('id')->map(fn($id)=>(int)$id)->sort()->values()->toArray();
                                            $isCorrect  = $submittedArr === $correctIds;
                                            $submittedTexts = $question->answers->whereIn('id', $submittedArr)->pluck('answer_text')->toArray();
                                            $correctTexts   = $question->answers->where('is_correct', true)->pluck('answer_text')->toArray();
                                        @endphp
                                        <div class="question-result">
                                            <div class="q-result-icon">{{ $isCorrect ? '✅' : '❌' }}</div>
                                            <div class="q-result-text">
                                                <div class="q-result-question">{{ $question->question }}</div>
                                                @if($submitted !== null)
                                                    <div class="q-result-answer {{ $isCorrect ? 'correct' : 'wrong' }}">
                                                        {{ $isCorrect ? 'Correct: ' : 'Answered: ' }}
                                                        {{ implode(', ', $submittedTexts) ?: 'No answer recorded' }}
                                                    </div>
                                                    @if(!$isCorrect)
                                                        <div class="q-correct-was">
                                                            ✓ Correct answer: {{ implode(', ', $correctTexts) }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="q-result-answer" style="color:var(--muted);">Not answered</div>
                                                @endif
                                            </div>
                                            <div style="font-size:10px;color:var(--muted);flex-shrink:0;">
                                                {{ $question->points }} {{ $question->points==1?'pt':'pts' }}
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div style="padding:2rem;text-align:center;font-size:13px;color:var(--muted);">No quiz attempts yet.</div>
        @endif
    </div>
    @endforeach
    @endif

    {{-- ── MEMBER PROGRESS TABLE ── --}}
    <div class="sec-head" style="margin-top:.5rem;">
        <div class="sec-title">Member Progress</div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-head-title">All Enrolled Members</div>
            <div class="card-head-meta">{{ $enrollments->count() }} members · {{ $totalLessons }} lessons</div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Callsign</th>
                        <th>Enrolled</th>
                        <th>Progress</th>
                        <th>Lessons Done</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $e)
                    @php
                        $memberProgress = $allProgress[$e->user_id] ?? collect();
                        $doneLessons    = $memberProgress->whereNotNull('completed_at')->count();
                        $allLessons     = $course->modules->flatMap(fn($m) => $m->lessons);
                        $rowId          = 'member-' . $e->user_id;
                    @endphp
                    <tr class="member-accordion-row" onclick="toggleAccordion('{{ $rowId }}')">
                        <td style="font-weight:bold;color:var(--text);">{{ ($_isTempAdmin && isset($e->user) && !($e->user->isTemporaryGuest() || $e->user->isTemporaryAdmin())) ? '●●●●●●●●●' : $e->user->name }}</td>
                        <td style="font-family:monospace;font-size:11px;">{{ $e->user->callsign ?? '—' }}</td>
                        <td style="font-size:11px;color:var(--muted);">{{ $e->enrolled_at->format('d M Y') }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <div class="prog-bar">
                                    <div class="prog-fill {{ $e->progress_pct==100?'complete':'' }}" style="width:{{ $e->progress_pct }}%;"></div>
                                </div>
                                <span style="font-size:11px;font-weight:bold;color:var(--navy);min-width:30px;">{{ $e->progress_pct }}%</span>
                            </div>
                        </td>
                        <td style="font-size:12px;">
                            <span style="font-weight:bold;color:var(--navy);">{{ $doneLessons }}</span>
                            <span style="color:var(--muted);">/{{ $totalLessons }}</span>
                        </td>
                        <td>
                            @if($e->completed_at)
                                <span class="badge badge-green">✓ Complete</span>
                            @elseif($e->progress_pct > 0)
                                <span class="badge badge-amber">In Progress</span>
                            @else
                                <span class="badge badge-grey">Not Started</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:{{ $e->due_date && $e->due_date->isPast() && !$e->completed_at ? 'var(--red)' : 'var(--muted)' }};">
                            {{ $e->due_date ? $e->due_date->format('d M Y') : '—' }}
                        </td>
                        <td>
                            <button class="expand-toggle" onclick="event.stopPropagation();toggleAccordion('{{ $rowId }}')">
                                Lessons ▼
                            </button>
                        </td>
                    </tr>
                    <tr class="member-accordion-body" id="{{ $rowId }}">
                        <td colspan="8" style="padding:0;">
                            <div class="accordion-inner">
                                <div style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.65rem;">
                                    Lesson Completion — {{ ($_isTempAdmin && isset($e->user) && !($e->user->isTemporaryGuest() || $e->user->isTemporaryAdmin())) ? '●●●●●●●●●' : $e->user->name }}
                                </div>
                                @foreach($course->modules->sortBy('sort_order') as $mod)
                                <div style="font-size:11px;font-weight:bold;color:var(--navy);margin-bottom:.35rem;margin-top:.5rem;">
                                    📁 {{ $mod->title }}
                                </div>
                                <div class="lesson-prog-grid" style="padding:0;margin-bottom:.5rem;">
                                    @foreach($mod->lessons->sortBy('sort_order') as $lesson)
                                    @php
                                        $lessonProg = $memberProgress->firstWhere('lesson_id', $lesson->id);
                                        $isDone     = $lessonProg && $lessonProg->completed_at;
                                        $icons      = ['text'=>'📄','video'=>'🎬','scorm'=>'📦','quiz'=>'❓'];
                                    @endphp
                                    <div class="lesson-prog-item {{ $isDone ? 'done' : 'not-done' }}">
                                        <span class="lpi-icon">{{ $isDone ? '✓' : '○' }}</span>
                                        <span class="lpi-name {{ $isDone ? 'done' : '' }}">
                                            {{ $icons[$lesson->type] ?? '📄' }} {{ $lesson->title }}
                                        </span>
                                        @if($lesson->type === 'quiz' && $lessonProg && $lessonProg->quiz_score !== null)
                                            <span style="font-size:10px;font-weight:bold;color:{{ $lessonProg->quiz_score >= ($lesson->quiz?->pass_mark ?? 80) ? 'var(--green)' : 'var(--red)' }};">
                                                {{ $lessonProg->quiz_score }}%
                                            </span>
                                        @endif
                                        @if($isDone && $lessonProg->completed_at)
                                            <span style="font-size:9px;color:var(--muted);" title="{{ $lessonProg->completed_at->format('d M Y H:i') }}">
                                                {{ $lessonProg->completed_at->format('d M') }}
                                            </span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--muted);">No members enrolled yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function toggleAccordion(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const isOpen = row.classList.contains('open');
    row.classList.toggle('open', !isOpen);
    const btn = row.previousElementSibling?.querySelector('.expand-toggle');
    if (btn) btn.textContent = isOpen ? 'Details ▼' : 'Details ▲';
}
</script>
@include('admin.lms.reset-panel')
@endsection