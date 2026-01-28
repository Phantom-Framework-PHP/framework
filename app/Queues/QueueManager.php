<?php

namespace Phantom\Queues;

use Exception;

class QueueManager
{
    protected $connections = [];

    /**
     * Get a queue connection instance.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function connection($name = null)
    {
        $name = $name ?: config('queue.default');

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->resolve($name);
        }

        return $this->connections[$name];
    }

    /**
     * Resolve the queue connection.
     *
     * @param  string  $name
     * @return mixed
     * @throws Exception
     */
    protected function resolve($name)
    {
        $config = config("queue.connections.{$name}");

        if (is_null($config)) {
            throw new Exception("Queue connection [{$name}] is not defined.");
        }

        $driver = $config['driver'];

        if ($driver === 'sync') {
            return new SyncQueue();
        }

        throw new Exception("Queue driver [{$driver}] not supported yet.");
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  object  $job
     * @return mixed
     */
    public function push($job)
    {
        return $this->connection()->push($job);
    }
}
