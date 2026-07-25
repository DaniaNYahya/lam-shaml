<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(array $data = [], int $status = 200, string $message = 'OK'): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        echo json_encode([
            'success' => $status < 400,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
    }
}
