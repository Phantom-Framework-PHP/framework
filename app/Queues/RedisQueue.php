<?php

namespace Phantom\Queues;

use Phantom\Redis\RedisFactory;

class RedisQueue
{
    protected $redis;
    protected $queue;
    protected $connection;

    public function __construct(array $config)
    {
        $this->queue = $config['queue'] ?? 'default';
        $this->connection = $config['connection'] ?? 'default';
        
        $this->redis = RedisFactory::make($config);
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
