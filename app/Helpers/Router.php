<?php

declare(strict_types=1);

namespace App\Helpers;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }
            foreach ($route['middleware'] as $middleware) {
                $middleware();
            }
            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }
            $handler(...array_values($params));
            return;
        }

        http_response_code(404);
        require __DIR__ . '/../Views/errors/404.php';
    }

    /**
     * Matches routes with {param} placeholders, e.g. /flats/{id}
     */
    private function match(string $routePath, string $requestPath): ?array
    {
        $paramNames = [];
        $pattern = preg_replace_callback('#\{(\w+)\}#', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $routePath);

        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        array_shift($matches);
        return array_combine($paramNames, $matches);
    }
}
