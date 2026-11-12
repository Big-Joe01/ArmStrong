<?php
/**
 * Reusable pagination.
 *
 * @var array $result  ['page'=>int,'pages'=>int]
 * @var array $query   extra query params to preserve
 */
$page  = (int) ($result['page'] ?? 1);
$pages = (int) ($result['pages'] ?? 1);
$query = $query ?? [];

$buildUrl = static function (int $p) use ($query): string {
    $query['page'] = $p;
    return url('?' . http_build_query($query));
};

if ($pages <= 1) {
    return;
}

$start = max(1, $page - 2);
$end   = min($pages, $page + 2);
?>
<nav class="pagination" aria-label="Pagination">
    <?php if ($page > 1): ?>
        <a href="<?= e($buildUrl($page - 1)) ?>" rel="prev">&laquo; Prev</a>
    <?php else: ?>
        <span class="disabled">&laquo; Prev</span>
    <?php endif; ?>

    <?php if ($start > 1): ?>
        <a href="<?= e($buildUrl(1)) ?>">1</a>
        <?php if ($start > 2): ?><span class="disabled">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= e($buildUrl($i)) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($end < $pages): ?>
        <?php if ($end < $pages - 1): ?><span class="disabled">…</span><?php endif; ?>
        <a href="<?= e($buildUrl($pages)) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
        <a href="<?= e($buildUrl($page + 1)) ?>" rel="next">Next &raquo;</a>
    <?php else: ?>
        <span class="disabled">Next &raquo;</span>
    <?php endif; ?>
</nav>
