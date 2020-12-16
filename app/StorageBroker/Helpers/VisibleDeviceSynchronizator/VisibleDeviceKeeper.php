<?php


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;


use App\Api\Router\Structure\Neighbour;
use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;
use App\StorageBroker\Models\VisibleDevice;

class VisibleDeviceKeeper
{
    private VisibleDevice $visibleDevice;
    private string $state = VisibleDevicesSynchronizator::INTERNAL_STATE_UNTOUCHED;
    private Neighbour $neighbour;

    public function __construct(VisibleDevice $visibleDevice)
    {
        $this->visibleDevice = $visibleDevice;
    }

    public function getVisibleDevice()
    {
        return $this->visibleDevice;
    }

    public function getNeighbour(): ?Neighbour
    {
        return $this->neighbour ?? null;
    }

    public function setNeighbour(Neighbour $neighbour)
    {
        $this->neighbour = $neighbour;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function unpack(): array
    {
        return [
            $this->getNeighbour(),
            $this->getVisibleDevice()->getDevice(),
            $this->getVisibleDevice()->getDeviceLink(),
            $this->getVisibleDevice()->getDeviceLinkStateLog(),
        ];
    }

    public function pack(VisibleDeviceKeeper $k, Device $d, DeviceLink $dl, DeviceLinkStateLog $dlsl): self
    {
        $this->getVisibleDevice()->setDevice($d);
        $this->getVisibleDevice()->setDeviceLink($dl);
        $this->getVisibleDevice()->setDeviceLinkStateLog($dlsl);
        return $this;
    }

    public function save(): bool
    {
        return $this->visibleDevice->save();
    }
}
