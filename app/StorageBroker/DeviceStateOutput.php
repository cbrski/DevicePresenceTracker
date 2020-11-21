<?php


namespace App\StorageBroker;


use App\Device;
use App\Helpers\IpAddressInversion;

class DeviceStateOutput
{
    public function get()
    {
        $data = [];
        $devices = Device::query()->orderBy('name', 'asc')->get();
        foreach ($devices as $d)
        {
            $out['deviceName'] = $d->name;
            $lastUsedLink = $d->device_link_state_logs->toQuery()->orderBy('timestamp', 'desc')->limit(1)->get()->first();
            $out['lastUsedLink']['state'] = $lastUsedLink->state;
            $out['lastUsedLink']['dev'] = $lastUsedLink->device_link->dev;
            $out['lastUsedLink']['timestamp'] = $lastUsedLink->timestamp;
            foreach ($d->device_links as $dl)
            {
                $b['lladdr'] = $dl->lladdr;
                $b['dev'] = $dl->dev;
                $b['ip'] = $dl->ipv4;
                $b['hostname'] = $dl->hostname;
                $dlsl = $dl->device_link_state_logs->toQuery()->orderBy('timestamp', 'desc')->limit(1)->get()->first();
                $b['state'] = $dlsl->state;
                $b['timestamp'] = $dlsl->timestamp;
                $out['links'][] = $b;
            }
            $data[] = $out;
            unset($out['links']);
        }
        return $data;
    }
}
