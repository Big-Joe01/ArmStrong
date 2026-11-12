<?php
/**
 * Admin: activity log.
 *
 * @var list<array> $logs
 * @var list<string> $actions
 * @var array $filters
 * @var array $result
 */
?>
<div class="panel">
    <div class="panel-head">
        <form class="toolbar" method="get" action="<?= e(url('invent/activity')) ?>" style="margin:0">
            <div class="form-group">
                <select class="form-control" name="action" data-auto-submit>
                    <option value="">All actions</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= e($action) ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>><?= e($action) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        </form>
    </div>
    <div class="panel-body tight">
        <?php if ($logs): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Entity</th><th>Description</th><th>IP</th></tr></thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= e(date('d M Y H:i:s', strtotime((string) $log['created_at']))) ?></td>
                                <td><?= e($log['admin_name'] ?? 'System') ?></td>
                                <td><span class="badge"><?= e($log['action']) ?></span></td>
                                <td><?= e((string) $log['entity_type']) ?><?= $log['entity_id'] ? ' #' . (int) $log['entity_id'] : '' ?></td>
                                <td><?= e((string) $log['description']) ?></td>
                                <td class="muted"><?= e((string) $log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No activity recorded.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/admin-pagination.php'; ?>
