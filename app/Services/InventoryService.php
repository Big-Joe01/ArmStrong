<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;

/**
 * Inventory ledger and stock arithmetic.
 *
 * Model
 * -----
 *  products.stock_on_hand : physical units recorded in inventory.
 *  products.reserved_qty  : units held by active reservations.
 *  available              : stock_on_hand - reserved_qty  (never negative).
 *
 * inventory_movements.quantity is the signed change applied to
 * *physical* stock (stock_on_hand). Reservation and release rows carry
 * quantity = 0 because they do not change physical stock; the resulting
 * reserved level is captured in `reserved_after`, and the reserved
 * quantity itself lives in stock_reservations.
 *
 * Concurrency
 * -----------
 * Every mutation takes a row lock on the product (SELECT ... FOR UPDATE)
 * inside a transaction, so two simultaneous orders cannot oversell the
 * last unit. All checks are re-validated after the lock is held.
 */
final class InventoryService
{
    /** @return array<string,mixed>|null */
    public static function lockProduct(int $productId): ?array
    {
        return Database::first('SELECT * FROM products WHERE id = ? FOR UPDATE', [$productId]);
    }

    public static function available(array $product): int
    {
        return (int) $product['stock_on_hand'] - (int) $product['reserved_qty'];
    }

    /** Record an append-only ledger row and return its id. */
    public static function recordMovement(
        int $productId,
        string $type,
        int $quantity,
        int $stockAfter,
        int $reservedAfter,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?int $adminId = null
    ): int {
        return Database::insert('inventory_movements', [
            'product_id'     => $productId,
            'movement_type'  => $type,
            'quantity'       => $quantity,
            'stock_after'    => $stockAfter,
            'reserved_after' => $reservedAfter,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'reason'         => $reason,
            'admin_id'       => $adminId,
        ]);
    }

    /**
     * Set the opening physical stock for a new product.
     */
    public static function initialStock(int $productId, int $quantity, ?int $adminId = null, ?string $reason = null): void
    {
        Database::transaction(function () use ($productId, $quantity, $adminId, $reason): void {
            $product = self::lockProduct($productId);
            if ($product === null) {
                throw new \RuntimeException('Product not found.');
            }
            $newStock = max(0, $quantity);
            Database::update('products', ['stock_on_hand' => $newStock], ['id' => $productId]);
            self::recordMovement($productId, 'initial', $newStock, $newStock, (int) $product['reserved_qty'], 'manual', null, $reason ?? 'Opening stock', $adminId);
        });
    }

