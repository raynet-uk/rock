{{-- Admin Reset Panel — included in analytics.blade.php --}}
<div style="margin-top:2rem;">
    <div style="background:var(--navy);color:#fff;padding:.75rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;border-bottom:3px solid var(--red);">
        🔄 Reset Member Progress
    </div>
    <div style="background:#fff;border:1px solid var(--grey-mid);border-top:none;padding:1.1rem;">
        <p style="font-size:12px;color:var(--muted);margin-bottom:1rem;">
            Reset a member's progress for the entire course, an individual lesson, or a specific quiz. This cannot be undone.
        </p>

        {{-- Member selector --}}
        <div style="margin-bottom:1rem;">
            <label style="font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.06em;color:var(--navy);display:block;margin-bottom:.35rem;">Select Member</label>
            <select id="resetUserSelect" style="width:100%;padding:.45rem .65rem;border:1px solid var(--grey-mid);font-size:13px;background:#fff;">
                <option value="">— choose a member —</option>
                @foreach($enrollments as $e)
                    @if($e->user)
                    <option value="{{ $e->user_id }}">{{ $e->user->name }} ({{ $e->user->callsign ?? $e->user->email }})</option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- Reset options --}}
        <div id="resetOptions" style="display:none;">

            {{-- Reset entire course --}}
            <div style="border:1px solid #fecaca;background:#fff5f5;padding:.85rem 1rem;margin-bottom:.75rem;">
                <div style="font-size:12px;font-weight:bold;color:#991b1b;margin-bottom:.4rem;">⚠ Reset Entire Course</div>
                <div style="font-size:11px;color:#7f1d1d;margin-bottom:.65rem;">Clears all lesson progress, quiz attempts, course completion and certificate.</div>
                <button onclick="resetCourse()" style="padding:.4rem .9rem;background:#dc2626;border:none;color:#fff;font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;">
                    Reset Entire Course
                </button>
            </div>

            {{-- Reset individual lesson --}}
            <div style="border:1px solid var(--grey-mid);padding:.85rem 1rem;margin-bottom:.75rem;">
                <div style="font-size:12px;font-weight:bold;color:var(--navy);margin-bottom:.4rem;">Reset Single Lesson</div>
                <select id="resetLessonSelect" style="width:100%;padding:.4rem .6rem;border:1px solid var(--grey-mid);font-size:12px;margin-bottom:.6rem;background:#fff;">
                    <option value="">— choose a lesson —</option>
                    @foreach($course->lessons()->orderBy('sort_order')->get() as $l)
                    <option value="{{ $l->id }}">{{ $l->title }} ({{ ucfirst($l->type) }})</option>
                    @endforeach
                </select>
                <button onclick="resetLesson()" style="padding:.4rem .9rem;background:var(--navy);border:none;color:#fff;font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;">
                    Reset Lesson
                </button>
            </div>

            {{-- Reset quiz --}}
            @if($quizzes->count())
            <div style="border:1px solid var(--grey-mid);padding:.85rem 1rem;">
                <div style="font-size:12px;font-weight:bold;color:var(--navy);margin-bottom:.4rem;">Reset Quiz Attempts</div>
                <select id="resetQuizSelect" style="width:100%;padding:.4rem .6rem;border:1px solid var(--grey-mid);font-size:12px;margin-bottom:.6rem;background:#fff;">
                    <option value="">— choose a quiz —</option>
                    @foreach($quizzes as $q)
                    <option value="{{ $q->quiz->id }}">{{ $q->quiz->title }}</option>
                    @endforeach
                </select>
                <button onclick="resetQuiz()" style="padding:.4rem .9rem;background:var(--teal);border:none;color:#fff;font-size:11px;font-weight:bold;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;">
                    Reset Quiz
                </button>
            </div>
            @endif

        </div>

        {{-- Status message --}}
        <div id="resetMsg" style="display:none;margin-top:.75rem;padding:.6rem 1rem;font-size:12px;font-weight:bold;border-left:3px solid var(--green);background:var(--green-bg);color:var(--green);"></div>
    </div>
</div>

<script>
const RESET_COURSE_ID = {{ $course->id }};
const RESET_CSRF      = '{{ csrf_token() }}';

document.getElementById('resetUserSelect').addEventListener('change', function() {
    document.getElementById('resetOptions').style.display = this.value ? 'block' : 'none';
    document.getElementById('resetMsg').style.display = 'none';
});

function resetMsg(msg, ok) {
    const el = document.getElementById('resetMsg');
    el.style.display      = 'block';
    el.style.borderColor  = ok ? 'var(--green)' : '#dc2626';
    el.style.background   = ok ? 'var(--green-bg)' : '#fff5f5';
    el.style.color        = ok ? 'var(--green)' : '#dc2626';
    el.textContent        = msg;
}

async function doReset(url, confirmMsg) {
    if (!confirm(confirmMsg)) return;
    const r = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': RESET_CSRF, 'Accept': 'application/json' }
    });
    const d = await r.json();
    resetMsg(d.message || (d.success ? '✓ Done.' : '✗ Error.'), d.success);
    if (d.success) setTimeout(() => location.reload(), 1500);
}

function getUser() {
    const u = document.getElementById('resetUserSelect').value;
    if (!u) { alert('Please select a member first.'); return null; }
    return u;
}

function resetCourse() {
    const u = getUser(); if (!u) return;
    const name = document.getElementById('resetUserSelect').selectedOptions[0].text;
    doReset(`/admin/lms/${RESET_COURSE_ID}/reset/${u}`, `Reset ALL course progress for ${name}? This cannot be undone.`);
}

function resetLesson() {
    const u = getUser(); if (!u) return;
    const l = document.getElementById('resetLessonSelect').value;
    if (!l) { alert('Please select a lesson.'); return; }
    const name   = document.getElementById('resetUserSelect').selectedOptions[0].text;
    const lesson = document.getElementById('resetLessonSelect').selectedOptions[0].text;
    doReset(`/admin/lms/${RESET_COURSE_ID}/reset/${u}/lesson/${l}`, `Reset "${lesson}" for ${name}?`);
}

function resetQuiz() {
    const u = getUser(); if (!u) return;
    const q = document.getElementById('resetQuizSelect').value;
    if (!q) { alert('Please select a quiz.'); return; }
    const name = document.getElementById('resetUserSelect').selectedOptions[0].text;
    const quiz = document.getElementById('resetQuizSelect').selectedOptions[0].text;
    doReset(`/admin/lms/${RESET_COURSE_ID}/reset/${u}/quiz/${q}`, `Reset quiz "${quiz}" for ${name}? All attempts will be deleted.`);
}
</script>