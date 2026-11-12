<?php
/**
 * Contact page.
 */
use App\Core\Settings;
use App\Core\Session;

$errors = Session::pullFlash('errors', []);
$phone  = Settings::get('contact_phone');
$email  = Settings::get('contact_email');
$address = Settings::get('contact_address');
$hours  = Settings::get('business_hours');
?>
<section class="section section-ink" style="padding-block:var(--sp-8)">
    <div class="container">
        <span class="eyebrow">Contact</span>
        <h1>Get in touch</h1>
        <p style="color:#C9D4E1;max-width:60ch;margin-bottom:0">
            Send us a message about a product, a property, an order or anything else. We respond to enquiries as promptly as we can.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid" style="grid-template-columns:1.4fr 1fr;gap:var(--sp-8)">
            <div>
                <h2>Send a message</h2>
                <form method="post" action="<?= e(url('/contact')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full name <span class="req">*</span></label>
                        <input class="form-control" type="text" id="full_name" name="full_name" required maxlength="150" value="<?= e(old('full_name')) ?>">
                        <?php if (isset($errors['full_name'])): ?><div class="form-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">Email address <span class="req">*</span></label>
                            <input class="form-control" type="email" id="email" name="email" required maxlength="190" value="<?= e(old('email')) ?>">
                            <?php if (isset($errors['email'])): ?><div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone number</label>
                            <input class="form-control" type="tel" id="phone" name="phone" maxlength="40" value="<?= e(old('phone')) ?>">
                            <?php if (isset($errors['phone'])): ?><div class="form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="subject">Subject <span class="req">*</span></label>
                        <input class="form-control" type="text" id="subject" name="subject" required maxlength="200" value="<?= e(old('subject')) ?>">
                        <?php if (isset($errors['subject'])): ?><div class="form-error"><?= e($errors['subject']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message">Message <span class="req">*</span></label>
                        <textarea class="form-control" id="message" name="message" required minlength="10" maxlength="5000"><?= e(old('message')) ?></textarea>
                        <div class="form-hint">Please include the product or property reference where relevant.</div>
                        <?php if (isset($errors['message'])): ?><div class="form-error"><?= e($errors['message']) ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit">Send message</button>
                </form>
            </div>

            <aside>
                <div class="value-card">
                    <h3 style="font-size:var(--fs-md)">Contact details</h3>
                    <ul class="spec-list">
                        <?php if ($phone): ?>
                            <li><span class="k">Phone</span><span class="v"><a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a></span></li>
                        <?php endif; ?>
                        <?php if ($email): ?>
                            <li><span class="k">Email</span><span class="v"><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span></li>
                        <?php endif; ?>
                        <?php if ($address): ?>
                            <li><span class="k">Address</span><span class="v"><?= e($address) ?></span></li>
                        <?php endif; ?>
                        <?php if ($hours): ?>
                            <li><span class="k">Opening hours</span><span class="v"><?= e($hours) ?></span></li>
                        <?php endif; ?>
                    </ul>
                    <?php if (!$phone && !$email && !$address): ?>
                        <p class="muted" style="font-size:var(--fs-sm);margin-bottom:0">
                            Contact details will appear here once they are configured in the administration area.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="value-card mt-6">
                    <h3 style="font-size:var(--fs-md)">Looking for something specific?</h3>
                    <p class="muted" style="font-size:var(--fs-sm)">Browse the division you need and enquire directly from a product or property page.</p>
                    <div style="display:grid;gap:var(--sp-3)">
                        <a class="btn btn-outline btn-block" href="<?= e(url('/real-estate')) ?>">Browse properties</a>
                        <a class="btn btn-outline btn-block" href="<?= e(url('/building-materials')) ?>">Browse products</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
