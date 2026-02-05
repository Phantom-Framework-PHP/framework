<?php

namespace Phantom\Providers;

use Phantom\Core\ServiceProvider;
use Phantom\Database\Database;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('db', function () {
            return new Database(config('database'));
        });
    }
}
