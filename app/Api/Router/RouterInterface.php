<?php declare(strict_types=1);


namespace App\Api\Router;



interface RouterInterface
{
    public function authorize();
    public function getToken(): string;
    public function getNeighbours(): \stdClass;
}
