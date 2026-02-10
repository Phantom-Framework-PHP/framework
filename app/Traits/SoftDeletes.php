<?php

namespace Phantom\Traits;

trait SoftDeletes
{
    /**
     * Boot the soft delete trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes()
    {
        // This could be used for global scopes if we had them
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    public function delete()
    {
        $this->fireModelEvent('deleting');
        $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
        $this->save();
        $this->fireModelEvent('deleted');
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool
     */
    public function restore()
    {
        $this->attributes['deleted_at'] = null;
        return $this->save();
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function trashed()
    {
        return !is_null($this->attributes['deleted_at'] ?? null);
    }
}
