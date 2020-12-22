<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class MatchedUpdater
{
    public function update(Collection $keepers): Collection
    {
        /** @var Collection $keepers */
        return $keepers->map(function ($keeper, $key) {

            /** @var VisibleDeviceKeeper $keeper */
            if ($keeper->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_MATCHED) {

                /**
                 * @var Neighbour $n
                 * @var Device $d
                 * @var DeviceLink $dl
                 * @var DeviceLinkStateLog $dlsl
                 */
                [$n, $d, $dl, $dlsl] = $keeper->unpack();

                /** @var Helper $helper */
                $helper = App::getFacadeRoot()->make(Helper::class);
                $d->name = $helper::getUpdatedNameForDevice($dl->lladdr, $d->name);

                $dl->ipv4 = $n->ip;
                $dl->dev = $n->dev;
                $dl->hostname = $n->hostname;

                if (0 != strcasecmp($dlsl->state, $n->state)) {
                    $dlsl = DeviceLinkStateLog::make([
                        'device_id' => $d->id,
                        'device_link_id' => $dl->id,
                        'state' => strtolower($n->state),
                        'timestamp' => time(),
                    ]);
                }

                $keeper = $keeper->pack($keeper, $d, $dl, $dlsl);
                $keeper->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_MATCHED);
            }
            return $keeper;
        });
    }
}
