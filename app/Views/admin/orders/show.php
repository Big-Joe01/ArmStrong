<?php
/**
 * Admin: order detail.
 *
 * @var array $order
 * @var list<array> $items
 * @var list<array> $reservations
 * @var list<array> $payments
 * @var list<array> $history
 * @var list<string> $statuses
 */
?>
<div class="flex-between flex-wrap mb-4">
    <div>
        <h2 style="margin:0"><?= e($order['order_reference']) ?></h2>
        <div class="muted">Placed <?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?> · <?= e(ucfirst((string) $order['source'])) ?></div>
    </div>
    <div class="flex" style="gap:var(--sp-2)">
        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/orders/' . $order['id'] . '/receipt')) ?>" target="_blank">Receipt</a>
        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/orders')) ?>">Back</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
    <div>
        <div class="panel">
            <div class="panel-head"><h2>Items</h2></div>
            <div class="panel-body tight">
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Options</th><th class="num">Unit</th><th class="num">Qty</th><th class="num">Line total</th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><strong><?= e($item['product_name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)"><?= e($item['product_reference']) ?><?= $item['brand_name'] ? ' · ' . e($item['brand_name']) : '' ?></div></td>
                                    <td>
                                        <?php if ($item['selected_options']): ?>
                                            <?php foreach ($item['selected_options'] as $k => $v): ?>
                                                <?php if ($v): ?><span class="badge"><?= e(ucfirst((string) $k)) ?>: <?= e((string) $v) ?></span><?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td class="num"><?= e(money($item['unit_price'])) ?></td>
                                    <td class="num"><?= (int) $item['quantity'] ?></td>
                                    <td class="num"><?= e(money($item['line_total'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-body" style="border-top:1px solid var(--c-border)">
                <div class="summary-line"><span>Subtotal</span><span><?= e(money($order['subtotal'])) ?></span></div>
                <div class="summary-line"><span>Delivery fee</span><span><?= e(money($order['delivery_fee'])) ?></span></div>
                <div class="summary-line summary-total"><span>Total</span><span><?= e(money($order['total'])) ?></span></div>
                <div class="summary-line"><span>Paid</span><span><?= e(money($order['amount_paid'])) ?></span></div>
                <div class="summary-line"><span>Balance</span><span><strong><?= e(money($order['balance'])) ?></strong></span></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Reservations</h2></div>
            <div class="panel-body tight">
                <?php if ($reservations): ?>
                    <div class="table-wrap" style="border:0;border-radius:0">
                        <table class="table">
                            <thead><tr><th class="num">Qty</th><th>Status</th><th>Expires</th><th>Released</th><th>Reason</th></tr></thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td class="num"><?= (int) $reservation['quantity'] ?></td>
                                        <td><span class="pill pill-<?= e((string) $reservation['status']) ?>"><?= e(ucfirst((string) $reservation['status'])) ?></span></td>
                                        <td><?= e(date('d M Y H:i', strtotime((string) $reservation['expires_at']))) ?></td>
                                        <td><?= $reservation['released_at'] ? e(date('d M Y H:i', strtotime((string) $reservation['released_at']))) : '—' ?></td>
                                        <td><?= e((string) $reservation['release_reason']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="table-empty">No reservations (stock may have been released).</div>
                <?php endif; ?>
            </div>
            <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'expired'): ?>
                <div class="panel-body" style="border-top:1px solid var(--c-border)">
                    <form method="post" action="<?= e(url('invent/orders/' . $order['id'] . '/release')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline btn-sm" type="submit" data-confirm="Release all reserved stock for this order? This cannot be undone.">Release reserved stock now</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Payments</h2></div>
            <div class="panel-body tight">
                <?php if ($payments): ?>
                    <div class="table-wrap" style="border:0;border-radius:0">
                        <table class="table">
                            <thead><tr><th>Date</th><th class="num">Amount</th><th>Method</th><th>Reference</th><th>Recorded by</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= e(date('d M Y', strtotime((string) $payment['paid_at']))) ?></td>
                                        <td class="num"><?= e(money($payment['amount'])) ?></td>
                                        <td><?= e(ucwords(str_replace('_', ' ', (string) $payment['method']))) ?></td>
                                        <td><?= e((string) $payment['reference']) ?></td>
                                        <td><?= e($payment['admin_name'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="table-empty">No payments recorded yet.</div>
                <?php endif; ?>
            </div>
            <?php if ((float) $order['balance'] > 0 && !in_array($order['status'], ['cancelled', 'expired'], true)): ?>
                <div class="panel-body" style="border-top:1px solid var(--c-border)">
                    <h3 style="font-size:var(--fs-sm)">Record a payment</h3>
                    <form method="post" action="<?= e(url('invent/payments')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">Amount (₦)</label><input class="form-control" type="number" name="amount" min="0.01" step="0.01" value="<?= e(number_format((float) $order['balance'], 2, '.', '')) ?>" required></div>
                            <div class="form-group">
                                <label class="form-label">Method</label>
                                <select class="form-control" name="method">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank transfer</option>
                                    <option value="pos">POS</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">Reference</label><input class="form-control" type="text" name="reference" maxlength="120"></div>
                            <div class="form-group"><label class="form-label">Payment date</label><input class="form-control" type="date" name="paid_at" value="<?= e(date('Y-m-d')) ?>"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Note</label><input class="form-control" type="text" name="note" maxlength="255"></div>
                        <button class="btn btn-primary" type="submit">Record payment</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Order history</h2></div>
            <div class="panel-body tight">
                <?php if ($history): ?>
                    <div class="table-wrap" style="border:0;border-radius:0">
                        <table class="table">
                            <thead><tr><th>When</th><th>Change</th><th>Note</th><th>By</th></tr></thead>
                            <tbody>
                                <?php foreach ($history as $entry): ?>
                                    <tr>
                                        <td><?= e(date('d M Y H:i', strtotime((string) $entry['created_at']))) ?></td>
                                        <td><?= e((string) ($entry['from_status'] ? ucwords(str_replace('_', ' ', $entry['from_status'])) . ' → ' : '')) ?><strong><?= e(ucwords(str_replace('_', ' ', (string) $entry['to_status']))) ?></strong></td>
                                        <td><?= e((string) $entry['note']) ?></td>
                                        <td><?= e($entry['admin_name'] ?? 'System') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="table-empty">No status changes recorded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <aside>
        <div class="panel">
            <div class="panel-head"><h2>Status</h2></div>
            <div class="panel-body">
                <div style="margin-bottom:var(--sp-4)">
                    <span class="pill pill-<?= e((string) $order['status']) ?>"><?= e(ucwords(str_replace('_', ' ', (string) $order['status']))) ?></span>
                    <span class="pill pill-<?= e((string) $order['payment_status']) ?>"><?= e(ucfirst((string) $order['payment_status'])) ?></span>
                </div>
                <form method="post" action="<?= e(url('invent/orders/' . $order['id'] . '/status')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="status">Update status</label>
                        <select class="form-control" id="status" name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">Choosing Cancelled or Expired releases reserved stock.</div>
                    </div>
                    <div class="form-group"><label class="form-label" for="note">Note</label><input class="form-control" type="text" id="note" name="note" maxlength="255"></div>
                    <button class="btn btn-primary btn-block btn-sm" type="submit">Update status</button>
                </form>
                <hr>
                <form method="post" action="<?= e(url('invent/orders/' . $order['id'] . '/cancel')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group"><label class="form-label" for="reason">Cancel reason</label><input class="form-control" type="text" id="reason" name="reason" maxlength="255" placeholder="Reason for cancellation"></div>
                    <button class="btn btn-danger btn-block btn-sm" type="submit" data-confirm="Cancel this order and release its stock?">Cancel order</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Customer</h2></div>
            <div class="panel-body">
                <ul class="spec-list">
                    <li><span class="k">Name</span><span class="v"><?= e($order['customer_name']) ?></span></li>
                    <li><span class="k">Phone</span><span class="v"><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $order['customer_phone'])) ?>"><?= e($order['customer_phone']) ?></a></span></li>
                    <?php if ($order['customer_email']): ?><li><span class="k">Email</span><span class="v"><?= e($order['customer_email']) ?></span></li><?php endif; ?>
                    <li><span class="k">Fulfilment</span><span class="v"><?= e(ucfirst((string) $order['fulfilment_method'])) ?></span></li>
                    <?php if ($order['fulfilment_method'] === 'delivery'): ?>
                        <?php if ($order['delivery_timeframe']): ?><li><span class="k">Timeframe</span><span class="v"><?= e($order['delivery_timeframe']) ?></span></li><?php endif; ?>
                        <?php if ($order['delivery_address']): ?><li><span class="k">Address</span><span class="v"><?= e($order['delivery_address']) ?><?= $order['delivery_city'] ? ', ' . e($order['delivery_city']) : '' ?><?= $order['delivery_state'] ? ', ' . e($order['delivery_state']) : '' ?></span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php if ($order['reservation_expires_at']): ?><li><span class="k">Reservation expires</span><span class="v"><?= e(date('d M Y H:i', strtotime((string) $order['reservation_expires_at']))) ?></span></li><?php endif; ?>
                </ul>
                <?php if ($order['customer_note']): ?>
                    <div class="alert alert-info" style="margin-bottom:0"><span><?= e($order['customer_note']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Internal note</h2></div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('invent/orders/' . $order['id'] . '/note')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group"><textarea class="form-control" name="admin_note" rows="3" placeholder="Visible to administrators only"><?= e((string) $order['admin_note']) ?></textarea></div>
                    <button class="btn btn-outline btn-block btn-sm" type="submit">Save note</button>
                </form>
            </div>
        </div>
    </aside>
</div>
