<?php
declare(strict_types=1);

/**
 * Application bootstrap: autoloading, configuration, error handling, session.
 * Included by the public and admin front controllers.
 */

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/helpers.php';

use App\Core\Config;
use App\Core\Logger;
use App\Core\Session;
use App\Core\View;

// --- Configuration ---------------------------------------------------------
$configPath = dirname(__DIR__) . '/config/config.php';
Config::load($configPath);

// --- Environment -----------------------------------------------------------
date_default_timezone_set((string) Config::get('timezone', 'UTC'));

$debug = Config::isDebug();
error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

// --- Error / exception handling -------------------------------------------
set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $e): void {
    Logger::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (Config::isDebug()) {
        echo '<pre style="padding:24px;font:14px/1.5 monospace;background:#1e1b1c;color:#ffb4b4;white-space:pre-wrap">';
        echo htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8');
        echo '</pre>';
        return;
    }
    try {
        echo View::render('public/errors/500', ['title' => 'Something went wrong']);
    } catch (Throwable) {
        echo 'An unexpected error occurred. Please try again later.';
    }
});

// --- Security headers ------------------------------------------------------
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 0');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// --- Session ---------------------------------------------------------------
Session::start();

// --- Shared view data ------------------------------------------------------
View::share('config', [
    'base_url'        => rtrim((string) Config::get('base_url', ''), '/'),
    'admin_path'      => (string) Config::get('admin_path', 'invent'),
    'currency_symbol' => (string) Config::get('currency_symbol', '₦'),
    'app_name'        => 'Armstrong Homes & Interior Limited',
]);
