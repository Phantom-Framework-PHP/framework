<?php

use Phantom\Core\Env;

return [

    'default' => Env::get('BROADCAST_DRIVER', 'null'),

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => Env::get('PUSHER_APP_KEY'),
            'secret' => Env::get('PUSHER_APP_SECRET'),
            'app_id' => Env::get('PUSHER_APP_ID'),
            'options' => [
                'cluster' => Env::get('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
