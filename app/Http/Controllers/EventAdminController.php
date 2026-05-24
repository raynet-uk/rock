<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDocument;
use App\Models\EventType;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventAdminController extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX — list events; ?edit={id} pre-populates the edit form
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $events = Event::with(['type', 'documents'])
            ->orderBy('starts_at', 'asc')
            ->paginate(10);

        $types = EventType::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $editingEvent = null;
        if ($request->filled('edit')) {
            $editingEvent = Event::find($request->integer('edit'));
        }

        return view('admin.events.index', compact('events', 'types', 'editingEvent'));
    }

    // -------------------------------------------------------------------------
    // STORE
    // -------------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'location'           => ['nullable', 'string', 'max:255'],
            'starts_at'          => ['required', 'date'],
            'ends_at'            => ['nullable', 'date', 'after:starts_at'],
            'event_type_id'      => ['required', 'exists:event_types,id'],
            'description'        => ['nullable', 'string'],
            'is_private'         => ['nullable', 'boolean'],          // ← ADDED
            'event_lat'          => ['nullable', 'numeric', 'between:-90,90'],
            'event_lng'          => ['nullable', 'numeric', 'between:-180,180'],
            'event_polygon'      => ['nullable', 'string'],
            'event_polygon_name' => ['nullable', 'string', 'max:120'],
            'event_route'        => ['nullable', 'string'],
            'event_route_name'   => ['nullable', 'string', 'max:120'],
            'event_pois'         => ['nullable', 'string'],
        ], [], [
            'starts_at'     => 'start date/time',
            'ends_at'       => 'end date/time',
            'event_type_id' => 'event type',
        ]);

        $event = new Event;
        $event->title              = $data['title'];
        $event->slug               = Str::slug($data['title']);
        $event->location           = $data['location'] ?? null;
        $event->starts_at          = Carbon::parse($data['starts_at']);
        $event->ends_at            = !empty($data['ends_at']) ? Carbon::parse($data['ends_at']) : null;
        $event->event_type_id      = $data['event_type_id'];
        $event->description        = $data['description'] ?? null;
        $event->is_public          = true;
        $event->is_private         = $request->boolean('is_private');  // ← ADDED
        $event->event_lat          = isset($data['event_lat']) && $data['event_lat'] !== '' ? (float) $data['event_lat'] : null;
        $event->event_lng          = isset($data['event_lng']) && $data['event_lng'] !== '' ? (float) $data['event_lng'] : null;
        $event->event_polygon      = $this->parsePolygon($request->input('event_polygon'));
        $event->event_polygon_name = $request->filled('event_polygon_name') ? trim($request->input('event_polygon_name')) : null;
        $event->event_route        = $this->parseRoute($request->input('event_route'));
        $event->event_route_name   = $request->filled('event_route_name')   ? trim($request->input('event_route_name'))   : null;
        $event->event_pois         = $this->parsePois($request->input('event_pois'));
        $event->save();

        return redirect()->route('admin.events')->with('status', 'Event created.');
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    public function update(Request $request, int $id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'location'           => ['nullable', 'string', 'max:255'],
            'starts_at'          => ['required', 'date'],
            'ends_at'            => ['nullable', 'date', 'after:starts_at'],
            'event_type_id'      => ['required', 'exists:event_types,id'],
            'description'        => ['nullable', 'string'],
            'is_private'         => ['nullable', 'boolean'],          // ← ADDED
            'event_lat'          => ['nullable', 'numeric', 'between:-90,90'],
            'event_lng'          => ['nullable', 'numeric', 'between:-180,180'],
            'event_polygon'      => ['nullable', 'string'],
            'event_polygon_name' => ['nullable', 'string', 'max:120'],
            'event_route'        => ['nullable', 'string'],
            'event_route_name'   => ['nullable', 'string', 'max:120'],
            'event_pois'         => ['nullable', 'string'],
        ], [], [
            'starts_at'     => 'start date/time',
            'ends_at'       => 'end date/time',
            'event_type_id' => 'event type',
        ]);

        $event->title              = $data['title'];
        $event->slug               = Str::slug($data['title']);
        $event->location           = $data['location'] ?? null;
        $event->starts_at          = Carbon::parse($data['starts_at']);
        $event->ends_at            = !empty($data['ends_at']) ? Carbon::parse($data['ends_at']) : null;
        $event->event_type_id      = $data['event_type_id'];
        $event->description        = $data['description'] ?? null;
        $event->is_private         = $request->boolean('is_private');  // ← ADDED
        $event->event_lat          = isset($data['event_lat']) && $data['event_lat'] !== '' ? (float) $data['event_lat'] : null;
        $event->event_lng          = isset($data['event_lng']) && $data['event_lng'] !== '' ? (float) $data['event_lng'] : null;
        $event->event_polygon      = $this->parsePolygon($request->input('event_polygon'));
        $event->event_polygon_name = $request->filled('event_polygon_name') ? trim($request->input('event_polygon_name')) : null;
        $event->event_route        = $this->parseRoute($request->input('event_route'));
        $event->event_route_name   = $request->filled('event_route_name')   ? trim($request->input('event_route_name'))   : null;
        $event->event_pois         = $this->parsePois($request->input('event_pois'));
        $event->save();

        return redirect()->route('admin.events')->with('status', 'Event updated.');
    }

    // -------------------------------------------------------------------------
    // DESTROY
    // -------------------------------------------------------------------------

    public function destroy(int $id): RedirectResponse
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('admin.events')->with('status', 'Event deleted.');
    }

    // -------------------------------------------------------------------------
    // EXPORT CSV
    // -------------------------------------------------------------------------

    public function export(): StreamedResponse
    {
        $fileName = 'events_export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'id', 'title', 'slug', 'starts_at', 'ends_at',
                'location', 'type_name', 'description',
                'event_lat', 'event_lng',
                'is_sample', 'is_public', 'is_private', 'created_at', 'updated_at',
            ]);

            Event::with('type')
                ->orderBy('starts_at')
                ->chunk(200, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            $event->id,
                            $event->title,
                            $event->slug,
                            optional($event->starts_at)->format('Y-m-d H:i:s'),
                            optional($event->ends_at)->format('Y-m-d H:i:s'),
                            $event->location,
                            optional($event->type)->name,
                            $event->description,
                            $event->event_lat ?? '',
                            $event->event_lng ?? '',
                            $event->is_sample   ? 1 : 0,
                            $event->is_public   ? 1 : 0,
                            $event->is_private  ? 1 : 0,   // ← ADDED
                            optional($event->created_at)->format('Y-m-d H:i:s'),
                            optional($event->updated_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCsv(): StreamedResponse
    {
        return $this->export();
    }

    // -------------------------------------------------------------------------
    // IMPORT CSV
    // -------------------------------------------------------------------------

    public function showImportForm(): View
    {
        return view('admin.events.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'events_file'     => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $updateExisting = (bool)($data['update_existing'] ?? false);

        $file = $request->file('events_file');
        $path = $file->getRealPath();

        if (! $path || ! file_exists($path)) {
            return back()->withErrors(['events_file' => 'Uploaded file could not be read.']);
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->withErrors(['events_file' => 'Unable to open the uploaded file.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->withErrors(['events_file' => 'CSV file appears to be empty or invalid.']);
        }

        $map = [];
        foreach ($header as $index => $name) {
            $key = strtolower(trim($name));
            if ($key !== '') {
                $map[$key] = $index;
            }
        }

        $required = ['title', 'slug', 'starts_at', 'ends_at', 'location', 'type_name', 'description', 'is_sample'];

        foreach ($required as $column) {
            if (! array_key_exists($column, $map)) {
                fclose($handle);
                return back()->withErrors([
                    'events_file' => "Missing required column '{$column}'. Tip: export a CSV first, edit it, then re-import.",
                ]);
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            $col = function (string $name) use ($map, $row): ?string {
                $index = $map[$name] ?? null;
                if ($index === null || ! array_key_exists($index, $row)) {
                    return null;
                }
                return trim((string)$row[$index]);
            };

            $title       = $col('title') ?: null;
            $slug        = $col('slug') ?: null;
            $startsAtRaw = $col('starts_at') ?: null;
            $endsAtRaw   = $col('ends_at') ?: null;
            $location    = $col('location') ?: null;
            $typeName    = $col('type_name') ?: null;
            $description = $col('description') ?: null;
            $isSampleRaw = strtolower($col('is_sample') ?? '');
            $isPrivateRaw = strtolower($col('is_private') ?? '');  // ← ADDED
            $csvLat      = $col('event_lat');
            $csvLng      = $col('event_lng');

            if (! $title || ! $slug || ! $startsAtRaw) {
                $skipped++;
                continue;
            }

            try {
                $startsAt = Carbon::parse($startsAtRaw);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            $endsAt = null;
            if (! empty($endsAtRaw)) {
                try {
                    $endsAt = Carbon::parse($endsAtRaw);
                } catch (\Throwable $e) {
                    $endsAt = null;
                }
            }

            $isSample    = in_array($isSampleRaw,  ['1', 'true', 'yes', 'y'], true);
            $isPrivate   = in_array($isPrivateRaw, ['1', 'true', 'yes', 'y'], true);  // ← ADDED
            $eventLat    = ($csvLat !== null && $csvLat !== '') ? (float) $csvLat : null;
            $eventLng    = ($csvLng !== null && $csvLng !== '') ? (float) $csvLng : null;
            $eventTypeId = null;

            if ($typeName) {
                $type = EventType::firstOrCreate(
                    ['name' => $typeName],
                    ['colour' => '#22c55e']
                );
                $eventTypeId = $type->id;
            }

            $existing = Event::where('slug', $slug)->first();

            if ($existing) {
                if (! $updateExisting) {
                    $skipped++;
                    continue;
                }
                $existing->title         = $title;
                $existing->starts_at     = $startsAt;
                $existing->ends_at       = $endsAt;
                $existing->location      = $location;
                $existing->description   = $description;
                $existing->is_sample     = $isSample;
                $existing->is_private    = $isPrivate;   // ← ADDED
                $existing->event_type_id = $eventTypeId;
                $existing->event_lat     = $eventLat;
                $existing->event_lng     = $eventLng;
                $existing->save();
                $updated++;
            } else {
                Event::create([
                    'title'         => $title,
                    'slug'          => $slug,
                    'starts_at'     => $startsAt,
                    'ends_at'       => $endsAt,
                    'location'      => $location,
                    'description'   => $description,
                    'is_sample'     => $isSample,
                    'is_private'    => $isPrivate,    // ← ADDED
                    'event_type_id' => $eventTypeId,
                    'event_lat'     => $eventLat,
                    'event_lng'     => $eventLng,
                ]);
                $created++;
            }
        }

        fclose($handle);

        return redirect()
            ->route('admin.events')
            ->with('status', sprintf(
                'Import complete: %d created, %d updated, %d skipped.',
                $created, $updated, $skipped
            ));
    }

    // -------------------------------------------------------------------------
    // DOCUMENTS — upload, download, delete
    // -------------------------------------------------------------------------

    public function uploadDocument(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'document' => [
                'required', 'file', 'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,zip',
            ],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $file     = $request->file('document');
        $filename = $file->getClientOriginalName();
        $path     = $file->store("event-documents/{$event->id}", 'local');

        EventDocument::create([
            'event_id'    => $event->id,
            'filename'    => $filename,
            'label'       => $request->filled('label') ? trim($request->input('label')) : null,
            'disk'        => 'local',
            'path'        => $path,
            'size_bytes'  => $file->getSize(),
            'sort_order'  => EventDocument::where('event_id', $event->id)->count(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.events', ['docs' => $event->id])
            ->with('status', 'Document "' . $filename . '" uploaded successfully.');
    }

    public function downloadDocument(EventDocument $document): mixed
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->filename);
    }

    public function deleteDocument(EventDocument $document): RedirectResponse
    {
        $eventId = $document->event_id;
        $label   = $document->label ?: $document->filename;

        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return redirect()
            ->route('admin.events', ['docs' => $eventId])
            ->with('status', 'Document "' . $label . '" removed.');
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Decode and validate a GeoJSON Polygon/MultiPolygon from the map picker.
     */
    private function parsePolygon(?string $raw): ?array
    {
        if (empty($raw)) return null;

        try {
            $d = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if (! is_array($d) || ! isset($d['type'], $d['coordinates'])
            || ! in_array($d['type'], ['Polygon', 'MultiPolygon'], true)) {
            return null;
        }

        return $d;
    }

    /**
     * Decode and validate the event route field.
     *
     * New format:  [{id, name, geometry: LineString}, ...]
     * Legacy format: {type: "LineString", coordinates: [...]}
     *
     * Always stores as the array format. Returns null if empty/invalid.
     */
    private function parseRoute(?string $raw): ?array
    {
        if (empty($raw)) return null;

        try {
            $d = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        // New array format: [{id, name, geometry}, ...]
        if (isset($d[0]) && is_array($d[0])) {
            $clean = [];
            foreach ($d as $item) {
                if (!isset($item['geometry']['type'], $item['geometry']['coordinates'])) continue;
                if (!in_array($item['geometry']['type'], ['LineString', 'MultiLineString'], true)) continue;
                $clean[] = [
                    'id'       => $item['id']       ?? \Illuminate\Support\Str::uuid()->toString(),
                    'name'     => substr((string)($item['name'] ?? 'Route'), 0, 120),
                    'geometry' => $item['geometry'],
                ];
            }
            return empty($clean) ? null : $clean;
        }

        // Legacy single-geometry format: {type, coordinates}
        if (
            is_array($d) &&
            isset($d['type'], $d['coordinates']) &&
            in_array($d['type'], ['LineString', 'MultiLineString'], true)
        ) {
            return [[
                'id'       => \Illuminate\Support\Str::uuid()->toString(),
                'name'     => 'Route',
                'geometry' => $d,
            ]];
        }

        return null;
    }

    /**
     * Decode and sanitise the POI JSON array from the map picker.
     */
    private function parsePois(?string $raw): ?array
    {
        if (empty($raw)) return null;

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if (! is_array($decoded)) return null;

        $allowedTypes = ['entrance', 'exit', 'car_park', 'medical', 'control', 'hazard', 'info', 'custom'];

        $clean = [];
        foreach ($decoded as $poi) {
            if (! isset($poi['lat'], $poi['lng'], $poi['name'])) continue;

            $clean[] = [
                'id'          => $poi['id']          ?? \Illuminate\Support\Str::uuid()->toString(),
                'type'        => in_array($poi['type'] ?? '', $allowedTypes, true) ? $poi['type'] : 'custom',
                'name'        => substr((string)($poi['name']        ?? ''), 0, 100),
                'description' => substr((string)($poi['description'] ?? ''), 0, 300),
                'lat'         => round((float)$poi['lat'], 7),
                'lng'         => round((float)$poi['lng'], 7),
                'colour'      => preg_match('/^#[0-9a-fA-F]{6}$/', $poi['colour'] ?? '')
                                    ? $poi['colour'] : '#C8102E',
            ];
        }

        return empty($clean) ? null : $clean;
    }
    // ── Send availability request emails ───────────────────────────────────────
    public function sendAvailabilityRequest(\Illuminate\Http\Request $request, \App\Models\Event $event)
    {
        $members = \App\Models\User::where('is_active', true)
            ->whereNotNull('email')
            ->get();

        $sent = 0;
        foreach ($members as $member) {
            $token = base64_encode($member->id . '|' . $event->id . '|' . hash_hmac('sha256', $member->id . '|' . $event->id, config('app.key')));
            $availableUrl   = route('events.availability.respond', ['token' => $token, 'response' => 'available']);
            $unavailableUrl = route('events.availability.respond', ['token' => $token, 'response' => 'unavailable']);

            \Illuminate\Support\Facades\Mail::to($member->email)
                ->send(new \App\Mail\EventAvailabilityRequest(
                    $event,
                    $member,
                    $availableUrl,
                    $unavailableUrl
                ));
            $sent++;
        }

        return redirect()->back()->with('status', "Availability request sent to {$sent} members.");
    }

    // ── Handle availability response ───────────────────────────────────────────
    public function availabilityResponse(\Illuminate\Http\Request $request, string $token)
    {
        $response = $request->query('response');
        if (!in_array($response, ['available', 'unavailable'])) abort(404);

        try {
            $decoded = base64_decode($token);
            [$userId, $eventId, $hash] = explode('|', $decoded);
            $expected = hash_hmac('sha256', $userId . '|' . $eventId, config('app.key'));
            if (!hash_equals($expected, $hash)) abort(403, 'Invalid token');
        } catch (\Throwable $e) {
            abort(403, 'Invalid token');
        }

        $event  = \App\Models\Event::findOrFail($eventId);
        $member = \App\Models\User::findOrFail($userId);

        \App\Models\UserEventAvailability::updateOrCreate(
            ['user_id' => $userId, 'event_id' => $eventId],
            ['available' => $response === 'available', 'responded_at' => now()]
        );

        return view('events.availability-confirmed', [
            'event'    => $event,
            'member'   => $member,
            'response' => $response,
        ]);
    }
    public function netStatus() {
        $schedules = \App\Models\NetSchedule::orderBy('start_time')->get();
        $sessions  = \Illuminate\Support\Facades\DB::table('net_sessions')->latest()->limit(10)->get();
        $settings = [
            'net_active'            => \App\Models\Setting::get('net_active', '0'),
            'net_callsign'          => \App\Models\Setting::get('net_callsign', ''),
            'net_frequency'         => \App\Models\Setting::get('net_frequency', ''),
            'net_description'       => \App\Models\Setting::get('net_description', ''),
            'net_announcement'      => \App\Models\Setting::get('net_announcement', ''),
            'net_controller'        => \App\Models\Setting::get('net_controller', ''),
            'net_controller_slots'  => \App\Models\Setting::get('net_controller_slots', '[]'),
            'net_station_logging'   => \App\Models\Setting::get('net_station_logging', '0'),
            'net_band'              => \App\Models\Setting::get('net_band', ''),
            'net_priority'          => \App\Models\Setting::get('net_priority', 'routine'),
            'net_start_time'        => \App\Models\Setting::get('net_start_time', ''),
            'net_end_time'          => \App\Models\Setting::get('net_end_time', ''),
        ];
        return view('admin.events.net-status', compact('settings', 'schedules', 'sessions'));
    }

    public function storeNetSchedule(\Illuminate\Http\Request $request) {
        $request->validate(['name'=>'required|string|max:100','callsign'=>'required|string|max:30','frequency'=>'nullable|string|max:30','controller'=>'nullable|string|max:30','description'=>'nullable|string','days_of_week'=>'required|array|min:1','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i',]);
        $newSchedule = \App\Models\NetSchedule::create(['name'=>$request->name,'callsign'=>strtoupper($request->callsign),'frequency'=>$request->frequency,'band'=>$request->band,'controller'=>$request->controller ? strtoupper($request->controller) : null,'controller_slots'=>$request->controller_slots ?? [],'description'=>$request->description,'announcement'=>$request->announcement,'days_of_week'=>$request->days_of_week,'repeat_type'=>$request->repeat_type ?? 'weekly','priority'=>$request->priority ?? 'routine','start_time'=>$request->start_time,'end_time'=>$request->end_time,'auto_activate'=>$request->boolean('auto_activate'),'is_active'=>true,]);
        $this->notifyControllerSlots($request->controller_slots ?? [], [], [
            'callsign'     => strtoupper($request->callsign),
            'name'         => $request->name,
            'frequency'    => $request->frequency,
            'description'  => $request->description,
            'announcement' => $request->announcement,
        ]);
        return back()->with('success', 'Schedule created.');
    }


    public function updateNetSchedule(\Illuminate\Http\Request $request, $id) {
        $s = \App\Models\NetSchedule::findOrFail($id);
        $request->validate(['name'=>'required|string|max:100','callsign'=>'required|string|max:30','frequency'=>'nullable|string|max:30','controller'=>'nullable|string|max:30','description'=>'nullable|string','days_of_week'=>'required|array|min:1','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i']);
        $previousSlots2 = is_array($s->controller_slots) ? $s->controller_slots : json_decode($s->controller_slots ?? '[]', true) ?? [];
        $s->update(['name'=>$request->name,'callsign'=>strtoupper($request->callsign),'frequency'=>$request->frequency,'band'=>$request->band,'controller'=>$request->controller ? strtoupper($request->controller) : null,'controller_slots'=>$request->controller_slots ?? [],'description'=>$request->description,'announcement'=>$request->announcement,'days_of_week'=>$request->days_of_week,'repeat_type'=>$request->repeat_type ?? 'weekly','priority'=>$request->priority ?? 'routine','start_time'=>$request->start_time,'end_time'=>$request->end_time,'auto_activate'=>$request->boolean('auto_activate'),'is_active'=>$request->boolean('is_active')]);
        $this->notifyControllerSlots($request->controller_slots ?? [], $previousSlots2, [
            'callsign'     => strtoupper($request->callsign),
            'name'         => $request->name,
            'frequency'    => $request->frequency,
            'description'  => $request->description,
            'announcement' => $request->announcement,
        ]);
        return back()->with('success', 'Schedule updated.');
    }

    public function destroyNetSchedule($id) {
        \App\Models\NetSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Schedule deleted.');
    }

    public function toggleNetSchedule($id) {
        $s = \App\Models\NetSchedule::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
        return back()->with('success', 'Schedule ' . ($s->is_active ? 'enabled' : 'disabled') . '.');
    }


    public function cloneNetSchedule(\Illuminate\Http\Request $request, $id) {
        $request->validate(['name'=>'required|string|max:100','days_of_week'=>'required|array|min:1']);
        $orig = \App\Models\NetSchedule::findOrFail($id);
        $clone = $orig->replicate();
        $clone->name = $request->name;
        $clone->days_of_week = $request->days_of_week;
        $clone->is_active = false;
        $clone->save();
        return back()->with('success', 'Schedule cloned successfully.');
    }

    /**
     * Send controller scheduled emails for any slots whose callsign matches a registered user.
     * Only sends if the callsign is newly added or changed vs $previousSlots.
     */
    private function notifyControllerSlots(array $slots, array $previousSlots, array $netInfo): void
    {
        $previousCallsigns = collect($previousSlots)->pluck('callsign')->map(fn($c) => strtoupper($c))->toArray();
        $groupName = \App\Helpers\RaynetSetting::groupName();

        foreach ($slots as $slot) {
            $cs = strtoupper(trim($slot['callsign'] ?? ''));
            if (!$cs) continue;

            // Only notify if this is a new/changed assignment
            if (in_array($cs, $previousCallsigns)) continue;

            $user = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();
            if (!$user || !$user->email) continue;

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\NetControllerScheduled(
                        controllerName:     $user->name,
                        controllerCallsign: $cs,
                        netCallsign:        $netInfo['callsign'] ?? '',
                        netName:            $netInfo['name'] ?? '',
                        frequency:          $netInfo['frequency'] ?? '',
                        slotStart:          $slot['start'] ?? '',
                        slotEnd:            $slot['end'] ?? '',
                        groupName:          $groupName,
                        description:        $netInfo['description'] ?? null,
                        announcement:       $netInfo['announcement'] ?? null,
                        netUrl:             url('/admin/events/net-status'),
                    )
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('NetControllerScheduled mail failed for ' . $cs . ': ' . $e->getMessage());
            }
        }
    }

    public function updateNetStatus(\Illuminate\Http\Request $request) {
        // Checkbox only submits when checked — handle explicitly
        $wasActive = \App\Models\Setting::get('net_active','0') === '1';
        $nowActive  = $request->has('net_active');
        \App\Models\Setting::set('net_active', $nowActive ? '1' : '0');

        // Auto-archive station log when net is turned off
        if ($wasActive && !$nowActive) {
            $stations = \App\Models\NetStationLog::orderBy('checked_in_at')->get();
            if ($stations->isNotEmpty()) {
                \App\Models\NetLogHistory::create([
                    'net_callsign'  => \App\Models\Setting::get('net_callsign',''),
                    'net_name'      => \App\Models\Setting::get('net_description',''),
                    'frequency'     => \App\Models\Setting::get('net_frequency',''),
                    'started_at'    => \App\Models\Setting::get('net_start_time') ? \Carbon\Carbon::today('Europe/London')->setTimeFromTimeString(\App\Models\Setting::get('net_start_time')) : null,
                    'ended_at'      => now(),
                    'stations'      => $stations->toArray(),
                    'station_count' => $stations->count(),
                ]);
                \App\Models\NetStationLog::truncate();
            }
        }

        $fields = ['net_callsign','net_frequency','net_band','net_description','net_announcement','net_priority','net_start_time','net_end_time'];
        \App\Models\Setting::set('net_station_logging', $request->has('net_station_logging') ? '1' : '0');
        foreach ($fields as $key) {
            \App\Models\Setting::set($key, $request->input($key, ''));
        }

        // Save controller time slots — submitted as a single JSON string
        $slotsJson = $request->input('net_controller_slots_json', '[]');
        $rawSlots  = json_decode($slotsJson, true);
        $slots     = is_array($rawSlots)
            ? array_values(array_filter($rawSlots, fn($s) => !empty($s['callsign'])))
            : [];
        // Notify any newly-assigned controllers
        $previousSlots = json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? [];
        \App\Models\Setting::set('net_controller_slots', json_encode($slots));

        $this->notifyControllerSlots($slots, $previousSlots, [
            'callsign'     => $request->input('net_callsign',''),
            'name'         => $request->input('net_description',''),
            'frequency'    => $request->input('net_frequency',''),
            'description'  => $request->input('net_description',''),
            'announcement' => $request->input('net_announcement',''),
        ]);

        // Derive net_controller from the currently active time slot only
        // If slots exist but none is active right now, store empty — endpoint computes live
        $nowTime = \Carbon\Carbon::now('Europe/London')->format('H:i');
        $activeCtrl = '';
        foreach ($slots as $slot) {
            if (!empty($slot['from']) && !empty($slot['to']) && $nowTime >= $slot['from'] && $nowTime < $slot['to']) {
                $activeCtrl = strtoupper($slot['callsign']);
                break;
            }
        }
        \App\Models\Setting::set('net_controller', $activeCtrl);

        return back()->with('success', 'Net status updated.');
    }


    public function stationLogQrzPhoto(\Illuminate\Http\Request $request)
    {
        $cs  = strtoupper(trim($request->query('callsign', '')));
        if (!$cs) abort(404);
        try {
            $qrz  = app(\App\Services\QrzService::class);
            $data = $qrz->lookup($cs);
            if ($data && !empty($data['image_url'])) {
                $response = \Illuminate\Support\Facades\Http::timeout(8)->get($data['image_url']);
                if ($response->successful()) {
                    $type = $response->header('Content-Type') ?: 'image/jpeg';
                    return response($response->body(), 200)->header('Content-Type', $type)
                        ->header('Cache-Control', 'public, max-age=3600');
                }
            }
        } catch (\Throwable $e) {}
        abort(404);
    }

    public function stationLogQrz(\Illuminate\Http\Request $request)
    {
        $cs = strtoupper(trim($request->query('callsign', '')));
        if (!$cs) return response()->json(['found' => false]);

        $registered = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->exists();
        $user       = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();

        if ($user) {
            return response()->json([
                'found'        => true,
                'source'       => 'local',
                'is_registered'=> true,
                'name'         => $user->name,
                'callsign'     => $cs,
                'licence_class'=> $user->licence_class ?? null,
                'photo'        => $user->avatar ?? null,
                'email'        => null, // never expose local emails to admin UI
                'location'     => null,
                'grid'         => null,
                'country'      => null,
                'qrz_url'      => 'https://www.qrz.com/db/' . $cs,
            ]);
        }

        try {
            $qrz  = app(\App\Services\QrzService::class);
            $data = $qrz->lookup($cs);
            if ($data && !empty($data['name'])) {
                return response()->json([
                    'found'        => true,
                    'source'       => 'qrz',
                    'is_registered'=> false,
                    'name'         => $data['name_fmt'] ?? $data['name'],
                    'callsign'     => $cs,
                    'licence_class'=> $data['licence_class'] ?? null,
                    'photo'        => $data['image_url'] ?? null,
                    'email'        => $data['email'] ?? null,
                    'location'     => trim(implode(', ', array_filter([$data['city'] ?? null, $data['country'] ?? null]))),
                    'grid'         => $data['grid'] ?? null,
                    'country'      => $data['country'] ?? null,
                    'qrz_url'      => $data['url'] ?? ('https://www.qrz.com/db/' . $cs),
                    'cq_zone'      => $data['cq_zone'] ?? null,
                    'dxcc'         => $data['dxcc'] ?? null,
                    'lotw'         => $data['lotw'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {}

        return response()->json(['found' => false, 'is_registered' => false, 'callsign' => $cs]);
    }

    public function stationLogIndex()
    {
        $stations = \App\Models\NetStationLog::orderByDesc('checked_in_at')->get();
        return response()->json($stations);
    }

    public function stationLogStore(\Illuminate\Http\Request $request)
    {
        // Skip logging-enabled check for offline replays (already authorised by Bearer token)
        $isOfflineReplay = $request->hasHeader('X-Offline-Replay') || $request->bearerToken();
        if (!$isOfflineReplay && \App\Models\Setting::get('net_station_logging','0') !== '1') {
            return response()->json(['success' => false, 'error' => 'Station logging is not enabled']);
        }
        $request->validate(['callsign' => 'required|string|max:20']);
        $cs   = strtoupper(trim($request->callsign));
        // Prevent duplicate callsign in same session
        if (\App\Models\NetStationLog::whereRaw('UPPER(callsign) = ?', [$cs])->exists()) {
            return response()->json(['success' => false, 'error' => $cs . ' is already logged on this net']);
        }
        $name = null; $qrzData = null; $photoUrl = null;
        $isRegistered = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->exists();
        $user = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();
        if ($user) { $name = $user->name; $photoUrl = $user->avatar ?? null; }
        // Always attempt QRZ lookup for full data regardless of local registration
        try {
            $qrz  = app(\App\Services\QrzService::class);
            $data = $qrz->lookup($cs);
            if ($data && !empty($data['name'])) {
                if (!$name) {
                    $name     = $data['name_fmt'] ?? $data['name'];
                }
                if (!$photoUrl) $photoUrl = $data['image_url'] ?? null;
                $qrzData = array_filter([
                    'licence_class' => $data['licence_class'] ?? null,
                    'location'      => trim(implode(', ', array_filter([$data['city'] ?? null, $data['country'] ?? null]))),
                    'grid'          => $data['grid'] ?? null,
                    'country'       => $data['country'] ?? null,
                    'email'         => $data['email'] ?? null,
                    'qrz_url'       => $data['url'] ?? ('https://www.qrz.com/db/' . $cs),
                    'cq_zone'       => $data['cq_zone'] ?? null,
                    'dxcc'          => $data['dxcc'] ?? null,
                    'lotw'          => $data['lotw'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {}
        $entry = \App\Models\NetStationLog::create([
            'callsign'      => $cs,
            'name'          => $name,
            'signal_report' => $request->signal_report ?? null,
            'notes'         => $request->notes ?? null,
            'qrz_data'      => $qrzData ? json_encode($qrzData) : null,
            'is_registered' => $isRegistered,
            'photo_url'     => $photoUrl,
        ]);
        return response()->json(['success' => true, 'entry' => $entry->load([])]);
    }

    public function stationLogDestroy(int $id)
    {
        \App\Models\NetStationLog::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function stationLogClear()
    {
        \App\Models\NetStationLog::truncate();
        return response()->json(['success' => true]);
    }

    public function stationLogArchiveAndClear()
    {
        $stations = \App\Models\NetStationLog::orderBy('checked_in_at')->get();
        if ($stations->isNotEmpty()) {
            \App\Models\NetLogHistory::create([
                'net_callsign'  => \App\Models\Setting::get('net_callsign',''),
                'net_name'      => \App\Models\Setting::get('net_description',''),
                'frequency'     => \App\Models\Setting::get('net_frequency',''),
                'started_at'    => \App\Models\Setting::get('net_start_time') ? \Carbon\Carbon::today('Europe/London')->setTimeFromTimeString(\App\Models\Setting::get('net_start_time')) : null,
                'ended_at'      => now(),
                'stations'      => $stations->toArray(),
                'station_count' => $stations->count(),
            ]);
        }
        \App\Models\NetStationLog::truncate();
        return response()->json(['success' => true, 'archived' => $stations->count()]);
    }

    public function netLogHistory()
    {
        $history = \App\Models\NetLogHistory::orderByDesc('ended_at')->get();
        return response()->json($history);
    }

    public function netLogHistoryShow(int $id)
    {
        $h = \App\Models\NetLogHistory::findOrFail($id);
        return response()->json($h);
    }

    public function netLogHistoryAdif(int $id)
    {
        $h        = \App\Models\NetLogHistory::findOrFail($id);
        $stations = is_array($h->stations) ? $h->stations : json_decode($h->stations, true) ?? [];
        $date     = $h->ended_at->format('Ymd');
        $time     = $h->ended_at->format('His');
        $myCall   = strtoupper($h->net_callsign ?: \App\Models\Setting::get('net_callsign','UNKNOWN'));
        $freq     = $h->frequency ?: '';
        // Strip MHz suffix if present, convert to MHz float
        $freqMhz  = preg_replace('/[^0-9.]/', '', $freq) ?: '145.500';

        $adif  = "ADIF Export — {$h->net_callsign} Net Log — {$h->ended_at->format('d M Y')}
";
        $adif .= "<ADIF_VER:5>3.1.0 <PROGRAMID:9>RAYNET-OS <EOH>

";

        foreach ($stations as $s) {
            $cs = strtoupper($s['callsign'] ?? '');
            if (!$cs) continue;
            $rst = $s['signal_report'] ?? '59';
            $adif .= "<CALL:" . strlen($cs) . ">{$cs} ";
            $adif .= "<QSO_DATE:8>{$date} ";
            $adif .= "<TIME_ON:6>{$time} ";
            $adif .= "<FREQ:" . strlen($freqMhz) . ">{$freqMhz} ";
            $adif .= "<MODE:2>FM ";
            $adif .= "<RST_SENT:" . strlen($rst) . ">{$rst} ";
            $adif .= "<RST_RCVD:" . strlen($rst) . ">{$rst} ";
            $adif .= "<STATION_CALLSIGN:" . strlen($myCall) . ">{$myCall} ";
            $adif .= "<COMMENT:7>NET LOG ";
            $adif .= "<EOR>
";
        }

        $filename = 'net-log-' . $h->ended_at->format('Y-m-d') . '-' . strtolower($myCall) . '.adi';
        return response($adif, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function netLogHistoryPdf(int $id)
    {
        $h         = \App\Models\NetLogHistory::findOrFail($id);
        $stations  = collect(is_array($h->stations) ? $h->stations : json_decode($h->stations, true) ?? []);
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $netName   = $h->net_callsign ?: 'NET';
        $date      = $h->ended_at->format('d M Y H:i');
        // Reuse station-log-pdf view but with history data
        return view('admin.events.station-log-pdf', [
            'stations'  => $stations->map(fn($s) => (object) array_merge($s, [
                'checked_in_at' => \Carbon\Carbon::parse($s['checked_in_at'] ?? $h->ended_at),
                'qrz_data'      => $s['qrz_data'] ?? null,
                'is_registered' => $s['is_registered'] ?? false,
                'signal_report' => $s['signal_report'] ?? null,
                'notes'         => $s['notes'] ?? null,
                'name'          => $s['name'] ?? null,
                'callsign'      => $s['callsign'] ?? '',
            ])),
            'groupName' => $groupName,
            'netName'   => $netName,
            'date'      => $date,
        ]);
    }

    public function netLogHistoryDestroy(int $id)
    {
        \App\Models\NetLogHistory::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function stationLogInvite(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'callsign' => 'required|string|max:20',
            'email'    => 'required|email',
            'name'     => 'nullable|string|max:100',
        ]);
        $cs        = strtoupper(trim($request->callsign));
        $name      = $request->name ?? $cs;
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $inviteUrl = url('/register');
        try {
            \Illuminate\Support\Facades\Mail::to($request->email)
                ->send(new \App\Mail\NetStationInvite(
                    toEmail:   $request->email,
                    callsign:  $cs,
                    name:      $name,
                    groupName: $groupName,
                    inviteUrl: $inviteUrl,
                    adminName: auth()->user()->name ?? null,
                ));
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function stationLogExportPdf()
    {
        $stations  = \App\Models\NetStationLog::orderBy('checked_in_at')->get();
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $netName   = \App\Models\Setting::get('net_callsign','NET');
        $date      = now()->format('d M Y H:i');
        return view('admin.events.station-log-pdf', compact('stations','groupName','netName','date'));
    }

}
