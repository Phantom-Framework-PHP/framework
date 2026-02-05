<?php

namespace Phantom\Models;

use Phantom\Core\Container;
use Phantom\Core\Collection;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $visible = [];
    protected $appends = [];
    public $timestamps = true;
    protected $attributes = [];
    protected $relations = [];
    protected $exists = false;

    /**
     * The registered observers.
     *
     * @var array
     */
    protected static $observers = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isFillable($key)
    {
        if (empty($this->fillable)) {
            return true;
        }

        return in_array($key, $this->fillable);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();

        return $model;
    }

    /**
     * Register an observer for the model.
     *
     * @param  string  $class
     * @return void
     */
    public static function observe($class)
    {
        static::$observers[static::class][] = $class;
    }

    protected function fireModelEvent($event)
    {
        $observers = static::$observers[static::class] ?? [];

        foreach ($observers as $observerClass) {
            $observer = Container::getInstance()->make($observerClass);
            if (method_exists($observer, $event)) {
                $observer->$event($this);
            }
        }
    }

    public static function with($relations)
    {
        $instance = new static;
        $relations = is_array($relations) ? $relations : func_get_args();
        
        return new class($instance->query(), static::class, $relations) {
            protected $query;
            protected $class;
            protected $with;
            public function __construct($query, $class, $with) { 
                $this->query = $query; 
                $this->class = $class;
                $this->with = $with;
            }
            public function get() {
                $collection = $this->query->get();
                $models = $collection->map(fn($attr) => new $this->class((array)$attr));
                if ($models->count() > 0) {
                    $modelsArray = iterator_to_array($models);
                    (new $this->class)->loadRelations($modelsArray, $this->with);
                    return new Collection($modelsArray);
                }
                return $models;
            }
            public function __call($method, $args) { 
                $res = $this->query->$method(...$args); 
                return $res === $this->query ? $this : $res;
            }
        };
    }

    public function loadRelations(&$models, $relations)
    {
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                foreach ($models as $model) {
                    $model->relations[$relation] = $model->$relation()->getResults();
                }
            }
        }
    }

    public static function query()
    {
        $instance = new static;
        $builder = Container::getInstance()->make('db')
            ->table($instance->getTable())
            ->setModelClass(static::class);
        
        // Detect SoftDeletes trait
        if (in_array(\Phantom\Traits\SoftDeletes::class, class_uses_recursive(static::class))) {
            $builder->useSoftDeletes();
        }

        return $builder;
    }

    public static function all()
    {
        return static::query()->get()->map(fn($attr) => new static((array)$attr));
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

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @return static
     *
     * @throws \Exception
     */
    public static function findOrFail($id)
    {
        $result = static::find($id);

        if (!$result) {
            throw new \Exception("No query results for model [" . static::class . "] $id");
        }

        return $result;
    }

    public static function where($column, $operator = null, $value = null)
    {
        return static::query()->where(...func_get_args());
    }

    public static function withTrashed()
    {
        return static::query()->withTrashed();
    }

    public static function onlyTrashed()
    {
        return static::query()->onlyTrashed();
    }

    public function save()
    {
        $db = Container::getInstance()->make('db');

        if ($this->timestamps) {
            $this->updateTimestamps();
        }
        
        if ($this->exists) {
            $this->fireModelEvent('updating');
            $db->table($this->getTable())
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($this->attributes);
            $this->fireModelEvent('updated');
        } else {
            $this->fireModelEvent('creating');
            $db->table($this->getTable())->insert($this->attributes);
            $this->attributes[$this->primaryKey] = $db->getPdo()->lastInsertId();
            $this->exists = true;
            $this->fireModelEvent('created');
        }

        return $this;
    }

    /**
     * Update the creation and update timestamps.
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        $time = date('Y-m-d H:i:s');

        if (!$this->exists && !isset($this->attributes['created_at'])) {
            $this->attributes['created_at'] = $time;
        }

        $this->attributes['updated_at'] = $time;
    }

    public function delete()
    {
        $this->fireModelEvent('deleting');
        
        $db = Container::getInstance()->make('db');
        $db->table($this->getTable())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->delete();

        $this->fireModelEvent('deleted');
        return true;
    }

    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }

        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower($className) . 's';
    }

    public function __get($key)
    {
        // 1. Check for Accessor
        $accessor = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return $this->$accessor($this->attributes[$key] ?? null);
        }

        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            $result = $this->$key()->getResults();
            $this->relations[$key] = $result;
            return $result;
        }

        return null;
    }

    public function __set($key, $value)
    {
        // Check for Mutator
        $mutator = 'set' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        
        if (method_exists($this, $mutator)) {
            $this->$mutator($value);
        } else {
            $this->attributes[$key] = $value;
        }
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
                return $this->query->get()->map(fn($attr) => new $this->related((array)$attr));
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

    public function morphMany($related, $name)
    {
        $instance = new $related;
        $type = $name . '_type';
        $id = $name . '_id';

        return new class($instance->query()->where($type, static::class)->where($id, $this->{$this->primaryKey}), $related) {
            protected $query;
            protected $related;
            public function __construct($query, $related) { $this->query = $query; $this->related = $related; }
            public function getResults() { 
                return $this->query->get()->map(fn($attr) => new $this->related((array)$attr));
            }
            public function __call($method, $args) { return $this->query->$method(...$args); }
        };
    }

    public function morphTo($name = null)
    {
        if (!$name) {
            $name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        $typeField = $name . '_type';
        $idField = $name . '_id';

        $type = $this->$typeField;
        $id = $this->$idField;

        if (!$type || !$id) {
            return new class { public function getResults() { return null; } };
        }

        $instance = new $type;

        return new class($instance->query()->where($instance->primaryKey, $id), $type) {
            protected $query;
            protected $type;
            public function __construct($query, $type) { $this->query = $query; $this->type = $type; }
            public function getResults() { 
                $result = $this->query->first();
                return $result ? new $this->type((array)$result) : null;
            }
            public function __call($method, $args) { return $this->query->$method(...$args); }
        };
    }

    protected function getForeignKey()
    {
        return strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
    }

    public static function __callStatic($method, $args)
    {
        $instance = new static;
        $builder = static::query();
        
        if (method_exists($instance, 'scope' . ucfirst($method))) {
            return $builder->$method(...$args);
        }

        return $builder->$method(...$args);
    }

    public function jsonSerialize(): mixed
    {
        $attributes = $this->attributes;

        // 1. Process Appends (Accessors)
        foreach ($this->appends as $key) {
            $attributes[$key] = $this->$key;
        }

        // 2. Handle Visible/Hidden
        if (!empty($this->visible)) {
            return array_intersect_key($attributes, array_flip($this->visible));
        }

        if (!empty($this->hidden)) {
            return array_diff_key($attributes, array_flip($this->hidden));
        }

        return $attributes;
    }
}