<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ipv4', 'ipv6'];

    public function device_macs()
    {
        return $this->hasMany('App\DeviceMac');
    }

    public function device_state_logs()
    {
        return $this->hasMany('App\DeviceStateLog');
    }
}
