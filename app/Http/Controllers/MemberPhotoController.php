<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MemberPhotoController extends Controller {

    public function store(Request $request) {
        $request->validate([
            'photos'   => ['required', 'array', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:32768'],
            'caption'  => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:200'],
            'taken_at' => ['nullable', 'date'],
            'lat'      => ['nullable', 'numeric', 'between:-90,90'],
            'lng'      => ['nullable', 'numeric', 'between:-180,180'],
            'consent'  => ['required', 'accepted'],
        ]);

        $user  = auth()->user();
        $files = $request->file('photos') ?? [];
        $uploaded = 0;
        Log::info('Photo upload attempt', ['files_count' => count($files), 'user' => $user->id]);

        if (empty($files)) {
            return response()->json(['success' => false, 'message' => 'No files received.']);
        }

        foreach ($files as $file) {
            $ext  = strtolower($file->getClientOriginalExtension());
            $name = Str::uuid() . '.jpg';

            // Extract EXIF
            $exifData = [];
            try {
                if (function_exists('exif_read_data') && in_array($ext, ['jpg','jpeg'])) {
                    $raw = @exif_read_data($file->getRealPath(), 'ANY_TAG', false);
                    if ($raw) {
                        $keep = ['Make','Model','DateTime','DateTimeOriginal','ExposureTime',
                                 'FNumber','ISOSpeedRatings','FocalLength','Flash',
                                 'GPSLatitude','GPSLongitude','GPSAltitude','Software',
                                 'Orientation','ImageWidth','ImageLength'];
                        foreach ($keep as $k) {
                            if (isset($raw[$k])) {
                                $val = $raw[$k];
                                if (is_array($val)) {
                                    $val = implode(', ', array_map(fn($v) => is_string($v) ? $v : (string)$v, $val));
                                }
                                $exifData[$k] = mb_convert_encoding((string)$val, 'UTF-8', 'UTF-8');
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}

            ini_set('memory_limit', '256M');

            try {
                $sourcePath = $file->getRealPath();
                $info = getimagesize($sourcePath);
                $mime = $info['mime'] ?? 'image/jpeg';

                $src = match($mime) {
                    'image/png'  => imagecreatefrompng($sourcePath),
                    'image/webp' => imagecreatefromwebp($sourcePath),
                    default      => imagecreatefromjpeg($sourcePath),
                };

                if ($src) {
                    $w = imagesx($src); $h = imagesy($src);
                    $maxW = 2000;
                    if ($w > $maxW) {
                        $newH = intval($h * $maxW / $w);
                        $resized = imagecreatetruecolor($maxW, $newH);
                        imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxW, $newH, $w, $h);
                        imagedestroy($src);
                        $src = $resized; $w = $maxW; $h = $newH;
                    }
                    $mainPath = storage_path('app/public/gallery/' . $name);
                    imagejpeg($src, $mainPath, 85);
                    $tw = 400; $th = intval($h * $tw / $w);
                    $thumb = imagecreatetruecolor($tw, $th);
                    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
                    $thumbPath = storage_path('app/public/gallery/thumbs/' . $name);
                    imagejpeg($thumb, $thumbPath, 80);
                    imagedestroy($src); imagedestroy($thumb);
                } else {
                    $file->storeAs('gallery', $name, 'public');
                }
            } catch (\Throwable $e) {
                $file->storeAs('gallery', $name, 'public');
            }

            $takenAt = $request->taken_at;
            if (!$takenAt && !empty($exifData['DateTimeOriginal'])) {
                try {
                    $takenAt = \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $exifData['DateTimeOriginal'])->format('Y-m-d');
                } catch (\Throwable $e) {}
            }

            Photo::create([
                'user_id'           => $user->id,
                'filename'          => $name,
                'original_filename' => $file->getClientOriginalName(),
                'caption'           => $request->caption,
                'location'          => $request->location,
                'lat'               => $request->lat ?: null,
                'lng'               => $request->lng ?: null,
                'taken_at'          => $takenAt,
                'callsign'          => $user->callsign,
                'consent'           => true,
                'status'            => 'draft',
                'exif_data'         => !empty($exifData) ? json_encode($exifData) : null,
            ]);

            $uploaded++;
        }

        AuditLogger::log('photo.uploaded', $user, "Uploaded {$uploaded} photo(s) as draft", ['count' => $uploaded]);

        return response()->json(['success' => true, 'message' => $uploaded . ' photo' . ($uploaded > 1 ? 's' : '') . ' saved as draft. Review and submit when ready.']);
    }

    public function getUrl(Photo $photo) {
        abort_if($photo->user_id !== auth()->id(), 403);
        return response()->json(['url' => $photo->url()]);
    }

    public function rotate(Request $request, Photo $photo) {
        abort_if($photo->user_id !== auth()->id(), 403);
        $degrees   = (int) $request->input('degrees', 90);
        $path      = storage_path('app/public/gallery/' . $photo->filename);
        $thumbPath = storage_path('app/public/gallery/thumbs/' . $photo->filename);
        try {
            ini_set('memory_limit', '256M');
            foreach ([$path, $thumbPath] as $file) {
                if (!file_exists($file)) continue;
                $src     = imagecreatefromjpeg($file);
                $rotated = imagerotate($src, -$degrees, 0);
                imagejpeg($rotated, $file, 90);
                imagedestroy($src); imagedestroy($rotated);
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function submitForApproval(Photo $photo) {
        abort_if($photo->user_id !== auth()->id(), 403);
        abort_if($photo->status !== 'draft', 422, 'Photo already submitted.');
        $user = auth()->user();
        $photo->update(['status' => 'pending']);
        AuditLogger::log('photo.submitted', $user, "Photo #{$photo->id} submitted for approval", ['photo_id' => $photo->id]);
        try {
            $approvers = \App\Models\User::permission('approve photos')->get()
                ->merge(\App\Models\User::role(['admin','super-admin'])->get())
                ->unique('id');
            foreach ($approvers as $approver) {
                if ($approver->email && $approver->id !== $user->id) {
                    \Illuminate\Support\Facades\Mail::send('emails.photo-pending', [
                        'uploader'   => $user,
                        'count'      => 1,
                        'groupName'  => \App\Helpers\RaynetSetting::groupName(),
                        'approveUrl' => url('/members/photo-approval'),
                    ], function($m) use ($approver) {
                        $m->to($approver->email, $approver->name)
                          ->subject('Photo awaiting approval — ' . \App\Helpers\RaynetSetting::groupName());
                    });
                }
            }
        } catch (\Throwable $e) {}
        return back()->with('photo_success', 'Photo submitted for approval.');
    }

    public function notifyApprovers(Request $request) {
        $user  = auth()->user();
        $count = (int) $request->input('count', 1);
        try {
            $approvers = \App\Models\User::permission('approve photos')->get()
                ->merge(\App\Models\User::role(['admin','super-admin'])->get())
                ->unique('id');
            foreach ($approvers as $approver) {
                if ($approver->email && $approver->id !== $user->id) {
                    \Illuminate\Support\Facades\Mail::send('emails.photo-pending', [
                        'uploader'   => $user,
                        'count'      => $count,
                        'groupName'  => \App\Helpers\RaynetSetting::groupName(),
                        'approveUrl' => url('/members/photo-approval'),
                    ], function($m) use ($approver) {
                        $m->to($approver->email, $approver->name)
                          ->subject('New photo(s) awaiting approval — ' . \App\Helpers\RaynetSetting::groupName());
                    });
                }
            }
        } catch (\Throwable $e) {}
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Photo $photo) {
        abort_if($photo->user_id !== auth()->id(), 403);
        $request->validate([
            'caption'  => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:200'],
            'taken_at' => ['nullable', 'date'],
        ]);
        $photo->update($request->only('caption', 'location', 'taken_at'));
        return back()->with('photo_success', 'Photo updated.');
    }

    public function destroy(Photo $photo) {
        abort_if($photo->user_id !== auth()->id(), 403);
        AuditLogger::log('photo.deleted', auth()->user(), "Deleted photo #{$photo->id} ({$photo->original_filename})", ['photo_id' => $photo->id]);
        Storage::disk('public')->delete('gallery/' . $photo->filename);
        Storage::disk('public')->delete('gallery/thumbs/' . $photo->filename);
        $photo->delete();
        return back()->with('photo_success', 'Photo deleted.');
    }
}
