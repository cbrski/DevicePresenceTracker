<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use Illuminate\Support\Collection;

class MatchMaker
{
    public function match(Collection $keepers, NeighboursRepository $neighboursLeft): array
    {
        /**
         * @var int $keyNeighbour
         * @var Neighbour $neighbour
         * @var int $keyKeeper
         * @var VisibleDeviceKeeper $keeper
         * @var VisibleDeviceKeeper[] $keepers
         */
        foreach ($neighboursLeft as $keyNeighbour => $neighbour) {
            foreach ($keepers as $keyKeeper => $keeper) {
                if ($keeper->getVisibleDevice()->getDeviceLink()->lladdr == $neighbour->lladdr) {
                    $keepers[$keyKeeper]->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED);
                    $keepers[$keyKeeper]->setNeighbour($neighbour);
                    unset($neighboursLeft[$keyNeighbour]);
                }
            }
        }
        return [$keepers, $neighboursLeft];
    }
}
