<?php
/**
 * Admin: website content editor.
 *
 * @var array $sections      section definitions keyed by section_key
 * @var array $sectionData   existing rows keyed by section_key
 * @var array $groups        plain-text field groups
 * @var array $values        current plain-text field values
 */
?>
<div class="alert alert-info">
    <span>Content fields accept plain text only. Markup and scripts are stripped for safety.</span>
</div>

<?php foreach ($sections as $key => $definition): ?>
    <?php $row = $sectionData[$key] ?? []; ?>
    <div class="panel">
        <div class="panel-head">
            <h2><?= e($definition['label']) ?></h2>
            <span class="muted" style="font-size:var(--fs-xs)"><?= e($key) ?></span>
        </div>
        <div class="panel-body">
            <form method="post" action="<?= e(url('invent/content/section/' . $key)) ?>">
                <?= csrf_field() ?>
                <?php foreach ($definition['fields'] as $field): ?>
                    <?php $isLong = in_array($field, ['body'], true); ?>
                    <div class="form-group">
                        <label class="form-label" for="sec-<?= e($key) ?>-<?= e($field) ?>"><?= e(ucwords(str_replace('_', ' ', $field))) ?></label>
                        <?php if ($isLong): ?>
                            <textarea class="form-control" id="sec-<?= e($key) ?>-<?= e($field) ?>" name="<?= e($field) ?>" rows="4"><?= e((string) ($row[$field] ?? '')) ?></textarea>
                        <?php else: ?>
                            <input class="form-control" type="text" id="sec-<?= e($key) ?>-<?= e($field) ?>" name="<?= e($field) ?>" value="<?= e((string) ($row[$field] ?? '')) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="flex-between">
                    <label class="form-check"><input type="checkbox" name="is_active" value="1" <?= (int) ($row['is_active'] ?? 1) === 1 ? 'checked' : '' ?>> <span>Visible on the site</span></label>
                    <button class="btn btn-primary btn-sm" type="submit">Save section</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($groups as $groupName => $keys): ?>
    <div class="panel">
        <div class="panel-head"><h2><?= e($groupName) ?></h2></div>
        <div class="panel-body">
            <?php foreach ($keys as $key): ?>
                <?php $isLong = in_array($key, ['about_intro', 'footer_about', 'footer_note', 'home_meta_description'], true); ?>
                <form method="post" action="<?= e(url('invent/content/' . $key)) ?>" class="content-field">
                    <?= csrf_field() ?>
                    <div class="form-group" style="flex:1;margin-bottom:0">
                        <label class="form-label" for="field-<?= e($key) ?>"><?= e(ucwords(str_replace('_', ' ', $key))) ?></label>
                        <?php if ($isLong): ?>
                            <textarea class="form-control" id="field-<?= e($key) ?>" name="value" rows="3"><?= e($values[$key] ?? '') ?></textarea>
                        <?php else: ?>
                            <input class="form-control" type="text" id="field-<?= e($key) ?>" name="value" value="<?= e($values[$key] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-outline btn-sm" type="submit">Save</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
