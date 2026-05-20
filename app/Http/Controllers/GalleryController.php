<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class GalleryController extends Controller {
    public function index(Request $request) {
        $photos = Photo::where('status', 'approved')
            ->with(['user', 'tags'])
            ->orderByDesc('created_at')
            ->paginate(24);
        return view('pages.gallery', compact('photos'));
    }
}
