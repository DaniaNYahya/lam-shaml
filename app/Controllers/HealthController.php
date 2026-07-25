<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Database;

final class HealthController
{
    public function index(): string
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = 'down';
        try {
            Database::pdo()->query('SELECT 1');
            $db = 'up';
        } catch (\Throwable) {
            $db = 'down';
        }
        return json_encode([
            'ok' => $db === 'up',
            'php' => PHP_VERSION,
            'database' => $db,
            'gd' => extension_loaded('gd'),
            'time' => date(DATE_ATOM),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
