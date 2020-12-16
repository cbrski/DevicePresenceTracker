<?php declare(strict_types=1);


namespace App\Console\Commands;


use App\Api\Router\RouterOpenWrt;
use App\Api\Router\Structure\Neighbours;
use App\StorageBroker\DeviceStateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

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
        $app = App::getFacadeApplication();
        $routerApi = $app->make(RouterOpenWrt::class);
        if ($routerApi->authorize())
        {
            $rawData = $routerApi->getNeighbours();
            $neighbours = $app->make(Neighbours::class, ['rawData' => $rawData]);

            /** @var DeviceStateInput $deviceStateInput */
            $deviceStateInput = $app->make(DeviceStateInput::class, ['neighbours' => $neighbours]);

            if ($deviceStateInput->update())
            {
                return 0;
            }
            return 1;
        }
        else
        {
            return 1;
        }
    }
}
