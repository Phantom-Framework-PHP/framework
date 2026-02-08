<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Application;
use Phantom\Services\PulseService;

class PulseSqliteTest extends TestCase
{
    protected $app;
    protected $sqliteFile;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__, 2));
        $this->sqliteFile = storage_path('framework/pulse.sqlite');
        
        if (file_exists($this->sqliteFile)) {
            unlink($this->sqliteFile);
        }

        // Set driver to sqlite for test
        config(['app.pulse_driver' => 'sqlite']);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->sqliteFile)) {
            unlink($this->sqliteFile);
        }
    }

    public function test_pulse_sqlite_driver_stores_and_retrieves_data()
    {
        $service = new PulseService();
        
        $data = [
            'url' => '/test-sqlite',
            'method' => 'POST',
            'duration' => 150.5,
            'memory' => 2.4,
            'queries_count' => 1,
            'queries' => [['sql' => 'SELECT 1', 'time' => 0.5]],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $service->record($data);

        $this->assertFileExists($this->sqliteFile);
        
        $entries = $service->getEntries();
        $this->assertCount(1, $entries);
        $this->assertEquals('/test-sqlite', $entries[0]['url']);
        $this->assertEquals(150.5, $entries[0]['duration']);
        $this->assertEquals('SELECT 1', $entries[0]['queries'][0]['sql']);
    }

    public function test_pulse_sqlite_limit_works()
    {
        $service = new PulseService();
        
        // Record 110 entries
        for ($i = 0; $i < 110; $i++) {
            $service->record([
                'url' => "/test-{$i}",
                'method' => 'GET',
                'duration' => 1,
                'memory' => 1,
                'queries_count' => 0,
                'queries' => [],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $entries = $service->getEntries();
        // Limit is 100
        $this->assertCount(100, $entries);
        // The first one should be the last recorded (test-109)
        $this->assertEquals('/test-109', $entries[0]['url']);
    }
}
