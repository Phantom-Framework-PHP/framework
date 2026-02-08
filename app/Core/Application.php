<?php

namespace Phantom\Core;

class Application extends Container
{
    /**
     * The Phantom framework version.
     *
     * @var string
     */
    /**
     * The Phantom framework version.
     *
     * @var string
     */
    const VERSION = '1.18.3';

    /**
     * The base path for the Phantom installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = [];

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
        $this->registerConfiguredProviders();
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
    }

    /**
     * Register the configured service providers.
     *
     * @return void
     */
    protected function registerConfiguredProviders()
    {
        $providers = config('app.providers', [
            // List of default providers if config is missing
        ]);

        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param  string|ServiceProvider  $provider
     * @return ServiceProvider
     */
    public function register($provider)
    {
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        $provider->register();

        $this->serviceProviders[] = $provider;

        if ($this->booted) {
            $provider->boot();
        }

        return $provider;
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

        // Boot all registered providers
        foreach ($this->serviceProviders as $provider) {
            $provider->boot();
        }

        $router = $this->make('router');

        // Register Internal Framework Routes
        $this->registerInternalRoutes($router);

        // Load user routes
        $router->loadRoutes($this->basePath . '/routes/web.php');

        $this->booted = true;
    }

    /**
     * Register internal framework routes based on configuration.
     * 
     * @param \Phantom\Core\Router $router
     * @return void
     */
    protected function registerInternalRoutes($router)
    {
        // Phantom Pulse
        if (config('app.pulse_enabled', true)) {
            $router->group(['prefix' => 'phantom/pulse'], function($router) {
                $router->get('/', [\Phantom\Http\Controllers\PulseController::class, 'index']);
                $router->post('/clear', [\Phantom\Http\Controllers\PulseController::class, 'clear']);
                $router->post('/reset-ip', [\Phantom\Http\Controllers\PulseController::class, 'resetIp']);
            });
        }

        // Phantom Live
        if (config('app.live_enabled', true)) {
            $router->post('/phantom/live/update', [\Phantom\Http\Controllers\LiveController::class, 'update']);
        }
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

    /**
     * Refresh the application for a new request.
     *
     * @return void
     */
    public function refreshRequest()
    {
        $this->forget('request');
        $this->forget(\Phantom\Http\Request::class);
        
        // Also forget session and auth if they store request-specific state
        $this->forget('session');
        $this->forget('auth');
        
        // Clear resolved instances of controllers to ensure fresh injection
        // This is a simplified approach; in a full implementation we might track resolved services.
    }
}
