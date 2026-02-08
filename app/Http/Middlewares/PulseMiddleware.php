<?php

namespace Phantom\Http\Middlewares;

use Phantom\Database\Database;

class PulseMiddleware
{
    public function handle($request, $next)
    {
        // Solo activar si estamos en modo debug o si está habilitado específicamente
        if (!config('app.debug', false)) {
            return $next($request);
        }

        Database::enableQueryLog();
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $memory = round(memory_get_peak_usage() / 1024 / 1024, 2);
        $queries = Database::getQueryLog();

        (new \Phantom\Services\PulseService())->record([
            'url' => $request->getPath(),
            'method' => $request->getMethod(),
            'duration' => $duration,
            'memory' => $memory,
            'queries_count' => count($queries),
            'queries' => $queries,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return $response;
    }
}
