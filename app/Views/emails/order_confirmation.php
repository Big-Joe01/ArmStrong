<?php
/**
 * Order confirmation email (HTML).
 *
 * @var array $order
 * @var list<array> $items
 * @var int $reservationDays
 */
use App\Core\Settings;

$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
?>
<div style="font-family:Arial,Helvetica,sans-serif;color:#1B2430;line-height:1.6;max-width:600px;margin:0 auto">
    <div style="background:#1F4E79;color:#fff;padding:20px 24px">
        <h1 style="margin:0;font-size:20px"><?= e($company) ?></h1>
        <div style="font-size:12px;color:#C9D4E1;letter-spacing:.08em;text-transform:uppercase">Order confirmation</div>
    </div>
    <div style="padding:24px">
        <p>Dear <?= e($order['customer_name']) ?>,</p>
        <p>Thank you for your order. We have received it and reserved your items for <strong><?= (int) $reservationDays ?> days</strong>.</p>

        <p style="font-size:15px"><strong>Order reference:</strong> <?= e($order['order_reference']) ?></p>

        <table style="width:100%;border-collapse:collapse;margin:16px 0">
            <thead>
                <tr>
                    <th style="text-align:left;border-bottom:1px solid #DDE3EA;padding:8px 0;font-size:12px;text-transform:uppercase;color:#5B6879">Item</th>
                    <th style="text-align:right;border-bottom:1px solid #DDE3EA;padding:8px 0;font-size:12px;text-transform:uppercase;color:#5B6879">Qty</th>
                    <th style="text-align:right;border-bottom:1px solid #DDE3EA;padding:8px 0;font-size:12px;text-transform:uppercase;color:#5B6879">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="border-bottom:1px solid #EEF1F5;padding:8px 0">
                            <?= e($item['product_name']) ?><br>
                            <span style="font-size:12px;color:#5B6879">Ref: <?= e($item['product_reference']) ?></span>
                        </td>
                        <td style="border-bottom:1px solid #EEF1F5;padding:8px 0;text-align:right"><?= (int) $item['quantity'] ?></td>
                        <td style="border-bottom:1px solid #EEF1F5;padding:8px 0;text-align:right"><?= e(money($item['line_total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table style="width:100%;border-collapse:collapse">
            <tr><td style="padding:4px 0">Subtotal</td><td style="padding:4px 0;text-align:right"><?= e(money($order['subtotal'])) ?></td></tr>
            <tr><td style="padding:4px 0">Delivery fee</td><td style="padding:4px 0;text-align:right"><?= e(money($order['delivery_fee'])) ?></td></tr>
            <tr><td style="padding:8px 0;border-top:1px solid #DDE3EA;font-weight:bold">Total</td><td style="padding:8px 0;border-top:1px solid #DDE3EA;text-align:right;font-weight:bold"><?= e(money($order['total'])) ?></td></tr>
        </table>

        <p style="margin-top:20px"><strong>Fulfilment:</strong> <?= e(ucfirst((string) $order['fulfilment_method'])) ?>
            <?php if ($order['fulfilment_method'] === 'delivery' && $order['delivery_timeframe']): ?>
                (<?= e($order['delivery_timeframe']) ?>)
            <?php endif; ?>
        </p>

        <div style="background:#E8F0F8;border:1px solid #C6DBEF;border-radius:6px;padding:16px;margin-top:16px;font-size:14px">
            <strong>Payment</strong><br>
            Payment is completed through our approved in-person process. Please quote your order reference
            <strong><?= e($order['order_reference']) ?></strong> when paying.
        </div>

        <?php if ($phone = Settings::get('contact_phone')): ?>
            <p style="margin-top:16px;font-size:14px">Questions? Call us on <?= e($phone) ?>.</p>
        <?php endif; ?>
    </div>
    <div style="background:#F7F8FA;padding:16px 24px;font-size:12px;color:#5B6879">
        <?= e($company) ?>
        <?php if ($address = Settings::get('contact_address')): ?> &middot; <?= e($address) ?><?php endif; ?>
    </div>
</div>
