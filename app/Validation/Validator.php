<?php

namespace Phantom\Validation;

class Validator
{
    protected $data;
    protected $rules;
    protected $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Validate the data against the rules.
     *
     * @return bool
     */
    public function validate()
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = explode('|', $rules);
            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a specific rule to a field.
     *
     * @param string $field
     * @param string $rule
     */
    protected function applyRule($field, $rule)
    {
        $value = $this->data[$field] ?? null;
        $params = [];

        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule);
            $params = explode(',', $paramStr);
        }

        $method = 'validate' . ucfirst($rule);
        if (method_exists($this, $method)) {
            if (! $this->$method($field, $value, $params)) {
                $this->addError($field, $rule, $params);
            }
        }
    }

    protected function validateRequired($field, $value)
    {
        return !is_null($value) && $value !== '';
    }

    protected function validateEmail($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateNumeric($field, $value)
    {
        return is_numeric($value);
    }

    protected function validateMin($field, $value, $params)
    {
        $min = $params[0];
        if (is_numeric($value)) return $value >= $min;
        return strlen($value) >= $min;
    }

    protected function validateMax($field, $value, $params)
    {
        $max = $params[0];
        if (is_numeric($value)) return $value <= $max;
        return strlen($value) <= $max;
    }

    protected function addError($field, $rule, $params)
    {
        $message = "The {$field} field failed the {$rule} validation.";
        // Custom messages could be implemented here
        $this->errors[$field][] = $message;
    }

    public function errors()
    {
        return $this->errors;
    }
}
