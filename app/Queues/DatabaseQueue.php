<?php

namespace Phantom\Queues;

use Phantom\Database\Database;
use Phantom\Core\Container;

class DatabaseQueue
{
    protected $database;
    protected $table;
    protected $queue;

    public function __construct(Database $database, $table, $queue = 'default')
    {
        $this->database = $database;
        $this->table = $table;
        $this->queue = $queue;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  object  $job
     * @return bool
     */
    public function push($job)
    {
        return $this->database->table($this->table)->insert([
            'queue' => $this->queue,
            'payload' => serialize($job),
            'attempts' => 0,
            'available_at' => time(),
            'created_at' => time(),
        ]);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @return object|null
     */
    public function pop()
    {
        $job = $this->database->table($this->table)
            ->where('queue', $this->queue)
            ->where('reserved_at', null)
            ->where('available_at', '<=', time())
            ->orderBy('id', 'asc')
            ->first();

        if ($job) {
            $this->database->table($this->table)
                ->where('id', $job->id)
                ->update(['reserved_at' => time()]);

            return unserialize($job->payload);
        }

        return null;
    }
}
