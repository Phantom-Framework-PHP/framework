<?php

namespace Phantom\Http;

class Request
{
    protected $uri;
    protected $method;
    protected $queryParams;
    protected $postParams;
    protected $routeParams = [];
    protected $serverParams;
    protected $files;

    public function __construct(array $server = [], array $get = [], array $post = [], array $files = [])
    {
        $this->serverParams = $server;
        $this->queryParams = $get;
        $this->postParams = $post;
        $this->files = $files;
        
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
        return new static($_SERVER, $_GET, $_POST, $_FILES);
    }

    /**
     * Get an uploaded file.
     *
     * @param string $key
     * @return UploadedFile|null
     */
    public function file($key)
    {
        if (isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK) {
            return new UploadedFile($this->files[$key]);
        }

        return null;
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
        return $this->postParams[$key] ?? $this->queryParams[$key] ?? $this->routeParams[$key] ?? $default;
    }

    public function setRouteParams(array $params)
    {
        $this->routeParams = $params;
        return $this;
    }

    public function all()
    {
        return array_merge($this->queryParams, $this->postParams, $this->routeParams);
    }

    /**
     * Validate the request data.
     *
     * @param array $rules
     * @return array
     * @throws \Exception
     */
    public function validate(array $rules)
    {
        $validator = new \Phantom\Validation\Validator($this->all(), $rules);

        if (!$validator->validate()) {
            // In a real framework we would redirect back with errors
            // For now, we throw an exception with the first error
            $errors = $validator->errors();
            $firstField = array_key_first($errors);
            throw new \Exception($errors[$firstField][0], 422);
        }

        return array_intersect_key($this->all(), $rules);
    }
}
