<?php
/**
 * Admin: properties list.
 *
 * @var list<array> $properties
 * @var list<array> $categories
 * @var array $filters
 * @var array $result
 */
use App\Models\Property;
use App\Services\MediaService;

$query = array_filter([
    'q' => $filters['q'] ?: null, 'category' => $filters['category'] ?: null, 'status' => $filters['status'] ?: null,
], static fn ($v) => $v !== null && $v !== '');
?>
<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/properties')) ?>" style="margin:0">
            <div class="form-group"><input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Title, reference or city"></div>
            <div class="form-group">
                <select class="form-control" name="category" data-auto-submit>
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $filters['category'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" name="status" data-auto-submit>
                    <option value="">Any status</option>
                    <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="featured" <?= $filters['status'] === 'featured' ? 'selected' : '' ?>>Featured</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($query): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('invent/properties')) ?>">Clear</a><?php endif; ?>
        </form>
        <a class="btn btn-accent btn-sm" href="<?= e(url('invent/properties/create')) ?>">Add property</a>
    </div>
    <div class="panel-body tight">
        <?php if ($properties): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th></th><th>Property</th><th>Category</th><th>Transaction</th><th class="num">Price</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td style="width:56px"><img src="<?= e(MediaService::propertyImageUrl($property['main_image'] ?? null, 80)) ?>" alt="" width="44" height="44" style="border-radius:4px;object-fit:cover"></td>
                                <td>
                                    <a href="<?= e(url('invent/properties/' . $property['id'] . '/edit')) ?>"><strong><?= e($property['title']) ?></strong></a>
                                    <div class="muted" style="font-size:var(--fs-xs)"><?= e($property['reference']) ?><?= $property['city'] ? ' · ' . e($property['city']) : '' ?></div>
                                </td>
                                <td><?= e($property['category_name'] ?? '—') ?></td>
                                <td><?= e(Property::transactionLabel((string) $property['transaction_type'])) ?></td>
                                <td class="num"><?= e(Property::priceLabel($property)) ?></td>
                                <td>
                                    <?php if ($property['is_published']): ?><span class="pill pill-approved">Published</span><?php else: ?><span class="pill pill-draft">Draft</span><?php endif; ?>
                                    <span class="pill pill-<?= e((string) $property['status']) ?>"><?= e(Property::statusLabel((string) $property['status'])) ?></span>
                                </td>
                                <td>
                                    <div class="flex" style="gap:var(--sp-2)">
                                        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/properties/' . $property['id'] . '/edit')) ?>">Edit</a>
                                        <form method="post" action="<?= e(url('invent/properties/' . $property['id'] . '/toggle-publish')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit"><?= $property['is_published'] ? 'Unpublish' : 'Publish' ?></button>
                                        </form>
                                        <form method="post" action="<?= e(url('invent/properties/' . $property['id'] . '/delete')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this property?" style="color:var(--c-danger)">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No properties found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
