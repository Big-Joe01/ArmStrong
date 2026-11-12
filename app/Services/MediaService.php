<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Cloudinary;
use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;

/**
 * Handles image uploads for products and properties.
 *
 * Images are stored on Cloudinary; the database keeps only the URL and
 * public id. When Cloudinary is not configured the upload is rejected
 * with a clear message rather than silently storing binaries on the
 * hosting server.
 */
final class MediaService
{
    /**
     * Validate and upload a single file.
     *
     * @param array{name:string,tmp_name:string,size:int,error:int,type:string} $file
     * @return array{url:string,public_id:string}
     *
     * @throws \RuntimeException
     */
    public static function uploadImage(array $file, string $folder): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('The image could not be uploaded. Please try again.');
        }

        $maxSize = (int) Config::get('uploads.max_size', 5242880);
        if ((int) $file['size'] > $maxSize) {
            throw new \RuntimeException(sprintf('Images must be smaller than %d MB.', (int) ($maxSize / 1048576)));
        }

        $allowed = (array) Config::get('uploads.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);
        $mime    = self::detectMime((string) $file['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            throw new \RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
        }

        // Re-encode/verify the image is genuine to defeat polyglot files.
        if (@getimagesize((string) $file['tmp_name']) === false) {
            throw new \RuntimeException('The uploaded file is not a valid image.');
        }

        if (!Cloudinary::isConfigured()) {
            throw new \RuntimeException(
                'Image storage is not configured. Ask the administrator to add Cloudinary credentials.'
            );
        }

        $result = Cloudinary::upload((string) $file['tmp_name'], $folder);
        if ($result === null) {
            throw new \RuntimeException('Image upload failed. Please try again.');
        }
        return $result;
    }

    /** @param list<array> $files @return list<array{url:string,public_id:string}> */
    public static function uploadMany(array $files, string $folder): array
    {
        $out = [];
        foreach ($files as $file) {
            $out[] = self::uploadImage($file, $folder);
        }
        return $out;
    }

    public static function detectMime(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path);
        return is_string($mime) ? $mime : '';
    }

    /**
     * Store an uploaded image URL against a product, preserving ordering
     * and main-image flag.
     *
     * @param array{url:string,public_id:string} $upload
     */
    public static function attachProductImage(int $productId, array $upload, bool $isMain, string $alt = ''): int
    {
        $sort = (int) Database::scalar('SELECT COALESCE(MAX(sort_order),0)+1 FROM product_images WHERE product_id = ?', [$productId]);
        if ($isMain) {
            Database::update('product_images', ['is_main' => 0], ['product_id' => $productId]);
        }
        return Database::insert('product_images', [
            'product_id' => $productId,
            'image_url'  => $upload['url'],
            'public_id'  => $upload['public_id'],
            'alt_text'   => $alt,
            'is_main'    => $isMain ? 1 : 0,
            'sort_order' => $sort,
        ]);
    }

    /** @param array{url:string,public_id:string} $upload */
    public static function attachPropertyImage(int $propertyId, array $upload, bool $isMain, string $alt = ''): int
    {
        $sort = (int) Database::scalar('SELECT COALESCE(MAX(sort_order),0)+1 FROM property_images WHERE property_id = ?', [$propertyId]);
        if ($isMain) {
            Database::update('property_images', ['is_main' => 0], ['property_id' => $propertyId]);
        }
        return Database::insert('property_images', [
            'property_id' => $propertyId,
            'image_url'   => $upload['url'],
            'public_id'   => $upload['public_id'],
            'alt_text'    => $alt,
            'is_main'     => $isMain ? 1 : 0,
            'sort_order'  => $sort,
        ]);
    }

    public static function deleteProductImage(int $imageId): void
    {
        $image = Database::first('SELECT * FROM product_images WHERE id = ?', [$imageId]);
        if ($image === null) {
            return;
        }
        Database::delete('product_images', ['id' => $imageId]);
        if (!empty($image['public_id'])) {
            Cloudinary::delete((string) $image['public_id']);
        }
        // Promote another image to main if we removed the main one.
        $hasMain = (int) Database::scalar('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_main = 1', [$image['product_id']]);
        if ($hasMain === 0) {
            $next = Database::first('SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1', [$image['product_id']]);
            if ($next) {
                Database::update('product_images', ['is_main' => 1], ['id' => $next['id']]);
            }
        }
    }

    public static function deletePropertyImage(int $imageId): void
    {
        $image = Database::first('SELECT * FROM property_images WHERE id = ?', [$imageId]);
        if ($image === null) {
            return;
        }
        Database::delete('property_images', ['id' => $imageId]);
        if (!empty($image['public_id'])) {
            Cloudinary::delete((string) $image['public_id']);
        }
        $hasMain = (int) Database::scalar('SELECT COUNT(*) FROM property_images WHERE property_id = ? AND is_main = 1', [$image['property_id']]);
        if ($hasMain === 0) {
            $next = Database::first('SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order ASC LIMIT 1', [$image['property_id']]);
            if ($next) {
                Database::update('property_images', ['is_main' => 1], ['id' => $next['id']]);
            }
        }
    }

    public static function setProductMainImage(int $productId, int $imageId): void
    {
        Database::update('product_images', ['is_main' => 0], ['product_id' => $productId]);
        Database::update('product_images', ['is_main' => 1], ['id' => $imageId]);
    }

    public static function setPropertyMainImage(int $propertyId, int $imageId): void
    {
        Database::update('property_images', ['is_main' => 0], ['property_id' => $propertyId]);
        Database::update('property_images', ['is_main' => 1], ['id' => $imageId]);
    }

    /** Best-effort URL for a product's main image (falls back to a placeholder). */
    public static function productImageUrl(?string $url, int $width = 600): string
    {
        if ($url === null || $url === '') {
            return Config::baseUrl('assets/img/placeholder.svg');
        }
        return Cloudinary::optimized($url, $width);
    }

    public static function propertyImageUrl(?string $url, int $width = 900): string
    {
        if ($url === null || $url === '') {
            return Config::baseUrl('assets/img/placeholder.svg');
        }
        return Cloudinary::optimized($url, $width);
    }
}
