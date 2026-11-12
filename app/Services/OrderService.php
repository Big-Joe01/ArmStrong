<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Str;
use App\Core\View;

/**
 * Creates guest orders, reserves stock and expires stale reservations.
 */
final class OrderService
{
    /**
     * Create an order from a validated payload.
     *
     * @param array{
     *   customer_name:string, customer_phone:string, customer_email?:string,
     *   fulfilment_method:string, delivery_rate_id?:int, delivery_address?:string,
     *   delivery_city?:string, delivery_state?:string, customer_note?:string,
     *   items:list<array{product_id:int,quantity:int,options?:array}>
     * } $payload
     * @return array{order_id:int,reference:string,expires_at:string,total:float}
     *
     * @throws \RuntimeException on validation or stock failure (order is rolled back)
     */
    public static function create(array $payload, ?string $ip = null): array
    {
        $items = $payload['items'] ?? [];
        if ($items === []) {
            throw new \RuntimeException('Your order is empty.');
        }

        // --- Resolve delivery fee from the database (never trust the client) ---
        $fulfilment = $payload['fulfilment_method'] === 'delivery' ? 'delivery' : 'collection';
        $deliveryFee = 0.00;
        $rateId      = null;
        $timeframe   = null;
        if ($fulfilment === 'delivery') {
            $rateId = (int) ($payload['delivery_rate_id'] ?? 0);
            $rate   = $rateId > 0
                ? Database::first('SELECT * FROM delivery_rates WHERE id = ? AND is_active = 1', [$rateId])
                : null;
            if ($rate === null) {
                throw new \RuntimeException('Please choose a valid delivery option.');
            }
            $deliveryFee = (float) $rate['fee'];
            $timeframe   = (string) ($rate['timeframe'] ?? '');
        }

        $reservationDays = (int) Config::get('reservation_days', 7);
        $expiresAt       = date('Y-m-d H:i:s', strtotime('+' . $reservationDays . ' days'));

        return Database::transaction(function () use ($payload, $items, $fulfilment, $deliveryFee, $rateId, $timeframe, $expiresAt, $ip): array {
            // --- Customer record (guest; deduplicated by phone) ---
            $customerId = self::upsertCustomer($payload);

            // --- Build line items from live product data ---
            $subtotal = 0.00;
            $lines    = [];
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity  = (int) ($item['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }
                $product = Database::first('SELECT * FROM products WHERE id = ?', [$productId]);
                if ($product === null || (int) $product['is_published'] !== 1) {
                    throw new \RuntimeException('A product in your order is no longer available.');
                }
                if ($product['availability'] !== 'available') {
                    throw new \RuntimeException(sprintf('"%s" is not currently available for order.', (string) $product['name']));
                }
                $unitPrice = (float) $product['price'];
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [
                    'product_id'       => $productId,
                    'product_name'     => (string) $product['name'],
                    'product_reference'=> (string) $product['reference'],
                    'brand_name'       => self::brandName((int) ($product['brand_id'] ?? 0)),
                    'unit_price'       => $unitPrice,
                    'quantity'         => $quantity,
                    'line_total'       => $lineTotal,
                    'selected_options' => $item['options'] ?? null,
                ];
            }
            if ($lines === []) {
                throw new \RuntimeException('Your order is empty.');
            }

            $subtotal = round($subtotal, 2);
            $total    = round($subtotal + $deliveryFee, 2);

            // --- Order row ---
            $reference = self::uniqueReference();
            $orderId   = Database::insert('orders', [
                'order_reference'   => $reference,
                'customer_id'       => $customerId,
                'customer_name'     => $payload['customer_name'],
                'customer_phone'    => $payload['customer_phone'],
                'customer_email'    => $payload['customer_email'] ?? null,
                'fulfilment_method' => $fulfilment,
                'delivery_rate_id'  => $rateId,
                'delivery_fee'      => $deliveryFee,
                'delivery_address'  => $payload['delivery_address'] ?? null,
                'delivery_city'     => $payload['delivery_city'] ?? null,
                'delivery_state'    => $payload['delivery_state'] ?? null,
                'delivery_timeframe'=> $timeframe,
                'subtotal'          => $subtotal,
                'total'             => $total,
                'amount_paid'       => 0.00,
                'balance'           => $total,
                'payment_status'    => 'unpaid',
                'status'            => 'awaiting_payment',
                'reservation_expires_at' => $expiresAt,
                'customer_note'     => $payload['customer_note'] ?? null,
                'source'            => 'online',
                'stock_released'    => 0,
                'ip_address'        => $ip,
            ]);

            // --- Insert lines, then reserve stock (throws on shortfall) ---
            $reservationLines = [];
            foreach ($lines as $line) {
                $itemId = Database::insert('order_items', [
                    'order_id'          => $orderId,
                    'product_id'        => $line['product_id'],
                    'product_name'      => $line['product_name'],
                    'product_reference' => $line['product_reference'],
                    'brand_name'        => $line['brand_name'],
                    'unit_price'        => $line['unit_price'],
                    'quantity'          => $line['quantity'],
                    'line_total'        => $line['line_total'],
                    'selected_options'  => $line['selected_options'],
                ]);
                $reservationLines[] = [
                    'order_item_id' => $itemId,
                    'product_id'    => $line['product_id'],
                    'quantity'      => $line['quantity'],
                ];
            }

            InventoryService::reserveForOrder($orderId, $reservationLines, $expiresAt);

            Database::insert('order_status_history', [
                'order_id'    => $orderId,
                'from_status' => null,
                'to_status'   => 'awaiting_payment',
                'note'        => 'Order placed; stock reserved for ' . (int) Config::get('reservation_days', 7) . ' days.',
            ]);

            if ($fulfilment === 'delivery') {
                Database::insert('deliveries', [
                    'order_id'       => $orderId,
                    'status'         => 'pending',
                    'recipient_name' => $payload['customer_name'],
                    'address'        => $payload['delivery_address'] ?? null,
                    'city'           => $payload['delivery_city'] ?? null,
                    'state'          => $payload['delivery_state'] ?? null,
                    'fee'            => $deliveryFee,
                    'timeframe'      => $timeframe,
                ]);
            }

            return [
                'order_id'   => $orderId,
                'reference'  => $reference,
                'expires_at' => $expiresAt,
                'total'      => $total,
            ];
        });
    }

    /**
     * Expire stale reservations. Idempotent and safe to run concurrently.
     *
     * @return array{orders:int,reservations:int}
     */
    public static function expireStaleReservations(): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = Database::all(
            "SELECT DISTINCT o.id
             FROM orders o
             JOIN stock_reservations r ON r.order_id = o.id AND r.status = 'active'
             WHERE o.reservation_expires_at IS NOT NULL
               AND o.reservation_expires_at < ?
               AND o.status IN ('awaiting_payment','processing','paid')
             ORDER BY o.id ASC",
            [$now]
        );

        $orders = 0;
        $releasedTotal = 0;
        foreach ($rows as $row) {
            $orderId = (int) $row['id'];
            $released = Database::transaction(function () use ($orderId): int {
                // Re-check inside the transaction with a lock.
                $order = Database::first('SELECT * FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($order === null) {
                    return 0;
                }
                if ((int) $order['stock_released'] === 1) {
                    return 0; // Already released.
                }
                $stillActive = (int) Database::scalar(
                    "SELECT COUNT(*) FROM stock_reservations WHERE order_id = ? AND status = 'active' AND expires_at < NOW()",
                    [$orderId]
                );
                if ($stillActive === 0) {
                    return 0;
                }

                $released = InventoryService::releaseOrderReservations($orderId, 'Reservation expired after ' . (int) Config::get('reservation_days', 7) . ' days', 'expired');

                Database::update('orders', [
                    'status'         => 'expired',
                    'stock_released' => 1,
                    'cancel_reason'  => 'Reservation expired (unpaid).',
                ], ['id' => $orderId]);

                Database::insert('order_status_history', [
                    'order_id'    => $orderId,
                    'from_status' => (string) $order['status'],
                    'to_status'   => 'expired',
                    'note'        => 'Reservation expired; stock released.',
                ]);
                return $released;
            });

            if ($released > 0) {
                $orders++;
                $releasedTotal += $released;
                self::notifyOrderExpired($orderId);
            }
        }

        return ['orders' => $orders, 'reservations' => $releasedTotal];
    }

