<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\AlertStatus;

class ReportApiController extends Controller
{
    public function index(Request $request)
    {
        $key    = $request->header('X-Licence-Key');
        $stored = Setting::where('key', 'cms_licence_key')->value('value') 
                  ?? Setting::where('key', 'licence_key')->value('value');

        if (!$key || $key !== $stored) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }

        $now       = now();
        $yearStart = $now->copy()->startOfYear();

        // --- Alert ---
        $alert       = AlertStatus::current();
        $alertLevel  = $alert?->level ?? 5;
        $alertConfig = AlertStatus::config()[$alertLevel] ?? AlertStatus::config()[5];
        $alertStatus = match($alertLevel) {
            1 => 'red', 2 => 'orange', 3 => 'amber', 4 => 'purple', default => 'green',
        };

        // --- Volunteer hours this year per user ---
        $hoursPerUser = ActivityLog::where('event_date', '>=', $yearStart)
            ->selectRaw('user_id, SUM(hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        // --- Members ---
        $members    = User::all();
        $memberList = $members->map(fn($m) => [
            'name'                      => $m->name,
            'callsign'                  => $m->callsign ?? null,
            'email'                     => $m->email,
            'is_admin'                  => $m->hasRole('admin'),
            'is_super_admin'            => $m->hasRole('super-admin'),
            'volunteer_hours_this_year' => round((float)($hoursPerUser[$m->id] ?? 0), 1),
            'attended_event_this_year'  => $m->attended_event_this_year ?? false,
            'joined_at'                 => $m->created_at?->format('d M Y'),
        ]);

        // --- Events ---
        $events    = Event::with(['rsvps', 'documents', 'type', 'assignments'])
                         ->orderBy('starts_at')
                         ->get();

        $eventList = $events->map(fn($e) => [
            'title'        => $e->title,
            'description'  => $e->description ?? null,
            'location'     => $e->location ?? null,
            'lat'          => $e->event_lat ?? null,
            'lng'          => $e->event_lng ?? null,
            'starts_at'    => $e->starts_at?->toIso8601String(),
            'ends_at'      => $e->ends_at?->toIso8601String(),
            'type'         => $e->type?->name ?? $e->category ?? null,
            'type_colour'  => $e->type?->colour ?? '#003366',
            'is_past'      => $e->starts_at < $now,
            'is_private'   => $e->is_private ?? false,
            'is_public'    => $e->is_public ?? true,
            'crew_count'   => $e->assignments->count(),
            'has_polygon'  => $e->hasPolygon(),
            'has_route'    => $e->hasRoute(),
            'has_pois'     => $e->hasPois(),
            'polygon'      => $e->event_polygon,
            'polygon_name' => $e->event_polygon_name,
            'route'        => $e->event_route,
            'route_name'   => $e->event_route_name,
            'pois'         => $e->event_pois,
            'doc_count'    => $e->documents->count(),
            'rsvp_yes'     => $e->rsvps->where('status', 'yes')->count(),
            'rsvp_maybe'   => $e->rsvps->where('status', 'maybe')->count(),
            'rsvp_no'      => $e->rsvps->where('status', 'no')->count(),
            'rsvp_total'   => $e->rsvps->count(),
            'crew'         => $e->assignments->map(fn($a) => [
                'name'      => $a->user?->name ?? '—',
                'callsign'  => $a->user?->callsign ?? '—',
                'role'      => $a->role ?? '—',
                'location'  => $a->location ?? '—',
                'frequency' => $a->frequency ?? '—',
                'mode'      => $a->mode ?? null,
                'lat'       => $a->lat ?? null,
                'lng'       => $a->lng ?? null,
            ]),
        ]);

        // --- Training ---
        $courses = Course::withCount('enrollments')->get();

        // --- Group info ---
        $info = [
            'name'     => Setting::get('group_name'),
            'number'   => Setting::get('group_number'),
            'callsign' => Setting::get('group_callsign'),
            'zone'     => Setting::get('raynet_zone'),
            'gc_name'  => Setting::get('gc_name'),
            'gc_email' => Setting::get('gc_email'),
        ];

        $totalHours = round((float) ActivityLog::where('event_date', '>=', $yearStart)->sum('hours'), 1);

        return response()->json([
            'group_info'     => $info,
            'alert_status'   => $alertStatus,
            'alert_level'    => $alertLevel,
            'alert_title'    => $alertConfig['title'],
            'alert_colour'   => $alertConfig['colour'],
            'alert_headline' => $alert?->headline,
            'alert_message'  => $alert?->message,
            'cms_version'    => config('app.version', '1.0.0'),
            'members' => [
                'total'                     => $members->count(),
                'active'                    => $members->count(),
                'pending'                   => 0,
                'volunteer_hours_this_year' => $totalHours,
                'list'                      => $memberList,
                'group_info'                => $info,
            ],
            'events' => [
                'total_this_year' => $events->where('starts_at', '>=', $yearStart)->count(),
                'upcoming'        => $events->where('starts_at', '>=', $now)->count(),
                'past_this_year'  => $events->where('starts_at', '<', $now)->where('starts_at', '>=', $yearStart)->count(),
                'list'            => $eventList->values(),
            ],
            'training' => [
                'completions_this_year' => CourseEnrollment::where('completed_at', '>=', $yearStart)->count(),
                'courses'               => $courses->map(fn($c) => [
                    'title'     => $c->title,
                    'enrolled'  => $c->enrollments_count,
                    'completed' => $c->enrollments()->whereNotNull('completed_at')->count(),
                ]),
            ],
        ]);
    }
}
