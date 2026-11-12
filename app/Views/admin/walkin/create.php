<?php
/**
 * Admin: record a walk-in sale.
 *
 * @var list<array> $products
 */
?>
<div class="alert alert-info">
    <span>Walk-in sales deduct stock immediately. Use the search box to filter the product list, then enter quantities for the items sold.</span>
</div>

<form method="post" action="<?= e(url('invent/walk-in-sales')) ?>" data-walkin-form>
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
        <div class="panel">
            <div class="panel-head">
                <h2>Products</h2>
                <input class="form-control" type="search" data-product-search placeholder="Search products…" style="max-width:260px">
            </div>
            <div class="panel-body tight">
                <?php if ($products): ?>
                    <div class="table-wrap" style="border:0;border-radius:0;max-height:640px;overflow-y:auto">
                        <table class="table">
                            <thead><tr><th>Product</th><th class="num">Price</th><th class="num">Available</th><th class="num">Quantity</th><th class="num">Line total</th></tr></thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $avail = (int) $product['stock_on_hand'] - (int) $product['reserved_qty'];
                                    $search = strtolower($product['name'] . ' ' . $product['reference']);
                                    ?>
                                    <tr data-walkin-row data-price="<?= e(number_format((float) $product['price'], 2, '.', '')) ?>" data-search="<?= e($search) ?>">
                                        <td>
                                            <strong><?= e($product['name']) ?></strong>
                                            <div class="muted" style="font-size:var(--fs-xs)"><?= e($product['reference']) ?> · per <?= e($product['unit']) ?></div>
                                        </td>
                                        <td class="num"><?= e(money($product['price'])) ?></td>
                                        <td class="num"><span class="<?= $avail <= 0 ? 'stock-out' : 'stock-in' ?>"><?= $avail ?></span></td>
                                        <td class="num">
                                            <input type="hidden" name="product_ids[]" value="<?= (int) $product['id'] ?>">
                                            <input class="form-control" type="number" name="quantities[]" min="0" max="<?= max(0, $avail) ?>" value="0" style="max-width:90px;text-align:center" <?= $avail <= 0 ? 'disabled' : '' ?>>
                                        </td>
                                        <td class="num" data-line-total>0.00</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="table-empty">No products available to sell.</div>
                <?php endif; ?>
            </div>
        </div>

        <aside>
            <div class="panel">
                <div class="panel-head"><h2>Sale summary</h2></div>
                <div class="panel-body">
                    <div class="summary-line summary-total" style="border-top:0;padding-top:0">
                        <span>Total</span>
                        <span>₦<span data-walkin-total>0.00</span></span>
                    </div>

                    <hr>

                    <div class="form-group"><label class="form-label" for="customer_name">Customer name</label><input class="form-control" type="text" id="customer_name" name="customer_name" maxlength="150" placeholder="Optional"></div>
                    <div class="form-group"><label class="form-label" for="customer_phone">Phone</label><input class="form-control" type="tel" id="customer_phone" name="customer_phone" maxlength="40" placeholder="Optional"></div>
                    <div class="form-group"><label class="form-label" for="amount_paid">Amount paid (₦)</label><input class="form-control" type="number" id="amount_paid" name="amount_paid" min="0" step="0.01" placeholder="Leave blank to mark as fully paid"></div>
                    <div class="form-group">
                        <label class="form-label" for="payment_method">Payment method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="pos">POS</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label" for="payment_reference">Payment reference</label><input class="form-control" type="text" id="payment_reference" name="payment_reference" maxlength="120"></div>
                    <div class="form-group"><label class="form-label" for="note">Note</label><textarea class="form-control" id="note" name="note" rows="2" maxlength="1000"></textarea></div>

                    <button class="btn btn-primary btn-block btn-lg" type="submit" data-confirm="Record this walk-in sale and deduct stock?">Record sale</button>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('invent/walk-in-sales')) ?>">Cancel</a>
                </div>
            </div>
        </aside>
    </div>
</form>
