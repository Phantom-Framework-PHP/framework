<?php

namespace Phantom\Live;

use Phantom\View\View;
use ReflectionClass;
use ReflectionProperty;
use Phantom\Validation\Validator;

abstract class Component
{
    /**
     * The unique ID for this component instance.
     */
    public $id;

    /**
     * Validation errors for the component.
     */
    protected $errors = [];

    /**
     * Initialize the component.
     */
    public function mount()
    {
        //
    }

    /**
     * Render the component view.
     */
    abstract public function render();

    /**
     * Validate the component data.
     * 
     * @param array $rules
     * @return array
     */
    public function validate(array $rules)
    {
        $validator = new Validator($this->getState(), $rules);

        if (!$validator->validate()) {
            $this->errors = $validator->errors();
            throw new \Exception("Validation failed in Live Component");
        }

        return array_intersect_key($this->getState(), $rules);
    }

    /**
     * Get validation errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the public properties of the component for state hydration.
     */
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

    /**
     * Fill the component state from an array.
     */
    public function fill(array $state)
    {
        foreach ($state as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Output the component's HTML wrapped in a data-driven container.
     */
    public function output()
    {
        $html = $this->render();
        $state = base64_encode(json_encode($this->getState()));
        $name = get_class($this);

        return '
        <div data-live-component="' . $name . '" data-live-id="' . $this->id . '" data-live-state="' . $state . '">
            ' . $html . '
        </div>
        ';
    }
}
