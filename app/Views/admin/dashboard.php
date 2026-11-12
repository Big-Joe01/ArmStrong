<?php
/**
 * Administration dashboard.
 *
 * @var array $stats
 * @var list<array> $recentOrders
 * @var list<array> $recentEnquiries
 * @var list<array> $recentMessages
 * @var list<array> $lowStock
 * @var list<array> $expiringSoon
 */
use App\Core\Str;
?>
<div class="stat-grid">
    <div class="stat-card info">
        <div class="label">Total products</div>
        <div class="value"><?= (int) $stats['products_total'] ?></div>
        <div class="meta"><?= (int) $stats['products_published'] ?> published</div>
    </div>
    <div class="stat-card danger">
        <div class="label">Out of stock</div>
        <div class="value"><?= (int) $stats['products_out'] ?></div>
        <div class="meta"><?= (int) $stats['products_low'] ?> low stock</div>
    </div>
    <div class="stat-card accent">
        <div class="label">Active reservations</div>
        <div class="value"><?= (int) $stats['reservations_active'] ?></div>
        <div class="meta">Holding stock now</div>
    </div>
    <div class="stat-card warning">
        <div class="label">Pending orders</div>
        <div class="value"><?= (int) $stats['orders_pending'] ?></div>
        <div class="meta"><?= (int) $stats['orders_unpaid'] ?> unpaid</div>
    </div>
    <div class="stat-card success">
        <div class="label">Paid orders</div>
        <div class="value"><?= (int) $stats['orders_paid'] ?></div>
        <div class="meta"><?= (int) $stats['orders_expired'] ?> expired</div>
    </div>
    <div class="stat-card info">
        <div class="label">Properties</div>
        <div class="value"><?= (int) $stats['properties_total'] ?></div>
        <div class="meta"><?= (int) $stats['properties_available'] ?> available &amp; published</div>
    </div>
    <div class="stat-card accent">
        <div class="label">Property enquiries</div>
        <div class="value"><?= (int) $stats['enquiries_new'] ?></div>
        <div class="meta">New and unread</div>
    </div>
    <div class="stat-card warning">
        <div class="label">Contact messages</div>
        <div class="value"><?= (int) $stats['messages_new'] ?></div>
        <div class="meta">Unread</div>
    </div>
    <div class="stat-card warning">
        <div class="label">Reviews awaiting approval</div>
        <div class="value"><?= (int) $stats['reviews_pending'] ?></div>
        <div class="meta">Pending moderation</div>
    </div>
</div>

<div class="grid grid-2" style="gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head">
            <h2>Recent orders</h2>
            <a class="btn btn-outline btn-sm" href="<?= e(url('invent/orders')) ?>">View all</a>
        </div>
        <div class="panel-body tight">
            <?php if ($recentOrders): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table" style="min-width:0">
                        <thead>
                            <tr><th>Reference</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><a href="<?= e(url('invent/orders/' . $order['id'])) ?>"><?= e($order['order_reference']) ?></a></td>
                                    <td><?= e(Str::excerpt((string) $order['customer_name'], 22)) ?></td>
                                    <td><span class="pill pill-<?= e((string) $order['status']) ?>"><?= e(ucwords(str_replace('_', ' ', (string) $order['status']))) ?></span></td>
                                    <td class="num"><?= e(money($order['total'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No orders yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Reservations expiring soon</h2></div>
        <div class="panel-body tight">
            <?php if ($expiringSoon): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table" style="min-width:0">
                        <thead><tr><th>Order</th><th>Customer</th><th>Expires</th></tr></thead>
                        <tbody>
                            <?php foreach ($expiringSoon as $order): ?>
                                <tr>
                                    <td><a href="<?= e(url('invent/orders/' . $order['id'])) ?>"><?= e($order['order_reference']) ?></a></td>
                                    <td><?= e(Str::excerpt((string) $order['customer_name'], 20)) ?></td>
                                    <td><?= e(date('d M, H:i', strtotime((string) $order['reservation_expires_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No reservations expiring soon.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Low stock</h2>
            <a class="btn btn-outline btn-sm" href="<?= e(url('invent/inventory')) ?>">Manage inventory</a>
        </div>
        <div class="panel-body tight">
            <?php if ($lowStock): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table" style="min-width:0">
                        <thead><tr><th>Product</th><th class="num">Available</th><th class="num">Threshold</th></tr></thead>
                        <tbody>
                            <?php foreach ($lowStock as $product): ?>
                                <?php $avail = (int) $product['stock_on_hand'] - (int) $product['reserved_qty']; ?>
                                <tr>
                                    <td><a href="<?= e(url('invent/products/' . $product['id'] . '/edit')) ?>"><?= e(Str::excerpt((string) $product['name'], 30)) ?></a></td>
                                    <td class="num"><span class="<?= $avail <= 0 ? 'stock-out' : 'stock-low' ?>"><?= $avail ?></span></td>
                                    <td class="num"><?= (int) $product['low_stock_threshold'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">All products are above their low-stock threshold.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Latest enquiries &amp; messages</h2>
        </div>
        <div class="panel-body tight">
            <?php if ($recentEnquiries || $recentMessages): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table" style="min-width:0">
                        <thead><tr><th>Type</th><th>From</th><th>Received</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentEnquiries as $enquiry): ?>
                                <tr>
                                    <td><span class="pill pill-new">Enquiry</span></td>
                                    <td><a href="<?= e(url('invent/enquiries/' . $enquiry['id'])) ?>"><?= e(Str::excerpt((string) $enquiry['full_name'], 22)) ?></a></td>
                                    <td><?= e(Str::timeAgo((string) $enquiry['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($recentMessages as $message): ?>
                                <tr>
                                    <td><span class="pill pill-<?= e((string) $message['status']) ?>">Message</span></td>
                                    <td><a href="<?= e(url('invent/messages/' . $message['id'])) ?>"><?= e(Str::excerpt((string) $message['full_name'], 22)) ?></a></td>
                                    <td><?= e(Str::timeAgo((string) $message['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No enquiries or messages yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
