<div class="login-wrap">
    <div class="login-card">
        <span class="brand" style="margin-bottom:var(--sp-5)">
            <span class="brand-mark" aria-hidden="true">A</span>
            <span class="brand-text">
                <span class="brand-name">Armstrong</span>
                <span class="brand-sub">Administration</span>
            </span>
        </span>

        <h1>Sign in</h1>
        <p class="sub">Enter your administrator credentials to continue.</p>

        <?php $error = flash('error'); $success = flash('success'); ?>
        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><span><?= e($error) ?></span></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" role="status"><span><?= e($success) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('invent/login')) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="username">Username or email</label>
                <input class="form-control" type="text" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary btn-block btn-lg" type="submit">Sign in</button>
        </form>

        <p class="form-hint" style="margin-top:var(--sp-5);text-align:center">
            Authorised access only. All sign-in attempts are recorded.
        </p>
    </div>
</div>
