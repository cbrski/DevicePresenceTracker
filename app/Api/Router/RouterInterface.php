<?php


namespace App\Api\Router;



interface RouterInterface
{
    public function authorize();
    public function getToken(): string;
    public function getNeighbours(): \stdClass;
}
