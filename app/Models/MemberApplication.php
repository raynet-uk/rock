<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberApplication extends Model
{
    protected $fillable = [
        'callsign', 'title', 'surname', 'forenames', 'known_as', 'dob',
        'email', 'home_tel', 'home_tel_ex', 'mobile', 'mobile_ex',
        'nationality', 'former_nationality', 'place_of_birth', 'address',
        'doc_a_type', 'doc_a_date', 'doc_a_ref',
        'doc_b_type', 'doc_b_date', 'doc_b_ref',
        'criminal_1', 'criminal_1_detail',
        'criminal_2', 'criminal_2_detail',
        'criminal_3', 'criminal_3_detail',
        'comms_national_email', 'comms_group_email',
        'comms_national_tel',  'comms_group_tel',
        'comms_national_sms',  'comms_group_sms',
        'comms_national_post', 'comms_group_post',
        'signature_data', 'status', 'invite_token', 'invite_sent_at', 'converted_user_id', 'doc_a_file', 'doc_b_file', 'pdf_path', 'doc_a_file', 'doc_b_file', 'pdf_path',
    ];

    protected $casts = [
        'home_tel_ex'          => 'boolean',
        'mobile_ex'            => 'boolean',
        'comms_national_email' => 'boolean',
        'comms_group_email'    => 'boolean',
        'comms_national_tel'   => 'boolean',
        'comms_group_tel'      => 'boolean',
        'comms_national_sms'   => 'boolean',
        'comms_group_sms'      => 'boolean',
        'comms_national_post'  => 'boolean',
        'comms_group_post'     => 'boolean',
        'invite_sent_at'       => 'datetime',
        'dob'                  => 'date',
    ];

    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->forenames . ' ' . $this->surname);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'warning',
        };
    }
}
