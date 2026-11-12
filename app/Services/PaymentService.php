<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Manual payment recording (no gateway integration by design).
 * Guards against accidental duplicate records.
 */
final class PaymentService
{
    /**
     * Record a payment against an online order.
     *
     * @return array{ok:bool,message:string}
     */
    public static function recordForOrder(
        int $orderId,
        float $amount,
        string $method,
        ?string $reference,
        ?string $paidAt,
        ?string $note,
        ?int $adminId
    ): array {
        if ($amount <= 0) {
            return ['ok' => false, 'message' => 'Payment amount must be greater than zero.'];
        }

        return (bool) Database::transaction(function () use ($orderId, $amount, $method, $reference, $paidAt, $note, $adminId): array {
            $order = Database::first('SELECT * FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($order === null) {
                return ['ok' => false, 'message' => 'Order not found.'];
            }

            // Duplicate guard: same order + amount + reference recorded already.
            if ($reference !== null && $reference !== '') {
                $dupe = Database::first(
                    'SELECT id FROM payments WHERE order_id = ? AND reference = ? AND amount = ?',
                    [$orderId, $reference, $amount]
                );
                if ($dupe) {
                    return ['ok' => false, 'message' => 'A payment with this reference has already been recorded.'];
                }
            }

            $balance = (float) $order['balance'];
            if ($amount > $balance + 0.001) {
                return ['ok' => false, 'message' => sprintf('Amount exceeds the outstanding balance of %s.', number_format($balance, 2))];
            }

            Database::insert('payments', [
                'order_id'  => $orderId,
                'amount'    => round($amount, 2),
                'method'    => in_array($method, ['cash', 'bank_transfer', 'pos', 'other'], true) ? $method : 'other',
                'reference' => $reference,
                'paid_at'   => $paidAt !== null && $paidAt !== '' ? $paidAt : date('Y-m-d H:i:s'),
                'note'      => $note,
                'admin_id'  => $adminId,
            ]);

            $newPaid    = round((float) $order['amount_paid'] + $amount, 2);
            $newBalance = round((float) $order['total'] - $newPaid, 2);
            if ($newBalance < 0) {
                $newBalance = 0.0;
            }
            $paymentStatus = $newBalance <= 0.001 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');

            // An order is never marked paid by the customer; only here.
            $newStatus = (string) $order['status'];
            if ($paymentStatus === 'paid' && in_array($newStatus, ['awaiting_payment'], true)) {
                $newStatus = 'paid';
            }

            Database::update('orders', [
                'amount_paid'    => $newPaid,
                'balance'        => $newBalance,
                'payment_status' => $paymentStatus,
                'status'         => $newStatus,
            ], ['id' => $orderId]);

            if ($newStatus !== (string) $order['status']) {
                Database::insert('order_status_history', [
                    'order_id'    => $orderId,
                    'from_status' => (string) $order['status'],
                    'to_status'   => $newStatus,
                    'note'        => 'Payment recorded (' . number_format($amount, 2) . ').',
                    'admin_id'    => $adminId,
                ]);
            }

            return ['ok' => true, 'message' => 'Payment recorded.'];
        });
    }

    /** @return list<array<string,mixed>> */
    public static function forOrder(int $orderId): array
    {
        return Database::all(
            'SELECT p.*, a.name AS admin_name
             FROM payments p
             LEFT JOIN administrators a ON a.id = p.admin_id
             WHERE p.order_id = ?
             ORDER BY p.paid_at ASC, p.id ASC',
            [$orderId]
        );
    }
}
