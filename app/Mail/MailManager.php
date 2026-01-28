<?php

namespace Phantom\Mail;

use Exception;

class MailManager
{
    protected $mailers = [];

    /**
     * Get a mailer instance.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function mailer($name = null)
    {
        $name = $name ?: config('mail.default');

        if (!isset($this->mailers[$name])) {
            $this->mailers[$name] = $this->resolve($name);
        }

        return $this->mailers[$name];
    }

    /**
     * Resolve the mailer instance.
     *
     * @param  string  $name
     * @return mixed
     * @throws Exception
     */
    protected function resolve($name)
    {
        $config = config("mail.mailers.{$name}");

        if (is_null($config)) {
            throw new Exception("Mailer [{$name}] is not defined.");
        }

        $transport = $config['transport'];
        $method = 'create' . ucfirst($transport) . 'Transport';

        if (method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new Exception("Transport [{$transport}] not supported.");
    }

    protected function createSmtpTransport(array $config)
    {
        return new SmtpTransport($config);
    }

    /**
     * Send a new message.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure  $callback
     * @return void
     */
    public function send($view, array $data, $callback)
    {
        $this->mailer()->send($view, $data, $callback);
    }
}
