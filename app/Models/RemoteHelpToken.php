<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RemoteHelpToken extends Model
{
    protected $fillable = ['token','code','expires_at','used','created_by_name','created_by_email','accessed_at','accessed_by_ip'];
    protected $casts = ['expires_at'=>'datetime','accessed_at'=>'datetime','used'=>'boolean'];

    public static function generate(string $name, string $email, int $hours = 4): self
    {
        return self::create([
            'token'             => Str::random(48),
            'code'              => strtoupper(Str::random(4).'-'.Str::random(4)),
            'expires_at'        => now()->addHours($hours),
            'used'              => false,
            'created_by_name'   => $name,
            'created_by_email'  => $email,
        ]);
    }

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
