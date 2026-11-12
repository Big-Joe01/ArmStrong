<?php
/**
 * Public header: top bar, brand, main navigation, mobile drawer.
 */
use App\Core\Request;
use App\Core\Settings;

$path    = Request::capture()->path;
$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
$phone   = Settings::get('contact_phone', '');
$email   = Settings::get('contact_email', '');

$isActive = static function (string $prefix) use ($path): string {
    if ($prefix === '/') {
        return $path === '/' ? 'active' : '';
    }
    return str_starts_with($path, $prefix) ? 'active' : '';
};
?>
<header class="site-header" data-site-header>
    <div class="header-top">
        <div class="container">
            <div class="flex flex-wrap items-center" style="gap:var(--sp-4)">
                <?php if ($phone !== ''): ?>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                <?php endif; ?>
            </div>
            <div>Real Estate &middot; Building Materials &amp; Interior Products</div>
        </div>
    </div>

    <div class="container">
        <div class="header-main">
            <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e($company) ?> home">
                <span class="brand-mark" aria-hidden="true">A</span>
                <span class="brand-text">
                    <span class="brand-name">Armstrong</span>
                    <span class="brand-sub">Homes &amp; Interior</span>
                </span>
            </a>

            <nav class="nav" aria-label="Main navigation">
                <a class="nav-link <?= $isActive('/') ?>" href="<?= e(url('/')) ?>">Home</a>
                <a class="nav-link <?= $isActive('/real-estate') ?>" href="<?= e(url('/real-estate')) ?>">Real Estate</a>
                <a class="nav-link <?= $isActive('/building-materials') ?>" href="<?= e(url('/building-materials')) ?>">Building Materials</a>
                <span class="nav-divider" aria-hidden="true"></span>
                <a class="nav-link <?= $isActive('/about') ?>" href="<?= e(url('/about')) ?>">About</a>
                <a class="nav-link <?= $isActive('/contact') ?>" href="<?= e(url('/contact')) ?>">Contact</a>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/building-materials')) ?>" style="margin-left:var(--sp-2)">Shop Now</a>
            </nav>

            <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav">
                <span></span>
            </button>
        </div>
    </div>

    <!-- Mobile drawer -->
    <div class="mobile-nav" id="mobile-nav" data-mobile-nav>
        <div class="mobile-nav-head">
            <span class="brand">
                <span class="brand-mark" aria-hidden="true">A</span>
                <span class="brand-text">
                    <span class="brand-name">Armstrong</span>
                    <span class="brand-sub">Homes &amp; Interior</span>
                </span>
            </span>
            <button class="nav-toggle is-open" type="button" data-nav-toggle aria-label="Close menu"><span></span></button>
        </div>
        <a class="mobile-nav-link" href="<?= e(url('/')) ?>">Home</a>
        <a class="mobile-nav-link" href="<?= e(url('/real-estate')) ?>">Real Estate</a>
        <div class="mobile-nav-sub">
            <a href="<?= e(url('/real-estate?type=for_sale')) ?>">Properties for sale</a>
            <a href="<?= e(url('/real-estate?type=for_rent')) ?>">Properties for rent</a>
            <a href="<?= e(url('/real-estate?type=for_lease')) ?>">Properties for lease</a>
        </div>
        <a class="mobile-nav-link" href="<?= e(url('/building-materials')) ?>">Building Materials &amp; Interior</a>
        <a class="mobile-nav-link" href="<?= e(url('/about')) ?>">About Us</a>
        <a class="mobile-nav-link" href="<?= e(url('/contact')) ?>">Contact</a>
        <div style="margin-top:var(--sp-5);display:grid;gap:var(--sp-3)">
            <a class="btn btn-primary btn-block" href="<?= e(url('/building-materials')) ?>">Browse Products</a>
            <?php if ($phone !== ''): ?>
                <a class="btn btn-outline btn-block" href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>">Call <?= e($phone) ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>
