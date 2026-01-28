<?php

namespace Phantom\Security;

use Exception;

class FileValidator
{
    /**
     * Map of extensions to their allowed MIME types and Magic Numbers (hex).
     *
     * @var array
     */
    protected static $signatures = [
        'jpg'  => ['mime' => 'image/jpeg', 'magic' => 'ffd8ff'],
        'jpeg' => ['mime' => 'image/jpeg', 'magic' => 'ffd8ff'],
        'png'  => ['mime' => 'image/png',  'magic' => '89504e47'],
        'gif'  => ['mime' => 'image/gif',  'magic' => '47494638'],
        'pdf'  => ['mime' => 'application/pdf', 'magic' => '25504446'],
        'zip'  => ['mime' => 'application/zip', 'magic' => '504b0304'],
    ];

    /**
     * Validate an uploaded file for security.
     *
     * @param string $filePath Path to the temporary file.
     * @param string $extension Desired extension (e.g., 'jpg').
     * @return bool
     * @throws Exception
     */
    public static function validate($filePath, $extension)
    {
        $extension = strtolower($extension);

        if (!isset(static::$signatures[$extension])) {
            throw new Exception("Unsupported file extension for validation: {$extension}");
        }

        $expected = static::$signatures[$extension];

        // 1. Check MIME Type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if ($mime !== $expected['mime']) {
            return false;
        }

        // 2. Check Magic Number (Binary Signature)
        $handle = fopen($filePath, 'rb');
        $bytes = fread($handle, 8); // Read first 8 bytes
        fclose($handle);

        $hex = bin2hex($bytes);
        
        return str_starts_with($hex, $expected['magic']);
    }
}
