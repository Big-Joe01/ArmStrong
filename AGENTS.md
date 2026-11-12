# AGENTS.md — repository notes

Armstrong Homes & Interior Limited: PHP 8.2+/MySQL platform (public site,
real estate, product catalogue, guest ordering, 7-day stock reservations,
admin dashboard). No Node.js, no build step, no payment gateway.

## Layout

- `index.php` — public front controller; `invent/index.php` — admin front controller
- `app/` — Core (framework), Controllers (+ `Admin/`), Models, Services, Views, `routes/`
- `assets/` — CSS/JS/SVG (design tokens live in `assets/css/app.css` `:root`)
- `config/config.example.php` — committed; `config/config.php` is git-ignored
- `database/schema.sql` — full schema; `scripts/install.php` — installer + demo seed
- `cron/expire_reservations.php` — scheduled 7-day reservation expiry
- `docs/` — ARCHITECTURE, DATABASE, DEPLOYMENT, INSTALL, TESTING

## Design system

Palette carried over from the original React project: primary blue `#507EEF`,
near-black `#1E1B1C`, gold accent `#F2CE2C`, light grey `#EDEDED`. Typography is
a Graphik-style neutral grotesque (Inter via Google Fonts). Buttons are
pill-shaped with uppercase tracking. See README "Design system".

## Local development

```bash
sudo apt-get install -y php-cli php-mysql php-curl php-mbstring php-xml php-gd \
    mariadb-server
sudo mariadbd-safe --user=mysql &
sudo mysql -e "CREATE DATABASE armstrong CHARACTER SET utf8mb4; \
  CREATE USER 'armstrong'@'127.0.0.1' IDENTIFIED BY 'devpass'; \
  GRANT ALL ON armstrong.* TO 'armstrong'@'127.0.0.1'; FLUSH PRIVILEGES;"
mysql -uarmstrong -pdevpass -h127.0.0.1 armstrong < database/schema.sql
php scripts/install.php --admin-user=admin --admin-pass='<pw>' --seed-demo
php -S 0.0.0.0:12000 scripts/dev-router.php   # dev-router emulates .htaccess
```

`config/config.php` may set `'base_url' => 'auto'`; the base URL is then derived
from the request (works behind a proxy / in a sub-directory). A concrete URL in
`base_url` always wins.

## Verification

- `php -l` across all files
- `php scripts/test_concurrency.php` — two buyers, last unit: exactly one wins
- Public routes and all `/invent/*` admin routes should return 200

Note: order/enquiry/contact/review field names are `full_name`, `phone`,
`email`, `message`, `customer_name`, `body`, `rating`, `product_id` — check the
controllers rather than guessing.
