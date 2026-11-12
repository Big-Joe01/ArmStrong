<?php
/**
 * Admin: reviews moderation.
 *
 * @var list<array> $reviews
 * @var string $status
 * @var array $counts
 */
use App\Core\Str;

$tabs = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'hidden' => 'Hidden'];
?>
<div class="tabs">
    <?php foreach ($tabs as $value => $label): ?>
        <a class="tab <?= $status === $value ? 'active' : '' ?>" href="<?= e(url('invent/reviews' . ($value ? '?status=' . $value : ''))) ?>">
            <?= e($label) ?><?php if ($value && isset($counts[$value])): ?> <span class="muted">(<?= (int) $counts[$value] ?>)</span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-body tight">
        <?php if ($reviews): ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Customer</th><th>Rating</th><th>Product</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><strong><?= e($review['customer_name']) ?></strong><?php if ($review['email']): ?><div class="muted" style="font-size:var(--fs-xs)"><?= e($review['email']) ?></div><?php endif; ?></td>
                                <td><?= str_repeat('★', (int) $review['rating']) ?><span class="muted"><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></td>
                                <td><?= e($review['product_name'] ?? 'General') ?></td>
                                <td>
                                    <?php if ($review['title']): ?><strong><?= e($review['title']) ?></strong><?php endif; ?>
                                    <div><?= e(Str::excerpt((string) $review['body'], 90)) ?></div>
                                </td>
                                <td><span class="pill pill-<?= e((string) $review['status']) ?>"><?= e(ucfirst((string) $review['status'])) ?></span></td>
                                <td><?= e(date('d M Y', strtotime((string) $review['created_at']))) ?></td>
                                <td>
                                    <div class="flex" style="gap:var(--sp-2);flex-wrap:wrap">
                                        <?php if ($review['status'] !== 'approved'): ?>
                                            <form method="post" action="<?= e(url('invent/reviews/' . $review['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="approved"><button class="btn btn-outline btn-sm" type="submit">Approve</button></form>
                                        <?php endif; ?>
                                        <?php if ($review['status'] !== 'rejected'): ?>
                                            <form method="post" action="<?= e(url('invent/reviews/' . $review['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="rejected"><button class="btn btn-ghost btn-sm" type="submit">Reject</button></form>
                                        <?php endif; ?>
                                        <?php if ($review['status'] !== 'hidden'): ?>
                                            <form method="post" action="<?= e(url('invent/reviews/' . $review['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="hidden"><button class="btn btn-ghost btn-sm" type="submit">Hide</button></form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= e(url('invent/reviews/' . $review['id'] . '/delete')) ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this review?" style="color:var(--c-danger)">Delete</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-empty">No reviews found.</div>
        <?php endif; ?>
    </div>
</div>
