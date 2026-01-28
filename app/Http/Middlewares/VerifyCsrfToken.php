<?php

namespace Phantom\Http\Middlewares;

use Phantom\Http\Request;
use Phantom\Http\Response;
use Phantom\Security\Csrf;
use Closure;

class VerifyCsrfToken
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
        if ($this->isReading($request) || Csrf::validate($request->input('_token'))) {
            return $next($request);
        }

        return new Response('CSRF token mismatch.', 419);
    }

    /**
     * Determine if the HTTP request uses a reading method.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function isReading(Request $request)
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }
}
