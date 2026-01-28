<?php

namespace Phantom\Models;

use Phantom\Core\Container;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected $table;
    protected $primaryKey = 'id';
    protected $attributes = [];
    protected $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function query()
    {
        $instance = new static;
        return Container::getInstance()->make('db')->table($instance->getTable());
    }

    public static function all()
    {
        $results = static::query()->get();
        return array_map(fn($attributes) => new static((array) $attributes), $results);
    }

    public static function find($id)
    {
        $instance = new static;
        $result = static::query()->where($instance->primaryKey, $id)->first();
        
        if ($result) {
            $model = new static((array) $result);
            $model->exists = true;
            return $model;
        }

        return null;
    }

    public static function where($column, $operator = null, $value = null)
    {
        return static::query()->where(...func_get_args());
    }

    public function save()
    {
        $db = Container::getInstance()->make('db');
        
        if ($this->exists) {
            static::query()
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($this->attributes);
        } else {
            $db->table($this->getTable())->insert($this->attributes);
            $this->attributes[$this->primaryKey] = $db->getPdo()->lastInsertId();
            $this->exists = true;
        }

        return $this;
    }

    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }

        // Simple pluralization: User -> users
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower($className) . 's';
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }
}
