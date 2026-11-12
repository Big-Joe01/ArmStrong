<?php
/**
 * Order confirmation page.
 *
 * @var array $order
 * @var list<array> $items
 * @var int $reservationDays
 */
use App\Core\Settings;

$statusLabels = [
    'awaiting_payment' => 'Awaiting payment',
    'paid'             => 'Paid',
    'processing'       => 'Processing',
    'ready_for_collection' => 'Ready for collection',
    'out_for_delivery' => 'Out for delivery',
    'delivered'        => 'Delivered',
    'collected'        => 'Collected',
    'cancelled'        => 'Cancelled',
    'expired'          => 'Expired',
];
?>
<section class="section-tight">
    <div class="container container-narrow">
        <div class="alert alert-success" role="status">
            <div>
                <strong>Thank you. Your order has been received.</strong><br>
                Your items are reserved for <?= (int) $reservationDays ?> days. Please keep your order reference for collection or delivery.
            </div>
        </div>

        <div class="card" style="padding:var(--sp-6)">
            <div class="flex-between flex-wrap mb-4">
                <div>
                    <span class="eyebrow">Order reference</span>
                    <h1 style="font-size:var(--fs-2xl);margin:0"><?= e($order['order_reference']) ?></h1>
                </div>
                <span class="badge <?= $order['status'] === 'expired' || $order['status'] === 'cancelled' ? 'badge-danger' : 'badge-warning' ?>">
                    <?= e($statusLabels[$order['status']] ?? ucfirst((string) $order['status'])) ?>
                </span>
            </div>

            <div class="grid grid-2" style="gap:var(--sp-5)">
                <div>
                    <h3 style="font-size:var(--fs-sm);text-transform:uppercase;letter-spacing:.06em;color:var(--c-text-muted)">Order details</h3>
                    <ul class="spec-list">
                        <li><span class="k">Order date</span><span class="v"><?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?></span></li>
                        <li><span class="k">Fulfilment</span><span class="v"><?= e(ucfirst((string) $order['fulfilment_method'])) ?></span></li>
                        <?php if ($order['fulfilment_method'] === 'delivery'): ?>
                            <li><span class="k">Timeframe</span><span class="v"><?= e((string) ($order['delivery_timeframe'] ?: '—')) ?></span></li>
                        <?php endif; ?>
                        <li><span class="k">Payment status</span><span class="v"><?= e(ucfirst((string) $order['payment_status'])) ?></span></li>
                        <li><span class="k">Reservation expires</span><span class="v"><?= e($order['reservation_expires_at'] ? date('d M Y, H:i', strtotime((string) $order['reservation_expires_at'])) : '—') ?></span></li>
                    </ul>
                </div>
                <div>
                    <h3 style="font-size:var(--fs-sm);text-transform:uppercase;letter-spacing:.06em;color:var(--c-text-muted)">Contact</h3>
                    <ul class="spec-list">
                        <li><span class="k">Name</span><span class="v"><?= e($order['customer_name']) ?></span></li>
                        <li><span class="k">Phone</span><span class="v"><?= e($order['customer_phone']) ?></span></li>
                        <?php if ($order['customer_email']): ?>
                            <li><span class="k">Email</span><span class="v"><?= e($order['customer_email']) ?></span></li>
                        <?php endif; ?>
                        <?php if ($order['fulfilment_method'] === 'delivery' && $order['delivery_address']): ?>
                            <li><span class="k">Delivery to</span><span class="v"><?= e($order['delivery_address']) ?><?= $order['delivery_city'] ? ', ' . e($order['delivery_city']) : '' ?><?= $order['delivery_state'] ? ', ' . e($order['delivery_state']) : '' ?></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <h3 style="font-size:var(--fs-md);margin-top:var(--sp-6)">Items</h3>
            <?php foreach ($items as $item): ?>
                <div class="order-line">
                    <div class="order-line-info">
                        <h4><?= e($item['product_name']) ?></h4>
                        <div class="muted" style="font-size:var(--fs-xs)">Ref: <?= e($item['product_reference']) ?><?= $item['brand_name'] ? ' · ' . e($item['brand_name']) : '' ?></div>
                        <?php if (!empty($item['selected_options'])): ?>
                            <div class="muted" style="font-size:var(--fs-xs)">
                                <?php
                                $parts = [];
                                foreach ($item['selected_options'] as $k => $v) {
                                    if ($v) { $parts[] = ucfirst((string) $k) . ': ' . e((string) $v); }
                                }
                                echo implode(' · ', $parts);
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="muted" style="font-size:var(--fs-xs)"><?= e(money($item['unit_price'])) ?> × <?= (int) $item['quantity'] ?></div>
                    </div>
                    <div style="text-align:right;font-weight:600"><?= e(money($item['line_total'])) ?></div>
                </div>
            <?php endforeach; ?>

            <div class="summary-box" style="margin-top:var(--sp-5)">
                <div class="summary-line"><span>Subtotal</span><span><?= e(money($order['subtotal'])) ?></span></div>
                <div class="summary-line"><span>Delivery fee</span><span><?= e(money($order['delivery_fee'])) ?></span></div>
                <div class="summary-line summary-total"><span>Total</span><span><?= e(money($order['total'])) ?></span></div>
                <div class="summary-line"><span>Amount paid</span><span><?= e(money($order['amount_paid'])) ?></span></div>
                <div class="summary-line"><span>Outstanding balance</span><span><strong><?= e(money($order['balance'])) ?></strong></span></div>
            </div>

            <div class="alert alert-info" style="margin-top:var(--sp-6)">
                <div>
                    <strong>Payment instructions</strong><br>
                    Payment is completed through our approved in-person process. Please quote your order reference
                    <strong><?= e($order['order_reference']) ?></strong> when paying. Your order will be marked as paid once our team records the payment.
                    <?php if ($contactPhone = Settings::get('contact_phone')): ?>
                        <br>Questions? Call <a href="tel:<?= e(preg_replace('/\s+/', '', $contactPhone)) ?>"><?= e($contactPhone) ?></a>.
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-wrap" style="gap:var(--sp-3);margin-top:var(--sp-6)">
                <a class="btn btn-primary btn-inline" href="<?= e(url('/building-materials')) ?>">Continue shopping</a>
                <a class="btn btn-outline btn-inline" href="<?= e(url('/contact')) ?>">Contact us about this order</a>
                <button class="btn btn-outline btn-inline no-print" type="button" data-print onclick="window.print()">Print this page</button>
            </div>
        </div>
    </div>
</section>
