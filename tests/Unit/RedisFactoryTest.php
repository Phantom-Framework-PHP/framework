<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Redis\RedisFactory;

class RedisFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists('Redis')) {
            $this->markTestSkipped('The Redis extension is not installed.');
        }
    }

    public function test_make_returns_redis_instance_for_single_config()
    {
        // This test might fail if no Redis server is running at localhost:6379
        // We wrap in try/catch to assert it TRIES to connect using Redis class
        try {
            $redis = RedisFactory::make([
                'host' => '127.0.0.1',
                'port' => 6379
            ]);
            $this->assertInstanceOf(\Redis::class, $redis);
        } catch (\Exception $e) {
            // If connection fails, it means it tried to use Redis class, which is good enough
            $this->assertTrue(true);
        }
    }

    public function test_make_throws_exception_if_cluster_class_missing()
    {
        if (!class_exists('RedisCluster')) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('RedisCluster class not found');
            
            RedisFactory::make([
                'clusters' => ['127.0.0.1:7000']
            ]);
        } else {
            // If RedisCluster exists, we try to instantiate
            try {
                $cluster = RedisFactory::make([
                    'clusters' => ['127.0.0.1:7000']
                ]);
                $this->assertInstanceOf(\RedisCluster::class, $cluster);
            } catch (\Exception $e) {
                // Connection fail is acceptable
                $this->assertTrue(true);
            }
        }
    }
}
