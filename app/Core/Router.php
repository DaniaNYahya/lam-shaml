<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = [$method, trim($path, '/'), $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = trim(parse_url($_SERVER['PATH_INFO'] ?? $_GET['path'] ?? '', PHP_URL_PATH) ?: '', '/');
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            $params = $this->match($routePath, $path);
            if ($routeMethod === $method && $params !== null) {
                [$class, $action] = $handler;
                echo (new $class())->$action(...$params);
                return;
            }
        }
        throw new HttpException(404, 'الصفحة المطلوبة غير موجودة.');
    }

    private function match(string $route, string $path): ?array
    {
        $routeParts = $route === '' ? [] : explode('/', $route);
        $pathParts = $path === '' ? [] : explode('/', $path);
        if (count($routeParts) !== count($pathParts)) {
            return null;
        }
        $params = [];
        foreach ($routeParts as $i => $part) {
            if (preg_match('/^\{[a-z_]+\}$/', $part)) {
                $params[] = $pathParts[$i];
                continue;
            }
            if ($part !== $pathParts[$i]) {
                return null;
            }
        }
        return $params;
    }
}
