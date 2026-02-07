<?php

namespace Phantom\Storage;

use Exception;

class FtpDisk implements DiskInterface
{
    protected $config;
    protected $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $this->connection = ftp_connect($this->config['host'], $this->config['port'] ?? 21, $this->config['timeout'] ?? 90);

        if (!$this->connection || !ftp_login($this->connection, $this->config['username'], $this->config['password'])) {
            throw new Exception("Could not connect to FTP server.");
        }

        if (isset($this->config['passive']) && $this->config['passive']) {
            ftp_pasv($this->connection, true);
        }

        return $this->connection;
    }

    public function put($path, $contents)
    {
        $connection = $this->connect();
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $contents);
        rewind($temp);

        $result = ftp_fput($connection, $this->path($path), $temp, FTP_BINARY);
        fclose($temp);

        return $result;
    }

    public function get($path)
    {
        $connection = $this->connect();
        $temp = fopen('php://temp', 'r+');

        if (ftp_fget($connection, $temp, $this->path($path), FTP_BINARY)) {
            rewind($temp);
            $contents = stream_get_contents($temp);
            fclose($temp);
            return $contents;
        }

        fclose($temp);
        return null;
    }

    public function delete($path)
    {
        $connection = $this->connect();
        return @ftp_delete($connection, $this->path($path));
    }

    public function exists($path)
    {
        $connection = $this->connect();
        $directory = dirname($path);
        $filename = basename($path);
        $files = ftp_nlist($connection, $this->path($directory));

        return is_array($files) && in_array($this->path($path), $files);
    }

    public function path($path)
    {
        $root = rtrim($this->config['root'] ?? '', '/');
        return $root . '/' . ltrim($path, '/');
    }

    public function __destruct()
    {
        if ($this->connection) {
            ftp_close($this->connection);
        }
    }
}
