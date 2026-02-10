<?php

namespace Phantom\Database\Migrations;

class Blueprint
{
    protected $table;
    protected $columns = [];
    protected $currentColumn;

    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Add a new column to the blueprint.
     */
    protected function addColumn($name, $type)
    {
        $this->currentColumn = [
            'name' => $name,
            'type' => $type,
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'auto_increment' => false,
            'unsigned' => false
        ];
        
        $this->columns[] = &$this->currentColumn;
        
        return $this;
    }

    public function id()
    {
        return $this->addColumn('id', 'INT')
            ->unsigned()
            ->primary()
            ->autoIncrement();
    }

    public function string($name, $length = 255)
    {
        return $this->addColumn($name, "VARCHAR({$length})");
    }

    public function text($name)
    {
        return $this->addColumn($name, 'TEXT');
    }

    public function longText($name)
    {
        return $this->addColumn($name, 'LONGTEXT');
    }

    public function integer($name)
    {
        return $this->addColumn($name, 'INT');
    }
    
    public function int($name)
    {
        return $this->integer($name);
    }

    public function unsignedInteger($name)
    {
        return $this->integer($name)->unsigned();
    }

    public function boolean($name)
    {
        return $this->addColumn($name, 'TINYINT(1)');
    }

    public function foreignId($name)
    {
        return $this->unsignedInteger($name);
    }

    public function nullable()
    {
        $this->currentColumn['nullable'] = true;
        return $this;
    }

    public function default($value)
    {
        $this->currentColumn['default'] = $value;
        return $this;
    }

    public function unique()
    {
        $this->currentColumn['unique'] = true;
        return $this;
    }

    public function primary()
    {
        $this->currentColumn['primary'] = true;
        return $this;
    }

    public function unsigned()
    {
        $this->currentColumn['unsigned'] = true;
        return $this;
    }

    public function autoIncrement()
    {
        $this->currentColumn['auto_increment'] = true;
        return $this;
    }

    public function timestamps()
    {
        $this->addColumn('created_at', 'TIMESTAMP')->nullable()->default('CURRENT_TIMESTAMP');
        $this->addColumn('updated_at', 'TIMESTAMP')->nullable()->default('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        return $this;
    }

    protected function wrap($value)
    {
        return "`{$value}`";
    }

    public function toSql()
    {
        $lines = [];
        foreach ($this->columns as $column) {
            $sql = $this->wrap($column['name']) . " " . $column['type'];
            
            if ($column['unsigned']) {
                $sql .= " UNSIGNED";
            }
            
            if (!$column['nullable']) {
                $sql .= " NOT NULL";
            }
            
            if ($column['default'] !== null) {
                $default = $column['default'];
                if ($default === 'CURRENT_TIMESTAMP' || $default === 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP') {
                    $sql .= " DEFAULT {$default}";
                } else {
                    $sql .= " DEFAULT " . (is_string($default) ? "'{$default}'" : ($default === true ? 1 : ($default === false ? 0 : $default)));
                }
            }
            
            if ($column['auto_increment']) {
                $sql .= " AUTO_INCREMENT";
            }
            
            if ($column['primary']) {
                $sql .= " PRIMARY KEY";
            }
            
            if ($column['unique']) {
                $sql .= " UNIQUE";
            }
            
            $lines[] = $sql;
        }

        $columnsSql = implode(', ', $lines);
        return "CREATE TABLE " . $this->wrap($this->table) . " ({$columnsSql}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
}
