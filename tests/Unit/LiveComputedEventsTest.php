<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;

class LiveComputedEventsTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_computed_properties_work()
    {
        $component = new class extends \Phantom\Live\Component {
            public $first_name = 'John';
            public $last_name = 'Doe';
            public function getFullNameProperty() { return "{$this->first_name} {$this->last_name}"; }
            public function render() { return ""; }
        };

        $this->assertEquals('John Doe', $component->full_name);
    }

    public function test_event_emission_works()
    {
        $component = new class extends \Phantom\Live\Component {
            public function fire() { $this->emit('postCreated', 1); }
            public function render() { return ""; }
        };

        $component->fire();
        $events = $component->getEmittedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('postCreated', $events[0]['event']);
        $this->assertEquals([1], $events[0]['params']);
    }
}
