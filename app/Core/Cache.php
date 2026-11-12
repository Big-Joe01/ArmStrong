<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple file-based cache with TTL, stored in storage/cache.
 */
final class Cache
{
    private static string $dir = '';

    private static function dir(): string
    {
        if (self::$dir === '') {
            self::$dir = dirname(__DIR__, 2) . '/storage/cache';
            if (!is_dir(self::$dir)) {
                @mkdir(self::$dir, 0755, true);
            }
        }
        return self::$dir;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $file = self::path($key);
        if (!is_file($file)) {
            return $default;
        }
        $raw = @file_get_contents($file);
        if ($raw === false) {
            return $default;
        }
        $payload = @unserialize($raw);
        if (!is_array($payload) || ($payload['expires'] ?? 0) < time()) {
            @unlink($file);
            return $default;
        }
        return $payload['value'] ?? $default;
    }

    public static function put(string $key, mixed $value, int $ttlSeconds = 3600): void
    {
        @file_put_contents(self::path($key), serialize([
            'expires' => time() + $ttlSeconds,
            'value'   => $value,
        ]), LOCK_EX);
    }

    public static function forget(string $key): void
    {
        @unlink(self::path($key));
    }

    /** Remember a value, computing it when absent/expired. */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::put($key, $value, $ttl);
        return $value;
    }

    private static function path(string $key): string
    {
        return self::dir() . '/' . hash('sha256', $key) . '.cache';
    }
}
