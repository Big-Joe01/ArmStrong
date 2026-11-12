<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Property queries for the real estate division.
 */
final class Property
{
    /** @param array<string,mixed> $filters */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $where  = ['p.is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['transaction_type'])) {
            $where[] = 'p.transaction_type = ?';
            $params[] = $filters['transaction_type'];
        }
        if (!empty($filters['state'])) {
            $where[] = 'p.state = ?';
            $params[] = $filters['state'];
        }
        if (!empty($filters['city'])) {
            $where[] = 'p.city LIKE ?';
            $params[] = '%' . $filters['city'] . '%';
        }
        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE ? OR p.short_description LIKE ? OR p.reference LIKE ? OR p.city LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (isset($filters['min']) && $filters['min'] !== null && $filters['min'] !== '') {
            $where[] = 'p.price >= ?';
            $params[] = (float) $filters['min'];
        }
        if (isset($filters['max']) && $filters['max'] !== null && $filters['max'] !== '') {
            $where[] = 'p.price <= ?';
            $params[] = (float) $filters['max'];
        }
        if (!empty($filters['bedrooms'])) {
            $where[] = 'p.bedrooms >= ?';
            $params[] = (int) $filters['bedrooms'];
        }

        $orderBy = match ($filters['sort'] ?? '') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'oldest'     => 'p.created_at ASC',
            default      => 'p.is_featured DESC, p.created_at DESC',
        };

        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM properties p LEFT JOIN property_categories c ON c.id = p.category_id WHERE $sqlWhere",
            $params
        );
        $perPage = max(1, min(48, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::all(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM properties p
             LEFT JOIN property_categories c ON c.id = p.category_id
             WHERE $sqlWhere
             ORDER BY $orderBy
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    /** @return array<string,mixed>|null */
    public static function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM properties p
                LEFT JOIN property_categories c ON c.id = p.category_id
                WHERE p.slug = ?';
        if ($publishedOnly) {
            $sql .= ' AND p.is_published = 1';
        }
        return Database::first($sql, [$slug]);
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM properties WHERE id = ?', [$id]);
    }

    /** @return list<array<string,mixed>> */
    public static function images(int $propertyId): array
    {
        return Database::all(
            'SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, sort_order ASC, id ASC',
            [$propertyId]
        );
    }

    /** @return list<array<string,mixed>> */
    public static function featured(int $limit = 6): array
    {
        return Database::all(
            "SELECT p.*, c.name AS category_name,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM properties p
             LEFT JOIN property_categories c ON c.id = p.category_id
             WHERE p.is_published = 1 AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT $limit"
        );
    }

    /** @return list<array<string,mixed>> */
    public static function related(int $propertyId, ?int $categoryId, int $limit = 3): array
    {
        if ($categoryId === null) {
            return [];
        }
        return Database::all(
            "SELECT p.*,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM properties p
             WHERE p.is_published = 1 AND p.category_id = ? AND p.id <> ?
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT $limit",
            [$categoryId, $propertyId]
        );
    }

    /** @return list<string> */
    public static function states(): array
    {
        $rows = Database::all(
            "SELECT DISTINCT state FROM properties WHERE state IS NOT NULL AND state <> '' AND is_published = 1 ORDER BY state ASC"
        );
        return array_map(static fn ($r) => (string) $r['state'], $rows);
    }

    /** Admin listing. */
    public static function adminPaginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE ? OR p.reference LIKE ? OR p.city LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM properties p WHERE $sqlWhere", $params);
        $perPage = max(1, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::all(
            "SELECT p.*, c.name AS category_name,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM properties p
             LEFT JOIN property_categories c ON c.id = p.category_id
             WHERE $sqlWhere
             ORDER BY p.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'AH-PRP-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while ((int) Database::scalar('SELECT COUNT(*) FROM properties WHERE reference = ?', [$reference]) > 0);
        return $reference;
    }

    public static function transactionLabel(string $type): string
    {
        return match ($type) {
            'for_sale'  => 'For Sale',
            'for_rent'  => 'For Rent',
            'for_lease' => 'For Lease',
            default     => 'Other',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'available'   => 'Available',
            'reserved'    => 'Reserved',
            'sold'        => 'Sold',
            'rented'      => 'Rented',
            'leased'      => 'Leased',
            'unavailable' => 'Unavailable',
            default       => 'Draft',
        };
    }

    public static function priceLabel(array $property): string
    {
        if ($property['price'] === null || (float) $property['price'] <= 0) {
            return (string) ($property['price_frequency'] === 'negotiable' ? 'Price on request' : 'Contact for price');
        }
        $price = number_format((float) $property['price'], 2);
        $symbol = (string) \App\Core\Config::get('currency_symbol', '₦');
        $suffix = match ($property['price_frequency']) {
            'per_year'  => ' / year',
            'per_month' => ' / month',
            'per_week'  => ' / week',
            'per_day'   => ' / day',
            default     => '',
        };
        return $symbol . $price . $suffix;
    }
}
