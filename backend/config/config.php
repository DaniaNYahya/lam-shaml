<?php

return [
    'app' => [
        'name' => 'Lam Shaml API',
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost/lamshaml/backend/public',
        'env' => getenv('APP_ENV') ?: 'local',
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3308',
        'database' => getenv('DB_DATABASE') ?: 'lam_shaml',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'auth' => [
        'token_days' => 7,
    ],
    'uploads' => [
        'dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads',
        'public_prefix' => '/storage/uploads/',
        'max_bytes' => 2 * 1024 * 1024,
    ],
];
