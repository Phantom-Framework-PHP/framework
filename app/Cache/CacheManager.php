<?php

namespace Phantom\Cache;

use Exception;

class CacheManager
{
    protected $stores = [];

    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function store($name = null)
    {
        $name = $name ?: config('cache.default');

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->createStore($name);
        }

        return $this->stores[$name];
    }

    /**
     * Create a new cache store instance.
     *
     * @param  string  $name
     * @return mixed
     * @throws Exception
     */
    protected function createStore($name)
    {
        $config = config("cache.stores.{$name}");

        if (is_null($config)) {
            throw new Exception("Cache store [{$name}] is not defined.");
        }

        $driver = $config['driver'];
        $method = 'create' . ucfirst($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new Exception("Driver [{$driver}] not supported.");
    }

    protected function createFileDriver(array $config)
    {
        return new FileStore($config['path']);
    }

    protected function createRedisDriver(array $config)
    {
        return new RedisStore($config);
    }

    /**
     * Pass methods to the default store.
     */
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}
