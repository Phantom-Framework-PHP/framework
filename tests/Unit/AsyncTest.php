<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Async;
use Fiber;

class AsyncTest extends TestCase
{
    public function test_async_run_executes_callback()
    {
        $result = Async::run(function() {
            return 'hello';
        });

        $this->assertEquals('hello', $result);
    }

    public function test_async_suspend_and_resume()
    {
        $fiber = new Fiber(function() {
            $value = Async::suspend('waiting');
            return 'received: ' . $value;
        });

        $status = $fiber->start();
        $this->assertEquals('waiting', $status);
        $this->assertTrue($fiber->isSuspended());

        $fiber->resume('data');
        $this->assertEquals('received: data', $fiber->getReturn());
        $this->assertTrue($fiber->isTerminated());
    }

    public function test_async_helper()
    {
        $result = async(fn() => 1 + 1);
        $this->assertEquals(2, $result);
    }
}