    public static function cancel(int $orderId, string $reason, ?int $adminId = null): bool
    {
        return (bool) Database::transaction(function () use ($orderId, $reason, $adminId): bool {
            $order = Database::first('SELECT * FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($order === null) {
                return false;
            }
            if (in_array($order['status'], ['cancelled', 'expired'], true)) {
                return false;
            }

            if ((int) $order['stock_released'] === 0) {
                InventoryService::releaseOrderReservations($orderId, $reason, 'released');
            }

            Database::update('orders', [
                'status'         => 'cancelled',
                'stock_released' => 1,
                'cancel_reason'  => $reason,
            ], ['id' => $orderId]);

            Database::insert('order_status_history', [
                'order_id'    => $orderId,
                'from_status' => (string) $order['status'],
                'to_status'   => 'cancelled',
                'note'        => $reason,
                'admin_id'    => $adminId,
            ]);
            return true;
        });
    }

    private static function upsertCustomer(array $payload): int
    {
        $phone = (string) $payload['customer_phone'];
        $existing = Database::first('SELECT id FROM customers WHERE phone = ? LIMIT 1', [$phone]);
        $data = [
            'full_name' => (string) $payload['customer_name'],
            'phone'     => $phone,
            'email'     => $payload['customer_email'] ?? null,
            'address'   => $payload['delivery_address'] ?? null,
            'city'      => $payload['delivery_city'] ?? null,
            'state'     => $payload['delivery_state'] ?? null,
        ];
        if ($existing) {
            Database::update('customers', $data, ['id' => $existing['id']]);
            return (int) $existing['id'];
        }
        return Database::insert('customers', $data);
    }

    private static function brandName(int $brandId): ?string
    {
        if ($brandId <= 0) {
            return null;
        }
        $name = Database::scalar('SELECT name FROM brands WHERE id = ?', [$brandId]);
        return $name === null ? null : (string) $name;
    }

    private static function uniqueReference(): string
    {
        do {
            $reference = 'AHO-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while ((int) Database::scalar('SELECT COUNT(*) FROM orders WHERE order_reference = ?', [$reference]) > 0);
        return $reference;
    }

    private static function notifyOrderExpired(int $orderId): void
    {
        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null || empty($order['customer_email'])) {
            return;
        }
        try {
            $html = View::render('emails/reservation_expired', ['order' => $order]);
            Mailer::send((string) $order['customer_email'], 'Reservation expired - ' . $order['order_reference'], $html);
        } catch (\Throwable $e) {
            Logger::warning('Expiry notification failed: ' . $e->getMessage());
        }
    }
}
