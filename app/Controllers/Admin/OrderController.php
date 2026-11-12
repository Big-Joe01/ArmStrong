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
use App\Services\OrderService;
use App\Services\PaymentService;

/**
 * Online order management: search, filter, status, payments, receipts.
 */
final class OrderController extends Controller
{
    private const STATUSES = [
        'awaiting_payment', 'paid', 'processing', 'ready_for_collection',
        'out_for_delivery', 'delivered', 'collected', 'cancelled', 'expired',
    ];

    public function index(Request $request): Response
    {
        $q       = $request->string('q');
        $status  = $request->string('status');
        $payment = $request->string('payment');
        $from    = $request->string('from');
        $to      = $request->string('to');
        $page    = max(1, $request->int('page', 1));
        $perPage = 20;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(o.order_reference LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%', '%' . $q . '%');
        }
        if (in_array($status, self::STATUSES, true)) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }
        if (in_array($payment, ['unpaid', 'partial', 'paid'], true)) {
            $where[] = 'o.payment_status = ?';
            $params[] = $payment;
        }
        if ($from !== '' && strtotime($from) !== false) {
            $where[] = 'o.created_at >= ?';
            $params[] = date('Y-m-d 00:00:00', strtotime($from));
        }
        if ($to !== '' && strtotime($to) !== false) {
            $where[] = 'o.created_at <= ?';
            $params[] = date('Y-m-d 23:59:59', strtotime($to));
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM orders o WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $orders = Database::all(
            "SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count
             FROM orders o WHERE $whereSql
             ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return $this->adminView('admin/orders/index', [
            'title'    => 'Orders',
            'activeNav'=> 'orders',
            'orders'   => $orders,
            'filters'  => compact('q', 'status', 'payment', 'from', 'to'),
            'statuses' => self::STATUSES,
            'result'   => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $orderId = (int) $id;
        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null) {
            Session::flash('error', 'Order not found.');
            return $this->redirect('invent/orders');
        }

        $items = Database::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
        foreach ($items as &$item) {
            $item['selected_options'] = json_decode((string) ($item['selected_options'] ?? '[]'), true) ?: [];
        }
        unset($item);

        $reservations = Database::all('SELECT * FROM stock_reservations WHERE order_id = ? ORDER BY id', [$orderId]);
        $payments     = PaymentService::forOrder($orderId);
        $history      = Database::all(
            'SELECT h.*, a.name AS admin_name FROM order_status_history h
             LEFT JOIN administrators a ON a.id = h.admin_id
             WHERE h.order_id = ? ORDER BY h.created_at DESC, h.id DESC',
            [$orderId]
        );

        return $this->adminView('admin/orders/show', [
            'title'       => 'Order ' . $order['order_reference'],
            'activeNav'   => 'orders',
            'order'       => $order,
            'items'       => $items,
            'reservations'=> $reservations,
            'payments'    => $payments,
            'history'     => $history,
            'statuses'    => self::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/orders/' . $id);
        }

        $orderId = (int) $id;
        $status  = $request->string('status');
        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            return $this->redirect('invent/orders/' . $id);
        }

        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null) {
            Session::flash('error', 'Order not found.');
            return $this->redirect('invent/orders');
        }

        // Cancelling or expiring releases stock; defer to the service so the
        // release stays idempotent.
        if (in_array($status, ['cancelled', 'expired'], true)) {
            OrderService::cancel($orderId, 'Status set to ' . $status . ' by administrator', Auth::id());
            Session::flash('success', 'Order marked as ' . $status . ' and any reserved stock released.');
            return $this->redirect('invent/orders/' . $id);
        }

        // Terminal fulfilment states convert the reservation into a sale and
        // deduct physical stock exactly once.
        $fulfilled = in_array($status, ['delivered', 'collected'], true)
            && !in_array($order['status'], ['delivered', 'collected'], true);
        if ($fulfilled) {
            OrderService::complete($orderId, 'Order ' . $status, Auth::id());
        }

        Database::transaction(function () use ($orderId, $order, $status, $request): void {
            Database::update('orders', ['status' => $status], ['id' => $orderId]);
            Database::insert('order_status_history', [
                'order_id'    => $orderId,
                'from_status' => $order['status'],
                'to_status'   => $status,
                'note'        => $request->string('note') ?: null,
                'admin_id'    => Auth::id(),
            ]);
        });

        ActivityLog::record('update_status', 'order', $orderId, 'Order status set to ' . $status);
        Session::flash('success', 'Order status updated.');
        return $this->redirect('invent/orders/' . $id);
    }

    public function cancel(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/orders/' . $id);
        }
        $reason = $request->string('reason') ?: 'Cancelled by administrator';
        OrderService::cancel((int) $id, $reason, Auth::id());
        ActivityLog::record('cancel', 'order', (int) $id, $reason);
        Session::flash('success', 'Order cancelled and stock released.');
        return $this->redirect('invent/orders/' . $id);
    }

    public function releaseStock(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/orders/' . $id);
        }
        $released = \App\Services\InventoryService::releaseOrderReservations((int) $id, 'Manual release by administrator');
        ActivityLog::record('release_stock', 'order', (int) $id, "Released $released reservation(s).");
        Session::flash('success', $released > 0 ? "Released $released reservation(s)." : 'No active reservations to release.');
        return $this->redirect('invent/orders/' . $id);
    }

    public function saveNote(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/orders/' . $id);
        }
        Database::update('orders', ['admin_note' => $request->string('admin_note') ?: null], ['id' => (int) $id]);
        Session::flash('success', 'Internal note saved.');
        return $this->redirect('invent/orders/' . $id);
    }

    public function receipt(Request $request, string $id): Response
    {
        $orderId = (int) $id;
        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null) {
            Session::flash('error', 'Order not found.');
            return $this->redirect('invent/orders');
        }
        $items = Database::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
        foreach ($items as &$item) {
            $item['selected_options'] = json_decode((string) ($item['selected_options'] ?? '[]'), true) ?: [];
        }
        unset($item);

        $html = View::render('admin/orders/receipt', [
            'order'    => $order,
            'items'    => $items,
            'payments' => PaymentService::forOrder($orderId),
        ]);
        return Response::html($html);
    }
}
