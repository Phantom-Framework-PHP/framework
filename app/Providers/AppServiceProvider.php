<?php

namespace Phantom\Providers;

use Phantom\Core\ServiceProvider;
use Phantom\Core\Translator;
use Phantom\Cache\CacheManager;
use Phantom\Events\Dispatcher;
use Phantom\Queues\QueueManager;
use Phantom\Storage\StorageManager;
use Phantom\Mail\MailManager;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('translator', function () {
            return new Translator(
                base_path('lang'),
                config('app.locale', 'en'),
                config('app.fallback_locale', 'en')
            );
        });

        $this->app->singleton('cache', function () {
            return new CacheManager();
        });

        $this->app->singleton('events', function () {
            return new Dispatcher();
        });

        $this->app->singleton('queue', function () {
            return new QueueManager();
        });

        $this->app->singleton('storage', function () {
            return new StorageManager();
        });

        $this->app->singleton('mail', function () {
            return new MailManager();
        });

        $this->app->singleton('gate', function () {
            return new \Phantom\Security\Gate();
        });

        $this->app->singleton('broadcaster', function () {
            $config = config('broadcasting');
            $driver = $config['connections'][$config['default']];

            if ($config['default'] === 'pusher') {
                return new \Phantom\Events\PusherBroadcaster($driver);
            }

            return new class extends \Phantom\Events\Broadcaster {
                public function broadcast(array $channels, $event, array $payload = []) {}
            };
        });
    }

    /**
     * Boot any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register global middlewares
        $router = $this->app->make('router');
        
        $router->use(\Phantom\Http\Middlewares\ShieldMiddleware::class);
        $router->use(\Phantom\Http\Middlewares\PulseMiddleware::class);
    }
}
