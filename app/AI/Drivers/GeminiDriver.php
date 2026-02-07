<?php

namespace Phantom\AI\Drivers;

use Phantom\AI\AIInterface;
use Exception;

class GeminiDriver implements AIInterface
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
        $model = $this->config['model'] ?? 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => $options
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("Gemini API Error: " . $err);
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception("Gemini API Error: " . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function embed(string $text): array
    {
        $apiKey = $this->config['key'];
        $model = $this->config['embedding_model'] ?? 'text-embedding-004';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent?key={$apiKey}";

        $payload = [
            'model' => "models/{$model}",
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("Gemini Embedding Error: " . $err);
        }

        $data = json_decode($response, true);

        return $data['embedding']['values'] ?? [];
    }

    public function getClient()
    {
        return $this;
    }
}