    /**
     * Add physical stock (restock / goods received).
     */
    public static function addStock(int $productId, int $quantity, ?int $adminId = null, ?string $reason = null): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity to add must be positive.');
        }
        Database::transaction(function () use ($productId, $quantity, $adminId, $reason): void {
            $product = self::lockProduct($productId);
            if ($product === null) {
                throw new \RuntimeException('Product not found.');
            }
            $newStock = (int) $product['stock_on_hand'] + $quantity;
            Database::update('products', ['stock_on_hand' => $newStock], ['id' => $productId]);
            self::recordMovement($productId, 'addition', $quantity, $newStock, (int) $product['reserved_qty'], 'manual', null, $reason ?? 'Stock addition', $adminId);
        });
    }

    /**
     * Apply a signed manual adjustment. Never allows physical stock or
     * available stock to become negative.
     */
    public static function adjustStock(int $productId, int $delta, ?int $adminId = null, ?string $reason = null): void
    {
        if ($delta === 0) {
            return;
        }
        Database::transaction(function () use ($productId, $delta, $adminId, $reason): void {
            $product = self::lockProduct($productId);
            if ($product === null) {
                throw new \RuntimeException('Product not found.');
            }
            $newStock = (int) $product['stock_on_hand'] + $delta;
            if ($newStock < 0) {
                throw new \RuntimeException('Adjustment would make stock negative.');
            }
            if ($newStock < (int) $product['reserved_qty']) {
                throw new \RuntimeException('Adjustment would make stock lower than reserved quantity.');
            }
            Database::update('products', ['stock_on_hand' => $newStock], ['id' => $productId]);
            $type = $delta > 0 ? 'addition' : 'reduction';
            self::recordMovement($productId, $type, $delta, $newStock, (int) $product['reserved_qty'], 'manual', null, $reason ?? 'Manual adjustment', $adminId);
        });
    }

    /**
     * Reserve stock for order lines. Must be called inside an open
     * transaction that also creates the order. Throws when stock is
     * insufficient so the whole order rolls back.
     *
     * @param list<array{order_item_id:int,product_id:int,quantity:int}> $lines
     */
    public static function reserveForOrder(int $orderId, array $lines, string $expiresAt): void
    {
        foreach ($lines as $line) {
            $productId = (int) $line['product_id'];
            $quantity  = (int) $line['quantity'];

            $product = self::lockProduct($productId);
            if ($product === null) {
                throw new \RuntimeException('Product no longer available.');
            }
            if ($quantity <= 0) {
                throw new \RuntimeException('Invalid quantity.');
            }

            $available = self::available($product);
            if ($available < $quantity) {
                throw new \RuntimeException(sprintf(
                    'Only %d unit(s) of "%s" are available.',
                    max(0, $available),
                    (string) $product['name']
                ));
            }

            $newReserved = (int) $product['reserved_qty'] + $quantity;
            Database::update('products', ['reserved_qty' => $newReserved], ['id' => $productId]);

            Database::insert('stock_reservations', [
                'order_id'      => $orderId,
                'order_item_id' => (int) $line['order_item_id'],
                'product_id'    => $productId,
                'quantity'      => $quantity,
                'status'        => 'active',
                'reserved_at'   => date('Y-m-d H:i:s'),
                'expires_at'    => $expiresAt,
            ]);

            self::recordMovement(
                $productId,
                'reservation',
                0,
                (int) $product['stock_on_hand'],
                $newReserved,
                'order',
                $orderId,
                sprintf('Reserved %d unit(s)', $quantity)
            );
        }
    }

    /**
     * Release every active reservation for an order. Idempotent: the
     * conditional UPDATE only affects rows still marked 'active', so a
     * second call (or a concurrent cron run) releases nothing.
     *
     * @return int number of reservations released
     */
    public static function releaseOrderReservations(int $orderId, string $reason, string $reservationStatus = 'released'): int
    {
        return (int) Database::transaction(function () use ($orderId, $reason, $reservationStatus): int {
            $released = 0;
            $rows = Database::all(
                'SELECT id, product_id, quantity FROM stock_reservations WHERE order_id = ? AND status = ?',
                [$orderId, 'active']
            );

            foreach ($rows as $row) {
                $productId = (int) $row['product_id'];
                $quantity  = (int) $row['quantity'];

                // Conditional update guarantees exactly-once release.
                $affected = Database::run(
                    "UPDATE stock_reservations
                     SET status = ?, released_at = NOW(), release_reason = ?
                     WHERE id = ? AND status = 'active'",
                    [$reservationStatus, $reason, $row['id']]
                )->rowCount();

                if ($affected === 0) {
                    continue; // Another process already released it.
                }

                $product = self::lockProduct($productId);
                if ($product === null) {
                    continue;
                }
                $newReserved = max(0, (int) $product['reserved_qty'] - $quantity);
                Database::update('products', ['reserved_qty' => $newReserved], ['id' => $productId]);

                self::recordMovement(
                    $productId,
                    'reservation_release',
                    0,
                    (int) $product['stock_on_hand'],
                    $newReserved,
                    'order',
                    $orderId,
                    sprintf('Released %d unit(s): %s', $quantity, $reason)
                );
                $released++;
            }

            return $released;
        });
    }

    /**
     * Convert an order's active reservations into a completed sale:
     * physical stock leaves and the reservation is closed. Idempotent.
     *
     * @return int number of reservations fulfilled
     */
    public static function fulfilOrderReservations(int $orderId, string $reason = 'Order completed'): int
    {
        return (int) Database::transaction(function () use ($orderId, $reason): int {
            $fulfilled = 0;
            $rows = Database::all(
                'SELECT id, product_id, quantity FROM stock_reservations WHERE order_id = ? AND status = ?',
                [$orderId, 'active']
            );

            foreach ($rows as $row) {
                $affected = Database::run(
                    "UPDATE stock_reservations
                     SET status = 'fulfilled', released_at = NOW(), release_reason = ?
                     WHERE id = ? AND status = 'active'",
                    [$reason, $row['id']]
                )->rowCount();
                if ($affected === 0) {
                    continue;
                }

                $product = self::lockProduct((int) $row['product_id']);
                if ($product === null) {
                    continue;
                }
                $qty         = (int) $row['quantity'];
                $newStock    = max(0, (int) $product['stock_on_hand'] - $qty);
                $newReserved = max(0, (int) $product['reserved_qty'] - $qty);

                Database::update('products', [
                    'stock_on_hand' => $newStock,
                    'reserved_qty'  => $newReserved,
                ], ['id' => $product['id']]);

                self::recordMovement(
                    (int) $product['id'],
                    'sale',
                    -$qty,
                    $newStock,
                    $newReserved,
                    'order',
                    $orderId,
                    $reason
                );
                $fulfilled++;
            }

            return $fulfilled;
        });
    }

    /**
     * Deduct physical stock immediately (walk-in / counter sale).
     * Rejects the sale when available stock is insufficient.
     */
    public static function deductForSale(int $productId, int $quantity, string $referenceType, int $referenceId, ?int $adminId, ?string $reason = null): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive.');
        }
        $product = self::lockProduct($productId);
        if ($product === null) {
            throw new \RuntimeException('Product not found.');
        }
        if (self::available($product) < $quantity) {
            throw new \RuntimeException(sprintf(
                'Only %d unit(s) of "%s" are available.',
                max(0, self::available($product)),
                (string) $product['name']
            ));
        }
        $newStock = (int) $product['stock_on_hand'] - $quantity;
        Database::update('products', ['stock_on_hand' => $newStock], ['id' => $productId]);
        self::recordMovement(
            $productId,
            $referenceType === 'walk_in_sale' ? 'walk_in_sale' : 'sale',
            -$quantity,
            $newStock,
            (int) $product['reserved_qty'],
            $referenceType,
            $referenceId,
            $reason ?? 'Counter sale',
            $adminId
        );
    }

    /**
     * Recompute the denormalised reserved counter from active reservations.
     * Used by the maintenance/repair command.
     */
    public static function reconcile(int $productId): int
    {
        $reserved = (int) Database::scalar(
            "SELECT COALESCE(SUM(quantity),0) FROM stock_reservations WHERE product_id = ? AND status = 'active'",
            [$productId]
        );
        Database::update('products', ['reserved_qty' => $reserved], ['id' => $productId]);
        return $reserved;
    }
}
