<?php
namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MemberAlbumController extends Controller {

    public function index() {
        $user    = auth()->user();
        $albums  = Album::where('user_id', $user->id)->withCount('photos')->orderByDesc('created_at')->get();
        $drafts  = Photo::where('user_id', $user->id)->whereNull('album_id')->where('status','draft')->orderByDesc('created_at')->get();
        return view('members.albums', compact('albums', 'drafts'));
    }

    public function store(Request $request) {
        $request->validate(['name' => ['required','string','max:200']]);
        $album = Album::create([
            'user_id'     => auth()->id(),
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => 'draft',
        ]);
        return response()->json(['success' => true, 'album' => $album]);
    }

    public function update(Request $request, Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        $request->validate([
            'name'        => ['required','string','max:200'],
            'description' => ['nullable','string','max:500'],
        ]);
        $album->update($request->only('name','description'));
        return response()->json(['success' => true]);
    }

    public function destroy(Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        abort_if($album->status !== 'draft', 403, 'Cannot delete a submitted album.');
        // Return photos to unassigned drafts
        Photo::where('album_id', $album->id)->update(['album_id' => null]);
        $album->delete();
        return back()->with('success', 'Album deleted.');
    }

    public function assignPhoto(Request $request, Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        $request->validate(['photo_id' => ['required','integer']]);
        $photo = Photo::where('id', $request->photo_id)->where('user_id', auth()->id())->firstOrFail();
        $photo->update(['album_id' => $album->id]);
        return response()->json(['success' => true]);
    }

    public function removePhoto(Request $request, Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        $request->validate(['photo_id' => ['required','integer']]);
        $photo = Photo::where('id', $request->photo_id)->where('user_id', auth()->id())->firstOrFail();
        $photo->update(['album_id' => null]);
        return response()->json(['success' => true]);
    }

    public function setCover(Request $request, Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        $album->update(['cover_photo_id' => $request->photo_id]);
        return response()->json(['success' => true]);
    }

    public function submit(Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        abort_if($album->photos()->count() === 0, 422, 'Album has no photos.');

        $user = auth()->user();
        $count = $album->photos()->count();

        // Update album and all its photos to pending
        $album->update(['status' => 'pending']);
        Photo::where('album_id', $album->id)->update(['status' => 'pending']);

        AuditLogger::log('album.submitted', $user, "Album '{$album->name}' submitted for approval ({$count} photos)", ['album_id' => $album->id]);

        // Send ONE notification email
        try {
            $approvers = \App\Models\User::permission('approve photos')->get()
                ->merge(\App\Models\User::role(['admin','super-admin'])->get())
                ->unique('id');
            $groupName  = \App\Helpers\RaynetSetting::groupName();
            $approveUrl = url('/members/photo-approval');
            foreach ($approvers as $approver) {
                if ($approver->email && $approver->id !== $user->id) {
                    Mail::send('emails.album-pending', [
                        'uploader'   => $user,
                        'album'      => $album,
                        'count'      => $count,
                        'groupName'  => $groupName,
                        'approveUrl' => $approveUrl,
                    ], function($m) use ($approver, $groupName) {
                        $m->to($approver->email, $approver->name)
                          ->subject("Album awaiting approval — {$groupName}");
                    });
                }
            }
        } catch (\Throwable $e) {}

        return back()->with('success', "Album '{$album->name}' submitted for approval.");
    }

    public function submitUnassigned() {
        $user   = auth()->user();
        $photos = Photo::where('user_id', $user->id)->whereNull('album_id')->where('status','draft')->get();
        if ($photos->isEmpty()) {
            return back()->with('error', 'No unassigned draft photos to submit.');
        }
        $count = $photos->count();
        Photo::where('user_id', $user->id)->whereNull('album_id')->where('status','draft')->update(['status' => 'pending']);

        AuditLogger::log('photos.submitted', $user, "Submitted {$count} unassigned photo(s) for approval", ['count' => $count]);

        try {
            $approvers = \App\Models\User::permission('approve photos')->get()
                ->merge(\App\Models\User::role(['admin','super-admin'])->get())
                ->unique('id');
            $groupName  = \App\Helpers\RaynetSetting::groupName();
            $approveUrl = url('/members/photo-approval');
            foreach ($approvers as $approver) {
                if ($approver->email && $approver->id !== $user->id) {
                    Mail::send('emails.photo-pending', [
                        'uploader'   => $user,
                        'count'      => $count,
                        'groupName'  => $groupName,
                        'approveUrl' => $approveUrl,
                    ], function($m) use ($approver, $groupName) {
                        $m->to($approver->email, $approver->name)
                          ->subject("Photos awaiting approval — {$groupName}");
                    });
                }
            }
        } catch (\Throwable $e) {}

        return back()->with('success', "{$count} photo(s) submitted for approval.");
    }
}
