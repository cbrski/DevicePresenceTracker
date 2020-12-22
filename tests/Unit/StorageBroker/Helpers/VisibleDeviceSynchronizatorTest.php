<?php declare(strict_types=1);


namespace Tests\Unit\StorageBroker\Helpers;



use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\Helpers\IpAddressInversion;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator\BaseTest;

class VisibleDeviceSynchronizatorTest extends BaseTest
{
    public function testIntegrationTestSync()
    {
        [$keepers, $neighboursLeft] = $this->getInitialState();
        /** @var VisibleDevicesSynchronizator $vds */
        $vds = $this->app->make(VisibleDevicesSynchronizator::class);
        $this->assertTrue(
            $vds->sync($neighboursLeft)
        );
        return $neighboursLeft;
    }

    /** @depends testIntegrationTestSync */
    public function testDatabase(NeighboursRepository $neighbours)
    {
        $this->assertInstanceOf(NeighboursRepository::class, $neighbours);
        /** @var IpAddressInversion $ipAddressInversion */
        $ipAddressInversion = $this->app->make(IpAddressInversion::class);

        foreach ($neighbours as $n) {
            if (
                !is_null($n->lladdr)
                && 0 != strcasecmp($n->state, DeviceLinkStateLog::STATE_FAILED)
            ) {
                $this->assertDatabaseHas($this->app->make(DeviceLink::class)->getTable(), [
                    'ipv4' => $ipAddressInversion::ip2long($n->ip),
                    'dev' => $n->dev,
                    'hostname' => $n->hostname,
                    'lladdr' => $n->lladdr,
                ]);

                /** @var DeviceLink $deviceLink */
                $deviceLink = $this->app->make(DeviceLink::class);
                $dl = $deviceLink::where('lladdr', $n->lladdr)->firstOrFail();

                $lastState = $dl->device_link_state_logs->toQuery()->orderBy('id', 'desc')->firstOrFail()->state;
                $this->assertDatabaseHas($this->app->make(DeviceLinkStateLog::class)->getTable(), [
                    'device_link_id' => $dl->id,
                    'state' => $lastState,
                ]);
            }
        }
    }
}
