<?php


namespace Tests\Helpers;


class RegisterDependsCrossClass
{
    private static array $registry;

    public static function set($key, $value)
    {
        self::$registry[$key] = $value;
    }

    public static function get($key)
    {
        return isset(self::$registry[$key]) ? self::$registry[$key] : null;
    }
}
