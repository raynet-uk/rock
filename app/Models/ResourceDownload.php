<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResourceDownload extends Model {
    protected $fillable = ['resource_id','user_id','ip_address','user_agent'];
    public function resource() { return $this->belongsTo(Resource::class); }
    public function user() { return $this->belongsTo(User::class); }
}
