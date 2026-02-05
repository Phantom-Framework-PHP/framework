<?php

namespace Phantom\Core;

use Phantom\Http\Request;
use Phantom\Http\Response;
use Exception;

class Router
{
    protected $routes = [];
    protected $namedRoutes = [];
    protected $groupStack = [];
    protected $lastRoute;
    protected $globalMiddleware = [];

    /**
     * Register a global middleware.
     * 
     * @param string $middleware
     * @return $this
     */
    public function use($middleware)
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

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
     * Create a route group with shared attributes.
     *
     * @param array $attributes
     * @param \Closure $callback
     * @return void
     */
    public function group(array $attributes, $callback)
    {
        $this->groupStack[] = $attributes;

        call_user_func($callback, $this);

        array_pop($this->groupStack);
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
        // Calculate prefix and middleware from group stack
        $prefix = '';
        $middleware = [];
        $namePrefix = '';

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $middleware = array_merge($middleware, $groupMiddleware);
            }
            if (isset($group['as'])) {
                $namePrefix .= $group['as'];
            }
        }

        // Normalize URI with prefix
        $uri = '/' . ltrim($uri, '/');
        $finalUri = $prefix . $uri;
        
        // Ensure final URI is normalized
        $finalUri = '/' . ltrim($finalUri, '/');
        if ($finalUri !== '/') {
            $finalUri = rtrim($finalUri, '/');
        }
        
        $route = [
            'method' => $method,
            'uri' => $finalUri,
            'action' => $action,
            'middleware' => $middleware,
            'name' => null
        ];

        $this->routes[$method][$finalUri] = $route;
        // Reference to the newly created route for chaining
        $this->lastRoute = &$this->routes[$method][$finalUri];

        // If we have a name prefix, we might want to apply it immediately if 'name' is chained later,
        // but 'name()' method handles the full name. 
        // Just store the prefix temporarily in the route if needed, 
        // or better: The 'name' method will check the current stack or we store the namePrefix in the route.
        // Let's store the namePrefix in the route array so ->name() can use it.
        $this->lastRoute['name_prefix'] = $namePrefix;

        return $this;
    }

    /**
     * Assign a name to the last registered route.
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        if ($this->lastRoute) {
            $prefix = $this->lastRoute['name_prefix'] ?? '';
            $fullName = $prefix . $name;
            
            $this->lastRoute['name'] = $fullName;
            $this->namedRoutes[$fullName] = [
                'uri' => $this->lastRoute['uri'],
                'method' => $this->lastRoute['method'] // Storing method just in case
            ];
        }

        return $this;
    }

    /**
     * Generate a URL for a named route.
     *
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws Exception
     */
    public function route($name, $parameters = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route not found: [{$name}]");
        }

        $uri = $this->namedRoutes[$name]['uri'];

        // Replace parameters in URI (e.g., /posts/{id})
        foreach ($parameters as $key => $value) {
            if (strpos($uri, '{' . $key . '}') !== false) {
                $uri = str_replace('{' . $key . '}', $value, $uri);
                unset($parameters[$key]);
            }
        }

        // Append remaining parameters as query string
        if (!empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return url($uri);
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

        // Normalize URI: ensure starts with / and remove trailing slash (unless it's just /)
        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUri => $route) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $uri, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $request->setRouteParams($params);

                    $middleware = array_merge($this->globalMiddleware, $route['middleware']);

                    return (new Pipeline())
                        ->send($request)
                        ->through($middleware)
                        ->then(function($request) use ($route) {
                            return $this->resolveAction($route['action'], $request);
                        });
                }
            }
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
            $dependencies = $this->resolveMethodDependencies($action, $request);
            $result = call_user_func_array($action, $dependencies);
        } elseif (is_array($action)) {
            [$controller, $method] = $action;
            $controllerInstance = app($controller);
            $dependencies = $this->resolveMethodDependencies([$controllerInstance, $method], $request);
            $result = call_user_func_array([$controllerInstance, $method], $dependencies);
        } else {
            throw new Exception("Invalid route action type.");
        }

        if ($result instanceof Response) {
            return $result;
        }

        return new Response($result);
    }

    /**
     * Resolve the dependencies for a given method.
     *
     * @param mixed $action
     * @param Request $request
     * @return array
     */
    protected function resolveMethodDependencies($action, Request $request)
    {
        $reflection = is_array($action) 
            ? new \ReflectionMethod($action[0], $action[1]) 
            : new \ReflectionFunction($action);

        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                
                if ($className === Request::class) {
                    $dependencies[] = $request;
                } else {
                    $dependencies[] = app($className);
                }
            } else {
                // For basic parameters, we try to get from request input by name
                $dependencies[] = $request->input($parameter->getName());
            }
        }

        return $dependencies;
    }
    
    public function loadRoutes($path)
    {
        if (file_exists($path)) {
            $router = $this;
            require $path;
        }
    }
}