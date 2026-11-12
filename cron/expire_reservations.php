<?php
declare(strict_types=1);

/**
 * Scheduled reservation expiry.
 *
 * Expires orders whose 7-day stock reservation has lapsed and releases the
 * reserved quantity back to available stock. Idempotent: each reservation is
 * released at most once, guarded by the stock_released flag and the
 * reservation status inside a locked transaction.
 *
 * Run from a cPanel cron job, for example hourly:
 *
 *   /usr/local/bin/php /home/USER/public_html/cron/expire_reservations.php >> /home/USER/logs/armstrong-cron.log 2>&1
 *
 * Exits 0 on success and 1 on failure so cron alerts on real errors.
 */

if (PHP_SAPI !== 'cli') {
    // Allow an optional HTTP trigger guarded by a token for hosts without cron CLI.
    $root = dirname(__DIR__);
    require $root . '/app/autoload.php';
    require $root . '/app/helpers.php';
    App\Core\Config::load($root . '/config/config.php');

    $token = $_GET['token'] ?? '';
    $expected = (string) App\Core\Config::get('app_key', '');
    if ($expected === '' || !hash_equals($expected, (string) $token)) {
        http_response_code(403);
        echo 'Forbidden';
        exit(1);
    }
    header('Content-Type: application/json');
    $result = App\Services\OrderService::expireStaleReservations();
    echo json_encode(['ok' => true] + $result);
    exit(0);
}

$root = dirname(__DIR__);
require $root . '/app/autoload.php';
require $root . '/app/helpers.php';

use App\Core\Config;
use App\Core\Logger;
use App\Services\OrderService;

$configPath = $root . '/config/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Missing config/config.php\n");
    exit(1);
}
Config::load($configPath);
date_default_timezone_set((string) Config::get('timezone', 'UTC'));

try {
    $result = OrderService::expireStaleReservations();
    $line = sprintf(
        "[%s] reservation expiry: %d order(s) expired, %d reservation(s) released\n",
        date('Y-m-d H:i:s'),
        (int) ($result['orders'] ?? 0),
        (int) ($result['reservations'] ?? 0)
    );
    echo $line;
    Logger::info(trim($line));
    exit(0);
} catch (Throwable $e) {
    $line = sprintf("[%s] reservation expiry FAILED: %s\n", date('Y-m-d H:i:s'), $e->getMessage());
    fwrite(STDERR, $line);
    Logger::error($e->getMessage());
    exit(1);
}
