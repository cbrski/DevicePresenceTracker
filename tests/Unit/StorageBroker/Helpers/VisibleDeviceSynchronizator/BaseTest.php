<?php declare(strict_types=1);


namespace Tests\Unit\StorageBroker\Helpers\VisibleDeviceSynchronizator;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\RawDatabaseReader;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\KeepersInstantiatorFromDatabase;
use Database\Factories\Helpers\OneEntryHelper;
use Faker\Generator;
use Illuminate\Support\Collection;
use Tests\Helpers\NeighboursFaker;
use Tests\TestCase;

class BaseTest extends TestCase
{
    public function getInitialState(): array
    {
        $this->artisan('migrate:fresh');

        /** @var NeighboursFaker $rawData */
        $nf = $this->app->make(NeighboursFaker::class)
            ->withoutStates([DeviceLinkStateLog::STATE_FAILED])->count(12)->create();

        //store in DB 4 four same visible devices as neighbours
        for ($i=0 ; $i<4 ; ++$i) {
            new OneEntryHelper([
                Device::class => ['name' => $nf[$i]->hostname.'_same'],
                DeviceLink::class => [
                    'lladdr' =>     $nf[$i]->lladdr,
                    'dev' =>        $nf[$i]->dev,
                    'ipv4' =>       $nf[$i]->ip,
                    'hostname' =>   $nf[$i]->hostname
                ],
                DeviceLinkStateLog::class => [
                    'state' => $nf[$i]->state,
                ]
            ]);
        }

        //store in DB 4 four visible devices to match, update (update all info)
        for ($i=4 ; $i<8 ; ++$i) {
            new OneEntryHelper([
                Device::class => ['name' => $nf[$i]->hostname.'_match_and_update'],
                DeviceLink::class => [
                    'lladdr' =>     $nf[$i]->lladdr,
                    'dev' =>        $nf[$i]->dev.rand(0,9),
                    'ipv4' =>       $this->app->make(Generator::class)->unique->localIpv4(),
                    'hostname' =>   $nf[$i]->hostname.'_modified'
                ],
                DeviceLinkStateLog::class => [
                    'state' => NeighboursFaker::getRandomState([$nf[$i]->state]),
                ]
            ]);
        }

        /*
         *  store in DB 4 four visible devices to not match, update (set "_offline" state)
         *  4 Neighbours left in Faker will become new VisualDevices
         */
        for ($i=0 ; $i<4 ; ++$i) {
            new OneEntryHelper();
        }

        /**
         * at this point:
         *      8 Neighbours has their matched Visible Devices, they needed to be updated
         *      4 Neighoburs doesn't has their matched Visible Devices, there is need to create new 4 Visible Devices
         *      4 Visible Devices doesn't has ther matched Neighbours, they need to have state "_offline"
         *
         * at the end of the test chain there will be:
         *      0 NeighboursLeft
         *      12 (4 same, 4 updated (all), 4 updated (state:_offline), 4 newly created) VisibleDevices
         */

        $rawDatabaseReader = $this->app->make(RawDatabaseReader::class);
        /** @var Collection $keepers */
        $keepers = $this->app->make(KeepersInstantiatorFromDatabase::class)->getAll($rawDatabaseReader);
        $neighboursLeft = $this->app->make(NeighboursRepository::class, ['rawData' => $nf->shuffle()->toObject()]);

        return [$keepers, $neighboursLeft];
    }

    protected function testIfShouldProceed(...$args)
    {
        foreach ($args as $arg)
        {
            if (is_null($arg)) {
                $this->markTestIncomplete('Cannot proceed, one of needed input argument is NULL '.PHP_EOL.
                    '(Maybe you should test all suite "VisibleDeviceSynchronizator" ?)');
            }
        }
    }
}
