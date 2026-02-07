<?php

namespace Phantom\AI;

interface AIInterface
{
    /**
     * Send a prompt to the AI model and get a response.
     *
     * @param  string  $prompt
     * @param  array   $options
     * @return string
     */
    public function chat(string $prompt, array $options = []): string;

    /**
     * Generate text based on a prompt.
     *
     * @param  string  $prompt
     * @param  array   $options
     * @return string
     */
    public function generate(string $prompt, array $options = []): string;

    /**
     * Generate vector embeddings for the given text.
     *
     * @param  string  $text
     * @return array
     */
    public function embed(string $text): array;

    /**
     * Get the underlying client instance.
     *
     * @return mixed
     */
    public function getClient();
}
