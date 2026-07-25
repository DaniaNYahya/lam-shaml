<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script !== '/' && str_starts_with($uri, $script)) {
            $uri = substr($uri, strlen($script));
        }
        return '/' . trim($uri, '/');
    }

    public function input(): array
    {
        $type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($type, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '{}';
            $json = json_decode($raw, true);
            return is_array($json) ? $json : [];
        }
        return $_POST ?: [];
    }

    public function query(): array
    {
        return $_GET;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
