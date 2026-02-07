<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Database\ConnectionPool;
use PDO;

class DatabasePoolTest extends TestCase
{
    protected $config = [
        'driver' => 'sqlite',
        'database' => ':memory:'
    ];

    public function test_pool_creates_and_releases_connections()
    {
        $pool = new ConnectionPool($this->config, 2);

        $conn1 = $pool->getConnection();
        $this->assertInstanceOf(PDO::class, $conn1);

        $conn2 = $pool->getConnection();
        $this->assertInstanceOf(PDO::class, $conn2);

        // This should throw an exception because max connections is 2
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection pool limit reached');
        $pool->getConnection();
    }

    public function test_pool_reuses_connections()
    {
        $pool = new ConnectionPool($this->config, 1);

        $conn1 = $pool->getConnection();
        $pool->releaseConnection($conn1);

        $conn2 = $pool->getConnection();
        $this->assertSame($conn1, $conn2);
    }
}
