<?php
/**
 * Admin: inventory list.
 *
 * @var list<array> $products
 * @var array $filters
 * @var array $result
 * @var array $totals
 */
$query = array_filter(['q' => $filters['q'] ?: null, 'level' => $filters['level'] ?: null], static fn ($v) => $v !== null && $v !== '');
?>
<div class="stat-grid">
    <div class="stat-card info"><div class="label">Total stock on hand</div><div class="value"><?= (int) $totals['stock'] ?></div><div class="meta">units across all products</div></div>
    <div class="stat-card accent"><div class="label">Reserved</div><div class="value"><?= (int) $totals['reserved'] ?></div><div class="meta">held by active orders</div></div>
    <div class="stat-card success"><div class="label">Available</div><div class="value"><?= (int) $totals['stock'] - (int) $totals['reserved'] ?></div><div class="meta">free to order</div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/inventory')) ?>" style="margin:0">
            <div class="form-group"><input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Product name or reference"></div>
            <div class="form-group">
                <select class="form-control" name="level" data-auto-submit>
                    <option value="">All stock levels</option>
                    <option value="low" <?= $filters['level'] === 'low' ? 'selected' : '' ?>>Low stock</option>
                    <option value="out" <?= $filters['level'] === 'out' ? 'selected' : '' ?>>Out of stock</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($query): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('invent/inventory')) ?>">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body tight">
        <?php if ($products): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Product</th><th>Category</th><th class="num">On hand</th><th class="num">Reserved</th><th class="num">Available</th><th>Threshold</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php $avail = (int) $product['stock_on_hand'] - (int) $product['reserved_qty']; ?>
                            <tr>
                                <td><strong><?= e($product['name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)"><?= e($product['reference']) ?></div></td>
                                <td><?= e($product['category_name'] ?? '—') ?></td>
                                <td class="num"><?= (int) $product['stock_on_hand'] ?></td>
                                <td class="num"><?= (int) $product['reserved_qty'] ?></td>
                                <td class="num"><span class="<?= $avail <= 0 ? 'stock-out' : ($avail <= (int) $product['low_stock_threshold'] ? 'stock-low' : 'stock-in') ?>"><?= $avail ?></span></td>
                                <td class="num"><?= (int) $product['low_stock_threshold'] ?></td>
                                <td><a class="btn btn-outline btn-sm" href="<?= e(url('invent/inventory/' . $product['id'])) ?>">Manage</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No products found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
