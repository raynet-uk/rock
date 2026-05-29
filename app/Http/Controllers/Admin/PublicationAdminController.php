<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RaynetPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicationAdminController extends Controller
{
    public function index()
    {
        $news       = RaynetPublication::ofType('news')->orderByDesc('published_date')->get();
        $checkpoint = RaynetPublication::ofType('checkpoint')->orderByDesc('published_date')->get();
        return view('admin.publications.index', compact('news','checkpoint'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'           => ['required','in:news,checkpoint'],
            'title'          => ['required','string','max:200'],
            'edition'        => ['nullable','string','max:80'],
            'published_date' => ['required','date'],
            'description'    => ['nullable','string','max:1000'],
            'file'           => ['nullable','file','mimes:pdf','max:20480'],
            'cover_image'    => ['nullable','image','max:4096'],
            'external_url'   => ['nullable','url','max:500'],
            'is_current'     => ['nullable','boolean'],
        ]);

        $data = $request->only(['type','title','edition','published_date','description','external_url']);
        $data['is_current']   = $request->boolean('is_current');
        $data['is_published'] = true;

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('publications', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('publication-covers', 'public');
        }

        // If setting as current, unset others of same type
        if ($data['is_current']) {
            RaynetPublication::where('type', $data['type'])->update(['is_current' => false]);
        }

        RaynetPublication::create($data);
        return redirect()->route('admin.publications.index')->with('status', 'Publication added.');
    }

    public function setCurrent(RaynetPublication $publication)
    {
        RaynetPublication::where('type', $publication->type)->update(['is_current' => false]);
        $publication->update(['is_current' => true]);
        return back()->with('status', 'Current edition updated.');
    }

    public function destroy(RaynetPublication $publication)
    {
        if ($publication->file_path) Storage::disk('public')->delete($publication->file_path);
        if ($publication->cover_image_path) Storage::disk('public')->delete($publication->cover_image_path);
        $publication->delete();
        return back()->with('status', 'Publication deleted.');
    }
}
