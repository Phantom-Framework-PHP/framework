<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Response;
use Phantom\Security\Shield;
use Phantom\Http\Middlewares\ShieldMiddleware;

class ShieldTest extends TestCase
{
    protected $app;
    protected $shieldFile;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $this->shieldFile = storage_path('framework/shield.json');
        
        if (file_exists($this->shieldFile)) {
            unlink($this->shieldFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->shieldFile)) {
            unlink($this->shieldFile);
        }
    }

    public function test_shield_records_risk_and_blocks_ip()
    {
        $shield = new Shield();
        $ip = '192.168.1.1';

        $this->assertFalse($shield->isBlocked($ip));

        // Record risk up to threshold (100)
        for ($i = 0; $i < 10; $i++) {
            $shield->recordRisk($ip, 10);
        }

        $this->assertTrue($shield->isBlocked($ip));
        $this->assertEquals(100, $shield->getRiskScore($ip));
    }

    public function test_shield_middleware_blocks_request()
    {
        $shield = new Shield();
        $ip = '1.2.3.4';
        $_SERVER['REMOTE_ADDR'] = $ip;

        // Block IP manually
        $shield->recordRisk($ip, 100);

        $middleware = new ShieldMiddleware();
        $request = new Request(['REQUEST_URI' => '/', 'REQUEST_METHOD' => 'GET']);
        
        $next = function() {
            return new Response('Should not be reached');
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString("Your IP (1.2.3.4) has been blocked", $response->getContent());
    }

    public function test_shield_middleware_records_risk_on_404()
    {
        $ip = '5.6.7.8';
        $_SERVER['REMOTE_ADDR'] = $ip;
        
        $shield = new Shield();
        $this->assertEquals(0, $shield->getRiskScore($ip));

        $middleware = new ShieldMiddleware();
        $request = new Request(['REQUEST_URI' => '/not-found', 'REQUEST_METHOD' => 'GET']);
        
        $next = function() {
            return new Response('Not Found', 404);
        };

        $middleware->handle($request, $next);

        $this->assertEquals(10, $shield->getRiskScore($ip));
    }
}
