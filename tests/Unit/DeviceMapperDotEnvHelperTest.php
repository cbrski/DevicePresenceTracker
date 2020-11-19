<?php

namespace Tests\Unit;

use App\StorageBroker\Helpers\DeviceMapperDotEnvHelper;
use Tests\TestCase;

class DeviceMapperDotEnvHelperTest extends TestCase
{
    public function testGetHostnameByLladdr(): void
    {
        $originalMap1 = env('OPENWRT_MAP_DEVICE_1');
        $originalMap2 = env('OPENWRT_MAP_DEVICE_2');

        $_SERVER['OPENWRT_MAP_DEVICE_1'] = 'laptop1..11:11:11:11:11:11';
        $_SERVER['OPENWRT_MAP_DEVICE_2'] = 'laptop2..21:11:11:11:11:11';

        $hostname1 = DeviceMapperDotEnvHelper::getHostnameByLladdr('11:11:11:11:11:11');
        $hostname2 = DeviceMapperDotEnvHelper::getHostnameByLladdr('21:11:11:11:11:11');

        $this->assertEquals('laptop1', $hostname1);
        $this->assertEquals('laptop2', $hostname2);

        $_SERVER['OPENWRT_MAP_DEVICE_1'] = $originalMap1;
        $_SERVER['OPENWRT_MAP_DEVICE_2'] = $originalMap2;
    }
}
