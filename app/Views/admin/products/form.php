<?php
/**
 * Admin: product create/edit form.
 *
 * @var array|null $product
 * @var list<array> $images
 * @var list<array> $categories
 * @var list<array> $brands
 * @var string $action
 */
use App\Core\Session;
use App\Services\MediaService;

$isEdit = $product !== null;
$specs  = $isEdit ? (json_decode((string) ($product['specs'] ?? '[]'), true) ?: []) : [];
$colors = $isEdit ? (json_decode((string) ($product['colors'] ?? '[]'), true) ?: []) : [];
$sizes  = $isEdit ? (json_decode((string) ($product['sizes'] ?? '[]'), true) ?: []) : [];
$errors = Session::pullFlash('errors', []);
$val = static function (string $key, string $default = '') use ($product): string {
    if ($product !== null && isset($product[$key]) && $product[$key] !== null) {
        return (string) $product[$key];
    }
    return old($key, $default);
};
?>
<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns:1.6fr 1fr;gap:var(--sp-5);align-items:start">
        <div>
            <fieldset class="fieldset">
                <legend>Product information</legend>
                <div class="form-group">
                    <label class="form-label" for="name">Product name <span class="req">*</span></label>
                    <input class="form-control" type="text" id="name" name="name" required maxlength="255" value="<?= e($val('name')) ?>">
                    <?php if (isset($errors['name'])): ?><div class="form-error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Uncategorised</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (string) $val('category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="brand_id">Brand</label>
                        <select class="form-control" id="brand_id" name="brand_id">
                            <option value="">No brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= (int) $brand['id'] ?>" <?= (string) $val('brand_id') === (string) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="short_description">Short description</label>
                    <input class="form-control" type="text" id="short_description" name="short_description" maxlength="500" value="<?= e($val('short_description')) ?>">
                    <div class="form-hint">Shown on listings and in search results.</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Full description</label>
                    <textarea class="form-control" id="description" name="description" rows="8"><?= e($val('description')) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Specifications</legend>
                <div data-repeater>
                    <div data-repeater-rows>
                        <?php if ($specs): ?>
                            <?php foreach ($specs as $key => $value): ?>
                                <div class="repeat-row" data-repeater-row>
                                    <input class="form-control" type="text" name="spec_keys[]" value="<?= e((string) $key) ?>" placeholder="Label (e.g. Material)">
                                    <input class="form-control" type="text" name="spec_values[]" value="<?= e((string) $value) ?>" placeholder="Value (e.g. Ceramic)">
                                    <button type="button" class="btn btn-ghost btn-sm" data-repeater-remove aria-label="Remove">✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="repeat-row" data-repeater-row>
                                <input class="form-control" type="text" name="spec_keys[]" placeholder="Label (e.g. Material)">
                                <input class="form-control" type="text" name="spec_values[]" placeholder="Value (e.g. Ceramic)">
                                <button type="button" class="btn btn-ghost btn-sm" data-repeater-remove aria-label="Remove">✕</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <template>
                        <div class="repeat-row" data-repeater-row>
                            <input class="form-control" type="text" name="spec_keys[]" placeholder="Label">
                            <input class="form-control" type="text" name="spec_values[]" placeholder="Value">
                            <button type="button" class="btn btn-ghost btn-sm" data-repeater-remove aria-label="Remove">✕</button>
                        </div>
                    </template>
                    <button type="button" class="btn btn-outline btn-sm" data-repeater-add>Add specification</button>
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Options &amp; search engine metadata</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="colors">Available colours</label>
                        <input class="form-control" type="text" id="colors" name="colors" value="<?= e(implode(', ', $colors)) ?>" placeholder="Grey, White, Beige">
                        <div class="form-hint">Comma-separated.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sizes">Available sizes</label>
                        <input class="form-control" type="text" id="sizes" name="sizes" value="<?= e(implode(', ', $sizes)) ?>" placeholder="60x60cm, 30x30cm">
                        <div class="form-hint">Comma-separated.</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="meta_title">SEO title</label>
                    <input class="form-control" type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= e($val('meta_title')) ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="meta_description">SEO description</label>
                    <textarea class="form-control" id="meta_description" name="meta_description" maxlength="320" rows="2"><?= e($val('meta_description')) ?></textarea>
                </div>
            </fieldset>
        </div>

        <div>
            <fieldset class="fieldset">
                <legend>Pricing &amp; stock</legend>
                <div class="form-group">
                    <label class="form-label" for="price">Price (₦) <span class="req">*</span></label>
                    <input class="form-control" type="number" id="price" name="price" min="0" step="0.01" required value="<?= e($val('price', '0.00')) ?>">
                    <?php if (isset($errors['price'])): ?><div class="form-error"><?= e($errors['price']) ?></div><?php endif; ?>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="unit">Unit</label>
                        <select class="form-control" id="unit" name="unit">
                            <?php foreach (['piece' => 'Per piece', 'carton' => 'Per carton', 'box' => 'Per box', 'set' => 'Per set', 'metre' => 'Per metre', 'square_metre' => 'Per square metre', 'bag' => 'Per bag', 'roll' => 'Per roll', 'litre' => 'Per litre', 'bundle' => 'Per bundle', 'pack' => 'Per pack', 'other' => 'Other'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $val('unit', 'piece') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="packaging">Packaging</label>
                        <input class="form-control" type="text" id="packaging" name="packaging" maxlength="120" value="<?= e($val('packaging')) ?>" placeholder="e.g. 4 pieces per carton">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="stock_on_hand">Stock on hand</label>
                    <input class="form-control" type="number" id="stock_on_hand" name="stock_on_hand" min="0" step="1" value="<?= e($val('stock_on_hand', '0')) ?>">
                    <?php if ($isEdit): ?>
                        <div class="form-hint">Currently <?= (int) $product['stock_on_hand'] ?> on hand, <?= (int) $product['reserved_qty'] ?> reserved. Changes are recorded in the inventory ledger.</div>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label" for="low_stock_threshold">Low-stock threshold</label>
                    <input class="form-control" type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" step="1" value="<?= e($val('low_stock_threshold', '5')) ?>">
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Availability &amp; visibility</legend>
                <div class="form-group">
                    <label class="form-label" for="availability">Availability</label>
                    <select class="form-control" id="availability" name="availability">
                        <option value="available" <?= $val('availability', 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="temporarily_unavailable" <?= $val('availability') === 'temporarily_unavailable' ? 'selected' : '' ?>>Temporarily unavailable</option>
                        <option value="out_of_stock" <?= $val('availability') === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
                        <option value="discontinued" <?= $val('availability') === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
                    </select>
                    <div class="form-hint">Unavailable products cannot be ordered, but existing orders stay intact.</div>
                </div>
                <label class="form-check" style="margin-bottom:var(--sp-3)">
                    <input type="checkbox" name="is_published" value="1" <?= $val('is_published') ? 'checked' : '' ?>>
                    <span>Published (visible on the public site)</span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="is_featured" value="1" <?= $val('is_featured') ? 'checked' : '' ?>>
                    <span>Featured (can appear on the homepage)</span>
                </label>
            </fieldset>

            <div class="panel">
                <div class="panel-body">
                    <button class="btn btn-primary btn-block btn-lg" type="submit"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('invent/products')) ?>" style="margin-top:var(--sp-2)">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <div class="panel mt-6">
        <div class="panel-head">
            <h2>Product images</h2>
            <span class="muted" style="font-size:var(--fs-xs)">Uploaded to Cloudinary</span>
        </div>
        <div class="panel-body">
            <?php if ($images): ?>
                <div class="image-grid mb-6">
                    <?php foreach ($images as $image): ?>
                        <div class="image-item <?= $image['is_main'] ? 'is-main' : '' ?>">
                            <img src="<?= e(MediaService::productImageUrl($image['image_url'], 300)) ?>" alt="">
                            <div class="actions">
                                <?php if ($image['is_main']): ?>
                                    <span class="badge badge-accent">Main</span>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('invent/products/' . $product['id'] . '/images/' . $image['id'] . '/main')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-ghost btn-sm" type="submit">Set main</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('invent/products/' . $product['id'] . '/images/' . $image['id'] . '/delete')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Delete this image?" style="color:var(--c-danger)">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">No images uploaded yet. The first image uploaded becomes the main image.</p>
            <?php endif; ?>

            <form method="post" action="<?= e(url('invent/products/' . $product['id'] . '/images')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="images">Add images</label>
                    <input class="form-control" type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    <div class="form-hint">JPEG, PNG, WebP or GIF. Maximum 5&nbsp;MB per image.</div>
                </div>
                <button class="btn btn-primary" type="submit">Upload images</button>
            </form>
        </div>
    </div>
<?php endif; ?>
