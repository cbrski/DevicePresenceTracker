<?php


namespace App\Api\Structure;


class Neighbours implements \Iterator, \Countable
{
    private int $timestamp;

    private array $neighbours = [];
    private int $position = 0;

    private function populate(\stdClass $rawData): void
    {
        $this->timestamp = $rawData->timestamp;
        foreach ($rawData->neighbours as $key => $val)
        {
            $n = new Neighbour($val);
            $this->neighbours[] = $n;
        }
    }

    public function __construct($rawData)
    {
        $this->populate($rawData);
        $this->position = 0;
    }

    public function current()
    {
        return $this->neighbours[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
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
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->neighbours);
    }
}
