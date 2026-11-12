<?php
declare(strict_types=1);

namespace App\Core;

/**
 * String / formatting helpers.
 */
final class Str
{
    public static function slug(string $value): string
    {
        $value = trim($value);
        // Transliterate common accented characters.
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    /** Generate a slug that is unique for the given table/column. */
    public static function uniqueSlug(string $table, string $column, string $value, ?int $ignoreId = null): string
    {
        $base  = self::slug($value) ?: 'item';
        $slug  = $base;
        $i     = 2;
        while (true) {
            $sql    = sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` = ?', $table, $column);
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql     .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if ((int) Database::scalar($sql, $params) === 0) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }

    public static function reference(string $prefix, int $length = 6): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out      = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $prefix . '-' . $out;
    }

    /** Money display using the configured currency symbol. */
    public static function money(float|int|string|null $amount): string
    {
        $symbol = (string) Config::get('currency_symbol', '₦');
        return $symbol . number_format((float) $amount, 2);
    }

    public static function excerpt(?string $text, int $length = 140): string
    {
        $text = trim(strip_tags((string) $text));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $length)) . '…';
    }

    public static function timeAgo(string $datetime): string
    {
        $ts   = strtotime($datetime);
        if ($ts === false) {
            return $datetime;
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'just now';
        }
        $units = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
        ];
        foreach ($units as $seconds => $name) {
            $value = (int) floor($diff / $seconds);
            if ($value >= 1) {
                return $value . ' ' . $name . ($value > 1 ? 's' : '') . ' ago';
            }
        }
        return $datetime;
    }
}
