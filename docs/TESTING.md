# Testing & Quality Assurance

This document records the test plan for the platform and the results of the
verification run. Tests exercise real database-backed behaviour, not mocks.

---

## 1. Environment

| Item | Value |
|------|-------|
| PHP | 8.x with `pdo_mysql`, `curl`, `mbstring` |
| Database | MariaDB / MySQL 8 |
| Web server | `php -S localhost:8080` (development) or Apache |

Seed a clean database before running the suite:

```bash
php scripts/install.php --admin-user=admin --admin-pass='test-password' --seed-demo
php -S localhost:8080
```

---

## 2. Functional testing

### Public site

| Test | Expected | Result |
|------|----------|--------|
| Homepage loads | 200, hero + featured products/properties | Pass |
| About page | 200, mission/vision/values | Pass |
| Contact page | 200, form renders | Pass |
| Real estate landing | 200, listings + filters | Pass |
| Property detail by slug | 200, gallery, specs, enquiry form | Pass |
| Building materials landing | 200, product grid + filters | Pass |
| Product detail by slug | 200, options, related products | Pass |
| Product search (`?q=`) | 200, filtered results | Pass |
| Category / brand filters | 200, filtered results | Pass |
| sitemap.xml | 200, valid XML | Pass |
| robots.txt | 200 | Pass |

### Guest checkout and inventory

| Test | Expected | Result |
|------|----------|--------|
| Place order (collection) | 302 to confirmation, order stored | Pass |
| Order reference generated | `AHO-YYMMDD-XXXX` format | Pass |
| Stock reserved on order | `reserved_qty` increases | Pass |
| Reservation row created | `active`, `expires_at` = +7 days | Pass |
| Place order (delivery) | Delivery fee applied, delivery row created | Pass |
| Delivery fee shown before submit | Correct rate displayed | Pass |
| Order confirmation page | Reference, items, status, payment instructions | Pass |

### Reservation expiry

| Test | Expected | Result |
|------|----------|--------|
| Run `cron/expire_reservations.php` on lapsed order | 1 order expired, 1 reservation released | Pass |
| Run a second time | 0 released (idempotent) | Pass |
| Run a third time | 0 released (idempotent) | Pass |
| Stock released back to availability | `reserved_qty` decremented | Pass |
| Order marked expired | `status = expired` | Pass |

### Payments

| Test | Expected | Result |
|------|----------|--------|
| Record full payment | `payment_status = paid`, balance 0 | Pass |
| Record partial payment | `payment_status = partial`, balance reduced | Pass |
| Duplicate payment record | Rejected | Pass |
| Order not auto-marked paid on submit | Stays `unpaid` until recorded | Pass |

### Walk-in sales

| Test | Expected | Result |
|------|----------|--------|
| Record walk-in sale | Sale stored, reference generated | Pass |
| Stock deducted immediately | `stock_on_hand` decreases | Pass |
| Sell more than available | Rejected, no stock change | Pass |
| Receipt page | 200, printable | Pass |

### Order fulfilment

| Test | Expected | Result |
|------|----------|--------|
| Mark order `collected` | Physical stock deducted, `sale` movement | Pass |
| Mark order `delivered` | Physical stock deducted | Pass |
| Complete the same order twice | Deducted once (idempotent) | Pass |
| Cancel order | Reserved stock released | Pass |

### Property and content management

| Test | Expected | Result |
|------|----------|--------|
| Create property | Stored, slug generated, published | Pass |
| Edit property | Fields updated, slug regenerated if title changed | Pass |
| Delete property | Removed | Pass |
| Property enquiry (public) | Stored, visible in admin | Pass |
| Update enquiry status | Status and note saved | Pass |
| Contact message (public) | Stored, visible in admin | Pass |
| Mark message read | Status updated | Pass |
| Submit review | Stored as `pending` | Pass |
| Approve review | Status `approved`, appears publicly | Pass |
| Save content section | Stored against the section key | Pass |
| Save settings | Stored | Pass |

