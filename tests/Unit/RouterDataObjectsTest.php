<?php

namespace Tests\Unit;

use App\Api\Structure\Neighbour;
use App\DeviceLinkStateLog;
use Tests\TestCase;

class RouterDataObjectsTest extends TestCase
{
    public function testNeighbour()
    {
        $ip = '10.10.10.10';
        $hostname = 'computer';
        $state = DeviceLinkStateLog::STATE_FAILED;
        $lladdr = null;
        $dev = 'eth';

        $s = new \stdClass();
        $s->ip = $ip;
        $s->hostname = $hostname;
        $s->state = $state;
        $s->lladdr = $lladdr;
        $s->dev = $dev;

        $n = new Neighbour($s);
        $this->assertEquals($ip, $n->ip);
        $this->assertEquals($hostname, $n->hostname);
        $this->assertEquals($state, $n->state);
        $this->assertEquals($lladdr, $n->lladdr);
        $this->assertEquals($dev, $n->dev);
        $this->assertEquals(false, $n->thisDoesNotExist);
    }
}
