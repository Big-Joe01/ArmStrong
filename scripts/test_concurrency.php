<?php
declare(strict_types=1);

/**
 * Concurrency test: two buyers attempt to reserve the last available unit at
 * the same time. Exactly one must succeed; stock must never go negative.
 *
 *   php scripts/test_concurrency.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/app/autoload.php';
require $root . '/app/helpers.php';

use App\Core\Config;
use App\Core\Database;
use App\Services\InventoryService;
use App\Services\OrderService;

Config::load($root . '/config/config.php');

$productId = (int) Database::scalar('SELECT id FROM products WHERE is_published = 1 ORDER BY id LIMIT 1');
if ($productId === 0) {
    fwrite(STDERR, "No product found; run scripts/install.php --seed-demo first.\n");
    exit(1);
}

// --- Worker mode: attempt a single order and report the outcome ------------
if (($argv[1] ?? '') === '--worker') {
    $tag = $argv[2] ?? 'A';
    usleep(20000); // line both workers up on the same starting gun
    try {
        $result = OrderService::create([
            'customer_name'     => 'Concurrent Buyer ' . $tag,
            'customer_phone'    => '0803000000' . $tag,
            'customer_email'    => null,
            'fulfilment_method' => 'collection',
            'items'             => [['product_id' => $productId, 'quantity' => 1, 'options' => null]],
        ], '127.0.0.1');
        echo "WORKER {$tag}: SUCCESS " . $result['reference'] . "\n";
    } catch (\Throwable $e) {
        echo "WORKER {$tag}: REJECTED (" . $e->getMessage() . ")\n";
    }
    exit(0);
}

// --- Parent: reset stock to a single available unit ------------------------
Database::transaction(function () use ($productId): void {
    $product = Database::first('SELECT * FROM products WHERE id = ? FOR UPDATE', [$productId]);

    // Close any reservations left over from earlier runs so the test starts clean.
    foreach (Database::all("SELECT id FROM stock_reservations WHERE product_id = ? AND status = 'active'", [$productId]) as $r) {
        Database::run(
            "UPDATE stock_reservations SET status = 'released', released_at = NOW(), release_reason = 'test reset' WHERE id = ? AND status = 'active'",
            [(int) $r['id']]
        );
    }

    $delta = 1 - (int) $product['stock_on_hand'];
    Database::update('products', ['stock_on_hand' => 1, 'reserved_qty' => 0], ['id' => $productId]);
    if ($delta !== 0) {
        InventoryService::recordMovement(
            $productId, $delta > 0 ? 'addition' : 'reduction', $delta, 1, 0,
            'manual', null, 'Concurrency test setup', null
        );
    }
});
echo "Product #{$productId} set to 1 available unit.\n";

// --- Launch two workers simultaneously -------------------------------------
$script = __FILE__;
$procs  = [];
foreach (['A', 'B'] as $tag) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' --worker ' . escapeshellarg($tag);
    $procs[$tag] = popen($cmd . ' 2>&1', 'r');
}

$output = '';
foreach ($procs as $handle) {
    $output .= (string) stream_get_contents($handle);
    pclose($handle);
}
echo $output;

// --- Verify the invariant --------------------------------------------------
$product = Database::first('SELECT stock_on_hand, reserved_qty FROM products WHERE id = ?', [$productId]);
$active  = (int) Database::scalar("SELECT COUNT(*) FROM stock_reservations WHERE product_id = ? AND status = 'active'", [$productId]);
$succeeded = substr_count($output, 'SUCCESS');

printf(
    "\nResult: %d success(es); stock_on_hand=%d, reserved_qty=%d, active_reservations=%d\n",
    $succeeded, (int) $product['stock_on_hand'], (int) $product['reserved_qty'], $active
);

$ok = $succeeded === 1
    && (int) $product['reserved_qty'] <= (int) $product['stock_on_hand']
    && $active === 1
    && str_contains($output, 'Only');

echo $ok
    ? "PASS: exactly one buyer reserved the last unit; the other was told stock was insufficient.\n"
    : "FAIL: oversell, wrong reservation count, or unclear rejection message.\n";
exit($ok ? 0 : 1);
