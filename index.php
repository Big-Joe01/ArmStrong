<?php
declare(strict_types=1);

/**
 * Public front controller.
 * All public traffic is routed through this file via .htaccess.
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router   = new Router();
$register = require __DIR__ . '/app/routes/public.php';
$register($router);

$request  = Request::capture();
$response = $router->dispatch($request);

// Persist flash + old input lifecycle.
$response->send();
\App\Core\Session::clearOldInput();
