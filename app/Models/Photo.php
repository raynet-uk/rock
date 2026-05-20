<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model {
    protected $fillable = [
        'user_id','filename','original_filename','caption','location',
        'taken_at','callsign','consent','status','featured','admin_notes','exif_data',
    ];
    protected $casts = ['consent' => 'boolean', 'featured' => 'boolean', 'taken_at' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function tags() { return $this->hasMany(PhotoTag::class); }

    public function url(): string {
        return Storage::url('gallery/' . $this->filename);
    }
    public function thumbUrl(): string {
        $thumb = 'gallery/thumbs/' . $this->filename;
        return Storage::exists($thumb) ? Storage::url($thumb) : $this->url();
    }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
}
