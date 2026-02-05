<?php

namespace Phantom\Http;

class StreamResponse extends Response
{
    protected $callback;

    public function __construct(callable $callback, $statusCode = 200, array $headers = [])
    {
        parent::__construct('', $statusCode, array_merge([
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable buffering for Nginx
        ], $headers));

        $this->callback = $callback;
    }

    /**
     * Send the streamed response to the browser.
     *
     * @return void
     */
    public function send()
    {
        // Send headers
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Disable time limit for streaming
        set_time_limit(0);

        // Execute the callback
        ($this->callback)($this);
    }

    /**
     * Send an event to the client.
     *
     * @param  mixed   $data
     * @param  string|null  $event
     * @return void
     */
    public function event($data, $event = null)
    {
        if ($event) {
            echo "event: {$event}
";
        }

        $data = is_array($data) || is_object($data) ? json_encode($data) : $data;
        
        echo "data: {$data}

";

        // Flush the output buffer
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
