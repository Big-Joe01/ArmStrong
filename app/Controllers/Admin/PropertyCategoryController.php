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
 * Property category management.
 */
final class PropertyCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->adminView('admin/property-categories/index', [
            'title'      => 'Property categories',
            'activeNav'  => 'property_categories',
            'categories' => Database::all(
                'SELECT c.*, (SELECT COUNT(*) FROM properties p WHERE p.category_id = c.id) AS property_count
                 FROM property_categories c ORDER BY c.sort_order ASC, c.name ASC'
            ),
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
            return $this->redirect('invent/property-categories');
        }

        $id = Database::insert('property_categories', [
            'name'        => $request->string('name'),
            'slug'        => Str::uniqueSlug('property_categories', 'slug', $request->string('name')),
            'description' => $this->nullable($request->string('description')),
            'sort_order'  => $request->int('sort_order', 0),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ]);

        ActivityLog::record('create', 'property_category', $id, 'Created property category.');
        Session::flash('success', 'Property category created.');
        return $this->redirect('invent/property-categories');
    }

    public function update(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $categoryId = (int) $id;
        if (Database::first('SELECT id FROM property_categories WHERE id = ?', [$categoryId]) === null) {
            Session::flash('error', 'Category not found.');
            return $this->redirect('invent/property-categories');
        }

        $name = $request->string('name');
        Database::update('property_categories', [
            'name'        => $name,
            'slug'        => Str::uniqueSlug('property_categories', 'slug', $name, $categoryId),
            'description' => $this->nullable($request->string('description')),
            'sort_order'  => $request->int('sort_order', 0),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ], ['id' => $categoryId]);

        ActivityLog::record('update', 'property_category', $categoryId, 'Updated property category.');
        Session::flash('success', 'Property category updated.');
        return $this->redirect('invent/property-categories');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $categoryId = (int) $id;
        $inUse = (int) Database::scalar('SELECT COUNT(*) FROM properties WHERE category_id = ?', [$categoryId]);
        if ($inUse > 0) {
            Session::flash('error', "This category is used by $inUse property/ies. Reassign them before deleting.");
            return $this->redirect('invent/property-categories');
        }
        Database::delete('property_categories', ['id' => $categoryId]);
        ActivityLog::record('delete', 'property_category', $categoryId, 'Deleted property category.');
        Session::flash('success', 'Property category deleted.');
        return $this->redirect('invent/property-categories');
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function csrfFail(): Response
    {
        Session::flash('error', 'Your session expired. Please try again.');
        return $this->redirect('invent/property-categories');
    }
}
