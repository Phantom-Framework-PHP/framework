<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\Security\Gate;

class AuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_gate_allows_and_denies()
    {
        gate()->define('update-post', function ($user, $post) {
            return $user && $user->id === $post->user_id;
        });

        $user = (object) ['id' => 1];
        $post = (object) ['user_id' => 1];
        $wrongPost = (object) ['user_id' => 2];

        // Simular login
        app('session')->put('auth_user_id', 1);
        
        // Mock User model behavior for auth()
        // (En un entorno real, auth()->user() devolvería el modelo del usuario)
        
        $this->assertTrue(gate()->allows('update-post', $post));
        $this->assertFalse(gate()->allows('update-post', $wrongPost));
    }
}
