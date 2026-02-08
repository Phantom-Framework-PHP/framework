<?php

namespace Phantom\Traits;

use Phantom\Core\Container;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     * 
     * @return void
     */
    public static function bootBelongsToTenant()
    {
        // This will be called by the Model's boot process
    }

    /**
     * Scope a query to only include models belonging to the current tenant.
     *
     * @param  \Phantom\Database\Query\Builder  $builder
     * @return \Phantom\Database\Query\Builder
     */
    public function scopeTenant($builder)
    {
        $tenantManager = Container::getInstance()->make('tenant');

        if ($tenantManager->hasTenant()) {
            return $builder->where('tenant_id', $tenantManager->getTenantId());
        }

        return $builder;
    }

    /**
     * Initialize the trait.
     * 
     * @return void
     */
    public function initializeBelongsToTenant()
    {
        $tenantManager = Container::getInstance()->make('tenant');

        if ($tenantManager->hasTenant() && !isset($this->attributes['tenant_id'])) {
            $this->attributes['tenant_id'] = $tenantManager->getTenantId();
        }
    }
}
