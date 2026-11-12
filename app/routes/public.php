<?php
declare(strict_types=1);

use App\Controllers\CatalogueController;
use App\Controllers\CheckoutController;
use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\RealEstateController;
use App\Controllers\SeoController;
use App\Core\Router;

/**
 * Public routes. Returned as a closure so index.php can register them.
 */
return static function (Router $router): void {
    // --- Home & static -----------------------------------------------------
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/about', [HomeController::class, 'about']);
    $router->get('/contact', [HomeController::class, 'contact']);
    $router->post('/contact', [ContactController::class, 'submit']);
    $router->post('/reviews', [ContactController::class, 'submitReview']);

    // --- Real estate -------------------------------------------------------
    $router->get('/real-estate', [RealEstateController::class, 'index']);
    $router->get('/real-estate/{slug}', [RealEstateController::class, 'show']);
    $router->post('/real-estate/{slug}/enquire', [RealEstateController::class, 'enquire']);

    // --- Building materials & interior ------------------------------------
    $router->get('/building-materials', [CatalogueController::class, 'index']);
    $router->get('/products/{slug}', [CatalogueController::class, 'show']);

    // --- Guest ordering ----------------------------------------------------
    $router->get('/order', [CheckoutController::class, 'form']);
    $router->post('/order', [CheckoutController::class, 'submit']);
    $router->get('/order/confirmation/{reference}', [CheckoutController::class, 'confirmation']);

    // --- SEO ---------------------------------------------------------------
    $router->get('/sitemap.xml', [SeoController::class, 'sitemap']);
    $router->get('/robots.txt', [SeoController::class, 'robots']);
};
