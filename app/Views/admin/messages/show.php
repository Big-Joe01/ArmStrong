<?php
/**
 * Admin: contact message detail.
 *
 * @var array $message
 */
?>
<div class="flex-between flex-wrap mb-4">
    <div>
        <h2 style="margin:0"><?= e($message['subject'] ?: 'Message from ' . $message['full_name']) ?></h2>
        <div class="muted"><?= e(date('d M Y, H:i', strtotime((string) $message['created_at']))) ?></div>
    </div>
    <a class="btn btn-outline btn-sm" href="<?= e(url('invent/messages')) ?>">Back</a>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Message</h2><span class="pill pill-<?= e((string) $message['status']) ?>"><?= e(ucfirst((string) $message['status'])) ?></span></div>
        <div class="panel-body"><p style="white-space:pre-line"><?= e((string) $message['message']) ?></p></div>
    </div>

    <aside>
        <div class="panel">
            <div class="panel-head"><h2>Contact</h2></div>
            <div class="panel-body">
                <ul class="spec-list">
                    <li><span class="k">Name</span><span class="v"><?= e($message['full_name']) ?></span></li>
                    <li><span class="k">Email</span><span class="v"><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></span></li>
                    <?php if ($message['phone']): ?><li><span class="k">Phone</span><span class="v"><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $message['phone'])) ?>"><?= e($message['phone']) ?></a></span></li><?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Update</h2></div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('invent/messages/' . $message['id'] . '/status')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <?php foreach (['unread' => 'Unread', 'read' => 'Read', 'archived' => 'Archived'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $message['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label" for="admin_note">Internal note</label><textarea class="form-control" id="admin_note" name="admin_note" rows="3"><?= e((string) $message['admin_note']) ?></textarea></div>
                    <button class="btn btn-primary btn-block btn-sm" type="submit">Save</button>
                </form>
                <form method="post" action="<?= e(url('invent/messages/' . $message['id'] . '/delete')) ?>" style="margin-top:var(--sp-3)">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost btn-block btn-sm" type="submit" data-confirm="Delete this message?" style="color:var(--c-danger)">Delete message</button>
                </form>
            </div>
        </div>
    </aside>
</div>
