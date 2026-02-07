<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\AI\AIInterface;

class AIEmbeddingTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_ai_can_generate_embeddings()
    {
        $aiMock = $this->createMock(AIInterface::class);
        $aiMock->method('embed')->willReturn([0.1, 0.2, 0.3]);

        app()->instance('ai', $aiMock);

        $embedding = ai()->embed('Hello World');
        
        $this->assertIsArray($embedding);
        $this->assertCount(3, $embedding);
        $this->assertEquals(0.1, $embedding[0]);
    }
}
