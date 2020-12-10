<?php

namespace Tests\Unit;

use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\Log;
use Database\Factories\Helpers\OneEntryHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Helpers\IpAddressInversion;

class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    const IP_1 = '192.168.100.100';
    const IP_2 = '192.168.50.40';
    const NAME = 'myComputer';
    const HOSTNAME = 'computer_1';

    public function testWorkingRelationships()
    {
        $collection = new OneEntryHelper([
            DeviceLink::class => [
                'ipv4' => self::IP_1,
                'hostname' => self::HOSTNAME,
            ]
        ]);
        [$d, $dl, $dlsl] = $collection->list(
                Device::class,
                DeviceLink::class,
                DeviceLinkStateLog::class);


        $this->assertDatabaseHas(
            $d->getTable(),
            [
                'id' => $d->id,
            ]
        );
        $this->assertDatabaseHas(
            $dl->getTable(),
            [
                'device_id' => $d->id,
                'hostname' => $dl->hostname,
                'ipv4' => IpAddressInversion::ip2long(self::IP_1),
            ]
        );
        $this->assertDatabaseHas(
            $dlsl->getTable(),
            [
                'device_id' => $d->id,
                'device_link_id' => $dl->id,
            ]
        );


        $this->assertDatabaseHas(
            $dl->getTable(),
            ['id' => $d->device_links->first()->id]
        );
        $this->assertDatabaseHas(
            $dl->getTable(),
            ['device_id' => $d->device_link_state_logs->first()->id]
        );
        $this->assertDatabaseHas(
            $dlsl->getTable(),
            ['device_id' => $dlsl->device->id]
        );
        $this->assertDatabaseHas(
            $dlsl->getTable(),
            ['device_link_id' => $d->device_links->first()->id]
        );

        $this->assertDatabaseHas(
            $dlsl->getTable(),
            ['device_link_id' => $dlsl->device_link->id]
        );
    }

    public function testStoringIpv4Address()
    {

        $collection = new OneEntryHelper([
            Device::class => [
                'name' => self::NAME,
            ],
            DeviceLink::class => [
                'ipv4' => self::IP_2,
            ]
        ]);
        [$dl] = $collection->list(
            DeviceLink::class);

        $this->assertEquals(self::IP_2, $dl->ipv4);
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
