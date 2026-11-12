<?php
/**
 * Admin: property create/edit form.
 *
 * @var array|null $property
 * @var list<array> $images
 * @var list<array> $categories
 * @var string $action
 */
use App\Services\MediaService;

$isEdit = $property !== null;
$features  = $isEdit ? (json_decode((string) ($property['features'] ?? '[]'), true) ?: []) : [];
$amenities = $isEdit ? (json_decode((string) ($property['amenities'] ?? '[]'), true) ?: []) : [];
$specs     = $isEdit ? (json_decode((string) ($property['specifications'] ?? '[]'), true) ?: []) : [];
$val = static function (string $key, string $default = '') use ($property): string {
    if ($property !== null && isset($property[$key]) && $property[$key] !== null) {
        return (string) $property[$key];
    }
    return old($key, $default);
};
?>
<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns:1.6fr 1fr;gap:var(--sp-5);align-items:start">
        <div>
            <fieldset class="fieldset">
                <legend>Property details</legend>
                <div class="form-group">
                    <label class="form-label" for="title">Title <span class="req">*</span></label>
                    <input class="form-control" type="text" id="title" name="title" required maxlength="255" value="<?= e($val('title')) ?>">
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
                        <label class="form-label" for="transaction_type">Transaction type</label>
                        <select class="form-control" id="transaction_type" name="transaction_type">
                            <?php foreach (['for_sale' => 'For sale', 'for_rent' => 'For rent', 'for_lease' => 'For lease', 'other' => 'Other'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $val('transaction_type', 'for_sale') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label class="form-label" for="short_description">Short description</label><input class="form-control" type="text" id="short_description" name="short_description" maxlength="500" value="<?= e($val('short_description')) ?>"></div>
                <div class="form-group"><label class="form-label" for="description">Full description</label><textarea class="form-control" id="description" name="description" rows="8"><?= e($val('description')) ?></textarea></div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Location</legend>
                <div class="form-group"><label class="form-label" for="address">Street address</label><input class="form-control" type="text" id="address" name="address" maxlength="255" value="<?= e($val('address')) ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="neighbourhood">Neighbourhood</label><input class="form-control" type="text" id="neighbourhood" name="neighbourhood" maxlength="150" value="<?= e($val('neighbourhood')) ?>"></div>
                    <div class="form-group"><label class="form-label" for="city">City / town</label><input class="form-control" type="text" id="city" name="city" maxlength="120" value="<?= e($val('city')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="state">State</label><input class="form-control" type="text" id="state" name="state" maxlength="120" value="<?= e($val('state')) ?>"></div>
                    <div class="form-group"><label class="form-label" for="country">Country</label><input class="form-control" type="text" id="country" name="country" maxlength="120" value="<?= e($val('country', 'Nigeria')) ?>"></div>
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Features &amp; amenities</legend>
                <div class="form-group">
                    <label class="form-label" for="features">Features</label>
                    <input class="form-control" type="text" id="features" name="features" value="<?= e(implode(', ', $features)) ?>" placeholder="Borehole, Solar, CCTV, Fenced">
                    <div class="form-hint">Comma-separated.</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amenities">Amenities</label>
                    <input class="form-control" type="text" id="amenities" name="amenities" value="<?= e(implode(', ', $amenities)) ?>" placeholder="Treated water, 24-hour security">
                    <div class="form-hint">Comma-separated.</div>
                </div>
                <label class="form-label">Additional specifications</label>
                <div data-repeater>
                    <div data-repeater-rows>
                        <?php if ($specs): ?>
                            <?php foreach ($specs as $key => $value): ?>
                                <div class="repeat-row" data-repeater-row>
                                    <input class="form-control" type="text" name="spec_keys[]" value="<?= e((string) $key) ?>" placeholder="Label">
                                    <input class="form-control" type="text" name="spec_values[]" value="<?= e((string) $value) ?>" placeholder="Value">
                                    <button type="button" class="btn btn-ghost btn-sm" data-repeater-remove>✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <template>
                        <div class="repeat-row" data-repeater-row>
                            <input class="form-control" type="text" name="spec_keys[]" placeholder="Label">
                            <input class="form-control" type="text" name="spec_values[]" placeholder="Value">
                            <button type="button" class="btn btn-ghost btn-sm" data-repeater-remove>✕</button>
                        </div>
                    </template>
                    <button type="button" class="btn btn-outline btn-sm" data-repeater-add>Add specification</button>
                </div>
            </fieldset>
        </div>

        <div>
            <fieldset class="fieldset">
                <legend>Price &amp; size</legend>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="price">Price (₦)</label><input class="form-control" type="number" id="price" name="price" min="0" step="0.01" value="<?= e($val('price')) ?>"></div>
                    <div class="form-group">
                        <label class="form-label" for="price_frequency">Price frequency</label>
                        <select class="form-control" id="price_frequency" name="price_frequency">
                            <?php foreach (['total' => 'Total', 'per_year' => 'Per year', 'per_month' => 'Per month', 'per_week' => 'Per week', 'per_day' => 'Per day', 'negotiable' => 'Negotiable'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $val('price_frequency', 'total') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label class="form-label" for="property_type">Property type</label><input class="form-control" type="text" id="property_type" name="property_type" maxlength="80" value="<?= e($val('property_type')) ?>" placeholder="e.g. Duplex, Bungalow, Flat"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="bedrooms">Bedrooms</label><input class="form-control" type="number" id="bedrooms" name="bedrooms" min="0" value="<?= e($val('bedrooms')) ?>"></div>
                    <div class="form-group"><label class="form-label" for="bathrooms">Bathrooms</label><input class="form-control" type="number" id="bathrooms" name="bathrooms" min="0" value="<?= e($val('bathrooms')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="toilets">Toilets</label><input class="form-control" type="number" id="toilets" name="toilets" min="0" value="<?= e($val('toilets')) ?>"></div>
                    <div class="form-group"><label class="form-label" for="parking">Parking</label><input class="form-control" type="text" id="parking" name="parking" maxlength="80" value="<?= e($val('parking')) ?>" placeholder="e.g. 2 spaces"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="land_size">Land size</label><input class="form-control" type="text" id="land_size" name="land_size" maxlength="80" value="<?= e($val('land_size')) ?>" placeholder="e.g. 500 sqm"></div>
                    <div class="form-group"><label class="form-label" for="building_size">Building size</label><input class="form-control" type="text" id="building_size" name="building_size" maxlength="80" value="<?= e($val('building_size')) ?>" placeholder="e.g. 250 sqm"></div>
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Status &amp; visibility</legend>
                <div class="form-group">
                    <label class="form-label" for="status">Availability status</label>
                    <select class="form-control" id="status" name="status">
                        <?php foreach (['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold', 'rented' => 'Rented', 'leased' => 'Leased', 'unavailable' => 'Unavailable', 'draft' => 'Draft'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $val('status', 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label class="form-check" style="margin-bottom:var(--sp-3)"><input type="checkbox" name="is_published" value="1" <?= $val('is_published') ? 'checked' : '' ?>> <span>Published (visible on the public site)</span></label>
                <label class="form-check"><input type="checkbox" name="is_featured" value="1" <?= $val('is_featured') ? 'checked' : '' ?>> <span>Featured (can appear on the homepage)</span></label>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Search engine metadata</legend>
                <div class="form-group"><label class="form-label" for="meta_title">SEO title</label><input class="form-control" type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= e($val('meta_title')) ?>"></div>
                <div class="form-group mb-0"><label class="form-label" for="meta_description">SEO description</label><textarea class="form-control" id="meta_description" name="meta_description" maxlength="320" rows="2"><?= e($val('meta_description')) ?></textarea></div>
            </fieldset>

            <div class="panel">
                <div class="panel-body">
                    <button class="btn btn-primary btn-block btn-lg" type="submit"><?= $isEdit ? 'Save changes' : 'Create property' ?></button>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('invent/properties')) ?>" style="margin-top:var(--sp-2)">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <div class="panel mt-6">
        <div class="panel-head"><h2>Property images</h2><span class="muted" style="font-size:var(--fs-xs)">Uploaded to Cloudinary</span></div>
        <div class="panel-body">
            <?php if ($images): ?>
                <div class="image-grid mb-6">
                    <?php foreach ($images as $image): ?>
                        <div class="image-item <?= $image['is_main'] ? 'is-main' : '' ?>">
                            <img src="<?= e(MediaService::propertyImageUrl($image['image_url'], 300)) ?>" alt="">
                            <div class="actions">
                                <?php if ($image['is_main']): ?>
                                    <span class="badge badge-accent">Main</span>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('invent/properties/' . $property['id'] . '/images/' . $image['id'] . '/main')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-ghost btn-sm" type="submit">Set main</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('invent/properties/' . $property['id'] . '/images/' . $image['id'] . '/delete')) ?>">
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

            <form method="post" action="<?= e(url('invent/properties/' . $property['id'] . '/images')) ?>" enctype="multipart/form-data">
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
