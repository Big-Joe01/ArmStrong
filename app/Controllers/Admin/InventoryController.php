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
use App\Services\InventoryService;

/**
 * Inventory management and the stock movement ledger.
 */
final class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $q       = $request->string('q');
        $level   = $request->string('level');
        $page    = max(1, $request->int('page', 1));
        $perPage = 20;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.name LIKE ? OR p.reference LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        if ($level === 'out') {
            $where[] = '(p.stock_on_hand - p.reserved_qty) <= 0';
        } elseif ($level === 'low') {
            $where[] = '(p.stock_on_hand - p.reserved_qty) > 0 AND (p.stock_on_hand - p.reserved_qty) <= p.low_stock_threshold';
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM products p WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $products = Database::all(
            "SELECT p.id, p.name, p.reference, p.unit, p.price, p.stock_on_hand, p.reserved_qty,
                    p.low_stock_threshold, p.availability, p.is_published, c.name AS category_name
             FROM products p LEFT JOIN product_categories c ON c.id = p.category_id
             WHERE $whereSql
             ORDER BY (p.stock_on_hand - p.reserved_qty) ASC, p.name ASC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return $this->adminView('admin/inventory/index', [
            'title'     => 'Inventory',
            'activeNav' => 'inventory',
            'products'  => $products,
            'filters'   => ['q' => $q, 'level' => $level],
            'result'    => ['page' => $page, 'pages' => $pages, 'total' => $total],
            'totals'    => [
                'stock'    => (int) Database::scalar('SELECT COALESCE(SUM(stock_on_hand),0) FROM products'),
                'reserved' => (int) Database::scalar('SELECT COALESCE(SUM(reserved_qty),0) FROM products'),
            ],
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $productId = (int) $id;
        $product = Database::first('SELECT * FROM products WHERE id = ?', [$productId]);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            return $this->redirect('invent/inventory');
        }

        $movements = Database::all(
            'SELECT m.*, a.name AS admin_name FROM inventory_movements m
             LEFT JOIN administrators a ON a.id = m.admin_id
             WHERE m.product_id = ? ORDER BY m.created_at DESC, m.id DESC LIMIT 100',
            [$productId]
        );

        $reservations = Database::all(
            "SELECT r.*, o.order_reference FROM stock_reservations r
             JOIN orders o ON o.id = r.order_id
             WHERE r.product_id = ? ORDER BY r.created_at DESC LIMIT 40",
            [$productId]
        );

        $reconciled = InventoryService::reconcile($productId);

        return $this->adminView('admin/inventory/show', [
            'title'        => 'Inventory — ' . $product['name'],
            'activeNav'    => 'inventory',
            'product'      => $product,
            'movements'    => $movements,
            'reservations' => $reservations,
            'reconciled'   => $reconciled,
            'available'    => (int) $product['stock_on_hand'] - (int) $product['reserved_qty'],
        ]);
    }

    public function adjust(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/inventory');
        }

        $productId = (int) $id;
        $type   = $request->string('adjustment_type', 'add');
        $amount = abs($request->int('quantity', 0));
        $reason = $request->string('reason');

        if ($amount <= 0) {
            Session::flash('error', 'Enter a quantity greater than zero.');
            return $this->redirect('invent/inventory/' . $id);
        }

        $delta = $type === 'remove' ? -$amount : $amount;

        try {
            InventoryService::adjustStock($productId, $delta, Auth::id(), $reason ?: 'Manual adjustment');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect('invent/inventory/' . $id);
        }

        ActivityLog::record('adjust_stock', 'product', $productId, sprintf('%+d units (%s)', $delta, $reason ?: 'manual'));
        Session::flash('success', 'Inventory adjusted.');
        return $this->redirect('invent/inventory/' . $id);
    }
}
