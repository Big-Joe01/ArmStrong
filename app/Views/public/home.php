<?php
/**
 * Homepage — products first, real estate second.
 *
 * @var array|null $hero
 * @var string     $heroImage
 * @var array|null $about
 * @var array|null $banner
 * @var list<array> $featuredProperties
 * @var list<array> $featuredProducts
 * @var list<array> $latestProducts
 * @var list<array> $categories
 * @var list<array> $brands
 * @var list<array> $partners
 * @var list<array> $reviews
 */
use App\Core\Settings;
use App\Core\Str;
use App\Services\MediaService;

$heroTitle    = $hero['title']    ?? 'Building materials and interior products, supplied with confidence.';
$heroSubtitle = $hero['subtitle'] ?? 'Tiles, sanitary ware, doors, lighting, finishes and more — ordered online with store collection or delivery across Nigeria.';
$heroBody     = $hero['body']     ?? '';
// Featured products first, then newest, so the grid stays full even when
// only a few products are flagged as featured.
$shopProducts = [];
$seenProducts = [];
foreach (array_merge($featuredProducts, $latestProducts) as $product) {
    $id = (int) $product['id'];
    if (!isset($seenProducts[$id])) {
        $seenProducts[$id] = true;
        $shopProducts[] = $product;
    }
}
$shopProducts = array_slice($shopProducts, 0, 8);
?>
<section class="hero">
    <div class="hero-bg">
        <img src="<?= e($heroImage) ?>" alt="" width="1600" height="900" fetchpriority="high">
    </div>
    <div class="container">
        <div class="hero-inner">
            <span class="eyebrow">Building Materials &middot; Interior Products &middot; Real Estate</span>
            <h1><?= e($heroTitle) ?></h1>
            <p><?= e($heroSubtitle) ?></p>
            <?php if ($heroBody !== ''): ?>
                <p style="font-size:var(--fs-sm);color:rgba(255,255,255,0.75)"><?= e($heroBody) ?></p>
            <?php endif; ?>
            <div class="hero-actions">
                <a class="btn btn-primary btn-lg" href="<?= e(url('/building-materials')) ?>">Shop Building Materials</a>
                <a class="btn btn-light btn-lg" href="<?= e(url('/real-estate')) ?>">Explore Real Estate</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured products (primary focus) -->
<?php if ($shopProducts): ?>
<section class="section">
    <div class="container">
        <div class="flex-between flex-wrap mb-6">
            <div class="section-head mb-0">
                <span class="eyebrow">Building Materials &amp; Interior</span>
                <h2>Featured products</h2>
            </div>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/building-materials')) ?>">View all products</a>
        </div>

        <?php if ($categories): ?>
        <div class="quick-links mb-6">
            <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                <a href="<?= e(url('/building-materials?category=' . $category['slug'])) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-4">
            <?php foreach ($shopProducts as $product): ?>
                <?php include __DIR__ . '/../partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Shop by category -->
<?php if ($categories): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">Product categories</span>
            <h2>Shop by category</h2>
            <p class="lead">Browse the full range of building materials, fittings, finishes and interior products.</p>
        </div>
        <div class="grid grid-4">
            <?php foreach ($categories as $category): ?>
                <a class="category-tile" href="<?= e(url('/building-materials?category=' . $category['slug'])) ?>">
                    <h4><?= e($category['name']) ?></h4>
                    <span><?= (int) $category['product_count'] ?> product<?= (int) $category['product_count'] === 1 ? '' : 's' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- How ordering works -->
<section class="section">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">How ordering works</span>
            <h2>Straightforward from start to finish</h2>
        </div>
        <div class="grid grid-4">
            <div class="value-card">
                <div class="value-icon" aria-hidden="true">1</div>
                <h4>Live stock levels</h4>
                <p>Availability shown online reflects what we hold, tracked per product in our inventory system.</p>
            </div>
            <div class="value-card">
                <div class="value-icon" aria-hidden="true">2</div>
                <h4>Order without an account</h4>
                <p>Place an order as a guest with your name, phone number and delivery preference.</p>
            </div>
            <div class="value-card">
                <div class="value-icon" aria-hidden="true">3</div>
                <h4>Seven-day reservation</h4>
                <p>We hold your selected stock for seven days while payment and collection are arranged.</p>
            </div>
            <div class="value-card">
                <div class="value-icon" aria-hidden="true">4</div>
                <h4>Collection or delivery</h4>
                <p>Collect from our store or choose a delivery option with a clearly stated fee.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured properties (secondary) -->
<?php if ($featuredProperties): ?>
<section class="section section-alt">
    <div class="container">
        <div class="flex-between flex-wrap mb-6">
            <div class="section-head mb-0">
                <span class="eyebrow">Real Estate</span>
                <h2>Featured properties</h2>
            </div>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/real-estate')) ?>">View all properties</a>
        </div>

        <div class="grid grid-3">
            <?php foreach ($featuredProperties as $property): ?>
                <?php include __DIR__ . '/../partials/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Partners / suppliers (only shown when real partners are recorded) -->
