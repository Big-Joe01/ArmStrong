<?php
/**
 * Admin: property categories.
 *
 * @var list<array> $categories
 */
?>
<div class="grid" style="grid-template-columns:1.7fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Property categories</h2></div>
        <div class="panel-body tight">
            <?php if ($categories): ?>
                <div class="table-wrap" style="border:0;border-radius:0">
                    <table class="table">
                        <thead><tr><th>Name</th><th class="num">Properties</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong><?= e($category['name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)">/<?= e($category['slug']) ?></div></td>
                                    <td class="num"><?= (int) $category['property_count'] ?></td>
                                    <td><?= $category['is_active'] ? '<span class="pill pill-active">Active</span>' : '<span class="pill pill-hidden">Inactive</span>' ?></td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" type="button" data-toggle-edit="pcat-<?= (int) $category['id'] ?>">Edit</button>
                                        <form method="post" action="<?= e(url('invent/property-categories/' . $category['id'] . '/delete')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this category?" style="color:var(--c-danger)">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="hide" id="pcat-<?= (int) $category['id'] ?>">
                                    <td colspan="4">
                                        <form method="post" action="<?= e(url('invent/property-categories/' . $category['id'])) ?>">
                                            <?= csrf_field() ?>
                                            <div class="form-group"><label class="form-label">Name</label><input class="form-control" type="text" name="name" value="<?= e($category['name']) ?>" required></div>
                                            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"><?= e((string) $category['description']) ?></textarea></div>
                                            <div class="form-row">
                                                <div class="form-group"><label class="form-label">Sort order</label><input class="form-control" type="number" name="sort_order" value="<?= (int) $category['sort_order'] ?>"></div>
                                                <div class="form-group" style="display:flex;align-items:flex-end"><label class="form-check"><input type="checkbox" name="is_active" value="1" <?= $category['is_active'] ? 'checked' : '' ?>> <span>Active</span></label></div>
                                            </div>
                                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">No property categories yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Add category</h2></div>
        <div class="panel-body">
            <form method="post" action="<?= e(url('invent/property-categories')) ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label class="form-label" for="name">Name <span class="req">*</span></label><input class="form-control" type="text" id="name" name="name" required maxlength="150" placeholder="e.g. Residential"></div>
                <div class="form-group"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="3"></textarea></div>
                <div class="form-group"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" value="0"></div>
                <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label></div>
                <button class="btn btn-primary btn-block" type="submit">Create category</button>
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
