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
use App\Core\Validator;
use App\Services\PaymentService;

/**
 * Manual payment recording. No gateway is involved.
 */
final class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $q       = $request->string('q');
        $method  = $request->string('method');
        $from    = $request->string('from');
        $to      = $request->string('to');
        $page    = max(1, $request->int('page', 1));
        $perPage = 25;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(o.order_reference LIKE ? OR p.reference LIKE ? OR o.customer_name LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%', '%' . $q . '%');
        }
        if (in_array($method, ['cash', 'bank_transfer', 'pos', 'other'], true)) {
            $where[] = 'p.method = ?';
            $params[] = $method;
        }
        if ($from !== '' && strtotime($from) !== false) {
            $where[] = 'p.paid_at >= ?';
            $params[] = date('Y-m-d 00:00:00', strtotime($from));
        }
        if ($to !== '' && strtotime($to) !== false) {
            $where[] = 'p.paid_at <= ?';
            $params[] = date('Y-m-d 23:59:59', strtotime($to));
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM payments p LEFT JOIN orders o ON o.id = p.order_id WHERE $whereSql",
            $params
        );
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $payments = Database::all(
            "SELECT p.*, o.order_reference, o.customer_name, a.name AS admin_name
             FROM payments p
             LEFT JOIN orders o ON o.id = p.order_id
             LEFT JOIN administrators a ON a.id = p.admin_id
             WHERE $whereSql
             ORDER BY p.paid_at DESC, p.id DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        $summary = [
            'today' => (float) Database::scalar('SELECT COALESCE(SUM(amount),0) FROM payments WHERE DATE(paid_at) = CURDATE()'),
            'month' => (float) Database::scalar('SELECT COALESCE(SUM(amount),0) FROM payments WHERE YEAR(paid_at) = YEAR(CURDATE()) AND MONTH(paid_at) = MONTH(CURDATE())'),
            'total' => (float) Database::scalar('SELECT COALESCE(SUM(amount),0) FROM payments'),
        ];

        // Orders with an outstanding balance, for the record-payment form.
        $openOrders = Database::all(
            "SELECT id, order_reference, customer_name, total, amount_paid, balance
             FROM orders WHERE balance > 0 AND status NOT IN ('cancelled','expired')
             ORDER BY created_at DESC LIMIT 100"
        );

        return $this->adminView('admin/payments/index', [
            'title'      => 'Payments',
            'activeNav'  => 'payments',
            'payments'   => $payments,
            'summary'    => $summary,
            'openOrders' => $openOrders,
            'filters'    => compact('q', 'method', 'from', 'to'),
            'result'     => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    public function store(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/payments');
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('order_id', 'Order')
            ->integer('order_id', 'Order', true, 1)
            ->required('amount', 'Amount')->numeric('amount', 'Amount')->minValue('amount', 'Amount', 0.01)
            ->in('method', 'Payment method', ['cash', 'bank_transfer', 'pos', 'other']);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return $this->redirect('invent/payments');
        }

        $result = PaymentService::recordForOrder(
            (int) $request->input('order_id'),
            (float) $request->input('amount'),
            $request->string('method', 'cash'),
            $request->string('reference') ?: null,
            $request->string('paid_at') ?: null,
            $request->string('note') ?: null,
            Auth::id()
        );

        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            return $this->redirect('invent/payments');
        }

        ActivityLog::record('record_payment', 'order', (int) $request->input('order_id'), 'Recorded payment of ' . $request->input('amount'));
        Session::flash('success', $result['message'] ?? 'Payment recorded.');
        return $this->redirect('invent/orders/' . (int) $request->input('order_id'));
    }
}
