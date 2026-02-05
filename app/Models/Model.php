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
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        if (method_exists($this, $key)) {
            return $this->$key()->getResults();
        }

        return null;
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        return new class($instance->query()->where($foreignKey, $this->$localKey), $related) {
            protected $query;
            protected $related;
            public function __construct($query, $related) { $this->query = $query; $this->related = $related; }
            public function getResults() { 
                $results = $this->query->get();
                return array_map(fn($attr) => new $this->related((array)$attr), $results);
            }
            public function __call($method, $args) { return $this->query->$method(...$args); }
        };
    }

    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        return new class($instance->query()->where($foreignKey, $this->$localKey), $related) {
            protected $query;
            protected $related;
            public function __construct($query, $related) { $this->query = $query; $this->related = $related; }
            public function getResults() { 
                $result = $this->query->first();
                return $result ? new $this->related((array)$result) : null;
            }
            public function __call($method, $args) { return $this->query->$method(...$args); }
        };
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related;
        $ownerKey = $ownerKey ?: $instance->primaryKey;
        $foreignKey = $foreignKey ?: strtolower((new \ReflectionClass($instance))->getShortName()) . '_id';

        return new class($instance->query()->where($ownerKey, $this->$foreignKey), $related) {
            protected $query;
            protected $related;
            public function __construct($query, $related) { $this->query = $query; $this->related = $related; }
            public function getResults() { 
                $result = $this->query->first();
                return $result ? new $this->related((array)$result) : null;
            }
            public function __call($method, $args) { return $this->query->$method(...$args); }
        };
    }

    protected function getForeignKey()
    {
        return strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
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
