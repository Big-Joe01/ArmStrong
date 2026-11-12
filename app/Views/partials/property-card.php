<?php
/**
 * Reusable property card.
 *
 * @var array $property
 */
use App\Core\Str;
use App\Models\Property;
use App\Services\MediaService;

$statusLabel = Property::statusLabel((string) $property['status']);
$txLabel     = Property::transactionLabel((string) $property['transaction_type']);
$location    = trim(implode(', ', array_filter([
    (string) ($property['neighbourhood'] ?? ''),
    (string) ($property['city'] ?? ''),
    (string) ($property['state'] ?? ''),
])), ', ');
?>
<article class="card card-hover">
    <a href="<?= e(url('/real-estate/' . $property['slug'])) ?>" class="card-media">
        <span class="badge badge-overlay"><?= e($txLabel) ?></span>
        <img src="<?= e(MediaService::propertyImageUrl($property['main_image'] ?? null, 700)) ?>"
             alt="<?= e($property['title']) ?>" loading="lazy" width="700" height="525">
    </a>
    <div class="card-body">
        <div class="card-meta">
            <?= e($property['category_name'] ?? 'Property') ?>
            <?php if ($location !== ''): ?>
                &middot; <?= e($location) ?>
            <?php endif; ?>
        </div>
        <h3 class="card-title">
            <a href="<?= e(url('/real-estate/' . $property['slug'])) ?>"><?= e(Str::excerpt((string) $property['title'], 70)) ?></a>
        </h3>
        <div class="flex-between flex-wrap" style="gap:var(--sp-3)">
            <span class="card-price"><?= e(Property::priceLabel($property)) ?></span>
            <span class="badge <?= $property['status'] === 'available' ? 'badge-success' : ($property['status'] === 'reserved' ? 'badge-warning' : 'badge-danger') ?>">
                <?= e($statusLabel) ?>
            </span>
        </div>
        <?php if ($property['bedrooms'] !== null || $property['bathrooms'] !== null): ?>
            <div class="muted" style="font-size:var(--fs-xs);margin-top:var(--sp-3)">
                <?php if ($property['bedrooms'] !== null): ?><?= (int) $property['bedrooms'] ?> bed<?php endif; ?>
                <?php if ($property['bathrooms'] !== null): ?> &middot; <?= (int) $property['bathrooms'] ?> bath<?php endif; ?>
                <?php if ($property['toilets'] !== null): ?> &middot; <?= (int) $property['toilets'] ?> toilet<?= (int) $property['toilets'] === 1 ? '' : 's' ?><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
