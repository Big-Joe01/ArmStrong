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
        $base = rtrim(self::detectBaseUrl(), '/');
        return $path === '' ? $base : $base . '/' . ltrim($path, '/');
    }

    /**
     * Resolve the site's base URL. A configured base_url wins; when it is blank
     * the URL is derived from the incoming request so the app also works behind
     * a proxy or on a host whose domain is not known at configuration time.
     */
    private static function detectBaseUrl(): string
    {
        $configured = trim((string) self::get('base_url', ''));
        if ($configured !== '' && $configured !== 'auto') {
            return $configured;
        }

        if (PHP_SAPI === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            return $configured === 'auto' ? '' : $configured;
        }

        $https = ($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off';
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            $https = true;
        }
        $scheme = $https ? 'https' : 'http';

        $host = (string) $_SERVER['HTTP_HOST'];
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $dir = $dir === '.' ? '' : $dir;

        // The admin area lives in its own sub-directory and its routes already
        // include that prefix (url('invent/...')), so strip it from the base.
        $adminPath = '/' . trim((string) self::get('admin_path', 'invent'), '/');
        if ($adminPath !== '/' && $dir === $adminPath) {
            $dir = '';
        }

        return $scheme . '://' . $host . $dir;
    }
}
