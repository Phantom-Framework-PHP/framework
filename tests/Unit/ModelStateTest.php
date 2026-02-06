<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

class StateUser extends Model {
    protected $table = 'users';
    protected $fillable = ['name'];
}

class ModelStateTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
        
        $this->db = new Database([
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:'
                ]
            ]
        ]);
        
        Container::getInstance()->instance('db', $this->db);
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, created_at DATETIME, updated_at DATETIME)");
    }

    public function test_was_recently_created_is_set_correctly()
    {
        $user = new StateUser(['name' => 'New User']);
        $this->assertFalse($user->wasRecentlyCreated);

        $user->save();
        $this->assertTrue($user->wasRecentlyCreated);

        $user->name = 'Updated User';
        $user->save();
        $this->assertTrue($user->wasRecentlyCreated); // Sigue siendo true en el ciclo actual
    }

    public function test_fresh_returns_new_instance_from_db()
    {
        $user = StateUser::create(['name' => 'Original']);
        
        // Modificar directamente en la DB para testear fresh
        $this->db->query("UPDATE users SET name = 'Modified' WHERE id = 1");
        
        $freshUser = $user->fresh();
        
        $this->assertNotSame($user, $freshUser);
        $this->assertEquals('Original', $user->name);
        $this->assertEquals('Modified', $freshUser->name);
    }

    public function test_refresh_updates_current_instance()
    {
        $user = StateUser::create(['name' => 'Original']);
        
        // Modificar directamente en la DB
        $this->db->query("UPDATE users SET name = 'Modified' WHERE id = 1");
        
        $user->refresh();
        
        $this->assertEquals('Modified', $user->name);
    }
}
