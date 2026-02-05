<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Phantom\Http\Response;

class ExampleTest extends FeatureTestCase
{
    public function test_basic_get_request()
    {
        // Registrar ruta de prueba
        app('router')->get('/hello-test', function() {
            return "Hello from Feature Test";
        });

        $this->get('/hello-test')
             ->assertStatus(200)
             ->assertSee('Hello from Feature Test');
    }

    public function test_json_api_response()
    {
        // Registrar ruta de prueba
        app('router')->get('/api/test', function() {
            return (new Response())->json(['status' => 'ok', 'version' => '1.10.4']);
        });

        $this->get('/api/test')
             ->assertStatus(200)
             ->assertJson(['status' => 'ok', 'version' => '1.10.4']);
    }

    public function test_route_not_found()
    {
        $this->get('/non-existent-route')
             ->assertStatus(404);
    }
}
