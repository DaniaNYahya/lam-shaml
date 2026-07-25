<?php
declare(strict_types=1);

use LamShaml\Core\Config;
use LamShaml\Core\Csrf;
use LamShaml\Core\Session;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(Config::get('app_url'), '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function old(string $key, string $default = ''): string
{
    return (string)(Session::flashOld()[$key] ?? $default);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function flash(string $type, string $message): void
{
    Session::flash($type, $message);
}

function mask_phone(?string $phone): string
{
    $phone = preg_replace('/\D+/', '', (string)$phone) ?? '';
    if (strlen($phone) <= 5) {
        return str_repeat('*', strlen($phone));
    }
    return substr($phone, 0, 3) . '••••' . substr($phone, -2);
}

function is_admin(): bool
{
    return (LamShaml\Core\Auth::user()['role'] ?? null) === 'admin';
}

function is_staff(): bool
{
    return in_array(LamShaml\Core\Auth::user()['role'] ?? null, ['admin', 'organization'], true);
}
