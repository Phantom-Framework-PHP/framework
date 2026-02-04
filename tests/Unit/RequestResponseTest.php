<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Http\Request;
use Phantom\Http\Response;

class RequestResponseTest extends TestCase
{
    public function test_request_parses_uri_and_method()
    {
        $request = new Request(['REQUEST_URI' => '/users?id=1', 'REQUEST_METHOD' => 'POST']);
        
        $this->assertEquals('/users', $request->uri());
        $this->assertEquals('POST', $request->method());
    }

    public function test_request_input_retrieval()
    {
        $request = new Request([], ['name' => 'John'], ['age' => 25]);
        
        $this->assertEquals('John', $request->input('name'));
        $this->assertEquals(25, $request->input('age'));
        $this->assertEquals('default', $request->input('non_existent', 'default'));
    }

    public function test_response_status_and_content()
    {
        $response = new Response('Hello World', 201);
        
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_response_setters()
    {
        $response = new Response();
        $response->setContent('Updated Content')->setStatusCode(404);
        
        $this->assertEquals('Updated Content', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());
    }
}
