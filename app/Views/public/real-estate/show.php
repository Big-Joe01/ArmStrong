<?php
/**
 * Property details page.
 *
 * @var array $property
 * @var list<array> $images
 * @var list<array> $related
 * @var list<string> $features
 * @var list<string> $amenities
 * @var array $specs
 */
use App\Core\Settings;
use App\Core\Session;
use App\Models\Property;
use App\Services\MediaService;

$errors  = Session::pullFlash('errors', []);
$mainImg = $images[0]['image_url'] ?? null;
$location = trim(implode(', ', array_filter([
    (string) ($property['neighbourhood'] ?? ''),
    (string) ($property['city'] ?? ''),
    (string) ($property['state'] ?? ''),
])), ', ');
$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
?>
<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= e(url('/')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/real-estate')) ?>">Real Estate</a><span>/</span>
        <span><?= e($property['title']) ?></span>
    </nav>
</div>

<section class="section-tight">
    <div class="container">
        <div class="grid" style="grid-template-columns:1.7fr 1fr;gap:var(--sp-8)">
            <div>
                <!-- Gallery -->
                <div data-gallery>
                    <div class="gallery-main">
                        <img data-gallery-main src="<?= e(MediaService::propertyImageUrl($mainImg, 1200)) ?>"
                             alt="<?= e($property['title']) ?>" width="1200" height="900">
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="gallery-thumbs">
                            <?php foreach ($images as $index => $image): ?>
                                <button type="button" class="gallery-thumb <?= $index === 0 ? 'is-active' : '' ?>"
                                        data-gallery-thumb data-full="<?= e(MediaService::propertyImageUrl($image['image_url'], 1200)) ?>"
                                        aria-label="View image <?= $index + 1 ?>">
                                    <img src="<?= e(MediaService::propertyImageUrl($image['image_url'], 240)) ?>" alt="" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Summary -->
                <div style="margin-top:var(--sp-7)">
                    <div class="flex" style="gap:var(--sp-3);flex-wrap:wrap;margin-bottom:var(--sp-3)">
                        <span class="badge badge-solid"><?= e(Property::transactionLabel((string) $property['transaction_type'])) ?></span>
                        <span class="badge"><?= e($property['category_name'] ?? 'Property') ?></span>
                        <span class="badge <?= $property['status'] === 'available' ? 'badge-success' : 'badge-warning' ?>">
                            <?= e(Property::statusLabel((string) $property['status'])) ?>
                        </span>
                    </div>

                    <h1><?= e($property['title']) ?></h1>
                    <?php if ($location !== ''): ?>
                        <p class="muted"><?= e($location) ?></p>
                    <?php endif; ?>

                    <div class="flex flex-wrap" style="gap:var(--sp-5);margin:var(--sp-5) 0;padding:var(--sp-5) 0;border-top:1px solid var(--c-border);border-bottom:1px solid var(--c-border)">
                        <?php if ($property['bedrooms'] !== null): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Bedrooms</div><strong><?= (int) $property['bedrooms'] ?></strong></div>
                        <?php endif; ?>
                        <?php if ($property['bathrooms'] !== null): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Bathrooms</div><strong><?= (int) $property['bathrooms'] ?></strong></div>
                        <?php endif; ?>
                        <?php if ($property['toilets'] !== null): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Toilets</div><strong><?= (int) $property['toilets'] ?></strong></div>
                        <?php endif; ?>
                        <?php if ($property['land_size']): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Land size</div><strong><?= e($property['land_size']) ?></strong></div>
                        <?php endif; ?>
                        <?php if ($property['building_size']): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Building size</div><strong><?= e($property['building_size']) ?></strong></div>
                        <?php endif; ?>
                        <?php if ($property['parking']): ?>
                            <div><div class="muted" style="font-size:var(--fs-xs)">Parking</div><strong><?= e($property['parking']) ?></strong></div>
                        <?php endif; ?>
                    </div>

                    <h2 style="font-size:var(--fs-lg)">Description</h2>
                    <div><?= nl2br(e((string) $property['description'])) ?></div>
                </div>

                <?php if ($specs): ?>
                    <h2 style="font-size:var(--fs-lg);margin-top:var(--sp-7)">Specifications</h2>
                    <ul class="spec-list">
                        <?php foreach ($specs as $key => $value): ?>
                            <li><span class="k"><?= e(is_string($key) ? $key : (string) $value) ?></span><span class="v"><?= e(is_string($key) ? (string) $value : '') ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($features): ?>
                    <h2 style="font-size:var(--fs-lg);margin-top:var(--sp-7)">Features</h2>
                    <ul class="feature-list">
                        <?php foreach ($features as $feature): ?>
                            <li><?= e((string) $feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($amenities): ?>
                    <h2 style="font-size:var(--fs-lg);margin-top:var(--sp-7)">Amenities</h2>
                    <ul class="feature-list">
                        <?php foreach ($amenities as $amenity): ?>
                            <li><?= e((string) $amenity) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside>
                <div class="summary-box" style="position:sticky;top:96px">
                    <div class="muted" style="font-size:var(--fs-xs);text-transform:uppercase;letter-spacing:.06em">Price</div>
                    <div class="card-price" style="font-size:var(--fs-2xl);margin-bottom:var(--sp-4)"><?= e(Property::priceLabel($property)) ?></div>

                    <ul class="spec-list" style="margin-bottom:var(--sp-5)">
                        <li><span class="k">Reference</span><span class="v"><?= e($property['reference']) ?></span></li>
                        <li><span class="k">Type</span><span class="v"><?= e(Property::transactionLabel((string) $property['transaction_type'])) ?></span></li>
                        <?php if ($property['property_type']): ?>
                            <li><span class="k">Property type</span><span class="v"><?= e($property['property_type']) ?></span></li>
                        <?php endif; ?>
                        <li><span class="k">Status</span><span class="v"><?= e(Property::statusLabel((string) $property['status'])) ?></span></li>
                    </ul>

                    <div style="display:grid;gap:var(--sp-3)">
                        <a class="btn btn-primary btn-block" href="#enquire">Enquire about this property</a>
                        <?php if ($phone = Settings::get('contact_phone')): ?>
                            <a class="btn btn-outline btn-block" href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>">Call <?= e($phone) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Enquiry form -->
<section class="section section-alt" id="enquire">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <span class="eyebrow">Enquire</span>
                <h2>Ask about this property</h2>
                <p class="muted">
                    Send us your details and we will get back to you about <strong><?= e($property['title']) ?></strong> (Ref: <?= e($property['reference']) ?>).
                    You do not need an account to enquire.
                </p>
                <ul class="spec-list">
                    <li><span class="k">Reference</span><span class="v"><?= e($property['reference']) ?></span></li>
                    <?php if ($location !== ''): ?>
                        <li><span class="k">Location</span><span class="v"><?= e($location) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <form method="post" action="<?= e(url('/real-estate/' . $property['slug'] . '/enquire')) ?>" class="card" style="padding:var(--sp-5)">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="enq-name">Full name <span class="req">*</span></label>
                        <input class="form-control" type="text" id="enq-name" name="full_name" required maxlength="150" value="<?= e(old('full_name')) ?>">
                        <?php if (isset($errors['full_name'])): ?><div class="form-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="enq-phone">Phone number <span class="req">*</span></label>
                            <input class="form-control" type="tel" id="enq-phone" name="phone" required maxlength="40" value="<?= e(old('phone')) ?>">
                            <?php if (isset($errors['phone'])): ?><div class="form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="enq-email">Email</label>
                            <input class="form-control" type="email" id="enq-email" name="email" maxlength="190" value="<?= e(old('email')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="enq-message">Message <span class="req">*</span></label>
                        <textarea class="form-control" id="enq-message" name="message" required minlength="5" maxlength="3000"><?= e(old('message') ?: ('I would like more information about ' . $property['title'] . ' (Ref: ' . $property['reference'] . ').')) ?></textarea>
                        <?php if (isset($errors['message'])): ?><div class="form-error"><?= e($errors['message']) ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">Send enquiry</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section">
    <div class="container">
        <div class="section-head"><h2>Related properties</h2></div>
        <div class="grid grid-3">
            <?php foreach ($related as $property): ?>
                <?php include __DIR__ . '/../../partials/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Structured data (RealEstateListing) using only verified listing information.
$schema = array_filter([
    '@context' => 'https://schema.org',
    '@type'    => 'RealEstateListing',
    'name'     => (string) $property['title'],
    'url'      => url('/real-estate/' . $property['slug']),
    'description' => \App\Core\Str::excerpt((string) $property['description'], 300),
    'image'    => $mainImg ? [MediaService::propertyImageUrl($mainImg, 1200)] : null,
    'datePosted' => date('c', strtotime((string) ($property['published_at'] ?? $property['created_at']))),
    'address'  => array_filter([
        '@type' => 'PostalAddress',
        'streetAddress'   => $property['address'] ?: null,
        'addressLocality' => $property['city'] ?: null,
        'addressRegion'   => $property['state'] ?: null,
        'addressCountry'  => (string) $property['country'],
    ], static fn ($v) => $v !== null),
]);
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
