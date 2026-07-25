<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_set_cookie_params([
            'httponly' => true,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_name('LAMSHAMLSESSID');
        session_start();
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function flashes(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    public static function rememberOld(array $data): void
    {
        $_SESSION['_old'] = $data;
    }

    public static function flashOld(): array
    {
        static $old = null;
        if ($old === null) {
            $old = $_SESSION['_old'] ?? [];
            unset($_SESSION['_old']);
        }
        return is_array($old) ? $old : [];
    }
}
