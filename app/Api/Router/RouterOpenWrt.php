<?php declare(strict_types=1);

namespace App\Api\Router;

use AdvancedJsonRpc\Request;
use App\Api\Router\Helpers\Authorization;
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
        $validator->validate($_config);

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

    public function authorize()
    {
        $authorization = App::make(Authorization::class, [
            '_client'           => $this->client,
            '_timestampHelper'  => $this->timestampHelper,
            '_settings'         => $this->settings,
            '_config'           => $this->config,
        ]);

        if ($authorization->authorize())
        {
            return $this;
        }
        return false;
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
