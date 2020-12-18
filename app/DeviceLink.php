<?php

namespace App;

use App\Helpers\IpAddressInversion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Symfony\Component\String\UnicodeString;

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
        return $this->hasMany('App\DeviceLinkStateLog');
    }

    public function getIpv4Attribute()
    {
        if (!is_null($this->attributes['ipv4'])) {
            return IpAddressInversion::long2ip($this->attributes['ipv4']);
        }
        return null;
    }

    public function setIpv4Attribute(string $_ipv4)
    {
        if (!is_null($_ipv4)) {
            $this->attributes['ipv4'] = IpAddressInversion::ip2long($_ipv4);
        }
    }

    public function setLladdrAttribute(string $_lladdr)
    {
        if (!is_null($_lladdr)) {
            /** @var UnicodeString $unicodeString */
            $unicodeString = App::getFacadeRoot()->make(UnicodeString::class, ['string' => $_lladdr]);
            $this->attributes['lladdr'] = $unicodeString->upper();
        }
    }
}
