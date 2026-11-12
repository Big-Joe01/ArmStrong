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
 * Property enquiry inbox.
 */
final class EnquiryController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status');
        $where  = ['1=1'];
        $params = [];
        if (in_array($status, ['new', 'read', 'responded', 'closed'], true)) {
            $where[] = 'e.status = ?';
            $params[] = $status;
        }

        $enquiries = Database::all(
            'SELECT e.*, p.title AS property_title, p.reference AS property_reference
             FROM property_enquiries e
             LEFT JOIN properties p ON p.id = e.property_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY e.created_at DESC LIMIT 200',
            $params
        );

        return $this->adminView('admin/enquiries/index', [
            'title'     => 'Property enquiries',
            'activeNav' => 'enquiries',
            'enquiries' => $enquiries,
            'status'    => $status,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $enquiryId = (int) $id;
        $enquiry = Database::first(
            'SELECT e.*, p.title AS property_title, p.slug AS property_slug, p.reference AS property_reference
             FROM property_enquiries e LEFT JOIN properties p ON p.id = e.property_id
             WHERE e.id = ?',
            [$enquiryId]
        );
        if ($enquiry === null) {
            Session::flash('error', 'Enquiry not found.');
            return $this->redirect('invent/enquiries');
        }

        // Mark as read on first view.
        if ($enquiry['status'] === 'new') {
            Database::update('property_enquiries', ['status' => 'read'], ['id' => $enquiryId]);
            $enquiry['status'] = 'read';
        }

        return $this->adminView('admin/enquiries/show', [
            'title'     => 'Enquiry from ' . $enquiry['full_name'],
            'activeNav' => 'enquiries',
            'enquiry'   => $enquiry,
        ]);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/enquiries');
        }
        $status = $request->string('status');
        if (!in_array($status, ['new', 'read', 'responded', 'closed'], true)) {
            Session::flash('error', 'Invalid status.');
            return $this->redirect('invent/enquiries/' . $id);
        }

        Database::update('property_enquiries', [
            'status'     => $status,
            'admin_note' => $request->string('admin_note') ?: null,
        ], ['id' => (int) $id]);

        ActivityLog::record('update_status', 'property_enquiry', (int) $id, 'Enquiry marked as ' . $status);
        Session::flash('success', 'Enquiry updated.');
        return $this->redirect('invent/enquiries/' . $id);
    }

    public function destroy(Request $request, string $id): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/enquiries');
        }
        Database::delete('property_enquiries', ['id' => (int) $id]);
        ActivityLog::record('delete', 'property_enquiry', (int) $id, 'Deleted enquiry.');
        Session::flash('success', 'Enquiry deleted.');
        return $this->redirect('invent/enquiries');
    }
}
