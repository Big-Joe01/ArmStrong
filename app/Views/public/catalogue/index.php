<?php
/**
 * Building materials & interior catalogue listing.
 *
 * @var array $filters
 * @var array $result
 * @var list<array> $categories
 * @var list<array> $brands
 */
$query = array_filter([
    'q'            => $filters['q'] ?: null,
    'category'     => $filters['category'] ?: null,
    'brand'        => $filters['brand'] ?: null,
    'min'          => $filters['min'] !== null && $filters['min'] !== '' ? $filters['min'] : null,
    'max'          => $filters['max'] !== null && $filters['max'] !== '' ? $filters['max'] : null,
    'availability' => $filters['availability'] ?: null,
    'sort'         => $filters['sort'] !== 'featured' ? $filters['sort'] : null,
], static fn ($v) => $v !== null && $v !== '');
?>
<section class="section section-ink" style="padding-block:var(--sp-8)">
    <div class="container">
        <span class="eyebrow">Building Materials &amp; Interior Division</span>
        <h1>Building materials and interior products</h1>
        <p style="color:#C9D4E1;max-width:62ch;margin-bottom:0">
            Tiles, sanitary ware, doors, windows, lighting, plumbing, ceiling, flooring, paints and interior products. Order online with collection or delivery.
        </p>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/building-materials')) ?>">
            <div class="filter-grid">
                <div class="form-group mb-0">
                    <label class="form-label" for="c-q">Keyword</label>
                    <input class="form-control" type="search" id="c-q" name="q" value="<?= e($filters['q']) ?>" placeholder="Product, brand or reference">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-category">Category</label>
                    <select class="form-control" id="c-category" name="category" data-auto-submit>
                        <option value="">All categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['slug']) ?>" <?= $filters['category'] === $category['slug'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?> (<?= (int) $category['product_count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-brand">Brand</label>
                    <select class="form-control" id="c-brand" name="brand" data-auto-submit>
                        <option value="">All brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= e($brand['slug']) ?>" <?= $filters['brand'] === $brand['slug'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-min">Min price</label>
                    <input class="form-control" type="number" id="c-min" name="min" min="0" step="500" value="<?= e((string) ($filters['min'] ?? '')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-max">Max price</label>
                    <input class="form-control" type="number" id="c-max" name="max" min="0" step="500" value="<?= e((string) ($filters['max'] ?? '')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-availability">Availability</label>
                    <select class="form-control" id="c-availability" name="availability" data-auto-submit>
                        <option value="">Any</option>
                        <option value="available" <?= $filters['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="temporarily_unavailable" <?= $filters['availability'] === 'temporarily_unavailable' ? 'selected' : '' ?>>Temporarily unavailable</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="c-sort">Sort by</label>
                    <select class="form-control" id="c-sort" name="sort" data-auto-submit>
                        <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Featured first</option>
                        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                        <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name A–Z</option>
                        <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <button class="btn btn-primary btn-block" type="submit">Search</button>
                </div>
            </div>
            <?php if ($query): ?>
                <div style="margin-top:var(--sp-3)"><a href="<?= e(url('/building-materials')) ?>">Clear all filters</a></div>
            <?php endif; ?>
        </form>

        <p class="muted"><?= (int) $result['total'] ?> product<?= (int) $result['total'] === 1 ? '' : 's' ?> found</p>

        <?php if ($result['items']): ?>
            <div class="grid grid-4">
                <?php foreach ($result['items'] as $product): ?>
                    <?php include __DIR__ . '/../../partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php include __DIR__ . '/../../partials/pagination.php'; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No products match your search</h3>
                <p>Try a different keyword, adjust the price range, or clear the filters.</p>
                <a class="btn btn-primary btn-inline mt-6" href="<?= e(url('/building-materials')) ?>">Reset search</a>
            </div>
        <?php endif; ?>
    </div>
</section>
