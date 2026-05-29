<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RaynetPublication extends Model
{
    protected $fillable = [
        'type','title','edition','published_date','description',
        'file_path','cover_image_path','external_url',
        'is_current','is_published','sort_order',
    ];

    protected $casts = [
        'published_date' => 'date',
        'is_current'     => 'boolean',
        'is_published'   => 'boolean',
    ];

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) return Storage::url($this->file_path);
        return $this->external_url ?? null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image_path) return Storage::url($this->cover_image_path);
        return null;
    }

    public function scopePublished($q) { return $q->where('is_published', true); }
    public function scopeOfType($q, string $type) { return $q->where('type', $type); }
    public function scopeCurrent($q) { return $q->where('is_current', true); }
}
