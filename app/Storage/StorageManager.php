<?php

namespace Phantom\Storage;

use Exception;

class StorageManager
{
    protected $disks = [];

    /**
     * Get a storage disk instance.
     *
     * @param  string|null  $name
     * @return LocalDisk
     */
    public function disk($name = null)
    {
        $name = $name ?: config('filesystems.default');

        if (!isset($this->disks[$name])) {
            $this->disks[$name] = $this->resolve($name);
        }

        return $this->disks[$name];
    }

    /**
     * Resolve the storage disk.
     *
     * @param  string  $name
     * @return LocalDisk
     * @throws Exception
     */
    protected function resolve($name)
    {
        $config = config("filesystems.disks.{$name}");

        if (is_null($config)) {
            throw new Exception("Filesystem disk [{$name}] is not defined.");
        }

        if ($config['driver'] === 'local') {
            return new LocalDisk($config['root']);
        }

        throw new Exception("Storage driver [{$config['driver']}] not supported yet.");
    }

    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
