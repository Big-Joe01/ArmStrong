<?php
/**
 * Admin: property enquiries list.
 *
 * @var list<array> $enquiries
 * @var string $status
 */
use App\Core\Str;

$tabs = ['' => 'All', 'new' => 'New', 'read' => 'Read', 'responded' => 'Responded', 'closed' => 'Closed'];
?>
<div class="tabs">
    <?php foreach ($tabs as $value => $label): ?>
        <a class="tab <?= $status === $value ? 'active' : '' ?>" href="<?= e(url('invent/enquiries' . ($value ? '?status=' . $value : ''))) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-body tight">
        <?php if ($enquiries): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>From</th><th>Property</th><th>Message</th><th>Status</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <td><strong><?= e($enquiry['full_name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)"><?= e($enquiry['phone']) ?></div></td>
                                <td><?php if ($enquiry['property_title']): ?><a href="<?= e(url('invent/enquiries/' . $enquiry['id'])) ?>"><?= e(Str::excerpt((string) $enquiry['property_title'], 40)) ?></a><?php else: ?><span class="muted">General</span><?php endif; ?></td>
                                <td><?= e(Str::excerpt((string) $enquiry['message'], 60)) ?></td>
                                <td><span class="pill pill-<?= e((string) $enquiry['status']) ?>"><?= e(ucfirst((string) $enquiry['status'])) ?></span></td>
                                <td><?= e(date('d M Y H:i', strtotime((string) $enquiry['created_at']))) ?></td>
                                <td><a class="btn btn-outline btn-sm" href="<?= e(url('invent/enquiries/' . $enquiry['id'])) ?>">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No enquiries found.</div>
        <?php endif; ?>
    </div>
</div>
