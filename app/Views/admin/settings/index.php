<?php
/**
 * Admin: site settings.
 *
 * @var array $groups
 * @var array $values
 * @var array $environment
 */
?>
<div class="grid" style="grid-template-columns:2fr 1fr;gap:var(--sp-5);align-items:start">
    <div>
        <form method="post" action="<?= e(url('invent/settings')) ?>">
            <?= csrf_field() ?>
            <?php foreach ($groups as $groupName => $keys): ?>
                <fieldset class="fieldset">
                    <legend><?= e(ucfirst($groupName)) ?></legend>
                    <?php foreach ($keys as $key): ?>
                        <?php
                        $isBool = in_array($key, ['allow_partial_payment', 'enable_reviews', 'enable_delivery'], true);
                        $isLong = in_array($key, ['contact_address'], true);
                        ?>
                        <div class="form-group">
                            <label class="form-label" for="set-<?= e($key) ?>"><?= e(ucwords(str_replace('_', ' ', $key))) ?></label>
                            <?php if ($isBool): ?>
                                <select class="form-control" id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]">
                                    <option value="1" <?= ($values[$key] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($values[$key] ?? '') !== '1' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            <?php elseif ($isLong): ?>
                                <textarea class="form-control" id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" rows="2"><?= e($values[$key] ?? '') ?></textarea>
                            <?php else: ?>
                                <input class="form-control" type="text" id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" value="<?= e($values[$key] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <button class="btn btn-primary btn-lg" type="submit">Save settings</button>
        </form>
    </div>

    <aside>
        <div class="panel">
            <div class="panel-head"><h2>Environment</h2></div>
            <div class="panel-body">
                <ul class="spec-list">
                    <li><span class="k">PHP version</span><span class="v"><?= e($environment['php_version']) ?></span></li>
                    <li><span class="k">Environment</span><span class="v"><?= e($environment['app_env']) ?></span></li>
                    <li><span class="k">Base URL</span><span class="v"><?= e($environment['base_url']) ?></span></li>
                    <li><span class="k">Cloudinary</span><span class="v"><?= e($environment['cloudinary']) ?></span></li>
                    <li><span class="k">Email (SMTP)</span><span class="v"><?= e($environment['smtp']) ?></span></li>
                    <li><span class="k">Reservation period</span><span class="v"><?= (int) $environment['reservation_days'] ?> days</span></li>
                </ul>
                <p class="form-hint" style="margin-top:var(--sp-4)">
                    Database, Cloudinary and mail credentials are read from <code>config/config.php</code>
                    and cannot be changed here. See the deployment guide for details.
                </p>
            </div>
        </div>
    </aside>
</div>
