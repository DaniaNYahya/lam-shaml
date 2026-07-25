<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(): void
    {
        $token = (string)($_POST['_csrf'] ?? '');
        if ($token === '' || !hash_equals((string)($_SESSION['_csrf'] ?? ''), $token)) {
            throw new HttpException(403, 'انتهت صلاحية النموذج أو رمز الحماية غير صحيح.');
        }
    }
}
