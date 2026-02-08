<?php

use Phantom\Http\Request;

/** @var \Phantom\Core\Router $router */

$router->get('/', function () {
    return view('welcome', [
        'title' => 'Phantom Framework',
        'message' => 'Welcome to the Future of PHP'
    ]);
});

$router->get('/lang-test', function () {
    app('translator')->setLocale('es');
    return __('messages.welcome', ['name' => 'Desarrollador']);
});

$router->get('/db-test', function () {
    try {
        // Just try to get the PDO instance to verify connection attempt
        // Note: This requires a valid DB config in .env. 
        // If no DB is configured, it might fail if we actually query.
        $pdo = app('db')->getPdo();
        return "Database connection established!";
    } catch (\Exception $e) {
        return "Database error: " . $e->getMessage();
    }
});

// Phantom Pulse Routes
$router->group(['prefix' => 'phantom/pulse'], function($router) {
    $router->get('/', [\Phantom\Http\Controllers\PulseController::class, 'index']);
    $router->post('/clear', [\Phantom\Http\Controllers\PulseController::class, 'clear']);
    $router->post('/reset-ip', [\Phantom\Http\Controllers\PulseController::class, 'resetIp']);
});

// Phantom Live Routes
$router->post('/phantom/live/update', [\Phantom\Http\Controllers\LiveController::class, 'update']);