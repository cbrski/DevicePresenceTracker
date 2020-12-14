<?php declare(strict_types=1);

namespace App\StorageBroker\Models;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Illuminate\Support\Facades\App;

class VisibleDevice
{
    protected Device $device;

    /** @var VisibleDeviceLink[] */
    protected array $visibleDeviceLinks = [];

    public function __construct(array $args)
    {
        $this->device = $args[Device::class];
        $this->createLinks($args['links']);
        return null;
    }

    private function createLinks(array $args)
    {
        foreach ($args as $link) {
            $this->visibleDeviceLinks[] = App::getFacadeRoot()->make(VisibleDeviceLink::class, ['args' => $link]);
        }
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    public function getDeviceLink(): DeviceLink
    {
        return $this->visibleDeviceLinks[0]->getDeviceLink();
    }

    public function setDeviceLink(DeviceLink $deviceLink): void
    {
        $this->visibleDeviceLinks[0]->setDeviceLink($deviceLink);
    }

    public function getDeviceLinkStateLog(): DeviceLinkStateLog
    {
        return $this->visibleDeviceLinks[0]->getDeviceLinkStateLog();
    }

    public function setDeviceLinkStateLog(DeviceLinkStateLog $deviceLinkStateLog): void
    {
        $this->visibleDeviceLinks[0]->setDeviceLinkStateLog($deviceLinkStateLog);
    }

    public function save(): bool
    {
        if ($this->device->save() && $this->visibleDeviceLinks[0]->save()) {
            return true;
        }
        return false;
    }
}
