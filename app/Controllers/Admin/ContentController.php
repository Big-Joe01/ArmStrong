<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ActivityLog;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;

/**
 * Editable website content: homepage/section blocks (content_sections) and
 * plain-text site fields (settings). Markup is stripped from text fields and
 * every value is escaped on output, so no arbitrary HTML, PHP or JavaScript
 * can be injected through the admin content editor.
 */
final class ContentController extends Controller
{
    /** Content blocks the public pages read, with the fields they expose. */
    private const SECTIONS = [
        'home_hero'     => ['label' => 'Homepage hero',          'fields' => ['title', 'subtitle', 'body', 'cta_label', 'cta_url']],
        'home_about'    => ['label' => 'Homepage about',         'fields' => ['title', 'body']],
        'promo_banner'  => ['label' => 'Promotional banner',     'fields' => ['title', 'subtitle', 'cta_label', 'cta_url']],
        'about_story'   => ['label' => 'About — company story',  'fields' => ['title', 'body']],
        'about_mission' => ['label' => 'About — mission',        'fields' => ['title', 'body']],
        'about_vision'  => ['label' => 'About — vision',         'fields' => ['title', 'body']],
    ];

    /** Plain-text site fields, grouped for the editor. */
    private const FIELDS = [
        'Company' => ['company_name', 'company_tagline', 'about_intro'],
        'Contact' => ['contact_phone', 'contact_phone_alt', 'contact_email', 'contact_address', 'business_hours'],
        'Footer'  => ['footer_about', 'footer_note'],
        'Social'  => ['social_facebook', 'social_instagram', 'social_x', 'social_linkedin', 'social_whatsapp'],
        'SEO'     => ['home_meta_title', 'home_meta_description'],
    ];

    public function index(Request $request): Response
    {
        $keys = array_merge(...array_values(self::FIELDS));
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = Database::all(
            "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)",
            $keys
        );
        $values = [];
        foreach ($rows as $row) {
            $values[$row['setting_key']] = (string) $row['setting_value'];
        }

        $sectionRows = Database::all(
            'SELECT * FROM content_sections WHERE section_key IN ('
            . implode(',', array_fill(0, count(self::SECTIONS), '?')) . ')',
            array_keys(self::SECTIONS)
        );
        $sections = [];
        foreach ($sectionRows as $row) {
            $sections[$row['section_key']] = $row;
        }

        return $this->adminView('admin/content/index', [
            'title'       => 'Website content',
            'activeNav'   => 'content',
            'sections'    => self::SECTIONS,
            'sectionData' => $sections,
            'groups'      => self::FIELDS,
            'values'      => $values,
        ]);
    }

    /** Save one content section block. */
    public function updateSection(Request $request, string $key): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/content');
        }
        if (!isset(self::SECTIONS[$key])) {
            Session::flash('error', 'That content section is not editable.');
            return $this->redirect('invent/content');
        }

        $payload = [];
        foreach (self::SECTIONS[$key]['fields'] as $field) {
            $payload[$field] = $this->clean($request->string($field));
        }
        $payload['is_active'] = $request->input('is_active') ? 1 : 0;

        $existing = Database::first('SELECT id FROM content_sections WHERE section_key = ?', [$key]);
        if ($existing) {
            Database::update('content_sections', $payload, ['id' => (int) $existing['id']]);
        } else {
            $payload['section_key'] = $key;
            $payload['page'] = str_starts_with($key, 'about_') ? 'about' : 'home';
            Database::insert('content_sections', $payload);
        }

        ActivityLog::record('update_content', 'content_section', null, 'Updated content section: ' . $key);
        Session::flash('success', self::SECTIONS[$key]['label'] . ' updated.');
        return $this->redirect('invent/content');
    }

    /** Save one plain-text site field. */
    public function update(Request $request, string $key): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/content');
        }

        $allowed = array_merge(...array_values(self::FIELDS));
        if (!in_array($key, $allowed, true)) {
            Session::flash('error', 'That content field is not editable.');
            return $this->redirect('invent/content');
        }

        $group = 'content';
        foreach (self::FIELDS as $groupName => $groupKeys) {
            if (in_array($key, $groupKeys, true)) {
                $group = strtolower($groupName);
                break;
            }
        }

        Settings::set($key, $this->clean($request->string('value')), $group);
        ActivityLog::record('update_content', 'setting', null, 'Updated content field: ' . $key);
        Session::flash('success', 'Content updated.');
        return $this->redirect('invent/content');
    }

    private function clean(string $value): ?string
    {
        $value = trim(strip_tags($value));
        return $value === '' ? null : $value;
    }
}
