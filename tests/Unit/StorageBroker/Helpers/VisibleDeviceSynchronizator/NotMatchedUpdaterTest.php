<?php declare(strict_types=1);


namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\NotMatchedUpdater;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Helpers\RegisterDependsCrossClass;

class NotMatchedUpdaterTest extends BaseTest
{
    use RefreshDatabase;

    public function testUpdate(): Collection
    {
        $keepers = RegisterDependsCrossClass::get('keepers');
        $this->testIfShouldProceed($keepers);

        /** @var NotMatchedUpdater $matchedUpdater */
        $notMatchedUpdater = $this->app->make(NotMatchedUpdater::class);
        $keepers = $notMatchedUpdater->update($keepers);
        $this->assertInstanceOf(Collection::class, $keepers);

        RegisterDependsCrossClass::set('keepers', $keepers);
        return $keepers;
    }

    /** @depends testUpdate */
    public function testCollectionOfVisibleDeviceKeepersCorrectlyUpdated(Collection $keepers)
    {
        $keepersMatched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_NOT_MATCHED) {
                return true;
            }
            return false;
        });
        $keepersMatched->each(function ($keeper, $key) {
            /**
             * @var VisibleDeviceKeeper $keeper
             * @var DeviceLinkStateLog $dlsl
             */
            $dlsl = $keeper->getVisibleDevice()->getDeviceLinkStateLog();
            $this->assertEquals($dlsl->state, DeviceLinkStateLog::STATE_OFFLINE);
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
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUpdatedNotMatched(Collection $keepers)
    {
        $keepersMatched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_NOT_MATCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(4, $keepersMatched);
    }

    /** @depends testUpdate */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUntouched(Collection $keepers)
    {
        $keepersMatched = $keepers->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(0, $keepersMatched);
    }
}
