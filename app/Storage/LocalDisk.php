<?php

namespace Phantom\Storage;

class LocalDisk
{
    protected $root;

    public function __construct($root)
    {
        $this->root = rtrim($root, DIRECTORY_SEPARATOR);

        if (!file_exists($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    public function put($path, $contents)
    {
        $fullPath = $this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        $directory = dirname($fullPath);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($fullPath, $contents) !== false;
    }

    public function get($path)
    {
        $fullPath = $this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        
        if (file_exists($fullPath)) {
            return file_get_contents($fullPath);
        }

        return null;
    }

    public function delete($path)
    {
        $fullPath = $this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public function exists($path)
    {
        return file_exists($this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }
}
