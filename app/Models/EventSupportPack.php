<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EventSupportPack extends Model {
    protected $fillable = [
        'user_id','group_ref','event_name','event_date','duration_days','location','town_area',
        'event_description','organiser_name','organiser_contact','organiser_phone','organiser_email',
        'event_type','controller_callsign','primary_frequency','frequency_public','talkthrough_used',
        'talkthrough_public','assistance_visible','assistance_contact','assistance_phone_email',
        'duty_type','outstations','duty_description','message_type','data_comms','skill_level',
        'traffic_level','equipment_power','operating_environment','welfare_food','welfare_other',
        'raynet_roles','scope_traffic','scope_marshalling','scope_children','scope_first_aid',
        'scope_transport','scope_casualties','secondary_frequency','repeater_details','control_callsign',
        'event_controller','deputy_controller','net_control_location','call_round_interval',
        'fallback_methods','terrain','access_conditions','weather_exposure','road_exposure',
        'operator_movement','equipment','power_source','vehicles_operating','deployment_duration',
        'facilities','welfare_risks','lone_working','night_operation','under_18','rag_status',
        'status','approved_by','approved_at','approval_statement','notes','version','template_type','cloned_from',
    ];

    protected $casts = [
        'event_date'=>'date','approved_at'=>'datetime','frequency_public'=>'boolean',
        'talkthrough_public'=>'boolean','assistance_visible'=>'boolean',
        'raynet_roles'=>'array','terrain'=>'array','access_conditions'=>'array',
        'equipment'=>'array','facilities'=>'array','welfare_risks'=>'array',
    ];

    public function user()      { return $this->belongsTo(User::class); }
    public function approvedBy(){ return $this->belongsTo(User::class, 'approved_by'); }
    public function posts()     { return $this->hasMany(EventPost::class); }
    public function operators() { return $this->hasMany(EventOperator::class); }
    public function risks()     { return $this->hasMany(EventGeneratedRisk::class); }
    public function documents() { return $this->hasMany(EventPackDocument::class); }
    public function services()  { return $this->hasMany(EventUserService::class); }
    public function approvals() { return $this->hasMany(EventApproval::class); }

    public function ragLabel(): string {
        return match($this->rag_status) {
            'green' => 'GREEN - Suitable to proceed',
            'amber' => 'AMBER - Proceed with controls',
            'red'   => 'RED - Do not proceed without further review',
            default => 'Not calculated',
        };
    }
    public function ragColour(): string {
        return match($this->rag_status) {
            'green' => '#059669', 'amber' => '#f59e0b', 'red' => '#dc2626', default => '#6b7f96',
        };
    }
    public function statusLabel(): string {
        return match($this->status) {
            'draft'                 => 'Draft',
            'awaiting_review'       => 'Awaiting Review',
            'approved'              => 'Approved',
            'approved_with_controls'=> 'Approved with Controls',
            'escalated'             => 'Escalated',
            'returned'              => 'Returned',
            'closed'                => 'Closed',
            'cancelled'             => 'Cancelled',
            default                 => ucfirst($this->status),
        };
    }
    public function hasScope(): bool {
        return in_array('Yes', [$this->scope_traffic,$this->scope_marshalling,$this->scope_children,
                                 $this->scope_first_aid,$this->scope_transport,$this->scope_casualties]);
    }
}
