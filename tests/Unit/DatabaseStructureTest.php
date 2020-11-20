<?php

namespace Tests\Unit;

use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Helpers\IpAddressInversion;

class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    public function testWorkingRelationships()
    {
        $ip = '192.168.100.100';

        $device1 = new Device([
            'name' => 'laptop_1',
        ]);
        $device1->save();

        $device_link1 = new DeviceLink([
            'device_id' => $device1->id,
            'lladdr' => '00:00:00:11:11:11',
            'dev' => 'eth1',
            'ipv4' => $ip,
            'hostname' => 'computer',
        ]);
        $device_link1->save();

        $device_link_state_log1 = new DeviceLinkStateLog([
            'device_id' => $device1->id,
            'device_link_id' => $device_link1->id,
            'state' => DeviceLinkStateLog::STATE_REACHABLE,
            'timestamp' => time(),
        ]);
        $device_link_state_log1->save();


        $this->assertDatabaseHas(
            $device1->getTable(),
            [
                'id' => $device1->id,
            ]
        );
        $this->assertDatabaseHas(
            $device_link1->getTable(),
            [
                'device_id' => $device1->id,
                'hostname' => 'computer',
                'ipv4' => IpAddressInversion::ip2long($ip),
            ]
        );
        $this->assertDatabaseHas(
            $device_link_state_log1->getTable(),
            [
                'device_id' => $device1->id,
                'device_link_id' => $device_link1->id,
            ]
        );


        $this->assertDatabaseHas(
            $device_link1->getTable(),
            ['id' => $device1->device_links->first()->id]
        );
        $this->assertDatabaseHas(
            $device_link1->getTable(),
            ['device_id' => $device1->device_link_state_logs->first()->id]
        );
        $this->assertDatabaseHas(
            $device_link_state_log1->getTable(),
            ['device_id' => $device_link_state_log1->device->id]
        );
        $this->assertDatabaseHas(
            $device_link_state_log1->getTable(),
            ['device_link_id' => $device1->device_links->first()->id]
        );

        $c = $device_link_state_log1->device_link->id;

        $this->assertDatabaseHas(
            $device_link_state_log1->getTable(),
            ['device_link_id' => $device_link_state_log1->device_link->id]
        );
    }

    public function testStoringIpv4Address()
    {
        $ip = '192.168.50.40';

        $device1 = new Device(['name'=>'laptop_1']);
        $device1->save();

        $device_link1 = new DeviceLink([
            'device_id' => $device1->id,
            'name' => 'laptop_1',
            'lladdr' => 'test',
            'ipv4' => $ip,
            'dev' => 'eth20',
            'hostname' => 'computer',
        ]);
        $device_link1->save();

        $this->assertEquals($ip, $device_link1->ipv4);
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
