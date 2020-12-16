<?php declare(strict_types=1);


namespace App\StorageBroker\Helpers;


use App\Api\Router\Structure\Neighbour;
use App\Api\Router\Structure\Neighbours;

class NeighboursRepository extends Neighbours implements \ArrayAccess
{
    public function offsetExists($offset): bool
    {
        return isset($this->neighbours[$offset]);
    }

    public function offsetGet($offset): ?Neighbour
    {
        return isset($this->neighbours[$offset]) ? $this->neighbours[$offset] : null;
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
