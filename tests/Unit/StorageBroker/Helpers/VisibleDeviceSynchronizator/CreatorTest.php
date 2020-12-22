<?php

namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\Creator;
use Illuminate\Support\Collection;
use Tests\Helpers\RegisterDependsCrossClass;

class CreatorTest extends BaseTest
{
    private static int $countMustBeCreated = 0;
    private static int $countNeighboursMustLeft;

    public function testCreateNew(): array
    {
        $keepers = RegisterDependsCrossClass::get('keepers');
        $neighboursLeft = RegisterDependsCrossClass::get('neighboursLeft');
        $this->testIfShouldProceed($keepers, $neighboursLeft);
        $this->assertCount(4, $neighboursLeft);

        self::$countNeighboursMustLeft = count($neighboursLeft);
        foreach ($neighboursLeft as $neighbour) {
            if (
                !is_null($neighbour->lladdr)
                && (0 != strcasecmp($neighbour->state, DeviceLinkStateLog::STATE_FAILED))
            ) {
                self::$countMustBeCreated = ++self::$countMustBeCreated;
                self::$countNeighboursMustLeft = --self::$countNeighboursMustLeft;
            }
        }

        /** @var Creator $creator */
        $creator = $this->app->make(Creator::class);
        $result = $creator->createNew($keepers, $neighboursLeft);
        $this->assertIsArray($result);
        $this->assertInstanceOf(Collection::class, $result[0]);
        $this->assertInstanceOf(NeighboursRepository::class, $result[1]);

        RegisterDependsCrossClass::set('keepers', $result[0]);
        RegisterDependsCrossClass::set('neighboursLeft', $result[1]);
        return $result;
    }

    /** @depends testCreateNew */
    public function testCollectionOfVisibleDeviceKeepersCorrectlyAddedNew(array $args)
    {
        $keepersCreated = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_CREATE_NEW) {
                return true;
            }
            return false;
        });
        $keepersCreated->each(function ($keeper, $key) {
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

    /** @depends testCreateNew */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUpdatedMatched(array $args)
    {
        $keepersMatched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_MATCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(8, $keepersMatched);
    }

    /** @depends testCreateNew */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateUpdatedNotMatched(array $args)
    {
        $keepersMatched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_NOT_MATCHED) {
                return true;
            }
            return false;
        });
        $this->assertCount(4, $keepersMatched);
    }

    /** @depends testCreateNew */
    public function testCountCollectionOfVisualDeviceKeepersWithInternalStateCreateNew(array $args)
    {
        $keepersMatched = $args[0]->filter(function($value, $key) {
            if ($value->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_CREATE_NEW) {
                return true;
            }
            return false;
        });
        $this->assertCount(self::$countMustBeCreated, $keepersMatched);
    }

    /** @depends testCreateNew */
    public function testCountCollectionOfVisualDeviceKeepersAll(array $args)
    {
        /** @var Collection $keepers */
        $keepers = $args[0];
        $this->assertCount(12 + self::$countMustBeCreated, $keepers);
    }

    /** @depends testCreateNew */
    public function testCountNeighboursLeft(array $args)
    {
        /** @var Collection $keepers */
        $neighboursLeft = $args[1];
        $this->assertCount(self::$countNeighboursMustLeft, $neighboursLeft);
    }
}
