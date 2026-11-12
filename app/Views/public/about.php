<?php
/**
 * About Us page.
 *
 * @var array|null $mission
 * @var array|null $vision
 * @var array|null $story
 */
use App\Core\Settings;
?>
<section class="section section-ink" style="padding-block:var(--sp-8)">
    <div class="container">
        <span class="eyebrow">About us</span>
        <h1 style="max-width:22ch"><?= e(Settings::get('company_name', 'Armstrong Homes & Interior Limited')) ?></h1>
        <p style="color:#C9D4E1;max-width:64ch;margin-bottom:0">
            We supply building materials and interior products, and provide real estate solutions. Our work is built around dependable stock, clear pricing and responsive service.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8)">
            <div>
                <span class="eyebrow">Company introduction</span>
                <h2>Who we are</h2>
                <p>
                    <?= e($story['body'] ?? Settings::get('about_intro', 'Armstrong Homes & Interior Limited operates two divisions: Real Estate, and Building Materials & Interior Products. The company serves individual homeowners, contractors, and businesses that need materials, finishes and property solutions from a single supplier.')) ?>
                </p>
                <p class="muted">
                    We do not make claims we cannot support. Where a figure, certification or partnership is not verified, we leave it out rather than publish something inaccurate.
                </p>
            </div>
            <div class="grid" style="gap:var(--sp-4)">
                <div class="value-card">
                    <h4>Real Estate</h4>
                    <p>Residential and commercial property listings — houses, apartments, land, offices and retail space — available for sale, rent and lease.</p>
                </div>
                <div class="value-card">
                    <h4>Building Materials &amp; Interior</h4>
                    <p>Tiles, sanitary ware, doors, windows, lighting, plumbing, ceiling, flooring, paints, interior accessories and furnishings.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="grid grid-2">
            <div class="value-card">
                <span class="eyebrow">Mission</span>
                <h3><?= e($mission['title'] ?? 'Our mission') ?></h3>
                <p class="muted" style="font-size:var(--fs-sm)">
                    <?= e($mission['body'] ?? 'To make quality building materials, interior products and property opportunities accessible, with clear pricing and dependable service for every customer.') ?>
                </p>
            </div>
            <div class="value-card">
                <span class="eyebrow">Vision</span>
                <h3><?= e($vision['title'] ?? 'Our vision') ?></h3>
                <p class="muted" style="font-size:var(--fs-sm)">
                    <?= e($vision['body'] ?? 'To be a trusted name in building materials and real estate — known for reliability, honest dealing and consistent stock.') ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Core values</span>
            <h2>What guides our work</h2>
        </div>
        <div class="grid grid-3">
            <div class="value-card">
                <h4>Reliability</h4>
                <p>If a product or property is listed, it reflects what we actually hold or represent.</p>
            </div>
            <div class="value-card">
                <h4>Transparency</h4>
                <p>Prices, delivery fees and reservation periods are stated clearly before you commit.</p>
            </div>
            <div class="value-card">
                <h4>Professionalism</h4>
                <p>Enquiries and orders are handled promptly, with a record of every transaction.</p>
            </div>
            <div class="value-card">
                <h4>Customer service</h4>
                <p>We help customers confirm quantities, specifications and arrangements before purchase.</p>
            </div>
            <div class="value-card">
                <h4>Quality</h4>
                <p>We stock products we are prepared to stand behind and represent them accurately.</p>
            </div>
            <div class="value-card">
                <h4>Accountability</h4>
                <p>Inventory and orders are tracked so that records can be traced and verified.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="grid grid-2" style="gap:var(--sp-8);align-items:start">
            <div>
                <span class="eyebrow">Business philosophy</span>
                <h2>How we operate</h2>
                <p class="muted">
                    We keep the process straightforward. Products and properties are published from our database, so availability and pricing stay current. Orders are recorded with a reference, and stock is reserved so that what a customer has chosen is held for them.
                </p>
                <p class="muted">
                    Payment is completed through our approved in-person process. We do not accept card payments online, and an order is only marked as paid once our team has recorded the payment.
                </p>
            </div>
            <div>
                <span class="eyebrow">Capabilities</span>
                <h2>What we can handle</h2>
                <ul class="feature-list" style="grid-template-columns:minmax(0,1fr)">
                    <li>Property listing and enquiry handling</li>
                    <li>Building materials supply by unit and quantity</li>
                    <li>Interior products and finishes</li>
                    <li>Store collection and arranged delivery</li>
                    <li>Order tracking and inventory records</li>
                    <li>Enquiry and after-sales support</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="value-card" style="text-align:center;padding:var(--sp-8)">
            <h2 style="margin-bottom:var(--sp-3)">Talk to us about your project</h2>
            <p class="muted" style="max-width:56ch;margin-inline:auto">
                Whether you are buying materials, furnishing a space, or looking for property, our team can help you confirm what is available.
            </p>
            <div class="flex" style="justify-content:center;gap:var(--sp-3);flex-wrap:wrap">
                <a class="btn btn-primary btn-inline" href="<?= e(url('/contact')) ?>">Contact us</a>
                <a class="btn btn-outline btn-inline" href="<?= e(url('/building-materials')) ?>">Browse products</a>
                <a class="btn btn-outline btn-inline" href="<?= e(url('/real-estate')) ?>">Browse properties</a>
            </div>
        </div>
    </div>
</section>
