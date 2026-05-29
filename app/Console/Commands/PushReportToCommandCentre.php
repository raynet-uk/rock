<?php
namespace App\Console\Commands;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseProgress;
use App\Models\Setting;
use App\Models\User;
use App\Models\Event;
use App\Models\AlertStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PushReportToCommandCentre extends Command
{
    protected $signature   = 'raynet:push-report';
    protected $description = 'Push everything to RAYNET Command Centre';

    public function handle(): int
    {
        $licenceKey = Setting::get('cms_licence_key', '');
        $endpoint   = 'https://command.nathandillon.co.uk/api/reporting/push';
        if (!$licenceKey) { $this->warn('No licence key.'); return 0; }

        $this->info('Gathering data...');

        // ── Members ───────────────────────────────────────────────────────
        $allUsers = User::orderBy('name')->get();
        $memberList = $allUsers->map(fn($u) => [
            'name'                      => $u->name,
            'callsign'                  => $u->callsign ?? null,
            'email'                     => $u->email,
            'is_admin'                  => (bool)($u->getAttributes()['is_admin'] ?? false),
            'is_super_admin'            => (bool)($u->getAttributes()['is_super_admin'] ?? false),
            'registration_pending'      => (bool)($u->registration_pending ?? false),
            'email_verified'            => !is_null($u->email_verified_at),
            'attended_event_this_year'  => (bool)($u->attended_event_this_year ?? false),
            'volunteer_hours_this_year' => round((float)($u->volunteering_hours_this_year ?? 0), 1),
            'licence_class'             => $u->licence_class ?? null,
            'dmr_id'                    => $u->dmr_id ?? null,
            'joined_at'                 => $u->created_at?->format('Y-m-d'),
            'last_seen'                 => $u->updated_at?->format('Y-m-d'),
        ])->toArray();

        // ── Events ────────────────────────────────────────────────────────
        $events = Event::with(['assignments.user', 'type'])
            ->orderByDesc('starts_at')
            ->take(100)
            ->get()
            ->map(fn($e) => [
                'id'            => $e->id,
                'title'         => $e->title,
                'type'          => $e->type?->name ?? 'Event',
                'type_colour'   => $e->type?->colour ?? '#003366',
                'description'   => $e->description,
                'location'      => $e->location,
                'starts_at'     => $e->starts_at?->toIso8601String(),
                'ends_at'       => $e->ends_at?->toIso8601String(),
                'is_past'       => $e->starts_at?->isPast() ?? false,
                'is_private'    => (bool)($e->is_private ?? false),
                'slug'          => $e->slug ?? null,
                'lat'           => $e->lat ?? null,
                'lng'           => $e->lng ?? null,
                'crew_count'    => $e->assignments?->count() ?? 0,
                'team'          => $e->assignments?->map(fn($a) => [
                    'name'      => $a->user?->name,
                    'callsign'  => $a->user?->callsign,
                    'role'      => $a->role,
                    'status'    => $a->status,
                    'location'  => $a->location_name,
                    'lat'       => $a->lat,
                    'lng'       => $a->lng,
                    'frequency' => $a->frequency,
                    'mode'      => $a->mode,
                ])->toArray() ?? [],
            ])->toArray();

        // ── Basic training stats ──────────────────────────────────────────
        $trainingCompletions = 0;
        $courseStats = [];
        try {
            $trainingCompletions = DB::table('lms_enrollments')
    ->whereNotNull('completed_at')
    ->whereYear('completed_at', now()->year)
    ->count();
            $courseStats = DB::table('lms_courses as c')
                ->leftJoin('lms_progress as p', 'c.id', '=', 'p.course_id')
                ->select('c.title', DB::raw('COUNT(DISTINCT p.user_id) as enrolled'), DB::raw('SUM(p.completed) as completed'))
                ->groupBy('c.id', 'c.title')
                ->get()->toArray();
        } catch (\Throwable $e) {}

        // ── Full LMS analytics ────────────────────────────────────────────
        $lmsAnalytics = ['courses' => []];
        try {
            $courses = Course::with([
                'modules.lessons.quiz.questions.answers',
                'enrollments.user',
            ])->get();

            foreach ($courses as $course) {
                $enrollments = CourseEnrollment::where('course_id', $course->id)
                    ->with('user')
                    ->get()
                    ->map(function ($e) use ($course) {
                        $e->progress_pct = $course->getProgressFor($e->user_id);
                        return $e;
                    });

                $totalLessons = $course->lessons()->count();

                // Per-member lesson progress
                $allProgress = CourseProgress::where('course_id', $course->id)
                    ->with('lesson')
                    ->get()
                    ->groupBy('user_id');

                // ── Enrollments ───────────────────────────────────────────
                $enrollmentData = $enrollments->map(function ($e) use ($allProgress, $totalLessons) {
                    $memberProgress = $allProgress[$e->user_id] ?? collect();
                    $doneLessons    = $memberProgress->whereNotNull('completed_at')->count();
                    $status = $e->completed_at
                        ? 'completed'
                        : ($e->progress_pct > 0 ? 'in_progress' : 'not_started');
                    return [
                        'user_name'    => $e->user?->name,
                        'callsign'     => $e->user?->callsign,
                        'enrolled_at'  => $e->enrolled_at?->format('Y-m-d'),
                        'progress_pct' => $e->progress_pct,
                        'lessons_done' => $doneLessons,
                        'total_lessons'=> $totalLessons,
                        'status'       => $status,
                        'completed_at' => $e->completed_at?->format('Y-m-d'),
                        'due_date'     => $e->due_date?->format('Y-m-d'),
                        'overdue'      => $e->due_date && $e->due_date->isPast() && !$e->completed_at,
                    ];
                })->values()->toArray();

                // ── Quizzes ───────────────────────────────────────────────
                $allSubmissions = \App\Models\QuizSubmission::where('course_id', $course->id)
                    ->with('user')
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('user_id');

                $quizData = $course->modules->flatMap(fn($m) => $m->lessons)
                    ->filter(fn($l) => $l->type === 'quiz' && $l->quiz)
                    ->map(function ($lesson) use ($course, $allSubmissions) {
                        $quiz = $lesson->quiz->load('questions.answers');

                        $submissions = \App\Models\QuizSubmission::where('quiz_id', $quiz->id)
                            ->with('user')
                            ->orderBy('created_at')
                            ->get();

                        $passCount    = $submissions->where('passed', true)->unique('user_id')->count();
                        $totalMembers = $submissions->unique('user_id')->count();
                        $avgScore     = $submissions->count() > 0 ? round($submissions->avg('score')) : null;
                        $avgAttempts  = $totalMembers > 0
                            ? round($submissions->groupBy('user_id')->map->count()->avg(), 1)
                            : null;

                        // Per-member
                        $memberQuizData = $submissions->groupBy('user_id')->map(function ($userSubs) use ($quiz) {
                            $firstSub = $userSubs->first();
                            $bestScore = $userSubs->max('score');
                            $lastSub   = $userSubs->sortByDesc('created_at')->first();

                            // Per-attempt with per-question breakdown
                            $attemptDetails = $userSubs->sortBy('attempt_number')->map(function ($sub) use ($quiz) {
                                $questionDetails = $quiz->questions->sortBy('sort_order')->map(function ($question) use ($sub) {
                                    $submitted = $sub->answers[$question->id] ?? null;
                                    $submittedArr = $submitted !== null
                                        ? array_map('intval', is_array($submitted) ? $submitted : [$submitted])
                                        : [];
                                    sort($submittedArr);
                                    $correctIds   = $question->answers->where('is_correct', true)->pluck('id')->map(fn($id) => (int)$id)->sort()->values()->toArray();
                                    $isCorrect    = $submittedArr === $correctIds;
                                    $submittedTexts = $question->answers->whereIn('id', $submittedArr)->pluck('answer_text')->toArray();
                                    $correctTexts   = $question->answers->where('is_correct', true)->pluck('answer_text')->toArray();

                                    return [
                                        'question'       => $question->question,
                                        'submitted'      => implode(', ', $submittedTexts) ?: null,
                                        'correct'        => $isCorrect,
                                        'correct_answer' => implode(', ', $correctTexts),
                                        'points'         => $question->points ?? 1,
                                    ];
                                })->values()->toArray();

                                return [
                                    'attempt_number' => $sub->attempt_number,
                                    'score'          => $sub->score,
                                    'passed'         => (bool)$sub->passed,
                                    'created_at'     => $sub->created_at?->toIso8601String(),
                                    'questions'      => $questionDetails,
                                ];
                            })->values()->toArray();

                            return [
                                'name'            => $firstSub->user?->name,
                                'callsign'        => $firstSub->user?->callsign,
                                'attempts'        => $userSubs->count(),
                                'best_score'      => $bestScore,
                                'last_score'      => $lastSub->score,
                                'passed'          => $userSubs->where('passed', true)->count() > 0,
                                'last_attempt_at' => $lastSub->created_at?->toIso8601String(),
                                'attempt_details' => $attemptDetails,
                            ];
                        })->values()->toArray();

                        return [
                            'title'             => $quiz->title,
                            'lesson_title'      => $lesson->title,
                            'pass_mark'         => $quiz->pass_mark,
                            'attempts_allowed'  => $quiz->attempts_allowed,
                            'total_members'     => $totalMembers,
                            'pass_count'        => $passCount,
                            'pass_rate'         => $totalMembers > 0 ? round($passCount / $totalMembers * 100) : 0,
                            'avg_score'         => $avgScore,
                            'avg_attempts'      => $avgAttempts,
                            'submissions_count' => $submissions->count(),
                            'questions_count'   => $quiz->questions->count(),
                            'members'           => $memberQuizData,
                        ];
                    })->values()->toArray();

                // ── SCORM ─────────────────────────────────────────────────
                $scormData = [];
                $enrolledUserIds = $enrollments->pluck('user_id')->toArray();
                $allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
                $scormLessons = $allLessons->where('type', 'scorm');

                if ($scormLessons->isNotEmpty() && !empty($enrolledUserIds)) {
                    foreach ($scormLessons as $lesson) {
                        $scormRows = \App\Models\LmsScormData::where('lesson_id', $lesson->id)
                            ->whereIn('user_id', $enrolledUserIds)
                            ->get();

                        $byUser   = $scormRows->groupBy('user_id');
                        $users    = \App\Models\User::whereIn('id', $byUser->keys())->get()->keyBy('id');

                        $memberScorm = $byUser->map(function ($rows, $userId) use ($users) {
                            $map    = $rows->pluck('value', 'key');
                            $status = $map->get('cmi.core.lesson_status', 'not attempted');
                            $score  = $map->get('cmi.core.score.raw', null);
                            if ($status === 'not attempted') $status = $map->get('cmi.completion_status', 'not attempted');
                            if ($score === null) $score = $map->get('cmi.score.raw', null);
                            $u = $users->get($userId);
                            return [
                                'name'     => $u?->name ?? 'User #' . $userId,
                                'callsign' => $u?->callsign,
                                'status'   => $status,
                                'score'    => $score !== null ? (int)round((float)$score) : null,
                                'location' => $map->get('cmi.core.lesson_location', null),
                            ];
                        })->values()->toArray();

                        $completedCount = collect($memberScorm)->filter(
                            fn($d) => in_array(strtolower($d['status']), ['passed', 'completed', 'complete'], true)
                        )->count();

                        $scores   = collect($memberScorm)->pluck('score')->filter(fn($s) => $s !== null);
                        $avgScore = $scores->count() > 0 ? round($scores->avg()) : null;

                        // Members not yet started
                        $startedIds = $byUser->keys()->toArray();
                        foreach ($enrolledUserIds as $uid) {
                            if (!in_array($uid, $startedIds)) {
                                $u = $allUsers->firstWhere('id', $uid);
                                $memberScorm[] = [
                                    'name'     => $u?->name ?? 'User #' . $uid,
                                    'callsign' => $u?->callsign,
                                    'status'   => 'not attempted',
                                    'score'    => null,
                                    'location' => null,
                                ];
                            }
                        }

                        $scormData[] = [
                            'title'          => $lesson->title,
                            'total_enrolled' => count($enrolledUserIds),
                            'completed_count'=> $completedCount,
                            'pass_rate'      => count($enrolledUserIds) > 0
                                ? round($completedCount / count($enrolledUserIds) * 100)
                                : 0,
                            'avg_score'      => $avgScore,
                            'members'        => $memberScorm,
                        ];
                    }
                }

                $completedCount = $enrollments->whereNotNull('completed_at')->count();
                $lmsAnalytics['courses'][] = [
                    'id'              => $course->id,
                    'title'           => $course->title,
                    'category'        => $course->category,
                    'difficulty'      => $course->difficulty,
                    'estimated_hours' => $course->estimated_hours,
                    'is_published'    => (bool)$course->is_published,
                    'enrolled'        => $enrollments->count(),
                    'completed'       => $completedCount,
                    'completion_rate' => $enrollments->count() > 0
                        ? round($completedCount / $enrollments->count() * 100)
                        : 0,
                    'total_lessons'   => $totalLessons,
                    'quiz_count'      => count($quizData),
                    'enrollments'     => $enrollmentData,
                    'quizzes'         => $quizData,
                    'scorm_lessons'   => $scormData,
                ];
            }

            $this->info('LMS: ' . count($lmsAnalytics['courses']) . ' course(s) packaged.');
        } catch (\Throwable $e) {
            $this->warn('LMS analytics failed: ' . $e->getMessage());
        }

        // ── Alert status ──────────────────────────────────────────────────
        $alert = null;
        try { $alert = AlertStatus::first(); } catch (\Throwable $e) {}

        // ── Activity logs ─────────────────────────────────────────────────
        $recentActivity = [];
        try {
            $recentActivity = DB::table('activity_logs')
                ->orderByDesc('activity_date')
                ->take(50)
                ->get()
                ->map(fn($l) => [
                    'date'  => $l->activity_date,
                    'type'  => $l->activity_type ?? 'activity',
                    'hours' => $l->hours ?? 0,
                    'notes' => $l->notes ?? null,
                ])->toArray();
        } catch (\Throwable $e) {}

        // ── Settings ──────────────────────────────────────────────────────
        $groupInfo = [
            'name'     => Setting::get('group_name', ''),
            'number'   => Setting::get('group_number', ''),
            'callsign' => Setting::get('group_callsign', ''),
            'region'   => Setting::get('group_region', ''),
            'zone'     => Setting::get('raynet_zone', ''),
            'gc_name'  => Setting::get('gc_name', ''),
            'gc_email' => Setting::get('gc_email', ''),
            'site_url' => Setting::get('site_url', config('app.url')),
        ];

        // ── Build full payload ────────────────────────────────────────────
        $payload = [
            'cms_version'  => trim(@file_get_contents(base_path('VERSION')) ?: '1.0.0'),
            'site_url'     => config('app.url'),
            'group_info'   => $groupInfo,
            'members' => [
                'total'                     => $allUsers->count(),
                'active'                    => $allUsers->whereNotNull('email_verified_at')->where('registration_pending', 0)->count(),
                'pending'                   => $allUsers->where('registration_pending', 1)->count(),
                'attended_event_this_year'  => $allUsers->where('attended_event_this_year', 1)->count(),
                'volunteer_hours_this_year' => round((float)$allUsers->sum('volunteering_hours_this_year'), 1),
                'list'                      => $memberList,
            ],
            'events' => [
                'total_this_year' => Event::whereYear('starts_at', now()->year)->count(),
                'upcoming'        => Event::where('starts_at', '>', now())->count(),
                'past_this_year'  => Event::whereYear('starts_at', now()->year)->where('starts_at', '<', now())->count(),
                'list'            => $events,
            ],
            'training' => [
                'completions_this_year' => $trainingCompletions,
                'courses'               => $courseStats,
            ],
            'lms_analytics'   => $lmsAnalytics,
            'alert_status'    => $alert?->status ?? 'normal',
            'alert_updated'   => $alert ? (string)$alert->updated_at : null,
            'activity_logs'   => $recentActivity,
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders(['X-CMS-Licence' => $licenceKey])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $this->info('✓ Full report pushed successfully');
                $this->line('  Members:  ' . count($memberList));
                $this->line('  Events:   ' . count($events));
                $this->line('  Courses:  ' . count($lmsAnalytics['courses']));
                return 0;
            }
            $this->warn('Error ' . $response->status() . ': ' . $response->json('error', ''));
            return 1;
        } catch (\Throwable $e) {
            $this->warn('Failed: ' . $e->getMessage());
            return 1;
        }
    }
}