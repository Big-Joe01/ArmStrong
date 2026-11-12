<?php
declare(strict_types=1);

namespace App\Core;

/**
 * URL router with named parameters, e.g. /products/{slug}.
 */
final class Router
{
    /** @var list<array{method:string,pattern:string,regex:string,params:list<string>,handler:callable|array}> */
    private array $routes = [];

    /** @var list<callable> */
    private array $middleware = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $params = [];
        $regex  = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '(?P<' . $m[1] . '>[^/]+)';
            },
            $pattern
        );
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function use(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $name) {
                $args[$name] = $matches[$name];
            }

            $handler = function (Request $req) use ($route, $args): Response {
                foreach ($this->middleware as $mw) {
                    $result = $mw($req);
                    if ($result instanceof Response) {
                        return $result;
                    }
                }
                return $this->invoke($route['handler'], $req, $args);
            };

            return $handler($request);
        }

        return Response::html(
            View::render('public/errors/404', ['title' => 'Page not found']),
            404
        );
    }

    /** @param array<string,string> $args */
    private function invoke(callable|array $handler, Request $request, array $args): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller       = new $class();
            return $controller->$method($request, ...array_values($args));
        }
        return $handler($request, ...array_values($args));
    }
}
