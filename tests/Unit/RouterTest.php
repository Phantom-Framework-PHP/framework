<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Router;
use Phantom\Core\Container;
use Phantom\Core\Config;

class RouterTest extends TestCase
{
    protected $app;
    protected $router;

    protected function setUp(): void
    {
        // Reset container instance
        Container::setInstance(null);
        
        $this->app = new Application(dirname(__DIR__, 2));
        $this->router = new Router();
        
        // Bind router to container so helper 'route()' works if needed, 
        // though we will test Router methods directly mostly.
        $this->app->singleton('router', function () {
            return $this->router;
        });

        // Mock config for 'url' helper
        $config = new Config();
        $config->set('app.url', 'http://localhost');
        $this->app->instance('config', $config);
    }

    public function test_basic_route()
    {
        $this->router->get('/test', function() { return 'ok'; });
        
        $request = new \Phantom\Http\Request(['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET']);
        $response = $this->router->dispatch($request);
        
        $this->assertEquals('ok', $response->getContent());
    }

    public function test_route_groups_with_prefix()
    {
        $this->router->group(['prefix' => 'admin'], function($router) {
            $router->get('/dashboard', function() { return 'dashboard'; });
        });

        $request = new \Phantom\Http\Request(['REQUEST_URI' => '/admin/dashboard', 'REQUEST_METHOD' => 'GET']);
        $response = $this->router->dispatch($request);
        
        $this->assertEquals('dashboard', $response->getContent());
    }

    public function test_route_groups_with_middleware()
    {
        $this->router->group(['middleware' => 'auth'], function($router) {
            $router->get('/protected', function() { return 'protected'; });
        });

        // We are inspecting the routes property via reflection since dispatching middleware requires more setup
        $reflection = new \ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->router);

        $this->assertContains('auth', $routes['GET']['/protected']['middleware']);
    }

    public function test_nested_groups()
    {
        $this->router->group(['prefix' => 'api'], function($router) {
            $router->group(['prefix' => 'v1'], function($router) {
                $router->get('/users', function() { return 'users'; });
            });
        });

        $request = new \Phantom\Http\Request(['REQUEST_URI' => '/api/v1/users', 'REQUEST_METHOD' => 'GET']);
        $response = $this->router->dispatch($request);
        
        $this->assertEquals('users', $response->getContent());
    }

    public function test_named_routes()
    {
        $this->router->get('/user/{id}', function() {})->name('user.profile');

        $url = $this->router->route('user.profile', ['id' => 1]);
        
        $this->assertEquals('http://localhost/user/1', $url);
    }

    public function test_group_name_prefix()
    {
        $this->router->group(['as' => 'admin.'], function($router) {
            $router->get('/dashboard', function() {})->name('dashboard');
        });

        $url = $this->router->route('admin.dashboard');
        
        $this->assertEquals('http://localhost/dashboard', $url);
    }
}
