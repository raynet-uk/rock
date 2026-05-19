<?php
namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceDownload;
use App\Models\ResourceBookmark;
use App\Models\ResourceVersion;
use App\Models\ResourceFollower;
use App\Models\User;
use App\Notifications\ResourceApprovedNotification;
use App\Notifications\ResourceNewFileNotification;
use App\Notifications\ResourcePendingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user       = auth()->user();
        $sort       = $request->get('sort', 'date');
        $tag        = $request->get('tag');
        $driveFocus = $request->get('drive', 'public');

        $allowedVisibilities = \App\Models\Resource::visibilityForUser($user);

        $baseQuery = Resource::active()
            ->whereIn('visibility', $allowedVisibilities);

        if ($tag) {
            $baseQuery->where('tags', 'like', '%'.$tag.'%');
        }

        $sortFn = function($q) use ($sort) {
            return match($sort) {
                'name'      => $q->orderBy('title'),
                'size'      => $q->orderByDesc('file_size'),
                'downloads' => $q->orderByDesc('download_count'),
                default     => $q->orderByDesc('pinned')->orderByDesc('created_at'),
            };
        };

        // Group all accessible files by drive
        $drives = [];
        foreach ($allowedVisibilities as $vis) {
            $q = (clone $baseQuery)->where('visibility', $vis);
            $drives[$vis] = $sortFn($q)->get()->groupBy('category');
        }

        $bookmarked = null;
        if ($user) {
            $bookmarked = Resource::active()
                ->whereIn('visibility', $allowedVisibilities)
                ->whereHas('bookmarks', fn($q) => $q->where('user_id', $user->id))
                ->orderByDesc('created_at')->get();
        }

        $recent = Resource::active()
            ->whereIn('visibility', $allowedVisibilities)
            ->orderByDesc('created_at')->limit(6)->get();

        $pending = null;
        if ($user && $user->isAdmin()) {
            $pending = Resource::where('approved', false)->orderByDesc('created_at')->get();
        }

        $allTags = Resource::active()
            ->whereIn('visibility', $allowedVisibilities)
            ->whereNotNull('tags')->pluck('tags')
            ->flatMap(fn($t) => array_map('trim', explode(',', $t)))
            ->filter()->unique()->sort()->values();

        $followers = $user ? ResourceFollower::where('user_id', $user->id)->pluck('category')->toArray() : [];

        return view('resources.index', compact(
            'drives', 'pending', 'bookmarked', 'recent',
            'allTags', 'followers', 'sort', 'tag',
            'driveFocus', 'allowedVisibilities'
        ));
    }

    // ── Download ───────────────────────────────────────────────────────────

    public function download(Resource $resource)
    {
        if ($resource->visibility === 'members' && !auth()->check()) {
            abort(403, 'Login required.');
        }

        $path = $resource->storage_path;

        if (!Storage::exists($path)) {
            abort(404, 'File not found.');
        }

        ResourceDownload::create([
            'resource_id' => $resource->id,
            'user_id'     => auth()->id(),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        $resource->increment('download_count');

        return Storage::download($path, $resource->original_name);
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'file'        => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,txt,csv',
            'visibility'  => 'required|in:public,members',
            'category'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'tags'        => 'nullable|string|max:255',
            'version'     => 'nullable|string|max:50',
            'expires_at'  => 'nullable|date',
            'pinned'      => 'nullable|boolean',
        ]);

        $file     = $request->file('file');
        $filename = Str::uuid().'.'.  $file->getClientOriginalExtension();
        $folder   = $request->visibility === 'public' ? 'resources/public' : 'resources/members';

        Storage::putFileAs($folder, $file, $filename);

        $resource = Resource::create([
            'title'              => $request->title,
            'filename'           => $filename,
            'original_name'      => $file->getClientOriginalName(),
            'mime_type'          => $file->getMimeType(),
            'file_size'          => $file->getSize(),
            'visibility'         => $request->visibility,
            'category'           => $request->category,
            'description'        => $request->description,
            'tags'               => $request->tags,
            'version'            => $request->version,
            'expires_at'         => $request->expires_at,
            'pinned'             => $request->boolean('pinned'),
            'uploaded_by'        => auth()->user()->name.' ('.auth()->user()->callsign.')',
            'uploaded_by_user_id'=> auth()->id(),
            'source'             => 'manual',
            'approved'           => true,
            'approved_by'        => auth()->id(),
        ]);

        // Notify category followers
        $this->notifyFollowers($resource);

        return back()->with('success', 'Resource uploaded successfully.');
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'tags'        => 'nullable|string|max:255',
            'version'     => 'nullable|string|max:50',
            'expires_at'  => 'nullable|date',
            'visibility'  => 'required|in:public,members',
            'pinned'      => 'nullable|boolean',
        ]);

        $resource->update([
            'title'       => $request->title,
            'category'    => $request->category,
            'description' => $request->description,
            'tags'        => $request->tags,
            'version'     => $request->version,
            'expires_at'  => $request->expires_at ?: null,
            'visibility'  => $request->visibility,
            'pinned'      => $request->boolean('pinned'),
        ]);

        return back()->with('success', 'Resource updated.');
    }

    // ── New Version ────────────────────────────────────────────────────────

    public function newVersion(Request $request, Resource $resource)
    {
        $request->validate([
            'file'    => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,txt,csv',
            'version' => 'nullable|string|max:50',
            'notes'   => 'nullable|string|max:500',
        ]);

        // Archive current version
        ResourceVersion::create([
            'resource_id'        => $resource->id,
            'filename'           => $resource->filename,
            'original_name'      => $resource->original_name,
            'version'            => $resource->version,
            'file_size'          => $resource->file_size,
            'uploaded_by_user_id'=> $resource->uploaded_by_user_id,
            'notes'              => $request->notes,
        ]);

        // Save new file
        $file     = $request->file('file');
        $filename = Str::uuid().'.'.  $file->getClientOriginalExtension();
        $folder   = $resource->visibility === 'public' ? 'resources/public' : 'resources/members';
        Storage::putFileAs($folder, $file, $filename);

        $resource->update([
            'filename'      => $filename,
            'original_name'=> $file->getClientOriginalName(),
            'mime_type'    => $file->getMimeType(),
            'file_size'    => $file->getSize(),
            'version'      => $request->version,
        ]);

        return back()->with('success', 'New version uploaded.');
    }

    // ── Approve ────────────────────────────────────────────────────────────

    public function approve(Request $request, Resource $resource)
    {
        $request->validate([
            'category'   => 'nullable|string|max:100',
            'visibility' => 'nullable|in:public,members',
        ]);

        $resource->update([
            'approved'    => true,
            'approved_by' => auth()->id(),
            'category'    => $request->category ?: $resource->category,
            'visibility'  => $request->visibility ?: $resource->visibility,
        ]);

        // Notify the uploader if they are a user
        if ($resource->uploaded_by_user_id) {
            $uploader = User::find($resource->uploaded_by_user_id);
            if ($uploader) {
                $uploader->notify(new ResourceApprovedNotification($resource));
            }
        }

        // Notify followers
        $this->notifyFollowers($resource);

        return back()->with('success', 'Resource approved and uploader notified.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy(Resource $resource)
    {
        // Delete all version files
        foreach ($resource->versions as $version) {
            $vPath = ($resource->visibility === 'public' ? 'resources/public/' : 'resources/members/') . $version->filename;
            Storage::delete($vPath);
        }
        Storage::delete($resource->storage_path);
        $resource->versions()->delete();
        $resource->bookmarks()->delete();
        $resource->downloads()->delete();
        $resource->delete();

        return back()->with('success', 'Resource deleted.');
    }

    // ── Bookmark ───────────────────────────────────────────────────────────

    public function bookmark(Resource $resource)
    {
        $userId = auth()->id();
        $existing = ResourceBookmark::where('resource_id', $resource->id)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Bookmark removed.');
        }

        ResourceBookmark::create(['resource_id' => $resource->id, 'user_id' => $userId]);
        return back()->with('success', 'File bookmarked.');
    }

    // ── Follow Category ────────────────────────────────────────────────────

    public function followCategory(Request $request)
    {
        $request->validate(['category' => 'required|string|max:100']);
        $userId = auth()->id();

        $existing = ResourceFollower::where('user_id', $userId)->where('category', $request->category)->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Unfollowed category.');
        }

        ResourceFollower::create(['user_id' => $userId, 'category' => $request->category, 'visibility' => 'both']);
        return back()->with('success', 'Following category — you will be notified of new files.');
    }

    // ── Audit Log ──────────────────────────────────────────────────────────

    public function auditLog(Resource $resource)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
        $downloads = $resource->downloads()->with('user')->orderByDesc('created_at')->paginate(50);
        return view('resources.audit', compact('resource', 'downloads'));
    }

    // ── Preview ───────────────────────────────────────────────────────────────

    public function inline(Resource $resource)
    {
        if ($resource->visibility === 'members' && !auth()->check()) {
            abort(403);
        }
        $path = $resource->storage_path;
        if (!Storage::exists($path)) { abort(404); }
        $mime = Storage::mimeType($path) ?: 'application/octet-stream';
        $content = Storage::get($path);
        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $resource->original_name . '"');
    }

    public function preview(Resource $resource)
    {
        if ($resource->visibility === 'members' && !auth()->check()) {
            return redirect()->route('login')->with('message', 'Please log in to preview this file.');
        }

        $path = $resource->storage_path;

        if (!Storage::exists($path)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($resource->original_name, PATHINFO_EXTENSION));
        $officeExts = ['doc','docx','xls','xlsx','ppt','pptx'];
        $imageExts  = ['jpg','jpeg','png','gif'];
        $textExts   = ['txt','csv'];

        // For PDF and images — serve inline so browser renders them
        if ($ext === 'pdf' || in_array($ext, $imageExts)) {
            $mime = Storage::mimeType($path);
            $content = Storage::get($path);
            return response($content, 200)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'inline; filename="' . $resource->original_name . '"');
        }

        if (in_array($ext, $officeExts)) {
            $type = 'office';
            $downloadUrl = route('resources.download', $resource);
            $url = 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($downloadUrl);
        } elseif (in_array($ext, $textExts)) {
            $type = 'text';
            $url  = null;
        } else {
            $type = 'unsupported';
            $url  = null;
        }

        $textContent = in_array($ext, $textExts) ? Storage::get($path) : null;

        return view('resources.preview', compact('resource', 'type', 'url', 'textContent'));
    }

    // ── Expire Check (called by scheduler) ────────────────────────────────

    public function checkExpired()
    {
        $expired = Resource::where('approved', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $resource) {
            Log::info("Resource expired: {$resource->title} (ID {$resource->id})");
            // Just mark as unapproved — keeps the file but hides it
            $resource->update(['approved' => false]);
        }

        return $expired->count();
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    protected function notifyFollowers(Resource $resource): void
    {
        if (!$resource->category) return;

        $followers = ResourceFollower::where('category', $resource->category)->with('user')->get();

        foreach ($followers as $follower) {
            if (!$follower->user) continue;
            // Don't notify the person who uploaded it
            if ($follower->user_id === $resource->uploaded_by_user_id) continue;
            // Don't notify about members files to users who can't see them
            try {
                $follower->user->notify(new ResourceNewFileNotification($resource));
            } catch (\Exception $e) {
                Log::error("Failed to notify follower {$follower->user_id}: " . $e->getMessage());
            }
        }
    }
}
