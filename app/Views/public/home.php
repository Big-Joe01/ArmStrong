<?php
/**
 * Homepage.
 *
 * @var array|null $hero
 * @var array|null $about
 * @var array|null $banner
 * @var list<array> $featuredProperties
 * @var list<array> $featuredProducts
 * @var list<array> $categories
 * @var list<array> $reviews
 */
use App\Core\Settings;
use App\Core\Str;
use App\Services\MediaService;

$heroTitle    = $hero['title']    ?? 'Building materials, interior products and property — from one trusted supplier.';
$heroSubtitle = $hero['subtitle'] ?? 'Armstrong Homes & Interior Limited serves customers across Nigeria with dependable building materials, interior products and real estate solutions.';
$heroBody     = $hero['body']     ?? '';
?>
<section class="hero">
    <div class="container">
        <div class="hero-inner">
            <span class="eyebrow" style="color:var(--brand-accent)">Real Estate &middot; Building Materials &middot; Interior</span>
            <h1><?= e($heroTitle) ?></h1>
            <p><?= e($heroSubtitle) ?></p>
            <?php if ($heroBody !== ''): ?>
                <p style="color:#9FB0C4;font-size:var(--fs-sm)"><?= e($heroBody) ?></p>
            <?php endif; ?>
            <div class="hero-actions">
                <a class="btn btn-accent btn-lg" href="<?= e(url('/real-estate')) ?>">Explore Real Estate</a>
                <a class="btn btn-light btn-lg" href="<?= e(url('/building-materials')) ?>">Explore Building Materials</a>
            </div>
        </div>
    </div>
</section>

<!-- Two business divisions -->
<section class="section">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">Two divisions, one standard of service</span>
            <h2>What we do</h2>
            <p class="lead">Armstrong Homes &amp; Interior Limited operates two complementary divisions. Browse either division to see what is currently available.</p>
        </div>

        <div class="grid grid-2">
            <article class="division-card card-hover">
                <div class="card-media">
                    <img src="<?= e(asset('img/division-real-estate.svg')) ?>" alt="Property and real estate" loading="lazy">
                </div>
                <div class="division-card-body">
                    <h3>Real Estate</h3>
                    <p class="muted">Explore properties available for sale, rent and lease across residential and commercial categories.</p>
                    <ul class="division-list">
                        <li>Houses, apartments and duplexes</li>
                        <li>Land, plots and commercial space</li>
                        <li>Offices, shops and retail units</li>
                    </ul>
                    <a class="btn btn-primary" href="<?= e(url('/real-estate')) ?>">View Properties</a>
                </div>
            </article>

            <article class="division-card card-hover">
                <div class="card-media">
                    <img src="<?= e(asset('img/division-building-materials.svg')) ?>" alt="Building materials and interior products" loading="lazy">
                </div>
                <div class="division-card-body">
                    <h3>Building Materials &amp; Interior</h3>
                    <p class="muted">Order building materials, fittings, finishes and interior products with collection or delivery.</p>
                    <ul class="division-list">
                        <li>Tiles, sanitary ware and bathroom fittings</li>
                        <li>Doors, windows, ceiling and flooring</li>
                        <li>Lighting, plumbing and electrical materials</li>
                    </ul>
                    <a class="btn btn-primary" href="<?= e(url('/building-materials')) ?>">Browse Products</a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Featured properties -->
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

<!-- Featured products -->
<?php if ($featuredProducts): ?>
<section class="section">
    <div class="container">
        <div class="flex-between flex-wrap mb-6">
            <div class="section-head mb-0">
                <span class="eyebrow">Building Materials &amp; Interior</span>
                <h2>Featured products</h2>
            </div>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/building-materials')) ?>">View all products</a>
        </div>

        <div class="grid grid-4">
            <?php foreach ($featuredProducts as $product): ?>
                <?php include __DIR__ . '/../partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
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
                <a class="btn btn-accent" href="<?= e(url('/about')) ?>">Learn more about us</a>
            </div>
            <div class="grid" style="gap:var(--sp-4)">
                <div class="value-card" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.12)">
                    <h4 style="color:#fff">Reliable stock</h4>
                    <p style="color:#A9B6C6">Inventory is tracked per product, so availability shown online reflects what we hold.</p>
                </div>
                <div class="value-card" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.12)">
                    <h4 style="color:#fff">Clear process</h4>
                    <p style="color:#A9B6C6">Orders are reserved for seven days so you have time to confirm payment and arrange collection or delivery.</p>
                </div>
                <div class="value-card" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.12)">
                    <h4 style="color:#fff">Two divisions</h4>
                    <p style="color:#A9B6C6">Property and materials under one roof — useful when a project spans both.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why choose us -->
<section class="section">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">Why choose Armstrong</span>
            <h2>How we work</h2>
        </div>
        <div class="grid grid-4">
            <div class="value-card">
                <div class="value-icon" aria-hidden="true">1</div>
                <h4>Browse with confidence</h4>
                <p>Products and properties are listed from our live database, not from static sample pages.</p>
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

<!-- Capabilities and services -->
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Capabilities &amp; services</span>
            <h2>What we can support</h2>
        </div>
        <div class="grid grid-3">
            <div class="value-card">
                <h4>Property sales &amp; letting</h4>
                <p>Listing and matching residential and commercial property with buyers, tenants and lessees.</p>
            </div>
            <div class="value-card">
                <h4>Building materials supply</h4>
                <p>Tiles, sanitary ware, plumbing, electrical, ceiling, flooring and general building supplies.</p>
            </div>
            <div class="value-card">
                <h4>Interior products &amp; finishes</h4>
                <p>Doors, windows, lighting, paints, interior accessories and furnishings.</p>
            </div>
            <div class="value-card">
                <h4>Project quantities</h4>
                <p>Orders are placed per unit of measure, with quantities confirmed before collection or delivery.</p>
            </div>
            <div class="value-card">
                <h4>Store collection</h4>
                <p>Collect confirmed orders directly from our location at no additional charge.</p>
            </div>
            <div class="value-card">
                <h4>Delivery arrangement</h4>
                <p>Choose a delivery timeframe at checkout; the fee is shown before you submit your order.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured categories -->
<?php if ($categories): ?>
<section class="section">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow">Product categories</span>
            <h2>Shop by category</h2>
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

<!-- Reviews (only genuine, approved reviews) -->
<?php if ($reviews): ?>
<section class="section section-alt">
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
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <span class="eyebrow">Get in touch</span>
                <h2>Have a question about a property or a product?</h2>
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
