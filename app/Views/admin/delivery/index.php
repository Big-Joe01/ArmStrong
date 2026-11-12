<?php
/**
 * Admin: delivery settings.
 *
 * @var list<array> $rates
 */
?>
<div class="alert alert-info">
    <span>These options appear at checkout. Fees are shown to the customer before they submit an order. Store collection is always free.</span>
</div>

<div class="grid" style="grid-template-columns:1.7fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Delivery options</h2></div>
        <div class="panel-body tight">
            <?php if ($rates): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Timeframe</th><th class="num">Fee</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($rates as $rate): ?>
                                <tr>
                                    <td><strong><?= e($rate['name']) ?></strong><?php if ($rate['description']): ?><div class="muted" style="font-size:var(--fs-xs)"><?= e($rate['description']) ?></div><?php endif; ?></td>
                                    <td><?= e((string) $rate['timeframe']) ?></td>
                                    <td class="num"><?= e(money($rate['fee'])) ?></td>
                                    <td><?= $rate['is_active'] ? '<span class="pill pill-active">Active</span>' : '<span class="pill pill-hidden">Disabled</span>' ?></td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" type="button" data-toggle-edit="rate-<?= (int) $rate['id'] ?>">Edit</button>
                                        <form method="post" action="<?= e(url('invent/delivery-settings/' . $rate['id'] . '/delete')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this delivery option?" style="color:var(--c-danger)">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="hide" id="rate-<?= (int) $rate['id'] ?>">
                                    <td colspan="5">
                                        <form method="post" action="<?= e(url('invent/delivery-settings/' . $rate['id'])) ?>">
                                            <?= csrf_field() ?>
                                            <div class="form-row">
                                                <div class="form-group"><label class="form-label">Name</label><input class="form-control" type="text" name="name" value="<?= e($rate['name']) ?>" required></div>
                                                <div class="form-group"><label class="form-label">Fee (₦)</label><input class="form-control" type="number" name="fee" min="0" step="0.01" value="<?= e((string) $rate['fee']) ?>" required></div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group"><label class="form-label">Timeframe</label><input class="form-control" type="text" name="timeframe" value="<?= e((string) $rate['timeframe']) ?>" placeholder="e.g. Same day"></div>
                                                <div class="form-group"><label class="form-label">Sort order</label><input class="form-control" type="number" name="sort_order" value="<?= (int) $rate['sort_order'] ?>"></div>
                                            </div>
                                            <div class="form-group"><label class="form-label">Description</label><input class="form-control" type="text" name="description" value="<?= e((string) $rate['description']) ?>" maxlength="255"></div>
                                            <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" value="1" <?= $rate['is_active'] ? 'checked' : '' ?>> <span>Active</span></label></div>
                                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No delivery options configured.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Add delivery option</h2></div>
        <div class="panel-body">
            <form method="post" action="<?= e(url('invent/delivery-settings')) ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label class="form-label" for="name">Name <span class="req">*</span></label><input class="form-control" type="text" id="name" name="name" required maxlength="120" placeholder="e.g. Same-day delivery"></div>
                <div class="form-group"><label class="form-label" for="fee">Fee (₦) <span class="req">*</span></label><input class="form-control" type="number" id="fee" name="fee" min="0" step="0.01" required value="0.00"></div>
                <div class="form-group"><label class="form-label" for="timeframe">Timeframe</label><input class="form-control" type="text" id="timeframe" name="timeframe" maxlength="120" placeholder="e.g. Within 24 hours"></div>
                <div class="form-group"><label class="form-label" for="description">Description</label><input class="form-control" type="text" id="description" name="description" maxlength="255"></div>
                <div class="form-group"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" value="0"></div>
                <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label></div>
                <button class="btn btn-primary btn-block" type="submit">Create option</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-toggle-edit]');
    if (!btn) return;
    var row = document.getElementById(btn.getAttribute('data-toggle-edit'));
    if (row) row.classList.toggle('hide');
});
</script>
