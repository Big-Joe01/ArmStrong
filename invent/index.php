<?php
declare(strict_types=1);

/**
 * Administration front controller (/invent).
 * All admin traffic is routed through this file.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\Admin\AdminAuthController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

$router   = new Router();
$register = require dirname(__DIR__) . '/app/routes/admin.php';
$register($router);

$request = Request::capture();

// --- Authentication middleware ---------------------------------------------
$router->use(static function (Request $req): ?Response {
    // Login screen and its POST handler are reachable without a session.
    if ($req->path === '/login' || $req->path === '/logout') {
        return null;
    }
    if (!Auth::check()) {
        Session::flash('error', 'Please sign in to continue.');
        return Response::redirect(url('invent/login'));
    }
    // Enforce inactivity timeout.
    Auth::touch();
    if (!Auth::check()) {
        return Response::redirect(url('invent/login'));
    }
    return null;
});

$response = $router->dispatch($request);
$response->send();
Session::clearOldInput();
