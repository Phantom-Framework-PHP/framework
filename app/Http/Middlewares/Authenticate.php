<?php

namespace Phantom\Http\Middlewares;

use Phantom\Http\Request;
use Phantom\Http\Response;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return new Response("Unauthorized. Please login.", 401);
        }

        return $next($request);
    }
}
