<?php

namespace Phantom\Events;

class PusherBroadcaster extends Broadcaster
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Broadcast the given event via Pusher REST API.
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $appId = $this->config['app_id'];
        $key = $this->config['key'];
        $secret = $this->config['secret'];
        $cluster = $this->config['options']['cluster'] ?? null;
        $host = $this->config['options']['host'] ?? ($cluster ? "api-{$cluster}.pusher.com" : 'api.pusherapp.com');

        $path = "/apps/{$appId}/events";
        $body = json_encode([
            'name' => $event,
            'channels' => $channels,
            'data' => json_encode($payload)
        ]);

        $params = [
            'auth_key' => $key,
            'auth_timestamp' => time(),
            'auth_version' => '1.0',
            'body_md5' => md5($body)
        ];

        ksort($params);
        $queryString = http_build_query($params);
        $authSignature = hash_hmac('sha256', "POST
{$path}
{$queryString}", $secret);
        
        $url = "http://{$host}{$path}?{$queryString}&auth_signature={$authSignature}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}
