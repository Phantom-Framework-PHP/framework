<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Models\Model;

class CastUser extends Model {
    protected $table = 'users';
    protected $casts = [
        'is_admin' => 'boolean',
        'age' => 'integer',
        'meta' => 'json'
    ];
}

class AttributeCastingTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_boolean_casting()
    {
        $user = new CastUser(['is_admin' => '1']);
        $this->assertTrue($user->is_admin);
        $this->assertIsBool($user->is_admin);

        $user->is_admin = 0;
        $this->assertFalse($user->is_admin);
    }

    public function test_integer_casting()
    {
        $user = new CastUser(['age' => '25']);
        $this->assertEquals(25, $user->age);
        $this->assertIsInt($user->age);
    }

    public function test_json_casting_serialization()
    {
        $user = new CastUser();
        $user->meta = ['theme' => 'dark', 'notifications' => true];
        
        $attributes = $user->getAttributes();
        $this->assertIsString($attributes['meta']);
        $this->assertEquals('{"theme":"dark","notifications":true}', $attributes['meta']);
    }

    public function test_json_casting_deserialization()
    {
        $user = new CastUser(['meta' => '{"key":"value"}']);
        $this->assertIsArray($user->meta);
        $this->assertEquals('value', $user->meta['key']);
    }
}