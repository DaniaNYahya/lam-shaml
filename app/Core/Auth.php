<?php
declare(strict_types=1);

namespace LamShaml\Core;

use LamShaml\Repositories\UserRepository;

final class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        static $cached = null;
        if ($cached !== null && (int)$cached['account_id'] === (int)$_SESSION['user_id']) {
            return $cached;
        }
        $cached = (new UserRepository())->find((int)$_SESSION['user_id']);
        return $cached ?: null;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            redirect('login');
        }
        return $user;
    }

    public static function requireRole(array $roles): array
    {
        $user = self::requireLogin();
        if (!in_array($user['role'], $roles, true)) {
            throw new HttpException(403, 'ليست لديك صلاحية الوصول إلى هذه الصفحة.');
        }
        return $user;
    }

    public static function login(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}
