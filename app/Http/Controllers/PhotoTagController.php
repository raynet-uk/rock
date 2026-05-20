<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoTag;
use App\Models\User;
use App\Services\QRZLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PhotoTagController extends Controller {

    public function store(Request $request, Photo $photo, QRZLookup $qrz) {
        abort_if($photo->user_id !== auth()->id(), 403);

        $request->validate([
            'callsign' => ['required', 'string', 'max:20'],
            'x_pct'    => ['required', 'numeric', 'min:0', 'max:100'],
            'y_pct'    => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $callsign = strtoupper(trim($request->callsign));

        // ONLY allow tagging registered site members
        $user = User::whereRaw('UPPER(callsign) = ?', [$callsign])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => $callsign . ' is not a registered member of this site. Only site members can be tagged.',
            ], 422);
        }

        // Check not already tagged
        if (PhotoTag::where('photo_id', $photo->id)->where('callsign', $callsign)->exists()) {
            return response()->json([
                'success' => false,
                'message' => $callsign . ' is already tagged in this photo.',
            ], 422);
        }

        // QRZ for name
        $qrzData = $qrz->lookup($callsign);
        $name    = $qrzData['name'] ?? $user->name;

        $tag = PhotoTag::create([
            'photo_id' => $photo->id,
            'user_id'  => $user->id,
            'callsign' => $callsign,
            'name'     => $name,
            'x_pct'    => $request->x_pct,
            'y_pct'    => $request->y_pct,
        ]);

        // Email the tagged member
        if ($user->email) {
            try {
                $groupName = \App\Helpers\RaynetSetting::groupName();
                Mail::send('emails.photo-tagged', [
                    'photo'     => $photo,
                    'tag'       => $tag,
                    'user'      => $user,
                    'tagger'    => auth()->user(),
                    'groupName' => $groupName,
                    'taggedUrl' => route('members.photos.tagged'),
                    'removeUrl' => route('members.photos.tags.remove-self', $tag),
                ], function($m) use ($user, $groupName) {
                    $m->to($user->email, $user->name)
                      ->subject("You've been tagged in a photo — {$groupName}");
                });
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success'  => true,
            'tag'      => $tag,
            'name'     => $name,
            'callsign' => $callsign,
            'is_member'=> true,
        ]);
    }

    public function destroy(Photo $photo, PhotoTag $tag) {
        abort_if($photo->user_id !== auth()->id(), 403);
        $tag->delete();
        return response()->json(['success' => true]);
    }

    public function myTagged() {
        $user   = auth()->user();
        $tags   = PhotoTag::where('user_id', $user->id)
                    ->with(['photo.user'])
                    ->orderByDesc('created_at')
                    ->get();
        $photos = $tags->map->photo->filter(fn($p) => $p && $p->status === 'approved')->unique('id');
        return view('pages.my-tagged-photos', compact('photos', 'tags'));
    }

    public function removeSelf(PhotoTag $tag) {
        abort_if($tag->user_id !== auth()->id(), 403);
        $tag->delete();
        return back()->with('success', 'Tag removed from photo.');
    }
}
