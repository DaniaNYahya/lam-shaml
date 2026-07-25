<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);
    $map = [
        'Core' => 'core',
        'Controllers' => 'controllers',
        'Repositories' => 'repositories',
        'Services' => 'services',
    ];
    if (isset($map[$parts[0]])) {
        $parts[0] = $map[$parts[0]];
    }

    $path = BASE_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

set_exception_handler(function (Throwable $e): void {
    $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    $config = require BASE_PATH . '/config/config.php';
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $status === 500 ? 'Internal server error' : $e->getMessage(),
        'debug' => $config['app']['env'] === 'local' ? $e->getMessage() : null,
    ], JSON_UNESCAPED_UNICODE);
});

if (!is_dir(BASE_PATH . '/storage/uploads')) {
    mkdir(BASE_PATH . '/storage/uploads', 0775, true);
}
