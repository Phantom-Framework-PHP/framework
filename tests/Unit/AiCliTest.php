<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\AI\AIInterface;
use Phantom\Console\Commands\AiGenerateCommand;

class AiCliTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_ai_generate_command_creates_file()
    {
        // Mock AI Driver
        $mockAi = $this->createMock(AIInterface::class);
        
        $jsonResponse = json_encode([
            'action' => 'create_file',
            'path' => 'app/Models/TestModel.php',
            'content' => '<?php namespace Phantom\Models; class TestModel {}',
            'explanation' => 'Created a test model'
        ]);

        $mockAi->method('chat')->willReturn($jsonResponse);

        // Bind mock to container
        $this->app->singleton('ai', function() use ($mockAi) {
            return $mockAi;
        });

        // Instantiate command
        $command = new AiGenerateCommand();
        
        // Capture output
        ob_start();
        $command->handle(['create', 'a', 'model', 'TestModel']);
        $output = ob_get_clean();

        $this->assertStringContainsString('✅ Created: app/Models/TestModel.php', $output);
        $this->assertFileExists(dirname(__DIR__, 2) . '/app/Models/TestModel.php');

        // Cleanup
        unlink(dirname(__DIR__, 2) . '/app/Models/TestModel.php');
    }
}
