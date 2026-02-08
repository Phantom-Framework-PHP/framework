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
     * The connection currently in use (for transactions or long scopes).
     *
     * @var PDO|null
     */
    protected $activeConnection;

    /**
     * The recorded queries for telemetry.
     *
     * @var array
     */
    protected static $queryLog = [];

    /**
     * Whether query logging is enabled.
     *
     * @var bool
     */
    protected static $loggingQueries = false;

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
     * Disconnect from the current database.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->pdo = null;
        $this->pool = null;
    }

    /**
     * Reconnect to a database with a specific configuration.
     *
     * @param  array  $config
     * @return void
     */
    public function reconnect(array $config)
    {
        $this->disconnect();

        if (isset($config['pool']) && $config['pool']['enabled']) {
            $this->pool = new ConnectionPool(
                $config, 
                $config['pool']['max_connections'] ?? 5
            );
        } else {
            $this->connect($config);
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
        $start = microtime(true);
        
        try {
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            
            if (static::$loggingQueries) {
                $this->logQuery($sql, $params, $start);
            }
            
            return $statement;
        } finally {
            if ($this->pool && !$this->activeConnection) {
                $this->pool->releaseConnection($pdo);
            }
        }
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
        $start = microtime(true);

        try {
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            
            if (static::$loggingQueries) {
                $this->logQuery($sql, $params, $start);
            }
            
            return $statement->fetchAll();
        } finally {
            if ($this->pool && !$this->activeConnection) {
                $this->pool->releaseConnection($pdo);
            }
        }
    }

    /**
     * Log a query in the query log.
     *
     * @param  string  $sql
     * @param  array   $params
     * @param  float   $start
     * @return void
     */
    protected function logQuery($sql, $params, $start)
    {
        static::$queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => round((microtime(true) - $start) * 1000, 2), // ms
        ];
    }

    /**
     * Enable the query log.
     *
     * @return void
     */
    public static function enableQueryLog()
    {
        static::$loggingQueries = true;
    }

    /**
     * Disable the query log.
     *
     * @return void
     */
    public static function disableQueryLog()
    {
        static::$loggingQueries = false;
    }

    /**
     * Get the query log.
     *
     * @return array
     */
    public static function getQueryLog()
    {
        return static::$queryLog;
    }

    /**
     * Flush the query log.
     *
     * @return void
     */
    public static function flushQueryLog()
    {
        static::$queryLog = [];
    }
    
    /**
     * Get the PDO instance.
     * 
     * @return PDO
     */
    public function getPdo()
    {
        if ($this->activeConnection) {
            return $this->activeConnection;
        }

        if ($this->pool) {
            return $this->pool->getConnection();
        }

        return $this->pdo;
    }

    /**
     * Run a callback within a database transaction.
     *
     * @param  callable  $callback
     * @return mixed
     * @throws \Throwable
     */
    public function transaction(callable $callback)
    {
        $pdo = $this->getPdo();
        $this->activeConnection = $pdo;

        $pdo->beginTransaction();

        try {
            $result = $callback($this);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        } finally {
            $this->activeConnection = null;
            if ($this->pool) {
                $this->pool->releaseConnection($pdo);
            }
        }
    }
}
