<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use LamShaml\Core\Config;
use LamShaml\Core\Database;

header('Content-Type: text/plain; charset=utf-8');

$host = $_SERVER['REMOTE_ADDR'] ?? 'cli';
$allowedCli = PHP_SAPI === 'cli';
$allowedWeb = in_array($host, ['127.0.0.1', '::1'], true) || str_contains((string)($_SERVER['HTTP_HOST'] ?? ''), 'localhost');
if (!$allowedCli && !$allowedWeb) {
    http_response_code(403);
    exit("مرفوض: سكربت التثبيت يعمل محلياً فقط.\n");
}

$lock = __DIR__ . '/install.lock';
if (is_file($lock)) {
    exit("تم التثبيت مسبقاً. احذف database/install.lock يدوياً إذا أردت إعادة التثبيت.\n");
}

try {
    $config = Config::get('db');
    $pdo = Database::pdo(false);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $config['database']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . str_replace('`', '', $config['database']) . '`');

    foreach (['schema.sql', 'seed.sql'] as $file) {
        $sql = file_get_contents(__DIR__ . '/' . $file);
        if ($sql === false) {
            throw new RuntimeException('تعذر قراءة ' . $file);
        }
        $pdo->exec($sql);
        echo "تم تنفيذ $file\n";
    }

    if (file_put_contents($lock, 'installed_at=' . date(DATE_ATOM) . PHP_EOL) === false) {
        throw new RuntimeException('Install lock write failed.');
    }
    echo "اكتمل التثبيت بنجاح.\n";
} catch (Throwable $exception) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Database installation failed: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
    throw $exception;
}
