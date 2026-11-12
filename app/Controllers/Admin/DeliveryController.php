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
use App\Core\Validator;

/**
 * Delivery option (rate) management.
 */
final class DeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->adminView('admin/delivery/index', [
            'title'     => 'Delivery settings',
            'activeNav' => 'delivery',
            'rates'     => Database::all('SELECT * FROM delivery_rates ORDER BY sort_order ASC, fee ASC'),
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('name', 'Option name')->max('name', 'Option name', 120)
            ->required('fee', 'Fee')->numeric('fee', 'Fee')->minValue('fee', 'Fee', 0);
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return $this->redirect('invent/delivery-settings');
        }

        $id = Database::insert('delivery_rates', [
            'name'        => $request->string('name'),
            'description' => $this->nullable($request->string('description'), 255),
            'fee'         => number_format((float) $request->input('fee', 0), 2, '.', ''),
            'timeframe'   => $this->nullable($request->string('timeframe'), 120),
            'sort_order'  => $request->int('sort_order', 0),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ]);

        ActivityLog::record('create', 'delivery_rate', $id, 'Created delivery option.');
        Session::flash('success', 'Delivery option created.');
        return $this->redirect('invent/delivery-settings');
    }

    public function update(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $rateId = (int) $id;
        if (Database::first('SELECT id FROM delivery_rates WHERE id = ?', [$rateId]) === null) {
            Session::flash('error', 'Delivery option not found.');
            return $this->redirect('invent/delivery-settings');
        }

        Database::update('delivery_rates', [
            'name'        => $request->string('name'),
            'description' => $this->nullable($request->string('description'), 255),
            'fee'         => number_format((float) $request->input('fee', 0), 2, '.', ''),
            'timeframe'   => $this->nullable($request->string('timeframe'), 120),
            'sort_order'  => $request->int('sort_order', 0),
            'is_active'   => $request->input('is_active') ? 1 : 0,
        ], ['id' => $rateId]);

        ActivityLog::record('update', 'delivery_rate', $rateId, 'Updated delivery option.');
        Session::flash('success', 'Delivery option updated.');
        return $this->redirect('invent/delivery-settings');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            return $this->csrfFail();
        }
        $rateId = (int) $id;
        $inUse = (int) Database::scalar('SELECT COUNT(*) FROM orders WHERE delivery_rate_id = ?', [$rateId]);
        if ($inUse > 0) {
            // Preserve history: deactivate instead of deleting a rate used by orders.
            Database::update('delivery_rates', ['is_active' => 0], ['id' => $rateId]);
            Session::flash('success', 'This option is used by past orders, so it was disabled rather than deleted.');
            return $this->redirect('invent/delivery-settings');
        }
        Database::delete('delivery_rates', ['id' => $rateId]);
        ActivityLog::record('delete', 'delivery_rate', $rateId, 'Deleted delivery option.');
        Session::flash('success', 'Delivery option deleted.');
        return $this->redirect('invent/delivery-settings');
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
        return $this->redirect('invent/delivery-settings');
    }
}
