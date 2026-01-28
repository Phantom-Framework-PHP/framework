<?php

namespace Phantom\Queues;

class SyncQueue
{
    /**
     * Push a new job onto the queue (immediately execute).
     *
     * @param  object  $job
     * @return void
     */
    public function push($job)
    {
        if (method_exists($job, 'handle')) {
            $job->handle();
        }
    }
}
