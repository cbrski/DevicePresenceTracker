<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    public function device_mac() {
        $this->belongsTo('App\DeviceMac');
    }
}
