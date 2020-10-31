<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStateLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function device() {
        $this->hasOne('App\Device');
    }
}
