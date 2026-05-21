<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventGeneratedRisk extends Model {
    protected $fillable = ['event_support_pack_id','hazard','cause','persons_at_risk','controls',
        'likelihood','severity','residual','rag','escalation_required','briefing_note',
        'accepted','overridden_by','override_reason'];
    protected $casts = ['escalation_required'=>'boolean','accepted'=>'boolean'];
    public function pack() { return $this->belongsTo(EventSupportPack::class, 'event_support_pack_id'); }
}
