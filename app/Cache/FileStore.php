<?php

namespace Phantom\Cache;

class FileStore
{
    protected $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
        
        if (!file_exists($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed|null
     */
    public function get($key)
    {
        $path = $this->path($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $data = unserialize($content);

        if (time() >= $data['expiration']) {
            $this->forget($key);
            return null;
        }

        return $data['value'];
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return bool
     */
    public function put($key, $value, $seconds = 3600)
    {
        $expiration = time() + $seconds;
        
        $data = serialize([
            'value' => $value,
            'expiration' => $expiration
        ]);

        return file_put_contents($this->path($key), $data) !== false;
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        $path = $this->path($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $files = glob($this->directory . '/*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
        return true;
    }

    protected function path($key)
    {
        return $this->directory . DIRECTORY_SEPARATOR . sha1($key);
    }
}
