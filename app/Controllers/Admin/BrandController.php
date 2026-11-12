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
 * Brand management.
 */
final class BrandController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->adminView('admin/brands/index', [
            'title'     => 'Brands',
            'activeNav' => 'brands',
            'brands'    => Database::all(
                'SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) AS product_count
                 FROM brands b ORDER BY b.name ASC'
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
        $validator->required('name', 'Brand name')->max('name', 'Brand name', 150);
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return $this->redirect('invent/brands');
        }

        $id = Database::insert('brands', [
            'name'        => $request->string('name'),
            'slug'        => Str::uniqueSlug('brands', 'slug', $request->string('name')),
            'description' => $this->nullable($request->string('description')),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ]);

        ActivityLog::record('create', 'brand', $id, 'Created brand: ' . $request->string('name'));
        Session::flash('success', 'Brand created.');
        return $this->redirect('invent/brands');
    }

    public function update(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $brandId = (int) $id;
        if (Database::first('SELECT id FROM brands WHERE id = ?', [$brandId]) === null) {
            Session::flash('error', 'Brand not found.');
            return $this->redirect('invent/brands');
        }

        $name = $request->string('name');
        Database::update('brands', [
            'name'        => $name,
            'slug'        => Str::uniqueSlug('brands', 'slug', $name, $brandId),
            'description' => $this->nullable($request->string('description')),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ], ['id' => $brandId]);

        ActivityLog::record('update', 'brand', $brandId, 'Updated brand.');
        Session::flash('success', 'Brand updated.');
        return $this->redirect('invent/brands');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $brandId = (int) $id;
        $inUse = (int) Database::scalar('SELECT COUNT(*) FROM products WHERE brand_id = ?', [$brandId]);
        if ($inUse > 0) {
            Session::flash('error', "This brand is used by $inUse product(s). Reassign them before deleting.");
            return $this->redirect('invent/brands');
        }
        Database::delete('brands', ['id' => $brandId]);
        ActivityLog::record('delete', 'brand', $brandId, 'Deleted brand.');
        Session::flash('success', 'Brand deleted.');
        return $this->redirect('invent/brands');
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function csrfFail(): Response
    {
        Session::flash('error', 'Your session expired. Please try again.');
        return $this->redirect('invent/brands');
    }
}
