<?php

namespace Tests\Feature;

use Database\Factories\Helpers\OneEntryHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function testGetNeighbours()
    {
        new OneEntryHelper();

        $data = $this->json('GET', '/api/neighbours')
            ->assertStatus(200);
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
