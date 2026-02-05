<?php

namespace Phantom\Console;

use Closure;

class Schedule
{
    protected $events = [];

    /**
     * Add a new closure task to the schedule.
     *
     * @param  Closure  $callback
     * @return ScheduledEvent
     */
    public function call(Closure $callback)
    {
        $this->events[] = $event = new ScheduledEvent($callback);
        return $event;
    }

    /**
     * Add a new command task to the schedule.
     *
     * @param  string  $command
     * @return ScheduledEvent
     */
    public function command($command)
    {
        $this->events[] = $event = new ScheduledEvent(function() use ($command) {
            passthru("php phantom {$command}");
        });
        return $event;
    }

    /**
     * Get all events on the schedule.
     *
     * @return array
     */
    public function events()
    {
        return $this->events;
    }
}

class ScheduledEvent
{
    protected $callback;
    protected $expression = '* * * * *'; // Default: every minute

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function everyMinute() { $this->expression = '* * * * *'; return $this; }
    public function hourly() { $this->expression = '0 * * * *'; return $this; }
    public function daily() { $this->expression = '0 0 * * *'; return $this; }

    public function cron($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    public function shouldRun()
    {
        // Simple logic: for MVP we just run everything if called.
        // In a real system, we would parse the cron expression.
        return true; 
    }

    public function run()
    {
        ($this->callback)();
    }
}
