<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLinkStateLog extends Model
{
    const STATE_PERMAMENT   = 'permament';
    const STATE_NOARP       = 'noarp';
    const STATE_REACHABLE   = 'reachable';
    const STATE_STALE       = 'stale';
    const STATE_NONE        = 'none';
    const STATE_INCOMPLETE  = 'incomplete';
    const STATE_DELAY       = 'delay';
    const STATE_PROBE       = 'probe';
    const STATE_FAILED      = 'failed';

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'device_link_id',
        'state',
        'timestamp',
    ];

    public function device()
    {
        return $this->belongsTo('App\Device');
    }

    public function device_link()
    {
        return $this->belongsTo('App\DeviceLink');
    }
}
