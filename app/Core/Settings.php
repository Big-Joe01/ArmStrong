<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Read/write application settings (backed by the settings table, cached per request).
 */
final class Settings
{
    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** @return array<string,string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $rows = [];
        try {
            $rows = Database::all('SELECT setting_key, setting_value FROM settings');
        } catch (\Throwable) {
            return self::$cache = [];
        }
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }
        return self::$cache = $map;
    }

    public static function get(string $key, string $default = ''): string
    {
        $all = self::all();
        return array_key_exists($key, $all) && $all[$key] !== '' ? $all[$key] : $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general', string $type = 'text', bool $public = true): void
    {
        $existing = Database::first('SELECT id FROM settings WHERE setting_key = ?', [$key]);
        $data     = [
            'setting_value' => $value,
            'setting_group' => $group,
            'setting_type'  => $type,
            'is_public'     => $public ? 1 : 0,
        ];
        if ($existing) {
            Database::update('settings', $data, ['id' => $existing['id']]);
        } else {
            $data['setting_key'] = $key;
            Database::insert('settings', $data);
        }
        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
