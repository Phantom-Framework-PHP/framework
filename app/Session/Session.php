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
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            if (config('session.secure', false)) {
                ini_set('session.cookie_secure', 1);
            }

            session_start();
        }

        // Age flash data
        $this->ageFlashData();
    }

    /**
     * Get a key from the session.
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a key in the session.
     */
    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Set a flash value in the session.
     */
    public function flash($key, $value)
    {
        $this->put($key, $value);
        $_SESSION['_flash']['new'][] = $key;
    }

    /**
     * Remove aged flash data.
     */
    protected function ageFlashData()
    {
        $old = $_SESSION['_flash']['old'] ?? [];
        foreach ($old as $key) {
            $this->forget($key);
        }

        $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }

    public function forget($key)
    {
        unset($_SESSION[$key]);
    }

    public function flush()
    {
        $_SESSION = [];
    }

    public function regenerate()
    {
        return session_regenerate_id(true);
    }
}