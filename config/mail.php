<?php

use Phantom\Core\Env;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    'default' => Env::get('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'host' => Env::get('MAIL_HOST', 'smtp.mailtrap.io'),
            'port' => Env::get('MAIL_PORT', 2525),
            'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
            'username' => Env::get('MAIL_USERNAME'),
            'password' => Env::get('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => Env::get('MAIL_EHLO_DOMAIN'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => Env::get('MAIL_LOG_CHANNEL'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => Env::get('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => Env::get('MAIL_FROM_NAME', 'Example'),
    ],

];
