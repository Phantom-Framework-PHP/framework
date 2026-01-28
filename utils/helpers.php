<?php

use Phantom\Core\Container;
use Phantom\Core\Env;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @return mixed|\Phantom\Core\Application
     */
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract);
    }
}

if (! function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (! function_exists('config')) {
    /**
     * Get the specified configuration value.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|\Phantom\Core\Config
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the database path.
     *
     * @param  string  $path
     * @return string
     */
    function database_path($path = '')
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the storage path.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (! function_exists('view')) {
    /**
     * Render a view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return string
     */
    function view($view, $data = [])
    {
        return \Phantom\View\View::make($view, $data);
    }
}

if (! function_exists('session')) {
    /**
     * Get the session instance or a value from session.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|\Phantom\Session\Session
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        return app('session')->get($key, $default);
    }
}

if (! function_exists('auth')) {
    /**
     * Get the auth manager instance.
     *
     * @return \Phantom\Auth\AuthManager
     */
    function auth()
    {
        return app('auth');
    }
}

if (! function_exists('cache')) {
    /**
     * Get the cache instance or a value from cache.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|\Phantom\Cache\CacheManager
     */
    function cache($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('cache');
        }

        return app('cache')->get($key) ?: $default;
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    function event($event, $payload = [])
    {
        return app('events')->dispatch($event, $payload);
    }
}

if (! function_exists('dispatch')) {
    /**
     * Dispatch a job to the queue.
     *
     * @param  object  $job
     * @return mixed
     */
    function dispatch($job)
    {
        return app('queue')->push($job);
    }
}

if (! function_exists('storage')) {
    /**
     * Get the storage manager or a disk.
     *
     * @param  string|null  $disk
     * @return \Phantom\Storage\StorageManager|\Phantom\Storage\LocalDisk
     */
    function storage($disk = null)
    {
        if (is_null($disk)) {
            return app('storage');
        }

        return app('storage')->disk($disk);
    }
}

if (! function_exists('validate_file')) {
    /**
     * Validate a file's integrity and security.
     *
     * @param string $path
     * @param string $extension
     * @return bool
     */
    function validate_file($path, $extension)
    {
        return \Phantom\Security\FileValidator::validate($path, $extension);
    }
}

if (! function_exists('mail_send')) {
    /**
     * Send a new email message.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure  $callback
     * @return void
     */
    function mail_send($view, array $data, $callback)
    {
        app('mail')->send($view, $data, $callback);
    }
}

if (! function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array   $replace
     * @return string
     */
    function __($key, $replace = [])
    {
        return app('translator')->get($key, $replace);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @return string
     */
    function url($path = '')
    {
        $base = config('app.url', 'http://localhost');
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (! function_exists('redirect')) {
    /**
     * Create a redirect response.
     *
     * @param  string  $path
     * @return \Phantom\Http\Response
     */
    function redirect($path)
    {
        return new \Phantom\Http\Response('', 302, ['Location' => url($path)]);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token.
     *
     * @return string
     */
    function csrf_token()
    {
        return \Phantom\Security\Csrf::token();
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF HTML input field.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}
