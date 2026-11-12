<?php
declare(strict_types=1);

/**
 * Router for PHP's built-in development server.
 *
 * The production app relies on Apache's mod_rewrite rules in .htaccess, which
 * the built-in server ignores. This script reproduces the same routing so the
 * platform can be previewed locally with:
 *
 *     php -S 0.0.0.0:8080 scripts/dev-router.php
 */

$root = dirname(__DIR__);
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($uri), '/');

// Block direct access to sensitive directories (mirrors .htaccess).
if (preg_match('#^/(app|config|database|cron|storage|docs|vendor)(/|$)#', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

// Serve existing static files (assets, images, css, js, robots.txt, ...).
$file = realpath($root . $path);
if ($path !== '/' && $file !== false && is_file($file) && str_starts_with($file, $root)) {
    return false;
}

// Admin area -> its own front controller.
if (preg_match('#^/invent(/|$)#', $path)) {
    $_SERVER['SCRIPT_NAME'] = '/invent/index.php';
    require $root . '/invent/index.php';
    return true;
}

// Everything else -> the public front controller.
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
return true;
