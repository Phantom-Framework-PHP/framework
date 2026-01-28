<?php

namespace Phantom\Core;

use ReflectionClass;
use ReflectionParameter;
use ReflectionNamedType;
use Exception;

class Container
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The container's shared instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Register a binding with the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @return mixed
     *
     * @throws \Exception
     */
    public function make($abstract)
    {
        // 1. If it's a shared instance already resolved, return it.
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 2. If we have a binding...
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $shared = $this->bindings[$abstract]['shared'];

            // If concrete is a Closure, execute it.
            if ($concrete instanceof \Closure) {
                $object = $concrete($this);
            } else {
                // If it's a string (class name), resolve it recursively.
                $object = $this->build($concrete);
            }

            if ($shared) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        // 3. If no binding, try to resolve as a class directly.
        return $this->build($abstract);
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  string  $concrete
     * @return mixed
     *
     * @throws \Exception
     */
    protected function build($concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve the dependencies from the ReflectionParameters.
     *
     * @param  array  $dependencies
     * @return array
     *
     * @throws \Exception
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($dependency->isDefaultValueAvailable()) {
                    $results[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Unresolvable dependency resolving [$dependency] in class {$dependency->getDeclaringClass()->getName()}");
                }
            } else {
                $results[] = $this->make($type->getName());
            }
        }

        return $results;
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \Phantom\Core\Container|null  $container
     * @return \Phantom\Core\Container|static
     */
    public static function setInstance(Container $container = null)
    {
        return static::$instance = $container;
    }
}
