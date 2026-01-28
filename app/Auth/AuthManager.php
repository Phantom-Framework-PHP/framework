<?php

namespace Phantom\Auth;

use Phantom\Core\Container;
use Phantom\Session\Session;
use Exception;

class AuthManager
{
    protected $session;
    protected $user;
    protected $config;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->config = config('auth');
    }

    /**
     * Attempt to authenticate a user.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function attempt(array $credentials)
    {
        $provider = $this->config['providers']['users'];
        $modelClass = $provider['model'];

        // Assume 'email' is in credentials
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return false;
        }

        $user = $modelClass::where('email', $credentials['email'])->first();

        if (!$user) {
            return false;
        }

        if (Hash::check($credentials['password'], $user->password)) {
            $this->login($user);
            return true;
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param  mixed  $user
     * @return void
     */
    public function login($user)
    {
        $this->session->regenerate();
        $this->session->put('auth_user_id', $user->id);
        $this->user = $user;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $this->session->forget('auth_user_id');
        $this->user = null;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed|null
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $id = $this->session->get('auth_user_id');

        if (! $id) {
            return null;
        }

        $provider = $this->config['providers']['users'];
        $modelClass = $provider['model'];

        $this->user = $modelClass::find($id);

        return $this->user;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user() ? $this->user()->id : null;
    }
}
