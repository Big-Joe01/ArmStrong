<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish wrapper around the current HTTP request.
 */
final class Request
{
    /** @param array<string,mixed> $query @param array<string,mixed> $body @param array<string,mixed> $files */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $body = [],
        public readonly array $files = [],
        public readonly array $server = [],
        public readonly array $cookies = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing for HTML forms (_method hidden field).
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoof = strtoupper((string) $_POST['_method']);
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoof;
            }
        }

        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // If the app lives in a sub-directory, strip it from the path.
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }
        $path = '/' . trim(rawurldecode($path), '/');

        return new self($method, $path, $_GET, $_POST, $_FILES, $_SERVER, $_COOKIE);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? $default : $value;
        }
        return $value;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_numeric($value) ? (int) $value : $default;
    }

    public function float(string $key, float $default = 0.0): float
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_numeric($value) ? (float) $value : $default;
    }

    public function bool(string $key): bool
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? null;
        return in_array($value, ['1', 1, true, 'on', 'yes', 'true'], true);
    }

    /** @return list<mixed> */
    public function array(string $key): array
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? [];
        return is_array($value) ? $value : [];
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $file;
    }

    /** @return list<array> */
    public function files(string $key): array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || !is_array($file['name'])) {
            $single = $this->file($key);
            return $single ? [$single] : [];
        }
        $out = [];
        foreach (array_keys($file['name']) as $i) {
            if (($file['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name'     => $file['name'][$i],
                'type'     => $file['type'][$i],
                'tmp_name' => $file['tmp_name'][$i],
                'error'    => $file['error'][$i],
                'size'     => $file['size'][$i],
            ];
        }
        return $out;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjax(): bool
    {
        return strtolower((string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        $accept = (string) ($this->server['HTTP_ACCEPT'] ?? '');
        return $this->isAjax() || str_contains($accept, 'application/json');
    }
}
