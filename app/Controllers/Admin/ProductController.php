<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ActivityLog;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Str;
use App\Core\Validator;
use App\Services\InventoryService;
use App\Services\MediaService;

/**
 * Product management: CRUD, publishing, images, variations.
 */
final class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $q        = $request->string('q');
        $category = $request->string('category');
        $status   = $request->string('status');
        $page     = max(1, $request->int('page', 1));
        $perPage  = 20;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.name LIKE ? OR p.reference LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        if ($category !== '') {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $category;
        }
        if ($status === 'published') {
            $where[] = 'p.is_published = 1';
        } elseif ($status === 'draft') {
            $where[] = 'p.is_published = 0';
        } elseif ($status === 'out_of_stock') {
            $where[] = '(p.stock_on_hand - p.reserved_qty) <= 0';
        } elseif ($status === 'featured') {
            $where[] = 'p.is_featured = 1';
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM products p WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $products = Database::all(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY is_main DESC, sort_order ASC LIMIT 1) AS main_image
             FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE $whereSql
             ORDER BY p.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return $this->adminView('admin/products/index', [
            'title'      => 'Products',
            'activeNav'  => 'products',
            'products'   => $products,
            'categories' => Database::all('SELECT id, name FROM product_categories ORDER BY name'),
            'filters'    => ['q' => $q, 'category' => $category, 'status' => $status],
            'result'     => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->adminView('admin/products/form', [
            'title'      => 'Add product',
            'activeNav'  => 'products',
            'product'    => null,
            'images'     => [],
            'categories' => Database::all('SELECT id, name FROM product_categories ORDER BY name'),
            'brands'     => Database::all('SELECT id, name FROM brands ORDER BY name'),
            'action'     => url('invent/products'),
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products/create');
        }

        $data = $this->validated($request);
        if ($data === null) {
            Session::flashInput($request->all());
            return $this->redirect('invent/products/create');
        }

        $slug = Str::uniqueSlug('products', 'slug', (string) $data['name']);
        $data['slug'] = $slug;
        $data['reference'] = $request->string('reference') ?: $this->generateReference();

        $stock = max(0, (int) $request->int('stock_on_hand', 0));

        $productId = Database::transaction(function () use ($data, $stock): int {
            $payload = $data;
            $payload['stock_on_hand'] = 0; // set through the ledger below
            $id = Database::insert('products', $payload);
            if ($stock > 0) {
                InventoryService::initialStock($id, $stock, Auth::id(), 'Opening stock on product creation');
            }
            return $id;
        });

        ActivityLog::record('create', 'product', $productId, 'Created product: ' . $data['name']);
        Session::flash('success', 'Product created. You can now add images.');
        return $this->redirect('invent/products/' . $productId . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $product = Database::first('SELECT * FROM products WHERE id = ?', [(int) $id]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }

        return $this->adminView('admin/products/form', [
            'title'      => 'Edit product',
            'activeNav'  => 'products',
            'product'    => $product,
            'images'     => Database::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC', [(int) $id]),
            'categories' => Database::all('SELECT id, name FROM product_categories ORDER BY name'),
            'brands'     => Database::all('SELECT id, name FROM brands ORDER BY name'),
            'action'     => url('invent/products/' . $id),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $productId = (int) $id;
        $product = Database::first('SELECT * FROM products WHERE id = ?', [$productId]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products/' . $id . '/edit');
        }

        $data = $this->validated($request, $productId);
        if ($data === null) {
            Session::flashInput($request->all());
            return $this->redirect('invent/products/' . $id . '/edit');
        }

        $data['slug'] = Str::uniqueSlug('products', 'slug', (string) $data['name'], $productId);

        // Stock is adjusted through the ledger, not overwritten directly.
        $newStock = max(0, (int) $request->int('stock_on_hand', (int) $product['stock_on_hand']));
        $delta = $newStock - (int) $product['stock_on_hand'];

        Database::transaction(function () use ($productId, $data, $delta): void {
            Database::update('products', $data, ['id' => $productId]);
            if ($delta !== 0) {
                InventoryService::adjustStock($productId, $delta, Auth::id(), 'Stock set via product editor');
            }
        });

        ActivityLog::record('update', 'product', $productId, 'Updated product: ' . $data['name']);
        Session::flash('success', 'Product updated.');
        return $this->redirect('invent/products/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products');
        }
        $productId = (int) $id;
        $product = Database::first('SELECT * FROM products WHERE id = ?', [$productId]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }

        // Refuse deletion when active reservations exist; deactivate instead.
        $activeReservations = (int) Database::scalar(
            "SELECT COUNT(*) FROM stock_reservations WHERE product_id = ? AND status = 'active'",
            [$productId]
        );
        if ($activeReservations > 0) {
            Session::flash('error', 'This product has active reservations and cannot be deleted. Mark it unavailable instead.');
            return $this->redirect('invent/products');
        }

        Database::delete('products', ['id' => $productId]);
        ActivityLog::record('delete', 'product', $productId, 'Deleted product: ' . $product['name']);
        Session::flash('success', 'Product deleted.');
        return $this->redirect('invent/products');
    }

    public function togglePublish(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products');
        }
        $product = Database::first('SELECT * FROM products WHERE id = ?', [(int) $id]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }
        $publish = (int) $product['is_published'] === 1 ? 0 : 1;
        Database::update('products', [
            'is_published' => $publish,
            'published_at' => $publish === 1 && !$product['published_at'] ? date('Y-m-d H:i:s') : $product['published_at'],
        ], ['id' => (int) $id]);
        ActivityLog::record($publish ? 'publish' : 'unpublish', 'product', (int) $id, 'Changed publish state.');
        Session::flash('success', $publish ? 'Product published.' : 'Product unpublished.');
        return $this->back($request, 'invent/products');
    }

    public function toggleFeatured(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products');
        }
        $product = Database::first('SELECT id, is_featured FROM products WHERE id = ?', [(int) $id]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }
        $featured = (int) $product['is_featured'] === 1 ? 0 : 1;
        Database::update('products', ['is_featured' => $featured], ['id' => (int) $id]);
        Session::flash('success', $featured ? 'Product marked as featured.' : 'Product removed from featured.');
        return $this->back($request, 'invent/products');
    }

    public function uploadImages(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products/' . $id . '/edit');
        }
        $productId = (int) $id;
        if (Database::first('SELECT id FROM products WHERE id = ?', [$productId]) === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/products');
        }

        $files = $request->files('images');
        if ($files === []) {
            Session::flash('error', 'Choose at least one image to upload.');
            return $this->redirect('invent/products/' . $id . '/edit');
        }

        $hasMain = (int) Database::scalar('SELECT COUNT(*) FROM product_images WHERE product_id = ?', [$productId]) > 0;
        $uploaded = 0;
        $errors = [];
        foreach ($files as $file) {
            try {
                $upload = MediaService::uploadImage($file, 'armstrong/products');
                MediaService::attachProductImage($productId, $upload, !$hasMain);
                $hasMain = true;
                $uploaded++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        ActivityLog::record('upload_images', 'product', $productId, "Uploaded $uploaded image(s).");
        if ($uploaded > 0) {
            Session::flash('success', "$uploaded image(s) uploaded.");
        }
        if ($errors) {
            Session::flash('error', implode(' ', array_unique($errors)));
        }
        return $this->redirect('invent/products/' . $id . '/edit');
    }

    public function setMainImage(Request $request, string $id, string $imageId): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products/' . $id . '/edit');
        }
        MediaService::setProductMainImage((int) $id, (int) $imageId);
        Session::flash('success', 'Main image updated.');
        return $this->redirect('invent/products/' . $id . '/edit');
    }

    public function deleteImage(Request $request, string $id, string $imageId): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/products/' . $id . '/edit');
        }
        MediaService::deleteProductImage((int) $imageId);
        Session::flash('success', 'Image deleted.');
        return $this->redirect('invent/products/' . $id . '/edit');
    }

    /**
     * Validate and normalise product input. Returns null (and flashes an
     * error) when validation fails.
     *
     * @return array<string,mixed>|null
     */
    private function validated(Request $request, ?int $ignoreId = null): ?array
    {
        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('name', 'Product name')->max('name', 'Product name', 255)
            ->required('price', 'Price')->numeric('price', 'Price')->minValue('price', 'Price', 0)
            ->integer('stock_on_hand', 'Stock quantity', false, 0)
            ->integer('low_stock_threshold', 'Low stock threshold', false, 0)
            ->in('availability', 'Availability', ['available', 'temporarily_unavailable', 'discontinued', 'out_of_stock'])
            ->in('unit', 'Unit', ['piece', 'carton', 'box', 'set', 'metre', 'square_metre', 'bag', 'roll', 'litre', 'bundle', 'pack', 'other']);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return null;
        }

        $specs = [];
        foreach ((array) $request->input('spec_keys', []) as $i => $key) {
            $key = trim((string) $key);
            $value = trim((string) (($request->input('spec_values', []))[$i] ?? ''));
            if ($key !== '' && $value !== '') {
                $specs[$key] = $value;
            }
        }

        return [
            'category_id'         => $request->int('category_id', 0) ?: null,
            'brand_id'            => $request->int('brand_id', 0) ?: null,
            'name'                => trim((string) $data['name']),
            'short_description'   => $this->nullable($request->string('short_description'), 500),
            'description'         => $this->nullable($request->string('description')),
            'unit'                => $request->string('unit', 'piece'),
            'packaging'           => $this->nullable($request->string('packaging'), 120),
            'price'               => number_format((float) $request->input('price', 0), 2, '.', ''),
            'compare_price'       => $request->input('compare_price') !== '' && $request->input('compare_price') !== null
                                        ? number_format((float) $request->input('compare_price'), 2, '.', '') : null,
            'low_stock_threshold' => max(0, $request->int('low_stock_threshold', 5)),
            'availability'        => $request->string('availability', 'available'),
            'is_featured'         => $request->input('is_featured') ? 1 : 0,
            'is_published'        => $request->input('is_published') ? 1 : 0,
            'specs'               => $specs ? json_encode($specs) : null,
            'colors'              => $this->jsonList($request->string('colors')),
            'sizes'               => $this->jsonList($request->string('sizes')),
            'meta_title'          => $this->nullable($request->string('meta_title'), 255),
            'meta_description'    => $this->nullable($request->string('meta_description'), 320),
        ];
    }

    /** @return list<string>|null */
    private function jsonList(string $csv): ?string
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $csv)), static fn ($v) => $v !== ''));
        return $items ? json_encode($items) : null;
    }

    private function nullable(string $value, ?int $max = null): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        return $max !== null ? mb_substr($value, 0, $max) : $value;
    }

    private function generateReference(): string
    {
        return 'PRD-' . strtoupper(Str::reference('', 6));
    }


    private function csrfFail(string $redirect): Response
    {
        Session::flash('error', 'Your session expired. Please try again.');
        return $this->redirect($redirect);
    }
}
