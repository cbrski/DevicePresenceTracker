<?php


namespace Database\Factories\Helpers;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Support\Collection;

class OneEntryHelper
{
    private Collection $entry;

    private function renameArrayKeys(array $arr): array
    {
        foreach ($arr as $key => $val)
        {
            $arr[get_class($val)] = $val;
            unset($arr[$key]);
        }
        return $arr;
    }

    public function __construct(array $args=null)
    {
        $device = Device::factory(
            $args[Device::class] ?? []
        )->create();

        $deviceLink = DeviceLink::factory(
            array_merge([
                'device_id' => $device->id
            ],
            $args[DeviceLink::class] ?? [])
        )->create();

        $deviceLinkStateLog = DeviceLinkStateLog::factory(
            array_merge([
                'device_id' => $device->id,
                'device_link_id' => $deviceLink->id
            ],
            $args[DeviceLinkStateLog::class] ?? [])
        )->create();

        $this->entry = new Collection(
            $this->renameArrayKeys([$device, $deviceLink, $deviceLinkStateLog])
        );
    }

    public function getCollection(): Collection
    {
        return $this->entry;
    }

    public function list(...$list): array
    {
        $arr = [];
        foreach ($list as $val)
        {
            $arr[] = $this->entry->get($val);
        }
        return $arr;
    }
}
