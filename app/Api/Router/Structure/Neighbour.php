<?php declare(strict_types=1);


namespace App\Api\Router\Structure;


use Illuminate\Support\Facades\App;
use Symfony\Component\String\UnicodeString;

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
            if ($name == 'lladdr' && !is_null($this->lladdr)) {
                /** @var UnicodeString $unicodeString */
                $unicodeString = App::getFacadeRoot()->make(UnicodeString::class, ['string' => $this->lladdr]);
                return $unicodeString->upper()->toString();
            }
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
