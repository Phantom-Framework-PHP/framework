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
        $pdo = app('db')->getPdo();
        return "Database connection established!";
    } catch (\Exception $e) {
        return "Database error: " . $e->getMessage();
    }
});
