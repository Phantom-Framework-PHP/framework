<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Session\Session;

class SessionTest extends TestCase
{
    protected $session;

    protected function setUp(): void
    {
        $this->session = new Session();
        $_SESSION = [];
    }

    public function test_can_put_and_get_session_data()
    {
        $this->session->put('user_id', 123);
        $this->assertEquals(123, $this->session->get('user_id'));
        $this->assertEquals(123, $_SESSION['user_id']);
    }

    public function test_can_forget_session_data()
    {
        $_SESSION['key'] = 'value';
        $this->session->forget('key');
        $this->assertNull($this->session->get('key'));
    }

    public function test_flush_clears_all_data()
    {
        $_SESSION['a'] = 1;
        $_SESSION['b'] = 2;
        
        $this->session->flush();
        
        $this->assertEmpty($_SESSION);
    }
}
