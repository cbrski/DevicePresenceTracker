<?php

namespace Tests\Unit;

use App\Device;
use App\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceStatusManagerTest extends TestCase
{
    use RefreshDatabase;

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

    private function getNeighbours(string $collection): \stdClass
    {
        switch ($collection)
        {
            case 'init':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'STALE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                ]);
                break;
            case 'case1:update':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                ]);
                break;
            case 'case2:new_device':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                    ['192.168.1.30', 'eth1', '3a:11:22:33:bb:33', 'REACHABLE', 'laptop_3'],
                ]);
                break;
            case 'case2:update':
                return $this->prepareNeighboursCollection([
                    ['10.2.0.1', 'eth0', '0a:11:22:33:bb:00', 'REACHABLE', null],
                    ['192.168.1.1', 'eth1', '1a:11:22:33:bb:11', 'REACHABLE', 'laptop_1'],
                    ['192.168.1.2', 'eth1', '2a:11:22:33:bb:22', 'FAILED', 'laptop_2'],
                    ['192.168.1.30', 'eth1', '3a:11:22:33:bb:33', 'FAILED', 'laptop_3'],
                ]);
        }
    }

    public function testGetData(): void
    {

    }

    public function testSetData(): void
    {

    }

    public function testDiffData(): void
    {

    }

}
