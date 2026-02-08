<?php

namespace Phantom\Http\Middlewares;

use Phantom\Security\Shield;
use Phantom\Http\Response;

class ShieldMiddleware
{
    protected $shield;

    public function __construct()
    {
        $this->shield = new Shield();
    }

    public function handle($request, $next)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if ($this->shield->isBlocked($ip)) {
            return new Response("Your IP ({$ip}) has been blocked due to suspicious activity.", 403);
        }

        try {
            $response = $next($request);

            // Si la respuesta es un 404, incrementamos el riesgo levemente
            if ($response->getStatusCode() === 404) {
                $this->shield->recordRisk($ip, 10);
            }

            return $response;
        } catch (\Throwable $e) {
            // Si hay un error 404 capturado por el router
            if ($e->getCode() === 404) {
                $this->shield->recordRisk($ip, 10);
            }
            throw $e;
        }
    }
}
