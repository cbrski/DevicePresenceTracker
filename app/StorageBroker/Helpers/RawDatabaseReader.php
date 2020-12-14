<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Support\Collection;

class RawDatabaseReader
{
    private function getAllDevices(): Collection
    {
        return Device::query()
            ->orderBy('name', 'asc')
            ->get();
    }

    private function getLastStateByDeviceLink(DeviceLink $deviceLink): DeviceLinkStateLog
    {
        return DeviceLinkStateLog::where('device_link_id', $deviceLink->id)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get()
            ->first();
    }

    private function readFromDatabase(): array
    {
        $data = [];
        $devices = $this->getAllDevices();
        foreach ($devices as $device) {
            $arrLinks = [];
            foreach ($device->device_links as $deviceLink) {
                $lastState = $this->getLastStateByDeviceLink($deviceLink);
                $arrLinks = [
                    DeviceLink::class => $deviceLink,
                    'lastStates' => [
                        DeviceLinkStateLog::class => $lastState
                    ]
                ];
            }
            $data[] = [
                Device::class => $device,
                'links' => [
                    $arrLinks
                ]
            ];
        }
        return $data;
    }

    public function getAll(): array
    {
        return $this->readFromDatabase();
    }
}

