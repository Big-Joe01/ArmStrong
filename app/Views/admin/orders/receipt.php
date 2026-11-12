<?php
/**
 * Admin: printable order receipt (standalone document).
 *
 * @var array $order
 * @var list<array> $items
 * @var list<array> $payments
 */
use App\Core\Settings;
use App\Core\Config;

$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
?>
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt <?= e($order['order_reference']) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1B2430; margin: 0; padding: 32px; font-size: 14px; }
        .wrap { max-width: 760px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid #507EEF; padding-bottom: 16px; margin-bottom: 24px; }
        .head h1 { margin: 0; font-size: 20px; color: #507EEF; }
        .muted { color: #5B6879; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { text-align: left; border-bottom: 1px solid #DDE3EA; padding: 8px 0; font-size: 12px; text-transform: uppercase; color: #5B6879; }
        td { padding: 8px 0; border-bottom: 1px solid #EEF1F5; }
        .num { text-align: right; }
        .totals td { border: 0; padding: 4px 0; }
        .totals .grand { border-top: 1px solid #DDE3EA; font-weight: bold; padding-top: 8px; }
        .grid2 { display: flex; gap: 32px; margin-bottom: 16px; }
        .grid2 > div { flex: 1; }
        .actions { margin-top: 24px; text-align: center; }
        .actions button { padding: 10px 20px; background: #507EEF; color: #fff; border: 0; border-radius: 6px; cursor: pointer; font-size: 14px; }
        @media print { .actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <h1><?= e($company) ?></h1>
            <div class="muted"><?= e(Settings::get('contact_address', '')) ?></div>
            <div class="muted"><?= e(Settings::get('contact_phone', '')) ?> <?= Settings::get('contact_email') ? ' · ' . e(Settings::get('contact_email')) : '' ?></div>
        </div>
        <div style="text-align:right">
            <div style="font-size:16px;font-weight:bold">ORDER RECEIPT</div>
            <div class="muted"><?= e($order['order_reference']) ?></div>
            <div class="muted"><?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?></div>
        </div>
    </div>

    <div class="grid2">
        <div>
            <div class="muted">CUSTOMER</div>
            <strong><?= e($order['customer_name']) ?></strong><br>
            <?= e($order['customer_phone']) ?><br>
            <?php if ($order['customer_email']): ?><?= e($order['customer_email']) ?><?php endif; ?>
        </div>
        <div>
            <div class="muted">FULFILMENT</div>
            <strong><?= e(ucfirst((string) $order['fulfilment_method'])) ?></strong>
            <?php if ($order['fulfilment_method'] === 'delivery'): ?>
                <br><?= e((string) $order['delivery_timeframe']) ?>
                <br><?= e((string) $order['delivery_address']) ?><?= $order['delivery_city'] ? ', ' . e($order['delivery_city']) : '' ?><?= $order['delivery_state'] ? ', ' . e($order['delivery_state']) : '' ?>
            <?php endif; ?>
        </div>
    </div>

    <table>
        <thead><tr><th>Item</th><th>Options</th><th class="num">Unit</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['product_name']) ?><div class="muted"><?= e($item['product_reference']) ?></div></td>
                    <td>
                        <?php
                        $opts = [];
                        foreach (($item['selected_options'] ?? []) as $k => $v) {
                            if ($v) { $opts[] = ucfirst((string) $k) . ': ' . $v; }
                        }
                        echo e(implode(', ', $opts)) ?: '—';
                        ?>
                    </td>
                    <td class="num"><?= e(money($item['unit_price'])) ?></td>
                    <td class="num"><?= (int) $item['quantity'] ?></td>
                    <td class="num"><?= e(money($item['line_total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals" style="width:320px;margin-left:auto">
        <tr><td>Subtotal</td><td class="num"><?= e(money($order['subtotal'])) ?></td></tr>
        <tr><td>Delivery fee</td><td class="num"><?= e(money($order['delivery_fee'])) ?></td></tr>
        <tr class="grand"><td>Total</td><td class="num"><?= e(money($order['total'])) ?></td></tr>
        <tr><td>Amount paid</td><td class="num"><?= e(money($order['amount_paid'])) ?></td></tr>
        <tr><td><strong>Balance due</strong></td><td class="num"><strong><?= e(money($order['balance'])) ?></strong></td></tr>
    </table>

    <?php if ($payments): ?>
        <div style="margin-top:16px">
            <div class="muted">PAYMENTS RECORDED</div>
            <table>
                <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th class="num">Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= e(date('d M Y', strtotime((string) $payment['paid_at']))) ?></td>
                            <td><?= e(ucwords(str_replace('_', ' ', (string) $payment['method']))) ?></td>
                            <td><?= e((string) $payment['reference']) ?></td>
                            <td class="num"><?= e(money($payment['amount'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="muted" style="margin-top:24px">
        Status: <?= e(ucwords(str_replace('_', ' ', (string) $order['status']))) ?> ·
        Payment: <?= e(ucfirst((string) $order['payment_status'])) ?>
        <?php if ($order['reservation_expires_at']): ?>
            · Reservation expires <?= e(date('d M Y H:i', strtotime((string) $order['reservation_expires_at']))) ?>
        <?php endif; ?>
    </div>

    <div class="actions">
        <button type="button" onclick="window.print()">Print receipt</button>
    </div>
</div>
</body>
</html>
