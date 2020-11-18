<?php

namespace Tests\Unit;

use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\DeviceStateInput;
use App\StorageBroker\Helpers\DeviceMapperDotEnvHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceStateInputTest extends TestCase
{
    use RefreshDatabase;

    const LLADDR_1 = '11:11:11:00:00:00';
    const HOSTNAME_1 = 'testerFirst';
    const IP_1 = '10.10.1.1';
    const STATE_1 = DeviceLinkStateLog::STATE_STALE;
    const DEV_1 = 'eth1';

    const LLADDR_2 = '22:22:22:00:00:00';
    const HOSTNAME_2 = 'testerSecond';
    const IP_2 = '12.22.1.1';
    const STATE_2 = DeviceLinkStateLog::STATE_REACHABLE;
    const DEV_2 = 'eth2';

    private function _assertCount(
        int $deviceCount,
        int $deviceLinkCount,
        int $deviceLinkStateLogCount
    )
    {
        $this->assertDatabaseCount(
            (new Device())->getTable(), $deviceCount
        );
        $this->assertDatabaseCount(
            (new DeviceLink())->getTable(), $deviceLinkCount
        );
        $this->assertDatabaseCount(
            (new DeviceLinkStateLog())->getTable(), $deviceLinkStateLogCount
        );
    }

    private function _assertDatabaseHas(array $args)
    {
        $this->assertDatabaseHas(
            (new DeviceLink())->getTable(),
            [
                'ipv4' => ip2long($args['ip']),
                'dev' => $args['dev'],
                'lladdr' => $args['lladdr'],
                'hostname' => $args['hostname'],
            ]
        );

        $this->assertDatabaseHas(
            (new Device())->getTable(),
            [
                'name' => $args['name']
            ]
        );

        $this->assertDatabaseHas(
            (new DeviceLinkStateLog())->getTable(),
            [
                'state' => $args['state']
            ]
        );
    }

    private function passNeighbour(string $privateMethod, array $neighbourPlus)
    {
        if (isset($neighbourPlus['name']))
        {
            unset($neighbourPlus['name']);
        }
        $rm = new \ReflectionMethod(DeviceStateInput::class, $privateMethod);
        $rm->setAccessible(true);
        $result = $rm->invokeArgs(
            new DeviceStateInput(
                new DeviceMapperDotEnvHelper()
            ),
            [
                'neighbour' => (object) $neighbourPlus
            ]
        );
        $this->assertTrue($result);
    }

    private function checkLastState(string $state)
    {
        $lastState = DeviceLinkStateLog::select('*')->orderBy('id', 'desc')->limit(1)->get();
        $this->assertEquals($lastState->first()->state, $state);
    }

    private function checkPassingNeighbour(
        string $privateMethod,
        array $neighbourPlus,
        $deviceCount,
        $deviceLinkCount,
        $deviceLinkStateLogCount
    )
    {
        $this->passNeighbour($privateMethod, $neighbourPlus);
        $this->_assertCount($deviceCount, $deviceLinkCount, $deviceLinkStateLogCount);
        $this->_assertDatabaseHas($neighbourPlus);
        $this->checkLastState($neighbourPlus['state']);
    }

    public function testGetNameForDevice(): void
    {
        $rm = new \ReflectionMethod(DeviceStateInput::class,
            'getNameForDevice');
        $rm->setAccessible(true);

        $result =  $rm->invokeArgs(new DeviceStateInput(
            new DeviceMapperDotEnvHelper()), [
            'lladdr' => self::LLADDR_1,
            'hostname' => 'something'
        ]);
        $this->assertEquals($result, self::HOSTNAME_1);

        $result =  $rm->invokeArgs(new DeviceStateInput(
            new DeviceMapperDotEnvHelper()), [
            'lladdr' => self::LLADDR_2,
            'hostname' => null
        ]);
        $this->assertEquals($result, self::HOSTNAME_2);

        $result =  $rm->invokeArgs(new DeviceStateInput(
            new DeviceMapperDotEnvHelper()), [
            'lladdr' => '00:00:00:00:00:00',
            'hostname' => 'something'
        ]);
        $this->assertEquals($result, 'something');

        $result =  $rm->invokeArgs(new DeviceStateInput(
            new DeviceMapperDotEnvHelper()), [
            'lladdr' => '00:00:00:00:00:00',
            'hostname' => null
        ]);
        $this->assertTrue(strlen($result) == 8);
    }

    public function testDatabaseNewDevice()
    {
        $neighbourPlus_1 = [
            'ip' => self::IP_1,
            'dev' => self::DEV_1,
            'lladdr' => self::LLADDR_1,
            'state' => self::STATE_1,
            'hostname' => self::HOSTNAME_1,
            'name' => self::HOSTNAME_1,
        ];
        $this->checkPassingNeighbour('databaseDeviceNew', $neighbourPlus_1, 1, 1, 1);
    }

    public function testDatabaseDeviceUpdateOnline()
    {
        $this->testDatabaseNewDevice();

        $neighbourPlus_1 = [
            'ip' => self::IP_1,
            'dev' => self::DEV_1,
            'lladdr' => self::LLADDR_1,
            'state' => DeviceLinkStateLog::STATE_INCOMPLETE,
            'hostname' => self::HOSTNAME_1,
            'name' => self::HOSTNAME_1,
        ];
        $this->checkPassingNeighbour('databaseDeviceUpdateOnline', $neighbourPlus_1, 1, 1, 2);

        $neighbourPlus_2 = [
            'ip' => self::IP_1,
            'dev' => self::DEV_1,
            'lladdr' => self::LLADDR_1,
            'state' => self::STATE_2,
            'hostname' => self::HOSTNAME_1,
            'name' => self::HOSTNAME_1,
        ];
        $this->checkPassingNeighbour('databaseDeviceUpdateOnline', $neighbourPlus_2, 1, 1, 3);
    }

    public function testDatabaseDeviceUpdateOffline()
    {
        $this->testDatabaseNewDevice();

        $neighbourPlus_1 = [
            'ip' => self::IP_1,
            'dev' => self::DEV_1,
            'lladdr' => self::LLADDR_1,
            'state' => DeviceLinkStateLog::STATE_FAILED,
            'hostname' => self::HOSTNAME_1,
            'name' => self::HOSTNAME_1,
        ];
        $this->checkPassingNeighbour('databaseDeviceUpdateOffline', $neighbourPlus_1, 1, 1, 2);

    }
}
