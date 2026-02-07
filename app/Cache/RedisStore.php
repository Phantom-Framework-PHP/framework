<?php

namespace Phantom\Cache;

use Redis;
use Exception;

class RedisStore
{
    protected $redis;
    protected $prefix;

    public function __construct(array $config)
    {
        if (!class_exists('Redis')) {
            throw new Exception("Redis extension not found.");
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

        $this->prefix = $config['prefix'] ?? 'phantom:';
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed|null
     */
    public function get($key)
    {
        $value = $this->redis->get($this->prefix . $key);

        return $value !== false ? unserialize($value) : null;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return bool
     */
    public function put($key, $value, $seconds = 3600)
    {
        return $this->redis->setex(
            $this->prefix . $key,
            (int) $seconds,
            serialize($value)
        );
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->redis->flushDB();
    }
}
