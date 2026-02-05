<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;
use Phantom\Database\Database;

class FeatureUser extends Model {
    protected $table = 'users';

    // Accessor: full_name
    public function getFullNameAttribute() {
        return ucfirst($this->attributes['first_name']) . ' ' . ucfirst($this->attributes['last_name']);
    }

    // Mutator: password
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = 'hashed_' . $value;
    }

    // Scope: active
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }
}

class ModelFeaturesTest extends TestCase
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
        $this->db->query("CREATE TABLE users (id INTEGER PRIMARY KEY, first_name TEXT, last_name TEXT, password TEXT, status TEXT)");
    }

    public function test_accessors_work()
    {
        $user = new FeatureUser(['first_name' => 'mario', 'last_name' => 'bros']);
        $this->assertEquals('Mario Bros', $user->full_name);
    }

    public function test_mutators_work()
    {
        $user = new FeatureUser();
        $user->password = 'secret';
        $this->assertEquals('hashed_secret', $user->password);
    }

    public function test_scopes_work()
    {
        $this->db->query("INSERT INTO users (first_name, status) VALUES ('Active User', 'active')");
        $this->db->query("INSERT INTO users (first_name, status) VALUES ('Inactive User', 'inactive')");

        $activeUsers = FeatureUser::active()->get();

        $this->assertCount(1, $activeUsers);
        $this->assertEquals('Active User', $activeUsers->first()->first_name);
    }
}
