<?php


namespace App\Api\Helpers;


use Spatie\LaravelSettings\Settings;

class SettingsHelper extends Settings
{
    public string $tokenString;
    public int $tokenAcquisitionTimestamp;

    public static function group(): string
    {
        return 'RouterApiSettings';
    }
}
