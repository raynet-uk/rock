<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventUserService extends Model {
    protected $fillable = ['event_support_pack_id','service_name'];
    public function pack() { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
}