### Catalogue management

| Test | Expected | Result |
|------|----------|--------|
| Create product | Stored, slug, initial stock movement | Pass |
| Edit product | Fields updated | Pass |
| Create category / brand | Stored, slug generated | Pass |
| Create delivery rate | Stored | Pass |
| Manual stock add | `stock_on_hand` increases, ledger row | Pass |
| Manual stock remove beyond stock | Rejected, no change | Pass |

---

## 3. Security testing

| Test | Expected | Result |
|------|----------|--------|
| Admin route without session | 302 to `/invent/login` | Pass |
| Invalid credentials | Rejected | Pass |
| Login throttling (6 failures) | Locked out | Pass |
| Correct password while locked | Rejected | Pass |
| CSRF token missing/invalid | Request rejected | Pass |
| SQL injection in search | 200, no error, no injection | Pass |
| SQL injection in filter | 200, parameterised | Pass |
| XSS in content field | Markup stripped before storage | Pass |
| Direct access to `/config/config.php` | Blocked by `.htaccess` | Pass |
| Direct access to `/app/` | Blocked by `.htaccess` | Pass |
| Password stored hashed | `password_hash()` output | Pass |
| Session ID regenerated on login | New ID issued | Pass |

---

## 4. Inventory integrity testing

The critical invariants are checked by `scripts/test_concurrency.php`.

```bash
php scripts/test_concurrency.php
```

| Test | Expected | Result |
|------|----------|--------|
| Two buyers, last available unit | Exactly one succeeds | Pass |
| Losing buyer's message | Clear "only N available" | Pass |
| `reserved_qty` after race | ≤ `stock_on_hand` | Pass |
| Active reservations after race | 1 | Pass |
| Expired reservation releases once | Idempotent across runs | Pass |
| Cancelled order releases stock | Released once | Pass |
| Completed sale deducts once | Idempotent | Pass |
| Walk-in sale beyond stock | Rejected | Pass |
| Admin adjustment below reserved | Rejected | Pass |

Expected output:

```
Result: 1 success(es); stock_on_hand=1, reserved_qty=1, active_reservations=1
PASS: exactly one buyer reserved the last unit; the other was told stock was insufficient.
```

---

## 5. Manual regression checks

Run after any change to inventory or ordering code:

1. Place an order and confirm `reserved_qty` increased.
2. Force expiry (`UPDATE stock_reservations SET expires_at = NOW() - INTERVAL 1 DAY`),
   run the cron twice, confirm stock released once.
3. Complete an order and confirm `stock_on_hand` decreased and a `sale`
   movement exists.
4. Cancel an order and confirm the reservation was released.
5. Confirm no product has `reserved_qty > stock_on_hand`:

   ```sql
   SELECT id, name, stock_on_hand, reserved_qty
   FROM products WHERE reserved_qty > stock_on_hand;
   -- expect: empty set
   ```

6. Confirm every reservation is accounted for:

   ```sql
   SELECT status, COUNT(*) FROM stock_reservations GROUP BY status;
   ```

---

## 6. Deployment testing

Verify on a clean cPanel-style environment:

- [ ] Files uploaded, `config/config.php` created and not web-accessible
- [ ] Installer runs and creates the administrator
- [ ] Homepage and admin login work over HTTPS
- [ ] `.htaccess` blocks `app/`, `config/`, `database/`, `cron/`, `storage/`
- [ ] Image upload to Cloudinary succeeds
- [ ] A test order reserves stock and appears in admin
- [ ] Cron job runs and logs output
- [ ] `debug` is `false`

---

## 7. Known limitations

- Email delivery requires SMTP configuration; without it, notifications are
  logged instead. This never blocks an order.
- Image uploads require Cloudinary; without it, uploads are rejected with a
  clear message while the rest of the site keeps working.
- Payment is recorded manually by the administrator — there is intentionally no
  online payment gateway.
