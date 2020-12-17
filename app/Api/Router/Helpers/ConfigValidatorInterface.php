<?php declare(strict_types=1);


namespace App\Api\Router\Helpers;



interface ConfigValidatorInterface
{
    public function validate(array $_config);
}
