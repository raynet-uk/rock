<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Referral extends Model {
    protected $fillable = ['referrer_id','callsign','email','name','sent_at'];
    protected $casts = ['sent_at' => 'datetime'];
    public function referrer() { return $this->belongsTo(User::class, 'referrer_id'); }
}
