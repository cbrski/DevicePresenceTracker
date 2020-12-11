<?php declare(strict_types=1);

namespace App\Api\Router;

use AdvancedJsonRpc\Request;
use App\Api\Router\Helpers\ConfigValidator;
use App\Api\Router\Helpers\SettingsHelper;
use App\Api\Router\Helpers\TimestampFileHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use JsonMapper;
use App\Api\Router\Mappers\TargetLogin;

class RouterOpenWrt implements RouterInterface
{
    protected array $config = [];

    protected Client $client;
    protected TimestampFileHelper $timestampHelper;
    protected SettingsHelper $settings;

    public function __construct(
        Client $_client,
        TimestampFileHelper $_timestampFileHelper,
        SettingsHelper $_settings,
        array $_config
    )
    {
        $validator = App::make(ConfigValidator::class);
        $validator->check($_config);

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
            if ($e->getMessage() == 'JSON property "result" in class "App\Api\Router\Mappers\TargetLogin" must not be NULL')
            {
                Log::critical(__CLASS__.':'.__METHOD__.': incorrect login data');
                return false;
            }
            Log::critical(__CLASS__.':'.__METHOD__.': cannot login');
            return false;
        }
        return $loginResponse;
    }

    public function authorize()
    {
        if ($this->isOngoingSession())
        {
            return $this;
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
        return $this;
    }

    public function getToken(): string
    {
        return $this->settings->tokenString;
    }

    public function getNeighbours(): \stdClass
    {
        $result = $this->client->request('GET',
            $this->config['host'] . $this->config['url_neighbours'] .'?auth='.$this->getToken());
        return json_decode($result->getBody()->getContents());
    }

}
