<?php

namespace Tests\Unit;

use Tests\FeatureTestCase;
use Phantom\Core\Container;

class CacheTenantTest extends FeatureTestCase
{
    public function test_cache_isolates_data_by_tenant()
    {
        $cache = Container::getInstance()->make('cache');
        $tenantManager = Container::getInstance()->make('tenant');

        // Tenant 1
        $tenantManager->setTenant('tenant_1');
        $cache->put('key', 'value_1', 60);
        $this->assertEquals('value_1', $cache->get('key'));

        // Tenant 2
        $tenantManager->setTenant('tenant_2');
        // Should not see tenant 1 data
        $this->assertNull($cache->get('key'));
        
        $cache->put('key', 'value_2', 60);
        $this->assertEquals('value_2', $cache->get('key'));

        // Switch back to Tenant 1
        $tenantManager->setTenant('tenant_1');
        $this->assertEquals('value_1', $cache->get('key'));
    }
}
