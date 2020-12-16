<?php declare(strict_types=1);

namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchedUpdater;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper;
use Illuminate\Support\Collection;
use Tests\Helpers\RegisterDependsCrossClass;

class MatchedUpdaterTest extends BaseTest
{
    public function testUpdate(): Collection
    {
        $keepers = RegisterDependsCrossClass::get('keepers');
        $this->testIfShouldProceed($keepers);

        /** @var MatchedUpdater $matchedUpdater */
        $matchedUpdater = $this->app->make(MatchedUpdater::class);
        $keepers = $matchedUpdater->update($keepers);
        $this->assertInstanceOf(Collection::class, $keepers);

        RegisterDependsCrossClass::set('keepers', $keepers);
        return $keepers;
    }

    /** @depends testUpdate */
    public function testCollectionOfVisibleDeviceKeepersCorrectlyUpdated(Collection $keepers)
    {
        $keepersMatched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_MATCHED) {
                return true;
            }
            return false;
        });
        $keepersMatched->each(function ($keeper, $key) {
            /**
             * @var Neighbour $n
             * @var Device $d
             * @var DeviceLink $dl
             * @var DeviceLinkStateLog $dlsl
             */
            [$n, $d, $dl, $dlsl] = $keeper->unpack();
            $this->assertEquals($n->ip,         $dl->ipv4);
            $this->assertEquals($n->dev,        $dl->dev);
            $this->assertEquals($n->lladdr,     $dl->lladdr);
            $this->assertEquals($n->state,      $dlsl->state);
            $this->assertEquals($n->hostname,   $dl->hostname);

            $this->assertNotNull($d->name);
        });
    }

    /** @depends testUpdate */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUpdatedMatched(Collection $keepers)
    {
        $keepersMatched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_MATCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(8, $keepersMatched);
    }

    /** @depends testUpdate */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUntouched(Collection $keepers)
    {
        /** @var VisibleDeviceKeeper $value */
        $keepersUntouched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(4, $keepersUntouched);
    }
}
