<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers;



use App\Api\Router\Structure\Neighbours;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\Creator;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\KeepersInstantiatorFromDatabase;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchedUpdater;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\MatchMaker;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\NotMatchedUpdater;
use App\StorageBroker\Helpers\VisibleDeviceSynchronizator\VisibleDeviceKeeper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class VisibleDevicesSynchronizator
{
    const INTERNAL_STATE_UNTOUCHED              = '_untouched';
    const INTERNAL_STATE_MATCHED                = '_matched';
    const INTERNAL_STATE_UPDATED_MATCHED        = '_updatedMatched';
    const INTERNAL_STATE_UPDATED_NOT_MATCHED    = '_updatedNotMatched';
    const INTERNAL_STATE_CREATE_NEW             = '_createNew';


    private NeighboursRepository $neighboursRepository;
    private NeighboursRepository $neighboursRepositoryLeft;

    /** @var VisibleDeviceKeeper[] */
    private Collection $original;

    /** @var VisibleDeviceKeeper[] */
    private Collection $current;

    private $app;

    public function __construct(KeepersInstantiatorFromDatabase $instantiator)
    {
        $this->app = App::getFacadeRoot();
        $rawDatabaseReader = $this->app->make(RawDatabaseReader::class);
        $this->original = $instantiator->getAll($rawDatabaseReader);
        $this->current = $this->original;
    }

    private function createNeighboursRepository(
        Neighbours $neighbours): NeighboursRepository
    {
        return $this->app
            ->make(NeighboursRepository::class, ['rawData' => $neighbours->getRawData()]);
    }

    private function matchDevices(Collection $keepers, NeighboursRepository $neighboursLeft): array
    {
        /** @var MatchMaker $matchMaker */
        $matchMaker = $this->app->make(MatchMaker::class);
        return $matchMaker->match($keepers, $neighboursLeft);
    }

    private function updateMatchedDevices(Collection $keepers): Collection
    {
        /** @var MatchedUpdater $matchedUpdater */
        $matchedUpdater = $this->app->make(MatchedUpdater::class);
        return $matchedUpdater->update($keepers);
    }

    private function updateNotMatchedDevices(Collection $keepers): Collection
    {
        /** @var NotMatchedUpdater $notMatchedUpdater */
        $notMatchedUpdater = $this->app->make(NotMatchedUpdater::class);
        return $notMatchedUpdater->update($keepers);
    }

    private function createNewDevices(Collection $keepers, NeighboursRepository $neighboursLeft): array
    {
        /** @var Creator $creator */
        $creator = $this->app->make(Creator::class);
        return $creator->createNew($keepers, $neighboursLeft);
    }

    private function saveDevices(Collection $keepers): bool
    {
        $isSaved = false;
        DB::beginTransaction();
        try {
            /** @var VisibleDeviceKeeper $keeper */
            foreach ($keepers as $keeper) {
                $keeper->save();
            }
            DB::commit();
            $isSaved = true;
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $isSaved;
    }

    public function sync(Neighbours $neighbours): bool
    {
        $this->neighboursRepository = $this->createNeighboursRepository($neighbours);
        $this->neighboursRepositoryLeft = $this->neighboursRepository;

        [$this->current, $this->neighboursRepositoryLeft]
            = $this->matchDevices($this->current, $this->neighboursRepositoryLeft);

        $this->current = $this->updateMatchedDevices(   $this->current);
        $this->current = $this->updateNotMatchedDevices($this->current);

        [$this->current, $this->neighboursRepositoryLeft]
            = $this->createNewDevices($this->current, $this->neighboursRepositoryLeft);

        if ($this->saveDevices($this->current)) {
            return true;
        }
        return false;
    }

}

/**
 *  mnemonic about how it works:
 *
 *  1. NeighboursRepository
 *  2. Collection of VisibleDeviceKeeper's
 *
 *  1-->2   -> VisibleDeviceSynchronizator match
 *  *       -> Visible Devices doesnt exist, so let's create them
 *  *   *   -> Visible Devices need to be update according to matched Neighbours
 *      *   -> router doesn't know about Visible Device which we keep so let's update their state to "_offline"
 *
 */
