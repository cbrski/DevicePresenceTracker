<?php


use Spatie\LaravelSettings\Migrations\SettingsMigration;

class createRouterApiSettingsHelper extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('RouterApiSettings.tokenString', 'none');
        $this->migrator->add('RouterApiSettings.tokenAcquisitionTimestamp', 0);
    }
}
