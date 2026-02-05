<?php

namespace Phantom\Providers;

use Phantom\Core\ServiceProvider;
use Phantom\Auth\AuthManager;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('auth', function () {
            return new AuthManager($this->app->make('session'));
        });
    }
}
