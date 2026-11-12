<?php
declare(strict_types=1);

use App\Controllers\Admin\ActivityController;
use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\BrandController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DeliveryController;
use App\Controllers\Admin\EnquiryController;
use App\Controllers\Admin\InventoryController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\PaymentController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\PropertyCategoryController;
use App\Controllers\Admin\PropertyController;
use App\Controllers\Admin\ReviewController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\WalkInSaleController;
use App\Core\Router;

/**
 * Administration routes (paths are relative to the admin base, /invent).
 */
return static function (Router $router): void {
    // --- Authentication ----------------------------------------------------
    $router->get('/login', [AdminAuthController::class, 'loginForm']);
    $router->post('/login', [AdminAuthController::class, 'login']);
    $router->get('/logout', [AdminAuthController::class, 'logout']);
    $router->get('/password', [AdminAuthController::class, 'passwordForm']);
    $router->post('/password', [AdminAuthController::class, 'updatePassword']);

    // --- Dashboard ---------------------------------------------------------
    $router->get('/', [DashboardController::class, 'index']);

    // --- Products ----------------------------------------------------------
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/create', [ProductController::class, 'create']);
    $router->post('/products', [ProductController::class, 'store']);
    $router->get('/products/{id}/edit', [ProductController::class, 'edit']);
    $router->post('/products/{id}', [ProductController::class, 'update']);
    $router->post('/products/{id}/delete', [ProductController::class, 'destroy']);
    $router->post('/products/{id}/toggle-publish', [ProductController::class, 'togglePublish']);
    $router->post('/products/{id}/toggle-featured', [ProductController::class, 'toggleFeatured']);
    $router->post('/products/{id}/images', [ProductController::class, 'uploadImages']);
    $router->post('/products/{id}/images/{imageId}/main', [ProductController::class, 'setMainImage']);
    $router->post('/products/{id}/images/{imageId}/delete', [ProductController::class, 'deleteImage']);

    // --- Product categories ------------------------------------------------
    $router->get('/product-categories', [CategoryController::class, 'index']);
    $router->post('/product-categories', [CategoryController::class, 'store']);
    $router->post('/product-categories/{id}', [CategoryController::class, 'update']);
    $router->post('/product-categories/{id}/delete', [CategoryController::class, 'destroy']);

    // --- Brands ------------------------------------------------------------
    $router->get('/brands', [BrandController::class, 'index']);
    $router->post('/brands', [BrandController::class, 'store']);
    $router->post('/brands/{id}', [BrandController::class, 'update']);
    $router->post('/brands/{id}/delete', [BrandController::class, 'destroy']);

    // --- Inventory ---------------------------------------------------------
    $router->get('/inventory', [InventoryController::class, 'index']);
    $router->get('/inventory/{id}', [InventoryController::class, 'show']);
    $router->post('/inventory/{id}/adjust', [InventoryController::class, 'adjust']);

    // --- Orders ------------------------------------------------------------
    $router->get('/orders', [OrderController::class, 'index']);
    $router->get('/orders/{id}', [OrderController::class, 'show']);
    $router->post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    $router->post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    $router->post('/orders/{id}/release', [OrderController::class, 'releaseStock']);
    $router->post('/orders/{id}/note', [OrderController::class, 'saveNote']);
    $router->get('/orders/{id}/receipt', [OrderController::class, 'receipt']);

    // --- Walk-in sales -----------------------------------------------------
    $router->get('/walk-in-sales', [WalkInSaleController::class, 'index']);
    $router->get('/walk-in-sales/create', [WalkInSaleController::class, 'create']);
    $router->post('/walk-in-sales', [WalkInSaleController::class, 'store']);
    $router->get('/walk-in-sales/{id}', [WalkInSaleController::class, 'show']);
    $router->get('/walk-in-sales/{id}/receipt', [WalkInSaleController::class, 'receipt']);

    // --- Payments ----------------------------------------------------------
    $router->get('/payments', [PaymentController::class, 'index']);
    $router->post('/payments', [PaymentController::class, 'store']);

    // --- Delivery settings -------------------------------------------------
    $router->get('/delivery-settings', [DeliveryController::class, 'index']);
    $router->post('/delivery-settings', [DeliveryController::class, 'store']);
    $router->post('/delivery-settings/{id}', [DeliveryController::class, 'update']);
    $router->post('/delivery-settings/{id}/delete', [DeliveryController::class, 'destroy']);

    // --- Properties --------------------------------------------------------
    $router->get('/properties', [PropertyController::class, 'index']);
    $router->get('/properties/create', [PropertyController::class, 'create']);
    $router->post('/properties', [PropertyController::class, 'store']);
    $router->get('/properties/{id}/edit', [PropertyController::class, 'edit']);
    $router->post('/properties/{id}', [PropertyController::class, 'update']);
    $router->post('/properties/{id}/delete', [PropertyController::class, 'destroy']);
    $router->post('/properties/{id}/toggle-publish', [PropertyController::class, 'togglePublish']);
    $router->post('/properties/{id}/toggle-featured', [PropertyController::class, 'toggleFeatured']);
    $router->post('/properties/{id}/images', [PropertyController::class, 'uploadImages']);
    $router->post('/properties/{id}/images/{imageId}/main', [PropertyController::class, 'setMainImage']);
    $router->post('/properties/{id}/images/{imageId}/delete', [PropertyController::class, 'deleteImage']);

    // --- Property categories ----------------------------------------------
    $router->get('/property-categories', [PropertyCategoryController::class, 'index']);
    $router->post('/property-categories', [PropertyCategoryController::class, 'store']);
    $router->post('/property-categories/{id}', [PropertyCategoryController::class, 'update']);
    $router->post('/property-categories/{id}/delete', [PropertyCategoryController::class, 'destroy']);

    // --- Enquiries ---------------------------------------------------------
    $router->get('/enquiries', [EnquiryController::class, 'index']);
    $router->get('/enquiries/{id}', [EnquiryController::class, 'show']);
    $router->post('/enquiries/{id}/status', [EnquiryController::class, 'updateStatus']);
    $router->post('/enquiries/{id}/delete', [EnquiryController::class, 'destroy']);

    // --- Reviews -----------------------------------------------------------
    $router->get('/reviews', [ReviewController::class, 'index']);
    $router->post('/reviews/{id}/status', [ReviewController::class, 'updateStatus']);
    $router->post('/reviews/{id}/delete', [ReviewController::class, 'destroy']);

    // --- Contact messages --------------------------------------------------
    $router->get('/messages', [MessageController::class, 'index']);
    $router->get('/messages/{id}', [MessageController::class, 'show']);
    $router->post('/messages/{id}/status', [MessageController::class, 'updateStatus']);
    $router->post('/messages/{id}/delete', [MessageController::class, 'destroy']);

    // --- Website content ---------------------------------------------------
    $router->get('/content', [ContentController::class, 'index']);
    $router->post('/content/section/{key}', [ContentController::class, 'updateSection']);
    $router->post('/content/{key}', [ContentController::class, 'update']);

    // --- Settings ----------------------------------------------------------
    $router->get('/settings', [SettingController::class, 'index']);
    $router->post('/settings', [SettingController::class, 'update']);

    // --- Activity log ------------------------------------------------------
    $router->get('/activity', [ActivityController::class, 'index']);
};
