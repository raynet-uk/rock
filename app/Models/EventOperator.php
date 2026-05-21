<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventOperator extends Model {
    protected $fillable = ['event_support_pack_id','post_id','user_id','name','callsign',
        'vehicle_reg','mobile','start_time','finish_time','equipment','notes'];
    public function pack() { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
    public function post() { return $this->belongsTo(EventPost::class); }
    public function user() { return $this->belongsTo(User::class); }
}
