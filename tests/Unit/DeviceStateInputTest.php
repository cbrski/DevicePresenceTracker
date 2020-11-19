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

    private function invokeReflectedMethod(string $method, array $args)
    {
        $rm = new \ReflectionMethod(DeviceStateInput::class, $method);
        $rm->setAccessible(true);

        $result =  $rm->invokeArgs(new DeviceStateInput(
            new DeviceMapperDotEnvHelper()), $args);
        return $result;
    }

    private function insertNewDeviceToDatabaseAndReturn(): DeviceLink
    {
        $this->testDatabaseNewDevice();
        return DeviceLink::where('lladdr', self::LLADDR_1)->get()->first();
    }

    public function testGetNameForDevice(): void
    {
        $_method = 'getNameForDevice';

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'hostname' => 'something'
        ]);
        $this->assertEquals(self::HOSTNAME_1, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'hostname' => null
        ]);
        $this->assertEquals(self::HOSTNAME_2, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => '00:00:00:00:00:00',
            'hostname' => 'something'
        ]);
        $this->assertEquals('something', $result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => '00:00:00:00:00:00',
            'hostname' => null
        ]);
        $this->assertTrue(strlen($result) == DeviceStateInput::UNDEFINED_DEVICE_NAME_LENGTH);
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

    public function testGetLastState()
    {
        $_method = 'getLastState';
        $deviceLink = $this->insertNewDeviceToDatabaseAndReturn();

        $result = $this->invokeReflectedMethod($_method, [
            'deviceLink' => $deviceLink
        ]);
        $this->assertEquals($result,self::STATE_1);
    }

    public function testSetNewLastState()
    {
        $_method = 'setNewLastState';
        $deviceLink = $this->insertNewDeviceToDatabaseAndReturn();

        $this->invokeReflectedMethod($_method, [
            'deviceLink' => $deviceLink,
            'state' => DeviceLinkStateLog::STATE_NOARP,
        ]);
        $this->checkLastState(DeviceLinkStateLog::STATE_NOARP);
    }

    public function testIsLastStateActual()
    {
        $_method = 'isLastStateActual';
        $deviceLink = $this->insertNewDeviceToDatabaseAndReturn();

        $result = $this->invokeReflectedMethod($_method, [
            'deviceLink' => $deviceLink,
            'state' => self::STATE_1,
        ]);
        $this->assertTrue($result);

        $result = $this->invokeReflectedMethod($_method, [
            'deviceLink' => $deviceLink,
            'state' => DeviceLinkStateLog::STATE_NOARP,
        ]);
        $this->assertFalse($result);
    }

    public function testUpdateState()
    {
        $_method = 'updateState';
        $deviceLink = $this->insertNewDeviceToDatabaseAndReturn();

        $this->invokeReflectedMethod($_method, [
            'deviceLink' => $deviceLink,
            'state' => DeviceLinkStateLog::STATE_NOARP,
        ]);
        $this->checkLastState(DeviceLinkStateLog::STATE_NOARP);
    }

    public function testNewDevice(): Device
    {
        $_method = 'newDevice';

        $device = $this->invokeReflectedMethod($_method, [
            'lladdr' => '11:00:44:00:11',
            'hostname' => null,
        ]);
        $this->assertInstanceOf(Device::class, $device);
        $this->_assertCount(1,0,0);
        $this->assertTrue(strlen($device->name) == DeviceStateInput::UNDEFINED_DEVICE_NAME_LENGTH);

        $device = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'hostname' => null,
        ]);
        $this->assertInstanceOf(Device::class, $device);
        $this->_assertCount(2,0,0);
        $this->assertEquals($device->name, self::HOSTNAME_1);

        return $device;
    }

    public function testNewDeviceLink(): DeviceLink
    {
        $device = $this->testNewDevice();
        $this->_assertCount(2,0,0);

        $_method = 'newDeviceLink';
        $deviceLink = $this->invokeReflectedMethod($_method, [
            'device' => $device,
            'neighbour' => (object) [
                'ip' => self::IP_1,
                'dev' => self::DEV_1,
                'lladdr' => self::LLADDR_1,
                'state' => DeviceLinkStateLog::STATE_REACHABLE,
                'hostname' => self::HOSTNAME_1,
            ]
        ]);
        $this->_assertCount(2,1,0);
        $this->assertDatabaseHas(
            (new DeviceLink())->getTable(), [
            'lladdr' => self::LLADDR_1
        ]);
        return $deviceLink;
    }

    public function testNewDeviceLinkStateLog(): DeviceLinkStateLog
    {
        $deviceLink = $this->testNewDeviceLink();
        $_method = 'newDeviceLinkStateLog';
        $deviceLinkStateLog = $this->invokeReflectedMethod($_method, [
                'deviceLink' => $deviceLink,
                'state' => DeviceLinkStateLog::STATE_DELAY,
        ]);
        $this->_assertCount(2, 1, 1);
        $this->assertDatabaseHas(
            (new DeviceLinkStateLog())->getTable(), [
                'state' => DeviceLinkStateLog::STATE_DELAY
        ]);
        return $deviceLinkStateLog;
    }

    public function testIsDeviceOnlineOnRouter()
    {
        $_method = 'isDeviceOnlineOnRouter';

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => null,
            'state' => null
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'state' => null
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => null,
            'state' => DeviceLinkStateLog::STATE_REACHABLE
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'state' => DeviceLinkStateLog::STATE_REACHABLE
        ]);
        $this->assertTrue($result);
    }

    public function testIsDeviceAlreadyTracked(): void
    {
        $_method = 'isDeviceAlreadyTracked';
        $this->testDatabaseNewDevice();
        $this->_assertCount(1,1,1);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'dev' => self::DEV_1,
            'ip' => self::IP_1,
        ]);
        $this->assertTrue($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => null,
            'dev' => self::DEV_1,
            'ip' => self::IP_1,
        ]);
        $this->assertTrue($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'dev' => self::DEV_2,
            'ip' => self::IP_1,
        ]);
        $this->assertTrue($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_1,
            'dev' => self::DEV_2,
            'ip' => self::IP_2,
        ]);
        $this->assertTrue($result);


        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'dev' => self::DEV_2,
            'ip' => self::IP_2,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'dev' => self::DEV_1,
            'ip' => self::IP_2,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'dev' => self::DEV_1,
            'ip' => self::IP_1,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'dev' => self::DEV_2,
            'ip' => self::IP_1,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => self::LLADDR_2,
            'dev' => self::DEV_1,
            'ip' => self::IP_2,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => null,
            'dev' => self::DEV_1,
            'ip' => null,
        ]);
        $this->assertFalse($result);

        $result = $this->invokeReflectedMethod($_method, [
            'lladdr' => null,
            'dev' => null,
            'ip' => self::IP_1,
        ]);
        $this->assertFalse($result);
    }

    public function testDecideWhatAction()
    {
        $_method = 'decideWhatAction';
        $this->testDatabaseNewDevice();

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_2,
                'dev' => self::DEV_2,
                'lladdr' => self::LLADDR_2,
                'state' => DeviceLinkStateLog::STATE_DELAY,
                'hostname' => self::HOSTNAME_2,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_NEW, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_2,
                'dev' => self::DEV_2,
                'lladdr' => self::LLADDR_1,
                'state' => DeviceLinkStateLog::STATE_DELAY,
                'hostname' => self::HOSTNAME_2,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_ONLINE, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_1,
                'dev' => self::DEV_1,
                'lladdr' => self::LLADDR_1,
                'state' => DeviceLinkStateLog::STATE_DELAY,
                'hostname' => self::HOSTNAME_1,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_ONLINE, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_2,
                'dev' => self::DEV_1,
                'lladdr' => self::LLADDR_1,
                'state' => DeviceLinkStateLog::STATE_DELAY,
                'hostname' => self::HOSTNAME_2,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_ONLINE, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_1,
                'dev' => self::DEV_1,
                'lladdr' => self::LLADDR_1,
                'state' => DeviceLinkStateLog::STATE_FAILED,
                'hostname' => self::HOSTNAME_1,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_OFFLINE, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_1,
                'dev' => self::DEV_1,
                'lladdr' => null,
                'state' => DeviceLinkStateLog::STATE_FAILED,
                'hostname' => self::HOSTNAME_1,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_OFFLINE, $result);

        $result = $this->invokeReflectedMethod($_method, [
            'neighbour' => (object) [
                'ip' => self::IP_1,
                'dev' => self::DEV_1,
                'lladdr' => null,
                'state' => DeviceLinkStateLog::STATE_FAILED,
                'hostname' => null,
            ],
        ]);
        $this->assertEquals(DeviceStateInput::ACTION_DEVICE_UPDATE_OFFLINE, $result);
    }
}
