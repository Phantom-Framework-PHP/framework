<?php

namespace Phantom\Cache;

use Phantom\Redis\RedisFactory;
use Exception;

class RedisStore
{
    /**
     * The Redis instance.
     *
     * @var \Redis|\RedisCluster
     */
    protected $redis;

    /**
     * The cache key prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new RedisStore instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->redis = RedisFactory::make($config);
        $this->prefix = $config['prefix'] ?? 'phantom:';
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
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
