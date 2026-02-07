<?php

namespace Phantom\Database\Query;

use Phantom\Database\Database;
use PDO;

class Builder
{
    protected $db;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = [];
    protected $orders = [];
    protected $limit;
    protected $bindings = [];
    protected $useSoftDeletes = false;
    protected $modelClass;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function setModelClass($class)
    {
        $this->modelClass = $class;
        return $this;
    }

    public function __call($method, $args)
    {
        if ($this->modelClass) {
            $scopeMethod = 'scope' . ucfirst($method);
            $instance = new $this->modelClass;
            
            if (method_exists($instance, $scopeMethod)) {
                return $instance->$scopeMethod($this, ...$args) ?: $this;
            }
        }

        throw new \Exception("Method [{$method}] does not exist on the query builder.");
    }

    public function useSoftDeletes($value = true)
    {
        $this->useSoftDeletes = $value;
        return $this;
    }

    public function withTrashed()
    {
        $this->useSoftDeletes = false;
        return $this;
    }

    public function onlyTrashed()
    {
        $this->useSoftDeletes = false;
        return $this->where('deleted_at', 'IS NOT', null);
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, '=', $val);
            }
            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        if ($operator !== 'IS' && $operator !== 'IS NOT') {
            $this->bindings[] = $value;
        }

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    public function get()
    {
        if ($this->useSoftDeletes) {
            $this->where('deleted_at', 'IS', null);
        }

        $sql = $this->toSql();
        $results = $this->db->select($sql, $this->bindings);
        
        $collection = new \Phantom\Core\Collection($results);

        if ($this->modelClass) {
            return $collection->map(function ($item) {
                return (new $this->modelClass)->newInstance((array) $item, true);
            });
        }

        return $collection;
    }

    /**
     * Get the query results as a plain collection without model hydration.
     *
     * @param  bool  $asArray
     * @return \Phantom\Core\Collection
     */
    public function toPlainArray($asArray = false)
    {
        if ($this->useSoftDeletes) {
            $this->where('deleted_at', 'IS', null);
        }

        $sql = $this->toSql();
        $results = $this->db->select($sql, $this->bindings);

        if ($asArray) {
            $results = array_map(function($item) {
                return (array) $item;
            }, $results);
        }

        return new \Phantom\Core\Collection($results);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results->first();
    }

    public function count()
    {
        if ($this->useSoftDeletes) {
            $this->where('deleted_at', 'IS', null);
        }

        $this->columns = ["COUNT(*) as aggregate"];
        $sql = $this->toSql();
        $result = $this->db->select($sql, $this->bindings);
        return (int) ($result[0]->aggregate ?? 0);
    }

    public function paginate($perPage = 15)
    {
        if ($this->useSoftDeletes) {
            $this->where('deleted_at', 'IS', null);
        }

        $page = (int) ($_GET['page'] ?? 1);
        $total = $this->count();
        
        $this->columns = ['*']; // Reset columns after count
        $this->limit($perPage);
        
        // Manual offset calculation for the SQL
        $offset = ($page - 1) * $perPage;
        
        $sql = $this->toSql() . " OFFSET {$offset}";
        $items = $this->db->select($sql, $this->bindings);

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function toSql()
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->compileWheres();
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . $this->compileOrders();
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        return $sql;
    }

    protected function compileWheres()
    {
        $parts = [];
        foreach ($this->wheres as $where) {
            if ($where['operator'] === 'IS' || $where['operator'] === 'IS NOT') {
                $parts[] = "{$where['column']} {$where['operator']} NULL";
            } else {
                $parts[] = "{$where['column']} {$where['operator']} ?";
            }
        }
        return implode(' AND ', $parts);
    }

    protected function compileOrders()
    {
        $parts = [];
        foreach ($this->orders as $order) {
            $parts[] = "{$order['column']} {$order['direction']}";
        }
        return implode(', ', $parts);
    }

    public function insert(array $values)
    {
        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->db->query($sql, array_values($values));
    }

    public function update(array $values)
    {
        $set = [];
        $bindings = [];

        foreach ($values as $column => $value) {
            $set[] = "{$column} = ?";
            $bindings[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->compileWheres();
            $bindings = array_merge($bindings, $this->bindings);
        }

        return $this->db->query($sql, $bindings);
    }

    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->compileWheres();
        }

        return $this->db->query($sql, $this->bindings);
    }
}
