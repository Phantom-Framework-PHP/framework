<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Controllers\LiveController;

class LiveProMaxTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_component_can_receive_action_parameters()
    {
        $controller = new LiveController();
        
        $componentClass = 'Phantom\Live\Components\ParamComponent';
        if (!class_exists($componentClass)) {
            eval("namespace Phantom\Live\Components; class ParamComponent extends \Phantom\Live\Component { 
                public \$receivedValue;
                public function updateValue(\$val) { \$this->receivedValue = \$val; }
                public function render() { return ''; }
            }");
        }

        $request = new Request([], [], [
            'component' => $componentClass,
            'id' => 'test-id',
            'state' => base64_encode(json_encode(['receivedValue' => null])),
            'action' => 'updateValue',
            'params' => json_encode([42])
        ]);

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);
        $state = json_decode(base64_decode($data['state']), true);

        $this->assertEquals(42, $state['receivedValue']);
    }
}
