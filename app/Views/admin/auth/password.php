<div class="panel" style="max-width:560px">
    <div class="panel-head"><h2>Change password</h2></div>
    <div class="panel-body">
        <?php if (!empty($admin['must_change_password'])): ?>
            <div class="alert alert-warning">
                <span>You are using the initial password. Please set a new password now.</span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('invent/password')) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="current_password">Current password <span class="req">*</span></label>
                <input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New password <span class="req">*</span></label>
                <input class="form-control" type="password" id="password" name="password" required minlength="12" autocomplete="new-password">
                <div class="form-hint">At least 12 characters. Use a passphrase or a mix of letters, numbers and symbols.</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm new password <span class="req">*</span></label>
                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required minlength="12" autocomplete="new-password">
            </div>
            <button class="btn btn-primary" type="submit">Update password</button>
        </form>
    </div>
</div>
