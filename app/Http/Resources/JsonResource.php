<?php

namespace Phantom\Http\Resources;

use JsonSerializable;

class JsonResource implements JsonSerializable
{
    /**
     * The resource instance.
     *
     * @var mixed
     */
    protected $resource;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return static
     */
    public static function make($resource)
    {
        return new static($resource);
    }

    /**
     * Create a new resource collection.
     *
     * @param  mixed  $resource
     * @return ResourceCollection
     */
    public static function collection($resource)
    {
        return new ResourceCollection($resource, static::class);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray()
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource) ? $this->resource : $this->resource->toArray();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Dynamically proxy properties to the resource.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->resource->$key;
    }
}
