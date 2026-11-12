<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Models\Product;

/**
 * Building materials & interior catalogue.
 */
final class CatalogueController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q'            => $request->string('q'),
            'category'     => $request->string('category'),
            'brand'        => $request->string('brand'),
            'min'          => $request->input('min'),
            'max'          => $request->input('max'),
            'availability' => $request->string('availability'),
            'sort'         => $request->string('sort', 'featured'),
        ];

        $result     = Product::paginate($filters, max(1, $request->int('page', 1)), 12);
        $categories = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_published = 1) AS product_count
             FROM product_categories c WHERE c.is_active = 1 ORDER BY c.sort_order ASC, c.name ASC"
        );
        $brands = Database::all('SELECT * FROM brands WHERE is_active = 1 ORDER BY name ASC');

        return $this->view('public/catalogue/index', [
            'title'           => 'Building Materials & Interior Products | ' . Settings::get('company_name', 'Armstrong Homes & Interior Limited'),
            'metaDescription' => 'Browse tiles, sanitary ware, doors, windows, lighting, plumbing, ceiling materials, flooring, paints and interior products. Order online with store collection or delivery.',
            'canonical'       => url('/building-materials'),
            'filters'         => $filters,
            'result'          => $result,
            'categories'      => $categories,
            'brands'          => $brands,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $product = Product::findBySlug($slug);
        if ($product === null) {
            return Response::html(\App\Core\View::render('public/errors/404', ['title' => 'Product not found']), 404);
        }

        Database::run('UPDATE products SET views = views + 1 WHERE id = ?', [$product['id']]);

        $images   = Product::images((int) $product['id']);
        $related  = Product::related((int) $product['id'], isset($product['category_id']) ? (int) $product['category_id'] : null, 4);
        $rating   = Product::averageRating((int) $product['id']);
        $reviews  = Product::reviews((int) $product['id'], 20);
        $delivery = Database::all('SELECT * FROM delivery_rates WHERE is_active = 1 ORDER BY sort_order ASC, fee ASC');

        return $this->view('public/catalogue/show', [
            'title'           => (string) ($product['meta_title'] ?: ($product['name'] . ' | Building Materials')),
            'metaDescription' => (string) ($product['meta_description'] ?: \App\Core\Str::excerpt((string) $product['short_description'], 155)),
            'canonical'       => url('/products/' . $product['slug']),
            'ogImage'         => $images[0]['image_url'] ?? null,
            'product'         => $product,
            'images'          => $images,
            'related'         => $related,
            'rating'          => $rating,
            'reviews'         => $reviews,
            'deliveryOptions' => $delivery,
            'specs'           => json_decode((string) ($product['specs'] ?? '[]'), true) ?: [],
            'colors'          => json_decode((string) ($product['colors'] ?? '[]'), true) ?: [],
            'sizes'           => json_decode((string) ($product['sizes'] ?? '[]'), true) ?: [],
            'variations'      => json_decode((string) ($product['variations'] ?? '[]'), true) ?: [],
        ]);
    }
}
