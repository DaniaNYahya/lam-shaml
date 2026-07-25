<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\MatchController;
use App\Controllers\NotificationController;
use App\Controllers\RequestController;
use App\Controllers\SearchController;
use App\Core\Request;
use App\Core\Router;

$router = new Router();

$router->add('POST', '/auth/register', [AuthController::class, 'register']);
$router->add('POST', '/auth/login', [AuthController::class, 'login']);
$router->add('GET', '/auth/me', [AuthController::class, 'me']);
$router->add('POST', '/auth/logout', [AuthController::class, 'logout']);

$router->add('GET', '/stats', [RequestController::class, 'stats']);
$router->add('POST', '/requests', [RequestController::class, 'create']);
$router->add('GET', '/requests/mine', [RequestController::class, 'mine']);
$router->add('GET', '/requests/{id}', [RequestController::class, 'show']);
$router->add('GET', '/search', [SearchController::class, 'index']);
$router->add('POST', '/matches/report', [MatchController::class, 'report']);
$router->add('GET', '/notifications', [NotificationController::class, 'index']);
$router->add('POST', '/notifications/{id}/read', [NotificationController::class, 'read']);

$router->add('GET', '/admin/dashboard', [AdminController::class, 'dashboard']);
$router->add('GET', '/admin/users', [AdminController::class, 'users']);
$router->add('POST', '/admin/users/{id}/status', [AdminController::class, 'userStatus']);
$router->add('GET', '/admin/requests', [AdminController::class, 'requests']);
$router->add('POST', '/admin/requests/{id}/status', [AdminController::class, 'requestStatus']);
$router->add('GET', '/admin/matches', [AdminController::class, 'matches']);
$router->add('POST', '/admin/matches/{id}/status', [AdminController::class, 'matchStatus']);
$router->add('GET', '/admin/reports', [AdminController::class, 'reports']);

$router->dispatch(new Request());
