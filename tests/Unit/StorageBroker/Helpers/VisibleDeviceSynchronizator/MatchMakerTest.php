<?php declare(strict_types=1);

namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;


use App\Api\Router\Structure\Neighbour;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchMaker;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper;
use Illuminate\Support\Collection;
use Tests\Helpers\RegisterDependsCrossClass;

class MatchMakerTest extends BaseTest
{
    public function testMatch(): array
    {
        [$keepers, $neighboursLeft] = $this->getInitialState();

        /** @var MatchMaker $matchMaker */
        $matchMaker = $this->app->make(MatchMaker::class);
        $result = $matchMaker->match($keepers, $neighboursLeft);
        $this->assertIsArray($result);
        $this->assertInstanceOf(Collection::class, $result[0]);
        $this->assertInstanceOf(NeighboursRepository::class, $result[1]);

        RegisterDependsCrossClass::set('keepers', $result[0]);
        RegisterDependsCrossClass::set('neighboursLeft', $result[1]);
        return $result;
    }

    /** @depends testMatch */
    public function testCollectionOfVisibleDeviceKeepersCorrectlyMatched(array $args)
    {
        /** @var VisibleDeviceKeeper $value */
        $keepersMatched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED) {
                return true;
            }
            return false;
        });

        /** @var VisibleDeviceKeeper $keeperMatched */
        foreach ($keepersMatched as $keeperMatched) {
            $this->assertInstanceOf(Neighbour::class, $keeperMatched->getNeighbour());
            $this->assertEquals(
                $keeperMatched->getNeighbour()->lladdr,
                $keeperMatched->getVisibleDevice()->getDeviceLink()->lladdr
            );
        }
    }

    /** @depends testMatch */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateMatched(array $args)
    {
        $keepersMatched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(8, $keepersMatched);
    }

    /** @depends testMatch */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUntouched(array $args)
    {
        /** @var VisibleDeviceKeeper $value */
        $keepersUntouched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(4, $keepersUntouched);
    }
}
