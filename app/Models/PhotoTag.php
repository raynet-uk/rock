<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PhotoTag extends Model {
    protected $fillable = ['photo_id','user_id','callsign','name','x_pct','y_pct'];
    public function photo() { return $this->belongsTo(Photo::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
