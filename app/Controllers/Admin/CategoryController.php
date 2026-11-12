<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ActivityLog;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Str;
use App\Core\Validator;

/**
 * Product category management.
 */
final class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->adminView('admin/categories/index', [
            'title'      => 'Product categories',
            'activeNav'  => 'categories',
            'categories' => Database::all(
                'SELECT c.*, p.name AS parent_name,
                        (SELECT COUNT(*) FROM products x WHERE x.category_id = c.id) AS product_count
                 FROM product_categories c
                 LEFT JOIN product_categories p ON p.id = c.parent_id
                 ORDER BY c.sort_order ASC, c.name ASC'
            ),
            'allCategories' => Database::all('SELECT id, name FROM product_categories ORDER BY name'),
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $data = $request->all();
        $validator = new Validator($data);
        $validator->required('name', 'Category name')->max('name', 'Category name', 150);
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return $this->redirect('invent/product-categories');
        }

        $id = Database::insert('product_categories', [
            'parent_id'        => $request->int('parent_id', 0) ?: null,
            'name'             => $request->string('name'),
            'slug'             => Str::uniqueSlug('product_categories', 'slug', $request->string('name')),
            'description'      => $this->nullable($request->string('description')),
            'sort_order'       => $request->int('sort_order', 0),
            'is_active'        => $request->input('is_active') ? 1 : 0,
            'meta_title'       => $this->nullable($request->string('meta_title'), 255),
            'meta_description' => $this->nullable($request->string('meta_description'), 320),
        ]);

        ActivityLog::record('create', 'product_category', $id, 'Created category: ' . $request->string('name'));
        Session::flash('success', 'Category created.');
        return $this->redirect('invent/product-categories');
    }

    public function update(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $categoryId = (int) $id;
        $category = Database::first('SELECT * FROM product_categories WHERE id = ?', [$categoryId]);
        if ($category === null) {
            Session::flash('error', 'Category not found.');
            return $this->redirect('invent/product-categories');
        }

        $parentId = $request->int('parent_id', 0) ?: null;
        if ($parentId === $categoryId) {
            Session::flash('error', 'A category cannot be its own parent.');
            return $this->redirect('invent/product-categories');
        }

        Database::update('product_categories', [
            'parent_id'        => $parentId,
            'name'             => $request->string('name', (string) $category['name']),
            'slug'             => Str::uniqueSlug('product_categories', 'slug', $request->string('name', (string) $category['name']), $categoryId),
            'description'      => $this->nullable($request->string('description')),
            'sort_order'       => $request->int('sort_order', 0),
            'is_active'        => $request->input('is_active') ? 1 : 0,
            'meta_title'       => $this->nullable($request->string('meta_title'), 255),
            'meta_description' => $this->nullable($request->string('meta_description'), 320),
        ], ['id' => $categoryId]);

        ActivityLog::record('update', 'product_category', $categoryId, 'Updated category.');
        Session::flash('success', 'Category updated.');
        return $this->redirect('invent/product-categories');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $categoryId = (int) $id;
        $inUse = (int) Database::scalar('SELECT COUNT(*) FROM products WHERE category_id = ?', [$categoryId]);
        if ($inUse > 0) {
            Session::flash('error', "This category is used by $inUse product(s). Reassign them before deleting.");
            return $this->redirect('invent/product-categories');
        }
        Database::delete('product_categories', ['id' => $categoryId]);
        ActivityLog::record('delete', 'product_category', $categoryId, 'Deleted category.');
        Session::flash('success', 'Category deleted.');
        return $this->redirect('invent/product-categories');
    }

    private function nullable(string $value, ?int $max = null): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        return $max !== null ? mb_substr($value, 0, $max) : $value;
    }

    private function csrfFail(): Response
    {
        Session::flash('error', 'Your session expired. Please try again.');
        return $this->redirect('invent/product-categories');
    }
}
