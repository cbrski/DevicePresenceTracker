<?php declare(strict_types=1);


namespace Tests\Helpers;


use App\DeviceLinkStateLog;
use Faker\Generator;
use Illuminate\Support\Facades\App;

class NeighboursFaker
{
    private int $timestamp;
    private int $count = 1;
    /** @var Generator */
    private $faker;

    public function __construct()
    {
        $this->faker = App::getFacadeRoot()->make(Generator::class);
        $this->timestamp = time();
    }

    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    private function randomDev(): string
    {
        $s = ['eth', 'wlan'];
        return $s[rand(0,1)].rand(0,9);
    }

    private function createOneNeighbour(): \stdClass
    {
        $neighbour = new \stdClass();
        $neighbour->ip = $this->faker->unique()->localIpv4();
        $neighbour->dev = $this->randomDev();
        $neighbour->lladdr = strtolower($this->faker->unique()->macAddress());
        $neighbour->hostname = $this->faker->unique()->colorName();
        $neighbour->state = $this->faker->randomElement([
            DeviceLinkStateLog::STATE_PERMAMENT,
            DeviceLinkStateLog::STATE_NOARP,
            DeviceLinkStateLog::STATE_REACHABLE,
            DeviceLinkStateLog::STATE_STALE,
            DeviceLinkStateLog::STATE_NONE,
            DeviceLinkStateLog::STATE_INCOMPLETE,
            DeviceLinkStateLog::STATE_DELAY,
            DeviceLinkStateLog::STATE_PROBE,
            DeviceLinkStateLog::STATE_FAILED,
        ]);
        return $neighbour;
    }

    private function createNeighbours(): array
    {
        $neighbours = [];
        for ($i=0 ; $i<$this->count ; ++$i) {
            $neighbours[] = $this->createOneNeighbour();
        }
        return $neighbours;
    }

    public function create(): \stdClass
    {
        $rawData = [
            'timestamp' => $this->timestamp,
            'neighbours' => $this->createNeighbours(),
        ];
        return (object) $rawData;
    }
}
