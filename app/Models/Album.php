<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Album extends Model {
    protected $fillable = ['user_id','name','description','cover_photo_id','status'];
    public function user()   { return $this->belongsTo(User::class); }
    public function photos() { return $this->hasMany(Photo::class); }
    public function cover()  { return $this->belongsTo(Photo::class, 'cover_photo_id'); }
    public function isPending()  { return $this->status === 'pending'; }
    public function isDraft()    { return $this->status === 'draft'; }
}
