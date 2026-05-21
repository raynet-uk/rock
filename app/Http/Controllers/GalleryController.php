<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class GalleryController extends Controller {
    public function index(Request $request) {
        // Public photos: both L1 and L2 approved
        $publicPhotos = Photo::where('status', 'approved')
            ->where('public_status', 'approved')
            ->with(['user', 'tags'])
            ->orderByDesc('created_at')
            ->paginate(24, ['*'], 'public_page');

        // Members-only: L1 approved but NOT L2 approved yet
        $membersPhotos = null;
        if (auth()->check()) {
            $membersPhotos = Photo::where('status', 'approved')
                ->where('public_status', '!=', 'approved')
                ->with(['user', 'tags'])
                ->orderByDesc('created_at')
                ->paginate(24, ['*'], 'members_page');
        }

        return view('pages.gallery', compact('publicPhotos', 'membersPhotos'));
    }
}
