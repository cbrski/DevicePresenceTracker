<?php declare(strict_types=1);


namespace App\StorageBroker;


use App\Api\Router\Structure\Neighbours;
use App\StorageBroker\Helpers\VisibleDevicesSynchronizator;

class DeviceStateInput
{
    private Neighbours $neighbours;
    private VisibleDevicesSynchronizator $synchronizer;

    public function __construct(Neighbours $neighbours, VisibleDevicesSynchronizator $synchronizer)
    {
        $this->neighbours = $neighbours;
        $this->synchronizer = $synchronizer;
    }

    public function setNeighbours(Neighbours $neighbours): void
    {
        $this->neighbours = $neighbours;
    }

    public function setSynchronizer(VisibleDevicesSynchronizator $synchronizer): void
    {
        $this->synchronizer = $synchronizer;
    }

    public function update(): bool
    {
        if ($this->synchronizer->sync($this->neighbours)) {
            return true;
        }
        return false;
    }
}
