<?php
/**
 * Administration layout: sidebar navigation, top bar, content.
 *
 * @var string $content
 */
use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;

$active = $activeNav ?? '';
$admin  = Auth::user();
$base   = url('invent');

// Badge counts for the navigation.
$badges = [
    'messages'  => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'"),
    'enquiries' => (int) Database::scalar("SELECT COUNT(*) FROM property_enquiries WHERE status = 'new'"),
    'reviews'   => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
    'orders'    => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE status = 'awaiting_payment'"),
];

$nav = static function (string $key, string $path, string $label, int $count = 0) use ($active, $base): string {
    $cls = $key === $active ? 'active' : '';
    $badge = $count > 0 ? '<span class="count">' . $count . '</span>' : '';
    return '<a class="' . $cls . '" href="' . e($base . $path) . '">' . e($label) . $badge . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Administration') ?> | <?= e(Settings::get('company_name', 'Armstrong')) ?> Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <?= $head ?? '' ?>
</head>
<body class="admin-body">
<div class="admin-backdrop" data-admin-backdrop></div>
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <div class="admin-brand">
            <span class="brand-mark" aria-hidden="true">A</span>
            <span class="brand-text">
                <span class="brand-name">Armstrong Admin</span>
                <span class="brand-sub">Control panel</span>
            </span>
        </div>

        <nav class="admin-nav" aria-label="Administration">
            <?php
            $groups = [
                'Overview' => [
                    ['dashboard', '/', 'Dashboard', 0],
                ],
                'Catalogue' => [
                    ['products', '/products', 'Products', 0],
                    ['categories', '/product-categories', 'Product Categories', 0],
                    ['brands', '/brands', 'Brands', 0],
                    ['inventory', '/inventory', 'Inventory', 0],
                ],
                'Sales' => [
                    ['orders', '/orders', 'Orders', $badges['orders']],
                    ['walkin', '/walk-in-sales', 'Walk-in Sales', 0],
                    ['payments', '/payments', 'Payments', 0],
                    ['delivery', '/delivery-settings', 'Delivery Settings', 0],
                ],
                'Real Estate' => [
                    ['properties', '/properties', 'Properties', 0],
                    ['property_categories', '/property-categories', 'Property Categories', 0],
                    ['enquiries', '/enquiries', 'Enquiries', $badges['enquiries']],
                ],
                'Customers' => [
                    ['reviews', '/reviews', 'Customer Reviews', $badges['reviews']],
                    ['messages', '/messages', 'Contact Messages', $badges['messages']],
                ],
                'Configuration' => [
                    ['content', '/content', 'Website Content', 0],
                    ['settings', '/settings', 'Settings', 0],
                    ['activity', '/activity', 'Activity Log', 0],
                ],
            ];
            foreach ($groups as $groupName => $items):
                echo '<div class="admin-nav-group">' . e($groupName) . '</div>';
                foreach ($items as [$key, $path, $label, $count]):
                    echo $nav($key, $path, $label, (int) $count);
                endforeach;
            endforeach;
            ?>
        </nav>

        <div style="padding:var(--sp-4) var(--sp-3);border-top:1px solid rgba(255,255,255,0.08)">
            <div style="font-size:var(--fs-xs);color:#7D8B9C;padding:0 var(--sp-3) var(--sp-3)">
                <?= e($admin['name'] ?? 'Administrator') ?>
            </div>
            <a href="<?= e($base . '/password') ?>">Change password</a>
            <a href="<?= e($base . '/logout') ?>">Logout</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="flex items-center" style="gap:var(--sp-3)">
                <button class="btn btn-outline btn-sm admin-menu-toggle" type="button" data-admin-menu aria-label="Toggle menu">Menu</button>
                <div>
                    <h1><?= e($title ?? 'Administration') ?></h1>
                    <?php if (!empty($subtitle)): ?><div class="sub"><?= e($subtitle) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="flex items-center" style="gap:var(--sp-2)">
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">View site</a>
                <a class="btn btn-outline btn-sm" href="<?= e($base . '/logout') ?>">Logout</a>
            </div>
        </header>

        <main class="admin-content">
            <?php
            $success = flash('success');
            $error   = flash('error');
            ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="status">
                    <span><?= e($success) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" role="alert">
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors) && is_array($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <ul style="margin:0;padding-left:1.1rem">
                        <?php foreach ($errors as $fieldError): ?>
                            <li><?= e((string) $fieldError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
