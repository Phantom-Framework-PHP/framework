<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Http\Request;

abstract class FeatureTestCase extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        Container::setInstance(null);
        $this->app = new Application(dirname(__DIR__));
    }

    /**
     * Perform a GET request.
     *
     * @param  string  $uri
     * @param  array   $headers
     * @return TestResponse
     */
    public function get($uri, array $headers = [])
    {
        return $this->call('GET', $uri, [], [], $headers);
    }

    /**
     * Perform a POST request.
     *
     * @param  string  $uri
     * @param  array   $data
     * @param  array   $headers
     * @return TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        return $this->call('POST', $uri, $data, [], $headers);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array   $parameters
     * @param  array   $files
     * @param  array   $headers
     * @return TestResponse
     */
    public function call($method, $uri, $parameters = [], $files = [], $headers = [])
    {
        $server = [
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => strtoupper($method),
        ];

        foreach ($headers as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        $request = new Request($server, $method === 'GET' ? $parameters : [], $method === 'POST' ? $parameters : [], $files);
        
        $response = $this->app->handle($request);

        return new TestResponse($response);
    }
}
