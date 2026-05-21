<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventApproval extends Model {
    protected $fillable = ['event_support_pack_id','status','approver_id','statement','comments'];
    public function pack()    { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
    public function approver(){ return $this->belongsTo(User::class, 'approver_id'); }
}
