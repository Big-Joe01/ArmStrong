<?php
/**
 * Admin: brands.
 *
 * @var list<array> $brands
 */
?>
<div class="grid" style="grid-template-columns:1.7fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Brands</h2></div>
        <div class="panel-body tight">
            <?php if ($brands): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table">
                        <thead><tr><th>Name</th><th class="num">Products</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($brands as $brand): ?>
                                <tr>
                                    <td><strong><?= e($brand['name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)">/<?= e($brand['slug']) ?></div></td>
                                    <td class="num"><?= (int) $brand['product_count'] ?></td>
                                    <td><?= $brand['is_active'] ? '<span class="pill pill-active">Active</span>' : '<span class="pill pill-hidden">Inactive</span>' ?></td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" type="button" data-toggle-edit="brand-<?= (int) $brand['id'] ?>">Edit</button>
                                        <form method="post" action="<?= e(url('invent/brands/' . $brand['id'] . '/delete')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this brand?" style="color:var(--c-danger)">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="hide" id="brand-<?= (int) $brand['id'] ?>">
                                    <td colspan="4">
                                        <form method="post" action="<?= e(url('invent/brands/' . $brand['id'])) ?>">
                                            <?= csrf_field() ?>
                                            <div class="form-group"><label class="form-label">Name</label><input class="form-control" type="text" name="name" value="<?= e($brand['name']) ?>" required></div>
                                            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"><?= e((string) $brand['description']) ?></textarea></div>
                                            <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" value="1" <?= $brand['is_active'] ? 'checked' : '' ?>> <span>Active</span></label></div>
                                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No brands yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Add brand</h2></div>
        <div class="panel-body">
            <form method="post" action="<?= e(url('invent/brands')) ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label class="form-label" for="name">Name <span class="req">*</span></label><input class="form-control" type="text" id="name" name="name" required maxlength="150"></div>
                <div class="form-group"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="3"></textarea></div>
                <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label></div>
                <button class="btn btn-primary btn-block" type="submit">Create brand</button>
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
