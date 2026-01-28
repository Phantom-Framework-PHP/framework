<?php

return [
    'defaults' => [
        'guard' => 'web',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'database',
            'model' => \Phantom\Models\User::class, // We will create this
            'table' => 'users',
        ],
    ],
];
