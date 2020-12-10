<?php

namespace App\Providers;

use App\Api\Router\Helpers\SettingsHelper;
use App\Api\Router\Helpers\TimestampFileHelper;
use App\Api\Router\RouterApi;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TimestampFileHelper::class, function($app) {
            return new TimestampFileHelper(env('OPENWRT_API_FILE_TIMESTAMP_HELPER'));
        });

        $this->app->bind(RouterApi::class, function($app) {
            $key = 'OPENWRT_API_';

            return new RouterApi(
                $this->app->make(Client::class),
                $this->app->make(TimestampFileHelper::class),
                $this->app->make(SettingsHelper::class),
                [
                    'login' =>              env($key.'LOGIN'),
                    'password' =>           env($key.'PASSWORD'),
                    'host' =>               env($key.'HOST'),
                    'url_auth' =>           env($key.'URL_AUTH'),
                    'url_neighbours' =>     env($key.'URL_NEIGHBOURS'),
                    'session_timeout' =>    env($key.'SESSION_TIMEOUT'),
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
