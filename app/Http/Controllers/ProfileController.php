<?php
namespace App\Http\Controllers;
use App\Helpers\AuditLogger;
use App\Services\QrzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $completedCourseIds = collect($user->completed_course_ids ?? []);
        return view('profile.edit', [
            'user'               => $user,
            'completedCourseIds' => $completedCourseIds,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'callsign' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[A-Z0-9\/]{3,10}$/i',
                'unique:users,callsign,' . $user->id,
                'unique:users,pending_callsign,' . $user->id,
            ],
            'dmr_id'   => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]+$/',
            ],
            'telegram_chat_id' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^-?[0-9]+$/',
            ],
        ], [
            'callsign.regex'  => 'Callsign must be 3–10 characters of letters, numbers or "/".',
            'callsign.unique' => 'That callsign is already in use or pending approval by another account.',
            'dmr_id.regex'    => 'DMR ID must contain numbers only.',
        ]);

        $oldName     = $user->name;
        $oldDmrId    = $user->dmr_id;
        $oldCallsign = $user->callsign ? strtoupper(trim($user->callsign)) : null;

        $user->name   = $request->name;
        $user->dmr_id          = $request->dmr_id ? trim($request->dmr_id) : null;
        $user->telegram_chat_id = $request->telegram_chat_id ? trim($request->telegram_chat_id) : null;

        $submitted       = $request->callsign ? strtoupper(trim($request->callsign)) : null;
        $statusMessage   = 'Profile updated successfully.';
        $callsignPending = false;

        if ($submitted !== $oldCallsign) {
            $user->pending_callsign = $submitted;
            $callsignPending = true;
            $statusMessage = $submitted
                ? 'Profile updated. Your callsign change to ' . $submitted . ' is awaiting admin approval.'
                : 'Profile updated. Your request to remove your callsign is awaiting admin approval.';
        }

        $user->save();

        $old = [];
        $new = [];
        if ($oldName !== $user->name)   { $old['name']   = $oldName;   $new['name']   = $user->name; }
        if ($oldDmrId !== $user->dmr_id){ $old['dmr_id'] = $oldDmrId;  $new['dmr_id'] = $user->dmr_id; }
        if ($callsignPending) {
            $old['callsign']         = $oldCallsign;
            $new['pending_callsign'] = $submitted;
        }

        AuditLogger::log(
            'profile.updated',
            $user,
            "Member updated their own profile: {$user->name}",
            $old ?: [],
            $new ?: []
        );

        if ($callsignPending) {
            AuditLogger::log(
                'profile.callsign_change_requested',
                $user,
                "Callsign change requested by {$user->name}: {$oldCallsign} → " . ($submitted ?? 'removed'),
                ['callsign' => $oldCallsign],
                ['pending_callsign' => $submitted]
            );
        }

        return redirect()->route('profile.edit')->with('status', $statusMessage);
    }

    /**
     * Proxy QRZ.com callsign lookup — credentials stay server-side.
     */
    public function qrzLookup(string $callsign, QrzService $qrz): JsonResponse
    {
        $callsign = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $callsign));

        if (strlen($callsign) < 3 || strlen($callsign) > 10) {
            return response()->json(['found' => false, 'error' => 'Invalid callsign format'], 422);
        }

        $reason = '';
        $data   = $qrz->lookup($callsign, $reason);

        if (!$data) {
            // Distinguish a clean "not found" from a service error so the
            // frontend can show the right message.
            $isNotFound = $reason === 'not_found' || $reason === '';
            return response()->json([
                'found'    => false,
                'reason'   => $reason,
                'service_error' => !$isNotFound,
            ]);
        }

        return response()->json(['found' => true, 'data' => $data]);
    }
   public function importQrzAvatar(Request $request)
{
    $request->validate([
        'qrz_image_url' => ['required', 'url', 'max:2048'],
    ]);

    $url  = $request->input('qrz_image_url');
    $host = parse_url($url, PHP_URL_HOST);

    if (!str_ends_with($host, 'qrz.com') && !str_ends_with($host, 'qrzcdn.com')) {
        return back()->withErrors(['avatar' => 'Invalid image source.']);
    }

    try {
        $binary = \Illuminate\Support\Facades\Http::timeout(10)->get($url)->body();
    } catch (\Exception $e) {
        return back()->withErrors(['avatar' => 'Could not fetch image from QRZ.com. Please upload manually.']);
    }

    if (!$binary || strlen($binary) > 5 * 1024 * 1024) {
        return back()->withErrors(['avatar' => 'Image too large or corrupt.']);
    }

    $user = $request->user();

    if ($user->avatar) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
    }

    $filename = 'avatars/' . \Illuminate\Support\Str::uuid() . '.jpg';
    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $binary);

    $user->avatar = $filename;
    $user->save();

    return redirect()->route('profile.edit')->with('status', 'Profile photo imported from QRZ.com.');
}
}