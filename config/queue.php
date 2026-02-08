<?php

use Phantom\Core\Env;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => Env::get('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'redis_cluster' => [
            'driver' => 'redis',
            'clusters' => [
                '127.0.0.1:7000',
                '127.0.0.1:7001',
                '127.0.0.1:7002',
            ],
            'timeout' => 5.0,
            'queue' => 'default',
            'retry_after' => 90,
        ],

    ],

];
