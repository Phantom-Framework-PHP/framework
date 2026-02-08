<?php

namespace Phantom\Core;

class TenantManager
{
    /**
     * The current tenant instance.
     *
     * @var mixed
     */
    protected $tenant;

    /**
     * Set the current tenant and optionally switch database.
     *
     * @param  mixed  $tenant
     * @param  array|null  $dbConfig
     * @return void
     */
    public function setTenant($tenant, array $dbConfig = null)
    {
        $this->tenant = $tenant;

        if ($dbConfig) {
            Container::getInstance()->make('db')->reconnect($dbConfig);
        }
    }

    /**
     * Get the current tenant.
     *
     * @return mixed
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Get the ID of the current tenant.
     *
     * @return mixed
     */
    public function getTenantId()
    {
        if (!$this->tenant) {
            return null;
        }

        return is_object($this->tenant) ? ($this->tenant->id ?? null) : $this->tenant;
    }

    /**
     * Determine if a tenant is currently set.
     *
     * @return bool
     */
    public function hasTenant()
    {
        return !is_null($this->tenant);
    }
}
