<?php
/**
 * Admin pagination (shared).
 *
 * @var array $result
 * @var array $filters
 */
$page  = (int) ($result['page'] ?? 1);
$pages = (int) ($result['pages'] ?? 1);
$query = $filters ?? [];
if ($pages <= 1) {
    return;
}
$base = $request_path ?? null;
$buildUrl = static function (int $p) use ($query): string {
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
    $query['page'] = $p;
    return '?' . http_build_query($query);
};
$start = max(1, $page - 2);
$end   = min($pages, $page + 2);
?>
<div class="flex-between" style="margin-top:var(--sp-4)">
    <span class="muted" style="font-size:var(--fs-sm)">Page <?= $page ?> of <?= $pages ?></span>
    <nav class="pagination" style="margin-top:0">
        <?php if ($page > 1): ?><a href="<?= e($buildUrl($page - 1)) ?>">&laquo; Prev</a><?php else: ?><span class="disabled">&laquo; Prev</span><?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="<?= e($buildUrl($i)) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $pages): ?><a href="<?= e($buildUrl($page + 1)) ?>">Next &raquo;</a><?php else: ?><span class="disabled">Next &raquo;</span><?php endif; ?>
    </nav>
</div>
