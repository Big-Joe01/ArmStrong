-- =============================================================================
--  Armstrong Homes & Interior Limited
--  Database schema  (MySQL 8+ / MariaDB 10.4+)
--  Engine: InnoDB  |  Charset: utf8mb4
-- =============================================================================
--  Conventions
--   * Money is DECIMAL(15,2)  - never FLOAT/DOUBLE.
--   * Every table uses surrogate integer PKs and UNIQUE business references.
--   * Stock is derived from inventory_movements (append-only ledger).
--   * Reservations are 1:1 with an order line and expire idempotently.
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. administrators  (single account by design)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS administrators (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name              VARCHAR(120)  NOT NULL,
    email             VARCHAR(190)  NOT NULL,
    username          VARCHAR(60)   NOT NULL,
    password_hash     VARCHAR(255)  NOT NULL,
    must_change_password TINYINT(1)  NOT NULL DEFAULT 0,
    last_login_at     DATETIME      NULL,
    last_login_ip     VARCHAR(45)   NULL,
    failed_attempts   INT UNSIGNED  NOT NULL DEFAULT 0,
    locked_until      DATETIME      NULL,
    is_active         TINYINT(1)    NOT NULL DEFAULT 1,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_email (email),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. settings  (key/value site configuration - editable by admin)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key   VARCHAR(120) NOT NULL,
    setting_value TEXT         NULL,
    setting_group VARCHAR(60)  NOT NULL DEFAULT 'general',
    setting_type  ENUM('text','textarea','html','number','boolean','email','phone','url','json') NOT NULL DEFAULT 'text',
    is_public     TINYINT(1)   NOT NULL DEFAULT 1,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_setting_key (setting_key),
    KEY idx_setting_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. content_sections  (manageable page blocks: hero, about, banners ...)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS content_sections (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    section_key   VARCHAR(120) NOT NULL,
    page          VARCHAR(60)  NOT NULL DEFAULT 'home',
    title         VARCHAR(255) NULL,
    subtitle      VARCHAR(255) NULL,
    body          TEXT         NULL,
    media_url     VARCHAR(500) NULL,
    media_public_id VARCHAR(255) NULL,
    cta_label     VARCHAR(120) NULL,
    cta_url       VARCHAR(500) NULL,
    sort_order    INT          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_section_key (section_key),
    KEY idx_section_page (page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. product_categories  (self-referencing tree)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_categories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id     INT UNSIGNED NULL,
    name          VARCHAR(150) NOT NULL,
    slug          VARCHAR(170) NOT NULL,
    description   TEXT         NULL,
    image_url     VARCHAR(500) NULL,
    image_public_id VARCHAR(255) NULL,
    sort_order    INT          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    meta_title    VARCHAR(255) NULL,
    meta_description VARCHAR(320) NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pcat_slug (slug),
    KEY idx_pcat_parent (parent_id),
    CONSTRAINT fk_pcat_parent FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. brands
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS brands (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(150) NOT NULL,
    slug          VARCHAR(170) NOT NULL,
    description   TEXT         NULL,
    logo_url      VARCHAR(500) NULL,
    logo_public_id VARCHAR(255) NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_brand_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. products
--    stock_on_hand : physical units recorded in inventory
--    reserved_qty  : units held by active reservations (maintained atomically)
--    available     : GENERATED = stock_on_hand - reserved_qty (never negative by app logic)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id       INT UNSIGNED NULL,
    brand_id          INT UNSIGNED NULL,
    name              VARCHAR(255) NOT NULL,
    slug              VARCHAR(280) NOT NULL,
    reference         VARCHAR(60)  NOT NULL,
    short_description VARCHAR(500) NULL,
    description       LONGTEXT     NULL,
    unit              VARCHAR(40)  NOT NULL DEFAULT 'piece',
    price             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    compare_price     DECIMAL(15,2) NULL,
    stock_on_hand     INT          NOT NULL DEFAULT 0,
    reserved_qty      INT          NOT NULL DEFAULT 0,
    low_stock_threshold INT        NOT NULL DEFAULT 5,
    availability      ENUM('available','temporarily_unavailable','discontinued','out_of_stock') NOT NULL DEFAULT 'available',
    is_published      TINYINT(1)   NOT NULL DEFAULT 0,
    is_featured       TINYINT(1)   NOT NULL DEFAULT 0,
    specs             JSON         NULL,   -- flexible key/value specifications
    colors            JSON         NULL,   -- ["Grey","White"]
    sizes             JSON         NULL,   -- ["60x60cm"]
    variations        JSON         NULL,   -- [{label,options:[..]}, ...]
    packaging         VARCHAR(120) NULL,
    weight_kg         DECIMAL(10,3) NULL,
    meta_title        VARCHAR(255) NULL,
    meta_description  VARCHAR(320) NULL,
    views             INT UNSIGNED NOT NULL DEFAULT 0,
    published_at      DATETIME     NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_product_slug (slug),
    UNIQUE KEY uq_product_reference (reference),
    KEY idx_product_category (category_id),
    KEY idx_product_brand (brand_id),
    KEY idx_product_published (is_published, availability),
    KEY idx_product_featured (is_featured),
    KEY idx_product_name (name),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_product_brand    FOREIGN KEY (brand_id)    REFERENCES brands(id) ON DELETE SET NULL,
    CONSTRAINT chk_product_stock   CHECK (stock_on_hand >= 0 AND reserved_qty >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. product_images
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id    INT UNSIGNED NOT NULL,
    image_url     VARCHAR(500) NOT NULL,
    public_id     VARCHAR(255) NULL,
    alt_text      VARCHAR(255) NULL,
    is_main       TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order    INT          NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pimg_product (product_id, is_main, sort_order),
    CONSTRAINT fk_pimg_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. property_categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS property_categories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(150) NOT NULL,
    slug          VARCHAR(170) NOT NULL,
    description   TEXT         NULL,
    image_url     VARCHAR(500) NULL,
    sort_order    INT          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    meta_title    VARCHAR(255) NULL,
    meta_description VARCHAR(320) NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_propcat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. properties
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS properties (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id       INT UNSIGNED NULL,
    title             VARCHAR(255) NOT NULL,
    slug              VARCHAR(280) NOT NULL,
    reference         VARCHAR(60)  NOT NULL,
    description       LONGTEXT     NULL,
    short_description VARCHAR(500) NULL,
    transaction_type  ENUM('for_sale','for_rent','for_lease','other') NOT NULL DEFAULT 'for_sale',
    property_type     VARCHAR(80)  NULL,
    price             DECIMAL(15,2) NULL,
    price_frequency   ENUM('total','per_year','per_month','per_week','per_day','negotiable') NOT NULL DEFAULT 'total',
    bedrooms          SMALLINT UNSIGNED NULL,
    bathrooms         SMALLINT UNSIGNED NULL,
    toilets           SMALLINT UNSIGNED NULL,
    land_size         VARCHAR(80)  NULL,
    building_size     VARCHAR(80)  NULL,
    parking           VARCHAR(80)  NULL,
    address           VARCHAR(255) NULL,
    neighbourhood     VARCHAR(150) NULL,
    city              VARCHAR(120) NULL,
    state             VARCHAR(120) NULL,
    country           VARCHAR(120) NOT NULL DEFAULT 'Nigeria',
    latitude          DECIMAL(10,7) NULL,
    longitude         DECIMAL(10,7) NULL,
    features          JSON         NULL,   -- ["Borehole","Solar","CCTV"]
    amenities         JSON         NULL,
    specifications    JSON         NULL,
    status            ENUM('available','reserved','sold','rented','leased','unavailable','draft') NOT NULL DEFAULT 'draft',
    is_published      TINYINT(1)   NOT NULL DEFAULT 0,
    is_featured       TINYINT(1)   NOT NULL DEFAULT 0,
    views             INT UNSIGNED NOT NULL DEFAULT 0,
    meta_title        VARCHAR(255) NULL,
    meta_description  VARCHAR(320) NULL,
    published_at      DATETIME     NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_property_slug (slug),
    UNIQUE KEY uq_property_reference (reference),
    KEY idx_property_category (category_id),
    KEY idx_property_status (is_published, status),
    KEY idx_property_featured (is_featured),
    KEY idx_property_location (state, city),
    KEY idx_property_transaction (transaction_type),
    KEY idx_property_price (price),
    CONSTRAINT fk_property_category FOREIGN KEY (category_id) REFERENCES property_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. property_images
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS property_images (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    property_id   INT UNSIGNED NOT NULL,
    image_url     VARCHAR(500) NOT NULL,
    public_id     VARCHAR(255) NULL,
    alt_text      VARCHAR(255) NULL,
    is_main       TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order    INT          NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_propimg_property (property_id, is_main, sort_order),
    CONSTRAINT fk_propimg_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. customers  (guest-first; created/updated from order details, no login)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(150) NOT NULL,
    phone         VARCHAR(40)  NOT NULL,
    email         VARCHAR(190) NULL,
    address       VARCHAR(255) NULL,
    city          VARCHAR(120) NULL,
    state         VARCHAR(120) NULL,
    notes         TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_customer_phone (phone),
    KEY idx_customer_email (email),
    KEY idx_customer_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. delivery_rates  (admin-configurable delivery options & fees)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS delivery_rates (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120) NOT NULL,
    description   VARCHAR(255) NULL,
    fee           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    timeframe     VARCHAR(120) NULL,
    sort_order    INT          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_delivery_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 13. orders  (guest checkout)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_reference   VARCHAR(30)  NOT NULL,
    customer_id       INT UNSIGNED NULL,
    customer_name     VARCHAR(150) NOT NULL,
    customer_phone    VARCHAR(40)  NOT NULL,
    customer_email    VARCHAR(190) NULL,
    fulfilment_method ENUM('collection','delivery') NOT NULL DEFAULT 'collection',
    delivery_rate_id  INT UNSIGNED NULL,
    delivery_fee      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    delivery_address  VARCHAR(500) NULL,
    delivery_city     VARCHAR(120) NULL,
    delivery_state    VARCHAR(120) NULL,
    delivery_timeframe VARCHAR(120) NULL,
    subtotal          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    amount_paid       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    balance           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_status    ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    status            ENUM('awaiting_payment','paid','processing','ready_for_collection','out_for_delivery','delivered','collected','cancelled','expired') NOT NULL DEFAULT 'awaiting_payment',
    reservation_expires_at DATETIME NULL,
    customer_note     TEXT         NULL,
    admin_note        TEXT         NULL,
    cancel_reason     VARCHAR(255) NULL,
    source            ENUM('online','walk_in') NOT NULL DEFAULT 'online',
    stock_released    TINYINT(1)   NOT NULL DEFAULT 0,   -- idempotency guard for release
    ip_address        VARCHAR(45)  NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_order_reference (order_reference),
    KEY idx_order_status (status),
    KEY idx_order_payment (payment_status),
    KEY idx_order_customer (customer_id),
    KEY idx_order_phone (customer_phone),
    KEY idx_order_created (created_at),
    KEY idx_order_expiry (reservation_expires_at, status),
    CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_order_delivery_rate FOREIGN KEY (delivery_rate_id) REFERENCES delivery_rates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 14. order_items
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id       INT UNSIGNED NOT NULL,
    product_id     INT UNSIGNED NULL,
    product_name   VARCHAR(255) NOT NULL,
    product_reference VARCHAR(60) NOT NULL,
    brand_name     VARCHAR(150) NULL,
    unit_price     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    quantity       INT UNSIGNED NOT NULL DEFAULT 1,
    line_total     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    selected_options JSON       NULL,   -- {brand,size,color,...}
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_oitem_order (order_id),
    KEY idx_oitem_product (product_id),
    CONSTRAINT fk_oitem_order   FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oitem_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 15. stock_reservations  (one row per reserved order line)
--     status: active -> released | expired | fulfilled
--     unique key makes expiry idempotent (a row can only be released once)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stock_reservations (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id       INT UNSIGNED NOT NULL,
    order_item_id  INT UNSIGNED NOT NULL,
    product_id     INT UNSIGNED NOT NULL,
    quantity       INT UNSIGNED NOT NULL,
    status         ENUM('active','released','expired','fulfilled') NOT NULL DEFAULT 'active',
    reserved_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at     DATETIME     NOT NULL,
    released_at    DATETIME     NULL,
    release_reason VARCHAR(120) NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_resv_order (order_id),
    KEY idx_resv_product (product_id),
    KEY idx_resv_status (status, expires_at),
    CONSTRAINT fk_resv_order   FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_resv_item    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_resv_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 16. inventory_movements  (append-only ledger; source of truth for stock)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inventory_movements (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id     INT UNSIGNED NOT NULL,
    movement_type  ENUM('initial','addition','reduction','reservation','reservation_release','sale','walk_in_sale','adjustment') NOT NULL,
    quantity       INT          NOT NULL,          -- signed: + adds, - removes
    stock_after    INT          NOT NULL,          -- stock_on_hand after movement
    reserved_after INT          NOT NULL,          -- reserved_qty after movement
    reference_type VARCHAR(40)  NULL,              -- order | walk_in_sale | manual
    reference_id   INT UNSIGNED NULL,
    reason         VARCHAR(255) NULL,
    admin_id       INT UNSIGNED NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inv_product (product_id, created_at),
    KEY idx_inv_type (movement_type),
    KEY idx_inv_ref (reference_type, reference_id),
    CONSTRAINT fk_inv_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_admin   FOREIGN KEY (admin_id)   REFERENCES administrators(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 17. payments  (manual recording only - no gateway)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id       INT UNSIGNED NULL,
    walk_in_sale_id INT UNSIGNED NULL,
    amount         DECIMAL(15,2) NOT NULL,
    method         ENUM('cash','bank_transfer','pos','other') NOT NULL DEFAULT 'cash',
    reference      VARCHAR(120) NULL,
    paid_at        DATETIME     NOT NULL,
    note           VARCHAR(255) NULL,
    admin_id       INT UNSIGNED NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payment_order (order_id),
    KEY idx_payment_walkin (walk_in_sale_id),
    KEY idx_payment_date (paid_at),
    CONSTRAINT fk_payment_order  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_admin  FOREIGN KEY (admin_id) REFERENCES administrators(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 18. walk_in_sales  (physical store transactions)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS walk_in_sales (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sale_reference VARCHAR(30)  NOT NULL,
    customer_name  VARCHAR(150) NULL,
    customer_phone VARCHAR(40)  NULL,
    subtotal       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    amount_paid    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    balance        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'paid',
    note           TEXT         NULL,
    admin_id       INT UNSIGNED NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_walkin_reference (sale_reference),
    KEY idx_walkin_date (created_at),
    CONSTRAINT fk_walkin_admin FOREIGN KEY (admin_id) REFERENCES administrators(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 19. walk_in_sale_items
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS walk_in_sale_items (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    walk_in_sale_id INT UNSIGNED NOT NULL,
    product_id     INT UNSIGNED NULL,
    product_name   VARCHAR(255) NOT NULL,
    product_reference VARCHAR(60) NOT NULL,
    unit_price     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    quantity       INT UNSIGNED NOT NULL DEFAULT 1,
    line_total     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    selected_options JSON       NULL,
    PRIMARY KEY (id),
    KEY idx_witem_sale (walk_in_sale_id),
    CONSTRAINT fk_witem_sale    FOREIGN KEY (walk_in_sale_id) REFERENCES walk_in_sales(id) ON DELETE CASCADE,
    CONSTRAINT fk_witem_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 20. deliveries
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS deliveries (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id        INT UNSIGNED NOT NULL,
    status          ENUM('pending','scheduled','dispatched','delivered','collected','cancelled') NOT NULL DEFAULT 'pending',
    recipient_name  VARCHAR(150) NULL,
    address         VARCHAR(500) NULL,
    city            VARCHAR(120) NULL,
    state           VARCHAR(120) NULL,
    fee             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    timeframe       VARCHAR(120) NULL,
    dispatched_at   DATETIME     NULL,
    completed_at    DATETIME     NULL,
    note            TEXT         NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_delivery_order (order_id),
    CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 21. property_enquiries
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS property_enquiries (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    property_id  INT UNSIGNED NULL,
    full_name    VARCHAR(150) NOT NULL,
    email        VARCHAR(190) NULL,
    phone        VARCHAR(40)  NOT NULL,
    message      TEXT         NULL,
    status       ENUM('new','read','responded','closed') NOT NULL DEFAULT 'new',
    admin_note   TEXT         NULL,
    ip_address   VARCHAR(45)  NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_enq_property (property_id),
    KEY idx_enq_status (status, created_at),
    CONSTRAINT fk_enq_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 22. contact_messages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name  VARCHAR(150) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    phone      VARCHAR(40)  NULL,
    subject    VARCHAR(200) NULL,
    message    TEXT         NOT NULL,
    status     ENUM('unread','read','archived') NOT NULL DEFAULT 'unread',
    admin_note TEXT         NULL,
    ip_address VARCHAR(45)  NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_msg_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 23. reviews  (moderated)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED NULL,   -- NULL = general company review
    order_id    INT UNSIGNED NULL,
    customer_name VARCHAR(150) NOT NULL,
    email       VARCHAR(190) NULL,
    rating      TINYINT UNSIGNED NOT NULL DEFAULT 5,
    title       VARCHAR(200) NULL,
    body        TEXT         NOT NULL,
    status      ENUM('pending','approved','rejected','hidden') NOT NULL DEFAULT 'pending',
    ip_address  VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_review_product (product_id, status),
    KEY idx_review_status (status, created_at),
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 24. order_status_history  (audit trail of order changes)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_status_history (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id    INT UNSIGNED NOT NULL,
    from_status VARCHAR(40)  NULL,
    to_status   VARCHAR(40)  NOT NULL,
    note        VARCHAR(255) NULL,
    admin_id    INT UNSIGNED NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_osh_order (order_id, created_at),
    CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_osh_admin FOREIGN KEY (admin_id) REFERENCES administrators(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 25. activity_logs  (admin actions & system events)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id    INT UNSIGNED NULL,
    action      VARCHAR(120) NOT NULL,
    entity_type VARCHAR(60)  NULL,
    entity_id   INT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    meta        JSON         NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_admin (admin_id, created_at),
    KEY idx_log_action (action),
    KEY idx_log_entity (entity_type, entity_id),
    CONSTRAINT fk_log_admin FOREIGN KEY (admin_id) REFERENCES administrators(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 26. login_attempts  (rate limiting / throttling)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username   VARCHAR(60)  NOT NULL,
    ip_address VARCHAR(45)  NOT NULL,
    success    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_lookup (ip_address, created_at),
    KEY idx_login_user (username, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
