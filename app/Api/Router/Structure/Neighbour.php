<?php


namespace App\Api\Router\Structure;


class Neighbour
{
    private $ip;
    private $dev;
    private $lladdr;
    private $state;
    private $hostname;

    public function __construct(\stdClass $rawNeighbour)
    {
        foreach(['ip', 'dev', 'lladdr', 'state', 'hostname'] as $val) {
            if (isset($rawNeighbour->{$val}))  {
                $this->{$val} = $rawNeighbour->{$val};
            } else {
                $this->{$val} = null;
            }
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return false;
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
        return false;
    }
}
