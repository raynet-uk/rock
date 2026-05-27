<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OperatorBriefingMail;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EventAssignmentController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Event $event): View
    {
        $assignments = EventAssignment::with('user')
            ->where('event_id', $event->id)
            ->orderByRaw("FIELD(status,'confirmed','standby','pending','declined')")
            ->orderBy('report_time')
            ->get();

        $assignedIds      = $assignments->pluck('user_id');
        $availableMembers = User::whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get(['id', 'name', 'callsign', 'email']);

        // Fetch unavailability periods that overlap the event date range
        $eventFrom = $event->starts_at ? $event->starts_at->toDateString() : null;
        $eventTo   = ($event->ends_at ?? $event->starts_at)?->toDateString();
        $unavailableUserIds = collect();
        if ($eventFrom) {
            $allUserIds = $availableMembers->pluck('id')->merge($assignments->pluck('user_id'));
            $unavailableUserIds = \App\Models\MemberUnavailability::query()
                ->whereIn('user_id', $allUserIds)
                ->where('from_date', '<=', $eventTo ?? $eventFrom)
                ->where('to_date',   '>=', $eventFrom)
                ->pluck('user_id')
                ->unique();
        }

        $pastEvents = Event::whereHas('assignments')
            ->where('id', '!=', $event->id)
            ->orderByDesc('starts_at')
            ->take(20)
            ->get(['id', 'title', 'starts_at']);

        $stats = [
            'total'      => $assignments->count(),
            'confirmed'  => $assignments->where('status', 'confirmed')->count(),
            'standby'    => $assignments->where('status', 'standby')->count(),
            'pending'    => $assignments->where('status', 'pending')->count(),
            'declined'   => $assignments->where('status', 'declined')->count(),
            'mapped'     => $assignments->whereNotNull('lat')->whereNotNull('lng')->count(),
            'vehicles'   => $assignments->where('has_vehicle', true)->count(),
            'first_aid'  => $assignments->where('first_aid_trained', true)->count(),
        ];

        return view('admin.events.assignments', compact(
            'event',
            'assignments',
            'availableMembers',
            'unavailableUserIds',
            'pastEvents',
            'stats',
        ));
    }

    // ── Store ──────────────────────────────────────────────────────────────────

   public function store(Request $request, Event $event)
{
    $request->validate([
        'user_ids'   => 'required|array|min:1',
        'user_ids.*' => 'exists:users,id',
        'status'     => 'nullable|string',
    ]);

    $shared = $request->except('user_ids', '_token');

    foreach ($request->user_ids as $userId) {
        if ($event->assignments()->where('user_id', $userId)->exists()) {
            continue;
        }
        $event->assignments()->create(array_merge($shared, [
            'user_id'         => $userId,
                'coverage_radius_m' => $request->input('coverage_radius_m', 0) ?? 0,
            'shifts'          => $request->shifts_json ? json_decode($request->shifts_json, true) : null,
            'equipment_items' => $request->equipment_items_json ? json_decode($request->equipment_items_json, true) : null,
        ]));
    }

    return redirect()->back()->with('status', 'Team members assigned successfully.');
}

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, EventAssignment $assignment): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,standby,declined',
        ]);

        $data = $this->prepareAssignmentData($request);

        if ($assignment->status !== $data['status']) {
            $data['status_changed_at'] = now();
        }

        $assignment->update($data);

        return redirect()
            ->route('admin.events.assignments', $assignment->event_id)
            ->with('status', 'Assignment updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(EventAssignment $assignment): RedirectResponse
    {
        $eventId = $assignment->event_id;
        $assignment->delete();

        return redirect()
            ->route('admin.events.assignments', $eventId)
            ->with('status', 'Operator removed from event.');
    }

    // ── Update position (AJAX) ─────────────────────────────────────────────────

    public function updatePosition(Request $request, EventAssignment $assignment): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $assignment->update([
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Attendance status (AJAX poll for team map pulse animations) ───────────

    public function attendanceStatus(Event $event): JsonResponse
    {
        $data = EventAssignment::where('event_id', $event->id)
            ->select('id', 'attendance_status')
            ->get()
            ->map(fn($a) => [
                'id'                => $a->id,
                'attendance_status' => $a->attendance_status,
            ]);

        return response()->json($data);
    }

    // ── Reset attendance (admin) ──────────────────────────────────────────────

    public function resetAttendance(EventAssignment $assignment): RedirectResponse
    {
        $assignment->update([
            'attendance_status' => 'not_arrived',
            'attendance_log'    => [],
        ]);

        return back()->with('success', "Attendance reset for {$assignment->user->name}.");
    }

    public function bulkStatus(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:event_assignments,id',
            'status' => 'required|in:pending,confirmed,standby,declined',
        ]);

        EventAssignment::whereIn('id', $request->input('ids'))
            ->where('event_id', $event->id)
            ->update([
                'status'            => $request->input('status'),
                'status_changed_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    // ── Duplicate team ─────────────────────────────────────────────────────────

    public function duplicateTeam(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'source_event_id' => 'required|exists:events,id',
        ]);

        $source  = EventAssignment::where('event_id', $request->input('source_event_id'))->get();
        $skipped = 0;
        $copied  = 0;

        foreach ($source as $asgn) {
            if (EventAssignment::where('event_id', $event->id)
                    ->where('user_id', $asgn->user_id)->exists()) {
                $skipped++;
                continue;
            }

            EventAssignment::create(array_merge(
                $asgn->only([
                    'user_id', 'role', 'callsign',
                    'frequency', 'mode', 'ctcss_tone', 'channel_label',
                    'secondary_frequency', 'secondary_mode', 'secondary_ctcss',
                    'fallback_frequency', 'fallback_mode', 'fallback_ctcss',
                    'location_name', 'grid_ref', 'what3words', 'lat', 'lng',
                    'coverage_radius_m', 'has_vehicle', 'vehicle_reg',
                    'first_aid_trained', 'equipment', 'equipment_items', 'briefing_notes',
                ]),
                [
                    'event_id'      => $event->id,
                    'status'        => 'pending',
                    'briefing_sent' => false,
                    'shifts'        => null,
                    'report_time'   => null,
                    'start_time'    => null,
                    'end_time'      => null,
                    'depart_time'   => null,
                ]
            ));

            $copied++;
        }

        $msg = "{$copied} operator(s) copied. All statuses set to Pending.";
        if ($skipped) {
            $msg .= " {$skipped} already-assigned operator(s) skipped.";
        }

        return redirect()
            ->route('admin.events.assignments', $event->id)
            ->with('status', $msg);
    }

    // ── Send briefing emails ───────────────────────────────────────────────────

    public function sendBriefings(Request $request, Event $event): RedirectResponse
    {
        $assignments = EventAssignment::with(['user', 'event'])
            ->where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'standby'])
            ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($assignments as $asgn) {
            // Skip if no email address
            if (empty($asgn->user->email)) {
                $failed++;
                continue;
            }

            try {
                Mail::to($asgn->user->email, $asgn->user->name)
                    ->send(new OperatorBriefingMail($asgn));

                $asgn->update([
                    'briefing_sent'    => true,
                    'briefing_sent_at' => now(),
                ]);

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                \Illuminate\Support\Facades\Log::error(
                    'Briefing email failed for assignment ' . $asgn->id,
                    ['error' => $e->getMessage()]
                );
            }
        }

        $msg = "Briefing emails sent to {$sent} operator(s).";
        if ($failed) {
            $msg .= " {$failed} failed (check logs).";
        }

        return redirect()
            ->route('admin.events.assignments', $event->id)
            ->with('status', $msg);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function prepareAssignmentData(Request $request): array
    {
        $shifts = null;
        if ($request->filled('shifts_json')) {
            $decoded = json_decode($request->input('shifts_json'), true);
            if (is_array($decoded)) {
                $shifts = array_values(
                    array_filter($decoded, fn($s) => isset($s['type'])
                        && in_array($s['type'], ['shift', 'break'], true)
                        && !empty($s['start']))
                );
            }
        }

        $equipmentItems = null;
        if ($request->filled('equipment_items_json')) {
            $decoded = json_decode($request->input('equipment_items_json'), true);
            if (is_array($decoded)) {
                $equipmentItems = array_values(
                    array_filter($decoded, fn($v) => is_string($v) && trim($v) !== '')
                );
            }
        }

        return [
            'role'                   => $request->input('role'),
            'callsign'               => $request->input('callsign'),
            'frequency'              => $request->input('frequency') ?: null,
            'mode'                   => $request->input('mode', 'FM'),
            'ctcss_tone'             => $request->input('ctcss_tone') ?: null,
            'channel_label'          => $request->input('channel_label') ?: null,
            'secondary_frequency'    => $request->input('secondary_frequency') ?: null,
            'secondary_mode'         => $request->input('secondary_mode') ?: null,
            'secondary_ctcss'        => $request->input('secondary_ctcss') ?: null,
            'fallback_frequency'     => $request->input('fallback_frequency') ?: null,
            'fallback_mode'          => $request->input('fallback_mode') ?: null,
            'fallback_ctcss'         => $request->input('fallback_ctcss') ?: null,
            'location_name'          => $request->input('location_name') ?: null,
            'grid_ref'               => $request->input('grid_ref') ?: null,
            'what3words'             => $request->input('what3words') ?: null,
            'lat'                    => $request->input('lat') !== '' ? $request->input('lat') : null,
            'lng'                    => $request->input('lng') !== '' ? $request->input('lng') : null,
            'coverage_radius_m'      => (int) $request->input('coverage_radius_m', 0),
            'report_time'            => $request->input('report_time') ?: null,
            'depart_time'            => $request->input('depart_time') ?: null,
            'start_time'             => $this->firstShiftStart($shifts),
            'end_time'               => $this->lastShiftEnd($shifts),
            'shifts'                 => $shifts,
            'has_vehicle'            => $request->boolean('has_vehicle'),
            'vehicle_reg'            => $request->input('vehicle_reg') ?: null,
            'first_aid_trained'      => $request->boolean('first_aid_trained'),
            'equipment'              => $request->input('equipment') ?: null,
            'equipment_items'        => $equipmentItems,
            'briefing_notes'         => $request->input('briefing_notes') ?: null,
            'medical_notes'          => $request->input('medical_notes') ?: null,
            'emergency_contact_name' => $request->input('emergency_contact_name') ?: null,
            'emergency_contact_phone'=> $request->input('emergency_contact_phone') ?: null,
            'status'                 => $request->input('status', 'pending'),
            'status_note'            => $request->input('status_note') ?: null,
        ];
    }

    private function firstShiftStart(?array $shifts): ?string
    {
        if (empty($shifts)) return null;
        foreach ($shifts as $s) {
            if (($s['type'] ?? 'shift') === 'shift' && !empty($s['start'])) {
                return $s['start'];
            }
        }
        return null;
    }

    private function lastShiftEnd(?array $shifts): ?string
    {
        if (empty($shifts)) return null;
        $last = null;
        foreach ($shifts as $s) {
            if (($s['type'] ?? 'shift') === 'shift' && !empty($s['end'])) {
                $last = $s['end'];
            }
        }
        return $last;
    }

    // ── Notify team ────────────────────────────────────────────────────────────
    public function notifyTeam(Request $request, \App\Models\Event $event)
    {
        $request->validate([
            'notify_type'    => 'required|in:custom,reminder',
            'custom_message' => 'required_if:notify_type,custom|nullable|string|max:2000',
            'notify_status'  => 'required|array',
        ]);

        $statuses    = $request->notify_status;
        $assignments = $event->assignments()
            ->with('user', 'event')
            ->whereIn('status', $statuses)
            ->get();

        $sent = 0;
        foreach ($assignments as $assignment) {
            if (!$assignment->user->email) continue;
            \Illuminate\Support\Facades\Mail::to($assignment->user->email)
                ->send(new \App\Mail\TeamNotification(
                    $assignment,
                    $request->notify_type,
                    $request->custom_message ?? ''
                ));
            $sent++;
        }

        return redirect()->back()->with('status', "Notification sent to {$sent} team member(s).");
    }

    // ── Send individual briefing email + PDF ───────────────────────────────────
    public function sendSingleBriefing(Request $request, EventAssignment $assignment)
    {
        $customMessage = $request->input('custom_message') ?? '';
        $pdfPath = $this->generateBriefingPdf($assignment, $customMessage);

        \Illuminate\Support\Facades\Mail::to($assignment->user->email)
            ->send(new \App\Mail\TeamBriefing($assignment, $customMessage, $pdfPath));

        $assignment->update(['briefing_sent' => true, 'briefing_sent_at' => now()]);

        if ($pdfPath && file_exists($pdfPath)) @unlink($pdfPath);

        return redirect()->back()->with('status', 'Briefing sent to ' . $assignment->user->name);
    }

    // ── Send bulk briefings ────────────────────────────────────────────
    public function sendBulkBriefings(Request $request, \App\Models\Event $event)
    {
        $customMessage = $request->input('custom_message') ?? '';
        $assignmentIds = $request->input('assignment_ids', []);
        $statuses      = $request->input('statuses', ['confirmed', 'standby']);
        $idCount       = count($assignmentIds);

        if ($idCount > 0) {
            $assignments = \App\Models\EventAssignment::with('user', 'event')
                ->whereIn('id', $assignmentIds)
                ->where('event_id', $event->id)
                ->whereHas('user', fn($q) => $q->whereNotNull('email'))
                ->get();
        } else {
            $assignments = $event->assignments()
                ->with('user', 'event')
                ->whereIn('status', $statuses)
                ->whereHas('user', fn($q) => $q->whereNotNull('email'))
                ->get();
        }

        $sent = 0;
        foreach ($assignments as $assignment) {
            $pdfPath = $this->generateBriefingPdf($assignment, $customMessage);
            \Illuminate\Support\Facades\Mail::to($assignment->user->email)
                ->send(new \App\Mail\TeamBriefing($assignment, $customMessage, $pdfPath));
            $assignment->update(['briefing_sent' => true, 'briefing_sent_at' => now()]);
            if ($pdfPath && file_exists($pdfPath)) @unlink($pdfPath);
            $sent++;
        }

        return redirect()->back()->with('status', "Briefing sent to {$sent} team member(s).");
    }

    // ── Download PDF for individual assignment ─────────────────────────────────
    public function downloadBriefingPdf(Request $request, EventAssignment $assignment)
    {
        $customMessage = $request->input('custom_message') ?? '';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.team-briefing', [
            'assignment'    => $assignment->load('user', 'event', 'event.type'),
            'customMessage' => $customMessage,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Briefing_' . str_replace(' ', '_', $assignment->user->name) . '.pdf');
    }

    // ── Helper: generate PDF to temp file ─────────────────────────────────────
    private function generateBriefingPdf(EventAssignment $assignment, string $customMessage = ''): ?string
    {
        try {
            $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.team-briefing', [
                'assignment'    => $assignment->load('user', 'event', 'event.type'),
                'customMessage' => $customMessage,
            ])->setPaper('a4', 'portrait');
            $path = storage_path('app/tmp/briefing_' . $assignment->id . '_' . time() . '.pdf');
            if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
            file_put_contents($path, $pdf->output());
            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function resetBriefing($assignment): \Illuminate\Http\RedirectResponse
    {
        $a = \App\Models\EventAssignment::findOrFail($assignment);
        $a->update([
            'briefing_sent'    => false,
            'briefing_sent_at' => null,
        ]);
        return back()->with('status', 'Briefing flag cleared for ' . ($a->user->name ?? 'operator') . '.');
    }


    public function bulkFill(Request $request, Event $event): \Illuminate\Http\JsonResponse
    {
        $ids    = $request->input('assignment_ids', []);
        $fields = $request->input('fields', []);
        $notesReplace = (bool)($fields['notes_replace'] ?? false);
        unset($fields['notes_replace']);

        $allowed = [
            'frequency', 'mode', 'ctcss_tone', 'channel_label',
            'fallback_frequency', 'fallback_mode',
            'report_time', 'depart_time', 'briefing_notes',
        ];

        $toApply = array_filter($fields, fn($v) => $v !== null && $v !== '', ARRAY_FILTER_USE_BOTH);
        $toApply = array_intersect_key($toApply, array_flip($allowed));

        if (empty($ids) || empty($toApply)) {
            return response()->json(['success' => false, 'message' => 'Nothing to update.']);
        }

        $assignments = \App\Models\EventAssignment::where('event_id', $event->id)
            ->whereIn('id', $ids)->get();

        $updated = [];
        foreach ($assignments as $a) {
            $data = $toApply;
            // Handle briefing notes append vs replace
            if (isset($data['briefing_notes']) && !$notesReplace && $a->briefing_notes) {
                $data['briefing_notes'] = $a->briefing_notes . "\n" . $data['briefing_notes'];
            }
            $a->update($data);
            $updated[] = [
                'id'              => $a->id,
                'frequency'       => $a->frequency,
                'mode'            => $a->mode,
                'ctcss_tone'      => $a->ctcss_tone,
                'channel_label'   => $a->channel_label,
                'fallback_frequency' => $a->fallback_frequency,
                'fallback_mode'   => $a->fallback_mode,
                'report_time'     => $a->report_time ? substr($a->report_time, 0, 5) : null,
                'depart_time'     => $a->depart_time ? substr($a->depart_time, 0, 5) : null,
                'briefing_notes'  => $a->briefing_notes,
            ];
        }

        return response()->json(['success' => true, 'updated' => count($updated), 'assignments' => $updated]);
    }


    public function downloadOpsPack(\App\Models\Event $event): mixed
    {
        $assignments = \App\Models\EventAssignment::with('user')
            ->where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'standby', 'pending'])
            ->orderBy('created_at')
            ->get();

        $pois = [];
        if ($event->event_pois) {
            $pois = is_array($event->event_pois) ? $event->event_pois : json_decode($event->event_pois, true) ?? [];
        }

        $event->load('type');

        $eventData = [
            'title'        => $event->title,
            'date'         => $event->starts_at?->format('l j F Y') ?? '',
            'time'         => ($event->starts_at?->format('H:i') ?? '') . ($event->ends_at ? ' - '.$event->ends_at->format('H:i') : ''),
            'location'     => $event->location ?? '',
            'type'         => $event->type?->name ?? '',
            'supporting'   => $event->supporting_group ?? '',
            'description'  => $event->description ?? '',
            'group_name'   => \App\Helpers\RaynetSetting::groupName(),
            'group_number' => \App\Helpers\RaynetSetting::groupNumber(),
            'group_region' => \App\Helpers\RaynetSetting::groupRegion(),
            'issued_by'    => auth()->user()->name . ' (' . (auth()->user()->callsign ?? 'no callsign') . ')',
            'issued_at'    => now()->format('j M Y H:i'),
        ];

        $assignmentsData = $assignments->map(fn($a) => [
            'name'                    => $a->user->name,
            'callsign'                => $a->callsign ?? '',
            'role'                    => $a->role ?? '',
            'status'                  => $a->status ?? '',
            'location_name'           => $a->location_name ?? '',
            'grid_ref'                => $a->grid_ref ?? '',
            'what3words'              => $a->what3words ?? '',
            'lat'                     => $a->lat ? number_format((float)$a->lat, 5) : '',
            'lng'                     => $a->lng ? number_format((float)$a->lng, 5) : '',
            'frequency'               => $a->frequency ?? '',
            'mode'                    => $a->mode ?? '',
            'ctcss_tone'              => $a->ctcss_tone ?? '',
            'channel_label'           => $a->channel_label ?? '',
            'secondary_frequency'     => $a->secondary_frequency ?? '',
            'secondary_mode'          => $a->secondary_mode ?? '',
            'fallback_frequency'      => $a->fallback_frequency ?? '',
            'fallback_mode'           => $a->fallback_mode ?? '',
            'report_time'             => $a->report_time ? substr($a->report_time, 0, 5) : '',
            'depart_time'             => $a->depart_time ? substr($a->depart_time, 0, 5) : '',
            'has_vehicle'             => (bool)$a->has_vehicle,
            'vehicle_reg'             => $a->vehicle_reg ?? '',
            'first_aid_trained'       => (bool)$a->first_aid_trained,
            'equipment_items'         => $a->equipment_items ?? [],
            'shifts'                  => $a->shifts ?? [],
            'briefing_notes'          => $a->briefing_notes ?? '',
            'medical_notes'           => $a->medical_notes ?? '',
            'emergency_contact_name'  => $a->emergency_contact_name ?? '',
            'emergency_contact_phone' => $a->emergency_contact_phone ?? '',
        ])->values()->all();

        return view('admin.events.ops-pack-pdf', [
            'event'           => $event,
            'eventData'       => $eventData,
            'assignmentsData' => $assignmentsData,
            'pois'            => $pois,
        ]);
    }


    public function mapThumbnail(\Illuminate\Http\Request $request): mixed
    {
        $lat  = (float) $request->query('lat', 53.4);
        $lng  = (float) $request->query('lng', -2.99);
        $zoom = (int)   $request->query('zoom', 15);
        $size = (int)   $request->query('size', 400);
        $size = min($size, 600);

        // Convert lat/lng to tile x/y + exact sub-tile pixel offset
        $n      = pow(2, $zoom);
        $tileX  = (int) floor(($lng + 180) / 360 * $n);
        $latRad = deg2rad($lat);
        $tileY  = (int) floor((1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n);
        $exactX = (($lng + 180) / 360 * $n - $tileX) * 256;
        $exactY = ((1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n - $tileY) * 256;

        // Fetch 3x3 grid of tiles and composite
        $tileSize = 256;
        $canvas   = imagecreatetruecolor($tileSize * 3, $tileSize * 3);

        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                $tx  = $tileX + $dx;
                $ty  = $tileY + $dy;
                $url = "https://tile.openstreetmap.org/{$zoom}/{$tx}/{$ty}.png";

                try {
                    $ctx  = stream_context_create(['http' => ['timeout' => 5, 'header' => "User-Agent: RAYNET-Liverpool-Site/1.0
"]]);
                    $data = @file_get_contents($url, false, $ctx);
                    if ($data) {
                        $tile = @imagecreatefromstring($data);
                        if ($tile) {
                            imagecopy($canvas, $tile, ($dx+1)*$tileSize, ($dy+1)*$tileSize, 0, 0, $tileSize, $tileSize);
                            imagedestroy($tile);
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Pin at exact sub-tile pixel position
        $cx = (int) round($tileSize + $exactX);
        $cy = (int) round($tileSize + $exactY);
        $red = imagecolorallocate($canvas, 200, 16, 46);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        // Pin circle
        imagefilledellipse($canvas, (int)$cx, (int)$cy - 18, 20, 20, $red);
        imageellipse($canvas, (int)$cx, (int)$cy - 18, 22, 22, $white);

        // Pin tail
        imageline($canvas, (int)$cx, (int)$cy - 8, (int)$cx, (int)$cy, $red);
        imageline($canvas, (int)$cx-1, (int)$cy - 8, (int)$cx-1, (int)$cy, $red);

        // Crop to size centred on pin
        $half   = (int)($size / 2);
        $cropped = imagecreatetruecolor($size, $size);
        imagecopy($cropped, $canvas, 0, 0, (int)$cx - $half, (int)$cy - $half, $size, $size);
        imagedestroy($canvas);

        ob_start();
        imagepng($cropped);
        $imageData = ob_get_clean();
        imagedestroy($cropped);

        return response($imageData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function streetViewThumbnail(\Illuminate\Http\Request $request): mixed
    {
        // Use a very close zoom OSM map (zoom 18) as a free street-level context image
        $lat  = (float) $request->query('lat', 53.4);
        $lng  = (float) $request->query('lng', -2.99);
        $zoom = 18;

        $n      = pow(2, $zoom);
        $tileX  = (int) floor(($lng + 180) / 360 * $n);
        $latRad = deg2rad($lat);
        $tileY  = (int) floor((1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n);
        $exactX = (($lng + 180) / 360 * $n - $tileX) * 256;
        $exactY = ((1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n - $tileY) * 256;

        $tileSize = 256;
        $canvas   = imagecreatetruecolor($tileSize * 3, $tileSize * 3);
        $bg       = imagecolorallocate($canvas, 220, 226, 232);
        imagefill($canvas, 0, 0, $bg);

        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                $tx  = $tileX + $dx;
                $ty  = $tileY + $dy;
                $url = "https://tile.openstreetmap.org/{$zoom}/{$tx}/{$ty}.png";
                try {
                    $ctx  = stream_context_create(['http' => ['timeout' => 5, 'header' => "User-Agent: RAYNET-Liverpool-Site/1.0
"]]);
                    $data = @file_get_contents($url, false, $ctx);
                    if ($data) {
                        $tile = @imagecreatefromstring($data);
                        if ($tile) {
                            imagecopy($canvas, $tile, ($dx+1)*$tileSize, ($dy+1)*$tileSize, 0, 0, $tileSize, $tileSize);
                            imagedestroy($tile);
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Draw pin
        $cx    = (int) round($tileSize + $exactX);
        $cy    = (int) round($tileSize + $exactY);
        $red   = imagecolorallocate($canvas, 200, 16, 46);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledellipse($canvas, $cx, $cy - 18, 20, 20, $red);
        imageellipse($canvas, $cx, $cy - 18, 22, 22, $white);
        imageline($canvas, $cx, $cy - 8, $cx, $cy, $red);
        imageline($canvas, $cx-1, $cy - 8, $cx-1, $cy, $red);

        // Crop landscape 400x220 centred on pin
        $outW = 800; $outH = 440;
        $cropped = imagecreatetruecolor($outW, $outH);
        imagecopy($cropped, $canvas, 0, 0, $cx - (int)($outW/2), $cy - (int)($outH/2), $outW, $outH);
        imagedestroy($canvas);

        // Add "Close-up View" label
        $navy = imagecolorallocate($cropped, 0, 51, 102);
        $lbl  = imagecolorallocate($cropped, 255, 255, 255);
        imagefilledrectangle($cropped, 0, 0, 100, 14, $navy);
        imagestring($cropped, 2, 4, 2, 'Close-up View (z18)', $lbl);

        ob_start();
        imagepng($cropped);
        $imageData = ob_get_clean();
        imagedestroy($cropped);

        return response($imageData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

}