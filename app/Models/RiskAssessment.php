<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model {
    protected $fillable = [
        'user_id','event_name','location','event_date','start_time','finish_time',
        'attendance','environment','event_type','other_agencies','operator_count',
        'roles','communications','infrastructure','terrain','operator_movement',
        'weather_exposure','road_exposure','access','deployment_duration','facilities',
        'lone_working','under_18','night_operation','equipment','power_source',
        'vehicles_operating','public_order','weather_contingency','fallback_comms',
        'withdrawal_authority','notes','rag_status','status','approved_by',
        'approved_at','version','pdf_path',
    ];

    protected $casts = [
        'environment'=>'array','event_type'=>'array','other_agencies'=>'array',
        'roles'=>'array','communications'=>'array','infrastructure'=>'array',
        'terrain'=>'array','facilities'=>'array','equipment'=>'array',
        'fallback_comms'=>'array','withdrawal_authority'=>'array',
        'event_date'=>'date','approved_at'=>'datetime',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function ragLabel(): string {
        return match($this->rag_status) {
            'green' => 'GREEN — Suitable to proceed',
            'amber' => 'AMBER — Proceed with controls',
            'red'   => 'RED — Do not proceed without further review',
            default => 'Not calculated',
        };
    }

    public function ragColour(): string {
        return match($this->rag_status) {
            'green' => '#059669',
            'amber' => '#f59e0b',
            'red'   => '#dc2626',
            default => '#6b7f96',
        };
    }
}
