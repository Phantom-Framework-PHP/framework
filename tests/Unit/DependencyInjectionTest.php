<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Router;
use Phantom\Core\Container;
use Phantom\Http\Request;
use Phantom\Http\Response;

class DependencyInjectionTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_automatic_request_injection_in_closure()
    {
        $router = new Router();
        
        $router->get('/test', function(Request $req) {
            return "Method: " . $req->method();
        });

        $request = new Request(['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET']);
        $response = $router->dispatch($request);
        
        $this->assertEquals('Method: GET', $response->getContent());
    }

    public function test_injection_of_custom_service()
    {
        $router = new Router();
        
        // Registrar un servicio ficticio en el contenedor
        app()->singleton(\stdClass::class, function() {
            $obj = new \stdClass();
            $obj->name = 'Phantom Service';
            return $obj;
        });

        $router->get('/service', function(\stdClass $service) {
            return $service->name;
        });

        $request = new Request(['REQUEST_URI' => '/service', 'REQUEST_METHOD' => 'GET']);
        $response = $router->dispatch($request);
        
        $this->assertEquals('Phantom Service', $response->getContent());
    }
}
