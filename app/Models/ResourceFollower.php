<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResourceFollower extends Model {
    protected $fillable = ['user_id','category','visibility'];
    public function user() { return $this->belongsTo(User::class); }
}
