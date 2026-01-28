<?php

/**
 * Phantom Framework - A minimalist PHP Framework
 *
 * @package  Phantom
 * @author   Phantom Community
 */

require __DIR__ . '/../vendor/autoload.php';

use Phantom\Core\Application;
use Phantom\Http\Request;

// 1. Initialize Application
$app = new Application(
    dirname(__DIR__)
);

// 2. Capture Request & Send Response
$request = Request::capture();
$response = $app->handle($request);

$response->send();
