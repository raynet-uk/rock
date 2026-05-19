<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model {
    use HasFactory;

    protected $fillable = [
        'title','filename','original_name','mime_type','file_size','visibility',
        'category','tags','uploaded_by','uploaded_by_user_id','source',
        'approved','approved_by','description','pinned','featured',
        'expires_at','download_count','version',
    ];

    protected $casts = [
        'approved'   => 'boolean',
        'pinned'     => 'boolean',
        'featured'   => 'boolean',
        'file_size'  => 'integer',
        'expires_at' => 'datetime',
        'download_count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
    public function downloads()  { return $this->hasMany(ResourceDownload::class); }
    public function bookmarks()  { return $this->hasMany(ResourceBookmark::class); }
    public function versions()   { return $this->hasMany(ResourceVersion::class)->orderByDesc('created_at'); }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($q) {
        return $q->where('approved', true)
                 ->where(function($q) {
                     $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                 });
    }

    public function scopePinned($q)   { return $q->where('pinned', true); }
    public function scopeFeatured($q) { return $q->where('featured', true); }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getStoragePathAttribute(): string
    {
        return 'resources/' . $this->visibility . '/' . $this->filename;
    }

    public function getDriveNameAttribute(): string
    {
        return match($this->visibility) {
            'public'    => 'Public Library',
            'members'   => 'Members Drive',
            'committee' => 'Committee Drive',
            'admin'     => 'Admin Drive',
            default     => ucfirst($this->visibility),
        };
    }

    public function getDriveIconAttribute(): string
    {
        return match($this->visibility) {
            'public'    => '&#127760;',
            'members'   => '&#128274;',
            'committee' => '&#128203;',
            'admin'     => '&#9881;',
            default     => '&#128193;',
        };
    }

    public static function visibilityForUser(?\App\Models\User $user): array
    {
        $levels = ['public'];
        if (!$user) return $levels;
        if ($user->hasRole(['member','committee','admin','super-admin'])) $levels[] = 'members';
        if ($user->hasRole(['committee','admin','super-admin']))          $levels[] = 'committee';
        if ($user->hasRole(['admin','super-admin']))                      $levels[] = 'admin';
        return $levels;
    }

    public function getFileSizeFormattedAttribute(): string {
        $b = $this->file_size ?? 0;
        if ($b >= 1048576) return round($b/1048576,1).' MB';
        if ($b >= 1024)    return round($b/1024,1).' KB';
        return $b.' B';
    }

    public function getTagsArrayAttribute(): array {
        if (!$this->tags) return [];
        return array_filter(array_map('trim', explode(',', $this->tags)));
    }

    public function isExpired(): bool {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isNew(): bool {
        return $this->created_at->gte(now()->subDays(7));
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return static::withoutGlobalScopes()->find($value);
    }

    public function isBookmarkedBy(?int $userId): bool {
        if (!$userId) return false;
        return $this->bookmarks()->where('user_id', $userId)->exists();
    }
}
