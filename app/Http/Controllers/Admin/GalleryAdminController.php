<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\PhotoTag;

class GalleryAdminController extends Controller {

    public function index(Request $request) {
        $filter = $request->get('status', 'pending');
        $photos = Photo::with(['user', 'tags'])
            ->when($filter !== 'all', fn($q) => $q->where('status', $filter))
            ->orderByDesc('created_at')
            ->paginate(30);
        $counts = [
            'pending'  => Photo::where('status', 'pending')->count(),
            'approved' => Photo::where('status', 'approved')->count(),
            'rejected' => Photo::where('status', 'rejected')->count(),
            'featured' => Photo::where('featured', true)->count(),
        ];
        return view('admin.gallery.index', compact('photos', 'filter', 'counts'));
    }

    public function approve(Photo $photo) {
        $wasApproved = $photo->isApproved();
        $photo->update(['status' => 'approved']);
        if (!$wasApproved && $photo->user?->email) {
            try {
                Mail::send('emails.photo-approved', ['photo' => $photo, 'user' => $photo->user], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo has been approved — ' . \App\Helpers\RaynetSetting::groupName());
                });
            } catch (\Throwable $e) {}
        }
        return back()->with('success', 'Photo approved.');
    }

    public function reject(Request $request, Photo $photo) {
        $photo->update(['status' => 'rejected', 'featured' => false, 'admin_notes' => $request->notes]);
        return back()->with('success', 'Photo rejected.');
    }

    public function feature(Photo $photo) {
        $photo->update(['featured' => !$photo->featured]);
        if ($photo->fresh()->featured && $photo->user?->email) {
            try {
                Mail::send('emails.photo-featured', ['photo' => $photo, 'user' => $photo->user], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo has been featured — ' . \App\Helpers\RaynetSetting::groupName());
                });
            } catch (\Throwable $e) {}
        }
        return back()->with('success', $photo->fresh()->featured ? 'Photo featured.' : 'Photo unfeatured.');
    }

    public function destroy(Photo $photo) {
        Storage::disk('public')->delete('gallery/' . $photo->filename);
        Storage::disk('public')->delete('gallery/thumbs/' . $photo->filename);
        $photo->delete();
        return back()->with('success', 'Photo deleted.');
    }

    public function removeTag(Photo $photo, PhotoTag $tag) {
        $tag->delete();
        return back()->with('success', 'Tag removed.');
    }

    public function updateLocation(Request $request, Photo $photo) {
        $request->validate(['location' => ['nullable','string','max:200']]);
        $photo->update([
            'location' => $request->location,
            'exif_data' => $request->boolean('redact_location') ? null : $photo->exif_data,
        ]);
        return back()->with('success', 'Location updated.');
    }

    public function update(Request $request, Photo $photo) {
        $request->validate([
            'caption'     => ['nullable', 'string', 'max:500'],
            'location'    => ['nullable', 'string', 'max:200'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);
        $photo->update($request->only('caption', 'location', 'admin_notes'));
        return back()->with('success', 'Photo updated.');
    }
}
