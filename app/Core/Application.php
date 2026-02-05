<?php

namespace Phantom\Core;

class Application extends Container
{
    /**
     * The Phantom framework version.
     *
     * @var string
     */
    const VERSION = '1.9.5';

    /**
     * The base path for the Phantom installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Create a new Phantom application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();
        $this->registerErrorHandlers();
        $this->loadEnvironment();
        $this->loadConfiguration();
    }

    /**
     * Register global error and exception handlers.
     *
     * @return void
     */
    protected function registerErrorHandlers()
    {
        set_exception_handler(function ($e) {
            $response = (new \Phantom\Core\Exceptions\Handler())->render($e);
            $response->send();
        });

        set_error_handler(function ($level, $message, $file, $line) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
    }

    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->bind('app', $this);
        $this->bind(Container::class, $this);
        
        $this->singleton('router', function() {
            return new Router();
        });
    }

    /**
     * Load the environment variables.
     *
     * @return void
     */
    protected function loadEnvironment()
    {
        Env::load($this->basePath . '/.env');
    }

    /**
     * Load the configuration files.
     *
     * @return void
     */
    protected function loadConfiguration()
    {
        $this->singleton('config', function () {
            $config = new Config();
            $config->load($this->basePath . '/config');
            return $config;
        });

        // Register Database Service
        $this->singleton('db', function () {
            return new \Phantom\Database\Database(config('database'));
        });

        // Register Session Service
        $this->singleton('session', function () {
            return new \Phantom\Session\Session();
        });

        // Register Auth Service
        $this->singleton('auth', function () {
            return new \Phantom\Auth\AuthManager($this->make('session'));
        });

        // Register Translator Service
        $this->singleton('translator', function () {
            return new Translator(
                $this->basePath . '/lang',
                config('app.locale', 'en'),
                config('app.fallback_locale', 'en')
            );
        });

        // Register Cache Service
        $this->singleton('cache', function () {
            return new \Phantom\Cache\CacheManager();
        });

        // Register Event Service
        $this->singleton('events', function () {
            return new \Phantom\Events\Dispatcher();
        });

        // Register Queue Service
        $this->singleton('queue', function () {
            return new \Phantom\Queues\QueueManager();
        });

        // Register Storage Service
        $this->singleton('storage', function () {
            return new \Phantom\Storage\StorageManager();
        });

        // Register Mail Service
        $this->singleton('mail', function () {
            return new \Phantom\Mail\MailManager();
        });
        
        // Auto-start session for MVP (Should be middleware later)
        if (config('session.driver') === 'file') {
            $this->make('session')->start();
        }
    }
    
    /**
     * Determine if the application has booted.
     * 
     * @var bool
     */
    protected $booted = false;

    /**
     * Handle the incoming request.
     *
     * @param  \Phantom\Http\Request  $request
     * @return \Phantom\Http\Response
     */
    public function handle($request)
    {
        try {
            $this->boot();
            
            return $this->make('router')->dispatch($request);
        } catch (\Throwable $e) {
            return (new \Phantom\Core\Exceptions\Handler())->render($e);
        }
    }

    /**
     * Boot the application services.
     * 
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Load routes
        $this->make('router')->loadRoutes($this->basePath . '/routes/web.php');

        $this->booted = true;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get the base path of the installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }
}
