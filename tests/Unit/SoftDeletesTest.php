<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Traits\SoftDeletes;
use Phantom\Database\Database;

class SoftUser extends Model {
    use SoftDeletes;
    protected $table = 'users';
}

class SoftDeletesTest extends TestCase
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
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT DEFAULT NULL)");
    }

    public function test_model_can_be_soft_deleted()
    {
        $user = new SoftUser(['id' => 1, 'name' => 'Mario']);
        $user->save();
        
        $this->assertCount(1, SoftUser::all());
        
        $user->delete();
        
        // Should not appear in normal queries
        $this->assertCount(0, SoftUser::all());
        $this->assertNotNull($user->deleted_at);
        $this->assertTrue($user->trashed());
    }

    public function test_can_retrieve_soft_deleted_models()
    {
        $user = new SoftUser(['id' => 1, 'name' => 'Mario']);
        $user->save();
        $user->delete();
        
        $all = SoftUser::withTrashed()->get();
        $this->assertCount(1, $all);
        
        $only = SoftUser::onlyTrashed()->get();
        $this->assertCount(1, $only);
    }

    public function test_can_restore_soft_deleted_models()
    {
        $user = new SoftUser(['id' => 1, 'name' => 'Mario']);
        $user->save();
        $user->delete();
        
        $user->restore();
        
        $this->assertCount(1, SoftUser::all());
        $this->assertNull($user->deleted_at);
        $this->assertFalse($user->trashed());
    }
}
