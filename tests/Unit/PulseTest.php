<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Response;
use Phantom\Http\Middlewares\PulseMiddleware;
use Phantom\Database\Database;

class PulseTest extends TestCase
{
    protected $app;
    protected $pulseFile;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $this->pulseFile = storage_path('framework/pulse.json');
        
        if (file_exists($this->pulseFile)) {
            unlink($this->pulseFile);
        }

        // Enable debug mode for test
        config(['app.debug' => true]);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->pulseFile)) {
            unlink($this->pulseFile);
        }
    }

    public function test_pulse_middleware_captures_request_data()
    {
        $middleware = new PulseMiddleware();
        $request = new Request(['REQUEST_URI' => '/test-pulse', 'REQUEST_METHOD' => 'GET']);

        $next = function($req) {
            // Simulate some work
            usleep(10000); // 10ms
            return new Response('OK');
        };

        $middleware->handle($request, $next);

        $this->assertFileExists($this->pulseFile);
        
        $data = json_decode(file_get_contents($this->pulseFile), true);
        $this->assertCount(1, $data);
        $this->assertEquals('/test-pulse', $data[0]['url']);
        $this->assertEquals('GET', $data[0]['method']);
        $this->assertGreaterThan(0, $data[0]['duration']);
        $this->assertIsArray($data[0]['queries']);
    }

    public function test_pulse_middleware_captures_database_queries()
    {
        // Mock DB query log
        Database::enableQueryLog();
        Database::flushQueryLog();
        
        $middleware = new PulseMiddleware();
        $request = new Request(['REQUEST_URI' => '/db-pulse', 'REQUEST_METHOD' => 'GET']);

        $next = function($req) {
            // Manual log since we don't have a real DB connection here
            // In a real scenario, Database::query() would do this.
            // We'll call a method that we know logs.
            
            // This is a bit hacky because we don't have a real DB config, 
            // but we want to see if the middleware picks up what's in Database::$queryLog
            $reflection = new \ReflectionClass(Database::class);
            $method = $reflection->getMethod('logQuery');
            $method->setAccessible(true);
            
            $db = $this->app->make('db');
            $method->invoke($db, 'SELECT * FROM users', [], microtime(true) - 0.005);
            
            return new Response('OK');
        };

        $middleware->handle($request, $next);

        $data = json_decode(file_get_contents($this->pulseFile), true);
        $this->assertEquals(1, $data[0]['queries_count']);
        $this->assertEquals('SELECT * FROM users', $data[0]['queries'][0]['sql']);
    }
}
