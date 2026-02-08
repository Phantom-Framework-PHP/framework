<?php

namespace Phantom\Redis;

use Redis;
use RedisCluster;
use Exception;

class RedisFactory
{
    /**
     * Create a new Redis connection instance.
     *
     * @param  array  $config
     * @return Redis|RedisCluster
     * @throws Exception
     */
    public static function make(array $config)
    {
        if (!class_exists('Redis')) {
            throw new Exception("Redis extension not found. Please install phpredis.");
        }

        // Redis Cluster Support
        if (isset($config['clusters'])) {
            return static::createCluster($config);
        }

        // Redis Sentinel Support (Simplified implementation via raw connection or loops)
        // Note: Native phpredis Sentinel support is varying. 
        // For minimalism, we will focus on Cluster and Single/Replicated first.
        
        return static::createSingle($config);
    }

    protected static function createSingle(array $config)
    {
        $redis = new Redis();
        $redis->connect(
            $config['host'] ?? '127.0.0.1', 
            $config['port'] ?? 6379, 
            $config['timeout'] ?? 0.0
        );

        if (isset($config['password']) && $config['password']) {
            $redis->auth($config['password']);
        }

        if (isset($config['database'])) {
            $redis->select($config['database']);
        }

        if (isset($config['prefix'])) {
            $redis->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }

        return $redis;
    }

    protected static function createCluster(array $config)
    {
        if (!class_exists('RedisCluster')) {
            throw new Exception("RedisCluster class not found.");
        }

        // Format: ['127.0.0.1:7000', '127.0.0.1:7001']
        $seeds = $config['clusters'];
        $timeout = $config['timeout'] ?? 0.0;
        $readTimeout = $config['read_timeout'] ?? 0.0;
        $persistent = $config['persistent'] ?? false;
        $auth = $config['password'] ?? null;

        return new RedisCluster(
            null, // Name (optional)
            $seeds,
            $timeout,
            $readTimeout,
            $persistent,
            $auth
        );
    }
}
