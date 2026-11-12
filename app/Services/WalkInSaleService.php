<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Physical (counter) sales recorded by the administrator.
 * Deducts inventory immediately, inside one transaction.
 */
final class WalkInSaleService
{
    /**
     * @param list<array{product_id:int,quantity:int}> $items
     * @param array<string,mixed> $meta  customer_name, customer_phone, note, amount_paid, method, reference
     * @return array{sale_id:int,reference:string,total:float}
     */
    public static function create(array $items, array $meta, ?int $adminId): array
    {
        if ($items === []) {
            throw new \RuntimeException('Add at least one product to the sale.');
        }

        return Database::transaction(function () use ($items, $meta, $adminId): array {
            $subtotal = 0.00;
            $prepared = [];
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity  = (int) ($item['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }
                $product = Database::first('SELECT * FROM products WHERE id = ?', [$productId]);
                if ($product === null) {
                    throw new \RuntimeException('A selected product no longer exists.');
                }
                $lineTotal = round((float) $product['price'] * $quantity, 2);
                $subtotal += $lineTotal;
                $prepared[] = [
                    'product_id' => $productId,
                    'name'       => (string) $product['name'],
                    'reference'  => (string) $product['reference'],
                    'unit_price' => (float) $product['price'],
                    'quantity'   => $quantity,
                    'line_total' => $lineTotal,
                ];
            }
            if ($prepared === []) {
                throw new \RuntimeException('Add at least one product to the sale.');
            }

            $subtotal = round($subtotal, 2);
            $amountPaid = isset($meta['amount_paid']) && $meta['amount_paid'] !== ''
                ? round((float) $meta['amount_paid'], 2)
                : $subtotal;
            if ($amountPaid < 0) {
                $amountPaid = 0.0;
            }
            if ($amountPaid > $subtotal) {
                $amountPaid = $subtotal;
            }
            $balance = round($subtotal - $amountPaid, 2);
            $paymentStatus = $balance <= 0.001 ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid');

            $reference = self::uniqueReference();
            $saleId = Database::insert('walk_in_sales', [
                'sale_reference' => $reference,
                'customer_name'  => $meta['customer_name'] ?? null,
                'customer_phone' => $meta['customer_phone'] ?? null,
                'subtotal'       => $subtotal,
                'total'          => $subtotal,
                'amount_paid'    => $amountPaid,
                'balance'        => $balance,
                'payment_status' => $paymentStatus,
                'note'           => $meta['note'] ?? null,
                'admin_id'       => $adminId,
            ]);

            foreach ($prepared as $line) {
                Database::insert('walk_in_sale_items', [
                    'walk_in_sale_id'  => $saleId,
                    'product_id'       => $line['product_id'],
                    'product_name'     => $line['name'],
                    'product_reference'=> $line['reference'],
                    'unit_price'       => $line['unit_price'],
                    'quantity'         => $line['quantity'],
                    'line_total'       => $line['line_total'],
                ]);
                // Deducts stock; throws (rolling back the sale) when short.
                InventoryService::deductForSale($line['product_id'], $line['quantity'], 'walk_in_sale', $saleId, $adminId, 'Walk-in sale ' . $reference);
            }

            if ($amountPaid > 0) {
                Database::insert('payments', [
                    'walk_in_sale_id' => $saleId,
                    'amount'          => $amountPaid,
                    'method'          => in_array($meta['method'] ?? 'cash', ['cash', 'bank_transfer', 'pos', 'other'], true) ? $meta['method'] : 'cash',
                    'reference'       => $meta['reference'] ?? null,
                    'paid_at'         => date('Y-m-d H:i:s'),
                    'note'            => 'Walk-in sale payment',
                    'admin_id'        => $adminId,
                ]);
            }

            return ['sale_id' => $saleId, 'reference' => $reference, 'total' => $subtotal];
        });
    }

    private static function uniqueReference(): string
    {
        do {
            $reference = 'AHW-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while ((int) Database::scalar('SELECT COUNT(*) FROM walk_in_sales WHERE sale_reference = ?', [$reference]) > 0);
        return $reference;
    }
}
