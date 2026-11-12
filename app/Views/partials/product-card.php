<?php
/**
 * Reusable product card.
 *
 * @var array $product
 */
use App\Core\Str;
use App\Services\MediaService;

$available = ((int) $product['stock_on_hand'] - (int) $product['reserved_qty']);
$unavailable = $product['availability'] !== 'available';
?>
<article class="card card-hover">
    <a href="<?= e(url('/products/' . $product['slug'])) ?>" class="card-media">
        <?php if ($product['is_featured']): ?>
            <span class="badge badge-overlay">Featured</span>
        <?php elseif ($unavailable): ?>
            <span class="badge badge-overlay">Unavailable</span>
        <?php endif; ?>
        <img src="<?= e(MediaService::productImageUrl($product['main_image'] ?? null, 600)) ?>"
             alt="<?= e($product['name']) ?>" loading="lazy" width="600" height="450">
    </a>
    <div class="card-body">
        <div class="card-meta"><?= e($product['category_name'] ?? 'Uncategorised') ?></div>
        <h3 class="card-title">
            <a href="<?= e(url('/products/' . $product['slug'])) ?>"><?= e(Str::excerpt((string) $product['name'], 60)) ?></a>
        </h3>
        <?php if (!empty($product['brand_name'])): ?>
            <div class="muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-2)"><?= e($product['brand_name']) ?></div>
        <?php endif; ?>
        <div class="flex-between">
            <span class="card-price"><?= e(money($product['price'])) ?></span>
            <?php if ($unavailable): ?>
                <span class="stock-pill stock-out">Unavailable</span>
            <?php elseif ($available <= 0): ?>
                <span class="stock-pill stock-out">Out of stock</span>
            <?php elseif ($available <= (int) $product['low_stock_threshold']): ?>
                <span class="stock-pill stock-low">Low stock</span>
            <?php else: ?>
                <span class="stock-pill stock-in">In stock</span>
            <?php endif; ?>
        </div>
    </div>
</article>
