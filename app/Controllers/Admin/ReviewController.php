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

/**
 * Customer review moderation. Only approved reviews appear publicly.
 */
final class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status', 'pending');
        $where  = ['1=1'];
        $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected', 'hidden'], true)) {
            $where[] = 'r.status = ?';
            $params[] = $status;
        } else {
            $status = 'all';
        }

        $reviews = Database::all(
            'SELECT r.*, p.name AS product_name
             FROM reviews r LEFT JOIN products p ON p.id = r.product_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY r.created_at DESC LIMIT 200',
            $params
        );

        $counts = [
            'pending'  => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
            'approved' => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'approved'"),
            'rejected' => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'"),
            'hidden'   => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'hidden'"),
        ];

        return $this->adminView('admin/reviews/index', [
            'title'     => 'Customer reviews',
            'activeNav' => 'reviews',
            'reviews'   => $reviews,
            'status'    => $status,
            'counts'    => $counts,
        ]);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/reviews');
        }
        $status = $request->string('status');
        if (!in_array($status, ['pending', 'approved', 'rejected', 'hidden'], true)) {
            Session::flash('error', 'Invalid status.');
            return $this->redirect('invent/reviews');
        }

        Database::update('reviews', ['status' => $status], ['id' => (int) $id]);
        ActivityLog::record('moderate_review', 'review', (int) $id, 'Review set to ' . $status);
        Session::flash('success', 'Review ' . $status . '.');
        return $this->back($request, 'invent/reviews');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/reviews');
        }
        Database::delete('reviews', ['id' => (int) $id]);
        ActivityLog::record('delete', 'review', (int) $id, 'Deleted review.');
        Session::flash('success', 'Review deleted.');
        return $this->back($request, 'invent/reviews');
    }
}
