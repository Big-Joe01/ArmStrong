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
 * Contact message inbox.
 */
final class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status');
        $where  = ['1=1'];
        $params = [];
        if (in_array($status, ['unread', 'read', 'archived'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $messages = Database::all(
            'SELECT * FROM contact_messages WHERE ' . implode(' AND ', $where) . '
             ORDER BY created_at DESC LIMIT 200',
            $params
        );

        $counts = [
            'unread'   => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'"),
            'read'     => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'"),
            'archived' => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'archived'"),
        ];

        return $this->adminView('admin/messages/index', [
            'title'     => 'Contact messages',
            'activeNav' => 'messages',
            'messages'  => $messages,
            'status'    => $status,
            'counts'    => $counts,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $messageId = (int) $id;
        $message = Database::first('SELECT * FROM contact_messages WHERE id = ?', [$messageId]);
        if ($message === null) {
            Session::flash('error', 'Message not found.');
            return $this->redirect('invent/messages');
        }

        if ($message['status'] === 'unread') {
            Database::update('contact_messages', ['status' => 'read'], ['id' => $messageId]);
            $message['status'] = 'read';
        }

        return $this->adminView('admin/messages/show', [
            'title'     => 'Message from ' . $message['full_name'],
            'activeNav' => 'messages',
            'message'   => $message,
        ]);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/messages');
        }
        $status = $request->string('status');
        if (!in_array($status, ['unread', 'read', 'archived'], true)) {
            Session::flash('error', 'Invalid status.');
            return $this->redirect('invent/messages');
        }

        Database::update('contact_messages', [
            'status'     => $status,
            'admin_note' => $request->string('admin_note') ?: null,
        ], ['id' => (int) $id]);

        ActivityLog::record('update_status', 'contact_message', (int) $id, 'Message marked as ' . $status);
        Session::flash('success', 'Message updated.');
        return $this->back($request, 'invent/messages');
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/messages');
        }
        Database::delete('contact_messages', ['id' => (int) $id]);
        ActivityLog::record('delete', 'contact_message', (int) $id, 'Deleted message.');
        Session::flash('success', 'Message deleted.');
        return $this->back($request, 'invent/messages');
    }
}
