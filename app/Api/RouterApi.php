<?php declare(strict_types=1);

namespace App\Api;

use AdvancedJsonRpc\Request;
use App\Api\Helpers\TimestampFileHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use JsonMapper;
use App\Api\Mappers\TargetLogin;

class RouterApi
{

    protected $config = [];
    protected $configNeededKeys = [
        'login', 'password', 'host', 'url_auth', 'url_neighbours'
    ];
    protected $token;
    protected $client;
    protected $timestampHelper;

    private function checkNeededKeysInConfig($_config)
    {
        foreach ($this->configNeededKeys as $val)
        {
            if (!array_key_exists($val, $_config))
            {
                throw new \InvalidArgumentException(
                    'Invalid configuration for '.__CLASS__.', there is no: '.$val
                );
            }
        }
    }

    private function checkValueOfOneConfig($input)
    {
        if (is_null($input) || !is_string($input))
        {
            return false;
        }
        return $input;
    }

    private function checkValuesOfConfig($_config)
    {
        foreach ($_config as $key => $val)
        {
            if (!$this->checkValueOfOneConfig($val))
            {
                throw new \InvalidArgumentException(
                    'Invalid configuration for '.__CLASS__.', misconfigured: '.$key
                );
            }
        }
        return true;
    }

    public function __construct(
        Client $_client, TimestampFileHelper $_timestampFileHelper, array $_config
    )
    {
        $this->checkNeededKeysInConfig($_config);
        $this->checkValuesOfConfig($_config);

        $this->config['login'] =           $_config['login'];
        $this->config['password'] =        $_config['password'];
        $this->config['host'] =            $_config['host'];
        $this->config['url_auth'] =        $_config['url_auth'];
        $this->config['url_neighbours'] =  $_config['url_neighbours'];

        $this->client = $_client;
        $this->timestampHelper = $_timestampFileHelper;
    }

    public function authorize(): bool
    {
        $request = new Request(1, 'login', [
            $this->config['login'],
            $this->config['password'],
        ]);

        try {
            $result = $this->client->request(
                'POST',
                $this->config['host'] . $this->config['url_auth'],
                ['body' => $request]
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::critical(__CLASS__.':'.__METHOD__.': '.$e->getMessage());
            return false;
        }

        $content = $result->getBody()->getContents();

        try {
            $mapper = new JsonMapper();
            $login = $mapper->map(json_decode($content), new TargetLogin());
        } catch (\JsonMapper_Exception $e) {
            if ($e->getMessage() == 'JSON property "result" in class "App\Api\Mappers\TargetLogin" must not be NULL')
            {
                Log::critical(__CLASS__.':'.__METHOD__.': incorrect login data');
                return false;
            }
        }

        $this->token = $login->result;
        return true;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getNeighbours()
    {
        $request = new Request(2, 'getNeighbours');
        $result = $this->client->request('GET',
            $this->config['host'] . $this->config['url_neighbours'] .'?auth='.$this->getToken());
        return $result->getBody()->getContents();
    }

}
