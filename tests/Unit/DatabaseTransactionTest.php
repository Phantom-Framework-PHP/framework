<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Database\Database;
use Phantom\Database\ConnectionPool;
use PDO;

class DatabaseTransactionTest extends TestCase
{
    protected $config = [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'pool' => [
                    'enabled' => true,
                    'max_connections' => 1
                ]
            ]
        ]
    ];

    public function test_transaction_manages_connection_lifecycle()
    {
        $db = new Database($this->config);
        
        $db->transaction(function($db) {
            // Within transaction, getting PDO should return the same active connection
            $pdo1 = $db->getPdo();
            $pdo2 = $db->getPdo();
            $this->assertSame($pdo1, $pdo2);
            
            $db->query("CREATE TABLE test (id INT)");
            $db->query("INSERT INTO test VALUES (1)");
        });

        // After transaction, activeConnection should be null
        $results = $db->select("SELECT * FROM test");
        $this->assertCount(1, $results);
    }

    public function test_transaction_rollbacks_on_exception()
    {
        $db = new Database($this->config);
        $db->query("CREATE TABLE test (id INT)");

        try {
            $db->transaction(function($db) {
                $db->query("INSERT INTO test VALUES (1)");
                throw new \Exception("Failure");
            });
        } catch (\Exception $e) {
            $this->assertEquals("Failure", $e->getMessage());
        }

        $results = $db->select("SELECT * FROM test");
        $this->assertCount(0, $results); // Should be empty due to rollback
    }
}
