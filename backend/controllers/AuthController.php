<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function register(Request $request): void
    {
        Response::json((new AuthService())->register($request->input()), 201, 'Registered');
    }

    public function login(Request $request): void
    {
        Response::json((new AuthService())->login($request->input()));
    }

    public function me(Request $request): void
    {
        Response::json(['account' => $this->auth($request)]);
    }

    public function logout(Request $request): void
    {
        (new AuthService())->logout($request->bearerToken());
        Response::json([], 200, 'Logged out');
    }
}
