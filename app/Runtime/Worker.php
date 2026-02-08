<?php

namespace Phantom\Runtime;

use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Response;

class Worker
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new Worker instance.
     *
     * @param  Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle a request and return a response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function handle(Request $request)
    {
        // Refresh the application for the new request
        $this->app->refreshRequest();

        // Bind the new request to the container
        $this->app->instance(Request::class, $request);
        $this->app->instance('request', $request);

        // Process the request
        $response = $this->app->handle($request);

        // Return the response
        return $response;
    }
}
