<?php

namespace Tests;

use Phantom\Http\Response;
use PHPUnit\Framework\Assert as PHPUnit;

class TestResponse
{
    /**
     * The response instance.
     *
     * @var Response
     */
    protected $response;

    /**
     * Create a new test response instance.
     *
     * @param  Response  $response
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Assert that the response has a given status code.
     *
     * @param  int  $status
     * @return $this
     */
    public function assertStatus($status)
    {
        PHPUnit::assertEquals($status, $this->response->getStatusCode());
        return $this;
    }

    /**
     * Assert that the response contains the given string.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertSee($value)
    {
        PHPUnit::assertStringContainsString($value, $this->response->getContent());
        return $this;
    }

    /**
     * Assert that the response is a JSON response with the given data.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJson(array $data)
    {
        $content = json_decode($this->response->getContent(), true);
        
        foreach ($data as $key => $value) {
            PHPUnit::assertArrayHasKey($key, $content);
            PHPUnit::assertEquals($value, $content[$key]);
        }
        
        return $this;
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->response->getContent();
    }

    /**
     * Get the headers of the response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * Dynamically access response properties.
     */
    public function __get($key)
    {
        if ($key === 'headers') {
            return $this->getHeaders();
        }

        return $this->response->$key;
    }
}
