<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Models\Model;

class TestDeferredModel extends Model {
    protected $casts = [
        'settings' => 'json',
        'is_active' => 'bool'
    ];
}

class ModelDeferredHydrationTest extends TestCase
{
    public function test_attributes_are_hydrated_lazily()
    {
        $json = json_encode(['theme' => 'dark']);
        $model = (new TestDeferredModel)->newInstance([
            'settings' => $json,
            'is_active' => '1'
        ], true);

        // Access private/protected attributes for verification if needed, 
        // but we can verify via behavior.
        
        $this->assertEquals(['theme' => 'dark'], $model->settings);
        $this->assertTrue($model->is_active);
        
        // After first access, it should be stored as array internally
        $attributes = $model->getAttributes();
        $this->assertIsArray($attributes['settings']);
        $this->assertIsBool($attributes['is_active']);
    }

    public function test_to_array_forces_full_hydration()
    {
        $model = (new TestDeferredModel)->newInstance([
            'settings' => '{"a":1}',
            'is_active' => '0'
        ], true);

        $array = $model->toArray();
        
        $this->assertIsArray($array['settings']);
        $this->assertIsBool($array['is_active']);
    }
}
