<?php
/**
 * Product details page.
 *
 * @var array $product
 * @var list<array> $images
 * @var list<array> $related
 * @var array $rating
 * @var list<array> $reviews
 * @var list<array> $deliveryOptions
 * @var array $specs
 * @var list<string> $colors
 * @var list<string> $sizes
 * @var list<array> $variations
 */
use App\Core\Settings;
use App\Core\Session;
use App\Services\MediaService;

$available = (int) $product['stock_on_hand'] - (int) $product['reserved_qty'];
$isOrderable = $product['availability'] === 'available' && $available > 0;
$mainImg = $images[0]['image_url'] ?? null;
$errors  = Session::pullFlash('errors', []);
?>
<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= e(url('/')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/building-materials')) ?>">Building Materials</a><span>/</span>
        <?php if (!empty($product['category_slug'])): ?>
            <a href="<?= e(url('/building-materials?category=' . $product['category_slug'])) ?>"><?= e($product['category_name']) ?></a><span>/</span>
        <?php endif; ?>
        <span><?= e($product['name']) ?></span>
    </nav>
</div>

<section class="section-tight">
    <div class="container">
        <div class="grid" style="grid-template-columns:1.4fr 1fr;gap:var(--sp-8)">
            <div>
                <div data-gallery>
                    <div class="gallery-main">
                        <img data-gallery-main src="<?= e(MediaService::productImageUrl($mainImg, 1000)) ?>"
                             alt="<?= e($product['name']) ?>" width="1000" height="750">
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="gallery-thumbs">
                            <?php foreach ($images as $index => $image): ?>
                                <button type="button" class="gallery-thumb <?= $index === 0 ? 'is-active' : '' ?>"
                                        data-gallery-thumb data-full="<?= e(MediaService::productImageUrl($image['image_url'], 1000)) ?>"
                                        aria-label="View image <?= $index + 1 ?>">
                                    <img src="<?= e(MediaService::productImageUrl($image['image_url'], 220)) ?>" alt="" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="flex" style="gap:var(--sp-2);flex-wrap:wrap;margin-bottom:var(--sp-3)">
                    <?php if (!empty($product['category_name'])): ?>
                        <span class="badge"><?= e($product['category_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($product['brand_name'])): ?>
                        <span class="badge badge-info"><?= e($product['brand_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($product['is_featured']): ?>
                        <span class="badge badge-accent">Featured</span>
                    <?php endif; ?>
                </div>

                <h1><?= e($product['name']) ?></h1>

                <?php if ((int) $rating['total'] > 0): ?>
                    <div class="flex items-center" style="gap:var(--sp-2);margin-bottom:var(--sp-4)">
                        <span class="stars" aria-label="<?= e((string) $rating['avg']) ?> out of 5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= round($rating['avg']) ? '' : 'off' ?>">&#9733;</span>
                            <?php endfor; ?>
                        </span>
                        <span class="muted" style="font-size:var(--fs-sm)"><?= e((string) $rating['avg']) ?> (<?= (int) $rating['total'] ?> review<?= (int) $rating['total'] === 1 ? '' : 's' ?>)</span>
                    </div>
                <?php endif; ?>

                <div class="card-price" style="font-size:var(--fs-2xl)"><?= e(money($product['price'])) ?></div>
                <div class="muted" style="font-size:var(--fs-sm)">per <?= e($product['unit']) ?><?= $product['packaging'] ? ' · ' . e($product['packaging']) : '' ?></div>

                <div style="margin:var(--sp-4) 0">
                    <?php if ($product['availability'] !== 'available'): ?>
                        <span class="badge badge-danger"><?= e(ucwords(str_replace('_', ' ', (string) $product['availability']))) ?></span>
                    <?php elseif ($available <= 0): ?>
                        <span class="badge badge-danger">Out of stock</span>
                    <?php elseif ($available <= (int) $product['low_stock_threshold']): ?>
                        <span class="badge badge-warning">Low stock — <?= (int) $available ?> left</span>
                    <?php else: ?>
                        <span class="badge badge-success">In stock</span>
                    <?php endif; ?>
                </div>

                <p class="muted"><?= e((string) $product['short_description']) ?></p>

                <!-- Ordering -->
                <?php if ($isOrderable): ?>
                    <a class="btn btn-primary btn-lg btn-block" href="<?= e(url('/order?product=' . $product['id'])) ?>">
                        Place an order
                    </a>
                    <p class="form-hint" style="margin-top:var(--sp-2)">
                        You will choose quantity, collection or delivery, and confirm your details on the next page. No account needed.
                    </p>
                <?php else: ?>
                    <div class="alert alert-warning">This product is not currently available to order. Please contact us for availability.</div>
                <?php endif; ?>

                <ul class="spec-list" style="margin-top:var(--sp-5)">
                    <li><span class="k">Reference</span><span class="v"><?= e($product['reference']) ?></span></li>
                    <?php if (!empty($product['brand_name'])): ?>
                        <li><span class="k">Brand</span><span class="v"><?= e($product['brand_name']) ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($product['category_name'])): ?>
                        <li><span class="k">Category</span><span class="v"><?= e($product['category_name']) ?></span></li>
                    <?php endif; ?>
                    <li><span class="k">Unit</span><span class="v"><?= e($product['unit']) ?></span></li>
                    <?php if ($sizes): ?>
                        <li><span class="k">Available sizes</span><span class="v"><?= e(implode(', ', $sizes)) ?></span></li>
                    <?php endif; ?>
                    <?php if ($colors): ?>
                        <li><span class="k">Available colours</span><span class="v"><?= e(implode(', ', $colors)) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt section-tight">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <h2 style="font-size:var(--fs-lg)">Product description</h2>
                <div><?= nl2br(e((string) $product['description'])) ?></div>

                <?php if ($specs): ?>
                    <h2 style="font-size:var(--fs-lg);margin-top:var(--sp-6)">Specifications</h2>
                    <ul class="spec-list">
                        <?php foreach ($specs as $key => $value): ?>
                            <?php if (is_string($key)): ?>
                                <li><span class="k"><?= e($key) ?></span><span class="v"><?= e((string) $value) ?></span></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($variations): ?>
                    <h2 style="font-size:var(--fs-lg);margin-top:var(--sp-6)">Available variations</h2>
                    <?php foreach ($variations as $variation): ?>
                        <?php if (!is_array($variation)) continue; ?>
                        <div style="margin-bottom:var(--sp-4)">
                            <div class="form-label"><?= e((string) ($variation['label'] ?? 'Option')) ?></div>
                            <div class="chip-row">
                                <?php foreach ((array) ($variation['options'] ?? []) as $option): ?>
                                    <span class="chip chip-static"><?= e((string) $option) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <h2 style="font-size:var(--fs-lg)">Delivery &amp; collection</h2>
                <ul class="spec-list">
                    <li><span class="k">Store collection</span><span class="v">No charge</span></li>
                    <?php foreach ($deliveryOptions as $option): ?>
                        <li>
                            <span class="k"><?= e($option['name']) ?><?= $option['timeframe'] ? ' (' . e($option['timeframe']) . ')' : '' ?></span>
                            <span class="v"><?= e(money($option['fee'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="alert alert-info" style="margin-top:var(--sp-4)">
                    Orders are reserved for seven days. Payment is completed through our approved in-person process — no card payment is taken online.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <h2 style="font-size:var(--fs-lg)">Customer reviews</h2>
                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card" style="margin-bottom:var(--sp-4)">
                            <span class="stars" aria-label="<?= (int) $review['rating'] ?> out of 5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?= $i <= (int) $review['rating'] ? '' : 'off' ?>">&#9733;</span>
                                <?php endfor; ?>
                            </span>
                            <?php if (!empty($review['title'])): ?>
                                <h4 style="margin:var(--sp-2) 0 0;font-size:var(--fs-sm)"><?= e($review['title']) ?></h4>
                            <?php endif; ?>
                            <blockquote><?= e((string) $review['body']) ?></blockquote>
                            <div class="review-author"><?= e($review['customer_name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="muted">There are no approved reviews for this product yet. If you have purchased it, you are welcome to leave one.</p>
                <?php endif; ?>
            </div>

            <div>
                <h2 style="font-size:var(--fs-lg)">Write a review</h2>
                <form method="post" action="<?= e(url('/reviews')) ?>" class="card" style="padding:var(--sp-5)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="visually-hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rev-name">Your name <span class="req">*</span></label>
                        <input class="form-control" type="text" id="rev-name" name="customer_name" required maxlength="150">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="rev-rating">Rating <span class="req">*</span></label>
                            <select class="form-control" id="rev-rating" name="rating" required>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="rev-title">Title</label>
                            <input class="form-control" type="text" id="rev-title" name="title" maxlength="200">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rev-body">Your review <span class="req">*</span></label>
                        <textarea class="form-control" id="rev-body" name="body" required minlength="10" maxlength="3000"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">Submit review</button>
                    <p class="form-hint">Reviews appear publicly once approved.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Related products</h2></div>
        <div class="grid grid-4">
            <?php foreach ($related as $product): ?>
                <?php include __DIR__ . '/../../partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Product structured data (only when a price and availability are known).
$schema = array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => (string) $product['name'],
    'description' => \App\Core\Str::excerpt((string) $product['description'], 400),
    'sku'         => (string) $product['reference'],
    'brand'       => !empty($product['brand_name']) ? ['@type' => 'Brand', 'name' => (string) $product['brand_name']] : null,
    'image'       => $mainImg ? [MediaService::productImageUrl($mainImg, 1000)] : null,
    'offers'      => [
        '@type'         => 'Offer',
        'price'         => number_format((float) $product['price'], 2, '.', ''),
        'priceCurrency' => (string) \App\Core\Config::get('currency_code', 'NGN'),
        'availability'  => $isOrderable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => url('/products/' . $product['slug']),
    ],
]);
if ((int) $rating['total'] > 0) {
    $schema['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => (string) $rating['avg'],
        'reviewCount' => (int) $rating['total'],
    ];
}
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
