<?php
/**
 * Armstrong Homes & Interior Limited - configuration example.
 *
 * Copy this file to config/config.php and fill in real values.
 * config/config.php is git-ignored and must NEVER be committed.
 */

return [
    // --- Environment -------------------------------------------------------
    'env'   => 'production',            // local | production
    'debug' => false,                   // ALWAYS false in production
    'timezone' => 'Africa/Lagos',
    'base_url' => 'https://example.com', // no trailing slash

    // --- Database ----------------------------------------------------------
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'armstrong',
        'user'     => 'db_user',
        'password' => 'db_password',
        'charset'  => 'utf8mb4',
    ],

    // --- Application secrets ----------------------------------------------
    // Generate with: php -r "echo bin2hex(random_bytes(32));"
    'app_key'      => 'CHANGE_ME_64_CHAR_RANDOM_HEX',
    'session_name' => 'armstrong_sess',

    // --- Admin area --------------------------------------------------------
    'admin_path'   => 'invent',         // URL segment for the admin area
    'session_lifetime' => 7200,         // seconds of inactivity before logout
    'login_max_attempts' => 5,          // per window
    'login_window_minutes' => 15,
    'login_lockout_minutes' => 15,

    // --- Business rules ----------------------------------------------------
    'currency_symbol' => '₦',
    'currency_code'   => 'NGN',
    'reservation_days' => 7,            // stock reservation period

    // --- Cloudinary (image storage) ---------------------------------------
    'cloudinary' => [
        'cloud_name' => '',
        'api_key'    => '',
        'api_secret' => '',
        'folder'     => 'armstrong',
    ],

    // --- Mail (SMTP) -------------------------------------------------------
    // If 'enabled' is false the app still records orders; mail is logged instead.
    'mail' => [
        'enabled'    => false,
        'host'       => 'smtp.example.com',
        'port'       => 587,
        'username'   => '',
        'password'   => '',
        'encryption' => 'tls',          // tls | ssl | none
        'from_email' => 'no-reply@example.com',
        'from_name'  => 'Armstrong Homes & Interior Limited',
        'admin_email'=> 'sales@example.com',
    ],

    // --- Uploads -----------------------------------------------------------
    'uploads' => [
        'max_size'       => 5242880,    // 5 MB
        'allowed_mimes'  => ['image/jpeg','image/png','image/webp','image/gif'],
    ],
];
