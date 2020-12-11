<?php


namespace App\Api\Router\Helpers;


interface AuthorizationInterface
{
    public function authorize(): bool;
}
