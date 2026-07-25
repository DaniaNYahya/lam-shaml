<?php
declare(strict_types=1);

namespace LamShaml\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(bool $withDatabase = true): PDO
    {
        if ($withDatabase && self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $db = Config::get('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;%scharset=%s',
            $db['host'],
            $db['port'],
            $withDatabase ? 'dbname=' . $db['database'] . ';' : '',
            $db['charset']
        );
        $pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($withDatabase) {
            self::$pdo = $pdo;
        }
        return $pdo;
    }
}
