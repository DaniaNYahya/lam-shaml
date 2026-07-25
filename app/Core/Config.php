<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class Config
{
    private static ?array $values = null;

    public static function all(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }
        $env = [
            'APP_ENV' => 'local',
            'APP_URL' => 'http://localhost/lam-shaml/public',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'lam_shaml',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'DB_CHARSET' => 'utf8mb4',
            'UPLOAD_MAX_BYTES' => '2097152',
        ];
        $envPath = BASE_PATH . '/.env';
        if (is_file($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
        self::$values = [
            'env' => $env['APP_ENV'],
            'app_url' => $env['APP_URL'],
            'db' => [
                'host' => $env['DB_HOST'],
                'port' => $env['DB_PORT'],
                'database' => $env['DB_DATABASE'],
                'username' => $env['DB_USERNAME'],
                'password' => $env['DB_PASSWORD'],
                'charset' => $env['DB_CHARSET'],
            ],
            'upload_max_bytes' => (int)$env['UPLOAD_MAX_BYTES'],
        ];
        return self::$values;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = self::all();
        return $values[$key] ?? $default;
    }
}
