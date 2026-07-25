<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

spl_autoload_register(function (string $class): void {
    $prefix = 'LamShaml\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = APP_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

require_once APP_PATH . '/helpers.php';

LamShaml\Core\Security::applyHeaders();
LamShaml\Core\Session::start();

set_exception_handler(function (Throwable $exception): void {
    $status = $exception instanceof LamShaml\Core\HttpException ? $exception->status : 500;
    http_response_code($status);
    if ($status >= 500) {
        error_log($exception->getMessage());
    }
    $message = $status >= 500 ? 'حدث خطأ غير متوقع. الرجاء المحاولة لاحقاً.' : $exception->getMessage();
    echo LamShaml\Core\View::render('errors/http', [
        'title' => 'خطأ',
        'message' => $message,
        'status' => $status,
    ]);
});
