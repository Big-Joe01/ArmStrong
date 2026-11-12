<?php
/**
 * Admin: walk-in sale detail.
 *
 * @var array $sale
 * @var list<array> $items
 */
?>
<div class="flex-between flex-wrap mb-4">
    <div>
        <h2 style="margin:0"><?= e($sale['sale_reference']) ?></h2>
        <div class="muted"><?= e(date('d M Y, H:i', strtotime((string) $sale['created_at']))) ?><?= $sale['admin_name'] ? ' · recorded by ' . e($sale['admin_name']) : '' ?></div>
    </div>
    <div class="flex" style="gap:var(--sp-2)">
        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/walk-in-sales/' . $sale['id'] . '/receipt')) ?>" target="_blank">Receipt</a>
        <a class="btn btn-outline btn-sm" href="<?= e(url('invent/walk-in-sales')) ?>">Back</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
    <div class="panel">
        <div class="panel-head"><h2>Items</h2></div>
        <div class="panel-body tight">
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead><tr><th>Product</th><th class="num">Unit price</th><th class="num">Qty</th><th class="num">Line total</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><strong><?= e($item['product_name']) ?></strong><div class="muted" style="font-size:var(--fs-xs)"><?= e($item['product_reference']) ?></div></td>
                                <td class="num"><?= e(money($item['unit_price'])) ?></td>
                                <td class="num"><?= (int) $item['quantity'] ?></td>
                                <td class="num"><?= e(money($item['line_total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <aside class="panel">
        <div class="panel-head"><h2>Summary</h2></div>
        <div class="panel-body">
            <div class="summary-line"><span>Total</span><span><?= e(money($sale['total'])) ?></span></div>
            <div class="summary-line"><span>Amount paid</span><span><?= e(money($sale['amount_paid'])) ?></span></div>
            <div class="summary-line summary-total"><span>Balance</span><span><?= e(money($sale['balance'])) ?></span></div>
            <div style="margin-top:var(--sp-4)">
                <span class="pill pill-<?= e((string) $sale['payment_status']) ?>"><?= e(ucfirst((string) $sale['payment_status'])) ?></span>
            </div>
            <?php if ($sale['customer_name'] || $sale['customer_phone']): ?>
                <hr>
                <ul class="spec-list">
                    <?php if ($sale['customer_name']): ?><li><span class="k">Customer</span><span class="v"><?= e($sale['customer_name']) ?></span></li><?php endif; ?>
                    <?php if ($sale['customer_phone']): ?><li><span class="k">Phone</span><span class="v"><?= e($sale['customer_phone']) ?></span></li><?php endif; ?>
                </ul>
            <?php endif; ?>
            <?php if ($sale['note']): ?><div class="alert alert-info" style="margin-bottom:0;margin-top:var(--sp-4)"><span><?= e($sale['note']) ?></span></div><?php endif; ?>
        </div>
    </aside>
</div>
