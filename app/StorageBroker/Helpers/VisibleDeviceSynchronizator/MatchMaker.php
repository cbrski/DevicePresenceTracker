<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\DeviceLinkStateLog;
use App\Helpers\IpAddressInversion;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Models\VisibleDevice;
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
                    $keeperHostname     = $kDl->hostname;
                    $keeperIpv4Address  = $kDl->ipv4;

                    $isNullNeighbourLlladdr = is_null($neighbour->lladdr);
                    $isFailedNeighbourState = (0 == strcasecmp($neighbour->state, DeviceLinkStateLog::STATE_FAILED));
                    $isEqualHostnames       = $neighbour->hostname == $keeperHostname;
                    $isEqualIpAddresses     = $neighbour->ip == $keeperIpv4Address;
                    $isEqualsLlladdrs       = $neighbour->lladdr == $keeperLladdr;

                    if (
                        ( $isNullNeighbourLlladdr
                            && $isFailedNeighbourState
//                                && $isEqualHostnames )
                                && $isEqualIpAddresses )
                        || $isEqualsLlladdrs
                    ) {
                        $keeper->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED);
                        $keeper->setNeighbour($neighbour);

                        $deviceName = $keeper->getVisibleDevice()->getDevice()->name;
                        $deviceLinkIP = $keeper->getVisibleDevice()->getDeviceLink()->ip;

                        unset($neighboursLeft[$keyNeighbour]);
                    }
                }
        });
        return [$keepers, $neighboursLeft];
    }
}
