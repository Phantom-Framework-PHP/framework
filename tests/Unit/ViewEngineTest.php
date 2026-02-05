<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\View\Compiler;
use Phantom\View\View;
use Phantom\Core\Application;
use Phantom\Core\Container;

class ViewEngineTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setInstance(null);
        new Application(dirname(__DIR__, 2));
    }

    public function test_compiler_echos()
    {
        $compiler = new Compiler();
        
        $this->assertEquals('<?php echo htmlspecialchars($name); ?>', $compiler->compile('{{ $name }}'));
        $this->assertEquals('<?php echo $raw; ?>', $compiler->compile('{!! $raw !!}'));
    }

    public function test_compiler_directives()
    {
        $compiler = new Compiler();
        
        $this->assertStringContainsString('<?php if($test): ?>', $compiler->compile('@if($test)'));
        $this->assertStringContainsString('<?php foreach($items as $item): ?>', $compiler->compile('@foreach($items as $item)'));
    }

    public function test_view_rendering_with_data()
    {
        // Creamos una vista temporal
        $viewDir = dirname(__DIR__, 2) . '/resources/views';
        if (!file_exists($viewDir)) mkdir($viewDir, 0755, true);
        
        file_put_contents($viewDir . '/test_engine.php', 'Hello {{ $name }}');
        
        $output = View::make('test_engine', ['name' => 'Phantom'])->render();
        
        $this->assertEquals('Hello Phantom', $output);
        
        unlink($viewDir . '/test_engine.php');
    }
}
