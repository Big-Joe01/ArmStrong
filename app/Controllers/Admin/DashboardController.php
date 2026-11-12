<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

/**
 * Administration dashboard with real, database-derived statistics.
 */
final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $stats = [
            'products_total'      => (int) Database::scalar('SELECT COUNT(*) FROM products'),
            'products_published'  => (int) Database::scalar('SELECT COUNT(*) FROM products WHERE is_published = 1'),
            'products_out'        => (int) Database::scalar('SELECT COUNT(*) FROM products WHERE (stock_on_hand - reserved_qty) <= 0'),
            'products_low'        => (int) Database::scalar('SELECT COUNT(*) FROM products WHERE (stock_on_hand - reserved_qty) > 0 AND (stock_on_hand - reserved_qty) <= low_stock_threshold'),
            'reservations_active' => (int) Database::scalar("SELECT COUNT(*) FROM stock_reservations WHERE status = 'active'"),
            'orders_pending'      => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE status IN ('awaiting_payment','processing','paid')"),
            'orders_paid'         => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'"),
            'orders_unpaid'       => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE payment_status = 'unpaid'"),
            'orders_expired'      => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE status = 'expired'"),
            'properties_total'    => (int) Database::scalar('SELECT COUNT(*) FROM properties'),
            'properties_available'=> (int) Database::scalar("SELECT COUNT(*) FROM properties WHERE status = 'available' AND is_published = 1"),
            'enquiries_new'       => (int) Database::scalar("SELECT COUNT(*) FROM property_enquiries WHERE status = 'new'"),
            'messages_new'        => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'"),
            'reviews_pending'     => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
        ];

        $recentOrders = Database::all(
            'SELECT * FROM orders ORDER BY created_at DESC LIMIT 8'
        );
        $recentEnquiries = Database::all(
            'SELECT e.*, p.title AS property_title FROM property_enquiries e
             LEFT JOIN properties p ON p.id = e.property_id
             ORDER BY e.created_at DESC LIMIT 5'
        );
        $recentMessages = Database::all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5');
        $lowStock = Database::all(
            'SELECT id, name, stock_on_hand, reserved_qty, low_stock_threshold
             FROM products WHERE (stock_on_hand - reserved_qty) <= low_stock_threshold
             ORDER BY (stock_on_hand - reserved_qty) ASC LIMIT 8'
        );
        $expiringSoon = Database::all(
            "SELECT o.* FROM orders o
             JOIN stock_reservations r ON r.order_id = o.id AND r.status = 'active'
             WHERE o.reservation_expires_at IS NOT NULL AND o.reservation_expires_at >= NOW()
             GROUP BY o.id
             ORDER BY o.reservation_expires_at ASC LIMIT 6"
        );

        return $this->adminView('admin/dashboard', [
            'title'          => 'Dashboard',
            'activeNav'      => 'dashboard',
            'stats'          => $stats,
            'recentOrders'   => $recentOrders,
            'recentEnquiries'=> $recentEnquiries,
            'recentMessages' => $recentMessages,
            'lowStock'       => $lowStock,
            'expiringSoon'   => $expiringSoon,
            'admin'          => Auth::user(),
        ]);
    }
}
