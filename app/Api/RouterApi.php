<?php declare(strict_types=1);

namespace App\Api;

use AdvancedJsonRpc\Request;
use App\Api\Helpers\SettingsHelper;
use App\Api\Helpers\TimestampFileHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use JsonMapper;
use App\Api\Mappers\TargetLogin;
use Psr\Http\Message\ResponseInterface;

class RouterApi
{
    protected array $config = [];
    protected array $configNeededKeys = [
        'login', 'password', 'host', 'url_auth', 'url_neighbours', 'session_timeout'
    ];
    protected Client $client;
    protected TimestampFileHelper $timestampHelper;
    protected SettingsHelper $settings;

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
        Client $_client,
        TimestampFileHelper $_timestampFileHelper,
        SettingsHelper $_settings,
        array $_config
    )
    {
        $this->checkNeededKeysInConfig($_config);
        $this->checkValuesOfConfig($_config);

        $this->config['login'] =           $_config['login'];
        $this->config['password'] =        $_config['password'];
        $this->config['host'] =            $_config['host'];
        $this->config['url_auth'] =        $_config['url_auth'];
        $this->config['url_neighbours'] =  $_config['url_neighbours'];
        $this->config['session_timeout'] = $_config['session_timeout'];

        $this->client = $_client;
        $this->timestampHelper = $_timestampFileHelper;
        $this->settings = $_settings;
    }

    private function isTokenExpired(): bool
    {
        $timestampBorder = time()-$this->config['session_timeout'];

        $timestampFromDatabase = $this->settings->tokenAcquisitionTimestamp;
        $timestampFromFile = $this->timestampHelper->getTimestamp();

        $authorityDatabase =    $timestampBorder < $timestampFromDatabase;
        $authorityFile =        $timestampBorder < $timestampFromFile;

        if ($authorityDatabase && $authorityFile)
        {
            return false;
        }
        return true;
    }

    private function isSetToken(): bool
    {
        if (strlen($this->settings->tokenString) == 32)
        {
            return true;
        }
        return false;
    }

    private function isOngoingSession(): bool
    {
        if (!$this->isTokenExpired() && $this->isSetToken())
        {
            return true;
        }
        return false;
    }

    private function keepToken($token): void
    {
        $this->settings->tokenString = $token;
        $this->settings->tokenAcquisitionTimestamp = time();
        $this->settings->save();
        $this->timestampHelper->setTimestamp();
    }

    private function prepareLoginRequestBody(): Request
    {
        return new Request(1, 'login', [
            $this->config['login'],
            $this->config['password'],
        ]);
    }

    private function sendLoginRequest(Request $requestBody)
    {
        try {
            $result = $this->client->request(
                'POST',
                $this->config['host'] . $this->config['url_auth'],
                ['body' => $requestBody]
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::critical(__CLASS__.':'.__METHOD__.': '.$e->getMessage());
            return false;
        }
        return $result;
    }

    private function getLoginResponse(string $content)
    {
        try {
            $mapper = new JsonMapper();
            $loginResponse = $mapper->map(json_decode($content), new TargetLogin());
        } catch (\JsonMapper_Exception $e) {
            if ($e->getMessage() == 'JSON property "result" in class "App\Api\Mappers\TargetLogin" must not be NULL')
            {
                Log::critical(__CLASS__.':'.__METHOD__.': incorrect login data');
                return false;
            }
            Log::critical(__CLASS__.':'.__METHOD__.': cannot login');
            return false;
        }
        return $loginResponse;
    }

    public function authorize(): bool
    {
        if ($this->isOngoingSession())
        {
            return true;
        }

        if (! ($result = $this->sendLoginRequest($this->prepareLoginRequestBody()) ))
        {
            return false;
        }

        if (! ($loginResponse = $this->getLoginResponse($result->getBody()->getContents()) ))
        {
            return false;
        }

        $this->keepToken($loginResponse->result);
        return true;
    }

    public function getToken()
    {
        return $this->settings->tokenString;
    }

    public function getNeighbours()
    {
        $request = new Request(2, 'getNeighbours');
        $result = $this->client->request('GET',
            $this->config['host'] . $this->config['url_neighbours'] .'?auth='.$this->getToken());
        return $result->getBody()->getContents();
    }

}
