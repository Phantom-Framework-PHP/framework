<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Router;
use Phantom\Core\Container;
use Phantom\Http\Request;

class RouterParameterTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_route_matching_with_parameters()
    {
        $router = new Router();
        $router->get('/user/{id}', function(Request $request) {
            return "User ID: " . $request->input('id');
        });

        $request = new Request(['REQUEST_URI' => '/user/123', 'REQUEST_METHOD' => 'GET']);
        $response = $router->dispatch($request);
        
        $this->assertEquals('User ID: 123', $response->getContent());
    }
}
