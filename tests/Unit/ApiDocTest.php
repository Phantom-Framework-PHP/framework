<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Console\Commands\ApiDocCommand;
use Phantom\AI\AIInterface;

class ApiDocTest extends TestCase
{
    protected $app;
    protected $swaggerFile;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $this->swaggerFile = base_path('public/swagger.json');
        if (file_exists($this->swaggerFile)) unlink($this->swaggerFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->swaggerFile)) unlink($this->swaggerFile);
    }

    public function test_api_doc_command_generates_swagger_json()
    {
        // Mock AI
        $mockAi = $this->createMock(AIInterface::class);
        $mockAi->method('generate')->willReturn(json_encode([
            'summary' => 'Test Endpoint',
            'responses' => ['200' => ['description' => 'OK']]
        ]));

        $this->app->singleton('ai', function() use ($mockAi) {
            return $mockAi;
        });

        // Add a dummy route
        $this->app->make('router')->get('/api/test', function() { return 'ok'; });

        $command = new ApiDocCommand();
        
        ob_start();
        $command->handle();
        $output = ob_get_clean();

        $this->assertFileExists($this->swaggerFile);
        $json = json_decode(file_get_contents($this->swaggerFile), true);
        
        $this->assertEquals('3.0.0', $json['openapi']);
        $this->assertArrayHasKey('/api/test', $json['paths']);
        $this->assertEquals('Test Endpoint', $json['paths']['/api/test']['get']['summary']);
    }
}
