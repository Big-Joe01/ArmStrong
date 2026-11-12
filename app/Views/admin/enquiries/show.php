<?php
/**
 * Admin: property enquiry detail.
 *
 * @var array $enquiry
 */
?>
<div class="flex-between flex-wrap mb-4">
    <div>
        <h2 style="margin:0">Enquiry from <?= e($enquiry['full_name']) ?></h2>
        <div class="muted"><?= e(date('d M Y, H:i', strtotime((string) $enquiry['created_at']))) ?></div>
    </div>
    <a class="btn btn-outline btn-sm" href="<?= e(url('invent/enquiries')) ?>">Back</a>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Message</h2><span class="pill pill-<?= e((string) $enquiry['status']) ?>"><?= e(ucfirst((string) $enquiry['status'])) ?></span></div>
        <div class="panel-body">
            <p style="white-space:pre-line"><?= e((string) $enquiry['message']) ?></p>
        </div>
    </div>

    <aside>
        <div class="panel">
            <div class="panel-head"><h2>Contact</h2></div>
            <div class="panel-body">
                <ul class="spec-list">
                    <li><span class="k">Name</span><span class="v"><?= e($enquiry['full_name']) ?></span></li>
                    <li><span class="k">Phone</span><span class="v"><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $enquiry['phone'])) ?>"><?= e($enquiry['phone']) ?></a></span></li>
                    <?php if ($enquiry['email']): ?><li><span class="k">Email</span><span class="v"><a href="mailto:<?= e($enquiry['email']) ?>"><?= e($enquiry['email']) ?></a></span></li><?php endif; ?>
                    <?php if ($enquiry['property_title']): ?>
                        <li><span class="k">Property</span><span class="v"><?= e($enquiry['property_title']) ?></span></li>
                        <li><span class="k">Reference</span><span class="v"><?= e((string) $enquiry['property_reference']) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Update</h2></div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('invent/enquiries/' . $enquiry['id'] . '/status')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <?php foreach (['new' => 'New', 'read' => 'Read', 'responded' => 'Responded', 'closed' => 'Closed'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $enquiry['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label" for="admin_note">Internal note</label><textarea class="form-control" id="admin_note" name="admin_note" rows="3"><?= e((string) $enquiry['admin_note']) ?></textarea></div>
                    <button class="btn btn-primary btn-block btn-sm" type="submit">Save</button>
                </form>
                <form method="post" action="<?= e(url('invent/enquiries/' . $enquiry['id'] . '/delete')) ?>" style="margin-top:var(--sp-3)">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost btn-block btn-sm" type="submit" data-confirm="Delete this enquiry?" style="color:var(--c-danger)">Delete enquiry</button>
                </form>
            </div>
        </div>
    </aside>
</div>
