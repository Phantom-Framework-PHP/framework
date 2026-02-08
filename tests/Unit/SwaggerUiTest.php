<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Http\Request;
use Phantom\Http\Controllers\ApiDocController;

class SwaggerUiTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
    }

    public function test_docs_route_returns_404_if_json_missing()
    {
        $swaggerFile = base_path('public/swagger.json');
        if (file_exists($swaggerFile)) unlink($swaggerFile);

        $controller = new ApiDocController();
        $response = $controller->index();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString("Documentation not found", $response->getContent());
    }

    public function test_docs_route_returns_view_if_json_exists()
    {
        $swaggerFile = base_path('public/swagger.json');
        file_put_contents($swaggerFile, '{}');

        $controller = new ApiDocController();
        $response = $controller->index();

        // Since it returns a View (string via buffer in this framework's controller index often)
        // Let's assert the response is a Response object with 200
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("SwaggerUIBundle", $response->getContent());

        unlink($swaggerFile);
    }
}
