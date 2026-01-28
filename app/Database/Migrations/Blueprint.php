<?php

namespace Phantom\Database\Migrations;

class Blueprint
{
    protected $table;
    protected $columns = [];

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function id()
    {
        $this->columns[] = "id INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string($name, $length = 255)
    {
        $this->columns[] = "{$name} VARCHAR({$length}) NOT NULL";
        return $this;
    }

    public function text($name)
    {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    public function timestamps()
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function toSql()
    {
        $columnsSql = implode(', ', $this->columns);
        return "CREATE TABLE {$this->table} ({$columnsSql}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
}
