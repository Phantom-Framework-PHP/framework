<?php

namespace Tests\Unit;

use Tests\FeatureTestCase;
use Phantom\Core\Container;
use Phantom\Http\Request;
use Phantom\Http\Response;

class RateLimitingTest extends FeatureTestCase
{
    public function test_rate_limiter_allows_requests_within_limit()
    {
        $limiter = Container::getInstance()->make('ratelimiter');
        $key = 'test_key';
        $limiter->reset($key);

        $this->assertTrue($limiter->attempt($key, 2, 60));
        $this->assertTrue($limiter->attempt($key, 2, 60));
        $this->assertFalse($limiter->attempt($key, 2, 60));
    }

    public function test_rate_limiter_sliding_window_expiration()
    {
        $limiter = Container::getInstance()->make('ratelimiter');
        $key = 'sliding_key';
        $limiter->reset($key);

        // 2 requests allowed in 1 second
        $this->assertTrue($limiter->attempt($key, 2, 1));
        $this->assertTrue($limiter->attempt($key, 2, 1));
        $this->assertFalse($limiter->attempt($key, 2, 1));

        // Wait for 1.1 seconds
        usleep(1100000);

        $this->assertTrue($limiter->attempt($key, 2, 1));
    }

    public function test_throttle_middleware_blocks_exceeded_requests()
    {
        $this->app->make('ratelimiter')->reset(md5('GET/throttle-test1.1.1.1'));

        $this->app->make('router')->get('/throttle-test', function() {
            return 'OK';
        })->middleware('throttle:2,60');

        // First attempt
        $response = $this->get('/throttle-test', ['Remote-Addr' => '1.1.1.1']);
        $response->assertStatus(200);
        $this->assertEquals('2', $response->headers['X-RateLimit-Limit']);
        $this->assertEquals('1', $response->headers['X-RateLimit-Remaining']);

        // Second attempt
        $response = $this->get('/throttle-test', ['Remote-Addr' => '1.1.1.1']);
        $response->assertStatus(200);
        $this->assertEquals('0', $response->headers['X-RateLimit-Remaining']);

        // Third attempt - Should fail
        $response = $this->get('/throttle-test', ['Remote-Addr' => '1.1.1.1']);
        $response->assertStatus(429);
        $this->assertEquals('Too Many Requests', $response->getContent());
    }
}
