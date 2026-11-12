<?php
/**
 * Admin: inventory detail + movement ledger.
 *
 * @var array $product
 * @var list<array> $movements
 * @var list<array> $reservations
 * @var int $reconciled
 * @var int $available
 */
$typeLabels = [
    'initial' => 'Initial stock', 'addition' => 'Addition', 'reduction' => 'Reduction',
    'reservation' => 'Reservation', 'reservation_release' => 'Release', 'sale' => 'Sale',
    'walk_in_sale' => 'Walk-in sale', 'adjustment' => 'Adjustment',
];
?>
<div class="flex-between flex-wrap mb-4">
    <div>
        <h2 style="margin:0"><?= e($product['name']) ?></h2>
        <div class="muted"><?= e($product['reference']) ?> · <?= e(ucfirst((string) $product['unit'])) ?></div>
    </div>
    <a class="btn btn-outline btn-sm" href="<?= e(url('invent/inventory')) ?>">Back to inventory</a>
</div>

<div class="stat-grid">
    <div class="stat-card info"><div class="label">On hand</div><div class="value"><?= (int) $product['stock_on_hand'] ?></div></div>
    <div class="stat-card accent"><div class="label">Reserved</div><div class="value"><?= (int) $product['reserved_qty'] ?></div></div>
    <div class="stat-card success"><div class="label">Available</div><div class="value"><?= (int) $available ?></div></div>
    <div class="stat-card <?= $reconciled === (int) $product['stock_on_hand'] ? 'success' : 'danger' ?>">
        <div class="label">Ledger reconciliation</div>
        <div class="value"><?= (int) $reconciled ?></div>
        <div class="meta"><?= $reconciled === (int) $product['stock_on_hand'] ? 'Matches recorded stock' : 'Mismatch — review ledger' ?></div>
    </div>
</div>

<div class="grid" style="grid-template-columns:1fr 2fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Adjust stock</h2></div>
        <div class="panel-body">
            <form method="post" action="<?= e(url('invent/inventory/' . $product['id'] . '/adjust')) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="adjustment_type">Adjustment</label>
                    <select class="form-control" id="adjustment_type" name="adjustment_type">
                        <option value="add">Add stock</option>
                        <option value="remove">Remove stock</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label" for="quantity">Quantity <span class="req">*</span></label><input class="form-control" type="number" id="quantity" name="quantity" min="1" required></div>
                <div class="form-group"><label class="form-label" for="reason">Reason</label><input class="form-control" type="text" id="reason" name="reason" maxlength="255" placeholder="e.g. New delivery received"></div>
                <button class="btn btn-primary btn-block" type="submit">Record adjustment</button>
                <p class="form-hint">Every adjustment is written to the inventory ledger with your name and a timestamp.</p>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Stock movements</h2><span class="muted" style="font-size:var(--fs-xs)">Most recent 100</span></div>
        <div class="panel-body tight">
            <?php if ($movements): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table">
                        <thead><tr><th>Date</th><th>Type</th><th class="num">Qty</th><th class="num">On hand</th><th class="num">Reserved</th><th>Reason</th><th>By</th></tr></thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= e(date('d M Y H:i', strtotime((string) $movement['created_at']))) ?></td>
                                    <td><span class="pill pill-<?= (int) $movement['quantity'] >= 0 ? 'approved' : 'pending' ?>"><?= e($typeLabels[$movement['movement_type']] ?? $movement['movement_type']) ?></span></td>
                                    <td class="num"><?= (int) $movement['quantity'] > 0 ? '+' : '' ?><?= (int) $movement['quantity'] ?></td>
                                    <td class="num"><?= (int) $movement['stock_after'] ?></td>
                                    <td class="num"><?= (int) $movement['reserved_after'] ?></td>
                                    <td><?= e((string) $movement['reason']) ?></td>
                                    <td><?= e($movement['admin_name'] ?? 'System') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No movements recorded.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Reservations for this product</h2></div>
    <div class="panel-body tight">
        <?php if ($reservations): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Order</th><th class="num">Qty</th><th>Status</th><th>Reserved</th><th>Expires</th><th>Released</th><th>Reason</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><a href="<?= e(url('invent/orders/' . $reservation['order_id'])) ?>"><?= e($reservation['order_reference']) ?></a></td>
                                <td class="num"><?= (int) $reservation['quantity'] ?></td>
                                <td><span class="pill pill-<?= e((string) $reservation['status']) ?>"><?= e(ucfirst((string) $reservation['status'])) ?></span></td>
                                <td><?= e(date('d M Y H:i', strtotime((string) $reservation['reserved_at']))) ?></td>
                                <td><?= e(date('d M Y H:i', strtotime((string) $reservation['expires_at']))) ?></td>
                                <td><?= $reservation['released_at'] ? e(date('d M Y H:i', strtotime((string) $reservation['released_at']))) : '—' ?></td>
                                <td><?= e((string) $reservation['release_reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No reservations for this product.</div>
        <?php endif; ?>
    </div>
</div>
