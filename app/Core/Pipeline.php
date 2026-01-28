<?php

namespace Phantom\Core;

use Closure;

class Pipeline
{
    protected $passable;
    protected $pipes = [];

    /**
     * Set the object being sent through the pipeline.
     *
     * @param  mixed  $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param  array|mixed  $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param  Closure  $destination
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $destination
        );

        return $pipeline($this->passable);
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    // If pipe is a Closure
                    return $pipe($passable, $stack);
                } elseif (is_string($pipe) && class_exists($pipe)) {
                    // If pipe is a Class Name
                    $instance = new $pipe; // Usually resolved via Container in full framework
                    return $instance->handle($passable, $stack);
                } else {
                    throw new \Exception("Invalid middleware type.");
                }
            };
        };
    }
}