<?php if (!empty($partners)): ?>
<section class="section partners-section">
    <div class="container">
        <div class="partners-head">
            <span class="eyebrow">Our partners</span>
            <h2>Brands we supply</h2>
            <p>We source from established manufacturers, so the specification you order is the specification you receive.</p>
        </div>
        <ul class="partners-strip" role="list">
            <?php foreach ($partners as $partner): ?>
                <li class="partner-item">
                    <?php if (!empty($partner['logo_url'])): ?>
                        <img src="<?= e(MediaService::productImageUrl($partner['logo_url'], 320)) ?>"
                             alt="<?= e($partner['name']) ?>" loading="lazy" width="160" height="64">
                    <?php else: ?>
                        <span class="partner-name"><?= e($partner['name']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- Company introduction -->
<section class="section section-ink">
    <div class="container">
        <div class="grid grid-2" style="align-items:center;gap:var(--sp-8)">
            <div>
                <span class="eyebrow">About Armstrong</span>
                <h2><?= e($about['title'] ?? 'A dependable partner for building and property') ?></h2>
                <p style="color:#C9D4E1">
                    <?= e($about['body'] ?? 'Armstrong Homes & Interior Limited supplies building materials and interior products, and offers real estate solutions. We focus on reliable stock, clear pricing and responsive service so that customers can plan their projects with confidence.') ?>
                </p>
                <a class="btn btn-primary" href="<?= e(url('/about')) ?>">Learn more about us</a>
            </div>
            <div class="division-pair">
                <a class="division-tile" href="<?= e(url('/building-materials')) ?>">
                    <img src="<?= e(asset('img/division-building-materials.svg')) ?>" alt="" width="56" height="56" loading="lazy">
                    <div>
                        <h4>Building Materials &amp; Interior</h4>
                        <p>Tiles, sanitary ware, doors, lighting and finishes, held in stock.</p>
                    </div>
                </a>
                <a class="division-tile" href="<?= e(url('/real-estate')) ?>">
                    <img src="<?= e(asset('img/division-real-estate.svg')) ?>" alt="" width="56" height="56" loading="lazy">
                    <div>
                        <h4>Real Estate</h4>
                        <p>Homes, land and commercial space available to buy, rent or lease.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Reviews (only genuine, approved reviews) -->
<?php if ($reviews): ?>
<section class="section">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">Customer feedback</span>
            <h2>What customers say</h2>
        </div>
        <div class="grid grid-3">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <span class="stars" aria-label="<?= (int) $review['rating'] ?> out of 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= (int) $review['rating'] ? '' : 'off' ?>">&#9733;</span>
                        <?php endfor; ?>
                    </span>
                    <blockquote>&ldquo;<?= e(Str::excerpt((string) $review['body'], 220)) ?>&rdquo;</blockquote>
                    <div class="review-author">
                        <?= e($review['customer_name']) ?>
                        <?php if (!empty($review['product_name'])): ?>
                            <span class="muted" style="font-weight:400"> &middot; <?= e($review['product_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact / enquiry -->
<section class="section section-alt">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <span class="eyebrow">Get in touch</span>
                <h2>Have a question about a product or a property?</h2>
                <p class="muted">Send us an enquiry and our team will respond. You can also call or email us directly.</p>
                <ul class="footer-links" style="color:var(--c-text)">
                    <?php if ($p = Settings::get('contact_phone')): ?>
                        <li><strong>Phone:</strong> <a href="tel:<?= e(preg_replace('/\s+/', '', $p)) ?>"><?= e($p) ?></a></li>
                    <?php endif; ?>
                    <?php if ($em = Settings::get('contact_email')): ?>
                        <li><strong>Email:</strong> <a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li>
                    <?php endif; ?>
                    <?php if ($ad = Settings::get('contact_address')): ?>
                        <li><strong>Address:</strong> <?= e($ad) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <form method="post" action="<?= e(url('/contact')) ?>" class="card" style="padding:var(--sp-5)">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="home-name">Full name <span class="req">*</span></label>
                        <input class="form-control" type="text" id="home-name" name="full_name" required maxlength="150">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="home-email">Email <span class="req">*</span></label>
                            <input class="form-control" type="email" id="home-email" name="email" required maxlength="190">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="home-phone">Phone</label>
                            <input class="form-control" type="tel" id="home-phone" name="phone" maxlength="40">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="home-subject">Subject <span class="req">*</span></label>
                        <input class="form-control" type="text" id="home-subject" name="subject" required maxlength="200">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="home-message">Message <span class="req">*</span></label>
                        <textarea class="form-control" id="home-message" name="message" required minlength="10" maxlength="5000"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">Send enquiry</button>
                </form>
            </div>
        </div>
    </div>
</section>
