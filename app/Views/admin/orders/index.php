<?php
/**
 * Admin: orders list.
 *
 * @var list<array> $orders
 * @var array $filters
 * @var list<string> $statuses
 * @var array $result
 */
use App\Core\Str;

$query = array_filter([
    'q' => $filters['q'] ?: null, 'status' => $filters['status'] ?: null,
    'payment' => $filters['payment'] ?: null, 'from' => $filters['from'] ?: null, 'to' => $filters['to'] ?: null,
], static fn ($v) => $v !== null && $v !== '');
?>
<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/orders')) ?>" style="margin:0">
            <div class="form-group"><input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Reference, name or phone"></div>
            <div class="form-group">
                <select class="form-control" name="status" data-auto-submit>
                    <option value="">Any status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" name="payment" data-auto-submit>
                    <option value="">Any payment</option>
                    <option value="unpaid" <?= $filters['payment'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="partial" <?= $filters['payment'] === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid" <?= $filters['payment'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
            <div class="form-group"><input class="form-control" type="date" name="from" value="<?= e($filters['from']) ?>" aria-label="From date"></div>
            <div class="form-group"><input class="form-control" type="date" name="to" value="<?= e($filters['to']) ?>" aria-label="To date"></div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($query): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('invent/orders')) ?>">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body tight">
        <?php if ($orders): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Customer</th><th>Fulfilment</th><th class="num">Total</th><th class="num">Balance</th><th>Payment</th><th>Status</th><th>Placed</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><a href="<?= e(url('invent/orders/' . $order['id'])) ?>"><strong><?= e($order['order_reference']) ?></strong></a><div class="muted" style="font-size:var(--fs-xs)"><?= (int) $order['item_count'] ?> item(s)</div></td>
                                <td><?= e(Str::excerpt((string) $order['customer_name'], 24)) ?><div class="muted" style="font-size:var(--fs-xs)"><?= e($order['customer_phone']) ?></div></td>
                                <td><?= e(ucfirst((string) $order['fulfilment_method'])) ?></td>
                                <td class="num"><?= e(money($order['total'])) ?></td>
                                <td class="num"><?= e(money($order['balance'])) ?></td>
                                <td><span class="pill pill-<?= e((string) $order['payment_status']) ?>"><?= e(ucfirst((string) $order['payment_status'])) ?></span></td>
                                <td><span class="pill pill-<?= e((string) $order['status']) ?>"><?= e(ucwords(str_replace('_', ' ', (string) $order['status']))) ?></span></td>
                                <td><?= e(date('d M Y', strtotime((string) $order['created_at']))) ?></td>
                                <td><a class="btn btn-outline btn-sm" href="<?= e(url('invent/orders/' . $order['id'])) ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No orders found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
