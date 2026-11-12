<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;

if (!function_exists('e')) {
    /** Escape for HTML output. */
    function e(mixed $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('url')) {
    /** Build an absolute application URL from a path. */
    function url(string $path = ''): string
    {
        return Config::baseUrl($path);
    }
}

if (!function_exists('asset')) {
    /** Build an asset URL with a cache-busting version. */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = dirname(__DIR__, 2) . '/assets/' . $path;
        $ver  = is_file($file) ? (string) filemtime($file) : '1';
        return Config::baseUrl('assets/' . $path) . '?v=' . $ver;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('old')) {
    /** Retrieve previously submitted input after a validation failure. */
    function old(string $key, string $default = ''): string
    {
        $input = Session::oldInput();
        $value = $input[$key] ?? $default;
        return is_scalar($value) ? (string) $value : $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            return Session::pullFlash($key);
        }
        Session::flash($key, $value);
        return null;
    }
}

if (!function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        return Str::money($amount);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return \App\Core\Settings::get($key, $default);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        return Str::slug($value);
    }
}

if (!function_exists('json_attr')) {
    /** JSON-encode a value for safe use inside an HTML attribute. */
    function json_attr(mixed $value): string
    {
        return htmlspecialchars(
            (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('is_active_path')) {
    /** Returns "active" when the current path starts with the given prefix. */
    function is_active_path(string $prefix): string
    {
        $path = App\Core\Request::capture()->path;
        $prefix = '/' . trim($prefix, '/');
        return ($prefix === '/' ? $path === '/' : str_starts_with($path, $prefix)) ? 'active' : '';
    }
}
