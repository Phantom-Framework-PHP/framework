<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Validation\Validator;
use Phantom\Core\Application;
use Phantom\Core\Container;
use Phantom\AI\AIInterface;

class AIValidationTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_ai_spam_validation_rule()
    {
        $aiMock = $this->createMock(AIInterface::class);
        $aiMock->method('generate')->willReturn('no'); // Not spam

        app()->instance('ai', $aiMock);

        $validator = new Validator(['comment' => 'This is fine'], ['comment' => 'ai:spam']);
        $this->assertTrue($validator->validate());
    }

    public function test_ai_moderation_validation_rule_fails()
    {
        $aiMock = $this->createMock(AIInterface::class);
        $aiMock->method('generate')->willReturn('yes'); // Is inappropriate

        app()->instance('ai', $aiMock);

        $validator = new Validator(['comment' => 'Bad words'], ['comment' => 'ai:moderation']);
        $this->assertFalse($validator->validate());
    }
}
