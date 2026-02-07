<?php

namespace Phantom\Traits;

use Phantom\AI\AIManager;
use Phantom\Core\Container;

trait HasAI
{
    /**
     * Summarize a specific attribute using the default AI driver.
     *
     * @param  string  $attribute
     * @param  int     $sentences
     * @return string
     */
    public function summarize(string $attribute, int $sentences = 2): string
    {
        $content = $this->{$attribute};
        $prompt = "Summarize the following text in exactly {$sentences} sentences: {$content}";

        return Container::getInstance()->make('ai')->generate($prompt);
    }

    /**
     * Translate a specific attribute to a given language.
     *
     * @param  string  $attribute
     * @param  string  $toLocale
     * @return string
     */
    public function translateAttribute(string $attribute, string $toLocale): string
    {
        $content = $this->{$attribute};
        $prompt = "Translate the following text to {$toLocale}. Return only the translation: {$content}";

        return Container::getInstance()->make('ai')->generate($prompt);
    }

    /**
     * Generate content based on a custom prompt and the model's data.
     *
     * @param  string  $prompt
     * @return string
     */
    public function askAI(string $prompt): string
    {
        $data = json_encode($this->toArray());
        $fullPrompt = "Given this data: {$data}. Answer this: {$prompt}";

        return Container::getInstance()->make('ai')->generate($fullPrompt);
    }
}
