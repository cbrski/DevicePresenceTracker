<?php

namespace Tests\Unit;

use App\Device;
use App\DeviceMac;
use App\DeviceStateLog;
use App\Providers\DeviceStateProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceStateProviderTest extends TestCase
{
    use RefreshDatabase;

    private function ormSave(array $models)
    {
        foreach ($models as $model)
        {
            $model->save();
        }
    }

    private function fixtureInit(): void
    {
        $t['device'][0] = new Device([
            'name' => 'laptop_1',
            'ipv4' => ip2long('192.168.23.23'),
            'dev' => 'eth1',
        ]);
        $t['device'][1] = new Device([
            'name' => 'laptop_2',
            'ipv4' => ip2long('192.168.23.23'),
            'dev' => 'eth2',
        ]);
        $t['device'][2] = new Device([
            'name' => 'laptop_3',
            'ipv4' => ip2long('192.168.23.23'),
            'dev' => 'eth3  ',
        ]);
        $this->ormSave($t['device']);

        $t['device_mac'][0] = new DeviceMac([
            'device_id' => $t['device'][0]->id,
            'mac' => '00:11:22:33:44:55',
            'link_layer' => 'ethernet'
        ]);
        $t['device_mac'][1] = new DeviceMac([
            'device_id' => $t['device'][0]->id,
            'mac' => '00:00:22:33:44:55',
            'link_layer' => 'wifi'
        ]);
        $t['device_mac'][2] = new DeviceMac([
            'device_id' => $t['device'][1]->id,
            'mac' => '00:00:00:33:44:55',
            'link_layer' => 'ethernet'
        ]);
        $t['device_mac'][3] = new DeviceMac([
            'device_id' => $t['device'][2]->id,
            'mac' => '00:00:00:00:44:55',
            'link_layer' => 'ethernet'
        ]);
        $this->ormSave($t['device_mac']);

        $t['device_state_log'][0] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-1000000000,
            'state' => DeviceStateLog::STATE_REACHABLE,
        ]);
        $t['device_state_log'][1] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-100000000,
            'state' => DeviceStateLog::STATE_FAILED,
        ]);
        $t['device_state_log'][2] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-10000000,
            'state' => DeviceStateLog::STATE_INCOMPLETE,
        ]);
        $t['device_state_log'][3] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-10000000,
            'state' => DeviceStateLog::STATE_INCOMPLETE,
        ]);
        $t['device_state_log'][4] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-1000000,
            'state' => DeviceStateLog::STATE_DELAY,
        ]);
        $t['device_state_log'][5] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-10000,
            'state' => DeviceStateLog::STATE_STALE,
        ]);
        $t['device_state_log'][6] = new DeviceStateLog([
            'device_id' => $t['device'][0]->id,
            'timestamp' => time()-10,
            'state' => DeviceStateLog::STATE_REACHABLE,
        ]);
        $t['device_state_log'][7] = new DeviceStateLog([
            'device_id' => $t['device'][1]->id,
            'timestamp' => time()-100,
            'state' => DeviceStateLog::STATE_STALE,
        ]);
        $t['device_state_log'][8] = new DeviceStateLog([
            'device_id' => $t['device'][2]->id,
            'timestamp' => time()-100,
            'state' => DeviceStateLog::STATE_FAILED,
        ]);
        $this->ormSave($t['device_state_log']);

        $this->assertDatabaseCount($t['device'][0]->getTable(), 3);
        $this->assertDatabaseCount($t['device_mac'][0]->getTable(), 4);
        $this->assertDatabaseCount($t['device_state_log'][0]->getTable(), 9);
    }

    private function prepareNeighboursCollection($args): \stdClass
    {
        $n = new \stdClass();
        $n->neighbours = [];
        $values = ['ip', 'dev', 'lladdr', 'state', 'hostname'];

        foreach ($args as $entry_array)
        {
            $to_fill = $n->neighbours[] = new \stdClass();
            for ($i=0 ; $i<count($values) ; ++$i)
            {
                $to_fill->{$values[$i]} = $entry_array[$i];
            }
        }
        return $n;
    }

    private function getState(string $collection): \stdClass
    {
        switch ($collection)
        {
            case 'init':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'STALE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                ]);
            case 'case0:compareWithDatabase':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                    ['192.168.1.30', 'eth1', '3a:11:22:33:bb:33', 'FAILED', 'laptop_3'],
                ]);
            case 'case1:update':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                ]);
            case 'case2:new_device':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                    ['192.168.1.30', 'eth1', '3a:11:22:33:bb:33', 'REACHABLE', 'laptop_3'],
                ]);
            case 'case2:update':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                    ['192.168.1.30', 'eth1', '3a:11:22:33:bb:33', 'FAILED', 'laptop_3'],
                ]);
        }
    }

    private function stdClassEquals(\stdClass $sc1, \stdClass $sc2): bool
    {
        if (json_encode($sc1) === json_encode($sc2))
        {
            return true;
        }
        return false;
    }

    public function testGetData(): void
    {
        $this->fixtureInit();
        $provider = new DeviceStateProvider($this->app);
        $state = $provider->get();
        $this->assertTrue(
            $this->stdClassEquals(
                $state, $this->getState('case0:compareWithDatabase')
            )
        );
    }

    public function testSetData(): void
    {
        $this->fixtureInit();
        $provider = new DeviceStateProvider($this->app);
        $provider->set($this->getState('case1:update'));
        $state = $provider->get();
        $this->assertTrue(
            $this->stdClassEquals(
                $state, $this->getState('case1:update')
            )
        );
    }
}
