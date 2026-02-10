<?php

namespace Phantom\Core\Exceptions;

use Throwable;
use Phantom\Http\Response;

class Handler
{
    /**
     * Render an exception into an HTTP response or CLI output.
     *
     * @param  Throwable  $e
     * @return Response|void
     */
    public function render(Throwable $e)
    {
        $this->logError($e);

        $isTesting = defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');

        if (PHP_SAPI === 'cli' && !$isTesting) {
            $this->renderCli($e);
            return;
        }

        $debug = config('app.debug', false);
        
        $code = $e->getCode();
        $statusCode = (is_int($code) && $code >= 400 && $code <= 599) ? $code : 500;
        
        // Always show the nice 404 page for Not Found errors
        if ($statusCode === 404) {
            return new Response($this->renderProduction(404), 404);
        }
        
        if ($debug) {
            $content = $this->renderDebug($e);
        } else {
            $content = $this->renderProduction($statusCode);
        }

        return new Response($content, $statusCode);
    }

    /**
     * Render the exception for the CLI.
     *
     * @param Throwable $e
     * @return void
     */
    protected function renderCli(Throwable $e)
    {
        $debug = config('app.debug', false);
        
        echo "\n\033[41;37m ERROR \033[0m {$e->getMessage()}\n";
        echo "in \033[33m{$e->getFile()}\033[0m on line \033[33m{$e->getLine()}\033[0m\n\n";

        if ($debug) {
            echo "\033[1;31mStack Trace:\033[0m\n";
            echo $e->getTraceAsString() . "\n";
        }
        
        if (config('app.env') !== 'testing') {
            exit(1);
        }
    }

    protected function renderDebug(Throwable $e)
    {
        // We can try to load a nice debug view, or fallback to a styled internal one
        try {
            return (string) view('errors/debug', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
        } catch (\Exception $ex) {
            // Fallback in case the view doesn't exist yet
            $message = $e->getMessage();
            $trace = $e->getTraceAsString();
            return "<body style='font-family:sans-serif;padding:2rem;background:#fff5f5'><h1 style='color:#c53030'>Debug Error</h1><p><b>{$message}</b></p><pre style='background:#fff;padding:1rem;border-radius:8px'>{$trace}</pre></body>";
        }
    }

    protected function renderProduction($statusCode)
    {
        $viewPath = "errors/{$statusCode}";
        
        try {
            return (string) view($viewPath);
        } catch (\Exception $e) {
            $messages = [
                404 => 'Page Not Found',
                419 => 'Page Expired',
                500 => 'Internal Server Error'
            ];

            $message = $messages[$statusCode] ?? 'An error occurred';
            
            return "<h1>{$statusCode}</h1><p>{$message}</p>";
        }
    }

    protected function logError(Throwable $e)
    {
        $logPath = base_path('storage/logs/phantom.log');
        $date = date('Y-m-d H:i:s');
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n%s\n",
            $date,
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            str_repeat('-', 80)
        );

        error_log($message, 3, $logPath);
    }
}
