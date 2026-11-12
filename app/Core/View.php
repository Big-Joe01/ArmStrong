<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Plain-PHP view renderer with layout support and output escaping.
 */
final class View
{
    /** @var array<string,mixed> */
    private static array $globals = [];

    public static function share(string $key, mixed $value): void
    {
        self::$globals[$key] = $value;
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = self::renderFile($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::renderFile($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function renderFile(string $view, array $data): string
    {
        $file = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $view);
        }
        extract(array_merge(self::$globals, $data), EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /** Escape a value for safe HTML output. */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
