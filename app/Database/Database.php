<?php

namespace Phantom\Database;

use PDO;
use PDOException;
use Exception;

class Database
{
    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The connection pool instance.
     *
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * Create a new Database instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $default = $config['default'];
        $connectionConfig = $config['connections'][$default];

        if (isset($connectionConfig['pool']) && $connectionConfig['pool']['enabled']) {
            $this->pool = new ConnectionPool(
                $connectionConfig, 
                $connectionConfig['pool']['max_connections'] ?? 5
            );
        } else {
            $this->connect($connectionConfig);
        }
    }

    /**
     * Establish the PDO connection.
     *
     * @param  array  $config
     * @return void
     * @throws Exception
     */
    protected function connect(array $config)
    {
        $driver = $config['driver'];

        try {
            if ($driver === 'mysql') {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
                $this->pdo = new PDO($dsn, $config['username'], $config['password']);
            } elseif ($driver === 'sqlite') {
                $dsn = "sqlite:{$config['database']}";
                $this->pdo = new PDO($dsn);
            } else {
                throw new Exception("Database driver [{$driver}] not supported.");
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get a query builder instance for a table.
     *
     * @param  string  $table
     * @return \Phantom\Database\Query\Builder
     */
    public function table($table)
    {
        return (new \Phantom\Database\Query\Builder($this))->table($table);
    }

    /**
     * Execute a raw SQL query.
     *
     * @param  string  $sql
     * @param  array   $params
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        $pdo = $this->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        if ($this->pool) {
            $this->pool->releaseConnection($pdo);
        }

        return $statement;
    }

    /**
     * Fetch all results from a query.
     *
     * @param  string  $sql
     * @param  array   $params
     * @return array
     */
    public function select($sql, $params = [])
    {
        $pdo = $this->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $results = $statement->fetchAll();

        if ($this->pool) {
            $this->pool->releaseConnection($pdo);
        }

        return $results;
    }
    
    /**
     * Get the PDO instance.
     * 
     * @return PDO
     */
    public function getPdo()
    {
        if ($this->pool) {
            return $this->pool->getConnection();
        }

        return $this->pdo;
    }
}
