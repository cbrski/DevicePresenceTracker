<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'lladdr',
        'dev',
        'ipv4',
        'ipv6',
        'hostname',
    ];

    protected $attributes = [
        'ipv4' => null,
        'ipv6' => null,
        'hostname' => null,
    ];

    public function device()
    {
        return $this->belongsTo('App\Device');
    }

    public function device_link_state_logs()
    {
        return $this->hasMany('App\DeviceLinkStateLogs');
    }
}
