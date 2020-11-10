<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DeviceStateProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function get(): \stdClass
    {
        return new \stdClass();
    }

    public function set(\stdClass $sc): bool
    {
        return true;
    }
}
