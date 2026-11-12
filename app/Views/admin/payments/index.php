<?php
/**
 * Admin: payments list + record payment.
 *
 * @var list<array> $payments
 * @var array $summary
 * @var list<array> $openOrders
 * @var array $filters
 * @var array $result
 */
$query = array_filter([
    'q' => $filters['q'] ?: null, 'method' => $filters['method'] ?: null,
    'from' => $filters['from'] ?: null, 'to' => $filters['to'] ?: null,
], static fn ($v) => $v !== null && $v !== '');
?>
<div class="stat-grid">
    <div class="stat-card success"><div class="label">Collected today</div><div class="value"><?= e(money($summary['today'])) ?></div></div>
    <div class="stat-card info"><div class="label">This month</div><div class="value"><?= e(money($summary['month'])) ?></div></div>
    <div class="stat-card accent"><div class="label">All time</div><div class="value"><?= e(money($summary['total'])) ?></div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Record a payment</h2>
        <span class="muted" style="font-size:var(--fs-xs)">No gateway — record payments received in person</span>
    </div>
    <div class="panel-body">
        <?php if ($openOrders): ?>
            <form method="post" action="<?= e(url('invent/payments')) ?>">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="order_id">Order <span class="req">*</span></label>
                        <select class="form-control" id="order_id" name="order_id" required>
                            <option value="">Select an order with a balance</option>
                            <?php foreach ($openOrders as $order): ?>
                                <option value="<?= (int) $order['id'] ?>">
                                    <?= e($order['order_reference']) ?> — <?= e($order['customer_name']) ?> (balance <?= e(money($order['balance'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label" for="amount">Amount (₦) <span class="req">*</span></label><input class="form-control" type="number" id="amount" name="amount" min="0.01" step="0.01" required></div>
                    <div class="form-group">
                        <label class="form-label" for="method">Method</label>
                        <select class="form-control" id="method" name="method">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="pos">POS</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label" for="reference">Reference</label><input class="form-control" type="text" id="reference" name="reference" maxlength="120"></div>
                    <div class="form-group"><label class="form-label" for="paid_at">Payment date</label><input class="form-control" type="date" id="paid_at" name="paid_at" value="<?= e(date('Y-m-d')) ?>"></div>
                    <div class="form-group"><label class="form-label" for="note">Note</label><input class="form-control" type="text" id="note" name="note" maxlength="255"></div>
                </div>
                <button class="btn btn-primary" type="submit">Record payment</button>
            </form>
        <?php else: ?>
            <div class="table-empty">No orders currently have an outstanding balance.</div>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/payments')) ?>" style="margin:0">
            <div class="form-group"><input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Order ref, name or reference"></div>
            <div class="form-group">
                <select class="form-control" name="method" data-auto-submit>
                    <option value="">Any method</option>
                    <option value="cash" <?= $filters['method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank_transfer" <?= $filters['method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank transfer</option>
                    <option value="pos" <?= $filters['method'] === 'pos' ? 'selected' : '' ?>>POS</option>
                    <option value="other" <?= $filters['method'] === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group"><input class="form-control" type="date" name="from" value="<?= e($filters['from']) ?>" aria-label="From"></div>
            <div class="form-group"><input class="form-control" type="date" name="to" value="<?= e($filters['to']) ?>" aria-label="To"></div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($query): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('invent/payments')) ?>">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body tight">
        <?php if ($payments): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Date</th><th>Order</th><th>Customer</th><th class="num">Amount</th><th>Method</th><th>Reference</th><th>Recorded by</th></tr></thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= e(date('d M Y', strtotime((string) $payment['paid_at']))) ?></td>
                                <td><?php if ($payment['order_reference']): ?><a href="<?= e(url('invent/orders/' . $payment['order_id'])) ?>"><?= e($payment['order_reference']) ?></a><?php else: ?><span class="muted">Walk-in</span><?php endif; ?></td>
                                <td><?= e((string) $payment['customer_name']) ?></td>
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
            <div class="table-empty">No payments recorded.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
