<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token generation and validation (synchroniser token pattern).
 */
final class Csrf
{
    private const KEY = '_csrf_token';
    private const FIELD = '_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="' . self::token() . '">';
    }

    public static function fieldName(): string
    {
        return self::FIELD;
    }

    public static function validate(?string $token): bool
    {
        $expected = $_SESSION[self::KEY] ?? null;
        if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($expected, $token);
    }

    public static function check(Request $request): bool
    {
        $token = $request->body[self::FIELD]
            ?? $request->server['HTTP_X_CSRF_TOKEN']
            ?? null;
        return self::validate(is_string($token) ? $token : null);
    }
}
