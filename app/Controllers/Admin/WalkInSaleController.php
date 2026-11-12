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
use App\Core\View;
use App\Services\WalkInSaleService;

/**
 * Walk-in (physical store) sales. Deducts inventory immediately.
 */
final class WalkInSaleController extends Controller
{
    public function index(Request $request): Response
    {
        $q       = $request->string('q');
        $page    = max(1, $request->int('page', 1));
        $perPage = 20;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(s.sale_reference LIKE ? OR s.customer_name LIKE ? OR s.customer_phone LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%', '%' . $q . '%');
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) FROM walk_in_sales s WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $sales = Database::all(
            "SELECT s.*, (SELECT COUNT(*) FROM walk_in_sale_items i WHERE i.walk_in_sale_id = s.id) AS item_count
             FROM walk_in_sales s WHERE $whereSql
             ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return $this->adminView('admin/walkin/index', [
            'title'     => 'Walk-in sales',
            'activeNav' => 'walkin',
            'sales'     => $sales,
            'filters'   => ['q' => $q],
            'result'    => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->adminView('admin/walkin/create', [
            'title'     => 'Record walk-in sale',
            'activeNav' => 'walkin',
            'products'  => Database::all(
                'SELECT p.id, p.name, p.reference, p.unit, p.price, p.stock_on_hand, p.reserved_qty,
                        (p.stock_on_hand - p.reserved_qty) AS available
                 FROM products p WHERE p.is_published = 1 OR p.stock_on_hand > 0
                 ORDER BY p.name ASC LIMIT 500'
            ),
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/walk-in-sales/create');
        }

        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);
        $items = [];
        if (is_array($productIds) && is_array($quantities)) {
            foreach ($productIds as $i => $pid) {
                $qty = (int) ($quantities[$i] ?? 0);
                if ((int) $pid > 0 && $qty > 0) {
                    $items[] = ['product_id' => (int) $pid, 'quantity' => $qty];
                }
            }
        }

        try {
            $result = WalkInSaleService::create($items, [
                'customer_name'  => $request->string('customer_name') ?: null,
                'customer_phone' => $request->string('customer_phone') ?: null,
                'note'           => $request->string('note') ?: null,
                'amount_paid'    => (float) $request->input('amount_paid', 0),
                'method'         => $request->string('payment_method', 'cash'),
                'reference'      => $request->string('payment_reference') ?: null,
            ], Auth::id());
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            Session::flashInput($request->all());
            return $this->redirect('invent/walk-in-sales/create');
        }

        ActivityLog::record('create', 'walk_in_sale', $result['sale_id'], 'Recorded walk-in sale ' . $result['reference']);
        Session::flash('success', 'Walk-in sale recorded. Inventory updated.');
        return $this->redirect('invent/walk-in-sales/' . $result['sale_id']);
    }

    public function show(Request $request, string $id): Response
    {
        $saleId = (int) $id;
        $sale = Database::first('SELECT s.*, a.name AS admin_name FROM walk_in_sales s LEFT JOIN administrators a ON a.id = s.admin_id WHERE s.id = ?', [$saleId]);
        if ($sale === null) {
            Session::flash('error', 'Sale not found.');
            return $this->redirect('invent/walk-in-sales');
        }

        $items = Database::all('SELECT * FROM walk_in_sale_items WHERE walk_in_sale_id = ? ORDER BY id', [$saleId]);
        foreach ($items as &$item) {
            $item['selected_options'] = json_decode((string) ($item['selected_options'] ?? '[]'), true) ?: [];
        }
        unset($item);

        return $this->adminView('admin/walkin/show', [
            'title'     => 'Sale ' . $sale['sale_reference'],
            'activeNav' => 'walkin',
            'sale'      => $sale,
            'items'     => $items,
        ]);
    }

    public function receipt(Request $request, string $id): Response
    {
        $saleId = (int) $id;
        $sale = Database::first('SELECT s.*, a.name AS admin_name FROM walk_in_sales s LEFT JOIN administrators a ON a.id = s.admin_id WHERE s.id = ?', [$saleId]);
        if ($sale === null) {
            Session::flash('error', 'Sale not found.');
            return $this->redirect('invent/walk-in-sales');
        }
        $items = Database::all('SELECT * FROM walk_in_sale_items WHERE walk_in_sale_id = ? ORDER BY id', [$saleId]);

        return Response::html(View::render('admin/walkin/receipt', [
            'sale'  => $sale,
            'items' => $items,
        ]));
    }
}
