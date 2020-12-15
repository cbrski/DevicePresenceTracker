<?php declare(strict_types=1);


namespace Tests\Helpers;


use App\DeviceLinkStateLog;
use Faker\Generator;
use Illuminate\Support\Facades\App;

class NeighboursFaker implements \ArrayAccess
{
    private int $timestamp;
    private int $count = 1;
    /** @var Generator */
    private $faker;

    private array $neighbours;
    private array $rawData;

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

    /** @var string[] $avoid */
    public static function getRandomState(array $avoid = []): string
    {
        $allStates = [
            DeviceLinkStateLog::STATE_PERMAMENT,
            DeviceLinkStateLog::STATE_NOARP,
            DeviceLinkStateLog::STATE_REACHABLE,
            DeviceLinkStateLog::STATE_STALE,
            DeviceLinkStateLog::STATE_NONE,
            DeviceLinkStateLog::STATE_INCOMPLETE,
            DeviceLinkStateLog::STATE_DELAY,
            DeviceLinkStateLog::STATE_PROBE,
            DeviceLinkStateLog::STATE_FAILED,
        ];
        do {
            $candidateKey = array_rand($allStates);
        } while (in_array($avoid, $allStates));

        return $allStates[$candidateKey];
    }

    private function createOneNeighbour(): \stdClass
    {
        $neighbour = new \stdClass();
        $neighbour->ip = $this->faker->unique()->localIpv4();
        $neighbour->dev = $this->randomDev();
        $neighbour->lladdr = strtolower($this->faker->unique()->macAddress());
        $neighbour->hostname = $this->faker->unique()->colorName();
        $neighbour->state = self::getRandomState();
        return $neighbour;
    }

    private function createNeighbours(): array
    {
        for ($i=0 ; $i<$this->count ; ++$i) {
            $this->neighbours[] = $this->createOneNeighbour();
        }
        return $this->neighbours;
    }

    public function create(): self
    {
        $this->rawData = [
            'timestamp' => $this->timestamp,
            'neighbours' => $this->createNeighbours(),
        ];
        return $this;
    }

    public function toObject(): \stdClass
    {
        return (object) $this->rawData;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->neighbours[$offset]);
    }

    public function offsetGet($offset): ?\stdClass
    {
        return $this->neighbours[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->neighbours[] = $value;
        } else {
            $this->neighbours[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->neighbours[$offset]);
    }
}
