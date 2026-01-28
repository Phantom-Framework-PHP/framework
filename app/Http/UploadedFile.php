<?php

namespace Phantom\Http;

use Phantom\Security\FileValidator;

class UploadedFile
{
    protected $fileData;

    public function __construct(array $fileData)
    {
        $this->fileData = $fileData;
    }

    public function name()
    {
        return $this->fileData['name'];
    }

    public function tmpPath()
    {
        return $this->fileData['tmp_name'];
    }

    public function extension()
    {
        return pathinfo($this->name(), PATHINFO_EXTENSION);
    }

    /**
     * Validate the file using MIME type and Magic Numbers.
     *
     * @return bool
     */
    public function isValid()
    {
        try {
            return FileValidator::validate($this->tmpPath(), $this->extension());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Store the uploaded file.
     *
     * @param string $path
     * @param string|null $disk
     * @return bool|string
     */
    public function store($path, $disk = null)
    {
        if (!$this->isValid()) {
            return false;
        }

        $contents = file_get_contents($this->tmpPath());
        $fileName = bin2hex(random_bytes(16)) . '.' . $this->extension();
        $fullPath = rtrim($path, '/') . '/' . $fileName;

        if (storage($disk)->put($fullPath, $contents)) {
            return $fullPath;
        }

        return false;
    }
}
