<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\View\View;
use Phantom\Core\Application;
use Phantom\Core\Container;

class ViewStackTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_push_and_stack_work()
    {
        $viewDir = dirname(__DIR__, 2) . '/resources/views';
        if (!file_exists($viewDir . '/layouts')) mkdir($viewDir . '/layouts', 0755, true);
        
        // Layout
        file_put_contents($viewDir . '/layouts/test_stack.php', "Scripts: @stack('js')");
        // Vista
        file_put_contents($viewDir . '/child_stack.php', "@extends('layouts.test_stack') @push('js')<script src=\"test.js\"></script>@endpush");
        
        $output = View::make('child_stack')->render();
        
        $this->assertStringContainsString('Scripts: <script src="test.js"></script>', $output);
        
        unlink($viewDir . '/child_stack.php');
        unlink($viewDir . '/layouts/test_stack.php');
    }
}