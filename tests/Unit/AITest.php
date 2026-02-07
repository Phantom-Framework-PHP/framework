<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\AI\AIManager;
use Phantom\AI\Drivers\GeminiDriver;
use Phantom\AI\Drivers\OpenAIDriver;
use Phantom\Core\Application;
use Phantom\Core\Container;

class AITest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_ai_manager_can_resolve_gemini_driver()
    {
        config(['ai.drivers.gemini' => [
            'driver' => 'gemini',
            'key' => 'test-key',
            'model' => 'gemini-pro'
        ]]);

        $manager = new AIManager();
        $driver = $manager->driver('gemini');

        $this->assertInstanceOf(GeminiDriver::class, $driver);
    }

    public function test_ai_manager_can_resolve_openai_driver()
    {
        config(['ai.drivers.openai' => [
            'driver' => 'openai',
            'key' => 'test-key',
            'model' => 'gpt-4'
        ]]);

        $manager = new AIManager();
        $driver = $manager->driver('openai');

        $this->assertInstanceOf(OpenAIDriver::class, $driver);
    }
}
