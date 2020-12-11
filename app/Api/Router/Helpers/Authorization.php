<?php


namespace App\Api\Router\Helpers;


use AdvancedJsonRpc\Request;
use App\Api\Router\Mappers\TargetLogin;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use JsonMapper;

class Authorization implements AuthorizationInterface
{
    protected array $config = [];

    protected Client $client;
    protected TimestampFileHelper $timestampHelper;
    protected SettingsHelper $settings;

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
            if (
                $e->getMessage()
                == 'JSON property "result" in class "App\Api\Router\Mappers\TargetLogin" must not be NULL'
            )
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

    public function __construct(
        Client $_client,
        TimestampFileHelper $_timestampHelper,
        SettingsHelper $_settings,
        array $_config)
    {
        $this->client = $_client;
        $this->timestampHelper = $_timestampHelper;
        $this->settings = $_settings;
        $this->config = $_config;
    }
}
