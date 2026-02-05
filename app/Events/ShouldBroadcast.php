<?php

namespace Phantom\Events;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn();

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith();
}
