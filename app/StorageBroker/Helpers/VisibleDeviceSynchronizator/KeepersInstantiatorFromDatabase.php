<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers\VisibleDeviceSynchronizator;



use App\StorageBroker\Helpers\RawDatabaseReader;
use App\StorageBroker\Models\VisibleDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class KeepersInstantiatorFromDatabase
{
    private function arrayToCollection(array $raws): Collection
    {
        $app = App::getFacadeRoot();
        $collection = new Collection();
        foreach ($raws as $raw) {
            $visibleDevice = $app->make(VisibleDevice::class, ['args' => $raw]);
            $visibleDeviceKeeper = $app
                ->make(VisibleDeviceKeeper::class, ['visibleDevice' => $visibleDevice]);
            $collection->add($visibleDeviceKeeper);
        }
        return $collection;
    }

    public function getAll(RawDatabaseReader $rawDatabaseReader): Collection
    {
        $raws = $rawDatabaseReader->getAll();
        return $this->arrayToCollection($raws);
    }
}
