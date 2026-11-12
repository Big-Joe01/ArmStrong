<?php
/**
 * Guest checkout order form.
 *
 * @var array $product
 * @var string|null $mainImage
 * @var list<array> $deliveryOptions
 * @var array $prefill
 */
use App\Core\Config;
use App\Core\Settings;
use App\Core\Session;
use App\Services\MediaService;

$errors    = Session::pullFlash('errors', []);
$available = (int) $product['stock_on_hand'] - (int) $product['reserved_qty'];
$maxQty    = max(1, $available);
$currency  = (string) Config::get('currency_symbol', '₦');
$sizes     = json_decode((string) ($product['sizes'] ?? '[]'), true) ?: [];
$colors    = json_decode((string) ($product['colors'] ?? '[]'), true) ?: [];
$qty       = (int) ($prefill['quantity'] ?? 1);
?>
<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= e(url('/')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/building-materials')) ?>">Building Materials</a><span>/</span>
        <a href="<?= e(url('/products/' . $product['slug'])) ?>"><?= e($product['name']) ?></a><span>/</span>
        <span>Place an order</span>
    </nav>
</div>

<section class="section-tight">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Guest checkout</span>
            <h1>Place your order</h1>
            <p class="lead">No account is required. Complete the form below and we will reserve your items for <?= (int) Config::get('reservation_days', 7) ?> days.</p>
        </div>

        <?php if ($available <= 0): ?>
            <div class="alert alert-warning">
                This product is currently out of stock. Please contact us for availability.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/order')) ?>" data-order-form data-currency="<?= e($currency) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

            <div class="grid" style="grid-template-columns:1.5fr 1fr;gap:var(--sp-8);align-items:start">
                <div>
                    <!-- Customer details -->
                    <fieldset class="fieldset">
                        <legend>Your details</legend>
                        <div class="form-group">
                            <label class="form-label" for="customer_name">Full name <span class="req">*</span></label>
                            <input class="form-control" type="text" id="customer_name" name="customer_name" required maxlength="150" value="<?= e(old('customer_name')) ?>">
                            <?php if (isset($errors['customer_name'])): ?><div class="form-error"><?= e($errors['customer_name']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone number <span class="req">*</span></label>
                                <input class="form-control" type="tel" id="phone" name="phone" required maxlength="40" value="<?= e(old('phone')) ?>">
                                <div class="form-hint">We use this to confirm your order.</div>
                                <?php if (isset($errors['phone'])): ?><div class="form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email address</label>
                                <input class="form-control" type="email" id="email" name="email" maxlength="190" value="<?= e(old('email')) ?>">
                                <div class="form-hint">Optional — for order confirmation by email.</div>
                                <?php if (isset($errors['email'])): ?><div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Product options -->
                    <fieldset class="fieldset">
                        <legend>Product options</legend>

                        <?php if ($sizes): ?>
                            <div class="form-group">
                                <span class="form-label">Size</span>
                                <div class="chip-row" data-chip-group="size">
                                    <?php foreach ($sizes as $size): ?>
                                        <button type="button" class="chip <?= (string) $prefill['size'] === (string) $size ? 'is-selected' : '' ?>" data-value="<?= e((string) $size) ?>"><?= e((string) $size) ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="size" value="<?= e((string) $prefill['size']) ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($colors): ?>
                            <div class="form-group">
                                <span class="form-label">Colour</span>
                                <div class="chip-row" data-chip-group="color">
                                    <?php foreach ($colors as $color): ?>
                                        <button type="button" class="chip <?= (string) $prefill['color'] === (string) $color ? 'is-selected' : '' ?>" data-value="<?= e((string) $color) ?>"><?= e((string) $color) ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="color" value="<?= e((string) $prefill['color']) ?>">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="quantity">Quantity <span class="req">*</span></label>
                            <div class="flex items-center" data-qty style="gap:var(--sp-2);max-width:220px">
                                <button type="button" class="btn btn-outline btn-sm" data-qty-btn="down" aria-label="Decrease quantity">−</button>
                                <input class="form-control" type="number" id="quantity" name="quantity" min="1" max="<?= $maxQty ?>" value="<?= $qty ?>" required style="text-align:center">
                                <button type="button" class="btn btn-outline btn-sm" data-qty-btn="up" aria-label="Increase quantity">+</button>
                            </div>
                            <div class="form-hint"><?= $available ?> unit<?= $available === 1 ? '' : 's' ?> available (per <?= e($product['unit']) ?>).</div>
                            <?php if (isset($errors['quantity'])): ?><div class="form-error"><?= e($errors['quantity']) ?></div><?php endif; ?>
                        </div>
                    </fieldset>

                    <!-- Fulfilment -->
                    <fieldset class="fieldset">
                        <legend>Fulfilment</legend>
                        <div class="grid grid-2" style="gap:var(--sp-3)">
                            <label class="option-card is-selected">
                                <span class="form-check">
                                    <input type="radio" name="fulfilment_method" value="collection" <?= (old('fulfilment_method') !== 'delivery') ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Store collection</strong>
                                        <span class="muted" style="display:block;font-size:var(--fs-xs)">No delivery charge</span>
                                    </span>
                                </span>
                            </label>
                            <label class="option-card">
                                <span class="form-check">
                                    <input type="radio" name="fulfilment_method" value="delivery" <?= (old('fulfilment_method') === 'delivery') ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Delivery</strong>
                                        <span class="muted" style="display:block;font-size:var(--fs-xs)">Fee depends on timeframe</span>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div data-delivery-block class="hide" style="margin-top:var(--sp-5)">
                            <?php if ($deliveryOptions): ?>
                                <div class="form-group">
                                    <span class="form-label">Delivery option <span class="req">*</span></span>
                                    <div class="grid" style="gap:var(--sp-3)">
                                        <?php foreach ($deliveryOptions as $index => $option): ?>
                                            <label class="option-card" data-delivery-option>
                                                <span class="form-check">
                                                    <input type="radio" name="delivery_rate_id" value="<?= (int) $option['id'] ?>"
                                                           data-fee="<?= e(number_format((float) $option['fee'], 2, '.', '')) ?>"
                                                           <?= $index === 0 ? 'checked' : '' ?>>
                                                    <span class="flex-between" style="width:100%">
                                                        <span>
                                                            <strong><?= e($option['name']) ?></strong>
                                                            <?php if ($option['timeframe']): ?>
                                                                <span class="muted" style="display:block;font-size:var(--fs-xs)"><?= e($option['timeframe']) ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="card-price" style="font-size:var(--fs-base)"><?= e(money($option['fee'])) ?></span>
                                                    </span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (isset($errors['delivery_rate_id'])): ?><div class="form-error"><?= e($errors['delivery_rate_id']) ?></div><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No delivery options are currently available. Please choose store collection or contact us.</div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label" for="delivery_address">Delivery address <span class="req">*</span></label>
                                <input class="form-control" type="text" id="delivery_address" name="delivery_address" maxlength="500" value="<?= e(old('delivery_address')) ?>">
                                <?php if (isset($errors['delivery_address'])): ?><div class="form-error"><?= e($errors['delivery_address']) ?></div><?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="delivery_city">City / town <span class="req">*</span></label>
                                    <input class="form-control" type="text" id="delivery_city" name="delivery_city" maxlength="120" value="<?= e(old('delivery_city')) ?>">
                                    <?php if (isset($errors['delivery_city'])): ?><div class="form-error"><?= e($errors['delivery_city']) ?></div><?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="delivery_state">State <span class="req">*</span></label>
                                    <input class="form-control" type="text" id="delivery_state" name="delivery_state" maxlength="120" value="<?= e(old('delivery_state')) ?>">
                                    <?php if (isset($errors['delivery_state'])): ?><div class="form-error"><?= e($errors['delivery_state']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend>Additional information</legend>
                        <div class="form-group mb-0">
                            <label class="form-label" for="note">Order notes or instructions</label>
                            <textarea class="form-control" id="note" name="note" maxlength="1000" placeholder="Any specific requirements, preferred collection time, or additional details."></textarea>
                        </div>
                    </fieldset>
                </div>

                <!-- Summary -->
                <aside>
                    <div class="summary-box" style="position:sticky;top:96px">
                        <h3 style="font-size:var(--fs-md)">Order summary</h3>
                        <div class="order-line">
                            <img src="<?= e(MediaService::productImageUrl($mainImage, 160)) ?>" alt="<?= e($product['name']) ?>" width="72" height="72">
                            <div class="order-line-info">
                                <h4><?= e($product['name']) ?></h4>
                                <div class="muted" style="font-size:var(--fs-xs)">Ref: <?= e($product['reference']) ?></div>
                                <div class="muted" style="font-size:var(--fs-xs)"><?= e(money($product['price'])) ?> per <?= e($product['unit']) ?></div>
                            </div>
                        </div>

                        <div class="summary-line" style="margin-top:var(--sp-4)">
                            <span>Subtotal</span>
                            <span data-subtotal data-subtotal="<?= e(number_format((float) $product['price'] * $qty, 2, '.', '')) ?>"><?= e(money((float) $product['price'] * $qty)) ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Delivery fee</span>
                            <span data-summary-fee><?= e(money(0)) ?></span>
                        </div>
                        <div class="summary-line summary-total">
                            <span>Total</span>
                            <span data-summary-total><?= e(money((float) $product['price'] * $qty)) ?></span>
                        </div>

                        <button class="btn btn-primary btn-lg btn-block" type="submit" style="margin-top:var(--sp-5)" <?= $available <= 0 ? 'disabled' : '' ?>>
                            Submit order
                        </button>

                        <div class="alert alert-info" style="margin-top:var(--sp-5);margin-bottom:0">
                            <div>
                                <strong>Payment is in person.</strong>
                                We do not take card payments online. Once you submit, your items are reserved for
                                <?= (int) Config::get('reservation_days', 7) ?> days while you complete payment and arrange collection or delivery.
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</section>
