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
use App\Services\MediaService;

/**
 * Property listing management.
 */
final class PropertyController extends Controller
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
            $where[] = '(p.title LIKE ? OR p.reference LIKE ? OR p.city LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%', '%' . $q . '%');
        }
        if ($category !== '') {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $category;
        }
        if ($status === 'published') {
            $where[] = 'p.is_published = 1';
        } elseif ($status === 'draft') {
            $where[] = 'p.is_published = 0';
        } elseif ($status === 'featured') {
            $where[] = 'p.is_featured = 1';
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM properties p WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $properties = Database::all(
            "SELECT p.*, c.name AS category_name,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_main DESC, sort_order ASC LIMIT 1) AS main_image
             FROM properties p LEFT JOIN property_categories c ON c.id = p.category_id
             WHERE $whereSql ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return $this->adminView('admin/properties/index', [
            'title'      => 'Properties',
            'activeNav'  => 'properties',
            'properties' => $properties,
            'categories' => Database::all('SELECT id, name FROM property_categories ORDER BY name'),
            'filters'    => compact('q', 'category', 'status'),
            'result'     => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->adminView('admin/properties/form', [
            'title'      => 'Add property',
            'activeNav'  => 'properties',
            'property'   => null,
            'images'     => [],
            'categories' => Database::all('SELECT id, name FROM property_categories ORDER BY name'),
            'action'     => url('invent/properties'),
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties/create');
        }

        $data = $this->validated($request);
        if ($data === null) {
            Session::flashInput($request->all());
            return $this->redirect('invent/properties/create');
        }

        $data['slug'] = Str::uniqueSlug('properties', 'slug', (string) $data['title']);
        $data['reference'] = $request->string('reference') ?: ('PROP-' . strtoupper(Str::reference('', 6)));
        if ((int) $data['is_published'] === 1) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $id = Database::insert('properties', $data);
        ActivityLog::record('create', 'property', $id, 'Created property: ' . $data['title']);
        Session::flash('success', 'Property created. You can now add images.');
        return $this->redirect('invent/properties/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $property = Database::first('SELECT * FROM properties WHERE id = ?', [(int) $id]);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }

        return $this->adminView('admin/properties/form', [
            'title'      => 'Edit property',
            'activeNav'  => 'properties',
            'property'   => $property,
            'images'     => Database::all('SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, sort_order ASC', [(int) $id]),
            'categories' => Database::all('SELECT id, name FROM property_categories ORDER BY name'),
            'action'     => url('invent/properties/' . $id),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $propertyId = (int) $id;
        $property = Database::first('SELECT * FROM properties WHERE id = ?', [$propertyId]);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties/' . $id . '/edit');
        }

        $data = $this->validated($request);
        if ($data === null) {
            Session::flashInput($request->all());
            return $this->redirect('invent/properties/' . $id . '/edit');
        }

        $data['slug'] = Str::uniqueSlug('properties', 'slug', (string) $data['title'], $propertyId);
        if ((int) $data['is_published'] === 1 && !$property['published_at']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        Database::update('properties', $data, ['id' => $propertyId]);
        ActivityLog::record('update', 'property', $propertyId, 'Updated property: ' . $data['title']);
        Session::flash('success', 'Property updated.');
        return $this->redirect('invent/properties/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties');
        }
        $propertyId = (int) $id;
        $property = Database::first('SELECT title FROM properties WHERE id = ?', [$propertyId]);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }
        Database::delete('properties', ['id' => $propertyId]);
        ActivityLog::record('delete', 'property', $propertyId, 'Deleted property: ' . $property['title']);
        Session::flash('success', 'Property deleted.');
        return $this->redirect('invent/properties');
    }

    public function togglePublish(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties');
        }
        $property = Database::first('SELECT * FROM properties WHERE id = ?', [(int) $id]);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }
        $publish = (int) $property['is_published'] === 1 ? 0 : 1;
        Database::update('properties', [
            'is_published' => $publish,
            'published_at' => $publish === 1 && !$property['published_at'] ? date('Y-m-d H:i:s') : $property['published_at'],
        ], ['id' => (int) $id]);
        Session::flash('success', $publish ? 'Property published.' : 'Property unpublished.');
        return $this->back($request, 'invent/properties');
    }

    public function toggleFeatured(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties');
        }
        $property = Database::first('SELECT id, is_featured FROM properties WHERE id = ?', [(int) $id]);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }
        $featured = (int) $property['is_featured'] === 1 ? 0 : 1;
        Database::update('properties', ['is_featured' => $featured], ['id' => (int) $id]);
        Session::flash('success', $featured ? 'Property marked as featured.' : 'Property removed from featured.');
        return $this->back($request, 'invent/properties');
    }

    public function uploadImages(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties/' . $id . '/edit');
        }
        $propertyId = (int) $id;
        if (Database::first('SELECT id FROM properties WHERE id = ?', [$propertyId]) === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('invent/properties');
        }

        $files = $request->files('images');
        if ($files === []) {
            Session::flash('error', 'Choose at least one image to upload.');
            return $this->redirect('invent/properties/' . $id . '/edit');
        }

        $hasMain = (int) Database::scalar('SELECT COUNT(*) FROM property_images WHERE property_id = ?', [$propertyId]) > 0;
        $uploaded = 0;
        $errors = [];
        foreach ($files as $file) {
            try {
                $upload = MediaService::uploadImage($file, 'armstrong/properties');
                MediaService::attachPropertyImage($propertyId, $upload, !$hasMain);
                $hasMain = true;
                $uploaded++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        ActivityLog::record('upload_images', 'property', $propertyId, "Uploaded $uploaded image(s).");
        if ($uploaded > 0) {
            Session::flash('success', "$uploaded image(s) uploaded.");
        }
        if ($errors) {
            Session::flash('error', implode(' ', array_unique($errors)));
        }
        return $this->redirect('invent/properties/' . $id . '/edit');
    }

    public function setMainImage(Request $request, string $id, string $imageId): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties/' . $id . '/edit');
        }
        MediaService::setPropertyMainImage((int) $id, (int) $imageId);
        Session::flash('success', 'Main image updated.');
        return $this->redirect('invent/properties/' . $id . '/edit');
    }

    public function deleteImage(Request $request, string $id, string $imageId): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail('invent/properties/' . $id . '/edit');
        }
        MediaService::deletePropertyImage((int) $imageId);
        Session::flash('success', 'Image deleted.');
        return $this->redirect('invent/properties/' . $id . '/edit');
    }

    /** @return array<string,mixed>|null */
    private function validated(Request $request): ?array
    {
        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('title', 'Property title')->max('title', 'Property title', 255)
            ->in('transaction_type', 'Transaction type', ['for_sale', 'for_rent', 'for_lease', 'other'])
            ->in('price_frequency', 'Price frequency', ['total', 'per_year', 'per_month', 'per_week', 'per_day', 'negotiable'])
            ->in('status', 'Status', ['available', 'reserved', 'sold', 'rented', 'leased', 'unavailable', 'draft']);

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
            'category_id'       => $request->int('category_id', 0) ?: null,
            'title'             => trim((string) $data['title']),
            'short_description' => $this->nullable($request->string('short_description'), 500),
            'description'       => $this->nullable($request->string('description')),
            'transaction_type'  => $request->string('transaction_type', 'for_sale'),
            'property_type'     => $this->nullable($request->string('property_type'), 80),
            'price'             => $request->input('price') !== '' && $request->input('price') !== null
                                    ? number_format((float) $request->input('price'), 2, '.', '') : null,
            'price_frequency'   => $request->string('price_frequency', 'total'),
            'bedrooms'          => $request->input('bedrooms') !== '' ? max(0, $request->int('bedrooms')) : null,
            'bathrooms'         => $request->input('bathrooms') !== '' ? max(0, $request->int('bathrooms')) : null,
            'toilets'           => $request->input('toilets') !== '' ? max(0, $request->int('toilets')) : null,
            'land_size'         => $this->nullable($request->string('land_size'), 80),
            'building_size'     => $this->nullable($request->string('building_size'), 80),
            'parking'           => $this->nullable($request->string('parking'), 80),
            'address'           => $this->nullable($request->string('address'), 255),
            'neighbourhood'     => $this->nullable($request->string('neighbourhood'), 150),
            'city'              => $this->nullable($request->string('city'), 120),
            'state'             => $this->nullable($request->string('state'), 120),
            'country'           => $request->string('country', 'Nigeria') ?: 'Nigeria',
            'features'          => $this->jsonList($request->string('features')),
            'amenities'         => $this->jsonList($request->string('amenities')),
            'specifications'    => $specs ? json_encode($specs) : null,
            'status'            => $request->string('status', 'draft'),
            'is_published'      => $request->input('is_published') ? 1 : 0,
            'is_featured'       => $request->input('is_featured') ? 1 : 0,
            'meta_title'        => $this->nullable($request->string('meta_title'), 255),
            'meta_description'  => $this->nullable($request->string('meta_description'), 320),
        ];
    }

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

    private function csrfFail(string $redirect): Response
    {
        Session::flash('error', 'Your session expired. Please try again.');
        return $this->redirect($redirect);
    }
}
