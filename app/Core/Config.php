<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Loads and exposes application configuration from config/config.php.
 */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException(
                'Configuration file not found. Copy config/config.example.php to config/config.php.'
            );
        }
        $config = require $path;
        if (!is_array($config)) {
            throw new \RuntimeException('Configuration file must return an array.');
        }
        self::$items  = $config;
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value    = self::$items;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $ref      = &self::$items;
        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
    }

    public static function isDebug(): bool
    {
        return (bool) self::get('debug', false);
    }

    public static function baseUrl(string $path = ''): string
    {
        $base = rtrim((string) self::get('base_url', ''), '/');
        return $path === '' ? $base : $base . '/' . ltrim($path, '/');
    }
}
