<?php

namespace App\Http\Controllers;

use App\Models\AlertStatus;
use App\Models\Event;
use App\Models\Operator;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MemberDashboardController extends Controller
{
    /**
     * Members’ landing page – my operational home screen.
     *
     * This pulls together:
     *  - The logged-in user's profile (name, callsign, role/level if we can find it)
     *  - Current alert status
     *  - A short list of upcoming events
     *  - Training links, resources, ops-board hooks
     *  - SignalSafe propagation brief (if present on disk)
     */
    public function __invoke(Request $request): View
    {
        // ------------------------------------------------------------------
        // 1. Who is logged in?
        // ------------------------------------------------------------------
        $user = $request->user(); // always present here because of auth middleware

        // Normalise callsign to upper-case for matching and display
        $userCallsign = $user?->callsign
            ? strtoupper($user->callsign)
            : null;

        // ------------------------------------------------------------------
        // 2. Try to find an Operator record that matches this user
        //    We match by callsign OR email so we can be flexible.
        // ------------------------------------------------------------------
        $operatorRecord = null;

        if ($user) {
            $operatorRecord = Operator::query()
                ->when($userCallsign, function ($query) use ($userCallsign) {
                    // Match by callsign (case-insensitive)
                    $query->whereRaw('upper(callsign) = ?', [$userCallsign]);
                })
                ->orWhere(function ($query) use ($user) {
                    // Or fall back to matching on email
                    $query->where('email', $user->email);
                })
                // If there are multiple matches, prefer admin-ish records first
                ->orderByDesc('is_admin')
                ->orderBy('name')
                ->first();
        }

        // ------------------------------------------------------------------
        // 3. Pull role colour from the Roles table (if the operator has a role)
        // ------------------------------------------------------------------
        $roleModel = null;

        if ($operatorRecord && $operatorRecord->role) {
            $roleModel = Role::where('name', $operatorRecord->role)->first();
        }

        // ------------------------------------------------------------------
        // 4. Build the operator array used by the pill + cards
        // ------------------------------------------------------------------
        $operator = [
            // Prefer the Laravel user name, fall back to operator record
            'name'        => $user?->name ?? $operatorRecord?->name ?? 'Operator',

            // Callsign: prefer the user’s callsign, otherwise the operator’s
            'callsign'    => $userCallsign
                ?? ($operatorRecord?->callsign ? strtoupper($operatorRecord->callsign) : ''),

            // Role: if we have an Operator role, use it, otherwise generic "Member"
            'role'        => $operatorRecord?->role ?? 'Member',

            // Level & status can legitimately be null – the view will only show them if set
            'level'       => $operatorRecord?->level,
            'status'      => $operatorRecord?->status,

            // Optional role colour (used for the tiny role badge tint)
            'role_colour' => $roleModel?->colour,
        ];

        // ------------------------------------------------------------------
        // 5. Load current alert status (for the green Level 5 card etc.)
        // ------------------------------------------------------------------
        /** @var AlertStatus|null $alertStatus */
        $alertStatus = AlertStatus::query()->first();

        // ------------------------------------------------------------------
        // 6. Short list of upcoming events so the members page feels “alive”
        // ------------------------------------------------------------------
        $upcoming = Event::with('type')
            ->where('starts_at', '>=', Carbon::today()->startOfDay())
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        // ------------------------------------------------------------------
        // 7. SignalSafe propagation brief – read from JSON if available
        // ------------------------------------------------------------------
        $condx = null;
        $condxPath = public_path('Condx/propagation-brief.json');

        if (is_file($condxPath)) {
            try {
                $json = file_get_contents($condxPath);

                if ($json !== false) {
                    /** @var array|null $decoded */
                    $decoded = json_decode($json, true);

                    if (is_array($decoded)) {
                        $condx = $decoded;
                    }
                }
            } catch (\Throwable $e) {
                // If anything goes wrong, just leave $condx as null;
                // the view already has a graceful "not available" message.
            }
        }

        // ------------------------------------------------------------------
        // 8. Static links – training and reference resources
        // ------------------------------------------------------------------
        $trainingLinks = [
            [
                'label' => 'Level 0–2 operator pathway',
                'url'   => 'https://raynet-training.uk/pathways/level-0-2',
            ],
            [
                'label' => 'Specialist modules (NVIS, Power, Digital)',
                'url'   => 'https://raynet-training.uk/modules',
            ],
            [
                'label' => 'Assessment & sign-off forms',
                'url'   => 'https://raynet-training.uk/forms',
            ],
        ];

        $resources = [
            [
                'label' => 'Merseyside VHF/UHF frequency plan (PDF)',
                'url'   => '/docs/frequency-plan-merseyside.pdf',
            ],
            [
                'label' => 'Local SOPs and checklists (PDF bundle)',
                'url'   => '/docs/liverpool-raynet-sops.pdf',
            ],
            [
                'label' => 'Net times & regular skeds',
                'url'   => '/docs/net-times.pdf',
            ],
            [
                'label' => 'RAYNET-UK members’ area',
                'url'   => 'https://members.raynet-uk.net/',
            ],
        ];

        // Hooks into self-hosted systems – these URLs can change later
        $opsSystems = [
            'ops_board_url' => 'https://ops.liverpool.ray-net.uk',     // placeholder
            'backend_url'   => 'https://backend.liverpool.ray-net.uk', // placeholder
        ];

        // ------------------------------------------------------------------
        // 9. Return the view with everything neatly bundled
        // ------------------------------------------------------------------
        $myPhotos = \App\Models\Photo::where('user_id', $user->id)->orderByDesc('created_at')->get();

        return view('pages.members', compact(
            'myPhotos',
            'upcoming',
            'operator',
            'trainingLinks',
            'resources',
            'opsSystems',
            'alertStatus',
            'condx',
        ));
    }
}