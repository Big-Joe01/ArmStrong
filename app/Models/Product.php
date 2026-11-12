<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Product queries (public catalogue + admin lists).
 */
final class Product
{
    /**
     * Paginated catalogue search.
     *
     * @param array{category?:string,brand?:string,q?:string,min?:float,max?:float,availability?:string,sort?:string} $filters
     * @return array{items:list<array<string,mixed>>,total:int,page:int,pages:int,per_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where  = ["p.is_published = 1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $where[] = 'b.slug = ?';
            $params[] = $filters['brand'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.reference LIKE ? OR b.name LIKE ?)';
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
        if (!empty($filters['availability'])) {
            $where[] = 'p.availability = ?';
            $params[] = $filters['availability'];
        }

        $orderBy = match ($filters['sort'] ?? '') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name'       => 'p.name ASC',
            'oldest'     => 'p.created_at ASC',
            default      => 'p.is_featured DESC, p.created_at DESC',
        };

        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE $sqlWhere",
            $params
        );

        $perPage = max(1, min(48, $perPage));
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = max(1, min($page, $pages));
        $offset  = ($page - 1) * $perPage;

        $items = Database::all(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug,
                    (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
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
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug
                FROM products p
                LEFT JOIN product_categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                WHERE p.slug = ?';
        if ($publishedOnly) {
            $sql .= ' AND p.is_published = 1';
        }
        return Database::first($sql, [$slug]);
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM products WHERE id = ?', [$id]);
    }

    /** @return list<array<string,mixed>> */
    public static function images(int $productId): array
    {
        return Database::all(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC, id ASC',
            [$productId]
        );
    }

    /** @return list<array<string,mixed>> */
    public static function featured(int $limit = 8): array
    {
        return Database::all(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.is_published = 1 AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT $limit"
        );
    }

    /** @return list<array<string,mixed>> */
    public static function related(int $productId, ?int $categoryId, int $limit = 4): array
    {
        if ($categoryId === null) {
            return [];
        }
        return Database::all(
            "SELECT p.*, b.name AS brand_name,
                    (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.is_published = 1 AND p.category_id = ? AND p.id <> ?
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT $limit",
            [$categoryId, $productId]
        );
    }

    public static function averageRating(int $productId): array
    {
        $row = Database::first(
            "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total
             FROM reviews WHERE product_id = ? AND status = 'approved'",
            [$productId]
        );
        return [
            'avg'   => $row && $row['avg_rating'] !== null ? (float) $row['avg_rating'] : 0.0,
            'total' => $row ? (int) $row['total'] : 0,
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function reviews(int $productId, int $limit = 20): array
    {
        return Database::all(
            "SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT $limit",
            [$productId]
        );
    }

    /** Admin: all products with filters. */
    public static function adminPaginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR p.reference LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like);
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] === 'published') {
                $where[] = 'p.is_published = 1';
            } elseif ($filters['status'] === 'draft') {
                $where[] = 'p.is_published = 0';
            } elseif ($filters['status'] === 'low_stock') {
                $where[] = '(p.stock_on_hand - p.reserved_qty) <= p.low_stock_threshold';
            } elseif ($filters['status'] === 'out_of_stock') {
                $where[] = '(p.stock_on_hand - p.reserved_qty) <= 0';
            }
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM products p WHERE $sqlWhere", $params);
        $perPage = max(1, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::all(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_main DESC, pi.sort_order ASC LIMIT 1) AS main_image
             FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
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
            $reference = 'AH-PRD-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while ((int) Database::scalar('SELECT COUNT(*) FROM products WHERE reference = ?', [$reference]) > 0);
        return $reference;
    }
}
