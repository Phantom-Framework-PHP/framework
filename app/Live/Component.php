<?php

namespace Phantom\Live;

use Phantom\View\View;
use ReflectionClass;
use ReflectionProperty;

abstract class Component
{
    /**
     * The unique ID for this component instance.
     */
    public $id;

    /**
     * Render the component view.
     */
    abstract public function render();

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