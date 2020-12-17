<?php declare(strict_types=1);


namespace App\Api\Router\Helpers;



interface AuthorizationInterface
{
    public function authorize(): bool;
}
