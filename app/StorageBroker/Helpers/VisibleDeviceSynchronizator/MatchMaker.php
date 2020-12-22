<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use Illuminate\Support\Collection;

class MatchMaker
{
    public function match(Collection $keepers, NeighboursRepository $neighboursLeft): array
    {
        $keepers->map(function ($keeper, $key) use ($neighboursLeft) {
                /**
                 * @var int $keyNeighbour
                 * @var Neighbour $neighbour
                 * @var int $keyKeeper
                 * @var VisibleDeviceKeeper $keeper
                 * @var VisibleDeviceKeeper[] $keepers
                 */
                foreach ($neighboursLeft as $keyNeighbour => $neighbour) {

                    $kDl                = $keeper->getVisibleDevice()->getDeviceLink();
                    $keeperLladdr       = $kDl->lladdr;
                    $keeperIpv4Address  = $kDl->ipv4;

                    $isNullNeighbourLlladdr = is_null($neighbour->lladdr);
                    $isFailedNeighbourState = (0 == strcasecmp($neighbour->state, DeviceLinkStateLog::STATE_FAILED));
                    $isEqualIpAddresses     = $neighbour->ip == $keeperIpv4Address;
                    $isEqualsLlladdrs       = $neighbour->lladdr == $keeperLladdr;

                    if (
                        ( $isNullNeighbourLlladdr
                            && $isFailedNeighbourState
                                && $isEqualIpAddresses )
                        || $isEqualsLlladdrs
                    ) {
                        $keeper->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED);
                        $keeper->setNeighbour($neighbour);
                        unset($neighboursLeft[$keyNeighbour]);
                    }
                }
        });
        return [$keepers, $neighboursLeft];
    }
}
