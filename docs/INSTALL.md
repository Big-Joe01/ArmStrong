# Installation Guide

This guide covers installing the platform on a local machine and on a
production server. For cPanel-specific steps see [`DEPLOYMENT.md`](DEPLOYMENT.md).

---

## 1. Requirements

| Component | Minimum |
|-----------|---------|
| PHP | 8.2 |
| MySQL | 8.0 |
| MariaDB | 10.4 |
| Extensions | `pdo_mysql`, `curl`, `mbstring`, `json`, `openssl` |

Confirm the PHP extensions are available:

```bash
php -m | grep -E 'pdo_mysql|curl|mbstring|json|openssl'
```

---

## 2. Create the database

```sql
CREATE DATABASE armstrong CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'armstrong'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
GRANT ALL PRIVILEGES ON armstrong.* TO 'armstrong'@'localhost';
FLUSH PRIVILEGES;
```

You do **not** need to import `database/schema.sql` by hand — the installer
runs it for you. It is provided for reference and for restoring a database.

---

## 3. Configure the application

```bash
cp config/config.example.php config/config.php
```

Edit `config/config.php` and set:

- `db.host`, `db.port`, `db.name`, `db.user`, `db.password`
- `base_url` — the full site URL, e.g. `https://example.com` (no trailing slash)
- `app_key` — a long random string, used to derive CSRF and cookie secrets
- `cloudinary.cloud_name`, `cloudinary.api_key`, `cloudinary.api_secret`
- `mail.*` — SMTP details (optional; see step 7)

Generate an application key:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

> `config/config.php` is git-ignored. Never commit it or share it.

---

## 4. Run the installer

The installer creates the schema, seeds default settings and delivery options,
creates the single administrator account, and (optionally) loads demo data.

```bash
# Interactive — prompts for the administrator username and password
php scripts/install.php

# Non-interactive
php scripts/install.php --admin-user=admin --admin-pass='a-strong-password'

# Also load sample categories, products and properties
php scripts/install.php --admin-user=admin --admin-pass='a-strong-password' --seed-demo
```

The installer refuses to overwrite an existing installation. To reinstall, drop
and recreate the database first.

### What the installer creates

- All tables from `database/schema.sql`
- One administrator (password hashed with `password_hash()`)
- Default delivery options (same-day ₦10,000, two-day ₦8,000, four-day ₦5,000)
- Default site settings and homepage content sections
- With `--seed-demo`: example product/property categories, products and properties

---

## 5. Verify the installation

```bash
php -S localhost:8080
```

| Check | Expected |
|-------|----------|
| `http://localhost:8080/` | Homepage renders |
| `http://localhost:8080/real-estate` | Property listings |
| `http://localhost:8080/building-materials` | Product catalogue |
| `http://localhost:8080/invent/login` | Administrator login |
| `http://localhost:8080/sitemap.xml` | XML sitemap |

Sign in at `/invent/login` with the administrator credentials from step 4.

---

## 6. Configure Cloudinary

Image uploads require Cloudinary. Without it, uploads are rejected with a clear
message and the rest of the site keeps working.

1. Create a free account at <https://cloudinary.com>.
2. From the Console dashboard copy the **Cloud name**, **API Key** and
   **API Secret**.
3. Add them to `config/config.php` under `cloudinary`.

Only image URLs and public IDs are stored in the database; the image binaries
live on Cloudinary.

---

## 7. Configure email (optional)

Order confirmations and administrator notifications are sent by email when SMTP
is configured. Email failure never blocks an order — the order is still stored.

Set `mail.host`, `mail.port`, `mail.username`, `mail.password`, `mail.encryption`
and `mail.from_address` in `config/config.php`. To leave email off, set
`mail.enabled` to `false`; notifications are then written to the activity log.

---

## 8. Schedule reservation expiry

Reservations expire automatically seven days after an order is placed. This
must run on a schedule so stock is released even with no site traffic. See
[`DEPLOYMENT.md`](DEPLOYMENT.md#cron-jobs) for the cPanel cron command.

Test the job manually:

```bash
php cron/expire_reservations.php
```

The job is idempotent — running it repeatedly never releases the same stock twice.

---

## 9. First-run checklist

- [ ] `config/config.php` exists and is not web-accessible
- [ ] Administrator can sign in at `/invent/login`
- [ ] Administrator password changed from the installer value
- [ ] Cloudinary credentials added and a test image uploads
- [ ] At least one product category and one product published
- [ ] At least one property published
- [ ] A test order can be placed and appears under Orders
- [ ] Cron job scheduled and `php cron/expire_reservations.php` runs cleanly
- [ ] HTTPS enabled in production
