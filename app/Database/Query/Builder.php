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

    public function __construct(Database $db)
    {
        $this->db = $db;
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
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;

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
        $sql = $this->toSql();
        return $this->db->select($sql, $this->bindings);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count()
    {
        $this->columns = ["COUNT(*) as aggregate"];
        $sql = $this->toSql();
        $result = $this->db->select($sql, $this->bindings);
        return (int) ($result[0]->aggregate ?? 0);
    }

    public function paginate($perPage = 15)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $total = $this->count();
        
        $this->columns = ['*']; // Reset columns after count
        $this->limit($perPage);
        $this->bindings = array_merge($this->bindings, []); // Keep bindings intact
        
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
            $parts[] = "{$where['column']} {$where['operator']} ?";
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
