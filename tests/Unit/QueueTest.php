<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Queues\QueueManager;
use Phantom\Queues\DatabaseQueue;
use Phantom\Queues\RedisQueue;
use Phantom\Queues\SyncQueue;
use Phantom\Core\Application;
use Phantom\Core\Container;

class QueueTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_queue_manager_can_resolve_sync_driver()
    {
        config(['queue.connections.sync' => ['driver' => 'sync']]);
        config(['queue.default' => 'sync']);

        $manager = new QueueManager();
        $connection = $manager->connection();

        $this->assertInstanceOf(SyncQueue::class, $connection);
    }

    public function test_queue_manager_can_resolve_database_driver()
    {
        config(['queue.connections.database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default'
        ]]);

        $manager = new QueueManager();
        $connection = $manager->connection('database');

        $this->assertInstanceOf(DatabaseQueue::class, $connection);
    }

    public function test_queue_manager_can_resolve_redis_driver()
    {
        config(['queue.connections.redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379
        ]]);

        $manager = new QueueManager();
        
        // We only check if it tries to instantiate the class. 
        // We catch exception if Redis ext is missing to at least verify resolution logic.
        try {
            $connection = $manager->connection('redis');
            $this->assertInstanceOf(RedisQueue::class, $connection);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Redis extension not found', $e->getMessage());
        }
    }
}
