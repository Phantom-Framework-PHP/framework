<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;

class SerializableUser extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
    protected $appends = ['full_name'];

    public function getFullNameAttribute() {
        return "User: " . ($this->attributes['name'] ?? '');
    }
}

class VisibleUser extends Model {
    protected $table = 'users';
    protected $visible = ['name'];
}

class ModelSerializationTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_hidden_attributes_are_removed_from_json()
    {
        $user = new SerializableUser([
            'name' => 'Mario',
            'email' => 'mario@example.com',
            'password' => 'secret123'
        ]);

        $json = json_decode(json_encode($user), true);

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('email', $json);
        $this->assertArrayNotHasKey('password', $json);
    }

    public function test_visible_attributes_restrict_output()
    {
        $user = new VisibleUser([
            'name' => 'Luigi',
            'email' => 'luigi@example.com'
        ]);

        $json = json_decode(json_encode($user), true);

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayNotHasKey('email', $json);
    }

    public function test_appends_attributes_are_added_to_json()
    {
        $user = new SerializableUser([
            'name' => 'Mario'
        ]);

        $json = json_decode(json_encode($user), true);

        $this->assertArrayHasKey('full_name', $json);
        $this->assertEquals('User: Mario', $json['full_name']);
    }
}
