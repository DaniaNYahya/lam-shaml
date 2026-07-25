<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => '#^' . $regex . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        if ($request->method() === 'OPTIONS') {
            Response::json();
            return;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            if (preg_match($route['regex'], $request->path(), $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    $controller->$method($request, $params);
                    return;
                }
                $handler($request, $params);
                return;
            }
        }

        throw new HttpException('Route not found: ' . $request->method() . ' ' . $request->path(), 404);
    }
}
