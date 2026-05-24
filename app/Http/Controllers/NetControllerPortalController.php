<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\NetControllerAccess;
use App\Models\NetStationLog;
use App\Models\Setting;

class NetControllerPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $slot = $request->get('_controller_slot');
        $cs   = strtoupper($user->callsign ?? '');

        // All slots for this callsign to find prev/next
        $allSlots = NetControllerAccess::allSlotsWithMeta($cs);
        usort($allSlots, fn($a, $b) => strcmp($a['from'] ?? '', $b['from'] ?? ''));

        $prevSlot = null;
        $nextSlot = null;
        foreach ($allSlots as $i => $s) {
            if (($s['from'] ?? '') === ($slot['from'] ?? '') && ($s['to'] ?? '') === ($slot['to'] ?? '')) {
                $prevSlot = $allSlots[$i - 1] ?? null;
                $nextSlot = $allSlots[$i + 1] ?? null;
                break;
            }
        }

        $net = $slot['_net'] ?? [
            'callsign'  => Setting::get('net_callsign', ''),
            'frequency' => Setting::get('net_frequency', ''),
            'name'      => Setting::get('net_description', ''),
        ];

        $groupName  = \App\Helpers\RaynetSetting::groupName();
        $windowMins = NetControllerAccess::WINDOW_MINUTES;

        return view('net-control.portal', compact(
            'user', 'slot', 'prevSlot', 'nextSlot', 'net', 'groupName', 'windowMins'
        ));
    }

    public function stationLog(Request $request)
    {
        $stations = NetStationLog::orderByDesc('checked_in_at')->get();
        return response()->json($stations);
    }

    public function logStation(Request $request)
    {
        $slot = $request->get('_controller_slot');
        $isOfflineReplay = $request->hasHeader('X-Offline-Replay');
        if (!$isOfflineReplay && !($slot['can_log'] ?? false)) {
            return response()->json(['success' => false, 'error' => 'Not your control slot']);
        }

        $request->validate(['callsign' => 'required|string|max:20']);
        $cs = strtoupper(trim($request->callsign));

        if (NetStationLog::whereRaw('UPPER(callsign) = ?', [$cs])->exists()) {
            return response()->json(['success' => false, 'error' => $cs . ' is already logged']);
        }

        $name = null; $qrzData = null; $photoUrl = null;
        $isRegistered = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->exists();
        $user = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();
        if ($user) { $name = $user->name; $photoUrl = $user->avatar ?? null; }

        try {
            $qrz  = app(\App\Services\QrzService::class);
            $data = $qrz->lookup($cs);
            if ($data && !empty($data['name'])) {
                if (!$name) $name = $data['name_fmt'] ?? $data['name'];
                if (!$photoUrl) $photoUrl = $data['image_url'] ?? null;
                $qrzData = array_filter([
                    'licence_class' => $data['licence_class'] ?? null,
                    'location'      => trim(implode(', ', array_filter([$data['city'] ?? null, $data['country'] ?? null]))),
                    'grid'          => $data['grid'] ?? null,
                    'email'         => $data['email'] ?? null,
                    'qrz_url'       => $data['url'] ?? ('https://www.qrz.com/db/' . $cs),
                ]);
            }
        } catch (\Throwable $e) {}

        $entry = NetStationLog::create([
            'callsign'      => $cs,
            'name'          => $name,
            'signal_report' => $request->signal_report ?? null,
            'notes'         => $request->notes ?? null,
            'qrz_data'      => $qrzData ? json_encode($qrzData) : null,
            'is_registered' => $isRegistered,
            'photo_url'     => $photoUrl,
        ]);

        return response()->json(['success' => true, 'entry' => $entry]);
    }

    public function netStatus(Request $request)
    {
        return response()->json([
            'slot'     => $request->get('_controller_slot'),
            'stations' => NetStationLog::count(),
            'now'      => now('Europe/London')->toISOString(),
        ]);
    }
}
