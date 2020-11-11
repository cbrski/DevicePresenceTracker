<?php

namespace Tests\Unit;

use App\Device;
use App\DeviceMac;
use App\DeviceStateLog;
use App\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    public function testWorkingRelationships()
    {
        $device1 = new Device([
            'name' => 'laptop_1',
            'ipv4' => ip2long('192.168.23.23'),
            'dev' => 'eth10'
        ]);
        $device1->save();

        $device_mac1 = new DeviceMac([
            'device_id' => $device1->id,
            'mac' => '00:00:00:11:11:11',
            'link_layer' => 'ethernet',
        ]);
        $device_mac1->save();

        $device_state_log1 = new DeviceStateLog([
            'device_id' => $device1->id,
            'timestamp' => time(),
        ]);
        $device_state_log1->save();


        $this->assertDatabaseHas(
            $device1->getTable(),
            ['id' => $device1->id]
        );
        $this->assertDatabaseHas(
            $device_mac1->getTable(),
            ['device_id' => $device1->id]
        );
        $this->assertDatabaseHas(
            $device_state_log1->getTable(),
            ['device_id' => $device1->id]
        );


        $this->assertDatabaseHas(
            $device_mac1->getTable(),
            ['id' => $device1->device_macs->first()->id]
        );
        $this->assertDatabaseHas(
            $device_state_log1->getTable(),
            ['id' => $device1->device_state_logs->first()->id]
        );
    }

    public function testStoringIpv4Address()
    {
        $ipv4['string'] = '192.168.50.40';
        $ipv4['int'] = ip2long($ipv4['string']);

        $device1 = new Device([
            'name' => 'laptop_1',
            'ipv4' => ip2long($ipv4['string']),
            'dev' => 'eth20',
        ]);
        $device1->save();

        $this->assertEquals($ipv4['int'], $device1->ipv4);
        $this->assertEquals(long2ip($device1->ipv4), $ipv4['string']);
    }

    public function testWorkingLogTable()
    {
        $log1 = new Log([
            'timestamp' => time(),
            'message' => 'Lorem ipsum',
        ]);
        $log1->save();

        $this->assertDatabaseHas($log1->getTable(), ['id' => $log1->id]);
    }

    public function testExistSettingsTable()
    {
        $this->assertDatabaseHas('settings', ['group' => 'RouterApiSettings']);
        $this->assertDatabaseCount('settings', 2);
    }
}
