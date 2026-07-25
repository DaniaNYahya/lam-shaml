<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;

abstract class Controller
{
    protected function auth(Request $request): array
    {
        $account = (new AuthService())->currentAccount($request->bearerToken());
        if (!$account) {
            throw new HttpException('Authentication required', 401);
        }
        if ($account['status'] !== 'active') {
            throw new HttpException('Account is not active', 403);
        }
        return $account;
    }

    protected function admin(Request $request): array
    {
        $account = $this->auth($request);
        if ($account['role'] !== 'admin') {
            throw new HttpException('Admin permission required', 403);
        }
        return $account;
    }
}
