<?php

namespace Phantom\Core;

use Fiber;
use Throwable;

class Async
{
    /**
     * Run a closure within a Fiber.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function run(callable $callback)
    {
        $fiber = new Fiber($callback);

        try {
            $value = $fiber->start();

            if ($fiber->isTerminated()) {
                return $fiber->getReturn();
            }

            return $value;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Suspend the current execution.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function suspend($value = null)
    {
        if (Fiber::getCurrent()) {
            return Fiber::suspend($value);
        }

        return $value;
    }

    /**
     * Resume a suspended Fiber.
     *
     * @param  Fiber  $fiber
     * @param  mixed  $value
     * @return mixed
     */
    public static function resume(Fiber $fiber, $value = null)
    {
        return $fiber->resume($value);
    }
}
