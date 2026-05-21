<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\PhotoTag;
use App\Helpers\AuditLogger;

class GalleryAdminController extends Controller {

    public function index(Request $request) {
        $filter = $request->get('status', 'pending');
        $photos = Photo::with(['user', 'tags', 'rejectedBy', 'approvedBy', 'publicApprovedBy'])
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

    public function revokeL1(Photo $photo) {
        $photo->update(['status' => 'pending', 'public_status' => 'pending', 'approved_by' => null, 'public_approved_by' => null, 'featured' => false]);
        AuditLogger::log('photo.revoked_l1', $photo->user, 'Photo #'.$photo->id.' L1 approval revoked by '.auth()->user()->name, ['photo_id' => $photo->id]);
        return back()->with('success', 'Photo returned to pending.');
    }

    public function revokeL2(Photo $photo) {
        $photo->update(['public_status' => 'pending', 'public_approved_by' => null, 'featured' => false]);
        AuditLogger::log('photo.revoked_l2', $photo->user, 'Photo #'.$photo->id.' L2 approval revoked by '.auth()->user()->name, ['photo_id' => $photo->id]);
        return back()->with('success', 'Photo set back to members-only.');
    }

    public function publicApprove(Photo $photo) {
        $l2approver = auth()->user();
        $photo->update(['public_status' => 'approved', 'public_approved_by' => $l2approver->id]);
        $photo->load('approvedBy');
        AuditLogger::log('photo.public_approved', $photo->user, 'Photo #' . $photo->id . ' approved for public gallery by ' . $l2approver->name, ['photo_id' => $photo->id]);
        if ($photo->user?->email) {
            try {
                $groupName  = \App\Helpers\RaynetSetting::groupName();
                $l1approver = $photo->approvedBy ?? $l2approver;
                Mail::send('emails.photo-public-approved', compact('photo', 'l1approver', 'l2approver', 'groupName'), function($m) use ($photo, $groupName) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo is now live on the public gallery — ' . $groupName);
                });
            } catch (\Throwable $e) {}
        }
        return back()->with('success', 'Photo approved for public gallery.');
    }

    public function approve(Photo $photo) {
        $wasApproved = $photo->isApproved();
        $photo->update(['status' => 'approved', 'approved_by' => auth()->id()]);
        AuditLogger::log('photo.approved', $photo->user, 'Photo #' . $photo->id . ' approved by admin ' . auth()->user()->name, ['photo_id' => $photo->id]);
        if (!$wasApproved && $photo->user?->email) {
            try {
                Mail::send('emails.photo-approved', ['photo' => $photo, 'user' => $photo->user, 'approver' => auth()->user(), 'groupName' => \App\Helpers\RaynetSetting::groupName()], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo has been approved — ' . \App\Helpers\RaynetSetting::groupName());
                });
            } catch (\Throwable $e) {}
        }
        return back()->with('success', 'Photo approved.');
    }

    public function reject(Request $request, Photo $photo) {
        $photo->update(['status' => 'rejected', 'public_status' => 'rejected', 'featured' => false, 'admin_notes' => $request->notes, 'rejected_by' => auth()->id(), 'rejected_at' => now()]);
        return back()->with('success', 'Photo rejected.');
    }

    public function feature(Photo $photo) {
        $photo->update(['featured' => !$photo->featured]);
        if ($photo->fresh()->featured && $photo->user?->email) {
            try {
                $photo->load(['approvedBy', 'publicApprovedBy']);
                Mail::send('emails.photo-featured', [
                    'photo'      => $photo,
                    'user'       => $photo->user,
                    'l1approver' => $photo->approvedBy,
                    'l2approver' => $photo->publicApprovedBy,
                    'featuredBy' => auth()->user(),
                    'groupName'  => \App\Helpers\RaynetSetting::groupName(),
                ], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo has been featured — ' . \App\Helpers\RaynetSetting::groupName());
                });
            } catch (\Throwable $e) {}
        }
        return back()->with('success', $photo->fresh()->featured ? 'Photo featured.' : 'Photo unfeatured.');
    }

    public function destroy(Photo $photo) {
        AuditLogger::log('photo.admin_deleted', $photo->user, 'Photo #' . $photo->id . ' deleted by admin ' . auth()->user()->name, ['photo_id' => $photo->id]);
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
