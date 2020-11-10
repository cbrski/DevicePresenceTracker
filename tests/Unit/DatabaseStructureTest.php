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
        $device1 = new Device(['name' => 'laptop_1']);
        $device1->save();

        $device_mac1 = new DeviceMac([
            'device_id' => $device1->id,
            'mac' => '00:00:00:11:11:11',
            'link_layer' => 'ethernet'
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
