<?php


namespace App\StorageBroker\Helpers;


class DeviceMapperDotEnvHelper
{
    private const DOT_ENV_KEY_NAME = 'OPENWRT_MAP_DEVICE';
    private const DOT_ENV_DELIMITER = '..';

    private static $mappings;

    private static function getMappers(): void
    {
        $key = 1;
        while($mapping = env(self::DOT_ENV_KEY_NAME.'_'.$key++))
        {
            list($hostname, $lladdr) = explode(self::DOT_ENV_DELIMITER, $mapping);
            self::$mappings[$lladdr] = $hostname;
        }
    }

    public static function getHostnameByLladdr(string $lladdr)
    {
        self::getMappers();
        $hostname = null;
        if (isset(self::$mappings[$lladdr]) && !empty(self::$mappings[$lladdr]))
        {
            $hostname = self::$mappings[$lladdr];
        }
        return $hostname;
    }

}
