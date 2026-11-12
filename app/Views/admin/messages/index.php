<?php
/**
 * Admin: contact messages list.
 *
 * @var list<array> $messages
 * @var string $status
 * @var array $counts
 */
use App\Core\Str;

$tabs = ['' => 'All', 'unread' => 'Unread', 'read' => 'Read', 'archived' => 'Archived'];
?>
<div class="tabs">
    <?php foreach ($tabs as $value => $label): ?>
        <a class="tab <?= $status === $value ? 'active' : '' ?>" href="<?= e(url('invent/messages' . ($value ? '?status=' . $value : ''))) ?>">
            <?= e($label) ?><?php if ($value && isset($counts[$value])): ?> <span class="muted">(<?= (int) $counts[$value] ?>)</span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-body tight">
        <?php if ($messages): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Status</th><th>Received</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr class="<?= $message['status'] === 'unread' ? 'row-unread' : '' ?>">
                                <td><strong><?= e($message['full_name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)"><?= e($message['email']) ?></div></td>
                                <td><?= e($message['subject'] ?: '—') ?></td>
                                <td><?= e(Str::excerpt((string) $message['message'], 60)) ?></td>
                                <td><span class="pill pill-<?= e((string) $message['status']) ?>"><?= e(ucfirst((string) $message['status'])) ?></span></td>
                                <td><?= e(date('d M Y H:i', strtotime((string) $message['created_at']))) ?></td>
                                <td>
                                    <div class="flex" style="gap:var(--sp-2);flex-wrap:wrap">
                                        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/messages/' . $message['id'])) ?>">Open</a>
                                        <?php if ($message['status'] !== 'archived'): ?>
                                            <form method="post" action="<?= e(url('invent/messages/' . $message['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="archived"><button class="btn btn-ghost btn-sm" type="submit">Archive</button></form>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(url('invent/messages/' . $message['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="read"><button class="btn btn-ghost btn-sm" type="submit">Restore</button></form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= e(url('invent/messages/' . $message['id'] . '/delete')) ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this message?" style="color:var(--c-danger)">Delete</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No messages found.</div>
        <?php endif; ?>
    </div>
</div>
