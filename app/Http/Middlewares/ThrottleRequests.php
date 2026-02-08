<?php

namespace Phantom\Http\Middlewares;

use Phantom\Core\Container;
use Phantom\Http\Request;
use Phantom\Http\Response;

class ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Phantom\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $maxAttempts = 60, $decaySeconds = 60)
    {
        $limiter = Container::getInstance()->make('ratelimiter');
        
        $key = $this->resolveRequestKey($request);

        if (!$limiter->attempt($key, $maxAttempts, $decaySeconds)) {
            return new Response('Too Many Requests', 429, [
                'Retry-After' => $decaySeconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $response = $next($request);

        // Add headers to response
        $remaining = $limiter->remaining($key, $maxAttempts, $decaySeconds);
        
        if ($response instanceof Response) {
            $response->header('X-RateLimit-Limit', $maxAttempts);
            $response->header('X-RateLimit-Remaining', $remaining);
        }

        return $response;
    }

    /**
     * Resolve the key for the request.
     *
     * @param  \Phantom\Http\Request  $request
     * @return string
     */
    protected function resolveRequestKey(Request $request)
    {
        // Identify by IP and potentially user ID
        $ip = $request->header('X-Forwarded-For') ?? $request->header('Remote-Addr') ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $user = Container::getInstance()->has('auth') ? auth()->user() : null;
        $identifier = $user ? $user->id : $ip;

        return md5($request->method() . $request->uri() . $identifier);
    }
}
