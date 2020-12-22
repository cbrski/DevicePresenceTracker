<?php declare(strict_types=1);

namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;


use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchMaker;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper;
use App\StorageBroker\Models\VisibleDevice;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MatchMakerStandaloneTest extends TestCase
{

    private function makeCollectionOfVisibleDeviceKeeper($args): Collection
    {
        /** @var Collection $keepers */
        $keepers = $this->app->make(Collection::class);

        foreach ($args as $arg) {
            $device = $this->app->make(Device::class);
            $device->name =         $arg['name'];

            $deviceLink = $this->app->make(DeviceLink::class);
            $deviceLink->lladdr =   $arg['lladdr'];
            $deviceLink->dev =      $arg['dev'];
            $deviceLink->ipv4 =     $arg['ipv4'];
            $deviceLink->hostname = $arg['hostname'];

            $deviceLinkStateLog = $this->app->make(DeviceLinkStateLog::class);
            $deviceLinkStateLog->state = $arg['state'];

            $vd = $this->app->make(VisibleDevice::class, ['args' => [
                Device::class => $device,
                'links' => [
                    [
                        DeviceLink::class => $deviceLink,
                        'lastStates' => [
                            DeviceLinkStateLog::class => $deviceLinkStateLog,
                        ]
                    ]
                ]
            ]
            ]);

            $vdk = $this->app->make(VisibleDeviceKeeper::class, ['visibleDevice' => $vd]);
            $keepers->push($vdk);
        }
        return $keepers;
    }

    private function makeNeighboursRepository($args): NeighboursRepository
    {
        $neighbours = [];
        foreach ($args as $arg) {
            $neighbours += [
                (object) [
                    'ip' =>         $arg['ip'],
                    'dev' =>        $arg['dev'],
                    'lladdr' =>     $arg['lladdr'],
                    'state' =>      $arg['state'],
                    'hostname' =>   $arg['hostname'],
                ]
            ];
        }
        $rawData = (object) [
            'timestamp' => time(),
            'neighbours' => $neighbours,
        ];
        return $this->app->make(NeighboursRepository::class, ['rawData' => $rawData]);
    }

    public function testMatchSame1()
    {
        $keepers = $this->makeCollectionOfVisibleDeviceKeeper([[
            'name'      => 'computer',
            'lladdr'    => 'AB:00:00:00:00:00',
            'dev'       => 'eth0',
            'ipv4'      => '10.1.10.12',
            'hostname'  => null,
            'state'     => 'REACHABLE'
        ]]);

        $neighboursLeft = $this->makeNeighboursRepository([[
            'ip'        => '10.1.10.12',
            'dev'       => 'eth0',
            'lladdr'    => 'AB:00:00:00:00:00',
            'state'     => 'REACHABLE',
            'hostname'  => null,
        ]]);

        $matchMaker = $this->app->make(MatchMaker::class);
        [$keepers, $neighboursLeft] = $matchMaker->match($keepers, $neighboursLeft);

        $this->assertCount(0, $neighboursLeft);
        $this->assertCount(1, $keepers);

        $firstKeeper =  $keepers->first();
        $vd =           $firstKeeper->getVisibleDevice();
        $vdDl =         $vd->getDeviceLink();
        $vdDlsl =       $vd->getDeviceLinkStateLog();
        $n =            $firstKeeper->getNeighbour();

        $this->assertEquals($firstKeeper->getState(), VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED);

        $this->assertEquals($vdDl->lladdr, 'AB:00:00:00:00:00');
        $this->assertEquals($n->lladdr, 'AB:00:00:00:00:00');

        $this->assertEquals($vdDl->dev, 'eth0');
        $this->assertEquals($n->dev, 'eth0');

        $this->assertEquals($vdDl->ipv4, '10.1.10.12');
        $this->assertEquals($n->ip, '10.1.10.12');

        $this->assertEquals($vdDl->hostname, null);
        $this->assertEquals($n->hostname, null);

        $this->assertEquals($vdDlsl->state, 'REACHABLE');
        $this->assertEquals($n->state, 'REACHABLE');
    }

    public function testNotMatchAnother1()
    {
        $keepers = $this->makeCollectionOfVisibleDeviceKeeper([[
            'name'      => 'computer',
            'lladdr'    => 'FF:00:00:00:00:00',
            'dev'       => 'eth0',
            'ipv4'      => '10.1.10.12',
            'hostname'  => null,
            'state'     => 'REACHABLE'
        ]]);

        $neighboursLeft = $this->makeNeighboursRepository([[
            'ip'        => '10.1.10.12',
            'dev'       => 'eth0',
            'lladdr'    => 'AB:00:00:00:00:00',
            'state'     => 'REACHABLE',
            'hostname'  => null,
        ]]);

        $matchMaker = $this->app->make(MatchMaker::class);
        [$keepers, $neighboursLeft] = $matchMaker->match($keepers, $neighboursLeft);

        $this->assertCount(1, $neighboursLeft);
        $this->assertCount(1, $keepers);

        $firstKeeper =  $keepers->first();
        $vd =           $firstKeeper->getVisibleDevice();
        $vdDl =         $vd->getDeviceLink();
        $vdDlsl =       $vd->getDeviceLinkStateLog();
        $n =            $firstKeeper->getNeighbour();

        $this->assertEquals($firstKeeper->getState(), VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED);
        $this->assertNull($n);

        $this->assertEquals($vdDl->lladdr, 'FF:00:00:00:00:00');
        $this->assertEquals($vdDl->dev, 'eth0');
        $this->assertEquals($vdDl->ipv4, '10.1.10.12');
        $this->assertEquals($vdDl->hostname, null);
        $this->assertEquals($vdDlsl->state, 'REACHABLE');
    }

    public function testNotMatchAnother2()
    {
        $keepers = $this->makeCollectionOfVisibleDeviceKeeper([[
            'name'      => 'computer',
            'lladdr'    => 'FF:00:00:00:00:00',
            'dev'       => 'eth0',
            'ipv4'      => '10.1.10.12',
            'hostname'  => null,
            'state'     => 'STALE'
        ]]);

        $neighboursLeft = $this->makeNeighboursRepository([[
            'ip'        => '10.1.10.12',
            'dev'       => 'eth0',
            'lladdr'    => null,
            'state'     => 'FAILED',
            'hostname'  => null,
        ]]);

        $matchMaker = $this->app->make(MatchMaker::class);
        [$keepers, $neighboursLeft] = $matchMaker->match($keepers, $neighboursLeft);

        $this->assertCount(0, $neighboursLeft);
        $this->assertCount(1, $keepers);

        $firstKeeper =  $keepers->first();
        $vd =           $firstKeeper->getVisibleDevice();
        $vdDl =         $vd->getDeviceLink();
        $vdDlsl =       $vd->getDeviceLinkStateLog();
        $n =            $firstKeeper->getNeighbour();

        $this->assertEquals($firstKeeper->getState(), VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED);
        $this->assertInstanceOf(Neighbour::class, $n);

        $this->assertEquals($vdDl->lladdr, 'FF:00:00:00:00:00');
        $this->assertEquals($vdDl->dev, 'eth0');
        $this->assertEquals($vdDl->ipv4, '10.1.10.12');
        $this->assertEquals($vdDl->hostname, null);
        $this->assertEquals($vdDlsl->state, 'STALE');
    }
}
