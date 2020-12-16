<?php declare(strict_types=1);

namespace App\StorageBroker\Models;


use App\DeviceLink;
use App\DeviceLinkStateLog;

class VisibleDeviceLink
{
    protected DeviceLink $deviceLink;
    protected DeviceLinkStateLog $deviceLinkStateLog;

    public function __construct(array $args)
    {
        $this->deviceLink = $args[DeviceLink::class];
        $this->deviceLinkStateLog = $args['lastStates'][DeviceLinkStateLog::class];
    }

    public function getDeviceLink(): DeviceLink
    {
        return $this->deviceLink;
    }

    public function setDeviceLink(DeviceLink $deviceLink): void
    {
        $this->deviceLink = $deviceLink;
    }

    public function getDeviceLinkStateLog(): DeviceLinkStateLog
    {
        return $this->deviceLinkStateLog;
    }

    public function setDeviceLinkStateLog(DeviceLinkStateLog $deviceLinkStateLog): void
    {
        $this->deviceLinkStateLog = $deviceLinkStateLog;
    }

    public function save(): bool
    {
        if ($this->deviceLink->save() && $this->deviceLinkStateLog->save()) {
            return true;
        }
        return false;
    }
}
