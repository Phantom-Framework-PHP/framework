<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function test_required_rule()
    {
        $v = new Validator(['name' => ''], ['name' => 'required']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['name' => 'John'], ['name' => 'required']);
        $this->assertTrue($v->validate());
    }

    public function test_email_rule()
    {
        $v = new Validator(['email' => 'invalid-email'], ['email' => 'email']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['email' => 'test@example.com'], ['email' => 'email']);
        $this->assertTrue($v->validate());
    }

    public function test_min_rule()
    {
        $v = new Validator(['age' => 15], ['age' => 'min:18']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['username' => 'abc'], ['username' => 'min:5']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['username' => 'abcdef'], ['username' => 'min:5']);
        $this->assertTrue($v->validate());
    }

    public function test_max_rule()
    {
        $v = new Validator(['age' => 20], ['age' => 'max:18']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['username' => 'abcdef'], ['username' => 'max:5']);
        $this->assertFalse($v->validate());
    }

    public function test_numeric_rule()
    {
        $v = new Validator(['count' => 'abc'], ['count' => 'numeric']);
        $this->assertFalse($v->validate());
        
        $v = new Validator(['count' => '123'], ['count' => 'numeric']);
        $this->assertTrue($v->validate());
    }
}
