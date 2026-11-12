<?php
/**
 * Admin: walk-in sale receipt (standalone, printable).
 *
 * @var array $sale
 * @var list<array> $items
 */
use App\Core\Settings;

$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
?>
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt <?= e($sale['sale_reference']) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1B2430; margin: 0; padding: 32px; font-size: 14px; }
        .wrap { max-width: 680px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid #507EEF; padding-bottom: 16px; margin-bottom: 24px; }
        .head h1 { margin: 0; font-size: 20px; color: #507EEF; }
        .muted { color: #5B6879; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { text-align: left; border-bottom: 1px solid #DDE3EA; padding: 8px 0; font-size: 12px; text-transform: uppercase; color: #5B6879; }
        td { padding: 8px 0; border-bottom: 1px solid #EEF1F5; }
        .num { text-align: right; }
        .totals td { border: 0; padding: 4px 0; }
        .totals .grand { border-top: 1px solid #DDE3EA; font-weight: bold; padding-top: 8px; }
        .actions { margin-top: 24px; text-align: center; }
        .actions button { padding: 10px 20px; background: #507EEF; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        @media print { .actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <h1><?= e($company) ?></h1>
            <div class="muted"><?= e(Settings::get('contact_address', '')) ?></div>
            <div class="muted"><?= e(Settings::get('contact_phone', '')) ?></div>
        </div>
        <div style="text-align:right">
            <div style="font-size:16px;font-weight:bold">SALES RECEIPT</div>
            <div class="muted"><?= e($sale['sale_reference']) ?></div>
            <div class="muted"><?= e(date('d M Y, H:i', strtotime((string) $sale['created_at']))) ?></div>
        </div>
    </div>

    <?php if ($sale['customer_name'] || $sale['customer_phone']): ?>
        <div class="muted">CUSTOMER</div>
        <div style="margin-bottom:16px">
            <?= e($sale['customer_name'] ?: 'Walk-in customer') ?><?php if ($sale['customer_phone']): ?> · <?= e($sale['customer_phone']) ?><?php endif; ?>
        </div>
    <?php endif; ?>

    <table>
        <thead><tr><th>Item</th><th class="num">Unit</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['product_name']) ?><div class="muted"><?= e($item['product_reference']) ?></div></td>
                    <td class="num"><?= e(money($item['unit_price'])) ?></td>
                    <td class="num"><?= (int) $item['quantity'] ?></td>
                    <td class="num"><?= e(money($item['line_total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals" style="width:300px;margin-left:auto">
        <tr class="grand"><td>Total</td><td class="num"><?= e(money($sale['total'])) ?></td></tr>
        <tr><td>Paid</td><td class="num"><?= e(money($sale['amount_paid'])) ?></td></tr>
        <tr><td><strong>Balance</strong></td><td class="num"><strong><?= e(money($sale['balance'])) ?></strong></td></tr>
    </table>

    <div class="muted">Served by <?= e($sale['admin_name'] ?? 'Administrator') ?>. Thank you for your business.</div>

    <div class="actions"><button type="button" onclick="window.print()">Print receipt</button></div>
</div>
</body>
</html>
