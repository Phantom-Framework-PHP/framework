<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\View\Compiler;

class BladeComponentsTest extends TestCase
{
    public function test_compiler_handles_x_tag_syntax()
    {
        $compiler = new Compiler();
        $template = '<x-alert type="danger" />' ;
        $compiled = $compiler->compile($template);

        $this->assertStringContainsString("View::make('components.alert'", $compiled);
        $this->assertStringContainsString("'type' => 'danger'", $compiled);
    }

    public function test_compiler_handles_x_tag_with_slot_content()
    {
        $compiler = new Compiler();
        $template = '<x-card>Click me</x-card>';
        $compiled = $compiler->compile($template);

        $this->assertStringContainsString("\$slot = ob_get_clean()", $compiled);
        $this->assertStringContainsString("'slot' => \$slot", $compiled);
        $this->assertStringContainsString("Click me", $compiled);
    }

    public function test_slots_syntax_compilation()
    {
        $compiler = new Compiler();
        $template = "@slot('header') Title @endslot";
        $compiled = $compiler->compile($template);

        $this->assertStringContainsString("\$this->startSection('header')", $compiled);
        $this->assertStringContainsString("\$this->endSection()", $compiled);
    }
}
