<?php

namespace Phantom\Http\Resources;

use JsonSerializable;
use IteratorAggregate;
use ArrayIterator;

class ResourceCollection implements JsonSerializable, IteratorAggregate
{
    /**
     * The resource collection instance.
     *
     * @var mixed
     */
    protected $collection;

    /**
     * The resource class to wrap each item.
     *
     * @var string
     */
    protected $collects;

    /**
     * Create a new resource collection instance.
     *
     * @param  mixed  $collection
     * @param  string  $collects
     * @return void
     */
    public function __construct($collection, $collects)
    {
        $this->collection = $collection;
        $this->collects = $collects;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($item) {
            return (new $this->collects($item))->toArray();
        }, $this->getArrayableItems($this->collection));
    }

    /**
     * Get the arrayable items.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof \Phantom\Core\Collection) {
            return $items->toArray();
        }

        return (array) $items;
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
     * Get an iterator for the collection.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
