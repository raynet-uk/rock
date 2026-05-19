<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use ZipArchive;

class ScormController extends Controller
{
    /**
     * Upload and extract a SCORM ZIP for a lesson.
     * Called by admin builder via AJAX.
     */
    public function upload(Request $request, int $lessonId)
    {
        $request->validate([
            'scorm_zip' => ['required', 'file', 'mimes:zip', 'max:102400'], // 100MB
        ]);

        $lesson = CourseLesson::findOrFail($lessonId);

        // Clean up old package if one exists
        $oldPath = public_path('scorm/' . $lessonId);
        if (is_dir($oldPath)) {
            $this->rrmdir($oldPath);
        }

        $zip  = $request->file('scorm_zip');
        $dest = public_path('scorm/' . $lessonId);

        if (!mkdir($dest, 0755, true) && !is_dir($dest)) {
            return response()->json(['success' => false, 'error' => 'Could not create directory.'], 500);
        }

        $za = new ZipArchive();
        if ($za->open($zip->getRealPath()) !== true) {
            return response()->json(['success' => false, 'error' => 'Invalid ZIP file.'], 422);
        }
        $za->extractTo($dest);
        $za->close();

        // Find imsmanifest.xml — may be in a subdirectory
        $manifestPath = $this->findManifest($dest);
        if (!$manifestPath) {
            $this->rrmdir($dest);
            return response()->json(['success' => false, 'error' => 'No imsmanifest.xml found in ZIP.'], 422);
        }

        $launchFile = $this->parseLaunchFile($manifestPath, dirname($manifestPath));
        if (!$launchFile) {
            return response()->json(['success' => false, 'error' => 'Could not determine launch file from manifest.'], 422);
        }

        // Store the launch URL on the lesson
        $launchUrl = '/scorm/' . $lessonId . '/' . ltrim(str_replace(public_path('scorm/' . $lessonId . '/'), '', $launchFile), '/');
        $lesson->video_url = $launchUrl;
        $lesson->save();

        return response()->json([
            'success'    => true,
            'launch_url' => $launchUrl,
            'message'    => 'SCORM package uploaded and extracted successfully.',
        ]);
    }

    /**
     * Serve the SCORM player page — contains the SCORM 1.2 API bridge
     * and an iframe that loads the package.
     */
  public function play(Request $request, int $lessonId)
{
    $lesson     = CourseLesson::findOrFail($lessonId);
    $enrollment = CourseEnrollment::where('user_id', auth()->id())
                    ->where('course_id', $lesson->course_id)
                    ->firstOrFail();

    $launchUrl = $lesson->video_url;
    if (!$launchUrl) {
        abort(404, 'No SCORM package has been uploaded for this lesson.');
    }

    $csrfToken    = csrf_token();
    $completeUrl  = route('lms.complete', ['id' => $lessonId]);
    $enrollmentId = $enrollment->id;

    // Load CMI data server-side so it's available synchronously
    $scormData = \App\Models\LmsScormData::where('user_id', auth()->id())
                    ->where('lesson_id', $lessonId)
                    ->get()
                    ->pluck('value', 'key');

    return view('lms.scorm-player', compact(
        'lesson', 'launchUrl', 'csrfToken', 'completeUrl', 'enrollmentId', 'scormData'
    ));
}
    /**
     * SCORM 1.2 / 2004 data endpoint — stores CMI data for the current user/lesson.
     * Called via fetch() from the SCORM API bridge running in the parent page.
     */
    public function apiSet(Request $request, int $lessonId)
    {
        $data = $request->validate([
            'key'   => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:4096'],
        ]);

        // Persist to lms_scorm_data
        \App\Models\LmsScormData::updateOrCreate(
            [
                'user_id'   => auth()->id(),
                'lesson_id' => $lessonId,
                'key'       => $data['key'],
            ],
            ['value' => $data['value'] ?? '']
        );

        // Auto-complete the lesson when status is passed/completed
        if (in_array($data['key'], ['cmi.core.lesson_status', 'cmi.completion_status'], true)
            && in_array(strtolower($data['value'] ?? ''), ['passed', 'completed', 'complete'], true)) {
            // Reuse existing completion logic via the LearningController
            app(LearningController::class)->complete($request, $lessonId);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Get all stored SCORM CMI data for the current user/lesson.
     */
    public function apiGet(int $lessonId)
    {
        $rows = \App\Models\LmsScormData::where('user_id', auth()->id())
                    ->where('lesson_id', $lessonId)
                    ->get()
                    ->pluck('value', 'key');

        return response()->json($rows);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function findManifest(string $dir): ?string
    {
        // Check root first
        if (file_exists($dir . '/imsmanifest.xml')) {
            return $dir . '/imsmanifest.xml';
        }
        // Search one level deep
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $sub = $dir . '/' . $entry;
            if (is_dir($sub) && file_exists($sub . '/imsmanifest.xml')) {
                return $sub . '/imsmanifest.xml';
            }
        }
        return null;
    }

    private function parseLaunchFile(string $manifestPath, string $baseDir): ?string
    {
        try {
            $xml = simplexml_load_file($manifestPath);
            if (!$xml) return null;

            $xml->registerXPathNamespace('imscp', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2');
            $xml->registerXPathNamespace('adlcp', 'http://www.adlnet.org/xsd/adlcp_rootv1p2');

            // Try standard resource href
            $resources = $xml->xpath('//resource[@href]') ?: $xml->xpath('//*[@href]');
            if ($resources) {
                foreach ($resources as $res) {
                    $href = (string) $res['href'];
                    if ($href && !str_starts_with($href, 'http')) {
                        $full = $baseDir . '/' . $href;
                        if (file_exists($full)) return $full;
                        // href might include query string
                        $hrefClean = explode('?', $href)[0];
                        $full = $baseDir . '/' . $hrefClean;
                        if (file_exists($full)) return $full;
                    }
                }
            }

            // Fallback: find index.html / index.htm
            foreach (['index.html', 'index.htm', 'launch.html', 'story.html', 'story_html5.html'] as $f) {
                if (file_exists($baseDir . '/' . $f)) return $baseDir . '/' . $f;
            }
        } catch (\Throwable) {}

        return null;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }
}