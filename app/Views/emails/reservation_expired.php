<?php
/**
 * Reservation expired notification.
 *
 * @var array $order
 */
use App\Core\Settings;
?>
<div style="font-family:Arial,Helvetica,sans-serif;color:#1B2430;line-height:1.6;max-width:600px;margin:0 auto">
    <div style="background:#1E1B1C;color:#fff;padding:20px 24px">
        <h1 style="margin:0;font-size:20px">Reservation expired</h1>
    </div>
    <div style="padding:24px">
        <p>Dear <?= e($order['customer_name']) ?>,</p>
        <p>
            Your order <strong><?= e($order['order_reference']) ?></strong> was reserved but the reservation period has now ended.
            The reserved items have been returned to available stock.
        </p>
        <p>
            If you would still like these items, please place a new order or contact us and we will help you.
        </p>
        <?php if ($phone = Settings::get('contact_phone')): ?>
            <p style="font-size:14px">Call us on <?= e($phone) ?>.</p>
        <?php endif; ?>
    </div>
    <div style="background:#F7F8FA;padding:16px 24px;font-size:12px;color:#5B6879">
        <?= e(Settings::get('company_name', 'Armstrong Homes & Interior Limited')) ?>
    </div>
</div>
