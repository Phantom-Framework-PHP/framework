<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Live\Components\Search;

class LiveAdvancedTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_component_mount_initializes_data()
    {
        // We'll create a mock component to test mount
        $component = new class extends \Phantom\Live\Component {
            public $title;
            public function mount() { $this->title = 'Initialized'; }
            public function render() { return "<h1>{$this->title}</h1>"; }
        };

        $component->mount();
        $this->assertEquals('Initialized', $component->title);
    }

    public function test_component_validation_works()
    {
        $component = new class extends \Phantom\Live\Component {
            public $email = 'invalid-email';
            public function save() { $this->validate(['email' => 'required|email']); }
            public function render() { return ""; }
        };

        try {
            $component->save();
        } catch (\Exception $e) {
            $this->assertEquals('Validation failed in Live Component', $e->getMessage());
        }

        $errors = $component->getErrors();
        $this->assertArrayHasKey('email', $errors);
        $this->assertStringContainsString('failed the email validation', $errors['email'][0]);
    }
}
