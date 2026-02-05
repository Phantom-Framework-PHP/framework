<?php

namespace Phantom\Providers;

use Phantom\Core\ServiceProvider;
use Phantom\Session\Session;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('session', function () {
            return new Session();
        });
    }

    public function boot()
    {
        if (config('session.driver') === 'file') {
            $this->app->make('session')->start();
        }
    }
}
