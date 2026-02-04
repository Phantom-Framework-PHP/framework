<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Config;

class ConfigTest extends TestCase
{
    public function test_can_set_and_get_config()
    {
        $config = new Config();
        $config->set('app.name', 'Phantom');
        
        $this->assertEquals('Phantom', $config->get('app.name'));
    }

    public function test_can_get_nested_config()
    {
        $config = new Config();
        $config->set('database.connections.mysql.host', 'localhost');
        
        $this->assertEquals('localhost', $config->get('database.connections.mysql.host'));
    }

    public function test_returns_default_value_if_not_found()
    {
        $config = new Config();
        
        $this->assertEquals('default', $config->get('non.existent', 'default'));
    }

    public function test_can_get_all_config()
    {
        $config = new Config();
        $config->set('a', 1);
        $config->set('b', 2);
        
        $this->assertEquals(['a' => 1, 'b' => 2], $config->all());
    }
}
