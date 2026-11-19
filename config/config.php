<?php
/**
 * LOCAL DEVELOPMENT configuration.
 * This file is git-ignored. Do not commit real credentials.
 */

return [
    'env'   => 'local',
    'debug' => true,
    'timezone' => 'Africa/Lagos',
    'base_url' => 'auto',

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'armstrong',
        'user'     => 'armstrong',
        'password' => 'devpass',
        'charset'  => 'utf8mb4',
    ],

    'app_key'      => 'dev_only_key_change_in_production_0000000000000000',
    'session_name' => 'armstrong_sess',

    'admin_path'   => 'invent',
    'session_lifetime' => 7200,
    'login_max_attempts' => 5,
    'login_window_minutes' => 15,
    'login_lockout_minutes' => 15,

    'currency_symbol' => '₦',
    'currency_code'   => 'NGN',
    'reservation_days' => 7,

    'cloudinary' => [
        'cloud_name' => 'fvgwl4gy',
        'api_key'    => '324934876359653',
        'api_secret' => 'nOxg_yo7yLctY1Jr7NEsj65bliY',
        'folder'     => 'Armstrong',
    ],

    'mail' => [
        'enabled'    => false,
        'host'       => '',
        'port'       => 587,
        'username'   => '',
        'password'   => '',
        'encryption' => 'tls',
        'from_email' => 'no-reply@example.com',
        'from_name'  => 'Armstrong Homes & Interior Limited',
        'admin_email'=> 'sales@example.com',
    ],

    'uploads' => [
        'max_size'      => 5242880,
        'allowed_mimes' => ['image/jpeg','image/png','image/webp','image/gif'],
    ],
];
