<?php

namespace App\Console\Commands;

use App\Api\Helpers\SettingsHelper;
use App\Api\Helpers\TimestampFileHelper;
use App\Api\RouterApi;
use App\Api\Structure\Neighbours;
use App\StorageBroker\DeviceStateInput;
use App\StorageBroker\Helpers\DeviceMapperDotEnvHelper;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class pullNeighboursFromRouter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pullNeighboursFromRouter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull current state of neighbours (from ip neigh) from router';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routerApi = new RouterApi(
            new Client(),
            new TimestampFileHelper(env('OPENWRT_API_FILE_TIMESTAMP_HELPER')),
            new SettingsHelper(
                [
                    'tokenString' => app(SettingsHelper::class)->tokenString,
                    'tokenAcquisitionTimestamp' => app(SettingsHelper::class)->tokenAcquisitionTimestamp,
                ]
            ),
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        if ($routerApi->authorize())
        {
            $rawData = $routerApi->getNeighbours();
            $deviceStateInput = new DeviceStateInput(new DeviceMapperDotEnvHelper());
            $neighbours = new Neighbours($rawData);
            if ($deviceStateInput->update($neighbours))
            {
                return 0;
            }
            else
            {
                return 1;
            }
        }
    }
}
