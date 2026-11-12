# Armstrong Homes & Interior Limited — Business Platform

A production-ready PHP/MySQL platform for a Nigerian real estate and building
materials business. It combines a public corporate website, a real estate
listings module, a product catalogue with guest ordering, a seven-day stock
reservation system, and a secure administration dashboard.

No Node.js, no continuously running server process, and no online payment
gateway are required. Upload the files to a cPanel account, configure the
database, and the site runs.

---

## Business divisions

| Division | Public URL | What it does |
|----------|------------|--------------|
| Real Estate | `/real-estate` | Property listings for sale, rent and lease with enquiry forms |
| Building Materials & Interior | `/building-materials` | Product catalogue with filtering, search and guest ordering |

Both divisions share one corporate identity and one administration area.

---

## Technology

- **PHP 8.2+** (tested on PHP 8.x), PDO with prepared statements
- **MySQL 8 / MariaDB 10.4+**
- **HTML5, CSS3, vanilla JavaScript** (no build step)
- **Apache** with `.htaccess` (mod_rewrite)
- **Cloudinary** for image storage (URLs only are stored in the database)
- Custom lightweight MVC architecture under `app/`

---

## Requirements

- PHP 8.2 or newer with the `pdo_mysql`, `curl`, `mbstring`, `json` extensions
- MySQL 8 or MariaDB 10.4+
- A Cloudinary account (for image uploads)
- Optional: SMTP credentials for email notifications
- Cron access (for automatic reservation expiry)

---

## Quick start (local development)

```bash
# 1. Create the database and a user
mysql -u root -e "CREATE DATABASE armstrong CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER 'armstrong'@'localhost' IDENTIFIED BY 'yourpassword';"
mysql -u root -e "GRANT ALL PRIVILEGES ON armstrong.* TO 'armstrong'@'localhost';"

# 2. Configure the application
cp config/config.example.php config/config.php
#   then edit config/config.php with your database, Cloudinary and mail settings

# 3. Install: create schema, seed defaults, create the administrator
php scripts/install.php --admin-user=admin --admin-pass='a-strong-password'

#    Add sample categories, products and properties with --seed-demo

# 4. Run the development server
php -S localhost:8080
```

Open <http://localhost:8080>. The administration area is at
<http://localhost:8080/invent>.

---

## Documentation

| Document | Purpose |
|----------|---------|
| [`docs/INSTALL.md`](docs/INSTALL.md) | Full installation and first-run setup |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | cPanel deployment, cron jobs, Cloudinary, SMTP |
| [`docs/DATABASE.md`](docs/DATABASE.md) | Database schema reference and design notes |
| [`docs/TESTING.md`](docs/TESTING.md) | Functional, security and inventory-integrity test plan |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Application architecture and request lifecycle |

---

## Project layout

```
app/
  Core/            Framework primitives (Router, Request, Response, Database,
                   View, Session, Csrf, Auth, Validator, Settings, Cloudinary…)
  Controllers/     Public controllers
  Controllers/Admin/  Administration controllers
  Models/          Product, Property data access
  Services/        InventoryService, OrderService, PaymentService,
                   WalkInSaleService, MediaService
  Views/           PHP templates (public, admin, emails, layouts, partials)
  routes/          Route tables (public.php, admin.php)
assets/            CSS, JavaScript, images
config/            config.example.php (config.php is git-ignored)
cron/              expire_reservations.php (scheduled job)
database/          schema.sql
docs/              Documentation
invent/            Admin front controller
scripts/           install.php, seed_demo.php, test_concurrency.php
storage/           Logs and cache (not web-accessible)
index.php          Public front controller
.htaccess          Apache routing and security rules
```

---

## Key features

**Public website**
- Responsive corporate homepage with database-driven featured content
- About, Contact and SEO pages (sitemap, robots.txt, Open Graph, structured data)
- Real estate listings with search, filtering and per-property enquiry forms
- Product catalogue with category, brand, price and availability filters
- Product detail pages with options (size, colour) and related products
- Guest ordering — no customer account required

**Ordering & inventory**
- Quantity-based inventory with an append-only movement ledger
- Seven-day stock reservation created atomically with each order
- Automatic, idempotent expiry that releases stock back to availability
- Configurable delivery options and fees (store collection = ₦0)
- Manual payment recording (full or partial) with duplicate protection

**Administration (`/invent`)**
- Single, securely authenticated administrator account
- Dashboard with live business statistics
- Products, categories, brands, inventory and image management
- Orders, payments, walk-in sales and delivery settings
- Properties, property categories and enquiry management
- Reviews moderation and contact message handling
- Editable website content and settings
- Activity log of administrator actions

---

## Security

- PDO prepared statements throughout; no string-concatenated SQL
- CSRF tokens on every state-changing form
- Output escaping via a global `e()` helper
- `password_hash()` / `password_verify()` for the administrator password
- Login rate limiting plus account lockout
- Secure session cookies with regeneration on login and inactivity timeout
- Markup stripped from admin content fields
- Sensitive directories (`app`, `config`, `database`, `cron`, `storage`) blocked
  from direct web access
- Credentials kept in `config/config.php` (git-ignored), never in the repository

---

## Scheduled jobs

The reservation expiry job must run on a schedule so reservations lapse even
when nobody visits the site:

```
0 * * * * /usr/local/bin/php /home/USER/public_html/cron/expire_reservations.php >> /home/USER/logs/armstrong-cron.log 2>&1
```

See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for the full cPanel instructions.

---

## Licence

Private project. © Armstrong Homes & Interior Limited.
