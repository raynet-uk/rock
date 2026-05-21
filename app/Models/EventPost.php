<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventPost extends Model {
    protected $fillable = ['event_support_pack_id','tactical_callsign','post_name','description',
        'post_type','location','grid_ref','what3words','access_notes','start_time','finish_time',
        'minimum_operators','vehicle_required','remote_post','lone_working_possible','sort_order'];
    protected $casts = ['vehicle_required'=>'boolean','remote_post'=>'boolean','lone_working_possible'=>'boolean'];
    public function pack()     { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
    public function operators(){ return $this->hasMany(EventOperator::class, 'post_id'); }
}
