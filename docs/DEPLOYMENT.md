# cPanel Deployment Guide

This guide deploys the platform to a standard cPanel PHP hosting account. The
application is plain PHP/MySQL — no Node.js build step and no long-running
process are required.

---

## 1. Upload the files

**Option A — File Manager**

1. Zip the project locally (exclude `config/config.php`, `storage/`, and `.git`).
2. In cPanel → **File Manager**, open `public_html` (or your add-on domain's
   document root).
3. Upload the zip and use **Extract**.

**Option B — Git**

```bash
cd ~/public_html
git clone https://github.com/OWNER/Armstrong.git .
```

The document root must contain `index.php` and `.htaccess`.

> If you deploy into a subdirectory (e.g. `public_html/shop`), set `base_url`
> accordingly in `config/config.php`.

---

## 2. Create the database

1. cPanel → **MySQL Databases** → create a database, e.g. `myuser_armstrong`.
2. Create a MySQL user and a strong password.
3. Add the user to the database with **All Privileges**.

Note the cPanel-prefixed names — shared hosting prefixes both the database and
user with your account name.

---

## 3. Configure the application

Copy the example config and edit it in File Manager:

```bash
cp config/config.example.php config/config.php
```

Set:

| Setting | Value |
|---------|-------|
| `base_url` | `https://yourdomain.com` |
| `db.host` | usually `localhost` |
| `db.name` | `myuser_armstrong` |
| `db.user` | `myuser_armstrong` |
| `db.password` | the database password |
| `app_key` | a 64-character random hex string |
| `cloudinary.*` | Cloudinary credentials |
| `mail.*` | SMTP credentials, or `enabled => false` |
| `debug` | `false` in production |

Generate `app_key` from cPanel → **Terminal**:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Permissions: directories `755`, files `644`. `config/config.php` and the
`storage/` directory should be writable by PHP but never publicly readable —
the bundled `.htaccess` already blocks them.

---

## 4. Run the installer

From cPanel → **Terminal** (or SSH):

```bash
cd ~/public_html
php scripts/install.php --admin-user=admin --admin-pass='a-strong-password'
```

Add `--seed-demo` only if you want sample data. Once the installer completes,
sign in at `https://yourdomain.com/invent/login` and change the password.

If Terminal is unavailable, run the installer once via a temporary browser
script, then delete it immediately. Prefer the CLI method.

---

## 5. Cloudinary

1. Create an account at <https://cloudinary.com>.
2. Copy the Cloud name, API Key and API Secret from the Console.
3. Add them to `config/config.php`.
4. Test by uploading a product image in the admin area.

Uploads without Cloudinary configured are rejected with a clear message; the
rest of the site continues to work.

---

## 6. Cron jobs

The reservation expiry job releases stock when a customer does not pay and
collect within seven days. It must run on a schedule so it works even with no
site traffic.

cPanel → **Cron Jobs** → add a new job:

| Field | Value |
|-------|-------|
| Minute | `0` |
| Hour | `*` |
| Day / Month / Weekday | `*` |
| Command | `/usr/local/bin/php /home/USER/public_html/cron/expire_reservations.php >> /home/USER/logs/armstrong-cron.log 2>&1` |

Replace `USER` with your cPanel username and adjust the PHP path. Find the
correct PHP binary with `which php` in Terminal (on many hosts it is
`/usr/local/bin/php` or `/opt/cpanel/ea-php82/root/usr/bin/php`).

Test it once from Terminal:

```bash
php ~/public_html/cron/expire_reservations.php
```

Expected output:

```
[2025-01-01 00:00:00] reservation expiry: 0 order(s) expired, 0 reservation(s) released
```

The job is idempotent — running it twice never releases the same stock twice.

> **No CLI cron?** If your host cannot run PHP from cron, the script also
> accepts an HTTP trigger: `https://yourdomain.com/cron/expire_reservations.php?token=YOUR_APP_KEY`.
> Configure a cron job that calls this URL (cPanel's "cron via URL" or an
> external cron service). The token is your `app_key` — keep it secret.

---

## 7. HTTPS

1. cPanel → **SSL/TLS Status** → run AutoSSL, or install a certificate.
2. Uncomment the HTTPS redirect block in `.htaccess`:

   ```apache
   RewriteCond %{HTTPS} !=on
   RewriteCond %{HTTP:X-Forwarded-Proto} !https
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. Uncomment the HSTS header in `.htaccess` once HTTPS is confirmed working.
4. Set `base_url` to the `https://` URL.

---

## 8. Email

Configure SMTP under `mail.*` in `config/config.php`. cPanel → **Email Accounts**
gives you SMTP host (`mail.yourdomain.com`), port (`587` with TLS), username and
password.

If email is not configured, set `mail.enabled` to `false`. Orders and enquiries
are still recorded; notifications are written to the activity log instead. A
failed email never invalidates a recorded order.

---

## 9. Post-deployment checks

| Check | How |
|-------|-----|
| Homepage loads over HTTPS | Visit the site |
| Admin login works | `/invent/login` |
| Sensitive paths blocked | `/config/config.php` and `/app/` must return 403/404 |
| Product image uploads | Add an image to a product |
| Test order reserves stock | Place an order, check Inventory |
| Order appears in admin | Admin → Orders |
| Cron runs | Check `logs/armstrong-cron.log` after an hour |
| Sitemap reachable | `/sitemap.xml` |
| `debug` is `false` | Inspect `config/config.php` |

---

## 10. Backups

Back up regularly:

- **Database:** cPanel → **phpMyAdmin** → Export, or
  `mysqldump -u USER -p myuser_armstrong > backup.sql`
- **Files:** the project directory minus `storage/cache`
- **Images:** Cloudinary holds the binaries; the database holds the URLs, so
  database backups preserve image references

---

## 11. Updating

```bash
cd ~/public_html
git pull            # or upload changed files
```

After an update that changes the schema, apply the new statements from
`database/schema.sql` or a migration you prepared. Do not re-run
`scripts/install.php` on a live database — it refuses to overwrite.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| 500 error on every page | `config/config.php` missing or malformed | Recreate from the example |
| "Database connection failed" | Wrong credentials or host | Check `db.*`; try host `127.0.0.1` |
| Admin login loops | `base_url` mismatch or session path | Set `base_url` to the exact URL |
| Images fail to upload | Cloudinary not configured | Add credentials |
| Reservations never expire | Cron not running | Test the script manually, check the log |
| Pretty URLs 404 | `mod_rewrite` off | Ask the host to enable it |
