<?php

namespace Phantom\Live;

use ReflectionClass;
use ReflectionProperty;
use Phantom\Validation\Validator;

abstract class Component
{
    public $id;
    protected $errors = [];
    protected $emittedEvents = [];
    protected $listeners = []; // ['eventName' => 'methodName']
    protected $redirectTo;
    protected $queryString = []; // Properties to sync with URL

    public function mount() {}

    abstract public function render();

    public function redirect($url)
    {
        $this->redirectTo = $url;
    }

    public function getRedirect() { return $this->redirectTo; }

    public function emit($event, ...$params)
    {
        $this->emittedEvents[] = ['event' => $event, 'params' => $params];
    }

    public function getEmittedEvents() { return $this->emittedEvents; }
    public function getListeners() { return $this->listeners; }

    public function validate(array $rules)
    {
        $validator = new Validator($this->getState(), $rules);
        if (!$validator->validate()) {
            $this->errors = $validator->errors();
            throw new \Exception("Validation failed in Live Component");
        }
        return array_intersect_key($this->getState(), $rules);
    }

    public function getErrors() { return $this->errors; }

    public function getState()
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $state = [];
        foreach ($properties as $property) {
            if ($property->getName() === 'id') continue;
            $state[$property->getName()] = $property->getValue($this);
        }
        return $state;
    }

    public function fill(array $state)
    {
        foreach ($state as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function __get($key)
    {
        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Property';
        if (method_exists($this, $method)) return $this->$method();
        return null;
    }

    public function output()
    {
        // On initial render, sync from URL if properties defined in queryString
        if (empty($this->id)) {
            foreach ($this->queryString as $property) {
                if (isset($_GET[$property])) {
                    $this->$property = $_GET[$property];
                }
            }
        }

        $html = $this->render();
        $state = base64_encode(json_encode($this->getState()));
        $listeners = base64_encode(json_encode($this->getListeners()));
        $urlSync = base64_encode(json_encode($this->queryString));
        $name = get_class($this);

        return '
        <div data-live-component="' . $name . '" 
             data-live-id="' . $this->id . '" 
             data-live-state="' . $state . '"
             data-live-listeners="' . $listeners . '"
             data-live-url-sync="' . $urlSync . '">
            ' . $html . '
        </div>
        ';
    }
}
