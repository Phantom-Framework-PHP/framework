<?php

namespace Phantom\Mail;

class SmtpTransport
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Send the given message.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure  $callback
     * @return void
     */
    public function send($view, array $data, $callback)
    {
        $message = new Message();
        $callback($message);

        $content = view($view, $data);
        
        // In a real implementation, this would use a library like SwiftMailer or Symfony Mailer
        // For our minimalist framework, we will simulate the SMTP sending process
        // or use the native mail() function as a fallback for the MVP
        
        $to = implode(', ', array_keys($message->getTo()));
        $subject = $message->getSubject();
        $headers = $this->buildHeaders($message);

        mail($to, $subject, $content, $headers);
    }

    protected function buildHeaders(Message $message)
    {
        $from = config('mail.from');
        $headers = "From: {$from['name']} <{$from['address']}>\r\n";
        $headers .= "Reply-To: {$from['address']}\r\n";
        $headers .= "X-Mailer: Phantom Framework\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return $headers;
    }
}
