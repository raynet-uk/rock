<?php
namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberPhotoController extends Controller {

    public function store(Request $request) {
        $request->validate([
            'photos'   => ['required', 'array', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:32768'],
            'caption'  => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:200'],
            'taken_at' => ['nullable', 'date'],
            'consent'  => ['required', 'accepted'],
        ]);

        $user  = auth()->user();
        $files = $request->file('photos');
        $uploaded = 0;

        foreach ($files as $file) {
        $ext  = strtolower($file->getClientOriginalExtension());
        $name = Str::uuid() . '.jpg'; // always store as jpg

        // Extract EXIF before moving file
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

        // Bump memory for image processing
        ini_set('memory_limit', '256M');

        // Store as JPEG using GD — resize if > 2000px wide
        $sourcePath = $file->getRealPath();
        try {
            $info = getimagesize($sourcePath);
            $mime = $info['mime'] ?? 'image/jpeg';

            $src = match($mime) {
                'image/png'  => imagecreatefrompng($sourcePath),
                'image/webp' => imagecreatefromwebp($sourcePath),
                default      => imagecreatefromjpeg($sourcePath),
            };

            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);

                // Main image — max 2000px wide
                $maxW = 2000;
                if ($w > $maxW) {
                    $newH = intval($h * $maxW / $w);
                    $resized = imagecreatetruecolor($maxW, $newH);
                    if ($mime === 'image/png') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                    }
                    imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxW, $newH, $w, $h);
                    imagedestroy($src);
                    $src = $resized;
                    $w = $maxW; $h = $newH;
                }

                $mainPath = storage_path('app/public/gallery/' . $name);
                imagejpeg($src, $mainPath, 85);

                // Thumbnail — 400px wide
                $tw = 400;
                $th = intval($h * $tw / $w);
                $thumb = imagecreatetruecolor($tw, $th);
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
                $thumbPath = storage_path('app/public/gallery/thumbs/' . $name);
                imagejpeg($thumb, $thumbPath, 80);

                imagedestroy($src);
                imagedestroy($thumb);
            } else {
                // Fallback: just copy the file
                $file->storeAs('gallery', $name, 'public');
            }
        } catch (\Throwable $e) {
            // Fallback: just copy
            $file->storeAs('gallery', $name, 'public');
        }

        // Try to get taken_at from EXIF if not provided
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
            'taken_at'          => $takenAt,
            'callsign'          => $user->callsign,
            'consent'           => true,
            'status'            => 'pending',
            'admin_notes'       => !empty($exifData) ? json_encode($exifData) : null,
        ]);

        $uploaded++;
        } // end foreach

        return response()->json(['success' => true, 'message' => $uploaded . ' photo' . ($uploaded > 1 ? 's' : '') . ' uploaded successfully and awaiting approval.']);
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
        Storage::disk('public')->delete('gallery/' . $photo->filename);
        Storage::disk('public')->delete('gallery/thumbs/' . $photo->filename);
        $photo->delete();
        return back()->with('photo_success', 'Photo deleted.');
    }
}
