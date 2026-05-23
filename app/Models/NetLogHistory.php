<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NetLogHistory extends Model
{
    protected $table    = 'net_log_history';
    protected $fillable = ['net_callsign','net_name','frequency','started_at','ended_at','stations','station_count'];
    protected $casts    = ['started_at' => 'datetime', 'ended_at' => 'datetime', 'stations' => 'array'];
}
