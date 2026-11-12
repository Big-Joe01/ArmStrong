<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Secure session wrapper: hardened cookies, ID regeneration, flash messages.
 */
final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_name((string) Config::get('session_name', 'armstrong_sess'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        if ($secure) {
            ini_set('session.cookie_secure', '1');
        }

        session_start();
        self::$started = true;

        // Periodic ID rotation to limit fixation windows.
        $last = (int) ($_SESSION['_rotated_at'] ?? 0);
        if ($last === 0 || (time() - $last) > 900) {
            session_regenerate_id(true);
            $_SESSION['_rotated_at'] = time();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_rotated_at'] = time();
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pullFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public static function flashInput(array $input): void
    {
        unset($input['_token'], $input['password'], $input['password_confirmation']);
        $_SESSION['_old_input'] = $input;
    }

    public static function oldInput(): array
    {
        $input = $_SESSION['_old_input'] ?? [];
        return is_array($input) ? $input : [];
    }

    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }
}
