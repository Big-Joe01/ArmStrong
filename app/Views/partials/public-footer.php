<?php
/**
 * Public footer: company info, division links, contact, social.
 */
use App\Core\Settings;

$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
$phone   = Settings::get('contact_phone', '');
$email   = Settings::get('contact_email', '');
$address = Settings::get('contact_address', '');
$year    = date('Y');
$socials = [
    'facebook'  => Settings::get('social_facebook'),
    'instagram' => Settings::get('social_instagram'),
    'x'         => Settings::get('social_x'),
    'linkedin'  => Settings::get('social_linkedin'),
    'whatsapp'  => Settings::get('social_whatsapp'),
];
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="brand" style="margin-bottom:var(--sp-4)">
                    <span class="brand-mark" aria-hidden="true">A</span>
                    <span class="brand-text">
                        <span class="brand-name">Armstrong</span>
                        <span class="brand-sub">Homes &amp; Interior</span>
                    </span>
                </span>
                <p style="max-width:34ch">
                    <?= e(Settings::get('footer_about', 'Real estate solutions and building materials, interior products and furnishings — supplied with reliability and professional service.')) ?>
                </p>
                <?php if (array_filter($socials)): ?>
                    <div class="social-row">
                        <?php foreach ($socials as $network => $link): ?>
                            <?php if ($link): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(ucfirst($network)) ?>">
                                    <?= e(strtoupper(substr($network, 0, 1))) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h4>Real Estate</h4>
                <ul class="footer-links">
                    <li><a href="<?= e(url('/real-estate')) ?>">All Properties</a></li>
                    <li><a href="<?= e(url('/real-estate?type=for_sale')) ?>">For Sale</a></li>
                    <li><a href="<?= e(url('/real-estate?type=for_rent')) ?>">For Rent</a></li>
                    <li><a href="<?= e(url('/real-estate?type=for_lease')) ?>">For Lease</a></li>
                </ul>
            </div>

            <div>
                <h4>Building Materials</h4>
                <ul class="footer-links">
                    <li><a href="<?= e(url('/building-materials')) ?>">All Products</a></li>
                    <li><a href="<?= e(url('/building-materials?availability=available')) ?>">Available Now</a></li>
                    <li><a href="<?= e(url('/building-materials?sort=price_asc')) ?>">Shop by Price</a></li>
                    <li><a href="<?= e(url('/about')) ?>">About Us</a></li>
                </ul>
            </div>

            <div>
                <h4>Contact</h4>
                <ul class="footer-links">
                    <?php if ($address !== ''): ?>
                        <li><?= nl2br(e($address)) ?></li>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                        <li><a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a></li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
                    <?php endif; ?>
                    <li><a href="<?= e(url('/contact')) ?>">Send an enquiry</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= e($year) ?> <?= e($company) ?>. All rights reserved.</span>
            <span>Prices in Nigerian Naira (₦). Payment is completed through our approved in-person process.</span>
        </div>
    </div>
</footer>
