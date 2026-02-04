<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Database\Query\Builder;
use Phantom\Database\Database;

class QueryBuilderTest extends TestCase
{
    protected $db;
    protected $builder;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->builder = new Builder($this->db);
    }

    public function test_basic_select_sql()
    {
        $this->builder->table('users')->select('id', 'name');
        
        $this->assertEquals('SELECT id, name FROM users', $this->builder->toSql());
    }

    public function test_where_clauses()
    {
        $this->builder->table('users')
            ->where('id', 1)
            ->where('status', 'active');
        
        $this->assertEquals('SELECT * FROM users WHERE id = ? AND status = ?', $this->builder->toSql());
    }

    public function test_order_and_limit()
    {
        $this->builder->table('users')
            ->orderBy('created_at', 'desc')
            ->limit(10);
        
        $this->assertEquals('SELECT * FROM users ORDER BY created_at desc LIMIT 10', $this->builder->toSql());
    }

    public function test_update_sql_generation()
    {
        $this->db->expects($this->once())
                 ->method('query')
                 ->with($this->equalTo('UPDATE users SET name = ?, email = ? WHERE id = ?'), $this->equalTo(['John', 'john@example.com', 1]));

        $this->builder->table('users')
            ->where('id', 1)
            ->update(['name' => 'John', 'email' => 'john@example.com']);
    }
}
