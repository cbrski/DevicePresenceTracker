<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Models\VisibleDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class NotMatchedUpdater
{
    public function update(Collection $keepers): Collection
    {
        /** @var Collection $keepers */
        return $keepers->map(function ($keeper, $key) {

            /** @var VisibleDeviceKeeper $keeper */
            if ($keeper->getState() == VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED) {

                /**
                 * @var VisibleDevice $vd
                 * @var DeviceLinkStateLog $dlsl
                 */
                $vd = $keeper->getVisibleDevice();
                $dlsl = $vd->getDeviceLinkStateLog();
                if ($dlsl->state != DeviceLinkStateLog::STATE_OFFLINE) {
                    $dlsl = App::getFacadeRoot()->make(DeviceLinkStateLog::class);

                    $dlsl->device_id =       $vd->getDevice()->id;
                    $dlsl->device_link_id =  $vd->getDeviceLink()->id;
                    $dlsl->state =           DeviceLinkStateLog::STATE_OFFLINE;
                    $dlsl->timestamp =       time();
                }
                $vd->setDeviceLinkStateLog($dlsl);

                $keeper->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_UPDATED_NOT_MATCHED);
            }
            return $keeper;
        });
    }
}
