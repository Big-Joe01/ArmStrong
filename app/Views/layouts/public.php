<?php
/**
 * Public site layout.
 *
 * @var string $content
 * @var array  $config
 */
use App\Core\Settings;
use App\Core\View;

$company = Settings::get('company_name', 'Armstrong Homes & Interior Limited');
$phone   = Settings::get('contact_phone', '');
$email   = Settings::get('contact_email', '');
$address = Settings::get('contact_address', '');
$noindex = $noindex ?? false;
$ogImage = $ogImage ?? null;
$title   = $title ?? $company;
$metaDescription = $metaDescription ?? '';
$canonical = $canonical ?? url('/');
?>
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($noindex): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph / social -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($company) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php if ($ogImage): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <?= $head ?? '' ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

<?php include __DIR__ . '/../partials/public-header.php'; ?>

<main id="main">
    <?php
    $success = flash('success');
    $error   = flash('error');
    ?>
    <?php if ($success || $error): ?>
        <div class="container" style="padding-top:var(--sp-5)">
            <?php if ($success): ?>
                <div class="alert alert-success" role="status">
                    <span><?= e($success) ?></span>
                    <button type="button" data-dismiss class="btn-ghost" style="margin-left:auto;background:none;border:0;color:inherit" aria-label="Dismiss">&times;</button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" role="alert">
                    <span><?= e($error) ?></span>
                    <button type="button" data-dismiss class="btn-ghost" style="margin-left:auto;background:none;border:0;color:inherit" aria-label="Dismiss">&times;</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php include __DIR__ . '/../partials/public-footer.php'; ?>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
