<?php
/**
 * Admin: product list.
 *
 * @var list<array> $products
 * @var list<array> $categories
 * @var array $filters
 * @var array $result
 */
use App\Services\MediaService;

$query = array_filter([
    'q'        => $filters['q'] ?: null,
    'category' => $filters['category'] ?: null,
    'status'   => $filters['status'] ?: null,
], static fn ($v) => $v !== null && $v !== '');
?>
<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/products')) ?>" style="margin:0">
            <div class="form-group">
                <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Name or reference">
            </div>
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
                    <option value="out_of_stock" <?= $filters['status'] === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($query): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('invent/products')) ?>">Clear</a><?php endif; ?>
        </form>
        <a class="btn btn-accent btn-sm" href="<?= e(url('invent/products/create')) ?>">Add product</a>
    </div>

    <div class="panel-body tight">
        <?php if ($products): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th class="num">Price</th>
                            <th class="num">Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php $avail = (int) $product['stock_on_hand'] - (int) $product['reserved_qty']; ?>
                            <tr>
                                <td style="width:56px">
                                    <img src="<?= e(MediaService::productImageUrl($product['main_image'] ?? null, 80)) ?>" alt="" width="44" height="44" style="border-radius:4px;object-fit:cover">
                                </td>
                                <td>
                                    <a href="<?= e(url('invent/products/' . $product['id'] . '/edit')) ?>"><strong><?= e($product['name']) ?></strong></a>
                                    <div class="muted" style="font-size:var(--fs-xs)"><?= e($product['reference']) ?></div>
                                </td>
                                <td><?= e($product['category_name'] ?? '—') ?></td>
                                <td><?= e($product['brand_name'] ?? '—') ?></td>
                                <td class="num"><?= e(money($product['price'])) ?></td>
                                <td class="num">
                                    <span class="<?= $avail <= 0 ? 'stock-out' : ($avail <= (int) $product['low_stock_threshold'] ? 'stock-low' : 'stock-in') ?>"><?= $avail ?></span>
                                    <?php if ((int) $product['reserved_qty'] > 0): ?>
                                        <div class="muted" style="font-size:var(--fs-xs)"><?= (int) $product['reserved_qty'] ?> reserved</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['is_published']): ?>
                                        <span class="pill pill-approved">Published</span>
                                    <?php else: ?>
                                        <span class="pill pill-draft">Draft</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="pill pill-new">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex" style="gap:var(--sp-2)">
                                        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/products/' . $product['id'] . '/edit')) ?>">Edit</a>
                                        <form method="post" action="<?= e(url('invent/products/' . $product['id'] . '/toggle-publish')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit"><?= $product['is_published'] ? 'Unpublish' : 'Publish' ?></button>
                                        </form>
                                        <form method="post" action="<?= e(url('invent/products/' . $product['id'] . '/delete')) ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this product? This cannot be undone." style="color:var(--c-danger)">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
