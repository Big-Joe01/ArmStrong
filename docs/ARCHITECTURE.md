# Application Architecture

A small custom MVC framework sits under `app/Core`. It is deliberately
lightweight: no Composer runtime dependency, no build step, deployable by
uploading files.

---

## Request lifecycle

```
Browser
  │
  ▼
index.php  (public)  or  invent/index.php  (admin)
  │  require app/bootstrap.php  →  autoloader + helpers + config + session
  ▼
App\Core\Router
  │  match method + path against the route table
  ▼
Controller  (app/Controllers or app/Controllers/Admin)
  │  uses Core primitives and Services
  ▼
View  (app/Views/...)
  │  rendered inside a layout
  ▼
App\Core\Response  →  HTTP response
```

`invent/` is a second front controller. It shares the same framework and
database but boots with the admin route table and the admin authentication
middleware. The unusual path is a small amount of discretion, not a security
control — the authentication middleware is.

---

## Core components (`app/Core`)

| Class | Responsibility |
|-------|----------------|
| `Router` | Route registration, parameterised matching, middleware dispatch |
| `Request` | Input access (`string`, `input`, `all`), IP, method |
| `Response` | HTML / JSON / redirect responses |
| `Database` | PDO singleton; `first`, `all`, `insert`, `update`, `run`, `transaction`, `scalar` |
| `View` | Template rendering and layout composition |
| `Session` | Session start, flash messages, regeneration, timeout |
| `Csrf` | Token generation and verification |
| `Auth` | Administrator authentication, throttling, lockout |
| `Validator` | Input validation with per-field error messages |
| `Settings` | Cached key/value settings access |
| `Cloudinary` | Image upload / delete / optimised URL helpers |
| `Logger` | File logging to `storage/logs` |
| `Config` | Configuration loading and dotted-key access |

---

## Services (`app/Services`)

Business rules live here, not in controllers.

| Service | Responsibility |
|---------|----------------|
| `InventoryService` | Stock arithmetic, the movement ledger, reserve / release / fulfil |
| `OrderService` | Order creation, cancellation, completion, reservation expiry |
| `PaymentService` | Manual payment recording with duplicate protection |
| `WalkInSaleService` | Physical store sales that deduct stock immediately |
| `MediaService` | Validated image uploads to Cloudinary, attach to products/properties |

---

## Controllers

**Public:** `HomeController`, `RealEstateController`, `CatalogueController`,
`CheckoutController`, `ContactController`, `SeoController`.

**Admin (`app/Controllers/Admin`):** dashboard, products, categories, brands,
inventory, orders, walk-in sales, payments, delivery, properties, property
categories, enquiries, reviews, messages, content, settings, activity, auth.

---

## Views

```
app/Views/
  layouts/     public.php, admin.php, admin-guest.php
  partials/    public-header, public-footer, product-card, property-card, pagination
  public/      home, about, contact, catalogue/, real-estate/, checkout/, errors/
  admin/       one folder per section
  emails/      order_confirmation, admin_notification, reservation_expired
```

Templates escape output through the global `e()` helper. Admin content fields
are stripped of markup before storage.

---

## Route tables

- `app/routes/public.php` — public routes
- `app/routes/admin.php` — admin routes, all behind the auth middleware

---

## Transactions and locking

Multi-step operations run inside `Database::transaction()`. Inventory mutations
take a row lock (`SELECT … FOR UPDATE`) on the affected product before checking
and changing stock. This is what prevents overselling under concurrency; see
[`DATABASE.md`](DATABASE.md#concurrency-and-idempotency).

---

## Configuration

`config/config.php` (git-ignored) is loaded by `app/bootstrap.php`. A committed
`config/config.example.php` documents every key. Secrets — database password,
Cloudinary credentials, SMTP password, `app_key` — live only in the real config.

---

## Front controllers and `.htaccess`

`index.php` serves the public site; `invent/index.php` serves the admin area.
`.htaccess` routes non-file requests to the right front controller, blocks
direct access to `app/`, `config/`, `database/`, `cron/` and `storage/`, and
sets security headers.

---

## Storage

`storage/logs` and `storage/cache` hold runtime files. They are not
web-accessible and are git-ignored apart from placeholder `.gitignore` files.
