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

        // All slots (all callsigns) to find prev/next neighbours
        $netInfo = [
            'callsign'  => \App\Models\Setting::get('net_callsign', ''),
            'frequency' => \App\Models\Setting::get('net_frequency', ''),
            'name'      => \App\Models\Setting::get('net_description', ''),
        ];
        $allSlots = array_map(
            fn($s) => array_merge($s, ['_net' => $netInfo, '_source' => 'live']),
            json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? []
        );
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

    public function earlyHandover(Request $request)
    {
        $user = $request->user();
        $slot = $request->get('_controller_slot');

        // Find next controller slot
        $allSlots = json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? [];
        usort($allSlots, fn($a, $b) => strcmp($a['from'] ?? '', $b['from'] ?? ''));

        $myFrom = $slot['from'] ?? '';
        $nextSlot = null;
        $foundMine = false;
        foreach ($allSlots as $s) {
            if ($foundMine && !empty($s['callsign'])) { $nextSlot = $s; break; }
            if (($s['from'] ?? '') === $myFrom) $foundMine = true;
        }

        // Determine recipient
        $isFallback = false;
        $recipientEmail = null;
        if ($nextSlot) {
            $nextUser = \App\Models\User::whereRaw('UPPER(callsign) = ?', [strtoupper($nextSlot['callsign'])])->first();
            if ($nextUser && $nextUser->email) {
                $recipientEmail = $nextUser->email;
            }
        }
        if (!$recipientEmail) {
            $recipientEmail = 'nathandillon1@me.com';
            $isFallback = true;
        }

        // Generate a token stored in cache for 2 hours
        $token = \Illuminate\Support\Str::random(48);
        $payload = [
            'requester_id' => $user->id,
            'requester_cs' => strtoupper($user->callsign ?? ''),
            'slot_from'    => $myFrom,
            'slot_to'      => $slot['to'] ?? '',
        ];
        \Illuminate\Support\Facades\Cache::put('handover_token_' . $token, $payload, now()->addHours(2));

        $acceptUrl = route('net-control.accept-handover', ['token' => $token]);
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $net = $slot['_net'] ?? [];

        \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(
            new \App\Mail\EarlyHandoverRequest(
                requesterName:     $user->name ?? $user->callsign,
                requesterCallsign: strtoupper($user->callsign ?? ''),
                requesterSlotFrom: $myFrom,
                requesterSlotTo:   $slot['to'] ?? '',
                netCallsign:       $net['callsign'] ?? \App\Models\Setting::get('net_callsign',''),
                frequency:         $net['frequency'] ?? \App\Models\Setting::get('net_frequency',''),
                groupName:         $groupName,
                acceptUrl:         $acceptUrl,
                isFallback:        $isFallback,
            )
        );

        return response()->json(['success' => true, 'fallback' => $isFallback]);
    }

    public function acceptHandover(Request $request, string $token)
    {
        $payload = \Illuminate\Support\Facades\Cache::pull('handover_token_' . $token);
        if (!$payload) {
            return response('This handover link has already been used or has expired.', 410)
                ->header('Content-Type', 'text/plain');
        }

        $nowTime = now('Europe/London')->format('H:i');

        // Update outgoing slot end; capture original end to advance incoming slot start
        $slots      = json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? [];
        $originalTo = null;
        foreach ($slots as &$s) {
            if (($s['from'] ?? '') === $payload['slot_from']) {
                $originalTo = $s['to'];
                $s['to']    = $nowTime;
            }
        }
        // Advance the incoming controller's slot start to now
        foreach ($slots as &$s) {
            if ($originalTo !== null && ($s['from'] ?? '') === $originalTo) {
                $s['from'] = $nowTime;
            }
        }
        unset($s);
        \App\Models\Setting::set('net_controller_slots', json_encode(array_values($slots)));

        // Block outgoing controller from re-entering the portal (persists 30 min)
        \Illuminate\Support\Facades\Cache::put('handover_done_' . $payload['requester_id'], [
            'cs'   => $payload['requester_cs'],
            'from' => $payload['slot_from'],
            'to'   => $nowTime,
        ], now()->addMinutes(30));

        // Signal requester's page to redirect
        \Illuminate\Support\Facades\Cache::put('handover_accepted_' . $payload['requester_id'], true, now()->addMinutes(10));

        return view('net-control.handover-accepted', [
            'requesterCallsign' => $payload['requester_cs'],
            'nowTime'           => $nowTime,
            'groupName'         => \App\Helpers\RaynetSetting::groupName(),
        ]);
    }

    public function netStatus(Request $request)
    {
        $user = $request->user();
        $accepted = \Illuminate\Support\Facades\Cache::get('handover_accepted_' . $user->id, false);
        return response()->json([
            'slot'             => $request->get('_controller_slot'),
            'stations'         => NetStationLog::count(),
            'now'              => now('Europe/London')->toISOString(),
            'handover_accepted' => $accepted,
        ]);
    }
}
