<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Api\Helpers\SettingsHelper;
use App\Api\Helpers\TimestampFileHelper;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Api\RouterApi;

class RouterApiTest extends TestCase
{
//    use RefreshDatabase;

    private const FILENAME = 'test_api_timestamp_remove_this_file';
    private SettingsHelper $settingsHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->settingsHelper = new SettingsHelper(
            [
                'tokenString' => 'none',
                'tokenAcquisitionTimestamp' => 0,
            ]
        );
    }

    public function testAuthorizeInvalidNotAllNeededConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration for App\Api\RouterApi, there is no: login');
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
            ]
        );
        $api->authorize();
    }

    public function testAuthorizeInvalidNullValuesInConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration for App\Api\RouterApi, misconfigured: login');

        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'login' => null,
                'password' => null,
                'host' => null,
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        $api->authorize();
    }

    public function testAuthorizeInvalidOrOfflineHostAddress()
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => 'invalid.thanks',
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        $this->assertFalse($api->authorize());

    }

    public function testAuthorizeInvalidCredentials()
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'login' => 'not_valid',
                'password' => 'not_valid',
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        $this->assertFalse($api->authorize());
    }

    public function testAuthorize(): RouterApi
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        $api->authorize();
        $this->assertEquals(32, strlen($api->getToken()));

        return $api;
    }

    public function testAuthorizeSameToken(): RouterApi
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            $this->settingsHelper,
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
                'session_timeout' => env('OPENWRT_API_SESSION_TIMEOUT'),
            ]
        );
        $api->authorize();
        $token1 = $api->getToken();
        for($i=0 ; $i<100 ; ++$i)
        {
            $api->authorize();
        }
        $token2 = $api->getToken();
        $this->assertEquals($token1, $token2);
        return $api;
    }

    /**
     * @param RouterApi $api
     * @depends testAuthorize
     */
    public function testGetNeighboursOneAuthorize(RouterApi $api): \stdClass
    {
        $neighbours = $api->getNeighbours();

        $this->assertObjectHasAttribute('timestamp', $neighbours);
        $this->assertObjectHasAttribute('neighbours', $neighbours);
        $this->assertIsArray($neighbours->neighbours);

        return $neighbours;
    }

    /**
     * @param RouterApi $api
     * @depends testAuthorizeSameToken
     */
    public function testGetNeighboursManyAuthorize(RouterApi $api): \stdClass
    {
        $neighbours = $api->getNeighbours();

        $this->assertObjectHasAttribute('timestamp', $neighbours);
        $this->assertObjectHasAttribute('neighbours', $neighbours);
        $this->assertIsArray($neighbours->neighbours);

        return $neighbours;
    }

    /**
     * @param array $neighbours
     * @depends testGetNeighboursManyAuthorize
     */
    public function testNeighboursStructure(\stdClass $neighboursObject): void
    {
        $a = func_get_arg(0);
        if (is_array($neighboursObject->neighbours)
            && isset($neighboursObject->neighbours[0])
            && !empty($neighboursObject->neighbours[0]))
        {
            $n0 = $neighboursObject->neighbours[0];
            $this->assertInstanceOf(\stdClass::class, $n0);
            $this->assertObjectHasAttribute('ip', $n0);
            $this->assertObjectHasAttribute('dev', $n0);
            $this->assertObjectHasAttribute('lladdr', $n0);
            $this->assertObjectHasAttribute('state', $n0);
            $this->assertObjectHasAttribute('hostname', $n0);
        }
        else
        {
            $this->addWarning('There is no neighbours inside response.');
        }
    }
}
