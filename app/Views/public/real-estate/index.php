<?php
/**
 * Real estate landing page: search, filters, listings.
 *
 * @var array $filters
 * @var array $result
 * @var list<array> $categories
 * @var list<string> $states
 */
use App\Models\Property;

$query = array_filter([
    'q'        => $filters['q'] ?: null,
    'category' => $filters['category'] ?: null,
    'type'     => $filters['transaction_type'] ?: null,
    'state'    => $filters['state'] ?: null,
    'city'     => $filters['city'] ?: null,
    'min'      => $filters['min'] !== null && $filters['min'] !== '' ? $filters['min'] : null,
    'max'      => $filters['max'] !== null && $filters['max'] !== '' ? $filters['max'] : null,
    'bedrooms' => $filters['bedrooms'] ?: null,
    'sort'     => $filters['sort'] !== 'featured' ? $filters['sort'] : null,
], static fn ($v) => $v !== null && $v !== '');
?>
<section class="section section-ink" style="padding-block:var(--sp-8)">
    <div class="container">
        <span class="eyebrow">Real Estate Division</span>
        <h1>Properties for sale, rent and lease</h1>
        <p style="color:#C9D4E1;max-width:62ch;margin-bottom:0">
            Browse residential and commercial properties, land and plots. Every listing is published from our live property records.
        </p>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/real-estate')) ?>">
            <div class="filter-grid">
                <div class="form-group mb-0">
                    <label class="form-label" for="f-q">Keyword</label>
                    <input class="form-control" type="search" id="f-q" name="q" value="<?= e($filters['q']) ?>" placeholder="Title, city or reference">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-type">Transaction</label>
                    <select class="form-control" id="f-type" name="type">
                        <option value="">Any</option>
                        <?php foreach (['for_sale' => 'For Sale', 'for_rent' => 'For Rent', 'for_lease' => 'For Lease', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filters['transaction_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-category">Category</label>
                    <select class="form-control" id="f-category" name="category">
                        <option value="">Any</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['slug']) ?>" <?= $filters['category'] === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-state">State</label>
                    <select class="form-control" id="f-state" name="state">
                        <option value="">Any</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?= e($state) ?>" <?= $filters['state'] === $state ? 'selected' : '' ?>><?= e($state) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-min">Min price</label>
                    <input class="form-control" type="number" id="f-min" name="min" min="0" step="1000" value="<?= e((string) ($filters['min'] ?? '')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-max">Max price</label>
                    <input class="form-control" type="number" id="f-max" name="max" min="0" step="1000" value="<?= e((string) ($filters['max'] ?? '')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-bedrooms">Min bedrooms</label>
                    <input class="form-control" type="number" id="f-bedrooms" name="bedrooms" min="0" max="20" value="<?= e((string) ($filters['bedrooms'] ?? '')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="f-sort">Sort by</label>
                    <select class="form-control" id="f-sort" name="sort">
                        <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Featured first</option>
                        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                        <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <button class="btn btn-primary btn-block" type="submit">Search</button>
                </div>
            </div>
            <?php if ($query): ?>
                <div style="margin-top:var(--sp-3)"><a href="<?= e(url('/real-estate')) ?>">Clear all filters</a></div>
            <?php endif; ?>
        </form>

        <div class="flex-between flex-wrap mb-4">
            <p class="muted mb-0">
                <?= (int) $result['total'] ?> propert<?= (int) $result['total'] === 1 ? 'y' : 'ies' ?> found
            </p>
        </div>

        <?php if ($result['items']): ?>
            <div class="grid grid-3">
                <?php foreach ($result['items'] as $property): ?>
                    <?php include __DIR__ . '/../../partials/property-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php $query = $query; include __DIR__ . '/../../partials/pagination.php'; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No properties match your search</h3>
                <p>Try widening your price range, changing the transaction type, or clearing the filters.</p>
                <a class="btn btn-primary btn-inline mt-6" href="<?= e(url('/real-estate')) ?>">Reset search</a>
            </div>
        <?php endif; ?>
    </div>
</section>
