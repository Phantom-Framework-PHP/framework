<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

class MassAssignmentUser extends Model {
    protected $table = 'users';
    protected $fillable = ['first_name', 'email'];
}

class MassAssignmentTest extends TestCase
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
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, first_name TEXT, email TEXT, is_admin INTEGER DEFAULT 0)");
    }

    public function test_mass_assignment_protection_works()
    {
        $user = new MassAssignmentUser([
            'first_name' => 'Mario',
            'email' => 'mario@example.com',
            'is_admin' => 1 // Este no debería asignarse
        ]);

        $this->assertEquals('Mario', $user->first_name);
        $this->assertEquals('mario@example.com', $user->email);
        $this->assertNull($user->is_admin);
    }

    public function test_create_method_works()
    {
        $user = MassAssignmentUser::create([
            'first_name' => 'Luigi',
            'email' => 'luigi@example.com'
        ]);

        $this->assertInstanceOf(MassAssignmentUser::class, $user);
        $this->assertEquals('Luigi', $user->first_name);
        $this->assertEquals(1, $user->id); // Primer ID en SQLite memory
        
        $dbUser = $this->db->table('users')->where('id', 1)->first();
        $this->assertEquals('Luigi', $dbUser->first_name);
    }
}
