<?php

namespace Phantom\Http;

class Response
{
    protected $content;
    protected $statusCode;
    protected $headers;

    public function __construct($content = '', $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Set the content of the response.
     *
     * @param mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the status code.
     *
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get the content of the response.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the headers of the response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the headers for the response.
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set a single header for the response.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data
     * @param int $status
     * @return $this
     */
    public function json($data, $status = 200)
    {
        $this->content = json_encode($data);
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json';
        
        return $this;
    }

    /**
     * Create a new streamed response instance.
     *
     * @param  callable  $callback
     * @param  int  $status
     * @param  array  $headers
     * @return StreamResponse
     */
    public static function stream(callable $callback, $status = 200, array $headers = [])
    {
        return new StreamResponse($callback, $status, $headers);
    }

    /**
     * Send the response to the browser.
     *
     * @return void
     */
    public function send()
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }
}
