<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Authentication for the single administrator account, with throttling.
 */
final class Auth
{
    private const SESSION_KEY = 'admin_id';
    private const LAST_ACTIVITY = 'admin_last_activity';

    public static function check(): bool
    {
        return (int) Session::get(self::SESSION_KEY, 0) > 0;
    }

    public static function id(): ?int
    {
        $id = (int) Session::get(self::SESSION_KEY, 0);
        return $id > 0 ? $id : null;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }
        return Database::first('SELECT * FROM administrators WHERE id = ? AND is_active = 1', [$id]);
    }

    /** Enforce session expiry based on inactivity. */
    public static function touch(): void
    {
        if (!self::check()) {
            return;
        }
        $lifetime = (int) Config::get('session_lifetime', 7200);
        $last     = (int) Session::get(self::LAST_ACTIVITY, 0);
        if ($last > 0 && (time() - $last) > $lifetime) {
            self::logout();
            Session::flash('error', 'Your session expired. Please sign in again.');
            return;
        }
        Session::set(self::LAST_ACTIVITY, time());
    }

    /**
     * Attempt a login. Returns [success, message].
     *
     * @return array{0:bool,1:string}
     */
    public static function attempt(string $username, string $password, string $ip): array
    {
        $maxAttempts = (int) Config::get('login_max_attempts', 5);
        $window      = (int) Config::get('login_window_minutes', 15);

        if (self::tooManyAttempts($ip, $username, $maxAttempts, $window)) {
            self::recordAttempt($username, $ip, false);
            return [false, 'Too many failed attempts. Please try again later.'];
        }

        $admin = Database::first(
            'SELECT * FROM administrators WHERE (username = ? OR email = ?) AND is_active = 1',
            [$username, $username]
        );

        if ($admin === null) {
            self::recordAttempt($username, $ip, false);
            // Constant-ish time response.
            password_verify($password, '$2y$12$0000000000000000000000000000000000000000000000000000');
            return [false, 'Invalid credentials.'];
        }

        if (!empty($admin['locked_until']) && strtotime((string) $admin['locked_until']) > time()) {
            self::recordAttempt($username, $ip, false);
            return [false, 'Account temporarily locked. Please try again later.'];
        }

        if (!password_verify($password, (string) $admin['password_hash'])) {
            self::recordAttempt($username, $ip, false);
            self::bumpFailedAttempts((int) $admin['id']);
            return [false, 'Invalid credentials.'];
        }

        // Success.
        if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
            Database::update('administrators', [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ], ['id' => $admin['id']]);
        }

        Database::update('administrators', [
            'last_login_at'   => date('Y-m-d H:i:s'),
            'last_login_ip'   => $ip,
            'failed_attempts' => 0,
            'locked_until'    => null,
        ], ['id' => $admin['id']]);

        self::recordAttempt($username, $ip, true);

        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $admin['id']);
        Session::set(self::LAST_ACTIVITY, time());

        return [true, 'Signed in.'];
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget(self::LAST_ACTIVITY);
        Session::regenerate();
    }

    public static function tooManyAttempts(string $ip, string $username, int $max, int $windowMinutes): bool
    {
        $since = date('Y-m-d H:i:s', time() - $windowMinutes * 60);
        $count = (int) Database::scalar(
            'SELECT COUNT(*) FROM login_attempts
             WHERE success = 0 AND created_at >= ? AND (ip_address = ? OR username = ?)',
            [$since, $ip, $username]
        );
        return $count >= $max;
    }

    private static function recordAttempt(string $username, string $ip, bool $success): void
    {
        Database::insert('login_attempts', [
            'username'   => substr($username, 0, 60),
            'ip_address' => $ip,
            'success'    => $success ? 1 : 0,
        ]);
    }

    private static function bumpFailedAttempts(int $adminId): void
    {
        Database::run('UPDATE administrators SET failed_attempts = failed_attempts + 1 WHERE id = ?', [$adminId]);
        $max     = (int) Config::get('login_max_attempts', 5);
        $lockMin = (int) Config::get('login_lockout_minutes', 15);
        $admin   = Database::first('SELECT failed_attempts FROM administrators WHERE id = ?', [$adminId]);
        if ($admin && (int) $admin['failed_attempts'] >= $max) {
            Database::update('administrators', [
                'locked_until'    => date('Y-m-d H:i:s', time() + $lockMin * 60),
                'failed_attempts' => 0,
            ], ['id' => $adminId]);
        }
    }
}
