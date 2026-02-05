<?php

namespace Phantom\Events;

abstract class Broadcaster
{
    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    abstract public function broadcast(array $channels, $event, array $payload = []);
}
