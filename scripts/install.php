<?php
declare(strict_types=1);

/**
 * Armstrong Homes & Interior Limited — command-line installer.
 *
 * Run from the project root:
 *
 *   php scripts/install.php                 # create schema, seed defaults, prompt for admin
 *   php scripts/install.php --admin-user=admin --admin-pass='...'
 *   php scripts/install.php --seed-demo     # also insert sample categories/products/properties
 *
 * Safe to run more than once: schema statements use IF NOT EXISTS and seed
 * rows are only inserted when missing.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/app/autoload.php';
require $root . '/app/helpers.php';

use App\Core\Config;
use App\Core\Database;

$configPath = $root . '/config/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Missing config/config.php. Copy config/config.example.php and fill it in first.\n");
    exit(1);
}
Config::load($configPath);

$options = getopt('', ['admin-user::', 'admin-pass::', 'admin-name::', 'admin-email::', 'seed-demo', 'help']);

if (isset($options['help'])) {
    echo <<<TXT
    Armstrong installer

      --admin-user=USER    Administrator username (default: admin)
      --admin-pass=PASS    Administrator password (required for first install)
      --admin-name=NAME    Administrator display name
      --admin-email=EMAIL  Administrator email
      --seed-demo          Insert sample categories, brands, products and properties
      --help               Show this message

    TXT;
    exit(0);
}

function out(string $message): void
{
    echo $message . "\n";
}

function prompt(string $label, string $default = ''): string
{
    $suffix = $default !== '' ? " [$default]" : '';
    echo $label . $suffix . ': ';
    $line = trim((string) fgets(STDIN));
    return $line === '' ? $default : $line;
}

// --- 1. Schema -------------------------------------------------------------
out('-> Creating database schema...');
$schema = file_get_contents($root . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "Could not read database/schema.sql\n");
    exit(1);
}

$pdo = Database::connection();
foreach (array_filter(array_map('trim', explode(";\n", $schema))) as $statement) {
    if ($statement === '' || str_starts_with($statement, '--')) {
        continue;
    }
    try {
        $pdo->exec($statement);
    } catch (PDOException $e) {
        fwrite(STDERR, "Schema error: " . $e->getMessage() . "\n");
        exit(1);
    }
}
out('  schema ready.');

// --- 2. Administrator ------------------------------------------------------
$adminCount = (int) Database::scalar('SELECT COUNT(*) FROM administrators');
if ($adminCount === 0) {
    $username = $options['admin-user'] ?? prompt('Administrator username', 'admin');
    $name     = $options['admin-name'] ?? prompt('Administrator name', 'Administrator');
    $email    = $options['admin-email'] ?? prompt('Administrator email', 'admin@example.com');

    $password = $options['admin-pass'] ?? null;
    if ($password === null) {
        echo 'Administrator password: ';
        $password = trim((string) fgets(STDIN));
    }
    if (strlen((string) $password) < 8) {
        fwrite(STDERR, "Password must be at least 8 characters.\n");
        exit(1);
    }

    Database::insert('administrators', [
        'name'          => $name,
        'email'         => $email,
        'username'      => $username,
        'password_hash' => password_hash((string) $password, PASSWORD_DEFAULT),
        'is_active'     => 1,
    ]);
    out("-> Administrator '{$username}' created.");
} else {
    out('-> Administrator already exists; skipping.');
}

// --- 3. Default delivery rates --------------------------------------------
if ((int) Database::scalar('SELECT COUNT(*) FROM delivery_rates') === 0) {
    $rates = [
        ['Same-day delivery', 'Delivered within the same working day', 10000.00, 'Same day', 1],
        ['Two-day delivery', 'Delivered within two working days', 8000.00, '2 working days', 2],
        ['Four-day delivery', 'Delivered within four working days', 5000.00, '4 working days', 3],
    ];
    foreach ($rates as [$name, $description, $fee, $timeframe, $order]) {
        Database::insert('delivery_rates', [
            'name' => $name, 'description' => $description, 'fee' => $fee,
            'timeframe' => $timeframe, 'sort_order' => $order, 'is_active' => 1,
        ]);
    }
    out('-> Default delivery options created.');
}

// --- 4. Default settings ---------------------------------------------------
$settings = [
    ['company_name', 'Armstrong Homes & Interior Limited', 'Company', 'text'],
    ['company_tagline', 'Building materials, interior products and real estate', 'Company', 'text'],
    ['contact_phone', '', 'Contact', 'phone'],
    ['contact_phone_alt', '', 'Contact', 'phone'],
    ['contact_email', '', 'Contact', 'email'],
    ['contact_address', '', 'Contact', 'textarea'],
    ['business_hours', 'Monday - Saturday, 8:00am - 6:00pm', 'Contact', 'text'],
    ['footer_about', '', 'Footer', 'textarea'],
    ['footer_note', '', 'Footer', 'text'],
    ['social_facebook', '', 'Social', 'url'],
    ['social_instagram', '', 'Social', 'url'],
    ['social_x', '', 'Social', 'url'],
    ['social_linkedin', '', 'Social', 'url'],
    ['social_whatsapp', '', 'Social', 'url'],
    ['home_meta_title', 'Armstrong Homes & Interior Limited', 'SEO', 'text'],
    ['home_meta_description', 'Building materials, interior products and property from a trusted Nigerian supplier.', 'SEO', 'textarea'],
    ['reservation_days', (string) Config::get('reservation_days', 7), 'commerce', 'number'],
    ['allow_partial_payment', '1', 'commerce', 'boolean'],
    ['low_stock_threshold', '5', 'commerce', 'number'],
    ['enable_reviews', '1', 'commerce', 'boolean'],
    ['enable_delivery', '1', 'commerce', 'boolean'],
    ['currency_symbol', '₦', 'store', 'text'],
    ['currency_code', 'NGN', 'store', 'text'],
    ['meta_title_suffix', 'Armstrong Homes & Interior Limited', 'seo', 'text'],
    ['meta_description_default', '', 'seo', 'textarea'],
    ['google_analytics_id', '', 'seo', 'text'],
];
$inserted = 0;
foreach ($settings as [$key, $value, $group, $type]) {
    if ((int) Database::scalar('SELECT COUNT(*) FROM settings WHERE setting_key = ?', [$key]) === 0) {
        Database::insert('settings', [
            'setting_key' => $key, 'setting_value' => $value,
            'setting_group' => $group, 'setting_type' => $type,
        ]);
        $inserted++;
    }
}
out("-> {$inserted} default settings created.");

// --- 5. Content sections ---------------------------------------------------
$sections = [
    ['home_hero', 'home', 'Building materials, interior products and property - from one trusted supplier.',
        'Armstrong Homes & Interior Limited serves customers across Nigeria with dependable building materials, interior products and real estate solutions.',
        '', 'Explore Real Estate', '/real-estate', 1],
    ['home_about', 'home', 'A Nigerian supplier you can rely on', '', '', '', '', 2],
    ['promo_banner', 'home', '', '', '', '', '', 3],
    ['about_story', 'about', 'Our story', '', '', '', '', 1],
    ['about_mission', 'about', 'Our mission', '', '', '', '', 2],
    ['about_vision', 'about', 'Our vision', '', '', '', '', 3],
];
$inserted = 0;
foreach ($sections as [$key, $page, $title, $subtitle, $body, $ctaLabel, $ctaUrl, $order]) {
    if ((int) Database::scalar('SELECT COUNT(*) FROM content_sections WHERE section_key = ?', [$key]) === 0) {
        Database::insert('content_sections', [
            'section_key' => $key, 'page' => $page, 'title' => $title, 'subtitle' => $subtitle,
            'body' => $body, 'cta_label' => $ctaLabel, 'cta_url' => $ctaUrl,
            'sort_order' => $order, 'is_active' => 1,
        ]);
        $inserted++;
    }
}
out("-> {$inserted} content sections created.");

// --- 6. Optional demo catalogue -------------------------------------------
if (isset($options['seed-demo'])) {
    require __DIR__ . '/seed_demo.php';
    seed_demo();
    out('-> Demo catalogue inserted.');
}

out('');
out('Installation complete. Sign in at: ' . rtrim((string) Config::get('base_url', ''), '/') . '/' . Config::get('admin_path', 'invent') . '/login');
