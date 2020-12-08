<?php


namespace Database\Factories\Helpers;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Support\Collection;

class OneEntryHelper
{

    private static function renameArrayKeys(array $arr): array
    {
        foreach ($arr as $key => $val)
        {
            $arr[get_class($val)] = $val;
            unset($arr[$key]);
        }
        return $arr;
    }

    public static function create(): Collection
    {
        $device = Device::factory()->create();
        $deviceLink = DeviceLink::factory([
            'device_id' => $device->id
        ])->create();   
        $deviceLinkStateLog = DeviceLinkStateLog::factory([
            'device_id' => $device->id,
            'device_link_id' => $deviceLink->id
        ])->create();

        return new Collection(
            self::renameArrayKeys([$device, $deviceLink, $deviceLinkStateLog])
        );
    }

}
