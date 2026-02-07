<?php

namespace Phantom\Queues;

use Exception;
use Redis;

class RedisQueue
{
    protected $redis;
    protected $queue;
    protected $connection;

    public function __construct(array $config)
    {
        $this->queue = $config['queue'] ?? 'default';
        $this->connection = $config['connection'] ?? 'default';
        
        if (!class_exists('Redis')) {
            throw new Exception("Redis extension not found. Please install phpredis.");
        }

        $this->redis = new Redis();
        $this->redis->connect(
            $config['host'] ?? '127.0.0.1', 
            $config['port'] ?? 6379, 
            $config['timeout'] ?? 0.0
        );

        if (isset($config['password']) && $config['password']) {
            $this->redis->auth($config['password']);
        }

        if (isset($config['database'])) {
            $this->redis->select($config['database']);
        }
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  object  $job
     * @return bool
     */
    public function push($job)
    {
        $payload = serialize($job);
        return $this->redis->rPush("queues:{$this->queue}", $payload) !== false;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @return object|null
     */
    public function pop()
    {
        $payload = $this->redis->lPop("queues:{$this->queue}");

        if ($payload) {
            return unserialize($payload);
        }

        return null;
    }
}
