<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Controllers\LiveController;

class LiveUltimateTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_component_redirect_instruction_is_returned()
    {
        $controller = new LiveController();
        
        // Mock a component that redirects
        $componentClass = 'Phantom\Live\Components\RedirectComponent';
        if (!class_exists($componentClass)) {
            eval("namespace Phantom\Live\Components; class RedirectComponent extends \Phantom\Live\Component { 
                public function doRedirect() { \$this->redirect('/success'); }
                public function render() { return ''; }
            }");
        }

        $request = new Request([], [], [
            'component' => $componentClass,
            'id' => 'test-id',
            'state' => base64_encode(json_encode([])),
            'action' => 'doRedirect'
        ]);

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('/success', $data['redirect']);
    }
}
