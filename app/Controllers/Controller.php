<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

/**
 * Shared controller helpers.
 */
abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/public'): Response
    {
        return Response::html(View::render($view, $data, $layout));
    }

    /** @param array<string,mixed> $data */
    protected function adminView(string $view, array $data = []): Response
    {
        return Response::html(View::render($view, $data, 'layouts/admin'));
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect(url($path));
    }

    /** @param array<string,mixed> $data */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function back(Request $request, string $fallback = '/'): Response
    {
        $referer = $request->server['HTTP_REFERER'] ?? '';
        if ($referer !== '' && str_starts_with($referer, url(''))) {
            return Response::redirect($referer);
        }
        return $this->redirect($fallback);
    }
}
