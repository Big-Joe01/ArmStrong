<?php
/**
 * Admin: walk-in sales list.
 *
 * @var list<array> $sales
 * @var array $filters
 * @var array $result
 */
?>
<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/walk-in-sales')) ?>" style="margin:0">
            <div class="form-group"><input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Reference, name or phone"></div>
            <button class="btn btn-primary btn-sm" type="submit">Search</button>
        </form>
        <a class="btn btn-accent btn-sm" href="<?= e(url('invent/walk-in-sales/create')) ?>">Record sale</a>
    </div>
    <div class="panel-body tight">
        <?php if ($sales): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Customer</th><th class="num">Items</th><th class="num">Total</th><th class="num">Paid</th><th>Payment</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><a href="<?= e(url('invent/walk-in-sales/' . $sale['id'])) ?>"><strong><?= e($sale['sale_reference']) ?></strong></a></td>
                                <td><?= e($sale['customer_name'] ?: 'Walk-in customer') ?><?php if ($sale['customer_phone']): ?><div class="muted" style="font-size:var(--fs-xs)"><?= e($sale['customer_phone']) ?></div><?php endif; ?></td>
                                <td class="num"><?= (int) $sale['item_count'] ?></td>
                                <td class="num"><?= e(money($sale['total'])) ?></td>
                                <td class="num"><?= e(money($sale['amount_paid'])) ?></td>
                                <td><span class="pill pill-<?= e((string) $sale['payment_status']) ?>"><?= e(ucfirst((string) $sale['payment_status'])) ?></span></td>
                                <td><?= e(date('d M Y H:i', strtotime((string) $sale['created_at']))) ?></td>
                                <td><a class="btn btn-outline btn-sm" href="<?= e(url('invent/walk-in-sales/' . $sale['id'])) ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No walk-in sales recorded yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
