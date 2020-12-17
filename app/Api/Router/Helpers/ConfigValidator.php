<?php declare(strict_types=1);


namespace App\Api\Router\Helpers;



class ConfigValidator implements ConfigValidatorInterface
{
    protected array $configNeededKeys = [
        'login', 'password', 'host', 'url_auth', 'url_neighbours', 'session_timeout'
    ];

    private function validateValueOfOneConfig($input)
    {
        if (is_null($input) || !is_string($input))
        {
            return false;
        }
        return $input;
    }

    private function validateNeededKeysInConfig($_config)
    {
        foreach ($this->configNeededKeys as $val)
        {
            if (!array_key_exists($val, $_config))
            {
                throw new \InvalidArgumentException(
                    'Invalid configuration, there is no: '.$val
                );
            }
        }
    }

    private function validateValuesOfConfig($_config)
    {
        foreach ($_config as $key => $val)
        {
            if (!$this->validateValueOfOneConfig($val))
            {
                throw new \InvalidArgumentException(
                    'Invalid configuration, misconfigured: '.$key
                );
            }
        }
        return true;
    }

    public function validate(array $_config)
    {
        $this->validateNeededKeysInConfig($_config);
        $this->validateValuesOfConfig($_config);
    }
}
