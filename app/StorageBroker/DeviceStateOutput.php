<?php


namespace App\StorageBroker;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Database\Eloquent\Collection;

class DeviceStateOutput
{
    private function getDevices(): Collection
    {
        return Device::query()->orderBy('name', 'asc')->get();
    }

    private function getLastStateByDevice(Device $device): DeviceLinkStateLog
    {
        return DeviceLinkStateLog::where([['device_id', '=', $device->id]])
            ->orderBy('timestamp', 'desc')
            ->limit(1)
            ->firstOrFail();
    }

    private function fillDataWithDevice(Device $device): array
    {
        $lastState = $this->getLastStateByDevice($device);
        $l['state'] = $lastState->state;
        $l['dev'] = $lastState->device_link->dev;
        $l['timestamp'] = $lastState->timestamp;

        $t['deviceName'] = $device->name;
        $t['lastUsedLink'] = $l;
        return $t;
    }

    private function fillDataWithDeviceLink(DeviceLink $deviceLink): array
    {
        $state = $this->getLastStateByDevice($deviceLink->device);
        $t['state'] = $state->state;
        $t['lladdr'] = $deviceLink->lladdr;
        $t['dev'] = $deviceLink->dev;
        $t['ip'] = $deviceLink->ipv4;
        $t['hostname'] = $deviceLink->hostname;
        $t['timestamp'] = $state->timestamp;
        return $t;
    }

    public function get()
    {
        $data = [];
        foreach ($this->getDevices() as $device)
        {
            $out = $this->fillDataWithDevice($device);
            foreach ($device->device_links as $deviceLink)
            {
                $out['links'][] = $this->fillDataWithDeviceLink($deviceLink);
            }
            $data[] = $out;
            unset($out['links']);
        }
        return $data;
    }
}
