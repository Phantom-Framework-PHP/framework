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

        $this->storePulseData([
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

    protected function storePulseData(array $data)
    {
        $path = storage_path('framework/pulse.json');
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $history = [];
        if (file_exists($path)) {
            $history = json_decode(file_get_contents($path), true) ?: [];
        }

        // Mantener solo los últimos 50 registros
        array_unshift($history, $data);
        $history = array_slice($history, 0, 50);

        file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT));
    }
}
