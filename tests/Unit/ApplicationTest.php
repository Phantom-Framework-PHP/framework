<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;

class ApplicationTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
    }

    public function test_application_initialization()
    {
        $app = new Application(dirname(__DIR__, 2));
        
        $this->assertInstanceOf(Application::class, $app);
        $this->assertEquals(dirname(__DIR__, 2), $app->basePath());
        $this->assertEquals(Application::VERSION, $app->version());
    }

    public function test_singleton_bindings()
    {
        $app = new Application(dirname(__DIR__, 2));
        
        $router1 = $app->make('router');
        $router2 = $app->make('router');
        
        $this->assertSame($router1, $router2);
    }

    public function test_app_helper()
    {
        $app = new Application(dirname(__DIR__, 2));
        
        $this->assertSame($app, app());
        $this->assertSame($app->make('config'), app('config'));
    }
}
