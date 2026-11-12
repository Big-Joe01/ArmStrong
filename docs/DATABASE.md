# Database Architecture

The schema lives in [`database/schema.sql`](../database/schema.sql) and is applied
by `scripts/install.php`. This document explains the design and the rules that
keep inventory and financial data correct.

---

## Design principles

- **Normalised** — entities are separated and linked by foreign keys.
- **Money is `DECIMAL(15,2)`** — never floating point, so totals are exact.
- **Append-only history** — inventory movements, payments, order status changes
  and activity logs are recorded, never overwritten.
- **Referential integrity** — foreign keys with deliberate `ON DELETE` behaviour
  protect order history when a catalogue item is removed.
- **Indexed lookups** — commonly filtered columns (slugs, statuses, references,
  foreign keys, dates) are indexed.

---

## Entity groups

### Catalogue

| Table | Purpose |
|-------|---------|
| `product_categories` | Category tree (self-referencing `parent_id`) |
| `brands` | Product brands |
| `products` | Product master data, pricing, stock counters, options |
| `product_images` | Cloudinary URLs + public IDs, one flagged `is_main` |

### Real estate

| Table | Purpose |
|-------|---------|
| `property_categories` | Property types (residential, commercial, land…) |
| `properties` | Listing data: type, transaction, location, specs, status |
| `property_images` | Cloudinary URLs, one flagged `is_main` |
| `property_enquiries` | Visitor enquiries per property |

### Commerce

| Table | Purpose |
|-------|---------|
| `customers` | Guest customers, deduplicated by phone |
| `orders` | Order header, totals, payment and fulfilment status |
| `order_items` | Order lines with a price/name snapshot |
| `order_status_history` | Every status change with a note and administrator |
| `stock_reservations` | One row per reserved order line |
| `inventory_movements` | Append-only stock ledger |
| `delivery_rates` | Configurable delivery options and fees |
| `deliveries` | Delivery details for delivery orders |
| `payments` | Manual payment records for orders and walk-in sales |
| `walk_in_sales` / `walk_in_sale_items` | Physical store transactions |

### Content & system

| Table | Purpose |
|-------|---------|
| `reviews` | Customer reviews with moderation status |
| `contact_messages` | Contact form submissions |
| `content_sections` | Editable homepage/about blocks (keyed) |
| `settings` | Key/value site settings |
| `administrators` | The single administrator account |
| `login_attempts` | Login throttling and audit trail |
| `activity_logs` | Administrator action log |

---

## Inventory model

Two counters on `products` represent stock:

| Column | Meaning |
|--------|---------|
| `stock_on_hand` | Physical units recorded in inventory |
| `reserved_qty` | Units held by active reservations |

**Available stock** is always computed, never stored:

```
available = stock_on_hand - reserved_qty
```

The invariant `reserved_qty <= stock_on_hand` is enforced by every mutation and
`available` is never allowed to go negative.

### The ledger

`inventory_movements` records every change to stock. `quantity` is the **signed
change applied to physical stock**:

| `movement_type` | Effect on `stock_on_hand` | Effect on `reserved_qty` |
|-----------------|---------------------------|--------------------------|
| `initial` | set to opening quantity | unchanged |
| `addition` | `+quantity` | unchanged |
| `reduction` | `-quantity` | unchanged |
| `reservation` | none (`quantity = 0`) | `+reserved` |
| `reservation_release` | none (`quantity = 0`) | `-reserved` |
| `sale` | `-quantity` | `-reserved` |
| `walk_in_sale` | `-quantity` | unchanged |

Reservation rows carry `quantity = 0` because they do not move physical stock;
the resulting reserved level is captured in `reserved_after`, and the reserved
quantity itself lives in `stock_reservations`.

---

## Reservation lifecycle

```
order placed ──► reservation active (7 days) ──┬──► paid & collected ──► fulfilled (stock leaves)
                                               └──► 7 days elapse ─────► expired (stock released)
```

1. `OrderService::create()` opens a transaction, locks the products with
   `SELECT … FOR UPDATE`, validates availability, writes the order and lines,
   then calls `InventoryService::reserveForOrder()`.
2. Each line creates an `active` reservation with `expires_at = now + 7 days`
   and increments `reserved_qty`.
3. `cron/expire_reservations.php` calls `OrderService::expireStaleReservations()`,
   which marks lapsed reservations `expired` and decrements `reserved_qty`.
4. Completing an order calls `InventoryService::fulfilOrderReservations()`,
   marking reservations `fulfilled` and moving the quantity from `reserved_qty`
   to an actual `stock_on_hand` reduction (a `sale` movement).
5. Cancelling calls `InventoryService::releaseOrderReservations()`, marking
   reservations `released` and decrementing `reserved_qty`.

### Concurrency and idempotency

- **No overselling.** Product rows are locked with `FOR UPDATE` before the
  availability check, so two simultaneous buyers cannot both reserve the last
  unit. The loser receives a clear "only N available" message.
- **Exactly-once release.** Every release is a conditional update
  (`… WHERE id = ? AND status = 'active'`) inside a locked transaction, so a
  second call — or a concurrent cron run — affects zero rows.
- **Idempotent expiry.** The `orders.stock_released` flag plus the conditional
  reservation update guarantee stock is released at most once per order.

---

## Financial integrity

- Totals use `DECIMAL(15,2)`.
- `orders` keeps `subtotal`, `delivery_fee`, `total`, `amount_paid` and
  `balance`.
- `payment_status` (`unpaid` / `partial` / `paid`) is derived from recorded
  payments — an order is never marked paid merely because it was submitted.
- Duplicate payment records are prevented: an identical amount, method,
  reference and date for the same order is rejected.

---

## Order and property statuses

**Order status:** `awaiting_payment`, `paid`, `processing`,
`ready_for_collection`, `out_for_delivery`, `delivered`, `collected`,
`cancelled`, `expired`.

**Payment status:** `unpaid`, `partial`, `paid`.

**Property status:** `available`, `reserved`, `sold`, `rented`, `leased`,
`unavailable`, `draft`.

**Product availability:** `available`, `temporarily_unavailable`,
`discontinued`, `out_of_stock`.

---

## Public URLs

Public URLs use slugs, never numeric IDs:

| Page | Pattern |
|------|---------|
| Real estate landing | `/real-estate` |
| Property detail | `/real-estate/{slug}` |
| Catalogue landing | `/building-materials` |
| Product detail | `/products/{slug}` |

`slug` columns are unique and indexed.

---

## Backups

```bash
mysqldump -u USER -p armstrong > armstrong-$(date +%F).sql
```

Image binaries live on Cloudinary; the database stores only URLs and public IDs,
so a database dump plus a Cloudinary account fully restores the catalogue.
