<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStateLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['device_id', 'timestamp'];

    public function device()
    {
        $this->belongsTo('App\Device');
    }
}
