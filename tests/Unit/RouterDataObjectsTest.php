<?php

namespace Tests\Unit;

use App\Api\Structure\Neighbour;
use App\DeviceLinkStateLog;
use Tests\TestCase;

class RouterDataObjectsTest extends TestCase
{
    private array $rawNeighbour = [
        [
            'ip' => '10.11.11.11',
            'hostname' => 'computer1',
            'state' => DeviceLinkStateLog::STATE_FAILED,
            'lladdr' => null,
            'dev' => 'eth1',
        ],
        [
            'ip' => '10.12.12.12',
            'hostname' => 'computer2',
            'state' => DeviceLinkStateLog::STATE_REACHABLE,
            'lladdr' => '00:00:12:12:00:00',
            'dev' => 'eth2',
        ],
        [
            'ip' => '10.13.13.13',
            'hostname' => 'computer3',
            'state' => DeviceLinkStateLog::STATE_DELAY,
            'lladdr' => '00:00:13:13:00:00',
            'dev' => 'eth3',
        ],
    ];

    private function getOneNeighbourFromRouter(int $i): \stdClass
    {
        $s = new \stdClass();
        foreach ($this->rawNeighbour[$i] as $key => $val)
        {
            $s->{$key} = $val;
        }
        return $s;
    }

    private function getVal(int $i, string $key)
    {
        return $this->rawNeighbour[$i][$key];
    }

    public function testNeighbour()
    {
        $s = $this->getOneNeighbourFromRouter(0);

        $n = new Neighbour($s);
        $this->assertEquals($this->getVal(0, 'ip'), $n->ip);
        $this->assertEquals($this->getVal(0, 'hostname'), $n->hostname);
        $this->assertEquals($this->getVal(0, 'state'), $n->state);
        $this->assertEquals($this->getVal(0, 'lladdr'), $n->lladdr);
        $this->assertEquals($this->getVal(0, 'dev'), $n->dev);
        $this->assertEquals(false, $n->thisDoesNotExist);
    }

}
