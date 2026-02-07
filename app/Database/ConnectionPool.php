<?php

namespace Phantom\Database;

use PDO;
use Exception;

class ConnectionPool
{
    protected $config;
    protected $available = [];
    protected $busy = [];
    protected $maxConnections;

    public function __construct(array $config, $maxConnections = 5)
    {
        $this->config = $config;
        $this->maxConnections = $maxConnections;
    }

    /**
     * Get a connection from the pool.
     *
     * @return PDO
     * @throws Exception
     */
    public function getConnection()
    {
        if (!empty($this->available)) {
            $pdo = array_pop($this->available);
            $this->busy[spl_object_id($pdo)] = $pdo;
            return $pdo;
        }

        if (count($this->busy) < $this->maxConnections) {
            $pdo = $this->createNewConnection();
            $this->busy[spl_object_id($pdo)] = $pdo;
            return $pdo;
        }

        throw new Exception("Database connection pool limit reached ({$this->maxConnections}).");
    }

    /**
     * Release a connection back to the pool.
     *
     * @param  PDO  $pdo
     * @return void
     */
    public function releaseConnection(PDO $pdo)
    {
        $id = spl_object_id($pdo);
        if (isset($this->busy[$id])) {
            unset($this->busy[$id]);
            $this->available[] = $pdo;
        }
    }

    /**
     * Create a new PDO connection.
     *
     * @return PDO
     */
    protected function createNewConnection()
    {
        $driver = $this->config['driver'];

        if ($driver === 'mysql') {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $pdo = new PDO($dsn, $this->config['username'], $this->config['password']);
        } elseif ($driver === 'sqlite') {
            $dsn = "sqlite:{$this->config['database']}";
            $pdo = new PDO($dsn);
        } else {
            throw new Exception("Database driver [{$driver}] not supported.");
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        return $pdo;
    }
}
