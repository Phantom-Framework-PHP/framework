<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Env;

class EnvTest extends TestCase
{
    public function test_can_get_env_variable()
    {
        $_ENV['TEST_VAR'] = 'test_value';
        $this->assertEquals('test_value', Env::get('TEST_VAR'));
    }

    public function test_env_returns_default()
    {
        $this->assertEquals('default', Env::get('NON_EXISTENT', 'default'));
    }

    public function test_env_boolean_parsing()
    {
        $_ENV['TEST_TRUE'] = 'true';
        $_ENV['TEST_FALSE'] = 'false';
        
        $this->assertTrue(Env::get('TEST_TRUE'));
        $this->assertFalse(Env::get('TEST_FALSE'));
    }

    public function test_env_null_parsing()
    {
        $_ENV['TEST_NULL'] = 'null';
        $this->assertNull(Env::get('TEST_NULL'));
    }

    public function test_env_quotes_removal()
    {
        $_ENV['TEST_QUOTED'] = '"quoted_value"';
        $this->assertEquals('quoted_value', Env::get('TEST_QUOTED'));
    }
}
