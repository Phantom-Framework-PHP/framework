<?php

namespace Phantom\Http;

class Request
{
    protected $uri;
    protected $method;
    protected $queryParams;
    protected $postParams;
    protected $serverParams;

    public function __construct(array $server = [], array $get = [], array $post = [])
    {
        $this->serverParams = $server;
        $this->queryParams = $get;
        $this->postParams = $post;
        
        $this->uri = $this->parseUri($server['REQUEST_URI'] ?? '/');
        $this->method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Capture the current HTTP request from globals.
     *
     * @return static
     */
    public static function capture()
    {
        return new static($_SERVER, $_GET, $_POST);
    }

    /**
     * Parse the URI to remove query strings.
     * 
     * @param string $uri
     * @return string
     */
    protected function parseUri($uri)
    {
        return strtok($uri, '?');
    }

    public function uri()
    {
        return $this->uri;
    }

    public function method()
    {
        return $this->method;
    }

    public function input($key, $default = null)
    {
        return $this->postParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function all()
    {
        return array_merge($this->queryParams, $this->postParams);
    }
}
