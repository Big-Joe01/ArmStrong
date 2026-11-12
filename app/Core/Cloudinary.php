<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cloudinary upload/delete via the REST API (no SDK dependency).
 *
 * Credentials live in config only. Uploads are signed server-side so the
 * API secret never reaches the browser.
 */
final class Cloudinary
{
    public static function isConfigured(): bool
    {
        return (string) Config::get('cloudinary.cloud_name', '') !== ''
            && (string) Config::get('cloudinary.api_key', '') !== ''
            && (string) Config::get('cloudinary.api_secret', '') !== '';
    }

    /**
     * Upload a local file path to Cloudinary.
     *
     * @return array{url:string,public_id:string}|null
     */
    public static function upload(string $filePath, string $folder = ''): ?array
    {
        if (!self::isConfigured()) {
            Logger::warning('Cloudinary not configured; upload skipped.');
            return null;
        }

        $cloud  = (string) Config::get('cloudinary.cloud_name');
        $key    = (string) Config::get('cloudinary.api_key');
        $secret = (string) Config::get('cloudinary.api_secret');
        $folder = $folder !== '' ? $folder : (string) Config::get('cloudinary.folder', 'armstrong');

        $timestamp = time();
        $params    = ['folder' => $folder, 'timestamp' => $timestamp];
        ksort($params);
        $toSign  = [];
        foreach ($params as $k => $v) {
            $toSign[] = $k . '=' . $v;
        }
        $signature = sha1(implode('&', $toSign) . $secret);

        $post = [
            'file'      => new \CURLFile($filePath),
            'api_key'   => $key,
            'timestamp' => (string) $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init(sprintf('https://api.cloudinary.com/v1_1/%s/image/upload', $cloud));
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
        ]);
        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            Logger::error('Cloudinary upload failed', ['status' => $status, 'error' => $error, 'body' => $response]);
            return null;
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data) || empty($data['secure_url'])) {
            Logger::error('Cloudinary upload: unexpected response', ['body' => $response]);
            return null;
        }

        return ['url' => (string) $data['secure_url'], 'public_id' => (string) ($data['public_id'] ?? '')];
    }

    public static function delete(string $publicId): bool
    {
        if (!self::isConfigured() || $publicId === '') {
            return false;
        }
        $cloud  = (string) Config::get('cloudinary.cloud_name');
        $key    = (string) Config::get('cloudinary.api_key');
        $secret = (string) Config::get('cloudinary.api_secret');

        $timestamp = time();
        $toSign    = 'public_id=' . $publicId . '&timestamp=' . $timestamp;
        $signature = sha1($toSign . $secret);

        $ch = curl_init(sprintf('https://api.cloudinary.com/v1_1/%s/image/destroy', $cloud));
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'public_id' => $publicId,
                'api_key'   => $key,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status < 400;
    }

    /**
     * Insert delivery transformations into a Cloudinary URL for optimisation.
     */
    public static function optimized(string $url, int $width = 800, string $crop = 'fill'): string
    {
        if (!str_contains($url, '/upload/')) {
            return $url;
        }
        $transform = sprintf('f_auto,q_auto,w_%d,c_%s', $width, $crop);
        return str_replace('/upload/', '/upload/' . $transform . '/', $url);
    }
}
