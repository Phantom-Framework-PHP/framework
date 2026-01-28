<?php

namespace Phantom\Auth;

class Hash
{
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @return string
     */
    public static function make($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @return bool
     */
    public static function check($value, $hashedValue)
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash needs to be rehashed.
     *
     * @param  string  $hashedValue
     * @return bool
     */
    public static function needsRehash($hashedValue)
    {
        return password_needs_rehash($hashedValue, PASSWORD_DEFAULT);
    }
}
