<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

class PersistenceUser extends Model {
    protected $table = 'users';
    protected $fillable = ['email', 'name', 'votes'];
}

class ModelPersistenceTest extends TestCase
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
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, name TEXT, votes INTEGER DEFAULT 0, created_at DATETIME, updated_at DATETIME)");
    }

    public function test_first_or_new_finds_existing_record()
    {
        PersistenceUser::create(['email' => 'test@example.com', 'name' => 'Test']);

        $user = PersistenceUser::firstOrNew(['email' => 'test@example.com']);

        $this->assertTrue($user->exists);
        $this->assertEquals('Test', $user->name);
    }

    public function test_first_or_new_instantiates_new_record()
    {
        $user = PersistenceUser::firstOrNew(['email' => 'new@example.com'], ['name' => 'New']);

        $this->assertFalse($user->exists);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('New', $user->name);
    }

    public function test_first_or_create_creates_new_record()
    {
        $user = PersistenceUser::firstOrCreate(['email' => 'create@example.com'], ['name' => 'Created']);

        $this->assertTrue($user->exists);
        $this->assertEquals('Created', $user->name);
        $this->assertNotNull($user->id);
    }

    public function test_update_or_create_updates_existing_record()
    {
        PersistenceUser::create(['email' => 'update@example.com', 'name' => 'Old Name']);

        $user = PersistenceUser::updateOrCreate(
            ['email' => 'update@example.com'],
            ['name' => 'New Name']
        );

        $this->assertEquals('New Name', $user->name);
        $this->assertCount(1, PersistenceUser::all());
    }

    public function test_update_or_create_creates_new_record()
    {
        $user = PersistenceUser::updateOrCreate(
            ['email' => 'up-create@example.com'],
            ['name' => 'UpCreated', 'votes' => 10]
        );

        $this->assertTrue($user->exists);
        $this->assertEquals(10, $user->votes);
    }
}
