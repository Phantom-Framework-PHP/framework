<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Storage\StorageManager;
use Phantom\Storage\LocalDisk;
use Phantom\Storage\FtpDisk;
use Phantom\Storage\S3Disk;
use Phantom\Core\Application;
use Phantom\Core\Container;

class StorageTest extends TestCase
{
    protected function setUp(): void
    {
        // Setup application for config access
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_storage_manager_can_resolve_local_disk()
    {
        $manager = new StorageManager();
        $disk = $manager->disk('local');

        $this->assertInstanceOf(LocalDisk::class, $disk);
    }

    public function test_storage_manager_can_resolve_ftp_disk()
    {
        // Mock config for FTP
        config(['filesystems.disks.test_ftp' => [
            'driver' => 'ftp',
            'host' => 'localhost',
            'username' => 'user',
            'password' => 'pass'
        ]]);

        $manager = new StorageManager();
        $disk = $manager->disk('test_ftp');

        $this->assertInstanceOf(FtpDisk::class, $disk);
    }

    public function test_storage_manager_can_resolve_s3_disk()
    {
        // Mock config for S3
        config(['filesystems.disks.test_s3' => [
            'driver' => 's3',
            'key' => 'key',
            'secret' => 'secret',
            'region' => 'us-east-1',
            'bucket' => 'test-bucket'
        ]]);

        $manager = new StorageManager();
        $disk = $manager->disk('test_s3');

        $this->assertInstanceOf(S3Disk::class, $disk);
    }

    public function test_local_disk_operations()
    {
        $root = dirname(__DIR__, 2) . '/storage/framework/testing';
        $disk = new LocalDisk($root);

        $disk->put('test.txt', 'Hello World');
        $this->assertTrue($disk->exists('test.txt'));
        $this->assertEquals('Hello World', $disk->get('test.txt'));

        $disk->delete('test.txt');
        $this->assertFalse($disk->exists('test.txt'));

        // Cleanup
        rmdir($root);
    }
}
