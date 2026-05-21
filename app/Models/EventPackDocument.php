<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventPackDocument extends Model {
    protected $fillable = ['event_support_pack_id','document_type','filename','version','generated_at','generated_by'];
    protected $casts = ['generated_at'=>'datetime'];
    public function pack() { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
}
