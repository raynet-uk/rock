<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetStationLog extends Model
{
    protected $table    = 'net_station_log';
    protected $fillable = ['callsign','name','signal_report','notes','checked_in_at','checked_out_at'];
    protected $casts    = ['checked_in_at' => 'datetime', 'checked_out_at' => 'datetime'];
}
