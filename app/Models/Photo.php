<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model {
    protected $fillable = [
        'user_id','album_id','filename','original_filename','caption','location',
        'taken_at','callsign','consent','status','public_status','featured',
        'admin_notes','exif_data','approved_by','public_approved_by','rejected_by','rejected_at','lat','lng',
    ];
    protected $casts = ['consent' => 'boolean', 'featured' => 'boolean', 'taken_at' => 'date'];

    public function album() { return $this->belongsTo(Album::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function tags() { return $this->hasMany(PhotoTag::class); }

    public function url(): string {
        return Storage::url('gallery/' . $this->filename);
    }
    public function thumbUrl(): string {
        $thumb = 'gallery/thumbs/' . $this->filename;
        return Storage::exists($thumb) ? Storage::url($thumb) : $this->url();
    }
    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isApproved(): bool       { return $this->status === 'approved'; }
    public function isPending(): bool         { return $this->status === 'pending'; }
    public function isPublicApproved(): bool  { return $this->public_status === 'approved'; }
    public function rejectedBy() { return $this->belongsTo(User::class, 'rejected_by'); }
    public function approvedBy()             { return $this->belongsTo(User::class, 'approved_by'); }
    public function publicApprovedBy()       { return $this->belongsTo(User::class, 'public_approved_by'); }
}
