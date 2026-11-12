<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ActivityLog;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;

/**
 * General site settings (store identity, SEO, feature switches).
 * Credentials (database, Cloudinary, SMTP) live in config/config.php and
 * are never editable from the browser.
 */
final class SettingController extends Controller
{
    private const EDITABLE = [
        'store' => [
            'company_name', 'company_tagline', 'contact_phone', 'contact_email',
            'contact_address', 'business_hours', 'currency_symbol', 'currency_code',
        ],
        'commerce' => [
            'reservation_days', 'allow_partial_payment', 'low_stock_threshold',
            'enable_reviews', 'enable_delivery',
        ],
        'seo' => [
            'meta_title_suffix', 'meta_description_default', 'google_analytics_id',
        ],
    ];

    public function index(Request $request): Response
    {
        $keys = array_merge(...array_values(self::EDITABLE));
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = Database::all(
            "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)",
            $keys
        );
        $values = [];
        foreach ($rows as $row) {
            $values[$row['setting_key']] = (string) $row['setting_value'];
        }

        return $this->adminView('admin/settings/index', [
            'title'     => 'Settings',
            'activeNav' => 'settings',
            'groups'    => self::EDITABLE,
            'values'    => $values,
            'environment' => [
                'php_version'     => PHP_VERSION,
                'app_env'         => (string) Config::get('env', 'production'),
                'base_url'        => (string) Config::get('base_url', ''),
                'cloudinary'      => Config::get('cloudinary.cloud_name') ? 'Configured' : 'Not configured',
                'smtp'            => Config::get('mail.host') ? 'Configured' : 'Not configured (emails logged)',
                'reservation_days'=> (int) Config::get('reservation_days', 7),
            ],
        ]);
    }

    public function update(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/settings');
        }

        $allowed = array_merge(...array_values(self::EDITABLE));
        $input = $request->array('settings');

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $value = trim(strip_tags((string) $input[$key]));
            $group = 'general';
            foreach (self::EDITABLE as $groupName => $groupKeys) {
                if (in_array($key, $groupKeys, true)) {
                    $group = $groupName;
                    break;
                }
            }
            Settings::set($key, $value, $group);
        }

        ActivityLog::record('update_settings', 'settings', null, 'Updated site settings.');
        Session::flash('success', 'Settings saved.');
        return $this->redirect('invent/settings');
    }
}
