<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

/**
 * Read-only activity log viewer.
 */
final class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $action = $request->string('action');
        $page   = max(1, $request->int('page', 1));
        $perPage = 40;

        $where  = ['1=1'];
        $params = [];
        if ($action !== '') {
            $where[] = 'l.action = ?';
            $params[] = $action;
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::scalar("SELECT COUNT(*) FROM activity_logs l WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $logs = Database::all(
            "SELECT l.*, a.name AS admin_name FROM activity_logs l
             LEFT JOIN administrators a ON a.id = l.admin_id
             WHERE $whereSql ORDER BY l.created_at DESC, l.id DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        $actions = Database::all('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC');

        return $this->adminView('admin/activity/index', [
            'title'   => 'Activity log',
            'activeNav' => 'activity',
            'logs'    => $logs,
            'actions' => array_column($actions, 'action'),
            'filters' => ['action' => $action],
            'result'  => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }
}
