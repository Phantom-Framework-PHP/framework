<?php

namespace Phantom\Security;

use Closure;

class Gate
{
    protected $abilities = [];

    /**
     * Define a new ability.
     *
     * @param  string  $ability
     * @param  Closure|string  $callback
     * @return $this
     */
    public function define($ability, $callback)
    {
        $this->abilities[$ability] = $callback;
        return $this;
    }

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function allows($ability, $arguments = [])
    {
        if (!isset($this->abilities[$ability])) {
            return false;
        }

        $callback = $this->abilities[$ability];
        $user = auth()->user();

        $arguments = is_array($arguments) ? $arguments : [$arguments];

        return call_user_func($callback, $user, ...$arguments);
    }

    /**
     * Determine if the given ability should be denied for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function denies($ability, $arguments = [])
    {
        return ! $this->allows($ability, $arguments);
    }
}
