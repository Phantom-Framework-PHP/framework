<?php

namespace Phantom\Events;

use Phantom\Core\Container;
use Closure;

class Dispatcher
{
    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Register an event listener.
     *
     * @param  string  $event
     * @param  mixed   $listener
     * @return void
     */
    public function listen($event, $listener)
    {
        $this->listeners[$event][] = $listener;
    }

    /**
     * Fire an event and call all relevant listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function dispatch($event, $payload = [])
    {
        // If an object is passed, use its class name as the event name
        if (is_object($event)) {
            $this->broadcastEvent($event);
            $payload = $event;
            $event = get_class($event);
        }

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {
            $responses[] = $this->executeListener($listener, $payload);
        }

        return $responses;
    }

    /**
     * Broadcast the event if it implements ShouldBroadcast.
     * 
     * @param object $event
     * @return void
     */
    protected function broadcastEvent($event)
    {
        if ($event instanceof ShouldBroadcast && Container::getInstance()->has('broadcaster')) {
            $broadcaster = Container::getInstance()->make('broadcaster');
            $broadcaster->broadcast(
                $event->broadcastOn(),
                get_class($event),
                $event->broadcastWith()
            );
        }
    }

    /**
     * Get all listeners for a given event name.
     *
     * @param  string  $event
     * @return array
     */
    protected function getListeners($event)
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * Execute a listener.
     *
     * @param  mixed  $listener
     * @param  mixed  $payload
     * @return mixed
     */
    protected function executeListener($listener, $payload)
    {
        if ($listener instanceof Closure) {
            return $listener($payload);
        }

        if (is_string($listener)) {
            // Handle 'ClassName@method' or just 'ClassName' (defaults to handle)
            return $this->resolveAndCall($listener, $payload);
        }

        return null;
    }

    /**
     * Resolve the listener from the container and call the method.
     *
     * @param  string  $listener
     * @param  mixed   $payload
     * @return mixed
     */
    protected function resolveAndCall($listener, $payload)
    {
        $segments = explode('@', $listener);
        $method = $segments[1] ?? 'handle';
        
        $instance = Container::getInstance()->make($segments[0]);
        
        return $instance->{$method}($payload);
    }
}
