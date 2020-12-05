<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function testGetNeighbours()
    {
        //TODO before testing seed database with data

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
