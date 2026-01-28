<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Request;
use Phantom\Validation\Validator;
use Exception;

abstract class Controller
{
    /**
     * Validate the given request with the given rules.
     *
     * @param  Request  $request
     * @param  array    $rules
     * @return array
     * @throws Exception
     */
    public function validate(Request $request, array $rules)
    {
        $validator = new Validator($request->all(), $rules);

        if (!$validator->validate()) {
            // In a real framework we might redirect back with errors
            // For now, we throw an exception with the errors
            throw new Exception("Validation failed: " . json_encode($validator->errors()));
        }

        return $request->all();
    }
}