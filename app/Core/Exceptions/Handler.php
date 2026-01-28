<?php

namespace Phantom\Core\Exceptions;

use Throwable;
use Phantom\Http\Response;

class Handler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  Throwable  $e
     * @return Response
     */
    public function render(Throwable $e)
    {
        $debug = config('app.debug', false);
        
        $statusCode = method_exists($e, 'getCode') && $e->getCode() >= 400 ? $e->getCode() : 500;
        
        if ($debug) {
            $content = $this->renderDebug($e);
        } else {
            $content = $this->renderProduction($statusCode);
        }

        return new Response($content, $statusCode);
    }

    protected function renderDebug(Throwable $e)
    {
        return "<h1>Error</h1><p>{$e->getMessage()}</p><pre>{$e->getTraceAsString()}</pre>";
    }

    protected function renderProduction($statusCode)
    {
        $messages = [
            404 => 'Page Not Found',
            419 => 'Page Expired',
            500 => 'Internal Server Error'
        ];

        $message = $messages[$statusCode] ?? 'An error occurred';
        
        return "<h1>{$statusCode}</h1><p>{$message}</p>";
    }
}
