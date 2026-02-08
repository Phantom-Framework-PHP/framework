<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Validation\Validator;
use Phantom\Security\Shield;
use Phantom\AI\AIInterface;

class AiShieldTest extends TestCase
{
    protected $app;
    protected $shieldFile;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $this->shieldFile = storage_path('framework/shield.json');
        
        if (file_exists($this->shieldFile)) {
            unlink($this->shieldFile);
        }

        // Mock AI driver to always return "yes" (meaning it IS spam/harmful)
        $mockAi = $this->createMock(AIInterface::class);
        $mockAi->method('generate')->willReturn('yes');
        $this->app->singleton('ai', function() use ($mockAi) {
            return $mockAi;
        });

        // Ensure events are initialized
        $this->app->boot();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->shieldFile)) {
            unlink($this->shieldFile);
        }
    }

    public function test_ai_validation_failure_triggers_shield_penalty()
    {
        $ip = '9.9.9.9';
        $_SERVER['REMOTE_ADDR'] = $ip;

        $shield = new Shield();
        $this->assertEquals(0, $shield->getRiskScore($ip));

        // Create a validator with AI rule that will fail
        $validator = new Validator(
            ['comment' => 'This is a malicious comment'],
            ['comment' => 'ai:moderation']
        );

        $this->assertFalse($validator->validate());

        // Shield should have recorded 50 points because of the ai validation failure
        $this->assertEquals(50, $shield->getRiskScore($ip));
    }
}
