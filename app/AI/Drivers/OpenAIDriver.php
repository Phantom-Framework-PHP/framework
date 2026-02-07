<?php

namespace Phantom\AI\Drivers;

use Phantom\AI\AIInterface;
use Exception;

class OpenAIDriver implements AIInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function chat(string $prompt, array $options = []): string
    {
        return $this->generate($prompt, $options);
    }

    public function generate(string $prompt, array $options = []): string
    {
        $apiKey = $this->config['key'];
        $model = $this->config['model'] ?? 'gpt-3.5-turbo';
        $url = "https://api.openai.com/v1/chat/completions";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $options['temperature'] ?? 0.7
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("OpenAI API Error: " . $err);
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception("OpenAI API Error: " . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['choices'][0]['message']['content'] ?? '';
    }

    public function getClient()
    {
        return $this;
    }
}
