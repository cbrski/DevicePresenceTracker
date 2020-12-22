<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\StorageBroker\Helpers\DeviceMapperDotEnvHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class Helper
{
    const UNDEFINED_DEVICE_NAME_PREFIX = '?_';
    const UNDEFINED_DEVICE_NAME_LENGTH = 8;

    private static function getHostnameByLladdr(string $lladdr): ?string
    {
        $deviceMapper = App::getFacadeRoot()->make(DeviceMapperDotEnvHelper::class);
        return $deviceMapper->getHostnameByLladdr($lladdr);
    }

    public static function getNewNameForDevice(string $lladdr, ?string $hostname = null): string
    {
        $definedName = self::getHostnameByLladdr($lladdr);
        $definedName = $definedName ?? $hostname;
        $definedName = $definedName ?? self::UNDEFINED_DEVICE_NAME_PREFIX.strtolower(Str::random(
                self::UNDEFINED_DEVICE_NAME_LENGTH - strlen(self::UNDEFINED_DEVICE_NAME_PREFIX)
            ));
        return $definedName;
    }

    public static function getUpdatedNameForDevice(string $lladdr, string $currentName): string
    {
        if ($newName = self::getHostnameByLladdr($lladdr)) {
            return $newName;
        }
        return $currentName;
    }
}
