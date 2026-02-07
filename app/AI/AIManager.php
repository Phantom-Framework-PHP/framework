<?php

namespace Phantom\AI;

use Exception;
use Phantom\AI\Drivers\GeminiDriver;
use Phantom\AI\Drivers\OpenAIDriver;

class AIManager
{
    protected $drivers = [];

    /**
     * Get an AI driver instance.
     *
     * @param  string|null  $name
     * @return AIInterface
     */
    public function driver($name = null)
    {
        $name = $name ?: config('ai.default');

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->resolve($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Resolve the AI driver.
     *
     * @param  string  $name
     * @return AIInterface
     * @throws Exception
     */
    protected function resolve($name)
    {
        $config = config("ai.drivers.{$name}");

        if (is_null($config)) {
            throw new Exception("AI driver [{$name}] is not defined.");
        }

        $driver = $config['driver'];

        if ($driver === 'gemini') {
            return new GeminiDriver($config);
        }

        if ($driver === 'openai') {
            return new OpenAIDriver($config);
        }

        throw new Exception("AI driver [{$driver}] not supported yet.");
    }

    /**
     * Dynamically call methods on the default driver.
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
