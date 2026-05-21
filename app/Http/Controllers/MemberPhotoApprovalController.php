<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MemberPhotoApprovalController extends Controller {

    public function index(Request $request) {
        $user = auth()->user();
        abort_unless(
            $user->hasPermissionTo('approve photos') || $user->isAdmin(),
            403
        );

        $filter = $request->get('filter', 'pending');
        $photos = Photo::with(['user','tags'])
            ->when($filter === 'pending',  fn($q) => $q->where('status', 'pending'))
            ->when($filter === 'approved', fn($q) => $q->where('status', 'approved'))
            ->when($filter === 'rejected', fn($q) => $q->where('status', 'rejected'))
            ->orderByDesc('created_at')
            ->paginate(20);

        $canFeature = $user->hasPermissionTo('feature photos') || $user->isAdmin();
        $canPublicApprove = $user->isAdmin();

        $counts = [
            'pending'  => Photo::where('status', 'pending')->count(),
            'approved' => Photo::where('status', 'approved')->count(),
            'rejected' => Photo::where('status', 'rejected')->count(),
        ];

        return view('members.photo-approval', compact('photos','filter','counts','canFeature','canPublicApprove'));
    }

    public function approve(Photo $photo) {
        $user = auth()->user();
        abort_unless($user->hasPermissionTo('approve photos') || $user->isAdmin(), 403);

        $wasApproved = $photo->isApproved();
        $photo->update(['status' => 'approved', 'approved_by' => $user->id]);

        AuditLogger::log('photo.approved', $photo->user, "Photo #{$photo->id} approved by {$user->name}", [
            'photo_id' => $photo->id,
            'filename' => $photo->original_filename,
        ]);

        if (!$wasApproved && $photo->user?->email) {
            try {
                Mail::send('emails.photo-approved', ['photo' => $photo, 'user' => $photo->user, 'approver' => $user, 'groupName' => \App\Helpers\RaynetSetting::groupName()], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo has been approved — ' . \App\Helpers\RaynetSetting::groupName());
                });

            // Notify admins for L2 approval
            $admins = \App\Models\User::role(['admin','super-admin'])->get();
            $groupName = \App\Helpers\RaynetSetting::groupName();
            $approveUrl = url('/members/photo-approval');
            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::send('emails.photo-l2-pending', [
                        'photo'      => $photo,
                        'uploader'   => $photo->user,
                        'approver'   => $user,
                        'groupName'  => $groupName,
                        'approveUrl' => $approveUrl,
                    ], function($m) use ($admin, $groupName) {
                        $m->to($admin->email, $admin->name)
                          ->subject('Photo awaiting public approval (L2) — ' . $groupName);
                    });
                }
            }
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Photo approved for members area.');
    }

    public function publicApprove(Photo $photo) {
        $l2approver = auth()->user();
        abort_unless($l2approver->isAdmin(), 403);

        $photo->update(['public_status' => 'approved', 'public_approved_by' => $l2approver->id]);
        $photo->load('approvedBy');

        AuditLogger::log('photo.public_approved', $photo->user, "Photo #{$photo->id} approved for public gallery by {$l2approver->name}", [
            'photo_id' => $photo->id,
        ]);

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

    public function reject(Request $request, Photo $photo) {
        $user = auth()->user();
        abort_unless($user->hasPermissionTo('approve photos') || $user->isAdmin(), 403);

        $photo->update([
            'status'        => 'rejected',
            'public_status' => 'rejected',
            'admin_notes'   => $request->notes,
            'rejected_by'   => $user->id,
            'rejected_at'   => now(),
        ]);

        AuditLogger::log('photo.rejected', $photo->user, "Photo #{$photo->id} rejected by {$user->name}", [
            'photo_id' => $photo->id,
            'reason'   => $request->notes,
        ]);

        if ($photo->user?->email) {
            try {
                Mail::send('emails.photo-rejected', [
                    'photo'     => $photo,
                    'user'      => $photo->user,
                    'reviewer'  => $user,
                    'reason'    => $request->notes,
                    'groupName' => \App\Helpers\RaynetSetting::groupName(),
                ], function($m) use ($photo) {
                    $m->to($photo->user->email, $photo->user->name)
                      ->subject('Your photo was not approved — ' . \App\Helpers\RaynetSetting::groupName());
                });
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Photo rejected.');
    }

    public function feature(Photo $photo) {
        $user = auth()->user();
        abort_unless($user->hasPermissionTo('feature photos') || $user->isAdmin(), 403);

        $wasFeatured = $photo->featured;
        $photo->update(['featured' => !$photo->featured]);

        AuditLogger::log('photo.featured', $photo->user,
            ($photo->fresh()->featured ? 'Featured' : 'Unfeatured') . " photo #{$photo->id} by {$user->name}",
            ['photo_id' => $photo->id]
        );

        if ($photo->fresh()->featured && !$wasFeatured && $photo->user?->email) {
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

        return back()->with('success', $photo->fresh()->featured ? 'Photo featured on homepage.' : 'Photo unfeatured.');
    }
}
