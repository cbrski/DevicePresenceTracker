<?php declare(strict_types=1);


namespace App\Api\Router\Structure;


class Neighbours implements \Iterator, \Countable
{
    protected int $timestamp;

    protected array $neighbours = [];
    protected int $position = 0;

    protected \stdClass $rawData;

    private function populate(\stdClass $rawData): void
    {
        $this->timestamp = $rawData->timestamp;
        foreach ($rawData->neighbours as $key => $val)
        {
            $n = new Neighbour($val);
            $this->neighbours[] = $n;
        }
    }

    public function getRawData(): \stdClass
    {
        return $this->rawData;
    }

    public function __construct(\stdClass $rawData)
    {
        $this->rawData = $rawData;
        $this->populate($rawData);
        $this->position = 0;
    }

    public function current()
    {
        return $this->neighbours[$this->position];
    }

    public function next(): void
    {
        $lastKey = array_key_last($this->neighbours);

        if (is_null($lastKey) || $this->position == $lastKey) {
            ++$this->position;
            return;
        }

        if ($this->position < $lastKey) {
            $found = false;
            while (!$found) {
                if (array_key_exists(++$this->position, $this->neighbours)) {
                    $found = true;
                }
            }
        }
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->neighbours[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = (int) array_key_first($this->neighbours);
    }

    public function count(): int
    {
        return count($this->neighbours);
    }
}
