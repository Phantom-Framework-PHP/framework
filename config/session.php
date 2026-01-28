<?php

use Phantom\Core\Env;

return [
    'driver' => Env::get('SESSION_DRIVER', 'file'),
    'lifetime' => Env::get('SESSION_LIFETIME', 120),
    'secure' => Env::get('SESSION_SECURE_COOKIE', false),
];
