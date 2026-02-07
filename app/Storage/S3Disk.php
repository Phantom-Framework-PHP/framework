<?php

namespace Phantom\Storage;

use Exception;

class S3Disk implements DiskInterface
{
    protected $config;
    protected $client;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        if (!class_exists('Aws\S3\S3Client')) {
            throw new Exception("AWS SDK not found. Please run 'composer require aws/aws-sdk-php'.");
        }

        $this->client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $this->config['region'],
            'credentials' => [
                'key'    => $this->config['key'],
                'secret' => $this->config['secret'],
            ],
            'endpoint' => $this->config['endpoint'] ?? null,
            'use_path_style_endpoint' => $this->config['use_path_style_endpoint'] ?? false,
        ]);

        return $this->client;
    }

    public function put($path, $contents)
    {
        $client = $this->getClient();

        try {
            $client->putObject([
                'Bucket' => $this->config['bucket'],
                'Key'    => $this->path($path),
                'Body'   => $contents,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function get($path)
    {
        $client = $this->getClient();

        try {
            $result = $client->getObject([
                'Bucket' => $this->config['bucket'],
                'Key'    => $this->path($path),
            ]);
            return (string) $result['Body'];
        } catch (Exception $e) {
            return null;
        }
    }

    public function delete($path)
    {
        $client = $this->getClient();

        try {
            $client->deleteObject([
                'Bucket' => $this->config['bucket'],
                'Key'    => $this->path($path),
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function exists($path)
    {
        $client = $this->getClient();
        return $client->doesObjectExistV2($this->config['bucket'], $this->path($path));
    }

    public function path($path)
    {
        $root = rtrim($this->config['root'] ?? '', '/');
        return ltrim($root . '/' . ltrim($path, '/'), '/');
    }
}
