<?php
/**
 * Generic admin notification email.
 *
 * @var string $heading
 * @var array<string,string> $lines
 * @var string $body
 */
use App\Core\Settings;
?>
<div style="font-family:Arial,Helvetica,sans-serif;color:#1B2430;line-height:1.6;max-width:600px;margin:0 auto">
    <div style="background:#101820;color:#fff;padding:16px 24px">
        <h1 style="margin:0;font-size:18px"><?= e($heading) ?></h1>
    </div>
    <div style="padding:24px">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
            <?php foreach ($lines as $label => $value): ?>
                <tr>
                    <td style="padding:6px 0;color:#5B6879;width:38%"><?= e((string) $label) ?></td>
                    <td style="padding:6px 0;font-weight:600"><?= e((string) $value) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($body !== ''): ?>
            <div style="margin-top:16px;padding:16px;background:#F7F8FA;border-radius:6px;font-size:14px">
                <?= nl2br(e($body)) ?>
            </div>
        <?php endif; ?>
        <p style="margin-top:20px;font-size:12px;color:#5B6879">
            Sent automatically by the <?= e(Settings::get('company_name', 'Armstrong Homes & Interior Limited')) ?> website.
        </p>
    </div>
</div>
