<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ipv4', 'ipv6', 'dev'];

    protected $attributes = [
        'ipv4' => null,
        'ipv6' => null,
    ];

    public function device_macs()
    {
        return $this->hasMany('App\DeviceMac');
    }

    public function device_state_logs()
    {
        return $this->hasMany('App\DeviceStateLog');
    }
}
