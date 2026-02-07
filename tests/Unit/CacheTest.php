<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Cache\CacheManager;
use Phantom\Cache\FileStore;
use Phantom\Cache\RedisStore;
use Phantom\Core\Application;
use Phantom\Core\Container;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_cache_manager_can_resolve_file_driver()
    {
        config(['cache.stores.file' => [
            'driver' => 'file',
            'path' => storage_path('cache')
        ]]);

        $manager = new CacheManager();
        $store = $manager->store('file');

        $this->assertInstanceOf(FileStore::class, $store);
    }

    public function test_cache_manager_can_resolve_redis_driver()
    {
        config(['cache.stores.redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1'
        ]]);

        $manager = new CacheManager();
        
        try {
            $store = $manager->store('redis');
            $this->assertInstanceOf(RedisStore::class, $store);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Redis extension not found', $e->getMessage());
        }
    }
}
