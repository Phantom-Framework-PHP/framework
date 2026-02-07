<?php

namespace Phantom\Storage;

interface DiskInterface
{
    /**
     * Store the given content.
     *
     * @param  string  $path
     * @param  string  $contents
     * @return bool
     */
    public function put($path, $contents);

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string|null
     */
    public function get($path);

    /**
     * Delete the file at a given path.
     *
     * @param  string  $path
     * @return bool
     */
    public function delete($path);

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path);

    /**
     * Get the full path to the file.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path);
}
