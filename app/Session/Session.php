<?php

namespace Phantom\Session;

class Session
{
    /**
     * Start the session.
     *
     * @return void
     */
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            if (config('session.secure', false)) {
                ini_set('session.cookie_secure', 1);
            }

            session_start();
        }
    }

    /**
     * Get a key from the session.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a key in the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Remove a key from the session.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function flush()
    {
        $_SESSION = [];
    }

    /**
     * Regenerate the session ID.
     *
     * @return bool
     */
    public function regenerate()
    {
        return session_regenerate_id(true);
    }
}
