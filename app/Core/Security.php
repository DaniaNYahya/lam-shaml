<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class Security
{
    public static function applyHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; script-src 'self' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') && Config::get('env') !== 'local') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
