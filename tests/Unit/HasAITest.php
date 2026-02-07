<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Traits\HasAI;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\AI\AIManager;
use Phantom\AI\AIInterface;

class TestModelAI {
    use HasAI;
    public $content = 'This is a long text about nothing important.';
    public $title = 'Hello World';
    public function toArray() { return ['title' => $this->title, 'content' => $this->content]; }
}

class HasAITest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_summarize_method()
    {
        $aiMock = $this->createMock(AIInterface::class);
        $aiMock->expects($this->once())
               ->method('generate')
               ->with($this->stringContains('Summarize'))
               ->willReturn('Short version.');

        app()->instance('ai', $aiMock);

        $model = new TestModelAI();
        $result = $model->summarize('content');

        $this->assertEquals('Short version.', $result);
    }

    public function test_translate_attribute_method()
    {
        $aiMock = $this->createMock(AIInterface::class);
        $aiMock->expects($this->once())
               ->method('generate')
               ->with($this->stringContains('Translate'))
               ->willReturn('Hola Mundo');

        app()->instance('ai', $aiMock);

        $model = new TestModelAI();
        $result = $model->translateAttribute('title', 'es');

        $this->assertEquals('Hola Mundo', $result);
    }
}
