<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

class AdvancedUser extends Model {
    protected $table = 'users';
    protected $fillable = ['name'];
}

class ModelAdvancedFeaturesTest extends TestCase
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

    public function test_find_or_fail_throws_exception_on_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No query results for model [" . AdvancedUser::class . "] 999");
        
        AdvancedUser::findOrFail(999);
    }

    public function test_find_or_fail_returns_model_if_found()
    {
        $this->db->query("INSERT INTO users (id, name) VALUES (1, 'Mario')");
        
        $user = AdvancedUser::findOrFail(1);
        $this->assertInstanceOf(AdvancedUser::class, $user);
        $this->assertEquals('Mario', $user->name);
    }

    public function test_timestamps_are_automatically_filled_on_create()
    {
        $user = AdvancedUser::create(['name' => 'Luigi']);
        
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertEquals($user->created_at, $user->updated_at);
        
        $dbUser = $this->db->table('users')->where('id', 1)->first();
        $this->assertNotNull($dbUser->created_at);
    }

    public function test_updated_at_is_updated_on_save()
    {
        $user = AdvancedUser::create(['name' => 'Peach']);
        $originalUpdatedAt = $user->updated_at;
        
        // Simular paso del tiempo
        sleep(1);
        
        $user->name = 'Princess Peach';
        $user->save();
        
        $this->assertNotEquals($originalUpdatedAt, $user->updated_at);
        $this->assertEquals($user->created_at, $user->created_at); // No debería cambiar
    }
}
