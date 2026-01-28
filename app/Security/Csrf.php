<?php

namespace Phantom\Security;

class Csrf
{
    /**
     * Get the current CSRF token from the session.
     *
     * @return string
     */
    public static function token()
    {
        $session = app('session');
        
        if (!$token = $session->get('_token')) {
            $token = bin2hex(random_bytes(32));
            $session->put('_token', $token);
        }

        return $token;
    }

    /**
     * Validate the given token against the session token.
     *
     * @param  string  $token
     * @return bool
     */
    public static function validate($token)
    {
        return hash_equals(static::token(), (string) $token);
    }
}
