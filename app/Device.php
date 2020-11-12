<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name',
    ];

    public function device_links()
    {
        return $this->hasMany('App\DeviceLink');
    }

    public function device_link_state_logs()
    {
        return $this->hasMany('App\DeviceLinkStateLog');
    }
}
