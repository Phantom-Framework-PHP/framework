<?php

namespace Phantom\Core;

use Phantom\Http\Request;
use Phantom\Http\Response;
use Exception;

class Router
{
    protected $routes = [];
    protected $lastRoute;

    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param mixed $action
     * @return $this
     */
    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param mixed $action
     * @return $this
     */
    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Add a route to the collection.
     *
     * @param string $method
     * @param string $uri
     * @param mixed $action
     * @return $this
     */
    protected function addRoute($method, $uri, $action)
    {
        $uri = '/' . ltrim($uri, '/');
        
        $route = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => []
        ];

        $this->routes[$method][$uri] = $route;
        $this->lastRoute = &$this->routes[$method][$uri];

        return $this;
    }

    /**
     * Assign middleware to the last registered route.
     *
     * @param string|array $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->lastRoute['middleware'] = array_merge($this->lastRoute['middleware'], $middleware);
        } else {
            $this->lastRoute['middleware'][] = $middleware;
        }

        return $this;
    }

    /**
     * Dispatch the request to the appropriate route.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function dispatch(Request $request)
    {
        $method = $request->method();
        $uri = $request->uri();

        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            
            return (new Pipeline())
                ->send($request)
                ->through($route['middleware'])
                ->then(function($request) use ($route) {
                    return $this->resolveAction($route['action'], $request);
                });
        }

        throw new Exception("Route not found: [{$method}] {$uri}", 404);
    }

    /**
     * Resolve the action (Closure or Controller).
     *
     * @param mixed $action
     * @param Request $request
     * @return Response
     */
    protected function resolveAction($action, Request $request)
    {
        if (is_callable($action)) {
            $result = call_user_func($action, $request);
        } elseif (is_array($action)) {
            [$controller, $method] = $action;
            $controllerInstance = app($controller);
            $result = call_user_func([$controllerInstance, $method], $request);
        } else {
            throw new Exception("Invalid route action type.");
        }

        if ($result instanceof Response) {
            return $result;
        }

        return new Response($result);
    }
    
    public function loadRoutes($path)
    {
        if (file_exists($path)) {
            $router = $this;
            require $path;
        }
    }
}