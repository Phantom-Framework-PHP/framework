<?php

namespace Phantom\Security;

use Phantom\Core\Container;
use Phantom\Redis\RedisFactory;

class RateLimiter
{
    /**
     * The Redis instance.
     *
     * @var \Redis|\RedisCluster
     */
    protected $redis;

    /**
     * In-memory storage for fallback or testing.
     *
     * @var array
     */
    protected static $memory = [];

    /**
     * Create a new RateLimiter instance.
     *
     * @return void
     */
    public function __construct()
    {
        try {
            $config = config('queue.connections.redis', []);
            if (!empty($config) && class_exists('Redis')) {
                $this->redis = RedisFactory::make($config);
            }
        } catch (\Exception $e) {
            // Redis connection failed, will use memory fallback
        }
    }

    /**
     * Attempt to acquire a token for the given key.
     *
     * @param  string  $key
     * @param  int     $maxAttempts
     * @param  int     $decaySeconds
     * @return bool
     */
    public function attempt($key, $maxAttempts, $decaySeconds)
    {
        if ($this->redis) {
            return $this->attemptRedis($key, $maxAttempts, $decaySeconds);
        }

        return $this->attemptMemory($key, $maxAttempts, $decaySeconds);
    }

    protected function attemptRedis($key, $maxAttempts, $decaySeconds)
    {
        $now = microtime(true);
        $windowStart = $now - $decaySeconds;
        $fullKey = "ratelimit:{$key}";

        $this->redis->zRemRangeByScore($fullKey, 0, $windowStart);
        $count = $this->redis->zCard($fullKey);

        if ($count >= $maxAttempts) {
            return false;
        }

        $this->redis->zAdd($fullKey, $now, (string) $now);
        $this->redis->expire($fullKey, $decaySeconds + 10);

        return true;
    }

    protected function attemptMemory($key, $maxAttempts, $decaySeconds)
    {
        $now = microtime(true);
        $windowStart = $now - $decaySeconds;

        if (!isset(static::$memory[$key])) {
            static::$memory[$key] = [];
        }

        // Filter old attempts
        static::$memory[$key] = array_filter(static::$memory[$key], function($time) use ($windowStart) {
            return $time > $windowStart;
        });

        if (count(static::$memory[$key]) >= $maxAttempts) {
            return false;
        }

        static::$memory[$key][] = $now;

        return true;
    }

    /**
     * Get the number of remaining attempts for the given key.
     *
     * @param  string  $key
     * @param  int     $maxAttempts
     * @param  int     $decaySeconds
     * @return int
     */
    public function remaining($key, $maxAttempts, $decaySeconds)
    {
        if ($this->redis) {
            $now = microtime(true);
            $windowStart = $now - $decaySeconds;
            $fullKey = "ratelimit:{$key}";
            $this->redis->zRemRangeByScore($fullKey, 0, $windowStart);
            return max(0, $maxAttempts - $this->redis->zCard($fullKey));
        }

        if (!isset(static::$memory[$key])) {
            return $maxAttempts;
        }

        $now = microtime(true);
        $windowStart = $now - $decaySeconds;
        $attempts = array_filter(static::$memory[$key], function($time) use ($windowStart) {
            return $time > $windowStart;
        });

        return max(0, $maxAttempts - count($attempts));
    }

    /**
     * Reset the attempts for the given key.
     *
     * @param  string  $key
     * @return void
     */
    public function reset($key)
    {
        if ($this->redis) {
            $this->redis->del("ratelimit:{$key}");
        }
        
        unset(static::$memory[$key]);
    }
}
