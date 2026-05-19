<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResourceVersion extends Model {
    protected $fillable = ['resource_id','filename','original_name','version','file_size','uploaded_by_user_id','notes'];
    protected $casts = ['file_size' => 'integer'];

    public function resource() { return $this->belongsTo(Resource::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }

    public function getFileSizeFormattedAttribute(): string {
        $b = $this->file_size ?? 0;
        if ($b >= 1048576) return round($b/1048576,1).' MB';
        if ($b >= 1024)    return round($b/1024,1).' KB';
        return $b.' B';
    }
}
