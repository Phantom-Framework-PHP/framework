<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Response;
use Phantom\Http\Request;
use Exception;

class LiveController extends Controller
{
    public function update(Request $request)
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?: $request->all();
        
        $name = $payload['component'] ?? $request->input('component');
        $id = $payload['id'] ?? $request->input('id');
        $state = json_decode(base64_decode($payload['state'] ?? $request->input('state')), true);
        $action = $payload['action'] ?? $request->input('action');
        $params = $payload['params'] ?? $request->input('params', []);

        if (!$name) {
            throw new Exception("Component name is required.");
        }

        // The name should be a full class path
        $componentClass = str_replace('.', '\\', $name);

        if (!class_exists($componentClass)) {
            throw new Exception("Live component [{$componentClass}] not found.");
        }

        /** @var \Phantom\Live\Component $instance */
        $instance = new $componentClass();
        $instance->id = $id;
        $instance->fill($state);

        // Execute action if provided
        if ($action && method_exists($instance, $action)) {
            $instance->$action(...$params);
        }

        return (new Response())->json([
            'html' => $instance->output(),
            'state' => base64_encode(json_encode($instance->getState()))
        ]);
    }
}