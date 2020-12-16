<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\NeighboursRepository;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Models\VisibleDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class Creator
{
    private $app;

    private function createDevice(Neighbour $neighbour): Device
    {
        $helper = $this->app->make(Helper::class);
        $name = $helper::getNewNameForDevice($neighbour->lladdr, $neighbour->hostname);
        $d = $this->app->make(Device::class);
            $d->name = $name;
        $d->save();
        return $d;
    }

    private function createDeviceLink(Neighbour $neighbour, Device $device): DeviceLink
    {
        $dl = $this->app->make(DeviceLink::class);
            $dl->device_id  = $device->id;
            $dl->lladdr     = $neighbour->lladdr;
            $dl->ipv4       = $neighbour->ip;
            $dl->dev        = $neighbour->dev;
            $dl->hostname   = $neighbour->hostname;
        $dl->save();
        return $dl;
    }

    private function createDeviceLinkStateLog(Neighbour $neighbour, Device $device, DeviceLink $deviceLink): DeviceLinkStateLog
    {
        $dlsl = $this->app->make(DeviceLinkStateLog::class);
        $dlsl->device_id        = $device->id;
        $dlsl->device_link_id   = $deviceLink->id;
        $dlsl->state            = $neighbour->state;
        $dlsl->timestamp        = time();
        $dlsl->save();
        return $dlsl;
    }

    private function createNewVisibleDevice(Neighbour $neighbour): VisibleDevice
    {
        $device             = $this->createDevice($neighbour);
        $deviceLink         = $this->createDeviceLink($neighbour, $device);
        $deviceLinkStateLog = $this->createDeviceLinkStateLog($neighbour, $device, $deviceLink);

        /** @var VisibleDevice $visibleDevice */
        $visibleDevice = $this->app->make(VisibleDevice::class, ['args' => [
            Device::class => $device,
            'links' => [
                [
                    DeviceLink::class => $deviceLink,
                    'lastStates' => [
                        DeviceLinkStateLog::class => $deviceLinkStateLog,
                    ]
                ]
            ]
        ]]);

        return $visibleDevice;
    }

    private function createNewVisibleDeviceKeeper(Neighbour $neighbour): VisibleDeviceKeeper
    {
        $visibleDevice = $this->createNewVisibleDevice($neighbour);
        /** @var VisibleDeviceKeeper $visibleDeviceKeeper */
        $visibleDeviceKeeper = $this->app->make(VisibleDeviceKeeper::class, ['visibleDevice' => $visibleDevice]);
        $visibleDeviceKeeper->setNeighbour($neighbour);
        $visibleDeviceKeeper->setState(VisibleDevicesSynchronizator::INTERNAL_STATE_CREATE_NEW);
        return $visibleDeviceKeeper;
    }

    public function createNew(Collection $keepers, NeighboursRepository $neighboursLeft): array
    {
        $this->app = App::getFacadeRoot();
        /**
         * @var int $keyNeighbour
         * @var Neighbour $neighbour
         */
        foreach ($neighboursLeft as $keyNeighbour => $neighbour) {
            $keepers->push($this->createNewVisibleDeviceKeeper($neighbour));
            unset($neighboursLeft[$keyNeighbour]);
        }
        return [$keepers, $neighboursLeft];
    }
}
