<?php

namespace Phantom\Core;

class Translator
{
    protected $locale;
    protected $fallback;
    protected $lines = [];
    protected $path;

    public function __construct($path, $locale, $fallback = 'en')
    {
        $this->path = $path;
        $this->locale = $locale;
        $this->fallback = $fallback;
    }

    /**
     * Get the translation for the given key.
     *
     * @param  string  $key
     * @param  array   $replace
     * @return string
     */
    public function get($key, array $replace = [])
    {
        [$file, $line] = $this->parseKey($key);

        $this->load($file);

        $translation = $this->lines[$this->locale][$file][$line] 
                    ?? $this->lines[$this->fallback][$file][$line] 
                    ?? $key;

        return $this->makeReplacements($translation, $replace);
    }

    /**
     * Load the translation file if not already loaded.
     *
     * @param  string  $file
     * @return void
     */
    protected function load($file)
    {
        if (isset($this->lines[$this->locale][$file])) {
            return;
        }

        $locales = array_unique([$this->locale, $this->fallback]);

        foreach ($locales as $locale) {
            $path = "{$this->path}/{$locale}/{$file}.php";
            if (file_exists($path)) {
                $this->lines[$locale][$file] = require $path;
            } else {
                $this->lines[$locale][$file] = [];
            }
        }
    }

    /**
     * Parse the key into file and line.
     *
     * @param  string  $key
     * @return array
     */
    protected function parseKey($key)
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);
        $line = implode('.', $segments);

        return [$file, $line];
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @param  array   $replace
     * @return string
     */
    protected function makeReplacements($line, array $replace)
    {
        foreach ($replace as $key => $value) {
            $line = str_replace(':' . $key, $value, $line);
        }

        return $line;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
