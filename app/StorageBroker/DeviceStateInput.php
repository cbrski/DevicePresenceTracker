<?php


namespace App\StorageBroker;


use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use App\Helpers\IpAddressInversion;
use App\StorageBroker\Helpers\DeviceMapperDotEnvHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceStateInput
{
    const _IP = 'ip';
    const _IPV4 = 'ipv4';
    const _IPV6 = 'ipv6';
    const _DEV = 'dev';
    const _LLADDR = 'lladdr';
    const _STATE = 'state';
    const _HOSTNAME = 'hostname';

    const UNDEFINED_DEVICE_NAME_PREFIX = '?_';
    const UNDEFINED_DEVICE_NAME_LENGTH = 8;

    const ACTION_DEVICE_NEW = 100;
    const ACTION_DEVICE_UPDATE_ONLINE = 101;
    const ACTION_DEVICE_UPDATE_OFFLINE = 102;

    private DeviceMapperDotEnvHelper $deviceMapper;

    private $exceptions = [];

    private function getNameForDevice(string $lladdr, string $hostname = null): string
    {
        $definedName = $this->deviceMapper->getHostnameByLladdr($lladdr);
        $definedName = $definedName ?? $hostname;
        $definedName = $definedName ?? self::UNDEFINED_DEVICE_NAME_PREFIX.Str::random(
            self::UNDEFINED_DEVICE_NAME_LENGTH - strlen(self::UNDEFINED_DEVICE_NAME_PREFIX)
            );
        return $definedName;
    }

    private function getLastState(DeviceLink $deviceLink): string
    {
        return $deviceLink->device_link_state_logs->toQuery()->orderBy('timestamp', 'desc')->firstOrFail()->state;
    }

    private function setNewLastState(DeviceLink $deviceLink, string $state): void
    {
        $deviceLinkStateLog = new DeviceLinkStateLog([
            'device_id' => $deviceLink->device->id,
            'device_link_id' => $deviceLink->id,
            'timestamp' => time(),
            'state' => strtolower($state)
        ]);
        $deviceLinkStateLog->save();
    }

    private function isLastStateActual(DeviceLink $deviceLink, string $state): bool
    {
        $lastState = $this->getLastState($deviceLink);
        if (0 == strcasecmp($lastState, $state))
        {
            return true;
        }
        return false;
    }

    private function updateState(DeviceLink $deviceLink, string $state): void
    {
        if (! $this->isLastStateActual($deviceLink, $state))
        {
            $this->setNewLastState($deviceLink, $state);
        }
    }

    private function getDevice(array $args): Device
    {
        $device = Device::where([$args])->firstOrFail();
        return $device;
    }

    private function getDeviceLink(array $args): DeviceLink
    {
        $deviceLink = DeviceLink::where($args)->firstOrFail();
        return $deviceLink;
    }

    private function updateDeviceLink(DeviceLink $deviceLink, \stdClass $neighbour): DeviceLink
    {
        foreach(['lladdr', 'dev', 'ipv4', 'hostname'] as $valDatabase)
        {
            if ($valDatabase == 'ipv4')
            {
                $deviceLink->{$valDatabase} = $neighbour->ip;
            }
            else
            {
                $deviceLink->{$valDatabase} = $neighbour->{$valDatabase};
            }
        }
        if ($deviceLink->isDirty())
        {
            $deviceLink->save();
        }
        return $deviceLink;
    }

    private function updateDevice(DeviceLink $deviceLink, \stdClass $neighbour): Device
    {
        $device = $this->getDevice(['id', '=', $deviceLink->device_id]);
        $nameCurrent = $device->name;
        $nameProposal = $this->getNameForDevice($neighbour->{self::_LLADDR}, $neighbour->{self::_HOSTNAME});
        if (substr($nameCurrent, 0, 1) == self::UNDEFINED_DEVICE_NAME_PREFIX
            && ! (substr($nameProposal, 0, 1) == self::UNDEFINED_DEVICE_NAME_PREFIX))
        {
            $device->name = $nameProposal;
        }
        if ($device->isDirty())
        {
            $device->save();
        }
        return $device;
    }

    private function databaseDeviceUpdateOnline(\stdClass $neighbour): bool
    {
        DB::beginTransaction();
        try {
            $deviceLink = $this->getDeviceLink([[self::_LLADDR, '=', $neighbour->{self::_LLADDR}]]);
            $deviceLink = $this->updateDeviceLink($deviceLink, $neighbour);
            $this->updateDevice($deviceLink, $neighbour);
            $this->updateState($deviceLink, $neighbour->{self::_STATE});
        }
        catch (\Exception $e)
        {
            Log::critical(__CLASS__.':'.__METHOD__.': '.$e->getMessage());
            DB::rollBack();
            $this->exceptions[] = $e;
            return false;
        }
        DB::commit();
        return true;
    }

    private function databaseDeviceUpdateOffline(\stdClass $neighbour): bool
    {
        DB::beginTransaction();
        try {
            $deviceLink = $this->getDeviceLink([
                [self::_IPV4, '=', IpAddressInversion::ip2long($neighbour->{self::_IP})],
                [self::_DEV, '=', $neighbour->{self::_DEV}],
            ]);
            $this->updateState($deviceLink, $neighbour->state);
        }
        catch (\Exception $e)
        {
            Log::critical(__CLASS__.':'.__METHOD__.': '.$e->getMessage());
            DB::rollBack();
            $this->exceptions[] = $e;
            return false;
        }
        DB::commit();
        return true;
    }

    private function newDevice(string $lladdr, string $hostname = null): Device
    {
        $device = new Device([
            'name' => $this->getNameForDevice($lladdr, $hostname),
        ]);
        $device->save();
        return $device;
    }

    private function newDeviceLink(Device $device, \stdClass $neighbour): DeviceLink
    {
        $deviceLink = new DeviceLink([
            'device_id' =>  $device->id,
            'lladdr' =>     $neighbour->{self::_LLADDR},
            'dev' =>        $neighbour->{self::_DEV},
            'ipv4' =>       $neighbour->{self::_IP},
            'hostname' =>   $neighbour->{self::_HOSTNAME},
        ]);
        $deviceLink->save();
        return $deviceLink;
    }

    private function newDeviceLinkStateLog(DeviceLink $deviceLink, string $state): DeviceLinkStateLog
    {
        $deviceLinkStateLog = new DeviceLinkStateLog([
            'device_id' =>      $deviceLink->device->id,
            'device_link_id' => $deviceLink->id,
            'timestamp' =>      time(),
            'state' =>          strtolower($state),
        ]);
        $deviceLinkStateLog->save();
        return $deviceLinkStateLog;
    }

    private function databaseDeviceNew(\stdClass $neighbour): bool
    {
        DB::beginTransaction();
        try {
            $device = $this->newDevice($neighbour->{self::_LLADDR}, $neighbour->{self::_HOSTNAME});
            $deviceLink = $this->newDeviceLink($device, $neighbour);
            $this->newDeviceLinkStateLog($deviceLink, $neighbour->{self::_STATE});
        }
        catch (\Exception $e)
        {
            Log::critical(__CLASS__.':'.__METHOD__.': '.$e->getMessage());
            DB::rollBack();
            $this->exceptions[] = $e;
            return false;
        }
        DB::commit();
        return true;
    }

    private function isDeviceOnlineOnRouter($lladdr, $state): bool
    {
        $a = !is_null($lladdr);
        $b = !is_null($state);
        $c = strcasecmp(DeviceLinkStateLog::STATE_FAILED, $state) === 0 ? false : true;
        if ($a && $b && $c)
        {
            return true;
        }
        return false;
    }

    private function isDeviceAlreadyTracked($lladdr, $dev, $ip): bool
    {
        if (!is_null($lladdr))
        {
            $deviceLink = DeviceLink::where(self::_LLADDR, $lladdr)->get();
            return ! $deviceLink->isEmpty();
        }
        else
        {
            if (!is_null($dev) && !is_null($ip))
            {
                 $deviceLink = DeviceLink::where([
                    [self::_DEV, '=', $dev],
                    [self::_IPV4, '=', IpAddressInversion::ip2long($ip)]
                ])->get();
                return ! $deviceLink->isEmpty();
            }
            else
            {
                return false;
            }
        }
    }

    private function decideWhatAction(\stdClass $neighbour): int
    {
        $lladdr = $neighbour->{self::_LLADDR};
        $dev = $neighbour->{self::_DEV};
        $ip = $neighbour->{self::_IP};
        $state = $neighbour->{self::_STATE};

        $deviceAlreadyTracked = $this->isDeviceAlreadyTracked($lladdr, $dev, $ip);
        if ($this->isDeviceOnlineOnRouter($lladdr, $state))
        {
            if ($deviceAlreadyTracked)
            {
                return self::ACTION_DEVICE_UPDATE_ONLINE;
            }
            else
            {
                return self::ACTION_DEVICE_NEW;
            }
        }
        else
        {
            if ($deviceAlreadyTracked)
            {
                return self::ACTION_DEVICE_UPDATE_OFFLINE;
            }
        }
        return 0;
    }

    private function iterateOverData(array $neighbours): void
    {
        foreach ($neighbours as $key => $neighbour)
        {
            switch ($this->decideWhatAction($neighbour))
            {
                case self::ACTION_DEVICE_NEW:
                    $this->databaseDeviceNew($neighbour);
                    break;
                case self::ACTION_DEVICE_UPDATE_ONLINE:
                    $this->databaseDeviceUpdateOnline($neighbour);
                    break;
                case self::ACTION_DEVICE_UPDATE_OFFLINE:
                    $this->databaseDeviceUpdateOffline($neighbour);
                    break;
            }
        }
    }

    public function __construct(DeviceMapperDotEnvHelper $_deviceMapper)
    {
        $this->deviceMapper = $_deviceMapper;
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function update(\stdClass $_routerData): bool
    {
        $this->iterateOverData($_routerData->neighbours);
        if (empty($this->exceptions))
        {
            return true;
        }
        return false;
    }

}
