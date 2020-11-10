<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceMac extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'mac', 'link_layer'];

    public function device()
    {
        return $this->belongsTo('App\Device');
    }
}
