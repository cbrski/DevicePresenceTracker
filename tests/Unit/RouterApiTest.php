<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Api\Helpers\SettingsHelper;
use App\Api\Helpers\TimestampFileHelper;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Api\RouterApi;

class RouterApiTest extends TestCase
{
    private const FILENAME = 'test_api_timestamp_remove_this_file';

    public function testAuthorizeInvalidNotAllNeededConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration for App\Api\RouterApi, there is no: login');

        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            new SettingsHelper(),
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
            new SettingsHelper(),
            [
                'login' => null,
                'password' => null,
                'host' => null,
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
            ]
        );
        $api->authorize();
    }

    public function testAuthorizeInvalidOrOfflineHostAddress()
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            new SettingsHelper(),
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => 'invalid.thanks',
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
            ]
        );
        $this->assertFalse($api->authorize());

    }

    public function testAuthorizeInvalidCredentials()
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            new SettingsHelper(),
            [
                'login' => 'not_valid',
                'password' => 'not_valid',
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
            ]
        );
        $this->assertFalse($api->authorize());
    }

    public function testAuthorize(): RouterApi
    {
        $api = new RouterApi(
            new Client(),
            new TimestampFileHelper(self::FILENAME),
            new SettingsHelper(),
            [
                'login' => env('OPENWRT_API_LOGIN'),
                'password' => env('OPENWRT_API_PASSWORD'),
                'host' => env('OPENWRT_API_HOST'),
                'url_auth' => env('OPENWRT_API_URL_AUTH'),
                'url_neighbours' => env('OPENWRT_API_URL_NEIGHBOURS'),
            ]
        );
        $api->authorize();
        $this->assertEquals(32, strlen($api->getToken()));

        return $api;
    }

    public function testAuthorizeSameToken(): RouterApi
    {
        $this->assertTrue(false);
    }

    /**
     * @param RouterApi $api
     * @depends testAuthorize
     */
    public function testGetNeighbours(RouterApi $api)
    {
        $neighbours = json_decode($api->getNeighbours());

        $this->assertObjectHasAttribute('timestamp', $neighbours);
        $this->assertObjectHasAttribute('neighbours', $neighbours);
        $this->assertIsArray($neighbours->neighbours);

    }
}
