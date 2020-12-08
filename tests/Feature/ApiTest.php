<?php

namespace Tests\Feature;

use App\DeviceLinkStateLog;
use Database\Factories\Helpers\OneEntryHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function testGetNeighbours()
    {
        OneEntryHelper::create();

        $data = $this->json('GET', '/api/neighbours')
            ->assertStatus(200);
        $ala = null;
        $data->assertSee('deviceName');
        $data->assertSee('lastUsedLink');
        $data->assertSee('timestamp');
        $data->assertSee('links');
        $data->assertSee('state');
        $data->assertSee('hostname');
        $data->assertSee('ip');
        $data->assertSee('dev');
        $data->assertSee('lladdr');
    }

}
