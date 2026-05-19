<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use App\Models\CourseQuiz;
use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use App\Models\CourseEnrollment;
use App\Models\CourseProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LmsAdminController extends Controller
{
public function index()
{
    $courses = Course::withCount(['enrollments','lessons'])
        ->with('creator')
        ->orderByDesc('created_at')
        ->get();
    return view('admin.lms.index', compact('courses'));
}

    public function create()
    {
        return view('admin.lms.builder', ['course' => null, 'users' => collect(), 'enrolledIds' => []]);
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $course = Course::create([
            'title'               => $request->title,
            'slug'                => Str::slug($request->title) . '-' . Str::random(4),
            'description'         => $request->description,
            'category'            => $request->category,
            'difficulty'          => $request->difficulty ?? 'beginner',
            'estimated_hours'     => $request->estimated_hours,
            'is_published'        => false,
            'is_drip'             => $request->boolean('is_drip'),
            'drip_interval_days'  => $request->drip_interval_days ?? 7,
            'pass_mark'           => $request->pass_mark ?? 80,
            'certificate_enabled' => $request->boolean('certificate_enabled', true),
            'certificate_text'    => $request->certificate_text,
            'created_by'          => auth()->id(),
        ]);
        return redirect()->route('admin.lms.edit', $course->id)
            ->with('success', 'Course created. Build your content below.');
    }

    public function edit($id)
    {
        $course = Course::with(['modules.lessons.quiz.questions.answers'])->findOrFail($id);
        $users = User::where('registration_pending', false)->orderBy('name')->get(['id','name','email','callsign']);
        $enrolledIds = CourseEnrollment::where('course_id', $id)->pluck('user_id')->toArray();
        return view('admin.lms.builder', compact('course', 'users', 'enrolledIds'));
    }

public function update(Request $request, $id)
{
    $course = Course::findOrFail($id);
    $request->validate(['title' => 'required|string|max:255']);
    $course->update([
        'title'               => $request->title,
        'description'         => $request->description,
        'category'            => $request->category,
        'difficulty'          => $request->difficulty ?? 'beginner',
        'estimated_hours'     => $request->estimated_hours,
        'is_published'        => $request->boolean('is_published'),
        'is_drip'             => $request->boolean('is_drip'),
        'drip_interval_days'  => $request->drip_interval_days ?? 7,
        'pass_mark'           => $request->pass_mark ?? 80,
        'certificate_enabled' => $request->boolean('certificate_enabled'),
        'certificate_text'    => $request->certificate_text,
        'unlocks_badge_ids'   => array_map('intval', array_filter($request->input('unlocks_badge_ids', []))),
    ]);
    return response()->json(['success' => true, 'message' => 'Settings saved.']);
}

    public function togglePublish($id)
    {
        $course = Course::findOrFail($id);
        $course->is_published = !$course->is_published;
        $course->save();
        return redirect()->back()->with('success', 'Course ' . ($course->is_published ? 'published' : 'unpublished') . '.');
    }

    public function destroy($id)
    {
        Course::findOrFail($id)->delete();
        return redirect()->route('admin.lms.index')->with('success', 'Course deleted.');
    }

    // ── MODULES ──────────────────────────────────────────────────────────────

    public function storeModule(Request $request)
    {
        $request->validate(['course_id' => 'required', 'title' => 'required|string|max:255']);
        $max = CourseModule::where('course_id', $request->course_id)->max('sort_order') ?? -1;
        $module = CourseModule::create([
            'course_id'   => $request->course_id,
            'title'       => $request->title,
            'description' => $request->description,
            'sort_order'  => $max + 1,
        ]);
        return response()->json(['success' => true, 'module' => $module]);
    }

    public function updateModule(Request $request, $id)
    {
        CourseModule::findOrFail($id)->update($request->only(['title','description']));
        return response()->json(['success' => true]);
    }

    public function destroyModule($id)
    {
        CourseModule::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function reorderModules(Request $request)
    {
        foreach ($request->order as $i => $id) {
            CourseModule::where('id', $id)->update(['sort_order' => $i]);
        }
        return response()->json(['success' => true]);
    }

    // ── LESSONS ───────────────────────────────────────────────────────────────

    public function storeLesson(Request $request)
    {
        $request->validate([
            'module_id' => 'required', 'course_id' => 'required',
            'title'     => 'required|string|max:255',
            'type'      => 'required|in:text,video,scorm,quiz,audio,document,presentation,external,checklist',
        ]);
        $max = CourseLesson::where('module_id', $request->module_id)->max('sort_order') ?? -1;
        $lesson = CourseLesson::create([
            'module_id'        => $request->module_id,
            'course_id'        => $request->course_id,
            'title'            => $request->title,
            'type'             => $request->type,
            'content'          => $request->content,
            'video_url'        => $request->video_url,
            'duration_minutes' => $request->duration_minutes,
            'sort_order'       => $max + 1,
            'is_free_preview'  => $request->boolean('is_free_preview'),
            'drip_days'        => $request->drip_days ?? 0,
        ]);
        if ($lesson->type === 'quiz') {
            CourseQuiz::create([
                'lesson_id'        => $lesson->id,
                'course_id'        => $request->course_id,
                'title'            => $lesson->title . ' Quiz',
                'pass_mark'        => 80,
                'attempts_allowed' => 3,
            ]);
        }
        return response()->json(['success' => true, 'lesson' => $lesson->load('quiz')]);
    }

    public function updateLesson(Request $request, $id)
    {
        $lesson = CourseLesson::findOrFail($id);
        $lesson->update([
            'title'            => $request->title,
            'type'             => $request->type,
            'content'          => $request->content,
            'video_url'        => $request->video_url,
            'duration_minutes' => $request->duration_minutes,
            'is_free_preview'  => $request->boolean('is_free_preview'),
            'drip_days'        => $request->drip_days ?? 0,
        ]);
        return response()->json(['success' => true]);
    }

    public function destroyLesson($id)
    {
        CourseLesson::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function reorderLessons(Request $request)
    {
        foreach ($request->order as $i => $id) {
            CourseLesson::where('id', $id)->update(['sort_order' => $i]);
        }
        return response()->json(['success' => true]);
    }

    // ── QUIZ ─────────────────────────────────────────────────────────────────

    public function updateQuiz(Request $request, $id)
    {
        $quiz = CourseQuiz::findOrFail($id);
        $quiz->update([
            'title'             => $request->title,
            'pass_mark'         => $request->pass_mark ?? 80,
            'attempts_allowed'  => $request->attempts_allowed ?? 3,
            'time_limit_minutes'=> $request->time_limit_minutes ?: null,
        ]);
        if ($request->has('questions')) {
            $keepIds = collect($request->questions)->pluck('id')->filter()->toArray();
            $quiz->questions()->whereNotIn('id', $keepIds)->delete();
            foreach ($request->questions as $i => $qd) {
                $q = !empty($qd['id']) ? CourseQuestion::find($qd['id']) : new CourseQuestion(['quiz_id' => $quiz->id]);
                $q->fill(['question' => $qd['question'], 'type' => $qd['type'] ?? 'multiple_choice', 'points' => $qd['points'] ?? 1, 'sort_order' => $i])->save();
                if (isset($qd['answers'])) {
                    $keepAids = collect($qd['answers'])->pluck('id')->filter()->toArray();
                    $q->answers()->whereNotIn('id', $keepAids)->delete();
                    foreach ($qd['answers'] as $j => $ad) {
                        $a = !empty($ad['id']) ? CourseAnswer::find($ad['id']) : new CourseAnswer(['question_id' => $q->id]);
                        $a->fill(['answer_text' => $ad['answer_text'], 'is_correct' => !empty($ad['is_correct']), 'sort_order' => $j])->save();
                    }
                }
            }
        }
        return response()->json(['success' => true, 'quiz' => $quiz->load('questions.answers')]);
    }

    // ── ENROLLMENT ────────────────────────────────────────────────────────────

public function enroll(Request $request)
{
    $request->validate(['course_id' => 'required', 'user_ids' => 'required|array']);
    $course = \App\Models\Course::findOrFail($request->course_id);

    foreach ($request->user_ids as $uid) {
        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $uid, 'course_id' => $request->course_id],
            ['assigned_by' => auth()->id(), 'due_date' => $request->due_date ?: null]
        );

        // Send enrollment email only if freshly created
        if ($enrollment->wasRecentlyCreated) {
            $user    = \App\Models\User::find($uid);
            $dueDate = $request->due_date
                ? \Carbon\Carbon::parse($request->due_date)->format('d M Y')
                : null;
            if ($user) {
                try {
                    $user->notify(new \App\Notifications\CourseEnrolledNotification($course, $dueDate));
                } catch (\Throwable $e) {
                    \Log::error('CourseEnrolledNotification failed: ' . $e->getMessage());
                }
            }
        }
    }

    return response()->json(['success' => true, 'message' => count($request->user_ids) . ' member(s) enrolled.']);
}

    public function unenroll($courseId, $userId)
    {
        CourseEnrollment::where('course_id', $courseId)->where('user_id', $userId)->delete();
        return response()->json(['success' => true]);
    }
// ── RESET ─────────────────────────────────────────────────────────────────

    public function resetCourse($courseId, $userId)
    {
        // Delete all progress for this user on this course
        CourseProgress::where('course_id', $courseId)->where('user_id', $userId)->delete();

        // Delete quiz submissions
        \App\Models\QuizSubmission::where('course_id', $courseId)->where('user_id', $userId)->delete();

        // Reset enrollment completion
        CourseEnrollment::where('course_id', $courseId)->where('user_id', $userId)
            ->update(['completed_at' => null]);

        // Remove certificate
        \App\Models\CourseCertificate::where('course_id', $courseId)->where('user_id', $userId)->delete();

        return response()->json(['success' => true, 'message' => 'Course progress reset.']);
    }

    public function resetLesson($courseId, $userId, $lessonId)
    {
        CourseProgress::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->delete();

        // If course was previously completed, un-complete it
        CourseEnrollment::where('course_id', $courseId)->where('user_id', $userId)
            ->update(['completed_at' => null]);

        // Remove certificate too since course is no longer complete
        \App\Models\CourseCertificate::where('course_id', $courseId)->where('user_id', $userId)->delete();

        return response()->json(['success' => true, 'message' => 'Lesson progress reset.']);
    }

    public function resetQuiz($courseId, $userId, $quizId)
    {
        $quiz = CourseQuiz::findOrFail($quizId);

        // Reset progress record for this lesson
        CourseProgress::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->where('lesson_id', $quiz->lesson_id)
            ->update(['completed_at' => null, 'quiz_score' => null, 'attempts' => 0]);

        // Delete all submissions for this quiz by this user
        \App\Models\QuizSubmission::where('quiz_id', $quizId)->where('user_id', $userId)->delete();

        // Un-complete the enrollment/certificate
        CourseEnrollment::where('course_id', $courseId)->where('user_id', $userId)
            ->update(['completed_at' => null]);
        \App\Models\CourseCertificate::where('course_id', $courseId)->where('user_id', $userId)->delete();

        return response()->json(['success' => true, 'message' => 'Quiz reset.']);
    }
    // ── ANALYTICS ─────────────────────────────────────────────────────────────

   public function analytics($id)
{
    $course = Course::with([
        'modules.lessons.quiz.questions.answers',
        'enrollments.user',
    ])->findOrFail($id);

    $enrollments = CourseEnrollment::where('course_id', $id)
        ->with('user')
        ->get()
        ->map(function ($e) use ($course) {
            $e->progress_pct = $course->getProgressFor($e->user_id);
            return $e;
        });

    $completionRate = $enrollments->count() > 0
        ? round($enrollments->whereNotNull('completed_at')->count() / $enrollments->count() * 100)
        : 0;

    $totalLessons = $course->lessons()->count();

    // Per-member lesson progress
    $allProgress = CourseProgress::where('course_id', $id)
        ->with('lesson')
        ->get()
        ->groupBy('user_id');

    // All quiz submissions for this course
    $allSubmissions = \App\Models\QuizSubmission::where('course_id', $id)
        ->with('user')
        ->orderBy('created_at')
        ->get()
        ->groupBy('user_id');

    // Quiz list with stats
    $quizzes = $course->modules->flatMap(fn($m) => $m->lessons)
        ->filter(fn($l) => $l->type === 'quiz' && $l->quiz)
        ->map(function ($lesson) use ($id) {
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

            return (object)[
                'lesson'       => $lesson,
                'quiz'         => $quiz,
                'submissions'  => $submissions,
                'passCount'    => $passCount,
                'totalMembers' => $totalMembers,
                'avgScore'     => $avgScore,
                'avgAttempts'  => $avgAttempts,
                'passRate'     => $totalMembers > 0 ? round($passCount / $totalMembers * 100) : 0,
            ];
        });
// ── SCORM lesson analytics ─────────────────────────────────────────────
$scormLessons = collect();
 
$course->load('modules.lessons');
$allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
$scormLessonModels = $allLessons->where('type', 'scorm');
 
if ($scormLessonModels->isNotEmpty()) {
    $enrolledUserIds = $enrollments->pluck('user_id')->toArray();
 
    foreach ($scormLessonModels as $lesson) {
        // Pull all CMI data for this lesson
        $scormRows = \App\Models\LmsScormData::where('lesson_id', $lesson->id)
            ->whereIn('user_id', $enrolledUserIds)
            ->get();
 
        // Group by user
        $byUser = $scormRows->groupBy('user_id');
 
        $memberData = $byUser->map(function ($rows, $userId) {
            $map    = $rows->pluck('value', 'key');
            $status = $map->get('cmi.core.lesson_status', 'not attempted');
            $score  = $map->get('cmi.core.score.raw', null);
            // SCORM 2004 fallbacks
            if ($status === 'not attempted') {
                $status = $map->get('cmi.completion_status', 'not attempted');
            }
            if ($score === null) {
                $score = $map->get('cmi.score.raw', null);
            }
            return [
                'user_id'  => $userId,
                'status'   => $status,
                'score'    => $score !== null ? (int) round((float) $score) : null,
                'location' => $map->get('cmi.core.lesson_location', null),
            ];
        });
 
        // Resolve user models
        $users = \App\Models\User::whereIn('id', $memberData->keys())->get()->keyBy('id');
 
        $completedCount = $memberData->filter(
            fn($d) => in_array(strtolower($d['status']), ['passed', 'completed', 'complete'], true)
        )->count();
 
        $scores    = $memberData->pluck('score')->filter(fn($s) => $s !== null);
        $avgScore  = $scores->count() > 0 ? round($scores->avg()) : null;
 
        $scormLessons->push((object) [
            'lesson'         => $lesson,
            'memberData'     => $memberData,
            'users'          => $users,
            'completedCount' => $completedCount,
            'totalEnrolled'  => count($enrolledUserIds),
            'avgScore'       => $avgScore,
            'passRate'       => count($enrolledUserIds) > 0
                ? round($completedCount / count($enrolledUserIds) * 100)
                : 0,
        ]);
    }
}
return view('admin.lms.analytics', compact(
    'course',
    'enrollments',
    'completionRate',
    'totalLessons',
    'allProgress',
    'allSubmissions',
    'quizzes',
    'scormLessons'   // ← added here
));
}
}