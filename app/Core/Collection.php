<?php

namespace Phantom\Core;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Countable;

class Collection implements IteratorAggregate, JsonSerializable, Countable
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function make(array $items = [])
    {
        return new static($items);
    }

    public function first()
    {
        return reset($this->items) ?: null;
    }

    public function last()
    {
        return end($this->items) ?: null;
    }

    public function map(callable $callback)
    {
        return new static(array_map($callback, $this->items));
    }

    public function filter(callable $callback)
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function pluck($value, $key = null)
    {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = is_object($item) ? ($item->$value ?? null) : ($item[$value] ?? null);

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null);
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof self ? $value->toArray() : $value;
        }, $this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}
