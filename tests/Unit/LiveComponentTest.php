<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Controllers\LiveController;
use Phantom\Live\Components\Counter;

class LiveComponentTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_component_renders_initial_state()
    {
        $counter = new Counter();
        $counter->id = 'test-id';
        $counter->count = 5;
        
        $output = $counter->output();
        
        $this->assertStringContainsString('Count: 5', $output);
        $this->assertStringContainsString('data-live-component="Phantom\Live\Components\Counter"', $output);
        $this->assertStringContainsString('data-live-state', $output);
    }

    public function test_live_controller_updates_state()
    {
        $controller = new LiveController();
        
        $initialState = base64_encode(json_encode(['count' => 10]));
        
        $request = new Request([], [], [
            'component' => 'Phantom\Live\Components\Counter',
            'id' => 'test-id',
            'state' => $initialState,
            'action' => 'increment'
        ]);

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertStringContainsString('Count: 11', $data['html']);
        
        $newState = json_decode(base64_decode($data['state']), true);
        $this->assertEquals(11, $newState['count']);
    }
}
