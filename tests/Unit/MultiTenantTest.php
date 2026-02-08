<?php

namespace Tests\Unit;

use Tests\FeatureTestCase;
use Phantom\Models\Model;
use Phantom\Traits\BelongsToTenant;
use Phantom\Core\Container;

class TenantModel extends Model
{
    use BelongsToTenant;
    protected $table = 'tenant_models';
    protected $fillable = ['name', 'tenant_id'];
}

class MultiTenantTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $db = Container::getInstance()->make('db');
        $db->query("DROP TABLE IF EXISTS tenant_models");
        $db->query("CREATE TABLE tenant_models (id INTEGER PRIMARY KEY AUTO_INCREMENT, name TEXT, tenant_id TEXT, created_at DATETIME, updated_at DATETIME)");
    }

    public function test_model_automatically_sets_tenant_id()
    {
        $tenantManager = Container::getInstance()->make('tenant');
        $tenantManager->setTenant('tenant_1');

        $model = new TenantModel(['name' => 'Test Item']);
        $this->assertEquals('tenant_1', $model->tenant_id);
    }

    public function test_queries_are_scoped_to_tenant()
    {
        $tenantManager = Container::getInstance()->make('tenant');
        
        // Create items for tenant 1
        $tenantManager->setTenant('tenant_1');
        TenantModel::create(['name' => 'Item 1']);
        TenantModel::create(['name' => 'Item 2']);

        // Create item for tenant 2
        $tenantManager->setTenant('tenant_2');
        TenantModel::create(['name' => 'Item 3']);

        // Check tenant 1 results
        $tenantManager->setTenant('tenant_1');
        $this->assertEquals(2, TenantModel::all()->count());
        $this->assertEquals('Item 1', TenantModel::first()->name);

        // Check tenant 2 results
        $tenantManager->setTenant('tenant_2');
        $this->assertEquals(1, TenantModel::all()->count());
        $this->assertEquals('Item 3', TenantModel::first()->name);
    }

    public function test_identify_tenant_middleware()
    {
        $middleware = new \Phantom\Http\Middlewares\IdentifyTenant();
        $request = new \Phantom\Http\Request(['HTTP_X_TENANT_ID' => 'header_tenant']);
        
        $middleware->handle($request, function($req) {
            $tenantManager = Container::getInstance()->make('tenant');
            $this->assertEquals('header_tenant', $tenantManager->getTenantId());
            return 'next';
        });

        // Test subdomain
        $tenantManager = Container::getInstance()->make('tenant');
        $tenantManager->setTenant(null);

        $request = new \Phantom\Http\Request(['HTTP_HOST' => 'mario.phantom.test']);
        $middleware->handle($request, function($req) {
            $tenantManager = Container::getInstance()->make('tenant');
            $this->assertEquals('mario', $tenantManager->getTenantId());
            return 'next';
        });
    }
}
